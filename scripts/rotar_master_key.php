<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Solo por consola.');
}

$root = dirname(__DIR__);
$probar = in_array('--probar', $argv, true);

$archivo_vieja = "$root/app/config/master_key.php";
$archivo_nueva = "$root/app/config/master_key_nueva.php";

if (!is_file($archivo_vieja)) {
    fwrite(STDERR, "No existe app/config/master_key.php. Sin la clave vieja no se puede descifrar.\n\n");
    exit(1);
}
if (!is_file($archivo_nueva)) {
    fwrite(STDERR, "No existe app/config/master_key_nueva.php. Cree el archivo con la clave nueva antes de migrar.\n\n");
    exit(1);
}

$clave_vieja = require $archivo_vieja;
$clave_nueva = require $archivo_nueva;

if (!is_string($clave_vieja) || $clave_vieja === '' || !is_string($clave_nueva) || $clave_nueva === '') {
    fwrite(STDERR, "La clave vieja o la nueva no es un texto válido. Revise ambos archivos.\n\n");
    exit(1);
}
if ($clave_vieja === $clave_nueva) {
    fwrite(STDERR, "La clave nueva es igual a la vieja. Genere una distinta.\n\n");
    exit(1);
}

require "$root/app/config/database.php";

function descifrar_con_clave($texto_cifrado, $clave) {
    if (empty($texto_cifrado)) {
        return null;
    }
    $datos = base64_decode($texto_cifrado, true);
    if ($datos === false) {
        return null;
    }
    $metodo = 'aes-256-cbc';
    $iv_length = openssl_cipher_iv_length($metodo);
    if (strlen($datos) <= $iv_length) {
        return null;
    }
    $iv = substr($datos, 0, $iv_length);
    $cifrado = substr($datos, $iv_length);
    $plano = openssl_decrypt($cifrado, $metodo, $clave, 0, $iv);
    return ($plano === false) ? null : $plano;
}

function cifrar_con_clave($texto_plano, $clave) {
    $metodo = 'aes-256-cbc';
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($metodo));
    return base64_encode($iv . openssl_encrypt($texto_plano, $metodo, $clave, 0, $iv));
}

$stmt = $conn->query("SELECT id, nombre, 2fa_secret FROM usuarios WHERE 2fa_activo = 1 AND 2fa_secret IS NOT NULL");
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
$sin_secreto = (int)$conn->query("SELECT COUNT(*) FROM usuarios WHERE 2fa_activo = 1 AND 2fa_secret IS NULL")->fetchColumn();

if (count($usuarios) === 0) {
    echo "No hay usuarios con secreto 2FA que migrar.\n";
    if ($sin_secreto > 0) {
        echo "Aviso: $sin_secreto usuario(s) con 2FA activo pero sin secreto (no se tocan).\n";
    }
    exit(0);
}

$migrados = [];
$fallos = [];
foreach ($usuarios as $u) {
    $plano = descifrar_con_clave($u['2fa_secret'], $clave_vieja);
    if ($plano === null || $plano === '') {
        $fallos[] = $u;
        continue;
    }
    $nuevo_cifrado = cifrar_con_clave($plano, $clave_nueva);
    $verificado = descifrar_con_clave($nuevo_cifrado, $clave_nueva);
    if ($verificado !== $plano) {
        $fallos[] = $u;
        continue;
    }
    $migrados[] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'nuevo' => $nuevo_cifrado];
}

foreach ($migrados as $m) {
    echo "OK usuario {$m['id']} ({$m['nombre']})\n";
}
foreach ($fallos as $f) {
    echo "FALLO usuario {$f['id']} ({$f['nombre']}): no se pudo descifrar con la clave vieja.\n";
}

if (count($fallos) > 0) {
    echo "Migración CANCELADA: " . count($fallos) . " usuario(s) fallaron. No se escribió nada.\n";
    exit(1);
}

if ($probar) {
    echo "MODO PRUEBA: " . count($migrados) . " usuario(s) se migrarían. No se escribió nada.\n";
    exit(0);
}

$conn->beginTransaction();
try {
    $upd = $conn->prepare("UPDATE usuarios SET 2fa_secret = ? WHERE id = ?");
    foreach ($migrados as $m) {
        $upd->execute([$m['nuevo'], $m['id']]);
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error al escribir: " . $e->getMessage() . ". Se revirtieron los cambios.\n";
    exit(1);
}

echo "Migración COMPLETA: " . count($migrados) . " usuario(s) migrados.\n";
echo "Ahora reemplace app/config/master_key.php por app/config/master_key_nueva.php y reinicie Apache.\n";
