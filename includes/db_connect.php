<?php
/**
 * Database Connection
 * Holistic Prosperity Ministry Payment System
 * 
 * Security implementation:
 * - Using SQLite for development/testing
 * - Using real prepared statements
 * - Setting secure connection parameters
 * - Proper error handling
 */

// Ensure this file is not accessed directly
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('HTTP/1.0 403 Forbidden');
    exit('Direct access to this file is forbidden.');
}

// Get database path from environment or use default
$dbPath = getenv('DB_PATH') ?: __DIR__ . '/../database/donations.sqlite';

// Ensure database directory exists
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

// Attempt to connect to SQLite database
try {
    // Connection options for security
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // Use real prepared statements
        PDO::ATTR_PERSISTENT => false // Don't use persistent connections
    ];
    
    // Create PDO instance for SQLite
    $pdo = new PDO("sqlite:$dbPath", null, null, $options);
    
    // Enable foreign keys in SQLite
    $pdo->exec('PRAGMA foreign_keys = ON');
    
} catch(PDOException $e) {
    // Log detailed error message for administrators
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Check if this is being run from command line
    if (php_sapi_name() === 'cli') {
        echo "Database Connection Error: Unable to connect to the database.\n";
        echo "Error details (for admin): " . $e->getMessage() . "\n";
    } else {
        // Display generic error message to web users
        // This prevents exposing sensitive database information
        die("We're experiencing technical difficulties. Please try again later or contact support.");
    }
}
?>
