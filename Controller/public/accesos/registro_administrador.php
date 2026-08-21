<?php

session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../app/controllers/AdministradorController.php';

if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null];
$controller = new AdministradorController($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_secundario') {
    $resultado = $controller->registrarSecundario();
}

require_once __DIR__ . '/../app/views/registro_administrador.php';
