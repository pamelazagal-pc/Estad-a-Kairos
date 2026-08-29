<?php

require_once __DIR__ . '/../models/Respaldo.php';

class RespaldoController
{
    private Respaldo $modelo;

    public function __construct(mysqli $connection, string $database, string $usuario, string $password, string $servidor)
    {
        $this->modelo = new Respaldo($connection, $database, $usuario, $password, $servidor);
    }

    public function ejecutar(string $accion, array $archivo = []): array
    {
        try {
            if ($accion === 'crear') {
                $respaldo = $this->modelo->crear();
                return ['ok' => true, 'mensaje' => 'Respaldo generado correctamente.', 'respaldo' => $respaldo];
            }
            if ($accion === 'restaurar') {
                if (!(bool)($_SESSION['administrador_principal'] ?? false)) {
                    throw new RuntimeException('Solo el administrador principal puede restaurar la base de datos.');
                }
                if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) throw new InvalidArgumentException('Selecciona un archivo SQL válido.');
                $respaldoPrevio = $this->modelo->restaurar($archivo['tmp_name'], (string)($archivo['name'] ?? ''));
                return ['ok' => true, 'mensaje' => 'La base de datos fue restaurada correctamente. Respaldo automático previo: ' . $respaldoPrevio['nombre'] . '.'];
            }
            if ($accion === 'descargar') {
                $ruta = $this->modelo->ruta((string)($_GET['archivo'] ?? ''));
                header('Content-Type: application/sql; charset=utf-8');
                header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
                header('Content-Length: ' . filesize($ruta));
                readfile($ruta);
                exit;
            }
            return ['ok' => true, 'mensaje' => null];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'mensaje' => $e->getMessage()];
        }
    }

    public function lista(): array
    {
        try { return $this->modelo->listar(); } catch (Throwable $e) { error_log($e->getMessage()); return []; }
    }
}

?>
