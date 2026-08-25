<?php
class Recompensa
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function listar(string $estado = 'Todos'): array
    {
        $estados = ['Todos', 'Activo', 'Inactivo'];
        if (!in_array($estado, $estados, true)) $estado = 'Todos';
        $sql = "SELECT r.id_recompensa, r.nombre_insignia, r.descripcion, r.puntos_otorgados,
                       r.icono_url, r.estado, r.fecha_registro,
                       COALESCE(a.nombre, 'Administrador') AS administrador
                FROM recompensas_catalogo r
                LEFT JOIN administradores a ON a.id_administrador = r.id_administrador";
        if ($estado !== 'Todos') $sql .= ' WHERE r.estado = ?';
        $sql .= ' ORDER BY r.id_recompensa DESC';
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar el catálogo de recompensas.');
        if ($estado !== 'Todos') $stmt->bind_param('s', $estado);
        $stmt->execute();
        return ['filas' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'estado' => $estado];
    }

    public function listarActivas(): array
    {
        $resultado = $this->connection->query("SELECT id_recompensa, nombre_insignia, descripcion, puntos_otorgados, icono_url
                                               FROM recompensas_catalogo
                                               WHERE estado = 'Activo'
                                               ORDER BY nombre_insignia ASC");
        if (!$resultado) throw new RuntimeException('No fue posible cargar las recompensas activas.');
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT id_recompensa, nombre_insignia, descripcion, puntos_otorgados, icono_url, estado FROM recompensas_catalogo WHERE id_recompensa = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function guardar(?int $id, string $nombre, string $descripcion, int $puntos, string $icono, int $idAdministrador): bool
    {
        $nombre = trim($nombre);
        $descripcion = trim($descripcion);
        $icono = trim($icono);
        if ($nombre === '' || mb_strlen($nombre) > 100) throw new InvalidArgumentException('El nombre de la medalla es obligatorio y no puede superar 100 caracteres.');
        if ($descripcion === '') throw new InvalidArgumentException('La descripción de la medalla es obligatoria.');
        if ($puntos < 1 || $puntos > 10000) throw new InvalidArgumentException('Los puntos deben estar entre 1 y 10000.');
        if (mb_strlen($icono) > 255) throw new InvalidArgumentException('El icono no puede superar 255 caracteres.');
        if ($id === null) {
            $stmt = $this->connection->prepare("INSERT INTO recompensas_catalogo (nombre_insignia, descripcion, puntos_otorgados, icono_url, estado, id_administrador) VALUES (?, ?, ?, NULLIF(?, ''), 'Activo', ?)");
            $stmt->bind_param('ssisi', $nombre, $descripcion, $puntos, $icono, $idAdministrador);
        } else {
            $stmt = $this->connection->prepare("UPDATE recompensas_catalogo SET nombre_insignia = ?, descripcion = ?, puntos_otorgados = ?, icono_url = NULLIF(?, '') WHERE id_recompensa = ?");
            $stmt->bind_param('ssisi', $nombre, $descripcion, $puntos, $icono, $id);
        }
        if (!$stmt->execute()) {
            if ((int)$stmt->errno === 1062) throw new RuntimeException('Ya existe una medalla con ese nombre.');
            throw new RuntimeException('No fue posible guardar la medalla: ' . $stmt->error);
        }
        return true;
    }

    public function cambiarEstado(int $id, string $accion): bool
    {
        if ($id <= 0 || !in_array($accion, ['desactivar', 'reactivar'], true)) throw new InvalidArgumentException('Acción no válida.');
        $estado = $accion === 'desactivar' ? 'Inactivo' : 'Activo';
        $stmt = $this->connection->prepare('UPDATE recompensas_catalogo SET estado = ? WHERE id_recompensa = ?');
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) throw new RuntimeException('No fue posible cambiar el estado de la medalla.');
        return true;
    }
}
