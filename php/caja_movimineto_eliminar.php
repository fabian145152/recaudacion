<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$id = intval($_GET['id'] ?? 0);
$fecha_redirect = $_GET['fecha'] ?? date('Y-m-d');
$mes_redirect = $_GET['mes'] ?? date('Y-m');
$vista_redirect = $_GET['vista'] ?? 'dia';

if ($id > 0) {
    $stmt = $con->prepare("DELETE FROM caja_movimientos WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

$con->close();
header("Location: caja_movimientos.php?vista=" . urlencode($vista_redirect) . "&fecha=" . urlencode($fecha_redirect) . "&mes=" . urlencode($mes_redirect) . "&msg=del");
exit;
