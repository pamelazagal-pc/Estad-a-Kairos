<?php
class Lectura
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
        $sql = "SELECT l.id_lectura, l.titulo, l.contenido, l.categoria_tematica,
                       l.tiempo_estimado_min, l.estado, l.fecha_registro,
                       COALESCE(a.nombre, 'Administrador') AS administrador
                FROM lecturas_cuentos l
                LEFT JOIN administradores a ON a.id_administrador = l.id_administrador";
        if ($estado !== 'Todos') $sql .= ' WHERE l.estado = ?';
        $sql .= ' ORDER BY l.id_lectura DESC';
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar el listado de lecturas.');
        if ($estado !== 'Todos') $stmt->bind_param('s', $estado);
        $stmt->execute();
        return ['filas' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'estado' => $estado];
    }

    public function listarActivas(string $categoria = ''): array
    {
        if ($categoria !== '') {
            $stmt = $this->connection->prepare("SELECT id_lectura, titulo, contenido, categoria_tematica, tiempo_estimado_min
                                                FROM lecturas_cuentos
                                                WHERE estado = 'Activo' AND categoria_tematica = ?
                                                ORDER BY fecha_registro DESC, titulo ASC");
            if (!$stmt) throw new RuntimeException('No fue posible preparar las lecturas activas.');
            $stmt->bind_param('s', $categoria);
        } else {
            $stmt = $this->connection->prepare("SELECT id_lectura, titulo, contenido, categoria_tematica, tiempo_estimado_min
                                                FROM lecturas_cuentos
                                                WHERE estado = 'Activo'
                                                ORDER BY fecha_registro DESC, titulo ASC");
        }
        if (!$stmt) throw new RuntimeException('No fue posible preparar las lecturas activas.');
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function listarCategoriasActivas(): array
    {
        $resultado = $this->connection->query("SELECT DISTINCT categoria_tematica
                                               FROM lecturas_cuentos
                                               WHERE estado = 'Activo'
                                               ORDER BY categoria_tematica ASC");
        if (!$resultado) throw new RuntimeException('No fue posible cargar las categorías de lecturas.');
        return array_column($resultado->fetch_all(MYSQLI_ASSOC), 'categoria_tematica');
    }

    public function obtener(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT id_lectura, titulo, contenido, categoria_tematica, tiempo_estimado_min, estado FROM lecturas_cuentos WHERE id_lectura = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function guardar(?int $id, string $titulo, string $contenido, string $categoria, int $tiempo, int $idAdministrador): bool
    {
        $titulo = trim($titulo);
        $contenido = trim($contenido);
        $categoria = trim($categoria);
        if ($titulo === '' || mb_strlen($titulo) > 150) throw new InvalidArgumentException('El título es obligatorio y no puede superar 150 caracteres.');
        if ($contenido === '') throw new InvalidArgumentException('El contenido de la lectura es obligatorio.');
        if (mb_strlen($contenido) > 65535) throw new InvalidArgumentException('El contenido de la lectura es demasiado largo.');
        if ($categoria === '' || mb_strlen($categoria) > 100) throw new InvalidArgumentException('La categoría temática es obligatoria y no puede superar 100 caracteres.');
        if ($tiempo < 1 || $tiempo > 240) throw new InvalidArgumentException('El tiempo estimado debe estar entre 1 y 240 minutos.');
        if ($id === null) {
            $stmt = $this->connection->prepare("INSERT INTO lecturas_cuentos (titulo, contenido, categoria_tematica, tiempo_estimado_min, estado, id_administrador) VALUES (?, ?, ?, ?, 'Activo', ?)");
            if (!$stmt) throw new RuntimeException('No fue posible preparar el registro de la lectura.');
            $stmt->bind_param('sssii', $titulo, $contenido, $categoria, $tiempo, $idAdministrador);
        } else {
            $stmt = $this->connection->prepare('UPDATE lecturas_cuentos SET titulo = ?, contenido = ?, categoria_tematica = ?, tiempo_estimado_min = ? WHERE id_lectura = ?');
            if (!$stmt) throw new RuntimeException('No fue posible preparar la actualización de la lectura.');
            $stmt->bind_param('sssii', $titulo, $contenido, $categoria, $tiempo, $id);
        }
        if (!$stmt->execute()) throw new RuntimeException('No fue posible guardar la lectura: ' . $stmt->error);
        return true;
    }

    public function cambiarEstado(int $id, string $accion): bool
    {
        if ($id <= 0 || !in_array($accion, ['desactivar', 'reactivar'], true)) throw new InvalidArgumentException('Acción no válida.');
        $estado = $accion === 'desactivar' ? 'Inactivo' : 'Activo';
        $stmt = $this->connection->prepare('UPDATE lecturas_cuentos SET estado = ? WHERE id_lectura = ?');
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) throw new RuntimeException('No fue posible cambiar el estado de la lectura.');
        return true;
    }
}
