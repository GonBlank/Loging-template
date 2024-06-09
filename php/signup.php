<?php
require_once 'env.php';
require_once 'email.php';
require_once 'functions/temp_message.php';


//Validate request method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'];
} else {
    temp_message('Warn', 'Invalid Request_method', 'warn', '../html/login.html');
    exit;
}

// Validate password length
$password_length = strlen($raw_password);
if ($password_length < 8 || $password_length > 20) {
    $conn = null;
    temp_message('Warn', 'Invalid password length', 'warn', '../html/login.html');
    exit;
}


// Validate email
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

//check if the email already exists in the "users" table
try {
    $sql_check_email = "SELECT COUNT(*) FROM users WHERE email = :email";
    $stmt_check_email = $conn->prepare($sql_check_email);
    $stmt_check_email->bindParam(':email', $email);
    $stmt_check_email->execute();
    $email_exists = $stmt_check_email->fetchColumn();

    if ($email_exists) {
        $conn = null;
        temp_message('Information', "The email $email already exist.", 'information', '../html/login.html', "To recover your account", "restore_password.html");
        exit;
    }
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]:Check if the email already exists in the users table signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}



// Generate the password hash
$hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

// Generate a random 24-character hash to validate the email
$validation_hash = bin2hex(random_bytes(12));
$hash_date = date('Y-m-d H:i:s');

//Insert new user
try {
    $sql_insert_user = "INSERT INTO users (name, email, password, validation_hash, hash_date) VALUES (:name, :email, :password, :validation_hash, :hash_date)";
    $stmt_insert_user = $conn->prepare($sql_insert_user);
    $stmt_insert_user->bindParam(':name', $name);
    $stmt_insert_user->bindParam(':email', $email);
    $stmt_insert_user->bindParam(':password', $hashed_password);
    $stmt_insert_user->bindParam(':validation_hash', $validation_hash);
    $stmt_insert_user->bindParam(':hash_date', $hash_date);
    $stmt_insert_user->execute();
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Insert new user signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


try {
    // Insert contact user
    $sql_insert_contact = "INSERT INTO contacts (email) VALUES (:email)";
    $stmt_insert_contact = $conn->prepare($sql_insert_contact);
    $stmt_insert_contact->bindParam(':email', $email);
    $stmt_insert_contact->execute(); // Ejecución de la sentencia preparada
} catch (PDOException $e) {
    $conn = null;
    error_log("[ERROR]: Insert contact user signup.php:" . $e->getMessage());
    temp_message('Fatal error', "Database connection failed", 'error', '../html/login.html');
    exit;
}


// send email verification
$body = "Please verify your email: http://" . DOMAIN . "/Loging%20template/html/login.html?hash=$validation_hash";
send_email($body, "Verifique su email", $email);
$conn = null;
temp_message('User created!', 'Check your email to finish the registration', 'success', '../html/login.html');


