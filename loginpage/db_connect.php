<?php
/*
 * File: db_connect.php
 *
 * This file establishes the connection to the MySQL database.
 * It will be included in other PHP files that need database access.
 */

// Enable error reporting for development.
// IMPORTANT: Disable or restrict this in a production environment for security.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Configure MySQLi to throw exceptions on errors, making error handling cleaner.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- Database Connection Variables ---
// These should match your XAMPP MySQL setup.
$servername = "localhost";
$username = "root";
$password = ""; // Default XAMPP password is blank
$dbname = "databaseconnect"; // The database name you defined

// --- Create and Check the Connection ---
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Set character set to utf8mb4 for proper handling of various characters
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    // If connection fails, output a JSON error and terminate script.
    // This is crucial for AJAX requests to understand the failure.
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// If the script reaches this point, the connection was successful.
// The $conn variable can now be used by any script that includes this file.
?>
