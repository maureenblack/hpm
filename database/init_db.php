<?php
/**
 * Database Initialization Script
 * Holistic Prosperity Ministry Payment System
 * 
 * This script initializes the SQLite database with the schema defined in setup_sqlite.sql
 */

echo "Initializing SQLite database for Holistic Prosperity Ministry Payment System\n";
echo "=====================================================================\n\n";

// Set the database path
$dbPath = __DIR__ . '/donations.sqlite';
$sqlFile = __DIR__ . '/setup_sqlite.sql';

// Check if SQL file exists
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile\n");
}

// Read SQL file
$sql = file_get_contents($sqlFile);
if (!$sql) {
    die("Error: Could not read SQL file\n");
}

try {
    // Create/connect to SQLite database
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Enable foreign keys
    $pdo->exec('PRAGMA foreign_keys = ON');
    
    echo "Connected to SQLite database at: $dbPath\n\n";
    
    // Execute SQL statements
    $pdo->exec($sql);
    
    echo "Database schema created successfully!\n";
    
    // Set secure file permissions for the database file
    chmod($dbPath, 0600);
    echo "Set secure file permissions (0600) for database file\n";
    
    // Verify tables were created
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nCreated tables:\n";
    foreach ($tables as $table) {
        if ($table !== 'sqlite_sequence') {
            echo "- $table\n";
        }
    }
    
    echo "\nDatabase initialization complete!\n";
    echo "You can now run the web server to test the payment system.\n";
    
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage() . "\n");
}
?>
