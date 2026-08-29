<?php

declare(strict_types=1);

class RecuperacionCuenta
{
    private mysqli $connection;

    private const TABLAS = [
        'administrador' => ['tabla' => 'administradores', 'id' => 'id_administrador', 'nombre' => 'nombre'],
        'maestro' => ['tabla' => 'docentes', 'id' => 'id_docente', 'nombre' => 'nombre'],
        'tutor' => ['tabla' => 'tutores', 'id' => 'id_tutor', 'nombre' => 'nombre'],
    ];

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function solicitar(string $tipoUsuario, string $correo): ?array
    {
        $configuracion = $this->configuracion($tipoUsuario);
        $statement = $this->connection->prepare("SELECT {$configuracion['id']} AS id_usuario, {$configuracion['nombre']} AS nombre, correo FROM {$configuracion['tabla']} WHERE correo = ? AND estado = 'Activo' LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible buscar la cuenta.');
        $statement->bind_param('s', $correo);
        $statement->execute();
        $cuenta = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();
        if (!$cuenta) return null;

        $inactivar = $this->connection->prepare("UPDATE tokens_recuperacion SET usado_at = NOW() WHERE tipo_usuario = ? AND id_usuario = ? AND usado_at IS NULL");
        if (!$inactivar) throw new RuntimeException('No fue posible preparar el token de recuperación.');
        $inactivar->bind_param('si', $tipoUsuario, $cuenta['id_usuario']);
        $inactivar->execute();
        $inactivar->close();

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiraAt = date('Y-m-d H:i:s', time() + 3600);
        $insertar = $this->connection->prepare('INSERT INTO tokens_recuperacion (tipo_usuario, id_usuario, token_hash, expira_at) VALUES (?, ?, ?, ?)');
        if (!$insertar) throw new RuntimeException('No fue posible crear el token de recuperación.');
        $idUsuario = (int)$cuenta['id_usuario'];
        $insertar->bind_param('siss', $tipoUsuario, $idUsuario, $tokenHash, $expiraAt);
        if (!$insertar->execute()) {
            $error = $insertar->error;
            $insertar->close();
            throw new RuntimeException('No fue posible guardar el token de recuperación: ' . $error);
        }
        $insertar->close();
        return ['id_usuario' => $idUsuario, 'nombre' => (string)$cuenta['nombre'], 'correo' => (string)$cuenta['correo'], 'token' => $token, 'expira_at' => $expiraAt];
    }

    public function validarToken(string $token): ?array
    {
        $tokenHash = hash('sha256', trim($token));
        $statement = $this->connection->prepare("SELECT id_token, tipo_usuario, id_usuario, expira_at FROM tokens_recuperacion WHERE token_hash = ? AND usado_at IS NULL AND expira_at > NOW() LIMIT 1");
        if (!$statement) throw new RuntimeException('No fue posible validar el token.');
        $statement->bind_param('s', $tokenHash);
        $statement->execute();
        $datos = $statement->get_result()->fetch_assoc() ?: null;
        $statement->close();
        return $datos;
    }

    public function cambiarPassword(string $token, string $nuevaPassword): bool
    {
        $this->connection->begin_transaction();
        try {
            $datos = $this->validarToken($token);
            if (!$datos) throw new InvalidArgumentException('El enlace no es válido o ya expiró.');
            $configuracion = $this->configuracion((string)$datos['tipo_usuario']);
            $hash = password_hash($nuevaPassword, PASSWORD_DEFAULT);
            if ($hash === false) throw new RuntimeException('No fue posible cifrar la nueva contraseña.');
            $idUsuario = (int)$datos['id_usuario'];
            $actualizar = $this->connection->prepare("UPDATE {$configuracion['tabla']} SET password_hash = ? WHERE {$configuracion['id']} = ? AND estado = 'Activo'");
            if (!$actualizar) throw new RuntimeException('No fue posible actualizar la contraseña.');
            $actualizar->bind_param('si', $hash, $idUsuario);
            if (!$actualizar->execute() || $actualizar->affected_rows < 1) {
                $actualizar->close();
                throw new RuntimeException('La cuenta ya no está disponible.');
            }
            $actualizar->close();
            $marcar = $this->connection->prepare('UPDATE tokens_recuperacion SET usado_at = NOW() WHERE id_token = ? AND usado_at IS NULL');
            if (!$marcar) throw new RuntimeException('No fue posible cerrar el token.');
            $idToken = (int)$datos['id_token'];
            $marcar->bind_param('i', $idToken);
            if (!$marcar->execute()) throw new RuntimeException('No fue posible cerrar el token.');
            $marcar->close();
            $this->connection->commit();
            return true;
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    private function configuracion(string $tipoUsuario): array
    {
        if (!isset(self::TABLAS[$tipoUsuario])) throw new InvalidArgumentException('El tipo de usuario no es válido.');
        return self::TABLAS[$tipoUsuario];
    }
}
