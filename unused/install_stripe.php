<?php
/**
 * Stripe PHP SDK Installer
 * 
 * This script downloads and installs the Stripe PHP SDK without requiring Composer
 */

echo "Stripe PHP SDK Installer\n";
echo "=======================\n\n";

// Define paths
$vendorDir = __DIR__ . '/vendor';
$stripeDir = $vendorDir . '/stripe';
$stripePhpDir = $stripeDir . '/stripe-php';
$zipFile = __DIR__ . '/stripe-php.zip';
$extractDir = __DIR__ . '/stripe-php-master';

// Create vendor directory if it doesn't exist
if (!is_dir($vendorDir)) {
    echo "Creating vendor directory...\n";
    mkdir($vendorDir, 0755, true);
}

if (!is_dir($stripeDir)) {
    mkdir($stripeDir, 0755, true);
}

// Download the latest version from GitHub
echo "Downloading Stripe PHP SDK from GitHub...\n";
$zipUrl = 'https://github.com/stripe/stripe-php/archive/refs/heads/master.zip';
$zipData = file_get_contents($zipUrl);

if ($zipData === false) {
    die("Error: Failed to download Stripe PHP SDK.\n");
}

// Save the ZIP file
file_put_contents($zipFile, $zipData);
echo "Download complete.\n";

// Extract the ZIP file
echo "Extracting ZIP file...\n";
$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "Extraction complete.\n";
    
    // Move the extracted files to the vendor directory
    echo "Installing Stripe PHP SDK...\n";
    if (is_dir($extractDir)) {
        // Create autoloader file
        $autoloaderContent = '<?php
require_once __DIR__ . "/stripe-php-master/init.php";
';
        file_put_contents($stripeDir . '/autoload.php', $autoloaderContent);
        
        // Move files
        rename($extractDir, $stripePhpDir);
        
        echo "Stripe PHP SDK installed successfully!\n";
    } else {
        echo "Error: Extraction directory not found.\n";
    }
    
    // Clean up
    if (file_exists($zipFile)) {
        unlink($zipFile);
    }
} else {
    echo "Error: Failed to extract ZIP file.\n";
}

// Create autoload.php in vendor directory
$vendorAutoloader = '<?php
require_once __DIR__ . "/stripe/autoload.php";
';
file_put_contents($vendorDir . '/autoload.php', $vendorAutoloader);

echo "\nInstallation complete!\n";
echo "You can now use the Stripe PHP SDK in your project.\n";
echo "Include the following line at the top of your PHP files:\n";
echo "require_once __DIR__ . '/vendor/autoload.php';\n";
?>
