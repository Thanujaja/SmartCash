<?php
// ... existing db_connect.php code ...

// Set session cookie to expire when browser closes (highly recommended for admin)
// These must be set BEFORE session_start() in any file that uses sessions
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.gc_maxlifetime', 1800); // Set garbage collection max lifetime (e.g., 30 minutes)
}

// ... rest of your db_connect.php (like defining DB_HOST, DB_USER, PDO connection) ...
/**
 * db_connect.php
 *
 * This file handles the database connection for the SmartCash application.
 * It establishes a connection to the MySQL database using PDO for security and flexibility.
 *
 * This is a critical foundational file for the entire application.
 *
 * For your HND Final Project at ATI Dehiwala SLIATE.
 *
 * IMPORTANT: In a production environment, database credentials should NEVER be hardcoded
 * directly in the file. They should be stored in environment variables or a configuration
 * file outside the web-accessible directory. For a local XAMPP setup, this is acceptable
 * for development purposes.
 */

// Database configuration for your local XAMPP setup
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Default XAMPP MySQL password is often empty. Change if you've set one.
define('DB_NAME', 'smartcash_db'); // REPLACE with your actual database name. Create this database in phpMyAdmin.


// DSN (Data Source Name) string for PDO
$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

try {
    // Create a new PDO instance
    $pdo = new PDO($dsn, DB_USER, DB_PASS);

    // Set PDO attributes for error mode and default fetch mode
    // ERRMODE_EXCEPTION: PDO will throw exceptions on errors, which is good for debugging
    // FETCH_ASSOC: Fetches results as an associative array (column_name => value)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // Optional: Echo a success message during development (remove in production)
    // echo "Database connection successful!<br>";

} catch (PDOException $e) {
    // Catch any PDO exceptions (connection errors)
    // Log the error for debugging purposes (e.g., to a file, not to the browser for security)
    error_log("Database connection error: " . $e->getMessage(), 0);

    // Display a user-friendly error message
    die("<h1>System Error</h1><p>A critical system error occurred. Please try again later or contact support.</p>");
    // In a production environment, you might just redirect or show a generic message.
}

// You can now use the $pdo object for all your database interactions.
?>