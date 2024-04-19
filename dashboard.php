<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
require_once 'logger.php';

session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// Incluye la configuración de la base de datos
include("db.php");

// Configura Monolog
$log = new Logger('dashboard');
$log->pushHandler(new StreamHandler('logs/dashboard.log', Logger::INFO));

// Manejar inserción de usuarios
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newUsername = htmlspecialchars($_POST['new_username']);
    $newPassword = htmlspecialchars($_POST['new_password']);

    
    // Aplicar hashing a la contraseña antes de insertarla en la base de datos
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $insertSql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param("ss", $newUsername, $hashedPassword);
    $stmt->execute();
    $stmt->close();

    // Loggea el evento de inserción de usuario
    $log->info("Nuevo usuario agregado: $newUsername");
}

// Manejar eliminación de usuarios
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $userIdToDelete = $_GET['delete'];
    $deleteSql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bind_param("i", $userIdToDelete);
    $stmt->execute();
    $stmt->close();

    // Loggea el evento de eliminación de usuario antes de la redirección
    $log->info("Usuario eliminado con ID: $userIdToDelete");
    $log->info("El usuario " . $_SESSION['username'] . " ha eliinado a usuario con id $userIdToDelete.");

    header("Location: dashboard.php");
    exit();
}

// Obtener datos de usuarios desde la base de datos
$sql = "SELECT id, username FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
    <a href="logout.php" class="btn btn-danger">Cerrar sesión</a>

    <h3 class="mt-4">Agregar Nuevo Usuario</h3>
    <form method="post">
        <div class="form-row">
            <div class="col">
                <input type="text" class="form-control" placeholder="Nombre de Usuario" name="new_username" required>
            </div>
            <div class="col">
                <input type="password" class="form-control" placeholder="Contraseña" name="new_password" required>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Agregar Usuario</button>
            </div>
        </div>
    </form>

    <hr>

    <h3 class="mt-4">Lista de Usuarios</h3>

    <?php if ($result->num_rows > 0) { ?>
        <table class="table table-bordered table-striped mt-3">
            <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Acciones</th>
            </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                    <td>
                        <a href="edit_user.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-warning btn-sm">Editar</a>
                        <a href="dashboard.php?delete=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?')">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } else { ?>
        <p>No hay usuarios registrados.</p>
    <?php } ?>

</div>

</body>
</html>

<?php
$conn->close(); 

// Cierra el registro de Monolog
$log->close();
?>
