<?php

declare(strict_types=1);

class KairosMailer
{
    private array $config;
    /** @var resource|null */
    private $socket = null;

    public function __construct(?array $config = null)
    {
        $this->config = $config ?? $this->cargarConfiguracion();
    }

    public function enviar(string $destinatario, string $nombreDestinatario, string $asunto, string $contenidoHtml): bool
    {
        $destinatario = trim($destinatario);
        if (!filter_var($destinatario, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo del tutor no es válido.');
        }
        $this->validarConfiguracion();

        $host = (string)$this->config['host'];
        $port = (int)$this->config['port'];
        $timeout = (int)($this->config['timeout'] ?? 15);
        $errno = 0;
        $error = '';
        $this->socket = @stream_socket_client("tcp://{$host}:{$port}", $errno, $error, $timeout);
        if (!is_resource($this->socket)) {
            throw new RuntimeException('No fue posible conectar con el servidor de correo.');
        }
        stream_set_timeout($this->socket, $timeout);

        try {
            $this->esperarRespuesta([220]);
            $this->comando('EHLO localhost', [250]);
            $this->comando('STARTTLS', [220]);
            $crypto = STREAM_CRYPTO_METHOD_TLS_CLIENT;
            if (!stream_socket_enable_crypto($this->socket, true, $crypto)) {
                throw new RuntimeException('No fue posible activar la conexión segura con Gmail.');
            }
            $this->comando('EHLO localhost', [250]);
            $this->comando('AUTH LOGIN', [334]);
            $this->comando(base64_encode((string)$this->config['username']), [334]);
            $this->comando(base64_encode((string)$this->config['password']), [235]);
            $this->comando('MAIL FROM:<' . $this->correoSeguro((string)$this->config['from_email']) . '>', [250]);
            $this->comando('RCPT TO:<' . $destinatario . '>', [250, 251]);
            $this->comando('DATA', [354]);

            $fromName = $this->textoCabecera((string)($this->config['from_name'] ?? 'Kairos'));
            $toName = $this->textoCabecera($nombreDestinatario !== '' ? $nombreDestinatario : 'Tutor');
            $subject = $this->codificarCabecera($asunto);
            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . $fromName . ' <' . $this->correoSeguro((string)$this->config['from_email']) . '>',
                'To: ' . $toName . ' <' . $destinatario . '>',
                'Subject: ' . $subject,
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
                '',
            ];
            $body = preg_replace("/(\r\n|\r|\n)/", "\r\n", $contenidoHtml) ?? '';
            $body = preg_replace('/^\./m', '..', $body) ?? $body;
            // SMTP requiere una línea en blanco entre los encabezados y el cuerpo.
            $mensaje = implode("\r\n", $headers) . "\r\n" . $body . "\r\n.\r\n";
            $this->escribir($mensaje);
            $this->esperarRespuesta([250]);
            $this->comando('QUIT', [221, 250]);
            return true;
        } finally {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
            $this->socket = null;
        }
    }

    private function cargarConfiguracion(): array
    {
        $ruta = __DIR__ . '/../../config/mail.php';
        if (!is_file($ruta)) {
            throw new RuntimeException('Falta configurar config/mail.php para enviar correos.');
        }
        $config = require $ruta;
        if (!is_array($config)) {
            throw new RuntimeException('La configuración SMTP no es válida.');
        }
        return $config;
    }

    private function validarConfiguracion(): void
    {
        foreach (['host', 'port', 'username', 'password', 'from_email'] as $campo) {
            if (!isset($this->config[$campo]) || trim((string)$this->config[$campo]) === '') {
                throw new RuntimeException('La configuración SMTP está incompleta.');
            }
        }
        if (!filter_var((string)$this->config['from_email'], FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('El correo remitente no es válido.');
        }
    }

    private function comando(string $comando, array $codigosEsperados): void
    {
        $this->escribir($comando . "\r\n");
        $this->esperarRespuesta($codigosEsperados);
    }

    private function escribir(string $contenido): void
    {
        if (!is_resource($this->socket) || @fwrite($this->socket, $contenido) === false) {
            throw new RuntimeException('No fue posible enviar el mensaje por SMTP.');
        }
    }

    private function esperarRespuesta(array $codigosEsperados): void
    {
        if (!is_resource($this->socket)) {
            throw new RuntimeException('La conexión SMTP no está disponible.');
        }
        $respuesta = '';
        do {
            $linea = fgets($this->socket, 515);
            if ($linea === false) {
                throw new RuntimeException('Gmail no respondió al envío del correo.');
            }
            $respuesta = trim($linea);
            $codigo = (int)substr($respuesta, 0, 3);
        } while (isset($respuesta[3]) && $respuesta[3] === '-');

        if (!in_array($codigo, $codigosEsperados, true)) {
            throw new RuntimeException('Gmail rechazó la operación SMTP.');
        }
    }

    private function correoSeguro(string $correo): string
    {
        return str_replace(["\r", "\n", '<', '>', ' '], '', trim($correo));
    }

    private function textoCabecera(string $texto): string
    {
        $texto = trim(str_replace(["\r", "\n"], '', $texto));
        return $this->codificarCabecera($texto);
    }

    private function codificarCabecera(string $texto): string
    {
        return '=?UTF-8?B?' . base64_encode($texto) . '?=';
    }
}
