<?php
require_once 'logger.php'; // Incluye tu archivo logger.php donde se inicializa Monolog

// Inicializar Monolog
$log = getLogger('app');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mibase";

// Intenta conectarte a la base de datos
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar si hubo un error de conexión
if ($conn->connect_error) {
    // Generar un registro de error de conexión
    $log->error("Error de conexión a la base de datos: " . $conn->connect_error);
    die("Error de conexión: " . $conn->connect_error);
}

// Si la conexión se establece correctamente, puedes continuar usando $conn para realizar consultas a la base de datos.
?>
