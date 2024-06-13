<?php
require_once 'functions/temp_message.php';

session_start(); // Iniciar la sesión si aún no está iniciada

// Eliminar todas las variables de sesión
$_SESSION = array();

// Destruir la sesión
session_destroy();

// Limpiar las cookies de sesión si es necesario
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

// Redirigir al usuario a la página de inicio o a cualquier otra página
temp_message('Success', "Session closed correctly", 'success', '../html/login.html');
exit;
