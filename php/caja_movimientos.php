<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// ========== AUTO-CREAR TABLAS ==========
$con->query("CREATE TABLE IF NOT EXISTS caja_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tipo ENUM('INGRESO','EXTRACCION') NOT NULL,
    medio ENUM('MP','FT') NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descripcion VARCHAR(255) DEFAULT '',
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha),
    INDEX idx_medio (medio),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

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

// ========== FILTROS ==========
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_filtro)) $fecha_filtro = date('Y-m-d');
$mes_filtro = $_GET['mes'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $mes_filtro)) $mes_filtro = date('Y-m');
$vista = $_GET['vista'] ?? 'dia';

// ========== SALDOS TOTALES (all-time) ==========
$saldo_mp = floatval($con->query("SELECT COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END), 0) FROM caja_movimientos WHERE medio='MP'")->fetch_row()[0] ?? 0);
$saldo_ft = floatval($con->query("SELECT COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END), 0) FROM caja_movimientos WHERE medio='FT'")->fetch_row()[0] ?? 0);

// ========== SALDOS DEL MES ==========
$stmt = $con->prepare("
    SELECT medio,
        COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE 0 END), 0) AS ingresos,
        COALESCE(SUM(CASE WHEN tipo='EXTRACCION' THEN monto ELSE 0 END), 0) AS extracciones,
        COALESCE(SUM(CASE WHEN tipo='INGRESO' THEN monto ELSE -monto END), 0) AS neto
    FROM caja_movimientos
    WHERE DATE_FORMAT(fecha, '%Y-%m') = ?
    GROUP BY medio
");
$stmt->bind_param("s", $mes_filtro);
$stmt->execute();
$res = $stmt->get_result();
$mes_data = [
    'MP' => ['ingresos' => 0, 'extracciones' => 0, 'neto' => 0],
    'FT' => ['ingresos' => 0, 'extracciones' => 0, 'neto' => 0]
];
while ($row = $res->fetch_assoc()) {
    $mes_data[$row['medio']] = $row;
}
$stmt->close();

// ========== MOVIMIENTOS DEL DÍA ==========
$stmt = $con->prepare("SELECT * FROM caja_movimientos WHERE fecha = ? ORDER BY creado DESC");
$stmt->bind_param("s", $fecha_filtro);
$stmt->execute();
$movs_dia = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ========== MOVIMIENTOS DEL MES ==========
$stmt = $con->prepare("SELECT * FROM caja_movimientos WHERE DATE_FORMAT(fecha, '%Y-%m') = ? ORDER BY fecha DESC, creado DESC");
$stmt->bind_param("s", $mes_filtro);
$stmt->execute();
$movs_mes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ========== CIERRES ==========
$cierres = $con->query("SELECT * FROM caja_cierres_mensuales ORDER BY mes_cerrado DESC LIMIT 12")->fetch_all(MYSQLI_ASSOC);
$meses_cerrados = array_column($cierres, 'mes_cerrado');

// ========== DEUDAS ACTUALES ==========
$deuda_total = floatval($con->query("SELECT COALESCE(SUM(saldo), 0) FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo < 0) AS sub")->fetch_row()[0] ?? 0);
$favor_total = floatval($con->query("SELECT COALESCE(SUM(saldo), 0) FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo > 0) AS sub")->fetch_row()[0] ?? 0);
$cant_deuda = intval($con->query("SELECT COUNT(*) FROM (SELECT chofer_id FROM cuenta_corriente GROUP BY chofer_id HAVING SUM(monto) < 0) AS sub")->fetch_row()[0] ?? 0);

// ========== ¿MOSTRAR BOTÓN DE CIERRE? ==========
$dia_hoy = (int)date('j');
$mes_anterior = date('Y-m', strtotime('first day of last month'));
$mostrar_cierre = ($dia_hoy <= 10 && !in_array($mes_anterior, $meses_cerrados));

$msg = $_GET['msg'] ?? '';
$con->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Movimientos de Caja</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            margin: 0;
        }

        .container-caja {
            max-width: 1280px;
            margin: 0 auto;
            padding: 15px;
        }

        h2 {
            color: #333;
            font-size: 1.5rem;
            margin: 10px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        h2 i {
            color: #667eea;
        }

        .saldos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .saldo-card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #667eea;
        }

        .saldo-card.mp {
            border-left-color: #17a2b8;
        }

        .saldo-card.ft {
            border-left-color: #28a745;
        }

        .saldo-card.deuda {
            border-left-color: #dc3545;
        }

        .saldo-card.favor {
            border-left-color: #20c997;
        }

        .saldo-card .titulo {
            font-size: 0.75rem;
            color: #666;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .saldo-card .valor {
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
        }

        .saldo-card.mp .valor {
            color: #17a2b8;
        }

        .saldo-card.ft .valor {
            color: #28a745;
        }

        .saldo-card.deuda .valor {
            color: #dc3545;
        }

        .saldo-card.favor .valor {
            color: #20c997;
        }

        .saldo-card .detalle {
            font-size: 0.72rem;
            color: #999;
            margin-top: 4px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card h5 {
            font-size: 0.9rem;
            text-transform: uppercase;
            color: #667eea;
            margin: 0 0 12px 0;
            font-weight: 700;
            border-bottom: 1px solid #eef1f7;
            padding-bottom: 8px;
        }

        .filtros {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .filtros label {
            font-weight: 600;
            color: #555;
            margin: 0;
        }

        .filtros input,
        .filtros select {
            padding: 6px 10px;
            border: 1px solid #dfe6ef;
            border-radius: 6px;
            font-size: 0.85rem;
        }

        .btn {
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-warning {
            background: #ffc107;
            color: #856404;
        }

        .btn-warning:hover {
            color: #856404;
            opacity: 0.9;
        }

        .btn-sm {
            padding: 3px 8px;
            font-size: 0.72rem;
        }

        .form-inline {
            display: grid;
            grid-template-columns: 130px 130px 130px 130px 1fr 100px;
            gap: 10px;
            align-items: end;
        }

        .form-inline .campo {
            display: flex;
            flex-direction: column;
        }

        .form-inline label {
            font-size: 0.72rem;
            color: #666;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .form-inline input,
        .form-inline select {
            padding: 6px 8px;
            border: 1px solid #dfe6ef;
            border-radius: 5px;
            font-size: 0.85rem;
            width: 100%;
        }

        table.movs {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        table.movs th {
            background: #f8f9fc;
            color: #555;
            padding: 8px 10px;
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #eef1f7;
        }

        table.movs td {
            padding: 8px 10px;
            border-bottom: 1px solid #f0f3f9;
        }

        table.movs tr:hover {
            background: #fafbfe;
        }

        .badge-ingreso {
            background: #d4edda;
            color: #155724;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-extraccion {
            background: #f8d7da;
            color: #721c24;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-mp {
            background: #d1ecf1;
            color: #0c5460;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-ft {
            background: #d4edda;
            color: #155724;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .alerta {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alerta-info {
            background: linear-gradient(135deg, #cce5ff, #b3d9ff);
            border-left: 4px solid #004085;
            color: #004085;
        }

        .alerta-warning {
            background: linear-gradient(135deg, #fff3cd, #ffe69c);
            border-left: 4px solid #856404;
            color: #856404;
            font-weight: 600;
        }

        .alerta-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid #28a745;
            color: #155724;
        }

        .alerta-cierre {
            background: linear-gradient(135deg, #fff3cd, #ffe69c);
            border-left: 4px solid #856404;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
        }

        .alerta-cierre h4 {
            margin: 0 0 8px 0;
            color: #856404;
            font-size: 1rem;
        }

        .alerta-cierre .acciones-cierre {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .cierres-lista {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .cierre-item {
            background: #f8f9fc;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            border-left: 3px solid #17a2b8;
        }

        .cierre-item strong {
            display: block;
            color: #17a2b8;
            font-size: 0.8rem;
            margin-bottom: 2px;
        }

        .tabs {
            display: flex;
            gap: 4px;
            margin-bottom: 12px;
        }

        .tab {
            padding: 6px 14px;
            background: #eef1f7;
            color: #555;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.82rem;
            font-weight: 600;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .tab:hover {
            background: #dde3ec;
            color: #333;
        }

        .tab.active:hover {
            color: white;
            opacity: 0.9;
        }

        .money {
            font-weight: 700;
        }

        .money.positive {
            color: #28a745;
        }

        .money.negative {
            color: #dc3545;
        }

        .money.neutral {
            color: #333;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="container-caja">
        <h2><i class="fas fa-cash-register"></i> Movimientos de Caja</h2>

        <?php if ($msg === 'add'): ?>
            <div class="alerta alerta-success"><i class="fas fa-check-circle"></i> Movimiento guardado correctamente.</div>
        <?php elseif ($msg === 'del'): ?>
            <div class="alerta alerta-info"><i class="fas fa-trash"></i> Movimiento eliminado.</div>
        <?php elseif ($msg === 'cierre'): ?>
            <div class="alerta alerta-success"><i class="fas fa-lock"></i> Mes cerrado correctamente. Revisá el resumen abajo.</div>
        <?php endif; ?>

        <!-- ========== SALDOS ACTUALES ========== -->
        <div class="saldos-grid">
            <div class="saldo-card mp">
                <div class="titulo"><i class="fas fa-mobile-alt"></i> Saldo MP (total)</div>
                <div class="valor">$<?php echo number_format($saldo_mp, 2, ',', '.'); ?></div>
                <div class="detalle">Mes actual: <?php echo ($mes_data['MP']['neto'] >= 0 ? '+' : '') . number_format($mes_data['MP']['neto'], 2, ',', '.'); ?></div>
            </div>
            <div class="saldo-card ft">
                <div class="titulo"><i class="fas fa-file-invoice"></i> Saldo FT (total)</div>
                <div class="valor">$<?php echo number_format($saldo_ft, 2, ',', '.'); ?></div>
                <div class="detalle">Mes actual: <?php echo ($mes_data['FT']['neto'] >= 0 ? '+' : '') . number_format($mes_data['FT']['neto'], 2, ',', '.'); ?></div>
            </div>
            <div class="saldo-card deuda">
                <div class="titulo"><i class="fas fa-exclamation-triangle"></i> Deuda choferes</div>
                <div class="valor">$<?php echo number_format(abs($deuda_total), 0, ',', '.'); ?></div>
                <div class="detalle"><?php echo $cant_deuda; ?> choferes con deuda</div>
            </div>
            <div class="saldo-card favor">
                <div class="titulo"><i class="fas fa-check-circle"></i> A favor choferes</div>
                <div class="valor">$<?php echo number_format($favor_total, 0, ',', '.'); ?></div>
                <div class="detalle">Saldo acumulado</div>
            </div>
        </div>

        <!-- ========== BOTÓN DE CIERRE MENSUAL ========== -->
        <?php if ($mostrar_cierre): ?>
            <div class="alerta-cierre">
                <h4><i class="fas fa-calendar-check"></i> Cierre del mes de <?php echo date('F Y', strtotime($mes_anterior . '-01')); ?></h4>
                <p style="margin: 0; font-size: 0.85rem;">
                    Ya pasó el día 1. Podés cerrar el mes anterior para archivar los totales y registrar las deudas arrastradas.
                    <br><strong>Los saldos de caja NO se modifican</strong>, solo se registra el cierre en el historial.
                </p>
                <div class="acciones-cierre">
                    <a href="caja_cierre_mensual.php?mes=<?php echo $mes_anterior; ?>"
                        class="btn btn-warning"
                        onclick="return confirm('¿Cerrar el mes de <?php echo date('F Y', strtotime($mes_anterior . '-01')); ?>?\n\nEsto archivará los totales actuales en el historial. No se modifican los datos existentes.');">
                        <i class="fas fa-lock"></i> Cerrar <?php echo date('F Y', strtotime($mes_anterior . '-01')); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- ========== HISTORIAL DE CIERRES ========== -->
        <?php if (!empty($cierres)): ?>
            <div class="card">
                <h5><i class="fas fa-history"></i> Historial de Cierres Mensuales</h5>
                <div class="cierres-lista">
                    <?php foreach ($cierres as $c): ?>
                        <div class="cierre-item">
                            <strong><?php echo date('M Y', strtotime($c['mes_cerrado'] . '-01')); ?></strong>
                            MP: $<?php echo number_format($c['saldo_mp'], 0, ',', '.'); ?><br>
                            FT: $<?php echo number_format($c['saldo_ft'], 0, ',', '.'); ?><br>
                            Deuda: $<?php echo number_format(abs($c['deuda_total_chofers']), 0, ',', '.'); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ========== FORMULARIO: NUEVO MOVIMIENTO ========== -->
        <div class="card">
            <h5><i class="fas fa-plus-circle"></i> Registrar Nuevo Movimiento</h5>
            <form method="POST" action="caja_movimiento_guardar.php" class="form-inline">
                <div class="campo">
                    <label>Fecha</label>
                    <input type="date" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="campo">
                    <label>Tipo</label>
                    <select name="tipo" required>
                        <option value="INGRESO">💰 Ingreso</option>
                        <option value="EXTRACCION">💸 Extracción</option>
                    </select>
                </div>
                <div class="campo">
                    <label>Medio</label>
                    <select name="medio" required>
                        <option value="FT">FT (Efectivo)</option>
                        <option value="MP">MP (Mercado Pago)</option>
                    </select>
                </div>
                <div class="campo">
                    <label>Monto ($)</label>
                    <input type="number" name="monto" step="0.01" min="0.01" placeholder="0.00" required>
                </div>
                <div class="campo">
                    <label>Descripción / Detalle</label>
                    <input type="text" name="descripcion" maxlength="255" placeholder="Ej: Retiro a banco, gasto combustible, etc.">
                </div>
                <div class="campo">
                    <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>

        <!-- ========== TABS DÍA / MES ========== -->
        <div class="tabs">
            <a href="?vista=dia&fecha=<?php echo urlencode($fecha_filtro); ?>&mes=<?php echo urlencode($mes_filtro); ?>" class="tab <?php echo $vista === 'dia' ? 'active' : ''; ?>"><i class="fas fa-calendar-day"></i> Vista Día</a>
            <a href="?vista=mes&fecha=<?php echo urlencode($fecha_filtro); ?>&mes=<?php echo urlencode($mes_filtro); ?>" class="tab <?php echo $vista === 'mes' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Vista Mes</a>
        </div>

        <!-- ========== FILTROS + TABLA ========== -->
        <div class="card">
            <h5>
                <?php if ($vista === 'dia'): ?>
                    <i class="fas fa-list"></i> Movimientos del día <?php echo date('d/m/Y', strtotime($fecha_filtro)); ?>
                <?php else: ?>
                    <i class="fas fa-list"></i> Movimientos de <?php echo date('F Y', strtotime($mes_filtro . '-01')); ?>
                <?php endif; ?>
            </h5>

            <div class="filtros" style="margin-bottom: 15px;">
                <?php if ($vista === 'dia'): ?>
                    <form method="GET" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                        <input type="hidden" name="vista" value="dia">
                        <label>Fecha:</label>
                        <input type="date" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>" max="<?php echo date('Y-m-d'); ?>" onchange="this.form.submit()">
                        <input type="hidden" name="mes" value="<?php echo htmlspecialchars($mes_filtro); ?>">
                    </form>
                <?php else: ?>
                    <form method="GET" style="display: flex; gap: 10px; align-items: center; margin: 0;">
                        <input type="hidden" name="vista" value="mes">
                        <label>Mes:</label>
                        <input type="month" name="mes" value="<?php echo htmlspecialchars($mes_filtro); ?>" onchange="this.form.submit()">
                        <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>">
                    </form>
                <?php endif; ?>
            </div>

            <?php
            $movs_mostrar = ($vista === 'dia') ? $movs_dia : $movs_mes;
            if (empty($movs_mostrar)):
            ?>
                <div style="text-align: center; padding: 30px; color: #999; font-size: 0.9rem;">
                    <i class="fas fa-inbox" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.5;"></i>
                    No hay movimientos registrados en este período.
                </div>
            <?php else: ?>
                <table class="movs">
                    <thead>
                        <tr>
                            <th style="width: 90px;">Fecha</th>
                            <th style="width: 90px;">Tipo</th>
                            <th style="width: 70px;">Medio</th>
                            <th style="width: 110px; text-align: right;">Monto</th>
                            <th>Descripción</th>
                            <th style="width: 60px; text-align: center;">Acc.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($movs_mostrar as $m): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></td>
                                <td><span class="badge-<?php echo strtolower($m['tipo']); ?>"><?php echo $m['tipo']; ?></span></td>
                                <td><span class="badge-<?php echo strtolower($m['medio']); ?>"><?php echo $m['medio']; ?></span></td>
                                <td style="text-align: right;" class="money <?php echo $m['tipo'] === 'INGRESO' ? 'positive' : 'negative'; ?>">
                                    <?php echo $m['tipo'] === 'INGRESO' ? '+' : '-'; ?>$<?php echo number_format($m['monto'], 2, ',', '.'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($m['descripcion'] ?: '—'); ?></td>
                                <td style="text-align: center;">
                                    <a href="caja_movimiento_eliminar.php?id=<?php echo $m['id']; ?>&fecha=<?php echo urlencode($fecha_filtro); ?>&mes=<?php echo urlencode($mes_filtro); ?>&vista=<?php echo $vista; ?>"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('¿Eliminar este movimiento?');">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div style="margin-top: 15px; padding-top: 10px; border-top: 1px solid #eef1f7; font-size: 0.85rem;">
                    <strong>Totales del <?php echo $vista === 'dia' ? 'día' : 'mes'; ?>:</strong>
                    <?php
                    $tot_mp = 0;
                    $tot_ft = 0;
                    foreach ($movs_mostrar as $m) {
                        $delta = $m['tipo'] === 'INGRESO' ? $m['monto'] : -$m['monto'];
                        if ($m['medio'] === 'MP') $tot_mp += $delta;
                        else $tot_ft += $delta;
                    }
                    ?>
                    <span style="margin-left: 10px;">MP: <span class="money <?php echo $tot_mp >= 0 ? 'positive' : 'negative'; ?>"><?php echo $tot_mp >= 0 ? '+' : ''; ?>$<?php echo number_format($tot_mp, 2, ',', '.'); ?></span></span>
                    <span style="margin-left: 20px;">FT: <span class="money <?php echo $tot_ft >= 0 ? 'positive' : 'negative'; ?>"><?php echo $tot_ft >= 0 ? '+' : ''; ?>$<?php echo number_format($tot_ft, 2, ',', '.'); ?></span></span>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>