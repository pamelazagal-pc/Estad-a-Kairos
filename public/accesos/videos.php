<?php
session_start();
if (!isset($_SESSION['administrador_id']) || (int)$_SESSION['administrador_id'] <= 0) {
    header('Location: login_administrador.php');
    exit;
}
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/VideoController.php';
$controller = new VideoController($connection);
$resultado = $controller->ejecutar((int)$_SESSION['administrador_id']);
require_once __DIR__ . '/../../app/views/videos.php';
