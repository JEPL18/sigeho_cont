<?php
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Solo por consola.');
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

require APP_PATH . '/config/database.php';
require APP_PATH . '/models/Seccion.php';
require APP_PATH . '/models/Aula.php';

function uso() {
    echo "Uso: php sugerir_secciones.php --matricula=N --capacidad=N [--aula=ID] [--aplicar --trayecto=T --trimestre=T --prefijo=P]\n";
    echo "  --matricula   Matrícula proyectada (alumnos). Obligatorio.\n";
    echo "  --capacidad   Capacidad por sección (alumnos). Obligatorio si no se pasa --aula.\n";
    echo "  --aula        ID de un aula OPERATIVA: su capacidad se usa como capacidad por sección.\n";
    echo "  --aplicar     Crea las secciones sugeridas (requiere --trayecto, --trimestre y --prefijo).\n";
    echo "  --trayecto    Trayecto de las secciones a crear. Obligatorio con --aplicar.\n";
    echo "  --trimestre   Trimestre de las secciones a crear. Obligatorio con --aplicar.\n";
    echo "  --prefijo     Prefijo de los códigos (ej. SECC): se numera desde 01 saltando los existentes.\n";
    exit(1);
}

$args = [];
foreach (array_slice($argv, 1) as $a) {
    if (preg_match('/^--([^=]+)=(.*)$/', $a, $m)) {
        $args[$m[1]] = $m[2];
    } elseif ($a === '--aplicar') {
        $args['aplicar'] = true;
    }
}

$matricula = isset($args['matricula']) ? (int)$args['matricula'] : 0;
if ($matricula <= 0) {
    uso();
}

$capacidad = 0;
if (isset($args['aula'])) {
    $aula = new Aula($conn);
    $datos_aula = $aula->find((int)$args['aula']);
    if (!$datos_aula) {
        echo "Error: aula no encontrada.\n";
        exit(1);
    }
    if ($datos_aula['estado'] !== 'OPERATIVA') {
        echo "Error: el aula no está OPERATIVA.\n";
        exit(1);
    }
    $capacidad = (int)$datos_aula['capacidad'];
} elseif (isset($args['capacidad'])) {
    $capacidad = (int)$args['capacidad'];
}
if ($capacidad <= 0) {
    uso();
}

if (isset($args['aplicar'])) {
    $trayecto = isset($args['trayecto']) ? (int)$args['trayecto'] : 0;
    $trimestre = isset($args['trimestre']) ? (int)$args['trimestre'] : 0;
    $prefijo = isset($args['prefijo']) ? trim($args['prefijo']) : '';
    if ($trayecto <= 0 || $trimestre <= 0 || $prefijo === '' || strlen($prefijo) > 10 || !preg_match('/^[A-Za-z0-9]+$/', $prefijo)) {
        echo "Error: --aplicar requiere --trayecto (número), --trimestre (número) y --prefijo (letras/números, máx. 10, ej. SECC).\n";
        exit(1);
    }
}

$seccion = new Seccion($conn);
$sugerencia = $seccion->sugerirCantidad($matricula, $capacidad);
if ($sugerencia === null) {
    echo "Error: matrícula y capacidad deben ser mayores que cero.\n";
    exit(1);
}

echo "Matrícula proyectada: $matricula alumnos\n";
echo "Capacidad por sección: $capacidad alumnos\n";
echo "Secciones sugeridas: " . $sugerencia['secciones'] . "\n";
echo "Distribución sugerida: " . implode(', ', $sugerencia['distribucion']) . " alumnos\n";
if (isset($args['aplicar'])) {
    $creadas = $seccion->crearSugeridas($sugerencia['distribucion'], $trayecto, $trimestre, $prefijo);
    echo "Automatización aplicada. Secciones creadas:\n";
    foreach ($creadas as $c) {
        echo "  $c\n";
    }
} else {
    echo "Nota: es solo una sugerencia. Agrega --aplicar con --trayecto, --trimestre y --prefijo para crearlas.\n";
}
