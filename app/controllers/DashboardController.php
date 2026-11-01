<?php
class DashboardController extends Controller {

    public function index() {
        $this->requireLogin();

        $profesor = new Profesor($this->conn);
        $seccion  = new Seccion($this->conn);
        $aula     = new Aula($this->conn);
        $horario  = new Horario($this->conn);
        $log      = new LogChoque($this->conn);
        $config   = new Configuracion($this->conn);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'clear_logs' && strtolower($_SESSION['rol']) == 'administrador') {
            $log->vaciar();
            $this->redirect('dashboard');
        }

        $tot_prof_activos      = $profesor->contarActivos();
        $tot_prof_inactivos    = $profesor->contarInactivos();
        $tot_sec               = $seccion->contar();
        $tot_aulas_operativas  = $aula->contarOperativas();
        $tot_aulas_inoperativas = $aula->contarInoperativas();
        $tot_choques           = $log->contar();
        $logs                 = $log->recientes(50);

        $lapso_actual = $config->fechaInicioLapso();

        $secciones = $seccion->listaParaDashboard();

        $seccion_seleccionada = isset($_GET['seccion_id']) ? (int)$_GET['seccion_id'] : null;
        $turno_seleccionado = isset($_GET['turno']) ? strtoupper($_GET['turno']) : '';

        $segmentos = [];
        $celdas = [];
        $ocupadas = [];
        $datos_seccion = null;

        if ($seccion_seleccionada) {
            $datos_seccion = $seccion->find($seccion_seleccionada);

            $filas = $horario->bloquesPorSeccion($seccion_seleccionada, $turno_seleccionado);

            $cortes = [];
            foreach ($filas as $row) {
                $cortes[$row['hora_inicio']] = true;
                $cortes[$row['hora_fin']] = true;
            }
            $cortes = array_keys($cortes);
            sort($cortes, SORT_STRING);
            $indice_corte = array_flip($cortes);

            for ($i = 0; $i + 1 < count($cortes); $i++) {
                $segmentos[] = [
                    'inicio' => $cortes[$i],
                    'fin'    => $cortes[$i + 1],
                    'texto'  => strtolower(date("h:ia", strtotime($cortes[$i]))) . " -- " . strtolower(date("h:ia", strtotime($cortes[$i + 1]))),
                ];
            }

            foreach ($filas as $row) {
                $i0 = $indice_corte[$row['hora_inicio']] ?? null;
                $i1 = $indice_corte[$row['hora_fin']] ?? null;
                if ($i0 === null || $i1 === null || $i1 <= $i0) {
                    continue;
                }
                $dia = $row['dia_semana'];
                if (isset($celdas[$i0][$dia]) || isset($ocupadas[$i0][$dia])) {
                    continue;
                }
                $celdas[$i0][$dia] = ['rowspan' => $i1 - $i0, 'clase' => $row];
                for ($j = $i0 + 1; $j < $i1; $j++) {
                    $ocupadas[$j][$dia] = true;
                }
            }
        }

        $this->render('dashboard/index', [
            'tot_prof_activos'       => $tot_prof_activos,
            'tot_prof_inactivos'     => $tot_prof_inactivos,
            'tot_sec'                => $tot_sec,
            'tot_aulas_operativas'   => $tot_aulas_operativas,
            'tot_aulas_inoperativas' => $tot_aulas_inoperativas,
            'tot_choques'            => $tot_choques,
            'logs'                   => $logs,
            'lapso_actual'           => $lapso_actual,
            'secciones'              => $secciones,
            'seccion_seleccionada'   => $seccion_seleccionada,
            'turno_seleccionado'     => $turno_seleccionado,
            'segmentos'              => $segmentos,
            'celdas'                 => $celdas,
            'ocupadas'               => $ocupadas,
            'datos_seccion'          => $datos_seccion,
        ]);
    }
}
