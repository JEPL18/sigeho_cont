<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 900);
session_start();

define('BASE_PATH', dirname(__DIR__));

$candidato_app = __DIR__ . '/app';
define('APP_PATH', is_dir($candidato_app) ? $candidato_app : BASE_PATH . '/app');
unset($candidato_app);

define('PUBLIC_PATH', __DIR__);

$__base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
define('BASE_URL', ($__base === '' ? '/' : $__base . '/'));
unset($__base);

define('SESION_MAX_INACTIVIDAD', 900);

if (isset($_SESSION['usuario_id'])) {
    if (isset($_SESSION['ultima_actividad']) && (time() - $_SESSION['ultima_actividad'] > SESION_MAX_INACTIVIDAD)) {
        session_unset();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        session_destroy();
        header('Location: ' . BASE_URL . 'auth/login?msg=sesion_expirada');
        exit;
    }
    $_SESSION['ultima_actividad'] = time();
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
}

require APP_PATH . '/config/database.php';
csrf_token();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_enviado = $_POST['csrf_token'] ?? '';
    if (!is_string($token_enviado) || !hash_equals($_SESSION['csrf_token'], $token_enviado)) {
        http_response_code(403);
        die('Sesión expirada o solicitud no válida. Vuelve atrás y envía el formulario de nuevo.');
    }
}

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
