<?php
class Horario {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    
    public function allConDetalle() {
        $sql = "SELECT h.*,
                       s.codigo AS seccion_cod, s.trayecto, s.trimestre,
                       m.nombre AS materia_nombre,
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

    public function filtrarConDetalle($seccion_id = 0, $dia = '', $turno = '') {
        $cond = [];
        $params = [];

        $seccion_id = (int)$seccion_id;
        if ($seccion_id > 0) { $cond[] = "h.seccion_id = ?"; $params[] = $seccion_id; }

        $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        if (in_array($dia, $dias, true)) { $cond[] = "h.dia_semana = ?"; $params[] = $dia; }

        if ($turno === 'MATUTINO') { $cond[] = "h.hora_inicio < '12:00:00'"; }
        elseif ($turno === 'VESPERTINO') { $cond[] = "h.hora_inicio >= '12:00:00' AND h.hora_inicio < '18:00:00'"; }
        elseif ($turno === 'NOCTURNO') { $cond[] = "h.hora_inicio >= '18:00:00'"; }

        if (empty($cond)) {
            return $this->allConDetalle();
        }

        $sql = "SELECT h.*,
                       s.codigo AS seccion_cod, s.trayecto, s.trimestre,
                       m.nombre AS materia_nombre,
                       p.nombre AS profesor_nombre,
                       a.codigo AS aula_cod
                FROM horarios h
                INNER JOIN secciones s ON h.seccion_id = s.id
                INNER JOIN materias m ON h.materia_id = m.id
                INNER JOIN profesores p ON h.profesor_id = p.id
                INNER JOIN aulas a ON h.aula_id = a.id
                WHERE " . implode(" AND ", $cond) . "
                ORDER BY h.dia_semana, h.hora_inicio ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        usort($horarios, [self::class, 'ordenarDias']);
        return $horarios;
    }

    
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

    
    public function detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id, $excluirId = null, $esEdicion = false) {
        $excluir = ($excluirId !== null) ? " AND id != ?" : "";

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

    
    public function bloquesPorSeccion($seccion_id, $turno) {
        $sql_horario = "SELECT h.*, m.nombre AS materia, p.nombre AS profesor, a.codigo AS aula
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

    public function bloquesPorProfesor($profesor_id, $turno) {
        $sql_horario = "SELECT h.*, m.nombre AS materia, s.codigo AS seccion, a.codigo AS aula
                        FROM horarios h
                        INNER JOIN materias m ON h.materia_id = m.id
                        INNER JOIN secciones s ON h.seccion_id = s.id
                        INNER JOIN aulas a ON h.aula_id = a.id
                        WHERE h.profesor_id = :prof_id ";

        if ($turno == 'MATUTINO') {
            $sql_horario .= " AND h.hora_inicio < '12:00:00' ";
        } elseif ($turno == 'VESPERTINO') {
            $sql_horario .= " AND h.hora_inicio >= '12:00:00' AND h.hora_inicio < '18:00:00' ";
        } elseif ($turno == 'NOCTURNO') {
            $sql_horario .= " AND h.hora_inicio >= '18:00:00' ";
        }

        $sql_horario .= " ORDER BY h.hora_inicio ASC";
        $stmt = $this->conn->prepare($sql_horario);
        $stmt->execute(['prof_id' => $profesor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function turnoDe($hora_inicio) {
        $hhmm = substr($hora_inicio, 0, 5);
        if ($hhmm < '12:00') { return 'MATUTINO'; }
        if ($hhmm < '18:00') { return 'VESPERTINO'; }
        return 'NOCTURNO';
    }

    private function minutosDe($hhmm) {
        $partes = explode(':', $hhmm);
        return ((int)$partes[0] * 60) + (int)$partes[1];
    }

    private function traslapa($hi, $hf, $ex_hi, $ex_hf) {
        return $hi < $ex_hf && $hf > $ex_hi;
    }

    public function huecosLibres($profesor_id, $materia_id, $seccion_id, $duracion_min, $dias = [], $max = 40, $desde_min = 420, $hasta_min = 1140) {
        $profesor = new Profesor($this->conn);
        $datos_prof = $profesor->find($profesor_id);
        if (!$datos_prof) {
            return ['error' => 'Profesor no encontrado.'];
        }
        if ($datos_prof['estado'] !== 'ACTIVO') {
            return ['error' => 'El profesor no está ACTIVO.'];
        }
        if ($profesor->tieneMaterias($profesor_id) && !$profesor->dictaMateria($profesor_id, $materia_id)) {
            return ['error' => 'El docente no dicta esa materia.'];
        }
        $matricula = 0;
        if ($seccion_id) {
            $seccion = new Seccion($this->conn);
            $datos_sec = $seccion->find($seccion_id);
            if (!$datos_sec) {
                return ['error' => 'Sección no encontrada.'];
            }
            $matricula = (int)$datos_sec['cantidad_alumnos'];
        }
        $aula = new Aula($this->conn);
        $aulas = [];
        foreach ($aula->catalogoOperativas() as $a) {
            if ((int)$a['capacidad'] >= $matricula) {
                $aulas[] = $a;
            }
        }
        $todos_dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        if (empty($dias)) {
            $dias = $todos_dias;
        }
        $tiene_disp = $profesor->tieneDisponibilidad($profesor_id);
        $disp_cache = [];
        $huecos = [];
        $total = 0;
        $stmt_dia = $this->conn->prepare("SELECT profesor_id, aula_id, seccion_id, hora_inicio, hora_fin FROM horarios WHERE dia_semana = ?");
        foreach ($dias as $dia) {
            $stmt_dia->execute([$dia]);
            $por_prof = [];
            $por_sec = [];
            $por_aula = [];
            foreach ($stmt_dia->fetchAll(PDO::FETCH_ASSOC) as $h) {
                $bloque = [$this->minutosDe(substr($h['hora_inicio'], 0, 5)), $this->minutosDe(substr($h['hora_fin'], 0, 5))];
                $por_prof[(int)$h['profesor_id']][] = $bloque;
                $por_sec[(int)$h['seccion_id']][] = $bloque;
                $por_aula[(int)$h['aula_id']][] = $bloque;
            }
            $inicio = $desde_min;
            while ($inicio + $duracion_min <= $hasta_min) {
                $fin = $inicio + $duracion_min;
                $hora_inicio = sprintf('%02d:%02d', intdiv($inicio, 60), $inicio % 60);
                $hora_fin = sprintf('%02d:%02d', intdiv($fin, 60), $fin % 60);
                if ($tiene_disp) {
                    $clave = $dia . '|' . $this->turnoDe($hora_inicio);
                    if (!isset($disp_cache[$clave])) {
                        $disp_cache[$clave] = $profesor->disponibilidadCubre($profesor_id, $dia, $this->turnoDe($hora_inicio));
                    }
                    if (!$disp_cache[$clave]) {
                        $inicio += 30;
                        continue;
                    }
                }
                $ocupado = false;
                foreach ([$por_prof[$profesor_id] ?? [], $seccion_id ? ($por_sec[$seccion_id] ?? []) : []] as $lista) {
                    foreach ($lista as $b) {
                        if ($this->traslapa($inicio, $fin, $b[0], $b[1])) {
                            $ocupado = true;
                            break 2;
                        }
                    }
                }
                if (!$ocupado) {
                    foreach ($aulas as $a) {
                        $choque_aula = false;
                        foreach ($por_aula[(int)$a['id']] ?? [] as $b) {
                            if ($this->traslapa($inicio, $fin, $b[0], $b[1])) {
                                $choque_aula = true;
                                break;
                            }
                        }
                        if (!$choque_aula) {
                            $total++;
                            if (count($huecos) < $max) {
                                $huecos[] = [
                                    'dia' => $dia,
                                    'hora_inicio' => $hora_inicio,
                                    'hora_fin' => $hora_fin,
                                    'aula_id' => (int)$a['id'],
                                    'aula' => $a['codigo'],
                                    'capacidad' => (int)$a['capacidad'],
                                ];
                            }
                        }
                    }
                }
                $inicio += 30;
            }
        }
        return ['huecos' => $huecos, 'total' => $total];
    }

    public function aplicarHueco($profesor_id, $materia_id, $seccion_id, $dia, $hora_inicio, $hora_fin, $aula_id) {
        $profesor = new Profesor($this->conn);
        $datos_prof = $profesor->find($profesor_id);
        if (!$datos_prof || $datos_prof['estado'] !== 'ACTIVO') {
            return 'El profesor no está ACTIVO.';
        }
        if ($profesor->tieneDisponibilidad($profesor_id) && !$profesor->disponibilidadCubre($profesor_id, $dia, $this->turnoDe($hora_inicio))) {
            return "El docente no tiene disponibilidad el $dia en el turno " . strtolower($this->turnoDe($hora_inicio)) . '.';
        }
        if ($profesor->tieneMaterias($profesor_id) && !$profesor->dictaMateria($profesor_id, $materia_id)) {
            return 'El docente no dicta esa materia.';
        }
        $recursos = $this->validarRecursos($profesor_id, $aula_id, $seccion_id);
        if ($recursos['aula_estado'] !== 'OPERATIVA') {
            return 'El aula no está OPERATIVA.';
        }
        if ((int)$recursos['aula_cap'] < (int)$recursos['sec_alumn']) {
            return 'La matrícula supera la capacidad del aula.';
        }
        $choque = $this->detectarChoque($dia, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id);
        if ($choque !== null) {
            return strip_tags($choque['detalle']);
        }
        $this->create($seccion_id, $materia_id, $profesor_id, $aula_id, $dia, $hora_inicio, $hora_fin);
        return null;
    }
}
