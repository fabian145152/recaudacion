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

$stmt = $con->prepare("
    SELECT a.*, v.marca, v.modelo, v.dominio
    FROM asignaciones_auto a
    INNER JOIN vehiculos v ON v.id = a.vehiculo_id
    WHERE a.chofer_id = ?
    ORDER BY a.fecha_desde DESC, a.id DESC
");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$asignaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Historial - <?php echo htmlspecialchars($chofer['nombre']); ?></title>
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
            background: linear-gradient(135deg, #6c757d, #495057);
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

        .timeline {
            position: relative;
            padding-left: 40px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: #eef1f7;
        }

        .timeline-item {
            position: relative;
            background: white;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -31px;
            top: 22px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #ccc;
            border: 3px solid #f4f6fa;
        }

        .timeline-item.activa::before {
            background: #28a745;
        }

        .timeline-item.cerrada::before {
            background: #6c757d;
        }

        .timeline-item .fecha {
            font-size: 0.78rem;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .timeline-item .vehiculo {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            margin: 5px 0;
        }

        .timeline-item .obs {
            font-size: 0.85rem;
            color: #666;
            background: #f8f9fc;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 8px;
            border-left: 3px solid #667eea;
        }

        .badge-activa {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .badge-cerrada {
            display: inline-block;
            background: #6c757d;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.7rem;
            font-weight: 600;
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
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <div class="header-card">
            <div class="icon-big"><i class="fas fa-history"></i></div>
            <div>
                <h1>Historial de Autos</h1>
                <p>Chofer: <strong><?php echo htmlspecialchars($chofer['nombre']); ?></strong> — Móvil #<?php echo $chofer['movil']; ?></p>
            </div>
        </div>

        <?php if (empty($asignaciones)): ?>
            <div style="text-align:center; padding:40px; color:#888; background:white; border-radius:12px;">
                <i class="fas fa-inbox" style="font-size:2rem;"></i>
                <p>Sin historial de autos.</p>
            </div>
        <?php else: ?>
            <div class="timeline">
                <?php foreach ($asignaciones as $a): ?>
                    <div class="timeline-item <?php echo $a['activa'] ? 'activa' : 'cerrada'; ?>">
                        <div class="fecha">
                            <?php if ($a['activa']): ?>
                                <span class="badge-activa">ACTUAL</span>
                                desde <?php echo date('d/m/Y', strtotime($a['fecha_desde'])); ?>
                            <?php else: ?>
                                <span class="badge-cerrada">FINALIZADO</span>
                                <?php echo date('d/m/Y', strtotime($a['fecha_desde'])); ?>
                                → <?php echo $a['fecha_hasta'] ? date('d/m/Y', strtotime($a['fecha_hasta'])) : '—'; ?>
                            <?php endif; ?>
                        </div>

                        <div class="vehiculo">
                            <i class="fas fa-taxi"></i>
                            <?php echo htmlspecialchars($a['marca'] . ' ' . $a['modelo']); ?>
                            (<?php echo htmlspecialchars($a['dominio']); ?>)
                        </div>

                        <?php if (!empty($a['observaciones'])): ?>
                            <div class="obs">
                                <i class="fas fa-comment"></i> <?php echo htmlspecialchars($a['observaciones']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <a href="taxis.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>
</body>

</html>