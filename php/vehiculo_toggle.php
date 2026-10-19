<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID inválido.");
}
$id = (int)$_GET['q'];

$stmt = $con->prepare("SELECT activo, movil FROM vehiculos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "<script>alert('Vehículo no encontrado.'); window.location='vehiculo_lista.php';</script>";
    exit;
}

$nuevo_estado = $row['activo'] ? 0 : 1;

// Si vas a desactivar, verificar que no tenga asignaciones activas
if ($nuevo_estado === 0) {
    $check = $con->prepare("SELECT COUNT(*) AS cant FROM asignaciones_auto WHERE vehiculo_id = ? AND activa = 1");
    $check->bind_param("i", $id);
    $check->execute();
    $cant = (int)$check->get_result()->fetch_assoc()['cant'];
    $check->close();

    if ($cant > 0) {
        echo "<script>alert('❌ No se puede desactivar el vehículo porque tiene $cant turno(s) asignado(s). Primero reasigná esos turnos.'); window.location='vehiculo_lista.php';</script>";
        exit;
    }
}

$stmt = $con->prepare("UPDATE vehiculos SET activo = ? WHERE id = ?");
$stmt->bind_param("ii", $nuevo_estado, $id);
$stmt->execute();
$stmt->close();

$accion = $nuevo_estado ? 'activado' : 'desactivado';
echo "<script>alert('✅ Vehículo #{$row['movil']} $accion.'); window.location='vehiculo_lista.php';</script>";
