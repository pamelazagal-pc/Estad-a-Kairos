<?php

require_once __DIR__ . '/../models/Consejo.php';

class ConsejoController
{
    private Consejo $model;

    public function __construct(mysqli $connection)
    {
        $this->model = new Consejo($connection);
    }

    public function ejecutar(?int $idAdministrador, ?int $idDocente, string $rutaActual = 'consejos.php'): array
    {
        $estado = (string)($_GET['estado'] ?? 'Todos');
        $resultado = [
            'ok' => false,
            'errores' => [],
            'mensaje' => null,
            'consejos' => [],
            'formulario' => null,
            'estado' => $estado,
            'es_maestro' => $idDocente !== null,
        ];

        try {
            if ($idAdministrador === null && $idDocente === null) {
                throw new RuntimeException('No se identificó un usuario autorizado.');
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $accion = (string)($_POST['accion'] ?? '');
                $estadoFiltro = (string)($_POST['estado_filtro'] ?? 'Todos');

                if ($accion === 'guardar') {
                    $id = filter_input(INPUT_POST, 'id_consejo', FILTER_VALIDATE_INT) ?: null;
                    if ($id !== null && $idDocente !== null) {
                        throw new RuntimeException('Los maestros solo pueden publicar consejos nuevos.');
                    }
                    $this->model->guardar(
                        $id,
                        (string)($_POST['titulo'] ?? ''),
                        (string)($_POST['recomendacion'] ?? ''),
                        (string)($_POST['categoria'] ?? ''),
                        $idDocente,
                        $idAdministrador
                    );
                    $mensaje = $id
                        ? 'Consejo actualizado correctamente.'
                        : 'Consejo publicado correctamente.';
                } elseif ($accion === 'estado') {
                    if ($idDocente !== null) {
                        throw new RuntimeException('Solo el administrador puede desactivar o reactivar consejos.');
                    }
                    $this->model->cambiarEstado(
                        (int)($_POST['id_consejo'] ?? 0),
                        (string)($_POST['operacion'] ?? '')
                    );
                    $mensaje = 'Estado del consejo actualizado correctamente.';
                } else {
                    throw new InvalidArgumentException('La acción solicitada no es válida.');
                }

                header(
                    'Location: ' . $rutaActual
                    . '?estado=' . urlencode($estadoFiltro)
                    . '&mensaje=' . urlencode($mensaje)
                );
                exit;
            }

            $resultado['estado'] = in_array($estado, ['Todos', 'Activo', 'Inactivo'], true)
                ? $estado
                : 'Todos';
            $resultado['consejos'] = $this->model->listar($resultado['estado']);

            if (isset($_GET['editar']) && $idAdministrador !== null) {
                $idEditar = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
                if ($idEditar) {
                    $resultado['formulario'] = $this->model->obtener($idEditar);
                }
            }
            if (isset($_GET['mensaje'])) {
                $resultado['mensaje'] = (string)$_GET['mensaje'];
            }
            $resultado['ok'] = true;
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $resultado['errores'][] = $exception->getMessage();
        }

        return $resultado;
    }
}
