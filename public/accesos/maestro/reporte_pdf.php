<?php
session_start();
if (!isset($_SESSION['maestro_id'])) {
    header('Location: login_maestro.php');
    exit;
}

require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/BitacoraMaestroController.php';

$controller = new BitacoraMaestroController($connection);
$resultado = $controller->ejecutar((int)$_SESSION['maestro_id']);
$datos = $resultado['datos'] ?? [];
$filtros = $resultado['filtros'] ?? [];
$errores = $resultado['errores'] ?? [];

if ($errores !== []) {
    http_response_code(500);
    echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Error de reporte | Kairos</title></head><body><h1>No fue posible generar el reporte</h1><p>' . htmlspecialchars($errores[0], ENT_QUOTES, 'UTF-8') . '</p><p><a href="reportes.php">Volver a reportes</a></p></body></html>';
    exit;
}

require_once __DIR__ . '/../../../app/views/maestro/reportes_pdf.php';
