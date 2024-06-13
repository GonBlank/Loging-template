<?php
require_once 'functions/temp_message.php';
//Login with secure cookie parameters
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    // If the user is not logged in
    temp_message('Warn', 'You must log in to access the website', 'warn', '../html/login.html');
    exit;
} else {
    // Authorized access record
    $user = $_SESSION['user'];
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1> Welcome </h1>

    <p>Name: <?php echo $user['name']; ?></p>
    <p>Email: <?php echo $user['email']; ?></p>
    <p>Role: <?php echo $user['role']; ?></p>

    <a href="logout.php">log out</a>

</body>

</html>