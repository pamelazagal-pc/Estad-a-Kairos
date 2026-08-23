<?php

require_once __DIR__ . '/../models/Panel.php';

class PanelController
{
    private $panelModel;

    public function __construct($connection)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->panelModel = new Panel($connection);
    }

    public function administradorAutenticado(): bool
    {
        return isset($_SESSION['administrador_id'])
            && (int) $_SESSION['administrador_id'] > 0;
    }

    public function datosAdministrador(): array
    {
        return [
            'id' => (int) ($_SESSION['administrador_id'] ?? 0),
            'nombre' => $_SESSION['administrador_nombre'] ?? 'Administrador',
            'es_principal' => (bool) ($_SESSION['administrador_principal'] ?? false),
        ];
    }

    public function resumen(): array
    {
        try {
            return [
                'ok' => true,
                'datos' => $this->panelModel->obtenerResumen(),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return [
                'ok' => false,
                'datos' => [
                    'administradores' => 0,
                    'docentes' => 0,
                    'alumnos' => 0,
                    'grupos' => 0,
                ],
                'error' => 'No fue posible cargar el resumen en este momento.',
            ];
        }
    }
}
