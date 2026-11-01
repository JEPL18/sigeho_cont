<?php
$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) == 'administrador');
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-person-badge text-danger me-2"></i> Nómina Docente</h2>

    <?php if($es_admin): ?>
        <!-- PUNTO 12: aria-label -->
        <a href="<?= $this->url('profesores/nuevo'); ?>" class="btn btn-danger" style="background-color: #8B1A1A;" aria-label="Registrar un nuevo profesor">
            <i class="bi bi-plus-lg"></i> Nuevo Profesor
        </a>
    <?php endif; ?>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'error_uso'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Acción denegada:</strong> No se puede eliminar este docente porque ya tiene bloques de horario asignados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<form method="GET" action="<?= $this->url('profesores'); ?>" class="row g-2 mb-3 area-no-imprimir align-items-end">
    <div class="col-md-3">
        <label class="form-label fw-bold small mb-1">Estado</label>
        <select name="estado" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por estado">
            <option value="">Todos</option>
            <option value="ACTIVO" <?= (($filtro_estado ?? '') == 'ACTIVO') ? 'selected' : ''; ?>>Activos</option>
            <option value="INACTIVO" <?= (($filtro_estado ?? '') == 'INACTIVO') ? 'selected' : ''; ?>>Inactivos</option>
        </select>
    </div>
    <div class="col-md-3">
        <a href="<?= $this->url('profesores'); ?>" class="btn btn-outline-secondary w-100">Limpiar filtros</a>
    </div>
    <div class="col-md-6 text-md-end">
        <small class="text-muted">Mostrando <b><?= count($profesores); ?></b> registro(s)</small>
    </div>
</form>

<div class="row mb-3 area-no-imprimir">
    <div class="col-md-5 ms-auto">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-danger">
                <i class="bi bi-search"></i>
            </span>
            <!-- PUNTO 12: aria-label -->
            <input type="text" id="buscadorGeneral" class="form-control border-start-0" placeholder="Buscar por cédula, nombre o estado..." aria-label="Buscar en la nómina docente">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <!-- PUNTO 5: Adaptabilidad móvil -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre Completo</th>
                        <th>Estado</th>
                        <th>Disponibilidad</th>
                        <th>Materias</th>
                        <?php if($es_admin): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($profesores as $p): ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['cedula']); ?></td>

                        <td>
                            <?php echo htmlspecialchars($p['nombre']); ?>
                        </td>

                        <td>
                            <?php if($p['estado'] == 'ACTIVO'): ?>
                                <span class="badge bg-success">Activo</span>
                            <?php else: ?>
                                <span class="badge bg-danger">Inactivo</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-start">
                            <?php if (!empty($disponibilidad_por_profesor[$p['id']])): ?>
                                <?php echo htmlspecialchars(implode(', ', $disponibilidad_por_profesor[$p['id']])); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin restricción</span>
                            <?php endif; ?>
                        </td>

                        <td class="text-start">
                            <?php if (!empty($materias_por_profesor[$p['id']])): ?>
                                <?php echo htmlspecialchars(implode(', ', $materias_por_profesor[$p['id']])); ?>
                            <?php else: ?>
                                <span class="text-muted">Sin restricción</span>
                            <?php endif; ?>
                        </td>

                        <?php if($es_admin): ?>
                        <td>
                            <div class="btn-group">
                                <!-- PUNTO 12: Etiquetas descriptivas dinámicas -->
                                <a href="<?= $this->url('profesores/editar', ['id' => $p['id']]); ?>" class="btn btn-sm btn-outline-primary" aria-label="Editar al profesor <?php echo htmlspecialchars($p['nombre']); ?>"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= $this->url('profesores/eliminar', ['id' => $p['id']]); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar profesor?');" aria-label="Eliminar al profesor <?php echo htmlspecialchars($p['nombre']); ?>" style="display:inline;"><?= csrf_field() ?><button type="submit"><i class="bi bi-trash"></i></button></form>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
