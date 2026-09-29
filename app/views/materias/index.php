<?php
// app/views/materias/index.php — Malla curricular.
// Migrada de modules/materias/index.php.
$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) == 'administrador');
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-journal-text text-danger me-2"></i> Malla Curricular</h2>

    <?php if($es_admin): ?>
        <a href="<?= $this->url('materias/nuevo'); ?>" class="btn btn-danger" style="background-color: #8B1A1A;" aria-label="Registrar nueva materia">
            <i class="bi bi-plus-lg"></i> Registrar Materia
        </a>
    <?php endif; ?>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'error_uso'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Acción denegada:</strong> No se puede eliminar esta materia porque ya está asignada a un bloque de horario.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<div class="row mb-3 area-no-imprimir">
    <div class="col-md-5 ms-auto">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-danger">
                <i class="bi bi-search"></i>
            </span>
            <!-- PUNTO 12: ARIA-LABEL -->
            <input type="text" id="buscadorGeneral" class="form-control border-start-0" placeholder="Buscar por código, materia o trayecto..." aria-label="Buscar en la malla curricular">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <!-- PUNTO 5: TABLA RESPONSIVA -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Código</th>
                        <th>Unidad Curricular</th>
                        <th>Trayecto</th>
                        <th>Horas Semanales</th>
                        <?php if($es_admin): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($materias as $m): ?>
                    <tr>
                        <td class="fw-bold text-secondary"><?php echo htmlspecialchars($m['codigo']); ?></td>
                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($m['nombre']); ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ($m['trayecto'] == 0) ? 'Trayecto Inicial' : 'Trayecto ' . $m['trayecto']; ?>
                            </span>
                        </td>
                        <td><i class="bi bi-clock-history me-1"></i><?php echo $m['horas_semanales']; ?></td>

                        <?php if($es_admin): ?>
                        <td>
                            <div class="btn-group">
                                <!-- PUNTO 12: ARIA-LABEL DINÁMICO -->
                                <a href="<?= $this->url('materias/editar', ['id' => $m['id']]); ?>" class="btn btn-sm btn-outline-primary" aria-label="Editar materia <?php echo htmlspecialchars($m['nombre']); ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                                <a href="<?= $this->url('materias/eliminar', ['id' => $m['id']]); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar materia?');" aria-label="Eliminar materia <?php echo htmlspecialchars($m['nombre']); ?>" title="Eliminar"><i class="bi bi-trash"></i></a>
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
