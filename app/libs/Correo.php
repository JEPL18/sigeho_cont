<?php
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as MailException;

class Correo {
    public static function config() {
        $archivo = __DIR__ . '/../config/correo.php';
        if (!is_file($archivo)) {
            return false;
        }
        try {
            $config = require $archivo;
        } catch (Throwable $e) {
            error_log('Correo: configuración inválida.');
            return false;
        }
        if (!is_array($config)) {
            return false;
        }
        foreach (['host', 'puerto', 'remitente'] as $clave) {
            if (empty($config[$clave])) {
                return false;
            }
        }
        return $config;
    }

    public static function enviar($destino, $asunto, $cuerpo, $cuerpoHtml = null) {
        $config = self::config();
        if ($config === false) {
            return false;
        }
        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $config['host'];
            $mail->Port = (int)$config['puerto'];
            if (!empty($config['usuario'])) {
                $mail->SMTPAuth = true;
                $mail->Username = $config['usuario'];
                $mail->Password = isset($config['clave']) ? $config['clave'] : '';
            } else {
                $mail->SMTPAuth = false;
            }
            if (!empty($config['seguro'])) {
                $mail->SMTPSecure = $config['seguro'];
            }
            $mail->CharSet = 'UTF-8';
            $mail->setFrom($config['remitente'], isset($config['nombre_remitente']) ? $config['nombre_remitente'] : 'SIGEHO-CONT');
            $mail->addAddress($destino);
            $mail->Subject = $asunto;
            if ($cuerpoHtml !== null) {
                $mail->isHTML(true);
                $mail->Body = $cuerpoHtml;
                $mail->AltBody = $cuerpo;
            } else {
                $mail->Body = $cuerpo;
                $mail->AltBody = $cuerpo;
            }
            return $mail->send();
        } catch (MailException $e) {
            error_log('Correo: ' . $e->getMessage());
            return false;
        }
    }
}
