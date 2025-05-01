<?php
/**
 * Test Process Donation
 * Holistic Prosperity Ministry Payment System
 * 
 * This is a simplified version for testing the payment form
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
    header("Location: donate-form.php");
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize form data
        $donorName = isset($_POST['firstName']) && isset($_POST['lastName']) 
            ? sanitizeInput($_POST['firstName'] . ' ' . $_POST['lastName']) 
            : sanitizeInput($_POST['donor_name'] ?? '');
            
        $donorEmail = sanitizeInput($_POST['email'] ?? $_POST['donor_email'] ?? '');
        $amount = floatval($_POST['amount'] ?? $_POST['custom_amount'] ?? 0);
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
        $isRecurring = isset($_POST['is_recurring']) ? 1 : 0;
        $donationType = sanitizeInput($_POST['donation_type'] ?? 'ministry');
        $donationPurpose = sanitizeInput($_POST['designation'] ?? $_POST['donation_purpose'] ?? '');
        
        // Validate required fields
        if (empty($donorName) || empty($donorEmail) || $amount <= 0 || empty($paymentMethod)) {
            $_SESSION['error_message'] = "Please fill in all required fields.";
            header("Location: donate-form.php");
            exit;
        }
        
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
        
        // Simulate transaction ID
        $transactionId = 'TEST_' . time() . '_' . $donationId;
        
        // Update the donation with the transaction ID
        $stmt = $pdo->prepare("
            UPDATE donations 
            SET transaction_id = ? 
            WHERE donation_id = ?
        ");
        $stmt->execute([$transactionId, $donationId]);
        
        // Store donation details in session for confirmation
        $_SESSION['donation'] = [
            'id' => $donationId,
            'name' => $donorName,
            'email' => $donorEmail,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'date' => date('Y-m-d H:i:s')
        ];
        
        // Set success message
        $_SESSION['success_message'] = "Thank you for your donation! Your transaction has been completed successfully.";
        
        // Redirect to confirmation page
        header("Location: donate-form.php#donation-form");
        exit;
        
    } catch (PDOException $e) {
        // Log error
        error_log("Payment Processing Error: " . $e->getMessage());
        
        // Set error message
        $_SESSION['error_message'] = "An error occurred while processing your donation. Please try again.";
        header("Location: donate-form.php");
        exit;
    }
} else {
    // Not a POST request
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}
?>
