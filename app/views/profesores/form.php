<?php
// app/views/profesores/form.php — Formulario compartido nuevo/editar.
// Migrado de modules/profesores/nuevo.php y editar.php.
// $modo: 'nuevo' | 'editar'. $datos: fila del profesor (modo editar).
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('profesores/nuevo') : $this->url('profesores/editar', ['id' => $id]);
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-person-plus text-danger me-2"></i> Registrar Profesor
        <?php else: ?>
            <i class="bi bi-pencil-square text-danger me-2"></i> Editar Profesor
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('profesores'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la lista de docentes"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="<?= $form_action; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cédula de Identidad</label>
                        <input type="text" name="cedula" class="form-control solo-numeros"
                               value="<?= $es_nuevo ? '' : htmlspecialchars($datos['cedula']); ?>"
                               placeholder="<?= $es_nuevo ? 'Ej: 12345678' : ''; ?>"
                               pattern="[0-9]{7,8}" title="Debe contener 7 u 8 números. Sin puntos ni letras." maxlength="8"
                               aria-label="<?= $es_nuevo ? 'Ingresar cédula de identidad' : 'Editar cédula de identidad'; ?>" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Título</label>
                            <?php $titulos = ['Prof.', 'Lcdo.', 'Lcda.', 'Ing.', 'Abg.', 'Dr.', 'Dra.', 'Mgs.', 'Econ.']; ?>
                            <select name="titulo" class="form-select" required>
                                <?php if ($es_nuevo): ?>
                                    <?php foreach($titulos as $t): ?>
                                        <option value="<?= $t; ?>"><?= $t; ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach($titulos as $t): ?>
                                        <option value="<?= $t; ?>" <?= ($datos['titulo_actual'] == $t) ? 'selected' : ''; ?>><?= $t; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre Completo (Sin el título)</label>
                            <input type="text" name="nombre" class="form-control solo-letras"
                                   value="<?= $es_nuevo ? '' : htmlspecialchars($datos['nombre']); ?>"
                                   placeholder="<?= $es_nuevo ? 'Ej: Alexis Mujica' : ''; ?>"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+" title="Solo se permiten letras, espacios y puntos."
                                   aria-label="<?= $es_nuevo ? 'Ingresar nombre completo' : 'Editar nombre completo'; ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <?php if ($es_nuevo): ?>
                            <label class="form-label fw-bold">Estado en la Institución</label>
                            <select name="estado" class="form-select" aria-label="Seleccionar estado del docente" required>
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO (Reposo/Jubilado)</option>
                            </select>
                        <?php else: ?>
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select" aria-label="Editar estado del docente" required>
                                <option value="ACTIVO" <?= ($datos['estado'] == 'ACTIVO') ? 'selected' : ''; ?>>ACTIVO</option>
                                <option value="INACTIVO" <?= ($datos['estado'] == 'INACTIVO') ? 'selected' : ''; ?>>INACTIVO</option>
                            </select>
                        <?php endif; ?>
                    </div>

                    <?php if ($es_nuevo): ?>
                        <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar nuevo docente">
                            <i class="bi bi-save"></i> Guardar Docente
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios del docente"><i class="bi bi-save"></i> Guardar Cambios</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
