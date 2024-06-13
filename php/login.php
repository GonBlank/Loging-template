<?php
require_once 'env.php';
require_once 'functions/temp_message.php';

//Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    temp_message('Warn', 'Invalid request method', 'warn', '../html/login.html');
    exit;
}

if (!isset($_POST['email']) || !isset($_POST['password'])) {
    temp_message('Warn', 'Invalid request variables', 'warn', '../html/login.html');
    exit;
}

// Generate the password hash
$raw_password = $_POST['password'];
$email = $_POST['email'];

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $conn = null;
    temp_message('Warn', 'Invalid email format', 'warn', '../html/login.html');
    exit;
}

//connection to the database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Connection to the database signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
}

// Check the users table if the email exists
try {
    $stmt = $conn->prepare("SELECT password, name, email, role FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]:Check if the email already exists in the users table login.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}

if (!$user) {
    // The email does not exist
    $conn = null;
    temp_message('Ups!', "That email/password combination does not match our records.", 'warn', '../html/login.html');
    exit;
}

//Check if the email is verified
try {
    $stmt = $conn->prepare("SELECT valid_email FROM contacts WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $valid_email = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]:Check if the email is validated login.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


if (!$valid_email['valid_email']) {
    // The email is not validated
    $conn = null;
    temp_message('Email not validated', "Please first validate your email", 'information', '../html/login.html', "If you did not receive the validation email", "resend_validation_code.html");
    exit;
}


// Verify credentials and establish session if valid
if (!password_verify($raw_password, $user['password'])) {
    // Invalid credentials
    $conn = null;
    temp_message('Ups!', "That email/password combination does not match our records.", 'warn', '../html/login.html');
    exit;
}

// Successful authentication
session_start();
session_regenerate_id(true); // Regenerate session ID to prevent session fixation

$_SESSION['user'] = [
    'name' => $user['name'],
    'email' => $user['email'],
    'role' => $user['role']
];

error_log("Authenticated user: " . print_r($_SESSION['user'], true));

// Redirect to home page
$conn = null;
header('Location: home.php');
exit;
