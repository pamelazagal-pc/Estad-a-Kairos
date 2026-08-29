<?php
session_start();
if (!isset($_SESSION['administrador_id']) || (int)$_SESSION['administrador_id'] <= 0) {
    header('Location: login_administrador.php');
    exit;
}
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/ReporteAdministradorController.php';
$controller = new ReporteAdministradorController($connection);
$resultado = $controller->ejecutar();
if (($resultado['errores'] ?? []) !== []) {
    http_response_code(500);
    echo '<!doctype html><html lang="es"><head><meta charset="UTF-8"><title>Error de reporte | Kairos</title></head><body><h1>No fue posible generar el reporte</h1><p>' . htmlspecialchars($resultado['errores'][0], ENT_QUOTES, 'UTF-8') . '</p><p><a href="reporte_administrador.php">Volver al reporte</a></p></body></html>';
    exit;
}
require_once __DIR__ . '/../../app/views/administrador/reporte_administrador_pdf.php';
