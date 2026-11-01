<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar por Correo - SIGEHO-CONT</title>
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>assets/img/logo_conta.jpeg" type="image/jpeg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
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
            <i class="bi bi-envelope-fill"></i>
        </div>
        <h4 class="fw-bold text-dark text-center mb-1">Recuperación por Correo</h4>
        <p class="text-muted small text-center mb-3">Te enviaremos un enlace para restablecer tu contraseña</p>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success small border-0 shadow-sm">
                <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <p class="small text-secondary mb-3">Ingresa tu correo institucional registrado.</p>
        <form action="<?= $this->url('auth/solicitarRecuperacion'); ?>" method="POST">
    <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Correo Institucional</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="correo" class="form-control" placeholder="usuario@uptag.edu.ve" required>
                </div>
            </div>
            <div class="mb-3 d-flex justify-content-center">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAFsJzSyLkF189kmT"></div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary rounded-3" style="background:#0a2a4d; border:none;">Enviar Enlace</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <a href="<?= $this->url('auth/recuperar'); ?>" class="text-decoration-none small text-muted">
                <i class="bi bi-patch-question me-1"></i> Recuperar con preguntas de seguridad
            </a>
            <br>
            <a href="<?= $this->url('auth/login'); ?>" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver al Login
            </a>
        </div>
    </div>

</body>
</html>
