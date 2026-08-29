<?php

declare(strict_types=1);

require_once __DIR__ . '/KairosMailer.php';

class AsignacionMailer
{
    public function enviarGrupoMaestro(array $datos): bool
    {
        $nombre = (string)($datos['docente'] ?? '');
        $correo = (string)($datos['correo'] ?? '');
        $grupo = $this->grupo($datos);
        $ciclo = htmlspecialchars((string)($datos['ciclo_escolar'] ?? ''), ENT_QUOTES, 'UTF-8');
        $contenido = $this->plantilla(
            'Nueva asignación de grupo',
            'Se te ha asignado un grupo dentro de la plataforma Kairos.',
            '<p><strong>Grupo asignado:</strong> ' . $grupo . '</p><p><strong>Ciclo escolar:</strong> ' . $ciclo . '</p><p>Ya puedes ingresar al panel del maestro para consultar a tus alumnos y registrar el seguimiento emocional correspondiente.</p>',
            $this->url('maestro/login_maestro.php')
        );
        return (new KairosMailer())->enviar($correo, $nombre, 'Kairos | Grupo asignado', $contenido);
    }

    public function enviarAlumnoTutor(array $datos): bool
    {
        $nombreTutor = (string)($datos['tutor'] ?? '');
        $correo = (string)($datos['correo'] ?? '');
        $alumno = htmlspecialchars((string)($datos['alumno'] ?? ''), ENT_QUOTES, 'UTF-8');
        $grupo = $this->grupo($datos);
        $parentesco = htmlspecialchars((string)($datos['parentesco'] ?? ''), ENT_QUOTES, 'UTF-8');
        $contenido = $this->plantilla(
            'Nuevo alumno vinculado',
            'Se ha registrado una nueva vinculación familiar en la plataforma Kairos.',
            '<p><strong>Alumno vinculado:</strong> ' . $alumno . '</p><p><strong>Grupo:</strong> ' . $grupo . '</p>' . ($parentesco !== '' ? '<p><strong>Parentesco:</strong> ' . $parentesco . '</p>' : '') . '<p>Ya puedes ingresar al panel familiar para consultar el seguimiento escolar y emocional disponible.</p>',
            $this->url('tutor/login_tutor.php')
        );
        return (new KairosMailer())->enviar($correo, $nombreTutor, 'Kairos | Alumno vinculado', $contenido);
    }

    private function grupo(array $datos): string
    {
        $grado = htmlspecialchars((string)($datos['grado'] ?? ''), ENT_QUOTES, 'UTF-8');
        $grupo = htmlspecialchars((string)($datos['grupo'] ?? ''), ENT_QUOTES, 'UTF-8');
        return trim($grado . '° ' . $grupo);
    }

    private function url(string $ruta): string
    {
        $config = is_file(__DIR__ . '/../../config/mail.php') ? require __DIR__ . '/../../config/mail.php' : [];
        $base = rtrim((string)($config['app_url'] ?? 'http://localhost/Estadia/Estad-a-Kairos/public/accesos'), '/');
        return htmlspecialchars($base . '/' . ltrim($ruta, '/'), ENT_QUOTES, 'UTF-8');
    }

    private function plantilla(string $titulo, string $intro, string $detalle, string $url): string
    {
        return '<!doctype html><html lang="es"><body style="margin:0;padding:24px;background:#f4f6f8;color:#243746;font-family:Arial,sans-serif"><div style="max-width:560px;margin:0 auto;padding:28px;border:1px solid #e0e6ed;border-radius:14px;background:#fff"><p style="margin:0;color:#009b82;font-size:12px;font-weight:700;letter-spacing:1px;text-transform:uppercase">Kairos | Bienestar escolar</p><h1 style="margin:12px 0;color:#4e2d78;font-size:24px">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h1><p>' . htmlspecialchars($intro, ENT_QUOTES, 'UTF-8') . '</p>' . $detalle . '<p style="margin:26px 0"><a href="' . $url . '" style="display:inline-block;padding:12px 18px;border-radius:8px;background:#009b82;color:#fff;text-decoration:none;font-weight:700">Ingresar a Kairos</a></p><p style="font-size:13px;color:#60717c">Este aviso fue generado automáticamente por una asignación realizada en la plataforma.</p></div></body></html>';
    }
}
