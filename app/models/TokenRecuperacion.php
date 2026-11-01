<?php
class TokenRecuperacion {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function crear($usuario_id, $token_hash, $expira_en) {
        $stmt = $this->conn->prepare("INSERT INTO recuperacion_tokens (usuario_id, token_hash, expira_en) VALUES (?, ?, ?)");
        $stmt->execute([$usuario_id, $token_hash, $expira_en]);
        return $this->conn->lastInsertId();
    }

    public function buscarValido($token_hash) {
        $stmt = $this->conn->prepare("SELECT * FROM recuperacion_tokens WHERE token_hash = ? AND usado = 0 AND expira_en > NOW() LIMIT 1");
        $stmt->execute([$token_hash]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function marcarUsado($id) {
        $stmt = $this->conn->prepare("UPDATE recuperacion_tokens SET usado = 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function invalidarDeUsuario($usuario_id) {
        $stmt = $this->conn->prepare("UPDATE recuperacion_tokens SET usado = 1 WHERE usuario_id = ? AND usado = 0");
        $stmt->execute([$usuario_id]);
    }

    public function eliminar($id) {
        $stmt = $this->conn->prepare("DELETE FROM recuperacion_tokens WHERE id = ?");
        $stmt->execute([$id]);
    }
}
