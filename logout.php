<?php
require_once 'logger.php'; // Incluye tu archivo logger.php donde se inicializa Monolog

// Inicializar Monolog
$log = getLogger('app');

session_start();
session_destroy();

// Generar un registro de cierre de sesión
$log->info("El usuario ha cerrado sesión.");

header("Location: index.php");
exit();
?>
