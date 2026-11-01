<?php
class Profesor {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM profesores ORDER BY nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrar($estado = '') {
        if ($estado === 'ACTIVO' || $estado === 'INACTIVO') {
            $stmt = $this->conn->prepare("SELECT * FROM profesores WHERE estado = ? ORDER BY nombre ASC");
            $stmt->execute([$estado]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->all();
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

    public function create($cedula, $nombre, $estado) {
        $stmt = $this->conn->prepare("INSERT INTO profesores (cedula, nombre, estado) VALUES (?, ?, ?)");
        $stmt->execute([$cedula, $nombre, $estado]);
        return $this->conn->lastInsertId();
    }

    public function update($id, $cedula, $nombre, $estado) {
        $stmt = $this->conn->prepare("UPDATE profesores SET cedula=?, nombre=?, estado=? WHERE id=?");
        $stmt->execute([$cedula, $nombre, $estado, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM profesor_disponibilidad WHERE profesor_id = ?");
        $stmt->execute([$id]);
        $stmt = $this->conn->prepare("DELETE FROM profesor_materia WHERE profesor_id = ?");
        $stmt->execute([$id]);
        $stmt = $this->conn->prepare("DELETE FROM profesores WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function disponibilidadDe($profesor_id) {
        $stmt = $this->conn->prepare("SELECT dia_semana, turno FROM profesor_disponibilidad WHERE profesor_id = ? ORDER BY dia_semana, turno");
        $stmt->execute([$profesor_id]);
        $pares = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
            $pares[] = $f['dia_semana'] . '|' . $f['turno'];
        }
        return $pares;
    }

    public function materiasDe($profesor_id) {
        $stmt = $this->conn->prepare("SELECT materia_id FROM profesor_materia WHERE profesor_id = ?");
        $stmt->execute([$profesor_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'materia_id');
    }

    public function mapaDisponibilidad() {
        $mapa = [];
        $filas = $this->conn->query("SELECT profesor_id, dia_semana, turno FROM profesor_disponibilidad ORDER BY dia_semana, turno")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $f) {
            $mapa[$f['profesor_id']][] = $f['dia_semana'] . ' ' . $f['turno'];
        }
        return $mapa;
    }

    public function mapaMaterias() {
        $mapa = [];
        $filas = $this->conn->query("SELECT pm.profesor_id, m.nombre FROM profesor_materia pm INNER JOIN materias m ON pm.materia_id = m.id ORDER BY m.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $f) {
            $mapa[$f['profesor_id']][] = $f['nombre'];
        }
        return $mapa;
    }

    public function sincronizarDisponibilidad($profesor_id, $pares) {
        $del = $this->conn->prepare("DELETE FROM profesor_disponibilidad WHERE profesor_id = ?");
        $del->execute([$profesor_id]);
        $ins = $this->conn->prepare("INSERT INTO profesor_disponibilidad (profesor_id, dia_semana, turno) VALUES (?, ?, ?)");
        foreach ($pares as $par) {
            list($dia, $turno) = explode('|', $par);
            $ins->execute([$profesor_id, $dia, $turno]);
        }
    }

    public function sincronizarMaterias($profesor_id, $ids) {
        $del = $this->conn->prepare("DELETE FROM profesor_materia WHERE profesor_id = ?");
        $del->execute([$profesor_id]);
        $ins = $this->conn->prepare("INSERT INTO profesor_materia (profesor_id, materia_id) VALUES (?, ?)");
        foreach ($ids as $mid) {
            $ins->execute([$profesor_id, (int)$mid]);
        }
    }

    public function tieneDisponibilidad($profesor_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM profesor_disponibilidad WHERE profesor_id = ?");
        $stmt->execute([$profesor_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function tieneMaterias($profesor_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM profesor_materia WHERE profesor_id = ?");
        $stmt->execute([$profesor_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function disponibilidadCubre($profesor_id, $dia_semana, $turno) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM profesor_disponibilidad WHERE profesor_id = ? AND dia_semana = ? AND turno = ?");
        $stmt->execute([$profesor_id, $dia_semana, $turno]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function dictaMateria($profesor_id, $materia_id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM profesor_materia WHERE profesor_id = ? AND materia_id = ?");
        $stmt->execute([$profesor_id, $materia_id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function tieneHorarios($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM horarios WHERE profesor_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function contarActivos() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM profesores WHERE estado = 'ACTIVO'")->fetchColumn();
    }

    public function contarInactivos() {
        return (int)$this->conn->query("SELECT COUNT(*) FROM profesores WHERE estado != 'ACTIVO'")->fetchColumn();
    }
}
