<?php
// app/models/Configuracion.php — Parámetros del sistema (tabla `configuracion`).

class Configuracion {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function get($parametro) {
        $stmt = $this->conn->prepare("SELECT valor FROM configuracion WHERE parametro = ?");
        $stmt->execute([$parametro]);
        return $stmt->fetchColumn();
    }

    public function set($parametro, $valor) {
        $stmt = $this->conn->prepare("UPDATE configuracion SET valor = ? WHERE parametro = ?");
        $stmt->execute([$valor, $parametro]);
    }

    public function fechaInicioLapso() {
        return $this->get('fecha_inicio_lapso');
    }

    public function guardarFechaInicioLapso($fecha) {
        $this->set('fecha_inicio_lapso', $fecha);
    }
}
