<?php
// app/controllers/AulaController.php
// Migrado de modules/aulas/ (index, nuevo, editar, eliminar).

class AulaController extends Controller {

    public function index() {
        $this->requireLogin();

        $aula = new Aula($this->conn);
        $aulas = $aula->all();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('aulas/index', [
            'aulas' => $aulas,
            'msg'   => $msg,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('aulas');

        $aula = new Aula($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $capacidad = (int)$_POST['capacidad'];
            $estado = $_POST['estado'];

            if (empty($codigo) || empty($capacidad) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($aula->existeCodigo($codigo)) {
                        $error = "Ya existe un aula registrada con ese código.";
                    } else {
                        $aula->create($codigo, $capacidad, $estado);
                        $success = "Aula registrada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('aulas/form', [
            'modo'    => 'nuevo',
            'error'   => $error,
            'success' => $success,
            'datos'   => null,
        ]);
    }

    public function editar() {
        $this->requireAdmin('aulas');

        $aula = new Aula($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('aulas'); }
        $id = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $capacidad = (int)$_POST['capacidad'];
            $estado = $_POST['estado'];

            if (empty($codigo) || empty($capacidad) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($aula->existeCodigo($codigo, $id)) {
                        $error = "El código ya pertenece a otra aula.";
                    } else {
                        $aula->update($id, $codigo, $capacidad, $estado);
                        $success = "Aula actualizada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $datos = $aula->find($id);
        if (!$datos) { $this->redirect('aulas'); }

        $this->render('aulas/form', [
            'modo'    => 'editar',
            'error'   => $error,
            'success' => $success,
            'datos'   => $datos,
            'id'      => $id,
        ]);
    }

    public function eliminar() {
        $this->requireAdmin('aulas');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            try {
                $aula = new Aula($this->conn);
                $aula->delete($id);
                $this->redirect('aulas', ['msg' => 'eliminado']);
            } catch(PDOException $e) {
                // Si el aula ya está asignada en la tabla de horarios, MySQL bloquea el borrado.
                // Redirigimos con la alerta.
                $this->redirect('aulas', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('aulas');
        }
    }
}
