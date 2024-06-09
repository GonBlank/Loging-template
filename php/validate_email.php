<?php
require_once 'env.php';
require_once 'functions/temp_message.php';

// Get the hash value of the GET parameter and sanitize it
if (!isset($_GET['hash']) || empty($_GET['hash'])) {
    temp_messaje('Warn', "Invalid token.", 'warn', '../html/login.html');
    exit;
}

$validation_hash = filter_var($_GET['hash'], FILTER_UNSAFE_RAW);
$validation_hash = htmlspecialchars($validation_hash, ENT_QUOTES, 'UTF-8');


//connection to the database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Connection to the database signup.php:" . $e->getMessage());
    temp_messaje('Fatal error', "Database connection failed", 'error', '../html/login.html');
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
    temp_messaje('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}

if ($result['count'] < 0) {
    // The hash does not exist in the table
    $conn = null;
    temp_messaje('Warn', "Invalid token.", 'warn', '../html/login.html');
    exit;
}


// Get user data
try {
    $stmt = $conn->prepare("SELECT name, email, hash_date FROM users WHERE validation_hash = :validation_hash");
    $stmt->bindParam(':validation_hash', $validation_hash);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Get user data validate_email.php:" . $e->getMessage());
    temp_messaje('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}



$currentDateTime = date("Y-m-d H:i:s");
$timeDiff = strtotime($currentDateTime) - strtotime($user['hash_date']);


//check if the hash expired (time greater than 15 min)
if ($timeDiff >= 900) {
    $conn = null;
    temp_messaje('Warn', "Link expired.", 'warn', '../html/login.html');
    exit;
}


try {
    // Update the value of valid_email to TRUE
    $updateStmt = $conn->prepare("UPDATE contacts SET valid_email = TRUE WHERE email = :email");
    $updateStmt->bindParam(':email', $user['email']);
    $updateStmt->execute();

    // Update the value of validation_hash and hash_date to NULL
    $updateStmt = $conn->prepare("UPDATE users SET validation_hash = NULL, hash_date = NULL WHERE validation_hash = :validation_hash");
    $updateStmt->bindParam(':validation_hash', $validation_hash);
    $updateStmt->execute();

    $conn = null;
    temp_messaje('Validated email', 'You can now log in!', 'success', '../html/login.html');
    exit;
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Update the value of valid_email to TRUE and Update the value of validation_hash and hash_date to NULL validate_email.php:" . $e->getMessage());
    temp_messaje('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}
