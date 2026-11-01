<?php
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('aulas/nuevo') : $this->url('aulas/editar', ['id' => $id]);
$recursos_texto = isset($recursos_texto) ? $recursos_texto : '';
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-door-closed text-danger me-2"></i> Registrar Aula
        <?php else: ?>
            <i class="bi bi-pencil-square text-danger me-2"></i> Editar Aula
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('aulas'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la lista de aulas"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="<?= $form_action; ?>" method="POST">
    <?= csrf_field() ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código del Espacio (Ej: M-110)</label>
                        <input type="text" name="codigo" class="form-control"
                               value="<?= $es_nuevo ? '' : htmlspecialchars($datos['codigo']); ?>"
                               pattern="[a-zA-Z0-9\-\s]+" data-bs-toggle="tooltip" data-bs-placement="top" title="Solo letras, números, guiones y espacios. Ej: M-110"
                               aria-label="<?= $es_nuevo ? 'Ingresar código del espacio' : 'Editar código del espacio'; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Capacidad (Número de Alumnos)</label>
                        <input type="number" name="capacidad" class="form-control solo-numeros"
                               value="<?= $es_nuevo ? '' : $datos['capacidad']; ?>"
                               min="1"
                               aria-label="<?= $es_nuevo ? 'Ingresar capacidad de alumnos' : 'Editar capacidad de alumnos'; ?>" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold"><?= $es_nuevo ? 'Estado Inicial' : 'Estado'; ?></label>
                        <select name="estado" class="form-select" aria-label="<?= $es_nuevo ? 'Seleccionar estado inicial' : 'Editar estado del aula'; ?>">
                            <option value="OPERATIVA" <?= (!$es_nuevo && $datos['estado'] == 'OPERATIVA') ? 'selected' : ''; ?>>OPERATIVA</option>
                            <?php if ($es_nuevo): ?>
                                <option value="INOPERATIVA">INOPERATIVA / MANTENIMIENTO</option>
                            <?php else: ?>
                                <option value="INOPERATIVA" <?= ($datos['estado'] == 'INOPERATIVA') ? 'selected' : ''; ?>>INOPERATIVA</option>
                            <?php endif; ?>
                        </select>
                    </div>
                    <details class="mb-4" <?= !empty($recursos_texto) ? 'open' : ''; ?>>
                        <summary class="form-label fw-bold" style="cursor:pointer;">Opciones avanzadas</summary>
                        <div class="mt-2">
                            <label class="form-label fw-bold">Recursos (separados por comas, máximo 15)</label>
                            <input type="text" name="recursos" class="form-control"
                                   value="<?= htmlspecialchars($recursos_texto); ?>"
                                   placeholder="Ej: Proyector, Aire acondicionado, Pizarra acrílica"
                                   maxlength="500"
                                   aria-label="Recursos del aula">
                        </div>
                    </details>
                    <script>
                    document.querySelector('form').addEventListener('submit', function(e) {
                        var valor = document.querySelector('input[name="recursos"]').value;
                        var items = valor.split(',').map(function(s) { return s.trim(); }).filter(function(s) { return s !== ''; });
                        if (items.length > 15) {
                            e.preventDefault();
                            alert('Máximo 15 recursos por aula.');
                        }
                    });
                    </script>
                    <?php if ($es_nuevo): ?>
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar nueva aula">
                            <i class="bi bi-save"></i> Guardar Aula
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios del aula"><i class="bi bi-save"></i> Guardar Cambios</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
