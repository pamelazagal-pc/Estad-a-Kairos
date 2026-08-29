<?php
$datos = $datos ?? [];
$filtros = $filtros ?? [];
$grupos = $datos['grupos'] ?? [];
$reporte = $datos['reporte'] ?? [];
$resumen = $reporte['resumen'] ?? [];
$emociones = $reporte['emociones'] ?? [];
$evolucion = $reporte['evolucion'] ?? [];
$alumnosSeguimiento = $reporte['alumnos_seguimiento'] ?? [];
$historial = $reporte['historial'] ?? [];

function reportePdfEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function reportePdfNumero($valor): int
{
    return (int)($valor ?? 0);
}

$maxEmocion = 1;
foreach ($emociones as $emocion) $maxEmocion = max($maxEmocion, reportePdfNumero($emocion['total'] ?? 0));
$maxEvolucion = 1;
foreach ($evolucion as $periodo) $maxEvolucion = max($maxEvolucion, reportePdfNumero($periodo['total'] ?? 0));
$evolucionGrafica = array_slice($evolucion, -8);
$grupoSeleccionado = 'Todos los grupos';
foreach ($grupos as $grupo) {
    if ((int)($filtros['id_grupo'] ?? 0) === (int)$grupo['id_grupo']) {
        $grupoSeleccionado = $grupo['nombre_grupo'] . ' · ' . $grupo['ciclo_escolar'];
        break;
    }
}
$periodo = (!empty($filtros['desde']) || !empty($filtros['hasta']))
    ? (($filtros['desde'] ?? '') ?: 'inicio') . ' — ' . (($filtros['hasta'] ?? '') ?: 'actualidad')
    : 'Todo el historial disponible';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reporte emocional | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body class="reporte-pdf-page">
    <header class="reporte-pdf-header">
        <div>
            <p style="margin:0;color:#335c81;font-size:12px;font-weight:800;letter-spacing:1px;text-transform:uppercase">Kairos | Bienestar escolar</p>
            <h1>Reporte emocional de grupos</h1>
            <p>Generado el <?= reportePdfEsc(date('d/m/Y H:i')) ?> · Docente: <?= reportePdfEsc($datos['maestro']['nombre'] ?? 'Maestro') ?></p>
        </div>
        <div style="text-align:right"><p><strong>Grupo:</strong> <?= reportePdfEsc($grupoSeleccionado) ?></p><p><strong>Periodo:</strong> <?= reportePdfEsc($periodo) ?></p></div>
    </header>

    <section class="reporte-pdf-summary" aria-label="Resumen del reporte">
        <div><strong><?= reportePdfNumero($resumen['total'] ?? 0) ?></strong><span>Registros</span></div>
        <div><strong><?= reportePdfNumero($resumen['incidencias'] ?? 0) ?></strong><span>Incidencias</span></div>
        <div><strong><?= reportePdfNumero($resumen['observaciones'] ?? 0) ?></strong><span>Observaciones</span></div>
        <div><strong><?= reportePdfNumero($resumen['logros'] ?? 0) ?></strong><span>Logros</span></div>
        <div><strong><?= reportePdfNumero($resumen['alumnos'] ?? 0) ?></strong><span>Alumnos</span></div>
    </section>

    <section class="reporte-pdf-grid">
        <div class="reporte-pdf-chart"><h2>Emociones registradas</h2>
            <?php if ($emociones === []): ?><p>No hay emociones para mostrar.</p><?php else: ?>
            <svg class="reporte-chart-svg" viewBox="0 0 640 285" role="img" aria-label="Emociones registradas">
                <?php foreach (array_slice($emociones, 0, 6) as $indice => $item): $valor = reportePdfNumero($item['total'] ?? 0); $y = 28 + ($indice * 42); $ancho = round(410 * $valor / $maxEmocion, 1); ?>
                    <text class="chart-label" x="0" y="<?= $y ?>"><?= reportePdfEsc($item['etiqueta']) ?></text><rect class="chart-track" x="175" y="<?= $y - 16 ?>" width="410" height="20" rx="10"></rect><rect class="chart-bar chart-bar-blue" x="175" y="<?= $y - 16 ?>" width="<?= $ancho ?>" height="20" rx="10"></rect><text class="chart-value" x="600" y="<?= $y ?>"><?= $valor ?></text>
                <?php endforeach; ?>
            </svg><?php endif; ?>
        </div>
        <div class="reporte-pdf-chart"><h2>Registros por mes</h2>
            <?php if ($evolucionGrafica === []): ?><p>No hay periodos para mostrar.</p><?php else: ?>
            <svg class="reporte-chart-svg" viewBox="0 0 640 285" role="img" aria-label="Registros por mes"><line class="chart-axis" x1="32" y1="220" x2="610" y2="220"></line>
                <?php foreach ($evolucionGrafica as $indice => $item): $valor = reportePdfNumero($item['total'] ?? 0); $x = 50 + ($indice * 70); $alto = round(170 * $valor / $maxEvolucion, 1); $y = 220 - $alto; ?>
                    <rect class="chart-bar chart-bar-purple" x="<?= $x ?>" y="<?= $y ?>" width="38" height="<?= $alto ?>" rx="7"></rect><text class="chart-value" x="<?= $x + 19 ?>" y="<?= max(15, $y - 8) ?>"><?= $valor ?></text><text class="chart-period" x="<?= $x + 19 ?>" y="242"><?= reportePdfEsc($item['periodo']) ?></text>
                <?php endforeach; ?>
            </svg><?php endif; ?>
        </div>
    </section>

    <h2>Alumnos con mayor seguimiento</h2>
    <table class="reporte-pdf-table"><thead><tr><th>Alumno</th><th>Registros</th><th>Incidencias</th><th>Último registro</th></tr></thead><tbody>
        <?php if ($alumnosSeguimiento === []): ?><tr><td colspan="4">No hay alumnos con registros en el filtro actual.</td></tr><?php else: foreach ($alumnosSeguimiento as $alumno): ?><tr><td><?= reportePdfEsc($alumno['alumno']) ?></td><td><?= reportePdfNumero($alumno['registros'] ?? 0) ?></td><td><?= reportePdfNumero($alumno['incidencias'] ?? 0) ?></td><td><?= reportePdfEsc($alumno['ultimo_registro']) ?></td></tr><?php endforeach; endif; ?>
    </tbody></table>

    <h2>Detalle de registros</h2>
    <table class="reporte-pdf-table"><thead><tr><th>Fecha</th><th>Alumno</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Descripción</th></tr></thead><tbody>
        <?php if ($historial === []): ?><tr><td colspan="6">No hay registros con los filtros seleccionados.</td></tr><?php else: foreach ($historial as $registro): ?><tr><td><?= reportePdfEsc($registro['fecha_hora']) ?></td><td><?= reportePdfEsc($registro['alumno']) ?></td><td><?= reportePdfEsc($registro['tipo_nota']) ?></td><td><?= reportePdfEsc($registro['emocion'] ?: '—') ?></td><td><?= reportePdfEsc($registro['accion_contencion'] ?: '—') ?></td><td><?= nl2br(reportePdfEsc($registro['nota_descripcion'])) ?></td></tr><?php endforeach; endif; ?>
    </tbody></table>
    <footer class="reporte-pdf-footer">Este reporte es una herramienta de seguimiento pedagógico. El semáforo y los registros no constituyen un diagnóstico clínico.</footer>
<script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 350); });</script>
</body>
</html>
