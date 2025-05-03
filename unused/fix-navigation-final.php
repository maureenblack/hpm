<?php
/**
 * Final Navigation Fix Script
 * Holistic Prosperity Ministry
 * 
 * This script directly addresses all critical navigation issues:
 * 1. Standardizes all donation links to point to donate-form.php
 * 2. Removes dropdown from Donate navigation item
 * 3. Ensures proper navigation structure
 * 4. Fixes mobile menu functionality
 */

// Define the directory to search
$directory = __DIR__;
$logFile = $directory . '/navigation-fix-log-final.txt';
$logContent = "Navigation Fix Log (Final) - " . date('Y-m-d H:i:s') . "\n";
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

// Counter for changes
$donationLinksFixed = 0;
$navigationStructureFixed = 0;
$mobileMenuFixed = 0;
$filesProcessed = 0;

// Correct navigation structure for Donate
$correctDonateNav = '<li class="nav-item">
                        <a class="nav-link" href="donate-form.php">Donate</a>
                    </li>';

// Mobile menu fix JavaScript
$mobileMenuScript = <<<'EOD'
<script>
// Mobile menu dropdown fix
document.addEventListener('DOMContentLoaded', function() {
    // Fix mobile dropdown functionality
    const dropdownToggles = document.querySelectorAll('.navbar-nav .dropdown-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                e.preventDefault();
                e.stopPropagation();
                
                const parent = this.parentElement;
                const dropdown = parent.querySelector('.dropdown-menu');
                
                // Toggle the dropdown
                if (dropdown.classList.contains('show')) {
                    dropdown.classList.remove('show');
                } else {
                    // Close other open dropdowns
                    document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(menu => {
                        if (menu !== dropdown) menu.classList.remove('show');
                    });
                    dropdown.classList.add('show');
                }
            }
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.navbar-nav')) {
            document.querySelectorAll('.navbar-nav .dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
        }
    });
});
</script>
EOD;

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
    
    // Find and fix any "Donate Now" or "Give Now" buttons that don't point to donate-form.php
    $donateButtonPattern = '~<a[^>]*>\s*(?:Donate|Give)(?:\s+Now)?\s*</a>~i';
    $content = preg_replace_callback($donateButtonPattern, function($matches) {
        if (strpos($matches[0], 'donate-form.php') === false && strpos($matches[0], 'href=') !== false) {
            return preg_replace('~href="[^"]*"~', 'href="donate-form.php"', $matches[0]);
        }
        return $matches[0];
    }, $content);
    
    // Count how many donation links were fixed
    $newDonationLinkCount = substr_count($content, 'donate-form.php');
    $linkChanges = $newDonationLinkCount - $oldDonationLinkCount;
    $donationLinksFixed += $linkChanges;
    if ($linkChanges > 0) {
        $fileChanges[] = "Fixed $linkChanges donation links";
    }
    
    // 2. Fix navigation structure - Remove dropdown from Donate
    // Look for the donate dropdown pattern
    $donateDropdownPattern = '~<li class="nav-item dropdown">\s*<a class="nav-link[^"]*" href="[^"]*"[^>]*>\s*Donate\s*</a>\s*<ul class="dropdown-menu"[^>]*>.*?</ul>\s*</li>~s';
    if (preg_match($donateDropdownPattern, $content)) {
        $content = preg_replace($donateDropdownPattern, $correctDonateNav, $content);
        $navigationStructureFixed++;
        $fileChanges[] = "Updated navigation structure";
    }
    
    // 3. Fix mobile menu dropdown functionality
    if (strpos($content, 'Mobile menu dropdown fix') === false && strpos($content, '</body>') !== false) {
        $content = str_replace('</body>', $mobileMenuScript . "\n</body>", $content);
        $mobileMenuFixed++;
        $fileChanges[] = "Added mobile menu fix";
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
$logContent .= "Mobile menu functionality fixed in $mobileMenuFixed files\n";
$logContent .= "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";

// Write log file
file_put_contents($logFile, $logContent);

echo "Navigation fixes completed! See $logFile for details.\n";
?>
