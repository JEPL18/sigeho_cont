<?php
// configuracion.php
session_start();
require 'config/db.php';

if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: dashboard.php");
    exit();
}

$error = "";
$success = "";

// RECIBIR ERROR DEL BACKUP (SI FALLA EL 2FA)
if (isset($_GET['err']) && $_GET['err'] == '2fa') {
    $error = "El código 2FA ingresado para autorizar el respaldo es incorrecto.";
}

// PROCESAR GUARDADO DE FECHA
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_fecha') {
    $nueva_fecha = trim($_POST['fecha_inicio_lapso']);
    
    if (empty($nueva_fecha)) {
        $error = "Debe seleccionar una fecha válida.";
    } else {
        try {
            $sql = "UPDATE configuracion SET valor = ? WHERE parametro = 'fecha_inicio_lapso'";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$nueva_fecha]);
            $success = "La fecha de inicio de lapso ha sido actualizada correctamente.";
        } catch(PDOException $e) {
            $error = "Error al actualizar: " . $e->getMessage();
        }
    }
}

// OBTENER LA FECHA ACTUAL 
$stmt = $conn->query("SELECT valor FROM configuracion WHERE parametro = 'fecha_inicio_lapso'");
$fecha_actual = $stmt->fetchColumn();

// VERIFICAR ESTADO DEL 2FA DEL ADMINISTRADOR ACTUAL
$stmt_2fa = $conn->prepare("SELECT 2fa_activo FROM usuarios WHERE id = ?");
$stmt_2fa->execute([$_SESSION['usuario_id']]);
$estado_2fa = $stmt_2fa->fetchColumn();

// GENERAR UNA CONTRASEÑA ALEATORIA SEGURA PARA EL RESPALDO
$caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
$pass_backup = substr(str_shuffle($caracteres), 0, 10);

$ruta = ''; 
include 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-gear-fill text-danger me-2"></i> Configuración del Sistema</h2>
    <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
</div>

<?php if($error): ?><div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="row">
    <!-- TARJETA 1: LAPSO ACADÉMICO -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-calendar-range me-2"></i> Control de Lapso Académico
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">El sistema calcula automáticamente la semana de clases actual basándose en la fecha en la que iniciaron formalmente las actividades del trimestre.</p>
                
                <form action="configuracion.php" method="POST">
                    <input type="hidden" name="accion" value="guardar_fecha">
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Fecha de Inicio del Lapso Actual</label>
                        <input type="date" name="fecha_inicio_lapso" class="form-control border-secondary" value="<?php echo $fecha_actual; ?>" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;">
                        <i class="bi bi-save me-1"></i> Actualizar Fecha
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- TARJETA 2: SEGURIDAD 2FA -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header text-white fw-bold" style="background-color: #0d6efd;">
                <i class="bi bi-phone-vibrate-fill me-2"></i> Doble Factor (2FA)
            </div>
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <i class="bi bi-shield-lock text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold">Seguridad de la Cuenta</h5>
                <p class="text-muted small mb-4">Añade una capa extra de seguridad. El sistema te pedirá un código desde tu celular para iniciar sesión o realizar acciones críticas.</p>
                
                <a href="configurar_2fa.php" class="btn btn-primary fw-bold w-100">
                    <?php if($estado_2fa == 1): ?>
                        <i class="bi bi-gear-fill me-1"></i> Administrar 2FA (Activado)
                    <?php else: ?>
                        <i class="bi bi-shield-plus me-1"></i> Configurar 2FA (Inactivo)
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>

    <!-- TARJETA 3: RESPALDO -->
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header text-white fw-bold" style="background-color: #198754;">
                <i class="bi bi-server me-2"></i> Respaldo (Backup)
            </div>
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <i class="bi bi-cloud-arrow-down text-muted mb-3" style="font-size: 3rem;"></i>
                <h5 class="fw-bold">Respaldo Cifrado</h5>
                <p class="text-muted small mb-4">Descarga una copia completa de la base de datos y archivos, con encriptación AES-256 para garantizar la privacidad.</p>
                
                <button type="button" class="btn btn-success fw-bold w-100" data-bs-toggle="modal" data-bs-target="#modalRespaldo">
                    <i class="bi bi-download me-1"></i> Generar Respaldo
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRespaldo" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <form action="backup.php" method="POST">
          <div class="modal-header bg-warning">
            <h5 class="modal-title fw-bold text-dark"><i class="bi bi-exclamation-triangle-fill me-2"></i> Aviso de Seguridad</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-4 text-center">
            <p class="text-dark">Por protocolos de seguridad, el sistema ha generado una contraseña única aleatoria para cifrar este archivo ZIP.</p>
            
            <div class="alert alert-danger p-2 mb-4">
                <strong>¡NO LA COMPARTAS!</strong> Es exclusiva para la administración del sistema.
            </div>

            <p class="text-muted small mb-1">Tu contraseña para este archivo es:</p>
            <h2 class="font-monospace text-primary fw-bold user-select-all bg-light border p-3 rounded mb-4" style="letter-spacing: 3px;">
                <?php echo $pass_backup; ?>
            </h2>
            
            <input type="hidden" name="password_respaldo" value="<?php echo $pass_backup; ?>">

            <!-- SI TIENE 2FA, PEDIR CÓDIGO PARA AUTORIZAR DESCARGA -->
            <?php if($estado_2fa == 1): ?>
                <hr>
                <div class="mb-2 mt-3 text-start">
                    <label class="form-label fw-bold text-dark"><i class="bi bi-phone-vibrate text-primary"></i> Confirma la descarga con tu código 2FA:</label>
                    <input type="text" name="codigo_2fa" class="form-control text-center tracking-widest fw-bold" placeholder="123456" pattern="[0-9]{6}" maxlength="6" required>
                </div>
            <?php endif; ?>

          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success fw-bold" onclick="setTimeout(function(){ $('#modalRespaldo').modal('hide'); }, 1500);"><i class="bi bi-download me-2"></i> Descargar Archivo</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>