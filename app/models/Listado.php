<?php

class Listado
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function obtener(string $tipo, string $filtroEstado = 'Todos'): array
    {
        $consultas = [
            'administradores' => [
                'titulo' => 'Administradores', 'columnas' => ['ID', 'Nombre', 'Correo', 'Estado', 'Tipo', 'Fecha de registro'],
                'tabla' => 'administradores', 'id' => 'id_administrador', 'estado' => 'estado', 'estado_index' => 3,
                'sql' => "SELECT id_administrador, nombre, correo, estado, CASE WHEN es_principal = 1 THEN 'Principal' ELSE 'Secundario' END AS tipo, fecha_registro FROM administradores",
            ],
            'maestros' => [
                'titulo' => 'Maestros', 'columnas' => ['ID', 'Nombre', 'Correo', 'Teléfono', 'Estado', 'Fecha de registro'],
                'tabla' => 'docentes', 'id' => 'id_docente', 'estado' => 'estado', 'estado_index' => 4,
                'sql' => "SELECT id_docente, nombre, correo, COALESCE(telefono, 'Sin teléfono') AS telefono, estado, fecha_registro FROM docentes",
            ],
            'tutores' => [
                'titulo' => 'Tutores', 'columnas' => ['ID', 'Nombre', 'Tipo', 'Correo', 'Teléfono', 'Estado'],
                'tabla' => 'tutores', 'id' => 'id_tutor', 'estado' => 'estado', 'estado_index' => 5,
                'sql' => "SELECT id_tutor, nombre, cargo, correo, COALESCE(telefono, 'Sin teléfono') AS telefono, estado FROM tutores",
            ],
            'grupos' => [
                'titulo' => 'Grupos', 'columnas' => ['ID', 'Grado', 'Grupo', 'Ciclo escolar', 'Estado'],
                'tabla' => 'grupos', 'id' => 'id_grupo', 'estado' => 'estado', 'estado_index' => 4,
                'sql' => "SELECT id_grupo, CONCAT(grado, '°') AS grado, grupo, ciclo_escolar, estado FROM grupos",
            ],
            'alumnos' => [
                'titulo' => 'Alumnos', 'columnas' => ['ID', 'Nombre completo', 'Edad', 'Grupo', 'Semáforo', 'Estado'],
                'tabla' => 'alumnos', 'id' => 'id_alumno', 'estado' => 'estado', 'estado_index' => 5,
                'sql' => "SELECT a.id_alumno, CONCAT(a.nombre, ' ', a.apellido_paterno, CASE WHEN a.apellido_materno IS NULL OR a.apellido_materno = '' THEN '' ELSE CONCAT(' ', a.apellido_materno) END) AS nombre_completo, COALESCE(CAST(a.edad AS CHAR), 'Sin edad') AS edad, CONCAT(g.grado, '° ', g.grupo, ' — ', g.ciclo_escolar) AS grupo, a.estado_semaforo, a.estado FROM alumnos a INNER JOIN grupos g ON g.id_grupo = a.id_grupo",
            ],
        ];
        if (!isset($consultas[$tipo])) throw new InvalidArgumentException('Listado no válido.');
        $definicion = $consultas[$tipo];
        $estados = ['Todos', 'Activo', 'Inactivo', 'Trasladado', 'Egresado'];
        if (!in_array($filtroEstado, $estados, true)) $filtroEstado = 'Todos';
        if ($filtroEstado !== 'Todos') {
            $filtro = $this->connection->real_escape_string($filtroEstado);
            $definicion['sql'] .= " WHERE {$definicion['estado']} = '{$filtro}'";
        }
        $definicion['sql'] .= " ORDER BY {$definicion['id']} DESC";
        $resultado = $this->connection->query($definicion['sql']);
        if (!$resultado) throw new RuntimeException('Error al cargar el listado: ' . $this->connection->error);
        $filas = [];
        while ($fila = $resultado->fetch_assoc()) $filas[] = array_values($fila);
        return ['titulo' => $definicion['titulo'], 'columnas' => $definicion['columnas'], 'filas' => $filas, 'filtro_estado' => $filtroEstado, 'estado_index' => $definicion['estado_index']];
    }

    public function cambiarEstado(string $tipo, int $id, string $accion): bool
    {
        $mapa = [
            'administradores' => ['tabla' => 'administradores', 'id' => 'id_administrador'],
            'maestros' => ['tabla' => 'docentes', 'id' => 'id_docente'],
            'tutores' => ['tabla' => 'tutores', 'id' => 'id_tutor'],
            'grupos' => ['tabla' => 'grupos', 'id' => 'id_grupo'],
            'alumnos' => ['tabla' => 'alumnos', 'id' => 'id_alumno'],
        ];
        if (!isset($mapa[$tipo]) || $id <= 0 || !in_array($accion, ['desactivar', 'reactivar'], true)) throw new InvalidArgumentException('Acción de estado no válida.');
        if ($tipo === 'administradores') {
            $consulta = $this->connection->prepare("SELECT es_principal FROM administradores WHERE id_administrador = ? LIMIT 1");
            $consulta->bind_param('i', $id); $consulta->execute();
            $consulta->bind_result($esPrincipal);
            $registro = $consulta->fetch() ? ['es_principal' => $esPrincipal] : null;
            $consulta->close();
            if ($registro && (int) $registro['es_principal'] === 1 && $accion === 'desactivar') throw new RuntimeException('El administrador principal no puede desactivarse.');
        }
        $estado = $accion === 'desactivar' ? 'Inactivo' : 'Activo';
        $sql = "UPDATE {$mapa[$tipo]['tabla']} SET estado = ? WHERE {$mapa[$tipo]['id']} = ?";
        $statement = $this->connection->prepare($sql);
        if (!$statement) throw new RuntimeException('No fue posible preparar el cambio de estado.');
        $statement->bind_param('si', $estado, $id); $correcto = $statement->execute(); $error = $statement->error; $statement->close();
        if (!$correcto) throw new RuntimeException('No fue posible actualizar el estado: ' . $error);
        return true;
    }
}
