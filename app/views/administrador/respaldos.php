<?php
$mensaje = $resultado['mensaje'] ?? null;
$ok = (bool)($resultado['ok'] ?? true);
$respaldos = $respaldos ?? [];
$esPrincipal = (bool)($_SESSION['administrador_principal'] ?? false);
function respaldoEsc($valor): string { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }
function respaldoTamano($bytes): string { return number_format(((int)$bytes) / 1024, 1) . ' KB'; }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Respaldos | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
<div class="panel-contenedor">
    <header class="panel-topbar"><strong>RESPALDOS DE BASE DE DATOS</strong><span>Bienvenido/a, <?= respaldoEsc($_SESSION['administrador_nombre'] ?? 'Administrador') ?></span></header>
    <main class="contenedor-admin respaldo-contenedor">
        <section class="encabezado-seccion"><p class="eyebrow-admin">Mantenimiento</p><h1>Respaldo y restauración</h1><p>Administra copias de seguridad de la base de datos <strong>kairos_db</strong>.</p></section>
        <?php if ($mensaje !== null): ?><div class="mensaje <?= $ok ? 'mensaje-exito' : 'mensaje-error' ?>"><?= respaldoEsc($mensaje) ?></div><?php endif; ?>
        <section class="respaldo-grid">
            <article class="panel-seccion respaldo-card"><h2>Crear respaldo</h2><p>Genera una copia completa de la estructura y los datos actuales de Kairos.</p><form method="post"><input type="hidden" name="accion" value="crear"><button class="boton" type="submit">Generar respaldo SQL</button></form></article>
            <article class="panel-seccion respaldo-card"><h2>Restaurar respaldo</h2><p class="advertencia-respaldo">La restauración reemplaza los datos actuales. Antes de continuar, asegúrate de contar con un respaldo reciente.</p><?php if ($esPrincipal): ?><form method="post" enctype="multipart/form-data" onsubmit="return confirm('Esta acción reemplazará los datos actuales. ¿Deseas continuar?');"><input type="hidden" name="accion" value="restaurar"><label for="archivo-respaldo">Archivo SQL</label><input id="archivo-respaldo" type="file" name="respaldo" accept=".sql,application/sql" required><button class="boton boton-peligro" type="submit">Restaurar base de datos</button></form><?php else: ?><p>Solo el administrador principal puede restaurar la base de datos.</p><?php endif; ?></article>
        </section>
        <section class="panel-seccion"><div class="seccion-titulo"><h2>Respaldos disponibles</h2></div><?php if ($respaldos === []): ?><p>No hay respaldos generados desde el sistema.</p><?php else: ?><div class="tabla-responsive"><table class="tabla-admin"><thead><tr><th>Archivo</th><th>Fecha</th><th>Tamaño</th><th>Acción</th></tr></thead><tbody><?php foreach ($respaldos as $copia): ?><tr><td><?= respaldoEsc($copia['nombre']) ?></td><td><?= respaldoEsc($copia['fecha']) ?></td><td><?= respaldoEsc(respaldoTamano($copia['tamano'])) ?></td><td><a class="boton boton-secundario" href="respaldos.php?accion=descargar&amp;archivo=<?= rawurlencode($copia['nombre']) ?>">Descargar</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
    </main>
</div>
</body>
</html>
