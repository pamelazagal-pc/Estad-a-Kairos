<?php

session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/AdministradorController.php';

$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null];
$controller = new AdministradorController($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'crear_principal') {
    $resultado = $controller->crearPrincipal();
}

require_once __DIR__ . '/../../app/views/crear_administrador_principal.php';
