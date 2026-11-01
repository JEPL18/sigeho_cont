<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-lightbulb text-danger me-2"></i> Sugerir Huecos Libres</h2>
    <a href="<?= $this->url('horarios'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la gestión de horarios"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>

        <p class="text-muted">Elige profesor, materia y sección. El sistema propone bloques libres cruzando la disponibilidad del profesor, la capacidad de las aulas y los horarios ya asignados. La sección es obligatoria porque el horario se crea para una sección real.</p>
        <form action="<?= $this->url('horarios/sugerir'); ?>" method="POST">
            <?= csrf_field() ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Profesor</label>
                    <select name="profesor_id" class="form-select" aria-label="Seleccionar profesor" required>
                        <option value="" disabled <?= $profesor_id <= 0 ? 'selected' : ''; ?>>Seleccione...</option>
                        <?php foreach($profesores as $p): ?>
                            <option value="<?= $p['id']; ?>" <?= $profesor_id == $p['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($p['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Materia</label>
                    <select name="materia_id" class="form-select" aria-label="Seleccionar materia" required>
                        <option value="" disabled <?= $materia_id <= 0 ? 'selected' : ''; ?>>Seleccione...</option>
                        <?php foreach($materias as $m): ?>
                            <option value="<?= $m['id']; ?>" <?= $materia_id == $m['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Sección</label>
                    <select name="seccion_id" class="form-select" aria-label="Seleccionar sección" required>
                        <option value="" disabled <?= $seccion_id <= 0 ? 'selected' : ''; ?>>Seleccione...</option>
                        <?php foreach($secciones as $s): ?>
                            <option value="<?= $s['id']; ?>" <?= $seccion_id == $s['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($s['codigo']); ?> (<?= $s['cantidad_alumnos']; ?> alumnos)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Duración (minutos)</label>
                    <input type="number" name="duracion" class="form-control" min="1" max="720" value="<?= $duracion; ?>" aria-label="Duración en minutos" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Día</label>
                    <select name="dia" class="form-select" aria-label="Filtrar por día">
                        <option value="">Todos</option>
                        <?php foreach($dias_validos as $d): ?>
                            <option value="<?= $d; ?>" <?= $dia === $d ? 'selected' : ''; ?>><?= $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Desde</label>
                    <input type="time" name="desde" class="form-control" value="<?= htmlspecialchars($desde); ?>" aria-label="Hora inicial del rango">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Hasta</label>
                    <input type="time" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta); ?>" aria-label="Hora final del rango">
                </div>
            </div>
            <button type="submit" class="btn btn-danger w-100 fw-bold mt-4" style="background-color: #8B1A1A;" aria-label="Buscar huecos libres"><i class="bi bi-lightbulb"></i> Sugerir Huecos</button>
        </form>

        <?php if($resultado !== null): ?>
        <hr class="my-4">
        <div class="alert alert-success" role="alert">
            <strong>Profesor:</strong> <?= htmlspecialchars($datos_profesor['nombre'] ?? ''); ?> &nbsp;|&nbsp;
            <strong>Materia:</strong> <?= htmlspecialchars($datos_materia['nombre'] ?? ''); ?> &nbsp;|&nbsp;
            <strong>Sección:</strong> <?= htmlspecialchars($datos_seccion['codigo'] ?? ''); ?> &nbsp;|&nbsp;
            <strong>Duración:</strong> <?= $duracion; ?> min<br>
            Mostrando <?= count($resultado['huecos']); ?> de <?= $resultado['total']; ?> huecos libres.
        </div>
        <?php if(empty($resultado['huecos'])): ?>
            <p class="text-muted">Sin huecos libres con esos criterios. Cambia el día, el rango de horas o la duración.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead class="table-dark">
                    <tr>
                        <th>Día</th>
                        <th>Hora</th>
                        <th>Aula</th>
                        <th>Capacidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resultado['huecos'] as $h): ?>
                    <tr>
                        <td><?= htmlspecialchars($h['dia']); ?></td>
                        <td><?= substr($h['hora_inicio'], 0, 5); ?> - <?= substr($h['hora_fin'], 0, 5); ?></td>
                        <td><?= htmlspecialchars($h['aula']); ?></td>
                        <td><?= $h['capacidad']; ?> alumnos</td>
                        <td>
                            <form action="<?= $this->url('horarios/sugerir'); ?>" method="POST" class="m-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="accion" value="aplicar">
                                <input type="hidden" name="profesor_id" value="<?= $profesor_id; ?>">
                                <input type="hidden" name="materia_id" value="<?= $materia_id; ?>">
                                <input type="hidden" name="seccion_id" value="<?= $seccion_id; ?>">
                                <input type="hidden" name="dia" value="<?= htmlspecialchars($h['dia']); ?>">
                                <input type="hidden" name="hora_inicio" value="<?= substr($h['hora_inicio'], 0, 5); ?>">
                                <input type="hidden" name="hora_fin" value="<?= substr($h['hora_fin'], 0, 5); ?>">
                                <input type="hidden" name="aula_id" value="<?= $h['aula_id']; ?>">
                                <button type="submit" class="btn btn-success btn-sm" aria-label="Crear horario en este hueco"><i class="bi bi-check-circle"></i> Crear Horario</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
