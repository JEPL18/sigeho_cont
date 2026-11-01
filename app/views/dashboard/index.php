<?php
?>
<div class="row g-3 mb-4 area-no-imprimir">
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid #0d6efd;">
            <div class="card-body">
                <p class="text-muted mb-2">Estado Docente</p>
                <h3 class="fw-bold mb-0"><?= $tot_prof_activos; ?> <small class="text-success fs-6"><i class="bi bi-check-circle"></i> Activos</small></h3>
                <?php if($tot_prof_inactivos > 0): ?>
                    <small class="text-danger fw-bold"><i class="bi bi-exclamation-circle"></i> <?= $tot_prof_inactivos; ?> de Reposo/Inactivos</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid #198754;">
            <div class="card-body">
                <p class="text-muted mb-2">Secciones (Matrícula)</p>
                <h3 class="fw-bold mb-0"><?= $tot_sec; ?> <small class="text-muted fs-6">Registradas</small></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100" style="border-left: 4px solid #ffc107;">
            <div class="card-body">
                <p class="text-muted mb-2">Disponibilidad de Aulas</p>
                <h3 class="fw-bold mb-0"><?= $tot_aulas_operativas; ?> <small class="text-success fs-6"><i class="bi bi-door-open-fill"></i> Operativas</small></h3>
                <?php if($tot_aulas_inoperativas > 0): ?>
                    <small class="text-warning text-dark fw-bold"><i class="bi bi-tools"></i> <?= $tot_aulas_inoperativas; ?> Mantenimiento/Clausuradas</small>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0 h-100 bg-danger text-white border-0">
            <div class="card-body d-flex flex-column justify-content-center">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <p class="text-white-50 mb-0">Choques Detectados</p>
                    <i class="bi bi-shield-fill-x fs-3 opacity-50"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                    <h3 class="fw-bold mb-0"><?= $tot_choques; ?></h3>
                    <button class="btn btn-sm btn-light text-danger fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalLog">
                        <i class="bi bi-eye-fill"></i> Ver Log
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 bg-light area-no-imprimir">
    <div class="card-body">
        <form action="<?= $this->url('dashboard'); ?>" method="GET" class="row align-items-end">
            <div class="col-md-6">
                <label class="form-label fw-bold text-dark"><i class="bi bi-search me-1"></i> Consultar Horario de la Sección:</label>
                <select name="seccion_id" class="form-select border-secondary" required>
                    <option value="" disabled <?= !$seccion_seleccionada ? 'selected' : ''; ?>>Seleccione la sección...</option>
                    <?php foreach($secciones as $s): ?>
                        <option value="<?= $s['id']; ?>" <?= ($seccion_seleccionada == $s['id']) ? 'selected' : ''; ?>>
                            TRAYECTO <?= $s['trayecto']; ?> - TRIM. <?= $s['trimestre']; ?> | <?= $s['codigo']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-bold text-dark"><i class="bi bi-clock-fill me-1"></i> Turno:</label>
                <select name="turno" class="form-select border-secondary">
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

<?php if ($seccion_seleccionada && $datos_seccion): ?>
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center area-no-imprimir">
            <p class="mb-0 fw-bold text-dark">Vista Previa de Impresión</p>
            <div class="btn-group">
                <button class="btn btn-outline-danger btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir / Guardar PDF</button>
                <button class="btn btn-success btn-sm" onclick="exportarExcel('tablaHorario', 'Horario_<?= $datos_seccion['codigo']; ?>')"><i class="bi bi-file-earmark-excel"></i> Exportar Excel</button>
            </div>
        </div>

        <div class="card-body overflow-auto p-4 bg-white" id="area-impresion">
            <h4 class="d-none d-print-block" style="font-family: Arial, sans-serif; font-weight: bold;">Horario de la sección: <?= htmlspecialchars($datos_seccion['codigo']); ?></h4>

            

            <div class="encabezado-siace">
                <h4 style="color: #cc0000; font-weight: bold; margin: 0; font-family: Arial, sans-serif;">
                    TURNO: <?= htmlspecialchars($turno_seleccionado); ?>
                </h4>
            </div>

            <?php if($es_admin): ?>
                <p class="text-muted small area-no-imprimir">Borrador: arrastra un bloque a otro día u hora para moverlo. El servidor valida choques antes de guardar.</p>
            <?php endif; ?>
            <div id="mensaje-mover" class="alert" style="display: none;" role="alert"></div>
            <table class="tabla-siace" id="tablaHorario">
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
                    <tr><td colspan="7" class="py-5" style="color: #666; font-size: 0.9rem;">No hay clases asignadas para esta sección en este turno.</td></tr>
                <?php else: ?>
                    <?php
                    $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                    foreach ($segmentos as $i => $seg):
                    ?>
                        <tr data-inicio="<?= substr($seg['inicio'], 0, 5); ?>">
                            <td class="celda-hora-siace"><?= $seg['texto']; ?></td>

                            <?php foreach ($dias as $d): ?>
                                <?php if (isset($celdas[$i][$d])):
                                    $clase = $celdas[$i][$d]['clase'];
                                ?>
                                    <td class="celda-datos-siace<?= $es_admin ? ' bloque-arrastrable' : ''; ?>" rowspan="<?= $celdas[$i][$d]['rowspan']; ?>" data-dia="<?= $d; ?>"<?= $es_admin ? ' draggable="true" data-horario-id="' . $clase['id'] . '"' : ''; ?>>
                                        <span class="materia-siace">
                                            (<?= htmlspecialchars($datos_seccion['codigo']); ?>) - <?= htmlspecialchars($clase['materia']); ?>
                                        </span>
                                        <span class="texto-siace">Seccion: <?= htmlspecialchars($datos_seccion['codigo']); ?></span>
                                        <span class="texto-siace">
                                            <?= htmlspecialchars($clase['profesor']); ?>
                                        </span>
                                        <span class="texto-siace">AULA: <?= htmlspecialchars($clase['aula']); ?></span>
                                    </td>
                                <?php elseif (!isset($ocupadas[$i][$d])): ?>
                                    <td data-dia="<?= $d; ?>"></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
            <?php if($es_admin): ?>
            <script>
            (function() {
                var mensaje = document.getElementById('mensaje-mover');
                var arrastrado = null;
                function mostrar(texto, ok) {
                    mensaje.innerHTML = texto;
                    mensaje.className = 'alert ' + (ok ? 'alert-success' : 'alert-danger');
                    mensaje.style.display = 'block';
                }
                document.querySelectorAll('.bloque-arrastrable').forEach(function(b) {
                    b.addEventListener('dragstart', function() {
                        arrastrado = { id: b.dataset.horarioId };
                        b.style.opacity = '0.4';
                    });
                    b.addEventListener('dragend', function() {
                        b.style.opacity = '';
                        arrastrado = null;
                    });
                });
                document.querySelectorAll('#tablaHorario td[data-dia]').forEach(function(celda) {
                    celda.addEventListener('dragover', function(e) {
                        if (arrastrado) {
                            e.preventDefault();
                            celda.style.outline = '2px dashed #8B1A1A';
                        }
                    });
                    celda.addEventListener('dragleave', function() {
                        celda.style.outline = '';
                    });
                    celda.addEventListener('drop', function(e) {
                        e.preventDefault();
                        celda.style.outline = '';
                        if (!arrastrado) return;
                        var fila = celda.closest('tr');
                        var inicio = fila ? fila.dataset.inicio : '';
                        var dia = celda.dataset.dia;
                        if (!inicio || !dia) return;
                        var tokenEl = document.querySelector('input[name="csrf_token"]');
                        if (!tokenEl) {
                            mostrar('No se encontró el token de seguridad. Recarga la página.', false);
                            return;
                        }
                        var datos = new FormData();
                        datos.append('csrf_token', tokenEl.value);
                        datos.append('id', arrastrado.id);
                        datos.append('dia', dia);
                        datos.append('hora_inicio', inicio);
                        fetch('<?= $this->url('horarios/mover'); ?>', { method: 'POST', body: datos })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.ok) {
                                    mostrar('Bloque movido correctamente.', true);
                                    setTimeout(function() { location.reload(); }, 900);
                                } else {
                                    mostrar(res.error || 'No se pudo mover el bloque.', false);
                                }
                            })
                            .catch(function() {
                                mostrar('No se pudo mover el bloque. Revisa tu sesión e inténtalo de nuevo.', false);
                            });
                    });
                });
            })();
            </script>
            <?php endif; ?>
        </div>
    </div>

    <script>
    function exportarExcel(tableID, filename = ''){
        var downloadLink;
        var dataType = 'application/vnd.ms-excel;charset=UTF-8';
        var tableSelect = document.getElementById(tableID);
        var html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta charset="UTF-8"></head><body>' + tableSelect.outerHTML + '</body></html>';
        var blob = new Blob(['\ufeff', html], { type: dataType });
        filename = filename ? filename + '.xls' : 'Horario_SIACE.xls';
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
        <p class="text-muted">Seleccione una sección y un turno en el menú superior para visualizar su horario.</p>
    </div>
<?php endif; ?>

<!-- MODAL DEL LOG DE CHOQUES -->
<div class="modal fade" id="modalLog" tabindex="-1" role="dialog" aria-labelledby="modalLogLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-danger text-white">
        <h4 class="modal-title fw-bold" id="modalLogLabel"><i class="bi bi-shield-lock-fill"></i> Auditoría: Log de Choques Bloqueados</h4>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-striped table-hover mb-0" style="font-size: 0.85rem;">
            <thead class="table-dark sticky-top">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>Intento de Asignación</th>
                    <th>Detalle del Conflicto (Motivo del Bloqueo)</th>
                </tr>
            </thead>
            <tbody>
                <?php if(count($logs) > 0): ?>
                    <?php foreach($logs as $log): ?>
                        <tr>
                            <td class="text-nowrap fw-bold text-danger"><?= date("d/m/Y h:i A", strtotime($log['fecha'])); ?></td>
                            <td><?= htmlspecialchars($log['intento_asignacion']); ?></td>
                            <td><?= htmlspecialchars($log['detalle_conflicto']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-muted">No se han registrado choques de horarios en el sistema.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
      </div>
      <div class="modal-footer bg-light d-flex justify-content-between">
        <?php if(strtolower($_SESSION['rol']) == 'administrador'): ?>
            <form method="post" action="<?= $this->url('dashboard'); ?>" class="d-inline m-0" onsubmit="return confirm('¿Estás seguro de borrar todo el historial de auditoría?');">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="clear_logs">
                <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                    <i class="bi bi-trash-fill"></i> Vaciar Historial
                </button>
            </form>
        <?php else: ?>
            <div></div> <!-- Espacio vacío para asistentes -->
        <?php endif; ?>
        <button type="button" class="btn btn-secondary fw-bold" data-bs-dismiss="modal">Cerrar Registro</button>
      </div>
    </div>
  </div>
</div>
