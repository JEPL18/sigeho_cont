<?php
// app/views/materias/form.php — Formulario compartido nuevo/editar.
// Migrado de modules/materias/nuevo.php y editar.php.
// $modo: 'nuevo' | 'editar'. $datos: fila de la materia (modo editar).
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('materias/nuevo') : $this->url('materias/editar', ['id' => $id]);
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-journal-plus text-danger me-2"></i> Registrar Materia
        <?php else: ?>
            <i class="bi bi-pencil-square text-danger me-2"></i> Editar Materia
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('materias'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la malla curricular"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="<?= $form_action; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= $es_nuevo ? 'Código Oficial (Ej: CON-221)' : 'Código Oficial'; ?></label>
                        <!-- PUNTOS 12 Y 13: PATTERN ALFANUMÉRICO Y GUIONES -->
                        <input type="text" name="codigo" class="form-control"
                               value="<?= $es_nuevo ? '' : htmlspecialchars($datos['codigo']); ?>"
                               pattern="[a-zA-Z0-9\-]+" title="Solo letras, números y guiones. Ej: CON-221"
                               aria-label="<?= $es_nuevo ? 'Ingresar código de materia' : 'Editar código de materia'; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nombre de la Unidad Curricular</label>
                        <!-- PUNTOS 12 Y 13: CLASE solo-letras -->
                        <input type="text" name="nombre" class="form-control solo-letras"
                               value="<?= $es_nuevo ? '' : htmlspecialchars($datos['nombre']); ?>"
                               pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+" title="Solo se permiten letras, espacios y puntos."
                               aria-label="<?= $es_nuevo ? 'Ingresar nombre de la materia' : 'Editar nombre de la materia'; ?>" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Trayecto</label>
                            <select name="trayecto" class="form-select" aria-label="<?= $es_nuevo ? 'Seleccionar trayecto' : 'Editar trayecto de la materia'; ?>" required>
                                <?php if ($es_nuevo): ?>
                                    <option value="" disabled selected>Seleccione...</option>
                                <?php endif; ?>
                                <option value="0" <?= (!$es_nuevo && $datos['trayecto'] == 0) ? 'selected' : ''; ?>>Trayecto Inicial</option>
                                <option value="1" <?= (!$es_nuevo && $datos['trayecto'] == 1) ? 'selected' : ''; ?>>Trayecto 1</option>
                                <option value="2" <?= (!$es_nuevo && $datos['trayecto'] == 2) ? 'selected' : ''; ?>>Trayecto 2</option>
                                <option value="3" <?= (!$es_nuevo && $datos['trayecto'] == 3) ? 'selected' : ''; ?>>Trayecto 3</option>
                                <option value="4" <?= (!$es_nuevo && $datos['trayecto'] == 4) ? 'selected' : ''; ?>>Trayecto 4</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Horas Semanales</label>
                            <!-- PUNTOS 12 Y 13: CLASE solo-numeros -->
                            <input type="number" name="horas_semanales" class="form-control solo-numeros"
                                   value="<?= $es_nuevo ? '' : $datos['horas_semanales']; ?>"
                                   min="1" max="10"
                                   aria-label="<?= $es_nuevo ? 'Ingresar horas semanales' : 'Editar horas semanales'; ?>" required>
                        </div>
                    </div>

                    <?php if ($es_nuevo): ?>
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar nueva materia">
                            <i class="bi bi-save"></i> Guardar Materia
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios de la materia"><i class="bi bi-save"></i> Guardar Cambios</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
