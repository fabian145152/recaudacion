<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de chofer inválido.");
}
$chofer_id = (int)$_GET['q'];

// Datos del chofer
$stmt = $con->prepare("SELECT * FROM choferes WHERE id = ?");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) die("Chofer no encontrado.");

// Movimientos
$stmt = $con->prepare("
    SELECT * FROM cuenta_corriente
    WHERE chofer_id = ?
    ORDER BY fecha DESC, id DESC
");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$movimientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Saldo total
$saldo = 0;
foreach ($movimientos as $m) {
    $saldo += floatval($m['monto']);
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Cuenta Corriente - <?php echo htmlspecialchars($chofer['nombre']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            border-radius: 12px;
            padding: 20px 25px;
            color: white;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .header-card .icon-big {
            font-size: 2rem;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
        }

        .header-card h1 {
            font-size: 1.4rem;
            margin: 0;
            font-weight: 600;
        }

        .header-card p {
            margin: 3px 0 0 0;
            opacity: 0.95;
            font-size: 0.9rem;
        }

        .saldo-caja {
            background: white;
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .saldo-caja .label {
            font-size: 0.85rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .saldo-caja .valor {
            font-size: 2rem;
            font-weight: 700;
        }

        .saldo-negativo {
            color: #dc3545;
        }

        .saldo-positivo {
            color: #28a745;
        }

        .saldo-neutral {
            color: #888;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-size: 0.9rem;
        }

        thead {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eef1f7;
        }

        tbody tr:hover {
            background: #f8f9fc;
        }

        .badge-tipo {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .tipo-deuda {
            background: #f8d7da;
            color: #721c24;
        }

        .tipo-acuenta {
            background: #d4edda;
            color: #155724;
        }

        .tipo-pago {
            background: #cce5ff;
            color: #004085;
        }

        .tipo-ajuste {
            background: #e2e3e5;
            color: #383d41;
        }

        .monto-neg {
            color: #dc3545;
            font-weight: 700;
        }

        .monto-pos {
            color: #28a745;
            font-weight: 700;
        }

        .btn-volver {
            background: #eef1f7;
            color: #555;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 15px;
        }

        .btn-volver:hover {
            background: #dde3ec;
            color: #333;
            text-decoration: none;
        }

        .btn-pagar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-top: 15px;
            margin-left: 10px;
        }

        .btn-pagar:hover {
            color: white;
            opacity: 0.9;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <div class="header-card">
            <div class="icon-big"><i class="fas fa-wallet"></i></div>
            <div>
                <h1>Cuenta Corriente</h1>
                <p><?php echo htmlspecialchars($chofer['nombre']); ?> — Móvil #<?php echo $chofer['movil']; ?></p>
            </div>
        </div>

        <div class="saldo-caja">
            <div>
                <div class="label">Saldo actual</div>
                <div class="valor <?php echo $saldo < 0 ? 'saldo-negativo' : ($saldo > 0 ? 'saldo-positivo' : 'saldo-neutral'); ?>">
                    <?php if ($saldo < 0): ?>
                        -$<?php echo number_format(abs($saldo), 2, ',', '.'); ?>
                    <?php elseif ($saldo > 0): ?>
                        +$<?php echo number_format($saldo, 2, ',', '.'); ?>
                    <?php else: ?>
                        $0,00
                    <?php endif; ?>
                </div>
            </div>
            <div style="text-align:right;">
                <?php if ($saldo < 0): ?>
                    <span style="color:#dc3545; font-weight:600; font-size:1.1rem;">DEBE PLATA</span>
                <?php elseif ($saldo > 0): ?>
                    <span style="color:#28a745; font-weight:600; font-size:1.1rem;">A FAVOR</span>
                <?php else: ?>
                    <span style="color:#888; font-weight:600; font-size:1.1rem;">EQUILIBRADO</span>
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($movimientos)): ?>
            <div style="text-align:center; padding:40px; color:#888; background:white; border-radius:12px;">
                <i class="fas fa-inbox" style="font-size:2rem;"></i>
                <p>Sin movimientos registrados.</p>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th style="text-align:right;">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $m):
                        $monto = floatval($m['monto']);
                        $clase_tipo = 'tipo-ajuste';
                        if ($m['tipo'] === 'DEUDA')   $clase_tipo = 'tipo-deuda';
                        if ($m['tipo'] === 'A CUENTA') $clase_tipo = 'tipo-acuenta';
                        if ($m['tipo'] === 'PAGO')    $clase_tipo = 'tipo-pago';
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></td>
                            <td><span class="badge-tipo <?php echo $clase_tipo; ?>"><?php echo htmlspecialchars($m['tipo']); ?></span></td>
                            <td><?php echo htmlspecialchars($m['descripcion']); ?></td>
                            <td style="text-align:right;" class="<?php echo $monto < 0 ? 'monto-neg' : 'monto-pos'; ?>">
                                <?php echo ($monto < 0 ? '-' : '+') . '$' . number_format(abs($monto), 2, ',', '.'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="cuenta.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver</a>

        <?php if ($saldo < 0): ?>
            <a href="cuenta_pagar.php?q=<?php echo $chofer_id; ?>" class="btn-pagar">
                <i class="fas fa-hand-holding-usd"></i> Registrar pago
            </a>
        <?php endif; ?>
    </div>
</body>

</html>