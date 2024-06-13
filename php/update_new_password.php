<?php
require_once 'env.php';
require_once 'email.php';
require_once 'functions/temp_message.php';


//Validate request method
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    temp_message('Warn', 'Invalid request method', 'warn', '../html/login.html');
    exit;
}

if (!isset($_POST['hash']) || !isset($_POST['password']) ) {
    temp_message('Warn', 'Invalid request', 'warn', '../html/login.html');
    exit;
}

// Validate password length
$password_length = strlen($_POST['password']);

if ($password_length < 8 || $password_length > 20) {
    $conn = null;
    temp_message('Warn', 'Invalid password length', 'warn', '../html/login.html');
    exit;
}

$validation_hash = filter_var($_POST['hash'], FILTER_UNSAFE_RAW);
$hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

//connection to the database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Connection to the database signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


try {
    //Verify the existence of the hash in the table
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM users WHERE validation_hash = :validation_hash");
    $stmt->bindParam(':validation_hash', $validation_hash);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Verify the existence of the hash in the table validate_email.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}

if ($result['count'] <= 0) {
    // The hash does not exist in the table
    $conn = null;
    temp_message('Warn', "Invalid token.", 'warn', '../html/login.html');
    exit;
}


// Get user data
try {
    $stmt = $conn->prepare("SELECT email, hash_date FROM users WHERE validation_hash = :validation_hash");
    $stmt->bindParam(':validation_hash', $validation_hash);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Get user data validate_email.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


$currentDateTime = date("Y-m-d H:i:s");
$timeDiff = strtotime($currentDateTime) - strtotime($user['hash_date']);

//check if the hash expired (time greater than 15 min)
if ($timeDiff >= 900) {
    $conn = null;
    temp_message('Warn', "Your link is expired, request a new one.", 'warn', '../html/login.html');
    exit;
}



try {
    // Update the value of password
    $updateStmt = $conn->prepare("UPDATE users SET password = :hashed_password WHERE email = :email");
    $updateStmt->bindParam(':hashed_password', $hashed_password);
    $updateStmt->bindParam(':email', $user['email']);
    $updateStmt->execute();

    // Update the value of validation_hash and hash_date to NULL
    $updateStmt = $conn->prepare("UPDATE users SET validation_hash = NULL, hash_date = NULL WHERE validation_hash = :validation_hash");
    $updateStmt->bindParam(':validation_hash', $validation_hash);
    $updateStmt->execute();

    $conn = null;
    temp_message('Updated password', 'You can now log in!', 'success', '../html/login.html');
    exit;
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Update the value of valid_email to TRUE and Update the value of validation_hash and hash_date to NULL validate_email.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}

?>