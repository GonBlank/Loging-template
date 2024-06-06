<?php
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
        $mail->SMTPDebug  = 0;                    
        $mail->isSMTP();                                            
        $mail->Host       = SMTP_SERVER;                   
        $mail->SMTPAuth   = true;                                   
        $mail->Username   = SMTP_USERNAME;                     
        $mail->Password   = SMTP_PASSWORD;                              
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         
        $mail->Port       = SMTP_PORT;                                    
        //Recipients
        $mail->setFrom(SMTP_USERNAME, 'Elipticnet');
        $mail->addAddress($client_email);
        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
    } catch (Exception $e) {
        echo "[ERROR] EMAIL";
    }
}
