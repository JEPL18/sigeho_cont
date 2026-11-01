<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - SIGEHO-CONT</title>
    <!-- Favicon -->
    <link rel="icon" href="<?php echo $baseUrl; ?>assets/img/logo_conta.jpeg" type="image/jpeg">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <style>
        /* Estilos CSS adaptados del login original */
        body {
            background: linear-gradient(135deg, #4a0a0a 0%, #8b1a1a 55%, #b71c1c 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 950px;
            width: 100%;
            margin: 15px;
        }
        .login-brand {
            background: #3d0a0a;
            color: white;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            text-align: center;
        }
        .brand-logo { width: 100px; height: auto; margin: 0 auto 20px; }
        .login-form-section { padding: 50px 45px; }
        .input-group-text { background-color: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #ced4da; }
        .btn-login {
            background: #8b1a1a;
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
        }
        .btn-login:hover { background: #b71c1c; transform: translateY(-2px); }
        .logos-footer {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 25px;
            align-items: center;
        }
        .logos-footer img { max-height: 60px; width: auto; }
    </style>
</head>
<body>

<div class="login-card row g-0">
    <div class="col-md-5 login-brand d-none d-md-flex">
        <img src="<?= $this->asset('assets/img/logo_conta.jpeg'); ?>" alt="Logo" class="brand-logo rounded shadow-sm bg-white p-1">
        <h3 class="fw-bold mb-3">SIGEHO-CONT</h3>
        <p class="opacity-75">Sistema Integrado de Gestión de Horarios</p>
        <hr class="my-4 opacity-25">
        <p class="small opacity-75">Acceso restringido al personal de la Coordinación de Contaduría Pública (UPTAG).</p>
    </div>

    <div class="col-md-7 login-form-section">

        <div class="logos-footer">
            <img src="<?= $this->asset('assets/img/logo_conta.jpeg'); ?>" alt="Coordinación" class="img-fluid rounded border">
            <img src="<?= $this->asset('assets/img/logo_uptag.jpeg'); ?>" alt="UPTAG" class="img-fluid rounded border">
        </div>

        <h4 class="fw-bold text-dark mb-1">Acceso al Sistema</h4>
        <p class="text-muted small mb-4">Ingresa tus credenciales para continuar</p>

        <?php if ($msg == 'clave_recuperada'): ?>
            <div class="alert alert-success border-0 shadow-sm small mb-3">
                <i class="bi bi-check-circle-fill me-1"></i> ¡Clave restablecida! Por favor, inicia sesión con tu nueva contraseña.
            </div>
        <?php endif; ?>

        <?php if ($msg == 'sesion_expirada'): ?>
            <div class="alert alert-warning border-0 shadow-sm small mb-3">
                <i class="bi bi-clock-fill me-1"></i> Tu sesión expiró por 15 minutos de inactividad. Por favor, inicia sesión de nuevo.
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger border-0 shadow-sm small mb-3">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $this->url('auth/login'); ?>" method="POST" autocomplete="off">
    <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label small fw-bold text-secondary">Correo Institucional</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope text-secondary"></i></span>
                    <input type="email" name="correo" class="form-control" placeholder="ejemplo@uptag.edu.ve" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-secondary">Contraseña</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock text-secondary"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••••••••••" required>
                </div>
            </div>

            <div class="mb-3 d-flex justify-content-center">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAFsJzSyLkF189kmT"></div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-login rounded-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>INGRESAR
                </button>
            </div>

            <div class="text-center mt-4">
                <a href="<?= $this->url('auth/solicitarRecuperacion'); ?>" class="text-decoration-none small fw-bold text-secondary">
                    <i class="bi bi-key me-1"></i> ¿Olvidaste tu contraseña? (por correo)
                </a>
                <span class="text-muted small mx-1">·</span>
                <a href="<?= $this->url('auth/recuperar'); ?>" class="text-decoration-none small fw-bold text-secondary">
                    <i class="bi bi-patch-question me-1"></i> Recuperar con preguntas de seguridad
                </a>
            </div>
        </form>
    </div>
</div>

<div class="text-center small px-3 pb-3" style="color: rgba(255,255,255,0.9); max-width: 950px;">
    © 2026 Juan Montañez, Andrés Moreno y Jesús Peña (SIGEHO-CONT, UPTAG) · Esta obra está bajo una <a href="https://creativecommons.org/licenses/by-nc-sa/4.0/deed.es" target="_blank" rel="license" class="text-white fw-bold">Licencia Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional</a>
</div>

</body>
</html>
