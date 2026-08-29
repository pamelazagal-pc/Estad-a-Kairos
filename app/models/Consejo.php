<?php

class Consejo
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
    }

    public function listar(string $estado = 'Todos'): array
    {
        $estado = in_array($estado, ['Todos', 'Activo', 'Inactivo'], true) ? $estado : 'Todos';
        $sql = "SELECT c.id_consejo, c.titulo, c.recomendacion, c.categoria, c.estado,
                       c.fecha_publicacion, c.id_docente_autor, c.id_administrador_autor,
                       COALESCE(d.nombre, a.nombre, 'Sin autor') AS autor
                FROM consejos_hogar c
                LEFT JOIN docentes d ON d.id_docente = c.id_docente_autor
                LEFT JOIN administradores a ON a.id_administrador = c.id_administrador_autor";

        if ($estado !== 'Todos') {
            $sql .= ' WHERE c.estado = ?';
        }
        $sql .= ' ORDER BY c.fecha_publicacion DESC, c.titulo ASC';

        if ($estado === 'Todos') {
            $resultado = $this->connection->query($sql);
        } else {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new RuntimeException('No fue posible preparar el listado de consejos.');
            }
            $stmt->bind_param('s', $estado);
            $stmt->execute();
            $resultado = $stmt->get_result();
        }

        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar los consejos para el hogar.');
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtener(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            "SELECT id_consejo, titulo, recomendacion, categoria, estado,
                    id_docente_autor, id_administrador_autor
             FROM consejos_hogar
             WHERE id_consejo = ?
             LIMIT 1"
        );
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar la consulta del consejo.');
        }
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function guardar(
        ?int $id,
        string $titulo,
        string $recomendacion,
        string $categoria,
        ?int $idDocenteAutor,
        ?int $idAdministradorAutor
    ): bool {
        $titulo = trim($titulo);
        $recomendacion = trim($recomendacion);
        $categoria = trim($categoria);

        if ($titulo === '' || mb_strlen($titulo) > 150) {
            throw new InvalidArgumentException('El título es obligatorio y no puede superar 150 caracteres.');
        }
        if ($recomendacion === '' || mb_strlen($recomendacion) > 65535) {
            throw new InvalidArgumentException('La recomendación es obligatoria y no puede superar 65535 caracteres.');
        }
        if ($categoria === '' || mb_strlen($categoria) > 100) {
            throw new InvalidArgumentException('La categoría es obligatoria y no puede superar 100 caracteres.');
        }
        if (($idDocenteAutor === null && $idAdministradorAutor === null)
            || ($idDocenteAutor !== null && $idAdministradorAutor !== null)) {
            throw new InvalidArgumentException('El consejo debe tener un único autor válido.');
        }

        if ($id === null) {
            if ($idDocenteAutor !== null) {
                $stmt = $this->connection->prepare(
                    "INSERT INTO consejos_hogar
                        (titulo, recomendacion, categoria, id_docente_autor, id_administrador_autor, estado)
                     VALUES (?, ?, ?, ?, NULL, 'Activo')"
                );
                if (!$stmt) {
                    throw new RuntimeException('No fue posible preparar el registro del consejo.');
                }
                $stmt->bind_param('sssi', $titulo, $recomendacion, $categoria, $idDocenteAutor);
            } else {
                $stmt = $this->connection->prepare(
                    "INSERT INTO consejos_hogar
                        (titulo, recomendacion, categoria, id_docente_autor, id_administrador_autor, estado)
                     VALUES (?, ?, ?, NULL, ?, 'Activo')"
                );
                if (!$stmt) {
                    throw new RuntimeException('No fue posible preparar el registro del consejo.');
                }
                $stmt->bind_param('sssi', $titulo, $recomendacion, $categoria, $idAdministradorAutor);
            }
        } else {
            if ($idDocenteAutor !== null) {
                $stmt = $this->connection->prepare(
                    'UPDATE consejos_hogar
                     SET titulo = ?, recomendacion = ?, categoria = ?,
                         id_docente_autor = ?, id_administrador_autor = NULL
                     WHERE id_consejo = ?'
                );
                if (!$stmt) {
                    throw new RuntimeException('No fue posible preparar la actualización del consejo.');
                }
                $stmt->bind_param('sssii', $titulo, $recomendacion, $categoria, $idDocenteAutor, $id);
            } else {
                $stmt = $this->connection->prepare(
                    'UPDATE consejos_hogar
                     SET titulo = ?, recomendacion = ?, categoria = ?,
                         id_docente_autor = NULL, id_administrador_autor = ?
                     WHERE id_consejo = ?'
                );
                if (!$stmt) {
                    throw new RuntimeException('No fue posible preparar la actualización del consejo.');
                }
                $stmt->bind_param('sssii', $titulo, $recomendacion, $categoria, $idAdministradorAutor, $id);
            }
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('No fue posible guardar el consejo: ' . $stmt->error);
        }
        return true;
    }

    public function cambiarEstado(int $id, string $operacion): bool
    {
        if ($id <= 0 || !in_array($operacion, ['desactivar', 'reactivar'], true)) {
            throw new InvalidArgumentException('La acción solicitada no es válida.');
        }

        $estado = $operacion === 'desactivar' ? 'Inactivo' : 'Activo';
        $stmt = $this->connection->prepare(
            'UPDATE consejos_hogar SET estado = ? WHERE id_consejo = ?'
        );
        if (!$stmt) {
            throw new RuntimeException('No fue posible preparar el cambio de estado.');
        }
        $stmt->bind_param('si', $estado, $id);
        if (!$stmt->execute()) {
            throw new RuntimeException('No fue posible cambiar el estado del consejo.');
        }
        return true;
    }
}
