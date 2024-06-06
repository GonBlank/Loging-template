<?php
// Incluir el archivo env.php para obtener las variables de entorno
require_once 'env.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPmailer/Exception.php';
require 'PHPmailer/PHPMailer.php';
require 'PHPmailer/SMTP.php';



function send_email($body, $subject, $client_email)
{

    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->SMTPDebug  = 0;                    // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = SMTP_SERVER;                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = SMTP_USERNAME;                     // SMTP username
        $mail->Password   = SMTP_PASSWORD;                               // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = SMTP_PORT;                                    // TCP port to connect to
        //Recipients
        $mail->setFrom(SMTP_USERNAME, 'Elipticnet');
        $mail->addAddress($client_email); //email del cliente
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "[ERROR] EMAIL";
        //header('Location: index.html?message=email_error');
    }
}

//echo "[OK] EMAIL";
//header('Location: index.html?message=email_sent');
