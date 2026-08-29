<?php
$datos = $datos ?? [];
$tutor = $datos['tutor'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$alumno = $datos['alumno'] ?? null;
$incidencias = $datos['incidencias'] ?? [];
$medallas = $datos['medallas'] ?? [];
$consejos = $datos['consejos'] ?? [];
$notificaciones = $datos['notificaciones'] ?? [];
$sesiones = $datos['sesiones'] ?? [];
$esc = static fn($valor): string => htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
$estado = strtolower((string)($alumno['estado_semaforo'] ?? 'verde'));
$estadoClase = in_array($estado, ['verde', 'amarillo', 'rojo'], true) ? $estado : 'verde';
$estadoTexto = ucfirst($estadoClase);
$avisos = count($notificaciones);
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
        <a class="panel-nav-item panel-nav-activo" href="dashboard_tutor.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg><span>Panel principal</span></a>
        <a class="panel-nav-item" href="#seguimiento"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8"/><circle cx="12" cy="12" r="2"/></svg><span>Seguimiento emocional</span></a>
        <a class="panel-nav-item" href="#avisos"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg><span>Avisos de la escuela</span></a>
        <a class="panel-nav-item" href="#medallas"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 21h8M12 17v4M7 4h10v4a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4a3 3 0 0 0 3 5M17 6h3a3 3 0 0 1-3 5"/></svg><span>Logros y medallas</span></a>
        <a class="panel-nav-item" href="#sesiones"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg><span>Sesiones del aula</span></a>
        <a class="panel-nav-item" href="#consejos"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v16H6.5A2.5 2.5 0 0 0 4 21.5v-16Z"/><path d="M4 5.5v16M8 7h8M8 11h8"/></svg><span>Consejos para el hogar</span></a>
    </nav>
    <div class="tutor-sidebar-note">Información escolar protegida<br><small>Consulta de solo lectura</small></div>
    <a class="panel-nav-item panel-nav-salida" href="logout_tutor.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-6"/></svg><span>Cerrar sesión</span></a>
</aside>
<div class="panel-contenedor tutor-contenedor">
    <header class="topbar panel-topbar">
        <div class="topbar-title"><p class="panel-topbar-kicker">PLATAFORMA KAIROS</p><strong>Panel del tutor</strong></div>
        <div class="topbar-right panel-topbar-user"><span class="panel-topbar-avatar" aria-hidden="true"><?= $esc(mb_strtoupper(mb_substr($tutor['nombre'] ?? 'T', 0, 1))) ?></span><span class="topbar-user">Bienvenido/a, <?= $esc($tutor['nombre'] ?? 'Tutor') ?></span><a class="btn-logout" href="logout_tutor.php">Cerrar sesión</a></div>
    </header>
    <header class="panel-encabezado tutor-page-heading">
        <div><p class="panel-etiqueta">PANEL FAMILIAR</p><h1>Hola, <?= $esc($tutor['nombre'] ?? 'Tutor') ?></h1><p class="panel-bienvenida">Consulta el bienestar y los avances de tus alumnos vinculados.</p></div>
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
        <article class="tutor-kpi-card"><span class="tutor-kpi-icon amber">!</span><div><strong><?= $avisos ?></strong><span>Avisos comunicados</span></div></article>
    </section>
    <section class="tutor-main-grid">
        <article class="panel-seccion tutor-status-card"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Estado actual</p><h2>Semáforo emocional</h2></div><span class="tutor-status-dot <?= $estadoClase ?>"></span></div><div class="tutor-status-large <?= $estadoClase ?>"><strong><?= $estadoTexto ?></strong><span><?= $estadoClase === 'verde' ? 'Sin incidencias recientes que requieran atención especial.' : ($estadoClase === 'amarillo' ? 'Se recomienda acompañamiento y comunicación con el docente.' : 'Se recomienda revisar el seguimiento con el docente y orientación escolar.') ?></span></div><p class="tutor-disclaimer">El semáforo es una referencia pedagógica del registro escolar y no constituye un diagnóstico clínico.</p><?php if (!empty($alumno['ultima_actualizacion'])): ?><p class="tutor-last-update">Último registro emocional: <strong><?= $esc($alumno['ultima_actualizacion']) ?></strong><?php if (!empty($alumno['ultima_emocion'])): ?> · <?= $esc($alumno['ultima_emocion']) ?><?php endif; ?></p><?php endif; ?></article>
        <article class="panel-seccion tutor-notifications" id="avisos"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Comunicación escuela-familia</p><h2>Avisos recientes</h2></div><span class="tutor-notification-count"><?= $avisos ?></span></div><?php if ($notificaciones === []): ?><p class="tutor-muted">No hay avisos comunicados por la escuela.</p><?php else: ?><div class="tutor-notification-list"><?php foreach (array_slice($notificaciones, 0, 5) as $nota): ?><div class="tutor-notification"><span class="tutor-notification-mark">●</span><div class="tutor-notification-content"><div class="tutor-notification-title"><strong><?= $esc($nota['tipo_nota']) ?></strong><span class="tutor-notification-status">Comunicado</span></div><p><?= $esc($nota['nota_descripcion']) ?></p><small><?= $esc($nota['docente']) ?> · <?= $esc($nota['fecha_hora']) ?></small></div></div><?php endforeach; ?></div><?php endif; ?></article>
    </section>
    <section class="panel-seccion tutor-table-section"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Registro escolar</p><h2>Seguimiento emocional reciente</h2></div><span class="tutor-table-caption">Últimos <?= count($incidencias) ?> registros</span></div><div class="tabla-scroll"><table class="tabla-admin tutor-table"><thead><tr><th>Fecha</th><th>Tipo</th><th>Emoción</th><th>Contención</th><th>Docente</th><th>Descripción</th></tr></thead><tbody><?php if ($incidencias === []): ?><tr><td colspan="6" class="tutor-muted">No hay registros recientes para este alumno.</td></tr><?php else: foreach ($incidencias as $incidencia): ?><tr><td><?= $esc($incidencia['fecha_hora']) ?></td><td><span class="tutor-type-pill <?= strtolower($esc($incidencia['tipo_nota'])) ?>"><?= $esc($incidencia['tipo_nota']) ?></span></td><td><?= $esc($incidencia['emocion'] ?: '—') ?></td><td><?= $esc($incidencia['accion_contencion'] ?: '—') ?></td><td><?= $esc($incidencia['docente']) ?></td><td><?= $esc($incidencia['nota_descripcion']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
    <section class="panel-seccion tutor-table-section tutor-sessions-section" id="sesiones"><div class="tutor-section-heading"><div><p class="eyebrow-admin">Actividad del aula</p><h2>Sesiones del grupo</h2></div><span class="tutor-table-caption">Últimas <?= count($sesiones) ?> sesiones</span></div><p class="tutor-session-note">Las sesiones se muestran según el grupo actual del alumno. El detalle emocional individual aparece en el registro de seguimiento.</p><div class="tabla-scroll"><table class="tabla-admin tutor-table tutor-session-table"><thead><tr><th>Fecha</th><th>Duración</th><th>Nivel</th><th>Estado</th><th>Docente</th></tr></thead><tbody><?php if ($sesiones === []): ?><tr><td colspan="5" class="tutor-muted">No hay sesiones registradas para el grupo de este alumno.</td></tr><?php else: foreach ($sesiones as $sesion): ?><tr><td><?= $esc($sesion['fecha_inicio'] ?: $sesion['fecha']) ?></td><td><?= (int)$sesion['duracion_clase_min'] ?> min</td><td><?= $esc($sesion['nivel_irritabilidad']) ?></td><td><span class="tutor-session-state"><?= $esc($sesion['estado']) ?></span></td><td><?= $esc($sesion['docente']) ?></td></tr><?php endforeach; endif; ?></tbody></table></div></section>
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
