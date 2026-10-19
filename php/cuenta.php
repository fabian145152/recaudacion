<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$sql = "
    SELECT 
        c.id, c.movil, c.turno, c.nombre, c.foto,
        COALESCE((SELECT SUM(monto) FROM cuenta_corriente WHERE chofer_id = c.id), 0) AS saldo
    FROM choferes c
    WHERE c.activo = 1
    ORDER BY saldo ASC, c.nombre ASC
";
$result = $con->query($sql);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Cuenta Corriente</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 1100px;
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
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eef1f7;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9fc;
        }

        img.foto-chof {
            width: 38px;
            height: 38px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #eef1f7;
        }

        .sin-foto {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: #eef1f7;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 0.95rem;
        }

        .saldo-deudor {
            color: #dc3545;
            font-weight: 700;
            font-size: 1rem;
        }

        .saldo-favor {
            color: #28a745;
            font-weight: 700;
            font-size: 1rem;
        }

        .saldo-cero {
            color: #888;
            font-weight: 600;
            font-size: 1rem;
        }

        .btn-accion {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 0.78rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-historial {
            background-color: #6c757d;
        }

        .btn-historial:hover {
            background-color: #5a6268;
            color: white;
        }

        .btn-pagar {
            background-color: #28a745;
        }

        .btn-pagar:hover {
            background-color: #218838;
            color: white;
        }

        .badge-turno {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.68rem;
            font-weight: 700;
            color: white;
        }

        .badge-dia {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .badge-noche {
            background: linear-gradient(135deg, #34495e, #2c3e50);
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
                <p>Saldos deudores y a favor de cada chofer</p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Chofer</th>
                    <th>Móvil / Turno</th>
                    <th>Saldo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $result->fetch_assoc()):
                    $saldo = floatval($c['saldo']);
                ?>
                    <tr>
                        <td>
                            <?php if (!empty($c['foto'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($c['foto']); ?>" class="foto-chof">
                            <?php else: ?>
                                <div class="sin-foto"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($c['nombre']); ?></strong></td>
                        <td>
                            #<?php echo htmlspecialchars($c['movil']); ?>
                            <span class="badge-turno <?php echo ($c['turno'] === 'DIA') ? 'badge-dia' : 'badge-noche'; ?>">
                                <?php echo htmlspecialchars($c['turno']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($saldo < 0): ?>
                                <span class="saldo-deudor">-$<?php echo number_format(abs($saldo), 2, ',', '.'); ?></span>
                            <?php elseif ($saldo > 0): ?>
                                <span class="saldo-favor">+$<?php echo number_format($saldo, 2, ',', '.'); ?></span>
                            <?php else: ?>
                                <span class="saldo-cero">$0,00</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($saldo < 0): ?>
                                <span style="color:#dc3545; font-weight:600;">DEBE</span>
                            <?php elseif ($saldo > 0): ?>
                                <span style="color:#28a745; font-weight:600;">A FAVOR</span>
                            <?php else: ?>
                                <span style="color:#888;">EQUILIBRADO</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="cuenta_movimientos.php?q=<?php echo $c['id']; ?>" class="btn-accion btn-historial">
                                <i class="fas fa-list"></i> Movimientos
                            </a>
                            <?php if ($saldo < 0): ?>
                                <a href="cuenta_pagar.php?q=<?php echo $c['id']; ?>" class="btn-accion btn-pagar">
                                    <i class="fas fa-hand-holding-usd"></i> Registrar pago
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>