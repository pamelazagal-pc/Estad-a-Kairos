<?php

declare(strict_types=1);

require_once __DIR__ . '/KairosMailer.php';

class BienvenidaMailer
{
    public function enviar(string $tipoUsuario, string $nombre, string $correo): bool
    {
        $etiquetas = [
            'administrador' => 'Administrador',
            'maestro' => 'Maestro',
            'tutor' => 'Tutor',
        ];
        if (!isset($etiquetas[$tipoUsuario])) {
            throw new InvalidArgumentException('El tipo de cuenta no es válido.');
        }
        $url = $this->urlLogin($tipoUsuario);
        $contenido = $this->contenido($nombre, $etiquetas[$tipoUsuario], $url);
        return (new KairosMailer())->enviar($correo, $nombre, 'Tu cuenta de Kairos fue creada', $contenido);
    }

    private function urlLogin(string $tipoUsuario): string
    {
        $config = is_file(__DIR__ . '/../../config/mail.php') ? require __DIR__ . '/../../config/mail.php' : [];
        $base = rtrim((string)($config['app_url'] ?? 'http://localhost/Estadia/Estad-a-Kairos/public/accesos'), '/');
        $rutas = [
            'administrador' => 'login_administrador.php',
            'maestro' => 'maestro/login_maestro.php',
            'tutor' => 'tutor/login_tutor.php',
        ];
        return $base . '/' . $rutas[$tipoUsuario];
    }

    private function contenido(string $nombre, string $tipo, string $url): string
    {
        $nombreSeguro = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
        $tipoSeguro = htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8');
        $urlSeguro = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        return '<!doctype html><html lang="es"><body style="margin:0;padding:24px;background:#f4f6f8;color:#243746;font-family:Arial,sans-serif"><div style="max-width:560px;margin:0 auto;padding:28px;border:1px solid #e0e6ed;border-radius:14px;background:#fff"><p style="margin:0;color:#009b82;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Kairos | Bienestar escolar</p><h1 style="margin:12px 0;color:#4e2d78;font-size:24px">¡Bienvenido a Kairos!</h1><p>Hola, <strong>' . $nombreSeguro . '</strong>.</p><p>Tu cuenta como <strong>' . $tipoSeguro . '</strong> fue creada correctamente en la plataforma Kairos.</p><p>Ya puedes ingresar con el correo que fue registrado y la contraseña que te proporcionó el administrador.</p><p style="margin:26px 0"><a href="' . $urlSeguro . '" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#009b82;color:#fff;text-decoration:none;font-weight:700">Ingresar a Kairos</a></p><p style="font-size:13px;color:#60717c">Por seguridad, Kairos nunca envía contraseñas por correo. Si no reconoces esta cuenta, comunícate con el administrador de tu institución.</p></div></body></html>';
    }
}
