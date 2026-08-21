<?php

class Administrador
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function existePrincipal(): bool
    {
        $sql = "SELECT id_administrador
                FROM administradores
                WHERE es_principal = 1
                AND estado = 'Activo'
                LIMIT 1";

        $resultado = $this->connection->query($sql);

        if (!$resultado) {
            throw new RuntimeException('Error al consultar el administrador principal: ' . $this->connection->error);
        }

        return $resultado->num_rows > 0;
    }

    public function buscarPorCorreo(string $correo): ?array
    {
        $sql = "SELECT id_administrador, nombre, correo, password_hash,
                       estado, es_principal, debe_cambiar_password
                FROM administradores
                WHERE correo = ?
                LIMIT 1";

        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar la búsqueda: ' . $this->connection->error);
        }

        $statement->bind_param('s', $correo);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows === 0) {
            $statement->close();
            return null;
        }

        $statement->bind_result(
            $idAdministrador,
            $nombre,
            $correoEncontrado,
            $passwordHash,
            $estado,
            $esPrincipal,
            $debeCambiarPassword
        );
        $statement->fetch();
        $statement->close();

        return [
            'id_administrador' => $idAdministrador,
            'nombre' => $nombre,
            'correo' => $correoEncontrado,
            'password_hash' => $passwordHash,
            'estado' => $estado,
            'es_principal' => $esPrincipal,
            'debe_cambiar_password' => $debeCambiarPassword,
        ];
    }

    public function registrar(
        string $nombre,
        string $correo,
        string $password,
        bool $esPrincipal = false
    ): bool {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        if ($passwordHash === false) {
            throw new RuntimeException('No fue posible cifrar la contraseña.');
        }

        $estado = 'Activo';
        $principal = $esPrincipal ? 1 : 0;
        $debeCambiarPassword = 1;

        $sql = "INSERT INTO administradores
                (nombre, correo, password_hash, estado, es_principal, debe_cambiar_password)
                VALUES (?, ?, ?, ?, ?, ?)";

        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new RuntimeException('Error al preparar el registro: ' . $this->connection->error);
        }

        $statement->bind_param(
            'ssssii',
            $nombre,
            $correo,
            $passwordHash,
            $estado,
            $principal,
            $debeCambiarPassword
        );

        if (!$statement->execute()) {
            $error = $statement->error;
            $statement->close();
            throw new RuntimeException('Error al registrar el administrador: ' . $error);
        }

        $statement->close();
        return true;
    }

    public function correoExiste(string $correo): bool
    {
        return $this->buscarPorCorreo($correo) !== null;
    }
}
