<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestamo_id = (int)$_POST['prestamo_id'];

    // 1. Obtener datos del préstamo
    $stmt = $con->prepare("SELECT * FROM prestamos WHERE id = ?");
    $stmt->bind_param("i", $prestamo_id);
    $stmt->execute();
    $prestamo = $stmt->get_result()->fetch_assoc();

    if ($prestamo && $prestamo['estado'] === 'ACTIVO') {
        $con->begin_transaction();
        try {
            $hoy = date('Y-m-d');
            $valor_cuota = $prestamo['valor_cuota'];

            // 2. Registrar el pago en la tabla de pagos (auditoría)
            $stmt_pago = $con->prepare("INSERT INTO prestamos_pagos (prestamo_id, monto, fecha_pago) VALUES (?, ?, ?)");
            $stmt_pago->bind_param("ids", $prestamo_id, $valor_cuota, $hoy);
            $stmt_pago->execute();

            // 3. Sumar 1 cuota pagada al préstamo
            $nuevas_cuotas_pagas = $prestamo['cuotas_pagas'] + 1;

            // 4. Verificar si ya terminó de pagar
            $nuevo_estado = ($nuevas_cuotas_pagas >= $prestamo['cuotas']) ? 'PAGADO' : 'ACTIVO';

            $stmt_update = $con->prepare("UPDATE prestamos SET cuotas_pagas = ?, estado = ?, estado_mora = 'AL DIA' WHERE id = ?");
            $stmt_update->bind_param("isi", $nuevas_cuotas_pagas, $nuevo_estado, $prestamo_id);
            $stmt_update->execute();

            // ⚠️ ACÁ ESTABA EL ERROR: Se eliminó la inserción en cuenta_corriente
            // El pago de la cuota ya se descuenta del saldo del préstamo, no de la caja diaria.

            $con->commit();
            echo "<script>window.location='prestamos.php?msg=pagado';</script>";
            exit;
        } catch (Exception $e) {
            $con->rollback();
            echo "<script>alert('Error al procesar el pago: " . addslashes($e->getMessage()) . "'); window.location='prestamos.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Préstamo no encontrado o ya está pagado.'); window.location='prestamos.php';</script>";
        exit;
    }
}
