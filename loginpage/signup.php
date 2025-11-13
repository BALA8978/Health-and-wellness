<?php
/*
 * File: database_setup.php
 *
 * This file is a utility script to set up the MySQL database and the 'signup' table.
 * It should be run once to initialize your database schema.
 *
 * IMPORTANT: For production environments, ensure database credentials are
 * handled securely (e.g., environment variables) and this script is not
 * publicly accessible after initial setup.
 */

// --- Step 1: Database Connection Variables ---
// Replace with your actual database server credentials
$servername = "localhost"; // Or your server IP/domain
$username = "root";       // Your MySQL username
$password = "";           // Your MySQL password
$dbname = "databaseconnect"; // The name for your new database: 'databaseconnect'

// --- Step 2: Create a connection to the MySQL server ---
// This connection is to the server itself, not a specific database yet.
$conn = new mysqli($servername, $username, $password);

// Check the connection for errors
if ($conn->connect_error) {
    // If there's an error, stop the script and display the error message.
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected to MySQL server successfully.<br>";

// --- Step 3: Create the Database if it doesn't exist ---
$sql_create_db = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql_create_db) === TRUE) {
    echo "Database '$dbname' created successfully or already exists.<br>";
} else {
    // If there's an error creating the database, stop the script.
    die("Error creating database: " . $conn->error);
}

// --- Step 4: Select the new database for use ---
// After creating or confirming the database, select it for subsequent operations.
$conn->select_db($dbname);

// --- Set Character Set for the connection ---
// It's good practice to explicitly set the character set to avoid encoding issues.
if (!$conn->set_charset("utf8mb4")) {
    echo "Warning: Error loading character set utf8mb4: " . $conn->error . "<br>";
}

// --- Step 5: Define the SQL query to create the 'signup' table ---
// This table will store the information from your registration form.
// 'password' column is long enough for hashed passwords.
// 'confirm password' is NOT stored as a separate column; it's for validation during signup.
$sql_create_table = "
CREATE TABLE IF NOT EXISTS signup (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Stores the hashed password
    age INT NOT NULL,
    height INT NOT NULL,
    weight INT NOT NULL,
    user_type VARCHAR(50) NOT NULL, -- e.g., 'customer' or 'trainer'
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// --- Step 6: Execute the query to create the table ---
if ($conn->query($sql_create_table) === TRUE) {
    echo "Table 'signup' created successfully or already exists.<br>";
} else {
    // If there's an error creating the table, show the error.
    echo "Error creating table: " . $conn->error . "<br>";
}

// --- Step 7: Close the database connection ---
$conn->close();

echo "<hr><strong>Database setup is complete!</strong> Your database '$dbname' and table 'signup' are ready.";
echo "<br>You can now proceed with your 'signup.php' and 'login.php' files, ensuring they connect to 'databaseconnect'.";

?>
