<?php
$esMaestro = (bool)($resultado['es_maestro'] ?? false);
$consejos = $resultado['consejos'] ?? [];
$formulario = $resultado['formulario'] ?? null;
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$estado = $resultado['estado'] ?? 'Todos';
$esc = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$nombreUsuario = $esMaestro
    ? ($_SESSION['maestro_nombre'] ?? 'Maestro')
    : ($_SESSION['administrador_nombre'] ?? 'Administrador');
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consejos para el hogar | Kairos</title>
    <?php if ($esMaestro): ?>
        <link rel="stylesheet" href="../../CSS/maestro_dashboard.css?v=consejos-2">
        <link rel="stylesheet" href="../../CSS/consejos.css?v=consejos-2">
    <?php else: ?>
        <link rel="stylesheet" href="../CSS/main.css?v=consejos-2">
        <link rel="stylesheet" href="../CSS/consejos.css?v=consejos-2">
    <?php endif; ?>
</head>
<body class="<?= $esMaestro ? 'maestro-body' : 'panel-body' ?>">
<?php if ($esMaestro): ?>
    <?php require_once __DIR__ . '/sidebar_maestro.php'; ?>
    <div class="main consejos-main">
        <?php $maestro = ['nombre' => $nombreUsuario]; require_once __DIR__ . '/topbar_maestro.php'; ?>
<?php else: ?>
    <?php require_once __DIR__ . '/sidebar_administrador.php'; ?>
    <div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
        <?php $nombreAdministrador = $nombreUsuario; require_once __DIR__ . '/topbar_administrador.php'; ?>
<?php endif; ?>

    <main class="<?= $esMaestro ? 'content consejos-content' : 'contenedor-admin consejos-admin' ?>">
        <div class="encabezado-seccion <?= $esMaestro ? 'consejos-heading' : '' ?>">
            <div>
                <p class="eyebrow-admin">Acompañamiento escolar</p>
                <h1>Consejos para el hogar</h1>
                <p><?= $esMaestro
                    ? 'Comparte recomendaciones breves para que las familias continúen el acompañamiento emocional en casa.'
                    : 'Publica recomendaciones breves para apoyar a las familias en el seguimiento emocional de los alumnos.' ?></p>
            </div>
        </div>

        <?php if ($errores !== []): ?>
            <div class="alerta error" role="alert">
                <?php foreach ($errores as $error): ?><p><?= $esc($error) ?></p><?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($mensaje): ?><div class="alerta ok" role="status"><?= $esc($mensaje) ?></div><?php endif; ?>

        <section class="contenido-dos-columnas consejos-layout">
            <div class="tarjeta-admin consejos-form-card">
                <h2><?= $formulario ? 'Editar consejo' : 'Publicar consejo' ?></h2>
                <p class="panel-nota">El consejo quedará visible para los tutores cuando esté activo.</p>
                <form method="post" class="formulario-admin">
                    <input type="hidden" name="accion" value="guardar">
                    <input type="hidden" name="estado_filtro" value="<?= $esc($estado) ?>">
                    <?php if ($formulario): ?><input type="hidden" name="id_consejo" value="<?= (int)$formulario['id_consejo'] ?>"><?php endif; ?>

                    <label for="titulo">Título</label>
                    <input id="titulo" name="titulo" maxlength="150" required placeholder="Ej. Escuchar antes de responder" value="<?= $esc($formulario['titulo'] ?? '') ?>">

                    <label for="categoria">Categoría</label>
                    <input id="categoria" name="categoria" maxlength="100" required placeholder="Ej. Paciencia y comunicación" value="<?= $esc($formulario['categoria'] ?? 'Apoyo Emocional') ?>">

                    <label for="recomendacion">Recomendación</label>
                    <textarea id="recomendacion" name="recomendacion" rows="10" maxlength="65535" required placeholder="Escribe una recomendación breve para la familia..."><?= $esc($formulario['recomendacion'] ?? '') ?></textarea>

                    <button class="boton" type="submit"><?= $formulario ? 'Guardar cambios' : 'Publicar consejo' ?></button>
                    <?php if ($formulario): ?><a class="boton-secundario" href="consejos.php?estado=<?= urlencode($estado) ?>">Cancelar edición</a><?php endif; ?>
                </form>
            </div>

            <div class="tarjeta-admin consejos-list-card">
                <div class="encabezado-tabla">
                    <div>
                        <h2>Consejos publicados</h2>
                        <p class="panel-nota">Administra el contenido visible en el panel del tutor.</p>
                    </div>
                    <form method="get" aria-label="Filtrar consejos por estado">
                        <label class="sr-only" for="estado">Estado</label>
                        <select id="estado" name="estado" onchange="this.form.submit()">
                            <option value="Todos" <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="Activo" <?= $estado === 'Activo' ? 'selected' : '' ?>>Activos</option>
                            <option value="Inactivo" <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivos</option>
                        </select>
                    </form>
                </div>
                <div class="tabla-scroll">
                    <table class="tabla-admin">
                        <thead>
                            <tr><th>Título</th><th>Categoría</th><th>Autor</th><th>Estado</th><?php if (!$esMaestro): ?><th>Acciones</th><?php endif; ?></tr>
                        </thead>
                        <tbody>
                        <?php if ($consejos === []): ?>
                            <tr><td colspan="<?= $esMaestro ? 4 : 5 ?>">No hay consejos para este filtro.</td></tr>
                        <?php else: foreach ($consejos as $consejo): ?>
                            <tr>
                                <td>
                                    <strong><?= $esc($consejo['titulo']) ?></strong>
                                    <details><summary>Ver recomendación</summary><p><?= nl2br($esc($consejo['recomendacion'])) ?></p></details>
                                </td>
                                <td><?= $esc($consejo['categoria']) ?></td>
                                <td><?= $esc($consejo['autor']) ?></td>
                                <td><span class="estado <?= strtolower($esc($consejo['estado'])) ?>"><?= $esc($consejo['estado']) ?></span></td>
                                <?php if (!$esMaestro): ?>
                                    <td class="acciones-tabla">
                                        <a class="boton-tabla" href="consejos.php?editar=<?= (int)$consejo['id_consejo'] ?>&estado=<?= urlencode($estado) ?>">Editar</a>
                                        <form method="post">
                                            <input type="hidden" name="accion" value="estado">
                                            <input type="hidden" name="id_consejo" value="<?= (int)$consejo['id_consejo'] ?>">
                                            <input type="hidden" name="estado_filtro" value="<?= $esc($estado) ?>">
                                            <input type="hidden" name="operacion" value="<?= $consejo['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>">
                                            <button class="boton-tabla <?= $consejo['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>" type="submit">
                                                <?= $consejo['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?>
                                            </button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>

<?php if ($esMaestro): ?>
    </div>
<?php else: ?>
    </div>
<?php endif; ?>
</body>
</html>
