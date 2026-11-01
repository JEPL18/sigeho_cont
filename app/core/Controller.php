<?php
class Controller {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    
    protected function render($view, $data = []) {
        $conn    = $this->conn;
        $baseUrl = BASE_URL;
        $es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) == 'administrador');
        extract($data, EXTR_SKIP);

        $vista = APP_PATH . '/views/' . $view . '.php';
        if (!is_file($vista)) {
            http_response_code(404);
            die("Vista no encontrada: " . htmlspecialchars($view));
        }

        require APP_PATH . '/views/layouts/header.php';
        require $vista;
        require APP_PATH . '/views/layouts/footer.php';
    }

    
    protected function renderPage($view, $data = []) {
        $conn    = $this->conn;
        $baseUrl = BASE_URL;
        extract($data, EXTR_SKIP);

        $vista = APP_PATH . '/views/' . $view . '.php';
        if (!is_file($vista)) {
            http_response_code(404);
            die("Vista no encontrada: " . htmlspecialchars($view));
        }

        require $vista;
    }

    
    protected function url($ruta, $params = []) {
        $url = BASE_URL . ltrim($ruta, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    
    protected function asset($ruta) {
        return BASE_URL . ltrim($ruta, '/');
    }

    protected function redirect($ruta, $params = []) {
        header("Location: " . $this->url($ruta, $params));
        exit();
    }

    protected function requireLogin() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('auth/login');
        }
    }

    
    protected function requireAdmin($fallback = 'dashboard') {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('auth/login');
        }
        if (strtolower($_SESSION['rol']) != 'administrador') {
            http_response_code(403);
            $this->render('errores/403');
            exit;
        }
    }

    protected function bitacora($accion) {
        try {
            $bitacora = new Bitacora($this->conn);
            $bitacora->registrar($_SESSION['usuario_id'], $accion);
        } catch (Exception $e) {
        }
    }
}
