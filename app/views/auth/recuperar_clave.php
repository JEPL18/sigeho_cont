<?php
// app/views/auth/recuperar_clave.php — Página completa (sin header/footer).
// Migrada de recuperar_clave.php (3 pasos).
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - SIGEHO-CONT</title>
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
        .steps { display: flex; justify-content: center; margin-bottom: 25px; gap: 10px; }
        .step { width: 30px; height: 6px; border-radius: 3px; background: #e9ecef; }
        .step.active { background: #0a2a4d; }
        .step.done { background: #198754; }
    </style>
</head>
<body>

    <div class="card-rec">
        <div class="icon-box">
            <i class="bi bi-key-fill"></i>
        </div>
        <h4 class="fw-bold text-dark text-center mb-1">Recuperación de Acceso</h4>
        <p class="text-muted small text-center mb-3">Restablece tu contraseña de forma segura</p>

        <div class="steps">
            <div class="step <?= ($step >= 1) ? 'active' : '' ?> <?= ($step > 1) ? 'done' : '' ?>"></div>
            <div class="step <?= ($step >= 2) ? 'active' : '' ?> <?= ($step > 2) ? 'done' : '' ?>"></div>
            <div class="step <?= ($step == 3) ? 'active' : '' ?>"></div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger small border-0 shadow-sm">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($step == 1): ?>
            <p class="small text-secondary mb-3"><strong>Paso 1:</strong> Ingrese su correo institucional registrado.</p>
            <form action="<?= $this->url('auth/recuperar'); ?>" method="POST">
                <input type="hidden" name="action" value="verificar_correo">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="correo" class="form-control" placeholder="usuario@uptag.edu.ve" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-3" style="background:#0a2a4d; border:none;">Verificar Correo</button>
                </div>
            </form>

        <?php elseif ($step == 2): ?>
            <p class="small text-secondary mb-3"><strong>Paso 2:</strong> Responda sus preguntas de seguridad.</p>
            <form action="<?= $this->url('auth/recuperar'); ?>" method="POST" autocomplete="off">
                <input type="hidden" name="action" value="verificar_respuestas">

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">¿Fecha de cumpleaños?</label>
                    <input type="text" name="r1" class="form-control" placeholder="Ej: 15-08-1990" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">¿Nombre del colegio donde estudiaste?</label>
                    <input type="text" name="r2" class="form-control" placeholder="Escriba la respuesta" required>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">¿Ciudad donde naciste?</label>
                    <input type="text" name="r3" class="form-control" placeholder="Escriba la respuesta" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary rounded-3" style="background:#0a2a4d; border:none;">Validar Respuestas</button>
                </div>
            </form>

        <?php elseif ($step == 3): ?>
            <p class="small text-secondary mb-3"><strong>Paso 3:</strong> Cree su nueva contraseña. (Mínimo 16 caracteres)</p>
            <form action="<?= $this->url('auth/recuperar'); ?>" method="POST">
                <input type="hidden" name="action" value="actualizar_clave">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Nueva Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="nueva_clave" class="form-control" placeholder="••••••••••••••••" required minlength="16">
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success rounded-3 fw-bold">Guardar Nueva Contraseña</button>
                </div>
            </form>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="<?= $this->url('auth/login'); ?>" class="text-decoration-none small text-muted">
                <i class="bi bi-arrow-left me-1"></i> Volver al Login
            </a>
        </div>
    </div>

</body>
</html>
