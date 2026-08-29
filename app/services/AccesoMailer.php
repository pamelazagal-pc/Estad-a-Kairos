<?php

declare(strict_types=1);

require_once __DIR__ . '/KairosMailer.php';

class AccesoMailer
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
        $fecha = htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8');
        $contenido = '<!doctype html><html lang="es"><body style="margin:0;padding:24px;background:#f4f6f8;color:#243746;font-family:Arial,sans-serif"><div style="max-width:560px;margin:0 auto;padding:28px;border:1px solid #e0e6ed;border-radius:14px;background:#fff"><p style="margin:0;color:#009b82;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Kairos | Bienestar escolar</p><h1 style="margin:12px 0;color:#4e2d78;font-size:24px">Inicio de sesión detectado</h1><p>Hola, <strong>' . htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') . '</strong>.</p><p>Se inició sesión correctamente en tu cuenta de Kairos como <strong>' . $etiquetas[$tipoUsuario] . '</strong>.</p><div style="margin:22px 0;padding:16px;border-radius:10px;background:#f4f8f8"><p style="margin:0"><strong>Fecha y hora:</strong> ' . $fecha . '</p></div><p>Si reconoces este acceso, no necesitas realizar ninguna acción. Si no fuiste tú, cambia tu contraseña desde la opción de recuperación y comunícate con el administrador.</p><p style="font-size:13px;color:#60717c">Este mensaje se envía automáticamente cada vez que se inicia sesión correctamente.</p></div></body></html>';
        return (new KairosMailer())->enviar($correo, $nombre, 'Kairos | Inicio de sesión detectado', $contenido);
    }
}
