<?php
session_start();
if (!isset($_SESSION['tutor_id']) || (int)$_SESSION['tutor_id'] <= 0) {
    header('Location: login_tutor.php');
    exit;
}
require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/TutorController.php';
$idAlumno = filter_input(INPUT_GET, 'id_alumno', FILTER_VALIDATE_INT) ?: null;
$controller = new TutorController($connection);
$resultado = $controller->obtenerDashboard((int)$_SESSION['tutor_id'], $idAlumno);
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
$datos = $resultado['datos'] ?? [];
require_once __DIR__ . '/../../../app/views/tutor/dashboard_tutor.php';
