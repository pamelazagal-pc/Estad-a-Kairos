<?php
$lecturas = $resultado['lecturas'] ?? [];
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
    <title>Lecturas y cuentos | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=lecturas-1">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_administrador.php'; ?>
<main class="contenedor-admin">
    <div class="encabezado-seccion">
        <div><p class="eyebrow-admin">Contenido pedagógico</p><h1>Lecturas y cuentos</h1><p>Administra lecturas cortas que los maestros pueden consultar durante sus actividades de regulación.</p></div>
    </div>
    <?php if ($errores !== []): ?><div class="alerta error"><?php foreach ($errores as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <section class="contenido-dos-columnas">
        <div class="tarjeta-admin">
            <h2><?= $formulario ? 'Editar lectura' : 'Registrar lectura' ?></h2>
            <form method="post" class="formulario-admin">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($formulario): ?><input type="hidden" name="id_lectura" value="<?= (int)$formulario['id_lectura'] ?>"><?php endif; ?>
                <label for="titulo">Título del cuento o lectura</label>
                <input id="titulo" name="titulo" maxlength="150" required value="<?= htmlspecialchars($formulario['titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <label for="categoria_tematica">Categoría temática</label>
                <input id="categoria_tematica" name="categoria_tematica" maxlength="100" required placeholder="Ej. Paciencia, aceptar críticas" value="<?= htmlspecialchars($formulario['categoria_tematica'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <label for="tiempo_estimado_min">Tiempo estimado de lectura</label>
                <input id="tiempo_estimado_min" name="tiempo_estimado_min" type="number" min="1" max="240" required value="<?= (int)($formulario['tiempo_estimado_min'] ?? 5) ?>">
                <label for="contenido">Contenido</label>
                <textarea id="contenido" name="contenido" rows="10" maxlength="65535" required><?= htmlspecialchars($formulario['contenido'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <button class="boton" type="submit"><?= $formulario ? 'Guardar cambios' : 'Registrar lectura' ?></button>
                <?php if ($formulario): ?><a class="boton-secundario" href="lecturas.php">Cancelar edición</a><?php endif; ?>
            </form>
        </div>
        <div class="tarjeta-admin">
            <div class="encabezado-tabla"><h2>Lecturas registradas</h2><form method="get"><select name="estado" onchange="this.form.submit()"><option <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option><option <?= $estado === 'Activo' ? 'selected' : '' ?>>Activo</option><option <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option></select></form></div>
            <div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Título</th><th>Categoría</th><th>Tiempo</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php if ($lecturas === []): ?><tr><td colspan="5">No hay lecturas para este filtro.</td></tr><?php endif; ?>
            <?php foreach ($lecturas as $lectura): ?><tr><td><strong><?= htmlspecialchars($lectura['titulo'], ENT_QUOTES, 'UTF-8') ?></strong><details><summary>Ver contenido</summary><p><?= nl2br(htmlspecialchars($lectura['contenido'], ENT_QUOTES, 'UTF-8')) ?></p></details></td><td><?= htmlspecialchars($lectura['categoria_tematica'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$lectura['tiempo_estimado_min'] ?> min</td><td><span class="estado <?= strtolower($lectura['estado']) ?>"><?= htmlspecialchars($lectura['estado'], ENT_QUOTES, 'UTF-8') ?></span></td><td class="acciones-tabla"><a class="boton-tabla" href="lecturas.php?editar=<?= (int)$lectura['id_lectura'] ?>&estado=<?= urlencode($estado) ?>">Editar</a><form method="post"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_lectura" value="<?= (int)$lectura['id_lectura'] ?>"><input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="operacion" value="<?= $lectura['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>"><button class="boton-tabla <?= $lectura['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>" type="submit"><?= $lectura['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?></button></form></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </section>
</main>
</div>
</body>
</html>
