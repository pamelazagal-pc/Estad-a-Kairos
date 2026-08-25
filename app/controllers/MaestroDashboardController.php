<?php

require_once __DIR__ . '/../models/MaestroDashboard.php';

class MaestroDashboardController
{
    private MaestroDashboard $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new MaestroDashboard($connection);
    }

    public function ejecutar(int $idDocente): array
    {
        $idGrupo = filter_input(INPUT_GET, 'id_grupo', FILTER_VALIDATE_INT) ?: (filter_input(INPUT_POST, 'id_grupo', FILTER_VALIDATE_INT) ?: null);
        $resultado = [
            'ok' => false,
            'errores' => [],
            'mensaje' => null,
            'datos' => [],
        ];

        try {
            $accion = (string)($_POST['accion'] ?? '');
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'registrar_incidencia') {
                $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
                $emocion = trim($_POST['emocion'] ?? '');
                $accionContencion = trim($_POST['accion_contencion'] ?? '');
                $descripcion = trim($_POST['descripcion'] ?? '');

                if (!$idAlumno) $resultado['errores'][] = 'Selecciona un alumno válido.';
                if ($emocion === '') $resultado['errores'][] = 'Selecciona la emoción detectada.';
                if ($accionContencion === '') $resultado['errores'][] = 'Selecciona el tipo de contención.';
                if ($descripcion === '') $resultado['errores'][] = 'Describe brevemente la situación.';
                if (mb_strlen($descripcion) > 1000) $resultado['errores'][] = 'La descripción no puede superar los 1000 caracteres.';

                if ($resultado['errores'] === []) {
                    $this->model->registrarIncidencia($idDocente, (int)$idAlumno, $emocion, $accionContencion, $descripcion);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Incidencia registrada correctamente.';
                }
            } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && $accion === 'asignar_medalla') {
                $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
                $idRecompensa = filter_input(INPUT_POST, 'id_recompensa', FILTER_VALIDATE_INT);
                $descripcion = trim($_POST['descripcion_medalla'] ?? '');
                if (!$idAlumno) $resultado['errores'][] = 'Selecciona un alumno válido.';
                if (!$idRecompensa) $resultado['errores'][] = 'Selecciona una medalla válida.';
                if ($descripcion === '') $resultado['errores'][] = 'Describe brevemente el logro reconocido.';
                if (mb_strlen($descripcion) > 1000) $resultado['errores'][] = 'La descripción no puede superar los 1000 caracteres.';
                if ($resultado['errores'] === []) {
                    $this->model->asignarMedalla($idDocente, (int)$idAlumno, (int)$idRecompensa, $descripcion);
                    $resultado['ok'] = true;
                    $resultado['mensaje'] = 'Medalla y puntos asignados correctamente.';
                }
            }

            $resultado['datos'] = $this->model->obtenerDatos($idDocente, $idGrupo, true);
            if ($resultado['datos']['maestro'] === []) {
                $resultado['errores'][] = 'La cuenta del maestro no está activa o no existe.';
            }
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible cargar el dashboard. Verifica la estructura de la base de datos.';
        }

        return $resultado;
    }
}
