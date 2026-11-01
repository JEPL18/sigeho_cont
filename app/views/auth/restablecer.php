<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - SIGEHO-CONT</title>
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>assets/img/logo_conta.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0a2a4d 0%, #1e5799 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-rec {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            margin: 15px;
            padding: 40px;
        }
        .icon-box {
            width: 70px; height: 70px;
            background: #fef3cd; color: #997404;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2rem; margin: 0 auto 20px;
        }
    </style>
</head>
<body>

    <div class="card-rec">
        <div class="icon-box">
            <i class="bi bi-key-fill"></i>
        </div>
        <h4 class="fw-bold text-dark text-center mb-1">Restablecer Contraseña</h4>
        <p class="text-muted small text-center mb-3">Crea tu nueva contraseña de acceso</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($valido): ?>
            <p class="small text-secondary mb-3">Escribe tu nueva contraseña. (Mínimo 16 caracteres)</p>
            <form action="<?= $this->url('auth/restablecer'); ?>" method="POST">
    <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="nueva_clave" class="form-control" placeholder="••••••••••••••••" required minlength="16">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Confirmar Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="confirmar_clave" class="form-control" placeholder="••••••••••••••••" required minlength="16">
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success rounded-3 fw-bold">Guardar Nueva Contraseña</button>
                </div>
            </form>
        <?php else: ?>
            <div class="text-center mt-3">
                <a href="<?= $this->url('auth/solicitarRecuperacion'); ?>" class="btn btn-primary rounded-3" style="background:#0a2a4d; border:none;">Solicitar un Nuevo Enlace</a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= $this->url('auth/login'); ?>" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver al Login
            </a>
        </div>
    </div>

</body>
</html>
