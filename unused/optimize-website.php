<?php
/**
 * Website Optimization Script
 * Holistic Prosperity Ministry
 * 
 * This script performs comprehensive optimizations:
 * 1. Simplifies navigation by removing dropdowns
 * 2. Integrates payment methods display
 * 3. Cleans up unused files
 * 4. Standardizes donation flow
 */

// Define the directory to search
$directory = __DIR__;
$unusedDir = $directory . '/unused';
$logFile = $directory . '/optimization-log.txt';
$logContent = "Website Optimization Log - " . date('Y-m-d H:i:s') . "\n";
$logContent .= "==================================================\n\n";

// Create unused directory if it doesn't exist
if (!file_exists($unusedDir)) {
    mkdir($unusedDir, 0755, true);
    $logContent .= "Created unused directory: $unusedDir\n\n";
}

// Get all HTML files
function getAllHtmlFiles($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'unused') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, getAllHtmlFiles($path));
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'html' || pathinfo($path, PATHINFO_EXTENSION) === 'php') {
            $result[] = $path;
        }
    }
    
    return $result;
}

$htmlFiles = getAllHtmlFiles($directory);

$logContent .= "Found " . count($htmlFiles) . " HTML/PHP files to process.\n\n";
$logContent .= "TASK 1: SIMPLIFY NAVIGATION\n";
$logContent .= "--------------------------\n";

// Counter for changes
$navigationUpdated = 0;
$paymentMethodsIntegrated = false;
$unusedFilesMoved = 0;
$donationFlowStandardized = 0;

// New simplified navigation HTML
$newNavigation = <<<'EOD'
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
                        <a class="nav-link" href="resources.html">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="donate-form.php">Donate</a>
                    </li>
                </ul>
EOD;

// Process each file for navigation simplification
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileName = basename($file);
    
    // 1. Simplify navigation
    $navPattern = '~<ul class="navbar-nav[^>]*">.*?</ul>~s';
    if (preg_match($navPattern, $content)) {
        $content = preg_replace($navPattern, $newNavigation, $content, 1);
        $navigationUpdated++;
        $logContent .= "Updated navigation in: $fileName\n";
    }
    
    // Save changes
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
    }
}

$logContent .= "\nTASK 2: INTEGRATE PAYMENT METHODS DISPLAY\n";
$logContent .= "---------------------------------------\n";

// Extract payment gateway information from payment.html
$paymentHtmlPath = $directory . '/payment.html';
if (file_exists($paymentHtmlPath)) {
    $paymentHtml = file_get_contents($paymentHtmlPath);
    
    // Extract payment methods information
    preg_match('~<section class="payment-methods[^>]*">.*?</section>~s', $paymentHtml, $paymentMethodsSection);
    
    if (!empty($paymentMethodsSection[0])) {
        // Clean up the extracted section
        $paymentMethodsInfo = $paymentMethodsSection[0];
        
        // Integrate into donate-form.php
        $donateFormPath = $directory . '/donate-form.php';
        if (file_exists($donateFormPath)) {
            $donateForm = file_get_contents($donateFormPath);
            
            // Create a new payment methods section for the donation form
            $paymentMethodsDisplay = <<<'EOD'
<!-- Payment Methods Section -->
<section class="payment-methods-section py-4 bg-light rounded mb-4">
    <div class="container">
        <h3 class="text-center mb-4">Payment Methods</h3>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <!-- Credit/Debit Card -->
                    <div class="col">
                        <div class="card h-100 payment-method-card" data-payment-method="credit_card">
                            <div class="card-body text-center">
                                <i class="fas fa-credit-card fa-3x mb-3 text-primary"></i>
                                <h5 class="card-title">Credit/Debit Card</h5>
                                <p class="card-text">Secure payment via Stripe. All major cards accepted.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Mobile Money -->
                    <div class="col">
                        <div class="card h-100 payment-method-card" data-payment-method="mobile_money">
                            <div class="card-body text-center">
                                <i class="fas fa-mobile-alt fa-3x mb-3 text-success"></i>
                                <h5 class="card-title">Mobile Money</h5>
                                <p class="card-text">Send to Kort Godlove Fai (652444097) with WhatsApp confirmation.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Transfer -->
                    <div class="col">
                        <div class="card h-100 payment-method-card" data-payment-method="bank_transfer">
                            <div class="card-body text-center">
                                <i class="fas fa-university fa-3x mb-3 text-warning"></i>
                                <h5 class="card-title">Bank Transfer</h5>
                                <p class="card-text">Direct bank transfer to our ministry account.</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="mb-0"><strong>Note:</strong> Select your preferred payment method in the form below.</p>
                </div>
            </div>
        </div>
    </div>
</section>
EOD;
            
            // Add CSS for payment method cards
            $paymentMethodsCSS = <<<'EOD'
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
EOD;
            
            // Add JavaScript for payment method selection
            $paymentMethodsJS = <<<'EOD'
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method card selection
    const paymentMethodCards = document.querySelectorAll('.payment-method-card');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    
    // Initialize cards based on selected payment method
    function updateSelectedCard() {
        const selectedMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        paymentMethodCards.forEach(card => {
            if (card.dataset.paymentMethod === selectedMethod) {
                card.classList.add('selected');
            } else {
                card.classList.remove('selected');
            }
        });
    }
    
    // Set initial state
    updateSelectedCard();
    
    // Add click event to cards
    paymentMethodCards.forEach(card => {
        card.addEventListener('click', function() {
            const method = this.dataset.paymentMethod;
            
            // Find and select the corresponding radio button
            paymentMethodRadios.forEach(radio => {
                if (radio.value === method) {
                    radio.checked = true;
                    // Trigger change event to update form
                    radio.dispatchEvent(new Event('change'));
                }
            });
            
            // Update card selection
            updateSelectedCard();
        });
    });
    
    // Listen for changes on radio buttons
    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', updateSelectedCard);
    });
});
</script>
EOD;
            
            // Insert the payment methods section after the header
            $headerEndPos = strpos($donateForm, '</header>');
            if ($headerEndPos !== false) {
                $newDonateForm = substr($donateForm, 0, $headerEndPos + 9) . "\n\n" . 
                                $paymentMethodsDisplay . "\n\n" . 
                                substr($donateForm, $headerEndPos + 9);
                
                // Add CSS and JS
                $headEndPos = strpos($newDonateForm, '</head>');
                if ($headEndPos !== false) {
                    $newDonateForm = substr($newDonateForm, 0, $headEndPos) . 
                                    $paymentMethodsCSS . "\n" . 
                                    substr($newDonateForm, $headEndPos);
                }
                
                $bodyEndPos = strpos($newDonateForm, '</body>');
                if ($bodyEndPos !== false) {
                    $newDonateForm = substr($newDonateForm, 0, $bodyEndPos) . 
                                    $paymentMethodsJS . "\n" . 
                                    substr($newDonateForm, $bodyEndPos);
                }
                
                file_put_contents($donateFormPath, $newDonateForm);
                $paymentMethodsIntegrated = true;
                $logContent .= "Integrated payment methods display into donate-form.php\n";
            }
        }
    }
}

$logContent .= "\nTASK 3: CLEAN UP UNUSED FILES\n";
$logContent .= "----------------------------\n";

// Get all files in the directory
function getAllFiles($dir, $baseDir = '') {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..' || $file === 'unused' || $file === '.git') continue;
        
        $path = $dir . '/' . $file;
        $relativePath = $baseDir ? $baseDir . '/' . $file : $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, getAllFiles($path, $relativePath));
        } else {
            $result[] = [
                'path' => $path,
                'relative' => $relativePath,
                'referenced' => 0
            ];
        }
    }
    
    return $result;
}

$allFiles = getAllFiles($directory);
$logContent .= "Found " . count($allFiles) . " total files to analyze.\n\n";

// Check which files are referenced in other files
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    
    foreach ($allFiles as &$checkFile) {
        if (strpos($content, $checkFile['relative']) !== false) {
            $checkFile['referenced']++;
        }
    }
}

// Identify potentially unused files
$unusedFiles = [];
$potentiallyUnusedPatterns = [
    '/test[-_]/',
    '/temp[-_]/',
    '/draft[-_]/',
    '/old[-_]/',
    '/backup[-_]/',
    '/[-_]backup/',
    '/[-_]old/',
    '/[-_]draft/',
    '/[-_]temp/',
    '/[-_]test/',
    '/\.bak$/',
    '/~$/'
];

foreach ($allFiles as $file) {
    $fileName = basename($file['path']);
    $isUnused = false;
    
    // Skip important files
    if (in_array($fileName, ['index.html', 'about.html', 'ministries.html', 'contact.html', 'resources.html', 'donate-form.php'])) {
        continue;
    }
    
    // Check if file is not referenced
    if ($file['referenced'] === 0) {
        $isUnused = true;
    }
    
    // Check if file matches potentially unused patterns
    foreach ($potentiallyUnusedPatterns as $pattern) {
        if (preg_match($pattern, $fileName)) {
            $isUnused = true;
            break;
        }
    }
    
    if ($isUnused) {
        $unusedFiles[] = $file;
    }
}

// Move unused files to the unused directory
foreach ($unusedFiles as $file) {
    $fileName = basename($file['path']);
    $targetPath = $unusedDir . '/' . $fileName;
    
    // Create subdirectories if needed
    $subDir = dirname(str_replace($directory, '', $file['path']));
    if ($subDir !== '/' && $subDir !== '.') {
        $targetDir = $unusedDir . $subDir;
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        $targetPath = $targetDir . '/' . $fileName;
    }
    
    // Move the file
    if (rename($file['path'], $targetPath)) {
        $unusedFilesMoved++;
        $logContent .= "Moved unused file: " . $file['relative'] . " to " . str_replace($directory, '', $targetPath) . "\n";
    }
}

$logContent .= "\nTASK 4: STANDARDIZE DONATION FLOW\n";
$logContent .= "--------------------------------\n";

// Ensure all donation links point to donate-form.php
foreach ($htmlFiles as $file) {
    // Skip files that have been moved
    if (!file_exists($file)) {
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Replace all donation links
    $patterns = [
        '~href="donate\.html"~' => 'href="donate-form.php"',
        '~href="donate\.html#[^"]*"~' => 'href="donate-form.php"',
        '~<a[^>]*>\s*(?:Donate|Give)(?:\s+Now)?\s*</a>~i' => function($matches) {
            if (strpos($matches[0], 'donate-form.php') === false && strpos($matches[0], 'href=') !== false) {
                return preg_replace('~href="[^"]*"~', 'href="donate-form.php"', $matches[0]);
            }
            return $matches[0];
        }
    ];
    
    foreach ($patterns as $pattern => $replacement) {
        if (is_callable($replacement)) {
            $content = preg_replace_callback($pattern, $replacement, $content);
        } else {
            $content = preg_replace($pattern, $replacement, $content);
        }
    }
    
    // Save changes
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        $donationFlowStandardized++;
        $logContent .= "Standardized donation links in: " . basename($file) . "\n";
    }
}

// Add summary to log
$logContent .= "\nSUMMARY\n";
$logContent .= "-------\n";
$logContent .= "Navigation simplified in $navigationUpdated files\n";
$logContent .= "Payment methods display integrated: " . ($paymentMethodsIntegrated ? "Yes" : "No") . "\n";
$logContent .= "Unused files moved to /unused/: $unusedFilesMoved\n";
$logContent .= "Donation flow standardized in $donationFlowStandardized files\n";
$logContent .= "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";

// Write log file
file_put_contents($logFile, $logContent);

echo "Website optimization completed! See $logFile for details.\n";
?>
