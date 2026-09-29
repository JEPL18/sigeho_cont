<?php
// app/views/auth/verificar_2fa.php — Página completa (sin header/footer).
// Migrada de verificar_2fa.php.
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación de 2 Pasos - SIGEHO-CONT</title>
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
        .card-2fa {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 450px;
            width: 100%;
            margin: 15px;
            padding: 40px;
            text-align: center;
        }
        .icon-box {
            width: 80px; height: 80px;
            background: #eef6ff;
            color: #0a2a4d;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; margin: 0 auto 20px;
        }
        .codigo-input {
            font-size: 2rem; letter-spacing: 10px; text-align: center;
            font-weight: bold; color: #0a2a4d;
        }
    </style>
</head>
<body>

    <div class="card-2fa">
        <div class="icon-box">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h3 class="fw-bold text-dark mb-2">Verificación en 2 Pasos</h3>
        <p class="text-muted small mb-4">Hola <strong><?= htmlspecialchars($_SESSION['temp_nombre'] ?? 'Usuario'); ?></strong>, por seguridad ingresa el código de 6 dígitos de tu aplicación autenticadora.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $this->url('auth/verificar2fa'); ?>" method="POST" autocomplete="off">
            <div class="mb-4">
                <input type="text" name="codigo" class="form-control codigo-input rounded-3" maxlength="6" pattern="\d{6}" title="Ingrese los 6 dígitos" required autofocus placeholder="••••••">
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold" style="background:#0a2a4d; border:none;">
                    <i class="bi bi-unlock-fill me-2"></i>VALIDAR Y ACCEDER
                </button>
            </div>
        </form>

        <div class="mt-4">
            <a href="<?= $this->url('auth/logout'); ?>" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver al inicio de sesión
            </a>
        </div>
    </div>

</body>
</html>
