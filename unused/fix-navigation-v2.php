<?php
/**
 * Navigation Fix Script V2
 * Holistic Prosperity Ministry
 * 
 * This script addresses critical navigation issues:
 * 1. Standardizes all donation links to point to donate-form.php
 * 2. Removes dropdown from Donate navigation item
 * 3. Ensures proper navigation structure
 */

// Define the directory to search
$directory = __DIR__;
$logFile = $directory . '/navigation-fix-log-v2.txt';
$logContent = "Navigation Fix Log V2 - " . date('Y-m-d H:i:s') . "\n";
$logContent .= "==================================================\n\n";

// Get all HTML files recursively
function getAllHtmlFiles($dir) {
    $result = [];
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            $result = array_merge($result, getAllHtmlFiles($path));
        } else if (pathinfo($path, PATHINFO_EXTENSION) === 'html') {
            $result[] = $path;
        }
    }
    
    return $result;
}

$htmlFiles = getAllHtmlFiles($directory);

$logContent .= "Found " . count($htmlFiles) . " HTML files to process.\n\n";
$logContent .= "TASK 1 & 2: DONATION LINKS AND NAVIGATION STRUCTURE\n";
$logContent .= "-------------------------------------------------\n";

// Counter for changes
$donationLinksFixed = 0;
$navigationStructureFixed = 0;
$filesProcessed = 0;

// Process each file
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileName = basename($file);
    $filesProcessed++;
    
    $fileChanges = 0;
    
    // 1. Fix all donation links - comprehensive approach
    $oldDonationLinkCount = substr_count($content, 'donate-form.php');
    
    // Replace all donate.html links with donate-form.php
    $content = str_replace('donate.html', 'donate-form.php', $content);
    
    // Find and fix any "Donate Now" or "Give Now" buttons that don't point to donate-form.php
    $donateButtonPattern = '~<a[^>]*class="[^"]*btn[^"]*"[^>]*>\s*(?:Donate|Give)(?:\s+Now)?\s*</a>~i';
    $content = preg_replace_callback($donateButtonPattern, function($matches) {
        if (strpos($matches[0], 'donate-form.php') === false) {
            return preg_replace('~href="[^"]*"~', 'href="donate-form.php"', $matches[0]);
        }
        return $matches[0];
    }, $content);
    
    // Count how many donation links were fixed
    $newDonationLinkCount = substr_count($content, 'donate-form.php');
    $donationLinksFixed += ($newDonationLinkCount - $oldDonationLinkCount);
    $fileChanges += ($newDonationLinkCount - $oldDonationLinkCount);
    
    // 2. Fix navigation structure - Remove dropdown from Donate
    // This is a more precise approach targeting the exact HTML structure
    
    // First, check if we have a dropdown for Donate
    if (preg_match('~<li class="nav-item dropdown">\s*<a class="nav-link[^"]*" href="[^"]*" id="donateDropdown"[^>]*>\s*Donate\s*</a>~s', $content)) {
        // Replace the entire dropdown structure with a simple link
        $navPattern = '~<li class="nav-item dropdown">\s*<a class="nav-link[^"]*" href="[^"]*" id="donateDropdown"[^>]*>\s*Donate\s*</a>\s*<ul class="dropdown-menu"[^>]*>.*?</ul>\s*</li>~s';
        $navReplacement = '<li class="nav-item">' . PHP_EOL . 
                          '                        <a class="nav-link" href="donate-form.php">Donate</a>' . PHP_EOL . 
                          '                    </li>';
        
        $newContent = preg_replace($navPattern, $navReplacement, $content);
        
        // If the pattern was found and replaced
        if ($newContent !== $content) {
            $content = $newContent;
            $navigationStructureFixed++;
            $fileChanges++;
        }
    }
    
    // If content was changed, save the file
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        
        $logContent .= "Updated: $fileName\n";
        $logContent .= "  - Fixed donation links: " . ($newDonationLinkCount - $oldDonationLinkCount) . "\n";
        $logContent .= "  - Updated navigation structure: " . (preg_match('~<li class="nav-item">\s*<a class="nav-link" href="donate-form\.php">Donate</a>~', $content) ? "Yes" : "No") . "\n";
    } else {
        $logContent .= "No changes needed in: $fileName\n";
    }
}

// Add summary to log
$logContent .= "\nSUMMARY\n";
$logContent .= "-------\n";
$logContent .= "Total files processed: $filesProcessed\n";
$logContent .= "Total donation links fixed: $donationLinksFixed\n";
$logContent .= "Navigation structure updated in $navigationStructureFixed files\n";
$logContent .= "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";

// Write log file
file_put_contents($logFile, $logContent);

echo "Navigation fixes completed! See $logFile for details.\n";
?>
