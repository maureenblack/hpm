<?php
/**
 * Stripe Webhook Handler
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - Webhook signature verification
 * - Event validation
 * - Secure database updates
 * - Error logging
 */

// Set strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/stripe-handler.php';

// Get the webhook secret from environment variables
$webhookSecret = getenv('STRIPE_WEBHOOK_SECRET') ?: '';

if (empty($webhookSecret)) {
    error_log('Stripe webhook secret is not configured');
    http_response_code(500);
    echo json_encode(['error' => 'Webhook configuration error']);
    exit;
}

// Get the raw POST data
$payload = @file_get_contents('php://input');

// Get the Stripe signature header
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (empty($sigHeader)) {
    error_log('Stripe webhook signature header is missing');
    http_response_code(400);
    echo json_encode(['error' => 'Signature header missing']);
    exit;
}

// Validate the webhook signature
$event = validateStripeWebhook($payload, $sigHeader, $webhookSecret);

if (!$event) {
    error_log('Stripe webhook validation failed');
    http_response_code(400);
    echo json_encode(['error' => 'Invalid webhook signature']);
    exit;
}

// Handle the event
try {
    switch ($event->type) {
        case 'payment_intent.succeeded':
            $paymentIntent = $event->data->object;
            handleSuccessfulPayment($paymentIntent);
            break;
            
        case 'payment_intent.payment_failed':
            $paymentIntent = $event->data->object;
            handleFailedPayment($paymentIntent);
            break;
            
        case 'invoice.payment_succeeded':
            $invoice = $event->data->object;
            handleSuccessfulInvoice($invoice);
            break;
            
        case 'invoice.payment_failed':
            $invoice = $event->data->object;
            handleFailedInvoice($invoice);
            break;
            
        case 'customer.subscription.created':
            $subscription = $event->data->object;
            handleSubscriptionCreated($subscription);
            break;
            
        case 'customer.subscription.updated':
            $subscription = $event->data->object;
            handleSubscriptionUpdated($subscription);
            break;
            
        case 'customer.subscription.deleted':
            $subscription = $event->data->object;
            handleSubscriptionCancelled($subscription);
            break;
            
        default:
            // Unexpected event type
            error_log('Unhandled event type: ' . $event->type);
    }
    
    // Return a 200 response to acknowledge receipt of the event
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Stripe webhook error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing error']);
}

/**
 * Handle successful payment intent
 * 
 * @param \Stripe\PaymentIntent $paymentIntent
 * @return void
 */
function handleSuccessfulPayment($paymentIntent) {
    global $pdo;
    
    try {
        // Get payment intent ID
        $paymentIntentId = $paymentIntent->id;
        
        // Find transaction by payment intent ID
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.donor_id, t.amount, t.reference_code, t.is_recurring, t.recurring_frequency,
                   d.email, d.first_name, d.last_name
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            JOIN donors d ON t.donor_id = d.donor_id
            WHERE sp.stripe_charge_id = ?
        ");
        $stmt->execute([$paymentIntentId]);
        $transaction = $stmt->fetch();
        
        if (!$transaction) {
            // If transaction not found by payment intent ID, check metadata
            if (isset($paymentIntent->metadata->reference_code)) {
                $referenceCode = $paymentIntent->metadata->reference_code;
                
                $stmt = $pdo->prepare("
                    SELECT transaction_id, donor_id, amount, reference_code, is_recurring, recurring_frequency
                    FROM transactions
                    WHERE reference_code = ?
                ");
                $stmt->execute([$referenceCode]);
                $transaction = $stmt->fetch();
                
                if ($transaction) {
                    // Insert Stripe payment details
                    $stmt = $pdo->prepare("
                        INSERT INTO stripe_payments (
                            transaction_id, stripe_charge_id, card_last_four, card_brand
                        ) VALUES (?, ?, ?, ?)
                    ");
                    
                    $charge = $paymentIntent->charges->data[0];
                    $cardLastFour = $charge->payment_method_details->card->last4 ?? null;
                    $cardBrand = $charge->payment_method_details->card->brand ?? null;
                    
                    $stmt->execute([
                        $transaction['transaction_id'], 
                        $paymentIntentId, 
                        $cardLastFour, 
                        $cardBrand
                    ]);
                }
            }
        }
        
        if ($transaction) {
            // Update transaction status
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET payment_status = 'completed', updated_at = NOW()
                WHERE transaction_id = ?
            ");
            $stmt->execute([$transaction['transaction_id']]);
            
            // Generate receipt
            $receiptNumber = generateReceiptNumber($transaction['transaction_id']);
            $stmt = $pdo->prepare("
                INSERT INTO receipts (transaction_id, receipt_number)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE receipt_date = NOW()
            ");
            $stmt->execute([$transaction['transaction_id'], $receiptNumber]);
            
            // Send confirmation email
            if (isset($transaction['email'])) {
                $emailData = [
                    'donor_name' => $transaction['first_name'] . ' ' . $transaction['last_name'],
                    'amount' => formatCurrency($transaction['amount']),
                    'date' => date('F j, Y'),
                    'reference_code' => $transaction['reference_code'],
                    'payment_method' => 'Credit/Debit Card',
                    'tax_status' => 'is'
                ];
                
                sendEmail($transaction['email'], 'donation_receipt', $emailData);
            }
            
            // Log activity
            logActivity(null, "Payment completed via Stripe", "transaction", $transaction['transaction_id']);
        } else {
            error_log("Transaction not found for payment intent: " . $paymentIntentId);
        }
    } catch (Exception $e) {
        error_log("Error handling successful payment: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle failed payment intent
 * 
 * @param \Stripe\PaymentIntent $paymentIntent
 * @return void
 */
function handleFailedPayment($paymentIntent) {
    global $pdo;
    
    try {
        // Get payment intent ID
        $paymentIntentId = $paymentIntent->id;
        
        // Find transaction by payment intent ID
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.donor_id, t.reference_code, d.email, d.first_name, d.last_name
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            JOIN donors d ON t.donor_id = d.donor_id
            WHERE sp.stripe_charge_id = ?
        ");
        $stmt->execute([$paymentIntentId]);
        $transaction = $stmt->fetch();
        
        if (!$transaction && isset($paymentIntent->metadata->reference_code)) {
            // If transaction not found by payment intent ID, check metadata
            $referenceCode = $paymentIntent->metadata->reference_code;
            
            $stmt = $pdo->prepare("
                SELECT t.transaction_id, t.donor_id, t.reference_code, d.email, d.first_name, d.last_name
                FROM transactions t
                JOIN donors d ON t.donor_id = d.donor_id
                WHERE t.reference_code = ?
            ");
            $stmt->execute([$referenceCode]);
            $transaction = $stmt->fetch();
        }
        
        if ($transaction) {
            // Update transaction status
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET payment_status = 'failed', updated_at = NOW(),
                    notes = CONCAT(IFNULL(notes, ''), ' | Payment failed: ', ?)
                WHERE transaction_id = ?
            ");
            
            $errorMessage = $paymentIntent->last_payment_error ? 
                            $paymentIntent->last_payment_error->message : 
                            'Unknown error';
            
            $stmt->execute([$errorMessage, $transaction['transaction_id']]);
            
            // Log activity
            logActivity(null, "Payment failed via Stripe", "transaction", $transaction['transaction_id']);
            
            // Send failure notification email
            if (isset($transaction['email'])) {
                // You would need to create a payment_failed email template
                $emailData = [
                    'donor_name' => $transaction['first_name'] . ' ' . $transaction['last_name'],
                    'reference_code' => $transaction['reference_code'],
                    'error_message' => $errorMessage
                ];
                
                // Assuming you have a payment_failed email template
                // sendEmail($transaction['email'], 'payment_failed', $emailData);
            }
        } else {
            error_log("Transaction not found for failed payment intent: " . $paymentIntentId);
        }
    } catch (Exception $e) {
        error_log("Error handling failed payment: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle successful invoice payment (for subscriptions)
 * 
 * @param \Stripe\Invoice $invoice
 * @return void
 */
function handleSuccessfulInvoice($invoice) {
    global $pdo;
    
    try {
        // Get subscription ID
        $subscriptionId = $invoice->subscription;
        
        if (!$subscriptionId) {
            error_log("No subscription ID found in invoice: " . $invoice->id);
            return;
        }
        
        // Find existing subscription in our database
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.donor_id, t.amount, t.reference_code, 
                   d.email, d.first_name, d.last_name
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            JOIN donors d ON t.donor_id = d.donor_id
            WHERE sp.stripe_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            // This is a recurring payment for an existing subscription
            
            // Create a new transaction record for this payment
            $stmt = $pdo->prepare("
                INSERT INTO transactions (
                    donor_id, amount, currency, payment_method, payment_status,
                    category_id, is_recurring, recurring_frequency, is_anonymous,
                    cover_fees, fee_amount, reference_code, notes
                )
                SELECT donor_id, amount, currency, payment_method, 'completed',
                       category_id, is_recurring, recurring_frequency, is_anonymous,
                       cover_fees, fee_amount, ?, 'Recurring payment via Stripe'
                FROM transactions
                WHERE transaction_id = ?
            ");
            
            $newReferenceCode = generateReferenceCode();
            $stmt->execute([$newReferenceCode, $subscription['transaction_id']]);
            $newTransactionId = $pdo->lastInsertId();
            
            // Record Stripe payment details
            $stmt = $pdo->prepare("
                INSERT INTO stripe_payments (
                    transaction_id, stripe_charge_id, stripe_customer_id, stripe_subscription_id
                )
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $newTransactionId,
                $invoice->charge,
                $invoice->customer,
                $subscriptionId
            ]);
            
            // Generate receipt
            $receiptNumber = generateReceiptNumber($newTransactionId);
            $stmt = $pdo->prepare("
                INSERT INTO receipts (transaction_id, receipt_number)
                VALUES (?, ?)
            ");
            $stmt->execute([$newTransactionId, $receiptNumber]);
            
            // Send confirmation email
            if (isset($subscription['email'])) {
                $emailData = [
                    'donor_name' => $subscription['first_name'] . ' ' . $subscription['last_name'],
                    'amount' => formatCurrency($subscription['amount']),
                    'date' => date('F j, Y'),
                    'reference_code' => $newReferenceCode,
                    'payment_method' => 'Credit/Debit Card (Recurring)',
                    'tax_status' => 'is'
                ];
                
                sendEmail($subscription['email'], 'donation_receipt', $emailData);
            }
            
            // Log activity
            logActivity(null, "Recurring payment processed via Stripe", "transaction", $newTransactionId);
        } else {
            error_log("Subscription not found for invoice: " . $invoice->id);
        }
    } catch (Exception $e) {
        error_log("Error handling successful invoice: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle failed invoice payment
 * 
 * @param \Stripe\Invoice $invoice
 * @return void
 */
function handleFailedInvoice($invoice) {
    global $pdo;
    
    try {
        // Get subscription ID
        $subscriptionId = $invoice->subscription;
        
        if (!$subscriptionId) {
            error_log("No subscription ID found in failed invoice: " . $invoice->id);
            return;
        }
        
        // Find existing subscription in our database
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.donor_id, t.reference_code, 
                   d.email, d.first_name, d.last_name
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            JOIN donors d ON t.donor_id = d.donor_id
            WHERE sp.stripe_subscription_id = ?
        ");
        $stmt->execute([$subscriptionId]);
        $subscription = $stmt->fetch();
        
        if ($subscription) {
            // Log the failed payment attempt
            $stmt = $pdo->prepare("
                INSERT INTO transactions (
                    donor_id, amount, currency, payment_method, payment_status,
                    category_id, is_recurring, recurring_frequency, is_anonymous,
                    cover_fees, fee_amount, reference_code, notes
                )
                SELECT donor_id, amount, currency, payment_method, 'failed',
                       category_id, is_recurring, recurring_frequency, is_anonymous,
                       cover_fees, fee_amount, ?, 'Failed recurring payment via Stripe'
                FROM transactions
                WHERE transaction_id = ?
            ");
            
            $newReferenceCode = generateReferenceCode();
            $stmt->execute([$newReferenceCode, $subscription['transaction_id']]);
            $newTransactionId = $pdo->lastInsertId();
            
            // Record Stripe payment details
            $stmt = $pdo->prepare("
                INSERT INTO stripe_payments (
                    transaction_id, stripe_charge_id, stripe_customer_id, stripe_subscription_id
                )
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $newTransactionId,
                $invoice->charge,
                $invoice->customer,
                $subscriptionId
            ]);
            
            // Log activity
            logActivity(null, "Recurring payment failed via Stripe", "transaction", $newTransactionId);
            
            // Send failure notification email
            if (isset($subscription['email'])) {
                // You would need to create a recurring_payment_failed email template
                $emailData = [
                    'donor_name' => $subscription['first_name'] . ' ' . $subscription['last_name'],
                    'reference_code' => $newReferenceCode
                ];
                
                // Assuming you have a recurring_payment_failed email template
                // sendEmail($subscription['email'], 'recurring_payment_failed', $emailData);
            }
        } else {
            error_log("Subscription not found for failed invoice: " . $invoice->id);
        }
    } catch (Exception $e) {
        error_log("Error handling failed invoice: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription creation
 * 
 * @param \Stripe\Subscription $subscription
 * @return void
 */
function handleSubscriptionCreated($subscription) {
    global $pdo;
    
    try {
        // Get customer ID and metadata
        $customerId = $subscription->customer;
        $metadata = $subscription->metadata->toArray();
        
        // Check if we have a reference code in metadata
        if (isset($metadata['reference_code'])) {
            $referenceCode = $metadata['reference_code'];
            
            // Find transaction by reference code
            $stmt = $pdo->prepare("
                SELECT transaction_id
                FROM transactions
                WHERE reference_code = ?
            ");
            $stmt->execute([$referenceCode]);
            $transaction = $stmt->fetch();
            
            if ($transaction) {
                // Update Stripe payment record with subscription ID
                $stmt = $pdo->prepare("
                    UPDATE stripe_payments
                    SET stripe_subscription_id = ?, stripe_customer_id = ?
                    WHERE transaction_id = ?
                ");
                $stmt->execute([$subscription->id, $customerId, $transaction['transaction_id']]);
                
                // Log activity
                logActivity(null, "Subscription created via Stripe", "transaction", $transaction['transaction_id']);
            } else {
                error_log("Transaction not found for subscription creation: " . $subscription->id);
            }
        } else {
            error_log("No reference code in metadata for subscription: " . $subscription->id);
        }
    } catch (Exception $e) {
        error_log("Error handling subscription creation: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription updates
 * 
 * @param \Stripe\Subscription $subscription
 * @return void
 */
function handleSubscriptionUpdated($subscription) {
    global $pdo;
    
    try {
        // Find transaction by subscription ID
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.payment_status
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            WHERE sp.stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription->id]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            // Update transaction based on subscription status
            $status = $subscription->status;
            $paymentStatus = 'pending';
            
            switch ($status) {
                case 'active':
                    $paymentStatus = 'completed';
                    break;
                case 'past_due':
                case 'unpaid':
                    $paymentStatus = 'failed';
                    break;
                case 'canceled':
                    $paymentStatus = 'canceled';
                    break;
            }
            
            // Only update if status has changed
            if ($transaction['payment_status'] !== $paymentStatus) {
                $stmt = $pdo->prepare("
                    UPDATE transactions
                    SET payment_status = ?, updated_at = NOW()
                    WHERE transaction_id = ?
                ");
                $stmt->execute([$paymentStatus, $transaction['transaction_id']]);
                
                // Log activity
                logActivity(null, "Subscription updated via Stripe", "transaction", $transaction['transaction_id']);
            }
        } else {
            error_log("Transaction not found for subscription update: " . $subscription->id);
        }
    } catch (Exception $e) {
        error_log("Error handling subscription update: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Handle subscription cancellation
 * 
 * @param \Stripe\Subscription $subscription
 * @return void
 */
function handleSubscriptionCancelled($subscription) {
    global $pdo;
    
    try {
        // Find transaction by subscription ID
        $stmt = $pdo->prepare("
            SELECT t.transaction_id, t.donor_id, d.email, d.first_name, d.last_name
            FROM transactions t
            JOIN stripe_payments sp ON t.transaction_id = sp.transaction_id
            JOIN donors d ON t.donor_id = d.donor_id
            WHERE sp.stripe_subscription_id = ?
        ");
        $stmt->execute([$subscription->id]);
        $transaction = $stmt->fetch();
        
        if ($transaction) {
            // Update transaction status
            $stmt = $pdo->prepare("
                UPDATE transactions
                SET payment_status = 'canceled', updated_at = NOW(),
                    notes = CONCAT(IFNULL(notes, ''), ' | Subscription canceled')
                WHERE transaction_id = ?
            ");
            $stmt->execute([$transaction['transaction_id']]);
            
            // Log activity
            logActivity(null, "Subscription canceled via Stripe", "transaction", $transaction['transaction_id']);
            
            // Send cancellation notification email
            if (isset($transaction['email'])) {
                // You would need to create a subscription_canceled email template
                $emailData = [
                    'donor_name' => $transaction['first_name'] . ' ' . $transaction['last_name']
                ];
                
                // Assuming you have a subscription_canceled email template
                // sendEmail($transaction['email'], 'subscription_canceled', $emailData);
            }
        } else {
            error_log("Transaction not found for subscription cancellation: " . $subscription->id);
        }
    } catch (Exception $e) {
        error_log("Error handling subscription cancellation: " . $e->getMessage());
        throw $e;
    }
}
?>
