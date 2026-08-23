<?php

class Alumno
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function registrar(
        string $nombre,
        string $apellidoPaterno,
        ?string $apellidoMaterno,
        ?int $edad,
        int $idGrupo
    ): bool {
        $estadoSemaforo = 'Verde';
        $estado = 'Activo';
        $puntosAcumulados = 0;

        $sql = "INSERT INTO alumnos
                (nombre, apellido_paterno, apellido_materno, edad, id_grupo,
                 estado_semaforo, estado, puntos_acumulados)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar el registro del alumno: ' . $this->connection->error);
        }

        $statement->bind_param(
            'sssiissi',
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $edad,
            $idGrupo,
            $estadoSemaforo,
            $estado,
            $puntosAcumulados
        );

        if (!$statement->execute()) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('Error al registrar el alumno: ' . $error);
        }

        $statement->close();
        return true;
    }
}
