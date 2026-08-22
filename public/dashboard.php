<?php
session_start();
require_once __DIR__ . '/../config/db_connection.php';
require_once __DIR__ . '/../app/controllers/KairosController.php';

if (!isset($_SESSION['administrador_id']) && !isset($_SESSION['maestro_id'])) {
    header('Location: accesos/login_administrador.php');
    exit;
}

$controller = new KairosController($connection);
$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion !== '') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode(['ok' => true] + $controller->procesar($accion), JSON_UNESCAPED_UNICODE);
    } catch (Throwable $exception) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

if ($accion === 'datos') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'datos' => $controller->datos()], JSON_UNESCAPED_UNICODE);
    exit;
}

$datos = $controller->datos();
require_once __DIR__ . '/../app/views/dashboard.php';
?>
