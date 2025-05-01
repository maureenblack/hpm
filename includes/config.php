<?php
/**
 * Configuration File
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - Uses .env file outside web root for sensitive credentials
 * - Implements environment variable loading
 * - Sets secure defaults for PHP configuration
 */

// Load environment variables from .env file
$envFile = dirname(__DIR__) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse environment variable
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        // Remove quotes if present
        if (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        } elseif (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1) {
            $value = substr($value, 1, -1);
        }
        
        // Set environment variable
        putenv("$name=$value");
        $_ENV[$name] = $value;
    }
}

// Site settings
define('SITE_NAME', 'Holistic Prosperity Ministry');
define('SITE_URL', 'https://holisticprosperityministry.org');
define('ADMIN_EMAIL', 'hello@holisticprosperityministry.org');

// Stripe API keys - use environment variables
define('STRIPE_PUBLISHABLE_KEY', getenv('STRIPE_PUBLISHABLE_KEY') ?: '');
define('STRIPE_SECRET_KEY', getenv('STRIPE_SECRET_KEY') ?: '');

// Mobile Money settings
define('MOBILE_MONEY_NAME', 'Kort Godlove Fai');
define('MOBILE_MONEY_NUMBER', '652444097');

// Email settings - use environment variables
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'hello@holisticprosperityministry.org');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: '');

// Security settings
define('HASH_SALT', getenv('HASH_SALT') ?: bin2hex(random_bytes(16))); // Used for generating secure tokens
define('SESSION_TIMEOUT', 1800); // 30 minutes in seconds

// Payment processing
define('PROCESSING_FEE_PERCENTAGE', 2.9);
define('PROCESSING_FEE_FIXED', 0.30);

// Donation settings
define('MIN_DONATION_AMOUNT', 5);
define('DEFAULT_CURRENCY', 'USD');
define('DONATION_AMOUNTS', [10, 25, 50, 100, 250, 500, 1000]);

// System paths
define('ROOT_PATH', dirname(__DIR__));
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Time zone
date_default_timezone_set('America/Chicago');

// Security headers - only set if headers haven't been sent yet
if (!headers_sent()) {
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: SAMEORIGIN");
    header("X-XSS-Protection: 1; mode=block");
    header("Content-Security-Policy: default-src 'self'; script-src 'self' https://js.stripe.com https://cdn.jsdelivr.net https://code.jquery.com; style-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; img-src 'self' data:; font-src 'self' https://cdnjs.cloudflare.com; connect-src 'self' https://api.stripe.com; frame-src https://js.stripe.com");
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// Set secure cookie parameters only if session hasn't started yet
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1); // Requires HTTPS
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Strict');
}

// Error reporting (turn off in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_PATH . '/php-errors.log');

// Create necessary directories if they don't exist
$directories = [UPLOADS_PATH, LOGS_PATH];
foreach ($directories as $directory) {
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }
}

// Include database connection
require_once INCLUDES_PATH . '/db_connect.php';

// Include helper functions
require_once INCLUDES_PATH . '/functions.php';
?>
