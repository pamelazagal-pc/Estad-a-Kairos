<?php
    $errores = $resultado['errores'] ?? [];
    $mensaje = $resultado['mensaje'] ?? null;
    $datos = $datos ?? ($resultado['datos'] ?? []);
    $maestro = $datos['maestro'] ?? [];
    $grupos = $datos['grupos'] ?? [];
    $alumnosDashboard = $datos['alumnos'] ?? [];
    $kpis = $datos['kpis'] ?? ['alumnos' => 0, 'atencion' => 0, 'crisis' => 0, 'contenciones' => 0];
    $grupoSeleccionado = (int)($datos['grupo_seleccionado'] ?? 0);
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
  <link rel="stylesheet" href="../../CSS/maestro_dashboard.css?v=maestro-dashboard-1" />

</head>
<body>

<!-- ══════════════ SIDEBAR ══════════════ -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">U</div>
    <div class="logo-name">UPEMOR <span class="logo-sub"><br/>Universidad Politécnica</span></div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-label">Principal</div>

    <div class="nav-item active">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
      Tablero
      <span class="nav-sub">(Dashboard)</span>
    </div>

    <div class="nav-item" onclick="this.classList.toggle('active')">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M21 21v-2a4 4 0 0 0-3-3.85"/></svg>
      Mi Grupo
      <span class="nav-sub">(Lista c/Semáforos)</span>
    </div>

    <div class="nav-label">Registros</div>

    <a class="nav-item nav-link" href="bitacora_maestro.php">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      Bitácora emocional
    </a>

    <div class="nav-label">Catálogos</div>

    <div class="nav-item" onclick="this.classList.toggle('active')">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
      Catálogo Emocional
    </div>

    <div class="nav-item" onclick="this.classList.toggle('active')">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14"/><path d="M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
      Reportes
    </div>

    <div class="nav-label">Sistema</div>

    <div class="nav-item" onclick="this.classList.toggle('active')">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41"/><path d="M5.34 5.34l1.41 1.41"/><path d="M12 2v2M12 20v2M2 12h2M20 12h2"/><path d="M4.93 19.07l1.41-1.41"/><path d="M18.66 18.66l-1.41-1.41"/></svg>
      Configuración
    </div>
  </nav>
</aside>

<!-- ══════════════ MAIN ══════════════ -->
<div class="main">

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="topbar-title">EMOTION MONITOR – MAESTRO</div>
    <div class="topbar-right">
<span class="topbar-user">Bienvenido/a, <?= htmlspecialchars($maestro['nombre'] ?? $_SESSION['maestro_nombre'] ?? 'Maestro', ENT_QUOTES, 'UTF-8') ?></span>
      <a class="btn-logout" href="logout_maestro.php">Cerrar Sesión</a>
    </div>
  </header>

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

    <!-- ── SEMÁFORO TABLE ── -->
    <div class="card">
      <div class="card-header">
        <span class="section-heading">Monitoreo de Semáforos Emocionales Grupal</span>
      </div>
      <table class="semaforo-table">
        <thead>
          <tr>
            <th>Foto</th>
            <th>Nombre Alumno</th>
            <th>Estado Actual</th>
            <th>Última Actualización</th>
            <th>Último Registro Emocional</th>
            <th>Acciones</th>
          </tr>
        </thead>
<tbody id="alumnos-tbody">
          <?php if ($alumnosDashboard === []): ?>
            <tr><td colspan="6" class="empty-row">No hay alumnos activos en tus grupos asignados.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- ── BOTTOM ROW ── -->
    <div class="bottom-row">

      <!-- REGISTRO RÁPIDO -->
      <div class="card">
        <div class="card-header">
          <span class="section-heading">Registro de Incidencia Rápido</span>
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

      <!-- TEMPORIZADOR -->
      <div class="timer-card">
        <div class="timer-label">Control de Tiempo</div>
        <div class="timer-display" id="timer-display">00:00</div>
        <div class="timer-status" id="timer-status">Listo para iniciar</div>

        <div class="timer-config">
          <div class="form-group timer-config-group">
            <label class="form-label timer-config-label">Grupo</label>
            <select id="timer-grupo" class="form-select timer-config-control" required>
              <option value="">Seleccionar grupo</option>
              <?php foreach ($grupos as $grupo): ?>
                <option value="<?= (int)$grupo['id_grupo'] ?>"><?= htmlspecialchars($grupo['nombre_grupo'] . ' — ' . $grupo['ciclo_escolar'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>
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
  </div><!-- /content -->
</div><!-- /main -->

<audio id="break-finished-audio" preload="auto" src="audio_pausa.php"></audio>

<script>
/* ── DATOS DE ALUMNOS ── */
const alumnos = <?= $alumnosJson ?: '[]' ?>;

const badgeMap = {
  green:  ["green",  "Verde – Estable"],
  yellow: ["yellow", "Amarillo – Atención"],
  red:    ["red",    "Rojo – Crisis"],
};

function renderAlumnos() {
  const tbody = document.getElementById("alumnos-tbody");
  tbody.innerHTML = "";
  if (alumnos.length === 0) {
    tbody.innerHTML = '<tr><td colspan="6" class="empty-row">No hay alumnos activos en tus grupos asignados.</td></tr>';
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
function guardarNota() {
  const alumno  = document.getElementById("sel-alumno").value;
  const tipo    = document.getElementById("sel-tipo").value;
  const emocion = document.getElementById("sel-emocion").value;
  const desc    = document.getElementById("txt-desc").value.trim();

  if (!alumno || !tipo || !emocion) {
    alert("Por favor selecciona alumno, tipo de contención y emoción detectada.");
    return;
  }

  // actualiza estado del alumno en la tabla
  const idx = alumnos.findIndex(a => a.nombre === alumno);
  if (idx !== -1) {
    alumnos[idx].reg = tipo;
    const now = new Date();
    alumnos[idx].hora = now.toLocaleDateString('es-MX') + ", " + now.toLocaleTimeString('es-MX', {hour:'2-digit',minute:'2-digit'});
    renderAlumnos();
  }

  document.getElementById("msg-ok").style.display = "inline";
  setTimeout(() => document.getElementById("msg-ok").style.display = "none", 2500);
  descartar();
}

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
  const grupo = document.getElementById("timer-grupo").value;
  const mins  = parseInt(document.getElementById("timer-input").value) || 40;
  const nivel = parseInt(document.getElementById("timer-nivel").value);
  if (!grupo) {
    alert("Selecciona el grupo de la sesión.");
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
    document.getElementById("timer-display").textContent = fmtTime(elapsed);

    // alerta de break
    const prog = totalSeconds - elapsed;
    if (prog > 0 && prog % breakInterval === 0) {
      document.getElementById("break-alert").style.display = "block";
      setTimeout(() => document.getElementById("break-alert").style.display = "none", 6000);
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
  document.getElementById("break-alert").style.display = "none";
}
</script>
</body>
</html>
