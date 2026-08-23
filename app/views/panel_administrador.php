<?php
$administrador = $datosAdministrador ?? [];
$nombre = $administrador['nombre'] ?? 'Administrador';
$esPrincipal = !empty($administrador['es_principal']);
$resumen = $resumen ?? [
    'administradores' => 0,
    'docentes' => 0,
    'alumnos' => 0,
    'grupos' => 0,
];
$errorResumen = $errorResumen ?? null;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel administrativo | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css?v=panel-2">
</head>
<body class="panel-body">
    <aside class="panel-sidebar">
        <div class="panel-marca">
            <span class="panel-logo">K</span>
            <strong>Kairos</strong>
        </div>
        <nav class="panel-nav" aria-label="Navegación principal">
            <a class="panel-nav-item panel-nav-activo" href="panel_administrador.php"><span>⌂</span>Panel</a>
            <a class="panel-nav-item" href="listado.php?tipo=administradores"><span>A</span>Administradores</a>
            <a class="panel-nav-item" href="listado.php?tipo=maestros"><span>M</span>Maestros</a>
            <a class="panel-nav-item" href="listado.php?tipo=alumnos"><span>E</span>Alumnos</a>
            <a class="panel-nav-item" href="listado.php?tipo=grupos"><span>G</span>Grupos</a>
            <a class="panel-nav-item" href="listado.php?tipo=tutores"><span>T</span>Tutores</a>
        </nav>
        <a class="panel-nav-item panel-nav-salida" href="logout.php"><span>↪</span>Cerrar sesión</a>
    </aside>

    <main class="panel-contenedor">
        <header class="panel-encabezado">
            <div>
                <p class="panel-etiqueta">PLATAFORMA KAIROS</p>
                <h1>Panel administrativo</h1>
                <p class="panel-bienvenida">Bienvenido, <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            </div>
            <div class="panel-usuario"><span class="usuario-avatar">A</span><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></div>
        </header>

        <section class="panel-seccion">
            <h2>Gestión de usuarios</h2>
            <p class="panel-descripcion">Selecciona el registro que deseas realizar.</p>
            <div class="panel-grid">
                <a class="panel-tarjeta" href="registro_administrador.php"><span class="panel-icono">A+</span><strong>Registrar administrador</strong><span>Crear nuevas cuentas administrativas del sistema.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_maestro.php"><span class="panel-icono">M+</span><strong>Registrar maestro</strong><span>Añadir nuevos maestros a la plataforma.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_alumno.php"><span class="panel-icono">E+</span><strong>Registrar alumno</strong><span>Inscribir nuevos alumnos en el sistema.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_tutor.php"><span class="panel-icono">T+</span><strong>Registrar tutor</strong><span>Crear una cuenta para un padre o tutor.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_grupo.php"><span class="panel-icono">G+</span><strong>Registrar grupo</strong><span>Crear un grupo y asignar su ciclo escolar.</span><b>→</b></a>
            </div>
        </section>

        <section class="panel-seccion panel-resumen">
            <h2>Resumen del sistema</h2>
            <p class="panel-descripcion">Registros activos en Kairos.</p>
            <?php if ($errorResumen !== null): ?><div class="error"><?= htmlspecialchars($errorResumen, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div class="estadisticas-grid">
                <article class="estadistica-tarjeta"><span class="estadistica-icono">A</span><div><strong><?= $resumen['administradores'] ?></strong><span>Administradores</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono">M</span><div><strong><?= $resumen['docentes'] ?></strong><span>Maestros</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono">E</span><div><strong><?= $resumen['alumnos'] ?></strong><span>Alumnos</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono">G</span><div><strong><?= $resumen['grupos'] ?></strong><span>Grupos</span></div></article>
            </div>
        </section>
        <?php if ($esPrincipal): ?><p class="panel-nota">Has iniciado sesión como administrador principal.</p><?php endif; ?>
    </main>
</body>
</html>
