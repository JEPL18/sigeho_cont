<?php
// app/models/Profesor.php — Consultas de la tabla `profesores`.

class Profesor {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM profesores ORDER BY nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM profesores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function catalogoActivos() {
        return $this->conn->query("SELECT id, nombre FROM profesores WHERE estado = 'ACTIVO'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoTodos() {
        return $this->conn->query("SELECT id, nombre FROM profesores")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeCedula($cedula, $excluirId = null) {
        if ($excluirId === null) {
            $check = $this->conn->prepare("SELECT id FROM profesores WHERE cedula = ?");
            $check->execute([$cedula]);
        } else {
            $check = $this->conn->prepare("SELECT id FROM profesores WHERE cedula = ? AND id != ?");
            $check->execute([$cedula, $excluirId]);
        }
        return $check->rowCount() > 0;
    }

    public function create($cedula, $titulo, $nombre, $estado) {
        $stmt = $this->conn->prepare("INSERT INTO profesores (cedula, titulo, nombre, estado) VALUES (?, ?, ?, ?)");
        $stmt->execute([$cedula, $titulo, $nombre, $estado]);
    }

    public function update($id, $cedula, $titulo, $nombre, $estado) {
        $stmt = $this->conn->prepare("UPDATE profesores SET cedula=?, titulo=?, nombre=?, estado=? WHERE id=?");
        $stmt->execute([$cedula, $titulo, $nombre, $estado, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM profesores WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function contarActivos() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM profesores WHERE estado = 'ACTIVO'")->fetchColumn();
    }

    public function contarInactivos() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM profesores WHERE estado != 'ACTIVO'")->fetchColumn();
    }
}
