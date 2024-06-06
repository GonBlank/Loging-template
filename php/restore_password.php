<?php
require_once 'env.php';
require_once 'email.php';

try {
    // Crear conexión usando PDO y variables del archivo env.php
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Establecer el modo de error PDO en excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los valores del formulario
        $email = $_POST['email'];

        // Validar el correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $conn = null;
            header('Location: restore_password.html?result=error&message=Invalid email format.');
            exit; // Detener la ejecución del script si el correo no es válido
        }

        // Consulta SQL para verificar si el correo existe en la tabla "usuarios"
        $sql_check_email = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bindParam(':email', $email);
        $stmt_check_email->execute();
        $email_exists = $stmt_check_email->fetchColumn();

        if ($email_exists) {
            // Generar un hash aleatorio de 24 caracteres
            $validation_hash = bin2hex(random_bytes(12)); // 12 bytes = 24 caracteres hexadecimales
            // Captura la hora en la que se crea el hash
            $hash_date = date('Y-m-d H:i:s');

            // Actualizar el hash en la tabla "usuarios" donde el correo electrónico coincida con $email
            $sql_update_hash = "UPDATE usuarios SET validation_hash = :validation_hash, hash_date = :hash_date WHERE email = :email";
            $stmt_update_hash = $conn->prepare($sql_update_hash);
            $stmt_update_hash->bindParam(':validation_hash', $validation_hash);
            $stmt_update_hash->bindParam(':hash_date', $hash_date);
            $stmt_update_hash->bindParam(':email', $email);
            $stmt_update_hash->execute();

            // Enviar verificación de email
            $body = "Para restablecer su contraseña haga click en el siguiente enlace: http://" . DOMAIN . "/LOGIN/set_new_password.php?hash=$validation_hash";
            send_email($body, "Restore your password", $email);

            // Cerrar conexión
            $conn = null;
            header("Location: restore_password.html?result=ok&message=If $email matches a registered address, you will receive an email with instructions to recover your password.");
            exit;
        } else {
            // El correo no existe en la base de datos
            $conn = null;
            header("Location: restore_password.html?result=ok&message=If $email matches a registered address, you will receive an email with instructions to recover your password.");
            exit;
        }
    } else {
        // Solicitud inválida
        error_log("Invalid request in restore_password.php");
        header('Location: restore_password.html?result=error&message=Invalid request');
        exit;
    }
} catch (PDOException $e) {
    // Guardar el error en el log del servidor
    error_log("[ERROR] restore_password.php: " . $e->getMessage());
    header("Location: restore_password.html?result=error&message=An unexpected error occurred. Please try again later.");
    exit;
}
?>
