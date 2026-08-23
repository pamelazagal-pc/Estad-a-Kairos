<?php

class Grupo
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function existe(int $grado, string $grupo, string $cicloEscolar): bool
    {
        $sql = "SELECT id_grupo
                FROM grupos
                WHERE grado = ? AND grupo = ? AND ciclo_escolar = ?
                LIMIT 1";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar la consulta del grupo: ' . $this->connection->error);
        }

        $statement->bind_param('iss', $grado, $grupo, $cicloEscolar);
        $statement->execute();
        $statement->store_result();
        $existe = $statement->num_rows > 0;
        $statement->close();

        return $existe;
    }

    public function listarActivos(): array
    {
        $sql = "SELECT id_grupo, grado, grupo, ciclo_escolar
                FROM grupos
                WHERE estado = 'Activo'
                ORDER BY grado, grupo, ciclo_escolar";
        $resultado = $this->connection->query($sql);

        if (!$resultado) {
            throw new RuntimeException('Error al consultar los grupos: ' . $this->connection->error);
        }

        $grupos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $grupos[] = $fila;
        }

        return $grupos;
    }

    public function registrar(int $grado, string $grupo, string $cicloEscolar): bool
    {
        $estado = 'Activo';
        $sql = "INSERT INTO grupos (grado, grupo, ciclo_escolar, estado)
                VALUES (?, ?, ?, ?)";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar el registro del grupo: ' . $this->connection->error);
        }

        $statement->bind_param('isss', $grado, $grupo, $cicloEscolar, $estado);

        if (!$statement->execute()) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('Error al registrar el grupo: ' . $error);
        }

        $statement->close();
        return true;
    }
}
