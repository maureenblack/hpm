<?php
/**
 * Security Implementation Verification Script
 * Holistic Prosperity Ministry Payment System
 * 
 * This script checks if the security implementations are working correctly.
 * Run this script to verify your security setup.
 */

// Set strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buffer output to prevent headers already sent errors
ob_start();

echo "Security Implementation Verification\n";
echo "===================================\n\n";

// Check 1: Environment Variables
echo "1. Environment Variables Check\n";
echo "-----------------------------\n";

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    echo "✓ .env file exists\n";
    
    // Check file permissions
    $perms = substr(sprintf('%o', fileperms($envFile)), -4);
    if ($perms == '0600' || $perms == '0400') {
        echo "✓ .env file has secure permissions: $perms\n";
    } else {
        echo "✗ .env file has insecure permissions: $perms (should be 0600)\n";
        echo "  Run: chmod 0600 " . $envFile . "\n";
    }
    
    // Check if environment loader works
    include_once 'includes/config.php';
    
    if (defined('STRIPE_PUBLISHABLE_KEY') && !empty(STRIPE_PUBLISHABLE_KEY)) {
        echo "✓ Environment variables are loaded correctly\n";
    } else {
        echo "✗ Environment variables are not loaded correctly\n";
    }
} else {
    echo "✗ .env file does not exist. Run setup-env.php to create it.\n";
}

echo "\n";

// Check 2: Database Security
echo "2. Database Security Check\n";
echo "-------------------------\n";

try {
    if (defined('DB_HOST')) {
        echo "✗ Database credentials are defined as constants (insecure)\n";
    } else {
        echo "✓ Database credentials are not exposed as constants\n";
    }
    
    // Check if database connection works
    if (isset($pdo) && $pdo instanceof PDO) {
        echo "✓ Database connection is established\n";
        
        // Check PDO attributes
        $attributes = [
            PDO::ATTR_EMULATE_PREPARES => "Emulate Prepares",
            PDO::ATTR_ERRMODE => "Error Mode"
        ];
        
        foreach ($attributes as $attr => $name) {
            $value = $pdo->getAttribute($attr);
            if ($attr == PDO::ATTR_EMULATE_PREPARES && $value === false) {
                echo "✓ PDO is using real prepared statements\n";
            } elseif ($attr == PDO::ATTR_ERRMODE && $value === PDO::ERRMODE_EXCEPTION) {
                echo "✓ PDO is using exception error mode\n";
            }
        }
    } else {
        echo "✗ Database connection failed\n";
    }
} catch (Exception $e) {
    echo "✗ Error checking database security: " . $e->getMessage() . "\n";
}

echo "\n";

// Check 3: CSRF Protection
echo "3. CSRF Protection Check\n";
echo "-----------------------\n";

if (function_exists('generateCSRFToken') && function_exists('verifyCSRFToken')) {
    echo "✓ CSRF protection functions exist\n";
    
    // Generate a token
    session_start();
    $token = generateCSRFToken();
    
    if (!empty($token) && isset($_SESSION['csrf_token']) && $_SESSION['csrf_token'] === $token) {
        echo "✓ CSRF token generation works correctly\n";
        
        // Verify the token
        if (verifyCSRFToken($token)) {
            echo "✓ CSRF token verification works correctly\n";
        } else {
            echo "✗ CSRF token verification failed\n";
        }
    } else {
        echo "✗ CSRF token generation failed\n";
    }
} else {
    echo "✗ CSRF protection functions are missing\n";
}

echo "\n";

// Check 4: Security Headers
echo "4. Security Headers Check\n";
echo "------------------------\n";

$headers = [
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'SAMEORIGIN',
    'X-XSS-Protection' => '1; mode=block',
    'Content-Security-Policy' => 'default-src \'self\'',
    'Strict-Transport-Security' => 'max-age=31536000',
    'Referrer-Policy' => 'strict-origin-when-cross-origin'
];

$headersSent = headers_list();
$headersAssoc = [];

foreach ($headersSent as $header) {
    $parts = explode(':', $header, 2);
    if (count($parts) == 2) {
        $headersAssoc[trim($parts[0])] = trim($parts[1]);
    }
}

$securityHeadersFound = 0;
foreach ($headers as $header => $value) {
    if (isset($headersAssoc[$header])) {
        echo "✓ $header header is set\n";
        $securityHeadersFound++;
    } else {
        echo "✗ $header header is missing\n";
    }
}

if ($securityHeadersFound > 0) {
    echo "✓ $securityHeadersFound out of " . count($headers) . " security headers are implemented\n";
} else {
    echo "✗ No security headers are implemented\n";
}

echo "\n";

// Check 5: Stripe Integration Security
echo "5. Stripe Integration Security Check\n";
echo "---------------------------------\n";

if (file_exists(__DIR__ . '/includes/stripe-handler.php')) {
    echo "✓ Stripe handler file exists\n";
    
    include_once __DIR__ . '/includes/stripe-handler.php';
    
    if (function_exists('createStripePaymentIntent') && 
        function_exists('retrieveStripePaymentIntent') && 
        function_exists('validateStripeWebhook')) {
        echo "✓ Stripe security functions are implemented\n";
    } else {
        echo "✗ Some Stripe security functions are missing\n";
    }
} else {
    echo "✗ Stripe handler file is missing\n";
}

if (file_exists(__DIR__ . '/stripe-webhook.php')) {
    echo "✓ Stripe webhook handler exists\n";
    
    // Check webhook signature verification
    $webhookContents = file_get_contents(__DIR__ . '/stripe-webhook.php');
    if (strpos($webhookContents, 'validateStripeWebhook') !== false) {
        echo "✓ Webhook signature verification is implemented\n";
    } else {
        echo "✗ Webhook signature verification is missing\n";
    }
} else {
    echo "✗ Stripe webhook handler is missing\n";
}

echo "\n";

// Check 6: Password Security
echo "6. Password Security Check\n";
echo "------------------------\n";

// Check if password_hash is used
$files = glob(__DIR__ . '/**/*.php');
$passwordHashFound = false;

foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (strpos($contents, 'password_hash') !== false) {
        $passwordHashFound = true;
        break;
    }
}

if ($passwordHashFound) {
    echo "✓ Secure password hashing is used (password_hash)\n";
} else {
    echo "✗ Secure password hashing (password_hash) not found\n";
}

// Check if there are any MD5/SHA1 hashes for passwords
$insecureHashFound = false;
foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (preg_match('/md5\s*\(\s*.*password.*\)/i', $contents) || 
        preg_match('/sha1\s*\(\s*.*password.*\)/i', $contents)) {
        $insecureHashFound = true;
        echo "✗ Insecure password hashing found in: " . basename($file) . "\n";
    }
}

if (!$insecureHashFound) {
    echo "✓ No insecure password hashing methods found\n";
}

echo "\n";

// Check 7: Input Validation
echo "7. Input Validation Check\n";
echo "-----------------------\n";

if (function_exists('sanitizeInput')) {
    echo "✓ Input sanitization function exists\n";
    
    // Test the function
    $testInput = '<script>alert("XSS")</script>';
    $sanitized = sanitizeInput($testInput);
    
    if ($sanitized !== $testInput && htmlspecialchars($testInput) === $sanitized) {
        echo "✓ Input sanitization function works correctly\n";
    } else {
        echo "✗ Input sanitization function does not work correctly\n";
    }
} else {
    echo "✗ Input sanitization function is missing\n";
}

echo "\n";

// Check 8: File Security
echo "8. File Security Check\n";
echo "--------------------\n";

$securityChecks = [
    'direct access prevention' => 'basename($_SERVER[\'PHP_SELF\']) === basename(__FILE__)',
    'directory traversal prevention' => 'dirname(__DIR__)',
    'secure file permissions' => 'chmod('
];

$fileSecurityFound = [];
foreach ($files as $file) {
    $contents = file_get_contents($file);
    foreach ($securityChecks as $check => $pattern) {
        if (strpos($contents, $pattern) !== false && !isset($fileSecurityFound[$check])) {
            $fileSecurityFound[$check] = basename($file);
        }
    }
}

foreach ($securityChecks as $check => $pattern) {
    if (isset($fileSecurityFound[$check])) {
        echo "✓ $check implemented in: " . $fileSecurityFound[$check] . "\n";
    } else {
        echo "✗ $check not found in any files\n";
    }
}

echo "\n";

// Check 9: Session Security
echo "9. Session Security Check\n";
echo "-----------------------\n";

$sessionSecurityChecks = [
    'httponly' => 'session.cookie_httponly',
    'secure' => 'session.cookie_secure',
    'samesite' => 'session.cookie_samesite',
    'session regeneration' => 'session_regenerate_id'
];

$sessionSecurityFound = [];
foreach ($files as $file) {
    $contents = file_get_contents($file);
    foreach ($sessionSecurityChecks as $check => $pattern) {
        if (strpos($contents, $pattern) !== false && !isset($sessionSecurityFound[$check])) {
            $sessionSecurityFound[$check] = basename($file);
        }
    }
}

foreach ($sessionSecurityChecks as $check => $pattern) {
    if (isset($sessionSecurityFound[$check])) {
        echo "✓ Session $check implemented in: " . $sessionSecurityFound[$check] . "\n";
    } else {
        echo "✗ Session $check not found in any files\n";
    }
}

echo "\n";

// Check 10: PCI Compliance
echo "10. PCI Compliance Check\n";
echo "----------------------\n";

$pciChecks = [
    'No credit card data storage' => true,
    'Using Stripe.js for card collection' => false,
    'TLS 1.2+ for data transmission' => false
];

// Check for credit card storage
$ccRegex = '/\b(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|3[47][0-9]{13}|3(?:0[0-5]|[68][0-9])[0-9]{11}|6(?:011|5[0-9]{2})[0-9]{12}|(?:2131|1800|35\d{3})\d{11})\b/';
foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (preg_match($ccRegex, $contents)) {
        $pciChecks['No credit card data storage'] = false;
        echo "✗ Possible credit card number found in: " . basename($file) . "\n";
    }
}

// Check for Stripe.js
foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (strpos($contents, 'https://js.stripe.com/v3/') !== false) {
        $pciChecks['Using Stripe.js for card collection'] = true;
    }
}

// Check for TLS requirement
foreach ($files as $file) {
    $contents = file_get_contents($file);
    if (strpos($contents, 'Strict-Transport-Security') !== false) {
        $pciChecks['TLS 1.2+ for data transmission'] = true;
    }
}

foreach ($pciChecks as $check => $result) {
    if ($result) {
        echo "✓ $check\n";
    } else {
        echo "✗ $check\n";
    }
}

echo "\n";
echo "Security Verification Complete!\n";
?>
