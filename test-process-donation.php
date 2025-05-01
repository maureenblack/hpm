<?php
/**
 * Test Process Donation
 * Holistic Prosperity Ministry Payment System
 * 
 * This is a simplified version for testing the payment form with improved payment flows
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    $_SESSION['error_message'] = "Security validation failed. Please try again.";
    header("Location: donate-form.php#donation-form");
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log POST data
        error_log("POST data: " . print_r($_POST, true));
        
        // Get and sanitize form data
        $donorName = isset($_POST['firstName']) && isset($_POST['lastName']) 
            ? sanitizeInput($_POST['firstName'] . ' ' . $_POST['lastName']) 
            : sanitizeInput($_POST['donor_name'] ?? '');
            
        $donorEmail = sanitizeInput($_POST['email'] ?? $_POST['donor_email'] ?? '');
        $amount = floatval($_POST['amount'] ?? $_POST['custom_amount'] ?? 0);
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
        $frequency = sanitizeInput($_POST['frequency'] ?? 'one-time');
        $isRecurring = ($frequency !== 'one-time') ? 1 : 0;
        $donationType = sanitizeInput($_POST['donation_type'] ?? 'ministry');
        $donationPurpose = sanitizeInput($_POST['designation'] ?? $_POST['donation_purpose'] ?? '');
        
        // Debug: Log processed data
        error_log("Processed data: Name=$donorName, Email=$donorEmail, Amount=$amount, Method=$paymentMethod, Frequency=$frequency");
        
        // Special handling for test data
        $isTestMode = (strpos(strtolower($_POST['comments'] ?? ''), 'test donation') !== false);
        
        // Validate required fields with better error messages
        $errors = [];
        if (empty($donorName)) $errors[] = "Donor name is required";
        if (empty($donorEmail)) $errors[] = "Email address is required";
        if ($amount <= 0 && !$isTestMode) $errors[] = "Valid donation amount is required";
        if (empty($paymentMethod)) $errors[] = "Payment method is required";
        
        // For test mode, set a minimum amount if none provided
        if ($amount <= 0 && $isTestMode) {
            $amount = 50.00; // Default test amount
            error_log("Test mode: Setting default amount to $50");
        }
        
        if (!empty($errors)) {
            $_SESSION['error_message'] = "Please fix the following issues: " . implode(", ", $errors);
            error_log("Validation errors: " . implode(", ", $errors));
            header("Location: donate-form.php#donation-form");
            exit;
        }
        
        // Validate email format
        if (!filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Please enter a valid email address.";
            header("Location: donate-form.php#donation-form");
            exit;
        }
        
        // Different handling based on payment method
        switch ($paymentMethod) {
            case 'mobile_money':
                // Extract mobile number from comments if present
                $mobileNumber = '';
                $comments = $_POST['comments'] ?? '';
                
                // Try to extract mobile number from comments
                if (preg_match('/\b(6[0-9]{8})\b/', $comments, $matches)) {
                    $mobileNumber = $matches[1];
                    error_log("Mobile number extracted from comments: $mobileNumber");
                }
                
                // Store payment data in session for mobile money instructions
                $_SESSION['payment_data'] = [
                    'donor_name' => $donorName,
                    'donor_email' => $donorEmail,
                    'amount' => $amount,
                    'is_recurring' => $isRecurring,
                    'donation_type' => $donationType,
                    'donation_purpose' => $donationPurpose,
                    'mobile_number' => $mobileNumber,
                    'reference_code' => 'MM' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8))
                ];
                
                error_log("Mobile money payment data prepared, redirecting to instructions page");
                
                // Redirect to mobile money instructions page
                header("Location: mobile-money-instructions.php");
                exit;
                
            case 'credit_card':
                // For credit card payments, process with Stripe
                // In this test version, we'll simulate a successful payment
                
                // Insert donation record
                $stmt = $pdo->prepare("
                    INSERT INTO donations 
                    (donor_name, donor_email, amount, payment_method, payment_status, 
                     is_recurring, donation_type, donation_purpose, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $donorName,
                    $donorEmail,
                    $amount,
                    $paymentMethod,
                    'completed', // Simulate successful payment
                    $isRecurring,
                    $donationType,
                    $donationPurpose,
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
                
                // Get the donation ID
                $donationId = $pdo->lastInsertId();
                
                // Generate transaction ID
                $transactionId = 'CC_' . time() . '_' . $donationId;
                
                // Update the donation with the transaction ID
                $stmt = $pdo->prepare("
                    UPDATE donations 
                    SET transaction_id = ? 
                    WHERE donation_id = ?
                ");
                $stmt->execute([$transactionId, $donationId]);
                
                // Store donation details in session for thank you page
                $_SESSION['donation'] = [
                    'id' => $donationId,
                    'name' => $donorName,
                    'email' => $donorEmail,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionId,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                // Redirect to thank you page
                header("Location: thank-you.php");
                exit;
                
            case 'paypal':
            case 'zelle':
            case 'cashapp':
            case 'bank_transfer':
                // For other payment methods, record as pending
                $paymentStatus = 'pending';
                
                // Insert donation record
                $stmt = $pdo->prepare("
                    INSERT INTO donations 
                    (donor_name, donor_email, amount, payment_method, payment_status, 
                     is_recurring, donation_type, donation_purpose, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $donorName,
                    $donorEmail,
                    $amount,
                    $paymentMethod,
                    $paymentStatus,
                    $isRecurring,
                    $donationType,
                    $donationPurpose,
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]);
                
                // Get the donation ID
                $donationId = $pdo->lastInsertId();
                
                // Generate reference code based on payment method
                $methodPrefix = strtoupper(substr($paymentMethod, 0, 2));
                $transactionId = $methodPrefix . '_' . time() . '_' . $donationId;
                
                // Update the donation with the transaction ID
                $stmt = $pdo->prepare("
                    UPDATE donations 
                    SET transaction_id = ? 
                    WHERE donation_id = ?
                ");
                $stmt->execute([$transactionId, $donationId]);
                
                // Store donation details in session for thank you page
                $_SESSION['donation'] = [
                    'id' => $donationId,
                    'name' => $donorName,
                    'email' => $donorEmail,
                    'amount' => $amount,
                    'payment_method' => $paymentMethod,
                    'transaction_id' => $transactionId,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                // Redirect to thank you page
                header("Location: thank-you.php");
                exit;
                
            default:
                // Invalid payment method
                $_SESSION['error_message'] = "Please select a valid payment method.";
                header("Location: donate-form.php#donation-form");
                exit;
        }
        
    } catch (PDOException $e) {
        // Log error
        error_log("Payment Processing Error: " . $e->getMessage());
        
        // Set error message
        $_SESSION['error_message'] = "An error occurred while processing your donation. Please try again.";
        header("Location: donate-form.php#donation-form");
        exit;
    }
} else {
    // Not a POST request
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}
?>
