<?php
$datos = $resultadoEdicion['datos'] ?? [];
$errores = $resultadoEdicion['errores'] ?? [];
$grupos = $resultadoEdicion['grupos'] ?? [];
$etiquetas = ['administradores'=>'administrador', 'maestros'=>'maestro', 'tutores'=>'tutor', 'alumnos'=>'alumno', 'grupos'=>'grupo'];
$etiqueta = $etiquetas[$tipo] ?? 'registro';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar <?= $etiqueta ?> | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css?v=edicion-1">
</head>
<body>
<main class="card">
    <p class="top">Edición de registro</p>
    <h1>Editar <?= ucfirst($etiqueta) ?></h1>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="actualizar">
        <?php if ($tipo === 'administradores' || $tipo === 'maestros' || $tipo === 'tutores'): ?>
            <label for="nombre">Nombre completo</label><input id="nombre" name="nombre" maxlength="100" value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if ($tipo === 'tutores'): ?><label for="cargo">Tipo de tutor</label><select id="cargo" name="cargo" required><option value="Padre" <?= ($datos['cargo'] ?? '') === 'Padre' ? 'selected' : '' ?>>Padre</option><option value="Tutor" <?= ($datos['cargo'] ?? '') === 'Tutor' ? 'selected' : '' ?>>Tutor</option></select><?php endif; ?>
            <label for="correo">Correo electrónico</label><input id="correo" name="correo" type="email" maxlength="150" value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <?php if ($tipo !== 'administradores'): ?><label for="telefono">Teléfono</label><input id="telefono" name="telefono" maxlength="20" value="<?= htmlspecialchars($datos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?>
            <label for="password">Nueva contraseña (opcional)</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password"><small class="ayuda">Déjalo vacío para conservar la contraseña actual.</small>
        <?php elseif ($tipo === 'grupos'): ?>
            <label for="grado">Grado</label><input id="grado" name="grado" type="number" min="1" max="6" value="<?= htmlspecialchars($datos['grado'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="grupo">Grupo</label><input id="grupo" name="grupo" maxlength="1" value="<?= htmlspecialchars($datos['grupo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="ciclo_escolar">Ciclo escolar</label><input id="ciclo_escolar" name="ciclo_escolar" maxlength="20" value="<?= htmlspecialchars($datos['ciclo_escolar'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <?php else: ?>
            <label for="nombre">Nombre</label><input id="nombre" name="nombre" maxlength="100" value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="apellido_paterno">Apellido paterno</label><input id="apellido_paterno" name="apellido_paterno" maxlength="50" value="<?= htmlspecialchars($datos['apellido_paterno'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            <label for="apellido_materno">Apellido materno</label><input id="apellido_materno" name="apellido_materno" maxlength="50" value="<?= htmlspecialchars($datos['apellido_materno'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="edad">Edad</label><input id="edad" name="edad" type="number" min="5" max="18" value="<?= htmlspecialchars($datos['edad'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="id_grupo">Grupo</label><select id="id_grupo" name="id_grupo" required><?php foreach ($grupos as $grupo): ?><option value="<?= (int)$grupo['id_grupo'] ?>" <?= (int)($datos['id_grupo'] ?? 0) === (int)$grupo['id_grupo'] ? 'selected' : '' ?>><?= htmlspecialchars($grupo['grado'].'° '.$grupo['grupo'].' — '.$grupo['ciclo_escolar'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
            <label for="estado_semaforo">Estado del semáforo</label><select id="estado_semaforo" name="estado_semaforo"><option value="Verde" <?= ($datos['estado_semaforo'] ?? '') === 'Verde' ? 'selected' : '' ?>>Verde</option><option value="Amarillo" <?= ($datos['estado_semaforo'] ?? '') === 'Amarillo' ? 'selected' : '' ?>>Amarillo</option><option value="Rojo" <?= ($datos['estado_semaforo'] ?? '') === 'Rojo' ? 'selected' : '' ?>>Rojo</option></select>
            <label for="estado">Situación</label><select id="estado" name="estado"><option value="Activo" <?= ($datos['estado'] ?? '') === 'Activo' ? 'selected' : '' ?>>Activo</option><option value="Inactivo" <?= ($datos['estado'] ?? '') === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option><option value="Trasladado" <?= ($datos['estado'] ?? '') === 'Trasladado' ? 'selected' : '' ?>>Trasladado</option><option value="Egresado" <?= ($datos['estado'] ?? '') === 'Egresado' ? 'selected' : '' ?>>Egresado</option></select>
        <?php endif; ?>
        <button type="submit">Guardar cambios</button>
    </form>
    <a class="boton boton-secundario" href="listado.php?tipo=<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">Cancelar y regresar</a>
</main>
</body>
</html>
