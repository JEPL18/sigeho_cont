<?php
// app/models/Seccion.php — Consultas de la tabla `secciones`.

class Seccion {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM secciones ORDER BY trayecto ASC, trimestre ASC, codigo ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM secciones WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function catalogo() {
        return $this->conn->query("SELECT id, codigo, trayecto, trimestre, cantidad_alumnos FROM secciones")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listaParaDashboard() {
        return $this->conn->query("SELECT id, codigo, trayecto, trimestre FROM secciones ORDER BY trayecto ASC, trimestre ASC, codigo ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeCodigo($codigo, $excluirId = null) {
        if ($excluirId === null) {
            $check = $this->conn->prepare("SELECT id FROM secciones WHERE codigo = ?");
            $check->execute([$codigo]);
        } else {
            $check = $this->conn->prepare("SELECT id FROM secciones WHERE codigo = ? AND id != ?");
            $check->execute([$codigo, $excluirId]);
        }
        return $check->rowCount() > 0;
    }

    public function create($codigo, $trayecto, $trimestre, $cantidad_alumnos) {
        $stmt = $this->conn->prepare("INSERT INTO secciones (codigo, trayecto, trimestre, cantidad_alumnos) VALUES (?, ?, ?, ?)");
        $stmt->execute([$codigo, $trayecto, $trimestre, $cantidad_alumnos]);
    }

    public function update($id, $codigo, $trayecto, $trimestre, $cantidad_alumnos) {
        $stmt = $this->conn->prepare("UPDATE secciones SET codigo=?, trayecto=?, trimestre=?, cantidad_alumnos=? WHERE id=?");
        $stmt->execute([$codigo, $trayecto, $trimestre, $cantidad_alumnos, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM secciones WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function contar() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM secciones")->fetchColumn();
    }
}
