<?php
$listado = $resultadoListado['listado'] ?? ['titulo' => 'Listado', 'columnas' => [], 'filas' => []];
$error = $resultadoListado['error'] ?? null;
$filtroEstado = $listado['filtro_estado'] ?? 'Todos';
$mensajeAccion = $mensajeAccion ?? null;
$estadoIndex = $listado['estado_index'] ?? count($listado['columnas']) - 1;
$nombreAdministrador = $_SESSION['administrador_nombre'] ?? 'Administrador';
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($listado['titulo'], ENT_QUOTES, 'UTF-8') ?> | Kairos</title>
    <link rel="stylesheet" href="../../public/CSS/main.css?v=listados-1">
</head>
<body class="panel-body">
    <?php require_once __DIR__ . '/sidebar_administrador.php'; ?>

    <main class="panel-contenedor listado-contenedor">
        <?php require_once __DIR__ . '/topbar_administrador.php'; ?>
        <header class="panel-encabezado">
            <div>
                <p class="panel-etiqueta">PLATAFORMA KAIROS</p>
                <h1><?= htmlspecialchars($listado['titulo'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="panel-bienvenida">Registros disponibles para <?= htmlspecialchars($nombreAdministrador, ENT_QUOTES, 'UTF-8') ?>.</p>
            </div>
        </header>

        <section class="panel-seccion listado-seccion">
            <div class="listado-cabecera">
                <div><h2>Listado de <?= htmlspecialchars($listado['titulo'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= count($listado['filas']) ?> registro(s) encontrado(s).</p></div>
                <div class="listado-filtros">
                    <form method="get">
                        <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8') ?>">
                        <select name="estado" onchange="this.form.submit()" aria-label="Filtrar por estado">
                            <?php foreach (['Todos', 'Activo', 'Inactivo', 'Trasladado', 'Egresado'] as $estadoOpcion): ?>
                                <option value="<?= $estadoOpcion ?>" <?= $filtroEstado === $estadoOpcion ? 'selected' : '' ?>><?= $estadoOpcion ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                    <input class="listado-buscador" type="search" placeholder="Buscar en este listado" data-listado-busqueda>
                </div>
            </div>

            <?php if ($mensajeAccion !== null): ?><div class="ok"><?= htmlspecialchars($mensajeAccion, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <?php if ($error !== null): ?>
                <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            <?php elseif ($listado['filas'] === []): ?>
                <div class="listado-vacio">Todavía no hay registros para mostrar.</div>
            <?php else: ?>
                <div class="tabla-contenedor">
                    <table class="tabla-listado">
                        <thead><tr><?php foreach ($listado['columnas'] as $columna): ?><th><?= htmlspecialchars($columna, ENT_QUOTES, 'UTF-8') ?></th><?php endforeach; ?><th>Acciones</th></tr></thead>
                        <tbody data-listado-filas>
                            <?php foreach ($listado['filas'] as $fila): ?>
                                <?php $estadoRegistro = (string) ($fila[$estadoIndex] ?? ''); ?>
                                <tr>
                                    <?php foreach ($fila as $valor): ?><td><?= htmlspecialchars((string)($valor ?? '—'), ENT_QUOTES, 'UTF-8') ?></td><?php endforeach; ?>
                                    <td class="acciones-celda">
                                        <a class="accion-enlace" href="editar.php?tipo=<?= urlencode($tipo) ?>&id=<?= (int) $fila[0] ?>">Editar</a>
                                        <?php $esAdminPrincipal = $tipo === 'administradores' && (string)($fila[4] ?? '') === 'Principal'; ?>
                                        <?php if ($esAdminPrincipal): ?>
                                            <span class="accion-protegida">Protegido</span>
                                        <?php elseif ($estadoRegistro === 'Activo'): ?>
                                            <form method="post" onsubmit="return confirm('¿Deseas desactivar este registro? La información se conservará.');">
                                                <input type="hidden" name="accion" value="cambiar_estado"><input type="hidden" name="operacion" value="desactivar"><input type="hidden" name="id" value="<?= (int) $fila[0] ?>"><input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($filtroEstado, ENT_QUOTES, 'UTF-8') ?>">
                                                <button class="accion-enlace accion-peligro" type="submit">Desactivar</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" onsubmit="return confirm('¿Deseas reactivar este registro?');">
                                                <input type="hidden" name="accion" value="cambiar_estado"><input type="hidden" name="operacion" value="reactivar"><input type="hidden" name="id" value="<?= (int) $fila[0] ?>"><input type="hidden" name="estado_filtro" value="<?= htmlspecialchars($filtroEstado, ENT_QUOTES, 'UTF-8') ?>">
                                                <button class="accion-enlace" type="submit">Reactivar</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script>
        const buscador = document.querySelector('[data-listado-busqueda]');
        const filas = document.querySelector('[data-listado-filas]');
        if (buscador && filas) {
            buscador.addEventListener('input', () => {
                const texto = buscador.value.toLowerCase();
                filas.querySelectorAll('tr').forEach((fila) => {
                    fila.hidden = !fila.textContent.toLowerCase().includes(texto);
                });
            });
        }
    </script>
</body>
</html>
