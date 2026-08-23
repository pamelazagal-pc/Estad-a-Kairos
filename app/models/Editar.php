<?php

class Editar
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    private function mapa(string $tipo): array
    {
        $mapa = [
            'administradores' => ['tabla' => 'administradores', 'id' => 'id_administrador'],
            'maestros' => ['tabla' => 'docentes', 'id' => 'id_docente'],
            'tutores' => ['tabla' => 'tutores', 'id' => 'id_tutor'],
            'grupos' => ['tabla' => 'grupos', 'id' => 'id_grupo'],
            'alumnos' => ['tabla' => 'alumnos', 'id' => 'id_alumno'],
        ];
        if (!isset($mapa[$tipo])) throw new InvalidArgumentException('Tipo de registro no válido.');
        return $mapa[$tipo];
    }

    public function obtener(string $tipo, int $id): ?array
    {
        $mapa = $this->mapa($tipo);
        $columnas = [
            'administradores' => 'nombre, correo, estado',
            'maestros' => 'nombre, correo, telefono, estado',
            'tutores' => 'nombre, cargo, correo, telefono, estado',
            'grupos' => 'grado, grupo, ciclo_escolar, estado',
            'alumnos' => 'nombre, apellido_paterno, apellido_materno, edad, id_grupo, estado_semaforo, estado',
        ][$tipo];
        $stmt = $this->connection->prepare("SELECT {$columnas} FROM {$mapa['tabla']} WHERE {$mapa['id']} = ? LIMIT 1");
        $stmt->bind_param('i', $id); $stmt->execute();
        $resultado = $stmt->get_result();
        $registro = $resultado ? $resultado->fetch_assoc() : null;
        $stmt->close();
        return $registro ?: null;
    }

    public function actualizar(string $tipo, int $id, array $datos): bool
    {
        $mapa = $this->mapa($tipo);
        $password = trim($datos['password'] ?? '');
        $sets = [];
        $valores = [];
        $tipos = '';

        if ($tipo === 'administradores') {
            $sets = ['nombre = ?', 'correo = ?']; $valores = [$datos['nombre'], $datos['correo']]; $tipos = 'ss';
        } elseif ($tipo === 'maestros') {
            $sets = ['nombre = ?', 'correo = ?', 'telefono = ?']; $valores = [$datos['nombre'], $datos['correo'], $datos['telefono'] ?: null]; $tipos = 'sss';
        } elseif ($tipo === 'tutores') {
            $sets = ['nombre = ?', 'cargo = ?', 'correo = ?', 'telefono = ?']; $valores = [$datos['nombre'], $datos['cargo'], $datos['correo'], $datos['telefono'] ?: null]; $tipos = 'ssss';
        } elseif ($tipo === 'grupos') {
            $sets = ['grado = ?', 'grupo = ?', 'ciclo_escolar = ?']; $valores = [(int)$datos['grado'], $datos['grupo'], $datos['ciclo_escolar']]; $tipos = 'iss';
        } else {
            $sets = ['nombre = ?', 'apellido_paterno = ?', 'apellido_materno = ?', 'edad = ?', 'id_grupo = ?', 'estado_semaforo = ?', 'estado = ?'];
            $valores = [$datos['nombre'], $datos['apellido_paterno'], $datos['apellido_materno'] ?: null, $datos['edad'] === '' ? null : (int)$datos['edad'], (int)$datos['id_grupo'], $datos['estado_semaforo'], $datos['estado']];
            $tipos = 'sssii ss'; $tipos = str_replace(' ', '', $tipos);
        }

        if ($password !== '') {
            $sets[] = 'password_hash = ?'; $valores[] = password_hash($password, PASSWORD_DEFAULT); $tipos .= 's';
        }

        $valores[] = $id; $tipos .= 'i';
        $sql = "UPDATE {$mapa['tabla']} SET " . implode(', ', $sets) . " WHERE {$mapa['id']} = ?";
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) throw new RuntimeException('No fue posible preparar la actualización: ' . $this->connection->error);
        $parametros = [$tipos];
        foreach ($valores as $indice => $valor) {
            $parametros[] = &$valores[$indice];
        }
        call_user_func_array([$stmt, 'bind_param'], $parametros);
        $ok = $stmt->execute(); $error = $stmt->error; $stmt->close();
        if (!$ok) throw new RuntimeException('No fue posible actualizar el registro: ' . $error);
        return true;
    }

    public function grupos(): array
    {
        $resultado = $this->connection->query("SELECT id_grupo, grado, grupo, ciclo_escolar FROM grupos WHERE estado = 'Activo' ORDER BY grado, grupo");
        if (!$resultado) throw new RuntimeException('No fue posible cargar los grupos.');
        $grupos = []; while ($fila = $resultado->fetch_assoc()) $grupos[] = $fila;
        return $grupos;
    }
}
