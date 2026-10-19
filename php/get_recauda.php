<?php
include_once "../funciones/funciones.php";

header('Content-Type: application/json; charset=utf-8');

$con = conexion();
$con->set_charset("utf8mb4");

// === VALIDAR Y SANITIZAR PARAMS ===
$fecha = $_GET['fecha'] ?? '';
$movil = $_GET['movil'] ?? '';
$turno = $_GET['turno'] ?? '';

if (empty($fecha) || empty($movil) || empty($turno)) {
    echo json_encode(null);
    exit;
}

$movil = (int) $movil;
$turno = htmlspecialchars(trim($turno));

// === CONSULTA SEGURA ===
$stmt = $con->prepare("SELECT * FROM recauda WHERE fecha = ? AND turno = ? AND movil = ?");
$stmt->bind_param("ssi", $fecha, $turno, $movil);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $row = $result->fetch_assoc()) {
    // Devuelve solo los campos necesarios
    echo json_encode([
        'paga'      => floatval($row['paga']),
        'voucher'   => floatval($row['voucher']),
        'porcent'   => floatval($row['porcent']),
        'comision'  => floatval($row['comision']),
        'total'     => floatval($row['total']),
        'mp'        => floatval($row['mp']),
        'ft'        => floatval($row['ft']),
        'debe'      => floatval($row['debe'])
    ]);
} else {
    echo json_encode(null); // No hay registro
}
