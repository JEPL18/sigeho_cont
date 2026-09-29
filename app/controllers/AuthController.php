<?php
// app/controllers/AuthController.php
// Lógica de autenticación migrada de: index.php (login), logout.php,
// verificar_2fa.php, configurar_2fa.php y recuperar_clave.php.

require_once APP_PATH . '/libs/GoogleAuthenticator.php';

class AuthController extends Controller {

    /**
     * Muestra el login y procesa el inicio de sesión
     * (lógica original de index.php, incluyendo PUNTO 17 de intentos y 2FA).
     */
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

            if (empty($correo) || empty($password)) {
                $error = "Por favor, ingresa tu correo y contraseña.";
            } else {
                try {
                    // AÑADIMOS '2fa_activo' y '2fa_secret' A LA CONSULTA PARA EL DOBLE FACTOR
                    $user = $usuario->findParaLogin($correo);

                    if ($user) {
                        // --- PUNTO 17: VERIFICAR SI LA CUENTA ESTÁ BLOQUEADA ---
                        if ($user['bloqueado'] == 1) {
                            $error = "Cuenta bloqueada por seguridad tras 3 intentos fallidos. Contacte al soporte técnico.";
                        } else {
                            if (password_verify($password, $user['password'])) {

                                // Si entra correctamente, reseteamos sus intentos a 0
                                $usuario->resetearIntentos($user['id']);

                                // ======== NUEVA LÓGICA 2FA ========
                                if ($user['2fa_activo'] == 1) {
                                    // GUARDAMOS EN SESIÓN TEMPORAL
                                    $_SESSION['temp_user_id'] = $user['id'];
                                    $_SESSION['temp_nombre'] = $user['nombre'];
                                    $_SESSION['temp_rol'] = $user['rol'];

                                    // --- NUEVO: DESCIFRAMOS LA LLAVE ANTES DE PONERLA EN LA SESIÓN TEMPORAL ---
                                    $_SESSION['temp_2fa_secret'] = descifrar_secreto($user['2fa_secret']);

                                    // REDIRIGIMOS A LA VERIFICACIÓN
                                    $this->redirect('auth/verificar2fa');
                                } else {
                                    // INGRESO NORMAL (No tiene 2FA)
                                    // PUNTO 27: Regenerar ID de sesión para prevenir ataques de "Session Fixation"
                                    session_regenerate_id(true);

                                    $_SESSION['usuario_id'] = $user['id'];
                                    $_SESSION['nombre'] = $user['nombre'];
                                    $_SESSION['rol'] = $user['rol'];

                                    // Registro en la bitácora
                                    $bitacora->registrar($user['id'], "Inicio de sesión exitoso");

                                    $this->redirect('dashboard');
                                }
                                // ==================================

                            } else {
                                // --- PUNTO 17: LÓGICA DE INTENTOS FALLIDOS ---
                                $resultado = $usuario->registrarIntentoFallido($user['id'], $user['intentos']);

                                if ($resultado['bloqueado']) {
                                    $error = "¡Cuenta bloqueada! Ha excedido los 3 intentos permitidos.";

                                    // Registramos el bloqueo en la bitácora
                                    $bitacora->registrar($user['id'], "Cuenta bloqueada por intentos fallidos");
                                } else {
                                    // Actualizamos el contador y le avisamos cuántos le quedan
                                    $quedan = 3 - $resultado['intentos'];
                                    $error = "Contraseña incorrecta. Le quedan $quedan intento(s).";
                                }
                            }
                        }
                    } else {
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

    /**
     * Verificación del código 2FA tras el login (verificar_2fa.php).
     */
    public function verificar2fa() {
        // Si no pasó por el login primero, lo rebotamos
        if (!isset($_SESSION['temp_user_id'])) {
            $this->redirect('auth/login');
        }

        $bitacora = new Bitacora($this->conn);
        $error = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $codigo_ingresado = trim($_POST['codigo']);
            $secret = $_SESSION['temp_2fa_secret'];

            $ga = new PHPGangsta_GoogleAuthenticator();
            $resultado = $ga->verifyCode($secret, $codigo_ingresado, 2); // Tolerancia de tiempo

            if ($resultado) {
                // CÓDIGO CORRECTO: Transformar sesión temporal en sesión real
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $_SESSION['temp_user_id'];
                $_SESSION['nombre'] = $_SESSION['temp_nombre'];
                $_SESSION['rol'] = $_SESSION['temp_rol'];

                // Registrar en bitácora
                $bitacora->registrar($_SESSION['usuario_id'], "Inicio de sesión exitoso (Validado con 2FA)");

                // Limpiar temporales
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

    /**
     * Cierre de sesión con registro en bitácora (logout.php).
     */
    public function logout() {
        // --- NUEVO: REGISTRO DE BITÁCORA (SALIDA) ---
        if (isset($_SESSION['usuario_id'])) {
            try {
                $bitacora = new Bitacora($this->conn);
                $bitacora->registrar($_SESSION['usuario_id'], "Cierre de sesión manual");
            } catch(PDOException $e) {
                // Silencioso, si falla la bitácora igual debe dejar salir al usuario.
            }
        }
        // ---------------------------------------------

        session_destroy(); // Destruye la sesión segura
        $this->redirect('auth/login'); // Te devuelve al Login
    }

    /**
     * Activar/desactivar 2FA de la cuenta propia (configurar_2fa.php).
     */
    public function configurar2fa() {
        $this->requireLogin();

        $usuario = new Usuario($this->conn);
        $ga = new PHPGangsta_GoogleAuthenticator();
        $error = '';
        $success = '';

        // 1. OBTENEMOS EL ESTADO ACTUAL DEL USUARIO
        $user = $usuario->datos2fa($_SESSION['usuario_id']);

        // --- NUEVO: DESCIFRAMOS LA LLAVE SI EXISTE EN LA BASE DE DATOS ---
        $secret_descifrado = null;
        if (!empty($user['2fa_secret'])) {
            $secret_descifrado = descifrar_secreto($user['2fa_secret']);
        }

        // 2. SI NO TIENE UNA LLAVE SECRETA, LE CREAMOS UNA TEMPORAL
        if (empty($secret_descifrado) && !isset($_SESSION['temp_2fa_secret'])) {
            $_SESSION['temp_2fa_secret'] = $ga->createSecret();
        }

        // Usaremos la llave descifrada si ya la tiene, o la temporal si es nuevo
        $secret = !empty($secret_descifrado) ? $secret_descifrado : $_SESSION['temp_2fa_secret'];

        // 3. GENERAMOS LA URL DEL CÓDIGO QR
        $titulo = urlencode('SIGEHO-CONT');
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode('otpauth://totp/'.$titulo.'?secret='.$secret);

        // 4. PROCESAR EL FORMULARIO DE VERIFICACIÓN
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['activar_2fa'])) {
                $codigo_ingresado = $_POST['codigo'];

                $resultado = $ga->verifyCode($secret, $codigo_ingresado, 2);

                if ($resultado) {
                    // --- NUEVO: CIFRAMOS LA LLAVE JUSTO ANTES DE GUARDARLA ---
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
                $usuario->desactivar2fa($_SESSION['usuario_id']);

                $success = "Se ha desactivado el 2FA de tu cuenta.";
                $user['2fa_activo'] = 0;
                $user['2fa_secret'] = null;
                $secret_descifrado = null;
                unset($_SESSION['temp_2fa_secret']);
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

    /**
     * Recuperación de contraseña en 3 pasos (recuperar_clave.php).
     */
    public function recuperar() {
        $usuario = new Usuario($this->conn);
        $error = "";
        $step = 1; // Paso por defecto: Pedir el correo

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['action'])) {

                // PASO 1: Validar si el correo existe
                if ($_POST['action'] == 'verificar_correo') {
                    $correo = trim(strtolower($_POST['correo']));

                    if ($usuario->existeIdPorCorreo($correo)) {
                        $_SESSION['reset_email'] = $correo; // Guardamos el correo en sesión temporalmente
                        $step = 2; // Avanzar a las preguntas
                    } else {
                        $error = "No existe ninguna cuenta vinculada a este correo institucional.";
                    }
                }

                // PASO 2: Validar las 3 respuestas de seguridad
                elseif ($_POST['action'] == 'verificar_respuestas') {
                    $r1 = strtolower(trim($_POST['r1']));
                    $r2 = strtolower(trim($_POST['r2']));
                    $r3 = strtolower(trim($_POST['r3']));
                    $correo = $_SESSION['reset_email'];

                    if ($usuario->verificarRespuestasSeguridad($correo, $r1, $r2, $r3)) {
                        $_SESSION['reset_autorizado'] = true; // Permiso concedido para cambiar clave
                        $step = 3; // Avanzar al formulario de nueva clave
                    } else {
                        $error = "Respuestas incorrectas. Verifique la ortografía e intente nuevamente.";
                        $step = 2; // Mantener en las preguntas
                    }
                }

                // PASO 3: Guardar la nueva contraseña
                elseif ($_POST['action'] == 'actualizar_clave') {
                    if (isset($_SESSION['reset_autorizado']) && $_SESSION['reset_autorizado'] === true) {
                        $nueva_clave = $_POST['nueva_clave'];

                        // --- PUNTO 23 APROBADO: MÍNIMO 16 CARACTERES ---
                        if (strlen($nueva_clave) < 16) {
                            $error = "Por políticas de seguridad (ENISA), la contraseña debe tener al menos 16 caracteres.";
                            $step = 3;
                        } else {
                            $pass_hash = password_hash($nueva_clave, PASSWORD_DEFAULT);
                            $correo = $_SESSION['reset_email'];

                            $usuario->actualizarPasswordPorCorreo($correo, $pass_hash);

                            // Limpiar la seguridad temporal
                            unset($_SESSION['reset_email']);
                            unset($_SESSION['reset_autorizado']);

                            // Redirigir al login con éxito
                            $this->redirect('auth/login', ['msg' => 'clave_recuperada']);
                        }
                    }
                }
            }
        }

        $this->renderPage('auth/recuperar_clave', ['error' => $error, 'step' => $step]);
    }
}
