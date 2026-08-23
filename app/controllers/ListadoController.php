<?php

require_once __DIR__ . '/../models/Listado.php';

class ListadoController
{
    private $listadoModel;

    public function __construct($connection)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $this->listadoModel = new Listado($connection);
    }

    public function autenticado(): bool
    {
        return isset($_SESSION['administrador_id'])
            && (int) $_SESSION['administrador_id'] > 0;
    }

    public function cambiarEstado(string $tipo, int $id, string $accion): array
    {
        try {
            $this->listadoModel->cambiarEstado($tipo, $id, $accion);
            return ['ok' => true, 'mensaje' => $accion === 'desactivar' ? 'Registro desactivado correctamente.' : 'Registro reactivado correctamente.'];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'mensaje' => $exception->getMessage()];
        }
    }

    public function mostrar(string $tipo, string $filtroEstado = 'Todos'): array
    {
        try {
            return [
                'ok' => true,
                'listado' => $this->listadoModel->obtener($tipo, $filtroEstado),
                'error' => null,
            ];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return [
                'ok' => false,
                'listado' => [
                    'titulo' => ucfirst($tipo),
                    'columnas' => [],
                    'filas' => [],
                ],
                'error' => 'No fue posible cargar este listado: ' . $exception->getMessage(),
            ];
        }
    }
}
