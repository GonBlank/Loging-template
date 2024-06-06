<?php
require_once 'env.php';
try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        if (!isset($_POST['email']) || !isset($_POST['password'])) {
            // Validar si email y password están seteados
            header('Location: login.html?result=error&message=' . urlencode('Bad request.'));
            exit;
        }

        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        // Validar que el correo electrónico tenga formato válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header('Location: login.html?result=warn&message=' . urlencode('El correo electrónico no tiene formato válido.'));
            exit; // Detener la ejecución del script si el correo no es válido
        }

        // Crear conexión usando PDO y variables del archivo env.php
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        // Establecer el modo de error PDO en excepción
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consultar en la tabla usuarios si el correo existe
        $stmt = $conn->prepare("SELECT password, nombre, email, role FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            // El correo no existe
            $conn = null;
            header('Location: login.html?result=error&message=' . urlencode('Sorry, that email/password combination does not match our records.'));
            exit;
        }

        $stmt = $conn->prepare("SELECT valid_email FROM contacts WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['valid_email'] != 1) {
            // El email no está validado (valid_email es false o null)
            $conn = null;
            header('Location: login.html?result=email_not_validated&message=' . urlencode('Please, Validate your email to log in.'));
            exit;
        }

        // Verificar las credenciales y establecer la sesión si son válidas
        if (password_verify($password, $usuario['password'])) {

            // Autenticación exitosa
            session_start();
            session_regenerate_id(true); // Regenerar ID de sesión para prevenir fijación de sesión

            // Guardar los datos del usuario en la sesión
            $_SESSION['usuario'] = [
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'role' => $usuario['role']
            ];

            error_log("Usuario autenticado: " . print_r($_SESSION['usuario'], true));

            // Redirigir a la página de inicio
            $conn = null;
            header('Location: home.php');
            exit;
        } else {
            // Credenciales inválidas
            $conn = null;
            header('Location: login.html?result=error&message=' . urlencode('Sorry, that email/password combination does not match our records.'));
            exit;
        }
    } else {
        header('Location: login.html?result=error&message=' . urlencode('Bad request.'));
        exit;
    }
} catch (PDOException $e) {
    if ($conn) {
        $conn = null;
    }
    header("Location: login.html?result=error&message=" . urlencode($e->getMessage()));
    exit;
} catch (Exception $e) {
    header('Location: login.html?result=error&message=' . urlencode('Error: ' . $e->getMessage()));
    exit;
}
?>
