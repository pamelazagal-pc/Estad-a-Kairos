<?php
$archivo = __DIR__ . '/../../../app/multimedia/audios/pausa_activa_fin.mp3';

if (!is_file($archivo) || !is_readable($archivo)) {
    http_response_code(404);
    exit('Audio no disponible.');
}

header('Content-Type: audio/mpeg');
header('Content-Length: ' . filesize($archivo));
header('Content-Disposition: inline; filename="pausa_activa_fin.mp3"');
header('Cache-Control: public, max-age=86400');
readfile($archivo);
exit;
