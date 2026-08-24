<?php
$relaciones = $datos['relaciones'] ?? [];
$alumnos = $datos['alumnos'] ?? [];
$tutores = $datos['tutores'] ?? [];
$estado = $datos['estado'] ?? 'Todos';
$errores = $resultado['errores'] ?? [];
$mensaje = $resultado['mensaje'] ?? null;
function e($valor): string { return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relaciones familiares | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css?v=relaciones-1">
</head>
<body>
<main class="panel-contenedor relaciones-contenedor">
    <header class="panel-encabezado">
        <div>
            <p class="panel-etiqueta">PLATAFORMA KAIROS</p>
            <h1>Relaciones familiares</h1>
            <p class="panel-bienvenida">Asigna uno o varios tutores a cada alumno.</p>
        </div>
        <a class="boton-secundario" href="panel_administrador.php">Regresar al panel</a>
    </header>

    <?php if ($errores !== []): ?><div class="alerta alerta-error"><ul><?php foreach ($errores as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if ($mensaje): ?><div class="alerta alerta-exito"><?= e($mensaje) ?></div><?php endif; ?>

    <section class="panel-seccion relacion-formulario">
        <h2>Asignar tutor a alumno</h2>
        <p class="panel-descripcion">Un tutor puede tener varios alumnos y un alumno puede tener varios tutores.</p>
        <?php if ($alumnos === [] || $tutores === []): ?>
            <div class="alerta alerta-error">Necesitas contar con alumnos y tutores activos antes de crear una relación.</div>
        <?php else: ?>
            <form method="post" action="relaciones.php">
                <input type="hidden" name="accion" value="guardar">
                <div class="relacion-form-grid">
                    <div class="campo"><label for="id_alumno">Alumno</label><select id="id_alumno" name="id_alumno" required><option value="">Selecciona un alumno</option><?php foreach ($alumnos as $alumno): ?><option value="<?= (int)$alumno['id_alumno'] ?>"><?= e($alumno['alumno']) ?> — <?= e($alumno['grado'].'°'.$alumno['grupo']) ?></option><?php endforeach; ?></select></div>
                    <div class="campo"><label for="id_tutor">Tutor</label><select id="id_tutor" name="id_tutor" required><option value="">Selecciona un tutor</option><?php foreach ($tutores as $tutor): ?><option value="<?= (int)$tutor['id_tutor'] ?>"><?= e($tutor['nombre']) ?> — <?= e($tutor['cargo']) ?></option><?php endforeach; ?></select></div>
                    <div class="campo"><label for="parentesco">Parentesco <span>(opcional)</span></label><input id="parentesco" name="parentesco" type="text" maxlength="50" placeholder="Ej. Madre, padre, abuelo"></div>
                    <div class="campo relacion-principal"><label><input type="checkbox" name="es_principal" value="1"> Marcar como tutor principal</label></div>
                </div>
                <button type="submit">Guardar relación</button>
            </form>
        <?php endif; ?>
    </section>

    <section class="panel-seccion">
        <div class="listado-cabecera"><div><h2>Relaciones registradas</h2><p class="panel-descripcion">Vínculos almacenados en alumno_tutores.</p></div><form method="get" action="relaciones.php"><label for="estado">Mostrar</label><select id="estado" name="estado" onchange="this.form.submit()"><option <?= $estado === 'Todos' ? 'selected' : '' ?>>Todos</option><option <?= $estado === 'Activo' ? 'selected' : '' ?>>Activo</option><option <?= $estado === 'Inactivo' ? 'selected' : '' ?>>Inactivo</option></select></form></div>
        <div class="tabla-wrap"><table><thead><tr><th>Alumno</th><th>Tutor</th><th>Parentesco</th><th>Principal</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
        <?php if ($relaciones === []): ?><tr><td colspan="6" class="tabla-vacia">No hay relaciones para el filtro seleccionado.</td></tr><?php else: foreach ($relaciones as $relacion): ?><tr><td><?= e($relacion['alumno']) ?></td><td><?= e($relacion['tutor']) ?></td><td><?= e($relacion['parentesco'] ?: '—') ?></td><td><?= (int)$relacion['es_principal'] === 1 ? 'Sí' : 'No' ?></td><td><span class="estado estado-<?= e($relacion['estado']) ?>"><?= e($relacion['estado']) ?></span></td><td><form class="form-accion" method="post" action="relaciones.php" onsubmit="return confirm('¿Deseas cambiar el estado de esta relación?');"><input type="hidden" name="accion" value="estado"><input type="hidden" name="id_alumno_tutor" value="<?= (int)$relacion['id_alumno_tutor'] ?>"><input type="hidden" name="accion_estado" value="<?= $relacion['estado'] === 'Activo' ? 'desactivar' : 'reactivar' ?>"><button type="submit" class="boton-tabla <?= $relacion['estado'] === 'Activo' ? 'boton-peligro' : 'boton-exito' ?>"><?= $relacion['estado'] === 'Activo' ? 'Desactivar' : 'Reactivar' ?></button></form></td></tr><?php endforeach; endif; ?></tbody></table></div>
    </section>
</main>
</body>
</html>
