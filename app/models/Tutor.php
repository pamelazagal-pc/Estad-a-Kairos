<?php

class Tutor
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function correoExiste(string $correo): bool
    {
        $sql = "SELECT id_tutor FROM tutores WHERE correo = ? LIMIT 1";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar la consulta del tutor: ' . $this->connection->error);
        }

        $statement->bind_param('s', $correo);
        $statement->execute();
        $statement->store_result();
        $existe = $statement->num_rows > 0;
        $statement->close();

        return $existe;
    }

    public function registrar(
        string $nombre,
        string $cargo,
        string $correo,
        string $password,
        ?string $telefono
    ): bool {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('No fue posible cifrar la contraseña del tutor.');
        }

        $estado = 'Activo';
        $sql = "INSERT INTO tutores
                (nombre, cargo, correo, password_hash, telefono, estado)
                VALUES (?, ?, ?, ?, ?, ?)";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar el registro del tutor: ' . $this->connection->error);
        }

        $statement->bind_param(
            'ssssss',
            $nombre,
            $cargo,
            $correo,
            $passwordHash,
            $telefono,
            $estado
        );

        if (!$statement->execute()) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('Error al registrar el tutor: ' . $error);
        }

        $statement->close();
        return true;
    }
}
