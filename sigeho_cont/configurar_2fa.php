<?php
// configurar_2fa.php
session_start();
require 'config/db.php';
require 'includes/libs/GoogleAuthenticator.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

$ga = new PHPGangsta_GoogleAuthenticator();
$error = '';
$success = '';

// 1. OBTENEMOS EL ESTADO ACTUAL DEL USUARIO
$stmt = $conn->prepare("SELECT 2fa_secret, 2fa_activo FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

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
            
            $stmt = $conn->prepare("UPDATE usuarios SET 2fa_secret = ?, 2fa_activo = 1 WHERE id = ?");
            $stmt->execute([$secret_cifrado, $_SESSION['usuario_id']]);
            
            $success = "¡Autenticación de Dos Factores (2FA) activada con éxito!";
            $user['2fa_activo'] = 1;
            unset($_SESSION['temp_2fa_secret']);
        } else {
            $error = "El código ingresado es incorrecto o ha expirado. Inténtalo de nuevo.";
        }
    }
    
    if (isset($_POST['desactivar_2fa'])) {
        $stmt = $conn->prepare("UPDATE usuarios SET 2fa_secret = NULL, 2fa_activo = 0 WHERE id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        
        $success = "Se ha desactivado el 2FA de tu cuenta.";
        $user['2fa_activo'] = 0;
        $user['2fa_secret'] = null;
        $secret_descifrado = null;
        unset($_SESSION['temp_2fa_secret']); 
    }
}

$ruta = ''; 
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Seguridad: Autenticación 2FA</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 text-center">
                
                <?php if($error): ?><div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill"></i> <?php echo $success; ?></div><?php endif; ?>

                <?php if($user['2fa_activo'] == 1): ?>
                    <!-- VISTA SI YA TIENE 2FA ACTIVADO -->
                    <i class="bi bi-shield-check text-success mb-3" style="font-size: 5rem;"></i>
                    <h3 class="fw-bold text-success">2FA Activado</h3>
                    <p class="text-muted mb-4">Tu cuenta está protegida con autenticación de dos factores. El sistema te pedirá un código de tu aplicación cada vez que inicies sesión o ejecutes acciones críticas.</p>
                    
                    <form action="configurar_2fa.php" method="POST">
                        <button type="submit" name="desactivar_2fa" class="btn btn-outline-danger fw-bold w-50" aria-label="Desactivar seguridad de dos factores" onclick="return confirm('¿Seguro que deseas desactivar esta capa de seguridad?');">
                            Desactivar 2FA
                        </button>
                    </form>
                    
                <?php else: ?>
                    <!-- VISTA PARA ACTIVAR EL 2FA -->
                    <h4 class="fw-bold mb-3">Protege tu cuenta</h4>
                    <p class="text-muted small mb-4">Abre tu aplicación de autenticación (Google Authenticator, Authy, Proton Pass) y escanea este Código QR.</p>
                    
                    <div class="p-3 bg-light d-inline-block rounded border mb-3">
                        <img src="<?php echo $qrCodeUrl; ?>" alt="Código QR 2FA">
                    </div>
                    
                    <p class="text-muted small mb-4">Si no puedes escanearlo, ingresa esta clave manual:<br> <strong class="fs-5 text-primary tracking-widest"><?php echo htmlspecialchars($secret); ?></strong></p>
                    
                    <hr class="w-75 mx-auto my-4">
                    
                    <form action="configurar_2fa.php" method="POST" class="w-75 mx-auto">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ingresa el código de 6 dígitos de tu app:</label>
                            <input type="text" name="codigo" class="form-control form-control-lg text-center fw-bold tracking-widest" placeholder="123456" pattern="[0-9]{6}" maxlength="6" aria-label="Ingresar código de autenticación de 6 dígitos" required autofocus>
                        </div>
                        <button type="submit" name="activar_2fa" class="btn btn-success w-100 fw-bold fs-5" aria-label="Verificar y activar autenticación de dos factores">
                            <i class="bi bi-check2-circle"></i> Verificar y Activar
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>