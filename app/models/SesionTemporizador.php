<?php

class SesionTemporizador
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function grupoAsignado(int $idDocente, int $idGrupo): bool
    {
        $sql = "SELECT dg.id_docente_grupo
                FROM docente_grupos dg
                INNER JOIN docentes d ON d.id_docente = dg.id_docente
                INNER JOIN grupos g ON g.id_grupo = dg.id_grupo
                WHERE dg.id_docente = ? AND dg.id_grupo = ?
                  AND dg.estado = 'Activo' AND d.estado = 'Activo' AND g.estado = 'Activo'
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ii', $idDocente, $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function iniciar(int $idDocente, int $idGrupo, int $duracion, string $nivel): int
    {
        if (!$this->grupoAsignado($idDocente, $idGrupo)) {
            throw new InvalidArgumentException('El grupo no está asignado a este maestro.');
        }
        if ($duracion < 1 || $duracion > 120) throw new InvalidArgumentException('La duración debe estar entre 1 y 120 minutos.');
        if (!in_array($nivel, ['Bajo', 'Medio', 'Alto'], true)) throw new InvalidArgumentException('El nivel de irritabilidad no es válido.');

        $cerrar = $this->connection->prepare("UPDATE sesiones_temporizador SET estado = 'Interrumpido', fecha_fin = NOW() WHERE id_docente = ? AND estado = 'En curso'");
        $cerrar->bind_param('i', $idDocente);
        $cerrar->execute();

        $estado = 'En curso';
        $stmt = $this->connection->prepare("INSERT INTO sesiones_temporizador (fecha, id_docente, id_grupo, duracion_clase_min, nivel_irritabilidad, estado) VALUES (CURDATE(), ?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiss', $idDocente, $idGrupo, $duracion, $nivel, $estado);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        return (int)$stmt->insert_id;
    }

    public function cambiarEstado(int $idSesion, int $idDocente, string $estado): bool
    {
        if (!in_array($estado, ['En curso', 'Finalizado', 'Interrumpido'], true)) throw new InvalidArgumentException('El estado de sesión no es válido.');
        $stmt = $this->connection->prepare('UPDATE sesiones_temporizador SET estado = ?, fecha_fin = CASE WHEN ? IN (\'Finalizado\', \'Interrumpido\') THEN NOW() ELSE fecha_fin END WHERE id_sesion = ? AND id_docente = ?');
        $stmt->bind_param('ssii', $estado, $estado, $idSesion, $idDocente);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        if ($stmt->affected_rows < 1) throw new RuntimeException('La sesión no existe o no pertenece a este maestro.');
        return true;
    }

    public function pertenece(int $idSesion, int $idDocente): bool
    {
        $stmt = $this->connection->prepare('SELECT id_sesion FROM sesiones_temporizador WHERE id_sesion = ? AND id_docente = ? LIMIT 1');
        $stmt->bind_param('ii', $idSesion, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }
}
