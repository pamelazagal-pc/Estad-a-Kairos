<?php
$datos = $datos ?? [];
$filtros = $filtros ?? [];
$grupos = $datos['grupos'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$reporte = $datos['reporte'] ?? [];
$resumen = $reporte['resumen'] ?? [];
$emociones = $reporte['emociones'] ?? [];
$evolucion = $reporte['evolucion'] ?? [];
$alumnosSeguimiento = $reporte['alumnos_seguimiento'] ?? [];
$historial = $reporte['historial'] ?? [];

function reporteEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function reporteNumero($valor): int
{
    return (int)($valor ?? 0);
}

$maxEmocion = 1;
foreach ($emociones as $emocion) $maxEmocion = max($maxEmocion, reporteNumero($emocion['total'] ?? 0));
$maxEvolucion = 1;
foreach ($evolucion as $periodo) $maxEvolucion = max($maxEvolucion, reporteNumero($periodo['total'] ?? 0));
$evolucionGrafica = array_slice($evolucion, -8);
$grupoSeleccionado = 'Todos los grupos';
foreach ($grupos as $grupo) {
    if ((int)($filtros['id_grupo'] ?? 0) === (int)$grupo['id_grupo']) {
        $grupoSeleccionado = $grupo['nombre_grupo'] . ' · ' . $grupo['ciclo_escolar'];
        break;
    }
}
$filtrosPdf = array_filter([
    'id_grupo' => $filtros['id_grupo'] ?? null,
    'id_alumno' => $filtros['id_alumno'] ?? null,
    'tipo' => $filtros['tipo'] ?? '',
    'emocion' => $filtros['emocion'] ?? '',
    'desde' => $filtros['desde'] ?? '',
    'hasta' => $filtros['hasta'] ?? '',
], static fn($valor): bool => $valor !== null && $valor !== '');
$urlPdf = 'reporte_pdf.php' . ($filtrosPdf !== [] ? '?' . http_build_query($filtrosPdf) : '');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte emocional | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body class="panel-body reporte-page">
<?php require_once __DIR__ . '/../sidebar_maestro.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_maestro.php'; ?>
<main class="contenedor-admin reporte-contenedor">
    <header class="encabezado-seccion reporte-header">
        <div>
            <p class="eyebrow-admin">Seguimiento emocional</p>
            <h1>Reporte emocional de mis grupos</h1>
            <p>Analiza los registros reales de bitácora por grupo, alumno y periodo.</p>
        </div>
        <div class="acciones encabezado-acciones reporte-actions">
            <a class="boton" href="<?= reporteEsc($urlPdf) ?>" target="_blank" rel="noopener">Generar PDF</a>
        </div>
    </header>

    <form class="filtros-reporte" method="get">
        <label>Grupo
            <select name="id_grupo">
                <option value="">Todos los grupos</option>
                <?php foreach ($grupos as $grupo): ?>
                    <option value="<?= (int)$grupo['id_grupo'] ?>" <?= (int)($filtros['id_grupo'] ?? 0) === (int)$grupo['id_grupo'] ? 'selected' : '' ?>><?= reporteEsc($grupo['nombre_grupo'] . ' · ' . $grupo['ciclo_escolar']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Alumno
            <select name="id_alumno">
                <option value="">Todos los alumnos</option>
                <?php foreach ($alumnos as $alumno): ?>
                    <option value="<?= (int)$alumno['id_alumno'] ?>" <?= (int)($filtros['id_alumno'] ?? 0) === (int)$alumno['id_alumno'] ? 'selected' : '' ?>><?= reporteEsc($alumno['nombre_completo']) ?> · <?= reporteEsc($alumno['grupo']) ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Tipo
            <select name="tipo">
                <option value="">Todos</option>
                <?php foreach (['Observacion' => 'Observación', 'Incidencia' => 'Incidencia', 'Logro' => 'Logro'] as $valor => $etiqueta): ?>
                    <option value="<?= $valor ?>" <?= ($filtros['tipo'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Emoción
            <input name="emocion" value="<?= reporteEsc($filtros['emocion'] ?? '') ?>" placeholder="Ej. Ansiedad">
        </label>
        <label>Desde
            <input type="date" name="desde" value="<?= reporteEsc($filtros['desde'] ?? '') ?>">
        </label>
        <label>Hasta
            <input type="date" name="hasta" value="<?= reporteEsc($filtros['hasta'] ?? '') ?>">
        </label>
        <div class="acciones filtro-acciones">
            <button class="boton" type="submit">Aplicar filtros</button>
            <a class="boton-secundario" href="reportes.php">Limpiar</a>
        </div>
    </form>

    <div class="reporte-contexto">
        <span><strong>Grupo:</strong> <?= reporteEsc($grupoSeleccionado) ?></span>
        <?php if (!empty($filtros['desde']) || !empty($filtros['hasta'])): ?><span><strong>Periodo:</strong> <?= reporteEsc($filtros['desde'] ?: 'inicio') ?> — <?= reporteEsc($filtros['hasta'] ?: 'actualidad') ?></span><?php else: ?><span><strong>Periodo:</strong> Todo el historial disponible</span><?php endif; ?>
        <span><strong>Registros:</strong> <?= reporteNumero($resumen['total'] ?? 0) ?></span>
    </div>

    <section class="reporte-kpis" aria-label="Indicadores del reporte">
        <article class="reporte-kpi"><span class="reporte-kpi-label">Registros</span><strong><?= reporteNumero($resumen['total'] ?? 0) ?></strong><small>en el filtro actual</small></article>
        <article class="reporte-kpi reporte-kpi-warning"><span class="reporte-kpi-label">Incidencias</span><strong><?= reporteNumero($resumen['incidencias'] ?? 0) ?></strong><small>requieren seguimiento</small></article>
        <article class="reporte-kpi"><span class="reporte-kpi-label">Observaciones</span><strong><?= reporteNumero($resumen['observaciones'] ?? 0) ?></strong><small>registros pedagógicos</small></article>
        <article class="reporte-kpi reporte-kpi-success"><span class="reporte-kpi-label">Logros</span><strong><?= reporteNumero($resumen['logros'] ?? 0) ?></strong><small>reconocimientos positivos</small></article>
        <article class="reporte-kpi"><span class="reporte-kpi-label">Alumnos</span><strong><?= reporteNumero($resumen['alumnos'] ?? 0) ?></strong><small>con registros</small></article>
    </section>

    <section class="reporte-graficas">
        <article class="panel-seccion reporte-grafica-card">
            <div class="encabezado-tabla"><div><p class="eyebrow-admin">Distribución</p><h2>Emociones registradas</h2></div></div>
            <?php if ($emociones === []): ?>
                <p class="tabla-vacia">No hay emociones para graficar con los filtros seleccionados.</p>
            <?php else: ?>
                <svg class="reporte-chart-svg reporte-chart-emociones" viewBox="0 0 640 285" role="img" aria-label="Gráfica de emociones registradas">
                    <?php foreach (array_slice($emociones, 0, 6) as $indice => $item): $valor = reporteNumero($item['total'] ?? 0); $y = 28 + ($indice * 42); $ancho = round(410 * $valor / $maxEmocion, 1); ?>
                        <text class="chart-label" x="0" y="<?= $y ?>"><?= reporteEsc($item['etiqueta']) ?></text>
                        <rect class="chart-track" x="175" y="<?= $y - 16 ?>" width="410" height="20" rx="10"></rect>
                        <rect class="chart-bar chart-bar-blue" x="175" y="<?= $y - 16 ?>" width="<?= $ancho ?>" height="20" rx="10"></rect>
                        <text class="chart-value" x="600" y="<?= $y ?>"><?= $valor ?></text>
                    <?php endforeach; ?>
                </svg>
            <?php endif; ?>
        </article>
        <article class="panel-seccion reporte-grafica-card">
            <div class="encabezado-tabla"><div><p class="eyebrow-admin">Evolución</p><h2>Registros por mes</h2></div></div>
            <?php if ($evolucionGrafica === []): ?>
                <p class="tabla-vacia">No hay periodos para graficar con los filtros seleccionados.</p>
            <?php else: ?>
                <svg class="reporte-chart-svg reporte-chart-evolucion" viewBox="0 0 640 285" role="img" aria-label="Gráfica de registros por mes">
                    <line class="chart-axis" x1="32" y1="220" x2="610" y2="220"></line>
                    <?php foreach ($evolucionGrafica as $indice => $item): $valor = reporteNumero($item['total'] ?? 0); $x = 50 + ($indice * 70); $alto = round(170 * $valor / $maxEvolucion, 1); $y = 220 - $alto; ?>
                        <rect class="chart-bar chart-bar-purple" x="<?= $x ?>" y="<?= $y ?>" width="38" height="<?= $alto ?>" rx="7"></rect>
                        <text class="chart-value chart-value-center" x="<?= $x + 19 ?>" y="<?= max(15, $y - 8) ?>"><?= $valor ?></text>
                        <text class="chart-period" x="<?= $x + 19 ?>" y="242"><?= reporteEsc($item['periodo']) ?></text>
                    <?php endforeach; ?>
                </svg>
            <?php endif; ?>
        </article>
    </section>

    <section class="reporte-destacados">
        <article class="panel-seccion">
            <div class="encabezado-tabla"><div><p class="eyebrow-admin">Atención prioritaria</p><h2>Alumnos con mayor seguimiento</h2></div></div>
            <div class="tabla-scroll"><table class="tabla-admin reporte-tabla"><thead><tr><th>Alumno</th><th>Registros</th><th>Incidencias</th><th>Último registro</th></tr></thead><tbody><?php if ($alumnosSeguimiento === []): ?><tr><td colspan="4" class="tabla-vacia">No hay alumnos con registros en este filtro.</td></tr><?php else: foreach ($alumnosSeguimiento as $alumno): ?><tr><td><strong><?= reporteEsc($alumno['alumno']) ?></strong></td><td><?= reporteNumero($alumno['registros'] ?? 0) ?></td><td><span class="reporte-incidencias-pill"><?= reporteNumero($alumno['incidencias'] ?? 0) ?></span></td><td><?= reporteEsc($alumno['ultimo_registro']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
        </article>
    </section>

    <section class="panel-seccion reporte-resultados">
        <div class="encabezado-tabla"><div><p class="eyebrow-admin">Detalle</p><h2>Registros incluidos</h2><p class="panel-descripcion"><?= count($historial) ?> registros encontrados con los filtros actuales.</p></div></div>
        <div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Fecha</th><th>Alumno</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Descripción</th></tr></thead><tbody><?php if ($historial === []): ?><tr><td colspan="6" class="tabla-vacia">No hay registros con los filtros seleccionados.</td></tr><?php else: foreach ($historial as $registro): ?><tr><td><?= reporteEsc($registro['fecha_hora']) ?></td><td><?= reporteEsc($registro['alumno']) ?></td><td><?= reporteEsc($registro['tipo_nota']) ?></td><td><?= reporteEsc($registro['emocion'] ?: '—') ?></td><td><?= reporteEsc($registro['accion_contencion'] ?: '—') ?></td><td><?= reporteEsc($registro['nota_descripcion']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>
</main>
</div>
</body>
</html>
