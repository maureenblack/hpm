<?php
/**
 * Donation Form
 * Holistic Prosperity Ministry Payment System
 */

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Get any error messages
$errorMessage = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);

// Get any success messages
$successMessage = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate - Holistic Prosperity Ministry</title>
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
        /* Payment Processing Overlay */
        .payment-processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            color: white;
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #FFD700;
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .payment-method-option {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method-option:hover,
        .payment-method-option.selected {
            border-color: #4B0082;
            background-color: #f9f5ff;
        }
        
        .form-control.is-invalid {
            border-color: #dc3545;
            padding-right: calc(1.5em + 0.75rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.375em + 0.1875rem) center;
            background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem);
        }
        
        .invalid-feedback {
            display: none;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }
    </style>
    <!-- Chart.js for donation charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Payment system simplified to use direct Stripe payment links -->
<style>
    .payment-method-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border: 2px solid transparent;
    }
    .payment-method-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    .payment-method-card.selected {
        border-color: #4B0082;
        background-color: rgba(75, 0, 130, 0.05);
    }
    .mobile-money-instructions {
        background-color: #f8f9fa;
        border-left: 4px solid #4B0082;
        padding: 15px;
        margin: 20px 0;
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
                        <a class="nav-link active" href="donate-form.php">Donate</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="donation-hero d-flex align-items-center text-white">
        <div class="overlay"></div>
        <div class="container position-relative">
            <div class="row align-items-center">
                <div class="col-lg-8 mx-auto text-center">
                    <h1 class="display-4 fw-bold mb-3">Empower change. Plant a seed.</h1>
                    <p class="lead mb-4">Your generosity transforms lives through biblical prosperity principles and community impact.</p>
                    <div class="hero-buttons">
                        <a href="#giving-options" class="btn btn-primary btn-lg me-2 mb-2" aria-label="View donation options">Ways to Give</a>
                        <a href="#donation-form" class="btn btn-gold btn-lg mb-2" aria-label="Donate to Holistic Prosperity Ministry">Give Now</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

<!-- Payment Methods Section Removed -->



    <!-- Impact Statistics Section -->
    <section class="impact-stats py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Your Impact</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">Together, we're creating lasting change</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="impact-stat-card text-center h-100">
                        <div class="impact-icon">
                            <i class="fas fa-users fa-3x text-primary"></i>
                        </div>
                        <div class="impact-number counter" data-count="5000">5000+</div>
                        <div class="impact-label">Lives Transformed</div>
                        <p class="impact-description">Individuals and families experiencing spiritual and financial transformation</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-stat-card text-center h-100">
                        <div class="impact-icon">
                            <i class="fas fa-graduation-cap fa-3x text-primary"></i>
                        </div>
                        <div class="impact-number counter" data-count="1500">1500+</div>
                        <div class="impact-label">Academy Graduates</div>
                        <p class="impact-description">Students equipped with financial literacy and biblical prosperity principles</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-stat-card text-center h-100">
                        <div class="impact-icon">
                            <i class="fas fa-hands-helping fa-3x text-primary"></i>
                        </div>
                        <div class="impact-number counter" data-count="120">120+</div>
                        <div class="impact-label">Community Projects</div>
                        <p class="impact-description">Outreach initiatives bringing hope and practical support to communities</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="impact-stat-card text-center h-100">
                        <div class="impact-icon">
                            <i class="fas fa-globe fa-3x text-primary"></i>
                        </div>
                        <div class="impact-number counter" data-count="32">32</div>
                        <div class="impact-label">Countries Reached</div>
                        <p class="impact-description">Global impact through our online programs and international partnerships</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Transformation Stories Section -->
    <section class="transformation-stories py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Transformation Stories</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">Real people, real change through your generosity</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="story-card">
                        <div class="story-content">
                            <h3 class="story-title">Sarah's Journey</h3>
                            <p class="story-quote">The CrypStock Academy changed my life. I was drowning in debt, but now I'm debt-free and building wealth for my family's future.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="story-card">
                        <div class="story-content">
                            <h3 class="story-title">James' Journey</h3>
                            <p class="story-quote">The mentorship I received gave me the confidence to pursue my business dream. Now I'm employing others from my community.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mx-auto">
                    <div class="story-card">
                        <div class="story-content">
                            <h3 class="story-title">The Williams Family</h3>
                            <p class="story-quote">We've learned that prosperity isn't just about money—it's about creating a legacy of faith and generosity for generations to come.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="#donation-form" class="btn btn-primary btn-lg">Help Write the Next Story</a>
            </div>
        </div>
    </section>

    <!-- Giving Options Section -->
    <section id="giving-options" class="giving-options py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Ways to Give</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">Choose the giving option that works best for you</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="giving-option-card text-center">
                        <div class="giving-option-icon">
                            <i class="fas fa-hand-holding-heart fa-2x"></i>
                        </div>
                        <h3 class="giving-option-title">One-Time & Monthly</h3>
                        <p class="giving-option-description">Support our mission with a one-time gift or become a monthly partner for sustained impact.</p>
                        <ul class="giving-option-features">
                            <li>Flexible giving amounts</li>
                            <li>Easy online process</li>
                            <li>Tax-deductible</li>
                            <li>Impact updates</li>
                        </ul>
                        <a href="#donation-form" class="btn btn-primary" aria-label="Donate to Holistic Prosperity Ministry">Give Now</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="giving-option-card text-center">
                        <div class="giving-option-icon">
                            <i class="fas fa-church fa-2x"></i>
                        </div>
                        <h3 class="giving-option-title">Tithes & Offerings</h3>
                        <p class="giving-option-description">Honor God with your tithes and offerings to support the ministry's operations and outreach.</p>
                        <ul class="giving-option-features">
                            <li>Biblical stewardship</li>
                            <li>Ministry sustainability</li>
                            <li>Community impact</li>
                            <li>Spiritual growth</li>
                        </ul>
                        <a href="#donation-form" class="btn btn-primary">Give Tithe</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="giving-option-card text-center">
                        <div class="giving-option-icon">
                            <i class="fas fa-graduation-cap fa-2x"></i>
                        </div>
                        <h3 class="giving-option-title">Sponsor a Student</h3>
                        <p class="giving-option-description">Provide a scholarship for a student to attend our CrypStock Academy programs.</p>
                        <ul class="giving-option-features">
                            <li>Full/partial scholarships</li>
                            <li>Student progress updates</li>
                            <li>Transformational impact</li>
                            <li>Mentorship opportunity</li>
                        </ul>
                        <a href="#donation-form" class="btn btn-primary">Sponsor Now</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="giving-option-card text-center">
                        <div class="giving-option-icon">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                        <h3 class="giving-option-title">Corporate Donations</h3>
                        <p class="giving-option-description">Partner your business with our ministry for community impact and social responsibility.</p>
                        <ul class="giving-option-features">
                            <li>Corporate matching</li>
                            <li>Sponsorship opportunities</li>
                            <li>CSR partnership</li>
                            <li>Employee engagement</li>
                        </ul>
                        <a href="#donation-form" class="btn btn-primary">Partner With Us</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Donation Form Section -->
    <section id="donation-form" class="donation-form-section py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Give Today</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">Your generosity makes a lasting difference</p>
            </div>
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="donation-form-container">
                        <?php if ($errorMessage): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $errorMessage; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($successMessage): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $successMessage; ?>
                            </div>
                        <?php endif; ?>
                        
                        
                        <form id="donationForm" class="needs-validation" action="process.php" method="post" novalidate data-stripe-key="<?php echo getenv('STRIPE_PUBLISHABLE_KEY'); ?>">
                            <!-- CSRF Token for security -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                            <!-- Amount Selection -->
                            <div class="form-section">
                                <h3 class="form-section-title">Select Amount</h3>
                                <!-- Hidden amount field that will be populated by JavaScript -->
                                <input type="hidden" id="hidden_amount" name="amount" value="">
                                <div class="amount-options">
                                    <div class="amount-option" data-amount="25">$25</div>
                                    <div class="amount-option" data-amount="50">$50</div>
                                    <div class="amount-option" data-amount="100">$100</div>
                                    <div class="amount-option" data-amount="250">$250</div>
                                    <div class="amount-option" data-amount="500">$500</div>
                                    <div class="amount-option" data-amount="1000">$1,000</div>
                                </div>
                                <div class="mt-3">
                                    <div class="custom-amount input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="customAmount" name="custom_amount" placeholder="Other Amount" aria-label="Custom donation amount">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Frequency Selection -->
                            <div class="form-section">
                                <h3 class="form-section-title">Select Frequency</h3>
                                <input type="hidden" id="frequency" name="frequency" value="one-time">
                                <div class="frequency-options">
                                    <div class="frequency-option active" data-frequency="one-time">One-Time</div>
                                    <div class="frequency-option" data-frequency="monthly">Monthly</div>
                                    <div class="frequency-option" data-frequency="quarterly">Quarterly</div>
                                </div>
                            </div>
                            
                            <!-- Designation Selection -->
                            <div class="form-section">
                                <h3 class="form-section-title">Designation</h3>
                                <select class="form-select" id="designation" name="designation" required data-name="Designation">
                                    <option value="" selected disabled>Select where you'd like your gift to go</option>
                                    <option value="general">Where Needed Most</option>
                                    <option value="cryptstock">CrypStock Academy</option>
                                    <option value="community">Community Impact Projects</option>
                                    <option value="worship">Faith & Worship Ministry</option>
                                    <option value="building">Building Fund</option>
                                </select>
                                <div class="invalid-feedback">
                                    Please select a designation for your gift.
                                </div>
                            </div>
                            
                            <!-- Donor Information -->
                            <div class="form-section">
                                <h3 class="form-section-title">Your Information</h3>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="donateAnonymously" name="donate_anonymously" value="1">
                                    <label class="form-check-label" for="donateAnonymously">
                                        <strong>I would like to donate anonymously</strong>
                                    </label>
                                    <div class="form-text">Your personal information will not be stored with your donation.</div>
                                </div>
                                
                                <div id="personalInfoFields" class="row g-3">
                                    <div class="col-md-6">
                                        <label for="firstName" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" data-name="First Name">
                                        <div class="invalid-feedback">
                                            Please provide your first name.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lastName" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" data-name="Last Name">
                                        <div class="invalid-feedback">
                                            Please provide your last name.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" data-name="Email Address">
                                        <div class="form-text">We'll send a receipt to this email if provided.</div>
                                        <div class="invalid-feedback">
                                            Please provide a valid email address.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" data-name="Phone Number">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Memorial/Honor Gift -->
                            <div class="form-section">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="memorialGift" name="is_memorial_gift" value="1">
                                    <label class="form-check-label" for="memorialGift">
                                        This gift is in memory/honor of someone
                                    </label>
                                </div>
                                <div id="memorialFields" class="d-none">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <select class="form-select" id="tributeType" name="tribute_type">
                                                <option value="memory">In Memory Of</option>
                                                <option value="honor">In Honor Of</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="text" class="form-control" id="tributeName" name="tribute_name" placeholder="Name of Honoree">
                                        </div>
                                        <div class="col-12">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="sendNotification" name="send_notification" value="1">
                                                <label class="form-check-label" for="sendNotification">
                                                    Send notification of this gift
                                                </label>
                                            </div>
                                        </div>
                                        <div id="notificationFields" class="col-12 d-none">
                                            <textarea class="form-control" id="notificationAddress" name="notification_address" rows="3" placeholder="Recipient's mailing or email address"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Comments/Special Instructions -->
                            <div class="form-section">
                                <h3 class="form-section-title">Comments/Special Instructions</h3>
                                <textarea class="form-control" id="comments" name="comments" rows="3" placeholder="Any special instructions or comments about your gift"></textarea>
                            </div>
                            
                            <!-- Payment Method Selection -->
                            <div class="form-section">
                                <h3 class="form-section-title">Payment Method</h3>
                                <div class="payment-methods">
                                    <div class="row g-4">
                                        <!-- Mobile Money Option -->
                                        <div class="col-md-12 mb-3">
                                            <div class="payment-method-card" id="mobile-money-option">
                                                <div class="payment-method-header">
                                                    <h4><i class="fas fa-mobile-alt"></i> Mobile Money (MoMo)</h4>
                                                </div>
                                                <div class="payment-method-body">
                                                    <p>Send your donation via Mobile Money to our secure account. You'll receive a reference code and instructions for completing your transaction.</p>
                                                    <a href="mobile-money-instructions.php" class="btn btn-primary payment-method-btn" id="mobile-money-btn">
                                                        <i class="fas fa-mobile-alt me-2"></i> Pay with Mobile Money
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Stripe One-Time Donation Option -->
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card" id="stripe-onetime-option">
                                                <div class="payment-method-header">
                                                    <h4><i class="fas fa-credit-card"></i> One-Time Donation</h4>
                                                </div>
                                                <div class="payment-method-body">
                                                    <p>Make a single donation using credit/debit card or other payment methods.</p>
                                                    <a href="https://buy.stripe.com/eVa6p68qadtrg4EeUU" target="_blank" class="btn btn-primary payment-method-btn">
                                                        <i class="fas fa-credit-card me-2"></i> Make One-Time Donation
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Stripe Monthly Recurring Option -->
                                        <div class="col-md-6 mb-3">
                                            <div class="payment-method-card" id="stripe-recurring-option">
                                                <div class="payment-method-header">
                                                    <h4><i class="fas fa-sync-alt"></i> Monthly Recurring</h4>
                                                </div>
                                                <div class="payment-method-body">
                                                    <p>Set up a monthly donation to provide ongoing support to our ministry.</p>
                                                    <a href="https://buy.stripe.com/bIYeVCgWGexvbOo3cd" target="_blank" class="btn btn-primary payment-method-btn">
                                                        <i class="fas fa-sync-alt me-2"></i> Set Up Monthly Donation
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Supported Payment Methods -->
                                        <div class="col-12 mt-3">
                                            <div class="supported-payment-methods">
                                                <h5>Stripe Supports These Payment Methods:</h5>
                                                <div class="payment-icons">
                                                    <span class="payment-icon" title="Credit/Debit Card"><i class="fas fa-credit-card"></i></span>
                                                    <span class="payment-icon" title="Apple Pay"><i class="fab fa-apple-pay"></i></span>
                                                    <span class="payment-icon" title="Google Pay"><i class="fab fa-google-pay"></i></span>
                                                    <span class="payment-icon" title="Cash App Pay"><i class="fas fa-dollar-sign"></i></span>
                                                    <span class="payment-icon" title="Amazon Pay"><i class="fab fa-amazon-pay"></i></span>
                                                </div>
                                                <p class="payment-note">All credit card and digital wallet payments are securely processed through Stripe's payment page.</p>
                                                <p class="subscription-note"><i class="fas fa-info-circle"></i> To cancel a recurring donation, visit <a href="https://billing.stripe.com/p/login/fZeg2RfMacaQagU288" target="_blank">Stripe Billing Portal</a></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden input for payment method -->
                            <input type="hidden" name="payment_method" id="payment_method" value="">

                            <div class="form-section text-center">
                                <button type="submit" id="donateButton" class="btn btn-primary btn-lg px-5">Complete Donation</button>
                                <style>
        :root {
            --royal-purple: #4B0082;
            --gold: #FFD700;
            --white: #FFFFFF;
            --dark-gray: #333333;
        }
        
        /* Payment Processing Overlay */
        .payment-processing-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            flex-direction: column;
            color: white;
            display: none;
        }
        
        .spinner {
            width: 80px;
            height: 80px;
            border: 8px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--gold);
            animation: spin 1s ease-in-out infinite;
            margin-bottom: 20px;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
                                </style>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Transparent Impact Section -->
    <section id="transparent-impact" class="transparent-impact py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Transparent Impact</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">See exactly how your donations are making a difference</p>
            </div>
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <h3 class="mb-4">Fund Allocation</h3>
                    <div class="impact-chart-container">
                        <canvas id="allocationChart"></canvas>
                    </div>
                    <div class="allocation-legend mt-4">
                        <div class="allocation-item">
                            <div class="allocation-color" style="background-color: #4B0082;"></div>
                            <div class="allocation-label">CrypStock Academy</div>
                            <div class="allocation-percentage">40%</div>
                        </div>
                        <div class="allocation-item">
                            <div class="allocation-color" style="background-color: #FFD700;"></div>
                            <div class="allocation-label">Community Outreach</div>
                            <div class="allocation-percentage">25%</div>
                        </div>
                        <div class="allocation-item">
                            <div class="allocation-color" style="background-color: #9370DB;"></div>
                            <div class="allocation-label">Worship & Ministry</div>
                            <div class="allocation-percentage">20%</div>
                        </div>
                        <div class="allocation-item">
                            <div class="allocation-color" style="background-color: #20B2AA;"></div>
                            <div class="allocation-label">Operations</div>
                            <div class="allocation-percentage">10%</div>
                        </div>
                        <div class="allocation-item">
                            <div class="allocation-color" style="background-color: #87CEEB;"></div>
                            <div class="allocation-label">Future Growth</div>
                            <div class="allocation-percentage">5%</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <h3 class="mb-4">Our Commitment to Transparency</h3>
                    <div class="transparency-content">
                        <p class="mb-4">At Holistic Prosperity Ministry, we believe in complete transparency with how your donations are used. We are committed to responsible stewardship of all resources entrusted to us.</p>
                        <div class="commitment-points">
                            <div class="commitment-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-primary me-2"></i>
                                    <h5 class="mb-0">Financial Integrity</h5>
                                </div>
                                <p>We maintain the highest standards of financial accountability and undergo regular financial reviews.</p>
                            </div>
                            <div class="commitment-item mb-4">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-primary me-2"></i>
                                    <h5 class="mb-0">Impact Reporting</h5>
                                </div>
                                <p>We regularly share stories and updates about how your donations are making a difference in people's lives.</p>
                            </div>
                            <div class="commitment-item">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-check-circle text-primary me-2"></i>
                                    <h5 class="mb-0">Donor Privacy</h5>
                                </div>
                                <p>We respect your privacy and will never share your personal information with third parties.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Planned Giving Section -->
    <section id="planned-giving" class="planned-giving py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-title">Planned Giving</h2>
                <div class="title-underline mx-auto"></div>
                <p class="lead">Create a legacy of faith and prosperity for generations to come</p>
            </div>
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="planned-giving-card text-center">
                        <div class="planned-giving-icon mx-auto">
                            <i class="fas fa-scroll fa-2x"></i>
                        </div>
                        <h3 class="planned-giving-title">Bequest in Will</h3>
                        <p>Include Holistic Prosperity Ministry in your will or living trust to leave a lasting legacy.</p>
                        <a href="#" class="btn btn-outline-primary mt-3">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="planned-giving-card text-center">
                        <div class="planned-giving-icon mx-auto">
                            <i class="fas fa-hand-holding-usd fa-2x"></i>
                        </div>
                        <h3 class="planned-giving-title">Charitable Trust</h3>
                        <p>Establish a charitable trust to benefit both your loved ones and our ministry.</p>
                        <a href="#" class="btn btn-outline-primary mt-3">Learn More</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mx-auto">
                    <div class="planned-giving-card text-center">
                        <div class="planned-giving-icon mx-auto">
                            <i class="fas fa-seedling fa-2x"></i>
                        </div>
                        <h3 class="planned-giving-title">Endowment Fund</h3>
                        <p>Create an endowment fund to provide ongoing support for specific ministry programs.</p>
                        <a href="#" class="btn btn-outline-primary mt-3">Learn More</a>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <p>For more information about planned giving options, please contact our Stewardship Team:</p>
                <p><strong>Email:</strong> <a href="mailto:hello@holisticprosperityministry.org">hello@holisticprosperityministry.org</a> | <strong>Phone:</strong> <a href="tel:+14697031453">+1 (469) 703-1453</a></p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="footer-border-top"></div>
            <div class="row">
                <div class="col-12 text-center py-3">
                    <p class="mb-0 text-white-50">&copy; 2025 Holistic Prosperity Ministry | Faith in Action, Prosperity in Motion</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top" id="backToTop">
        <i class="fas fa-arrow-up"></i>
    </a>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom Scripts -->
    <script src="js/responsive.js"></script>
    <script src="js/donate.js"></script>
<!-- Payment Processing Overlay -->
<div class="payment-processing-overlay" id="paymentProcessingOverlay">
    <div class="spinner"></div>
    <h3>Processing Your Donation</h3>
    <p>Please wait while we process your generous contribution...</p>
</div>

    <script>
        // Add hover effect to payment method cards
        document.addEventListener('DOMContentLoaded', function() {
            const paymentMethodCards = document.querySelectorAll('.payment-method-card');
            paymentMethodCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('hover');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('hover');
                });
            });
        });
    </script>
</body>
</html>
