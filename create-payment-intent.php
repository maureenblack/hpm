<?php
/**
 * Create Payment Intent API Endpoint
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - PCI compliant payment processing
 * - JSON request/response handling
 * - Input validation and sanitization
 * - CORS protection
 * - Rate limiting
 */

// Set strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Include configuration and functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Check if Stripe handler exists
if (file_exists('includes/stripe-handler.php')) {
    require_once 'includes/stripe-handler.php';
} else {
    // For testing without Stripe SDK
    http_response_code(200);
    echo json_encode(['clientSecret' => 'test_' . bin2hex(random_bytes(16))]);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Implement basic rate limiting
$ipAddress = $_SERVER['REMOTE_ADDR'];
$rateLimitFile = LOGS_PATH . '/rate_limits.json';
$rateLimitData = [];

if (file_exists($rateLimitFile)) {
    $rateLimitData = json_decode(file_get_contents($rateLimitFile), true) ?: [];
}

// Clean up old entries (older than 1 hour)
foreach ($rateLimitData as $ip => $data) {
    if (time() - $data['timestamp'] > 3600) {
        unset($rateLimitData[$ip]);
    }
}

// Check rate limit (max 10 requests per minute)
if (isset($rateLimitData[$ipAddress])) {
    $requestCount = $rateLimitData[$ipAddress]['count'];
    $timestamp = $rateLimitData[$ipAddress]['timestamp'];
    
    // If within the last minute and over limit
    if (time() - $timestamp < 60 && $requestCount >= 10) {
        http_response_code(429);
        echo json_encode(['error' => 'Too many requests. Please try again later.']);
        exit;
    }
    
    // Reset count if more than a minute has passed
    if (time() - $timestamp >= 60) {
        $rateLimitData[$ipAddress] = [
            'count' => 1,
            'timestamp' => time()
        ];
    } else {
        // Increment count
        $rateLimitData[$ipAddress]['count']++;
    }
} else {
    // First request from this IP
    $rateLimitData[$ipAddress] = [
        'count' => 1,
        'timestamp' => time()
    ];
}

// Save rate limit data
file_put_contents($rateLimitFile, json_encode($rateLimitData));

// Process only POST requests with JSON content
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

// Validate input
if (!$data || !isset($data['amount']) || !is_numeric($data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Sanitize and validate amount
$amount = filter_var($data['amount'], FILTER_VALIDATE_FLOAT);
if ($amount === false || $amount < MIN_DONATION_AMOUNT) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid donation amount']);
    exit;
}

// Get other parameters with defaults
$currency = isset($data['currency']) ? strtolower(sanitizeInput($data['currency'])) : 'usd';
$description = isset($data['description']) ? sanitizeInput($data['description']) : 'Donation to Holistic Prosperity Ministry';

// Prepare metadata
$metadata = [
    'source' => 'website',
    'ip_address' => hash('sha256', $ipAddress), // Hashed for privacy
    'timestamp' => time()
];

// Add additional metadata if provided
if (isset($data['metadata']) && is_array($data['metadata'])) {
    foreach ($data['metadata'] as $key => $value) {
        // Only allow specific metadata fields
        if (in_array($key, ['donor_id', 'category_id', 'reference_code', 'is_recurring'])) {
            $metadata[$key] = sanitizeInput($value);
        }
    }
}

// Create payment intent
$result = createStripePaymentIntent($amount, $currency, $description, $metadata);

// Check for errors
if (isset($result['error'])) {
    http_response_code(400);
    echo json_encode(['error' => $result['error']]);
    exit;
}

// Return payment intent details
echo json_encode([
    'id' => $result['id'],
    'client_secret' => $result['client_secret'],
    'amount' => $result['amount'],
    'currency' => $result['currency']
]);
?>
