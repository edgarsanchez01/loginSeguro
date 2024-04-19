<?php
session_start();

require_once 'logger.php'; // Asegúrate de incluir la autocarga de Composer

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// Inicializar Monolog para el log general
$log = new Logger('edit_user');
$log->pushHandler(new StreamHandler('logs/edit_user.log', Logger::INFO));

// Inicializar Monolog para el log de actualizaciones de usuario
$userLog = new Logger('edit_user_actions');
$userLog->pushHandler(new StreamHandler('logs/edit_user.log', Logger::INFO));

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

include("db.php");

// Obtener el nombre de usuario actual
$currentUsername = $_SESSION['username'];

// Manejar la actualización de usuarios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userIdToUpdate = $_POST['user_id'];
    $newUsername = htmlspecialchars($_POST['new_username']); // Escapar entrada del usuario
    $newPassword = $_POST['new_password']; // Nueva contraseña

    // Verificar si la contraseña no está vacía y realizar la actualización
    if (!empty($newPassword)) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT); // Encriptar la nueva contraseña
        $updateSql = "UPDATE users SET username = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ssi", $newUsername, $newPasswordHash, $userIdToUpdate);
    } else {
        // Si la contraseña está vacía, solo actualizar el nombre de usuario
        $updateSql = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newUsername, $userIdToUpdate);
    }

    $stmt->execute();
    $stmt->close();

    // Loggear el evento de actualización de usuario en el log general y en el log de actualizaciones de usuario
    $log->info("El usuario '$currentUsername' actualizó el usuario con ID: $userIdToUpdate");
    $userLog->info("El usuario '$currentUsername' actualizó el usuario con ID: $userIdToUpdate");

    header("Location: dashboard.php");
    exit();
}

// Manejar la eliminación de usuarios
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userIdToDelete = $_GET['delete'];
    $deleteSql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $userIdToDelete);
    $stmt->execute();
    $stmt->close();

    // Loggear el evento de eliminación de usuario en el log general y en el log de actualizaciones de usuario
    $log->info("El usuario '$currentUsername' eliminó el usuario con ID: $userIdToDelete");
    $userLog->info("El usuario '$currentUsername' eliminó el usuario con ID: $userIdToDelete");

    header("Location: dashboard.php");
    exit();
}

// Obtener el ID del usuario a editar desde la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$userId = $_GET['id'];

// Obtener información del usuario
$userSql = "SELECT id, username FROM users WHERE id = ?";
$stmt = $conn->prepare($userSql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$stmt->close();

if ($userResult->num_rows !== 1) {
    header("Location: dashboard.php");
    exit();
}

$user = $userResult->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Editar Usuario</h2>
    <form method="post">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
        <div class="form-group">
            <label for="new_username">Nuevo nombre de usuario:</label>
            <input type="text" class="form-control" id="new_username" name="new_username" value="<?php echo htmlspecialchars($user['username']); ?>" required> <!-- Escapar la salida -->
        </div>
        <div class="form-group">
            <label for="new_password">Nueva contraseña:</label>
            <input type="password" class="form-control" id="new_password" name="new_password">
        </div>
        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

</body>
</html>

<?php
$conn->close();
?>
