<?php
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

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    // Si el usuario no ha iniciado sesión, redirigir a la página de inicio de sesión
    header("Location: login.html?result=error&message=Debe iniciar sesión para acceder a esta página.");
    exit;
} else {
    // Registro de acceso autorizado
    error_log("Acceso autorizado: Usuario autenticado - " . $_SESSION['usuario']['email']);
    $usuario = $_SESSION['usuario'];
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
    <h1> Bienvenido </h1>

    <p>Nombre: <?php echo $usuario['nombre']; ?></p>
    <p>Email: <?php echo $usuario['email']; ?></p>
    <p>Rol: <?php echo $usuario['role']; ?></p>

    <a href="logout.php">Cerrar Sesión</a>

</body>
</html>