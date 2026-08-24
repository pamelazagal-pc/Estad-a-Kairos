<?php

require_once __DIR__ . '/../models/SesionTemporizador.php';

class SesionTemporizadorController
{
    private SesionTemporizador $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new SesionTemporizador($connection);
    }

    public function procesar(int $idDocente): array
    {
        $accion = $_POST['accion'] ?? '';
        try {
            if ($accion === 'iniciar_sesion') {
                $grupo = filter_input(INPUT_POST, 'id_grupo', FILTER_VALIDATE_INT);
                $duracion = filter_input(INPUT_POST, 'duracion', FILTER_VALIDATE_INT);
                $nivelNumerico = (int)($_POST['nivel'] ?? 0);
                $niveles = [1 => 'Bajo', 2 => 'Medio', 3 => 'Alto'];
                if (!$grupo || !$duracion || !isset($niveles[$nivelNumerico])) throw new InvalidArgumentException('Selecciona un grupo, una duración y un nivel válidos.');
                $idSesion = $this->model->iniciar($idDocente, $grupo, $duracion, $niveles[$nivelNumerico]);
                $_SESSION['sesion_temporizador_id'] = $idSesion;
                return ['ok' => true, 'id_sesion' => $idSesion, 'mensaje' => 'Sesión iniciada.'];
            }

            $idSesion = filter_input(INPUT_POST, 'id_sesion', FILTER_VALIDATE_INT);
            if (!$idSesion || !$this->model->pertenece((int)$idSesion, $idDocente)) throw new InvalidArgumentException('La sesión no es válida.');
            $estados = ['reanudar_sesion' => 'En curso', 'finalizar_sesion' => 'Finalizado', 'interrumpir_sesion' => 'Interrumpido'];
            if (!isset($estados[$accion])) throw new InvalidArgumentException('Acción de sesión no reconocida.');
            $this->model->cambiarEstado((int)$idSesion, $idDocente, $estados[$accion]);
            if ($estados[$accion] !== 'En curso' && ($_SESSION['sesion_temporizador_id'] ?? null) === (int)$idSesion) unset($_SESSION['sesion_temporizador_id']);
            return ['ok' => true, 'id_sesion' => (int)$idSesion, 'mensaje' => 'Sesión actualizada.'];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'error' => $exception->getMessage()];
        }
    }
}
