<?php
$es_admin = (strtolower($_SESSION['rol']) == 'administrador');
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 area-no-imprimir">
    <h2 class="fw-bold mb-0"><i class="bi bi-calendar-week-fill text-danger me-2"></i> Gestión de Horarios</h2>

    <div class="d-flex gap-2">
        <a href="<?= $this->url('horarios/porProfesor'); ?>" class="btn btn-outline-secondary" aria-label="Ver horarios por profesor">
            <i class="bi bi-person-lines-fill"></i> Por Profesor
        </a>
        <?php if($es_admin): ?>
            <a href="<?= $this->url('horarios/sugerir'); ?>" class="btn btn-outline-danger" aria-label="Sugerir huecos libres">
                <i class="bi bi-lightbulb"></i> Sugerir Huecos
            </a>
            <!-- PUNTO 12: ARIA-LABEL -->
            <a href="<?= $this->url('horarios/nuevo'); ?>" class="btn btn-danger" style="background-color: #8B1A1A;" aria-label="Asignar un nuevo bloque de horario">
                <i class="bi bi-plus-lg"></i> Asignar Nuevo Bloque
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'eliminado'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Bloque de horario eliminado.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<?php if(isset($_GET['msg']) && $_GET['msg'] == 'horario_creado'): ?>
    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> Horario creado desde la sugerencia.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar alerta"></button>
    </div>
<?php endif; ?>

<form method="GET" action="<?= $this->url('horarios'); ?>" class="row g-2 mb-3 area-no-imprimir align-items-end">
    <div class="col-md-3">
        <label class="form-label fw-bold small mb-1">Sección</label>
        <select name="seccion_id" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por sección">
            <option value="">Todas</option>
            <?php foreach(($secciones ?? []) as $s): ?>
                <option value="<?= $s['id']; ?>" <?= (($filtro_seccion ?? 0) == $s['id']) ? 'selected' : ''; ?>>Tray. <?= $s['trayecto']; ?> - Trim. <?= $s['trimestre']; ?> | <?= htmlspecialchars($s['codigo']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-bold small mb-1">Día</label>
        <select name="dia" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por día">
            <option value="">Todos</option>
            <?php foreach(['Lunes','Martes','Miercoles','Jueves','Viernes','Sabado'] as $d): ?>
                <option value="<?= $d; ?>" <?= (($filtro_dia ?? '') == $d) ? 'selected' : ''; ?>><?= $d; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-bold small mb-1">Turno</label>
        <select name="turno" class="form-select shadow-sm" onchange="this.form.submit()" aria-label="Filtrar por turno">
            <option value="">Todos</option>
            <option value="MATUTINO" <?= (($filtro_turno ?? '') == 'MATUTINO') ? 'selected' : ''; ?>>Matutino</option>
            <option value="VESPERTINO" <?= (($filtro_turno ?? '') == 'VESPERTINO') ? 'selected' : ''; ?>>Vespertino</option>
            <option value="NOCTURNO" <?= (($filtro_turno ?? '') == 'NOCTURNO') ? 'selected' : ''; ?>>Nocturno</option>
        </select>
    </div>
    <div class="col-md-2">
        <a href="<?= $this->url('horarios'); ?>" class="btn btn-outline-secondary w-100">Limpiar</a>
    </div>
    <div class="col-md-3 text-md-end">
        <small class="text-muted">Mostrando <b><?= count($horarios); ?></b> registro(s)</small>
    </div>
</form>

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

<div class="row mb-3 area-no-imprimir">
    <div class="col-md-12 text-end">
        <div class="btn-group">
            <button class="btn btn-outline-danger btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
            <button class="btn btn-success btn-sm" onclick="exportarExcel('tablaHorarios', 'Horarios_Contaduria')"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <!-- PUNTO 5: CONTENEDOR RESPONSIVO -->
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle" id="tablaHorarios">
                <thead class="table-dark" style="background-color: #343a40;">
                    <tr>
                        <th>Día y Hora</th>
                        <th>Sección</th>
                        <th>Materia</th>
                        <th>Profesor</th>
                        <th>Aula</th>
                        <?php if($es_admin): ?>
                            <th class="text-center area-no-imprimir">Acciones</th>
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

                            <td>
                                <?php echo htmlspecialchars($h['profesor_nombre']); ?>
                            </td>

                            <td class="fw-bold text-danger"><?php echo htmlspecialchars($h['aula_cod']); ?></td>

                            <?php if($es_admin): ?>
                            <td class="text-center area-no-imprimir">
                                <div class="btn-group">
                                    <!-- PUNTO 12: ARIA-LABEL DINÁMICO EXPLICANDO QUÉ SE EDITA/BORRA -->
                                    <a href="<?= $this->url('horarios/editar', ['id' => $h['id']]); ?>" class="btn btn-sm btn-outline-primary" title="Editar" aria-label="Editar bloque del <?php echo $h['dia_semana']; ?> de <?php echo htmlspecialchars($h['materia_nombre']); ?>"><i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="<?= $this->url('horarios/eliminar', ['id' => $h['id']]); ?>" class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Borrar este bloque?');" aria-label="Eliminar bloque del <?php echo $h['dia_semana']; ?> de <?php echo htmlspecialchars($h['materia_nombre']); ?>" style="display:inline;"><?= csrf_field() ?><button type="submit"><i class="bi bi-trash"></i></button></form>
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

<script>
function exportarExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel;charset=UTF-8';
    var tableSelect = document.getElementById(tableID);
    var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body>' + tableSelect.outerHTML + '</body></html>';
    var blob = new Blob(['\ufeff', html], { type: dataType });
    filename = filename ? filename + '.xls' : 'Horarios.xls';
    downloadLink = document.createElement("a");
    document.body.appendChild(downloadLink);
    if(navigator.msSaveOrOpenBlob){
        navigator.msSaveOrOpenBlob(blob, filename);
    }else{
        downloadLink.href = URL.createObjectURL(blob);
        downloadLink.download = filename;
        downloadLink.click();
    }
}
</script>
