<?php
// app/views/horarios/form.php — Formulario compartido nuevo/editar.
// Migrado de modules/horarios/nuevo.php y editar.php.
// $modo: 'nuevo' | 'editar'. $datos: fila del bloque (modo editar).
$es_nuevo = ($modo === 'nuevo');
$form_action = $es_nuevo ? $this->url('horarios/nuevo') : $this->url('horarios/editar', ['id' => $id]);
$dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark">
        <?php if ($es_nuevo): ?>
            <i class="bi bi-calendar-plus text-danger me-2"></i> Asignar Bloque de Clase
        <?php else: ?>
            <i class="bi bi-pencil-square text-primary me-2"></i> Editar Bloque de Clase
        <?php endif; ?>
    </h2>
    <a href="<?= $this->url('horarios'); ?>" class="btn btn-outline-secondary" aria-label="Volver a la gestión de horarios"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

        <form action="<?= $form_action; ?>" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sección</label>
                    <select name="seccion_id" class="form-select" aria-label="Seleccionar la sección" required>
                        <?php if ($es_nuevo): ?>
                            <option value="" disabled selected>Seleccione la sección...</option>
                        <?php endif; ?>
                        <?php foreach($secciones as $s):
                            $texto_tray = ($s['trayecto'] == 0) ? 'Inicial' : 'T'.$s['trayecto'];
                        ?>
                            <option value="<?= $s['id']; ?>" <?= (!$es_nuevo && $datos['seccion_id'] == $s['id']) ? 'selected' : ''; ?>>
                                <?= $texto_tray . " - " . $s['codigo'] . " (" . $s['cantidad_alumnos'] . " Alumnos)"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Materia</label>
                    <select name="materia_id" class="form-select" aria-label="Seleccionar la materia" required>
                        <?php if ($es_nuevo): ?>
                            <option value="" disabled selected>Seleccione la materia...</option>
                        <?php endif; ?>
                        <?php foreach($materias as $m): ?>
                            <option value="<?= $m['id']; ?>" <?= (!$es_nuevo && $datos['materia_id'] == $m['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= $es_nuevo ? 'Profesor (Solo Activos)' : 'Profesor'; ?></label>
                    <select name="profesor_id" class="form-select" aria-label="Seleccionar el profesor" required>
                        <?php if ($es_nuevo): ?>
                            <option value="" disabled selected>Seleccione el docente...</option>
                        <?php endif; ?>
                        <?php foreach($profesores as $p): ?>
                            <option value="<?= $p['id']; ?>" <?= (!$es_nuevo && $datos['profesor_id'] == $p['id']) ? 'selected' : ''; ?>><?= htmlspecialchars($p['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold"><?= $es_nuevo ? 'Aula (Solo Operativas)' : 'Aula'; ?></label>
                    <select name="aula_id" class="form-select" aria-label="Seleccionar el aula" required>
                        <?php if ($es_nuevo): ?>
                            <option value="" disabled selected>Seleccione el aula...</option>
                        <?php endif; ?>
                        <?php foreach($aulas as $a): ?>
                            <option value="<?= $a['id']; ?>" <?= (!$es_nuevo && $datos['aula_id'] == $a['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($a['codigo']) . " (Cap: " . $a['capacidad'] . " ptos)"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4 bg-light p-3 rounded">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Día</label>
                    <select name="dia_semana" class="form-select" aria-label="Seleccionar el día de la semana" required>
                        <?php foreach($dias as $d): ?>
                            <option value="<?= $d; ?>" <?= (!$es_nuevo && $datos['dia_semana'] == $d) ? 'selected' : ''; ?>>
                                <?= ($d == 'Miercoles') ? 'Miércoles' : (($d == 'Sabado') ? 'Sábado' : $d); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hora Inicio</label>
                    <input type="time" name="hora_inicio" class="form-control"
                           value="<?= $es_nuevo ? '' : $datos['hora_inicio']; ?>"
                           aria-label="<?= $es_nuevo ? 'Ingresar hora de inicio' : 'Editar hora de inicio'; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hora Fin</label>
                    <input type="time" name="hora_fin" class="form-control"
                           value="<?= $es_nuevo ? '' : $datos['hora_fin']; ?>"
                           aria-label="<?= $es_nuevo ? 'Ingresar hora de fin' : 'Editar hora de fin'; ?>" required>
                </div>
            </div>
            <?php if ($es_nuevo): ?>
                <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar asignación de bloque"><i class="bi bi-calendar-check"></i> Asignar Bloque</button>
            <?php else: ?>
                <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios del bloque de horario"><i class="bi bi-save"></i> Actualizar Bloque</button>
            <?php endif; ?>
        </form>
    </div>
</div>
