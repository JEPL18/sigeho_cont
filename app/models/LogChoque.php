<?php
// app/models/LogChoque.php — Auditoría de choques bloqueados (tabla `log_choques`).

class LogChoque {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function registrar($usuario_id, $intento_asignacion, $detalle_conflicto) {
        $sql = "INSERT INTO log_choques (usuario_id, intento_asignacion, detalle_conflicto) VALUES (?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$usuario_id, $intento_asignacion, $detalle_conflicto]);
    }

    public function recientes($limite = 50) {
        return $this->conn->query("SELECT * FROM log_choques ORDER BY fecha DESC LIMIT " . (int)$limite)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function contar() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM log_choques")->fetchColumn();
    }

    public function vaciar() {
        $this->conn->query("DELETE FROM log_choques");
    }
}
