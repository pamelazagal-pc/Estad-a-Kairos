<?php
require_once __DIR__ . '/../models/Video.php';

class VideoController
{
    private $model;

    public function __construct($connection)
    {
        $this->model = new Video($connection);
    }

    public function ejecutar(int $idAdministrador): array
    {
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'videos' => [], 'formulario' => null, 'estado' => $_GET['estado'] ?? 'Todos'];
        try {
            $accion = $_POST['accion'] ?? '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($accion === 'guardar') {
                    $id = filter_input(INPUT_POST, 'id_video', FILTER_VALIDATE_INT) ?: null;
                    $titulo = trim((string)($_POST['titulo'] ?? ''));
                    $url = trim((string)($_POST['url_youtube'] ?? ''));
                    $duracion = (int)($_POST['duracion_segundos'] ?? 0);
                    $categoria = trim((string)($_POST['categoria'] ?? ''));
                    $this->model->guardar($id, $titulo, $url, $duracion, $categoria, $idAdministrador);
                    $resultado['mensaje'] = $id ? 'Video actualizado correctamente.' : 'Video registrado correctamente.';
                } elseif ($accion === 'estado') {
                    $id = (int)($_POST['id_video'] ?? 0);
                    $this->model->cambiarEstado($id, (string)($_POST['operacion'] ?? ''));
                    $resultado['mensaje'] = 'Estado del video actualizado correctamente.';
                }
                if ($resultado['mensaje']) {
                    $estado = urlencode((string)($_POST['estado_filtro'] ?? 'Todos'));
                    header('Location: videos.php?estado=' . $estado . '&mensaje=' . urlencode($resultado['mensaje']));
                    exit;
                }
            }
            $resultado['estado'] = (string)($_GET['estado'] ?? 'Todos');
            $resultado['videos'] = $this->model->listar($resultado['estado'])['filas'];
            if (isset($_GET['editar'])) $resultado['formulario'] = $this->model->obtener((int)$_GET['editar']);
            if (isset($_GET['mensaje'])) $resultado['mensaje'] = (string)$_GET['mensaje'];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $resultado['errores'][] = $e->getMessage();
        }
        return $resultado;
    }
}
