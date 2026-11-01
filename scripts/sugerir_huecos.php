<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Solo por consola.');
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/config/database.php';
require APP_PATH . '/models/Profesor.php';
require APP_PATH . '/models/Materia.php';
require APP_PATH . '/models/Seccion.php';
require APP_PATH . '/models/Aula.php';
require APP_PATH . '/models/Horario.php';

function uso() {
    echo "Uso: php sugerir_huecos.php --profesor=ID --materia=ID [--seccion=ID] [--duracion=MIN] [--dia=DIA] [--max=N] [--aplicar=N]\n";
    echo "  --profesor   ID del profesor. Obligatorio.\n";
    echo "  --materia    ID de la materia. Obligatorio.\n";
    echo "  --seccion    ID de la sección. Opcional para sugerir; obligatorio con --aplicar.\n";
    echo "  --duracion   Duración de la clase en minutos. Por defecto 90.\n";
    echo "  --dia        Lunes, Martes, Miercoles, Jueves, Viernes o Sabado. Por defecto todos.\n";
    echo "  --max        Máximo de huecos a mostrar. Por defecto 40.\n";
    echo "  --desde      Hora inicial del rango a buscar (HH:MM). Por defecto 07:00.\n";
    echo "  --hasta      Hora final del rango a buscar (HH:MM). Por defecto 19:00.\n";
    echo "  --aplicar    Número del hueco mostrado (1, 2, 3...) para crearlo como horario. Vuelve a validar antes de crear.\n";
    exit(1);
}

$args = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([^=]+)=(.*)$/', $a, $m)) {
        $args[$m[1]] = $m[2];
    }
}

$profesor_id = isset($args['profesor']) ? (int)$args['profesor'] : 0;
$materia_id = isset($args['materia']) ? (int)$args['materia'] : 0;
if ($profesor_id <= 0 || $materia_id <= 0) {
    uso();
}
$seccion_id = isset($args['seccion']) ? (int)$args['seccion'] : 0;
$duracion = isset($args['duracion']) ? (int)$args['duracion'] : 90;
$max = isset($args['max']) ? (int)$args['max'] : 40;
$aplicar = isset($args['aplicar']) ? (int)$args['aplicar'] : 0;
if ($duracion <= 0 || $duracion > 720 || $max <= 0 || $aplicar < 0) {
    uso();
}
if ($aplicar > 0 && $seccion_id <= 0) {
    echo "Error: --aplicar requiere --seccion (un horario necesita su sección).\n";
    exit(1);
}
if ($aplicar > $max) {
    $max = $aplicar;
}

$desde_txt = isset($args['desde']) ? $args['desde'] : '07:00';
$hasta_txt = isset($args['hasta']) ? $args['hasta'] : '19:00';
$desde_min = null;
$hasta_min = null;
if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $desde_txt, $m)) {
    $desde_min = ((int)$m[1] * 60) + (int)substr($desde_txt, 3, 2);
}
if (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $hasta_txt, $m)) {
    $hasta_min = ((int)$m[1] * 60) + (int)substr($hasta_txt, 3, 2);
}
if ($desde_min === null || $hasta_min === null || $hasta_min <= $desde_min || ($hasta_min - $desde_min) < $duracion) {
    echo "Error: --desde y --hasta deben ser HH:MM válidos, con hasta mayor que desde y ventana mayor o igual que la duración.\n";
    exit(1);
}

$dias_validos = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'];
$dias = [];
if (isset($args['dia'])) {
    $normalizado = ucfirst(strtolower(strtr($args['dia'], ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u'])));
    if (!in_array($normalizado, $dias_validos, true)) {
        echo "Error: día no válido. Usa: " . implode(', ', $dias_validos) . ".\n";
        exit(1);
    }
    $dias = [$normalizado];
}

$materia = new Materia($conn);
$datos_mat = $materia->find($materia_id);
if (!$datos_mat) {
    echo "Error: materia no encontrada.\n";
    exit(1);
}

$profesor = new Profesor($conn);
$datos_prof = $profesor->find($profesor_id);
if (!$datos_prof) {
    echo "Error: profesor no encontrado.\n";
    exit(1);
}

$codigo_sec = 'sin sección';
if ($seccion_id) {
    $seccion = new Seccion($conn);
    $datos_sec = $seccion->find($seccion_id);
    if (!$datos_sec) {
        echo "Error: sección no encontrada.\n";
        exit(1);
    }
    $codigo_sec = $datos_sec['codigo'];
}

$horario = new Horario($conn);
$resultado = $horario->huecosLibres($profesor_id, $materia_id, $seccion_id, $duracion, $dias, $max, $desde_min, $hasta_min);
if (isset($resultado['error'])) {
    echo "Error: " . $resultado['error'] . "\n";
    exit(1);
}

echo "Profesor: " . $datos_prof['nombre'] . " | Materia: " . $datos_mat['nombre'] . " | Sección: " . $codigo_sec . " | Duración: $duracion min\n";
if (empty($resultado['huecos'])) {
    echo "Sin huecos libres con esos criterios.\n";
    exit(0);
}
foreach ($resultado['huecos'] as $h) {
    echo $h['dia'] . ' ' . $h['hora_inicio'] . '-' . $h['hora_fin'] . ' Aula ' . $h['aula'] . ' (cap ' . $h['capacidad'] . ")\n";
}
echo "Mostrando " . count($resultado['huecos']) . " de " . $resultado['total'] . " huecos libres.\n";
if ($aplicar > 0) {
    if ($aplicar > count($resultado['huecos'])) {
        echo "Error: el hueco $aplicar no existe (solo hay " . count($resultado['huecos']) . " mostrados).\n";
        exit(1);
    }
    $h = $resultado['huecos'][$aplicar - 1];
    $error = $horario->aplicarHueco($profesor_id, $materia_id, $seccion_id, $h['dia'], $h['hora_inicio'], $h['hora_fin'], $h['aula_id']);
    if ($error !== null) {
        echo "No se pudo aplicar: $error\n";
        exit(1);
    }
    echo "Automatización aplicada. Horario creado: " . $h['dia'] . ' ' . $h['hora_inicio'] . '-' . $h['hora_fin'] . ' Aula ' . $h['aula'] . ".\n";
} else {
    echo "Nota: es solo una sugerencia. Agrega --aplicar=N para crear el hueco número N.\n";
}
