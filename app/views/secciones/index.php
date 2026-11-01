<?php
$es_admin = (isset($_SESSION['rol']) && strtolower($_SESSION['rol']) == 'administrador');
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-people text-danger me-2"></i> Gestión de Secciones</h2>

    <?php if($es_admin): ?>
        <div class="d-flex gap-2">
            <a href="<?= $this->url('secciones/sugerir'); ?>" class="btn btn-outline-danger" aria-label="Sugerir secciones automáticamente">
                <i class="bi bi-lightbulb"></i> Sugerir Secciones
            </a>
            <a href="<?= $this->url('secciones/nuevo'); ?>" class="btn btn-danger" style="background-color: #8B1A1A;" aria-label="Registrar nueva sección">
                <i class="bi bi-plus-lg"></i> Registrar Sección
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'error_uso'): ?>
    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Acción denegada:</strong> No se puede eliminar esta sección porque ya tiene bloques de horario asignados.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'secciones_creadas'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Secciones sugeridas creadas correctamente<?= isset($_GET['n']) ? ' (' . (int)$_GET['n'] . ' secciones).' : '.'; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<form method="GET" action="<?= $this->url('secciones'); ?>" class="row g-2 mb-3 area-no-imprimir align-items-end">
    <div class="col-md-3">
        <label class="form-label fw-bold small mb-1">Trayecto</label>
        <select name="trayecto" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por trayecto">
            <option value="">Todos</option>
            <?php for ($t = 1; $t <= 4; $t++): ?>
                <option value="<?= $t; ?>" <?= (($filtro_trayecto ?? 0) == $t) ? 'selected' : ''; ?>>Trayecto <?= $t; ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold small mb-1">Trimestre</label>
        <select name="trimestre" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por trimestre">
            <option value="">Todos</option>
            <?php for ($t = 1; $t <= 3; $t++): ?>
                <option value="<?= $t; ?>" <?= (($filtro_trimestre ?? 0) == $t) ? 'selected' : ''; ?>>Trimestre <?= $t; ?></option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-2">
        <a href="<?= $this->url('secciones'); ?>" class="btn btn-outline-secondary w-100">Limpiar</a>
    </div>
    <div class="col-md-4 text-md-end">
        <small class="text-muted">Mostrando <b><?= count($secciones); ?></b> registro(s)</small>
    </div>
</form>

<div class="row mb-3 area-no-imprimir">
    <div class="col-md-5 ms-auto">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-danger">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" id="buscadorGeneral" class="form-control border-start-0" placeholder="Buscar por código, trayecto o trimestre..." aria-label="Buscar en la tabla de secciones">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle text-center">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Código de Sección</th>
                        <th>Trayecto</th>
                        <th>Trimestre</th>
                        <th>Matrícula (Alumnos)</th>
                        <?php if($es_admin): ?>
                            <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($secciones as $s): ?>
                    <tr>
                        <td class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($s['codigo']); ?></td>
                        <td>
                            <span class="badge bg-secondary">
                                <?php echo ($s['trayecto'] == 0) ? 'Trayecto Inicial' : 'Trayecto ' . $s['trayecto']; ?>
                            </span>
                        </td>
                        <td>Trimestre <?php echo $s['trimestre']; ?></td>
                        <td><i class="bi bi-person-fill text-muted me-1"></i> <?php echo $s['cantidad_alumnos']; ?></td>

                        <?php if($es_admin): ?>
                        <td>
                            <div class="btn-group">
                                <!-- PUNTO 12: aria-label dinámico -->
                                <a href="<?= $this->url('secciones/editar', ['id' => $s['id']]); ?>" class="btn btn-sm btn-outline-primary" aria-label="Editar sección <?php echo htmlspecialchars($s['codigo']); ?>" title="Editar"><i class="bi bi-pencil"></i></a>
                                <form method="POST" action="<?= $this->url('secciones/eliminar', ['id' => $s['id']]); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Borrar sección?');" aria-label="Eliminar sección <?php echo htmlspecialchars($s['codigo']); ?>" style="display:inline;"><?= csrf_field() ?><button type="submit" title="Eliminar"><i class="bi bi-trash"></i></button></form>
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
