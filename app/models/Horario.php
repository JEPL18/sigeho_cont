<?php
// app/models/Horario.php — Consultas de la tabla `horarios`,
// incluyendo la consulta con JOINs, el ordenamiento por día y el
// algoritmo anti-choques (tal cual estaban en modules/horarios/).

class Horario {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    /**
     * Listado con datos relacionados (misma consulta del index original).
     */
    public function allConDetalle() {
        $sql = "SELECT h.*,
                       s.codigo AS seccion_cod, s.trayecto, s.trimestre,
                       m.nombre AS materia_nombre,
                       p.titulo AS profesor_titulo,
                       p.nombre AS profesor_nombre,
                       a.codigo AS aula_cod
                FROM horarios h
                INNER JOIN secciones s ON h.seccion_id = s.id
                INNER JOIN materias m ON h.materia_id = m.id
                INNER JOIN profesores p ON h.profesor_id = p.id
                INNER JOIN aulas a ON h.aula_id = a.id
                ORDER BY h.dia_semana, h.hora_inicio ASC";

        $horarios = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        usort($horarios, [self::class, 'ordenarDias']);
        return $horarios;
    }

    /**
     * Ordena Lunes..Sábado y luego por hora (función ordenarDias del original).
     */
    public static function ordenarDias($a, $b) {
        $dias = ['Lunes'=>1, 'Martes'=>2, 'Miercoles'=>3, 'Jueves'=>4, 'Viernes'=>5, 'Sabado'=>6];
        if ($dias[$a['dia_semana']] == $dias[$b['dia_semana']]) {
            return strtotime($a['hora_inicio']) - strtotime($b['hora_inicio']);
        }
        return $dias[$a['dia_semana']] - $dias[$b['dia_semana']];
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM horarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin) {
        $sql = "INSERT INTO horarios (seccion_id, materia_id, profesor_id, aula_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin]);
    }

    public function update($id, $seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin) {
        $sql = "UPDATE horarios SET seccion_id=?, materia_id=?, profesor_id=?, aula_id=?, dia_semana=?, hora_inicio=?, hora_fin=? WHERE id=?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin, $id]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM horarios WHERE id = ?");
        $stmt->execute([$id]);
    }

    /**
     * Recopila estado del profesor, estado/capacidad del aula y
     * matrícula de la sección (consulta de subselects del original).
     */
    public function validarRecursos($profesor_id, $aula_id, $seccion_id) {
        $stmt_check = $this->conn->prepare("SELECT
            (SELECT estado FROM profesores WHERE id = ?) as prof_estado,
            (SELECT estado FROM aulas WHERE id = ?) as aula_estado,
            (SELECT capacidad FROM aulas WHERE id = ?) as aula_cap,
            (SELECT cantidad_alumnos FROM secciones WHERE id = ?) as sec_alumn
        ");
        $stmt_check->execute([$profesor_id, $aula_id, $aula_id, $seccion_id]);
        return $stmt_check->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Algoritmo anti-choques desglosado (Aula, Profesor, Sección).
     * Devuelve ['error' => ..., 'detalle' => ...] si hay choque, o null.
     * $excluirId se usa en edición para no chocar consigo mismo.
     * Los mensajes son los mismos del proyecto original.
     */
    public function detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id, $excluirId = null, $esEdicion = false) {
        $excluir = ($excluirId !== null) ? " AND id != ?" : "";

        // A. Revisar Choque de Aula
        $sql_aula = "SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND aula_id = ?" . $excluir;
        $stmt_aula = $this->conn->prepare($sql_aula);
        $params = [$dia_semana, $hora_fin, $hora_inicio, $aula_id];
        if ($excluirId !== null) { $params[] = $excluirId; }
        $stmt_aula->execute($params);
        if ($stmt_aula->rowCount() > 0) {
            return [
                'error'   => "<strong>¡Choque de Aula!</strong> Esa aula ya está reservada en ese horario.",
                'detalle' => "Choque de Aula: El aula ya está ocupada."
            ];
        }

        // B. Revisar Choque de Profesor
        $sql_prof = "SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND profesor_id = ?" . $excluir;
        $stmt_prof = $this->conn->prepare($sql_prof);
        $params = [$dia_semana, $hora_fin, $hora_inicio, $profesor_id];
        if ($excluirId !== null) { $params[] = $excluirId; }
        $stmt_prof->execute($params);
        if ($stmt_prof->rowCount() > 0) {
            if ($esEdicion) {
                $error = "<strong>¡Choque de Docente!</strong> Este profesor ya imparte clases a esa hora.";
            } else {
                $error = "<strong>¡Choque de Docente!</strong> Este profesor ya imparte clases a esa hora en otra aula o sección.";
            }
            return [
                'error'   => $error,
                'detalle' => "Choque de Profesor: Docente ya ocupado."
            ];
        }

        // C. Revisar Choque de Sección
        $sql_sec = "SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND seccion_id = ?" . $excluir;
        $stmt_sec = $this->conn->prepare($sql_sec);
        $params = [$dia_semana, $hora_fin, $hora_inicio, $seccion_id];
        if ($excluirId !== null) { $params[] = $excluirId; }
        $stmt_sec->execute($params);
        if ($stmt_sec->rowCount() > 0) {
            if ($esEdicion) {
                $error = "<strong>¡Choque de Sección!</strong> Los alumnos de esta sección ya tienen clase a esa hora.";
            } else {
                $error = "<strong>¡Choque de Sección!</strong> Los alumnos de esta sección ya tienen otra materia asignada en esa hora.";
            }
            return [
                'error'   => $error,
                'detalle' => "Choque de Sección: Los alumnos ya tienen clases."
            ];
        }

        return null;
    }

    /**
     * Bloques de una sección para el "algoritmo SIACE" del dashboard.
     * Devuelve las filas crudas; el controlador arma la matriz.
     */
    public function bloquesPorSeccion($seccion_id, $turno) {
        $sql_horario = "SELECT h.*, m.nombre AS materia, p.titulo, p.nombre AS profesor, a.codigo AS aula
                        FROM horarios h
                        INNER JOIN materias m ON h.materia_id = m.id
                        INNER JOIN profesores p ON h.profesor_id = p.id
                        INNER JOIN aulas a ON h.aula_id = a.id
                        WHERE h.seccion_id = :sec_id ";

        if ($turno == 'MATUTINO') {
            $sql_horario .= " AND h.hora_inicio < '12:00:00' ";
        } elseif ($turno == 'VESPERTINO') {
            $sql_horario .= " AND h.hora_inicio >= '12:00:00' AND h.hora_inicio < '18:00:00' ";
        } elseif ($turno == 'NOCTURNO') {
            $sql_horario .= " AND h.hora_inicio >= '18:00:00' ";
        }

        $sql_horario .= " ORDER BY h.hora_inicio ASC";
        $stmt = $this->conn->prepare($sql_horario);
        $stmt->execute(['sec_id' => $seccion_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
