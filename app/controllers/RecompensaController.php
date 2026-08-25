<?php
require_once __DIR__ . '/../models/Recompensa.php';

class RecompensaController
{
    private Recompensa $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new Recompensa($connection);
    }

    public function ejecutar(int $idAdministrador): array
    {
        $estado = (string)($_GET['estado'] ?? 'Todos');
        $resultado = ['ok' => false, 'errores' => [], 'mensaje' => null, 'recompensas' => [], 'formulario' => null, 'estado' => $estado];
        try {
            $accion = (string)($_POST['accion'] ?? '');
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if ($accion === 'guardar') {
                    $id = filter_input(INPUT_POST, 'id_recompensa', FILTER_VALIDATE_INT) ?: null;
                    $this->model->guardar(
                        $id,
                        (string)($_POST['nombre_insignia'] ?? ''),
                        (string)($_POST['descripcion'] ?? ''),
                        (int)($_POST['puntos_otorgados'] ?? 0),
                        (string)($_POST['icono_url'] ?? ''),
                        $idAdministrador
                    );
                    $mensaje = $id ? 'Medalla actualizada correctamente.' : 'Medalla registrada correctamente.';
                } elseif ($accion === 'estado') {
                    $this->model->cambiarEstado((int)($_POST['id_recompensa'] ?? 0), (string)($_POST['operacion'] ?? ''));
                    $mensaje = 'Estado de la medalla actualizado correctamente.';
                } else {
                    throw new InvalidArgumentException('Acción no válida.');
                }
                header('Location: recompensas.php?estado=' . urlencode((string)($_POST['estado_filtro'] ?? 'Todos')) . '&mensaje=' . urlencode($mensaje));
                exit;
            }
            $resultado['estado'] = $estado;
            $resultado['recompensas'] = $this->model->listar($estado)['filas'];
            if (isset($_GET['editar'])) $resultado['formulario'] = $this->model->obtener((int)$_GET['editar']);
            if (isset($_GET['mensaje'])) $resultado['mensaje'] = (string)$_GET['mensaje'];
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = $exception->getMessage();
        }
        return $resultado;
    }
}
