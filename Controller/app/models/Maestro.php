<?php

class Maestro
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function correoExiste(string $correo): bool
    {
        $sql = "SELECT id_docente FROM docentes WHERE correo = ? LIMIT 1";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException(
                'No fue posible preparar la validación del correo: ' . $this->connection->error
            );
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
        string $correo,
        string $password,
        ?string $telefono = null
    ): bool {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('No fue posible proteger la contraseña.');
        }

        $sql = "INSERT INTO docentes
                (nombre, correo, password_hash, telefono, estado)
                VALUES (?, ?, ?, ?, 'Activo')";

        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException(
                'No fue posible preparar el registro del maestro: ' . $this->connection->error
            );
        }

        $statement->bind_param(
            'ssss',
            $nombre,
            $correo,
            $passwordHash,
            $telefono
        );

        $resultado = $statement->execute();

        if (!$resultado) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('Error al registrar el maestro: ' . $error);
        }

        $statement->close();
        return true;
    }
}
