<?php
// app/models/Aula.php — Consultas de la tabla `aulas`.

class Aula {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM aulas ORDER BY codigo ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM aulas WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function catalogoOperativas() {
        return $this->conn->query("SELECT id, codigo, capacidad FROM aulas WHERE estado = 'OPERATIVA'")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function catalogoTodas() {
        return $this->conn->query("SELECT id, codigo, capacidad FROM aulas")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeCodigo($codigo, $excluirId = null) {
        if ($excluirId === null) {
            $check = $this->conn->prepare("SELECT id FROM aulas WHERE codigo = ?");
            $check->execute([$codigo]);
        } else {
            $check = $this->conn->prepare("SELECT id FROM aulas WHERE codigo = ? AND id != ?");
            $check->execute([$codigo, $excluirId]);
        }
        return $check->rowCount() > 0;
    }

    public function create($codigo, $capacidad, $estado) {
        $stmt = $this->conn->prepare("INSERT INTO aulas (codigo, capacidad, estado) VALUES (?, ?, ?)");
        $stmt->execute([$codigo, $capacidad, $estado]);
    }

    public function update($id, $codigo, $capacidad, $estado) {
        $stmt = $this->conn->prepare("UPDATE aulas SET codigo=?, capacidad=?, estado=? WHERE id=?");
        $stmt->execute([$codigo, $capacidad, $estado, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM aulas WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function contarOperativas() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM aulas WHERE estado = 'OPERATIVA'")->fetchColumn();
    }

    public function contarInoperativas() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM aulas WHERE estado != 'OPERATIVA'")->fetchColumn();
    }
}
