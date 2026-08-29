<?php

require_once __DIR__ . '/../models/Administrador.php';
require_once __DIR__ . '/../services/BienvenidaMailer.php';
require_once __DIR__ . '/../services/AccesoMailer.php';

class AdministradorController
{
    private $administradorModel;

    public function __construct($connection)
    {
        $this->administradorModel = new Administrador($connection);
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
            $administrador = $this->administradorModel->buscarPorCorreo($correo);

            if (
                $administrador === null ||
                $administrador['estado'] !== 'Activo' ||
                !password_verify($password, $administrador['password_hash'])
            ) {
                return ['ok' => false, 'errores' => ['Correo o contraseña incorrectos.']];
            }

            session_regenerate_id(true);
            $_SESSION['administrador_id'] = (int) $administrador['id_administrador'];
            $_SESSION['administrador_nombre'] = $administrador['nombre'];
            $_SESSION['administrador_principal'] = (int) $administrador['es_principal'] === 1;
            $_SESSION['debe_cambiar_password'] = (int) $administrador['debe_cambiar_password'] === 1;

            try {
                (new AccesoMailer())->enviar('administrador', $administrador['nombre'], $administrador['correo']);
            } catch (Throwable $mailException) {
                error_log('Alerta de acceso administrador: ' . $mailException->getMessage());
            }

            return ['ok' => true, 'errores' => []];

        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }

    public function registrarSecundario(): array
    {
        if (!$this->hayAdministradorAutenticado()) {
            return ['ok' => false, 'errores' => ['Debes iniciar sesión como administrador.']];
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';
        $errores = [];

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        } elseif (mb_strlen($nombre) > 100) {
            $errores[] = 'El nombre no puede superar los 100 caracteres.';
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        }

        if (mb_strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }

        if ($password !== $confirmarPassword) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }

        try {
            if ($this->administradorModel->correoExiste($correo)) {
                return ['ok' => false, 'errores' => ['El correo ya está registrado.']];
            }

            $this->administradorModel->registrar($nombre, $correo, $password, false);
            $mensaje = 'Administrador registrado correctamente.';
            try {
                (new BienvenidaMailer())->enviar('administrador', $nombre, $correo);
                $mensaje .= ' Se envió el correo de bienvenida.';
            } catch (Throwable $mailException) {
                error_log($mailException->getMessage());
                $mensaje .= ' La cuenta se creó, pero no fue posible enviar el correo de bienvenida.';
            }
            return ['ok' => true, 'mensaje' => $mensaje, 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }

    public function crearPrincipal(): array
    {
        $nombre = trim($_POST['nombre'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmarPassword = $_POST['confirmar_password'] ?? '';
        $errores = [];

        try {
            if ($this->administradorModel->existePrincipal()) {
                return ['ok' => false, 'errores' => ['El administrador principal ya existe.']];
            }
        } catch (Throwable $exception) {
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }

        if ($nombre === '') {
            $errores[] = 'El nombre es obligatorio.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Ingresa un correo electrónico válido.';
        }
        if (mb_strlen($password) < 8) {
            $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
        }
        if ($password !== $confirmarPassword) {
            $errores[] = 'Las contraseñas no coinciden.';
        }

        if ($errores !== []) {
            return ['ok' => false, 'errores' => $errores];
        }

        try {
            if ($this->administradorModel->correoExiste($correo)) {
                return ['ok' => false, 'errores' => ['El correo ya está registrado.']];
            }

            $this->administradorModel->registrar($nombre, $correo, $password, true);
            $mensaje = 'Administrador principal creado correctamente.';
            try {
                (new BienvenidaMailer())->enviar('administrador', $nombre, $correo);
                $mensaje .= ' Se envió el correo de bienvenida.';
            } catch (Throwable $mailException) {
                error_log($mailException->getMessage());
                $mensaje .= ' La cuenta se creó, pero no fue posible enviar el correo de bienvenida.';
            }
            return ['ok' => true, 'mensaje' => $mensaje, 'errores' => []];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            return ['ok' => false, 'errores' => [$exception->getMessage()]];
        }
    }

    private function hayAdministradorAutenticado(): bool
    {
        return isset($_SESSION['administrador_id']);
    }
}
