<?php
$asignaciones = $datos['asignaciones'] ?? [];
$docentes = $datos['docentes'] ?? [];
$grupos = $datos['grupos'] ?? [];
$estado = $datos['estado'] ?? 'Todos';
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
function esc($valor): string { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar maestros | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css?v=asignaciones-1">
</head>
<body>
<main class="panel-contenedor relaciones-contenedor">
    <header class="panel-encabezado">
        <div><p class="panel-etiqueta">PLATAFORMA KAIROS</p><h1>Asignar maestros a grupos</h1><p class="panel-bienvenida">Define qué grupos puede atender cada maestro.</p></div>
        <a class="boton-secundario" href="panel_administrador.php">Regresar al panel</a>
    </header>
    <?php if ($errores !== []): ?><div class="alerta alerta-error"><ul><?php foreach ($errores as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta alerta-exito"><?= esc($mensaje) ?></div><?php endif; ?>
    <section class="panel-seccion relacion-formulario">
        <h2>Nueva asignación</h2><p class="panel-descripcion">Un maestro puede atender varios grupos durante un ciclo escolar.</p>
        <?php if ($docentes === [] || $grupos === []): ?><div class="alerta alerta-error">Necesitas contar con maestros y grupos activos antes de crear una asignación.</div><?php else: ?>
        <form method="post" action="docente_grupos.php"><input type="hidden" name="accion" value="guardar"><div class="relacion-form-grid">
            <div class="campo"><label for="id_docente">Maestro</label><select id="id_docente" name="id_docente" required><option value="">Selecciona un maestro</option><?php foreach ($docentes as $docente): ?><option value="<?= (int)$docente['id_docente'] ?>"><?= esc($docente['nombre']) ?> — <?= esc($docente['correo']) ?></option><?php endforeach; ?></select></div>
            <div class="campo"><label for="id_grupo">Grupo</label><select id="id_grupo" name="id_grupo" required><option value="">Selecciona un grupo</option><?php foreach ($grupos as $grupo): ?><option value="<?= (int)$grupo['id_grupo'] ?>"><?= esc($grupo['grado'].'° '.$grupo['grupo'].' — '.$grupo['ciclo_escolar']) ?></option><?php endforeach; ?></select></div>
            <div class="campo"><label for="ciclo_escolar">Ciclo escolar</label><input id="ciclo_escolar" name="ciclo_escolar" type="text" maxlength="20" placeholder="Ej. 2026-2027" required></div>
            <div class="campo relacion-principal"><label><input type="checkbox" name="es_titular" value="1"> Marcar como maestro titular</label></div>
        </div><button type="submit">Guardar asignación</button></form>
        <?php endif; ?>
    </section>
    <section class="panel-seccion">
        <div class="listado-cabecera"><div><h2>Asignaciones registradas</h2><p class="panel-descripcion">Relaciones almacenadas en docente_grupos.</p></div><form method="get" action="docente_grupos.php"><label for="estado">Mostrar</label><select id="estado" name="estado" onchange="this.form.submit()"><option <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option><option <?= $estado === 'Activo' ? 'selected' : '' ?>>Activo</option><option <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option></select></form></div>
        <div class="tabla-wrap"><table><thead><tr><th>Maestro</th><th>Grupo</th><th>Ciclo escolar</th><th>Titular</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        <?php if ($asignaciones === []): ?><tr><td colspan="6" class="tabla-vacia">No hay asignaciones para el filtro seleccionado.</td></tr><?php else: foreach ($asignaciones as $asignacion): ?><tr><td><?= esc($asignacion['docente']) ?></td><td><?= esc($asignacion['grupo']) ?></td><td><?= esc($asignacion['ciclo_escolar']) ?></td><td><?= (int)$asignacion['es_titular'] === 1 ? 'Sí' : 'No' ?></td><td><?= esc($asignacion['estado']) ?></td><td><form class="form-accion" method="post" action="docente_grupos.php" onsubmit="return confirm('¿Deseas cambiar el estado de esta asignación?');"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_docente_grupo" value="<?= (int)$asignacion['id_docente_grupo'] ?>"><input type="hidden" name="accion_estado" value="<?= $asignacion['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>"><button type="submit" class="boton-tabla <?= $asignacion['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>"><?= $asignacion['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?></button></form></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>
</main>
</body>
</html>
