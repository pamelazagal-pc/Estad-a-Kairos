<?php
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$tipoUsuario = $resultado['tipo_usuario'] ?? '';
$correo = $resultado['correo'] ?? '';
$tipos = ['administrador' => 'Administrador', 'maestro' => 'Maestro', 'tutor' => 'Tutor'];
$loginUrls = ['administrador' => 'login_administrador.php', 'maestro' => 'maestro/login_maestro.php', 'tutor' => 'tutor/login_tutor.php'];
$tipoValido = isset($tipos[$tipoUsuario]);
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar contraseña | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css?v=recuperacion-1">
</head>
<body class="kairos-auth-body">
<main class="kairos-auth-card">
    <a class="kairos-auth-brand" href="<?= $tipoValido ? htmlspecialchars($loginUrls[$tipoUsuario], ENT_QUOTES, 'UTF-8') : 'login_administrador.php' ?>"><span class="kairos-auth-logo">K</span><span><strong>Kairos</strong><small>Gestión emocional</small></span></a>
    <div class="kairos-auth-heading"><p class="eyebrow-admin">Seguridad de la cuenta</p><h1>Recuperar contraseña</h1><p>Te enviaremos un enlace temporal al correo asociado a tu cuenta.</p></div>
    <?php if ($errores !== []): ?><div class="alerta error" role="alert"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta exito" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <div class="recuperacion-tipos" aria-label="Tipo de cuenta">
        <?php foreach ($tipos as $valor => $etiqueta): ?><a class="recuperacion-tipo <?= $tipoUsuario === $valor ? 'activo' : '' ?>" href="recuperar.php?tipo=<?= urlencode($valor) ?>"><?= htmlspecialchars($etiqueta, ENT_QUOTES, 'UTF-8') ?></a><?php endforeach; ?>
    </div>
    <?php if ($tipoValido): ?>
    <form method="post" class="formulario-admin kairos-auth-form">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipoUsuario, ENT_QUOTES, 'UTF-8') ?>">
        <label for="correo">Correo electrónico de <?= htmlspecialchars($tipos[$tipoUsuario], ENT_QUOTES, 'UTF-8') ?></label>
        <input id="correo" name="correo" type="email" autocomplete="email" required value="<?= htmlspecialchars($correo, ENT_QUOTES, 'UTF-8') ?>" placeholder="correo@ejemplo.com">
        <button class="boton" type="submit">Enviar enlace</button>
    </form>
    <?php else: ?><p class="recuperacion-ayuda">Selecciona el tipo de cuenta que deseas recuperar.</p><?php endif; ?>
    <p class="kairos-auth-note">Por seguridad, la respuesta será la misma aunque el correo no esté registrado.</p>
</main>
</body>
</html>
