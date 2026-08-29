<?php

session_start();
if (!isset($_SESSION['administrador_id']) || (int)$_SESSION['administrador_id'] <= 0) {
    header('Location: login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/ConsejoController.php';

$controller = new ConsejoController($connection);
$resultado = $controller->ejecutar((int)$_SESSION['administrador_id'], null, 'consejos.php');
require_once __DIR__ . '/../../app/views/consejos.php';
