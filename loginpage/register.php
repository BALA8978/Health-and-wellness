<?php
/*
 * File: register.php
 *
 * This script handles the user registration form submission.
 * It receives data from the registration form, validates it,
 * hashes the password, and inserts the new user into the database.
 */

// Enable error reporting for development (remove or comment out in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file. This file will set up $conn.
require_once 'db_connect.php';

// Set the header to indicate the response will be in JSON format.
header('Content-Type: application/json');

// Process the request only if it's a POST request.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Retrieve and Sanitize Form Data ---
    // Using trim() to remove any leading/trailing whitespace.
    // htmlspecialchars() is used for outputting data, not strictly for database insertion
    // but good practice if you were to echo it back.
    $fullname = trim($_POST['signupName'] ?? '');
    $email = trim($_POST['signupEmail'] ?? '');
    $password = $_POST['signupPassword'] ?? '';
    $confirm_password = $_POST['signupConfirmPassword'] ?? '';
    $age = filter_var($_POST['signupAge'] ?? '', FILTER_VALIDATE_INT);
    $height = filter_var($_POST['signupHeight'] ?? '', FILTER_VALIDATE_INT);
    $weight = filter_var($_POST['signupWeight'] ?? '', FILTER_VALIDATE_INT);
    $user_type = trim($_POST['userType'] ?? ''); // From the select dropdown

    // Initialize an array to store validation errors
    $errors = [];

    // --- Server-Side Validation ---
    if (empty($fullname)) {
        $errors[] = "Full Name is required.";
    }
    if (empty($email)) {
        $errors[] = "Email Address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid Email Address format.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if ($age === false || $age < 1) {
        $errors[] = "Valid Age is required.";
    }
    if ($height === false || $height < 1) {
        $errors[] = "Valid Height (in cm) is required.";
    }
    if ($weight === false || $weight < 1) {
        $errors[] = "Valid Weight (in kg) is required.";
    }
    if (empty($user_type) || !in_array($user_type, ['customer', 'trainer'])) {
        $errors[] = "Please select a valid User Type (Customer or Trainer).";
    }

    // Check if email already exists in the database
    if (empty($errors)) {
        try {
            $stmt_check_email = $conn->prepare("SELECT id FROM signup WHERE email = ?");
            $stmt_check_email->bind_param("s", $email);
            $stmt_check_email->execute();
            $stmt_check_email->store_result();
            if ($stmt_check_email->num_rows > 0) {
                $errors[] = "Email already registered. Please use a different email or login.";
            }
            $stmt_check_email->close();
        } catch (mysqli_sql_exception $e) {
            $errors[] = "Database error during email check: " . $e->getMessage();
        }
    }

    // If there are no validation errors, proceed with registration
    if (empty($errors)) {
        // --- Hash the password for security ---
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // --- Prepare and Execute the SQL INSERT Statement ---
        try {
            $sql = "INSERT INTO signup (fullname, email, password, age, height, weight, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssiiss", $fullname, $email, $hashed_password, $age, $height, $weight, $user_type);

            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Registration successful! You can now log in.']);
            } else {
                // This block should ideally not be reached if email uniqueness is checked
                echo json_encode(['success' => false, 'message' => 'Registration failed. Please try again.']);
            }
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            // Catch specific error for duplicate entry if not caught by num_rows check
            if ($e->getCode() == 1062) { // MySQL error code for duplicate entry
                echo json_encode(['success' => false, 'message' => 'Error: This email address is already registered.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Database error during registration: ' . $e->getMessage()]);
            }
        }
    } else {
        // Return validation errors as JSON
        echo json_encode(['success' => false, 'message' => implode("<br>", $errors)]);
    }

} else {
    // If the script is accessed directly without a POST request
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}

// Close the database connection (handled by db_connect.php's try-catch or at script end)
// The $conn object is closed automatically when the script finishes or if an exception occurs in db_connect.php
// If you want to explicitly close it here, ensure $conn is defined and not already closed.
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {
    $conn->close();
}
?>
