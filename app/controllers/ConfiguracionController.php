<?php
// app/controllers/ConfiguracionController.php
// Migrado de configuracion.php y backup.php.

require_once APP_PATH . '/libs/GoogleAuthenticator.php';

class ConfiguracionController extends Controller {

    public function index() {
        $this->requireAdmin('dashboard');

        $config  = new Configuracion($this->conn);
        $usuario = new Usuario($this->conn);
        $error = "";
        $success = "";

        // RECIBIR ERROR DEL BACKUP (SI FALLA EL 2FA)
        if (isset($_GET['err']) && $_GET['err'] == '2fa') {
            $error = "El código 2FA ingresado para autorizar el respaldo es incorrecto.";
        }

        // PROCESAR GUARDADO DE FECHA
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'guardar_fecha') {
            $nueva_fecha = trim($_POST['fecha_inicio_lapso']);

            if (empty($nueva_fecha)) {
                $error = "Debe seleccionar una fecha válida.";
            } else {
                try {
                    $config->guardarFechaInicioLapso($nueva_fecha);
                    $success = "La fecha de inicio de lapso ha sido actualizada correctamente.";
                } catch(PDOException $e) {
                    $error = "Error al actualizar: " . $e->getMessage();
                }
            }
        }

        // OBTENER LA FECHA ACTUAL
        $fecha_actual = $config->fechaInicioLapso();

        // VERIFICAR ESTADO DEL 2FA DEL ADMINISTRADOR ACTUAL
        $estado_2fa = $usuario->estado2fa($_SESSION['usuario_id']);

        // GENERAR UNA CONTRASEÑA ALEATORIA SEGURA PARA EL RESPALDO
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $pass_backup = substr(str_shuffle($caracteres), 0, 10);

        $this->render('configuracion/index', [
            'error'        => $error,
            'success'      => $success,
            'fecha_actual' => $fecha_actual,
            'estado_2fa'   => $estado_2fa,
            'pass_backup'  => $pass_backup,
        ]);
    }

    /**
     * Genera y descarga el respaldo ZIP cifrado (backup.php).
     * Solo responde a POST con password_respaldo; verifica 2FA si está activo.
     */
    public function backup() {
        $this->requireAdmin('dashboard');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['password_respaldo'])) {
            $this->redirect('configuracion');
        }

        $usuario = new Usuario($this->conn);

        // --- NUEVO: VERIFICAR 2FA ANTES DE GENERAR EL RESPALDO ---
        $admin_data = $usuario->datosSeguridad($_SESSION['usuario_id']);

        if ($admin_data['2fa_activo'] == 1) {
            if (empty($_POST['codigo_2fa'])) {
                $this->redirect('configuracion', ['err' => '2fa']);
            }

            $ga = new PHPGangsta_GoogleAuthenticator();
            $secret_descifrado = descifrar_secreto($admin_data['2fa_secret']);

            if (!$ga->verifyCode($secret_descifrado, trim($_POST['codigo_2fa']), 2)) {
                $this->redirect('configuracion', ['err' => '2fa']);
            }
        }
        // ---------------------------------------------------------

        $password_cifrado = $_POST['password_respaldo'];

        $fecha_backup = date("Y-m-d_H-i-s");
        $nombre_zip = "Respaldo_SIGEHO_" . $fecha_backup . ".zip";
        $ruta_zip = sys_get_temp_dir() . '/' . $nombre_zip;

        $zip = new ZipArchive();
        if ($zip->open($ruta_zip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Error crítico: No se pudo crear el archivo ZIP en el servidor.");
        }

        $tablas = ['aulas', 'configuracion', 'horarios', 'log_choques', 'materias', 'profesores', 'secciones', 'usuarios'];
        $sql_dump = "-- ==========================================\n";
        $sql_dump .= "-- RESPALDO DE BASE DE DATOS SIGEHO-CONT\n";
        $sql_dump .= "-- Fecha de Generación: " . date('Y-m-d H:i:s') . "\n";
        $sql_dump .= "-- ==========================================\n\n";

        foreach ($tablas as $tabla) {
            $stmt = $this->conn->query("SELECT * FROM $tabla");
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($filas) > 0) {
                $sql_dump .= "-- Volcado de datos para la tabla `$tabla`\n";
                foreach ($filas as $fila) {
                    $columnas = array_keys($fila);
                    $valores = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes($val) . "'";
                    }, array_values($fila));

                    $sql_dump .= "INSERT INTO `$tabla` (`" . implode("`, `", $columnas) . "`) VALUES (" . implode(", ", $valores) . ");\n";
                }
                $sql_dump .= "\n";
            }
        }

        $zip->addFromString('base_de_datos_sigeho.sql', $sql_dump);
        $zip->setEncryptionName('base_de_datos_sigeho.sql', ZipArchive::EM_AES_256, $password_cifrado);

        // NOTA MVC: las imágenes ahora viven en public/assets/img.
        $dir_img = BASE_PATH . '/public/assets/img';
        if (is_dir($dir_img)) {
            $archivos = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_img), RecursiveIteratorIterator::LEAVES_ONLY);

            foreach ($archivos as $name => $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen(realpath($dir_img)) + 1);

                    $zip->addFile($filePath, 'imagenes_sistema/' . $relativePath);
                    $zip->setEncryptionName('imagenes_sistema/' . $relativePath, ZipArchive::EM_AES_256, $password_cifrado);
                }
            }
        }

        $zip->close();

        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $nombre_zip . '"');
        header('Content-Length: ' . filesize($ruta_zip));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($ruta_zip);

        unlink($ruta_zip);
        exit();
    }
}
