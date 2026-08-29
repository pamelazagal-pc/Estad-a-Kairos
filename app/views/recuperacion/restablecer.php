<?php
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$token = $resultado['token'] ?? '';
$cuenta = $resultado['cuenta'] ?? null;
$tipo = $resultado['tipo_usuario'] ?? ($cuenta['tipo_usuario'] ?? '');
$loginUrls = ['administrador' => 'login_administrador.php', 'maestro' => 'maestro/login_maestro.php', 'tutor' => 'tutor/login_tutor.php'];
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nueva contraseña | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=recuperacion-1">
</head>
<body class="kairos-auth-body">
<main class="kairos-auth-card">
    <a class="kairos-auth-brand" href="<?= htmlspecialchars($loginUrls[$tipo] ?? 'login_administrador.php', ENT_QUOTES, 'UTF-8') ?>"><span class="kairos-auth-logo">K</span><span><strong>Kairos</strong><small>Gestión emocional</small></span></a>
    <div class="kairos-auth-heading"><p class="eyebrow-admin">Seguridad de la cuenta</p><h1>Nueva contraseña</h1><p>Define una contraseña nueva para volver a ingresar a Kairos.</p></div>
    <?php if ($errores !== []): ?><div class="alerta error" role="alert"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta exito" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if ($mensaje): ?>
        <a class="boton recuperacion-boton-enlace" href="<?= htmlspecialchars($loginUrls[$tipo] ?? 'login_administrador.php', ENT_QUOTES, 'UTF-8') ?>">Ir al inicio de sesión</a>
    <?php elseif ($cuenta): ?>
    <form method="post" class="formulario-admin kairos-auth-form">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Nueva contraseña</label>
        <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
        <label for="password_confirmacion">Confirmar contraseña</label>
        <input id="password_confirmacion" name="password_confirmacion" type="password" autocomplete="new-password" minlength="8" required>
        <p class="recuperacion-requisitos">Mínimo 8 caracteres, con al menos una letra y un número.</p>
        <button class="boton" type="submit">Guardar nueva contraseña</button>
    </form>
    <?php endif; ?>
</main>
</body>
</html>
