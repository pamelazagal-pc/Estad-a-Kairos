<?php
class Kairos
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function grupos(): array
    {
        $resultado = $this->connection->query("SELECT id_grupo, grado, grupo, ciclo_escolar, estado FROM grupos ORDER BY grado, grupo, ciclo_escolar DESC");
        if (!$resultado) {
            throw new RuntimeException($this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function grupo(int $id): ?array
    {
        $statement = $this->connection->prepare("SELECT id_grupo, grado, grupo, ciclo_escolar, estado FROM grupos WHERE id_grupo = ?");
        $statement->bind_param('i', $id);
        $statement->execute();
        $resultado = $statement->get_result();
        return $resultado->fetch_assoc() ?: null;
    }

    public function guardarGrupo(int $grado, string $grupo, string $ciclo, ?int $id = null): bool
    {
        if ($id) {
            $statement = $this->connection->prepare("UPDATE grupos SET grado = ?, grupo = ?, ciclo_escolar = ? WHERE id_grupo = ?");
            $statement->bind_param('issi', $grado, $grupo, $ciclo, $id);
        } else {
            $statement = $this->connection->prepare("INSERT INTO grupos (grado, grupo, ciclo_escolar) VALUES (?, ?, ?)");
            $statement->bind_param('iss', $grado, $grupo, $ciclo);
        }
        return $statement->execute();
    }

    public function eliminarGrupo(int $id): bool
    {
        $statement = $this->connection->prepare("UPDATE grupos SET estado = 'Inactivo' WHERE id_grupo = ?");
        $statement->bind_param('i', $id);
        return $statement->execute();
    }

    public function alumnos(): array
    {
        $sql = "SELECT a.id_alumno, a.nombre, a.apellido_paterno, a.apellido_materno, a.edad, a.id_grupo, a.estado_semaforo, a.estado, g.grado, g.grupo, g.ciclo_escolar
                FROM alumnos a INNER JOIN grupos g ON g.id_grupo = a.id_grupo WHERE a.estado = 'Activo' ORDER BY a.apellido_paterno, a.apellido_materno, a.nombre";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException($this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function guardarAlumno(string $nombre, string $paterno, ?string $materno, ?int $edad, int $grupo, ?int $id = null): bool
    {
        if (!$this->grupoActivoExiste($grupo)) {
            throw new InvalidArgumentException('Debes asignar un grupo activo al alumno.');
        }
        if ($id) {
            $statement = $this->connection->prepare("UPDATE alumnos SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, edad = ?, id_grupo = ? WHERE id_alumno = ?");
            $statement->bind_param('sssiii', $nombre, $paterno, $materno, $edad, $grupo, $id);
        } else {
            $statement = $this->connection->prepare("INSERT INTO alumnos (nombre, apellido_paterno, apellido_materno, edad, id_grupo) VALUES (?, ?, ?, ?, ?)");
            $statement->bind_param('sssii', $nombre, $paterno, $materno, $edad, $grupo);
        }
        return $statement->execute();
    }

    public function eliminarAlumno(int $id): bool
    {
        $statement = $this->connection->prepare("UPDATE alumnos SET estado = 'Inactivo' WHERE id_alumno = ?");
        $statement->bind_param('i', $id);
        return $statement->execute();
    }

    public function incidencias(): array
    {
        $sql = "SELECT b.id_nota, b.id_alumno, b.emocion, b.accion_contencion, b.nota_descripcion, b.fecha_hora,
                       CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', COALESCE(a.apellido_materno, '')) AS alumno
                FROM bitacora_notas b INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                WHERE b.tipo_nota = 'Incidencia' ORDER BY b.fecha_hora DESC LIMIT 50";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException($this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function primerMaestroActivo(): ?int
    {
        $resultado = $this->connection->query("SELECT id_docente FROM docentes WHERE estado = 'Activo' ORDER BY id_docente LIMIT 1");
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        return $fila ? (int)$fila['id_docente'] : null;
    }

    public function registrarIncidencia(int $alumno, string $contencion, string $emocion, string $descripcion, int $maestro): bool
    {
        $tipo = 'Incidencia';
        $statement = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, emocion, nota_descripcion, accion_contencion) VALUES (?, ?, ?, ?, ?, ?)");
        $statement->bind_param('iissss', $alumno, $maestro, $tipo, $emocion, $descripcion, $contencion);
        if (!$statement->execute()) {
            throw new RuntimeException($statement->error);
        }
        $this->actualizarSemaforo($alumno);
        return true;
    }

    public function kpis(): array
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM alumnos WHERE estado = 'Activo') AS alumnos,
            (SELECT COUNT(*) FROM bitacora_notas WHERE tipo_nota = 'Incidencia' AND fecha_hora >= NOW() - INTERVAL 7 DAY) AS incidencias,
            (SELECT COUNT(*) FROM bitacora_notas WHERE tipo_nota = 'Incidencia' AND accion_contencion IS NOT NULL AND accion_contencion <> '' AND fecha_hora >= NOW() - INTERVAL 7 DAY) AS contenciones,
            (SELECT COUNT(*) FROM bitacora_notas WHERE tipo_nota = 'Incidencia' AND (notificado_al_tutor = 0 OR notificado_al_tutor IS NULL)) AS pendientes";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException($this->connection->error);
        }
        return $resultado->fetch_assoc();
    }

    private function grupoActivoExiste(int $grupo): bool
    {
        $statement = $this->connection->prepare("SELECT id_grupo FROM grupos WHERE id_grupo = ? AND estado = 'Activo'");
        $statement->bind_param('i', $grupo);
        $statement->execute();
        return $statement->get_result()->num_rows > 0;
    }

    private function actualizarSemaforo(int $alumno): void
    {
        $statement = $this->connection->prepare("SELECT COUNT(*) total, MAX(fecha_hora) ultima FROM bitacora_notas WHERE id_alumno = ? AND tipo_nota = 'Incidencia' AND fecha_hora >= NOW() - INTERVAL 24 HOUR");
        $statement->bind_param('i', $alumno);
        $statement->execute();
        $datos = $statement->get_result()->fetch_assoc();
        $total = (int)($datos['total'] ?? 0);
        $semaforo = $total >= 3 ? 'Rojo' : ($total >= 1 ? 'Amarillo' : 'Verde');
        $actualizar = $this->connection->prepare("UPDATE alumnos SET estado_semaforo = ? WHERE id_alumno = ?");
        $actualizar->bind_param('si', $semaforo, $alumno);
        $actualizar->execute();
    }
}

