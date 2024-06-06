<?php
require_once 'env.php';
require_once 'email.php';


//Validate request method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'];
} else {
    echo 'invalid Request_method';
    exit;
}

// Validate password length
$password_length = strlen($raw_password);
if ($password_length < 8 || $password_length > 20) {
    $conn = null;
    echo "invalid password length";
    exit;
}


// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $conn = null;
    echo "invalid email";
    exit;
}

//connection to the database
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e) {
   echo "Database connection failed";
    $conn = null;
   
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
        echo "The user $email already exist in the data base.";
        exit; // Detener la ejecución del script si el correo ya existe en la base de datos
    }
} catch (PDOException $e) {
    echo "Error checking for duplicate email: " . $e->getMessage();
    $conn = null;
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
    echo "Error insert user: " . $e->getMessage();
    $conn = null;
}


try {
    // Insert contact user
    $sql_insert_contact = "INSERT INTO contacts (email) VALUES (:email)";
    $stmt_insert_contact = $conn->prepare($sql_insert_contact);
    $stmt_insert_contact->bindParam(':email', $email);
    $stmt_insert_contact->execute(); // Ejecución de la sentencia preparada
} catch (PDOException $e) {
    echo "Error insert contact: " . $e->getMessage();
    $conn = null;
}


// send email verification
$body = "Please verify your email: http://" . DOMAIN . "/Loging%20template/html/login.html?hash=$validation_hash";
send_email($body, "Verifique su email", $email);
$conn = null;
echo "Created user!";
