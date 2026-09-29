<?php
// app/models/Bitacora.php — Auditoría (tabla `bitacora`).

class Bitacora {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function registrar($usuario_id, $accion) {
        $stmt = $this->conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $accion]);
    }
}
