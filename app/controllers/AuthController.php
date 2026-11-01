<?php
require_once APP_PATH . '/libs/GoogleAuthenticator.php';
require_once APP_PATH . '/libs/Correo.php';
require_once APP_PATH . '/libs/Turnstile.php';

class AuthController extends Controller {

    
    public function login() {
        if (isset($_SESSION['usuario_id'])) {
            $this->redirect('dashboard');
        }

        $usuario  = new Usuario($this->conn);
        $bitacora = new Bitacora($this->conn);
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $correo = trim($_POST['correo']);
            $password = trim($_POST['password']);
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $intentosIp = new IntentoIp($this->conn);
            $esperaIp = ($ip !== '') ? $intentosIp->esperaRestante($ip) : 0;

            if ($esperaIp > 0) {
                $error = "Demasiados intentos desde esta dirección. Espera $esperaIp segundos e inténtalo de nuevo.";
            } elseif (!Turnstile::configurado()) {
                $error = "Verificación de seguridad no configurada en el servidor. Contacte al administrador.";
            } elseif (!Turnstile::verificar(isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null)) {
                $error = "Verificación de seguridad fallida. Inténtalo de nuevo.";
            } elseif (empty($correo) || empty($password)) {
                $error = "Por favor, ingresa tu correo y contraseña.";
            } else {
                try {

                    $user = $usuario->findParaLogin($correo);

                    if ($user) {

                        if ($user['bloqueado'] == 1) {
                            $error = "Cuenta bloqueada por seguridad tras 3 intentos fallidos. Contacte al soporte técnico.";
                        } else {
                            if (password_verify($password, $user['password'])) {

                                $usuario->resetearIntentos($user['id']);
                                if ($ip !== '') { $intentosIp->limpiar($ip); }

                                if ($user['2fa_activo'] == 1) {

                                    $_SESSION['temp_user_id'] = $user['id'];
                                    $_SESSION['temp_nombre'] = $user['nombre'];
                                    $_SESSION['temp_rol'] = $user['rol'];

                                    $_SESSION['temp_2fa_secret'] = descifrar_secreto($user['2fa_secret']);

                                    $this->redirect('auth/verificar2fa');
                                } else {

                                    session_regenerate_id(true);

                                    $_SESSION['usuario_id'] = $user['id'];
                                    $_SESSION['nombre'] = $user['nombre'];
                                    $_SESSION['rol'] = $user['rol'];

                                    $bitacora->registrar($user['id'], "Inicio de sesión exitoso");

                                    $this->redirect('dashboard');
                                }

                            } else {

                                if ($ip !== '') { $intentosIp->registrarFallo($ip); }
                                $resultado = $usuario->registrarIntentoFallido($user['id'], $user['intentos']);

                                if ($resultado['bloqueado']) {
                                    $error = "¡Cuenta bloqueada! Ha excedido los 3 intentos permitidos.";

                                    $bitacora->registrar($user['id'], "Cuenta bloqueada por intentos fallidos");
                                } else {

                                    $quedan = 3 - $resultado['intentos'];
                                    $error = "Contraseña incorrecta. Le quedan $quedan intento(s).";
                                }
                            }
                        }
                    } else {
                        if ($ip !== '') { $intentosIp->registrarFallo($ip); }
                        $error = "El correo no está registrado en el sistema.";
                    }
                } catch(PDOException $e) {
                    $error = "Error de conexión: " . $e->getMessage();
                }
            }
        }

        $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
        $this->renderPage('auth/login', ['error' => $error, 'msg' => $msg]);
    }

    
    public function verificar2fa() {

        if (!isset($_SESSION['temp_user_id'])) {
            $this->redirect('auth/login');
        }

        $bitacora = new Bitacora($this->conn);
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo_ingresado = trim($_POST['codigo']);
            $secret = $_SESSION['temp_2fa_secret'];

            $ga = new PHPGangsta_GoogleAuthenticator();
            $resultado = $ga->verifyCode($secret, $codigo_ingresado, 2);

            if ($resultado) {

                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $_SESSION['temp_user_id'];
                $_SESSION['nombre'] = $_SESSION['temp_nombre'];
                $_SESSION['rol'] = $_SESSION['temp_rol'];

                $bitacora->registrar($_SESSION['usuario_id'], "Inicio de sesión exitoso (Validado con 2FA)");

                unset($_SESSION['temp_user_id']);
                unset($_SESSION['temp_nombre']);
                unset($_SESSION['temp_rol']);
                unset($_SESSION['temp_2fa_secret']);

                $this->redirect('dashboard');
            } else {
                $error = "Código incorrecto o expirado. Verifique su aplicación.";
            }
        }

        $this->renderPage('auth/verificar_2fa', ['error' => $error]);
    }

    
    public function logout() {

        if (isset($_SESSION['usuario_id'])) {
            try {
                $bitacora = new Bitacora($this->conn);
                $bitacora->registrar($_SESSION['usuario_id'], "Cierre de sesión manual");
            } catch(PDOException $e) {

            }
        }

        session_unset();
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        session_destroy();
        $this->redirect('auth/login');
    }

    
    public function configurar2fa() {
        $this->requireLogin();

        $usuario = new Usuario($this->conn);
        $ga = new PHPGangsta_GoogleAuthenticator();
        $error = '';
        $success = '';

        $user = $usuario->datos2fa($_SESSION['usuario_id']);

        $secret_descifrado = null;
        if (!empty($user['2fa_secret'])) {
            $secret_descifrado = descifrar_secreto($user['2fa_secret']);
        }

        if (empty($secret_descifrado) && !isset($_SESSION['temp_2fa_secret'])) {
            $_SESSION['temp_2fa_secret'] = $ga->createSecret();
        }

        $secret = !empty($secret_descifrado) ? $secret_descifrado : $_SESSION['temp_2fa_secret'];

        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $imagen = $protocolo . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . 'assets/img/logo_conta.jpeg';
        $otpauth = 'otpauth://totp/SIGEHO-CONT:' . $user['correo'] . '?secret=' . $secret . '&issuer=SIGEHO-CONT&image=' . urlencode($imagen);
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($otpauth);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['activar_2fa'])) {
                $codigo_ingresado = $_POST['codigo'];

                $resultado = $ga->verifyCode($secret, $codigo_ingresado, 2);

                if ($resultado) {

                    $secret_cifrado = cifrar_secreto($secret);

                    $usuario->activar2fa($_SESSION['usuario_id'], $secret_cifrado);

                    $success = "¡Autenticación de Dos Factores (2FA) activada con éxito!";
                    $user['2fa_activo'] = 1;
                    unset($_SESSION['temp_2fa_secret']);
                } else {
                    $error = "El código ingresado es incorrecto o ha expirado. Inténtalo de nuevo.";
                }
            }

            if (isset($_POST['desactivar_2fa'])) {
                $codigo_ingresado = trim($_POST['codigo'] ?? '');

                if (empty($codigo_ingresado) || !$ga->verifyCode($secret, $codigo_ingresado, 2)) {
                    $error = "Código 2FA incorrecto o expirado. No se desactivó el 2FA.";
                } else {
                    $usuario->desactivar2fa($_SESSION['usuario_id']);

                    $success = "Se ha desactivado el 2FA de tu cuenta.";
                    $user['2fa_activo'] = 0;
                    $user['2fa_secret'] = null;
                    $secret_descifrado = null;
                    unset($_SESSION['temp_2fa_secret']);
                }
            }
        }

        $this->render('auth/configurar_2fa', [
            'error'      => $error,
            'success'    => $success,
            'user'       => $user,
            'secret'     => $secret,
            'qrCodeUrl'  => $qrCodeUrl,
        ]);
    }

    
    public function recuperar() {
        $usuario = new Usuario($this->conn);
        $error = "";
        $step = 1;

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $turnstileOk = true;
            if (!Turnstile::configurado()) {
                $error = "Verificación de seguridad no configurada en el servidor. Contacte al administrador.";
                $turnstileOk = false;
            } elseif (!Turnstile::verificar(isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null)) {
                $error = "Verificación de seguridad fallida. Inténtalo de nuevo.";
                if (isset($_POST['action']) && $_POST['action'] == 'verificar_respuestas') {
                    $step = 2;
                } elseif (isset($_POST['action']) && $_POST['action'] == 'actualizar_clave' && isset($_SESSION['reset_autorizado']) && $_SESSION['reset_autorizado'] === true) {
                    $step = 3;
                }
                $turnstileOk = false;
            }
            if ($turnstileOk && isset($_POST['action'])) {

                if ($_POST['action'] == 'verificar_correo') {
                    $correo = trim(strtolower($_POST['correo']));

                    if ($usuario->existeIdPorCorreo($correo)) {
                        $_SESSION['reset_email'] = $correo;
                        $step = 2;
                    } else {
                        $error = "No existe ninguna cuenta vinculada a este correo institucional.";
                    }
                }

                elseif ($_POST['action'] == 'verificar_respuestas') {
                    $r1 = strtolower(trim($_POST['r1']));
                    $r2 = strtolower(trim($_POST['r2']));
                    $r3 = strtolower(trim($_POST['r3']));
                    $correo = $_SESSION['reset_email'];

                    if ($usuario->verificarRespuestasSeguridad($correo, $r1, $r2, $r3)) {
                        $_SESSION['reset_autorizado'] = true;
                        $step = 3;
                    } else {
                        $error = "Respuestas incorrectas. Verifique la ortografía e intente nuevamente.";
                        $step = 2;
                    }
                }

                elseif ($_POST['action'] == 'actualizar_clave') {
                    if (isset($_SESSION['reset_autorizado']) && $_SESSION['reset_autorizado'] === true) {
                        $nueva_clave = $_POST['nueva_clave'];

                        if (strlen($nueva_clave) < 16) {
                            $error = "Por políticas de seguridad (ENISA), la contraseña debe tener al menos 16 caracteres.";
                            $step = 3;
                        } else {
                            $pass_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
                            $correo = $_SESSION['reset_email'];

                            $usuario->actualizarPasswordPorCorreo($correo, $pass_hash);

                            unset($_SESSION['reset_email']);
                            unset($_SESSION['reset_autorizado']);

                            $this->redirect('auth/login', ['msg' => 'clave_recuperada']);
                        }
                    }
                }
            }
        }

        $this->renderPage('auth/recuperar_clave', ['error' => $error, 'step' => $step]);
    }

    public function solicitarRecuperacion() {
        $mensaje = '';
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $turnstileConfigurado = Turnstile::configurado();
            if (!$turnstileConfigurado || !Turnstile::verificar(isset($_POST['cf-turnstile-response']) ? $_POST['cf-turnstile-response'] : '', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null)) {
                $error = $turnstileConfigurado ? 'Verificación de seguridad fallida. Inténtalo de nuevo.' : 'Verificación de seguridad no configurada en el servidor. Contacte al administrador.';
                $this->renderPage('auth/solicitar_recuperacion', ['mensaje' => '', 'error' => $error]);
                return;
            }
            $correo = trim(strtolower($_POST['correo'] ?? ''));
            $usuario = new Usuario($this->conn);
            $tokens = new TokenRecuperacion($this->conn);
            $usuario_id = $usuario->findIdPorCorreo($correo);
            if ($usuario_id !== null) {
                $tokens->invalidarDeUsuario($usuario_id);
                $token = bin2hex(random_bytes(32));
                $expira = date('Y-m-d H:i:s', time() + 3600);
                $id = $tokens->crear($usuario_id, hash('sha256', $token), $expira);
                $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $enlace = $protocolo . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . 'auth/restablecer?token=' . $token;
                $enlaceSeguro = htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8');
                $cuerpoHtml = '<div style="font-family:Arial,Helvetica,sans-serif;max-width:600px;margin:0 auto;color:#333333;">'
                    . '<div style="background-color:#8b1a1a;color:#ffffff;padding:20px;text-align:center;">'
                    . '<div style="font-size:22px;font-weight:bold;">SIGEHO-CONT</div>'
                    . '<div style="font-size:13px;">Universidad Politécnica Territorial de Falcón &lsquo;Alonso Gamero&rsquo; (UPTAG)</div>'
                    . '</div>'
                    . '<div style="padding:24px;">'
                    . '<h2 style="color:#8b1a1a;margin-top:0;">Recuperación de contraseña</h2>'
                    . '<p>Hola,</p>'
                    . '<p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en SIGEHO-CONT. Para continuar, pulsa el siguiente botón:</p>'
                    . '<p style="text-align:center;margin:28px 0;"><a href="' . $enlaceSeguro . '" style="display:inline-block;background-color:#8b1a1a;color:#ffffff;padding:13px 30px;border-radius:6px;text-decoration:none;font-weight:bold;">Restablecer contraseña</a></p>'
                    . '<p>El enlace es válido por <strong>60 minutos</strong> y solo puede usarse <strong>una vez</strong>.</p>'
                    . '<p>Si el botón no funciona, copia y pega este enlace en tu navegador:<br><span style="word-break:break-all;color:#0a58ca;">' . $enlaceSeguro . '</span></p>'
                    . '<p>Si no solicitaste este cambio, ignora este mensaje: tu contraseña no se modificará.</p>'
                    . '</div>'
                    . '<div style="border-top:1px solid #dddddd;padding:12px 24px;font-size:12px;color:#777777;text-align:center;">Este es un mensaje automático de SIGEHO-CONT (UPTAG).</div>'
                    . '</div>';
                $cuerpo = 'Para restablecer tu contraseña, abre este enlace (válido por 60 minutos y de un solo uso): ' . $enlace . "\n\n" . 'Si no solicitaste este cambio, ignora este mensaje.';
                $enviado = Correo::enviar($correo, 'Recuperación de contraseña - SIGEHO-CONT', $cuerpo, $cuerpoHtml);
                if (!$enviado) {
                    $tokens->eliminar($id);
                    error_log('Recuperación por correo fallida para usuario ' . $usuario_id);
                }
            }
            $mensaje = 'Si el correo está registrado, recibirás un enlace para restablecer tu contraseña.';
        }
        $this->renderPage('auth/solicitar_recuperacion', ['mensaje' => $mensaje, 'error' => $error]);
    }

    public function restablecer() {
        $error = '';
        $tokens = new TokenRecuperacion($this->conn);
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        $registro = null;
        if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
            $registro = $tokens->buscarValido(hash('sha256', $token));
        }
        $valido = !empty($registro);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $token = isset($_POST['token']) ? $_POST['token'] : '';
            $nueva = isset($_POST['nueva_clave']) ? $_POST['nueva_clave'] : '';
            $confirmar = isset($_POST['confirmar_clave']) ? $_POST['confirmar_clave'] : '';
            $registro = null;
            if (is_string($token) && preg_match('/^[a-f0-9]{64}$/', $token)) {
                $registro = $tokens->buscarValido(hash('sha256', $token));
            }
            if (empty($registro)) {
                $error = 'El enlace es inválido o ha vencido.';
                $valido = false;
            } elseif (strlen($nueva) < 16) {
                $error = 'Por políticas de seguridad (ENISA), la contraseña debe tener al menos 16 caracteres.';
                $valido = true;
            } elseif ($nueva !== $confirmar) {
                $error = 'Las contraseñas no coinciden.';
                $valido = true;
            } else {
                $usuario = new Usuario($this->conn);
                $usuario->actualizarPasswordPorId($registro['usuario_id'], password_hash($nueva, PASSWORD_DEFAULT));
                $tokens->marcarUsado($registro['id']);
                $tokens->invalidarDeUsuario($registro['usuario_id']);
                $this->redirect('auth/login', ['msg' => 'clave_recuperada']);
            }
        } elseif (!$valido) {
            $error = 'El enlace es inválido o ha vencido.';
        }
        $this->renderPage('auth/restablecer', ['error' => $error, 'valido' => $valido, 'token' => is_string($token) ? $token : '']);
    }
}
