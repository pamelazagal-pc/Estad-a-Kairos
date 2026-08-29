<?php
class TutorDashboard
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function obtenerDatos(int $idTutor, ?int $idAlumno = null): array
    {
        $alumnos = $this->obtenerAlumnos($idTutor);
        if ($idAlumno !== null && !$this->alumnoVinculado($idTutor, $idAlumno)) $idAlumno = null;
        if ($idAlumno === null && count($alumnos) === 1) $idAlumno = (int)$alumnos[0]['id_alumno'];
        return [
            'tutor' => $this->obtenerTutor($idTutor),
            'alumnos' => $alumnos,
            'alumno_seleccionado' => $idAlumno,
            'alumno' => $idAlumno ? $this->obtenerAlumno($idTutor, $idAlumno) : null,
            'incidencias' => $idAlumno ? $this->obtenerIncidencias($idTutor, $idAlumno) : [],
            'medallas' => $idAlumno ? $this->obtenerMedallas($idTutor, $idAlumno) : [],
            'consejos' => $this->obtenerConsejos(),
            'notificaciones' => $idAlumno ? $this->obtenerNotificaciones($idTutor, $idAlumno) : [],
            'sesiones' => $idAlumno ? $this->obtenerSesiones($idTutor, $idAlumno) : [],
        ];
    }

    private function obtenerTutor(int $idTutor): array
    {
        $stmt = $this->connection->prepare("SELECT id_tutor, nombre, cargo, correo FROM tutores WHERE id_tutor = ? AND estado = 'Activo' LIMIT 1");
        if (!$stmt) throw new RuntimeException('No fue posible consultar el tutor.');
        $stmt->bind_param('i', $idTutor);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: [];
    }

    public function obtenerAlumnos(int $idTutor): array
    {
        $sql = "SELECT a.id_alumno,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS nombre_completo,
                       a.estado_semaforo, a.puntos_acumulados,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo,
                       g.ciclo_escolar,
                       at.parentesco, at.es_principal,
                       (SELECT MAX(b.fecha_hora) FROM bitacora_notas b WHERE b.id_alumno = a.id_alumno AND b.estado = 'Activo') AS ultima_actualizacion,
                       (SELECT b2.emocion FROM bitacora_notas b2 WHERE b2.id_alumno = a.id_alumno AND b2.estado = 'Activo' ORDER BY b2.fecha_hora DESC, b2.id_nota DESC LIMIT 1) AS ultima_emocion,
                       (SELECT b3.accion_contencion FROM bitacora_notas b3 WHERE b3.id_alumno = a.id_alumno AND b3.estado = 'Activo' ORDER BY b3.fecha_hora DESC, b3.id_nota DESC LIMIT 1) AS ultima_accion

                FROM alumno_tutores at
                INNER JOIN alumnos a ON a.id_alumno = at.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                WHERE at.id_tutor = ? AND at.estado = 'Activo'
                  AND a.estado = 'Activo' AND g.estado = 'Activo'
                ORDER BY at.es_principal DESC, a.apellido_paterno, a.nombre";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible cargar los alumnos vinculados.');
        $stmt->bind_param('i', $idTutor);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function alumnoVinculado(int $idTutor, int $idAlumno): bool
    {
        $stmt = $this->connection->prepare("SELECT id_alumno_tutor FROM alumno_tutores WHERE id_tutor = ? AND id_alumno = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('ii', $idTutor, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    private function obtenerAlumno(int $idTutor, int $idAlumno): ?array
    {
        foreach ($this->obtenerAlumnos($idTutor) as $alumno) {
            if ((int)$alumno['id_alumno'] === $idAlumno) return $alumno;
        }
        return null;
    }

    private function obtenerIncidencias(int $idTutor, int $idAlumno): array
    {
        $sql = "SELECT b.fecha_hora, b.tipo_nota, b.emocion, b.accion_contencion,
                       b.nota_descripcion, d.nombre AS docente,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docentes d ON d.id_docente = b.id_docente
                INNER JOIN alumno_tutores at ON at.id_alumno = a.id_alumno
                WHERE at.id_tutor = ? AND at.estado = 'Activo' AND b.id_alumno = ?
                                    AND a.estado = 'Activo' AND b.estado = 'Activo'
                ORDER BY b.fecha_hora DESC LIMIT 30";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible cargar el seguimiento emocional.');
        $stmt->bind_param('ii', $idTutor, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerMedallas(int $idTutor, int $idAlumno): array
    {
        $sql = "SELECT b.fecha_hora, r.nombre_insignia, r.descripcion,
                       r.icono_url, b.puntos_otorgados, d.nombre AS docente
                FROM bitacora_notas b
                INNER JOIN recompensas_catalogo r ON r.id_recompensa = b.id_recompensa
                INNER JOIN docentes d ON d.id_docente = b.id_docente
                INNER JOIN alumno_tutores at ON at.id_alumno = b.id_alumno
                WHERE at.id_tutor = ? AND at.estado = 'Activo'
                                    AND b.id_alumno = ? AND b.tipo_nota = 'Medalla' AND b.estado = 'Activo'

                ORDER BY b.fecha_hora DESC LIMIT 30";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible cargar las medallas.');
        $stmt->bind_param('ii', $idTutor, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerConsejos(): array
    {
        $resultado = $this->connection->query("SELECT id_consejo, titulo, recomendacion, categoria, fecha_publicacion
                                               FROM consejos_hogar
                                               WHERE estado = 'Activo'
                                               ORDER BY fecha_publicacion DESC, titulo ASC");
        if (!$resultado) throw new RuntimeException('No fue posible cargar los consejos para el hogar.');
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerNotificaciones(int $idTutor, int $idAlumno): array
    {
        $sql = "SELECT b.id_nota, b.fecha_hora, b.tipo_nota, b.emocion,
                       b.nota_descripcion, b.notificado_al_tutor,
                       d.nombre AS docente
                FROM bitacora_notas b
                INNER JOIN docentes d ON d.id_docente = b.id_docente AND d.estado = 'Activo'
                INNER JOIN alumno_tutores at ON at.id_alumno = b.id_alumno
                WHERE at.id_tutor = ? AND at.estado = 'Activo' AND b.id_alumno = ?
                  AND b.notificado_al_tutor = 1 AND b.estado = 'Activo'
                ORDER BY b.fecha_hora DESC LIMIT 8";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible cargar las notificaciones.');
        $stmt->bind_param('ii', $idTutor, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function obtenerSesiones(int $idTutor, int $idAlumno): array
    {
        $sql = "SELECT DISTINCT s.id_sesion, s.fecha, s.fecha_inicio, s.fecha_fin,
                       s.duracion_clase_min, s.nivel_irritabilidad, s.estado,
                       d.nombre AS docente
                FROM sesiones_temporizador s
                INNER JOIN alumnos a ON a.id_grupo = s.id_grupo
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN alumno_tutores at ON at.id_alumno = a.id_alumno
                INNER JOIN docentes d ON d.id_docente = s.id_docente AND d.estado = 'Activo'
                WHERE at.id_tutor = ? AND at.estado = 'Activo'
                  AND a.id_alumno = ? AND a.estado = 'Activo' AND g.estado = 'Activo'
                ORDER BY s.fecha_inicio DESC LIMIT 20";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible cargar las sesiones del grupo.');
        $stmt->bind_param('ii', $idTutor, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
