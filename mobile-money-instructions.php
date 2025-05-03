<?php
/**
 * Mobile Money Instructions
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

// Check if payment data exists in session
if (!isset($_SESSION['payment_data'])) {
    // Redirect to donation page if no payment data
    header("Location: donate-form.php");
    exit;
}

// Get payment data from session
$paymentData = $_SESSION['payment_data'];

// Set recipient information
$recipientName = "Kort Godlove Fai";
$recipientNumber = "652444097";

// Convert USD to FCFA (1 USD = 650 FCFA)
$amountUSD = $paymentData['amount'];
$amountFCFA = round($amountUSD * 650);

// Generate a unique reference code if not already generated
if (!isset($paymentData['reference_code'])) {
    $paymentData['reference_code'] = 'MM' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    $_SESSION['payment_data'] = $paymentData;
    
    // Store the pending payment in the database
    try {
        // Insert donation record with pending status
        $stmt = $pdo->prepare("
            INSERT INTO donations 
            (donor_name, donor_email, amount, payment_method, payment_status, 
             is_recurring, donation_type, donation_purpose, transaction_id, ip_address)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $paymentData['donor_name'],
            $paymentData['donor_email'],
            $amountUSD, // Store original USD amount
            'mobile_money',
            'pending',
            $paymentData['is_recurring'] ? 1 : 0,
            $paymentData['donation_type'],
            $paymentData['donation_purpose'],
            $paymentData['reference_code'],
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);
        
        // Get the donation ID
        $donationId = $pdo->lastInsertId();
        $paymentData['donation_id'] = $donationId;
        $paymentData['amount_fcfa'] = $amountFCFA;
        $_SESSION['payment_data'] = $paymentData;
        
    } catch (PDOException $e) {
        // Log error
        error_log("Mobile Money Payment Error: " . $e->getMessage());
    }
} else {
    // Ensure FCFA amount is set
    $paymentData['amount_fcfa'] = $amountFCFA;
    $_SESSION['payment_data'] = $paymentData;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    // Update payment data with mobile number
    if (isset($_POST['mobile_number'])) {
        $paymentData['mobile_number'] = sanitizeInput($_POST['mobile_number']);
        $_SESSION['payment_data'] = $paymentData;
    }
    
    // Check if name verification was confirmed
    if (!isset($_POST['name_verified']) || $_POST['name_verified'] != '1') {
        $_SESSION['error_message'] = "Please confirm that you've verified the recipient's name before proceeding.";
        // Stay on the same page
        header("Location: mobile-money-instructions.php");
        exit;
    }
    
    // Create donation data for thank you page
    $_SESSION['donation'] = [
        'id' => $paymentData['donation_id'] ?? 0,
        'name' => $paymentData['donor_name'],
        'email' => $paymentData['donor_email'],
        'amount' => $paymentData['amount'],
        'amount_fcfa' => $paymentData['amount_fcfa'],
        'payment_method' => 'mobile_money',
        'transaction_id' => $paymentData['reference_code'],
        'recipient_name' => $recipientName,
        'recipient_number' => $recipientNumber,
        'date' => date('Y-m-d H:i:s')
    ];
    
    // Redirect to thank you page
    header("Location: thank-you.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Money Instructions - Holistic Prosperity Ministry</title>
    <link rel="icon" href="images/hpm-favicon.svg" type="image/svg+xml">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="css/donate.css">
    <style>
        .instructions-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .steps-container {
            margin: 30px 0;
        }
        
        .step-item {
            display: flex;
            margin-bottom: 20px;
            align-items: flex-start;
        }
        
        .step-number {
            background-color: #4B0082;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .step-content {
            flex-grow: 1;
        }
        
        .step-content h4 {
            margin-bottom: 5px;
        }
        
        .reference-code {
            font-family: monospace;
            font-size: 18px;
            background-color: #f0f0f0;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px dashed #ccc;
            display: inline-block;
            margin: 5px 0;
        }
        
        .payment-summary {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="images/hpm-logo.svg" alt="Holistic Prosperity Ministry Logo" height="70">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.html">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="ministries.html">Ministries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.html">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donate-form.php">Donate</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile Money Instructions Section -->
    <section class="instructions-section py-5 mt-5">
        <div class="container">
            <div class="instructions-container">
                <h1 class="text-center mb-4">Mobile Money Payment Instructions</h1>
                
                <div class="payment-summary">
                    <h3 class="mb-3">Payment Summary</h3>
                    <table class="table table-borderless">
                        <tr>
                            <th>Donor Name:</th>
                            <td><?php echo htmlspecialchars($paymentData['donor_name']); ?></td>
                        </tr>
                        <tr>
                            <th>Amount (USD):</th>
                            <td>$<?php echo number_format($paymentData['amount'], 2); ?></td>
                        </tr>
                        <tr>
                            <th>Amount (FCFA):</th>
                            <td><strong><?php echo number_format($paymentData['amount_fcfa'], 0); ?> FCFA</strong></td>
                        </tr>
                        <tr>
                            <th>Reference Code:</th>
                            <td><span class="reference-code"><?php echo htmlspecialchars($paymentData['reference_code']); ?></span></td>
                        </tr>
                    </table>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i> Please follow these steps carefully to complete your Mobile Money payment.
                </div>
                
                <div class="steps-container">
                    <div class="step-item">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <h4>Note Your Reference Code</h4>
                            <p>Please use this unique reference code for your transaction:</p>
                            <div class="reference-code"><?php echo htmlspecialchars($paymentData['reference_code']); ?></div>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <h4>Open Your Mobile Money App</h4>
                            <p>Launch your mobile money application on your phone.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <h4>Select "Send Money"</h4>
                            <p>Choose the option to send money to another mobile money account.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">4</div>
                        <div class="step-content">
                            <h4>Enter Recipient Number</h4>
                            <p>Enter the following mobile number: <strong><?php echo htmlspecialchars($recipientNumber); ?></strong></p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">5</div>
                        <div class="step-content">
                            <h4>Verify Recipient Name</h4>
                            <p>IMPORTANT: Verify that the recipient name shown is <strong><?php echo htmlspecialchars($recipientName); ?></strong> before proceeding.</p>
                            <p class="text-danger">Do not send money if the name does not match!</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">6</div>
                        <div class="step-content">
                            <h4>Enter Amount</h4>
                            <p>Enter the amount: <strong><?php echo number_format($paymentData['amount_fcfa'], 0); ?> FCFA</strong></p>
                            <p class="text-muted small">(Equivalent to $<?php echo number_format($paymentData['amount'], 2); ?> USD)</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">7</div>
                        <div class="step-content">
                            <h4>Add Reference Code</h4>
                            <p>In the message/note field, enter your reference code: <strong><?php echo htmlspecialchars($paymentData['reference_code']); ?></strong></p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">8</div>
                        <div class="step-content">
                            <h4>Complete Transaction</h4>
                            <p>Confirm and complete the transaction in your mobile money app.</p>
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-number">9</div>
                        <div class="step-content">
                            <h4>Send Confirmation via WhatsApp</h4>
                            <p>After completing your payment, please send a confirmation message via WhatsApp to <strong>+14697031453</strong> with your reference code.</p>
                        </div>
                    </div>
                </div>
                
                <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="mt-4">
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label">Your Mobile Number (Optional)</label>
                        <input type="tel" class="form-control" id="mobile_number" name="mobile_number" placeholder="Enter the number you used for payment">
                        <div class="form-text">This helps us verify your payment faster.</div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="name_verified" name="name_verified" value="1" required>
                            <label class="form-check-label" for="name_verified">
                                <strong>I confirm that I have verified the recipient name is "<?php echo htmlspecialchars($recipientName); ?>" before sending money</strong>
                            </label>
                            <div class="invalid-feedback">
                                You must confirm that you've verified the recipient's name.
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> After completing your payment, please send a confirmation message via WhatsApp to <strong>+14697031453</strong> with your reference code.
                    </div>
                    
                    <div class="d-grid gap-2">
                        <button type="submit" name="confirm_payment" value="1" class="btn btn-primary btn-lg">I've Completed My Payment</button>
                        <a href="donate-form.php" class="btn btn-outline-secondary">Cancel and Return to Donation Form</a>
                    </div>
                </form>
                
                <div class="alert alert-warning mt-4">
                    <i class="fas fa-exclamation-triangle me-2"></i> <strong>Important:</strong> Your donation will be marked as "pending" until we verify the payment. This typically takes 1-24 hours.
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="footer-about">
                        <p>Empowering individuals and communities through biblical prosperity principles, financial literacy, and faith-based community development.</p>
                        <div class="social-links mt-3">
                            <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <div class="footer-links">
                        <h5>Quick Links</h5>
                        <ul class="list-unstyled">
                            <li><a href="about.html">About Us</a></li>
                            <li><a href="ministries.html">Ministries</a></li>
                            <li><a href="events.html">Events</a></li>
                            <li><a href="resources.html">Resources</a></li>
                            <li><a href="get-involved.html">Get Involved</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="footer-links">
                        <h5>Ministries</h5>
                        <ul class="list-unstyled">
                            <li><a href="ministries/cryptstock.html">CrypStock Prosperity Academy</a></li>
                            <li><a href="ministries/faith-worship.html">Faith & Worship Ministry</a></li>
                            <li><a href="ministries/community.html">Community Impact Projects</a></li>
                            <li><a href="ministries/love-in-action.html">Love in Action Fellowship</a></li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-3 col-md-4">
                    <div class="footer-contact">
                        <h5>Contact Us</h5>
                        <address>
                            <p><i class="fas fa-map-marker-alt me-2"></i> 123 Prosperity Way, Houston, TX 77001</p>
                            <p><i class="fas fa-phone-alt me-2"></i> <a href="tel:+14697031453">(469) 703-1453</a></p>
                            <p><i class="fas fa-envelope me-2"></i> <a href="mailto:hello@holisticprosperityministry.org">hello@holisticprosperityministry.org</a></p>
                        </address>
                    </div>
                </div>
            </div>
            <hr class="mt-4 mb-4">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-md-0">© 2025 Holistic Prosperity Ministry. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0"><a href="privacy-policy.html">Privacy Policy</a> | <a href="terms-of-service.html">Terms of Service</a></p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

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
