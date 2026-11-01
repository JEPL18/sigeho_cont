<?php
class MateriaController extends Controller {

    public function index() {
        $this->requireLogin();

        $filtro_trayecto = isset($_GET['trayecto']) ? (int)$_GET['trayecto'] : 0;

        $materia = new Materia($this->conn);
        $materias = $materia->filtrar($filtro_trayecto);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('materias/index', [
            'materias'              => $materias,
            'msg'                   => $msg,
            'filtro_trayecto'       => $filtro_trayecto,
            'prelaciones_por_materia' => $materia->mapaPrelaciones(),
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('materias');

        $materia = new Materia($this->conn);
        $error = ""; $success = "";
        $todas = $materia->all();
        $seleccionadas = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $trayecto = $_POST['trayecto'];
            $horas_semanales = (int)$_POST['horas_semanales'];
            $seleccionadas = isset($_POST['prelaciones']) ? array_map('intval', (array)$_POST['prelaciones']) : [];
            $validos = array_column($todas, 'id');
            $seleccionadas = array_values(array_intersect($seleccionadas, $validos));

            if (empty($codigo) || empty($nombre) || $trayecto === '' || empty($horas_semanales)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($materia->existeCodigo($codigo)) {
                        $error = "Ya existe una materia registrada con ese código.";
                    } else {
                        $id = $materia->create($codigo, $nombre, $trayecto, $horas_semanales);
                        $materia->sincronizarPrelaciones($id, $seleccionadas);
                        $this->bitacora("Creó materia: " . $codigo . " - " . $nombre . " (ID " . $id . ")");
                        $success = "Unidad Curricular registrada correctamente.";
                        $seleccionadas = [];
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('materias/form', [
            'modo'          => 'nuevo',
            'error'         => $error,
            'success'       => $success,
            'datos'         => null,
            'todas'         => $todas,
            'seleccionadas' => $seleccionadas,
        ]);
    }

    public function editar() {
        $this->requireAdmin('materias');

        $materia = new Materia($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('materias'); }
        $id = (int)$_GET['id'];
        $todas = array_values(array_filter($materia->all(), function($m) use ($id) { return $m['id'] != $id; }));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $trayecto = $_POST['trayecto'];
            $horas_semanales = (int)$_POST['horas_semanales'];
            $crudas = isset($_POST['prelaciones']) ? array_map('intval', (array)$_POST['prelaciones']) : [];
            $validos = array_column($todas, 'id');
            $seleccionadas = array_values(array_intersect($crudas, $validos));

            if (empty($codigo) || empty($nombre) || $trayecto === '' || empty($horas_semanales)) {
                $error = "Todos los campos son obligatorios.";
            } elseif (in_array($id, $crudas)) {
                $error = "Una materia no puede ser prelación de sí misma.";
            } elseif ($materia->formaCiclo($id, $seleccionadas)) {
                $error = "Las prelaciones forman un ciclo. Revisa la cadena de materias.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($materia->existeCodigo($codigo, $id)) {
                        $error = "Este código ya pertenece a otra materia.";
                    } else {
                        $materia->update($id, $codigo, $nombre, $trayecto, $horas_semanales);
                        $materia->sincronizarPrelaciones($id, $seleccionadas);
                        $this->bitacora("Modificó materia: " . $codigo . " - " . $nombre . " (ID " . $id . ")");
                        $success = "Unidad Curricular actualizada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        } else {
            $seleccionadas = $materia->prelacionesDe($id);
        }

        $datos = $materia->find($id);
        if (!$datos) { $this->redirect('materias'); }

        $this->render('materias/form', [
            'modo'          => 'editar',
            'error'         => $error,
            'success'       => $success,
            'datos'         => $datos,
            'id'            => $id,
            'todas'         => $todas,
            'seleccionadas' => $seleccionadas,
        ]);
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('materias');
        }
        $this->requireAdmin('materias');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $materia = new Materia($this->conn);

            if ($materia->esPrelacionDe($id) > 0) {
                $this->redirect('materias', ['msg' => 'error_prelacion']);
            }

            if ($materia->tieneHorarios($id)) {
                $this->redirect('materias', ['msg' => 'error_uso']);
            }

            $datos = $materia->find($id);

            try {
                $materia->delete($id);
                $this->bitacora("Eliminó materia: " . ($datos['codigo'] ?? '') . " - " . ($datos['nombre'] ?? '') . " (ID " . $id . ")");
                $this->redirect('materias', ['msg' => 'eliminado']);
            } catch(PDOException $e) {

                $this->redirect('materias', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('materias');
        }
    }
}
