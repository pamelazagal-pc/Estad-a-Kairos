<?php

class ReporteAdministrador
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function obtenerFiltros(): array
    {
        $grupos = $this->connection->query("SELECT id_grupo, grado, grupo, ciclo_escolar, CONCAT(grado, '° Grado ', grupo) AS nombre_grupo FROM grupos WHERE estado = 'Activo' ORDER BY grado, grupo");
        $docentes = $this->connection->query("SELECT id_docente, nombre FROM docentes WHERE estado = 'Activo' ORDER BY nombre");
        if (!$grupos || !$docentes) throw new RuntimeException($this->connection->error);
        return [
            'grupos' => $grupos->fetch_all(MYSQLI_ASSOC),
            'docentes' => $docentes->fetch_all(MYSQLI_ASSOC),
        ];
    }

    public function obtenerReporte(array $filtros): array
    {
        $params = [];
        $types = '';
        $where = $this->construirWhere($filtros, $params, $types);
        $from = " FROM bitacora_notas b
                  INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                  INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                  INNER JOIN docentes d ON d.id_docente = b.id_docente
                  INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo AND dg.id_docente = b.id_docente";

        $resumen = $this->ejecutar(
            "SELECT COUNT(*) AS total, COUNT(DISTINCT b.id_alumno) AS alumnos,
                    SUM(CASE WHEN b.tipo_nota = 'Incidencia' THEN 1 ELSE 0 END) AS incidencias,
                    SUM(CASE WHEN b.tipo_nota = 'Observacion' THEN 1 ELSE 0 END) AS observaciones,
                    SUM(CASE WHEN b.tipo_nota IN ('Logro', 'Medalla') THEN 1 ELSE 0 END) AS logros,
                    COALESCE(SUM(b.puntos_otorgados), 0) AS puntos" . $from . $where,
            $types,
            $params
        )[0] ?? ['total' => 0, 'alumnos' => 0, 'incidencias' => 0, 'observaciones' => 0, 'logros' => 0, 'puntos' => 0];

        $emociones = $this->ejecutar(
            "SELECT COALESCE(NULLIF(TRIM(b.emocion), ''), 'Sin emoción') AS etiqueta, COUNT(*) AS total" . $from . $where . " GROUP BY etiqueta ORDER BY total DESC, etiqueta ASC",
            $types,
            $params
        );
        $evolucion = $this->ejecutar(
            "SELECT DATE_FORMAT(b.fecha_hora, '%Y-%m') AS periodo, COUNT(*) AS total" . $from . $where . " GROUP BY periodo ORDER BY periodo ASC",
            $types,
            $params
        );
        $porTipo = $this->ejecutar(
            "SELECT b.tipo_nota AS etiqueta, COUNT(*) AS total" . $from . $where . " GROUP BY b.tipo_nota ORDER BY total DESC",
            $types,
            $params
        );
        $alumnos = $this->ejecutar(
            "SELECT CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                    CONCAT(g.grado, '° ', g.grupo) AS grupo, d.nombre AS docente,
                    COUNT(*) AS registros,
                    SUM(CASE WHEN b.tipo_nota = 'Incidencia' THEN 1 ELSE 0 END) AS incidencias,
                    MAX(b.fecha_hora) AS ultimo_registro" . $from . $where . " GROUP BY a.id_alumno, alumno, grupo, d.id_docente, d.nombre ORDER BY incidencias DESC, ultimo_registro DESC LIMIT 20",
            $types,
            $params
        );
        $historial = $this->ejecutar(
            "SELECT b.fecha_hora, CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                    CONCAT(g.grado, '° ', g.grupo) AS grupo, d.nombre AS docente,
                    b.tipo_nota, b.emocion, b.accion_contencion, b.nota_descripcion,
                    b.puntos_otorgados, b.notificado_al_tutor" . $from . $where . " ORDER BY b.fecha_hora DESC LIMIT 300",
            $types,
            $params
        );

        return compact('resumen', 'emociones', 'evolucion', 'porTipo', 'alumnos', 'historial');
    }

    private function construirWhere(array $filtros, array &$params, string &$types): string
    {
        $params = [];
        $types = '';
        $where = " WHERE b.estado = 'Activo' AND a.estado = 'Activo' AND g.estado = 'Activo' AND d.estado = 'Activo' AND dg.estado = 'Activo'";
        if (!empty($filtros['id_grupo'])) { $where .= ' AND a.id_grupo = ?'; $types .= 'i'; $params[] = (int)$filtros['id_grupo']; }
        if (!empty($filtros['id_docente'])) { $where .= ' AND b.id_docente = ?'; $types .= 'i'; $params[] = (int)$filtros['id_docente']; }
        if (($filtros['tipo'] ?? '') !== '') { $where .= ' AND b.tipo_nota = ?'; $types .= 's'; $params[] = (string)$filtros['tipo']; }
        if (($filtros['emocion'] ?? '') !== '') { $where .= ' AND b.emocion LIKE ?'; $types .= 's'; $params[] = '%' . (string)$filtros['emocion'] . '%'; }
        if (($filtros['desde'] ?? '') !== '') { $where .= ' AND DATE(b.fecha_hora) >= ?'; $types .= 's'; $params[] = (string)$filtros['desde']; }
        if (($filtros['hasta'] ?? '') !== '') { $where .= ' AND DATE(b.fecha_hora) <= ?'; $types .= 's'; $params[] = (string)$filtros['hasta']; }
        return $where;
    }

    private function ejecutar(string $sql, string $types, array $params): array
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException($this->connection->error);
        if ($types !== '') {
            $bind = [$types];
            foreach ($params as $indice => $valor) $bind[] = &$params[$indice];
            call_user_func_array([$stmt, 'bind_param'], $bind);
        }
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
