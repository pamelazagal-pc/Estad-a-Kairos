<header class="topbar panel-topbar">
    <div class="topbar-title">
        <p class="panel-topbar-kicker">PLATAFORMA KAIROS</p>
        <strong>Panel del maestro</strong>
    </div>
    <div class="topbar-right panel-topbar-user">
        <span class="panel-topbar-avatar" aria-hidden="true">M</span>
        <span class="topbar-user">Bienvenido/a, <?= htmlspecialchars($maestro['nombre'] ?? $_SESSION['maestro_nombre'] ?? 'Maestro', ENT_QUOTES, 'UTF-8') ?></span>
        <a class="btn-logout" href="logout_maestro.php">Cerrar sesión</a>
    </div>
</header>
