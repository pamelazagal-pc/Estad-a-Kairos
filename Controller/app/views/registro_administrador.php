<?php
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$nombreAdministrador = $_SESSION['administrador_nombre'] ?? 'Administrador';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar administrador | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css">
</head>
<body>
<main class="card">
    <p class="top">Sesión activa: <?= htmlspecialchars($nombreAdministrador, ENT_QUOTES, 'UTF-8') ?></p>
    <h1>Registrar administrador</h1>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="registrar_secundario">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" maxlength="100" required>
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="150" required>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" minlength="8" required>
        <label for="confirmar_password">Confirmar contraseña</label>
        <input id="confirmar_password" name="confirmar_password" type="password" minlength="8" required>
        <button type="submit">Registrar administrador</button>
    </form>
</main>
</body>
</html>
