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
    <?php require_once __DIR__ . '/../sidebar_administrador.php'; ?>

    <main class="panel-contenedor">
        <?php require_once __DIR__ . '/../topbar_administrador.php'; ?>
        <header class="panel-encabezado">
            <div>
                <p class="panel-etiqueta">PLATAFORMA KAIROS</p>
                <h1>Panel administrativo</h1>
                <p class="panel-bienvenida">Bienvenido, <strong><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></strong>.</p>
            </div>
            <div class="panel-usuario"><span class="usuario-avatar" aria-hidden="true">♙</span><?= htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8') ?></div>
        </header>

        <section class="panel-seccion">
            <h2>Gestión de usuarios</h2>
            <p class="panel-descripcion">Selecciona el registro que deseas realizar.</p>
            <div class="panel-grid">
                <a class="panel-tarjeta" href="registro_administrador.php"><span class="panel-icono" aria-hidden="true">♙+</span><strong>Registrar administrador</strong><span>Crear nuevas cuentas administrativas del sistema.</span><b>→</b></a>
                <a class="panel-tarjeta" href="maestro/registro_maestro.php"><span class="panel-icono" aria-hidden="true">♟+</span><strong>Registrar maestro</strong><span>Añadir nuevos maestros a la plataforma.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_alumno.php"><span class="panel-icono" aria-hidden="true">♙+</span><strong>Registrar alumno</strong><span>Inscribir nuevos alumnos en el sistema.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_tutor.php"><span class="panel-icono" aria-hidden="true">♧+</span><strong>Registrar tutor</strong><span>Crear una cuenta para un padre o tutor.</span><b>→</b></a>
                <a class="panel-tarjeta" href="registro_grupo.php"><span class="panel-icono" aria-hidden="true">▦+</span><strong>Registrar grupo</strong><span>Crear un grupo y asignar su ciclo escolar.</span><b>→</b></a>
                <a class="panel-tarjeta" href="videos.php"><span class="panel-icono" aria-hidden="true">▶</span><strong>Administrar videos</strong><span>Gestionar enlaces de YouTube para pausas activas.</span><b>→</b></a>
                <a class="panel-tarjeta" href="lecturas.php"><span class="panel-icono" aria-hidden="true">▤</span><strong>Lecturas y cuentos</strong><span>Crear y publicar lecturas cortas por categoría temática.</span><b>→</b></a>
                <a class="panel-tarjeta" href="recompensas.php"><span class="panel-icono" aria-hidden="true">★</span><strong>Medallas y puntos</strong><span>Administrar reconocimientos para la conducta positiva.</span><b>→</b></a>
                <a class="panel-tarjeta" href="respaldos.php"><span class="panel-icono" aria-hidden="true">▣</span><strong>Respaldos</strong><span>Generar, descargar y restaurar copias de seguridad de Kairos.</span><b>→</b></a>
                <a class="panel-tarjeta" href="reporte_administrador.php"><span class="panel-icono" aria-hidden="true">▥</span><strong>Reportes generales</strong><span>Analizar emociones, incidencias, grupos y seguimiento institucional.</span><b>→</b></a>
            </div>
        </section>

        <section class="panel-seccion panel-resumen">
            <h2>Resumen del sistema</h2>
            <p class="panel-descripcion">Registros activos en Kairos.</p>
            <?php if ($errorResumen !== null): ?><div class="error"><?= htmlspecialchars($errorResumen, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <div class="estadisticas-grid">
                <article class="estadistica-tarjeta"><span class="estadistica-icono" aria-hidden="true">♙</span><div><strong><?= $resumen['administradores'] ?></strong><span>Administradores</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono" aria-hidden="true">♟</span><div><strong><?= $resumen['docentes'] ?></strong><span>Maestros</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono" aria-hidden="true">♙</span><div><strong><?= $resumen['alumnos'] ?></strong><span>Alumnos</span></div></article>
                <article class="estadistica-tarjeta"><span class="estadistica-icono" aria-hidden="true">▦</span><div><strong><?= $resumen['grupos'] ?></strong><span>Grupos</span></div></article>
            </div>
        </section>
        <?php if ($esPrincipal): ?><p class="panel-nota">Has iniciado sesión como administrador principal.</p><?php endif; ?>
    </main>
</body>
</html>
