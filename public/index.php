<?php
// public/index.php — Front Controller de SIGEHO-CONT (MVC)
//
// Todo el tráfico web entra por aquí. El document root del servidor
// debe apuntar a la carpeta public/.

// --- PROTECCIÓN DE COOKIES (HttpOnly y SameSite) ---
// (Antes estaba en index.php del login; ahora aplica a todas las páginas)
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
// ---------------------------------------------------

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

// URL base para generar enlaces y rutas de assets (funciona con o sin subcarpeta).
$__base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', ($__base === '' ? '/' : $__base . '/'));
unset($__base);

require APP_PATH . '/config/database.php';

// Autocarga simple: core, modelos y controladores.
spl_autoload_register(function ($class) {
    foreach ([APP_PATH . '/core/', APP_PATH . '/models/', APP_PATH . '/controllers/'] as $dir) {
        $file = $dir . $class . '.php';
        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

$router = new Router($conn);
$router->dispatch();
