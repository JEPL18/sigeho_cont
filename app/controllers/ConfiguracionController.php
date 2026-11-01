<?php
class ConfiguracionController extends Controller {

    public function index() {
        $this->requireAdmin('dashboard');

        $config  = new Configuracion($this->conn);
        $usuario = new Usuario($this->conn);
        $error = "";
        $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_fecha') {
            $nueva_fecha = trim($_POST['fecha_inicio_lapso']);

            if (empty($nueva_fecha)) {
                $error = "Debe seleccionar una fecha válida.";
            } else {
                try {
                    $config->guardarFechaInicioLapso($nueva_fecha);
                    $success = "La fecha de inicio de lapso ha sido actualizada correctamente.";
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        $fecha_actual = $config->fechaInicioLapso();

        $estado_2fa = $usuario->estado2fa($_SESSION['usuario_id']);

        $this->render('configuracion/index', [
            'error'        => $error,
            'success'      => $success,
            'fecha_actual' => $fecha_actual,
            'estado_2fa'   => $estado_2fa,
        ]);
    }
}
