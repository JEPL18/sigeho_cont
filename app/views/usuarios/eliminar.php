<?php
// app/views/usuarios/eliminar.php — Confirmación de eliminación con autenticación estricta.
// Migrada de modules/usuarios/eliminar.php.
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

                <form action="<?= $this->url('usuarios/eliminar', ['id' => $id_eliminar]); ?>" method="POST">
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
                        <a href="<?= $this->url('usuarios'); ?>" class="btn btn-outline-secondary px-4 fw-bold"><i class="bi bi-arrow-left"></i> Cancelar</a>
                        <button type="submit" class="btn btn-danger px-4 fw-bold"><i class="bi bi-trash"></i> Confirmar Eliminación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
