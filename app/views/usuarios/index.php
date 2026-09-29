<?php
// app/views/usuarios/index.php — Cuentas de acceso.
// Migrada de modules/usuarios/index.php.
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-person-badge-fill text-info me-2"></i> Cuentas de Acceso</h2>
    <a href="<?= $this->url('usuarios/nuevo'); ?>" class="btn btn-info text-white fw-bold" aria-label="Registrar un nuevo usuario">
        <i class="bi bi-person-plus-fill"></i> Registrar Usuario
    </a>
</div>

<?php if(isset($_GET['msg'])): ?>
    <?php if($_GET['msg'] == 'eliminado'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> Usuario revocado y eliminado del sistema.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
        </div>
    <?php elseif($_GET['msg'] == 'desbloqueado'): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-unlock-fill me-2"></i> El usuario ha sido desbloqueado y sus intentos reiniciados.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
        </div>
    <?php elseif($_GET['msg'] == 'error_uso'): ?>
        <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Error:</strong> No se pudo eliminar el usuario por dependencias en el sistema.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
        </div>
    <?php elseif($_GET['msg'] == 'error_propio'): ?>
        <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4 text-dark">
            <i class="bi bi-shield-exclamation me-2"></i> <strong>Acción denegada:</strong> No puedes eliminar tu propia cuenta activa.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
        </div>
    <?php endif; ?>
<?php endif; ?>

<div class="row mb-3 area-no-imprimir">
    <div class="col-md-5 ms-auto">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-info">
                <i class="bi bi-search"></i>
            </span>
            <!-- PUNTO 12: aria-label en el buscador -->
            <input type="text" id="buscadorGeneral" class="form-control border-start-0" placeholder="Buscar por nombre, correo o rol..." aria-label="Buscar en la tabla de usuarios">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Nombre del Personal</th>
                        <th>Correo (Acceso)</th>
                        <th>Nivel de Privilegios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($usuarios as $u): ?>
                    <tr>
                        <td class="fw-bold text-start ps-4"><?php echo htmlspecialchars($u['nombre']); ?></td>
                        <td class="text-muted"><?php echo htmlspecialchars($u['correo']); ?></td>
                        <td>
                            <?php if(strtolower($u['rol']) == 'administrador'): ?>
                                <span class="badge bg-danger"><i class="bi bi-shield-lock-fill"></i> Administrador</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-eye-fill"></i> Asistente</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($u['bloqueado'] == 1): ?>
                                <span class="badge bg-danger border border-light"><i class="bi bi-lock-fill"></i> Bloqueado</span>
                            <?php else: ?>
                                <span class="badge bg-success border border-light"><i class="bi bi-unlock-fill"></i> Activo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group">
                                <!-- PUNTO 12: aria-label dinámico para el botón de editar -->
                                <a href="<?= $this->url('usuarios/editar', ['id' => $u['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Editar / Cambiar Clave" aria-label="Editar usuario <?php echo htmlspecialchars($u['nombre']); ?>"><i class="bi bi-pencil"></i></a>

                                <?php if($u['bloqueado'] == 1): ?>
                                    <!-- PUNTO 12: aria-label dinámico para el botón de desbloqueo -->
                                    <a href="<?= $this->url('usuarios/desbloquear', ['id' => $u['id']]); ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('¿Seguro que deseas restaurar el acceso a este usuario?');" title="Desbloquear Cuenta" aria-label="Desbloquear usuario <?php echo htmlspecialchars($u['nombre']); ?>"><i class="bi bi-unlock"></i></a>
                                <?php endif; ?>

                                <?php if($u['id'] != $_SESSION['usuario_id']): ?>
                                    <!-- PUNTO 12: aria-label dinámico para el botón de eliminar -->
                                    <a href="<?= $this->url('usuarios/eliminar', ['id' => $u['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" aria-label="Eliminar usuario <?php echo htmlspecialchars($u['nombre']); ?>"><i class="bi bi-trash"></i></a>
                                <?php else: ?>
                                    <!-- PUNTO 12: aria-label explicando por qué está deshabilitado -->
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="No puedes eliminar tu propia cuenta" aria-label="Botón deshabilitado, no puedes eliminar tu propia cuenta"><i class="bi bi-trash"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
