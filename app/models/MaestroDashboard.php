<?php

require_once __DIR__ . '/../services/KairosMailer.php';

class MaestroDashboard
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function obtenerDatos(int $idDocente, ?int $idGrupo = null, bool $requiereSeleccion = false): array
    {
        $grupos = $this->obtenerGrupos($idDocente);
        if ($idGrupo !== null && !$this->grupoAsignado($idDocente, $idGrupo)) $idGrupo = null;
        $sinSeleccion = $requiereSeleccion && $idGrupo === null;
        return [
            'maestro' => $this->obtenerMaestro($idDocente),
            'grupos' => $grupos,
            'grupo_seleccionado' => $idGrupo,
'alumnos' => $sinSeleccion ? [] : $this->obtenerAlumnos($idDocente, $idGrupo),
            'incidencias' => $sinSeleccion ? [] : $this->obtenerIncidencias($idDocente, $idGrupo),
            'kpis' => $sinSeleccion ? ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0] : $this->obtenerKpis($idDocente, $idGrupo),
            'recompensas' => $this->obtenerRecompensas(),
        ];
    }

    private function obtenerMaestro(int $idDocente): array
    {
        $stmt = $this->connection->prepare("SELECT id_docente, nombre, correo FROM docentes WHERE id_docente = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: [];
    }

    public function obtenerGrupos(int $idDocente): array
    {
        $sql = "SELECT DISTINCT g.id_grupo, g.grado, g.grupo, g.ciclo_escolar,
                       CONCAT(g.grado, '° Grado ', g.grupo) AS nombre_grupo
                FROM docente_grupos dg
                INNER JOIN grupos g ON g.id_grupo = dg.id_grupo
                WHERE dg.id_docente = ? AND dg.estado = 'Activo' AND g.estado = 'Activo'
                ORDER BY g.grado, g.grupo, g.ciclo_escolar DESC";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerAlumnos(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT a.id_alumno,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS nombre_completo,
                       a.estado_semaforo,
                       a.puntos_acumulados,
                       g.id_grupo,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo,
                       COALESCE(MAX(b.fecha_hora), NULL) AS ultima_actualizacion,
                       COALESCE((SELECT b2.emocion FROM bitacora_notas b2
                                                                  WHERE b2.id_alumno = a.id_alumno AND b2.estado = 'Activo'

                                 ORDER BY b2.fecha_hora DESC LIMIT 1), 'Sin registro') AS ultima_emocion,
                       COALESCE((SELECT b3.accion_contencion FROM bitacora_notas b3
                                                                  WHERE b3.id_alumno = a.id_alumno AND b3.estado = 'Activo'

                                 ORDER BY b3.fecha_hora DESC LIMIT 1), '') AS ultima_accion
                FROM alumnos a
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = g.id_grupo
                LEFT JOIN bitacora_notas b ON b.id_alumno = a.id_alumno AND b.estado = 'Activo'

                WHERE dg.id_docente = ? AND dg.estado = 'Activo'
                  AND g.estado = 'Activo' AND a.estado = 'Activo'" . ($idGrupo !== null ? " AND g.id_grupo = ?" : '') . "
                GROUP BY a.id_alumno, a.nombre, a.apellido_paterno, a.apellido_materno,
                                                  a.estado_semaforo, a.puntos_acumulados, g.id_grupo, g.grado, g.grupo

                ORDER BY g.grado, g.grupo, a.apellido_paterno, a.apellido_materno, a.nombre";
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('ii', $idDocente, $idGrupo); else $stmt->bind_param('i', $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerIncidencias(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT b.id_nota, b.fecha_hora, b.emocion, b.accion_contencion, b.nota_descripcion,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                WHERE b.id_docente = ? AND dg.id_docente = ? AND dg.estado = 'Activo'
                                    AND b.tipo_nota = 'Incidencia' AND b.estado = 'Activo' AND a.estado = 'Activo' AND g.estado = 'Activo'" . ($idGrupo !== null ? " AND a.id_grupo = ?" : '') . "

                ORDER BY b.fecha_hora DESC LIMIT 30";
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('iii', $idDocente, $idDocente, $idGrupo); else $stmt->bind_param('ii', $idDocente, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerKpis(int $idDocente, ?int $idGrupo = null): array
    {
        $sql = "SELECT
                    COUNT(DISTINCT a.id_alumno) AS alumnos,
                    COALESCE(SUM(CASE WHEN a.estado_semaforo = 'Amarillo' THEN 1 ELSE 0 END), 0) AS atencion,
                    COALESCE(SUM(CASE WHEN a.estado_semaforo = 'Rojo' THEN 1 ELSE 0 END), 0) AS crisis,
                    (SELECT COUNT(*) FROM bitacora_notas b2
                     INNER JOIN alumnos a2 ON a2.id_alumno = b2.id_alumno
                     INNER JOIN docente_grupos dg2 ON dg2.id_grupo = a2.id_grupo
                     WHERE dg2.id_docente = ? AND dg2.estado = 'Activo'
                                              AND b2.id_docente = ? AND b2.estado = 'Activo' AND b2.accion_contencion IS NOT NULL

                       AND b2.accion_contencion <> '' AND DATE(b2.fecha_hora) = CURDATE()" . ($idGrupo !== null ? " AND a2.id_grupo = ?" : '') . ") AS contenciones
                FROM alumnos a
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = g.id_grupo
                WHERE dg.id_docente = ? AND dg.estado = 'Activo'
                  AND g.estado = 'Activo' AND a.estado = 'Activo'" . ($idGrupo !== null ? " AND a.id_grupo = ?" : '');
        $stmt = $this->connection->prepare($sql);
        if ($idGrupo !== null) $stmt->bind_param('iiiii', $idDocente, $idDocente, $idGrupo, $idDocente, $idGrupo);
        else $stmt->bind_param('iii', $idDocente, $idDocente, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0];
    }

    private function grupoAsignado(int $idDocente, int $idGrupo): bool
    {
        $stmt = $this->connection->prepare("SELECT id_docente_grupo FROM docente_grupos WHERE id_docente = ? AND id_grupo = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('ii', $idDocente, $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function alumnoAsignado(int $idDocente, int $idAlumno): bool
    {
        $sql = "SELECT a.id_alumno FROM alumnos a
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                WHERE a.id_alumno = ? AND a.estado = 'Activo'
                  AND g.estado = 'Activo' AND dg.id_docente = ? AND dg.estado = 'Activo' LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('ii', $idAlumno, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

        public function obtenerRecompensas(): array
    {
        $resultado = $this->connection->query("SELECT id_recompensa, nombre_insignia, descripcion, puntos_otorgados, icono_url
                                               FROM recompensas_catalogo
                                               WHERE estado = 'Activo'
                                               ORDER BY nombre_insignia ASC");
        if (!$resultado) throw new RuntimeException('No fue posible cargar el catálogo de medallas.');
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function asignarMedalla(int $idDocente, int $idAlumno, int $idRecompensa, string $descripcion, ?int $idSesion = null, bool $notificarTutor = false): bool
    {
        $descripcion = trim($descripcion);
        if ($descripcion === '') throw new InvalidArgumentException('Escribe una breve descripción del logro.');
        if (mb_strlen($descripcion) > 1000) throw new InvalidArgumentException('La descripción no puede superar los 1000 caracteres.');
        if (!$this->alumnoAsignado($idDocente, $idAlumno)) throw new RuntimeException('El alumno no pertenece a un grupo asignado a este maestro.');
        $stmt = $this->connection->prepare("SELECT puntos_otorgados FROM recompensas_catalogo WHERE id_recompensa = ? AND estado = 'Activo' LIMIT 1");
        $stmt->bind_param('i', $idRecompensa);
        $stmt->execute();
        $recompensa = $stmt->get_result()->fetch_assoc();
        if (!$recompensa) throw new RuntimeException('La medalla seleccionada no está disponible.');
        $puntos = (int)$recompensa['puntos_otorgados'];
        $notificado = $notificarTutor ? 1 : 0;
        $tipo = 'Medalla';
        if ($idSesion !== null && !$this->sesionValidaParaAlumno($idSesion, $idDocente, $idAlumno)) {
            throw new RuntimeException('La sesión seleccionada no corresponde al alumno ni al maestro.');
        }
        if ($notificarTutor) $this->enviarCorreoTutores($idAlumno, $tipo, '', $descripcion, '');
        $this->connection->begin_transaction();
        try {
            if ($idSesion === null) {
                $insertar = $this->connection->prepare('INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, nota_descripcion, id_recompensa, puntos_otorgados, notificado_al_tutor) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $insertar->bind_param('iissiii', $idAlumno, $idDocente, $tipo, $descripcion, $idRecompensa, $puntos, $notificado);
            } else {
                $insertar = $this->connection->prepare('INSERT INTO bitacora_notas (id_alumno, id_docente, id_sesion, tipo_nota, nota_descripcion, id_recompensa, puntos_otorgados, notificado_al_tutor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                $insertar->bind_param('iiissiii', $idAlumno, $idDocente, $idSesion, $tipo, $descripcion, $idRecompensa, $puntos, $notificado);
            }
            if (!$insertar->execute()) throw new RuntimeException($insertar->error);
            $actualizar = $this->connection->prepare('UPDATE alumnos SET puntos_acumulados = puntos_acumulados + ? WHERE id_alumno = ?');
            $actualizar->bind_param('ii', $puntos, $idAlumno);
            if (!$actualizar->execute()) throw new RuntimeException($actualizar->error);
            $this->connection->commit();
        } catch (Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
        return true;
    }

    public function obtenerReporte(int $idDocente, array $filtros): array
    {
        $params = [];
        $types = '';
        $where = $this->construirFiltroReporte($idDocente, $filtros, $params, $types);
        $from = " FROM bitacora_notas b
                  INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                  INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                  INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo";

        $resumen = $this->ejecutarReporte(
            "SELECT COUNT(*) AS total,
                    COUNT(DISTINCT b.id_alumno) AS alumnos,
                    SUM(CASE WHEN b.tipo_nota = 'Incidencia' THEN 1 ELSE 0 END) AS incidencias,
                    SUM(CASE WHEN b.tipo_nota = 'Observacion' THEN 1 ELSE 0 END) AS observaciones,
                    SUM(CASE WHEN b.tipo_nota = 'Logro' THEN 1 ELSE 0 END) AS logros" . $from . $where,
            $types,
            $params
        )[0] ?? ['total' => 0, 'alumnos' => 0, 'incidencias' => 0, 'observaciones' => 0, 'logros' => 0];

        $emociones = $this->ejecutarReporte(
            "SELECT COALESCE(NULLIF(TRIM(b.emocion), ''), 'Sin emoción') AS etiqueta, COUNT(*) AS total" . $from . $where . " GROUP BY etiqueta ORDER BY total DESC, etiqueta ASC",
            $types,
            $params
        );
        $evolucion = $this->ejecutarReporte(
            "SELECT DATE_FORMAT(b.fecha_hora, '%Y-%m') AS periodo, COUNT(*) AS total" . $from . $where . " GROUP BY periodo ORDER BY periodo ASC",
            $types,
            $params
        );
        $alumnosSeguimiento = $this->ejecutarReporte(
            "SELECT b.id_alumno, CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                    COUNT(*) AS registros,
                    SUM(CASE WHEN b.tipo_nota = 'Incidencia' THEN 1 ELSE 0 END) AS incidencias,
                    MAX(b.fecha_hora) AS ultimo_registro" . $from . $where . " GROUP BY b.id_alumno, alumno ORDER BY incidencias DESC, ultimo_registro DESC LIMIT 10",
            $types,
            $params
        );

        return [
            'resumen' => $resumen,
            'emociones' => $emociones,
            'evolucion' => $evolucion,
            'alumnos_seguimiento' => $alumnosSeguimiento,
            'historial' => $this->historial($idDocente, $filtros['id_alumno'] ?? null, $filtros['tipo'] ?? '', $filtros['emocion'] ?? '', $filtros['desde'] ?? '', $filtros['hasta'] ?? '', $filtros['id_grupo'] ?? null),
        ];
    }

    private function construirFiltroReporte(int $idDocente, array $filtros, array &$params, string &$types): string
    {
        $params = [$idDocente, $idDocente];
        $types = 'ii';
        $where = " WHERE b.id_docente = ? AND dg.id_docente = ?
                   AND b.estado = 'Activo' AND dg.estado = 'Activo' AND a.estado = 'Activo' AND g.estado = 'Activo'";
        if (!empty($filtros['id_grupo'])) { $where .= ' AND a.id_grupo = ?'; $types .= 'i'; $params[] = (int)$filtros['id_grupo']; }
        if (!empty($filtros['id_alumno'])) { $where .= ' AND b.id_alumno = ?'; $types .= 'i'; $params[] = (int)$filtros['id_alumno']; }
        if (($filtros['tipo'] ?? '') !== '') { $where .= ' AND b.tipo_nota = ?'; $types .= 's'; $params[] = (string)$filtros['tipo']; }
        if (($filtros['emocion'] ?? '') !== '') { $where .= ' AND b.emocion LIKE ?'; $types .= 's'; $params[] = '%' . (string)$filtros['emocion'] . '%'; }
        if (($filtros['desde'] ?? '') !== '') { $where .= ' AND DATE(b.fecha_hora) >= ?'; $types .= 's'; $params[] = (string)$filtros['desde']; }
        if (($filtros['hasta'] ?? '') !== '') { $where .= ' AND DATE(b.fecha_hora) <= ?'; $types .= 's'; $params[] = (string)$filtros['hasta']; }
        return $where;
    }

    private function ejecutarReporte(string $sql, string $types, array $params): array
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

    public function historial(int $idDocente, ?int $idAlumno = null, string $tipo = '', string $emocion = '', string $desde = '', string $hasta = '', ?int $idGrupo = null, string $estado = 'Activo'): array

    {
                $sql = "SELECT DISTINCT b.id_nota, b.id_alumno, b.tipo_nota, b.emocion, b.nota_descripcion,
                       b.accion_contencion, b.notificado_al_tutor, b.fecha_hora, b.estado, r.nombre_insignia, b.puntos_otorgados,

                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                LEFT JOIN recompensas_catalogo r ON r.id_recompensa = b.id_recompensa
                WHERE b.id_docente = ? AND dg.id_docente = ? AND dg.estado = 'Activo'
                  AND a.estado = 'Activo' AND g.estado = 'Activo'";
        $tipos = 'ii';
        $params = [$idDocente, $idDocente];
        $refs = [&$params[0], &$params[1]];
                if ($idGrupo !== null && $idGrupo > 0) { $sql .= ' AND a.id_grupo = ?'; $tipos .= 'i'; $params[] = $idGrupo; $refs[] = &$params[count($params)-1]; }
        if ($idAlumno !== null && $idAlumno > 0) { $sql .= ' AND b.id_alumno = ?'; $tipos .= 'i'; $params[] = $idAlumno; $refs[] = &$params[count($params)-1]; }
        if ($tipo !== '') { $sql .= ' AND b.tipo_nota = ?'; $tipos .= 's'; $params[] = $tipo; $refs[] = &$params[count($params)-1]; }
        if ($emocion !== '') { $sql .= ' AND b.emocion = ?'; $tipos .= 's'; $params[] = $emocion; $refs[] = &$params[count($params)-1]; }
        if ($desde !== '') { $sql .= ' AND DATE(b.fecha_hora) >= ?'; $tipos .= 's'; $params[] = $desde; $refs[] = &$params[count($params)-1]; }
                if ($hasta !== '') { $sql .= ' AND DATE(b.fecha_hora) <= ?'; $tipos .= 's'; $params[] = $hasta; $refs[] = &$params[count($params)-1]; }
        $estado = in_array($estado, ['Activo', 'Archivado', 'Todos'], true) ? $estado : 'Activo';
        if ($estado !== 'Todos') { $sql .= ' AND b.estado = ?'; $tipos .= 's'; $params[] = $estado; $refs[] = &$params[count($params)-1]; }
        $sql .= ' ORDER BY b.fecha_hora DESC LIMIT 200';

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException($this->connection->error);
        call_user_func_array([$stmt, 'bind_param'], array_merge([$tipos], $refs));
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function registrarNota(int $idDocente, int $idAlumno, string $tipo, string $emocion, string $accion, string $descripcion, ?int $idSesion = null, bool $notificarTutor = false): bool
    {
        if (!in_array($tipo, ['Observacion', 'Incidencia', 'Logro'], true)) throw new InvalidArgumentException('El tipo de nota no es válido.');
        $notificado = $notificarTutor ? 1 : 0;
        if (!$this->alumnoAsignado($idDocente, $idAlumno)) throw new RuntimeException('El alumno no pertenece a un grupo asignado a este maestro.');
        if ($idSesion !== null && !$this->sesionValidaParaAlumno($idSesion, $idDocente, $idAlumno)) throw new RuntimeException('La sesión activa no corresponde al alumno seleccionado.');
        if ($notificarTutor) $this->enviarCorreoTutores($idAlumno, $tipo, $emocion, $descripcion, $accion);
        if ($idSesion === null) {

            $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, emocion, nota_descripcion, accion_contencion, notificado_al_tutor) VALUES (?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?)");
            $stmt->bind_param('iissssi', $idAlumno, $idDocente, $tipo, $emocion, $descripcion, $accion, $notificado);
        } else {

            $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, id_sesion, tipo_nota, emocion, nota_descripcion, accion_contencion, notificado_al_tutor) VALUES (?, ?, ?, ?, NULLIF(?, ''), ?, NULLIF(?, ''), ?)");
            $stmt->bind_param('iiissssi', $idAlumno, $idDocente, $idSesion, $tipo, $emocion, $descripcion, $accion, $notificado);
        }
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        if ($tipo === 'Incidencia') $this->actualizarSemaforo($idAlumno);
        return true;
    }

        public function obtenerNotaParaEditar(int $idDocente, int $idNota): ?array
    {
        $sql = "SELECT b.id_nota, b.id_alumno, b.id_docente, b.tipo_nota, b.emocion,
                       b.nota_descripcion, b.accion_contencion, b.estado,
                       CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       CONCAT(g.grado, '° ', g.grupo) AS grupo
                FROM bitacora_notas b
                INNER JOIN alumnos a ON a.id_alumno = b.id_alumno
                INNER JOIN grupos g ON g.id_grupo = a.id_grupo
                INNER JOIN docente_grupos dg ON dg.id_grupo = a.id_grupo
                WHERE b.id_nota = ? AND b.id_docente = ?
                  AND dg.id_docente = ? AND dg.estado = 'Activo'
                  AND a.estado = 'Activo' AND g.estado = 'Activo'
                  AND b.tipo_nota <> 'Medalla'
                LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException($this->connection->error);
        $stmt->bind_param('iii', $idNota, $idDocente, $idDocente);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public function editarNota(int $idDocente, int $idNota, string $tipo, string $emocion, string $accion, string $descripcion): bool
    {
        $tipo = trim($tipo);
        $emocion = trim($emocion);
        $accion = trim($accion);
        $descripcion = trim($descripcion);
        if (!in_array($tipo, ['Observacion', 'Incidencia', 'Logro'], true)) throw new InvalidArgumentException('El tipo de nota no es válido.');
        if (mb_strlen($emocion) > 80) throw new InvalidArgumentException('La emoción no puede superar 80 caracteres.');
        if (mb_strlen($accion) > 150) throw new InvalidArgumentException('La acción de contención no puede superar 150 caracteres.');
        if ($descripcion === '') throw new InvalidArgumentException('La descripción es obligatoria.');
        if (mb_strlen($descripcion) > 2000) throw new InvalidArgumentException('La descripción no puede superar 2000 caracteres.');
        if ($tipo === 'Incidencia' && $accion === '') throw new InvalidArgumentException('Selecciona la acción de contención.');

        $nota = $this->obtenerNotaParaEditar($idDocente, $idNota);
        if (!$nota) throw new RuntimeException('La nota no existe, es una medalla protegida o no pertenece a un grupo asignado.');
        if ($nota['estado'] !== 'Activo') throw new RuntimeException('Restaura la nota antes de editarla.');

        $stmt = $this->connection->prepare(
            "UPDATE bitacora_notas
             SET tipo_nota = ?, emocion = NULLIF(?, ''), nota_descripcion = ?,
                 accion_contencion = NULLIF(?, '')
             WHERE id_nota = ? AND id_docente = ? AND tipo_nota <> 'Medalla'"
        );
        if (!$stmt) throw new RuntimeException($this->connection->error);
        $stmt->bind_param('ssssii', $tipo, $emocion, $descripcion, $accion, $idNota, $idDocente);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        if ($nota['tipo_nota'] === 'Incidencia' || $tipo === 'Incidencia') {
            $this->actualizarSemaforo((int)$nota['id_alumno']);
        }
        return true;
    }

    public function cambiarEstadoNota(int $idDocente, int $idNota, string $operacion): bool
    {
        if (!in_array($operacion, ['archivar', 'restaurar'], true)) throw new InvalidArgumentException('La operación de estado no es válida.');
        $nota = $this->obtenerNotaParaEditar($idDocente, $idNota);
        if (!$nota) throw new RuntimeException('La nota no existe, es una medalla protegida o no pertenece a un grupo asignado.');
        $estado = $operacion === 'archivar' ? 'Archivado' : 'Activo';
        $stmt = $this->connection->prepare(
            "UPDATE bitacora_notas SET estado = ?
             WHERE id_nota = ? AND id_docente = ? AND tipo_nota <> 'Medalla'"
        );
        if (!$stmt) throw new RuntimeException($this->connection->error);
        $stmt->bind_param('sii', $estado, $idNota, $idDocente);
        if (!$stmt->execute()) throw new RuntimeException($stmt->error);
        if ($nota['tipo_nota'] === 'Incidencia') $this->actualizarSemaforo((int)$nota['id_alumno']);
        return true;
    }

    private function sesionValidaParaAlumno(int $idSesion, int $idDocente, int $idAlumno): bool

    {
        $sql = "SELECT s.id_sesion FROM sesiones_temporizador s
                INNER JOIN alumnos a ON a.id_grupo = s.id_grupo
                WHERE s.id_sesion = ? AND s.id_docente = ? AND a.id_alumno = ? LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param('iii', $idSesion, $idDocente, $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->num_rows === 1;
    }

    public function registrarIncidencia(int $idDocente, int $idAlumno, string $emocion, string $accion, string $descripcion, bool $notificarTutor = false): bool
    {
        if (!$this->alumnoAsignado($idDocente, $idAlumno)) {
            throw new RuntimeException('El alumno no pertenece a un grupo asignado a este maestro.');
        }

        $tipo = 'Incidencia';
        $notificado = $notificarTutor ? 1 : 0;
        if ($notificarTutor) $this->enviarCorreoTutores($idAlumno, $tipo, $emocion, $descripcion, $accion);
        $stmt = $this->connection->prepare("INSERT INTO bitacora_notas (id_alumno, id_docente, tipo_nota, emocion, nota_descripcion, accion_contencion, notificado_al_tutor) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('iissssi', $idAlumno, $idDocente, $tipo, $emocion, $descripcion, $accion, $notificado);
        if (!$stmt->execute()) {
            throw new RuntimeException($stmt->error);
        }
        $this->actualizarSemaforo($idAlumno);
        return true;
    }

    private function enviarCorreoTutores(int $idAlumno, string $tipo, string $emocion, string $descripcion, string $accion): void
    {
        $sql = "SELECT CONCAT_WS(' ', a.nombre, a.apellido_paterno, a.apellido_materno) AS alumno,
                       t.nombre AS tutor_nombre, t.correo
                FROM alumno_tutores at
                INNER JOIN alumnos a ON a.id_alumno = at.id_alumno AND a.estado = 'Activo'
                INNER JOIN tutores t ON t.id_tutor = at.id_tutor AND t.estado = 'Activo'
                WHERE at.id_alumno = ? AND at.estado = 'Activo' AND t.correo <> ''";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar el envío al tutor.');
        $stmt->bind_param('i', $idAlumno);
        $stmt->execute();
        $tutores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        if ($tutores === []) throw new RuntimeException('El alumno no tiene un tutor activo con correo registrado.');

        $alumno = (string)$tutores[0]['alumno'];
        $esc = static fn(string $valor): string => htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
        $fecha = date('d/m/Y H:i');
        $contenido = '<!doctype html><html lang="es"><body style="margin:0;background:#f1f5f9;font-family:Arial,sans-serif;color:#1f2937;line-height:1.5">'
            . '<div style="max-width:620px;margin:24px auto;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e2e8f0">'
            . '<div style="padding:22px 26px;background:#335c81;color:#ffffff"><div style="font-size:12px;letter-spacing:1px;text-transform:uppercase;opacity:.85">Kairos</div><h1 style="margin:6px 0 0;font-size:23px">Nuevo aviso escolar</h1></div>'
            . '<div style="padding:26px"><p style="margin-top:0">Hola,</p><p>La escuela ha compartido una comunicación relacionada con el seguimiento de <strong>' . $esc($alumno) . '</strong>.</p>'
            . '<div style="margin:22px 0;padding:16px 18px;background:#f8fafc;border-left:4px solid #335c81;border-radius:6px">'
            . '<p style="margin:0 0 8px"><strong>Tipo de registro:</strong> ' . $esc($tipo) . '</p>'
            . ($emocion !== '' ? '<p style="margin:0 0 8px"><strong>Emoción registrada:</strong> ' . $esc($emocion) . '</p>' : '')
            . '<p style="margin:0"><strong>Descripción:</strong><br>' . nl2br($esc($descripcion)) . '</p>'
            . ($accion !== '' ? '<p style="margin:10px 0 0"><strong>Acción de contención:</strong> ' . $esc($accion) . '</p>' : '')
            . '</div><p style="color:#64748b;font-size:12px;margin-bottom:0">Fecha del aviso: ' . $esc($fecha) . '<br>Este mensaje fue generado automáticamente por Kairos. Para cualquier duda, comunícate con el docente.</p>'
            . '</div></div></body></html>';
        $asunto = 'Kairos: aviso escolar de ' . $alumno;
        $mailer = new KairosMailer();
        foreach ($tutores as $tutor) {
            $mailer->enviar((string)$tutor['correo'], (string)$tutor['tutor_nombre'], $asunto, $contenido);
        }
    }

    private function actualizarSemaforo(int $idAlumno): void
    {
                $stmt = $this->connection->prepare("SELECT COUNT(*) AS total FROM bitacora_notas WHERE id_alumno = ? AND tipo_nota = 'Incidencia' AND estado = 'Activo' AND fecha_hora >= NOW() - INTERVAL 24 HOUR");

        $stmt->bind_param('i', $idAlumno);
        $stmt->execute();
        $total = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $estado = $total >= 3 ? 'Rojo' : ($total >= 1 ? 'Amarillo' : 'Verde');
        $update = $this->connection->prepare('UPDATE alumnos SET estado_semaforo = ? WHERE id_alumno = ?');
        $update->bind_param('si', $estado, $idAlumno);
        $update->execute();
    }
}
