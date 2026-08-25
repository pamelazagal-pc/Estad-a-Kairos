<?php

class MaestroDashboard
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function obtenerDatos(int $idDocente, ?int $idGrupo = null, bool $requiereSeleccion = false): array
    {
        $grupos = $this->obtenerGrupos($idDocente);
        if ($idGrupo !== null && !$this->grupoAsignado($idDocente, $idGrupo)) $idGrupo = null;
        $sinSeleccion = $requiereSeleccion && $idGrupo === null;
        return [
            'maestro' => $this->obtenerMaestro($idDocente),
            'grupos' => $grupos,
            'grupo_seleccionado' => $idGrupo,
'alumnos' => $sinSeleccion ? [] : $this->obtenerAlumnos($idDocente, $idGrupo),
            'incidencias' => $sinSeleccion ? [] : $this->obtenerIncidencias($idDocente, $idGrupo),
            'kpis' => $sinSeleccion ? ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0] : $this->obtenerKpis($idDocente, $idGrupo),
        ];
    }

    private function obtenerMaestro(int $idDocente): array
    {
        $stmt = $this->connection->prepare("SELECT id_docente, nombre, correo FROM docentes WHERE id_docente = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: [];
    }

    public function obtenerGrupos(int $idDocente): array
    {
        $sql = "SELECT DISTINCT g.id_grupo, g.grado, g.grupo, g.ciclo_escolar,
                       CONCAT(g.grado, '° Grado ', g.grupo) AS nombre_grupo
                FROM docente_grupos dg
                INNER JOIN grupos g ON g.id_grupo = dg.id_grupo
                WHERE dg.id_docente = ? AND dg.estado = 'Activo' AND g.estado = 'Activo'
                ORDER BY g.grado, g.grupo, g.ciclo_escolar DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAlumnos(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT a.id_alumno,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS nombre_completo,
                       a.estado_semaforo,
                       g.id_grupo,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo,
                       COALESCE(MAX(b.fecha_hora), NULL) AS ultima_actualizacion,
                       COALESCE((SELECT b2.emocion FROM bitacora_notas b2
                                 WHERE b2.id_alumno = a.id_alumno
                                 ORDER BY b2.fecha_hora DESC LIMIT 1), 'Sin registro') AS ultima_emocion,
                       COALESCE((SELECT b3.accion_contencion FROM bitacora_notas b3
                                 WHERE b3.id_alumno = a.id_alumno
                                 ORDER BY b3.fecha_hora DESC LIMIT 1), '') AS ultima_accion
                FROM alumnos a
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = g.id_grupo
                LEFT JOIN bitacora_notas b ON b.id_alumno = a.id_alumno
                WHERE dg.id_docente = ? AND dg.estado = 'Activo'
                  AND g.estado = 'Activo' AND a.estado = 'Activo'" . ($idGrupo !== null ? " AND g.id_grupo = ?" : '') . "
                GROUP BY a.id_alumno, a.nombre, a.apellido_paterno, a.apellido_materno,
                         a.estado_semaforo, g.id_grupo, g.grado, g.grupo
                ORDER BY g.grado, g.grupo, a.apellido_paterno, a.apellido_materno, a.nombre";
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('ii', $idDocente, $idGrupo); else $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerIncidencias(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT b.id_nota, b.fecha_hora, b.emocion, b.accion_contencion, b.nota_descripcion,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                WHERE b.id_docente = ? AND dg.id_docente = ? AND dg.estado = 'Activo'
                  AND b.tipo_nota = 'Incidencia' AND a.estado = 'Activo' AND g.estado = 'Activo'" . ($idGrupo !== null ? " AND a.id_grupo = ?" : '') . "
                ORDER BY b.fecha_hora DESC LIMIT 30";
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('iii', $idDocente, $idDocente, $idGrupo); else $stmt->bind_param('ii', $idDocente, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerKpis(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT
                    COUNT(DISTINCT a.id_alumno) AS alumnos,
                    COALESCE(SUM(CASE WHEN a.estado_semaforo = 'Amarillo' THEN 1 ELSE 0 END), 0) AS atencion,
                    COALESCE(SUM(CASE WHEN a.estado_semaforo = 'Rojo' THEN 1 ELSE 0 END), 0) AS crisis,
                    (SELECT COUNT(*) FROM bitacora_notas b2
                     INNER JOIN alumnos a2 ON a2.id_alumno = b2.id_alumno
                     INNER JOIN docente_grupos dg2 ON dg2.id_grupo = a2.id_grupo
                     WHERE dg2.id_docente = ? AND dg2.estado = 'Activo'
                       AND b2.id_docente = ? AND b2.accion_contencion IS NOT NULL
                       AND b2.accion_contencion <> '' AND DATE(b2.fecha_hora) = CURDATE()" . ($idGrupo !== null ? " AND a2.id_grupo = ?" : '') . ") AS contenciones
                FROM alumnos a
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = g.id_grupo
                WHERE dg.id_docente = ? AND dg.estado = 'Activo'
                  AND g.estado = 'Activo' AND a.estado = 'Activo'" . ($idGrupo !== null ? " AND a.id_grupo = ?" : '');
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('iiiii', $idDocente, $idDocente, $idGrupo, $idDocente, $idGrupo);
        else $stmt->bind_param('iii', $idDocente, $idDocente, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0];
    }

    private function grupoAsignado(int $idDocente, int $idGrupo): bool
    {
        $stmt = $this->connection->prepare("SELECT id_docente_grupo FROM docente_grupos WHERE id_docente = ? AND id_grupo = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('ii', $idDocente, $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function alumnoAsignado(int $idDocente, int $idAlumno): bool
    {
        $sql = "SELECT a.id_alumno FROM alumnos a
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                WHERE a.id_alumno = ? AND a.estado = 'Activo'
                  AND g.estado = 'Activo' AND dg.id_docente = ? AND dg.estado = 'Activo' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ii', $idAlumno, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function historial(int $idDocente, ?int $idAlumno = null, string $tipo = '', string $emocion = '', string $desde = '', string $hasta = ''): array
    {
        $sql = "SELECT DISTINCT b.id_nota, b.id_alumno, b.tipo_nota, b.emocion, b.nota_descripcion,
                       b.accion_contencion, b.notificado_al_tutor, b.fecha_hora,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                WHERE b.id_docente = ? AND dg.id_docente = ? AND dg.estado = 'Activo'
                  AND a.estado = 'Activo' AND g.estado = 'Activo'";
        $tipos = 'ii';
        $params = [$idDocente, $idDocente];
        $refs = [&$params[0], &$params[1]];
        if ($idAlumno !== null && $idAlumno > 0) { $sql .= ' AND b.id_alumno = ?'; $tipos .= 'i'; $params[] = $idAlumno; $refs[] = &$params[count($params)-1]; }
        if ($tipo !== '') { $sql .= ' AND b.tipo_nota = ?'; $tipos .= 's'; $params[] = $tipo; $refs[] = &$params[count($params)-1]; }
        if ($emocion !== '') { $sql .= ' AND b.emocion = ?'; $tipos .= 's'; $params[] = $emocion; $refs[] = &$params[count($params)-1]; }
        if ($desde !== '') { $sql .= ' AND DATE(b.fecha_hora) >= ?'; $tipos .= 's'; $params[] = $desde; $refs[] = &$params[count($params)-1]; }
        if ($hasta !== '') { $sql .= ' AND DATE(b.fecha_hora) <= ?'; $tipos .= 's'; $params[] = $hasta; $refs[] = &$params[count($params)-1]; }
        $sql .= ' ORDER BY b.fecha_hora DESC LIMIT 200';
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException($this->connection->error);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$tipos], $refs));
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarNota(int $idDocente, int $idAlumno, string $tipo, string $emocion, string $accion, string $descripcion, ?int $idSesion = null): bool
    {
        if (!in_array($tipo, ['Observacion', 'Incidencia', 'Logro'], true)) throw new InvalidArgumentException('El tipo de nota no es válido.');
        if (!$this->alumnoAsignado($idDocente, $idAlumno)) throw new RuntimeException('El alumno no pertenece a un grupo asignado a este maestro.');
        if ($idSesion !== null && !$this->sesionValidaParaAlumno($idSesion, $idDocente, $idAlumno)) throw new RuntimeException('La sesión activa no corresponde al alumno seleccionado.');
        if ($idSesion === null) {
            $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, emocion, nota_descripcion, accion_contencion) VALUES (?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''))");
            $stmt->bind_param('iissss', $idAlumno, $idDocente, $tipo, $emocion, $descripcion, $accion);
        } else {
            $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, id_sesion, tipo_nota, emocion, nota_descripcion, accion_contencion) VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''))");
            $stmt->bind_param('iiissss', $idAlumno, $idDocente, $idSesion, $tipo, $emocion, $descripcion, $accion);
        }
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        if ($tipo === 'Incidencia') $this->actualizarSemaforo($idAlumno);
        return true;
    }

    private function sesionValidaParaAlumno(int $idSesion, int $idDocente, int $idAlumno): bool
    {
        $sql = "SELECT s.id_sesion FROM sesiones_temporizador s
                INNER JOIN alumnos a ON a.id_grupo = s.id_grupo
                WHERE s.id_sesion = ? AND s.id_docente = ? AND a.id_alumno = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('iii', $idSesion, $idDocente, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function registrarIncidencia(int $idDocente, int $idAlumno, string $emocion, string $accion, string $descripcion): bool
    {
        if (!$this->alumnoAsignado($idDocente, $idAlumno)) {
            throw new RuntimeException('El alumno no pertenece a un grupo asignado a este maestro.');
        }
        $tipo = 'Incidencia';
        $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, emocion, nota_descripcion, accion_contencion) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissss', $idAlumno, $idDocente, $tipo, $emocion, $descripcion, $accion);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
        $this->actualizarSemaforo($idAlumno);
        return true;
    }

    private function actualizarSemaforo(int $idAlumno): void
    {
        $stmt = $this->connection->prepare("SELECT COUNT(*) AS total FROM bitacora_notas WHERE id_alumno = ? AND tipo_nota = 'Incidencia' AND fecha_hora >= NOW() - INTERVAL 24 HOUR");
        $stmt->bind_param('i', $idAlumno);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $estado = $total >= 3 ? 'Rojo' : ($total >= 1 ? 'Amarillo' : 'Verde');
        $update = $this->connection->prepare('UPDATE alumnos SET estado_semaforo = ? WHERE id_alumno = ?');
        $update->bind_param('si', $estado, $idAlumno);
        $update->execute();
    }
}
