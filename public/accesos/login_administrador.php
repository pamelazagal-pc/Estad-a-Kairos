<?php

session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/AdministradorController.php';

$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null];
$controller = new AdministradorController($connection);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $resultado = $controller->iniciarSesion();

    if ($resultado['ok']) {
        header('Location: ../dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/../../app/views/login_administrador.php';
