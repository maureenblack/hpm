<?php
/**
 * Process Donation
 * Holistic Prosperity Ministry Payment System
 * 
 * This script processes donation form submissions
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Security validation failed. Please try again.";
    header("Location: donate-form.php");
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log POST data
        error_log("Donation form submitted: " . print_r($_POST, true));
        
        // Check if this is an anonymous donation
        $isAnonymous = isset($_POST['donate_anonymously']) && $_POST['donate_anonymously'] == '1';
        
        // Get and sanitize form data
        if ($isAnonymous) {
            // For anonymous donations, use 'Anonymous Donor' as the name
            $donorName = 'Anonymous Donor';
            $donorEmail = ''; // No email for anonymous donations
        } else {
            // Regular donation with personal information
            $donorName = isset($_POST['firstName']) && isset($_POST['lastName']) 
                ? sanitizeInput($_POST['firstName'] . ' ' . $_POST['lastName']) 
                : sanitizeInput($_POST['donor_name'] ?? '');
                
            $donorEmail = sanitizeInput($_POST['email'] ?? $_POST['donor_email'] ?? '');
        }
        
        $amount = floatval($_POST['amount'] ?? $_POST['custom_amount'] ?? 0);
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
        $frequency = sanitizeInput($_POST['frequency'] ?? 'one-time');
        $isRecurring = ($frequency !== 'one-time') ? 1 : 0;
        $donationType = sanitizeInput($_POST['donation_type'] ?? 'ministry');
        $donationPurpose = sanitizeInput($_POST['designation'] ?? $_POST['donation_purpose'] ?? '');
        
        // Validate required fields with better error messages
        $errors = [];
        if (!$isAnonymous && empty($donorName)) $errors[] = "Donor name is required unless donating anonymously";
        if (!$isAnonymous && empty($donorEmail)) $errors[] = "Email address is required unless donating anonymously";
        if ($amount <= 0) $errors[] = "Valid donation amount is required";
        if (empty($paymentMethod)) $errors[] = "Payment method is required";
        
        // If validation errors, redirect back with error message
        if (!empty($errors)) {
            $_SESSION['error_message'] = "Please correct the following errors: " . implode(", ", $errors);
            header("Location: donate-form.php");
            exit;
        }
        
        // Generate a unique transaction ID
        $transactionId = 'HPM-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 10));
        
        // Process based on payment method
        switch ($paymentMethod) {
            case 'mobile_money':
                // Store payment data in session for mobile money instructions
                $_SESSION['payment_data'] = [
                    'transaction_id' => $transactionId,
                    'donor_name' => $donorName,
                    'donor_email' => $donorEmail,
                    'amount' => $amount,
                    'amount_fcfa' => $amount * 650, // Convert to FCFA
                    'payment_method' => $paymentMethod,
                    'donation_purpose' => $donationPurpose,
                    'is_recurring' => $isRecurring,
                    'frequency' => $frequency,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                // Redirect to mobile money instructions
                header("Location: mobile-money-instructions.php");
                exit;
                
            case 'credit_card':
                // For Stripe payments, store transaction info and redirect to confirmation
                // In a real implementation, this would create a payment intent
                
                // Store donation data in session for confirmation
                $_SESSION['donation'] = [
                    'transaction_id' => $transactionId,
                    'donor_name' => $donorName,
                    'donor_email' => $donorEmail,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'donation_purpose' => $donationPurpose,
                    'is_recurring' => $isRecurring,
                    'frequency' => $frequency,
                    'date' => date('Y-m-d H:i:s'),
                    'payment_status' => 'completed'
                ];
                
                // Redirect to thank you page
                header("Location: thank-you.php");
                exit;
                
            default:
                // For other payment methods, store transaction info and redirect to confirmation
                
                // Store donation data in session for confirmation
                $_SESSION['donation'] = [
                    'transaction_id' => $transactionId,
                    'donor_name' => $donorName,
                    'donor_email' => $donorEmail,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'donation_purpose' => $donationPurpose,
                    'is_recurring' => $isRecurring,
                    'frequency' => $frequency,
                    'date' => date('Y-m-d H:i:s'),
                    'payment_status' => 'pending'
                ];
                
                // Redirect to thank you page
                header("Location: thank-you.php");
                exit;
        }
        
    } catch (Exception $e) {
        // Log error
        error_log("Payment Processing Error: " . $e->getMessage());
        
        // Set error message
        $_SESSION['error_message'] = "An error occurred while processing your donation. Please try again or contact support.";
        header("Location: donate-form.php");
        exit;
    }
} else {
    // Not a POST request
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}
?>
