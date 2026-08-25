<?php
$recompensas = $resultado['recompensas'] ?? [];
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
    <title>Medallas y recompensas | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=recompensas-1">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_administrador.php'; ?>
<main class="contenedor-admin">
    <div class="encabezado-seccion">
        <div><p class="eyebrow-admin">Gamificación positiva</p><h1>Medallas y recompensas</h1><p>Crea reconocimientos que los maestros pueden asignar a sus alumnos junto con puntos de esfuerzo.</p></div>
    </div>
    <?php if ($errores !== []): ?><div class="alerta error"><?php foreach ($errores as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endforeach; ?></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <section class="contenido-dos-columnas">
        <div class="tarjeta-admin">
            <h2><?= $formulario ? 'Editar medalla' : 'Registrar medalla' ?></h2>
            <form method="post" class="formulario-admin">
                <input type="hidden" name="accion" value="guardar">
                <input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>">
                <?php if ($formulario): ?><input type="hidden" name="id_recompensa" value="<?= (int)$formulario['id_recompensa'] ?>"><?php endif; ?>
                <label for="nombre_insignia">Nombre de la medalla</label>
                <input id="nombre_insignia" name="nombre_insignia" maxlength="100" required placeholder="Ej. Premio a la paciencia" value="<?= htmlspecialchars($formulario['nombre_insignia'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="5" maxlength="65535" required placeholder="Describe la conducta positiva que reconoce."><?= htmlspecialchars($formulario['descripcion'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                <label for="puntos_otorgados">Puntos de esfuerzo</label>
                <input id="puntos_otorgados" name="puntos_otorgados" type="number" min="1" max="10000" required value="<?= (int)($formulario['puntos_otorgados'] ?? 10) ?>">
                <label for="icono_url">Icono o emoji opcional</label>
                <input id="icono_url" name="icono_url" maxlength="255" placeholder="Ej. ⭐ o URL de imagen" value="<?= htmlspecialchars($formulario['icono_url'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <button class="boton" type="submit"><?= $formulario ? 'Guardar cambios' : 'Registrar medalla' ?></button>
                <?php if ($formulario): ?><a class="boton-secundario" href="recompensas.php">Cancelar edición</a><?php endif; ?>
            </form>
        </div>
        <div class="tarjeta-admin">
            <div class="encabezado-tabla"><h2>Catálogo registrado</h2><form method="get"><select name="estado" onchange="this.form.submit()"><option <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option><option <?= $estado === 'Activo' ? 'selected' : '' ?>>Activo</option><option <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option></select></form></div>
            <div class="tabla-scroll"><table class="tabla-admin"><thead><tr><th>Medalla</th><th>Descripción</th><th>Puntos</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
            <?php if ($recompensas === []): ?><tr><td colspan="5">No hay medallas para este filtro.</td></tr><?php endif; ?>
            <?php foreach ($recompensas as $recompensa): ?><tr><td><strong><?= htmlspecialchars($recompensa['icono_url'] ?: '★', ENT_QUOTES, 'UTF-8') ?> <?= htmlspecialchars($recompensa['nombre_insignia'], ENT_QUOTES, 'UTF-8') ?></strong></td><td><?= htmlspecialchars($recompensa['descripcion'], ENT_QUOTES, 'UTF-8') ?></td><td><?= (int)$recompensa['puntos_otorgados'] ?></td><td><span class="estado <?= strtolower($recompensa['estado']) ?>"><?= htmlspecialchars($recompensa['estado'], ENT_QUOTES, 'UTF-8') ?></span></td><td class="acciones-tabla"><a class="boton-tabla" href="recompensas.php?editar=<?= (int)$recompensa['id_recompensa'] ?>&estado=<?= urlencode($estado) ?>">Editar</a><form method="post"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_recompensa" value="<?= (int)$recompensa['id_recompensa'] ?>"><input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>"><input type="hidden" name="operacion" value="<?= $recompensa['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>"><button class="boton-tabla <?= $recompensa['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>" type="submit"><?= $recompensa['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?></button></form></td></tr><?php endforeach; ?>
            </tbody></table></div>
        </div>
    </section>
</main>
</div>
</body>
</html>
