<?php

session_start();
if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/EditarController.php';

$tiposPermitidos = ['administradores', 'maestros', 'tutores', 'grupos', 'alumnos'];
$tipo = $_GET['tipo'] ?? $_POST['tipo'] ?? '';
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
if (!in_array($tipo, $tiposPermitidos, true) || $id <= 0) {
    header('Location: panel_administrador.php');
    exit;
}

$controller = new EditarController($connection);
$errores = [];
$datos = $controller->obtener($tipo, $id);
if ($datos === null) {
    header('Location: listado.php?tipo=' . urlencode($tipo));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'actualizar') {
    $resultadoActualizacion = $controller->actualizar($tipo, $id);
    if ($resultadoActualizacion['ok']) {
        header('Location: listado.php?tipo=' . urlencode($tipo));
        exit;
    }
    $errores = $resultadoActualizacion['errores'];
    $datos = array_merge($datos, $resultadoActualizacion['datos']);
}

$resultadoEdicion = ['datos' => $datos, 'errores' => $errores, 'grupos' => $tipo === 'alumnos' ? $controller->grupos() : []];
require_once __DIR__ . '/../../app/views/editar_registro.php';
