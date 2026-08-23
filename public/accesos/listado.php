<?php

session_start();

if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/ListadoController.php';

$tiposPermitidos = ['administradores', 'maestros', 'alumnos', 'grupos', 'tutores'];
$tipo = $_GET['tipo'] ?? 'administradores';

if (!in_array($tipo, $tiposPermitidos, true)) {
    $tipo = 'administradores';
}

$controller = new ListadoController($connection);
$filtroEstado = $_GET['estado'] ?? 'Todos';
$mensajeAccion = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_estado') {
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $accion = $_POST['operacion'] ?? '';
    $resultadoAccion = $controller->cambiarEstado($tipo, (int) $id, $accion);
    $mensajeAccion = $resultadoAccion['mensaje'];
    $filtroEstado = $_POST['estado_filtro'] ?? 'Todos';
}

$resultadoListado = $controller->mostrar($tipo, $filtroEstado);
require_once __DIR__ . '/../../app/views/listado.php';
