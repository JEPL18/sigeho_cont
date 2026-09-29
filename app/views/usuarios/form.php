<?php
// app/views/usuarios/form.php — Formulario compartido nuevo/editar.
// Migrado de modules/usuarios/nuevo.php y editar.php.
// $modo: 'nuevo' | 'editar'. $datos: fila del usuario (modo editar).
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('usuarios/nuevo') : $this->url('usuarios/editar', ['id' => $id]);
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-person-plus-fill text-info me-2"></i> Crear Nueva Cuenta
        <?php else: ?>
            <i class="bi bi-person-gear text-info me-2"></i> Editar Cuenta
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('usuarios'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la lista de usuarios"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="<?= $form_action; ?>" method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Personal</label>
                            <input type="text" name="nombre" class="form-control solo-letras"
                                   value="<?= $es_nuevo ? '' : htmlspecialchars($datos['nombre']); ?>"
                                   placeholder="<?= $es_nuevo ? 'Ej: Lcda. María Pérez' : ''; ?>"
                                   pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+" title="Solo se permiten letras, espacios y puntos."
                                   aria-label="<?= $es_nuevo ? 'Ingresar nombre del personal' : 'Editar nombre del personal'; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold"><?= $es_nuevo ? 'Correo Institucional (Acceso)' : 'Correo Institucional'; ?></label>
                            <input type="email" name="correo" class="form-control"
                                   value="<?= $es_nuevo ? '' : htmlspecialchars($datos['correo']); ?>"
                                   placeholder="<?= $es_nuevo ? 'maria@uptag.edu.ve' : ''; ?>"
                                   aria-label="<?= $es_nuevo ? 'Ingresar correo institucional' : 'Editar correo institucional'; ?>" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <?php if ($es_nuevo): ?>
                                <label class="form-label fw-bold">Nivel de Permisos</label>
                                <select name="rol" class="form-select" aria-label="Seleccionar nivel de permisos" required>
                                    <option value="Asistente">Asistente (Solo Lectura)</option>
                                    <option value="Administrador">Administrador (Control Total)</option>
                                </select>
                            <?php else: ?>
                                <label class="form-label fw-bold">Rol</label>
                                <select name="rol" class="form-select" aria-label="Seleccionar nivel de permisos" required <?= ($id == $_SESSION['usuario_id']) ? 'disabled' : ''; ?>>
                                    <option value="Asistente" <?= (strtolower($datos['rol']) == 'asistente') ? 'selected' : ''; ?>>Asistente</option>
                                    <option value="Administrador" <?= (strtolower($datos['rol']) == 'administrador') ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                                <?php if($id == $_SESSION['usuario_id']): ?>
                                    <input type="hidden" name="rol" value="Administrador">
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($es_nuevo): ?>
                                <label class="form-label fw-bold">Contraseña de Acceso</label>
                                <input type="password" name="password" class="form-control" placeholder="Mínimo 16 caracteres" minlength="16" aria-label="Ingresar contraseña" required>
                            <?php else: ?>
                                <label class="form-label fw-bold">Nueva Contraseña</label>
                                <input type="password" name="password" class="form-control" placeholder="(Dejar en blanco para no cambiar)" minlength="16" aria-label="Ingresar nueva contraseña">
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="fw-bold text-danger border-bottom pb-2 mb-3"><i class="bi bi-shield-lock-fill"></i> Sistema de Recuperación (3 Pasos)</h5>

                    <div class="row mb-3 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <span class="fw-bold text-dark"><i class="bi bi-1-circle-fill text-danger me-1"></i> ¿Fecha de cumpleaños?</span>
                            <?php if ($es_nuevo): ?><small class="d-block text-muted">(Ej: 15/04/1990)</small><?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="respuesta_seguridad_1" class="form-control border-danger"
                                   <?= $es_nuevo ? '' : 'placeholder="(Dejar en blanco para no cambiar)"'; ?>
                                   aria-label="<?= $es_nuevo ? 'Respuesta de fecha de cumpleaños' : 'Respuesta de seguridad 1'; ?>"
                                   <?= $es_nuevo ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <div class="row mb-3 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <span class="fw-bold text-dark"><i class="bi bi-2-circle-fill text-danger me-1"></i> ¿Nombre del colegio donde estudiaste?</span>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="respuesta_seguridad_2" class="form-control border-danger"
                                   <?= $es_nuevo ? '' : 'placeholder="(Dejar en blanco para no cambiar)"'; ?>
                                   aria-label="<?= $es_nuevo ? 'Respuesta de nombre del colegio' : 'Respuesta de seguridad 2'; ?>"
                                   <?= $es_nuevo ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <div class="row mb-4 bg-light p-3 rounded align-items-center">
                        <div class="col-md-6">
                            <span class="fw-bold text-dark"><i class="bi bi-3-circle-fill text-danger me-1"></i> ¿Ciudad donde naciste?</span>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="respuesta_seguridad_3" class="form-control border-danger"
                                   <?= $es_nuevo ? '' : 'placeholder="(Dejar en blanco para no cambiar)"'; ?>
                                   aria-label="<?= $es_nuevo ? 'Respuesta de ciudad de nacimiento' : 'Respuesta de seguridad 3'; ?>"
                                   <?= $es_nuevo ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <?php if ($es_nuevo): ?>
                        <button type="submit" class="btn btn-info text-white w-100 fw-bold fs-5" aria-label="Guardar nuevo usuario"><i class="bi bi-check-circle"></i> Guardar Usuario</button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary w-100 fw-bold fs-5" aria-label="Guardar cambios del usuario"><i class="bi bi-save"></i> Guardar Cambios</button>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>
