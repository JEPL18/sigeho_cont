<?php
// index.php

// --- PUNTO 27: PROTECCIÓN DE COOKIES (HttpOnly y SameSite) ---
// Esto evita que scripts maliciosos roben la sesión del administrador
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
// -----------------------------------------------------------

if (isset($_SESSION['usuario_id'])) {
    header("Location: dashboard.php");
    exit();
}

require 'config/db.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if (empty($correo) || empty($password)) {
        $error = "Por favor, ingresa tu correo y contraseña.";
    } else {
        try {
            // AÑADIMOS '2fa_activo' y '2fa_secret' A LA CONSULTA PARA EL DOBLE FACTOR
            $stmt = $conn->prepare("SELECT id, nombre, correo, rol, password, intentos, bloqueado, 2fa_activo, 2fa_secret FROM usuarios WHERE correo = ? LIMIT 1");
            $stmt->execute([$correo]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // --- PUNTO 17: VERIFICAR SI LA CUENTA ESTÁ BLOQUEADA ---
                if ($user['bloqueado'] == 1) {
                    $error = "Cuenta bloqueada por seguridad tras 3 intentos fallidos. Contacte al soporte técnico.";
                } else {
                    if (password_verify($password, $user['password'])) {
                        
                        // Si entra correctamente, reseteamos sus intentos a 0
                        $conn->query("UPDATE usuarios SET intentos = 0 WHERE id = ".$user['id']);
                        
                        // ======== NUEVA LÓGICA 2FA ========
                        if ($user['2fa_activo'] == 1) {
                            // GUARDAMOS EN SESIÓN TEMPORAL
                            $_SESSION['temp_user_id'] = $user['id'];
                            $_SESSION['temp_nombre'] = $user['nombre'];
                            $_SESSION['temp_rol'] = $user['rol'];
                            
                            // --- NUEVO: DESCIFRAMOS LA LLAVE ANTES DE PONERLA EN LA SESIÓN TEMPORAL ---
                            $_SESSION['temp_2fa_secret'] = descifrar_secreto($user['2fa_secret']);
                            
                            // REDIRIGIMOS A LA VERIFICACIÓN
                            header("Location: verificar_2fa.php");
                            exit();
                        } else {
                            // INGRESO NORMAL (No tiene 2FA)
                            // PUNTO 27: Regenerar ID de sesión para prevenir ataques de "Session Fixation"
                            session_regenerate_id(true);

                            $_SESSION['usuario_id'] = $user['id'];
                            $_SESSION['nombre'] = $user['nombre'];
                            $_SESSION['rol'] = $user['rol'];
                            
                            // Registro en la bitácora
                            $accion = "Inicio de sesión exitoso";
                            $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
                            $stmt_bitacora->execute([$user['id'], $accion]);
                            
                            header("Location: dashboard.php");
                            exit();
                        }
                        // ==================================
                        
                    } else {
                        // --- PUNTO 17: LÓGICA DE INTENTOS FALLIDOS ---
                        $nuevos_intentos = $user['intentos'] + 1;
                        
                        if ($nuevos_intentos >= 3) {
                            // Bloquear la cuenta
                            $conn->query("UPDATE usuarios SET intentos = 3, bloqueado = 1 WHERE id = ".$user['id']);
                            $error = "¡Cuenta bloqueada! Ha excedido los 3 intentos permitidos.";
                            
                            // Registramos el bloqueo en la bitácora
                            $accion_bloqueo = "Cuenta bloqueada por intentos fallidos";
                            $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
                            $stmt_bitacora->execute([$user['id'], $accion_bloqueo]);
                        } else {
                            // Actualizamos el contador y le avisamos cuántos le quedan
                            $conn->query("UPDATE usuarios SET intentos = $nuevos_intentos WHERE id = ".$user['id']);
                            $quedan = 3 - $nuevos_intentos;
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | SIGEHO-CONT</title>
    <link rel="icon" href="assets/img/logo_conta.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body>

<div class="container d-flex justify-content-center login-container">
    <div class="row g-0 login-card">
        
        <div class="col-md-5 bg-uptag d-none d-md-flex">
            <img src="assets/img/logo_conta.jpeg" alt="Logo Contaduría" class="logo-login">
            <h3 class="fw-bold mb-2">SIGEHO-CONT</h3>
            <p class="mb-0 text-white-50">Sistema Integrado de Gestión de Horarios</p>
            <hr class="w-75 mx-auto my-4 border-light">
            <small class="fw-bold">PNF Contaduría Pública</small>
        </div>

        <div class="col-md-7 form-section">
            <div class="text-center mb-4 d-md-none">
                <img src="assets/img/logo_uptag.jpeg" alt="UPTAG" width="120" class="mb-3">
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold text-dark mb-0">Ingresa tus credenciales</h4>
                <img src="assets/img/logo_uptag.jpeg" alt="UPTAG" width="100" class="d-none d-md-block">
            </div>
            
            <?php if(isset($_GET['msg']) && $_GET['msg'] == 'clave_recuperada'): ?>
                <div class="alert alert-success p-2 mb-4 shadow-sm border-0 text-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> Contraseña actualizada. Ya puede iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if($error): ?>
                <div class="alert alert-danger p-2 mb-4 shadow-sm border-0" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold text-muted small">Correo Institucional</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                        <!-- PUNTO 12: aria-label añadido -->
                        <input type="email" name="correo" class="form-control border-start-0 ps-0" placeholder="admin@uptag.edu.ve" aria-label="Ingresar correo institucional" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-muted small">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                        <!-- PUNTO 12: aria-label añadido -->
                        <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="••••••••" aria-label="Ingresar contraseña" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 py-2 fw-bold mb-3" style="background-color: #8B1A1A;" aria-label="Iniciar sesión">
                    Ingresar al Sistema <i class="bi bi-box-arrow-in-right ms-2"></i>
                </button>
                
                <div class="text-center mt-3">
                    <a href="recuperar_clave.php" class="text-decoration-none fw-bold" style="color: #8B1A1A;" aria-label="¿Olvidaste tu contraseña? Haz clic aquí para recuperarla.">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>