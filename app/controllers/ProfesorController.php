<?php
class ProfesorController extends Controller {

    public function index() {
        $this->requireLogin();

        $filtro_estado = isset($_GET['estado']) ? strtoupper(trim($_GET['estado'])) : '';

        $profesor = new Profesor($this->conn);
        $profesores = $profesor->filtrar($filtro_estado);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('profesores/index', [
            'profesores'          => $profesores,
            'msg'                 => $msg,
            'filtro_estado'       => $filtro_estado,
            'disponibilidad_por_profesor' => $profesor->mapaDisponibilidad(),
            'materias_por_profesor'       => $profesor->mapaMaterias(),
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('profesores');

        $profesor = new Profesor($this->conn);
        $materia = new Materia($this->conn);
        $error = ""; $success = "";
        $materias = $materia->all();
        $disp_seleccionada = [];
        $mat_seleccionadas = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim(strtoupper($_POST['cedula']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $estado = $_POST['estado'];
            $disp_seleccionada = isset($_POST['disponibilidad']) ? (array)$_POST['disponibilidad'] : [];
            $dias_validos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
            $turnos_validos = ['MATUTINO', 'VESPERTINO', 'NOCTURNO'];
            $disp_seleccionada = array_values(array_filter($disp_seleccionada, function($par) use ($dias_validos, $turnos_validos) {
                $partes = explode('|', $par);
                return count($partes) == 2 && in_array($partes[0], $dias_validos, true) && in_array($partes[1], $turnos_validos, true);
            }));
            $mat_seleccionadas = isset($_POST['materias']) ? array_map('intval', (array)$_POST['materias']) : [];
            $validos = array_column($materias, 'id');
            $mat_seleccionadas = array_values(array_intersect($mat_seleccionadas, $validos));

            if (empty($cedula) || empty($nombre) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($profesor->existeCedula($cedula)) {
                        $error = "Esta cédula ya está registrada en el sistema.";
                    } else {

                        $id = $profesor->create($cedula, $nombre, $estado);
                        $profesor->sincronizarDisponibilidad($id, $disp_seleccionada);
                        $profesor->sincronizarMaterias($id, $mat_seleccionadas);
                        $this->bitacora("Creó profesor: " . $nombre . " (ID " . $id . ")");
                        $success = "Docente registrado correctamente.";
                        $disp_seleccionada = [];
                        $mat_seleccionadas = [];
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('profesores/form', [
            'modo'              => 'nuevo',
            'error'             => $error,
            'success'           => $success,
            'datos'             => null,
            'materias'          => $materias,
            'disp_seleccionada' => $disp_seleccionada,
            'mat_seleccionadas' => $mat_seleccionadas,
        ]);
    }

    public function editar() {
        $this->requireAdmin('profesores');

        $profesor = new Profesor($this->conn);
        $materia = new Materia($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('profesores'); }
        $id = (int)$_GET['id'];
        $materias = $materia->all();

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim(strtoupper($_POST['cedula']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $estado = $_POST['estado'];
            $disp_seleccionada = isset($_POST['disponibilidad']) ? (array)$_POST['disponibilidad'] : [];
            $dias_validos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
            $turnos_validos = ['MATUTINO', 'VESPERTINO', 'NOCTURNO'];
            $disp_seleccionada = array_values(array_filter($disp_seleccionada, function($par) use ($dias_validos, $turnos_validos) {
                $partes = explode('|', $par);
                return count($partes) == 2 && in_array($partes[0], $dias_validos, true) && in_array($partes[1], $turnos_validos, true);
            }));
            $mat_seleccionadas = isset($_POST['materias']) ? array_map('intval', (array)$_POST['materias']) : [];
            $validos = array_column($materias, 'id');
            $mat_seleccionadas = array_values(array_intersect($mat_seleccionadas, $validos));

            if (empty($cedula) || empty($nombre) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($profesor->existeCedula($cedula, $id)) {
                        $error = "Esta cédula ya está asociada a otro docente.";
                    } else {

                        $profesor->update($id, $cedula, $nombre, $estado);
                        $profesor->sincronizarDisponibilidad($id, $disp_seleccionada);
                        $profesor->sincronizarMaterias($id, $mat_seleccionadas);
                        $this->bitacora("Modificó profesor: " . $nombre . " (ID " . $id . ")");
                        $success = "Datos del docente actualizados correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        } else {
            $disp_seleccionada = $profesor->disponibilidadDe($id);
            $mat_seleccionadas = $profesor->materiasDe($id);
        }

        $datos = $profesor->find($id);
        if (!$datos) { $this->redirect('profesores'); }

        $this->render('profesores/form', [
            'modo'              => 'editar',
            'error'             => $error,
            'success'           => $success,
            'datos'             => $datos,
            'id'                => $id,
            'materias'          => $materias,
            'disp_seleccionada' => $disp_seleccionada,
            'mat_seleccionadas' => $mat_seleccionadas,
        ]);
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('profesores');
        }
        $this->requireAdmin('profesores');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $profesor = new Profesor($this->conn);

            if ($profesor->tieneHorarios($id)) {
                $this->redirect('profesores', ['msg' => 'error_uso']);
            }

            $datos = $profesor->find($id);

            try {
                $profesor->delete($id);
                $this->bitacora("Eliminó profesor: " . ($datos['nombre'] ?? '') . " (ID " . $id . ")");
                $this->redirect('profesores', ['msg' => 'eliminado']);
            } catch(PDOException $e) {

                $this->redirect('profesores', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('profesores');
        }
    }
}
