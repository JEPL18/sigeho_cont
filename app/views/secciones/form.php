<?php
// app/views/secciones/form.php — Formulario compartido nuevo/editar.
// Migrado de modules/secciones/nuevo.php y editar.php.
// $modo: 'nuevo' | 'editar'. $datos: fila de la sección (modo editar).
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('secciones/nuevo') : $this->url('secciones/editar', ['id' => $id]);
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-people text-danger me-2"></i> Registrar Sección
        <?php else: ?>
            <i class="bi bi-pencil-square text-danger me-2"></i> Editar Sección
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('secciones'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la lista de secciones"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="<?= $form_action; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de Sección (Ej: SECC-34)</label>
                        <!-- PUNTO 13: PATTERN PARA LETRAS, NÚMEROS Y GUIONES -->
                        <input type="text" name="codigo" class="form-control"
                               value="<?= $es_nuevo ? '' : htmlspecialchars($datos['codigo']); ?>"
                               pattern="[a-zA-Z0-9\-]+" title="Solo se permiten letras, números y guiones. Ej: SECC-34"
                               aria-label="<?= $es_nuevo ? 'Ingresar código de sección' : 'Editar código de sección'; ?>" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Trayecto</label>
                            <select name="trayecto" class="form-select" aria-label="<?= $es_nuevo ? 'Seleccionar trayecto' : 'Editar trayecto de la sección'; ?>" required>
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
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Trimestre</label>
                            <!-- PUNTO 13: CLASE solo-numeros AÑADIDA -->
                            <input type="number" name="trimestre" class="form-control solo-numeros"
                                   value="<?= $es_nuevo ? '' : $datos['trimestre']; ?>"
                                   min="1" max="3"
                                   aria-label="<?= $es_nuevo ? 'Ingresar trimestre' : 'Editar trimestre'; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Matrícula</label>
                            <!-- PUNTO 13: CLASE solo-numeros AÑADIDA -->
                            <input type="number" name="cantidad_alumnos" class="form-control solo-numeros"
                                   value="<?= $es_nuevo ? '' : $datos['cantidad_alumnos']; ?>"
                                   min="1" <?= $es_nuevo ? 'placeholder="Alumnos"' : ''; ?>
                                   aria-label="<?= $es_nuevo ? 'Ingresar cantidad de alumnos' : 'Editar cantidad de alumnos'; ?>" required>
                        </div>
                    </div>

                    <?php if ($es_nuevo): ?>
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar nueva sección">
                            <i class="bi bi-save"></i> Guardar Sección
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios de la sección"><i class="bi bi-save"></i> Guardar Cambios</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
