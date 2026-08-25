<?php
$datos = $datos ?? [];
$filtros = $filtros ?? [];
$grupos = $datos['grupos'] ?? [];
$historial = $datos['historial'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
function reporteEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reportes de maestro | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_maestro.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_maestro.php'; ?>

<main class="contenedor-admin reporte-contenedor">
    <header class="encabezado-seccion">
        <div>
            <p class="eyebrow-admin">Seguimiento emocional</p>
            <h1>Reportes de mi grupo</h1>
            <p>Consulta incidencias por alumno, periodo y tipo de registro.</p>
        </div>
        <div class="acciones encabezado-acciones">
            <button class="boton" type="button" onclick="window.print()">Imprimir / PDF</button>
        </div>
    </header>
    <form class="filtros-reporte" method="get">
        <label>Alumno<select name="id_alumno"><option value="">Todos</option><?php foreach ($alumnos as $alumno): ?><option value="<?= (int)$alumno['id_alumno'] ?>" <?= (int)($filtros['id_alumno'] ?? 0) === (int)$alumno['id_alumno'] ? 'selected' : '' ?>><?= reporteEsc($alumno['nombre_completo']) ?></option><?php endforeach; ?></select></label>
        <label>Tipo<select name="tipo"><option value="">Todos</option><?php foreach (['Observacion','Incidencia','Logro'] as $tipo): ?><option value="<?= $tipo ?>" <?= ($filtros['tipo'] ?? '') === $tipo ? 'selected' : '' ?>><?= $tipo ?></option><?php endforeach; ?></select></label>
        <label>Emoción<input name="emocion" value="<?= reporteEsc($filtros['emocion'] ?? '') ?>" placeholder="Ej. Ansiedad"></label>
        <label>Desde<input type="date" name="desde" value="<?= reporteEsc($filtros['desde'] ?? '') ?>"></label>
        <label>Hasta<input type="date" name="hasta" value="<?= reporteEsc($filtros['hasta'] ?? '') ?>"></label>
        <div class="acciones filtro-acciones"><button class="boton" type="submit">Aplicar filtros</button><a class="boton-secundario" href="reportes.php">Limpiar</a></div>
    </form>
    <section class="panel-seccion">
        <div class="encabezado-tabla"><div><h2>Resultados</h2><p class="panel-descripcion"><?= count($historial) ?> registros encontrados.</p></div></div>
        <div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Fecha</th><th>Alumno</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Descripción</th></tr></thead><tbody><?php if ($historial === []): ?><tr><td colspan="6" class="tabla-vacia">No hay registros con los filtros seleccionados.</td></tr><?php else: foreach ($historial as $registro): ?><tr><td><?= reporteEsc($registro['fecha_hora']) ?></td><td><?= reporteEsc($registro['alumno']) ?></td><td><?= reporteEsc($registro['tipo_nota']) ?></td><td><?= reporteEsc($registro['emocion'] ?: '—') ?></td><td><?= reporteEsc($registro['accion_contencion'] ?: '—') ?></td><td><?= reporteEsc($registro['nota_descripcion']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>
</main>
</div>
</body>
</html>
