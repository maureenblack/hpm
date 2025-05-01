<?php
/**
 * Update Donation Links Script
 * Holistic Prosperity Ministry
 * 
 * This script updates all HTML files to ensure donation links point to donate-form.php
 */

// Define the directory to search
$directory = __DIR__;

// Get all HTML files
$htmlFiles = glob($directory . '/*.html');
$htmlFiles = array_merge($htmlFiles, glob($directory . '/ministries/*.html'));

echo "Updating donation links in HTML files...\n";

// Patterns to search for and replace
$patterns = [
    // Navigation bar donation links
    '~<a class="nav-link dropdown-toggle(?:\s+donate-btn)?" href="donate\.html" id="donateDropdown"~' => 
    '<a class="nav-link dropdown-toggle$1" href="donate-form.php" id="donateDropdown"',
    
    // Dropdown menu links
    '~<li><a class="dropdown-item" href="donate\.html#donation-form">Give Now</a></li>\s+<li><a class="dropdown-item" href="donate\.html#giving-options">~' => 
    '<li><a class="dropdown-item" href="donate-form.php">Give Now</a></li>' . PHP_EOL . '                            <li><a class="dropdown-item" href="donate.html#giving-options">',
    
    // Direct "Give Now" buttons
    '~<a href="donate\.html#donation-form" class="btn btn-(?:gold|primary)(?:\s+\w+)*">Give Now</a>~' => 
    '<a href="donate-form.php" class="btn btn-$1$2">Give Now</a>',
    
    // Hero section buttons
    '~<a href="#donation-form" class="btn btn-(?:gold|primary)(?:\s+\w+)*">Give Now</a>~' => 
    '<a href="donate-form.php" class="btn btn-$1$2">Give Now</a>'
];

// Process each file
$updatedFiles = 0;
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Apply all patterns
    foreach ($patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // If content was changed, save the file
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Updated: " . basename($file) . "\n";
        $updatedFiles++;
    }
}

echo "\nDone! Updated $updatedFiles files.\n";
echo "All donation links now point to donate-form.php\n";
?>
