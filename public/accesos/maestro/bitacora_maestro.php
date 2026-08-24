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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($resultado['ok'] ?? false)) {
    $_SESSION['bitacora_flash'] = $resultado['mensaje'] ?? 'Registro guardado correctamente.';
    header('Location: bitacora_maestro.php');
    exit;
}

if (!empty($_SESSION['bitacora_flash'])) {
    $resultado['mensaje'] = $_SESSION['bitacora_flash'];
    unset($_SESSION['bitacora_flash']);
}

$datos = $resultado['datos'] ?? [];
require_once __DIR__ . '/../../../app/views/maestro/bitacora_maestro.php';
