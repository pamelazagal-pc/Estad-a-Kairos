<?php

session_start();
if (!isset($_SESSION['maestro_id']) || (int)$_SESSION['maestro_id'] <= 0) {
    header('Location: login_maestro.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/ConsejoController.php';

$controller = new ConsejoController($connection);
$resultado = $controller->ejecutar(null, (int)$_SESSION['maestro_id'], 'consejos.php');
require_once __DIR__ . '/../../../app/views/consejos.php';
