<?php

require_once __DIR__ . '/../models/AlumnoTutor.php';

class AlumnoTutorController
{
    private $modelo;

    public function __construct($connection)
    {
        $this->modelo = new AlumnoTutor($connection);
    }

    public function cargar(string $estado): array
    {
        $estados = ['Todos', 'Activo', 'Inactivo'];
        if (!in_array($estado, $estados, true)) {
            $estado = 'Todos';
        }
        return [
            'relaciones' => $this->modelo->relaciones($estado),
            'alumnos' => $this->modelo->alumnosActivos(),
            'tutores' => $this->modelo->tutoresActivos(),
            'estado' => $estado,
            'errores' => [],
            'mensaje' => null,
        ];
    }

    public function guardar(): array
    {
        $idAlumno = filter_input(INPUT_POST, 'id_alumno', FILTER_VALIDATE_INT);
        $idTutor = filter_input(INPUT_POST, 'id_tutor', FILTER_VALIDATE_INT);
        $parentesco = trim($_POST['parentesco'] ?? '');
        $esPrincipal = isset($_POST['es_principal']) && $_POST['es_principal'] === '1';
        $errores = [];

        if (!$idAlumno || $idAlumno < 1) {
            $errores[] = 'Selecciona un alumno.';
        }
        if (!$idTutor || $idTutor < 1) {
            $errores[] = 'Selecciona un tutor.';
        }
        if ($parentesco !== '' && mb_strlen($parentesco) > 50) {
            $errores[] = 'El parentesco no puede superar los 50 caracteres.';
        }

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }

        try {
            $this->modelo->guardar((int) $idAlumno, (int) $idTutor, $parentesco !== '' ? $parentesco : null, $esPrincipal);
            return ['ok' => true, 'mensaje' => 'Relación alumno-tutor guardada correctamente.', 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }

    public function cambiarEstado(): array
    {
        $idRelacion = filter_input(INPUT_POST, 'id_alumno_tutor', FILTER_VALIDATE_INT);
        $accion = $_POST['accion_estado'] ?? '';
        $estado = $accion === 'desactivar' ? 'Inactivo' : ($accion === 'reactivar' ? 'Activo' : '');
        if (!$idRelacion || $idRelacion < 1 || $estado === '') {
            return ['ok' => false, 'errores' => ['La operación de estado no es válida.']];
        }
        try {
            if (!$this->modelo->cambiarEstado((int) $idRelacion, $estado)) {
                return ['ok' => false, 'errores' => ['No fue posible cambiar el estado de la relación.']];
            }
            return ['ok' => true, 'mensaje' => 'Estado de la relación actualizado.', 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }
}
?>
