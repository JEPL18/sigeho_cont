<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-journal-text text-danger me-2"></i> Bitácora del Sistema</h2>
    <form action="<?= $this->url('bitacora'); ?>" method="POST" class="m-0" onsubmit="return confirm('¿Vaciar toda la bitácora? Esta acción no se puede deshacer.');">
        <?= csrf_field() ?>
        <input type="hidden" name="accion" value="vaciar">
        <button type="submit" class="btn btn-outline-danger" aria-label="Vaciar la bitácora"><i class="bi bi-trash"></i> Vaciar Bitácora</button>
    </form>
</div>

<?php if($msg == 'vaciada'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Bitácora vaciada.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<p class="text-muted">Últimas 100 acciones registradas en el sistema, de la más reciente a la más antigua. Solo el administrador puede ver esta página.</p>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Usuario</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($entradas)): ?>
                        <tr><td colspan="3" class="text-center text-muted py-4">Sin registros todavía.</td></tr>
                    <?php else: ?>
                        <?php foreach($entradas as $e): ?>
                        <tr>
                            <td><?= date("d/m/Y H:i", strtotime($e['fecha'])); ?></td>
                            <td><?= htmlspecialchars($e['usuario'] ?? ('ID ' . $e['usuario_id'])); ?></td>
                            <td><?= htmlspecialchars($e['accion']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
