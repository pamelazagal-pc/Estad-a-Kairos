<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['maestro_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'La sesión del maestro expiró.']);
    exit;
}

require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/SesionTemporizadorController.php';

$controller = new SesionTemporizadorController($connection);
echo json_encode($controller->procesar((int)$_SESSION['maestro_id']), JSON_UNESCAPED_UNICODE);
