(() => {
    const timer = document.querySelector('[data-maestro-timer]');
    if (!timer) return;
    const params = new URLSearchParams(window.location.search);
    const grupoActual = document.body.dataset.grupo || params.get('id_grupo') || localStorage.getItem('kairos_active_group') || 'sin-grupo';
    if (grupoActual !== 'sin-grupo') localStorage.setItem('kairos_active_group', grupoActual);
    const key = `kairos_timer_${grupoActual}`;
    const display = timer.querySelector('[data-timer-display]');
    const status = timer.querySelector('[data-timer-status]');

    const format = (seconds) => {
        const value = Math.max(0, Number(seconds) || 0);
        const minutes = String(Math.floor(value / 60)).padStart(2, '0');
        const rest = String(value % 60).padStart(2, '0');
        return `${minutes}:${rest}`;
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
            const remaining = state.running && state.endAt ? Math.max(0, Math.ceil((state.endAt - Date.now()) / 1000)) : Math.max(0, Number(state.elapsed) || 0);
            if (remaining <= 0) {
                localStorage.removeItem(key);
                display.textContent = '00:00';
                status.textContent = 'Sesión finalizada';
                timer.classList.remove('is-running');
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
            return false;
        }
    };

    render();
    window.setInterval(render, 1000);
    window.addEventListener('storage', render);
})();
