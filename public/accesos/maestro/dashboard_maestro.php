<?php
session_start();

if (!isset($_SESSION['maestro_id'])) {
    header('Location: login_maestro.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/MaestroDashboardController.php';

$controller = new MaestroDashboardController($connection);
$resultado = $controller->ejecutar((int)$_SESSION['maestro_id']);
$datos = $resultado['datos'] ?? [];

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

require_once __DIR__ . '/../../../app/views/maestro/dashboard_maestro.php';
