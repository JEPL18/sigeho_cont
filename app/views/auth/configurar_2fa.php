<?php
// app/views/auth/configurar_2fa.php — Activar/desactivar 2FA de la cuenta propia.
// Migrada de configurar_2fa.php (usa header/footer: usuario autenticado).
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-lock-fill text-danger me-2"></i> Seguridad: Autenticación 2FA</h2>
    <a href="<?= $this->url('dashboard'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver al Inicio</a>
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

                    <form action="<?= $this->url('auth/configurar2fa'); ?>" method="POST">
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

                    <form action="<?= $this->url('auth/configurar2fa'); ?>" method="POST" class="w-75 mx-auto">
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
