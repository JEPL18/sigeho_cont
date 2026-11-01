<?php
class AyudaController extends Controller {

    public function index() {
        $this->requireLogin();
        $this->render('ayuda/index');
    }
}
