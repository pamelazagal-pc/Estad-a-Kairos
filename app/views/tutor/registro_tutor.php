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
    <title>Registrar tutor | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_administrador.php'; ?>

<main class="card">
    <h1>Registrar tutor</h1>
    <p>Agrega una cuenta de padre, madre o tutor a la plataforma.</p>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="registrar_tutor">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" maxlength="100" value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <label for="cargo">Tipo de tutor</label>
        <select id="cargo" name="cargo" required>
            <option value="">Selecciona una opción</option>
            <option value="Padre" <?= ($datos['cargo'] ?? '') === 'Padre' ? 'selected' : '' ?>>Padre</option>
            <option value="Tutor" <?= ($datos['cargo'] ?? '') === 'Tutor' ? 'selected' : '' ?>>Tutor</option>
        </select>
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="150" value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        <label for="telefono">Teléfono</label>
        <input id="telefono" name="telefono" maxlength="20" value="<?= htmlspecialchars($datos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
        <label for="confirmar_password">Confirmar contraseña</label>
        <input id="confirmar_password" name="confirmar_password" type="password" minlength="8" autocomplete="new-password" required>
        <button type="submit">Registrar tutor</button>
    </form>
</main>
</div>
</body>
</html>
