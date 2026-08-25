<?php
    $errores = $resultado['errores'] ?? [];
    $mensaje = $resultado['mensaje'] ?? null;
    $datos = $datos ?? ($resultado['datos'] ?? []);
    $maestro = $datos['maestro'] ?? [];
    $grupos = $datos['grupos'] ?? [];
    $alumnosDashboard = $datos['alumnos'] ?? [];
    $incidenciasDashboard = $datos['incidencias'] ?? [];
    $kpis = $datos['kpis'] ?? ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0];
    $grupoSeleccionado = (int)($datos['grupo_seleccionado'] ?? 0);
    $grupoActual = $grupoSeleccionado > 0 ? $grupoSeleccionado : (count($grupos) === 1 ? (int)$grupos[0]['id_grupo'] : 0);
    $mostrarPanel = $grupoActual > 0;
    $grupoActivo = null;
    foreach ($grupos as $grupo) { if ((int)$grupo['id_grupo'] === $grupoSeleccionado) { $grupoActivo = $grupo; break; } }
    $nombreGrupo = $grupoActivo ? ($grupoActivo['nombre_grupo'] . ' — ' . $grupoActivo['ciclo_escolar']) : (count($grupos) === 1 ? ($grupos[0]['nombre_grupo'] . ' — ' . $grupos[0]['ciclo_escolar']) : (count($grupos) > 1 ? count($grupos) . ' grupos asignados' : 'Sin grupos asignados'));
    $alumnosJson = json_encode(array_map(static function (array $alumno): array {
        $estado = strtolower((string)($alumno['estado_semaforo'] ?? 'Verde'));
        return [
            'id' => (int)$alumno['id_alumno'],
            'nombre' => $alumno['nombre_completo'],
            'estado' => $estado === 'amarillo' ? 'yellow' : ($estado === 'rojo' ? 'red' : 'green'),
            'grupo' => $alumno['grupo'],
            'reg' => $alumno['ultima_emocion'] ?: 'Sin registro',
            'accion' => $alumno['ultima_accion'] ?: '',
            'hora' => $alumno['ultima_actualizacion'] ?: 'Sin registro'
        ];
    }, $alumnosDashboard), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Emotion Monitor – maestro | UPEMOR</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="../../CSS/maestro_dashboard.css" />
</head>
<body data-grupo="<?= $grupoActual ?>">

<?php require_once __DIR__ . '/../sidebar_maestro.php'; ?>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main" id="alumnos">

  <?php require_once __DIR__ . '/../topbar_maestro.php'; ?>

  <!-- CONTENT -->
  <div class="content">

    <!-- ── HEADING ── -->
    <div>
      <span class="section-heading">Panel de Control de Aula — <?= htmlspecialchars($nombreGrupo, ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="group-picker">
      <div class="group-picker-title">Mis grupos asignados</div>
      <div class="group-picker-grid">
        <?php foreach ($grupos as $grupo): ?>
          <a class="group-card <?= (int)$grupo['id_grupo'] === $grupoSeleccionado || ($grupoSeleccionado === 0 && count($grupos) === 1) ? 'selected' : '' ?>" href="?id_grupo=<?= (int)$grupo['id_grupo'] ?>">
            <strong><?= htmlspecialchars($grupo['nombre_grupo'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span>Ciclo <?= htmlspecialchars($grupo['ciclo_escolar'], ENT_QUOTES, 'UTF-8') ?></span>
            <small>Ver alumnos asignados →</small>
          </a>
        <?php endforeach; ?>
        <?php if ($grupos === []): ?><div class="empty-group">No tienes grupos asignados actualmente.</div><?php endif; ?>
      </div>
    </div>

    <?php if ($mostrarPanel): ?>
    <!-- ── KPIs ── -->
    <div class="kpi-row">
      <div class="kpi-card blue">
        <div class="kpi-label">Total Alumnos</div>
        <div class="kpi-value"><?= (int)$kpis['alumnos'] ?></div>
      </div>
      <div class="kpi-card amber">
        <div class="kpi-label">Atención Pendiente</div>
        <div class="kpi-value"><?= (int)$kpis['atencion'] ?></div>
      </div>
      <div class="kpi-card red">
        <div class="kpi-label">Crisis Reportadas Hoy</div>
        <div class="kpi-value"><?= (int)$kpis['crisis'] ?></div>
      </div>
      <div class="kpi-card teal">
        <div class="kpi-label">Acciones de Contención</div>
        <div class="kpi-value"><?= (int)$kpis['contenciones'] ?></div>
      </div>
    </div>

    <div class="timer-row">
      <!-- TEMPORIZADOR -->
      <div class="timer-card" id="configuracion">
        <div class="timer-label">Control de Tiempo</div>
        <div class="timer-display" id="timer-display">00:00</div>
        <div class="timer-status" id="timer-status">Listo para iniciar</div>

        <div class="timer-config">
          <div class="form-group timer-config-duration">
            <label class="form-label timer-config-label">Duración (min)</label>
            <input id="timer-input" type="number" min="1" max="120" value="40" class="form-select timer-config-control" />
          </div>
          <div class="form-group timer-config-level">
            <label class="form-label timer-config-label">Nivel Irritabilidad</label>
            <select id="timer-nivel" class="form-select timer-config-control">
              <option value="1">Bajo</option>
              <option value="2" selected>Medio</option>
              <option value="3">Alto</option>
            </select>
          </div>
        </div>

        <div class="timer-controls">
          <button class="timer-btn" id="btn-start" onclick="startTimer()">
            <div class="timer-btn-icon">
              <svg class="icon-lg" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3" fill="currentColor" stroke="none"/></svg>
            </div>
            <span class="timer-btn-label">Iniciar</span>
          </button>
          <button class="timer-btn disabled" id="btn-pause" onclick="pauseTimer()">
            <div class="timer-btn-icon">
              <svg class="icon-lg" viewBox="0 0 24 24"><rect x="6" y="4" width="4" height="16" fill="currentColor" stroke="none"/><rect x="14" y="4" width="4" height="16" fill="currentColor" stroke="none"/></svg>
            </div>
            <span class="timer-btn-label">Pausar</span>
          </button>
          <button class="timer-btn" id="btn-reset" onclick="resetTimer()">
            <div class="timer-btn-icon">
              <svg class="icon-lg" viewBox="0 0 24 24"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
            </div>
            <span class="timer-btn-label">Reiniciar</span>
          </button>
        </div>

        <!-- pausa activa alerta -->
        <div id="break-alert" class="break-alert">
          🐾 ¡Momento de pausa activa!<br/>La mascota está lista.
        </div>
      </div>
    </div>

    <!-- ── SEMÁFORO TABLE ── -->
    <div class="card">
      <table class="semaforo-table">
        <thead>
          <tr>
            <th>Nombre Alumno</th>
            <th>Estado Actual</th>
            <th>Última Actualización</th>
            <th>Último Registro Emocional</th>
            <th>Acciones</th>
          </tr>
        </thead>
<tbody id="alumnos-tbody">
          <?php if ($alumnosDashboard === []): ?>
            <tr><td colspan="5" class="empty-row">No hay alumnos activos en tus grupos asignados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="card incidencias-recientes-card">
      <div class="card-header"><span class="section-heading">Incidencias recientes</span></div>
      <div class="tabla-scroll">
        <table class="semaforo-table incidencias-table">
          <thead><tr><th>Fecha y hora</th><th>Alumno</th><th>Emoción</th><th>Contención</th><th>Descripción</th></tr></thead>
          <tbody id="incidencias-tbody">
            <?php if ($incidenciasDashboard === []): ?><tr><td colspan="5" class="empty-row">No hay incidencias recientes en este grupo.</td></tr><?php endif; ?>
            <?php foreach ($incidenciasDashboard as $incidencia): ?>
              <tr><td><?= htmlspecialchars($incidencia['fecha_hora'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['alumno'], ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['emocion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['accion_contencion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td><td><?= htmlspecialchars($incidencia['nota_descripcion'] ?? '—', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── BOTTOM ROW ── -->
    <div class="bottom-row">

      <!-- REGISTRO RÁPIDO -->
      <button class="incident-open" type="button" onclick="openIncidentModal()">
        <svg class="icon-lg" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        <span>Registrar incidencia rápida</span>
      </button>
      <div class="card incident-modal" id="incident-modal" hidden>
        <div class="card-header">
          <span class="section-heading">Registro de Incidencia Rápido</span>
          <button class="modal-close" type="button" onclick="closeIncidentModal()" aria-label="Cerrar ventana">×</button>
        </div>
        <form method="post" class="incident-form" id="incident-form">
          <input type="hidden" name="accion" value="registrar_incidencia">
          <?php if ($grupoSeleccionado > 0): ?><input type="hidden" name="id_grupo" value="<?= $grupoSeleccionado ?>"><?php endif; ?>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Alumno</label>
            <select class="form-select" id="sel-alumno" name="id_alumno" required>
              <option value="">— Seleccionar Alumno —</option>
<?php foreach ($alumnosDashboard as $alumno): ?>
                <option value="<?= (int)$alumno['id_alumno'] ?>"><?= htmlspecialchars($alumno['nombre_completo'], ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($alumno['grupo'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tipo Contención</label>
            <select class="form-select" id="sel-tipo" name="accion_contencion" required>
              <option value="">— Tipo —</option>
              <option>Pausa Activa</option>
              <option>Diálogo de Contención</option>
              <option>Pausa de Respiración</option>
              <option>Ejercicio Físico</option>
              <option>Lectura de Reflexión</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Emoción Detectada</label>
            <select class="form-select" id="sel-emocion" name="emocion" required>
              <option value="">— Emoción —</option>
              <option>Frustración</option>
              <option>Irritabilidad</option>
              <option>Ansiedad</option>
              <option>Tristeza</option>
              <option>Enojo</option>
              <option>Apatía</option>
            </select>
          </div>
          <textarea class="form-textarea form-select" id="txt-desc" name="descripcion" maxlength="1000" required placeholder="Descripción de la situación (máx. 5 líneas): detonante identificado, contexto del aula, acción tomada..."></textarea>
        </div>
        <div class="form-actions">
          <button class="btn-save" type="submit">
            <span>Guardar Nota</span>
          </button>
          <button class="btn-discard" type="button" onclick="descartar()">Descartar</button>
          <?php if ($mensaje): ?><span id="msg-ok" class="message-success">✓ <?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
          <?php if ($errores !== []): ?><span class="message-error"><?= htmlspecialchars(implode(' ', $errores), ENT_QUOTES, 'UTF-8') ?></span><?php endif; ?>
        </div>
        </form>
      </div>



    </div>
    <?php else: ?>
    <div class="group-selection-help">Selecciona uno de tus grupos para ver sus alumnos, indicadores y herramientas de seguimiento.</div>
    <?php endif; ?>
  </div><!-- /content -->
</div><!-- /main -->

<audio id="break-finished-audio" preload="auto" src="audio_pausa.php"></audio>

<script>
/* ── DATOS DE ALUMNOS ── */
const alumnos = <?= $alumnosJson ?: '[]' ?>;
const grupoActual = <?= (int)$grupoActual ?>;

function openIncidentModal() {
  const modal = document.getElementById('incident-modal');
  modal.hidden = false;
  modal.classList.add('is-open');
}
function closeIncidentModal() {
  const modal = document.getElementById('incident-modal');
  modal.classList.remove('is-open');
  modal.hidden = true;
}


const badgeMap = {
  green:  ["green",  "Verde – Estable"],
  yellow: ["yellow", "Amarillo – Atención"],
  red:    ["red",    "Rojo – Crisis"],
};

function renderAlumnos() {
  const tbody = document.getElementById("alumnos-tbody");
  tbody.innerHTML = "";
  if (alumnos.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty-row">No hay alumnos activos en tus grupos asignados.</td></tr>';
    return;
  }
  alumnos.forEach((a, i) => {
    const [bc, blabel] = badgeMap[a.estado] || badgeMap.green;
    const rowClass = a.estado === "red" ? "highlight-red" : a.estado === "yellow" ? "highlight-yellow" : "";
    const inicial = a.nombre.split(/\s+/).map(p => p[0]).slice(0, 2).join('').toUpperCase();
    tbody.innerHTML += `
      <tr class="${rowClass}">
        <td><div class="avatar">${inicial}</div></td>
        <td><div class="student-name">${a.nombre}</div><small class="student-group">${a.grupo}</small></td>
        <td><span class="dot ${a.estado}" title="${blabel}"></span><small class="status-label">${blabel}</small></td>
        <td class="last-update">${a.hora}</td>
        <td><span class="badge ${bc}">${a.reg}</span></td>
        <td><div class="action-btns"><button class="action-btn" title="Seleccionar alumno" onclick="editAlumno(${i})"><svg class="icon" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></button></div></td>
      </tr>`;
  });
}

function editAlumno(i) {
  const sel = document.getElementById("sel-alumno");
  sel.value = String(alumnos[i].id);
  document.getElementById("txt-desc").focus();
}

renderAlumnos();

/* ── FORMULARIO ── */
async function guardarNota(event) {
  event.preventDefault();
  const form = document.getElementById("incident-form");
  const alumno = document.getElementById("sel-alumno").value;
  const tipo = document.getElementById("sel-tipo").value;
  const emocion = document.getElementById("sel-emocion").value;
  const descripcion = document.getElementById("txt-desc").value.trim();
  if (!alumno || !tipo || !emocion || !descripcion) {
    alert("Completa alumno, tipo de contención, emoción y descripción.");
    return;
  }
  const respuesta = await fetch(window.location.href, {
    method: "POST",
    body: new FormData(form),
    headers: { "X-Requested-With": "XMLHttpRequest" }
  });
  const json = await respuesta.json();
  if (!json.ok) {
    alert((json.errores || ["No fue posible guardar la incidencia."]).join(" "));
    return;
  }
  const nuevosAlumnos = (json.datos && json.datos.alumnos) || [];
  alumnos.splice(0, alumnos.length, ...nuevosAlumnos.map(a => ({
    id: Number(a.id_alumno),
    nombre: a.nombre_completo,
    estado: String(a.estado_semaforo || "Verde").toLowerCase() === "amarillo" ? "yellow" : (String(a.estado_semaforo || "Verde").toLowerCase() === "rojo" ? "red" : "green"),
    grupo: a.grupo,
    reg: a.ultima_emocion || "Sin registro",
    accion: a.ultima_accion || "",
    hora: a.ultima_actualizacion || "Sin registro"
  })));
  renderAlumnos();
  const incidencias = (json.datos && json.datos.incidencias) || [];
  const incidenciasBody = document.getElementById("incidencias-tbody");
  incidenciasBody.innerHTML = incidencias.length ? incidencias.map(i => `<tr><td>${i.fecha_hora}</td><td>${i.alumno}</td><td>${i.emocion || "—"}</td><td>${i.accion_contencion || "—"}</td><td>${i.nota_descripcion || "—"}</td></tr>`).join("") : '<tr><td colspan="5" class="empty-row">No hay incidencias recientes en este grupo.</td></tr>';
  descartar();
  closeIncidentModal();
  const mensaje = document.querySelector(".form-actions .message-success");
  if (mensaje) {
    mensaje.textContent = "✓ Incidencia guardada correctamente.";
    mensaje.classList.add("is-visible");
  }
}

document.getElementById("incident-form").addEventListener("submit", guardarNota);

function descartar() {
  document.getElementById("sel-alumno").value = "";
  document.getElementById("sel-tipo").value   = "";
  document.getElementById("sel-emocion").value = "";
  document.getElementById("txt-desc").value   = "";
}

/* ── TEMPORIZADOR ── */
let timerInterval = null;
let totalSeconds  = 0;
let elapsed       = 0;
let running       = false;
let breakInterval = 0;
let activeSessionId = null;
const sessionEndpoint = "sesion_temporizador.php";

async function actualizarSesion(accion) {
  if (!activeSessionId) return true;
  const datos = new FormData();
  datos.append("accion", accion);
  datos.append("id_sesion", activeSessionId);
  const respuesta = await fetch(sessionEndpoint, { method: "POST", body: datos });
  const json = await respuesta.json();
  if (!json.ok) throw new Error(json.error || "No fue posible actualizar la sesión.");
  if (accion === "finalizar_sesion" || accion === "interrumpir_sesion") activeSessionId = null;
  return true;
}

function fmtTime(s) {
  const m = Math.floor(s / 60).toString().padStart(2, "0");
  const sec = (s % 60).toString().padStart(2, "0");
  return `${m}:${sec}`;
}
const timerStorageKey = `kairos_timer_${grupoActual || "sin-grupo"}`;
function saveTimerState() {
  if (!grupoActual || elapsed <= 0) return;
  localStorage.setItem(timerStorageKey, JSON.stringify({
    totalSeconds,
    elapsed,
    breakInterval,
    activeSessionId,
    running,
    savedAt: Date.now(),
    endAt: running ? Date.now() + elapsed * 1000 : null
  }));
}
function clearTimerState() {
  localStorage.removeItem(timerStorageKey);
}
function restoreTimerState() {
  const raw = localStorage.getItem(timerStorageKey);
  if (!raw) return;
  try {
    const state = JSON.parse(raw);
    totalSeconds = Number(state.totalSeconds) || 0;
    breakInterval = Number(state.breakInterval) || 0;
    activeSessionId = state.activeSessionId || null;
    elapsed = state.running && state.endAt ? Math.max(0, Math.ceil((state.endAt - Date.now()) / 1000)) : Number(state.elapsed) || 0;
    if (elapsed <= 0) { clearTimerState(); return; }
    document.getElementById("timer-display").textContent = fmtTime(elapsed);
    document.getElementById("timer-input").value = Math.max(1, Math.round(totalSeconds / 60));
    if (state.running) setTimeout(() => startTimer(), 0);
  } catch (error) {
    clearTimerState();
  }
}
function prepararAudioFin() {
  const audio = document.getElementById("break-finished-audio");
  if (!audio) return;
  audio.muted = true;
  audio.currentTime = 0;
  const desbloqueo = audio.play();
  if (desbloqueo && typeof desbloqueo.then === "function") {
    desbloqueo.then(() => {
      audio.pause();
      audio.currentTime = 0;
      audio.muted = false;
    }).catch(() => {
      audio.muted = false;
    });
  }
}

function reproducirAudioFin() {
  const audio = document.getElementById("break-finished-audio");
  if (!audio) return;
  audio.muted = false;
  audio.currentTime = 0;
  const reproduccion = audio.play();
  if (reproduccion && typeof reproduccion.catch === "function") {
    reproduccion.catch(() => {
      document.getElementById("timer-status").textContent = "✓ Sesión finalizada — presiona reproducir para escuchar el aviso";
    });
  }
}

async function startTimer() {
  if (running) return;
  const grupo = grupoActual;
  const mins  = parseInt(document.getElementById("timer-input").value) || 40;
  const nivel = parseInt(document.getElementById("timer-nivel").value);
  if (!grupo) {
    document.getElementById("timer-status").textContent = "Selecciona un grupo antes de iniciar.";
    return;
  }
  prepararAudioFin();
  try {
    if (activeSessionId) {
      await actualizarSesion("reanudar_sesion");
    } else {
      const datos = new FormData();
      datos.append("accion", "iniciar_sesion");
      datos.append("id_grupo", grupo);
      datos.append("duracion", mins);
      datos.append("nivel", nivel);
      const respuesta = await fetch(sessionEndpoint, { method: "POST", body: datos });
      const json = await respuesta.json();
      if (!json.ok) throw new Error(json.error || "No fue posible iniciar la sesión.");
      activeSessionId = json.id_sesion;
    }
  } catch (error) {
    document.getElementById("timer-status").textContent = error.message;
    return;
  }
  totalSeconds = mins * 60;
  breakInterval = nivel === 1 ? 20 * 60 : nivel === 2 ? 15 * 60 : 10 * 60;

  if (elapsed === 0) elapsed = totalSeconds;
  running = true;

  document.getElementById("btn-start").classList.add("disabled");
  document.getElementById("btn-pause").classList.remove("disabled");
  document.getElementById("timer-status").textContent = "⏱ Clase en progreso";
  document.getElementById("timer-display").className = "timer-display running";

  timerInterval = setInterval(() => {
    elapsed--;
    saveTimerState();
    document.getElementById("timer-display").textContent = fmtTime(elapsed);

    // alerta de break
    const prog = totalSeconds - elapsed;
    if (prog > 0 && prog % breakInterval === 0) {
      document.getElementById("break-alert").classList.add("is-visible");
      setTimeout(() => document.getElementById("break-alert").classList.remove("is-visible"), 6000);
    }

    // advertencia últimos 2 min
    if (elapsed === 120) {
      document.getElementById("timer-display").className = "timer-display warning";
      document.getElementById("timer-status").textContent = "⚠ Quedan 2 minutos";
    }
    if (elapsed <= 0) {
      clearInterval(timerInterval);
      running = false;
      document.getElementById("timer-display").textContent = "00:00";
      document.getElementById("timer-status").textContent = "✓ Sesión finalizada";
      document.getElementById("timer-display").className = "timer-display";
      reproducirAudioFin();
      actualizarSesion("finalizar_sesion").catch(error => {
        document.getElementById("timer-status").textContent = "Sesión finalizada localmente: " + error.message;
      });
      document.getElementById("btn-start").classList.remove("disabled");
      document.getElementById("btn-pause").classList.add("disabled");
      clearTimerState();
    }
  }, 1000);
}

function pauseTimer() {
  if (!running) return;
  clearInterval(timerInterval);
  running = false;
  document.getElementById("btn-start").classList.remove("disabled");
  document.getElementById("btn-pause").classList.add("disabled");
  document.getElementById("timer-status").textContent = "⏸ Pausado";
  document.getElementById("timer-display").className = "timer-display";
  saveTimerState();
}

async function resetTimer() {
  clearInterval(timerInterval);
  if (activeSessionId) {
    try { await actualizarSesion("interrumpir_sesion"); }
    catch (error) { document.getElementById("timer-status").textContent = error.message; }
  }
  running = false; elapsed = 0;
  document.getElementById("timer-display").textContent = "00:00";
  document.getElementById("timer-display").className = "timer-display";
  document.getElementById("timer-status").textContent = "Listo para iniciar";
  document.getElementById("btn-start").classList.remove("disabled");
  document.getElementById("btn-pause").classList.add("disabled");
  document.getElementById("break-alert").classList.remove("is-visible");
  clearTimerState();
}
restoreTimerState();
</script>
</body>
</html>
