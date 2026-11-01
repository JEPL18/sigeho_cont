<?php
class Seccion {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT * FROM secciones ORDER BY trayecto ASC, trimestre ASC, codigo ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function filtrar($trayecto = 0, $trimestre = 0) {
        $trayecto = (int)$trayecto;
        $trimestre = (int)$trimestre;
        $cond = [];
        $params = [];
        if ($trayecto >= 1 && $trayecto <= 4) { $cond[] = "trayecto = ?"; $params[] = $trayecto; }
        if ($trimestre >= 1 && $trimestre <= 3) { $cond[] = "trimestre = ?"; $params[] = $trimestre; }
        if (empty($cond)) {
            return $this->all();
        }
        $stmt = $this->conn->prepare("SELECT * FROM secciones WHERE " . implode(" AND ", $cond) . " ORDER BY trayecto ASC, trimestre ASC, codigo ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function sugerirCantidad($matricula, $capacidad) {
        $matricula = (int)$matricula;
        $capacidad = (int)$capacidad;
        if ($matricula <= 0 || $capacidad <= 0) {
            return null;
        }
        $n = (int)ceil($matricula / $capacidad);
        $base = (int)floor($matricula / $n);
        $resto = $matricula - ($base * $n);
        $distribucion = [];
        for ($i = 0; $i < $n; $i++) {
            $distribucion[] = $base + ($i < $resto ? 1 : 0);
        }
        return ['secciones' => $n, 'distribucion' => $distribucion];
    }

    public function crearSugeridas($distribucion, $trayecto, $trimestre, $prefijo) {
        $creadas = [];
        $n = 1;
        foreach ($distribucion as $alumnos) {
            do {
                $codigo = $prefijo . '-' . str_pad($n, 2, '0', STR_PAD_LEFT);
                $n++;
            } while ($this->existeCodigo($codigo));
            $this->create($codigo, $trayecto, $trimestre, $alumnos);
            $creadas[] = $codigo . ' (' . $alumnos . ' alumnos)';
        }
        return $creadas;
    }
}
