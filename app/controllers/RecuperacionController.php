<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/RecuperacionCuenta.php';
require_once __DIR__ . '/../services/KairosMailer.php';

class RecuperacionController
{
    private RecuperacionCuenta $model;
    private const TIPOS = ['administrador', 'maestro', 'tutor'];

    public function __construct(mysqli $connection)
    {
        $this->model = new RecuperacionCuenta($connection);
    }

    public function solicitar(string $tipoUsuario, string $correo): array
    {
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'tipo_usuario' => $tipoUsuario, 'correo' => $correo];
        if (!in_array($tipoUsuario, self::TIPOS, true)) {
            $resultado['errores'][] = 'El tipo de usuario no es válido.';
            return $resultado;
        }
        $correo = trim($correo);
        $resultado['correo'] = $correo;
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $resultado['errores'][] = 'Ingresa un correo electrónico válido.';
            return $resultado;
        }
        try {
            $cuenta = $this->model->solicitar($tipoUsuario, $correo);
            if ($cuenta) {
                $url = $this->urlRestablecimiento($tipoUsuario, $cuenta['token']);
                $contenido = $this->contenidoCorreo($cuenta['nombre'], $url, $cuenta['expira_at']);
                $mailer = new KairosMailer();
                $mailer->enviar($cuenta['correo'], $cuenta['nombre'], 'Restablece tu contraseña de Kairos', $contenido);
            }
            $resultado['ok'] = true;
            $resultado['mensaje'] = 'Si existe una cuenta activa con ese correo, recibirás un enlace para restablecer tu contraseña. Revisa también la carpeta de spam.';
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible procesar la solicitud. Intenta nuevamente más tarde.';
        }
        return $resultado;
    }

    public function prepararRestablecimiento(string $token): array
    {
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'token' => trim($token), 'cuenta' => null];
        if ($resultado['token'] === '') {
            $resultado['errores'][] = 'El enlace de recuperación está incompleto.';
            return $resultado;
        }
        try {
            $cuenta = $this->model->validarToken($resultado['token']);
            if (!$cuenta) {
                $resultado['errores'][] = 'El enlace no es válido o ya expiró.';
                return $resultado;
            }
            $resultado['ok'] = true;
            $resultado['cuenta'] = $cuenta;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = 'No fue posible validar el enlace de recuperación.';
        }
        return $resultado;
    }

    public function restablecer(string $token, string $password, string $confirmacion): array
    {
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'token' => trim($token), 'tipo_usuario' => ''];
        if ($resultado['token'] !== '') {
            try {
                $datosToken = $this->model->validarToken($resultado['token']);
                $resultado['tipo_usuario'] = (string)($datosToken['tipo_usuario'] ?? '');
            } catch (Throwable $exception) {
                $resultado['errores'][] = 'No fue posible validar el enlace de recuperación.';
            }
        }
        if ($resultado['token'] === '') $resultado['errores'][] = 'El enlace de recuperación está incompleto.';
        if (strlen($password) < 8) $resultado['errores'][] = 'La contraseña debe tener al menos 8 caracteres.';
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) $resultado['errores'][] = 'La contraseña debe incluir al menos una letra y un número.';
        if ($password !== $confirmacion) $resultado['errores'][] = 'Las contraseñas no coinciden.';
        if ($resultado['errores'] !== []) return $resultado;
        try {
            $this->model->cambiarPassword($resultado['token'], $password);
            $resultado['ok'] = true;
            $resultado['mensaje'] = 'Tu contraseña fue actualizada correctamente. Ya puedes iniciar sesión.';
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = $exception instanceof InvalidArgumentException ? $exception->getMessage() : 'No fue posible actualizar la contraseña.';
        }
        return $resultado;
    }

    private function urlRestablecimiento(string $tipoUsuario, string $token): string
    {
        $config = is_file(__DIR__ . '/../../config/mail.php') ? require __DIR__ . '/../../config/mail.php' : [];
        $base = rtrim((string)($config['app_url'] ?? 'http://localhost/Estadia/Estad-a-Kairos/public/accesos'), '/');
        return $base . '/restablecer.php?tipo=' . rawurlencode($tipoUsuario) . '&token=' . rawurlencode($token);
    }

    private function contenidoCorreo(string $nombre, string $url, string $expiraAt): string
    {
        $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $urlSeguro = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $expiraSeguro = htmlspecialchars(date('d/m/Y H:i', strtotime($expiraAt)), ENT_QUOTES, 'UTF-8');
        return '<!doctype html><html lang="es"><body style="margin:0;padding:24px;background:#f4f6f8;color:#243746;font-family:Arial,sans-serif"><div style="max-width:560px;margin:0 auto;padding:28px;border:1px solid #e0e6ed;border-radius:14px;background:#fff"><p style="margin:0;color:#009b82;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Kairos | Bienestar escolar</p><h1 style="margin:12px 0;color:#4e2d78;font-size:24px">Restablece tu contraseña</h1><p>Hola, <strong>' . $nombreSeguro . '</strong>.</p><p>Recibimos una solicitud para cambiar la contraseña de tu cuenta de Kairos. Si fuiste tú, utiliza el siguiente botón:</p><p style="margin:26px 0"><a href="' . $urlSeguro . '" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#009b82;color:#fff;text-decoration:none;font-weight:700">Crear nueva contraseña</a></p><p style="font-size:13px;color:#60717c">Este enlace es de un solo uso y expira el <strong>' . $expiraSeguro . '</strong>. Si no solicitaste este cambio, puedes ignorar este correo.</p><p style="font-size:12px;color:#60717c">Por seguridad, nunca compartas este enlace.</p></div></body></html>';
    }
}
