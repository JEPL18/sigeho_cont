<?php
class Turnstile {
    public static function config() {
        $archivo = __DIR__ . '/../config/turnstile.php';
        if (!is_file($archivo)) {
            return false;
        }
        try {
            $config = require $archivo;
        } catch (Throwable $e) {
            error_log('Turnstile: configuración inválida.');
            return false;
        }
        if (!is_array($config) || empty($config['clave_secreta'])) {
            return false;
        }
        return $config;
    }

    public static function configurado() {
        return self::config() !== false;
    }

    public static function verificar($token, $ip = null) {
        $config = self::config();
        if ($config === false) {
            return false;
        }
        if (!is_string($token) || $token === '') {
            return false;
        }
        $datos = [
            'secret' => $config['clave_secreta'],
            'response' => $token,
        ];
        if (is_string($ip) && $ip !== '') {
            $datos['remoteip'] = $ip;
        }
        $cuerpo = http_build_query($datos);
        $respuesta = false;
        $intentos = 0;
        while ($respuesta === false && $intentos < 2) {
            $intentos++;
            if (function_exists('curl_init')) {
                $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $cuerpo);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                $respuesta = curl_exec($ch);
                curl_close($ch);
            } else {
                $contexto = stream_context_create([
                    'http' => [
                        'method' => 'POST',
                        'header' => 'Content-Type: application/x-www-form-urlencoded',
                        'content' => $cuerpo,
                        'timeout' => 5,
                    ],
                ]);
                $respuesta = file_get_contents('https://challenges.cloudflare.com/turnstile/v0/siteverify', false, $contexto);
            }
        }
        if (!is_string($respuesta) || $respuesta === '') {
            error_log('Turnstile: sin respuesta del servidor de verificación.');
            return false;
        }
        $json = json_decode($respuesta, true);
        if (!is_array($json) || !isset($json['success'])) {
            error_log('Turnstile: respuesta de verificación inválida.');
            return false;
        }
        if ($json['success'] !== true) {
            if (!empty($json['error-codes']) && is_array($json['error-codes'])) {
                error_log('Turnstile: verificación fallida (' . implode(',', $json['error-codes']) . ').');
            }
            return false;
        }
        return true;
    }
}
