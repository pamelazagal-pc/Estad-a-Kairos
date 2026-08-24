<?php
class Video
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function listar(string $estado = 'Todos'): array
    {
        $estados = ['Todos', 'Activo', 'Inactivo'];
        if (!in_array($estado, $estados, true)) $estado = 'Todos';
        $sql = "SELECT v.id_video, v.titulo, v.url_youtube, v.duracion_segundos, v.categoria,
                       v.estado, v.fecha_registro, COALESCE(a.nombre, 'Administrador') AS administrador
                FROM videos_pausas_activas v
                LEFT JOIN administradores a ON a.id_administrador = v.id_administrador";
        if ($estado !== 'Todos') $sql .= " WHERE v.estado = ?";
        $sql .= " ORDER BY v.id_video DESC";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar el listado de videos.');
        if ($estado !== 'Todos') $stmt->bind_param('s', $estado);
        $stmt->execute();
        return ['filas' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'estado' => $estado];
    }

    public function obtener(int $id): ?array
    {
        $stmt = $this->connection->prepare('SELECT id_video, titulo, url_youtube, duracion_segundos, categoria, estado FROM videos_pausas_activas WHERE id_video = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function guardar(?int $id, string $titulo, string $url, int $duracion, string $categoria, int $idAdministrador): bool
    {
        $videoId = $this->extraerIdYouTube($url);
        if ($videoId === null) throw new InvalidArgumentException('Ingresa un enlace válido de YouTube.');
        if ($titulo === '' || mb_strlen($titulo) > 150) throw new InvalidArgumentException('El título es obligatorio y no puede superar 150 caracteres.');
        if ($duracion < 1 || $duracion > 7200) throw new InvalidArgumentException('La duración debe estar entre 1 y 7200 segundos.');
        if ($categoria === '' || mb_strlen($categoria) > 100) throw new InvalidArgumentException('La categoría es obligatoria y no puede superar 100 caracteres.');
        $urlCanonica = 'https://www.youtube.com/watch?v=' . $videoId;
        if ($id === null) {
            $stmt = $this->connection->prepare("INSERT INTO videos_pausas_activas (titulo, url_youtube, duracion_segundos, categoria, estado, id_administrador) VALUES (?, ?, ?, ?, 'Activo', ?)");
            $stmt->bind_param('ssisi', $titulo, $urlCanonica, $duracion, $categoria, $idAdministrador);
        } else {
            $stmt = $this->connection->prepare('UPDATE videos_pausas_activas SET titulo = ?, url_youtube = ?, duracion_segundos = ?, categoria = ? WHERE id_video = ?');
            $stmt->bind_param('ssisi', $titulo, $urlCanonica, $duracion, $categoria, $id);
        }
        if (!$stmt->execute()) {
            if ((int)$stmt->errno === 1062) throw new RuntimeException('Ese video de YouTube ya está registrado.');
            throw new RuntimeException('No fue posible guardar el video: ' . $stmt->error);
        }
        return true;
    }

    public function cambiarEstado(int $id, string $accion): bool
    {
        if ($id <= 0 || !in_array($accion, ['desactivar', 'reactivar'], true)) throw new InvalidArgumentException('Acción no válida.');
        $estado = $accion === 'desactivar' ? 'Inactivo' : 'Activo';
        $stmt = $this->connection->prepare('UPDATE videos_pausas_activas SET estado = ? WHERE id_video = ?');
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) throw new RuntimeException('No fue posible cambiar el estado del video.');
        return true;
    }

    private function extraerIdYouTube(string $url): ?string
    {
        $partes = parse_url(trim($url));
        if (!$partes || empty($partes['host'])) return null;
        $host = strtolower($partes['host']);
        $host = preg_replace('/^www\./', '', $host);
        $id = null;
        if ($host === 'youtu.be') $id = trim($partes['path'] ?? '', '/');
        elseif (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            parse_str($partes['query'] ?? '', $query);
            $id = $query['v'] ?? null;
            if (!$id && strpos($partes['path'] ?? '', '/embed/') === 0) $id = substr($partes['path'], 7);
        }
        return is_string($id) && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
    }
}
