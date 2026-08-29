<?php

require_once __DIR__ . '/../models/MaestroDashboard.php';

class BitacoraMaestroController
{
    private MaestroDashboard $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new MaestroDashboard($connection);
    }

    public function ejecutar(int $idDocente, bool $incluirReporte = false): array
    {
        $estadoFiltro = (string)($_GET['estado'] ?? 'Activo');
        if (!in_array($estadoFiltro, ['Activo', 'Archivado', 'Todos'], true)) {
            $estadoFiltro = 'Activo';
        }

        $filtros = [
            'id_grupo' => filter_input(INPUT_GET, 'id_grupo', FILTER_VALIDATE_INT) ?: null,
            'id_alumno' => filter_input(INPUT_GET, 'id_alumno', FILTER_VALIDATE_INT) ?: null,
            'tipo' => trim($_GET['tipo'] ?? ''),
            'emocion' => trim($_GET['emocion'] ?? ''),
            'desde' => trim($_GET['desde'] ?? ''),
            'hasta' => trim($_GET['hasta'] ?? ''),
            'estado' => $estadoFiltro,
        ];

        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'datos' => [], 'filtros' => $filtros];

        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar_nota') {
                $idNota = filter_input(INPUT_POST, 'id_nota', FILTER_VALIDATE_INT);
                $tipo = trim($_POST['tipo_nota'] ?? '');
                $emocion = trim($_POST['emocion'] ?? '');
                $accion = trim($_POST['accion_contencion'] ?? '');
                $descripcion = trim($_POST['nota_descripcion'] ?? '');
                if (!$idNota) $resultado['errores'][] = 'La nota seleccionada no es válida.';
                if ($resultado['errores'] === []) {
                    $this->model->editarNota($idDocente, (int)$idNota, $tipo, $emocion, $accion, $descripcion);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Registro actualizado correctamente.';
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'cambiar_estado_nota') {
                $idNota = filter_input(INPUT_POST, 'id_nota', FILTER_VALIDATE_INT);
                $operacion = trim($_POST['operacion'] ?? '');
                if (!$idNota) $resultado['errores'][] = 'La nota seleccionada no es válida.';
                if (!in_array($operacion, ['archivar', 'restaurar'], true)) $resultado['errores'][] = 'La operación solicitada no es válida.';
                if ($resultado['errores'] === []) {
                    $this->model->cambiarEstadoNota($idDocente, (int)$idNota, $operacion);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = $operacion === 'archivar'
                        ? 'Registro archivado correctamente.'
                        : 'Registro restaurado correctamente.';
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_medalla') {
                $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
                $idRecompensa = filter_input(INPUT_POST, 'id_recompensa', FILTER_VALIDATE_INT);
                $descripcion = trim($_POST['nota_descripcion'] ?? '');
                $notificarTutor = ($_POST['notificar_tutor'] ?? '') === '1';
                if (!$idAlumno) $resultado['errores'][] = 'Selecciona un alumno válido.';
                if (!$idRecompensa) $resultado['errores'][] = 'Selecciona una medalla válida.';
                if ($descripcion === '') $resultado['errores'][] = 'La descripción del logro es obligatoria.';
                if (mb_strlen($descripcion) > 1000) $resultado['errores'][] = 'La descripción no puede superar 1000 caracteres.';
                if ($resultado['errores'] === []) {
                    $idSesion = isset($_SESSION['sesion_temporizador_id']) ? (int)$_SESSION['sesion_temporizador_id'] : null;
                    $this->model->asignarMedalla($idDocente, (int)$idAlumno, (int)$idRecompensa, $descripcion, $idSesion ?: null, $notificarTutor);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Medalla asignada y puntos acumulados correctamente.';
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'registrar_nota') {
                $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
                $tipo = trim($_POST['tipo_nota'] ?? '');
                $emocion = trim($_POST['emocion'] ?? '');
                $accion = trim($_POST['accion_contencion'] ?? '');
                $descripcion = trim($_POST['nota_descripcion'] ?? '');
                $notificarTutor = ($_POST['notificar_tutor'] ?? '') === '1';

                if (!$idAlumno) $resultado['errores'][] = 'Selecciona un alumno válido.';
                if (!in_array($tipo, ['Observacion', 'Incidencia', 'Logro'], true)) $resultado['errores'][] = 'Selecciona un tipo de nota válido.';
                if ($descripcion === '') $resultado['errores'][] = 'La descripción es obligatoria.';
                if (mb_strlen($descripcion) > 2000) $resultado['errores'][] = 'La descripción no puede superar 2000 caracteres.';
                if ($tipo === 'Incidencia' && $accion === '') $resultado['errores'][] = 'Selecciona la acción de contención.';

                if ($resultado['errores'] === []) {
                    $idSesion = isset($_SESSION['sesion_temporizador_id']) ? (int)$_SESSION['sesion_temporizador_id'] : null;
                    $this->model->registrarNota($idDocente, (int)$idAlumno, $tipo, $emocion, $accion, $descripcion, $idSesion ?: null, $notificarTutor);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Registro guardado correctamente.';
                }
            }

            $datos = $this->model->obtenerDatos($idDocente);
            $resultado['datos'] = $datos;
            if (isset($_GET['editar'])) {
                $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
                if ($idEditar) {
                    $notaEdicion = $this->model->obtenerNotaParaEditar($idDocente, $idEditar);
                    if ($notaEdicion && $notaEdicion['estado'] === 'Activo') {
                        $resultado['datos']['nota_edicion'] = $notaEdicion;
                    }
                }
            }
            $resultado['datos']['historial'] = $this->model->historial($idDocente, $filtros['id_alumno'], $filtros['tipo'], $filtros['emocion'], $filtros['desde'], $filtros['hasta'], null, $filtros['estado']);
            if ($incluirReporte) $resultado['datos']['reporte'] = $this->model->obtenerReporte($idDocente, $filtros);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible cargar o guardar la bitácora.';
        }

        return $resultado;
    }
}
