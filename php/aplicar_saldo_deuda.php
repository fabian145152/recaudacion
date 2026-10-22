<?php
include_once "../funciones/funciones.php";
header('Content-Type: application/json');

$con = conexion();
$con->set_charset("utf8mb4");

$chofer_id = (int)($_POST['chofer_id'] ?? 0);
$monto = floatval($_POST['monto'] ?? 0);

if ($chofer_id <= 0 || $monto <= 0) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

// 1. Buscar el préstamo activo de este chofer
$stmt = $con->prepare("
    SELECT pr.id, pr.monto_total, pr.cuotas, pr.valor_cuota, 
           COALESCE((SELECT SUM(monto) FROM prestamos_pagos WHERE prestamo_id = pr.id), 0) AS total_pagado
    FROM prestamos pr
    WHERE pr.chofer_id = ? AND pr.estado = 'ACTIVO' LIMIT 1
");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$prestamo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$prestamo) {
    echo json_encode(['ok' => false, 'error' => 'No se encontró un préstamo activo para este chofer.']);
    exit;
}

$con->begin_transaction();
try {
    $hoy = date('Y-m-d');
    $prestamo_id = $prestamo['id'];

    // 2. Descontar el saldo a favor de la Cuenta Corriente
    $descripcion_cc = "Pago de deuda con saldo a favor";
    $stmt_cc = $con->prepare("INSERT INTO cuenta_corriente (chofer_id, fecha, monto, tipo, descripcion) VALUES (?, ?, ?, 'DEUDA', ?)");
    $monto_negativo = -$monto;
    $stmt_cc->bind_param("isds", $chofer_id, $hoy, $monto_negativo, $descripcion_cc);
    $stmt_cc->execute();
    $stmt_cc->close();

    // 3. Registrar el pago en la tabla de auditoría
    $stmt_pago = $con->prepare("INSERT INTO prestamos_pagos (prestamo_id, monto, fecha_pago) VALUES (?, ?, ?)");
    $stmt_pago->bind_param("ids", $prestamo_id, $monto, $hoy);
    $stmt_pago->execute();
    $stmt_pago->close();

    // 4. Verificar si ya se cubrió el total
    $nuevo_total_pagado = $prestamo['total_pagado'] + $monto;
    $saldo_restante = $prestamo['monto_total'] - $nuevo_total_pagado;

    if ($saldo_restante <= 0.01) { // Si ya pagó todo (o un centavo de diferencia por redondeo)
        $nuevo_estado = 'PAGADO';
        $stmt_up = $con->prepare("UPDATE prestamos SET estado = ?, estado_mora = 'AL DIA', cuotas_pagas = cuotas WHERE id = ?");
        $stmt_up->bind_param("si", $nuevo_estado, $prestamo_id);
        $stmt_up->execute();
        $stmt_up->close();
    } else {
        // Si es un pago parcial, NO tocamos cuotas_pagas para no romper el INT de la BD.
        // La deuda real se calcula con la tabla de pagos.
    }

    $con->commit();
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    $con->rollback();
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

$con->close();
