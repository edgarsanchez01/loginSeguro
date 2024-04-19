<?php
require_once 'logger.php'; // Incluye tu archivo logger.php donde se inicializa Monolog

// Inicializar Monolog
$log = getLogger('app');

// Iniciar sesión
session_start();

if (isset($_SESSION['username'])) {
    // Generar un registro de inicio de sesión exitoso
    $log->info("El usuario " . $_SESSION['username'] . " ha iniciado sesión exitosamente.");
    // Redirigir al panel de control
    header("Location: dashboard.php");
    exit();
}

include("db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validación de entrada
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($userId, $dbUsername, $dbPassword);
        $stmt->fetch();

        // Verificar si la contraseña proporcionada coincide con la contraseña almacenada (desencriptada)
        if (password_verify($password, $dbPassword)) {
            $_SESSION['username'] = $dbUsername;
            // Generar un registro de inicio de sesión exitoso
            $log->info("El usuario " . $_SESSION['username'] . " ha iniciado sesión exitosamente.");
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Usuario o contraseña incorrectos.";
            // Generar un registro de intento de inicio de sesión fallido
            $log->warning("Intento fallido de inicio de sesión para el usuario $username.");
        }
    } else {
        $error = "Usuario o contraseña incorrectos.";
        // Generar un registro de intento de inicio de sesión fallido
        $log->warning("Intento fallido de inicio de sesión para el usuario $username.");
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2>Iniciar sesión</h2>
    <?php if (isset($error)) { ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php } ?>
    <form method="post">
        <div class="form-group">
            <label for="username">Usuario:</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña:</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Iniciar sesión</button>
    </form>
</div>

</body>
</html>
