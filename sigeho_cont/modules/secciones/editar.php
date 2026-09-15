<?php
// modules/secciones/editar.php
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: index.php");
    exit();
}

require '../../config/db.php';
$error = ""; $success = "";

if (!isset($_GET['id'])) { header("Location: index.php"); exit(); }
$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $codigo = trim(strtoupper($_POST['codigo']));
    $trayecto = $_POST['trayecto'];
    $trimestre = (int)$_POST['trimestre'];
    $cantidad_alumnos = (int)$_POST['cantidad_alumnos'];

    if (empty($codigo) || $trayecto === '' || empty($trimestre) || empty($cantidad_alumnos)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $trayecto = (int)$trayecto;
        try {
            $check = $conn->prepare("SELECT id FROM secciones WHERE codigo = ? AND id != ?");
            $check->execute([$codigo, $id]);
            
            if($check->rowCount() > 0){
                $error = "Este código ya pertenece a otra sección.";
            } else {
                $stmt = $conn->prepare("UPDATE secciones SET codigo=?, trayecto=?, trimestre=?, cantidad_alumnos=? WHERE id=?");
                $stmt->execute([$codigo, $trayecto, $trimestre, $cantidad_alumnos, $id]);
                $success = "Sección actualizada correctamente.";
            }
        } catch(PDOException $e) { 
            $error = "Error al actualizar: " . $e->getMessage(); 
        }
    }
}

$stmt = $conn->prepare("SELECT * FROM secciones WHERE id = ?");
$stmt->execute([$id]);
$seccion = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$seccion) { header("Location: index.php"); exit(); }

$ruta = '../../'; include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-pencil-square text-danger me-2"></i> Editar Sección</h2>
    <a href="index.php" class="btn btn-outline-secondary" aria-label="Volver a la lista de secciones"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="editar.php?id=<?php echo $id; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de Sección</label>
                        <!-- PATTERN APLICADO Y ARIA-LABEL -->
                        <input type="text" name="codigo" class="form-control" value="<?php echo htmlspecialchars($seccion['codigo']); ?>" pattern="[a-zA-Z0-9\-]+" title="Solo se permiten letras, números y guiones. Ej: SECC-34" aria-label="Editar código de sección" required>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Trayecto</label>
                            <select name="trayecto" class="form-select" aria-label="Editar trayecto de la sección" required>
                                <option value="0" <?php echo ($seccion['trayecto'] == 0) ? 'selected' : ''; ?>>Trayecto Inicial</option>
                                <option value="1" <?php echo ($seccion['trayecto'] == 1) ? 'selected' : ''; ?>>Trayecto 1</option>
                                <option value="2" <?php echo ($seccion['trayecto'] == 2) ? 'selected' : ''; ?>>Trayecto 2</option>
                                <option value="3" <?php echo ($seccion['trayecto'] == 3) ? 'selected' : ''; ?>>Trayecto 3</option>
                                <option value="4" <?php echo ($seccion['trayecto'] == 4) ? 'selected' : ''; ?>>Trayecto 4</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Trimestre</label>
                            <!-- CLASE solo-numeros -->
                            <input type="number" name="trimestre" class="form-control solo-numeros" value="<?php echo $seccion['trimestre']; ?>" min="1" max="3" aria-label="Editar trimestre" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Matrícula</label>
                            <!-- CLASE solo-numeros -->
                            <input type="number" name="cantidad_alumnos" class="form-control solo-numeros" value="<?php echo $seccion['cantidad_alumnos']; ?>" min="1" aria-label="Editar cantidad de alumnos" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 fw-bold" aria-label="Guardar cambios en la sección"><i class="bi bi-save"></i> Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>