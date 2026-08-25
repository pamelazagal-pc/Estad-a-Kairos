<?php
require_once __DIR__ . '/../models/Lectura.php';

class LecturaController
{
    private Lectura $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new Lectura($connection);
    }

    public function ejecutar(int $idAdministrador): array
    {
        $estado = (string)($_GET['estado'] ?? 'Todos');
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'lecturas' => [], 'formulario' => null, 'estado' => $estado];
        try {
            $accion = (string)($_POST['accion'] ?? '');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($accion === 'guardar') {
                    $id = filter_input(INPUT_POST, 'id_lectura', FILTER_VALIDATE_INT) ?: null;
                    $this->model->guardar(
                        $id,
                        (string)($_POST['titulo'] ?? ''),
                        (string)($_POST['contenido'] ?? ''),
                        (string)($_POST['categoria_tematica'] ?? ''),
                        (int)($_POST['tiempo_estimado_min'] ?? 0),
                        $idAdministrador
                    );
                    $mensaje = $id ? 'Lectura actualizada correctamente.' : 'Lectura registrada correctamente.';
                } elseif ($accion === 'estado') {
                    $this->model->cambiarEstado((int)($_POST['id_lectura'] ?? 0), (string)($_POST['operacion'] ?? ''));
                    $mensaje = 'Estado de la lectura actualizado correctamente.';
                } else {
                    throw new InvalidArgumentException('Acción no válida.');
                }
                header('Location: lecturas.php?estado=' . urlencode((string)($_POST['estado_filtro'] ?? 'Todos')) . '&mensaje=' . urlencode($mensaje));
                exit;
            }
            $resultado['estado'] = $estado;
            $resultado['lecturas'] = $this->model->listar($estado)['filas'];
            if (isset($_GET['editar'])) $resultado['formulario'] = $this->model->obtener((int)$_GET['editar']);
            if (isset($_GET['mensaje'])) $resultado['mensaje'] = (string)$_GET['mensaje'];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = $exception->getMessage();
        }
        return $resultado;
    }
}
