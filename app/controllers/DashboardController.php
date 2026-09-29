<?php
// app/controllers/DashboardController.php
// Lógica del panel principal: KPIs, log de choques y "algoritmo SIACE".

class DashboardController extends Controller {

    public function index() {
        $this->requireLogin();

        $profesor = new Profesor($this->conn);
        $seccion  = new Seccion($this->conn);
        $aula     = new Aula($this->conn);
        $horario  = new Horario($this->conn);
        $log      = new LogChoque($this->conn);
        $config   = new Configuracion($this->conn);

        // --- LÓGICA PARA VACIAR EL LOG DE CHOQUES ---
        if (isset($_GET['action']) && $_GET['action'] == 'clear_logs' && strtolower($_SESSION['rol']) == 'administrador') {
            $log->vaciar();
            $this->redirect('dashboard');
        }

        // --- CONSULTAS PARA LOS INDICADORES (KPIs) ---
        $tot_prof_activos      = $profesor->contarActivos();
        $tot_prof_inactivos    = $profesor->contarInactivos();
        $tot_sec               = $seccion->contar();
        $tot_aulas_operativas  = $aula->contarOperativas();
        $tot_aulas_inoperativas = $aula->contarInoperativas();
        $tot_choques           = $log->contar();
        $logs                 = $log->recientes(50);

        // OBTENER LAPSO ACADÉMICO PARA EL ENCABEZADO
        $lapso_actual = $config->fechaInicioLapso();

        // --- LÓGICA PARA CARGAR EL HORARIO (ALGORITMO SIACE) ---
        $secciones = $seccion->listaParaDashboard();

        $seccion_seleccionada = isset($_GET['seccion_id']) ? (int)$_GET['seccion_id'] : null;
        $turno_seleccionado = isset($_GET['turno']) ? strtoupper($_GET['turno']) : '';

        $tramos_tiempo = [];
        $matriz_horario = [];
        $datos_seccion = null;

        if ($seccion_seleccionada) {
            $datos_seccion = $seccion->find($seccion_seleccionada);

            $filas = $horario->bloquesPorSeccion($seccion_seleccionada, $turno_seleccionado);

            foreach ($filas as $row) {
                // Formato exacto de hora del SIACE (ej: 12:30pm -- 01:15pm)
                $inicio_str = strtolower(date("h:ia", strtotime($row['hora_inicio'])));
                $fin_str = strtolower(date("h:ia", strtotime($row['hora_fin'])));
                $rango = $inicio_str . " -- " . $fin_str;

                $hora_cruda = $row['hora_inicio'];

                // Guardar el rango de horas como llave principal de las filas
                if (!isset($tramos_tiempo[$hora_cruda])) {
                    $tramos_tiempo[$hora_cruda] = $rango;
                }

                // Ubicar la clase en su coordenada exacta [Hora][Día]
                $dia = $row['dia_semana'];
                $matriz_horario[$hora_cruda][$dia] = $row;
            }
            // Ordenar las filas cronológicamente
            ksort($tramos_tiempo);
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
            'tramos_tiempo'          => $tramos_tiempo,
            'matriz_horario'         => $matriz_horario,
            'datos_seccion'          => $datos_seccion,
        ]);
    }
}
