<?php

session_start();

if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/RegistroController.php';

$resultado = [
    'ok' => false,
    'errores' => [],
    'mensaje' => null,
    'datos' => [],
    'grupos' => [],
];

$controller = new RegistroController($connection);
$resultado['grupos'] = $controller->gruposParaFormulario();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_alumno') {
    $resultado = $controller->alumno();

    if ($resultado['ok']) {
        header('Location: panel_administrador.php');
        exit;
    }
}

require_once __DIR__ . '/../../app/views/registro_alumno.php';
