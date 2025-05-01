<?php
/**
 * Helper Functions
 * Holistic Prosperity Ministry Payment System
 */

/**
 * Generate a unique reference code for transactions
 *
 * @param string $prefix Prefix for the reference code
 * @return string Unique reference code
 */
function generateReferenceCode($prefix = 'HPM') {
    $timestamp = time();
    $random = mt_rand(1000, 9999);
    $reference = strtoupper($prefix . '-' . date('Ymd', $timestamp) . '-' . $random);
    
    // Check if reference already exists in database
    global $pdo;
    $stmt = $pdo->prepare("SELECT reference_code FROM transactions WHERE reference_code = ?");
    $stmt->execute([$reference]);
    
    // If reference exists, generate a new one recursively
    if ($stmt->rowCount() > 0) {
        return generateReferenceCode($prefix);
    }
    
    return $reference;
}

/**
 * Format currency amount
 *
 * @param float $amount Amount to format
 * @param string $currency Currency code
 * @return string Formatted amount
 */
function formatCurrency($amount, $currency = 'USD') {
    $currencies = [
        'USD' => ['symbol' => '$', 'position' => 'before'],
        'EUR' => ['symbol' => '€', 'position' => 'before'],
        'GBP' => ['symbol' => '£', 'position' => 'before'],
        'XAF' => ['symbol' => 'FCFA', 'position' => 'after']
    ];
    
    $currency = strtoupper($currency);
    $currencyInfo = isset($currencies[$currency]) ? $currencies[$currency] : $currencies['USD'];
    
    $formattedAmount = number_format($amount, 2, '.', ',');
    
    if ($currencyInfo['position'] === 'before') {
        return $currencyInfo['symbol'] . $formattedAmount;
    } else {
        return $formattedAmount . ' ' . $currencyInfo['symbol'];
    }
}

/**
 * Calculate processing fee for a donation
 *
 * @param float $amount Donation amount
 * @return float Processing fee
 */
function calculateProcessingFee($amount) {
    $percentage = PROCESSING_FEE_PERCENTAGE / 100;
    $fixed = PROCESSING_FEE_FIXED;
    
    return round(($amount * $percentage) + $fixed, 2);
}

/**
 * Sanitize and validate user input
 *
 * @param string $input User input
 * @param string $type Type of validation (email, string, int, float)
 * @return mixed Sanitized input or false if invalid
 */
function sanitizeInput($input, $type = 'string') {
    $input = trim($input);
    
    switch ($type) {
        case 'email':
            $input = filter_var($input, FILTER_SANITIZE_EMAIL);
            return filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : false;
            
        case 'int':
            return filter_var($input, FILTER_VALIDATE_INT) !== false ? 
                   filter_var($input, FILTER_SANITIZE_NUMBER_INT) : false;
            
        case 'float':
            return filter_var($input, FILTER_VALIDATE_FLOAT) !== false ? 
                   filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : false;
            
        case 'url':
            $input = filter_var($input, FILTER_SANITIZE_URL);
            return filter_var($input, FILTER_VALIDATE_URL) ? $input : false;
            
        case 'string':
        default:
            return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * Log user activity
 *
 * @param int $userId User ID
 * @param string $action Action performed
 * @param string $entityType Type of entity affected
 * @param int $entityId ID of entity affected
 * @return bool Success status
 */
function logActivity($userId, $action, $entityType = null, $entityId = null) {
    global $pdo;
    
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_log (user_id, action, entity_type, entity_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([$userId, $action, $entityType, $entityId, $ipAddress, $userAgent]);
    } catch (PDOException $e) {
        error_log("Activity Log Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send email using templates
 *
 * @param string $to Recipient email
 * @param string $templateName Template name
 * @param array $data Data to replace in template
 * @return bool Success status
 */
function sendEmail($to, $templateName, $data = []) {
    global $pdo;
    
    try {
        // Get email template
        $stmt = $pdo->prepare("SELECT subject, body FROM email_templates WHERE template_name = ? AND is_active = 1");
        $stmt->execute([$templateName]);
        $template = $stmt->fetch();
        
        if (!$template) {
            error_log("Email template not found: $templateName");
            return false;
        }
        
        $subject = $template['subject'];
        $body = $template['body'];
        
        // Replace placeholders with actual data
        foreach ($data as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }
        
        // Set up email headers
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">" . "\r\n";
        
        // Send email
        return mail($to, $subject, $body, $headers);
        
    } catch (PDOException $e) {
        error_log("Email Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Generate a receipt number
 *
 * @param int $transactionId Transaction ID
 * @return string Receipt number
 */
function generateReceiptNumber($transactionId) {
    $prefix = 'HPMR';
    $year = date('Y');
    $padded = str_pad($transactionId, 6, '0', STR_PAD_LEFT);
    
    return $prefix . $year . $padded;
}

/**
 * Check if user is logged in
 *
 * @return bool Login status
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user has admin privileges
 *
 * @return bool Admin status
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Redirect to a URL
 *
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Create CSRF token
 *
 * @return string CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 *
 * @param string $token Token to verify
 * @return bool Verification result
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get donation categories
 *
 * @param bool $activeOnly Get only active categories
 * @return array Categories
 */
function getDonationCategories($activeOnly = true) {
    global $pdo;
    
    $sql = "SELECT category_id, category_name, description FROM donation_categories";
    if ($activeOnly) {
        $sql .= " WHERE is_active = 1";
    }
    $sql .= " ORDER BY category_name";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

/**
 * Get payment method name
 *
 * @param string $method Payment method code
 * @return string Payment method name
 */
function getPaymentMethodName($method) {
    $methods = [
        'credit_card' => 'Credit/Debit Card',
        'mobile_money' => 'Mobile Money',
        'paypal' => 'PayPal',
        'zelle' => 'Zelle',
        'cashapp' => 'CashApp',
        'bank_transfer' => 'Bank Transfer'
    ];
    
    return isset($methods[$method]) ? $methods[$method] : 'Unknown';
}

/**
 * Get recurring frequency name
 *
 * @param string $frequency Frequency code
 * @return string Frequency name
 */
function getRecurringFrequencyName($frequency) {
    $frequencies = [
        'monthly' => 'Monthly',
        'quarterly' => 'Quarterly',
        'annual' => 'Annual'
    ];
    
    return isset($frequencies[$frequency]) ? $frequencies[$frequency] : 'One-time';
}
?>
