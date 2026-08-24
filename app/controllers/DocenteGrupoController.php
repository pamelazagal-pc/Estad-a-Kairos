<?php

require_once __DIR__ . '/../models/DocenteGrupo.php';

class DocenteGrupoController
{
    private $modelo;

    public function __construct($connection)
    {
        $this->modelo = new DocenteGrupo($connection);
    }

    public function cargar(string $estado): array
    {
        if (!in_array($estado, ['Todos', 'Activo', 'Inactivo'], true)) {
            $estado = 'Todos';
        }
        return [
            'asignaciones' => $this->modelo->listar($estado),
            'docentes' => $this->modelo->docentesActivos(),
            'grupos' => $this->modelo->gruposActivos(),
            'estado' => $estado,
        ];
    }

    public function guardar(): array
    {
        $idDocente = filter_input(INPUT_POST, 'id_docente', FILTER_VALIDATE_INT);
        $idGrupo = filter_input(INPUT_POST, 'id_grupo', FILTER_VALIDATE_INT);
        $ciclo = trim($_POST['ciclo_escolar'] ?? '');
        $esTitular = isset($_POST['es_titular']) && $_POST['es_titular'] === '1';
        $errores = [];
        if (!$idDocente || $idDocente < 1) $errores[] = 'Selecciona un maestro.';
        if (!$idGrupo || $idGrupo < 1) $errores[] = 'Selecciona un grupo.';
        if ($ciclo === '' || strlen($ciclo) > 20) $errores[] = 'Escribe un ciclo escolar válido de máximo 20 caracteres.';
        if ($errores !== []) return ['ok' => false, 'errores' => $errores];
        try {
            $this->modelo->guardar((int)$idDocente, (int)$idGrupo, $ciclo, $esTitular);
            return ['ok' => true, 'mensaje' => 'Maestro asignado al grupo correctamente.', 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }

    public function cambiarEstado(): array
    {
        $id = filter_input(INPUT_POST, 'id_docente_grupo', FILTER_VALIDATE_INT);
        $accion = $_POST['accion_estado'] ?? '';
        $estado = $accion === 'desactivar' ? 'Inactivo' : ($accion === 'reactivar' ? 'Activo' : '');
        if (!$id || !$estado) return ['ok' => false, 'errores' => ['La operación no es válida.']];
        try {
            if (!$this->modelo->cambiarEstado((int)$id, $estado)) return ['ok' => false, 'errores' => ['No se pudo actualizar la asignación.']];
            return ['ok' => true, 'mensaje' => 'Estado de la asignación actualizado.', 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }
}
?>
