<?php
$datos = $resultado['datos'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
$nombreAdministrador = $_SESSION['administrador_nombre'] ?? 'Administrador';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar maestro | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css?v=registro-maestro-2">
</head>
<body class="panel-body">
<?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_administrador.php'; ?>

<main class="card registro-cuenta-card">
    <p class="eyebrow-admin">Administración de usuarios</p>
    <h1>Registrar maestro</h1>
    <p class="descripcion">Crea una cuenta de maestro para acceder a la plataforma Kairos.</p>

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

    <form method="post" class="formulario-admin">
        <input type="hidden" name="accion" value="registrar">

        <label for="nombre">Nombre completo</label>
        <input id="nombre" name="nombre" type="text" maxlength="100" autocomplete="name" value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="correo">Correo electrónico</label>
        <input id="correo" name="correo" type="email" maxlength="150" autocomplete="email" value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>

        <label for="telefono">Teléfono <span>(opcional)</span></label>
        <input id="telefono" name="telefono" type="tel" maxlength="20" autocomplete="tel" value="<?= htmlspecialchars($datos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

        <label for="password">Contraseña</label>
        <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
        <small class="ayuda">Debe contener al menos 8 caracteres.</small>

        <label for="confirmar_password">Confirmar contraseña</label>
        <input id="confirmar_password" name="confirmar_password" type="password" minlength="8" autocomplete="new-password" required>

        <div class="filtro-acciones">
            <button class="boton" type="submit">Registrar maestro</button>
            <a class="boton-secundario" href="../panel_administrador.php">Volver al panel</a>
        </div>
    </form>
</main>
</div>
</body>
</html>
