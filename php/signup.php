<?php
// Incluir el archivo env.php para obtener las variables de entorno
require_once 'env.php';
require_once 'email.php';

try {
    // Crear conexión usando PDO y variables del archivo env.php
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Establecer el modo de error PDO en excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Verificar si se recibieron los datos del formulario
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Obtener los valores del formulario
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];
        $raw_password = $_POST['password']; // Contraseña en texto plano

        // Validar la longitud de la contraseña
        $password_length = strlen($raw_password);
        if ($password_length < 8 || $password_length > 20) {
            $conn = null;
            header('Location: signup.html?result=warn&message=' . urlencode('The password must be between 8 and 20 characters.'));
            exit; // Detener la ejecución del script si la contraseña no cumple con la longitud requerida
        }

        // Validar el correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $conn = null;
            header('Location: signup.html?result=warn&message=' . urlencode('Invalid email format.'));
            exit; // Detener la ejecución del script si el correo no es válido
        }

        // Consulta SQL para verificar si el correo ya existe en la tabla "usuarios"
        $sql_check_email = "SELECT COUNT(*) FROM usuarios WHERE email = :email";
        $stmt_check_email = $conn->prepare($sql_check_email);
        $stmt_check_email->bindParam(':email', $email);
        $stmt_check_email->execute();
        $email_exists = $stmt_check_email->fetchColumn();

        if ($email_exists) {
            $conn = null;
            header('Location: signup.html?result=registered_mail&message=' . urlencode('This email is already registered. Login here !'));
            exit; // Detener la ejecución del script si el correo ya existe en la base de datos
        }

        // Generar el hash de la contraseña
        $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

        // Generar un hash aleatorio de 24 caracteres para validar el correo
        $validation_hash = bin2hex(random_bytes(12)); // 12 bytes = 24 caracteres hexadecimales
        // Capturar la hora en la que se crea el hash
        $hash_date = date('Y-m-d H:i:s');

        // Consulta SQL para insertar un nuevo usuario
        $sql_insert_user = "INSERT INTO usuarios (nombre, email, password, validation_hash, hash_date) VALUES (:nombre, :email, :password, :validation_hash, :hash_date)";
        $stmt_insert_user = $conn->prepare($sql_insert_user);
        $stmt_insert_user->bindParam(':nombre', $nombre);
        $stmt_insert_user->bindParam(':email', $email);
        $stmt_insert_user->bindParam(':password', $hashed_password);
        $stmt_insert_user->bindParam(':validation_hash', $validation_hash);
        $stmt_insert_user->bindParam(':hash_date', $hash_date);
        $stmt_insert_user->execute();

        // Inserción en la tabla contacts
        $sql_insert_contact = "INSERT INTO contacts (email) VALUES (:email)";
        $stmt_insert_contact = $conn->prepare($sql_insert_contact);
        $stmt_insert_contact->bindParam(':email', $email);
        $stmt_insert_contact->execute(); // Ejecución de la sentencia preparada

        // Enviar verificación de email
        $body = "Por favor verifique su email: http://" . DOMAIN . "/LOGIN/validate_email.php?hash=$validation_hash";
        send_email($body, "Verifique su email", $email);

        // Redirigir después de que se haya ejecutado todo el código correctamente
        $conn = null;
        header('Location: signup.html?result=ok&message=' . urlencode('Verify your email to complete registration'));
        exit;
    } else {
        header('Location: signup.html?result=error&message=' . urlencode('Bad request.'));
        exit;
    }
} catch (PDOException $e) {
    // Cerrar la conexión en caso de error
    if ($conn) {
        $conn = null;
    }
    header("Location: signup.html?result=error&message=" . urlencode("Sorry, we are experiencing problems creating users, please try again later: " . $e->getMessage()));
    exit;
}
?>
