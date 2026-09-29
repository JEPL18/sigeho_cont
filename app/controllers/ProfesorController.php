<?php
// app/controllers/ProfesorController.php
// Migrado de modules/profesores/ (index, nuevo, editar, eliminar).

class ProfesorController extends Controller {

    public function index() {
        $this->requireLogin();

        $profesor = new Profesor($this->conn);
        $profesores = $profesor->all();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('profesores/index', [
            'profesores' => $profesores,
            'msg'        => $msg,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('profesores');

        $profesor = new Profesor($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim(strtoupper($_POST['cedula']));
            $titulo = $_POST['titulo']; // NUEVO CAMPO
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $estado = $_POST['estado'];

            if (empty($cedula) || empty($titulo) || empty($nombre) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($profesor->existeCedula($cedula)) {
                        $error = "Esta cédula ya está registrada en el sistema.";
                    } else {
                        // SE AGREGA EL TÍTULO A LA CONSULTA SQL
                        $profesor->create($cedula, $titulo, $nombre, $estado);
                        $success = "Docente registrado correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('profesores/form', [
            'modo'    => 'nuevo',
            'error'   => $error,
            'success' => $success,
            'datos'   => null,
        ]);
    }

    public function editar() {
        $this->requireAdmin('profesores');

        $profesor = new Profesor($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('profesores'); }
        $id = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $cedula = trim(strtoupper($_POST['cedula']));
            $titulo = $_POST['titulo']; // NUEVO CAMPO CAPTURADO
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $estado = $_POST['estado'];

            if (empty($cedula) || empty($titulo) || empty($nombre) || empty($estado)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                try {
                    if ($profesor->existeCedula($cedula, $id)) {
                        $error = "Esta cédula ya está asociada a otro docente.";
                    } else {
                        // AÑADIMOS EL TÍTULO AL UPDATE
                        $profesor->update($id, $cedula, $titulo, $nombre, $estado);
                        $success = "Datos del docente actualizados correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $datos = $profesor->find($id);
        if (!$datos) { $this->redirect('profesores'); }

        // Si el profesor es viejo y no tiene título guardado, le ponemos 'Prof.' por defecto
        $datos['titulo_actual'] = !empty($datos['titulo']) ? $datos['titulo'] : 'Prof.';

        $this->render('profesores/form', [
            'modo'    => 'editar',
            'error'   => $error,
            'success' => $success,
            'datos'   => $datos,
            'id'      => $id,
        ]);
    }

    public function eliminar() {
        $this->requireAdmin('profesores');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            try {
                $profesor = new Profesor($this->conn);
                $profesor->delete($id);
                $this->redirect('profesores', ['msg' => 'eliminado']);
            } catch(PDOException $e) {
                // Bloqueo de seguridad: Si el profesor está asignado a un horario,
                // redirigimos con el mensaje de error.
                $this->redirect('profesores', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('profesores');
        }
    }
}
