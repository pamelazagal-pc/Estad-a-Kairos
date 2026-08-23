<?php
$datos = $resultado['datos'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar grupo | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css">
</head>
<body>
<main class="card">
    <h1>Registrar grupo</h1>
    <p>Crea un grupo escolar para organizar a los alumnos.</p>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="registrar_grupo">
        <label for="grado">Grado</label>
        <select id="grado" name="grado" required>
            <option value="">Selecciona un grado</option>
            <?php for ($grado = 1; $grado <= 6; $grado++): ?>
                <option value="<?= $grado ?>" <?= (string)($datos['grado'] ?? '') === (string)$grado ? 'selected' : '' ?>><?= $grado ?>° grado</option>
            <?php endfor; ?>
        </select>
        <label for="grupo">Grupo</label>
        <input id="grupo" name="grupo" maxlength="1" pattern="[A-Za-z]" value="<?= htmlspecialchars($datos['grupo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <label for="ciclo_escolar">Ciclo escolar</label>
        <input id="ciclo_escolar" name="ciclo_escolar" maxlength="20" placeholder="2026-2027" value="<?= htmlspecialchars($datos['ciclo_escolar'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <button type="submit">Registrar grupo</button>
    </form>
    <a class="boton boton-secundario" href="panel_administrador.php">Regresar al panel</a>
</main>
</body>
</html>
