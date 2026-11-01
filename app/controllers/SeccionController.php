<?php
class SeccionController extends Controller {

    public function index() {
        $this->requireLogin();

        $filtro_trayecto = isset($_GET['trayecto']) ? (int)$_GET['trayecto'] : 0;
        $filtro_trimestre = isset($_GET['trimestre']) ? (int)$_GET['trimestre'] : 0;

        $seccion = new Seccion($this->conn);
        $secciones = $seccion->filtrar($filtro_trayecto, $filtro_trimestre);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('secciones/index', [
            'secciones'        => $secciones,
            'msg'              => $msg,
            'filtro_trayecto'  => $filtro_trayecto,
            'filtro_trimestre' => $filtro_trimestre,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('secciones');

        $seccion = new Seccion($this->conn);
        $error = ""; $success = "";

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
                    if ($seccion->existeCodigo($codigo)) {
                        $error = "Ya existe una sección registrada con ese código.";
                    } else {
                        $seccion->create($codigo, $trayecto, $trimestre, $cantidad_alumnos);
                        $this->bitacora("Creó sección: " . $codigo);
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
                        $this->bitacora("Modificó sección: " . $codigo . " (ID " . $id . ")");
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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('secciones');
        }
        $this->requireAdmin('secciones');

        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];

            $datos = (new Seccion($this->conn))->find($id);

            try {
                $seccion = new Seccion($this->conn);
                $seccion->delete($id);
                $this->bitacora("Eliminó sección: " . ($datos['codigo'] ?? '') . " (ID " . $id . ")");
                $this->redirect('secciones', ['msg' => 'eliminado']);
            } catch(PDOException $e) {

                $this->redirect('secciones', ['msg' => 'error_uso']);
            }
        } else {
            $this->redirect('secciones');
        }
    }

    public function sugerir() {
        $this->requireAdmin('secciones');

        $seccion = new Seccion($this->conn);
        $aula = new Aula($this->conn);
        $error = "";
        $sugerencia = null;
        $matricula = 0;
        $capacidad = 0;
        $aula_id = 0;
        $trayecto = 0;
        $trimestre = 0;
        $prefijo = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $accion = $_POST['accion'] ?? '';
            if ($accion === 'aplicar') {
                $matricula = (int)($_POST['matricula'] ?? 0);
                $capacidad = (int)($_POST['capacidad'] ?? 0);
                $trayecto = (int)($_POST['trayecto'] ?? 0);
                $trimestre = (int)($_POST['trimestre'] ?? 0);
                $prefijo = trim($_POST['prefijo'] ?? '');
                if ($matricula <= 0 || $capacidad <= 0 || $trayecto <= 0 || $trimestre <= 0 || $prefijo === '' || strlen($prefijo) > 10 || !preg_match('/^[A-Za-z0-9]+$/', $prefijo)) {
                    $error = "Para crear las secciones hacen falta matrícula, capacidad, trayecto, trimestre y prefijo válidos.";
                } else {
                    $sugerencia = $seccion->sugerirCantidad($matricula, $capacidad);
                    if ($sugerencia === null) {
                        $error = "Matrícula y capacidad deben ser mayores que cero.";
                    } else {
                        try {
                            $creadas = $seccion->crearSugeridas($sugerencia['distribucion'], $trayecto, $trimestre, strtoupper($prefijo));
                            $this->bitacora("Creó " . count($creadas) . " secciones sugeridas: " . implode(', ', $creadas));
                            $this->redirect('secciones', ['msg' => 'secciones_creadas', 'n' => count($creadas)]);
                        } catch(PDOException $e) {
                            $error = "Error al crear: " . $e->getMessage();
                        }
                    }
                }
            } else {
                $matricula = (int)($_POST['matricula'] ?? 0);
                $aula_id = (int)($_POST['aula_id'] ?? 0);
                if ($aula_id > 0) {
                    $datos_aula = $aula->find($aula_id);
                    if (!$datos_aula) {
                        $error = "Aula no encontrada.";
                    } elseif ($datos_aula['estado'] !== 'OPERATIVA') {
                        $error = "El aula no está OPERATIVA.";
                    } else {
                        $capacidad = (int)$datos_aula['capacidad'];
                    }
                } else {
                    $capacidad = (int)($_POST['capacidad'] ?? 0);
                }
                if ($error === '') {
                    if ($matricula <= 0 || $capacidad <= 0) {
                        $error = "Indica una matrícula proyectada y una capacidad por sección mayores que cero.";
                    } else {
                        $sugerencia = $seccion->sugerirCantidad($matricula, $capacidad);
                    }
                }
            }
        }

        $aulas = $aula->catalogoOperativas();

        $this->render('secciones/sugerir', [
            'error'      => $error,
            'sugerencia' => $sugerencia,
            'matricula'  => $matricula,
            'capacidad'  => $capacidad,
            'aula_id'    => $aula_id,
            'aulas'      => $aulas,
            'trayecto'   => $trayecto,
            'trimestre'  => $trimestre,
            'prefijo'    => $prefijo,
        ]);
    }
}
