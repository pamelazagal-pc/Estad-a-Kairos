<?php
session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/RecuperacionController.php';
$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'token' => $token, 'cuenta' => null];
$controller = new RecuperacionController($connection);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resultado = $controller->restablecer($token, (string)($_POST['password'] ?? ''), (string)($_POST['password_confirmacion'] ?? ''));
    if (!$resultado['ok']) {
        $resultado['cuenta'] = $controller->prepararRestablecimiento($token)['cuenta'] ?? null;
    }
} else {
    $resultado = $controller->prepararRestablecimiento($token);
}
require_once __DIR__ . '/../../app/views/recuperacion/restablecer.php';
