<?php

function temp_message($title, $message, $type, $redirection_to, $extra_info = null, $link = null)
{

    // Parameters for the cookie
    $cookieParams = [
        'title' => $title,
        'message' => $message,
        'type' => $type,
        'extra_info' => $extra_info,
        'link' => $link
    ];

    // Convert parameters to JSON format
    $cookieValue = json_encode($cookieParams);

    // Set the cookie with the parameters
    setcookie('temp_message', $cookieValue, time() + 30, '/'); // Cookie valid for 30 seconds
    header("Location: $redirection_to");
    exit;
}