<?php
$videos = $videos ?? [];
$lecturas = $lecturas ?? [];
$actividades = $actividades ?? [];
function recursoEsc($valor): string
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}
function recursoYoutubeEmbed($url): string
{
    $valor = trim((string)$url);
    $partes = parse_url($valor);
    $host = strtolower($partes['host'] ?? '');
    $video = '';
    if ($host === 'youtu.be' || $host === 'www.youtu.be') {
        $video = trim($partes['path'] ?? '', '/');
    } elseif (str_contains($host, 'youtube.com')) {
        parse_str($partes['query'] ?? '', $query);
        $video = (string)($query['v'] ?? '');
        if (($partes['path'] ?? '') !== '' && str_starts_with($partes['path'], '/embed/')) $video = substr($partes['path'], 7);
    }
    return $video !== '' ? 'https://www.youtube.com/embed/' . rawurlencode($video) : '';
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recursos pedagógicos | Kairos</title>
    <link rel="stylesheet" href="../../CSS/main.css">
</head>
<body class="panel-body recursos-page">
<?php require_once __DIR__ . '/../sidebar_maestro.php'; ?>
<div class="panel-contenedor vista-interna sidebar-maestro-inyectada">
<?php require_once __DIR__ . '/../topbar_maestro.php'; ?>
    <main class="contenedor-admin recursos-contenedor">
        <header class="encabezado-seccion">
            <div>
                <p class="eyebrow-admin">Biblioteca Kairos</p>
                <h1>Recursos pedagógicos</h1>
                <p>Consulta videos, lecturas y actividades publicadas por el administrador.</p>
            </div>
        </header>
        <section class="recursos-seccion">
            <h2>Videos</h2>
            <div class="recursos-grid">
                <?php if ($videos === []): ?><article class="panel-seccion recurso-vacio"><p>No hay videos activos publicados.</p></article><?php endif; ?>
                <?php foreach ($videos as $video): ?>
                    <article class="recurso-card">
                        <div class="recurso-icono">▶</div>
                        <div><p class="recurso-categoria"><?= recursoEsc($video['categoria']) ?></p><h3><?= recursoEsc($video['titulo']) ?></h3><p><?= (int)$video['duracion_segundos'] ?> segundos</p><?php $embed = recursoYoutubeEmbed($video['url_youtube']); ?><?php if ($embed !== ''): ?><div class="video-embed"><iframe src="<?= recursoEsc($embed) ?>" title="<?= recursoEsc($video['titulo']) ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php else: ?><a class="boton" href="<?= recursoEsc($video['url_youtube']) ?>" target="_blank" rel="noopener noreferrer">Abrir video</a><?php endif; ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="recursos-seccion">
            <h2>Lecturas</h2>
            <div class="recursos-grid">
                <?php if ($lecturas === []): ?><article class="panel-seccion recurso-vacio"><p>No hay lecturas activas publicadas.</p></article><?php endif; ?>
                <?php foreach ($lecturas as $lectura): ?>
                    <article class="recurso-card">
                        <div class="recurso-icono">▤</div>
                        <div><p class="recurso-categoria"><?= recursoEsc($lectura['categoria_tematica']) ?></p><h3><?= recursoEsc($lectura['titulo']) ?></h3><p><?= (int)$lectura['tiempo_estimado_min'] ?> minutos</p><details><summary>Leer contenido</summary><p><?= nl2br(recursoEsc($lectura['contenido'])) ?></p></details></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <section class="recursos-seccion">
            <h2>Actividades</h2>
            <div class="recursos-grid">
                <?php if ($actividades === []): ?><article class="panel-seccion recurso-vacio"><p>No hay actividades activas registradas. El administrador puede publicar un video con categoría “Actividad”.</p></article><?php endif; ?>
                <?php foreach ($actividades as $actividad): ?>
                    <article class="recurso-card"><div class="recurso-icono">✓</div><div><p class="recurso-categoria">Actividad guiada</p><h3><?= recursoEsc($actividad['titulo']) ?></h3><?php $embedActividad = recursoYoutubeEmbed($actividad['url_youtube']); ?><?php if ($embedActividad !== ''): ?><div class="video-embed"><iframe src="<?= recursoEsc($embedActividad) ?>" title="<?= recursoEsc($actividad['titulo']) ?>" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe></div><?php else: ?><a class="boton" href="<?= recursoEsc($actividad['url_youtube']) ?>" target="_blank" rel="noopener noreferrer">Abrir actividad</a><?php endif; ?></div></article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
</body>
</html>
