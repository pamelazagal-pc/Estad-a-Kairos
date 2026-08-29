<?php

require_once __DIR__ . '/../models/ReporteAdministrador.php';

class ReporteAdministradorController
{
    private ReporteAdministrador $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new ReporteAdministrador($connection);
    }

    public function ejecutar(): array
    {
        $filtros = [
            'id_grupo' => filter_input(INPUT_GET, 'id_grupo', FILTER_VALIDATE_INT) ?: null,
            'id_docente' => filter_input(INPUT_GET, 'id_docente', FILTER_VALIDATE_INT) ?: null,
            'tipo' => trim($_GET['tipo'] ?? ''),
            'emocion' => trim($_GET['emocion'] ?? ''),
            'desde' => trim($_GET['desde'] ?? ''),
            'hasta' => trim($_GET['hasta'] ?? ''),
        ];
        $resultado = ['filtros' => $filtros, 'filtros_catalogo' => ['grupos' => [], 'docentes' => []], 'reporte' => [], 'errores' => []];
        try {
            $resultado['filtros_catalogo'] = $this->model->obtenerFiltros();
            $resultado['reporte'] = $this->model->obtenerReporte($filtros);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible cargar el reporte administrativo.';
        }
        return $resultado;
    }
}
