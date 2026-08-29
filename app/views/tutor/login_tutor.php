<?php
$errores = $resultado['errores'] ?? [];
$datos = $resultado['datos'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso tutor | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css?v=tutor-login-2">
</head>
<body>
<main class="card">
    <h1>Acceso tutor</h1>
    <p>Ingresa para consultar el seguimiento emocional y los avances de tus alumnos vinculados.</p>

    <?php if ($errores !== []): ?>
        <div class="error" role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($mensaje): ?>
        <div class="ok" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="accion" value="login">

        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" autocomplete="username" required value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>

        <button type="submit">Iniciar sesión</button>
    </form>

    <p class="recuperacion-link"><a href="../recuperar.php?tipo=tutor">¿Olvidaste tu contraseña?</a></p>
    <p class="nota-acceso">Este acceso es exclusivamente de consulta. Los tutores no pueden modificar registros escolares.</p>
</main>
</body>
</html>
