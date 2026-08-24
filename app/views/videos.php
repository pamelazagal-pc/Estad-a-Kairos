<?php
$videos = $resultado['videos'] ?? [];
$formulario = $resultado['formulario'] ?? null;
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$estado = $resultado['estado'] ?? 'Todos';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Videos de pausas activas | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=videos-1">
</head>
<body>
<main class="contenedor-admin">
    <div class="encabezado-seccion">
        <div><p class="eyebrow-admin">Contenido pedagógico</p><h1>Videos de pausas activas</h1><p>Administra los enlaces de YouTube que podrán utilizar los maestros.</p></div>
        <a class="boton-secundario" href="panel_administrador.php">Volver al panel</a>
    </div>
    <?php if ($errores !== []): ?><div class="alerta error"><?php foreach ($errores as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <section class="contenido-dos-columnas">
        <div class="tarjeta-admin">
            <h2><?= $formulario ? 'Editar video' : 'Registrar video' ?></h2>
            <form method="post" class="formulario-admin">
                <input type="hidden" name="accion" value="guardar">
                <?php if ($formulario): ?><input type="hidden" name="id_video" value="<?= (int)$formulario['id_video'] ?>"><?php endif; ?>
                <label for="titulo">Título</label>
                <input id="titulo" name="titulo" maxlength="150" required value="<?= htmlspecialchars($formulario['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <label for="url_youtube">Enlace de YouTube</label>
                <input id="url_youtube" name="url_youtube" type="url" required placeholder="https://www.youtube.com/watch?v=..." value="<?= htmlspecialchars($formulario['url_youtube'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <div class="formulario-dos-columnas">
                    <div><label for="duracion_segundos">Duración (segundos)</label><input id="duracion_segundos" name="duracion_segundos" type="number" min="1" max="7200" required value="<?= (int)($formulario['duracion_segundos'] ?? 60) ?>"></div>
                    <div><label for="categoria">Categoría</label><input id="categoria" name="categoria" maxlength="100" required value="<?= htmlspecialchars($formulario['categoria'] ?? 'Relajación', ENT_QUOTES, 'UTF-8') ?>"></div>
                </div>
                <button class="boton" type="submit"><?= $formulario ? 'Guardar cambios' : 'Registrar video' ?></button>
                <?php if ($formulario): ?><a class="boton-secundario" href="videos.php">Cancelar edición</a><?php endif; ?>
            </form>
        </div>
        <div class="tarjeta-admin">
            <div class="encabezado-tabla"><h2>Videos registrados</h2><form method="get"><select name="estado" onchange="this.form.submit()"><option <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option><option <?= $estado === 'Activo' ? 'selected' : '' ?>>Activo</option><option <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option></select></form></div>
            <div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Título</th><th>Categoría</th><th>Duración</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php if ($videos === []): ?><tr><td colspan="5">No hay videos para este filtro.</td></tr><?php endif; ?>
            <?php foreach ($videos as $video): ?><tr><td><strong><?= htmlspecialchars($video['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><br><a href="<?= htmlspecialchars($video['url_youtube'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir en YouTube</a></td><td><?= htmlspecialchars($video['categoria'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$video['duracion_segundos'] ?> s</td><td><span class="estado <?= strtolower($video['estado']) ?>"><?= htmlspecialchars($video['estado'], ENT_QUOTES, 'UTF-8') ?></span></td><td class="acciones-tabla"><a class="boton-tabla" href="videos.php?editar=<?= (int)$video['id_video'] ?>&estado=<?= urlencode($estado) ?>">Editar</a><form method="post"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_video" value="<?= (int)$video['id_video'] ?>"><input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="operacion" value="<?= $video['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>"><button class="boton-tabla <?= $video['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>" type="submit"><?= $video['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?></button></form></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </section>
</main>
</body>
</html>
