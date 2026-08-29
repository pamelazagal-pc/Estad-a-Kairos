<?php

session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/PanelController.php';
require_once __DIR__ . '/../../app/controllers/RespaldoController.php';

$panelController = new PanelController($connection);
if (!$panelController->administradorAutenticado()) {
    header('Location: login_administrador.php');
    exit;
}

$controlador = new RespaldoController($connection, $db, $user, $password, $server);
$resultado = ['ok' => true, 'mensaje' => null];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controlador->ejecutar((string)($_POST['accion'] ?? ''), $_FILES['respaldo'] ?? []);
}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['accion'] ?? '') === 'descargar') {
    $controlador->ejecutar('descargar');
}
$respaldos = $controlador->lista();
require_once __DIR__ . '/../../app/views/administrador/respaldos.php';
