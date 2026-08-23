<?php
session_start();

if (!isset($_SESSION['maestro_id'])) {
    header('Location: ../login_administrador.php');
    exit;
}

require_once __DIR__ . '/../../../app/views/maestro/dashboard_maestro.php';
?>
