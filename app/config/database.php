<?php
// app/config/database.php
// Adaptación de config/db.php del proyecto original.
// Misma conexión PDO, misma MASTER_KEY y mismas funciones de cifrado
// (la llave NO debe cambiar: los secretos 2FA guardados están cifrados con ella).

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

// --- SISTEMA DE CIFRADO AES-256 PARA DATOS SENSIBLES ---

// LLAVE MAESTRA (¡Nunca la cambies una vez que haya usuarios registrados!)
define('MASTER_KEY', 'SigehoContMasterKey2026_Secreta!');

function cifrar_secreto($texto_plano) {
    if (empty($texto_plano)) return null;
    $metodo = 'aes-256-cbc';
    // Generar un vector de inicialización único para máxima seguridad
    $iv_length = openssl_cipher_iv_length($metodo);
    $iv = openssl_random_pseudo_bytes($iv_length);
    // Ciframos los datos
    $cifrado = openssl_encrypt($texto_plano, $metodo, MASTER_KEY, 0, $iv);
    // Devolvemos el IV y el texto cifrado concatenados en base64
    return base64_encode($iv . $cifrado);
}

function descifrar_secreto($texto_cifrado) {
    if (empty($texto_cifrado)) return null;
    // Decodificamos el base64
    $datos = base64_decode($texto_cifrado);
    $metodo = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($metodo);
    // Separamos el IV del texto cifrado
    $iv = substr($datos, 0, $iv_length);
    $cifrado = substr($datos, $iv_length);
    // Desciframos
    return openssl_decrypt($cifrado, $metodo, MASTER_KEY, 0, $iv);
}
