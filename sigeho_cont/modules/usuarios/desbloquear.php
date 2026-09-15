<?php
// modules/usuarios/desbloquear.php
session_start();

// Validamos que sea un Administrador autorizado
if (!isset($_SESSION['usuario_id']) || strtolower($_SESSION['rol']) != 'administrador') {
    header("Location: ../../dashboard.php");
    exit();
}

require '../../config/db.php';

if (isset($_GET['id'])) {
    $id_usuario = (int)$_GET['id'];
    
    try {
        // Restauramos los valores a su estado normal
        $stmt = $conn->prepare("UPDATE usuarios SET bloqueado = 0, intentos = 0 WHERE id = ?");
        $stmt->execute([$id_usuario]);
        
        // (Opcional pero recomendado) Dejar registro en la bitácora
        $accion = "Desbloqueó al usuario ID: " . $id_usuario;
        $stmt_bitacora = $conn->prepare("INSERT INTO bitacora (usuario_id, accion) VALUES (?, ?)");
        $stmt_bitacora->execute([$_SESSION['usuario_id'], $accion]);

        // Redirigimos de vuelta con mensaje de éxito
        header("Location: index.php?msg=desbloqueado");
        exit();
        
    } catch(PDOException $e) {
        header("Location: index.php");
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>