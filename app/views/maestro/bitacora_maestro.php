<?php
$datos = $resultado['datos'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$historial = $datos['historial'] ?? [];
$recompensas = $datos['recompensas'] ?? [];
$filtros = $resultado['filtros'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$notaEdicion = $datos['nota_edicion'] ?? null;
$estadoHistorial = $filtros['estado'] ?? 'Activo';
$esc = static function ($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
};
$accionesContencion = [
    'Respiración guiada',
    'Pausa activa',
    'Acompañamiento',
    'Diálogo emocional',
    'Lectura de reflexión',
];
$accionEdicion = (string)($notaEdicion['accion_contencion'] ?? '');
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
            <h2><?= $notaEdicion ? 'Editar registro' : 'Nuevo registro' ?></h2>
            <?php if ($notaEdicion): ?>
                <p class="texto-ayuda">Editando el registro de <strong><?= $esc($notaEdicion['alumno']) ?></strong>. Las medallas no se modifican desde aquí.</p>
                <form method="post" class="form-grid">
                    <input type="hidden" name="accion" value="editar_nota">
                    <input type="hidden" name="id_nota" value="<?= (int)$notaEdicion['id_nota'] ?>">
                    <div class="campo ancho">
                        <label>Alumno</label>
                        <input value="<?= $esc($notaEdicion['alumno'] . ' — ' . $notaEdicion['grupo']) ?>" disabled>
                    </div>
                    <div class="campo">
                        <label for="editar-tipo">Tipo de registro</label>
                        <select id="editar-tipo" name="tipo_nota" required>
                            <?php foreach (['Observacion' => 'Observación', 'Incidencia' => 'Incidencia', 'Logro' => 'Logro'] as $valor => $etiqueta): ?>
                                <option value="<?= $valor ?>" <?= ($notaEdicion['tipo_nota'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo">
                        <label for="editar-emocion">Emoción</label>
                        <input id="editar-emocion" name="emocion" maxlength="80" value="<?= $esc($notaEdicion['emocion'] ?? '') ?>">
                    </div>
                    <div class="campo">
                        <label for="editar-accion">Acción de contención</label>
                        <select id="editar-accion" name="accion_contencion">
                            <option value="">No aplica</option>
                            <?php foreach ($accionesContencion as $opcion): ?>
                                <option <?= $accionEdicion === $opcion ? 'selected' : '' ?>><?= $esc($opcion) ?></option>
                            <?php endforeach; ?>
                            <?php if ($accionEdicion !== '' && !in_array($accionEdicion, $accionesContencion, true)): ?>
                                <option selected><?= $esc($accionEdicion) ?></option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="campo ancho">
                        <label for="editar-descripcion">Descripción</label>
                        <textarea id="editar-descripcion" name="nota_descripcion" maxlength="2000" required><?= $esc($notaEdicion['nota_descripcion'] ?? '') ?></textarea>
                    </div>
                    <div class="acciones ancho">
                        <button class="boton" type="submit">Guardar cambios</button>
                        <a class="boton secundario" href="bitacora_maestro.php?estado=<?= urlencode($estadoHistorial) ?>">Cancelar edición</a>
                    </div>
                </form>
            <?php else: ?>
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
                            <?php foreach ($accionesContencion as $opcion): ?><option><?= $esc($opcion) ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="campo ancho">
                        <label for="nota-descripcion">Descripción</label>
                        <textarea id="nota-descripcion" name="nota_descripcion" maxlength="2000" required placeholder="Describe el contexto, avance observado o situación presentada."></textarea>
                    </div>
                    <div class="campo ancho notificar-tutor-field">
                        <label><input type="checkbox" name="notificar_tutor" value="1"> Comunicar este registro al tutor</label>
                        <small>El aviso aparecerá en el panel familiar del alumno seleccionado.</small>
                    </div>
                    <div class="acciones ancho"><button class="boton" type="submit">Guardar registro</button></div>
                </form>
            <?php endif; ?>
        </article>

        <article class="panel medalla-panel">
            <h2>Asignar medalla</h2>
            <p class="texto-ayuda">Reconoce un avance positivo. Los puntos se acumularán automáticamente al alumno.</p>
            <form method="post" class="form-grid">
                <input type="hidden" name="accion" value="registrar_medalla">
                <div class="campo ancho">
                    <label for="medalla-alumno">Alumno</label>
                    <select id="medalla-alumno" name="id_alumno" required>
                        <option value="">Selecciona un alumno</option>
                        <?php foreach ($alumnos as $alumno): ?>
                            <option value="<?= (int)$alumno['id_alumno'] ?>"><?= $esc($alumno['nombre_completo']) ?> — <?= $esc($alumno['grupo']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo ancho">
                    <label for="medalla-recompensa">Medalla</label>
                    <select id="medalla-recompensa" name="id_recompensa" required>
                        <option value="">Selecciona una medalla</option>
                        <?php foreach ($recompensas as $recompensa): ?>
                            <option value="<?= (int)$recompensa['id_recompensa'] ?>"><?= $esc($recompensa['nombre_insignia']) ?> — <?= (int)$recompensa['puntos_otorgados'] ?> puntos</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="campo ancho">
                    <label for="medalla-descripcion">Descripción del logro</label>
                    <textarea id="medalla-descripcion" name="nota_descripcion" maxlength="1000" required placeholder="Describe el avance o conducta positiva reconocida."></textarea>
                </div>
                <div class="campo ancho notificar-tutor-field">
                    <label><input type="checkbox" name="notificar_tutor" value="1"> Comunicar esta medalla al tutor</label>
                    <small>El reconocimiento aparecerá en los avisos del panel familiar.</small>
                </div>
                <div class="acciones ancho"><button class="boton" type="submit">Asignar medalla</button></div>
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
                <div class="campo">
                    <label for="filtro-estado">Estado</label>
                    <select id="filtro-estado" name="estado">
                        <option value="Activo" <?= $estadoHistorial === 'Activo' ? 'selected' : '' ?>>Activos</option>
                        <option value="Archivado" <?= $estadoHistorial === 'Archivado' ? 'selected' : '' ?>>Archivados</option>
                        <option value="Todos" <?= $estadoHistorial === 'Todos' ? 'selected' : '' ?>>Todos</option>
                    </select>
                </div>
                <div class="acciones ancho"><button class="boton" type="submit">Aplicar filtros</button><a class="boton secundario" href="bitacora_maestro.php">Limpiar</a></div>
            </form>
        </article>
    </section>

    <section class="panel tabla-panel">
        <h2>Historial de registros</h2>
        <div class="tabla-wrap">
            <table>
                <thead><tr><th>Fecha</th><th>Alumno</th><th>Grupo</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Medalla</th><th>Puntos</th><th>Comunicación</th><th>Estado</th><th>Descripción</th><th>Acciones</th></tr></thead>
                <tbody>
                <?php if (!$historial): ?>
                    <tr><td colspan="12" class="vacio">No hay registros que coincidan con los filtros.</td></tr>
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
                        <td><span class="notificacion-estado <?= (int)($nota['notificado_al_tutor'] ?? 0) === 1 ? 'enviada' : 'privada' ?>"><?= (int)($nota['notificado_al_tutor'] ?? 0) === 1 ? 'Comunicado' : 'Privado' ?></span></td>
                        <td><span class="notificacion-estado <?= ($nota['estado'] ?? 'Activo') === 'Activo' ? 'enviada' : 'privada' ?>"><?= $esc($nota['estado'] ?? 'Activo') ?></span></td>
                        <td><?= $esc($nota['nota_descripcion']) ?></td>
                        <td class="acciones-tabla">
                            <?php if (($nota['tipo_nota'] ?? '') === 'Medalla'): ?>
                                <span class="texto-ayuda">Protegida</span>
                            <?php elseif (($nota['estado'] ?? 'Activo') === 'Activo'): ?>
                                <a class="boton-tabla" href="bitacora_maestro.php?editar=<?= (int)$nota['id_nota'] ?>&estado=<?= urlencode($estadoHistorial) ?>">Editar</a>
                                <form method="post">
                                    <input type="hidden" name="accion" value="cambiar_estado_nota">
                                    <input type="hidden" name="id_nota" value="<?= (int)$nota['id_nota'] ?>">
                                    <input type="hidden" name="operacion" value="archivar">
                                    <button class="boton-tabla boton-peligro" type="submit">Archivar</button>
                                </form>
                            <?php else: ?>
                                <form method="post">
                                    <input type="hidden" name="accion" value="cambiar_estado_nota">
                                    <input type="hidden" name="id_nota" value="<?= (int)$nota['id_nota'] ?>">
                                    <input type="hidden" name="operacion" value="restaurar">
                                    <button class="boton-tabla boton-exito" type="submit">Restaurar</button>
                                </form>
                            <?php endif; ?>
                        </td>
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
    if (!tipo || !accion) return;
    const esIncidencia = tipo.value === 'Incidencia';
    accion.required = esIncidencia;
    const etiqueta = accion.parentElement.querySelector('label');
    if (etiqueta) etiqueta.textContent = esIncidencia ? 'Acción de contención' : 'Acción de contención (opcional)';
}
if (tipo) {
    tipo.addEventListener('change', actualizarContencion);
    actualizarContencion();
}
const tipoEdicion = document.getElementById('editar-tipo');
const accionEdicion = document.getElementById('editar-accion');
function actualizarContencionEdicion() {
    if (!tipoEdicion || !accionEdicion) return;
    accionEdicion.required = tipoEdicion.value === 'Incidencia';
}
if (tipoEdicion) {
    tipoEdicion.addEventListener('change', actualizarContencionEdicion);
    actualizarContencionEdicion();
}
</script>
</div>
</body>
</html>
