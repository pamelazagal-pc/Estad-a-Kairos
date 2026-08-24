<?php

session_start();
if (!isset($_SESSION['administrador_id'])) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/AlumnoTutorController.php';

$controller = new AlumnoTutorController($connection);
$accion = $_POST['accion'] ?? '';
$estado = $_GET['estado'] ?? ($_POST['estado_filtro'] ?? 'Todos');
$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($accion === 'guardar') {
        $resultado = $controller->guardar();
    } elseif ($accion === 'estado') {
        $resultado = $controller->cambiarEstado();
    }
    if ($resultado['ok']) {
        header('Location: relaciones.php?estado=' . urlencode($estado) . '&mensaje=' . urlencode($resultado['mensaje']));
        exit;
    }
}

$datos = $controller->cargar($estado);
if (isset($_GET['mensaje'])) {
    $resultado['mensaje'] = $_GET['mensaje'];
}
require_once __DIR__ . '/../../app/views/alumno_tutor.php';
