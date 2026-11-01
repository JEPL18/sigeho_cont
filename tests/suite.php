<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Solo por consola.');
}

$root = dirname(__DIR__);
$host = getenv('SIGEHO_TEST_HOST') ?: '127.0.0.1';
$db = getenv('SIGEHO_TEST_DB') ?: 'sigeho_tests';
$user = getenv('SIGEHO_TEST_USER') ?: 'root';
$pass = getenv('SIGEHO_TEST_PASS') ?: '';

$admin = new PDO("mysql:host=$host;charset=utf8", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$admin->exec("DROP DATABASE IF EXISTS `$db`");
$admin->exec("CREATE DATABASE `$db` CHARACTER SET utf8mb4");
$schema = file_get_contents("$root/sql/sigeho_cont_db.sql");
$mysql_cli = trim((string)shell_exec('command -v mysql'));
if ($mysql_cli !== '') {
    $tmp_schema = tempnam(sys_get_temp_dir(), 'sigeho_schema_');
    file_put_contents($tmp_schema, $schema);
    $cmd = 'MYSQL_PWD=' . escapeshellarg($pass) . ' ' . escapeshellarg($mysql_cli)
        . ' -h ' . escapeshellarg($host) . ' -u ' . escapeshellarg($user)
        . ' ' . escapeshellarg($db) . ' < ' . escapeshellarg($tmp_schema);
    passthru($cmd, $code);
    unlink($tmp_schema);
    if ($code !== 0) {
        exit("No se pudo importar el esquema con el cliente mysql.\n");
    }
} else {
    $conn_boot = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    foreach (explode(";\n", $schema) as $stmt) {
        $stmt = trim($stmt);
        if ($stmt !== '') {
            $conn_boot->exec($stmt);
        }
    }
}

$conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach (['Aula', 'Bitacora', 'Configuracion', 'Horario', 'LogChoque', 'Materia', 'Profesor', 'Seccion', 'Usuario'] as $m) {
    require "$root/app/models/$m.php";
}

$pasadas = 0;
$totales = 0;
function prueba($nombre, $cond, $detalle = '') {
    global $pasadas, $totales;
    $totales++;
    if ($cond) {
        $pasadas++;
        echo "PASS $nombre\n";
    } else {
        echo "FAIL $nombre" . ($detalle !== '' ? " | $detalle" : '') . "\n";
    }
}
function id_de($conn, $tabla, $campo, $valor) {
    $stmt = $conn->prepare("SELECT id FROM $tabla WHERE $campo = ?");
    $stmt->execute([$valor]);
    return (int)$stmt->fetchColumn();
}

$profesor = new Profesor($conn);
$materia = new Materia($conn);
$seccion = new Seccion($conn);
$aula = new Aula($conn);
$horario = new Horario($conn);

$profesor->create('11111111', 'Prof Activo', 'ACTIVO');
$profesor->create('22222222', 'Prof Inactivo', 'INACTIVO');
$profesor->create('33333333', 'Prof Activo Dos', 'ACTIVO');
$profesor->create('44444444', 'Prof Sin Horarios', 'ACTIVO');
$materia->create('TST-100', 'Materia Test', 1, 4);
$seccion->create('SEC-A', 1, 1, 27);
$seccion->create('SEC-B', 1, 1, 20);
$aula->create('AULA-T1', 30, 'OPERATIVA', '');
$aula->create('AULA-T2', 20, 'OPERATIVA', '');
$aula->create('AULA-T3', 40, 'INOPERATIVA', '');

$p_act = id_de($conn, 'profesores', 'cedula', '11111111');
$p_ina = id_de($conn, 'profesores', 'cedula', '22222222');
$p_act2 = id_de($conn, 'profesores', 'cedula', '33333333');
$p_sin = id_de($conn, 'profesores', 'cedula', '44444444');
$m_id = id_de($conn, 'materias', 'codigo', 'TST-100');
$s_a = id_de($conn, 'secciones', 'codigo', 'SEC-A');
$s_b = id_de($conn, 'secciones', 'codigo', 'SEC-B');
$a_t1 = id_de($conn, 'aulas', 'codigo', 'AULA-T1');
$a_t2 = id_de($conn, 'aulas', 'codigo', 'AULA-T2');
$a_t3 = id_de($conn, 'aulas', 'codigo', 'AULA-T3');

$horario->create($s_a, $m_id, $p_act, $a_t1, 'Lunes', '07:00:00', '08:35:00');
$h_base = (int)$conn->lastInsertId();

$r = $seccion->sugerirCantidad(85, 30);
prueba('sugerir 85/30 reparte 29-28-28', $r['secciones'] === 3 && $r['distribucion'] === [29, 28, 28], json_encode($r));
$r = $seccion->sugerirCantidad(60, 30);
prueba('sugerir 60/30 reparte 30-30', $r['secciones'] === 2 && $r['distribucion'] === [30, 30], json_encode($r));
$r = $seccion->sugerirCantidad(31, 30);
prueba('sugerir 31/30 reparte 16-15', $r['secciones'] === 2 && $r['distribucion'] === [16, 15], json_encode($r));
prueba('sugerir 0/30 devuelve null', $seccion->sugerirCantidad(0, 30) === null);

$seccion->create('PRU-02', 1, 1, 10);
$creadas = $seccion->crearSugeridas([10, 10, 10], 1, 1, 'PRU');
prueba('crearSugeridas salta codigos existentes', $creadas === ['PRU-01 (10 alumnos)', 'PRU-03 (10 alumnos)', 'PRU-04 (10 alumnos)'], json_encode($creadas));

prueba('choque de aula ocupada', $horario->detectarChoque('Lunes', '07:30:00', '09:00:00', $a_t1, $p_act2, $s_b) !== null);
prueba('bloque contiguo no choca', $horario->detectarChoque('Lunes', '08:35:00', '10:00:00', $a_t1, $p_act, $s_a) === null);
prueba('choque de profesor en otra aula', $horario->detectarChoque('Lunes', '07:30:00', '09:00:00', $a_t2, $p_act, $s_b) !== null);
prueba('choque de seccion con otro profesor', $horario->detectarChoque('Lunes', '07:30:00', '09:00:00', $a_t2, $p_act2, $s_a) !== null);
prueba('edicion excluye su propio horario', $horario->detectarChoque('Lunes', '07:00:00', '08:35:00', $a_t1, $p_act, $s_a, $h_base, true) === null);

$rec = $horario->validarRecursos($p_act, $a_t1, $s_a);
prueba('validarRecursos devuelve estados y capacidad', $rec['prof_estado'] === 'ACTIVO' && $rec['aula_estado'] === 'OPERATIVA' && (int)$rec['aula_cap'] === 30 && (int)$rec['sec_alumn'] === 27, json_encode($rec));
$rec = $horario->validarRecursos($p_ina, $a_t1, $s_a);
prueba('validarRecursos detecta profesor inactivo', $rec['prof_estado'] === 'INACTIVO');
$rec = $horario->validarRecursos($p_act, $a_t2, $s_a);
prueba('validarRecursos expone capacidad insuficiente', (int)$rec['aula_cap'] < (int)$rec['sec_alumn']);

$huecos = $horario->huecosLibres($p_act, $m_id, $s_a, 90, ['Lunes']);
prueba('huecosLibres sin opt-in devuelve huecos', !isset($huecos['error']) && $huecos['total'] > 0 && count($huecos['huecos']) > 0, json_encode(array_slice($huecos, 0, 1)));

$conn->prepare("INSERT INTO profesor_disponibilidad (profesor_id, dia_semana, turno) VALUES (?, 'Martes', 'MATUTINO')")->execute([$p_act]);
$huecos = $horario->huecosLibres($p_act, $m_id, $s_a, 90, ['Lunes']);
prueba('huecosLibres salta dias sin disponibilidad', !isset($huecos['error']) && $huecos['total'] === 0 && count($huecos['huecos']) === 0, json_encode($huecos));

$n_antes = (int)$conn->query("SELECT COUNT(*) FROM horarios")->fetchColumn();
$res = $horario->aplicarHueco($p_act, $m_id, $s_b, 'Martes', '07:00:00', '08:30:00', $a_t1);
$n_despues = (int)$conn->query("SELECT COUNT(*) FROM horarios")->fetchColumn();
prueba('aplicarHueco crea el horario', $res === null && $n_despues === $n_antes + 1, var_export($res, true));
prueba('el horario creado ya genera choque', $horario->detectarChoque('Martes', '07:00:00', '08:30:00', $a_t1, $p_act2, $s_a) !== null);
$res = $horario->aplicarHueco($p_act, $m_id, $s_b, 'Lunes', '07:30:00', '09:00:00', $a_t1);
prueba('aplicarHueco rechaza con choque', is_string($res) && $res !== '', var_export($res, true));

prueba('tieneHorarios detecta profesor con clases', $profesor->tieneHorarios($p_act) === true);
prueba('tieneHorarios detecta profesor sin clases', $profesor->tieneHorarios($p_sin) === false);

echo "\n$pasadas/$totales pruebas pasaron (BD: $db).\n";
exit($pasadas === $totales ? 0 : 1);
