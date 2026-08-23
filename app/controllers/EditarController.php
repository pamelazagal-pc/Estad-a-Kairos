<?php

require_once __DIR__ . '/../models/Editar.php';

class EditarController
{
    private $model;

    public function __construct($connection)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->model = new Editar($connection);
    }

    public function autenticado(): bool
    {
        return isset($_SESSION['administrador_id']);
    }

    public function obtener(string $tipo, int $id): ?array
    {
        return $this->model->obtener($tipo, $id);
    }

    public function grupos(): array
    {
        try { return $this->model->grupos(); } catch (Throwable $e) { error_log($e->getMessage()); return []; }
    }

    public function actualizar(string $tipo, int $id): array
    {
        $datos = $_POST;
        $errores = [];
        if ($id <= 0) $errores[] = 'El identificador no es válido.';
        if (in_array($tipo, ['administradores', 'maestros', 'tutores', 'alumnos'], true) && trim($datos['nombre'] ?? '') === '') $errores[] = 'El nombre es obligatorio.';
        if (in_array($tipo, ['administradores', 'maestros', 'tutores'], true) && !filter_var($datos['correo'] ?? '', FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no es válido.';
        if ($tipo === 'tutores' && !in_array($datos['cargo'] ?? '', ['Padre', 'Tutor'], true)) $errores[] = 'El tipo debe ser Padre o Tutor.';
        if ($tipo === 'grupos' && (!ctype_digit((string)($datos['grado'] ?? '')) || (int)$datos['grado'] < 1 || (int)$datos['grado'] > 6)) $errores[] = 'El grado debe estar entre 1 y 6.';
        if ($tipo === 'alumnos') {
            if (trim($datos['apellido_paterno'] ?? '') === '') $errores[] = 'El apellido paterno es obligatorio.';
            if ($datos['edad'] !== '' && (!ctype_digit((string)$datos['edad']) || (int)$datos['edad'] < 5 || (int)$datos['edad'] > 18)) $errores[] = 'La edad debe estar entre 5 y 18.';
            if ((int)($datos['id_grupo'] ?? 0) <= 0) $errores[] = 'Selecciona un grupo válido.';
            if (!in_array($datos['estado_semaforo'] ?? '', ['Verde', 'Amarillo', 'Rojo'], true)) $errores[] = 'El semáforo no es válido.';
            if (!in_array($datos['estado'] ?? '', ['Activo', 'Inactivo', 'Trasladado', 'Egresado'], true)) $errores[] = 'El estado del alumno no es válido.';
        }
        if (($datos['password'] ?? '') !== '' && strlen($datos['password']) < 8) $errores[] = 'La nueva contraseña debe tener al menos 8 caracteres.';
        if ($errores !== []) return ['ok' => false, 'errores' => $errores, 'datos' => $datos];
        try {
            $this->model->actualizar($tipo, $id, $datos);
            return ['ok' => true, 'errores' => [], 'datos' => []];
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return ['ok' => false, 'errores' => [$e->getMessage()], 'datos' => $datos];
        }
    }
}
