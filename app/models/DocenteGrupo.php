<?php

class DocenteGrupo
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function docentesActivos(): array
    {
        $resultado = $this->connection->query("SELECT id_docente, nombre, correo FROM docentes WHERE estado = 'Activo' ORDER BY nombre");
        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar los maestros: ' . $this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function gruposActivos(): array
    {
        $resultado = $this->connection->query("SELECT id_grupo, grado, grupo, ciclo_escolar FROM grupos WHERE estado = 'Activo' ORDER BY ciclo_escolar DESC, grado, grupo");
        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar los grupos: ' . $this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function listar(string $estado = 'Todos'): array
    {
        $sql = "SELECT dg.id_docente_grupo, dg.id_docente, dg.id_grupo, dg.ciclo_escolar,
                       dg.es_titular, dg.estado, dg.fecha_asignacion,
                       d.nombre AS docente, d.correo,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM docente_grupos dg
                INNER JOIN docentes d ON d.id_docente = dg.id_docente
                INNER JOIN grupos g ON g.id_grupo = dg.id_grupo";
        if ($estado !== 'Todos') {
            $sql .= " WHERE dg.estado = ?";
        }
        $sql .= " ORDER BY dg.estado, dg.ciclo_escolar DESC, d.nombre, g.grado, g.grupo";
        $statement = $this->connection->prepare($sql);
        if (!$statement) {
            throw new RuntimeException('No fue posible preparar las asignaciones: ' . $this->connection->error);
        }
        if ($estado !== 'Todos') {
            $statement->bind_param('s', $estado);
        }
        $statement->execute();
        $resultado = $statement->get_result();
        $filas = $resultado->fetch_all(MYSQLI_ASSOC);
        $statement->close();
        return $filas;
    }

    public function guardar(int $idDocente, int $idGrupo, string $cicloEscolar, bool $esTitular): bool
    {
        if ($idDocente < 1 || $idGrupo < 1 || $cicloEscolar === '' || strlen($cicloEscolar) > 20) {
            throw new InvalidArgumentException('Los datos de la asignación no son válidos.');
        }
        $principal = $esTitular ? 1 : 0;
        $estado = 'Activo';
        $buscar = $this->connection->prepare('SELECT id_docente_grupo FROM docente_grupos WHERE id_docente = ? AND id_grupo = ? AND ciclo_escolar = ? LIMIT 1');
        $buscar->bind_param('iis', $idDocente, $idGrupo, $cicloEscolar);
        $buscar->execute();
        $buscar->store_result();
        $existe = $buscar->num_rows > 0;
        $idAsignacion = 0;
        if ($existe) {
            $buscar->bind_result($idAsignacion);
            $buscar->fetch();
        }
        $buscar->close();
        if ($existe) {
            $statement = $this->connection->prepare('UPDATE docente_grupos SET es_titular = ?, estado = ? WHERE id_docente_grupo = ?');
            $statement->bind_param('isi', $principal, $estado, $idAsignacion);
        } else {
            $statement = $this->connection->prepare('INSERT INTO docente_grupos (id_docente, id_grupo, ciclo_escolar, es_titular, estado) VALUES (?, ?, ?, ?, ?)');
            $statement->bind_param('iisis', $idDocente, $idGrupo, $cicloEscolar, $principal, $estado);
        }
        $correcto = $statement->execute();
        if (!$correcto) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('No fue posible guardar la asignación: ' . $error);
        }
        $statement->close();
        return true;
    }

    public function existeAsignacionActiva(int $idDocente, int $idGrupo, string $cicloEscolar): bool
    {
        $statement = $this->connection->prepare("SELECT id_docente_grupo FROM docente_grupos WHERE id_docente = ? AND id_grupo = ? AND ciclo_escolar = ? AND estado = 'Activo' LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible comprobar la asignación: ' . $this->connection->error);
        $statement->bind_param('iis', $idDocente, $idGrupo, $cicloEscolar);
        $statement->execute();
        $statement->store_result();
        $existe = $statement->num_rows > 0;
        $statement->close();
        return $existe;
    }

    public function datosAsignacion(int $idDocente, int $idGrupo, string $cicloEscolar): ?array
    {
        $statement = $this->connection->prepare("SELECT d.nombre AS docente, d.correo,
                       g.grado, g.grupo, dg.ciclo_escolar
                FROM docente_grupos dg
                INNER JOIN docentes d ON d.id_docente = dg.id_docente
                INNER JOIN grupos g ON g.id_grupo = dg.id_grupo
                WHERE dg.id_docente = ? AND dg.id_grupo = ? AND dg.ciclo_escolar = ?
                  AND dg.estado = 'Activo'
                ORDER BY dg.id_docente_grupo DESC LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible consultar la asignación: ' . $this->connection->error);
        $statement->bind_param('iis', $idDocente, $idGrupo, $cicloEscolar);
        $statement->execute();
        $fila = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();
        return $fila;
    }

    public function cambiarEstado(int $idAsignacion, string $estado): bool

    {
        if (!in_array($estado, ['Activo', 'Inactivo'], true)) {
            return false;
        }
        $statement = $this->connection->prepare('UPDATE docente_grupos SET estado = ? WHERE id_docente_grupo = ?');
        $statement->bind_param('si', $estado, $idAsignacion);
        $correcto = $statement->execute();
        $statement->close();
        return $correcto;
    }
}
?>
