<?php
$datos = $resultado['datos'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$grupos = $resultado['grupos'] ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar alumno | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css">
</head>
<body>
<main class="card">
    <h1>Registrar alumno</h1>
    <p>Agrega un alumno y asígnalo a un grupo activo.</p>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

    <?php if ($grupos === []): ?>
        <div class="error">Primero debes registrar al menos un grupo activo.</div>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="accion" value="registrar_alumno">
            <label for="nombre">Nombre</label>
            <input id="nombre" name="nombre" maxlength="100" value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="apellido_paterno">Apellido paterno</label>
            <input id="apellido_paterno" name="apellido_paterno" maxlength="50" value="<?= htmlspecialchars($datos['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="apellido_materno">Apellido materno (opcional)</label>
            <input id="apellido_materno" name="apellido_materno" maxlength="50" value="<?= htmlspecialchars($datos['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="edad">Edad (opcional)</label>
            <input id="edad" name="edad" type="number" min="5" max="18" value="<?= htmlspecialchars($datos['edad'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="id_grupo">Grupo</label>
            <select id="id_grupo" name="id_grupo" required>
                <option value="">Selecciona un grupo</option>
                <?php foreach ($grupos as $grupo): ?>
                    <?php $idGrupo = (string) $grupo['id_grupo']; ?>
                    <option value="<?= $idGrupo ?>" <?= (string)($datos['id_grupo'] ?? '') === $idGrupo ? 'selected' : '' ?>>
                        <?= htmlspecialchars($grupo['grado'] . '° ' . $grupo['grupo'] . ' — ' . $grupo['ciclo_escolar'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Registrar alumno</button>
        </form>
    <?php endif; ?>

    <a class="boton boton-secundario" href="panel_administrador.php">Regresar al panel</a>
</main>
</body>
</html>
