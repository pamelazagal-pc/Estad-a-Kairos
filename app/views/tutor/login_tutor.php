<?php
$errores = $resultado['errores'] ?? [];
$datos = $resultado['datos'] ?? [];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso de tutor | Kairos</title>
    <link rel="stylesheet" href="../../../public/CSS/main.css?v=tutor-login-1">
</head>
<body class="kairos-auth-body">
<main class="kairos-auth-card">
    <a class="kairos-auth-brand" href="../login_administrador.php"><span class="kairos-auth-logo">K</span><span><strong>Kairos</strong><small>Gestión emocional</small></span></a>
    <div class="kairos-auth-heading"><p class="eyebrow-admin">Familias y tutores</p><h1>Consulta el progreso</h1><p>Ingresa para consultar el seguimiento emocional y los logros de tus alumnos vinculados.</p></div>
    <?php if ($errores !== []): ?><div class="alerta error" role="alert"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" class="formulario-admin kairos-auth-form">
        <input type="hidden" name="accion" value="login">
        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" autocomplete="username" required value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="boton" type="submit">Ingresar al panel</button>
    </form>
    <p class="kairos-auth-note">Este acceso es exclusivamente de consulta. Los tutores no pueden modificar registros escolares.</p>
</main>
</body>
</html>
