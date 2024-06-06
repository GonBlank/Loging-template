<?php
// Incluir el archivo con las credenciales de la base de datos
require_once 'env.php';

try {
    // Crear conexión usando PDO y variables del archivo env.php
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Establecer el modo de error PDO en excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Obtener el valor del hash del parámetro GET y sanitizarlo
    if (!isset($_GET['hash']) || empty($_GET['hash'])) {
        header('Location: login.html?result=error&message=' . urlencode('Invalid token.'));
        exit;
    }
    $validation_hash = filter_var($_GET['hash'], FILTER_UNSAFE_RAW);
    $validation_hash = htmlspecialchars($validation_hash, ENT_QUOTES, 'UTF-8');

    // Preparar la consulta SQL para verificar la existencia del hash en la tabla
    $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM usuarios WHERE validation_hash = :validation_hash");
    $stmt->bindParam(':validation_hash', $validation_hash);
    $stmt->execute();

    // Obtener el resultado de la consulta
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el hash existe en la tabla
    if ($result['count'] > 0) {

        // Obtener la fecha y hora actual en formato DATETIME
        $currentDateTime = date("Y-m-d H:i:s");

        // Obtener datos del usuario
        $stmt = $conn->prepare("SELECT nombre, email, hash_date FROM usuarios WHERE validation_hash = :validation_hash");
        $stmt->bindParam(':validation_hash', $validation_hash);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calcular la diferencia de tiempo entre la fecha actual y la "hash_date"
        $timeDiff = strtotime($currentDateTime) - strtotime($usuario['hash_date']);

        // Verificar si la diferencia de tiempo es menor o igual a 15 minutos (900 segundos)
        if ($timeDiff <= 900) { // 900 segundos = 15 minutos

            // Iniciar transacción
            $conn->beginTransaction();

            try {
                // Actualizar el valor de valid_email a TRUE (1)
                $updateStmt = $conn->prepare("UPDATE contacts SET valid_email = TRUE WHERE email = :email");
                $updateStmt->bindParam(':email', $usuario['email']);
                $updateStmt->execute();

                // Actualizar el valor de validation_hash y hash_date a NULL
                $updateStmt = $conn->prepare("UPDATE usuarios SET validation_hash = NULL, hash_date = NULL WHERE validation_hash = :validation_hash");
                $updateStmt->bindParam(':validation_hash', $validation_hash);
                $updateStmt->execute();

                // Confirmar transacción
                $conn->commit();

                header('Location: login.html?result=ok&message=' . urlencode('You can now log in!'));
                exit;
            } catch (PDOException $e) {
                // Revertir transacción en caso de error
                $conn->rollBack();
                throw $e;
            }
        } else {
            // El hash ha expirado
            header('Location: resend_validation_code.html?result=expirated_link&message=' . urlencode('Link expired.'));
            exit;
        }
    } else {
        // El hash no existe en la tabla
        header('Location: login.html?result=error&message=' . urlencode('Invalid link.'));
        exit;
    }
} catch (PDOException $e) {
    header('Location: login.html?result=error&message=' . urlencode('Error de base de datos: ' . $e->getMessage()));
    exit;
} catch (Exception $e) {
    header('Location: login.html?result=error&message=' . urlencode('Error: ' . $e->getMessage()));
    exit;
}

// Cerrar la conexión
$conn = null;
?>
