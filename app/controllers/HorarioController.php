<?php
// app/controllers/HorarioController.php
// Migrado de modules/horarios/ (index, nuevo, editar, eliminar),
// incluyendo el algoritmo anti-choques y el log de choques.

class HorarioController extends Controller {

    public function index() {
        $this->requireLogin();

        $horario = new Horario($this->conn);
        $horarios = $horario->allConDetalle();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('horarios/index', [
            'horarios' => $horarios,
            'msg'      => $msg,
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
                    // 1. RECOPILAR DATOS PARA VALIDACIONES
                    $datos = $horario->validarRecursos($profesor_id, $aula_id, $seccion_id);

                    if ($datos['prof_estado'] != 'ACTIVO') {
                        $error = "El docente seleccionado no está ACTIVO.";
                    } elseif ($datos['aula_estado'] != 'OPERATIVA') {
                        $error = "El aula seleccionada no está OPERATIVA.";
                    } elseif ($datos['sec_alumn'] > $datos['aula_cap']) {
                        $error = "Capacidad excedida: El aula (".$datos['aula_cap']." ptos) no soporta la matrícula (".$datos['sec_alumn']." alumnos).";
                    } else {

                        // 2. ALGORITMO ANTI-CHOQUES (DESGLOSADO Y ESPECÍFICO)
                        $choque = $horario->detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id);

                        if ($choque) {
                            // REGISTRAR EL LOG ESPECÍFICO EN LA BASE DE DATOS
                            $intento = "Día: $dia_semana | Hora: $hora_inicio a $hora_fin";
                            $log->registrar($_SESSION['usuario_id'], $intento, $choque['detalle']);
                            $error = $choque['error'];
                        } else {
                            // 3. GUARDAR
                            $horario->create($seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin);
                            $success = "Bloque de clase asignado correctamente.";
                        }
                    }
                } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
            }
        }

        // Cargar catálogos
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
                        $error = "Capacidad excedida: El aula (".$datos['aula_cap']." ptos) no soporta la matrícula (".$datos['sec_alumn']." alumnos).";
                    } else {

                        // --- ALGORITMO ANTI-CHOQUES (DESGLOSADO Y EXCLUYENDO EL ID ACTUAL) ---
                        $choque = $horario->detectarChoque($dia_semana, $hora_inicio, $hora_fin, $aula_id, $profesor_id, $seccion_id, $id, true);

                        if ($choque) {
                            // REGISTRAR EL LOG EN LA BASE DE DATOS
                            $intento = "Edición -> Día: $dia_semana | Hora: $hora_inicio a $hora_fin";
                            $log->registrar($_SESSION['usuario_id'], $intento, $choque['detalle']);
                            $error = $choque['error'];
                        } else {
                            $horario->update($id, $seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin);
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
        $this->requireAdmin('horarios');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            try {
                $horario = new Horario($this->conn);
                $horario->delete($id);
            } catch(PDOException $e) {
                // Ignorar si hay un fallo
            }
        }
        $this->redirect('horarios', ['msg' => 'eliminado']);
    }
}
