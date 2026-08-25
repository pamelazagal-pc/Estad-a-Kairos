<header class="panel-topbar">
    <div>
        <p class="panel-topbar-kicker">PLATAFORMA KAIROS</p>
        <strong>Panel administrativo</strong>
    </div>
    <div class="panel-topbar-user">
        <span class="panel-topbar-avatar" aria-hidden="true">A</span>
        <span><?= htmlspecialchars($nombreAdministrador ?? $nombre ?? $_SESSION['administrador_nombre'] ?? 'Administrador', ENT_QUOTES, 'UTF-8') ?></span>
        <a class="btn-logout" href="logout.php">Cerrar sesión</a>
    </div>
</header>
