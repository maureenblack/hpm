<?php
/**
 * Thank You Page
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if donation data exists in session
if (!isset($_SESSION['donation'])) {
    // Redirect to donation page if no donation data
    header("Location: donate-form.php");
    exit;
}

// Get donation details from session
$donation = $_SESSION['donation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thank You - Holistic Prosperity Ministry</title>
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
        .thank-you-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .success-icon {
            font-size: 80px;
            color: #4B0082;
            margin-bottom: 20px;
        }
        
        .donation-details {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .donation-details table {
            width: 100%;
        }
        
        .donation-details th {
            width: 40%;
            padding: 10px;
            text-align: left;
            color: #555;
        }
        
        .donation-details td {
            padding: 10px;
            text-align: left;
            font-weight: 500;
        }
        
        .next-steps {
            background-color: #f0e6ff;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }
        
        .reference-code {
            font-family: monospace;
            font-size: 18px;
            background-color: #f0f0f0;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px dashed #ccc;
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

    <!-- Thank You Section -->
    <section class="thank-you-section py-5 mt-5">
        <div class="container">
            <div class="thank-you-container text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="mb-3">Thank You for Your Generosity!</h1>
                <p class="lead mb-4">Your donation will help transform lives through biblical prosperity principles and community impact.</p>
                
                <div class="donation-details">
                    <h3 class="mb-3">Donation Details</h3>
                    <table class="table table-borderless">
                        <tr>
                            <th>Transaction ID:</th>
                            <td><?php echo htmlspecialchars($donation['transaction_id']); ?></td>
                        </tr>
                        <tr>
                            <th>Amount (USD):</th>
                            <td>$<?php echo number_format($donation['amount'], 2); ?></td>
                        </tr>
                        <?php if (isset($donation['amount_fcfa'])): ?>
                        <tr>
                            <th>Amount (FCFA):</th>
                            <td><strong><?php echo number_format($donation['amount_fcfa'], 0); ?> FCFA</strong></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th>Date:</th>
                            <td><?php echo date('F j, Y', strtotime($donation['date'])); ?></td>
                        </tr>
                        <tr>
                            <th>Payment Method:</th>
                            <td><?php echo ucfirst(str_replace('_', ' ', $donation['payment_method'])); ?></td>
                        </tr>
                    </table>
                </div>
                
                <?php if ($donation['payment_method'] === 'mobile_money'): ?>
                <div class="next-steps">
                    <h3 class="mb-3">Mobile Money Payment Confirmation</h3>
                    <p>Your donation has been registered in our system. Please note your reference code:</p>
                    <p class="reference-code mb-3"><?php echo htmlspecialchars($donation['transaction_id']); ?></p>
                    
                    <div class="alert alert-warning">
                        <h4><i class="fas fa-exclamation-triangle me-2"></i> Important Next Step</h4>
                        <p>Please send a confirmation message via WhatsApp to <strong>+14697031453</strong> with your reference code.</p>
                        <p>This will help us verify your payment faster and ensure your donation is properly credited.</p>
                    </div>
                    
                    <p>Our team will verify your payment within 24 hours. You will receive a confirmation email once your payment is verified.</p>
                </div>
                <?php endif; ?>
                
                <p>A confirmation email has been sent to <strong><?php echo $donation['email']; ?></strong> with the details of your donation.</p>
                
                <div class="mt-4">
                    <a href="index.html" class="btn btn-outline-primary me-2">Return to Homepage</a>
                    <a href="donate-form.php" class="btn btn-primary">Make Another Donation</a>
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
                            <li><a href="ministries.html">Get Involved</a></li>
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
