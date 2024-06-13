<?php
require_once 'env.php';
require_once 'email.php';
require_once 'functions/temp_message.php';

//Validate request method
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_POST['email'])) {
        temp_message('Warn', 'Invalid request', 'warn', '../html/login.html');
        exit;
    }

    $email = $_POST['email'];
} else {
    temp_message('Warn', 'Invalid request method', 'warn', '../html/login.html');
    exit;
}


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


//check if the email exists in the "users" table
try {
    $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bindParam(':email', $email);
    $stmt_check_email->execute();
    $email_exists = $stmt_check_email->fetchColumn();
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]:Check if the email already exists in the users table signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


if (!$email_exists) {
    //The email does not exist
    $conn = null;
    temp_message('Success', "If $email matches a registered address, you will receive an email with the validation link shortly", 'success', '../html/login.html');
}


// Generate a random hash of 24 characters
$validation_hash = bin2hex(random_bytes(12));
$hash_date = date('Y-m-d H:i:s');


try {
    // Update the hash in the "users" table
    $sql_update_hash = "UPDATE users SET validation_hash = :validation_hash, hash_date = :hash_date WHERE email = :email";
    $stmt_update_hash = $conn->prepare($sql_update_hash);
    $stmt_update_hash->bindParam(':validation_hash', $validation_hash);
    $stmt_update_hash->bindParam(':hash_date', $hash_date);
    $stmt_update_hash->bindParam(':email', $email);
    $stmt_update_hash->execute();
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]:Update the hash in the users table resend_validation_code.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}

// Send email verification
$body = "Please verify your email: http://" . DOMAIN . "/LOGIN/validate_email.php?hash=$validation_hash";
send_email($body, "Check your email", $email);

$conn = null;
temp_message('Success', "If $email matches a registered address, you will receive an email with the validation link shortly", 'success', '../html/login.html');
exit;
