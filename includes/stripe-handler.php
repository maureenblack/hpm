<?php
/**
 * Stripe Payment Handler
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - PCI compliant payment processing
 * - Server-side validation
 * - Secure API key handling
 * - Error logging and monitoring
 */

// Ensure this file is not accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}

// Include configuration
require_once dirname(__DIR__) . '/includes/config.php';

/**
 * Create a payment intent with Stripe
 * 
 * @param float $amount Amount to charge in dollars
 * @param string $currency Currency code (default: USD)
 * @param string $description Description of the payment
 * @param array $metadata Additional metadata for the payment
 * @return array|false Payment intent data or false on failure
 */
function createStripePaymentIntent($amount, $currency = 'usd', $description = '', $metadata = []) {
    // Validate inputs
    if (!is_numeric($amount) || $amount <= 0) {
        error_log("Invalid amount for Stripe payment: $amount");
        return false;
    }
    
    // Convert amount to cents (Stripe requires amounts in smallest currency unit)
    $amountInCents = (int)($amount * 100);
    
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        \Stripe\Stripe::setAppInfo(
            "Holistic Prosperity Ministry",
            "1.0.0",
            "https://holisticprosperityministry.org"
        );
        
        // Create a payment intent
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amountInCents,
            'currency' => $currency,
            'description' => $description,
            'metadata' => $metadata,
            'payment_method_types' => ['card'],
            // Specify receipt email if available in metadata
            'receipt_email' => $metadata['email'] ?? null,
            // Use automatic payment methods
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never'
            ]
        ]);
        
        return [
            'id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'amount' => $amount,
            'currency' => $currency
        ];
        
    } catch (\Stripe\Exception\CardException $e) {
        // Card was declined
        error_log("Stripe Card Error: " . $e->getMessage());
        return ['error' => 'Your card was declined. ' . $e->getMessage()];
        
    } catch (\Stripe\Exception\RateLimitException $e) {
        // Too many requests made to the API too quickly
        error_log("Stripe Rate Limit Error: " . $e->getMessage());
        return ['error' => 'Too many payment attempts. Please try again later.'];
        
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        // Invalid parameters were supplied to Stripe's API
        error_log("Stripe Invalid Request Error: " . $e->getMessage());
        return ['error' => 'Invalid payment information. Please check your details.'];
        
    } catch (\Stripe\Exception\AuthenticationException $e) {
        // Authentication with Stripe's API failed
        error_log("Stripe Authentication Error: " . $e->getMessage());
        return ['error' => 'Payment system configuration error. Please contact support.'];
        
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        // Network communication with Stripe failed
        error_log("Stripe API Connection Error: " . $e->getMessage());
        return ['error' => 'Network error. Please check your connection and try again.'];
        
    } catch (\Stripe\Exception\ApiErrorException $e) {
        // Generic API error
        error_log("Stripe API Error: " . $e->getMessage());
        return ['error' => 'Payment processing error. Please try again or contact support.'];
        
    } catch (Exception $e) {
        // Generic error
        error_log("Stripe General Error: " . $e->getMessage());
        return ['error' => 'An error occurred during payment processing. Please try again.'];
    }
}

/**
 * Retrieve a payment intent from Stripe
 * 
 * @param string $paymentIntentId Payment intent ID
 * @return \Stripe\PaymentIntent|false Payment intent or false on failure
 */
function retrieveStripePaymentIntent($paymentIntentId) {
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Retrieve the payment intent
        return \Stripe\PaymentIntent::retrieve($paymentIntentId);
        
    } catch (Exception $e) {
        error_log("Error retrieving Stripe payment intent: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a Stripe customer
 * 
 * @param string $email Customer email
 * @param string $name Customer name
 * @param array $metadata Additional metadata
 * @return \Stripe\Customer|false Customer object or false on failure
 */
function createStripeCustomer($email, $name, $metadata = []) {
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Create a customer
        $customer = \Stripe\Customer::create([
            'email' => $email,
            'name' => $name,
            'metadata' => $metadata
        ]);
        
        return $customer;
        
    } catch (Exception $e) {
        error_log("Error creating Stripe customer: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a subscription for recurring donations
 * 
 * @param string $customerId Stripe customer ID
 * @param string $paymentMethodId Payment method ID
 * @param float $amount Amount to charge in dollars
 * @param string $interval Billing interval (month, quarter, year)
 * @param string $description Description of the subscription
 * @param array $metadata Additional metadata
 * @return \Stripe\Subscription|false Subscription object or false on failure
 */
function createStripeSubscription($customerId, $paymentMethodId, $amount, $interval = 'month', $description = '', $metadata = []) {
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Convert amount to cents
        $amountInCents = (int)($amount * 100);
        
        // Map interval to Stripe format
        $intervalMapping = [
            'month' => [
                'interval' => 'month',
                'interval_count' => 1
            ],
            'quarter' => [
                'interval' => 'month',
                'interval_count' => 3
            ],
            'year' => [
                'interval' => 'year',
                'interval_count' => 1
            ]
        ];
        
        $intervalData = $intervalMapping[$interval] ?? $intervalMapping['month'];
        
        // Create a price object
        $price = \Stripe\Price::create([
            'unit_amount' => $amountInCents,
            'currency' => 'usd',
            'recurring' => [
                'interval' => $intervalData['interval'],
                'interval_count' => $intervalData['interval_count']
            ],
            'product_data' => [
                'name' => 'Recurring Donation - ' . ucfirst($interval) . 'ly',
                'description' => $description
            ]
        ]);
        
        // Attach payment method to customer
        \Stripe\PaymentMethod::attach(
            $paymentMethodId,
            ['customer' => $customerId]
        );
        
        // Set as default payment method
        \Stripe\Customer::update(
            $customerId,
            ['invoice_settings' => ['default_payment_method' => $paymentMethodId]]
        );
        
        // Create the subscription
        $subscription = \Stripe\Subscription::create([
            'customer' => $customerId,
            'items' => [
                ['price' => $price->id]
            ],
            'metadata' => $metadata,
            'description' => $description,
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'payment_method_types' => ['card'],
                'save_default_payment_method' => 'on_subscription'
            ],
            'expand' => ['latest_invoice.payment_intent']
        ]);
        
        return $subscription;
        
    } catch (Exception $e) {
        error_log("Error creating Stripe subscription: " . $e->getMessage());
        return false;
    }
}

/**
 * Cancel a Stripe subscription
 * 
 * @param string $subscriptionId Subscription ID
 * @return bool Success status
 */
function cancelStripeSubscription($subscriptionId) {
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Cancel the subscription
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);
        $subscription->cancel();
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error canceling Stripe subscription: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a webhook signature validation function
 * 
 * @param string $payload Raw request body
 * @param string $sigHeader Stripe-Signature header
 * @param string $webhookSecret Webhook secret
 * @return \Stripe\Event|false Event object or false on failure
 */
function validateStripeWebhook($payload, $sigHeader, $webhookSecret) {
    try {
        // Check if Stripe API key is set
        if (empty(STRIPE_SECRET_KEY)) {
            throw new Exception("Stripe API key is not configured");
        }
        
        // Include Stripe PHP SDK
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        // Set Stripe API key
        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
        
        // Verify webhook signature
        $event = \Stripe\Webhook::constructEvent(
            $payload,
            $sigHeader,
            $webhookSecret
        );
        
        return $event;
        
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        error_log("Stripe webhook signature verification failed: " . $e->getMessage());
        return false;
        
    } catch (Exception $e) {
        error_log("Error validating Stripe webhook: " . $e->getMessage());
        return false;
    }
}
?>
