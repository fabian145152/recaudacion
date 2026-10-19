<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$chofer_id = isset($_GET['q']) ? (int)$_GET['q'] : 0;

if ($chofer_id > 0) {
    // Actualizamos la asignación activa a inactiva (fecha_hasta = hoy)
    $hoy = date('Y-m-d');

    $stmt = $con->prepare("
        UPDATE asignaciones_auto 
        SET activa = 0, fecha_hasta = ? 
        WHERE chofer_id = ? AND activa = 1
    ");
    $stmt->bind_param("si", $hoy, $chofer_id);
    $stmt->execute();
    $stmt->close();
}

$con->close();

// Redirigimos de vuelta a la tabla de taxis
echo "<script>window.location='taxis.php';</script>";
exit;
