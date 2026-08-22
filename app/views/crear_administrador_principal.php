<?php
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrador principal | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css">
</head>
<body>
<main class="card">
    <h1>Crear administrador principal</h1>
    <p>Esta pantalla solo funciona mientras no exista un administrador principal activo.</p>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="crear_principal">
        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" maxlength="100" required>
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="150" required>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" minlength="8" required>
        <label for="confirmar_password">Confirmar contraseña</label>
        <input id="confirmar_password" name="confirmar_password" type="password" minlength="8" required>
        <button type="submit">Crear administrador principal</button>
    </form>
</main>
</body>
</html>
