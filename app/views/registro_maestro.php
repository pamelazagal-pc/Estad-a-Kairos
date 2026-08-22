<?php
$datos = $resultado['datos'] ?? [];
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de maestros | Kairos</title>
    <link rel="stylesheet" href="../CSS/main.css">
</head>
<body>
    <main class="tarjeta">
        <h1>Registro de maestro</h1>
        <p class="descripcion">Crea una cuenta docente para acceder a la plataforma Kairos.</p>

        <?php if ($errores !== []): ?>
            <div class="alerta alerta-error" role="alert">
                <ul>
                    <?php foreach ($errores as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($mensaje !== null): ?>
            <div class="alerta alerta-exito" role="status">
                <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php
$scriptActual = $_SERVER['SCRIPT_NAME'] ?? '';
$actionFormulario = strpos($scriptActual, '/public/') !== false
    ? basename($scriptActual)
    : '../../Public/accesos/registro_maestro.php';
?>
        <form method="POST" action="<?= htmlspecialchars($actionFormulario, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="accion" value="registrar">

            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    maxlength="100"
                    value="<?= htmlspecialchars($datos['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="correo">Correo electrónico</label>
                <input
                    type="email"
                    id="correo"
                    name="correo"
                    maxlength="150"
                    value="<?= htmlspecialchars($datos['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </div>

            <div class="campo">
                <label for="telefono">Teléfono <span>(opcional)</span></label>
                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    maxlength="20"
                    value="<?= htmlspecialchars($datos['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                >
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" minlength="8" required>
                <small class="ayuda">Debe contener al menos 8 caracteres.</small>
            </div>

            <div class="campo">
                <label for="confirmar_password">Confirmar contraseña</label>
                <input type="password" id="confirmar_password" name="confirmar_password" minlength="8" required>
            </div>

            <button type="submit">Registrar maestro</button>
        </form>
    </main>
</body>
</html>
