<?php
// app/controllers/MateriaController.php
// Migrado de modules/materias/ (index, nuevo, editar, eliminar).

class MateriaController extends Controller {

    public function index() {
        $this->requireLogin();

        $materia = new Materia($this->conn);
        $materias = $materia->all();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('materias/index', [
            'materias' => $materias,
            'msg'      => $msg,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('materias');

        $materia = new Materia($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $trayecto = $_POST['trayecto'];
            $horas_semanales = (int)$_POST['horas_semanales'];

            // Se cambia la validación para que acepte el "0" (Trayecto Inicial)
            if (empty($codigo) || empty($nombre) || $trayecto === '' || empty($horas_semanales)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($materia->existeCodigo($codigo)) {
                        $error = "Ya existe una materia registrada con ese código.";
                    } else {
                        $materia->create($codigo, $nombre, $trayecto, $horas_semanales);
                        $success = "Unidad Curricular registrada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('materias/form', [
            'modo'    => 'nuevo',
            'error'   => $error,
            'success' => $success,
            'datos'   => null,
        ]);
    }

    public function editar() {
        $this->requireAdmin('materias');

        $materia = new Materia($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('materias'); }
        $id = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $trayecto = $_POST['trayecto'];
            $horas_semanales = (int)$_POST['horas_semanales'];

            if (empty($codigo) || empty($nombre) || $trayecto === '' || empty($horas_semanales)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($materia->existeCodigo($codigo, $id)) {
                        $error = "Este código ya pertenece a otra materia.";
                    } else {
                        $materia->update($id, $codigo, $nombre, $trayecto, $horas_semanales);
                        $success = "Unidad Curricular actualizada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $datos = $materia->find($id);
        if (!$datos) { $this->redirect('materias'); }

        $this->render('materias/form', [
            'modo'    => 'editar',
            'error'   => $error,
            'success' => $success,
            'datos'   => $datos,
            'id'      => $id,
        ]);
    }

    public function eliminar() {
        $this->requireAdmin('materias');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            try {
                $materia = new Materia($this->conn);
                $materia->delete($id);
                $this->redirect('materias', ['msg' => 'eliminado']);
            } catch(PDOException $e) {
                // Bloqueo de seguridad: Si la materia está asignada en "horarios",
                // mandamos la alerta de vuelta al index
                $this->redirect('materias', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('materias');
        }
    }
}
