<?php
/**
 * Payment System Test Page
 * Holistic Prosperity Ministry Payment System
 */

// Include configuration
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token
$csrfToken = generateCSRFToken();

// Initialize variables
$paymentSuccess = false;
$paymentError = '';
$formData = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $paymentError = "Security validation failed. Please try again.";
    } else {
        // Get form data
        $formData = [
            'donor_name' => sanitizeInput($_POST['donor_name'] ?? ''),
            'donor_email' => sanitizeInput($_POST['donor_email'] ?? ''),
            'amount' => floatval($_POST['amount'] ?? 0),
            'payment_method' => sanitizeInput($_POST['payment_method'] ?? ''),
            'donation_type' => sanitizeInput($_POST['donation_type'] ?? 'ministry'),
            'donation_purpose' => sanitizeInput($_POST['donation_purpose'] ?? ''),
            'is_recurring' => isset($_POST['is_recurring']) ? 1 : 0
        ];
        
        // Validate form data
        if (empty($formData['donor_name'])) {
            $paymentError = "Please enter your name.";
        } elseif (empty($formData['donor_email']) || !filter_var($formData['donor_email'], FILTER_VALIDATE_EMAIL)) {
            $paymentError = "Please enter a valid email address.";
        } elseif ($formData['amount'] <= 0) {
            $paymentError = "Please enter a valid amount.";
        } elseif (empty($formData['payment_method'])) {
            $paymentError = "Please select a payment method.";
        } else {
            // Simulate payment processing
            try {
                // Connect to database
                require_once 'includes/db_connect.php';
                
                // Insert donation record
                $stmt = $pdo->prepare("
                    INSERT INTO donations 
                    (donor_name, donor_email, amount, payment_method, payment_status, 
                     is_recurring, donation_type, donation_purpose, ip_address)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $formData['donor_name'],
                    $formData['donor_email'],
                    $formData['amount'],
                    $formData['payment_method'],
                    'completed', // Simulate successful payment
                    $formData['is_recurring'],
                    $formData['donation_type'],
                    $formData['donation_purpose'],
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
                
                // Set success flag
                $paymentSuccess = true;
                
                // Store donation details in session for confirmation
                $_SESSION['donation'] = [
                    'id' => $donationId,
                    'name' => $formData['donor_name'],
                    'email' => $formData['donor_email'],
                    'amount' => $formData['amount'],
                    'payment_method' => $formData['payment_method'],
                    'transaction_id' => $transactionId,
                    'date' => date('Y-m-d H:i:s')
                ];
                
                // Set success flag to show confirmation
                $paymentSuccess = true;
                
            } catch (PDOException $e) {
                // Log error
                error_log("Payment Processing Error: " . $e->getMessage());
                
                // Set error message
                $paymentError = "An error occurred while processing your payment. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Payment - Holistic Prosperity Ministry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --royal-purple: #4B0082;
            --gold: #FFD700;
            --white: #FFFFFF;
            --dark-gray: #333333;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-gray);
        }
        
        .header {
            background-color: var(--royal-purple);
            color: var(--white);
            padding: 20px 0;
            text-align: center;
        }
        
        .header h1 {
            color: var(--gold);
        }
        
        .payment-container {
            max-width: 800px;
            margin: 30px auto;
            background-color: var(--white);
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        .form-label {
            font-weight: bold;
        }
        
        .btn-primary {
            background-color: var(--royal-purple);
            border-color: var(--royal-purple);
        }
        
        .btn-primary:hover {
            background-color: #3a0065;
            border-color: #3a0065;
        }
        
        .payment-method-option {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method-option:hover {
            border-color: var(--royal-purple);
            background-color: #f9f5ff;
        }
        
        .payment-method-option.selected {
            border-color: var(--royal-purple);
            background-color: #f9f5ff;
            box-shadow: 0 0 5px rgba(75, 0, 130, 0.3);
        }
        
        .payment-method-option input[type="radio"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>Holistic Prosperity Ministry</h1>
            <p>Test Payment System</p>
        </div>
    </div>
    
    <div class="container payment-container">
        <?php if ($paymentSuccess): ?>
            <div class="text-center">
                <div class="mb-4">
                    <i class="fas fa-check-circle" style="font-size: 64px; color: #4B0082;"></i>
                </div>
                <h2 class="mb-3">Thank You for Your Donation!</h2>
                <p class="lead mb-4">Your donation has been successfully processed.</p>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Donation Details</h5>
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <th>Transaction ID:</th>
                                    <td><?php echo $_SESSION['donation']['transaction_id']; ?></td>
                                </tr>
                                <tr>
                                    <th>Amount:</th>
                                    <td>$<?php echo number_format($_SESSION['donation']['amount'], 2); ?></td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td><?php echo date('F j, Y', strtotime($_SESSION['donation']['date'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $_SESSION['donation']['payment_method'])); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="mb-4">
                    <a href="test-payment.php" class="btn btn-primary">Make Another Donation</a>
                </div>
            </div>
        <?php else: ?>
            <?php if ($paymentError): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $paymentError; ?>
                </div>
            <?php endif; ?>
            
            <h2 class="mb-4">Make a Donation</h2>
        
        <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="payment-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
            
            <div class="mb-3">
                <label for="donation_type" class="form-label">Donation Type</label>
                <select class="form-select" id="donation_type" name="donation_type" required>
                    <option value="ministry" <?php echo ($formData['donation_type'] ?? '') === 'ministry' ? 'selected' : ''; ?>>Ministry Donation</option>
                    <option value="academy" <?php echo ($formData['donation_type'] ?? '') === 'academy' ? 'selected' : ''; ?>>CrypStock Academy</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="donor_name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="donor_name" name="donor_name" value="<?php echo $formData['donor_name'] ?? ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="donor_email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="donor_email" name="donor_email" value="<?php echo $formData['donor_email'] ?? ''; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label">Amount ($)</label>
                <input type="number" class="form-control" id="amount" name="amount" min="1" step="0.01" value="<?php echo $formData['amount'] ?? '10.00'; ?>" required>
            </div>
            
            <div class="mb-3">
                <label for="donation_purpose" class="form-label">Purpose (Optional)</label>
                <input type="text" class="form-control" id="donation_purpose" name="donation_purpose" value="<?php echo $formData['donation_purpose'] ?? ''; ?>">
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="is_recurring" name="is_recurring" <?php echo isset($formData['is_recurring']) && $formData['is_recurring'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_recurring">Make this a monthly recurring donation</label>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Payment Method</label>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('credit_card')">
                    <input type="radio" name="payment_method" id="credit_card" value="credit_card" <?php echo ($formData['payment_method'] ?? '') === 'credit_card' ? 'checked' : ''; ?> required>
                    <label for="credit_card">Credit/Debit Card</label>
                </div>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('mobile_money')">
                    <input type="radio" name="payment_method" id="mobile_money" value="mobile_money" <?php echo ($formData['payment_method'] ?? '') === 'mobile_money' ? 'checked' : ''; ?>>
                    <label for="mobile_money">Mobile Money (MoMo)</label>
                </div>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('paypal')">
                    <input type="radio" name="payment_method" id="paypal" value="paypal" <?php echo ($formData['payment_method'] ?? '') === 'paypal' ? 'checked' : ''; ?>>
                    <label for="paypal">PayPal</label>
                </div>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('zelle')">
                    <input type="radio" name="payment_method" id="zelle" value="zelle" <?php echo ($formData['payment_method'] ?? '') === 'zelle' ? 'checked' : ''; ?>>
                    <label for="zelle">Zelle</label>
                </div>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('cashapp')">
                    <input type="radio" name="payment_method" id="cashapp" value="cashapp" <?php echo ($formData['payment_method'] ?? '') === 'cashapp' ? 'checked' : ''; ?>>
                    <label for="cashapp">CashApp</label>
                </div>
                
                <div class="payment-method-option" onclick="selectPaymentMethod('bank_transfer')">
                    <input type="radio" name="payment_method" id="bank_transfer" value="bank_transfer" <?php echo ($formData['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : ''; ?>>
                    <label for="bank_transfer">Bank Transfer</label>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">Complete Donation</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
    
    <script>
        function selectPaymentMethod(method) {
            // Remove selected class from all options
            document.querySelectorAll('.payment-method-option').forEach(option => {
                option.classList.remove('selected');
            });
            
            // Add selected class to the clicked option
            document.querySelector(`#${method}`).closest('.payment-method-option').classList.add('selected');
            
            // Check the radio button
            document.querySelector(`#${method}`).checked = true;
        }
        
        // Initialize selected payment method
        document.addEventListener('DOMContentLoaded', function() {
            const checkedMethod = document.querySelector('input[name="payment_method"]:checked');
            if (checkedMethod) {
                selectPaymentMethod(checkedMethod.id);
            }
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
