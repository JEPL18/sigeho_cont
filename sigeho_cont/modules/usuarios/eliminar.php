<?php
// modules/usuarios/eliminar.php
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: ../../dashboard.php");
    exit();
}

require '../../config/db.php';
// LIBRERÍA 2FA AÑADIDA
require '../../includes/libs/GoogleAuthenticator.php';

$error = "";

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}
$id_eliminar = (int)$_GET['id'];

// Evitar auto-eliminación
if ($id_eliminar == $_SESSION['usuario_id']) {
    header("Location: index.php?msg=error_propio");
    exit();
}

// Obtener datos del usuario a eliminar para mostrarlos en pantalla
$stmt_user = $conn->prepare("SELECT nombre, correo FROM usuarios WHERE id = ?");
$stmt_user->execute([$id_eliminar]);
$usuario_borrar = $stmt_user->fetch(PDO::FETCH_ASSOC);

if (!$usuario_borrar) {
    header("Location: index.php");
    exit();
}

// Traer la info de seguridad del administrador actual
$stmt_admin = $conn->prepare("SELECT password, 2fa_activo, 2fa_secret FROM usuarios WHERE id = ?");
$stmt_admin->execute([$_SESSION['usuario_id']]);
$admin_data = $stmt_admin->fetch(PDO::FETCH_ASSOC);

// --- AUTENTICACIÓN ESTRICTA ---
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password_admin = $_POST['password_admin'];
    
    // 1. Comprobamos la contraseña
    if (password_verify($password_admin, $admin_data['password'])) {
        $codigo_valido = true; 
        
        // 2. Comprobamos el 2FA si está activo
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
        
        // 3. Si todo es correcto, procedemos a borrar
        if ($codigo_valido) {
            try {
                // Borrar dependencias primero
                $stmt_logs = $conn->prepare("DELETE FROM log_choques WHERE usuario_id = ?");
                $stmt_logs->execute([$id_eliminar]);
                
                $stmt_bitacora = $conn->prepare("DELETE FROM bitacora WHERE usuario_id = ?");
                $stmt_bitacora->execute([$id_eliminar]);

                // Borrar usuario
                $stmt_del = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
                $stmt_del->execute([$id_eliminar]);
                
                header("Location: index.php?msg=eliminado");
                exit();
            } catch(PDOException $e) {
                header("Location: index.php?msg=error_uso");
                exit();
            }
        }
    } else {
        $error = "Contraseña de administrador incorrecta. Operación cancelada.";
    }
}

$ruta = '../../'; 
include '../../includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card shadow border-danger">
            <div class="card-header bg-danger text-white text-center py-3">
                <h4 class="mb-0 fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Autenticación Requerida</h4>
            </div>
            <div class="card-body p-4 text-center">
                <i class="bi bi-person-x-fill text-danger mb-3" style="font-size: 4rem;"></i>
                <h5 class="text-dark">Estás a punto de eliminar a:</h5>
                <p class="fs-5 fw-bold text-secondary"><?php echo htmlspecialchars($usuario_borrar['nombre']); ?> (<?php echo htmlspecialchars($usuario_borrar['correo']); ?>)</p>
                
                <hr class="my-4">
                
                <p class="text-danger fw-bold mb-4">Por motivos de seguridad extrema, autentica tu identidad para confirmar esta acción:</p>
                
                <?php if($error): ?>
                    <div class="alert alert-danger shadow-sm"><i class="bi bi-shield-x"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <form action="eliminar.php?id=<?php echo $id_eliminar; ?>" method="POST">
                    <div class="mb-3 text-start">
                        <label class="form-label fw-bold"><i class="bi bi-key-fill"></i> Contraseña de Administrador</label>
                        <input type="password" name="password_admin" class="form-control text-center" placeholder="Tu contraseña..." required autofocus>
                    </div>
                    
                    <?php if($admin_data['2fa_activo'] == 1): ?>
                    <div class="mb-4 text-start">
                        <label class="form-label fw-bold text-primary"><i class="bi bi-phone-vibrate"></i> Código 2FA</label>
                        <input type="text" name="codigo_2fa" class="form-control text-center tracking-widest fw-bold" placeholder="123456" pattern="[0-9]{6}" maxlength="6" required>
                    </div>
                    <?php else: ?>
                        <div class="mb-4"></div> <!-- Espaciador -->
                    <?php endif; ?>

                    <div class="d-flex justify-content-between">
                        <a href="index.php" class="btn btn-outline-secondary px-4 fw-bold"><i class="bi bi-arrow-left"></i> Cancelar</a>
                        <button type="submit" class="btn btn-danger px-4 fw-bold"><i class="bi bi-trash"></i> Confirmar Eliminación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>