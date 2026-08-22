<?php
session_start();

if (isset($_SESSION['maestro_id'])) {
    header('Location: dashboard_maestro.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/MaestroController.php';

$resultado = [
    'ok' => false,
    'errores' => [],
    'mensaje' => null,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $controller = new MaestroController($connection);
    $resultado = $controller->iniciarSesion();
    if ($resultado['ok']) {
        header('Location: dashboard_maestro.php');
        exit;
    }
}

require_once __DIR__ . '/../../../app/views/maestro/login_maestro.php';
?>
