<?php
// app/core/Controller.php — Controlador base.
//
// Provee renderizado de vistas (con o sin layout), redirecciones y
// los candados de autenticación/rol que el proyecto original repetía
// en cada archivo.

class Controller {
    protected $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Renderiza una vista dentro del layout (header + footer).
     * Las variables de $data quedan disponibles en la vista.
     */
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

    /**
     * Renderiza una página HTML completa sin layout
     * (login, verificación 2FA, recuperación de clave).
     */
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

    /**
     * Genera la URL de una ruta interna.
     * Ej: $this->url('horarios/nuevo')  |  $this->url('horarios/editar', ['id' => 5])
     */
    protected function url($ruta, $params = []) {
        $url = BASE_URL . ltrim($ruta, '/');
        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    /**
     * Genera la URL de un recurso estático bajo public/.
     * Ej: $this->asset('assets/img/logo_conta.jpeg')
     */
    protected function asset($ruta) {
        return BASE_URL . ltrim($ruta, '/');
    }

    protected function redirect($ruta, $params = []) {
        header("Location: " . $this->url($ruta, $params));
        exit();
    }

    /** Candado: requiere sesión iniciada. */
    protected function requireLogin() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('auth/login');
        }
    }

    /**
     * Candado: requiere sesión iniciada + rol administrador.
     * $fallback es la ruta de destino si no cumple (el proyecto original
     * redirigía al index del módulo, excepto usuarios y configuración
     * que redirigían al dashboard).
     */
    protected function requireAdmin($fallback = 'dashboard') {
        if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
            $this->redirect($fallback);
        }
    }
}
