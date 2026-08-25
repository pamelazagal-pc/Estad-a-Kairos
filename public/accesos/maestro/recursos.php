<?php
session_start();
if (!isset($_SESSION['maestro_id'])) {
    header('Location: login_maestro.php');
    exit;
}
require_once __DIR__ . '/../../../config/db_connection.php';
require_once __DIR__ . '/../../../app/models/Video.php';
require_once __DIR__ . '/../../../app/models/Lectura.php';
$videoModel = new Video($connection);
$lecturaModel = new Lectura($connection);
$videos = $videoModel->listar('Activo')['filas'] ?? [];
$lecturas = $lecturaModel->listarActivas();
$actividades = array_values(array_filter($videos, static function (array $video): bool {
    return stripos((string)($video['categoria'] ?? ''), 'actividad') !== false;
}));
require_once __DIR__ . '/../../../app/views/maestro/recursos.php';
