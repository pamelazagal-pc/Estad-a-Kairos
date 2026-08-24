<?php
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso maestro | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body>
<main class="card">
    <h1>Acceso maestro</h1>
    <?php if ($errores !== []): ?><div class="error"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="ok"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <form method="post">
        <input type="hidden" name="accion" value="login">
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" required>
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" required>
        <button type="submit">Iniciar sesión</button>
    </form>
</main>
</body>
</html>
