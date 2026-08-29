<?php

class AlumnoTutor
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function alumnosActivos(): array
    {
        $sql = "SELECT a.id_alumno, CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', COALESCE(a.apellido_materno, '')) AS alumno,
                       g.grado, g.grupo, g.ciclo_escolar
                FROM alumnos a
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                WHERE a.estado = 'Activo' AND g.estado = 'Activo'
                ORDER BY a.nombre, a.apellido_paterno";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar los alumnos: ' . $this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function tutoresActivos(): array
    {
        $sql = "SELECT id_tutor, nombre, cargo, correo
                FROM tutores
                WHERE estado = 'Activo'
                ORDER BY nombre";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar los tutores: ' . $this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function relaciones(string $estado = 'Todos'): array
    {
        $sql = "SELECT at.id_alumno_tutor, at.id_alumno, at.id_tutor,
                       CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', COALESCE(a.apellido_materno, '')) AS alumno,
                       t.nombre AS tutor, t.cargo, at.parentesco, at.es_principal,
                       at.estado, at.fecha_registro
                FROM alumno_tutores at
                INNER JOIN alumnos a ON a.id_alumno = at.id_alumno
                INNER JOIN tutores t ON t.id_tutor = at.id_tutor";
        $parametros = [];
        $tipos = '';
        if ($estado !== 'Todos') {
            $sql .= " WHERE at.estado = ?";
            $parametros[] = $estado;
            $tipos = 's';
        }
        $sql .= ' ORDER BY a.nombre, t.nombre';
        $statement = $this->connection->prepare($sql);
        if (!$statement) {
            throw new RuntimeException('No fue posible preparar las relaciones: ' . $this->connection->error);
        }
        if ($parametros) {
            $statement->bind_param($tipos, $parametros[0]);
        }
        $statement->execute();
        $resultado = $statement->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        return $filas;
    }

    public function guardar(int $idAlumno, int $idTutor, ?string $parentesco, bool $esPrincipal): bool
    {
        if ($idAlumno <= 0 || $idTutor <= 0) {
            throw new InvalidArgumentException('Debes seleccionar un alumno y un tutor.');
        }
        $this->connection->begin_transaction();
        try {
            if ($esPrincipal) {
                $reset = $this->connection->prepare('UPDATE alumno_tutores SET es_principal = 0 WHERE id_alumno = ?');
                $reset->bind_param('i', $idAlumno);
                $reset->execute();
                $reset->close();
            }

            $buscar = $this->connection->prepare('SELECT id_alumno_tutor FROM alumno_tutores WHERE id_alumno = ? AND id_tutor = ? LIMIT 1');
            $buscar->bind_param('ii', $idAlumno, $idTutor);
            $buscar->execute();
            $buscar->store_result();
            $existe = $buscar->num_rows > 0;
            $idRelacion = 0;
            if ($existe) {
                $buscar->bind_result($idRelacion);
                $buscar->fetch();
            }
            $buscar->close();

            if ($existe) {
                $estado = 'Activo';
                $actualizar = $this->connection->prepare('UPDATE alumno_tutores SET parentesco = ?, es_principal = ?, estado = ? WHERE id_alumno_tutor = ?');
                $principal = $esPrincipal ? 1 : 0;
                $actualizar->bind_param('sisi', $parentesco, $principal, $estado, $idRelacion);
                $correcto = $actualizar->execute();
                $actualizar->close();
            } else {
                $estado = 'Activo';
                $principal = $esPrincipal ? 1 : 0;
                $insertar = $this->connection->prepare('INSERT INTO alumno_tutores (id_alumno, id_tutor, parentesco, es_principal, estado) VALUES (?, ?, ?, ?, ?)');
                $insertar->bind_param('iisis', $idAlumno, $idTutor, $parentesco, $principal, $estado);
                $correcto = $insertar->execute();
                $insertar->close();
            }
            if (!$correcto) {
                throw new RuntimeException('No fue posible guardar la relación.');
            }
            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function existeRelacionActiva(int $idAlumno, int $idTutor): bool
    {
        $statement = $this->connection->prepare("SELECT id_alumno_tutor FROM alumno_tutores WHERE id_alumno = ? AND id_tutor = ? AND estado = 'Activo' LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible comprobar la relación: ' . $this->connection->error);
        $statement->bind_param('ii', $idAlumno, $idTutor);
        $statement->execute();
        $statement->store_result();
        $existe = $statement->num_rows > 0;
        $statement->close();
        return $existe;
    }

    public function datosRelacion(int $idAlumno, int $idTutor): ?array
    {
        $statement = $this->connection->prepare("SELECT
                       CONCAT(a.nombre, ' ', a.apellido_paterno, ' ', COALESCE(a.apellido_materno, '')) AS alumno,
                       g.grado, g.grupo, g.ciclo_escolar,
                       t.nombre AS tutor, t.correo, at.parentesco
                FROM alumno_tutores at
                INNER JOIN alumnos a ON a.id_alumno = at.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN tutores t ON t.id_tutor = at.id_tutor
                WHERE at.id_alumno = ? AND at.id_tutor = ? AND at.estado = 'Activo'
                ORDER BY at.id_alumno_tutor DESC LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible consultar la relación: ' . $this->connection->error);
        $statement->bind_param('ii', $idAlumno, $idTutor);
        $statement->execute();
        $fila = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();
        return $fila;
    }

    public function cambiarEstado(int $idRelacion, string $estado): bool

    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            throw new InvalidArgumentException('Estado no válido.');
        }
        $statement = $this->connection->prepare('UPDATE alumno_tutores SET estado = ? WHERE id_alumno_tutor = ?');
        $statement->bind_param('si', $estado, $idRelacion);
        $correcto = $statement->execute();
        $statement->close();
        return $correcto;
    }
}
?>
