<?php
// app/views/horarios/index.php — Gestión de horarios.
// Migrada de modules/horarios/index.php.
$es_admin = (strtolower($_SESSION['rol']) == 'administrador');
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0"><i class="bi bi-calendar-week-fill text-danger me-2"></i> Gestión de Horarios</h2>

    <?php if($es_admin): ?>
        <!-- PUNTO 12: ARIA-LABEL -->
        <a href="<?= $this->url('horarios/nuevo'); ?>" class="btn btn-danger" style="background-color: #8B1A1A;" aria-label="Asignar un nuevo bloque de horario">
            <i class="bi bi-plus-lg"></i> Asignar Nuevo Bloque
        </a>
    <?php endif; ?>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Bloque de horario eliminado.
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
            <input type="text" id="buscadorGeneral" class="form-control border-start-0" placeholder="Buscar por día, sección, materia, profesor o aula..." aria-label="Buscar en la tabla de horarios">
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <!-- PUNTO 5: CONTENEDOR RESPONSIVO -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Día y Hora</th>
                        <th>Sección</th>
                        <th>Materia</th>
                        <th>Profesor</th>
                        <th>Aula</th>
                        <?php if($es_admin): ?>
                            <th class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($horarios) > 0): ?>
                        <?php foreach($horarios as $h): ?>
                        <tr>
                            <td>
                                <strong class="text-success"><?php echo $h['dia_semana']; ?></strong><br>
                                <small class="text-muted fw-bold">
                                    <?php echo date("h:i A", strtotime($h['hora_inicio'])) . " - " . date("h:i A", strtotime($h['hora_fin'])); ?>
                                </small>
                            </td>
                            <td class="fw-bold">
                                <?php
                                $texto_tray = ($h['trayecto'] == 0) ? 'Inicial' : 'T'.$h['trayecto'];
                                echo htmlspecialchars($h['seccion_cod']) . " <br><small class='text-muted'>($texto_tray)</small>";
                                ?>
                            </td>
                            <td class="text-muted"><?php echo htmlspecialchars($h['materia_nombre']); ?></td>

                            <!-- MOSTRAMOS EL TÍTULO Y EL NOMBRE DEL PROFESOR -->
                            <td>
                                <small class="text-muted fw-bold d-block"><?php echo htmlspecialchars($h['profesor_titulo']); ?></small>
                                <?php echo htmlspecialchars($h['profesor_nombre']); ?>
                            </td>

                            <td class="fw-bold text-danger"><?php echo htmlspecialchars($h['aula_cod']); ?></td>

                            <?php if($es_admin): ?>
                            <td class="text-center">
                                <div class="btn-group">
                                    <!-- PUNTO 12: ARIA-LABEL DINÁMICO EXPLICANDO QUÉ SE EDITA/BORRA -->
                                    <a href="<?= $this->url('horarios/editar', ['id' => $h['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar bloque del <?php echo $h['dia_semana']; ?> de <?php echo htmlspecialchars($h['materia_nombre']); ?>"><i class="bi bi-pencil"></i></a>
                                    <a href="<?= $this->url('horarios/eliminar', ['id' => $h['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Borrar este bloque?');" aria-label="Eliminar bloque del <?php echo $h['dia_semana']; ?> de <?php echo htmlspecialchars($h['materia_nombre']); ?>"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="<?php echo $es_admin ? '6' : '5'; ?>" class="text-center py-5 text-muted">No hay horarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
