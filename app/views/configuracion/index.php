<?php
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-gear-fill text-danger me-2"></i> Configuración del Sistema</h2>
    <a href="<?= $this->url('dashboard'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
</div>

<?php if($error): ?><div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?></div><?php endif; ?>
<?php if($success): ?><div class="alert alert-success shadow-sm"><i class="bi bi-check-circle-fill me-2"></i> <?php echo $success; ?></div><?php endif; ?>

<div class="row">
    <!-- TARJETA 1: LAPSO ACADÉMICO -->
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-calendar-range me-2"></i> Control de Lapso Académico
            </div>
            <div class="card-body p-4">
                <p class="text-muted small">El sistema calcula automáticamente la semana de clases actual basándose en la fecha en la que iniciaron formalmente las actividades del trimestre.</p>

                <form action="<?= $this->url('configuracion'); ?>" method="POST">
    <?= csrf_field() ?>
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
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header text-white fw-bold" style="background-color: #0d6efd;">
                <i class="bi bi-phone-vibrate-fill me-2"></i> Doble Factor (2FA)
            </div>
            <div class="card-body p-4 text-center d-flex flex-column justify-content-center">
                <i class="bi bi-shield-lock text-muted mb-3" style="font-size: 3rem;"></i>
                <h3 class="fw-bold">Seguridad de la Cuenta</h3>
                <p class="text-muted small mb-4">Añade una capa extra de seguridad. El sistema te pedirá un código desde tu celular para iniciar sesión o realizar acciones críticas.</p>

                <a href="<?= $this->url('auth/configurar2fa'); ?>" class="btn btn-primary fw-bold w-100">
                    <?php if($estado_2fa == 1): ?>
                        <i class="bi bi-gear-fill me-1"></i> Administrar 2FA (Activado)
                    <?php else: ?>
                        <i class="bi bi-shield-plus me-1"></i> Configurar 2FA (Inactivo)
                    <?php endif; ?>
                </a>
            </div>
        </div>
    </div>
</div>
