<?php
/**
 * Process Donation
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - Strict input validation and sanitization
 * - CSRF protection
 * - Request method validation
 * - Secure session handling
 * - PCI compliance considerations
 */

// Ensure this file is not accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    // Log potential direct access attempt
    error_log("Direct access attempt to process-donation.php");
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}

// Initialize session with secure parameters
session_start([
    'cookie_httponly' => 1,
    'cookie_secure' => 1,
    'cookie_samesite' => 'Strict',
    'use_strict_mode' => 1
]);

// Regenerate session ID to prevent session fixation
if (!isset($_SESSION['last_regeneration']) || 
    (time() - $_SESSION['last_regeneration']) > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Include configuration and functions
require_once 'includes/config.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    // Log potential CSRF attack with additional information
    error_log("CSRF token validation failed. IP: " . $_SERVER['REMOTE_ADDR'] . 
              ", User Agent: " . $_SERVER['HTTP_USER_AGENT']);
    
    // Redirect with error
    $_SESSION['error_message'] = "Security validation failed. Please try again.";
    header("Location: donate-form.php");
    exit;
}

// Process only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: donate-form.php");
    exit;
}

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

try {
    // Get and sanitize form data
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    // Get donation amount
    if (isset($_POST['donation_amount_preset']) && $_POST['donation_amount_preset'] === 'custom') {
        $amount = sanitizeInput($_POST['custom_amount'], 'float');
    } else {
        $amount = sanitizeInput($_POST['donation_amount'], 'float');
    }
    
    // Validate donation amount
    if (!$amount || $amount < MIN_DONATION_AMOUNT) {
        throw new Exception("Invalid donation amount. Minimum donation is " . formatCurrency(MIN_DONATION_AMOUNT));
    }
    
    // Get other form data
    $category = sanitizeInput($_POST['donation_category'], 'int');
    $frequency = sanitizeInput($_POST['donation_frequency']);
    $isRecurring = ($frequency !== 'one-time');
    $coverFees = isset($_POST['cover_fees']) ? true : false;
    
    // Calculate processing fee if covered by donor
    $feeAmount = 0;
    if ($coverFees) {
        $feeAmount = calculateProcessingFee($amount);
        $totalAmount = $amount + $feeAmount;
    } else {
        $totalAmount = $amount;
    }
    
    // Get donor information
    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email'], 'email');
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $state = sanitizeInput($_POST['state']);
    $postalCode = sanitizeInput($_POST['postal_code']);
    $country = sanitizeInput($_POST['country']);
    $isAnonymous = isset($_POST['is_anonymous']) ? true : false;
    
    // Get tribute information
    $isTribute = isset($_POST['is_tribute']) ? true : false;
    $tributeType = $isTribute ? sanitizeInput($_POST['tribute_type']) : null;
    $tributeName = $isTribute ? sanitizeInput($_POST['tribute_name']) : null;
    
    // Generate reference code if not provided
    $referenceCode = isset($_POST['reference_code']) ? sanitizeInput($_POST['reference_code']) : generateReferenceCode();
    
    // Validate required fields
    if (!$firstName || !$lastName || !$email) {
        throw new Exception("Please fill in all required fields.");
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Insert or update donor
    $stmt = $pdo->prepare("
        SELECT donor_id FROM donors WHERE email = ?
    ");
    $stmt->execute([$email]);
    $donor = $stmt->fetch();
    
    if ($donor) {
        // Update existing donor
        $donorId = $donor['donor_id'];
        $stmt = $pdo->prepare("
            UPDATE donors 
            SET first_name = ?, last_name = ?, phone = ?, address = ?, 
                city = ?, state = ?, postal_code = ?, country = ?, updated_at = NOW()
            WHERE donor_id = ?
        ");
        $stmt->execute([
            $firstName, $lastName, $phone, $address, 
            $city, $state, $postalCode, $country, $donorId
        ]);
    } else {
        // Insert new donor
        $stmt = $pdo->prepare("
            INSERT INTO donors (first_name, last_name, email, phone, address, city, state, postal_code, country)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $firstName, $lastName, $email, $phone, $address, $city, $state, $postalCode, $country
        ]);
        $donorId = $pdo->lastInsertId();
    }
    
    // Prepare recurring frequency
    $recurringFrequency = null;
    if ($isRecurring) {
        $recurringFrequency = $frequency;
    }
    
    // Insert transaction
    $stmt = $pdo->prepare("
        INSERT INTO transactions (
            donor_id, amount, currency, payment_method, payment_status, 
            category_id, is_recurring, recurring_frequency, is_anonymous, 
            cover_fees, fee_amount, reference_code, in_honor_of, notes
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");
    
    $honorOf = null;
    if ($isTribute) {
        $honorOf = ($tributeType === 'honor' ? 'In Honor of: ' : 'In Memory of: ') . $tributeName;
    }
    
    $paymentStatus = ($paymentMethod === 'credit_card') ? 'pending' : 'pending';
    $notes = "Donation via website";
    
    $stmt->execute([
        $donorId, $totalAmount, DEFAULT_CURRENCY, $paymentMethod, $paymentStatus,
        $category, $isRecurring, $recurringFrequency, $isAnonymous,
        $coverFees, $feeAmount, $referenceCode, $honorOf, $notes
    ]);
    
    $transactionId = $pdo->lastInsertId();
    
    // Process based on payment method
    switch ($paymentMethod) {
        case 'credit_card':
            // Get Stripe payment intent ID
            $paymentIntentId = sanitizeInput($_POST['payment_intent_id']);
            
            if (empty($paymentIntentId)) {
                throw new Exception("Payment processing failed. Please try again.");
            }
            
            // Include Stripe PHP library
            require_once ROOT_PATH . '/vendor/autoload.php';
            
            // Set Stripe API key
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            
            // Retrieve the payment intent
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);
            
            // Check if payment was successful
            if ($paymentIntent->status === 'succeeded') {
                // Update transaction status
                $stmt = $pdo->prepare("
                    UPDATE transactions 
                    SET payment_status = 'completed' 
                    WHERE transaction_id = ?
                ");
                $stmt->execute([$transactionId]);
                
                // Store Stripe payment details
                $stmt = $pdo->prepare("
                    INSERT INTO stripe_payments (
                        transaction_id, stripe_charge_id, card_last_four, card_brand
                    ) VALUES (?, ?, ?, ?)
                ");
                
                $charge = $paymentIntent->charges->data[0];
                $cardLastFour = $charge->payment_method_details->card->last4;
                $cardBrand = $charge->payment_method_details->card->brand;
                
                $stmt->execute([
                    $transactionId, $charge->id, $cardLastFour, $cardBrand
                ]);
                
                // Generate receipt
                $receiptNumber = generateReceiptNumber($transactionId);
                $stmt = $pdo->prepare("
                    INSERT INTO receipts (transaction_id, receipt_number)
                    VALUES (?, ?)
                ");
                $stmt->execute([$transactionId, $receiptNumber]);
                
                // Send confirmation email
                $emailData = [
                    'donor_name' => $firstName . ' ' . $lastName,
                    'amount' => formatCurrency($totalAmount),
                    'date' => date('F j, Y'),
                    'reference_code' => $referenceCode,
                    'payment_method' => getPaymentMethodName($paymentMethod),
                    'tax_status' => 'is'
                ];
                
                sendEmail($email, 'donation_receipt', $emailData);
                
                $response['success'] = true;
                $response['message'] = "Thank you for your donation!";
                $response['redirect'] = "donation-confirmation.php?ref=" . $referenceCode;
            } else {
                throw new Exception("Payment processing failed: " . $paymentIntent->status);
            }
            break;
            
        case 'mobile_money':
            // Get mobile money details
            $mobileNumber = sanitizeInput($_POST['mobile_number']);
            $mobileProvider = sanitizeInput($_POST['mobile_provider']);
            $mobileReference = sanitizeInput($_POST['transaction_id']);
            
            // Insert mobile money payment details
            $stmt = $pdo->prepare("
                INSERT INTO mobile_money_payments (
                    transaction_id, phone_number, provider, mobile_reference
                ) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $transactionId, $mobileNumber, $mobileProvider, $mobileReference
            ]);
            
            // Send mobile money instructions email
            $emailData = [
                'donor_name' => $firstName . ' ' . $lastName,
                'amount' => formatCurrency($totalAmount),
                'reference_code' => $referenceCode
            ];
            
            sendEmail($email, 'mobile_money_instructions', $emailData);
            
            $response['success'] = true;
            $response['message'] = "Thank you for your donation! Please follow the mobile money payment instructions sent to your email.";
            $response['redirect'] = "donation-confirmation.php?ref=" . $referenceCode . "&method=mobile";
            break;
            
        case 'paypal':
            // PayPal is handled via redirect, so we just set up the transaction
            $response['success'] = true;
            $response['message'] = "You will be redirected to PayPal to complete your donation.";
            $response['redirect'] = "paypal-redirect.php?ref=" . $referenceCode;
            break;
            
        case 'zelle':
            // Get Zelle details
            $zelleEmail = sanitizeInput($_POST['zelle_email']);
            
            // Update transaction notes
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET notes = CONCAT(notes, ' | Zelle Email: ', ?) 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$zelleEmail, $transactionId]);
            
            $response['success'] = true;
            $response['message'] = "Thank you for your donation! Please follow the Zelle payment instructions.";
            $response['redirect'] = "donation-confirmation.php?ref=" . $referenceCode . "&method=zelle";
            break;
            
        case 'cashapp':
            // Get CashApp details
            $cashappName = sanitizeInput($_POST['cashapp_name']);
            
            // Update transaction notes
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET notes = CONCAT(notes, ' | CashApp Name: ', ?) 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$cashappName, $transactionId]);
            
            $response['success'] = true;
            $response['message'] = "Thank you for your donation! Please follow the CashApp payment instructions.";
            $response['redirect'] = "donation-confirmation.php?ref=" . $referenceCode . "&method=cashapp";
            break;
            
        case 'bank_transfer':
            // Get bank transfer details
            $bankName = sanitizeInput($_POST['bank_name']);
            $bankReference = sanitizeInput($_POST['bank_reference']);
            
            // Update transaction notes
            $stmt = $pdo->prepare("
                UPDATE transactions 
                SET notes = CONCAT(notes, ' | Bank: ', ?, ' | Bank Reference: ', ?) 
                WHERE transaction_id = ?
            ");
            $stmt->execute([$bankName, $bankReference, $transactionId]);
            
            $response['success'] = true;
            $response['message'] = "Thank you for your donation! Please follow the bank transfer instructions.";
            $response['redirect'] = "donation-confirmation.php?ref=" . $referenceCode . "&method=bank";
            break;
            
        default:
            throw new Exception("Invalid payment method selected.");
    }
    
    // Log activity
    logActivity(null, "Donation submitted", "transaction", $transactionId);
    
    // Commit transaction
    $pdo->commit();
    
} catch (Exception $e) {
    // Rollback transaction
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error
    error_log("Donation Processing Error: " . $e->getMessage());
    
    // Set error response
    $response['success'] = false;
    $response['message'] = "Error: " . $e->getMessage();
}

// If this is an AJAX request
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Store message in session
    if ($response['success']) {
        $_SESSION['success_message'] = $response['message'];
    } else {
        $_SESSION['error_message'] = $response['message'];
    }
    
    // Redirect
    if (!empty($response['redirect'])) {
        header("Location: " . $response['redirect']);
    } else {
        header("Location: donate-form.php");
    }
    exit;
}
?>
