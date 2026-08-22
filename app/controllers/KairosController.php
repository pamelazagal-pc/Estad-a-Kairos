<?php
require_once __DIR__ . '/../models/Kairos.php';

class KairosController
{
    private Kairos $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new Kairos($connection);
    }

    public function datos(): array
    {
        return [
            'kpis' => $this->model->kpis(),
            'grupos' => $this->model->grupos(),
            'alumnos' => $this->model->alumnos(),
            'incidencias' => $this->model->incidencias(),
        ];
    }

    public function procesar(string $accion): array
    {
        if ($accion === 'guardar_grupo') {
            $grado = (int)($_POST['grado'] ?? 0);
            $grupo = strtoupper(trim($_POST['grupo'] ?? ''));
            $ciclo = trim($_POST['ciclo_escolar'] ?? '');
            if ($grado < 1 || $grado > 6 || !preg_match('/^[A-Z]$/', $grupo) || $ciclo === '') {
                throw new InvalidArgumentException('El grado, la letra del grupo y el ciclo escolar son obligatorios.');
            }
            $this->model->guardarGrupo($grado, $grupo, $ciclo, $this->enteroOpcional('id_grupo'));
            return ['mensaje' => 'Grupo guardado correctamente.'];
        }
        if ($accion === 'eliminar_grupo') {
            $this->model->eliminarGrupo((int)($_POST['id_grupo'] ?? 0));
            return ['mensaje' => 'Grupo desactivado correctamente.'];
        }
        if ($accion === 'guardar_alumno') {
            $nombre = trim($_POST['nombre'] ?? '');
            $paterno = trim($_POST['apellido_paterno'] ?? '');
            $materno = trim($_POST['apellido_materno'] ?? '') ?: null;
            $edad = $this->enteroOpcional('edad');
            $grupo = (int)($_POST['id_grupo'] ?? 0);
            if ($nombre === '' || $paterno === '' || $grupo <= 0) {
                throw new InvalidArgumentException('El nombre, apellido paterno y grupo son obligatorios.');
            }
            $this->model->guardarAlumno($nombre, $paterno, $materno, $edad, $grupo, $this->enteroOpcional('id_alumno'));
            return ['mensaje' => 'Alumno guardado correctamente.'];
        }
        if ($accion === 'eliminar_alumno') {
            $this->model->eliminarAlumno((int)($_POST['id_alumno'] ?? 0));
            return ['mensaje' => 'Alumno dado de baja correctamente.'];
        }
        if ($accion === 'guardar_incidencia') {
            $alumno = (int)($_POST['id_alumno'] ?? 0);
            $contencion = trim($_POST['accion_contencion'] ?? '');
            $emocion = trim($_POST['emocion'] ?? '');
            $descripcion = trim($_POST['nota_descripcion'] ?? '');
            if ($alumno <= 0 || $contencion === '' || $emocion === '' || $descripcion === '') {
                throw new InvalidArgumentException('Completa alumno, tipo de contención, emoción y descripción.');
            }
            $maestro = $this->maestroDisponible();
            if (!$maestro) {
                throw new InvalidArgumentException('Registra al menos un maestro antes de capturar incidencias.');
            }
            $this->model->registrarIncidencia($alumno, $contencion, $emocion, $descripcion, $maestro);
            return ['mensaje' => 'Incidencia guardada correctamente.'];
        }
        throw new InvalidArgumentException('Acción no reconocida.');
    }

    private function enteroOpcional(string $campo): ?int
    {
        $valor = $_POST[$campo] ?? '';
        return $valor === '' ? null : (int)$valor;
    }

    private function maestroDisponible(): ?int
    {
        if (isset($_SESSION['maestro_id'])) {
            return (int)$_SESSION['maestro_id'];
        }
        return $this->model->primerMaestroActivo();
    }
}
?>
