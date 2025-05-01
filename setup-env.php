<?php
/**
 * Environment Setup Script
 * Holistic Prosperity Ministry Payment System
 * 
 * This script helps create the .env file with proper security settings.
 * Run this script once during initial setup, then delete it.
 */

// Check if .env file already exists
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    die("Error: .env file already exists. Delete it first if you want to recreate it.\n");
}

// Get user input
echo "Setting up environment variables for Holistic Prosperity Ministry Payment System\n";
echo "=============================================================================\n\n";
echo "This script will create a .env file with your configuration settings.\n";
echo "WARNING: This file will contain sensitive information and should be kept secure.\n\n";

// Database settings
echo "Database Configuration:\n";
$dbHost = readline("Database Host [localhost]: ");
if (empty($dbHost)) $dbHost = 'localhost';

$dbName = readline("Database Name [donations]: ");
if (empty($dbName)) $dbName = 'donations';

$dbUser = readline("Database Username [hpm]: ");
if (empty($dbUser)) $dbUser = 'hpm';

$dbPass = readline("Database Password: ");

// Stripe API keys
echo "\nStripe API Keys:\n";
echo "Get these from your Stripe Dashboard (https://dashboard.stripe.com/apikeys)\n";
$stripePublishable = readline("Stripe Publishable Key: ");
$stripeSecret = readline("Stripe Secret Key: ");

// Email settings
echo "\nEmail Configuration:\n";
$smtpHost = readline("SMTP Host [smtp.gmail.com]: ");
if (empty($smtpHost)) $smtpHost = 'smtp.gmail.com';

$smtpPort = readline("SMTP Port [587]: ");
if (empty($smtpPort)) $smtpPort = '587';

$smtpUser = readline("SMTP Username [hello@holisticprosperityministry.org]: ");
if (empty($smtpUser)) $smtpUser = 'hello@holisticprosperityministry.org';

$smtpPass = readline("SMTP Password: ");

// Security settings
$hashSalt = bin2hex(random_bytes(16)); // Generate a random salt

// Create .env file content
$envContent = "# Holistic Prosperity Ministry - Environment Variables\n";
$envContent .= "# Generated on " . date('Y-m-d H:i:s') . "\n";
$envContent .= "# IMPORTANT: This file contains sensitive information and should never be committed to version control\n\n";

// Group the variables by section
$envContent .= "# Database Configuration\n";
$envContent .= "DB_HOST=$dbHost\n";
$envContent .= "DB_NAME=$dbName\n";
$envContent .= "DB_USER=$dbUser\n";
$envContent .= "DB_PASS=$dbPass\n\n";

$envContent .= "# Stripe API Keys\n";
$envContent .= "STRIPE_PUBLISHABLE_KEY=$stripePublishable\n";
$envContent .= "STRIPE_SECRET_KEY=$stripeSecret\n";

// Add Stripe webhook secret
$stripeWebhookSecret = readline("Stripe Webhook Secret: ");
$envContent .= "STRIPE_WEBHOOK_SECRET=$stripeWebhookSecret\n\n";

$envContent .= "# Email Configuration\n";
$envContent .= "SMTP_HOST=$smtpHost\n";
$envContent .= "SMTP_PORT=$smtpPort\n";
$envContent .= "SMTP_USERNAME=$smtpUser\n";
$envContent .= "SMTP_PASSWORD=$smtpPass\n\n";

$envContent .= "# Mobile Money Configuration\n";
$envContent .= "MOMO_RECIPIENT_NAME=Kort Godlove Fai\n";
$envContent .= "MOMO_RECIPIENT_NUMBER=652444097\n";
$envContent .= "MOMO_WHATSAPP_NUMBER=+14697031453\n";
$envContent .= "USD_TO_FCFA_RATE=650\n\n";

$envContent .= "# Security Settings\n";
$envContent .= "HASH_SALT=$hashSalt\n";

// Write to file
if (file_put_contents('.env', $envContent)) {
    // Set permissions to 0600 (readable/writable only by owner)
    chmod('.env', 0600);
    echo "\nSuccessfully created .env file with secure permissions.\n";
    echo "Please edit the file to set your actual values.\n";
    echo "\nIMPORTANT: For production, replace the Stripe test keys with live keys.\n";
} else {
    echo "Failed to create .env file.\n";
    exit(1);
}
?>
