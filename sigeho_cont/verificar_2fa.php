<?php
// verificar_2fa.php
session_start();

// Si no pasó por el login primero, lo rebotamos
if (!isset($_SESSION['temp_user_id'])) {
    header("Location: index.php");
    exit();
}

require 'config/db.php';
require 'includes/libs/GoogleAuthenticator.php';

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
        $accion = "Inicio de sesión exitoso (Validado con 2FA)";
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
        $stmt_bitacora->execute([$_SESSION['usuario_id'], $accion]);
        
        // Limpiar temporales
        unset($_SESSION['temp_user_id']);
        unset($_SESSION['temp_nombre']);
        unset($_SESSION['temp_rol']);
        unset($_SESSION['temp_2fa_secret']);
        
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Código incorrecto o expirado. Verifique su aplicación.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación 2FA | SIGEHO-CONT</title>
    <link rel="icon" href="assets/img/logo_conta.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/estilos.css">
</head>
<body class="login-container">

<div class="container d-flex justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="card shadow border-0 rounded-4 mt-5">
            <div class="card-body p-5 text-center">
                
                <i class="bi bi-shield-lock text-danger mb-3 d-block" style="font-size: 4rem;"></i>
                <h4 class="fw-bold mb-3">Verificación de Dos Pasos</h4>
                <p class="text-muted small mb-4">Ingresa el código de 6 dígitos generado por tu aplicación de autenticación para continuar.</p>
                
                <?php if($error): ?>
                    <div class="alert alert-danger p-2 mb-4 shadow-sm border-0 small">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="verificar_2fa.php" method="POST">
                    <div class="mb-4">
                        <input type="text" name="codigo" class="form-control form-control-lg text-center fw-bold tracking-widest bg-light" placeholder="000000" pattern="[0-9]{6}" maxlength="6" aria-label="Código de Autenticación" required autofocus>
                    </div>

                    <button type="submit" class="btn btn-danger w-100 py-2 fw-bold mb-3" style="background-color: #8B1A1A;">
                        Verificar Código <i class="bi bi-check-circle ms-1"></i>
                    </button>
                </form>
                
                <a href="logout.php" class="text-decoration-none text-secondary small">Cancelar y volver al inicio</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>