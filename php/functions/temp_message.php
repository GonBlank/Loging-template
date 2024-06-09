<?php

function temp_messaje($title, $message, $type, $redirection_to)
{

    // Parámetros para la cookie
    $cookieParams = [
        'title' => $title,
        'message' => $message,
        'alertClass' => $type
    ];

    // Convertir los parámetros a formato JSON
    $cookieValue = json_encode($cookieParams);

    // Establecer la cookie con los parámetros
    setcookie('temp_message', $cookieValue, time() + 30, '/'); // Cookie válida por 60 segundos
    header("Location: $redirection_to");
    exit;
}