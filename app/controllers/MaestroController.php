<?php

require_once __DIR__ . '/../models/Maestro.php';

class MaestroController
{
    private $maestroModel;

    public function __construct($connection)
    {
        $this->maestroModel = new Maestro($connection);
    }

        public function iniciarSesion(): array
    {
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $errores = [];

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        }
        if ($password === '') {
            $errores[] = 'La contraseña es obligatoria.';
        }
        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }

        try {
            $maestro = $this->maestroModel->buscarPorCorreo($correo);
            if ($maestro === null || $maestro['estado'] !== 'Activo' || !password_verify($password, $maestro['password_hash'])) {
                return ['ok' => false, 'errores' => ['Correo o contraseña incorrectos.']];
            }

            session_regenerate_id(true);
            $_SESSION['maestro_id'] = (int)$maestro['id_docente'];
            $_SESSION['maestro_nombre'] = $maestro['nombre'];
            $_SESSION['maestro_correo'] = $maestro['correo'];
            return ['ok' => true, 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => ['No fue posible iniciar sesión.']];
        }
    }

    public function registrar(): array

    {
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'correo' => trim($_POST['correo'] ?? ''),
            'telefono' => trim($_POST['telefono'] ?? ''),
        ];

        $password = $_POST['password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';
        $errores = [];

        if ($datos['nombre'] === '') {
            $errores[] = 'El nombre completo es obligatorio.';
        } elseif (mb_strlen($datos['nombre']) > 100) {
            $errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        } elseif (mb_strlen($datos['correo']) > 150) {
            $errores[] = 'El correo no puede superar los 150 caracteres.';
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
            return [
                'ok' => false,
                'errores' => $errores,
                'datos' => $datos,
            ];
        }

        try {
            if ($this->maestroModel->correoExiste($datos['correo'])) {
                return [
                    'ok' => false,
                    'errores' => ['El correo electrónico ya está registrado.'],
                    'datos' => $datos,
                ];
            }

            $registrado = $this->maestroModel->registrar(
                $datos['nombre'],
                $datos['correo'],
                $password,
                $datos['telefono'] !== '' ? $datos['telefono'] : null
            );

            if (!$registrado) {
                return [
                    'ok' => false,
                    'errores' => ['No fue posible registrar al maestro.'],
                    'datos' => $datos,
                ];
            }

            return [
                'ok' => true,
                'mensaje' => 'Maestro registrado correctamente.',
                'datos' => [],
            ];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());

            return [
                'ok' => false,
                'errores' => [$exception->getMessage()],
                'datos' => $datos,
            ];
        }
    }
}
