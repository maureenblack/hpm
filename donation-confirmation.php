<?php
/**
 * Donation Confirmation
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';

// Get reference code from URL
$referenceCode = isset($_GET['ref']) ? sanitizeInput($_GET['ref']) : null;
$paymentMethod = isset($_GET['method']) ? sanitizeInput($_GET['method']) : null;

// If no reference code, redirect to donation page
if (!$referenceCode) {
    header("Location: donate-form.php");
    exit;
}

// Get transaction details
try {
    $stmt = $pdo->prepare("
        SELECT t.*, d.first_name, d.last_name, d.email, c.category_name
        FROM transactions t
        JOIN donors d ON t.donor_id = d.donor_id
        JOIN donation_categories c ON t.category_id = c.category_id
        WHERE t.reference_code = ?
    ");
    $stmt->execute([$referenceCode]);
    $transaction = $stmt->fetch();
    
    // If transaction not found, redirect to donation page
    if (!$transaction) {
        $_SESSION['error_message'] = "Transaction not found. Please try again.";
        header("Location: donate-form.php");
        exit;
    }
    
    // Get receipt if available
    $stmt = $pdo->prepare("
        SELECT receipt_number, receipt_date
        FROM receipts
        WHERE transaction_id = ?
    ");
    $stmt->execute([$transaction['transaction_id']]);
    $receipt = $stmt->fetch();
    
} catch (PDOException $e) {
    // Log error
    error_log("Confirmation Page Error: " . $e->getMessage());
    
    // Set error message
    $_SESSION['error_message'] = "An error occurred. Please contact support.";
    
    // Redirect to donation page
    header("Location: donate-form.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Confirmation | <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/donate.css">
    <link rel="stylesheet" href="css/payment.css">
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Confirmation Section -->
    <section class="confirmation-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="confirmation-wrapper text-center">
                        <?php if ($transaction['payment_status'] === 'completed'): ?>
                            <div class="confirmation-icon success mb-4">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <h1 class="mb-3">Thank You for Your Donation!</h1>
                            <p class="lead mb-4">Your donation has been successfully processed.</p>
                        <?php else: ?>
                            <div class="confirmation-icon pending mb-4">
                                <i class="fas fa-clock"></i>
                            </div>
                            <h1 class="mb-3">Thank You for Your Donation!</h1>
                            <p class="lead mb-4">Your donation is being processed.</p>
                        <?php endif; ?>
                        
                        <div class="donation-details card mb-4">
                            <div class="card-body">
                                <h5 class="card-title">Donation Details</h5>
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <th>Reference Code:</th>
                                            <td><?php echo $transaction['reference_code']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Amount:</th>
                                            <td><?php echo formatCurrency($transaction['amount']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Date:</th>
                                            <td><?php echo date('F j, Y', strtotime($transaction['transaction_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Designation:</th>
                                            <td><?php echo $transaction['category_name']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Payment Method:</th>
                                            <td><?php echo getPaymentMethodName($transaction['payment_method']); ?></td>
                                        </tr>
                                        <?php if ($transaction['is_recurring']): ?>
                                            <tr>
                                                <th>Frequency:</th>
                                                <td><?php echo getRecurringFrequencyName($transaction['recurring_frequency']); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <?php if ($receipt): ?>
                                            <tr>
                                                <th>Receipt Number:</th>
                                                <td><?php echo $receipt['receipt_number']; ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <?php if ($transaction['payment_method'] === 'mobile_money' && $transaction['payment_status'] !== 'completed'): ?>
                            <div class="payment-instructions card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Mobile Money Payment Instructions</h5>
                                    <p>Please send your donation to the following Mobile Money account:</p>
                                    <ul class="list-unstyled">
                                        <li><strong>Name:</strong> <?php echo MOBILE_MONEY_NAME; ?></li>
                                        <li><strong>Number:</strong> <?php echo MOBILE_MONEY_NUMBER; ?></li>
                                        <li><strong>Reference:</strong> <?php echo $transaction['reference_code']; ?></li>
                                    </ul>
                                    <p class="mt-3">After sending the payment, we will verify and update your donation status.</p>
                                </div>
                            </div>
                        <?php elseif ($transaction['payment_method'] === 'zelle' && $transaction['payment_status'] !== 'completed'): ?>
                            <div class="payment-instructions card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Zelle Payment Instructions</h5>
                                    <p>Please send your donation to the following Zelle account:</p>
                                    <ul class="list-unstyled">
                                        <li><strong>Email:</strong> <?php echo ADMIN_EMAIL; ?></li>
                                        <li><strong>Name:</strong> Holistic Prosperity Ministry</li>
                                        <li><strong>Reference:</strong> <?php echo $transaction['reference_code']; ?></li>
                                    </ul>
                                    <p class="mt-3">After sending the payment, we will verify and update your donation status.</p>
                                </div>
                            </div>
                        <?php elseif ($transaction['payment_method'] === 'cashapp' && $transaction['payment_status'] !== 'completed'): ?>
                            <div class="payment-instructions card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">CashApp Payment Instructions</h5>
                                    <p>Please send your donation to the following CashApp account:</p>
                                    <ul class="list-unstyled">
                                        <li><strong>$Cashtag:</strong> $HolisticPM</li>
                                        <li><strong>Reference:</strong> <?php echo $transaction['reference_code']; ?></li>
                                    </ul>
                                    <p class="mt-3">After sending the payment, we will verify and update your donation status.</p>
                                </div>
                            </div>
                        <?php elseif ($transaction['payment_method'] === 'bank_transfer' && $transaction['payment_status'] !== 'completed'): ?>
                            <div class="payment-instructions card mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">Bank Transfer Instructions</h5>
                                    <p>Please transfer your donation to the following bank account:</p>
                                    <ul class="list-unstyled">
                                        <li><strong>Bank Name:</strong> First National Bank</li>
                                        <li><strong>Account Name:</strong> Holistic Prosperity Ministry</li>
                                        <li><strong>Account Number:</strong> 1234567890</li>
                                        <li><strong>Routing Number:</strong> 987654321</li>
                                        <li><strong>Reference:</strong> <?php echo $transaction['reference_code']; ?></li>
                                    </ul>
                                    <p class="mt-3">After sending the payment, we will verify and update your donation status.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="confirmation-actions">
                            <?php if ($receipt): ?>
                                <a href="generate-receipt.php?ref=<?php echo $transaction['reference_code']; ?>" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-file-invoice"></i> Download Receipt
                                </a>
                            <?php endif; ?>
                            <a href="index.html" class="btn btn-primary mb-2">
                                <i class="fas fa-home"></i> Return to Homepage
                            </a>
                            <a href="donate-form.php" class="btn btn-outline-secondary mb-2">
                                <i class="fas fa-heart"></i> Make Another Donation
                            </a>
                        </div>
                        
                        <div class="mt-5">
                            <h5>What Happens Next?</h5>
                            <p>You will receive a confirmation email with the details of your donation. If you have any questions, please contact us at <a href="mailto:<?php echo ADMIN_EMAIL; ?>"><?php echo ADMIN_EMAIL; ?></a>.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/main.js"></script>

    <script>
    // Mobile dropdown fix
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.navbar-nav .dropdown-toggle');
        
        // For mobile view
        if (window.innerWidth < 992) {
            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        e.preventDefault();
                        e.stopPropagation();
                        const dropdownMenu = this.nextElementSibling;
                        if (dropdownMenu) {
                            dropdownMenu.classList.toggle('show');
                        }
                    }
                });
            });
        }
        
        // Handle resize events
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                    menu.classList.remove('show');
                });
            }
        });
    });
    </script>
</body>
</html>
