<?php

require_once __DIR__ . '/../models/Tutor.php';
require_once __DIR__ . '/../services/BienvenidaMailer.php';
require_once __DIR__ . '/../models/Grupo.php';
require_once __DIR__ . '/../models/Alumno.php';

class RegistroController
{
    private $tutorModel;
    private $grupoModel;
    private $alumnoModel;

    public function __construct($connection)
    {
        $this->tutorModel = new Tutor($connection);
        $this->grupoModel = new Grupo($connection);
        $this->alumnoModel = new Alumno($connection);
    }

    public function tutor(): array
    {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'cargo' => trim($_POST['cargo'] ?? 'Padre o tutor'),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
        ];
        $password = $_POST['password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';
        $errores = [];

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
            $errores[] = 'El nombre es obligatorio y no puede superar los 100 caracteres.';
        }
        if (!in_array($datos['cargo'], ['Padre', 'Tutor'], true)) {
            $errores[] = 'Debes seleccionar Padre o Tutor.';
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL) || mb_strlen($datos['correo']) > 150) {
            $errores[] = 'Ingresa un correo válido de máximo 150 caracteres.';
        }
        if ($datos['telefono'] !== '' && mb_strlen($datos['telefono']) > 20) {
            $errores[] = 'El teléfono no puede superar los 20 caracteres.';
        }
        if (mb_strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirmarPassword) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores, 'datos' => $datos];
        }

        try {
            if ($this->tutorModel->correoExiste($datos['correo'])) {
                return ['ok' => false, 'errores' => ['El correo ya está registrado como tutor.'], 'datos' => $datos];
            }

            $this->tutorModel->registrar(
                $datos['nombre'],
                $datos['cargo'],
                $datos['correo'],
                $password,
                $datos['telefono'] !== '' ? $datos['telefono'] : null
            );

            $mensaje = 'Tutor registrado correctamente.';
            try {
                (new BienvenidaMailer())->enviar('tutor', $datos['nombre'], $datos['correo']);
                $mensaje .= ' Se envió el correo de bienvenida.';
            } catch (Throwable $mailException) {
                error_log($mailException->getMessage());
                $mensaje .= ' La cuenta se creó, pero no fue posible enviar el correo de bienvenida.';
            }
            return ['ok' => true, 'mensaje' => $mensaje, 'errores' => [], 'datos' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()], 'datos' => $datos];
        }
    }

    public function alumno(): array
    {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'apellido_paterno' => trim($_POST['apellido_paterno'] ?? ''),
            'apellido_materno' => trim($_POST['apellido_materno'] ?? ''),
            'edad' => trim($_POST['edad'] ?? ''),
            'id_grupo' => (int) ($_POST['id_grupo'] ?? 0),
        ];
        $errores = [];

        if ($datos['nombre'] === '' || mb_strlen($datos['nombre']) > 100) {
            $errores[] = 'El nombre es obligatorio y no puede superar los 100 caracteres.';
        }
        if ($datos['apellido_paterno'] === '' || mb_strlen($datos['apellido_paterno']) > 50) {
            $errores[] = 'El apellido paterno es obligatorio y no puede superar los 50 caracteres.';
        }
        if ($datos['apellido_materno'] !== '' && mb_strlen($datos['apellido_materno']) > 50) {
            $errores[] = 'El apellido materno no puede superar los 50 caracteres.';
        }

        $edad = null;
        if ($datos['edad'] !== '') {
            if (!ctype_digit($datos['edad']) || (int) $datos['edad'] < 5 || (int) $datos['edad'] > 18) {
                $errores[] = 'La edad debe ser un número entre 5 y 18.';
            } else {
                $edad = (int) $datos['edad'];
            }
        }

        if ($datos['id_grupo'] <= 0) {
            $errores[] = 'Debes seleccionar un grupo activo.';
        }

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores, 'datos' => $datos, 'grupos' => $this->obtenerGrupos()];
        }

        try {
            $this->alumnoModel->registrar(
                $datos['nombre'],
                $datos['apellido_paterno'],
                $datos['apellido_materno'] !== '' ? $datos['apellido_materno'] : null,
                $edad,
                $datos['id_grupo']
            );

            return ['ok' => true, 'mensaje' => 'Alumno registrado correctamente.', 'errores' => [], 'datos' => [], 'grupos' => $this->obtenerGrupos()];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()], 'datos' => $datos, 'grupos' => $this->obtenerGrupos()];
        }
    }

    public function gruposParaFormulario(): array
    {
        return $this->obtenerGrupos();
    }

    private function obtenerGrupos(): array
    {
        try {
            return $this->grupoModel->listarActivos();
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return [];
        }
    }

    public function grupo(): array
    {
        $grado = filter_input(INPUT_POST, 'grado', FILTER_VALIDATE_INT);
        $grupo = strtoupper(trim($_POST['grupo'] ?? ''));
        $cicloEscolar = trim($_POST['ciclo_escolar'] ?? '');
        $errores = [];

        if ($grado === false || $grado === null || $grado < 1 || $grado > 6) {
            $errores[] = 'El grado debe ser un número entre 1 y 6.';
        }
        if (!preg_match('/^[A-Z]$/', $grupo)) {
            $errores[] = 'El grupo debe ser una sola letra.';
        }
        if ($cicloEscolar === '' || mb_strlen($cicloEscolar) > 20) {
            $errores[] = 'El ciclo escolar es obligatorio y no puede superar los 20 caracteres.';
        }

        $datos = [
            'grado' => $grado ?: '',
            'grupo' => $grupo,
            'ciclo_escolar' => $cicloEscolar,
        ];

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores, 'datos' => $datos];
        }

        try {
            if ($this->grupoModel->existe($grado, $grupo, $cicloEscolar)) {
                return ['ok' => false, 'errores' => ['Ese grupo ya existe para el ciclo escolar indicado.'], 'datos' => $datos];
            }

            $this->grupoModel->registrar($grado, $grupo, $cicloEscolar);
            return ['ok' => true, 'mensaje' => 'Grupo registrado correctamente.', 'errores' => [], 'datos' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()], 'datos' => $datos];
        }
    }
}
