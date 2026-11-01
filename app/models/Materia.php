<?php
class Materia {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM materias ORDER BY trayecto ASC, nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrar($trayecto = 0) {
        $trayecto = (int)$trayecto;
        if ($trayecto >= 1 && $trayecto <= 4) {
            $stmt = $this->conn->prepare("SELECT * FROM materias WHERE trayecto = ? ORDER BY trayecto ASC, nombre ASC");
            $stmt->execute([$trayecto]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        return $this->all();
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
        return $this->conn->lastInsertId();
    }

    public function update($id, $codigo, $nombre, $trayecto, $horas_semanales) {
        $stmt = $this->conn->prepare("UPDATE materias SET codigo=?, nombre=?, trayecto=?, horas_semanales=? WHERE id=?");
        $stmt->execute([$codigo, $nombre, $trayecto, $horas_semanales, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM materia_prelacion WHERE materia_id = ?");
        $stmt->execute([$id]);
        $stmt = $this->conn->prepare("DELETE FROM materias WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function prelacionesDe($materia_id) {
        $stmt = $this->conn->prepare("SELECT prelacion_id FROM materia_prelacion WHERE materia_id = ?");
        $stmt->execute([$materia_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'prelacion_id');
    }

    public function mapaPrelaciones() {
        $mapa = [];
        $filas = $this->conn->query("SELECT mp.materia_id, m.nombre FROM materia_prelacion mp INNER JOIN materias m ON mp.prelacion_id = m.id ORDER BY m.nombre ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $f) {
            $mapa[$f['materia_id']][] = $f['nombre'];
        }
        return $mapa;
    }

    public function sincronizarPrelaciones($materia_id, $ids) {
        $del = $this->conn->prepare("DELETE FROM materia_prelacion WHERE materia_id = ?");
        $del->execute([$materia_id]);
        $ins = $this->conn->prepare("INSERT INTO materia_prelacion (materia_id, prelacion_id) VALUES (?, ?)");
        foreach ($ids as $pid) {
            $ins->execute([$materia_id, (int)$pid]);
        }
    }

    public function esPrelacionDe($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM materia_prelacion WHERE prelacion_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn();
    }

    public function tieneHorarios($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM horarios WHERE materia_id = ?");
        $stmt->execute([$id]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function formaCiclo($materia_id, $nuevas) {
        $ady = [];
        $filas = $this->conn->query("SELECT materia_id, prelacion_id FROM materia_prelacion")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($filas as $f) {
            if ($f['materia_id'] == $materia_id) { continue; }
            $ady[$f['materia_id']][] = $f['prelacion_id'];
        }
        foreach ($nuevas as $p) {
            $ady[$materia_id][] = (int)$p;
        }
        $visitados = [];
        $pila = [$materia_id];
        while (!empty($pila)) {
            $actual = array_pop($pila);
            if (isset($visitados[$actual])) { continue; }
            $visitados[$actual] = true;
            foreach ($ady[$actual] ?? [] as $sig) {
                if ($sig == $materia_id) { return true; }
                $pila[] = $sig;
            }
        }
        return false;
    }
}
