<?php
// app/controllers/SeccionController.php
// Migrado de modules/secciones/ (index, nuevo, editar, eliminar).

class SeccionController extends Controller {

    public function index() {
        $this->requireLogin();

        $seccion = new Seccion($this->conn);
        $secciones = $seccion->all();
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('secciones/index', [
            'secciones' => $secciones,
            'msg'       => $msg,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('secciones');

        $seccion = new Seccion($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $trayecto = $_POST['trayecto']; // Se toma tal cual del form
            $trimestre = (int)$_POST['trimestre'];
            $cantidad_alumnos = (int)$_POST['cantidad_alumnos'];

            // Se valida permitiendo explícitamente el valor 0
            if (empty($codigo) || $trayecto === '' || empty($trimestre) || empty($cantidad_alumnos)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($seccion->existeCodigo($codigo)) {
                        $error = "Ya existe una sección registrada con ese código.";
                    } else {
                        $seccion->create($codigo, $trayecto, $trimestre, $cantidad_alumnos);
                        $success = "Sección registrada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al guardar: " . $e->getMessage();
                }
            }
        }

        $this->render('secciones/form', [
            'modo'    => 'nuevo',
            'error'   => $error,
            'success' => $success,
            'datos'   => null,
        ]);
    }

    public function editar() {
        $this->requireAdmin('secciones');

        $seccion = new Seccion($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('secciones'); }
        $id = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo = trim(strtoupper($_POST['codigo']));
            $trayecto = $_POST['trayecto'];
            $trimestre = (int)$_POST['trimestre'];
            $cantidad_alumnos = (int)$_POST['cantidad_alumnos'];

            if (empty($codigo) || $trayecto === '' || empty($trimestre) || empty($cantidad_alumnos)) {
                $error = "Todos los campos son obligatorios.";
            } else {
                $trayecto = (int)$trayecto;
                try {
                    if ($seccion->existeCodigo($codigo, $id)) {
                        $error = "Este código ya pertenece a otra sección.";
                    } else {
                        $seccion->update($id, $codigo, $trayecto, $trimestre, $cantidad_alumnos);
                        $success = "Sección actualizada correctamente.";
                    }
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $datos = $seccion->find($id);
        if (!$datos) { $this->redirect('secciones'); }

        $this->render('secciones/form', [
            'modo'    => 'editar',
            'error'   => $error,
            'success' => $success,
            'datos'   => $datos,
            'id'      => $id,
        ]);
    }

    public function eliminar() {
        $this->requireAdmin('secciones');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            try {
                $seccion = new Seccion($this->conn);
                $seccion->delete($id);
                $this->redirect('secciones', ['msg' => 'eliminado']);
            } catch(PDOException $e) {
                // Bloqueo de seguridad
                $this->redirect('secciones', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('secciones');
        }
    }
}
