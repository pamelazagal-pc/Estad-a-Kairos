<?php

session_start();

if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/RegistroController.php';

$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'datos' => []];
$controller = new RegistroController($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_grupo') {
    $resultado = $controller->grupo();

    if ($resultado['ok']) {
        header('Location: panel_administrador.php');
        exit;
    }
}

require_once __DIR__ . '/../../app/views/registro_grupo.php';
