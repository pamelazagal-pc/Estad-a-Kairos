<?php
require_once __DIR__ . '/../models/Tutor.php';
require_once __DIR__ . '/../models/TutorDashboard.php';
require_once __DIR__ . '/../services/AccesoMailer.php';

class TutorController
{
    private Tutor $tutor;
    private TutorDashboard $dashboard;

    public function __construct(mysqli $connection)
    {
        $this->tutor = new Tutor($connection);
        $this->dashboard = new TutorDashboard($connection);
    }

    public function iniciarSesion(): array
    {
        $correo = trim((string)($_POST['correo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $errores = [];
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'Ingresa un correo electrónico válido.';
        if ($password === '') $errores[] = 'Ingresa tu contraseña.';
        if ($errores !== []) return ['ok' => false, 'errores' => $errores, 'datos' => ['correo' => $correo]];
        try {
            $cuenta = $this->tutor->autenticar($correo, $password);
            if (!$cuenta) return ['ok' => false, 'errores' => ['El correo o la contraseña no son correctos.'], 'datos' => ['correo' => $correo]];
            session_regenerate_id(true);
            $_SESSION['tutor_id'] = (int)$cuenta['id_tutor'];
            $_SESSION['tutor_nombre'] = $cuenta['nombre'];
            $_SESSION['tutor_correo'] = $cuenta['correo'];

            try {
                (new AccesoMailer())->enviar('tutor', $cuenta['nombre'], $cuenta['correo']);
            } catch (Throwable $mailException) {
                error_log('Alerta de acceso tutor: ' . $mailException->getMessage());
            }

            return ['ok' => true, 'errores' => [], 'datos' => $cuenta];

        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => ['No fue posible iniciar sesión.'], 'datos' => ['correo' => $correo]];
        }
    }

    public function obtenerDashboard(int $idTutor, ?int $idAlumno = null): array
    {
        try {
            $datos = $this->dashboard->obtenerDatos($idTutor, $idAlumno);
            return ['ok' => $datos['tutor'] !== [], 'errores' => $datos['tutor'] === [] ? ['La cuenta del tutor no está activa.'] : [], 'datos' => $datos];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => ['No fue posible cargar la información del tutor.'], 'datos' => []];
        }
    }
}
