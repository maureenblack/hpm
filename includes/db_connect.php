<?php
/**
 * Database Connection
 * Holistic Prosperity Ministry Payment System
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'donations');
define('DB_USER', 'hpm');
define('DB_PASS', 'Holistic,4123?');

// Attempt to connect to MySQL database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Set character set to utf8mb4
    $pdo->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    // Log error message
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display user-friendly error message
    die("We're experiencing technical difficulties. Please try again later or contact support.");
}
?>
