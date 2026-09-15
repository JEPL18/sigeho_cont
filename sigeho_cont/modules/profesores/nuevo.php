<?php
// modules/profesores/nuevo.php
session_start();
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: index.php");
    exit();
}

require '../../config/db.php';
$error = ""; $success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = trim(strtoupper($_POST['cedula']));
    $titulo = $_POST['titulo']; // NUEVO CAMPO
    $nombre = trim(ucwords(strtolower($_POST['nombre'])));
    $estado = $_POST['estado'];

    if (empty($cedula) || empty($titulo) || empty($nombre) || empty($estado)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        try {
            $check = $conn->prepare("SELECT id FROM profesores WHERE cedula = ?");
            $check->execute([$cedula]);
            if($check->rowCount() > 0){
                $error = "Esta cédula ya está registrada en el sistema.";
            } else {
                // SE AGREGA EL TÍTULO A LA CONSULTA SQL
                $stmt = $conn->prepare("INSERT INTO profesores (cedula, titulo, nombre, estado) VALUES (?, ?, ?, ?)");
                $stmt->execute([$cedula, $titulo, $nombre, $estado]);
                $success = "Docente registrado correctamente.";
            }
        } catch(PDOException $e) { 
            $error = "Error al guardar: " . $e->getMessage(); 
        }
    }
}

$ruta = '../../'; include '../../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
    <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-person-plus text-danger me-2"></i> Registrar Profesor</h2>
    <a href="index.php" class="btn btn-outline-secondary" aria-label="Volver a la lista de docentes"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <?php if($error): ?><div class="alert alert-danger" role="alert"><?php echo $error; ?></div><?php endif; ?>
                <?php if($success): ?><div class="alert alert-success" role="alert"><?php echo $success; ?></div><?php endif; ?>

                <form action="nuevo.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cédula de Identidad</label>
                        <input type="text" name="cedula" class="form-control solo-numeros" placeholder="Ej: 12345678" pattern="[0-9]{7,8}" title="Debe contener 7 u 8 números. Sin puntos ni letras." maxlength="8" aria-label="Ingresar cédula de identidad" required>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Título</label>
                            <select name="titulo" class="form-select" required>
                                <option value="Prof.">Prof.</option>
                                <option value="Lcdo.">Lcdo.</option>
                                <option value="Lcda.">Lcda.</option>
                                <option value="Ing.">Ing.</option>
                                <option value="Abg.">Abg.</option>
                                <option value="Dr.">Dr.</option>
                                <option value="Dra.">Dra.</option>
                                <option value="Mgs.">Mgs.</option>
                                <option value="Econ.">Econ.</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre Completo (Sin el título)</label>
                            <input type="text" name="nombre" class="form-control solo-letras" placeholder="Ej: Alexis Mujica" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\.]+" title="Solo se permiten letras, espacios y puntos." aria-label="Ingresar nombre completo" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Estado en la Institución</label>
                        <select name="estado" class="form-select" aria-label="Seleccionar estado del docente" required>
                            <option value="ACTIVO">ACTIVO</option>
                            <option value="INACTIVO">INACTIVO (Reposo/Jubilado)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-danger w-100 fw-bold" style="background-color: #8B1A1A;" aria-label="Guardar nuevo docente">
                        <i class="bi bi-save"></i> Guardar Docente
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>