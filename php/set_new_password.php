<?php
require_once 'env.php';

if (!isset($_GET['hash']) || empty($_GET['hash'])) {
    // El parámetro 'hash' no está presente o está vacío
    header('Location: login.html?result=invalid_link&message=Invalid link.');
}

try {

    // Obtener el valor del hash del parámetro GET
    $validation_hash = $_GET['hash'];


    // Crear conexión usando PDO y variables del archivo env.php
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    // Establecer el modo de error PDO en excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


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

        // Preparar la consulta SQL para obtener la "hash_date"
        $getExpirationStmt = $conn->prepare("SELECT hash_date FROM usuarios WHERE validation_hash = :validation_hash");
        $getExpirationStmt->bindParam(':validation_hash', $validation_hash);
        $getExpirationStmt->execute();

        // Obtener la "hash_date" de la consulta
        $hash_date = $getExpirationStmt->fetchColumn();

        // Calcular la diferencia de tiempo entre la fecha actual y la "hash_date"
        $timeDiff = strtotime($currentDateTime) - strtotime($hash_date);

        // Verificar si la diferencia de tiempo es menor o igual a 15 minutos (900 segundos)
        if ($timeDiff <= 900) { // 900 segundos = 15 minutos

            //Obtener datos del usuario
            $stmt = $conn->prepare("SELECT nombre, email, role FROM usuarios WHERE validation_hash = :validation_hash");
            $stmt->bindParam(':validation_hash', $validation_hash);
            $stmt->execute();
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            // creo una sesión
            session_start();
            session_regenerate_id(true); // Regenerar ID de sesión para prevenir fijación de sesión

            // Guardar los datos del usuario en la sesión
            $_SESSION['usuario'] = [
                'nombre' => $usuario['nombre'],
                'email' => $usuario['email'],
                'update_password' => TRUE
            ];

            // Actualizar el valor de validation_hash y hash_date a NULL para ese cliente
            $updateStmt = $conn->prepare("UPDATE usuarios SET validation_hash = NULL, hash_date= NULL WHERE validation_hash = :validation_hash");
            $updateStmt->bindParam(':validation_hash', $validation_hash);
            $updateStmt->execute();

            $conn = null;
            //imprimo formulario
        } else {
            // Actualizar el valor de validation_hash y hash_date a NULL para ese cliente
            $updateStmt = $conn->prepare("UPDATE usuarios SET validation_hash = NULL, hash_date= NULL WHERE validation_hash = :validation_hash");
            $updateStmt->bindParam(':validation_hash', $validation_hash);
            $updateStmt->execute();
            $conn = null;
            header('Location: login.html?result=warn&message=Expired link.');
        }
    } else {
        //El $validation_hash no existe en la tabla de usuarios
        $conn = null;
        header('Location: login.html?result=invalid_link&message=Invalid link.');
    }
} catch (PDOException $e) {
    header("Location: login.html?result=error&message=" . $e->getMessage());
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restore your password</title>
    <!--Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <!--Bootstrap icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/signup.css" type="text/css">

</head>



<body>
    <div class="container text-center" style="width: 30rem;">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-person-bounding-box"></i> Restore your password</h5>
                <form id="signup-form" action="update_password.php" method="POST">
                    <div class="row">
                        <div class="col-12">
                            <p>Hello <?php echo $usuario['nombre']; ?></p>
                            <p>Enter your new password</p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="input-group mb-3">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <div class="form-floating">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" minlength="8" maxlength="20" required>
                                    <label for="password">Password</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="d-grid gap-2">
                                <button id="submit-btn" class="btn btn-outline-dark" type="submit">RESTORE YOUR PASSWORD</button>
                                <button id="loading-btn" class="btn btn-outline-dark d-none" type="button" disabled>
                                    <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                                    <span class="visually-hidden" role="status">Loading...</span>
                                </button>
                            </div>
                        </div>
                    </div>



                </form>
            </div>
            <div id="alert"></div>
        </div>

    </div>
    </div>
    <script src="js/spinner_animation.js"></script>
    <script src="js/restore_password_alert.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</body>

</html>