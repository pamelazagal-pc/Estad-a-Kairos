<?php
session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/RecuperacionController.php';
$tipoUsuario = strtolower(trim($_GET['tipo'] ?? $_POST['tipo'] ?? ''));
$correo = trim($_POST['correo'] ?? '');
$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'tipo_usuario' => $tipoUsuario, 'correo' => $correo];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new RecuperacionController($connection);
    $resultado = $controller->solicitar($tipoUsuario, $correo);
}
require_once __DIR__ . '/../../app/views/recuperacion/solicitar.php';
