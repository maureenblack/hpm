<?php
/**
 * Navigation Fix Script
 * Holistic Prosperity Ministry
 * 
 * This script addresses critical navigation issues:
 * 1. Standardizes all donation links to point to donate-form.php
 * 2. Removes dropdown from Donate navigation item
 * 3. Fixes mobile menu dropdown functionality
 */

// Define the directory to search
$directory = __DIR__;
$logFile = $directory . '/navigation-fix-log.txt';
$logContent = "Navigation Fix Log - " . date('Y-m-d H:i:s') . "\n";
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
$logContent .= "TASK 1: DONATION BUTTON STANDARDIZATION\n";
$logContent .= "-----------------------------------------\n";

// Counter for changes
$donationLinksFixed = 0;
$navigationStructureFixed = 0;
$mobileMenuFixed = 0;

// Process each file for donation links
foreach ($htmlFiles as $file) {
    $content = file_get_contents($file);
    $originalContent = $content;
    $fileName = basename($file);
    
    // 1. Fix donation links
    $patterns = [
        // Main navigation donation links
        '~<a class="nav-link[^"]*" href="donate\.html"~' => 
        '<a class="nav-link$1" href="donate-form.php"',
        
        // Footer and other donation links
        '~href="donate\.html"~' => 'href="donate-form.php"',
        
        // Donation form links with anchors
        '~href="donate\.html#[^"]*"~' => 'href="donate-form.php"',
        
        // Button links with "donate" or "give" text
        '~<a[^>]*href="[^"]*"[^>]*>\s*(?:Donate|Give|Support)[^<]*</a>~i' => 
        function($matches) {
            // Only replace if it's not already pointing to donate-form.php
            if (strpos($matches[0], 'donate-form.php') === false) {
                return preg_replace('~href="[^"]*"~', 'href="donate-form.php"', $matches[0]);
            }
            return $matches[0];
        },
        
        // "Donate Now" or "Give Now" buttons
        '~<a[^>]*class="[^"]*btn[^"]*"[^>]*>\s*(?:Donate Now|Give Now)[^<]*</a>~i' => 
        function($matches) {
            // Only replace if it's not already pointing to donate-form.php
            if (strpos($matches[0], 'donate-form.php') === false) {
                return preg_replace('~href="[^"]*"~', 'href="donate-form.php"', $matches[0]);
            }
            return $matches[0];
        }
    ];
    
    // Apply all donation link patterns
    foreach ($patterns as $pattern => $replacement) {
        if (is_callable($replacement)) {
            $content = preg_replace_callback($pattern, $replacement, $content);
        } else {
            $content = preg_replace($pattern, $replacement, $content);
        }
    }
    
    // 2. Fix navigation structure - Remove dropdown from Donate
    $navPattern = '~(<li class="nav-item dropdown">\s*<a class="nav-link[^"]*" href="donate-form\.php"[^>]*>\s*Donate\s*</a>\s*<ul class="dropdown-menu"[^>]*>.*?</ul>\s*</li>)~s';
    $navReplacement = '<li class="nav-item">' . PHP_EOL . 
                      '                        <a class="nav-link" href="donate-form.php">Donate</a>' . PHP_EOL . 
                      '                    </li>';
    
    $content = preg_replace($navPattern, $navReplacement, $content);
    
    // 3. Fix mobile menu dropdown functionality
    // Add necessary JavaScript for mobile menu
    $mobileMenuFix = <<<'EOD'
<script>
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

    // Add mobile menu fix before closing body tag if not already present
    if (strpos($content, 'Fix mobile dropdown functionality') === false) {
        $content = str_replace('</body>', $mobileMenuFix . PHP_EOL . '</body>', $content);
        $mobileMenuFixed++;
    }
    
    // If content was changed, save the file
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        
        // Count the changes
        $donationLinkChanges = substr_count($content, 'donate-form.php') - substr_count($originalContent, 'donate-form.php');
        $donationLinksFixed += $donationLinkChanges;
        
        if (strpos($originalContent, 'dropdown-toggle') !== false && 
            strpos($originalContent, 'Donate') !== false && 
            strpos($content, '<li class="nav-item"><a class="nav-link" href="donate-form.php">Donate</a>') !== false) {
            $navigationStructureFixed++;
        }
        
        $logContent .= "Updated: $fileName\n";
        $logContent .= "  - Fixed donation links: $donationLinkChanges\n";
        $logContent .= "  - Updated navigation structure: " . (strpos($content, '<li class="nav-item"><a class="nav-link" href="donate-form.php">Donate</a>') !== false ? "Yes" : "No") . "\n";
        $logContent .= "  - Added mobile menu fix: " . (strpos($content, 'Fix mobile dropdown functionality') !== false ? "Yes" : "No") . "\n";
    } else {
        $logContent .= "No changes needed in: $fileName\n";
    }
}

// Add summary to log
$logContent .= "\nSUMMARY\n";
$logContent .= "-------\n";
$logContent .= "Total donation links fixed: $donationLinksFixed\n";
$logContent .= "Navigation structure updated in $navigationStructureFixed files\n";
$logContent .= "Mobile menu functionality fixed in $mobileMenuFixed files\n";
$logContent .= "\nCompleted at: " . date('Y-m-d H:i:s') . "\n";

// Write log file
file_put_contents($logFile, $logContent);

echo "Navigation fixes completed! See $logFile for details.\n";
?>
