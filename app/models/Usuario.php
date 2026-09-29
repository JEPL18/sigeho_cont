<?php
// app/models/Usuario.php — Consultas de la tabla `usuarios`.

class Usuario {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function all() {
        $sql = "SELECT id, nombre, correo, rol, bloqueado FROM usuarios ORDER BY rol ASC, nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($id) {
        $stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findParaLogin($correo) {
        $stmt = $this->conn->prepare("SELECT id, nombre, correo, rol, password, intentos, bloqueado, 2fa_activo, 2fa_secret FROM usuarios WHERE correo = ? LIMIT 1");
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeCorreo($correo, $excluirId = null) {
        if ($excluirId === null) {
            $check = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
            $check->execute([$correo]);
        } else {
            $check = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND id != ?");
            $check->execute([$correo, $excluirId]);
        }
        return $check->rowCount() > 0;
    }

    public function existeIdPorCorreo($correo) {
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->execute([$correo]);
        return $stmt->rowCount() > 0;
    }

    public function verificarRespuestasSeguridad($correo, $r1, $r2, $r3) {
        // NOTA: el proyecto original compara las respuestas en texto plano contra la BD.
        // Se conserva el comportamiento tal cual (las respuestas se guardan hasheadas
        // con password_hash al crear el usuario, por lo que esta consulta nunca
        // coincidirá, igual que en el original).
        $stmt = $this->conn->prepare("SELECT id FROM usuarios WHERE correo = ? AND respuesta_seguridad_1 = ? AND respuesta_seguridad_2 = ? AND respuesta_seguridad_3 = ?");
        $stmt->execute([$correo, $r1, $r2, $r3]);
        return $stmt->rowCount() > 0;
    }

    public function create($nombre, $correo, $rol, $pass_hash, $p1, $r1_hash, $p2, $r2_hash, $p3, $r3_hash) {
        $sql = "INSERT INTO usuarios (nombre, correo, rol, password, pregunta_seguridad_1, respuesta_seguridad_1, pregunta_seguridad_2, respuesta_seguridad_2, pregunta_seguridad_3, respuesta_seguridad_3) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$nombre, $correo, $rol, $pass_hash, $p1, $r1_hash, $p2, $r2_hash, $p3, $r3_hash]);
    }

    /**
     * Actualización dinámica (solo incluye password y respuestas si se enviaron),
     * misma lógica del editar.php original.
     */
    public function update($id, $nombre, $correo, $rol, $nueva_password, $respuestas) {
        $p1 = "¿Fecha de cumpleaños?";
        $p2 = "¿Nombre del colegio donde estudiaste?";
        $p3 = "¿Ciudad donde naciste?";

        $sql = "UPDATE usuarios SET nombre=?, correo=?, rol=?, pregunta_seguridad_1=?, pregunta_seguridad_2=?, pregunta_seguridad_3=?";
        $params = [$nombre, $correo, $rol, $p1, $p2, $p3];

        if (!empty($nueva_password)) { $sql .= ", password=?"; $params[] = password_hash($nueva_password, PASSWORD_DEFAULT); }

        if (!empty($respuestas['r1'])) { $sql .= ", respuesta_seguridad_1=?"; $params[] = password_hash($respuestas['r1'], PASSWORD_DEFAULT); }
        if (!empty($respuestas['r2'])) { $sql .= ", respuesta_seguridad_2=?"; $params[] = password_hash($respuestas['r2'], PASSWORD_DEFAULT); }
        if (!empty($respuestas['r3'])) { $sql .= ", respuesta_seguridad_3=?"; $params[] = password_hash($respuestas['r3'], PASSWORD_DEFAULT); }

        $sql .= " WHERE id=?";
        $params[] = $id;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
    }

    public function actualizarPasswordPorCorreo($correo, $pass_hash) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET password = ? WHERE correo = ?");
        $stmt->execute([$pass_hash, $correo]);
    }

    public function delete($id) {
        $stmt = $this->conn->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function desbloquear($id) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET bloqueado = 0, intentos = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    /** Resetea intentos a 0 tras un login exitoso. */
    public function resetearIntentos($id) {
        $this->conn->query("UPDATE usuarios SET intentos = 0 WHERE id = " . (int)$id);
    }

    /**
     * Registra un intento fallido. Devuelve ['intentos' => int, 'bloqueado' => bool].
     * (Lógica del PUNTO 17 del login original.)
     */
    public function registrarIntentoFallido($id, $intentosActuales) {
        $nuevos_intentos = $intentosActuales + 1;
        if ($nuevos_intentos >= 3) {
            $this->conn->query("UPDATE usuarios SET intentos = 3, bloqueado = 1 WHERE id = " . (int)$id);
            return ['intentos' => 3, 'bloqueado' => true];
        }
        $this->conn->query("UPDATE usuarios SET intentos = $nuevos_intentos WHERE id = " . (int)$id);
        return ['intentos' => $nuevos_intentos, 'bloqueado' => false];
    }

    public function datosSeguridad($id) {
        $stmt = $this->conn->prepare("SELECT password, 2fa_activo, 2fa_secret FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function estado2fa($id) {
        $stmt = $this->conn->prepare("SELECT 2fa_activo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    public function datos2fa($id) {
        $stmt = $this->conn->prepare("SELECT 2fa_secret, 2fa_activo FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function activar2fa($id, $secret_cifrado) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET 2fa_secret = ?, 2fa_activo = 1 WHERE id = ?");
        $stmt->execute([$secret_cifrado, $id]);
    }

    public function desactivar2fa($id) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET 2fa_secret = NULL, 2fa_activo = 0 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function limpiarDependencias($id) {
        $stmt_logs = $this->conn->prepare("DELETE FROM log_choques WHERE usuario_id = ?");
        $stmt_logs->execute([$id]);

        $stmt_bitacora = $this->conn->prepare("DELETE FROM bitacora WHERE usuario_id = ?");
        $stmt_bitacora->execute([$id]);
    }
}
