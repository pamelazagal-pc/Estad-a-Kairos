(() => {
    const timer = document.querySelector('[data-maestro-timer]');
    if (!timer) return;

    const params = new URLSearchParams(window.location.search);
    const grupoEnVista = Number(document.body.dataset.grupo || 0);
    const grupoEnUrl = Number(params.get('id_grupo') || 0);
    const grupoGuardado = Number(localStorage.getItem('kairos_active_group') || 0);
    const grupoActual = grupoEnVista > 0 ? String(grupoEnVista) : (grupoEnUrl > 0 ? String(grupoEnUrl) : (grupoGuardado > 0 ? String(grupoGuardado) : 'sin-grupo'));
    if (grupoActual !== 'sin-grupo') {
        localStorage.setItem('kairos_active_group', grupoActual);
        document.querySelectorAll('a[href^="dashboard_maestro.php"]').forEach((enlace) => {
            const destino = new URL(enlace.getAttribute('href'), window.location.href);
            destino.searchParams.set('id_grupo', grupoActual);
            enlace.setAttribute('href', `${destino.pathname.split('/').pop()}${destino.search}${destino.hash}`);
        });
    }

    const key = `kairos_timer_${grupoActual}`;
    const display = timer.querySelector('[data-timer-display]');
    const status = timer.querySelector('[data-timer-status]');
    const endpoint = 'sesion_temporizador.php';
    let finalizando = false;

    const format = (seconds) => {
        const value = Math.max(0, Number(seconds) || 0);
        const minutes = String(Math.floor(value / 60)).padStart(2, '0');
        const rest = String(value % 60).padStart(2, '0');
        return `${minutes}:${rest}`;
    };

    const obtenerAudio = () => {
        let audio = document.getElementById('maestro-finish-audio');
        if (!audio) {
            audio = document.createElement('audio');
            audio.id = 'maestro-finish-audio';
            audio.preload = 'auto';
            audio.src = 'audio_pausa.php';
            audio.setAttribute('aria-hidden', 'true');
            audio.className = 'maestro-finish-audio';
            document.body.appendChild(audio);
        }
        return audio;
    };

    const reproducirAudioFin = () => {
        const audio = obtenerAudio();
        audio.currentTime = 0;
        audio.muted = false;
        const reproduccion = audio.play();
        if (reproduccion && typeof reproduccion.catch === 'function') {
            reproduccion.catch(() => {
                status.textContent = 'Sesión finalizada; presiona reproducir para escuchar el aviso';
            });
        }
    };

    const prepararAudio = () => {
        const audio = obtenerAudio();
        audio.muted = true;
        audio.currentTime = 0;
        const desbloqueo = audio.play();
        if (desbloqueo && typeof desbloqueo.then === 'function') {
            desbloqueo.then(() => {
                audio.pause();
                audio.currentTime = 0;
                audio.muted = false;
            }).catch(() => {
                audio.muted = false;
            });
        }
    };

    const finalizarSesionServidor = async (sessionId) => {
        if (!sessionId || finalizando) return;
        finalizando = true;
        try {
            const datos = new FormData();
            datos.append('accion', 'finalizar_sesion');
            datos.append('id_sesion', sessionId);
            await fetch(endpoint, { method: 'POST', body: datos, credentials: 'same-origin' });
        } catch (error) {
            // La sesión se considera finalizada localmente aunque la petición falle.
        }
    };

    const render = () => {
        const raw = localStorage.getItem(key);
        if (!raw) {
            display.textContent = '00:00';
            status.textContent = 'Sin sesión activa';
            timer.classList.remove('is-running');
            return false;
        }

        try {
            const state = JSON.parse(raw);
            const remaining = state.running && state.endAt
                ? Math.max(0, Math.ceil((state.endAt - Date.now()) / 1000))
                : Math.max(0, Number(state.elapsed) || 0);

            if (remaining <= 0) {
                if (state.running) {
                    status.textContent = 'Sesión finalizada';
                    timer.classList.remove('is-running');
                    localStorage.removeItem(key);
                    reproducirAudioFin();
                    finalizarSesionServidor(state.activeSessionId);
                } else {
                    localStorage.removeItem(key);
                    display.textContent = '00:00';
                    status.textContent = 'Sin sesión activa';
                    timer.classList.remove('is-running');
                }
                return false;
            }

            display.textContent = format(remaining);
            status.textContent = state.running ? 'Temporizador activo' : 'Temporizador pausado';
            timer.classList.toggle('is-running', Boolean(state.running));
            return true;
        } catch (error) {
            localStorage.removeItem(key);
            display.textContent = '00:00';
            status.textContent = 'Sin sesión activa';
            timer.classList.remove('is-running');
            return false;
        }
    };

    const activarAudioConInteraccion = () => {
        prepararAudio();
        document.removeEventListener('pointerdown', activarAudioConInteraccion);
        document.removeEventListener('keydown', activarAudioConInteraccion);
    };
    document.addEventListener('pointerdown', activarAudioConInteraccion, { once: true });
    document.addEventListener('keydown', activarAudioConInteraccion, { once: true });

    render();
    window.setInterval(render, 1000);
    window.addEventListener('storage', render);
})();

// El estado se conserva en localStorage y se calcula con endAt, por lo que
// cambiar de página no reinicia la sesión ni altera el tiempo restante.
