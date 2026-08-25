<?php
session_start();
if (!isset($_SESSION['maestro_id'])) {
    header('Location: login_maestro.php');
    exit;
}
require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/BitacoraMaestroController.php';
$controller = new BitacoraMaestroController($connection);
$resultado = $controller->ejecutar((int)$_SESSION['maestro_id']);
$datos = $resultado['datos'] ?? [];
$filtros = $resultado['filtros'] ?? [];
require_once __DIR__ . '/../../../app/views/maestro/reportes.php';
