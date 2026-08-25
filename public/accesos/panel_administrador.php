<?php

session_start();
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/PanelController.php';

$panelController = new PanelController($connection);

if (!$panelController->administradorAutenticado()) {
    header('Location: login_administrador.php');
    exit;
}

$datosAdministrador = $panelController->datosAdministrador();
$resultadoResumen = $panelController->resumen();
$resumen = $resultadoResumen['datos'];
$errorResumen = $resultadoResumen['error'];
require_once __DIR__ . '/../../app/views/administrador/panel_administrador.php';
