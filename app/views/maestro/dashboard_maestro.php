<?php
    $errores = $resultado['errores'] ?? [];
    $mensaje = $resultado['mensaje'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Emotion Monitor – maestro | UPEMOR</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet" />
  <style>
    /* ── TOKENS ── */
    :root {
      --upemor-green:   #009B82;
      --upemor-green-d: #00705E;
      --upemor-green-l: #E6F5F2;
      --upemor-purple:  #6B3FA0;
      --upemor-purple-d:#4E2D78;
      --upemor-purple-l:#F0EAF8;
      --sidebar-bg:     #1A3A4A;
      --sidebar-hover:  #234D62;
      --sidebar-active: #00897B;

      --red-500:    #E53935;
      --red-100:    #FFEBEE;
      --yellow-500: #F9A825;
      --yellow-100: #FFF8E1;
      --green-500:  #43A047;
      --green-100:  #E8F5E9;

      --bg:         #F4F6F8;
      --surface:    #FFFFFF;
      --border:     #E0E6ED;
      --text-main:  #1C2B36;
      --text-muted: #6B7F8E;
      --text-light: #9BAEBE;

      --radius-md: 10px;
      --radius-lg: 14px;
      --shadow-card: 0 2px 10px rgba(0,0,0,.07);
    }

    /* ── RESET ── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: 'Inter', sans-serif;
      background: var(--bg);
      color: var(--text-main);
      height: 100vh;
      display: flex;
      overflow: hidden;
    }

    /* ── SIDEBAR ── */
    .sidebar {
      width: 220px;
      min-width: 220px;
      background: var(--sidebar-bg);
      display: flex;
      flex-direction: column;
      padding: 0;
      color: #fff;
    }
    .sidebar-logo {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 20px 18px 16px;
      border-bottom: 1px solid rgba(255,255,255,.10);
    }
    .logo-mark {
      width: 36px; height: 36px;
      background: var(--upemor-green);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-family: 'Nunito', sans-serif;
      font-weight: 800; font-size: 17px; color: #fff;
      flex-shrink: 0;
    }
    .logo-name {
      font-family: 'Nunito', sans-serif;
      font-size: 13px; font-weight: 800;
      letter-spacing: .4px; color: #fff; line-height: 1.2;
    }
    .logo-sub { font-size: 10px; font-weight: 400; opacity: .6; }

    .sidebar-nav { flex: 1; padding: 12px 0; }
    .nav-label {
      font-size: 9px; font-weight: 700; letter-spacing: 1.2px;
      text-transform: uppercase; color: rgba(255,255,255,.35);
      padding: 10px 18px 4px;
    }
    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 18px; cursor: pointer;
      font-size: 12.5px; font-weight: 500;
      color: rgba(255,255,255,.70);
      border-left: 3px solid transparent;
      transition: background .15s, color .15s, border-color .15s;
      position: relative;
    }
    .nav-item:hover { background: var(--sidebar-hover); color: #fff; }
    .nav-item.active {
      background: var(--sidebar-hover);
      color: #fff;
      border-left-color: var(--upemor-green);
    }
    .nav-item .nav-icon {
      width: 16px; height: 16px; opacity: .7; flex-shrink: 0;
    }
    .nav-item.active .nav-icon { opacity: 1; }
    .nav-sub { font-size: 10px; opacity: .5; margin-left: auto; }

    /* ── TOPBAR ── */
    .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
    .topbar {
      background: var(--upemor-green);
      color: #fff;
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 24px;
      height: 56px;
      flex-shrink: 0;
    }
    .topbar-title {
      font-family: 'Nunito', sans-serif;
      font-size: 17px; font-weight: 800;
      letter-spacing: .5px;
    }
    .topbar-right { display: flex; align-items: center; gap: 14px; }
    .topbar-user { font-size: 12.5px; opacity: .9; }
    .btn-logout {
      background: rgba(255,255,255,.18);
      color: #fff; border: 1px solid rgba(255,255,255,.3);
      padding: 5px 14px; border-radius: 6px;
      font-size: 12px; font-weight: 600; cursor: pointer;
      transition: background .15s;
    }
    .btn-logout:hover { background: rgba(255,255,255,.28); }

    /* ── CONTENT AREA ── */
    .content {
      flex: 1; overflow-y: auto;
      padding: 20px 24px 24px;
      display: flex; flex-direction: column; gap: 18px;
    }

    /* ── SECTION HEADER ── */
    .section-heading {
      font-family: 'Nunito', sans-serif;
      font-size: 14px; font-weight: 800;
      letter-spacing: .3px; color: var(--text-main);
      padding-bottom: 2px;
      border-bottom: 2px solid var(--upemor-green);
      display: inline-block;
    }

    /* ── KPI CARDS ── */
    .kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }
    .kpi-card {
      background: var(--surface);
      border-radius: var(--radius-md);
      padding: 14px 18px 12px;
      box-shadow: var(--shadow-card);
      border-top: 3px solid transparent;
      display: flex; flex-direction: column; gap: 4px;
    }
    .kpi-card.blue  { border-top-color: #1E88E5; }
    .kpi-card.amber { border-top-color: var(--yellow-500); }
    .kpi-card.red   { border-top-color: var(--red-500); }
    .kpi-card.teal  { border-top-color: var(--upemor-green); }
    .kpi-label { font-size: 11px; color: var(--text-muted); font-weight: 500; }
    .kpi-value {
      font-family: 'Nunito', sans-serif;
      font-size: 28px; font-weight: 800; color: var(--text-main); line-height: 1;
    }

    /* ── SEMÁFORO TABLE ── */
    .card {
      background: var(--surface);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-card);
      overflow: hidden;
    }
    .card-header {
      padding: 14px 20px 12px;
      border-bottom: 1px solid var(--border);
    }
    .semaforo-table { width: 100%; border-collapse: collapse; }
    .semaforo-table th {
      font-size: 10.5px; font-weight: 700;
      color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px;
      padding: 9px 16px;
      background: #F9FAFB;
      border-bottom: 1px solid var(--border);
      text-align: left;
    }
    .semaforo-table td {
      padding: 10px 16px;
      font-size: 13px;
      border-bottom: 1px solid #F1F4F7;
      vertical-align: middle;
    }
    .semaforo-table tr:last-child td { border-bottom: none; }
    .semaforo-table tr:hover td { background: #FAFBFC; }
    .semaforo-table tr.highlight-red td { background: var(--red-100); }
    .semaforo-table tr.highlight-yellow td { background: var(--yellow-100); }

    .student-cell { display: flex; align-items: center; gap: 10px; }
    .avatar {
      width: 34px; height: 34px; border-radius: 50%;
      object-fit: cover; flex-shrink: 0;
      background: var(--upemor-green-l);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: var(--upemor-green-d);
    }
    .student-name { font-weight: 600; font-size: 13px; }

    .dot {
      width: 18px; height: 18px; border-radius: 50%;
      display: inline-block; flex-shrink: 0;
      box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .dot.green  { background: var(--green-500); }
    .dot.yellow { background: var(--yellow-500); }
    .dot.red    { background: var(--red-500); }

    .badge {
      display: inline-block; padding: 2px 8px; border-radius: 20px;
      font-size: 11px; font-weight: 600;
    }
    .badge.red    { background: var(--red-100);    color: var(--red-500); }
    .badge.yellow { background: var(--yellow-100); color: #B8860B; }
    .badge.green  { background: var(--green-100);  color: var(--green-500); }
    .badge.teal   { background: var(--upemor-green-l); color: var(--upemor-green-d); }

    .action-btn {
      background: none; border: none; cursor: pointer;
      color: var(--text-muted); padding: 4px;
      border-radius: 5px; transition: color .15s, background .15s;
    }
    .action-btn:hover { color: var(--upemor-green); background: var(--upemor-green-l); }
    .action-btns { display: flex; gap: 4px; }

    /* ── BOTTOM ROW ── */
    .bottom-row { display: grid; grid-template-columns: 1fr 340px; gap: 14px; }

    /* ── QUICK FORM ── */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; padding: 16px 18px; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 11px; font-weight: 600; color: var(--text-muted); }
    .form-select, .form-textarea {
      border: 1.5px solid var(--border);
      border-radius: 7px;
      padding: 7px 10px;
      font-size: 12.5px;
      color: var(--text-main);
      background: #FAFBFC;
      outline: none;
      font-family: 'Inter', sans-serif;
      transition: border-color .15s;
    }
    .form-select:focus, .form-textarea:focus { border-color: var(--upemor-green); }
    .form-textarea {
      resize: none; height: 70px;
      grid-column: 1 / -1;
      margin-top: 2px;
    }
    .form-actions {
      display: flex; gap: 8px;
      padding: 0 18px 16px;
      align-items: center;
    }
    .btn-save {
      background: var(--upemor-green);
      color: #fff; border: none; padding: 8px 18px;
      border-radius: 7px; font-size: 12.5px; font-weight: 700;
      cursor: pointer; transition: background .15s;
    }
    .btn-save:hover { background: var(--upemor-green-d); }
    .btn-discard {
      background: #ECEFF1;
      color: var(--text-muted); border: none; padding: 8px 16px;
      border-radius: 7px; font-size: 12.5px; font-weight: 600;
      cursor: pointer; transition: background .15s;
    }
    .btn-discard:hover { background: #DDE3E9; }

    /* ── TIMER ── */
    .timer-card {
      background: var(--surface);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-card);
      display: flex; flex-direction: column; align-items: center;
      padding: 18px 20px 16px; gap: 4px;
    }
    .timer-label {
      font-size: 11px; font-weight: 700; letter-spacing: .8px;
      text-transform: uppercase; color: var(--text-muted);
      margin-bottom: 6px;
    }
    .timer-display {
      font-family: 'Nunito', sans-serif;
      font-size: 56px; font-weight: 800;
      color: var(--text-main);
      letter-spacing: 2px; line-height: 1;
    }
    .timer-display.running { color: var(--upemor-green); }
    .timer-display.warning { color: var(--red-500); }
    .timer-status {
      font-size: 11px; color: var(--text-muted); margin-top: 4px;
      min-height: 16px;
    }
    .timer-controls {
      display: flex; gap: 10px; margin-top: 12px;
    }
    .timer-btn {
      display: flex; flex-direction: column; align-items: center;
      gap: 4px; background: none; border: none; cursor: pointer;
      color: var(--text-muted);
      transition: color .15s;
    }
    .timer-btn:hover { color: var(--upemor-green); }
    .timer-btn.disabled { opacity: .4; pointer-events: none; }
    .timer-btn-icon {
      width: 42px; height: 42px; border-radius: 50%;
      background: #F1F4F8;
      display: flex; align-items: center; justify-content: center;
      transition: background .15s;
    }
    .timer-btn:hover .timer-btn-icon { background: var(--upemor-green-l); }
    .timer-btn-label { font-size: 10px; font-weight: 600; }

    /* ── SCROLLBAR ── */
    .content::-webkit-scrollbar { width: 5px; }
    .content::-webkit-scrollbar-track { background: transparent; }
    .content::-webkit-scrollbar-thumb { background: #C8D4DC; border-radius: 4px; }

    /* ── NOTIFICATION DOT ── */
    .notif-dot {
      width: 7px; height: 7px; background: var(--red-500);
      border-radius: 50%; position: absolute; right: 14px; top: 50%; margin-top: -3.5px;
    }

    /* SVG icons inline */
    .icon { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .icon-lg { width: 20px; height: 20px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  </style>
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

    <div class="nav-item" style="position:relative" onclick="this.classList.toggle('active')">
      <svg class="nav-icon icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      Bitácora de Incidentes
      <div class="notif-dot"></div>
    </div>

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
      <span class="topbar-user">Bienvenida, Prof. Delgado Blanca</span>
      <button class="btn-logout">Cerrar Sesión</button>
    </div>
  </header>

  <!-- CONTENT -->
  <div class="content">

    <!-- ── HEADING ── -->
    <div>
      <span class="section-heading">Panel de Control de Aula — 6º Grado B</span>
    </div>

    <!-- ── KPIs ── -->
    <div class="kpi-row">
      <div class="kpi-card blue">
        <div class="kpi-label">Total Alumnos</div>
        <div class="kpi-value">28</div>
      </div>
      <div class="kpi-card amber">
        <div class="kpi-label">Atención Pendiente</div>
        <div class="kpi-value">3</div>
      </div>
      <div class="kpi-card red">
        <div class="kpi-label">Crisis Reportadas Hoy</div>
        <div class="kpi-value">1</div>
      </div>
      <div class="kpi-card teal">
        <div class="kpi-label">Acciones de Contención</div>
        <div class="kpi-value">5</div>
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
          <!-- filas generadas por JS -->
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
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Alumno</label>
            <select class="form-select" id="sel-alumno">
              <option value="">— Seleccionar Alumno —</option>
              <option>Martín Blanca Lucía</option>
              <option>Martínez Blanca Uri</option>
              <option>Poirier Blanca Ana</option>
              <option>Joranca Delgado Blanca</option>
              <option>Crata Blanca Jorge</option>
              <option>García Ramírez Sofía</option>
              <option>López Torres Andrés</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Tipo Contención</label>
            <select class="form-select" id="sel-tipo">
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
            <select class="form-select" id="sel-emocion">
              <option value="">— Emoción —</option>
              <option>Frustración</option>
              <option>Irritabilidad</option>
              <option>Ansiedad</option>
              <option>Tristeza</option>
              <option>Enojo</option>
              <option>Apatía</option>
            </select>
          </div>
          <textarea class="form-textarea form-select" id="txt-desc" placeholder="Descripción de la situación (máx. 5 líneas): detonante identificado, contexto del aula, acción tomada..."></textarea>
        </div>
        <div class="form-actions">
          <button class="btn-save" onclick="guardarNota()">
            <span>Guardar Nota</span>
          </button>
          <button class="btn-discard" onclick="descartar()">Descartar</button>
          <span id="msg-ok" style="font-size:12px;color:var(--upemor-green);font-weight:600;display:none;">✓ Incidencia registrada</span>
        </div>
      </div>

      <!-- TEMPORIZADOR -->
      <div class="timer-card">
        <div class="timer-label">Control de Tiempo</div>
        <div class="timer-display" id="timer-display">00:00</div>
        <div class="timer-status" id="timer-status">Listo para iniciar</div>

        <div style="display:flex;gap:10px;margin-top:10px;align-items:center;">
          <div class="form-group" style="width:110px;">
            <label class="form-label" style="font-size:10px;">Duración (min)</label>
            <input id="timer-input" type="number" min="1" max="120" value="40"
              class="form-select" style="padding:5px 8px;font-size:13px;width:100%;" />
          </div>
          <div class="form-group" style="width:130px;margin-top:0;">
            <label class="form-label" style="font-size:10px;">Nivel Irritabilidad</label>
            <select id="timer-nivel" class="form-select" style="padding:5px 8px;font-size:12px;">
              <option value="1">Bajo</option>
              <option value="2" selected>Moderado</option>
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
        <div id="break-alert" style="display:none;background:var(--yellow-100);border:1.5px solid var(--yellow-500);border-radius:8px;padding:8px 12px;margin-top:10px;font-size:11.5px;color:#7B5800;font-weight:600;text-align:center;">
          🐾 ¡Momento de pausa activa!<br/>La mascota está lista.
        </div>
      </div>

    </div>
  </div><!-- /content -->
</div><!-- /main -->

<script>
/* ── DATOS DE ALUMNOS ── */
const alumnos = [
  { nombre: "Martín Blanca Lucía",   inicial:"ML", estado:"green",  reg:"Contención",           hora:"28/12/2022, 12:35 AM" },
  { nombre: "Martínez Blanca Uri",    inicial:"MU", estado:"yellow", reg:"Registro de Emoción",  hora:"28/12/2022, 12:35 AM" },
  { nombre: "Poirier Blanca Ana",     inicial:"PA", estado:"red",    reg:"Semáforo Rojo",        hora:"28/12/2022, 12:37 AM" },
  { nombre: "Joranca Delgado Blanca", inicial:"JD", estado:"yellow", reg:"Registro de Emoción",  hora:"28/12/2022, 12:28 AM" },
  { nombre: "Crata Blanca Jorge",     inicial:"CJ", estado:"green",  reg:"Registro de Emoción",  hora:"28/12/2022, 12:28 AM" },
  { nombre: "García Ramírez Sofía",   inicial:"GS", estado:"green",  reg:"Pausa Activa",         hora:"28/12/2022, 12:20 AM" },
  { nombre: "López Torres Andrés",    inicial:"LA", estado:"yellow", reg:"Registro de Emoción",  hora:"28/12/2022, 12:15 AM" },
];

const badgeMap = {
  green:  ["green",  "Verde – Estable"],
  yellow: ["yellow", "Amarillo – Atención"],
  red:    ["red",    "Rojo – Crisis"],
};

function renderAlumnos() {
  const tbody = document.getElementById("alumnos-tbody");
  tbody.innerHTML = "";
  alumnos.forEach((a, i) => {
    const [bc, blabel] = badgeMap[a.estado];
    const rowClass = a.estado === "red" ? "highlight-red" : a.estado === "yellow" ? "highlight-yellow" : "";
    tbody.innerHTML += `
      <tr class="${rowClass}">
        <td>
          <div class="avatar">${a.inicial}</div>
        </td>
        <td><div class="student-name">${a.nombre}</div></td>
        <td><span class="dot ${a.estado}" title="${blabel}"></span></td>
        <td style="color:var(--text-muted);font-size:12px;">${a.hora}</td>
        <td><span class="badge ${bc}">${a.reg}</span></td>
        <td>
          <div class="action-btns">
            <button class="action-btn" title="Ver detalle">
              <svg class="icon" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </button>
            <button class="action-btn" title="Editar registro" onclick="editAlumno(${i})">
              <svg class="icon" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
          </div>
        </td>
      </tr>`;
  });
}

function editAlumno(i) {
  const sel = document.getElementById("sel-alumno");
  sel.value = alumnos[i].nombre;
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

function fmtTime(s) {
  const m = Math.floor(s / 60).toString().padStart(2, "0");
  const sec = (s % 60).toString().padStart(2, "0");
  return `${m}:${sec}`;
}

function startTimer() {
  if (running) return;
  const mins  = parseInt(document.getElementById("timer-input").value) || 40;
  const nivel = parseInt(document.getElementById("timer-nivel").value);
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

function resetTimer() {
  clearInterval(timerInterval);
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
