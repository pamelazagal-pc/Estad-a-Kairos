<?php
$datos = $datos ?? [];
$tutor = $datos['tutor'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$alumno = $datos['alumno'] ?? null;
$incidencias = $datos['incidencias'] ?? [];
$medallas = $datos['medallas'] ?? [];
$consejos = $datos['consejos'] ?? [];
$notificaciones = $datos['notificaciones'] ?? [];
$esc = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$estado = strtolower((string)($alumno['estado_semaforo'] ?? 'verde'));
$estadoClase = in_array($estado, ['verde', 'amarillo', 'rojo'], true) ? $estado : 'verde';
$estadoTexto = ucfirst($estadoClase);
$pendientes = count(array_filter($notificaciones, static fn($nota): bool => (int)($nota['notificado_al_tutor'] ?? 0) === 0));
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel tutor | Kairos</title>
    <link rel="stylesheet" href="../../../public/CSS/main.css?v=tutor-dashboard-1">
</head>
<body class="panel-body tutor-body">
<aside class="panel-sidebar tutor-sidebar">
    <a class="panel-marca" href="dashboard_tutor.php" aria-label="Volver al panel tutor"><span class="panel-logo">K</span><span class="panel-marca-text"><strong>Kairos</strong><small>Familias y bienestar</small></span></a>
    <nav class="panel-nav">
        <div class="panel-nav-label">Consulta</div>
        <a class="panel-nav-item panel-nav-activo" href="dashboard_tutor.php"><span class="tutor-nav-icon">⌂</span><span>Panel principal</span></a>
        <a class="panel-nav-item" href="#seguimiento"><span class="tutor-nav-icon">◉</span><span>Seguimiento emocional</span></a>
        <a class="panel-nav-item" href="#medallas"><span class="tutor-nav-icon">★</span><span>Logros y medallas</span></a>
        <a class="panel-nav-item" href="#consejos"><span class="tutor-nav-icon">▤</span><span>Consejos para el hogar</span></a>
    </nav>
    <div class="tutor-sidebar-note">Información escolar protegida<br><small>Consulta de solo lectura</small></div>
    <a class="panel-nav-item panel-nav-salida" href="logout_tutor.php"><span class="tutor-nav-icon">↪</span><span>Cerrar sesión</span></a>
</aside>
<div class="panel-contenedor tutor-contenedor">
    <header class="tutor-topbar">
        <div><p class="eyebrow-admin">Panel familiar</p><h1>Hola, <?= $esc($tutor['nombre'] ?? 'Tutor') ?></h1><p>Consulta el bienestar y los avances de tus alumnos vinculados.</p></div>
        <div class="tutor-user-chip"><span class="usuario-avatar"><?= $esc(mb_strtoupper(mb_substr($tutor['nombre'] ?? 'T', 0, 1))) ?></span><span><strong><?= $esc($tutor['cargo'] ?? 'Tutor') ?></strong><small><?= $esc($tutor['correo'] ?? '') ?></small></span></div>
    </header>
    <?php if (($datos['tutor'] ?? []) === []): ?><div class="alerta error">No fue posible validar la cuenta del tutor.</div><?php endif; ?>
    <?php if (count($alumnos) > 1): ?>
    <section class="tutor-selector panel-seccion">
        <div><p class="eyebrow-admin">Alumnos vinculados</p><h2>¿A quién deseas consultar?</h2></div>
        <div class="tutor-selector-grid">
            <?php foreach ($alumnos as $item): ?><a class="tutor-alumno-card <?= $alumno && (int)$alumno['id_alumno'] === (int)$item['id_alumno'] ? 'selected' : '' ?>" href="dashboard_tutor.php?id_alumno=<?= (int)$item['id_alumno'] ?>"><span class="tutor-alumno-avatar"><?= $esc(mb_strtoupper(mb_substr($item['nombre_completo'], 0, 1))) ?></span><span><strong><?= $esc($item['nombre_completo']) ?></strong><small><?= $esc($item['grupo']) ?> · <?= $esc($item['parentesco'] ?: 'Vinculado') ?></small></span><b>→</b></a><?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
    <?php if (!$alumno): ?>
        <section class="tutor-empty panel-seccion"><div class="tutor-empty-icon">◎</div><h2>No hay un alumno seleccionado</h2><p>La escuela todavía no ha vinculado un alumno a esta cuenta o debes seleccionar uno de la lista.</p></section>
    <?php else: ?>
    <section class="tutor-welcome panel-seccion"><div><p class="eyebrow-admin">Resumen de <?= $esc($alumno['nombre_completo']) ?></p><h2>Seguimiento escolar y emocional</h2><p>Esta información te ayuda a dar continuidad en casa al acompañamiento realizado por la escuela.</p></div><span class="tutor-readonly-badge">Solo lectura</span></section>
    <section class="tutor-kpi-grid" id="seguimiento">
        <article class="tutor-kpi-card"><span class="tutor-kpi-icon teal">◉</span><div><strong><?= $esc($alumno['grupo']) ?></strong><span>Grupo actual</span></div></article>
        <article class="tutor-kpi-card"><span class="tutor-kpi-icon <?= $estadoClase ?>">●</span><div><strong><?= $estadoTexto ?></strong><span>Semáforo emocional</span></div></article>
        <article class="tutor-kpi-card"><span class="tutor-kpi-icon purple">★</span><div><strong><?= (int)($alumno['puntos_acumulados'] ?? 0) ?></strong><span>Puntos de esfuerzo</span></div></article>
        <article class="tutor-kpi-card"><span class="tutor-kpi-icon amber">!</span><div><strong><?= $pendientes ?></strong><span>Avisos recientes</span></div></article>
    </section>
    <section class="tutor-main-grid">
        <article class="panel-seccion tutor-status-card"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Estado actual</p><h2>Semáforo emocional</h2></div><span class="tutor-status-dot <?= $estadoClase ?>"></span></div><div class="tutor-status-large <?= $estadoClase ?>"><strong><?= $estadoTexto ?></strong><span><?= $estadoClase === 'verde' ? 'Sin incidencias recientes que requieran atención especial.' : ($estadoClase === 'amarillo' ? 'Se recomienda acompañamiento y comunicación con el docente.' : 'Se recomienda revisar el seguimiento con el docente y orientación escolar.') ?></span></div><p class="tutor-disclaimer">El semáforo es una referencia pedagógica del registro escolar y no constituye un diagnóstico clínico.</p></article>
        <article class="panel-seccion tutor-notifications"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Comunicación escuela-familia</p><h2>Avisos recientes</h2></div></div><?php if ($notificaciones === []): ?><p class="tutor-muted">No hay avisos registrados todavía.</p><?php else: ?><div class="tutor-notification-list"><?php foreach (array_slice($notificaciones, 0, 5) as $nota): ?><div class="tutor-notification"><span class="tutor-notification-mark <?= (int)$nota['notificado_al_tutor'] === 0 ? 'pending' : '' ?>">●</span><div><strong><?= $esc($nota['tipo_nota']) ?></strong><p><?= $esc($nota['nota_descripcion']) ?></p><small><?= $esc($nota['docente']) ?> · <?= $esc($nota['fecha_hora']) ?></small></div></div><?php endforeach; ?></div><?php endif; ?></article>
    </section>
    <section class="panel-seccion tutor-table-section"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Registro escolar</p><h2>Seguimiento emocional reciente</h2></div><span class="tutor-table-caption">Últimos <?= count($incidencias) ?> registros</span></div><div class="tabla-scroll"><table class="tabla-admin tutor-table"><thead><tr><th>Fecha</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Docente</th><th>Descripción</th></tr></thead><tbody><?php if ($incidencias === []): ?><tr><td colspan="6" class="tutor-muted">No hay registros recientes para este alumno.</td></tr><?php else: foreach ($incidencias as $incidencia): ?><tr><td><?= $esc($incidencia['fecha_hora']) ?></td><td><span class="tutor-type-pill <?= strtolower($esc($incidencia['tipo_nota'])) ?>"><?= $esc($incidencia['tipo_nota']) ?></span></td><td><?= $esc($incidencia['emocion'] ?: '—') ?></td><td><?= $esc($incidencia['accion_contencion'] ?: '—') ?></td><td><?= $esc($incidencia['docente']) ?></td><td><?= $esc($incidencia['nota_descripcion']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
    <section class="tutor-main-grid" id="medallas"><article class="panel-seccion"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Reconocimiento positivo</p><h2>Logros y medallas</h2></div></div><?php if ($medallas === []): ?><p class="tutor-muted">Todavía no hay medallas asignadas.</p><?php else: ?><div class="tutor-medal-grid"><?php foreach ($medallas as $medalla): ?><div class="tutor-medal"><span><?= $esc($medalla['icono_url'] ?: '★') ?></span><div><strong><?= $esc($medalla['nombre_insignia']) ?></strong><p><?= $esc($medalla['descripcion']) ?></p><small>+<?= (int)$medalla['puntos_otorgados'] ?> puntos · <?= $esc($medalla['fecha_hora']) ?></small></div></div><?php endforeach; ?></div><?php endif; ?></article><article class="panel-seccion" id="consejos"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Acompañamiento en casa</p><h2>Consejos para el hogar</h2></div></div><?php if ($consejos === []): ?><p class="tutor-muted">No hay recomendaciones publicadas por la escuela.</p><?php else: ?><div class="tutor-advice-list"><?php foreach (array_slice($consejos, 0, 5) as $consejo): ?><details class="tutor-advice"><summary><span><?= $esc($consejo['categoria']) ?></span><?= $esc($consejo['titulo']) ?></summary><p><?= nl2br($esc($consejo['recomendacion'])) ?></p></details><?php endforeach; ?></div><?php endif; ?></article></section>
    <?php endif; ?>
    <footer class="tutor-footer">Kairos · Información educativa confidencial · Si tienes dudas sobre un registro, comunícate con el docente.</footer>
</div>
<script>
const tutorAlumno = <?= (int)($alumno['id_alumno'] ?? 0) ?>;
if (tutorAlumno > 0) setInterval(() => { fetch('dashboard_tutor.php?id_alumno=' + tutorAlumno, {headers: {'X-Requested-With': 'XMLHttpRequest'}}).catch(() => {}); }, 30000);
</script>
</body>
</html>
