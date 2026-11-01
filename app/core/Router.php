<?php
class Router {
    protected $conn;

    protected $controladores = [
        'auth'          => 'AuthController',
        'dashboard'     => 'DashboardController',
        'profesores'    => 'ProfesorController',
        'materias'      => 'MateriaController',
        'secciones'     => 'SeccionController',
        'aulas'         => 'AulaController',
        'horarios'      => 'HorarioController',
        'usuarios'      => 'UsuarioController',
        'configuracion' => 'ConfiguracionController',
        'ayuda'         => 'AyudaController',
        'bitacora'      => 'BitacoraController',
    ];

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function dispatch() {
        $ruta = isset($_GET['r']) ? trim($_GET['r'], '/') : '';

        if ($ruta === '') {
            if (isset($_SESSION['usuario_id'])) {
                $this->ir('dashboard');
            }
            $this->ir('auth/login');
        }

        $partes = explode('/', $ruta);
        $clave  = strtolower($partes[0]);
        $accion = isset($partes[1]) && $partes[1] !== '' ? $partes[1] : 'index';

        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $accion)) {
            $this->noEncontrado();
        }

        if (!isset($this->controladores[$clave])) {
            $this->noEncontrado();
        }

        $clase = $this->controladores[$clave];
        if (!class_exists($clase)) {
            $this->noEncontrado();
        }

        $controlador = new $clase($this->conn);
        if (!method_exists($controlador, $accion) || !is_callable([$controlador, $accion])) {
            $this->noEncontrado();
        }

        $controlador->$accion();
    }

    protected function ir($ruta) {
        header("Location: " . BASE_URL . ltrim($ruta, '/'));
        exit();
    }

    protected function noEncontrado() {
        http_response_code(404);
        die("Página no encontrada.");
    }
}
