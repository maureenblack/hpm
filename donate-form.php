<?php
/**
 * Donation Form
 * Holistic Prosperity Ministry Payment System
 */

// Initialize session
session_start();

// Include configuration and functions
require_once 'includes/config.php';

// Get donation categories
$categories = getDonationCategories();

// Generate CSRF token
$csrf_token = generateCSRFToken();

// Set default values
$defaultAmount = 50;
$defaultCategory = 1; // General Fund
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make a Donation | <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/donate.css">
    <link rel="stylesheet" href="css/payment.css">
    
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
    <!-- Navigation -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Donation Form Section -->
    <section class="donation-form-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="donation-form-wrapper">
                        <h1 class="text-center mb-4">Support Our Ministry</h1>
                        <p class="text-center mb-5">Your generous contribution helps us continue our mission of empowering individuals and communities through biblical prosperity principles.</p>
                        
                        <!-- Progress Steps -->
                        <div class="donation-steps mb-5">
                            <div class="step active" id="step-1">
                                <div class="step-number">1</div>
                                <div class="step-label">Donation Details</div>
                            </div>
                            <div class="step" id="step-2">
                                <div class="step-number">2</div>
                                <div class="step-label">Personal Information</div>
                            </div>
                            <div class="step" id="step-3">
                                <div class="step-number">3</div>
                                <div class="step-label">Payment Method</div>
                            </div>
                            <div class="step" id="step-4">
                                <div class="step-number">4</div>
                                <div class="step-label">Confirmation</div>
                            </div>
                        </div>
                        
                        <!-- Donation Form -->
                        <form id="donation-form" method="post" action="process-donation.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="payment_intent_id" id="payment_intent_id">
                            <input type="hidden" name="reference_code" id="reference_code" value="<?php echo generateReferenceCode(); ?>">
                            
                            <!-- Step 1: Donation Details -->
                            <div class="form-step" id="donation-details">
                                <h3 class="mb-4">Donation Details</h3>
                                
                                <!-- Donation Amount -->
                                <div class="mb-4">
                                    <label class="form-label">Donation Amount</label>
                                    <div class="amount-options">
                                        <?php foreach (DONATION_AMOUNTS as $amount): ?>
                                            <div class="amount-option">
                                                <input type="radio" name="donation_amount_preset" id="amount-<?php echo $amount; ?>" value="<?php echo $amount; ?>" <?php echo ($amount == $defaultAmount) ? 'checked' : ''; ?>>
                                                <label for="amount-<?php echo $amount; ?>"><?php echo formatCurrency($amount); ?></label>
                                            </div>
                                        <?php endforeach; ?>
                                        <div class="amount-option custom">
                                            <input type="radio" name="donation_amount_preset" id="amount-custom" value="custom">
                                            <label for="amount-custom">Custom</label>
                                        </div>
                                    </div>
                                    <div class="custom-amount-wrapper mt-3" style="display: none;">
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>
                                            <input type="number" class="form-control" id="custom_amount" name="custom_amount" min="<?php echo MIN_DONATION_AMOUNT; ?>" step="0.01" placeholder="Enter amount">
                                        </div>
                                        <small class="text-muted">Minimum donation: <?php echo formatCurrency(MIN_DONATION_AMOUNT); ?></small>
                                    </div>
                                    <input type="hidden" name="donation_amount" id="donation_amount" value="<?php echo $defaultAmount; ?>">
                                </div>
                                
                                <!-- Donation Category -->
                                <div class="mb-4">
                                    <label for="donation_category" class="form-label">Designation</label>
                                    <select class="form-select" id="donation_category" name="donation_category">
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['category_id']; ?>" <?php echo ($category['category_id'] == $defaultCategory) ? 'selected' : ''; ?>>
                                                <?php echo $category['category_name']; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Select where you would like your donation to be directed</small>
                                </div>
                                
                                <!-- Donation Frequency -->
                                <div class="mb-4">
                                    <label class="form-label">Donation Frequency</label>
                                    <div class="frequency-options">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="donation_frequency" id="frequency-one-time" value="one-time" checked>
                                            <label class="form-check-label" for="frequency-one-time">
                                                One-time Donation
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="donation_frequency" id="frequency-monthly" value="monthly">
                                            <label class="form-check-label" for="frequency-monthly">
                                                Monthly Recurring
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="donation_frequency" id="frequency-quarterly" value="quarterly">
                                            <label class="form-check-label" for="frequency-quarterly">
                                                Quarterly Recurring
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="donation_frequency" id="frequency-annual" value="annual">
                                            <label class="form-check-label" for="frequency-annual">
                                                Annual Recurring
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Cover Processing Fees -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="cover_fees" name="cover_fees" value="1">
                                        <label class="form-check-label" for="cover_fees">
                                            Add <?php echo PROCESSING_FEE_PERCENTAGE; ?>% + $<?php echo PROCESSING_FEE_FIXED; ?> to cover processing fees
                                        </label>
                                    </div>
                                    <div id="fee-explanation" class="mt-2" style="display: none;">
                                        <small class="text-muted">
                                            By covering the processing fee, 100% of your intended donation goes directly to our ministry.
                                            <span id="fee-amount"></span>
                                        </small>
                                    </div>
                                </div>
                                
                                <!-- Donation in Honor/Memory -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_tribute" name="is_tribute" value="1">
                                        <label class="form-check-label" for="is_tribute">
                                            Make this donation in honor or memory of someone
                                        </label>
                                    </div>
                                    <div id="tribute-fields" class="mt-3" style="display: none;">
                                        <div class="mb-3">
                                            <select class="form-select" id="tribute_type" name="tribute_type">
                                                <option value="honor">In Honor Of</option>
                                                <option value="memory">In Memory Of</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <input type="text" class="form-control" id="tribute_name" name="tribute_name" placeholder="Name of Honoree">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-primary next-step">Continue to Personal Information</button>
                                </div>
                            </div>
                            
                            <!-- Step 2: Personal Information -->
                            <div class="form-step" id="personal-information" style="display: none;">
                                <h3 class="mb-4">Personal Information</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name*</label>
                                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name*</label>
                                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address*</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address">
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="state" class="form-label">State/Province</label>
                                        <input type="text" class="form-control" id="state" name="state">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="postal_code" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="postal_code" name="postal_code">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control" id="country" name="country" value="United States">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_anonymous" name="is_anonymous" value="1">
                                        <label class="form-check-label" for="is_anonymous">
                                            Make this donation anonymous
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-secondary prev-step">Back</button>
                                    <button type="button" class="btn btn-primary next-step">Continue to Payment</button>
                                </div>
                            </div>
                            
                            <!-- Step 3: Payment Method -->
                            <div class="form-step" id="payment-method" style="display: none;">
                                <h3 class="mb-4">Payment Method</h3>
                                
                                <div class="payment-methods mb-4">
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-card" value="credit_card" checked>
                                        <label class="form-check-label" for="method-card">
                                            <i class="fas fa-credit-card"></i> Credit/Debit Card
                                        </label>
                                    </div>
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-mobile" value="mobile_money">
                                        <label class="form-check-label" for="method-mobile">
                                            <i class="fas fa-mobile-alt"></i> Mobile Money
                                        </label>
                                    </div>
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-paypal" value="paypal">
                                        <label class="form-check-label" for="method-paypal">
                                            <i class="fab fa-paypal"></i> PayPal
                                        </label>
                                    </div>
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-zelle" value="zelle">
                                        <label class="form-check-label" for="method-zelle">
                                            <i class="fas fa-exchange-alt"></i> Zelle
                                        </label>
                                    </div>
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-cashapp" value="cashapp">
                                        <label class="form-check-label" for="method-cashapp">
                                            <i class="fas fa-dollar-sign"></i> CashApp
                                        </label>
                                    </div>
                                    <div class="form-check payment-method-option">
                                        <input class="form-check-input" type="radio" name="payment_method" id="method-bank" value="bank_transfer">
                                        <label class="form-check-label" for="method-bank">
                                            <i class="fas fa-university"></i> Bank Transfer
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Credit Card Payment Form -->
                                <div class="payment-form" id="credit-card-form">
                                    <div class="mb-3">
                                        <label for="card-element" class="form-label">Credit or Debit Card</label>
                                        <div id="card-element" class="form-control">
                                            <!-- Stripe Card Element will be inserted here -->
                                        </div>
                                        <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                                    </div>
                                </div>
                                
                                <!-- Mobile Money Payment Form -->
                                <div class="payment-form" id="mobile-money-form" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> Mobile Money Payment Instructions</h5>
                                        <p>Please send your donation to the following Mobile Money account:</p>
                                        <ul>
                                            <li><strong>Name:</strong> <?php echo MOBILE_MONEY_NAME; ?></li>
                                            <li><strong>Number:</strong> <?php echo MOBILE_MONEY_NUMBER; ?></li>
                                            <li><strong>Reference:</strong> <span id="momo-reference"><?php echo $_SESSION['reference_code'] ?? generateReferenceCode(); ?></span></li>
                                        </ul>
                                        <p>After sending the payment, please fill out the verification form below.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mobile_number" class="form-label">Your Mobile Money Number*</label>
                                        <input type="tel" class="form-control" id="mobile_number" name="mobile_number">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="mobile_provider" class="form-label">Mobile Money Provider*</label>
                                        <select class="form-select" id="mobile_provider" name="mobile_provider">
                                            <option value="MTN">MTN Mobile Money</option>
                                            <option value="Orange">Orange Money</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="transaction_id" class="form-label">Transaction ID/Reference (if available)</label>
                                        <input type="text" class="form-control" id="transaction_id" name="transaction_id">
                                    </div>
                                </div>
                                
                                <!-- PayPal Payment Form -->
                                <div class="payment-form" id="paypal-form" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> PayPal Payment</h5>
                                        <p>You will be redirected to PayPal to complete your donation after reviewing your information.</p>
                                    </div>
                                    <div id="paypal-button-container" class="mt-3"></div>
                                </div>
                                
                                <!-- Zelle Payment Form -->
                                <div class="payment-form" id="zelle-form" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> Zelle Payment Instructions</h5>
                                        <p>Please send your donation to the following Zelle account:</p>
                                        <ul>
                                            <li><strong>Email:</strong> <?php echo ADMIN_EMAIL; ?></li>
                                            <li><strong>Name:</strong> Holistic Prosperity Ministry</li>
                                            <li><strong>Reference:</strong> <span id="zelle-reference"><?php echo $_SESSION['reference_code'] ?? generateReferenceCode(); ?></span></li>
                                        </ul>
                                        <p>After sending the payment, please fill out the verification form below.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="zelle_email" class="form-label">Your Zelle Email/Phone*</label>
                                        <input type="text" class="form-control" id="zelle_email" name="zelle_email">
                                    </div>
                                </div>
                                
                                <!-- CashApp Payment Form -->
                                <div class="payment-form" id="cashapp-form" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> CashApp Payment Instructions</h5>
                                        <p>Please send your donation to the following CashApp account:</p>
                                        <ul>
                                            <li><strong>$Cashtag:</strong> $HolisticPM</li>
                                            <li><strong>Reference:</strong> <span id="cashapp-reference"><?php echo $_SESSION['reference_code'] ?? generateReferenceCode(); ?></span></li>
                                        </ul>
                                        <p>After sending the payment, please fill out the verification form below.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="cashapp_name" class="form-label">Your CashApp Name*</label>
                                        <input type="text" class="form-control" id="cashapp_name" name="cashapp_name">
                                    </div>
                                </div>
                                
                                <!-- Bank Transfer Payment Form -->
                                <div class="payment-form" id="bank-form" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5><i class="fas fa-info-circle"></i> Bank Transfer Instructions</h5>
                                        <p>Please transfer your donation to the following bank account:</p>
                                        <ul>
                                            <li><strong>Bank Name:</strong> First National Bank</li>
                                            <li><strong>Account Name:</strong> Holistic Prosperity Ministry</li>
                                            <li><strong>Account Number:</strong> 1234567890</li>
                                            <li><strong>Routing Number:</strong> 987654321</li>
                                            <li><strong>Reference:</strong> <span id="bank-reference"><?php echo $_SESSION['reference_code'] ?? generateReferenceCode(); ?></span></li>
                                        </ul>
                                        <p>After sending the payment, please fill out the verification form below.</p>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="bank_name" class="form-label">Your Bank Name*</label>
                                        <input type="text" class="form-control" id="bank_name" name="bank_name">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="bank_reference" class="form-label">Your Transfer Reference*</label>
                                        <input type="text" class="form-control" id="bank_reference" name="bank_reference">
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-secondary prev-step">Back</button>
                                    <button type="button" class="btn btn-primary next-step" id="payment-button">Review Donation</button>
                                </div>
                            </div>
                            
                            <!-- Step 4: Confirmation -->
                            <div class="form-step" id="confirmation" style="display: none;">
                                <h3 class="mb-4">Review Your Donation</h3>
                                
                                <div class="donation-summary card mb-4">
                                    <div class="card-body">
                                        <h5 class="card-title">Donation Summary</h5>
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <th>Amount:</th>
                                                    <td id="summary-amount"></td>
                                                </tr>
                                                <tr>
                                                    <th>Designation:</th>
                                                    <td id="summary-category"></td>
                                                </tr>
                                                <tr>
                                                    <th>Frequency:</th>
                                                    <td id="summary-frequency"></td>
                                                </tr>
                                                <tr id="summary-fees-row" style="display: none;">
                                                    <th>Processing Fee:</th>
                                                    <td id="summary-fees"></td>
                                                </tr>
                                                <tr>
                                                    <th>Total Amount:</th>
                                                    <td id="summary-total"></td>
                                                </tr>
                                                <tr>
                                                    <th>Payment Method:</th>
                                                    <td id="summary-method"></td>
                                                </tr>
                                                <tr id="summary-tribute-row" style="display: none;">
                                                    <th id="summary-tribute-type"></th>
                                                    <td id="summary-tribute-name"></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms_agreement" name="terms_agreement" required>
                                        <label class="form-check-label" for="terms_agreement">
                                            I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="form-navigation">
                                    <button type="button" class="btn btn-secondary prev-step">Back</button>
                                    <button type="submit" class="btn btn-success" id="submit-donation">Complete Donation</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Donation Agreement</h6>
                    <p>By making a donation to Holistic Prosperity Ministry, you agree to the following terms:</p>
                    <ul>
                        <li>All donations are final and non-refundable.</li>
                        <li>For recurring donations, you authorize Holistic Prosperity Ministry to charge your payment method on a recurring basis until you notify us to stop.</li>
                        <li>You confirm that you are using your own payment method and have the authority to make this donation.</li>
                        <li>Holistic Prosperity Ministry will use your donation for its charitable purposes in accordance with its mission.</li>
                        <li>Your personal information will be handled in accordance with our Privacy Policy.</li>
                    </ul>
                    
                    <h6>Tax Deductibility</h6>
                    <p>Holistic Prosperity Ministry is a registered 501(c)(3) non-profit organization. Donations are tax-deductible to the extent allowed by law. Please consult your tax advisor for specific guidance.</p>
                    
                    <h6>Privacy Policy</h6>
                    <p>We respect your privacy and will not sell, rent, or lease your personal information to third parties. Your information will be used solely for processing your donation and communicating with you about ministry activities.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script src="js/payment.js"></script>
    
    <script>
        // Initialize Stripe
        const stripe = Stripe('<?php echo STRIPE_PUBLISHABLE_KEY; ?>');
        const elements = stripe.elements();
        
        // Create card element
        const cardElement = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            }
        });
        
        // Mount the card element
        cardElement.mount('#card-element');
    </script>
</body>
</html>
