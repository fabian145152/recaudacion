<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestamo_id = (int)$_POST['prestamo_id'];
    $monto_manual = floatval($_POST['monto_pago'] ?? 0); // Recibimos el monto del input

    // 1. Obtener datos del préstamo y el total pagado
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

    if ($prestamo && $prestamo['estado'] === 'ACTIVO') {
        $con->begin_transaction();
        try {
            $hoy = date('Y-m-d');

            // 2. Calcular el saldo real pendiente
            $saldo_pendiente = floatval($prestamo['monto_total']) - floatval($prestamo['total_pagado']);

            // Si no se envió monto, cobramos la cuota normal (o el saldo si es menor)
            if ($monto_manual <= 0) {
                $monto_a_pagar = ($saldo_pendiente < floatval($prestamo['valor_cuota'])) ? $saldo_pendiente : floatval($prestamo['valor_cuota']);
            } else {
                $monto_a_pagar = $monto_manual;
            }

            // Validar que el monto no supere el saldo pendiente
            if ($monto_a_pagar > $saldo_pendiente) {
                $monto_a_pagar = $saldo_pendiente;
            }

            $monto_a_pagar = round($monto_a_pagar, 2);

            if ($monto_a_pagar <= 0) {
                throw new Exception("El monto a pagar debe ser mayor a 0.");
            }

            // 3. Registrar el pago
            $stmt_pago = $con->prepare("INSERT INTO prestamos_pagos (prestamo_id, monto, fecha_pago) VALUES (?, ?, ?)");
            $stmt_pago->bind_param("ids", $prestamo_id, $monto_a_pagar, $hoy);
            $stmt_pago->execute();
            $stmt_pago->close();

            // 4. Verificar si terminó de pagar
            $nuevo_total_pagado = floatval($prestamo['total_pagado']) + $monto_a_pagar;
            $nuevo_saldo = floatval($prestamo['monto_total']) - $nuevo_total_pagado;

            if ($nuevo_saldo <= 0.01) {
                $nuevo_estado = 'PAGADO';
                $stmt_update = $con->prepare("UPDATE prestamos SET estado = ?, estado_mora = 'AL DIA', cuotas_pagas = cuotas WHERE id = ?");
                $stmt_update->bind_param("si", $nuevo_estado, $prestamo_id);
                $stmt_update->execute();
                $stmt_update->close();
            } else {
                // Si no terminó, actualizamos las cuotas proporcionalmente
                $cuotas_equivalentes = $monto_a_pagar / floatval($prestamo['valor_cuota']);
                $nuevas_cuotas_pagas = floatval($prestamo['cuotas_pagas']) + $cuotas_equivalentes;

                $stmt_update = $con->prepare("UPDATE prestamos SET cuotas_pagas = ?, estado_mora = 'AL DIA' WHERE id = ?");
                $stmt_update->bind_param("di", $nuevas_cuotas_pagas, $prestamo_id);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $con->commit();
            echo json_encode(['ok' => true]); // Devolvemos JSON para que el JS sepa que terminó
            exit;
        } catch (Exception $e) {
            $con->rollback();
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            exit;
        }
    } else {
        echo json_encode(['ok' => false, 'error' => 'Préstamo no encontrado o ya está pagado.']);
        exit;
    }
}
