<?php
// app/models/Materia.php — Consultas de la tabla `materias`.

class Materia {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM materias ORDER BY trayecto ASC, nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM materias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function catalogo() {
        return $this->conn->query("SELECT id, nombre FROM materias")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existeCodigo($codigo, $excluirId = null) {
        if ($excluirId === null) {
            $check = $this->conn->prepare("SELECT id FROM materias WHERE codigo = ?");
            $check->execute([$codigo]);
        } else {
            $check = $this->conn->prepare("SELECT id FROM materias WHERE codigo = ? AND id != ?");
            $check->execute([$codigo, $excluirId]);
        }
        return $check->rowCount() > 0;
    }

    public function create($codigo, $nombre, $trayecto, $horas_semanales) {
        $stmt = $this->conn->prepare("INSERT INTO materias (codigo, nombre, trayecto, horas_semanales) VALUES (?, ?, ?, ?)");
        $stmt->execute([$codigo, $nombre, $trayecto, $horas_semanales]);
    }

    public function update($id, $codigo, $nombre, $trayecto, $horas_semanales) {
        $stmt = $this->conn->prepare("UPDATE materias SET codigo=?, nombre=?, trayecto=?, horas_semanales=? WHERE id=?");
        $stmt->execute([$codigo, $nombre, $trayecto, $horas_semanales, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM materias WHERE id = ?");
        $stmt->execute([$id]);
    }
}
