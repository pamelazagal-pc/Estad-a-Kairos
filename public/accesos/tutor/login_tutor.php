<?php
session_start();
if (isset($_SESSION['tutor_id'])) {
    header('Location: dashboard_tutor.php');
    exit;
}
require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/controllers/TutorController.php';
$resultado = ['ok' => false, 'errores' => [], 'datos' => ['correo' => '']];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'login') {
    $controller = new TutorController($connection);
    $resultado = $controller->iniciarSesion();
    if ($resultado['ok']) {
        header('Location: dashboard_tutor.php');
        exit;
    }
}
require_once __DIR__ . '/../../../app/views/tutor/login_tutor.php';
