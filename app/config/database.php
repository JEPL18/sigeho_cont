<?php
$host = 'localhost';
$dbname = 'sigeho_cont_db';
$user = 'root';
$pass = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

$clave_maestra = getenv('SIGEHO_MASTER_KEY');
if ($clave_maestra === false || $clave_maestra === '') {
    $archivo_clave = __DIR__ . '/master_key.php';
    if (is_file($archivo_clave)) {
        $clave_maestra = require $archivo_clave;
    }
}
if ($clave_maestra === false || $clave_maestra === '' || !is_string($clave_maestra)) {
    die('Error de configuración: falta la clave maestra. Cree el archivo app/config/master_key.php con la clave del sistema o defina la variable de entorno SIGEHO_MASTER_KEY.');
}
define('MASTER_KEY', $clave_maestra);
unset($clave_maestra, $archivo_clave);

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function cifrar_secreto($texto_plano) {
    if (empty($texto_plano)) return null;
    $metodo = 'aes-256-cbc';

    $iv_length = openssl_cipher_iv_length($metodo);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $cifrado = openssl_encrypt($texto_plano, $metodo, MASTER_KEY, 0, $iv);

    return base64_encode($iv . $cifrado);
}

function descifrar_secreto($texto_cifrado) {
    if (empty($texto_cifrado)) return null;

    $datos = base64_decode($texto_cifrado);
    $metodo = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($metodo);

    $iv = substr($datos, 0, $iv_length);
    $cifrado = substr($datos, $iv_length);

    return openssl_decrypt($cifrado, $metodo, MASTER_KEY, 0, $iv);
}
