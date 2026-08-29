<?php

class Respaldo
{
    private mysqli $connection;
    private string $database;
    private string $usuario;
    private string $servidor;
    private string $password;
    private string $directorio;

    public function __construct(mysqli $connection, string $database, string $usuario, string $password, string $servidor)
    {
        $this->connection = $connection;
        $this->database = $database;
        $this->usuario = $usuario;
        $this->password = $password;
        $this->servidor = $servidor;
        $this->directorio = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($this->directorio)) {
            mkdir($this->directorio, 0750, true);
        }
    }

    public function listar(): array
    {
        $archivos = glob($this->directorio . DIRECTORY_SEPARATOR . '*.sql') ?: [];
        usort($archivos, static function ($a, $b) { return filemtime($b) <=> filemtime($a); });
        return array_map(static function ($ruta) {
            return [
                'nombre' => basename($ruta),
                'ruta' => $ruta,
                'tamano' => filesize($ruta),
                'fecha' => date('Y-m-d H:i:s', filemtime($ruta)),
            ];
        }, $archivos);
    }

    public function crear(): array
    {
        $binario = $this->buscarBinario('mysqldump');
        $nombre = 'kairos_db_' . date('Ymd_His') . '.sql';
        $ruta = $this->directorio . DIRECTORY_SEPARATOR . $nombre;
        $password = $this->password !== '' ? ' -p' . escapeshellarg($this->password) : '';
        $comando = escapeshellarg($binario) . ' --host=' . escapeshellarg($this->servidor)
            . ' --user=' . escapeshellarg($this->usuario) . $password
            . ' --single-transaction --routines --triggers --events --default-character-set=utf8mb4 '
            . escapeshellarg($this->database);
        $salida = fopen($ruta, 'wb');
        if (!$salida) throw new RuntimeException('No se pudo crear el archivo de respaldo.');
        $proceso = proc_open($comando, [1 => $salida, 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($proceso)) { fclose($salida); @unlink($ruta); throw new RuntimeException('No se pudo ejecutar mysqldump.'); }
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $codigo = proc_close($proceso);
        fclose($salida);
        if ($codigo !== 0 || !is_file($ruta) || filesize($ruta) < 20) {
            @unlink($ruta);
            throw new RuntimeException('No fue posible generar el respaldo: ' . trim($error));
        }
        return ['nombre' => $nombre, 'ruta' => $ruta, 'tamano' => filesize($ruta)];
    }

    public function restaurar(string $archivoTemporal, string $nombreOriginal): array
    {
        if (!is_file($archivoTemporal) || filesize($archivoTemporal) < 20) throw new InvalidArgumentException('El archivo SQL está vacío o no es válido.');
        $nombreOriginal = basename($nombreOriginal);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        if ($extension !== 'sql') throw new InvalidArgumentException('Solo se permiten archivos con extensión .sql.');
        // Conserva automáticamente la base actual antes de reemplazarla.
        $respaldoPrevio = $this->crear();
        $binario = $this->buscarBinario('mysql');
        $password = $this->password !== '' ? ' -p' . escapeshellarg($this->password) : '';
        $comando = escapeshellarg($binario) . ' --host=' . escapeshellarg($this->servidor)
            . ' --user=' . escapeshellarg($this->usuario) . $password
            . ' --default-character-set=utf8mb4 ' . escapeshellarg($this->database);
        $proceso = proc_open($comando, [['file', $archivoTemporal, 'r'], ['pipe', 'w'], ['pipe', 'w']], $pipes);
        if (!is_resource($proceso)) throw new RuntimeException('No se pudo ejecutar mysql para restaurar el respaldo.');
        $error = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        fclose($pipes[1]);
        $codigo = proc_close($proceso);
        if ($codigo !== 0) throw new RuntimeException('No fue posible restaurar el respaldo: ' . trim($error));
        return $respaldoPrevio;
    }

    public function ruta(string $nombre): string
    {
        if (!preg_match('/^kairos_db_[0-9]{8}_[0-9]{6}\.sql$/', $nombre)) throw new InvalidArgumentException('Nombre de respaldo no válido.');
        $ruta = $this->directorio . DIRECTORY_SEPARATOR . $nombre;
        if (!is_file($ruta)) throw new RuntimeException('El respaldo solicitado no existe.');
        return $ruta;
    }

    private function buscarBinario(string $nombre): string
    {
        $candidatos = PHP_OS_FAMILY === 'Windows'
            ? ['C:\\xampp\\mysql\\bin\\' . $nombre . '.exe', 'C:\\wamp64\\bin\\mysql\\mysql8.0.31\\bin\\' . $nombre . '.exe', $nombre . '.exe']
            : ['/usr/bin/' . $nombre, '/usr/local/bin/' . $nombre, $nombre];
        foreach ($candidatos as $candidato) if (is_file($candidato) || $candidato === $nombre . '.exe' || $candidato === $nombre) return $candidato;
        throw new RuntimeException('No se encontró ' . $nombre . '. Verifica la instalación de MySQL/XAMPP.');
    }
}

?>
