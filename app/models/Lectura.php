<?php
class Lectura
{
    private mysqli $connection;

    public function __construct(mysqli $connection)
    {
        $this->connection = $connection;
        $this->connection->set_charset('utf8mb4');
    }

    public function listarActivas(): array
    {
        $sql = "SELECT id_lectura, titulo, contenido, categoria_tematica, tiempo_estimado_min
                FROM lecturas_cuentos
                WHERE estado = 'Activo'
                ORDER BY fecha_registro DESC, titulo ASC";
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            throw new RuntimeException('No fue posible cargar las lecturas: ' . $this->connection->error);
        }
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
}
