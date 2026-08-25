<?php
$datos = $resultado['datos'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$historial = $datos['historial'] ?? [];
$filtros = $resultado['filtros'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$esc = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora emocional | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_maestro.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_maestro.php'; ?>

<main class="dashboard bitacora-dashboard">
    <header class="encabezado">
        <div>
            <h1>Bitácora emocional</h1>
            <p>Seguimiento de alumnos asignados a <?= $esc($_SESSION['maestro_nombre'] ?? 'este maestro') ?></p>
        </div>
    </header>

    <?php if ($mensaje): ?><div class="mensaje"><?= $esc($mensaje) ?></div><?php endif; ?>
    <?php if ($errores): ?><div class="mensaje error"><?= $esc(implode(' ', $errores)) ?></div><?php endif; ?>

    <section class="layout bitacora-layout">
        <article class="panel">
            <h2>Nuevo registro</h2>
            <form method="post" class="form-grid">
                <input type="hidden" name="accion" value="registrar_nota">
                <div class="campo ancho">
                    <label for="nota-alumno">Alumno</label>
                    <select id="nota-alumno" name="id_alumno" required>
                        <option value="">Selecciona un alumno</option>
                        <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?= (int)$alumno['id_alumno'] ?>"><?= $esc($alumno['nombre_completo']) ?> — <?= $esc($alumno['grupo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="nota-tipo">Tipo de registro</label>
                    <select id="nota-tipo" name="tipo_nota" required>
                        <option value="">Selecciona</option>
                        <option value="Observacion">Observación</option>
                        <option value="Incidencia">Incidencia</option>
                        <option value="Logro">Logro</option>
                    </select>
                </div>
                <div class="campo">
                    <label for="nota-emocion">Emoción</label>
                    <input id="nota-emocion" name="emocion" maxlength="80" placeholder="Ej. tranquilidad, ansiedad">
                </div>
                <div class="campo">
                    <label for="nota-accion">Acción de contención</label>
                    <select id="nota-accion" name="accion_contencion">
                        <option value="">No aplica</option>
                        <option>Respiración guiada</option>
                        <option>Pausa activa</option>
                        <option>Acompañamiento</option>
                        <option>Diálogo emocional</option>
                        <option>Lectura de reflexión</option>
                    </select>
                </div>
                <div class="campo ancho">
                    <label for="nota-descripcion">Descripción</label>
                    <textarea id="nota-descripcion" name="nota_descripcion" maxlength="2000" required placeholder="Describe el contexto, avance observado o situación presentada."></textarea>
                </div>
                <div class="acciones ancho">
                    <button class="boton" type="submit">Guardar registro</button>
                </div>
            </form>
        </article>

        <article class="panel">
            <h2>Filtrar historial</h2>
            <form method="get" class="form-grid">
                <div class="campo ancho">
                    <label for="filtro-alumno">Alumno</label>
                    <select id="filtro-alumno" name="id_alumno">
                        <option value="">Todos los alumnos</option>
                        <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?= (int)$alumno['id_alumno'] ?>" <?= (int)($filtros['id_alumno'] ?? 0) === (int)$alumno['id_alumno'] ? 'selected' : '' ?>><?= $esc($alumno['nombre_completo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo">
                    <label for="filtro-tipo">Tipo</label>
                    <select id="filtro-tipo" name="tipo">
                        <option value="">Todos</option>
                        <?php foreach (['Observacion' => 'Observación', 'Incidencia' => 'Incidencia', 'Logro' => 'Logro', 'Medalla' => 'Medalla'] as $valor => $etiqueta): ?>
                            <option value="<?= $valor ?>" <?= ($filtros['tipo'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo"><label for="filtro-emocion">Emoción</label><input id="filtro-emocion" name="emocion" value="<?= $esc($filtros['emocion'] ?? '') ?>"></div>
                <div class="campo"><label for="filtro-desde">Desde</label><input id="filtro-desde" type="date" name="desde" value="<?= $esc($filtros['desde'] ?? '') ?>"></div>
                <div class="campo"><label for="filtro-hasta">Hasta</label><input id="filtro-hasta" type="date" name="hasta" value="<?= $esc($filtros['hasta'] ?? '') ?>"></div>
                <div class="acciones ancho"><button class="boton" type="submit">Aplicar filtros</button><a class="boton secundario" href="bitacora_maestro.php">Limpiar</a></div>
            </form>
        </article>
    </section>

    <section class="panel tabla-panel">
        <h2>Historial de registros</h2>
        <div class="tabla-wrap">
            <table>
                <thead><tr><th>Fecha</th><th>Alumno</th><th>Grupo</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Medalla</th><th>Puntos</th><th>Descripción</th></tr></thead>
                <tbody>
                <?php if (!$historial): ?>
                    <tr><td colspan="9" class="vacio">No hay registros que coincidan con los filtros.</td></tr>
                <?php else: foreach ($historial as $nota): ?>
                    <tr>
                        <td><?= $esc($nota['fecha_hora']) ?></td>
                        <td><?= $esc($nota['alumno']) ?></td>
                        <td><?= $esc($nota['grupo']) ?></td>
                        <td><?= $esc($nota['tipo_nota']) ?></td>
                        <td><?= $esc($nota['emocion'] ?: '—') ?></td>
                        <td><?= $esc($nota['accion_contencion'] ?: '—') ?></td>
                        <td><?= $esc($nota['nombre_insignia'] ?: '—') ?></td>
                        <td><?= (int)($nota['puntos_otorgados'] ?? 0) ?></td>
                        <td><?= $esc($nota['nota_descripcion']) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
<script>
const tipo = document.getElementById('nota-tipo');
const accion = document.getElementById('nota-accion');
function actualizarContencion() {
    const esIncidencia = tipo.value === 'Incidencia';
    accion.required = esIncidencia;
    accion.parentElement.querySelector('label').textContent = esIncidencia ? 'Acción de contención' : 'Acción de contención (opcional)';
}
tipo.addEventListener('change', actualizarContencion);
actualizarContencion();
</script>
</div>
</body>
</html>
