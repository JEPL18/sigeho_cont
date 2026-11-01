<?php
class IntentoIp {
    protected $conn;
    public function __construct($conn) { $this->conn = $conn; }

    public function datos($ip) {
        $stmt = $this->conn->prepare("SELECT * FROM intentos_ip WHERE ip = ? LIMIT 1");
        $stmt->execute([$ip]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function esperaRestante($ip) {
        $fila = $this->datos($ip);
        if (empty($fila)) {
            return 0;
        }
        $ultimo = strtotime($fila['ultimo_intento']);
        if ($ultimo === false || time() - $ultimo > 86400) {
            $this->limpiar($ip);
            return 0;
        }
        $intentos = (int)$fila['intentos'];
        if ($intentos < 5) {
            return 0;
        }
        $espera = 30 * pow(2, $intentos - 5);
        if ($espera > 900) {
            $espera = 900;
        }
        $restante = $espera - (time() - $ultimo);
        return $restante > 0 ? $restante : 0;
    }

    public function registrarFallo($ip) {
        $stmt = $this->conn->prepare("INSERT INTO intentos_ip (ip, intentos, ultimo_intento) VALUES (?, 1, NOW()) ON DUPLICATE KEY UPDATE intentos = intentos + 1, ultimo_intento = NOW()");
        $stmt->execute([$ip]);
    }

    public function limpiar($ip) {
        $stmt = $this->conn->prepare("DELETE FROM intentos_ip WHERE ip = ?");
        $stmt->execute([$ip]);
    }
}
