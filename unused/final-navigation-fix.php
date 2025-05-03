<?php
/**
 * Final Navigation Fix Script
 * Holistic Prosperity Ministry
 * 
 * This script directly fixes all navigation issues with targeted replacements
 */

// Define the directory to search
$directory = __DIR__;
$logFile = $directory . '/final-navigation-fix-log.txt';
$logContent = "Final Navigation Fix Log - " . date('Y-m-d H:i:s') . "\n";
$logContent .= "==================================================\n\n";

// Get all HTML files
$htmlFiles = glob($directory . '/*.html');
$htmlFiles = array_merge($htmlFiles, glob($directory . '/ministries/*.html'));
$htmlFiles = array_merge($htmlFiles, glob($directory . '/resources/*.html'));

$logContent .= "Found " . count($htmlFiles) . " HTML files to process.\n\n";

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
    
    $fileChanges = [];
    
    // 1. Fix all donation links to point to donate-form.php
    $oldDonationLinkCount = substr_count($content, 'donate-form.php');
    
    // Replace all donate.html links with donate-form.php
    $content = str_replace('donate.html', 'donate-form.php', $content);
    
    // Replace any donation links with anchors
    $content = preg_replace('~href="donate-form\.php#[^"]*"~', 'href="donate-form.php"', $content);
    
    // Count how many donation links were fixed
    $newDonationLinkCount = substr_count($content, 'donate-form.php');
    $linkChanges = $newDonationLinkCount - $oldDonationLinkCount;
    $donationLinksFixed += $linkChanges;
    if ($linkChanges > 0) {
        $fileChanges[] = "Fixed $linkChanges donation links";
    }
    
    // 2. Fix navigation structure - Remove dropdown from Donate
    // First pattern: Donate with dropdown-toggle class
    $pattern1 = '~<li class="nav-item dropdown">\s*<a class="nav-link dropdown-toggle[^"]*" href="[^"]*" id="donateDropdown"[^>]*>\s*Donate\s*</a>\s*<ul class="dropdown-menu"[^>]*>.*?</ul>\s*</li>~s';
    
    // Second pattern: Donate with dropdown but no dropdown-toggle class
    $pattern2 = '~<li class="nav-item dropdown">\s*<a class="nav-link[^"]*" href="[^"]*" id="donateDropdown"[^>]*>\s*Donate\s*</a>\s*<ul class="dropdown-menu"[^>]*>.*?</ul>\s*</li>~s';
    
    $replacement = '<li class="nav-item">
                        <a class="nav-link" href="donate-form.php">Donate</a>
                    </li>';
    
    $contentAfterPattern1 = preg_replace($pattern1, $replacement, $content);
    if ($contentAfterPattern1 !== $content) {
        $content = $contentAfterPattern1;
        $navigationStructureFixed++;
        $fileChanges[] = "Updated navigation structure (pattern 1)";
    }
    
    $contentAfterPattern2 = preg_replace($pattern2, $replacement, $content);
    if ($contentAfterPattern2 !== $content) {
        $content = $contentAfterPattern2;
        $navigationStructureFixed++;
        $fileChanges[] = "Updated navigation structure (pattern 2)";
    }
    
    // If content was changed, save the file
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        
        $logContent .= "Updated: $fileName\n";
        foreach ($fileChanges as $change) {
            $logContent .= "  - $change\n";
        }
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

echo "Final navigation fixes completed! See $logFile for details.\n";
?>
