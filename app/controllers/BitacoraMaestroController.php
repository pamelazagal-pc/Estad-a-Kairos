<?php

require_once __DIR__ . '/../models/MaestroDashboard.php';

class BitacoraMaestroController
{
    private MaestroDashboard $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new MaestroDashboard($connection);
    }

    public function ejecutar(int $idDocente): array
    {
        $filtros = [
            'id_alumno' => filter_input(INPUT_GET, 'id_alumno', FILTER_VALIDATE_INT) ?: null,
            'tipo' => trim($_GET['tipo'] ?? ''),
            'emocion' => trim($_GET['emocion'] ?? ''),
            'desde' => trim($_GET['desde'] ?? ''),
            'hasta' => trim($_GET['hasta'] ?? ''),
        ];
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'datos' => [], 'filtros' => $filtros];

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_nota') {
                $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
                $tipo = trim($_POST['tipo_nota'] ?? '');
                $emocion = trim($_POST['emocion'] ?? '');
                $accion = trim($_POST['accion_contencion'] ?? '');
                $descripcion = trim($_POST['nota_descripcion'] ?? '');

                if (!$idAlumno) $resultado['errores'][] = 'Selecciona un alumno válido.';
                if (!in_array($tipo, ['Observacion', 'Incidencia', 'Logro'], true)) $resultado['errores'][] = 'Selecciona un tipo de nota válido.';
                if ($descripcion === '') $resultado['errores'][] = 'La descripción es obligatoria.';
                if (mb_strlen($descripcion) > 2000) $resultado['errores'][] = 'La descripción no puede superar 2000 caracteres.';
                if ($tipo === 'Incidencia' && $accion === '') $resultado['errores'][] = 'Selecciona la acción de contención.';

                if ($resultado['errores'] === []) {
                    $idSesion = isset($_SESSION['sesion_temporizador_id']) ? (int)$_SESSION['sesion_temporizador_id'] : null;
                    $this->model->registrarNota($idDocente, (int)$idAlumno, $tipo, $emocion, $accion, $descripcion, $idSesion ?: null);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Registro guardado correctamente.';
                }
            }

            $datos = $this->model->obtenerDatos($idDocente);
            $resultado['datos'] = $datos;
            $resultado['datos']['historial'] = $this->model->historial($idDocente, $filtros['id_alumno'], $filtros['tipo'], $filtros['emocion'], $filtros['desde'], $filtros['hasta']);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible cargar o guardar la bitácora.';
        }

        return $resultado;
    }
}
