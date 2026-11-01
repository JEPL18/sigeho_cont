<?php
class Bitacora {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function registrar($usuario_id, $accion) {
        $stmt = $this->conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
        $stmt->execute([$usuario_id, $accion]);
    }

    public function recientes($limite = 100) {
        $stmt = $this->conn->prepare("SELECT b.*, u.nombre AS usuario FROM bitacora b LEFT JOIN usuarios u ON u.id = b.usuario_id ORDER BY b.fecha DESC, b.id DESC LIMIT ?");
        $stmt->bindValue(1, (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function vaciar() {
        $this->conn->exec("DELETE FROM bitacora");
    }
}
