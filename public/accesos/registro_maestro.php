<?php

require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../app/controllers/MaestroController.php';

$resultado = [
    'ok' => false,
    'errores' => [],
    'datos' => [],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar') {
    $controller = new MaestroController($connection);
    $resultado = $controller->registrar();
}

require_once __DIR__ . '/../../app/views/registro_maestro.php';
