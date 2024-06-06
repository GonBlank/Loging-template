<?php
require_once 'env.php';
require_once 'email.php';

// Iniciar sesión con parámetros de cookies seguros
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['usuario'])) {
    // Verificar si se recibió el campo 'password' por POST
    if (!isset($_POST['password'])) {
        header('Location: login.html?result=error&message=Bad request.');
    }

    if ($_SESSION['usuario']['update_password'] != TRUE) {
        header('Location: login.html?result=error&message=Actualizacion de password desactivada.');
        exit;
    }
    error_log("cambiando password" . print_r($_SESSION['usuario'], true));

    if (strlen($_POST['password']) < 8 || strlen($_POST['password']) > 20) {
        header('Location: login.html?result=warn&message=La contraseña debe tener entre 8 y 20 caracteres.');
        exit; // Detener la ejecución del script si la contraseña no cumple con la longitud requerida
    }

    // Generar el hash de la contraseña
    $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Crear conexión usando PDO y variables del archivo env.php
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Establecer el modo de error PDO en excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql_update_password = "UPDATE usuarios SET password = :hashed_password WHERE email = :email";
    $stmt_update_password = $conn->prepare($sql_update_password);
    $stmt_update_password->bindParam(':hashed_password', $hashed_password);
    $stmt_update_password->bindParam(':email', $_SESSION['usuario']['email']);
    $stmt_update_password->execute();

    $conn = null;

    //Enviar aviso de cambio de password
    $body = "Your password has been updated. If you do not request this change, please contact us as soon as possible.";
    send_email($body, "Your password was updated", $_SESSION['usuario']['email']);

    // Eliminar todas las variables de sesión
    $_SESSION = array();
    // Destruir la sesión
    session_destroy();

    header('Location: login.html?result=ok&message=Password updated, you can now log in.');
} else {
    header('Location: login.html?result=error&message=Bad request.');
}

?>
