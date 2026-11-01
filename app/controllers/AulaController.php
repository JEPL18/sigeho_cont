<?php
class AulaController extends Controller {

    public function index() {
        $this->requireLogin();

        $filtro_estado = isset($_GET['estado']) ? strtoupper(trim($_GET['estado'])) : '';

        $aula = new Aula($this->conn);
        $aulas = $aula->filtrar($filtro_estado);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('aulas/index', [
            'aulas'         => $aulas,
            'msg'           => $msg,
            'filtro_estado' => $filtro_estado,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('aulas');

        $aula = new Aula($this->conn);
        $error = ""; $success = "";
        $recursos_texto = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $capacidad = (int)$_POST['capacidad'];
            $estado = $_POST['estado'];
            $recursos_texto = isset($_POST['recursos']) ? trim($_POST['recursos']) : '';
            $items = array_filter(array_map('trim', explode(',', $recursos_texto)));

            if (empty($codigo) || empty($capacidad) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } elseif (count($items) > 15) {
                $error = "Máximo 15 recursos por aula.";
            } else {
                try {
                    if ($aula->existeCodigo($codigo)) {
                        $error = "Ya existe un aula registrada con ese código.";
                    } else {
                        $aula->create($codigo, $capacidad, $estado, $recursos_texto);
                        $this->bitacora("Creó aula: " . $codigo);
                        $success = "Aula registrada correctamente.";
                        $recursos_texto = "";
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('aulas/form', [
            'modo'           => 'nuevo',
            'error'          => $error,
            'success'        => $success,
            'datos'          => null,
            'recursos_texto' => $recursos_texto,
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
            $recursos_texto = isset($_POST['recursos']) ? trim($_POST['recursos']) : '';
            $items = array_filter(array_map('trim', explode(',', $recursos_texto)));

            if (empty($codigo) || empty($capacidad) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } elseif (count($items) > 15) {
                $error = "Máximo 15 recursos por aula.";
            } else {
                try {
                    if ($aula->existeCodigo($codigo, $id)) {
                        $error = "El código ya pertenece a otra aula.";
                    } else {
                        $aula->update($id, $codigo, $capacidad, $estado, $recursos_texto);
                        $this->bitacora("Modificó aula: " . $codigo . " (ID " . $id . ")");
                        $success = "Aula actualizada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $datos = $aula->find($id);
        if (!$datos) { $this->redirect('aulas'); }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $recursos_texto = $datos['recursos'] ?? '';
        }

        $this->render('aulas/form', [
            'modo'           => 'editar',
            'error'          => $error,
            'success'        => $success,
            'datos'          => $datos,
            'id'             => $id,
            'recursos_texto' => $recursos_texto,
        ]);
    }

    public function eliminar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('aulas');
        }
        $this->requireAdmin('aulas');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            $datos = (new Aula($this->conn))->find($id);

            try {
                $aula = new Aula($this->conn);
                $aula->delete($id);
                $this->bitacora("Eliminó aula: " . ($datos['codigo'] ?? '') . " (ID " . $id . ")");
                $this->redirect('aulas', ['msg' => 'eliminado']);
            } catch(PDOException $e) {

                $this->redirect('aulas', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('aulas');
        }
    }
}
