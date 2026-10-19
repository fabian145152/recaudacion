<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de chofer inválido.");
}
$chofer_id = (int)$_GET['q'];

$stmt = $con->prepare("SELECT * FROM choferes WHERE id = ?");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) die("Chofer no encontrado.");

// Saldo actual
$stmt = $con->prepare("SELECT COALESCE(SUM(monto), 0) AS saldo FROM cuenta_corriente WHERE chofer_id = ?");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$saldo = floatval($stmt->get_result()->fetch_assoc()['saldo']);
$stmt->close();

// Procesar POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monto = floatval($_POST['monto'] ?? 0);
    $fecha = $_POST['fecha'] ?? date('Y-m-d');
    $descripcion = trim($_POST['descripcion'] ?? '');

    if ($monto <= 0) {
        die("<script>alert('El monto debe ser mayor a 0.'); window.history.back();</script>");
    }
    if ($monto > abs($saldo)) {
        die("<script>alert('El monto no puede superar la deuda actual ($" . number_format(abs($saldo), 2, ',', '.') . ").'); window.history.back();</script>");
    }
    if (empty($descripcion)) $descripcion = "Pago de deuda";

    $stmt = $con->prepare("
        INSERT INTO cuenta_corriente (chofer_id, fecha, monto, tipo, descripcion, recauda_id)
        VALUES (?, ?, ?, 'PAGO', ?, NULL)
    ");
    $stmt->bind_param("isds", $chofer_id, $fecha, $monto, $descripcion);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Pago registrado.'); window.location='cuenta.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
    exit;
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Registrar Pago</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 600px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, #28a745, #20c997);
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

        .card-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            padding: 25px;
            margin-bottom: 20px;
        }

        .deuda-actual {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #721c24;
            text-align: center;
        }

        .deuda-actual .monto {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .deuda-actual .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .form-group label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
        }

        .form-control {
            padding: 10px 12px;
            border-radius: 8px;
            border: 1px solid #dfe6ef;
        }

        .acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-guardar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancelar {
            background: #eef1f7;
            color: #555;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }

        .btn-cancelar:hover {
            background: #dde3ec;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <div class="header-card">
            <div class="icon-big"><i class="fas fa-hand-holding-usd"></i></div>
            <div>
                <h1>Registrar Pago</h1>
                <p><?php echo htmlspecialchars($chofer['nombre']); ?> — Móvil #<?php echo $chofer['movil']; ?></p>
            </div>
        </div>

        <form method="POST">
            <div class="card-section">

                <div class="deuda-actual">
                    <span class="label">Deuda actual</span>
                    <span class="monto">-$<?php echo number_format(abs($saldo), 2, ',', '.'); ?></span>
                </div>

                <div class="form-group">
                    <label>Monto a pagar <span style="color:#dc3545;">*</span></label>
                    <input type="number" name="monto" class="form-control" step="0.01" min="0.01" max="<?php echo abs($saldo); ?>" required>
                    <small class="text-muted">Puede ser un pago parcial.</small>
                </div>

                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Ej: Pago en efectivo, transferencia...">
                </div>

            </div>

            <div class="acciones">
                <a href="cuenta.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn-guardar"><i class="fas fa-check"></i> Registrar pago</button>
            </div>
        </form>
    </div>
</body>

</html>