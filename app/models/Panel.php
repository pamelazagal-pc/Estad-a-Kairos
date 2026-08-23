<?php

class Panel
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function obtenerResumen(): array
    {
        $consultas = [
            'administradores' => "SELECT COUNT(*) AS total FROM administradores WHERE estado = 'Activo'",
            'docentes' => "SELECT COUNT(*) AS total FROM docentes WHERE estado = 'Activo'",
            'alumnos' => "SELECT COUNT(*) AS total FROM alumnos WHERE estado = 'Activo'",
            'grupos' => "SELECT COUNT(*) AS total FROM grupos WHERE estado = 'Activo'",
        ];

        $resumen = [];

        foreach ($consultas as $clave => $sql) {
            $resultado = $this->connection->query($sql);

            if (!$resultado) {
                throw new RuntimeException('Error al consultar el resumen del panel: ' . $this->connection->error);
            }

            $fila = $resultado->fetch_assoc();
            $resumen[$clave] = (int) ($fila['total'] ?? 0);
        }

        return $resumen;
    }
}
