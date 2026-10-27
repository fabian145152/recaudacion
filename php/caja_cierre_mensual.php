<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// Auto-crear tabla
$con->query("CREATE TABLE IF NOT EXISTS caja_cierres_mensuales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mes_cerrado VARCHAR(7) NOT NULL UNIQUE,
    saldo_mp DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    saldo_ft DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deuda_total_chofers DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    a_favor_total_chofers DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    cant_choferes_deuda INT NOT NULL DEFAULT 0,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$mes = $_GET['mes'] ?? date('Y-m', strtotime('first day of last month'));
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) die("Mes inválido.");

// ¿Ya está cerrado?
$stmt = $con->prepare("SELECT id FROM caja_cierres_mensuales WHERE mes_cerrado = ?");
$stmt->bind_param("s", $mes);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $stmt->close();
    die("Este mes ya está cerrado. <a href='caja_movimientos.php'>Volver</a>");
}
$stmt->close();

// Calcular saldos al cierre del mes (acumulado hasta el último día del mes)
$primer_dia_sig = date('Y-m-01', strtotime($mes . '-01 +1 month'));
$stmt = $con->prepare("
    SELECT medio,
        COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END), 0) AS saldo
    FROM caja_movimientos
    WHERE fecha < ?
    GROUP BY medio
");
$stmt->bind_param("s", $primer_dia_sig);
$stmt->execute();
$res = $stmt->get_result();
$saldo_mp = 0;
$saldo_ft = 0;
while ($row = $res->fetch_assoc()) {
    if ($row['medio'] === 'MP') $saldo_mp = floatval($row['saldo']);
    else $saldo_ft = floatval($row['saldo']);
}
$stmt->close();

// Deudas actuales (al momento del cierre)
$deuda_total = floatval($con->query("SELECT COALESCE(SUM(saldo), 0) FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo < 0) AS sub")->fetch_row()[0] ?? 0);
$favor_total = floatval($con->query("SELECT COALESCE(SUM(saldo), 0) FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo > 0) AS sub")->fetch_row()[0] ?? 0);
$cant_deuda = intval($con->query("SELECT COUNT(*) FROM (SELECT chofer_id FROM cuenta_corriente GROUP BY chofer_id HAVING SUM(monto) < 0) AS sub")->fetch_row()[0] ?? 0);

// Insertar el cierre
$stmt = $con->prepare("INSERT INTO caja_cierres_mensuales (mes_cerrado, saldo_mp, saldo_ft, deuda_total_chofers, a_favor_total_chofers, cant_choferes_deuda) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sddddi", $mes, $saldo_mp, $saldo_ft, $deuda_total, $favor_total, $cant_deuda);
$stmt->execute();
$stmt->close();

$con->close();
header("Location: caja_movimientos.php?msg=cierre");
exit;
