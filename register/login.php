<?php
/*
 * File: login.php
 *
 * This script handles the user login process. It verifies credentials against the database,
 * starts a session, and returns a personalized JSON response.
 */

// Start a session to store user data after successful login.
session_start();

// Include the database connection file. This file will set up $conn or die with JSON error.
require 'db_connect.php';

// Set the header to indicate the response will be in JSON format.
header('Content-Type: application/json');

// Process the request only if it's a POST request.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get email and password from the form, trimming whitespace.
    // Use null coalescing operator (??) for safety.
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please enter both email and password.']);
        exit;
    }

    // Prepare a SQL statement to safely query the database.
    // Fetch 'fullname' and 'user_type' for session storage and success message.
    $sql = "SELECT id, fullname, email, password, user_type FROM signup WHERE email = ?";
    
    try {
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            echo json_encode(['success' => false, 'message' => 'Server error: Could not prepare statement.']);
            exit;
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if a user with that email exists.
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();

            // Verify the submitted password against the hashed password in the database.
            if (password_verify($password, $user['password'])) {
                // Password is correct. Login is successful.

                // Store user info in the session to use on other pages.
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fullname']; // Use 'fullname' from DB
                $_SESSION['user_role'] = $user['user_type']; // Use 'user_type' from DB

                // Create a personalized success message.
                $success_message = "Login Successful! Welcome, " . htmlspecialchars($user['fullname']) . ".";
                echo json_encode(['success' => true, 'message' => $success_message, 'redirect_role' => $user['user_type']]);

            } else {
                // Password does not match.
                echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
            }
        } else {
            // No user found with that email.
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }

        $stmt->close();

    } catch (mysqli_sql_exception $e) {
        // Catch any database errors during query execution
        echo json_encode(['success' => false, 'message' => 'Database error during login: ' . $e->getMessage()]);
    }

} else {
    // Handle cases where the script is accessed directly.
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection (handled by db_connect.php's try-catch or at script end)
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $conn->close();
}
?>
