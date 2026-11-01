<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-lightbulb text-danger me-2"></i> Sugerir Secciones</h2>
    <a href="<?= $this->url('secciones'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la gestión de secciones"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>

        <?php if($sugerencia === null): ?>
        <p class="text-muted">Indica la matrícula proyectada y la capacidad por sección. El sistema calcula cuántas secciones hacen falta y cómo repartir los alumnos.</p>
        <form action="<?= $this->url('secciones/sugerir'); ?>" method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Matrícula proyectada (alumnos)</label>
                    <input type="number" name="matricula" class="form-control" min="1" value="<?= $matricula > 0 ? $matricula : ''; ?>" aria-label="Matrícula proyectada" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Capacidad por sección (alumnos)</label>
                    <input type="number" name="capacidad" class="form-control" min="1" value="<?= $capacidad > 0 ? $capacidad : ''; ?>" aria-label="Capacidad por sección">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">O usa la capacidad de un aula</label>
                    <select name="aula_id" class="form-select" aria-label="Elegir un aula operativa">
                        <option value="">Escribir capacidad manualmente</option>
                        <?php foreach($aulas as $a): ?>
                            <option value="<?= $a['id']; ?>" <?= $aula_id == $a['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($a['codigo']); ?> (<?= $a['capacidad']; ?> alumnos)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold mt-4" style="background-color: #8B1A1A;" aria-label="Calcular sugerencia de secciones"><i class="bi bi-lightbulb"></i> Sugerir Secciones</button>
        </form>
        <?php else: ?>
        <div class="alert alert-success" role="alert">
            <strong>Secciones sugeridas: <?= $sugerencia['secciones']; ?></strong><br>
            Matrícula proyectada: <?= $matricula; ?> alumnos &nbsp;|&nbsp; Capacidad por sección: <?= $capacidad; ?> alumnos<br>
            Distribución sugerida: <?= implode(', ', $sugerencia['distribucion']); ?> alumnos
        </div>
        <p class="text-muted">Si te gusta la sugerencia, crea las secciones. Los códigos se numeran desde 01 saltando los que ya existen.</p>
        <form action="<?= $this->url('secciones/sugerir'); ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="accion" value="aplicar">
            <input type="hidden" name="matricula" value="<?= $matricula; ?>">
            <input type="hidden" name="capacidad" value="<?= $capacidad; ?>">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trayecto</label>
                    <select name="trayecto" class="form-select" aria-label="Trayecto de las secciones" required>
                        <option value="" disabled <?= $trayecto <= 0 ? 'selected' : ''; ?>>Seleccione...</option>
                        <?php for ($t = 1; $t <= 4; $t++): ?>
                            <option value="<?= $t; ?>" <?= $trayecto == $t ? 'selected' : ''; ?>>Trayecto <?= $t; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Trimestre</label>
                    <select name="trimestre" class="form-select" aria-label="Trimestre de las secciones" required>
                        <option value="" disabled <?= $trimestre <= 0 ? 'selected' : ''; ?>>Seleccione...</option>
                        <?php for ($t = 1; $t <= 3; $t++): ?>
                            <option value="<?= $t; ?>" <?= $trimestre == $t ? 'selected' : ''; ?>>Trimestre <?= $t; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Prefijo de los códigos</label>
                    <input type="text" name="prefijo" class="form-control" maxlength="10" pattern="[A-Za-z0-9]+" placeholder="Ej: SECC" value="<?= htmlspecialchars($prefijo); ?>" aria-label="Prefijo de los códigos" required>
                </div>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold mt-4" style="background-color: #8B1A1A;" aria-label="Crear las secciones sugeridas"><i class="bi bi-check-circle"></i> Crear las Secciones Sugeridas</button>
        </form>
        <div class="mt-3">
            <a href="<?= $this->url('secciones/sugerir'); ?>" class="btn btn-outline-secondary w-100" aria-label="Calcular otra sugerencia">Calcular otra sugerencia</a>
        </div>
        <?php endif; ?>
    </div>
</div>
