<?php
/**
 * Stripe Connection Test Script
 * Holistic Prosperity Ministry Payment System
 * 
 * This script tests the connection to the Stripe API using your configured API keys.
 * Run this script to verify your Stripe integration is working correctly.
 */

// Set strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once 'includes/config.php';

echo "Stripe Connection Test\n";
echo "=====================\n\n";

// Check if Stripe API keys are configured
if (empty(STRIPE_SECRET_KEY)) {
    die("Error: Stripe Secret Key is not configured in your .env file.\n");
}

if (empty(STRIPE_PUBLISHABLE_KEY)) {
    die("Error: Stripe Publishable Key is not configured in your .env file.\n");
}

echo "✓ Stripe API keys are configured\n";

// Test Stripe connection
try {
    // Include Stripe PHP SDK
    require_once 'vendor/stripe/stripe-php/init.php';
    
    echo "✓ Stripe PHP SDK loaded successfully\n";
    
    // Set Stripe API key
    \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
    
    // Get Stripe account information to verify connection
    $account = \Stripe\Account::retrieve();
    
    echo "✓ Successfully connected to Stripe API\n";
    echo "✓ Connected to Stripe account: " . $account->id . "\n";
    
    // Check if we're in test mode
    $isTestMode = strpos(STRIPE_SECRET_KEY, 'sk_test_') === 0;
    echo "✓ Using " . ($isTestMode ? "TEST" : "LIVE") . " mode\n";
    
    // Test creating a payment intent
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => 1000, // $10.00
        'currency' => 'usd',
        'description' => 'Test payment intent',
        'metadata' => [
            'test' => 'true',
            'timestamp' => time()
        ]
    ]);
    
    echo "✓ Successfully created test payment intent: " . $paymentIntent->id . "\n";
    echo "  Client secret: " . substr($paymentIntent->client_secret, 0, 10) . "...\n";
    
    // Clean up the test payment intent
    $paymentIntent->cancel();
    echo "✓ Successfully cancelled test payment intent\n";
    
    echo "\nStripe connection test completed successfully!\n";
    echo "Your payment system is ready to process payments.\n";
    
} catch (\Stripe\Exception\AuthenticationException $e) {
    echo "✗ Authentication Error: Your API key is invalid or expired.\n";
    echo "  Error message: " . $e->getMessage() . "\n";
    
} catch (\Stripe\Exception\ApiConnectionException $e) {
    echo "✗ Connection Error: Could not connect to Stripe API.\n";
    echo "  Error message: " . $e->getMessage() . "\n";
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    echo "✗ API Error: " . $e->getMessage() . "\n";
    
} catch (Exception $e) {
    echo "✗ General Error: " . $e->getMessage() . "\n";
}
?>
