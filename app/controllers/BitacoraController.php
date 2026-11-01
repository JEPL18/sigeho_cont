<?php
class BitacoraController extends Controller {

    public function index() {
        $this->requireAdmin('dashboard');

        $bitacora = new Bitacora($this->conn);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['accion'] ?? '') === 'vaciar') {
            $bitacora->vaciar();
            $this->redirect('bitacora', ['msg' => 'vaciada']);
        }

        $entradas = $bitacora->recientes(100);

        $this->render('bitacora/index', [
            'entradas' => $entradas,
            'msg'      => $msg,
        ]);
    }
}
