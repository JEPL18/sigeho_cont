<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3 area-no-imprimir">
    <h2 class="fw-bold mb-0"><i class="bi bi-calendar-week-fill text-danger me-2"></i> Horarios por Profesor</h2>
    <a href="<?= $this->url('horarios'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la gestión de horarios"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-0 mb-4 area-no-imprimir">
    <div class="card-body">
        <form method="GET" action="<?= $this->url('horarios/porProfesor'); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-bold text-dark">Profesor:</label>
                <select name="profesor_id" class="form-select border-secondary" required>
                    <option value="" disabled <?= !$profesor_seleccionado ? 'selected' : ''; ?>>Seleccione...</option>
                    <?php foreach($profesores as $p): ?>
                        <option value="<?= $p['id']; ?>" <?= ($profesor_seleccionado == $p['id']) ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($p['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-dark">Turno:</label>
                <select name="turno" class="form-select border-secondary">
                    <option value="" <?= ($turno_seleccionado == '') ? 'selected' : ''; ?>>Todos</option>
                    <option value="MATUTINO" <?= ($turno_seleccionado == 'MATUTINO') ? 'selected' : ''; ?>>Matutino</option>
                    <option value="VESPERTINO" <?= ($turno_seleccionado == 'VESPERTINO') ? 'selected' : ''; ?>>Vespertino</option>
                    <option value="NOCTURNO" <?= ($turno_seleccionado == 'NOCTURNO') ? 'selected' : ''; ?>>Nocturno</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-danger w-100" style="background-color: #8B1A1A;">
                    <i class="bi bi-calendar3 me-1"></i> Cargar Horario
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($profesor_seleccionado && $datos_profesor): ?>
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center area-no-imprimir">
            <p class="mb-0 fw-bold text-dark">Horario de: <?= htmlspecialchars($datos_profesor['nombre']); ?></p>
            <div class="btn-group">
                <button class="btn btn-outline-danger btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
                <button class="btn btn-success btn-sm" onclick="exportarExcel('tablaHorarioProfesor', 'Horario_<?= preg_replace('/[^A-Za-z0-9]/', '_', $datos_profesor['nombre']); ?>')"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
            </div>
        </div>

        <div class="card-body overflow-auto p-4 bg-white" id="area-impresion">
            <h3 class="d-none d-print-block" style="font-family: Arial, sans-serif; font-weight: bold;">Horario de: <?= htmlspecialchars($datos_profesor['nombre']); ?></h3>

            

            <div class="encabezado-siace">
                <h3 style="color: #cc0000; font-weight: bold; margin: 0; font-family: Arial, sans-serif;">
                    TURNO: <?= htmlspecialchars($turno_seleccionado == '' ? 'TODOS' : $turno_seleccionado); ?>
                </h3>
            </div>

            <table class="tabla-siace" id="tablaHorarioProfesor">
                <tr>
                    <th style="width: 13%;">HORA</th>
                    <th style="width: 14.5%;">LUNES</th>
                    <th style="width: 14.5%;">MARTES</th>
                    <th style="width: 14.5%;">MIERCOLES</th>
                    <th style="width: 14.5%;">JUEVES</th>
                    <th style="width: 14.5%;">VIERNES</th>
                    <th style="width: 14.5%;">SABADO</th>
                </tr>

                <?php if (empty($segmentos)): ?>
                    <tr><td colspan="7" class="py-5" style="color: #666; font-size: 0.9rem;">No hay clases asignadas para este profesor en este turno.</td></tr>
                <?php else: ?>
                    <?php
                    $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                    foreach ($segmentos as $i => $seg):
                    ?>
                        <tr>
                            <td class="celda-hora-siace"><?= $seg['texto']; ?></td>

                            <?php foreach ($dias as $d): ?>
                                <?php if (isset($celdas[$i][$d])):
                                    $clase = $celdas[$i][$d]['clase'];
                                ?>
                                    <td class="celda-datos-siace" rowspan="<?= $celdas[$i][$d]['rowspan']; ?>">
                                        <span class="materia-siace">
                                            (<?= htmlspecialchars($clase['seccion']); ?>) - <?= htmlspecialchars($clase['materia']); ?>
                                        </span>
                                        <span class="texto-siace">Seccion: <?= htmlspecialchars($clase['seccion']); ?></span>
                                        <span class="texto-siace">AULA: <?= htmlspecialchars($clase['aula']); ?></span>
                                    </td>
                                <?php elseif (!isset($ocupadas[$i][$d])): ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
        </div>
    </div>

    <script>
    function exportarExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel;charset=UTF-8';
        var tableSelect = document.getElementById(tableID);
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body>' + tableSelect.outerHTML + '</body></html>';
        var blob = new Blob(['\ufeff', html], { type: dataType });
        filename = filename ? filename + '.xls' : 'Horario_Profesor.xls';
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
<?php else: ?>
    <div class="alert alert-secondary text-center py-5 border-0 shadow-sm area-no-imprimir">
        <i class="bi bi-calendar3 fs-1 text-muted d-block mb-3"></i>
        <h3 class="text-muted">Selecciona un profesor para visualizar su horario semanal.</h3>
    </div>
<?php endif; ?>
