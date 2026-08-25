<aside class="sidebar" aria-label="Navegación del maestro">
    <a class="sidebar-logo" href="dashboard_maestro.php" aria-label="Volver al tablero del maestro">
        <div class="logo-mark">K</div>
        <div class="logo-name">KAIROS <span class="logo-sub"><br>Gestión emocional</span></div>
    </a>
    <nav class="sidebar-nav">
        <div class="nav-label">Principal</div>
        <a class="nav-item" title="Tablero" href="dashboard_maestro.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 10 9-7 9 7v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg><span>Tablero</span></a>
        <a class="nav-item" title="Mi grupo" href="dashboard_maestro.php#alumnos"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75M21 21v-2a4 4 0 0 0-3-3.85"/></svg><span>Mi grupo</span></a>
        <div class="nav-label">Registros</div>
        <a class="nav-item" title="Bitácora emocional" href="bitacora_maestro.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M8 13h8M8 17h6"/></svg><span>Bitácora emocional</span></a>
        <div class="nav-label">Recursos</div>
        <a class="nav-item" title="Recursos pedagógicos" href="recursos.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5a2 2 0 0 1 2-2h5v17H6a2 2 0 0 1-2 2z"/><path d="M20 5a2 2 0 0 0-2-2h-5v17h5a2 2 0 0 0 2-2zM8 7h1M15 7h1M8 11h1M15 11h1"/></svg><span>Recursos pedagógicos</span></a>
        <a class="nav-item" title="Reportes" href="reportes.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19V5M4 19h16M7 15l3-4 3 2 5-6"/></svg><span>Reportes</span></a>
        <div class="nav-label">Sistema</div>
        <a class="nav-item" title="Configuración" href="dashboard_maestro.php#configuracion"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93 17.66 6.34M6.34 6.34 4.93 4.93M12 2v2M12 20v2M2 12h2M20 12h2M4.93 19.07l1.41-1.41M18.66 18.66l-1.41-1.41"/></svg><span>Configuración</span></a>
    </nav>
    <div class="sidebar-timer" data-maestro-timer aria-live="polite">
        <span class="sidebar-timer-label">Temporizador</span>
        <strong data-timer-display>00:00</strong>
        <small data-timer-status>Sin sesión activa</small>
    </div>
    <a class="nav-item btn-logout" title="Cerrar sesión" href="logout_maestro.php"><svg class="nav-icon icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/></svg><span>Cerrar sesión</span></a>
</aside>
<script src="../../JS/maestro_timer.js" defer></script>
