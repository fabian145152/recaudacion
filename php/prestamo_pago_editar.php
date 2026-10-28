<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// Auto-migración
$check = $con->query("SHOW COLUMNS FROM prestamos_pagos LIKE 'editado_por'");
if ($check && $check->num_rows === 0) {
    $con->query("ALTER TABLE prestamos_pagos ADD COLUMN registrado_por VARCHAR(100) DEFAULT NULL");
    $con->query("ALTER TABLE prestamos_pagos ADD COLUMN editado_por VARCHAR(100) DEFAULT NULL");
    $con->query("ALTER TABLE prestamos_pagos ADD COLUMN editado_fecha DATETIME DEFAULT NULL");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pago_id = (int)$_POST['pago_id'];
    $prestamo_id = (int)$_POST['prestamo_id'];
    $nuevo_monto = floatval($_POST['nuevo_monto']);

    if ($pago_id <= 0 || $prestamo_id <= 0 || $nuevo_monto <= 0) {
        echo json_encode(['ok' => false, 'error' => 'Datos inválidos. El monto debe ser mayor a 0.']);
        exit;
    }

    // === QUIÉN EDITA Y CUÁNDO ===
    $usuario_nombre = $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? 'Desconocido');
    $ahora = date('Y-m-d H:i:s');

    $con->begin_transaction();
    try {
        // 1. Actualizar el monto del pago registrando la edición
        $stmt = $con->prepare("UPDATE prestamos_pagos SET monto = ?, editado_por = ?, editado_fecha = ? WHERE id = ? AND prestamo_id = ?");
        $stmt->bind_param("dssii", $nuevo_monto, $usuario_nombre, $ahora, $pago_id, $prestamo_id);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el pago en la base de datos.");
        }
        $stmt->close();

        // 2. Obtener los datos del préstamo y recalcular el total pagado
        $stmt = $con->prepare("
            SELECT pr.*, 
                   COALESCE((SELECT SUM(monto) FROM prestamos_pagos WHERE prestamo_id = pr.id), 0) AS total_pagado
            FROM prestamos pr
            WHERE pr.id = ?
        ");
        $stmt->bind_param("i", $prestamo_id);
        $stmt->execute();
        $prestamo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$prestamo) {
            throw new Exception("Préstamo no encontrado.");
        }

        $nuevo_total_pagado = floatval($prestamo['total_pagado']);
        $nuevo_saldo = floatval($prestamo['monto_total']) - $nuevo_total_pagado;

        // 3. Recalcular el estado
        if ($nuevo_saldo <= 0.01) {
            $nuevo_estado = 'PAGADO';
            $stmt_update = $con->prepare("UPDATE prestamos SET estado = ?, cuotas_pagas = cuotas WHERE id = ?");
            $stmt_update->bind_param("si", $nuevo_estado, $prestamo_id);
        } else {
            $nuevo_estado = 'ACTIVO';
            $cuotas_pagadas = ($prestamo['valor_cuota'] > 0) ? ($nuevo_total_pagado / floatval($prestamo['valor_cuota'])) : 0;
            $stmt_update = $con->prepare("UPDATE prestamos SET estado = ?, cuotas_pagas = ? WHERE id = ?");
            $stmt_update->bind_param("sdi", $nuevo_estado, $cuotas_pagadas, $prestamo_id);
        }

        if (!$stmt_update->execute()) {
            throw new Exception("Error al actualizar el estado del préstamo.");
        }
        $stmt_update->close();

        $con->commit();
        echo json_encode(['ok' => true]);
        exit;
    } catch (Exception $e) {
        $con->rollback();
        echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
        exit;
    }
}
$con->close();
