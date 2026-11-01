<?php
class HorarioController extends Controller {

    public function index() {
        $this->requireLogin();

        $filtro_seccion = isset($_GET['seccion_id']) ? (int)$_GET['seccion_id'] : 0;
        $filtro_dia = isset($_GET['dia']) ? trim($_GET['dia']) : '';
        $filtro_turno = isset($_GET['turno']) ? strtoupper(trim($_GET['turno'])) : '';

        $horario = new Horario($this->conn);
        $horarios = $horario->filtrarConDetalle($filtro_seccion, $filtro_dia, $filtro_turno);
        $secciones = (new Seccion($this->conn))->all();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('horarios/index', [
            'horarios'       => $horarios,
            'msg'            => $msg,
            'secciones'      => $secciones,
            'filtro_seccion' => $filtro_seccion,
            'filtro_dia'     => $filtro_dia,
            'filtro_turno'   => $filtro_turno,
        ]);
    }

    public function porProfesor() {
        $this->requireLogin();

        $profesor = new Profesor($this->conn);
        $horario  = new Horario($this->conn);

        $profesores = $profesor->catalogoTodos();
        $profesor_seleccionado = isset($_GET['profesor_id']) ? (int)$_GET['profesor_id'] : null;
        $turno_seleccionado = isset($_GET['turno']) ? strtoupper($_GET['turno']) : '';

        $segmentos = [];
        $celdas = [];
        $ocupadas = [];
        $datos_profesor = null;

        if ($profesor_seleccionado) {
            $datos_profesor = $profesor->find($profesor_seleccionado);

            $filas = $horario->bloquesPorProfesor($profesor_seleccionado, $turno_seleccionado);

            $cortes = [];
            foreach ($filas as $row) {
                $cortes[$row['hora_inicio']] = true;
                $cortes[$row['hora_fin']] = true;
            }
            $cortes = array_keys($cortes);
            sort($cortes, SORT_STRING);
            $indice_corte = array_flip($cortes);

            for ($i = 0; $i + 1 < count($cortes); $i++) {
                $segmentos[] = [
                    'inicio' => $cortes[$i],
                    'fin'    => $cortes[$i + 1],
                    'texto'  => strtolower(date("h:ia", strtotime($cortes[$i]))) . " -- " . strtolower(date("h:ia", strtotime($cortes[$i + 1]))),
                ];
            }

            foreach ($filas as $row) {
                $i0 = $indice_corte[$row['hora_inicio']] ?? null;
                $i1 = $indice_corte[$row['hora_fin']] ?? null;
                if ($i0 === null || $i1 === null || $i1 <= $i0) {
                    continue;
                }
                $dia = $row['dia_semana'];
                if (isset($celdas[$i0][$dia]) || isset($ocupadas[$i0][$dia])) {
                    continue;
                }
                $celdas[$i0][$dia] = ['rowspan' => $i1 - $i0, 'clase' => $row];
                for ($j = $i0 + 1; $j < $i1; $j++) {
                    $ocupadas[$j][$dia] = true;
                }
            }
        }

        $this->render('horarios/por_profesor', [
            'profesores'            => $profesores,
            'profesor_seleccionado' => $profesor_seleccionado,
            'turno_seleccionado'    => $turno_seleccionado,
            'segmentos'             => $segmentos,
            'celdas'                => $celdas,
            'ocupadas'              => $ocupadas,
            'datos_profesor'        => $datos_profesor,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('horarios');

        $horario  = new Horario($this->conn);
        $seccion  = new Seccion($this->conn);
        $materia  = new Materia($this->conn);
        $profesor = new Profesor($this->conn);
        $aula     = new Aula($this->conn);
        $log      = new LogChoque($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $seccion_id = (int)$_POST['seccion_id'];
            $materia_id = (int)$_POST['materia_id'];
            $profesor_id = (int)$_POST['profesor_id'];
            $aula_id = (int)$_POST['aula_id'];
            $dia_semana = $_POST['dia_semana'];
            $hora_inicio = $_POST['hora_inicio'];
            $hora_fin = $_POST['hora_fin'];

            if (empty($seccion_id) || empty($materia_id) || empty($profesor_id) || empty($aula_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
                $error = "Todos los campos son obligatorios.";
            } elseif ($hora_inicio >= $hora_fin) {
                $error = "La hora de inicio debe ser anterior a la hora de fin.";
            } else {
                try {

                    $datos = $horario->validarRecursos($profesor_id, $aula_id, $seccion_id);

                    if ($datos['prof_estado'] != 'ACTIVO') {
                        $error = "El docente seleccionado no está ACTIVO.";
                    } elseif ($datos['aula_estado'] != 'OPERATIVA') {
                        $error = "El aula seleccionada no está OPERATIVA.";
                    } elseif ($datos['sec_alumn'] > $datos['aula_cap']) {
                        $error = "Capacidad excedida: El aula (".$datos['aula_cap']." alumnos) no soporta la matrícula (".$datos['sec_alumn']." alumnos).";
                    } elseif ($profesor->tieneDisponibilidad($profesor_id) && !$profesor->disponibilidadCubre($profesor_id, $dia_semana, $this->turnoDe($hora_inicio))) {
                        $error = "El docente no tiene disponibilidad el $dia_semana en el turno " . strtolower($this->turnoDe($hora_inicio)) . ".";
                    } elseif ($profesor->tieneMaterias($profesor_id) && !$profesor->dictaMateria($profesor_id, $materia_id)) {
                        $error = "El docente no dicta esa materia.";
                    } else {

                        $choque = $horario->detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id);

                        if ($choque) {

                            $intento = "Día: $dia_semana | Hora: $hora_inicio a $hora_fin";
                            $log->registrar($_SESSION['usuario_id'], $intento, $choque['detalle']);
                            $error = $choque['error'];
                        } else {

                            $horario->create($seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin);
                            $this->bitacora("Creó horario: " . $dia_semana . " " . $hora_inicio . " a " . $hora_fin . " (sección ID " . $seccion_id . ")");
                            $success = "Bloque de clase asignado correctamente.";
                        }
                    }
                } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
            }
        }

        $secciones  = $seccion->catalogo();
        $materias   = $materia->catalogo();
        $profesores = $profesor->catalogoActivos();
        $aulas      = $aula->catalogoOperativas();

        $this->render('horarios/form', [
            'modo'       => 'nuevo',
            'error'      => $error,
            'success'    => $success,
            'datos'      => null,
            'secciones'  => $secciones,
            'materias'   => $materias,
            'profesores' => $profesores,
            'aulas'      => $aulas,
        ]);
    }

    public function editar() {
        $this->requireAdmin('horarios');

        $horario  = new Horario($this->conn);
        $seccion  = new Seccion($this->conn);
        $materia  = new Materia($this->conn);
        $profesor = new Profesor($this->conn);
        $aula     = new Aula($this->conn);
        $log      = new LogChoque($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('horarios'); }
        $id = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $seccion_id = (int)$_POST['seccion_id'];
            $materia_id = (int)$_POST['materia_id'];
            $profesor_id = (int)$_POST['profesor_id'];
            $aula_id = (int)$_POST['aula_id'];
            $dia_semana = $_POST['dia_semana'];
            $hora_inicio = $_POST['hora_inicio'];
            $hora_fin = $_POST['hora_fin'];

            if (empty($seccion_id) || empty($materia_id) || empty($profesor_id) || empty($aula_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
                $error = "Todos los campos son obligatorios.";
            } elseif ($hora_inicio >= $hora_fin) {
                $error = "La hora de inicio debe ser anterior a la hora de fin.";
            } else {
                try {
                    $datos = $horario->validarRecursos($profesor_id, $aula_id, $seccion_id);

                    if ($datos['prof_estado'] != 'ACTIVO') {
                        $error = "El docente seleccionado no está ACTIVO.";
                    } elseif ($datos['aula_estado'] != 'OPERATIVA') {
                        $error = "El aula seleccionada no está OPERATIVA.";
                    } elseif ($datos['sec_alumn'] > $datos['aula_cap']) {
                        $error = "Capacidad excedida: El aula (".$datos['aula_cap']." alumnos) no soporta la matrícula (".$datos['sec_alumn']." alumnos).";
                    } elseif ($profesor->tieneDisponibilidad($profesor_id) && !$profesor->disponibilidadCubre($profesor_id, $dia_semana, $this->turnoDe($hora_inicio))) {
                        $error = "El docente no tiene disponibilidad el $dia_semana en el turno " . strtolower($this->turnoDe($hora_inicio)) . ".";
                    } elseif ($profesor->tieneMaterias($profesor_id) && !$profesor->dictaMateria($profesor_id, $materia_id)) {
                        $error = "El docente no dicta esa materia.";
                    } else {

                        $choque = $horario->detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id, $id, true);

                        if ($choque) {

                            $intento = "Edición -> Día: $dia_semana | Hora: $hora_inicio a $hora_fin";
                            $log->registrar($_SESSION['usuario_id'], $intento, $choque['detalle']);
                            $error = $choque['error'];
                        } else {
                            $horario->update($id, $seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin);
                            $this->bitacora("Modificó horario ID " . $id . ": " . $dia_semana . " " . $hora_inicio . " a " . $hora_fin);
                            $success = "Bloque de horario actualizado correctamente.";
                        }
                    }
                } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
            }
        }

        $datos = $horario->find($id);
        if (!$datos) { $this->redirect('horarios'); }

        $secciones  = $seccion->catalogo();
        $materias   = $materia->catalogo();
        $profesores = $profesor->catalogoTodos();
        $aulas      = $aula->catalogoTodas();

        $this->render('horarios/form', [
            'modo'       => 'editar',
            'error'      => $error,
            'success'    => $success,
            'datos'      => $datos,
            'id'         => $id,
            'secciones'  => $secciones,
            'materias'   => $materias,
            'profesores' => $profesores,
            'aulas'      => $aulas,
        ]);
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('horarios');
        }
        $this->requireAdmin('horarios');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            $datos = (new Horario($this->conn))->find($id);

            try {
                $horario = new Horario($this->conn);
                $horario->delete($id);
                $this->bitacora("Eliminó horario ID " . $id . ": " . ($datos['dia_semana'] ?? '') . " " . substr($datos['hora_inicio'] ?? '', 0, 5) . " a " . substr($datos['hora_fin'] ?? '', 0, 5));
            } catch(PDOException $e) {

            }
        }
        $this->redirect('horarios', ['msg' => 'eliminado']);
    }

    private function turnoDe($hora_inicio) {
        $hhmm = substr($hora_inicio, 0, 5);
        if ($hhmm < '12:00') { return 'MATUTINO'; }
        if ($hhmm < '18:00') { return 'VESPERTINO'; }
        return 'NOCTURNO';
    }

    private function aMinutos($hhmm) {
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hhmm, $m)) {
            return null;
        }
        return ((int)$m[1] * 60) + (int)substr($hhmm, 3, 2);
    }

    public function sugerir() {
        $this->requireAdmin('horarios');

        $horario = new Horario($this->conn);
        $seccion = new Seccion($this->conn);
        $materia = new Materia($this->conn);
        $profesor = new Profesor($this->conn);
        $dias_validos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $error = "";
        $resultado = null;
        $profesor_id = 0;
        $materia_id = 0;
        $seccion_id = 0;
        $duracion = 90;
        $dia = '';
        $desde = '07:00';
        $hasta = '19:00';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $accion = $_POST['accion'] ?? '';
            $profesor_id = (int)($_POST['profesor_id'] ?? 0);
            $materia_id = (int)($_POST['materia_id'] ?? 0);
            $seccion_id = (int)($_POST['seccion_id'] ?? 0);
            if ($accion === 'aplicar') {
                $dia_ap = $_POST['dia'] ?? '';
                $hi = $_POST['hora_inicio'] ?? '';
                $hf = $_POST['hora_fin'] ?? '';
                $aula_id = (int)($_POST['aula_id'] ?? 0);
                if ($profesor_id <= 0 || $materia_id <= 0 || $seccion_id <= 0 || $aula_id <= 0 || $dia_ap === '' || $hi === '' || $hf === '') {
                    $error = "Faltan datos para crear el horario.";
                } elseif (!in_array($dia_ap, $dias_validos, true) || $this->aMinutos($hi) === null || $this->aMinutos($hf) === null || $hi >= $hf) {
                    $error = "Día u horas no válidos.";
                } elseif (!$materia->find($materia_id) || !$seccion->find($seccion_id)) {
                    $error = "Materia o sección no encontrada.";
                } else {
                    try {
                        $error_ap = $horario->aplicarHueco($profesor_id, $materia_id, $seccion_id, $dia_ap, $hi, $hf, $aula_id);
                        if ($error_ap !== null) {
                            $error = "No se pudo crear: " . $error_ap;
                        } else {
                            $this->bitacora("Creó horario desde sugerencia: " . $dia_ap . " " . $hi . " a " . $hf . " (sección ID " . $seccion_id . ")");
                            $this->redirect('horarios', ['msg' => 'horario_creado']);
                        }
                    } catch(PDOException $e) {
                        $error = "Error al crear: " . $e->getMessage();
                    }
                }
            } else {
                $duracion = (int)($_POST['duracion'] ?? 90);
                $dia = trim($_POST['dia'] ?? '');
                $desde = trim($_POST['desde'] ?? '07:00');
                $hasta = trim($_POST['hasta'] ?? '19:00');
                if ($profesor_id <= 0 || $materia_id <= 0 || $seccion_id <= 0) {
                    $error = "Elige un profesor, una materia y una sección.";
                } elseif ($duracion <= 0 || $duracion > 720) {
                    $error = "Duración no válida.";
                } elseif ($dia !== '' && !in_array($dia, $dias_validos, true)) {
                    $error = "Día no válido.";
                } elseif (!$materia->find($materia_id)) {
                    $error = "Materia no encontrada.";
                } else {
                    $desde_min = $this->aMinutos($desde);
                    $hasta_min = $this->aMinutos($hasta);
                    if ($desde_min === null || $hasta_min === null || $hasta_min <= $desde_min || ($hasta_min - $desde_min) < $duracion) {
                        $error = "Rango de horas no válido.";
                    } else {
                        $dias = $dia !== '' ? [$dia] : [];
                        $resultado = $horario->huecosLibres($profesor_id, $materia_id, $seccion_id, $duracion, $dias, 40, $desde_min, $hasta_min);
                        if (isset($resultado['error'])) {
                            $error = $resultado['error'];
                            $resultado = null;
                        }
                    }
                }
            }
        }

        $profesores = $profesor->catalogoActivos();
        $materias = $materia->catalogo();
        $secciones = $seccion->catalogo();
        $datos_profesor = $profesor_id > 0 ? $profesor->find($profesor_id) : null;
        $datos_materia = $materia_id > 0 ? $materia->find($materia_id) : null;
        $datos_seccion = $seccion_id > 0 ? $seccion->find($seccion_id) : null;

        $this->render('horarios/sugerir', [
            'error'          => $error,
            'resultado'      => $resultado,
            'profesores'     => $profesores,
            'materias'       => $materias,
            'secciones'      => $secciones,
            'profesor_id'    => $profesor_id,
            'materia_id'     => $materia_id,
            'seccion_id'     => $seccion_id,
            'duracion'       => $duracion,
            'dia'            => $dia,
            'desde'          => $desde,
            'hasta'          => $hasta,
            'dias_validos'   => $dias_validos,
            'datos_profesor' => $datos_profesor,
            'datos_materia'  => $datos_materia,
            'datos_seccion'  => $datos_seccion,
        ]);
    }

    public function mover() {
        $this->requireAdmin('horarios');
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Método no permitido.']);
            exit;
        }

        $horario = new Horario($this->conn);
        $profesor = new Profesor($this->conn);
        $log = new LogChoque($this->conn);
        $dias_validos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
        $id = (int)($_POST['id'] ?? 0);
        $dia = $_POST['dia'] ?? '';
        $hi = $_POST['hora_inicio'] ?? '';
        $datos = $id > 0 ? $horario->find($id) : null;

        if (!$datos) {
            echo json_encode(['ok' => false, 'error' => 'Bloque no encontrado.']);
            exit;
        }
        if (!in_array($dia, $dias_validos, true) || $this->aMinutos($hi) === null) {
            echo json_encode(['ok' => false, 'error' => 'Día u hora no válidos.']);
            exit;
        }

        $dur = $this->aMinutos(substr($datos['hora_fin'], 0, 5)) - $this->aMinutos(substr($datos['hora_inicio'], 0, 5));
        $hf_min = $this->aMinutos($hi) + $dur;
        if ($dur <= 0 || $hf_min >= 1440) {
            echo json_encode(['ok' => false, 'error' => 'El bloque no cabe en ese horario.']);
            exit;
        }
        $hf = sprintf('%02d:%02d', intdiv($hf_min, 60), $hf_min % 60);

        try {
            $rec = $horario->validarRecursos($datos['profesor_id'], $datos['aula_id'], $datos['seccion_id']);
            if ($rec['prof_estado'] != 'ACTIVO') {
                $error = "El docente no está ACTIVO.";
            } elseif ($rec['aula_estado'] != 'OPERATIVA') {
                $error = "El aula no está OPERATIVA.";
            } elseif ($rec['sec_alumn'] > $rec['aula_cap']) {
                $error = "Capacidad excedida: El aula (" . $rec['aula_cap'] . " alumnos) no soporta la matrícula (" . $rec['sec_alumn'] . " alumnos).";
            } elseif ($profesor->tieneDisponibilidad($datos['profesor_id']) && !$profesor->disponibilidadCubre($datos['profesor_id'], $dia, $this->turnoDe($hi))) {
                $error = "El docente no tiene disponibilidad el $dia en el turno " . strtolower($this->turnoDe($hi)) . ".";
            } elseif ($profesor->tieneMaterias($datos['profesor_id']) && !$profesor->dictaMateria($datos['profesor_id'], $datos['materia_id'])) {
                $error = "El docente no dicta esa materia.";
            } else {
                $choque = $horario->detectarChoque($dia, $hi, $hf, $datos['aula_id'], $datos['profesor_id'], $datos['seccion_id'], $id, true);
                if ($choque) {
                    $intento = "Mover bloque $id a: Día: $dia | Hora: $hi a $hf";
                    $log->registrar($_SESSION['usuario_id'], $intento, $choque['detalle']);
                    $error = $choque['error'];
                } else {
                    $horario->update($id, $datos['seccion_id'], $datos['materia_id'], $datos['profesor_id'], $datos['aula_id'], $dia, $hi, $hf);
                    $this->bitacora("Movió horario ID " . $id . ": " . $datos['dia_semana'] . " " . substr($datos['hora_inicio'], 0, 5) . " a " . substr($datos['hora_fin'], 0, 5) . " -> " . $dia . " " . $hi . " a " . $hf);
                    echo json_encode(['ok' => true]);
                    exit;
                }
            }
            echo json_encode(['ok' => false, 'error' => $error]);
            exit;
        } catch(PDOException $e) {
            echo json_encode(['ok' => false, 'error' => 'Error DB: ' . $e->getMessage()]);
            exit;
        }
    }
}
