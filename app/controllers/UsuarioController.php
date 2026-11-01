<?php
require_once APP_PATH . '/libs/GoogleAuthenticator.php';

class UsuarioController extends Controller {

    public function index() {
        $this->requireAdmin('dashboard');

        $filtro_rol = isset($_GET['rol']) ? strtolower(trim($_GET['rol'])) : '';

        $usuario = new Usuario($this->conn);
        $usuarios = $usuario->filtrar($filtro_rol);
        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';

        $this->render('usuarios/index', [
            'usuarios'   => $usuarios,
            'msg'        => $msg,
            'filtro_rol' => $filtro_rol,
        ]);
    }

    public function nuevo() {
        $this->requireAdmin('dashboard');

        $usuario = new Usuario($this->conn);
        $error = ""; $success = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $correo = trim(strtolower($_POST['correo']));
            $rol = $_POST['rol'];
            $password = $_POST['password'];

            $p1 = "¿Fecha de cumpleaños?";
            $r1_raw = strtolower(trim($_POST['respuesta_seguridad_1']));

            $p2 = "¿Nombre del colegio donde estudiaste?";
            $r2_raw = strtolower(trim($_POST['respuesta_seguridad_2']));

            $p3 = "¿Ciudad donde naciste?";
            $r3_raw = strtolower(trim($_POST['respuesta_seguridad_3']));

            if (empty($nombre) || empty($correo) || empty($rol) || empty($password) || empty($r1_raw) || empty($r2_raw) || empty($r3_raw)) {
                $error = "Todos los campos y respuestas son obligatorios.";
            } elseif (strlen($password) < 16) {
                $error = "Por políticas de seguridad, la contraseña debe tener al menos 16 caracteres.";
            } else {
                try {
                    if ($usuario->existeCorreo($correo)) {
                        $error = "Este correo ya está registrado en otra cuenta.";
                    } else {
                        $pass_hash = password_hash($password, PASSWORD_DEFAULT);

                        $r1_hash = password_hash($r1_raw, PASSWORD_DEFAULT);
                        $r2_hash = password_hash($r2_raw, PASSWORD_DEFAULT);
                        $r3_hash = password_hash($r3_raw, PASSWORD_DEFAULT);

                        $usuario->create($nombre, $correo, $rol, $pass_hash, $p1, $r1_hash, $p2, $r2_hash, $p3, $r3_hash);

                        $this->bitacora("Creó usuario: " . $nombre . " (" . $correo . ")");
                        $success = "Usuario registrado exitosamente.";
                    }
                } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
            }
        }

        $this->render('usuarios/form', [
            'modo'    => 'nuevo',
            'error'   => $error,
            'success' => $success,
            'datos'   => null,
        ]);
    }

    public function editar() {
        $this->requireAdmin('dashboard');

        $usuario = new Usuario($this->conn);
        $error = ""; $success = "";

        if (!isset($_GET['id'])) { $this->redirect('usuarios'); }
        $id_editar = (int)$_GET['id'];

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim(ucwords(strtolower($_POST['nombre'])));
            $correo = trim(strtolower($_POST['correo']));
            $rol = $_POST['rol'];
            $nueva_password = $_POST['password'];

            $p1 = "¿Fecha de cumpleaños?";
            $p2 = "¿Nombre del colegio donde estudiaste?";
            $p3 = "¿Ciudad donde naciste?";

            $r1_raw = strtolower(trim($_POST['respuesta_seguridad_1']));
            $r2_raw = strtolower(trim($_POST['respuesta_seguridad_2']));
            $r3_raw = strtolower(trim($_POST['respuesta_seguridad_3']));

            if (empty($nombre) || empty($correo) || empty($rol)) {
                $error = "Nombre, correo y rol son obligatorios.";
            } elseif (!empty($nueva_password) && strlen($nueva_password) < 16) {
                $error = "Por políticas de seguridad (ENISA), la contraseña debe tener al menos 16 caracteres.";
            } else {
                $es_cambio_sensible = !empty($nueva_password) || !empty($r1_raw) || !empty($r2_raw) || !empty($r3_raw);
                $error_2fa = "";
                if ($es_cambio_sensible) {
                    $admin_data = $usuario->datosSeguridad($_SESSION['usuario_id']);
                    if ($admin_data && $admin_data['2fa_activo'] == 1) {
                        $codigo_2fa = trim($_POST['codigo_2fa'] ?? '');
                        $ga = new PHPGangsta_GoogleAuthenticator();
                        $secret_descifrado = descifrar_secreto($admin_data['2fa_secret']);
                        if (empty($codigo_2fa) || !$ga->verifyCode($secret_descifrado, $codigo_2fa, 2)) {
                            $error_2fa = "Para cambiar la contraseña o las respuestas de seguridad debes ingresar un código 2FA válido.";
                        }
                    }
                }
                if ($error_2fa !== "") {
                    $error = $error_2fa;
                } else {
                try {
                    if ($id_editar == $_SESSION['usuario_id'] && strtolower($rol) != 'administrador') {
                        $error = "No puedes quitarte los permisos de administrador a ti mismo.";
                    } else {
                        if ($usuario->existeCorreo($correo, $id_editar)) {
                            $error = "El correo ya está en uso por otra persona.";
                        } else {
                            $usuario->update($id_editar, $nombre, $correo, $rol, $nueva_password, [
                                'r1' => $r1_raw,
                                'r2' => $r2_raw,
                                'r3' => $r3_raw,
                            ]);
                            $this->bitacora("Modificó usuario: " . $nombre . " (" . $correo . ")" . ($es_cambio_sensible ? " [cambio de contraseña o respuestas de seguridad]" : ""));
                            $success = "Datos actualizados correctamente.";
                        }
                    }
                } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
                }
            }
        }

        $datos = $usuario->find($id_editar);
        if (!$datos) { $this->redirect('usuarios'); }

        $admin_seg = $usuario->datosSeguridad($_SESSION['usuario_id']);

        $this->render('usuarios/form', [
            'modo'             => 'editar',
            'error'            => $error,
            'success'          => $success,
            'datos'            => $datos,
            'id'               => $id_editar,
            'admin_2fa_activo' => ($admin_seg && $admin_seg['2fa_activo'] == 1) ? 1 : 0,
        ]);
    }

    public function eliminar() {
        $this->requireAdmin('dashboard');

        $usuario = new Usuario($this->conn);
        $error = "";

        if (!isset($_GET['id'])) {
            $this->redirect('usuarios');
        }
        $id_eliminar = (int)$_GET['id'];

        if ($id_eliminar == $_SESSION['usuario_id']) {
            $this->redirect('usuarios', ['msg' => 'error_propio']);
        }

        $usuario_borrar = $usuario->find($id_eliminar);

        if (!$usuario_borrar) {
            $this->redirect('usuarios');
        }

        $admin_data = $usuario->datosSeguridad($_SESSION['usuario_id']);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password_admin = $_POST['password_admin'];

            if (password_verify($password_admin, $admin_data['password'])) {
                $codigo_valido = true;

                if ($admin_data['2fa_activo'] == 1) {
                    $codigo_2fa = trim($_POST['codigo_2fa'] ?? '');
                    if (empty($codigo_2fa)) {
                        $codigo_valido = false;
                        $error = "Debe ingresar su código de autenticación 2FA.";
                    } else {
                        $ga = new PHPGangsta_GoogleAuthenticator();
                        $secret_descifrado = descifrar_secreto($admin_data['2fa_secret']);
                        if (!$ga->verifyCode($secret_descifrado, $codigo_2fa, 2)) {
                            $codigo_valido = false;
                            $error = "Código 2FA incorrecto o expirado.";
                        }
                    }
                }

                if ($codigo_valido) {
                    try {

                        $usuario->limpiarDependencias($id_eliminar);

                        $usuario->delete($id_eliminar);

                        $this->bitacora("Eliminó usuario: " . ($usuario_borrar['nombre'] ?? '') . " (" . ($usuario_borrar['correo'] ?? '') . ")");
                        $this->redirect('usuarios', ['msg' => 'eliminado']);
                    } catch(PDOException $e) {
                        $this->redirect('usuarios', ['msg' => 'error_uso']);
                    }
                }
            } else {
                $error = "Contraseña de administrador incorrecta. Operación cancelada.";
            }
        }

        $this->render('usuarios/eliminar', [
            'error'          => $error,
            'id_eliminar'    => $id_eliminar,
            'usuario_borrar' => $usuario_borrar,
            'admin_data'     => $admin_data,
        ]);
    }

    public function desbloquear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('usuarios');
        }

        if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
            $this->redirect('dashboard');
        }

        if (isset($_GET['id'])) {
            $id_usuario = (int)$_GET['id'];

            try {
                $usuario = new Usuario($this->conn);

                $usuario->desbloquear($id_usuario);

                $bitacora = new Bitacora($this->conn);
                $bitacora->registrar($_SESSION['usuario_id'], "Desbloqueó al usuario ID: " . $id_usuario);

                $this->redirect('usuarios', ['msg' => 'desbloqueado']);

            } catch(PDOException $e) {
                $this->redirect('usuarios');
            }
        } else {
            $this->redirect('usuarios');
        }
    }
}
