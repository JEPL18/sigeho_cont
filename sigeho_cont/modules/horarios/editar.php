<?php
// modules/horarios/editar.php
session_start();
// EL CANDADO
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: index.php");
    exit();
}

require '../../config/db.php';

$error = ""; $success = "";
if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seccion_id = (int)$_POST['seccion_id'];
    $materia_id = (int)$_POST['materia_id'];
    $profesor_id = (int)$_POST['profesor_id'];
    $aula_id = (int)$_POST['aula_id'];
    $dia_semana = $_POST['dia_semana'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];

    if (empty($seccion_id) || empty($materia_id) || empty($profesor_id) || empty($aula_id) || empty($dia_semana) || empty($hora_inicio) || empty($hora_fin)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($hora_inicio >= $hora_fin) {
        $error = "La hora de inicio debe ser anterior a la hora de fin.";
    } else {
        try {
            $stmt_check = $conn->prepare("SELECT 
                (SELECT estado FROM profesores WHERE id = ?) as prof_estado,
                (SELECT estado FROM aulas WHERE id = ?) as aula_estado,
                (SELECT capacidad FROM aulas WHERE id = ?) as aula_cap,
                (SELECT cantidad_alumnos FROM secciones WHERE id = ?) as sec_alumn
            ");
            $stmt_check->execute([$profesor_id, $aula_id, $aula_id, $seccion_id]);
            $datos = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($datos['prof_estado'] != 'ACTIVO') {
                $error = "El docente seleccionado no está ACTIVO.";
            } elseif ($datos['aula_estado'] != 'OPERATIVA') {
                $error = "El aula seleccionada no está OPERATIVA.";
            } elseif ($datos['sec_alumn'] > $datos['aula_cap']) {
                $error = "Capacidad excedida: El aula (".$datos['aula_cap']." ptos) no soporta la matrícula (".$datos['sec_alumn']." alumnos).";
            } else {
                
                // --- ALGORITMO ANTI-CHOQUES (DESGLOSADO Y EXCLUYENDO EL ID ACTUAL) ---
                $choque = false;
                $detalle = "";

                // A. Choque de Aula
                $stmt_aula = $conn->prepare("SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND aula_id = ? AND id != ?");
                $stmt_aula->execute([$dia_semana, $hora_fin, $hora_inicio, $aula_id, $id]);
                if ($stmt_aula->rowCount() > 0) {
                    $error = "<strong>¡Choque de Aula!</strong> Esa aula ya está reservada en ese horario.";
                    $detalle = "Choque de Aula: El aula ya está ocupada.";
                    $choque = true;
                }

                // B. Choque de Profesor
                if (!$choque) {
                    $stmt_prof = $conn->prepare("SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND profesor_id = ? AND id != ?");
                    $stmt_prof->execute([$dia_semana, $hora_fin, $hora_inicio, $profesor_id, $id]);
                    if ($stmt_prof->rowCount() > 0) {
                        $error = "<strong>¡Choque de Docente!</strong> Este profesor ya imparte clases a esa hora.";
                        $detalle = "Choque de Profesor: Docente ya ocupado.";
                        $choque = true;
                    }
                }

                // C. Choque de Sección
                if (!$choque) {
                    $stmt_sec = $conn->prepare("SELECT id FROM horarios WHERE dia_semana = ? AND (hora_inicio < ? AND hora_fin > ?) AND seccion_id = ? AND id != ?");
                    $stmt_sec->execute([$dia_semana, $hora_fin, $hora_inicio, $seccion_id, $id]);
                    if ($stmt_sec->rowCount() > 0) {
                        $error = "<strong>¡Choque de Sección!</strong> Los alumnos de esta sección ya tienen clase a esa hora.";
                        $detalle = "Choque de Sección: Los alumnos ya tienen clases.";
                        $choque = true;
                    }
                }

                if ($choque) {
                    // REGISTRAR EL LOG EN LA BASE DE DATOS
                    $intento = "Edición -> Día: $dia_semana | Hora: $hora_inicio a $hora_fin";
                    $sql_log = "INSERT INTO log_choques (usuario_id, intento_asignacion, detalle_conflicto) VALUES (?, ?, ?)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->execute([$_SESSION['usuario_id'], $intento, $detalle]);
                } else {
                    $sql = "UPDATE horarios SET seccion_id=?, materia_id=?, profesor_id=?, aula_id=?, dia_semana=?, hora_inicio=?, hora_fin=? WHERE id=?";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute([$seccion_id, $materia_id, $profesor_id, $aula_id, $dia_semana, $hora_inicio, $hora_fin, $id]);
                    $success = "Bloque de horario actualizado correctamente.";
                }
            }
        } catch(PDOException $e) { $error = "Error DB: " . $e->getMessage(); }
    }
}

$stmt = $conn->prepare("SELECT * FROM horarios WHERE id = ?");
$stmt->execute([$id]);
$horario = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$horario) { header("Location: index.php"); exit(); }

$secciones = $conn->query("SELECT id, codigo, trayecto, trimestre, cantidad_alumnos FROM secciones")->fetchAll(PDO::FETCH_ASSOC);
$materias = $conn->query("SELECT id, nombre FROM materias")->fetchAll(PDO::FETCH_ASSOC);
$profesores = $conn->query("SELECT id, nombre FROM profesores")->fetchAll(PDO::FETCH_ASSOC);
$aulas = $conn->query("SELECT id, codigo, capacidad FROM aulas")->fetchAll(PDO::FETCH_ASSOC);

$ruta = '../../'; include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-primary me-2"></i> Editar Bloque de Clase</h2>
    <a href="index.php" class="btn btn-outline-secondary" aria-label="Volver a la gestión de horarios"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-4">
        <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

        <form action="editar.php?id=<?php echo $id; ?>" method="POST">
            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Sección</label>
                    <select name="seccion_id" class="form-select" aria-label="Seleccionar la sección" required>
                        <?php foreach($secciones as $s): 
                            $texto_tray = ($s['trayecto'] == 0) ? 'Inicial' : 'T'.$s['trayecto'];
                        ?>
                            <option value="<?php echo $s['id']; ?>" <?php echo ($horario['seccion_id'] == $s['id']) ? 'selected' : ''; ?>>
                                <?php echo $texto_tray . " - " . $s['codigo'] . " (" . $s['cantidad_alumnos'] . " Alumnos)"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Materia</label>
                    <select name="materia_id" class="form-select" aria-label="Seleccionar la materia" required>
                        <?php foreach($materias as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo ($horario['materia_id'] == $m['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($m['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Profesor</label>
                    <select name="profesor_id" class="form-select" aria-label="Seleccionar el profesor" required>
                        <?php foreach($profesores as $p): ?>
                            <option value="<?php echo $p['id']; ?>" <?php echo ($horario['profesor_id'] == $p['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Aula</label>
                    <select name="aula_id" class="form-select" aria-label="Seleccionar el aula" required>
                        <?php foreach($aulas as $a): ?>
                            <option value="<?php echo $a['id']; ?>" <?php echo ($horario['aula_id'] == $a['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($a['codigo']) . " (Cap: " . $a['capacidad'] . " ptos)"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-4 bg-light p-3 rounded">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Día</label>
                    <select name="dia_semana" class="form-select" aria-label="Seleccionar el día de la semana" required>
                        <?php $dias = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
                        foreach($dias as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo ($horario['dia_semana'] == $d) ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hora Inicio</label>
                    <input type="time" name="hora_inicio" class="form-control" value="<?php echo $horario['hora_inicio']; ?>" aria-label="Editar hora de inicio" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hora Fin</label>
                    <input type="time" name="hora_fin" class="form-control" value="<?php echo $horario['hora_fin']; ?>" aria-label="Editar hora de fin" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios del bloque de horario"><i class="bi bi-save"></i> Actualizar Bloque</button>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>