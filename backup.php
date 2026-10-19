<?php
// backup.php
include_once "funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// Obtener nombre de la BD
$db_nombre = $con->query("SELECT DATABASE()")->fetch_row()[0];

// Generar nombre del archivo
$fecha = date('Y-m-d_H-i-s');
$nombre_archivo = "backup_recaudacion_" . $fecha . ".sql";

// Ruta donde se va a guardar (carpeta backup en la raíz del proyecto)
$ruta_guardado = __DIR__ . "/backup/" . $nombre_archivo;

// Función para generar el SQL de una tabla
function generar_backup($con, $db_nombre)
{
    $output = "-- BACKUP DE BASE DE DATOS: $db_nombre\n";
    $output .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    $output .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
    $output .= "SET time_zone = \"+00:00\";\n\n";
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
    $output .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
    $output .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
    $output .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

    // Obtener todas las tablas
    $tablas = [];
    $res = $con->query("SHOW TABLES");
    while ($row = $res->fetch_row()) {
        $tablas[] = $row[0];
    }

    foreach ($tablas as $tabla) {
        // Estructura de la tabla
        $res_create = $con->query("SHOW CREATE TABLE `$tabla`");
        $row_create = $res_create->fetch_row();
        $create_sql = $row_create[1];

        $output .= "-- --------------------------------------------------------\n";
        $output .= "-- Estructura de la tabla `$tabla`\n";
        $output .= "-- --------------------------------------------------------\n\n";
        $output .= "DROP TABLE IF EXISTS `$tabla`;\n";
        $output .= $create_sql . ";\n\n";

        // Datos de la tabla
        $res_datos = $con->query("SELECT * FROM `$tabla`");
        if ($res_datos && $res_datos->num_rows > 0) {
            $output .= "-- Volcado de datos para la tabla `$tabla`\n";

            while ($fila = $res_datos->fetch_assoc()) {
                $columnas = array_keys($fila);
                $valores = [];

                foreach ($fila as $valor) {
                    if (is_null($valor)) {
                        $valores[] = "NULL";
                    } else {
                        $valor_escapado = $con->real_escape_string($valor);
                        $valores[] = "'$valor_escapado'";
                    }
                }

                $output .= "INSERT INTO `$tabla` (`" . implode("`, `", $columnas) . "`) VALUES (" . implode(", ", $valores) . ");\n";
            }
            $output .= "\n";
        }
    }
    return $output;
}

// Generar el contenido del backup
$contenido_sql = generar_backup($con, $db_nombre);

// Guardar el archivo en la carpeta backup
if (file_put_contents($ruta_guardado, $contenido_sql)) {
    // Si se guardó bien, volver al index con un mensaje de éxito
    echo "<script>
        alert('✅ Backup guardado con éxito en la carpeta /backup/ con el nombre: $nombre_archivo');
        window.location = 'index.php';
    </script>";
} else {
    // Si hubo un error (permisos)
    echo "<script>
        alert('❌ Error al guardar el backup. Verificá los permisos de la carpeta /backup/');
        window.location = 'index.php';
    </script>";
}

$con->close();
exit;
