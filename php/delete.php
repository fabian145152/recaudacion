<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($con->connect_error) {
    die("Conexión fallida: " . $con->connect_error);
}

// === VALIDAR ID ===
if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    echo "<script>alert('ID inválido.'); window.location='taxis.php';</script>";
    exit;
}

$id = (int)$_GET['q'];

// === OBTENER EL NÚMERO DE MÓVIL A PARTIR DEL ID ===
$stmt = $con->prepare("SELECT movil FROM moviles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo "<script>alert('No se encontró el registro con ID $id.'); window.location='taxis.php';</script>";
    exit;
}

$movil = (int)$row['movil'];

// === ELIMINAR AMBOS TURNOS DEL MÓVIL ===
$stmt = $con->prepare("DELETE FROM moviles WHERE movil = ?");
$stmt->bind_param("i", $movil);

if ($stmt->execute()) {
    $borrados = $stmt->affected_rows;
    echo "<script>
        alert('✅ Móvil $movil eliminado correctamente ($borrados turnos borrados).');
        window.location='taxis.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Error al eliminar: " . addslashes($stmt->error) . "');
        window.location='taxis.php';
    </script>";
}

$stmt->close();
$con->close();
