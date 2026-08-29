<?php
$resultado = $resultado ?? [];
$filtros = $resultado['filtros'] ?? [];
$catalogos = $resultado['filtros_catalogo'] ?? ['grupos' => [], 'docentes' => []];
$reporte = $resultado['reporte'] ?? [];
$errores = $resultado['errores'] ?? [];
$resumen = $reporte['resumen'] ?? [];
$emociones = $reporte['emociones'] ?? [];
$evolucion = $reporte['evolucion'] ?? [];
$porTipo = $reporte['porTipo'] ?? [];
$alumnos = $reporte['alumnos'] ?? [];
$historial = $reporte['historial'] ?? [];

function reporteAdminEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function reporteAdminNumero($valor): int
{
    return (int)($valor ?? 0);
}

$maxEmocion = 1;
foreach ($emociones as $item) $maxEmocion = max($maxEmocion, reporteAdminNumero($item['total'] ?? 0));
$maxTipo = 1;
foreach ($porTipo as $item) $maxTipo = max($maxTipo, reporteAdminNumero($item['total'] ?? 0));
$maxEvolucion = 1;
foreach ($evolucion as $item) $maxEvolucion = max($maxEvolucion, reporteAdminNumero($item['total'] ?? 0));
$evolucionGrafica = array_slice($evolucion, -8);
$filtrosPdf = array_filter($filtros, static fn($valor): bool => $valor !== null && $valor !== '');
$urlPdf = 'reporte_administrador_pdf.php' . ($filtrosPdf !== [] ? '?' . http_build_query($filtrosPdf) : '');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte general | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=reporte-admin-1">
</head>
<body class="panel-body reporte-page reporte-admin-page">
    <?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
    <main class="panel-contenedor">
        <?php require_once __DIR__ . '/../topbar_administrador.php'; ?>
        <div class="contenedor-admin reporte-contenedor">
            <header class="encabezado-seccion reporte-header">
                <div>
                    <p class="eyebrow-admin">Análisis institucional</p>
                    <h1>Reporte general de Kairos</h1>
                    <p>Consulta el comportamiento emocional y la actividad registrada en toda la plataforma.</p>
                </div>
                <div class="acciones encabezado-acciones reporte-actions"><a class="boton" href="<?= reporteAdminEsc($urlPdf) ?>" target="_blank" rel="noopener">Generar PDF</a></div>
            </header>

            <?php if ($errores !== []): ?><div class="mensaje error"><?= reporteAdminEsc(implode(' ', $errores)) ?></div><?php endif; ?>
            <form class="filtros-reporte" method="get">
                <label>Grupo<select name="id_grupo"><option value="">Todos los grupos</option><?php foreach ($catalogos['grupos'] as $grupo): ?><option value="<?= (int)$grupo['id_grupo'] ?>" <?= (int)($filtros['id_grupo'] ?? 0) === (int)$grupo['id_grupo'] ? 'selected' : '' ?>><?= reporteAdminEsc($grupo['nombre_grupo'] . ' · ' . $grupo['ciclo_escolar']) ?></option><?php endforeach; ?></select></label>
                <label>Maestro<select name="id_docente"><option value="">Todos los maestros</option><?php foreach ($catalogos['docentes'] as $docente): ?><option value="<?= (int)$docente['id_docente'] ?>" <?= (int)($filtros['id_docente'] ?? 0) === (int)$docente['id_docente'] ? 'selected' : '' ?>><?= reporteAdminEsc($docente['nombre']) ?></option><?php endforeach; ?></select></label>
                <label>Tipo<select name="tipo"><option value="">Todos</option><?php foreach (['Observacion' => 'Observación', 'Incidencia' => 'Incidencia', 'Logro' => 'Logro', 'Medalla' => 'Medalla'] as $valor => $etiqueta): ?><option value="<?= $valor ?>" <?= ($filtros['tipo'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option><?php endforeach; ?></select></label>
                <label>Emoción<input name="emocion" value="<?= reporteAdminEsc($filtros['emocion'] ?? '') ?>" placeholder="Ej. Ansiedad"></label>
                <label>Desde<input type="date" name="desde" value="<?= reporteAdminEsc($filtros['desde'] ?? '') ?>"></label>
                <label>Hasta<input type="date" name="hasta" value="<?= reporteAdminEsc($filtros['hasta'] ?? '') ?>"></label>
                <div class="acciones filtro-acciones"><button class="boton" type="submit">Aplicar filtros</button><a class="boton-secundario" href="reporte_administrador.php">Limpiar</a></div>
            </form>

            <div class="reporte-contexto"><span><strong>Alcance:</strong> Toda la institución</span><span><strong>Periodo:</strong> <?= reporteAdminEsc((!empty($filtros['desde']) || !empty($filtros['hasta'])) ? (($filtros['desde'] ?? '') ?: 'inicio') . ' — ' . (($filtros['hasta'] ?? '') ?: 'actualidad') : 'Todo el historial disponible') ?></span><span><strong>Registros:</strong> <?= reporteAdminNumero($resumen['total'] ?? 0) ?></span></div>
            <section class="reporte-kpis" aria-label="Indicadores institucionales">
                <article class="reporte-kpi"><span class="reporte-kpi-label">Registros</span><strong><?= reporteAdminNumero($resumen['total'] ?? 0) ?></strong><small>en el filtro actual</small></article>
                <article class="reporte-kpi reporte-kpi-warning"><span class="reporte-kpi-label">Incidencias</span><strong><?= reporteAdminNumero($resumen['incidencias'] ?? 0) ?></strong><small>requieren seguimiento</small></article>
                <article class="reporte-kpi"><span class="reporte-kpi-label">Observaciones</span><strong><?= reporteAdminNumero($resumen['observaciones'] ?? 0) ?></strong><small>registros pedagógicos</small></article>
                <article class="reporte-kpi reporte-kpi-success"><span class="reporte-kpi-label">Logros</span><strong><?= reporteAdminNumero($resumen['logros'] ?? 0) ?></strong><small>reconocimientos positivos</small></article>
                <article class="reporte-kpi"><span class="reporte-kpi-label">Puntos</span><strong><?= reporteAdminNumero($resumen['puntos'] ?? 0) ?></strong><small>otorgados en el periodo</small></article>
            </section>

            <section class="reporte-graficas">
                <article class="panel-seccion reporte-grafica-card"><div class="encabezado-tabla"><div><p class="eyebrow-admin">Distribución</p><h2>Emociones registradas</h2></div></div><?php if ($emociones === []): ?><p class="tabla-vacia">No hay emociones para graficar.</p><?php else: ?><svg class="reporte-chart-svg" viewBox="0 0 640 285" role="img" aria-label="Emociones registradas"><?php foreach (array_slice($emociones, 0, 6) as $indice => $item): $valor = reporteAdminNumero($item['total'] ?? 0); $y = 28 + ($indice * 42); $ancho = round(410 * $valor / $maxEmocion, 1); ?><text class="chart-label" x="0" y="<?= $y ?>"><?= reporteAdminEsc($item['etiqueta']) ?></text><rect class="chart-track" x="175" y="<?= $y - 16 ?>" width="410" height="20" rx="10"></rect><rect class="chart-bar chart-bar-blue" x="175" y="<?= $y - 16 ?>" width="<?= $ancho ?>" height="20" rx="10"></rect><text class="chart-value" x="600" y="<?= $y ?>"><?= $valor ?></text><?php endforeach; ?></svg><?php endif; ?></article>
                <article class="panel-seccion reporte-grafica-card"><div class="encabezado-tabla"><div><p class="eyebrow-admin">Evolución</p><h2>Registros por mes</h2></div></div><?php if ($evolucionGrafica === []): ?><p class="tabla-vacia">No hay periodos para graficar.</p><?php else: ?><svg class="reporte-chart-svg" viewBox="0 0 640 285" role="img" aria-label="Registros por mes"><line class="chart-axis" x1="32" y1="220" x2="610" y2="220"></line><?php foreach ($evolucionGrafica as $indice => $item): $valor = reporteAdminNumero($item['total'] ?? 0); $x = 50 + ($indice * 70); $alto = round(170 * $valor / $maxEvolucion, 1); $y = 220 - $alto; ?><rect class="chart-bar chart-bar-purple" x="<?= $x ?>" y="<?= $y ?>" width="38" height="<?= $alto ?>" rx="7"></rect><text class="chart-value" x="<?= $x + 19 ?>" y="<?= max(15, $y - 8) ?>"><?= $valor ?></text><text class="chart-period" x="<?= $x + 19 ?>" y="242"><?= reporteAdminEsc($item['periodo']) ?></text><?php endforeach; ?></svg><?php endif; ?></article>
                <article class="panel-seccion reporte-grafica-card reporte-grafica-tipo"><div class="encabezado-tabla"><div><p class="eyebrow-admin">Actividad</p><h2>Registros por tipo</h2></div></div><?php if ($porTipo === []): ?><p class="tabla-vacia">No hay tipos para graficar.</p><?php else: ?><svg class="reporte-chart-svg" viewBox="0 0 640 220" role="img" aria-label="Registros por tipo"><?php foreach ($porTipo as $indice => $item): $valor = reporteAdminNumero($item['total'] ?? 0); $y = 28 + ($indice * 42); $ancho = round(410 * $valor / $maxTipo, 1); ?><text class="chart-label" x="0" y="<?= $y ?>"><?= reporteAdminEsc($item['etiqueta']) ?></text><rect class="chart-track" x="175" y="<?= $y - 16 ?>" width="410" height="20" rx="10"></rect><rect class="chart-bar chart-bar-blue" x="175" y="<?= $y - 16 ?>" width="<?= $ancho ?>" height="20" rx="10"></rect><text class="chart-value" x="600" y="<?= $y ?>"><?= $valor ?></text><?php endforeach; ?></svg><?php endif; ?></article>
            </section>

            <section class="reporte-destacados"><article class="panel-seccion"><div class="encabezado-tabla"><div><p class="eyebrow-admin">Atención institucional</p><h2>Alumnos con mayor seguimiento</h2></div></div><div class="tabla-scroll"><table class="tabla-admin reporte-tabla"><thead><tr><th>Alumno</th><th>Grupo</th><th>Maestro</th><th>Registros</th><th>Incidencias</th><th>Último registro</th></tr></thead><tbody><?php if ($alumnos === []): ?><tr><td colspan="6" class="tabla-vacia">No hay alumnos con registros en este filtro.</td></tr><?php else: foreach ($alumnos as $alumno): ?><tr><td><strong><?= reporteAdminEsc($alumno['alumno']) ?></strong></td><td><?= reporteAdminEsc($alumno['grupo']) ?></td><td><?= reporteAdminEsc($alumno['docente']) ?></td><td><?= reporteAdminNumero($alumno['registros'] ?? 0) ?></td><td><span class="reporte-incidencias-pill"><?= reporteAdminNumero($alumno['incidencias'] ?? 0) ?></span></td><td><?= reporteAdminEsc($alumno['ultimo_registro']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></article></section>

            <section class="panel-seccion reporte-resultados"><div class="encabezado-tabla"><div><p class="eyebrow-admin">Detalle auditable</p><h2>Registros incluidos</h2><p class="panel-descripcion"><?= count($historial) ?> registros encontrados con los filtros actuales.</p></div></div><div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Fecha</th><th>Alumno</th><th>Grupo</th><th>Maestro</th><th>Tipo</th><th>Emoción</th><th>Puntos</th><th>Comunicación</th><th>Descripción</th></tr></thead><tbody><?php if ($historial === []): ?><tr><td colspan="9" class="tabla-vacia">No hay registros con los filtros seleccionados.</td></tr><?php else: foreach ($historial as $registro): ?><tr><td><?= reporteAdminEsc($registro['fecha_hora']) ?></td><td><?= reporteAdminEsc($registro['alumno']) ?></td><td><?= reporteAdminEsc($registro['grupo']) ?></td><td><?= reporteAdminEsc($registro['docente']) ?></td><td><?= reporteAdminEsc($registro['tipo_nota']) ?></td><td><?= reporteAdminEsc($registro['emocion'] ?: '—') ?></td><td><?= reporteAdminNumero($registro['puntos_otorgados'] ?? 0) ?></td><td><?= reporteAdminNumero($registro['notificado_al_tutor'] ?? 0) === 1 ? 'Comunicado' : 'Privado' ?></td><td><?= reporteAdminEsc($registro['nota_descripcion']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
        </div>
    </main>
</body>
</html>
