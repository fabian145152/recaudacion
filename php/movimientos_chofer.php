<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$chofer_id = isset($_GET['chofer_id']) ? (int)$_GET['chofer_id'] : 0;
$periodo = $_GET['periodo'] ?? 'mensual';
$fecha_base = $_GET['fecha'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_base)) {
    $fecha_base = date('Y-m-d');
}

$choferes = $con->query("SELECT id, nombre FROM choferes WHERE activo = 1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$fecha_desde = $fecha_base;
$fecha_hasta = $fecha_base;
$titulo_rango = "";

if ($periodo === 'dia') {
    $titulo_rango = "Movimientos del " . date('d/m/Y', strtotime($fecha_base));
} elseif ($periodo === 'semanal') {
    $dt = new DateTime($fecha_base);
    $dia_semana = $dt->format('N');
    $dt->modify('-' . ($dia_semana - 1) . ' days');
    $fecha_desde = $dt->format('Y-m-d');
    $dt->modify('+6 days');
    $fecha_hasta = $dt->format('Y-m-d');
    $titulo_rango = "Semana del " . date('d/m/Y', strtotime($fecha_desde)) . " al " . date('d/m/Y', strtotime($fecha_hasta));
} elseif ($periodo === 'quincenal') {
    $dt = new DateTime($fecha_base);
    $dia = (int)$dt->format('d');
    if ($dia <= 15) {
        $fecha_desde = $dt->format('Y-m-01');
        $fecha_hasta = $dt->format('Y-m-15');
        $titulo_rango = "1ra Quincena de " . date('m/Y', strtotime($fecha_base));
    } else {
        $fecha_desde = $dt->format('Y-m-16');
        $fecha_hasta = $dt->format('Y-m-t');
        $titulo_rango = "2da Quincena de " . date('m/Y', strtotime($fecha_base));
    }
} elseif ($periodo === 'mensual') {
    $dt = new DateTime($fecha_base);
    $fecha_desde = $dt->format('Y-m-01');
    $fecha_hasta = $dt->format('Y-m-t');
    $titulo_rango = "Mes de " . date('m/Y', strtotime($fecha_base));
}

$movimientos = [];
$nombre_chofer = "Seleccione un chofer...";
$total_periodo = 0;

if ($chofer_id > 0) {
    $stmt_ch = $con->prepare("SELECT nombre FROM choferes WHERE id = ?");
    $stmt_ch->bind_param("i", $chofer_id);
    $stmt_ch->execute();
    $res_ch = $stmt_ch->get_result()->fetch_assoc();
    if ($res_ch) $nombre_chofer = $res_ch['nombre'];
    $stmt_ch->close();

    $stmt = $con->prepare("SELECT * FROM recauda WHERE chofer_id = ? AND fecha BETWEEN ? AND ? ORDER BY fecha DESC");
    $stmt->bind_param("iss", $chofer_id, $fecha_desde, $fecha_hasta);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $movimientos[] = $row;
        $total_periodo += floatval($row['total']) + floatval($row['mp']) + floatval($row['ft']);
    }
    $stmt->close();
}
$con->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Movimientos por Chofer</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        .page-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        .filtros-card {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .filtros-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            align-items: end;
        }

        .form-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: #555;
            margin-bottom: 5px;
            display: block;
        }

        .form-select,
        .form-control {
            font-size: 0.9rem;
            border-radius: 8px;
            border: 1px solid #dfe6ef;
        }

        .btn-buscar {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: all 0.2s;
        }

        .btn-buscar:hover {
            color: white;
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .resultado-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .resultado-titulo {
            font-size: 1.1rem;
            color: #333;
            font-weight: 700;
            margin: 0;
        }

        .resultado-rango {
            font-size: 0.8rem;
            color: #888;
            background: #eef1f7;
            padding: 3px 10px;
            border-radius: 20px;
        }

        .tabla-movimientos {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8f9fc;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #888;
            font-weight: 700;
            border-bottom: 2px solid #eef1f7;
            padding: 10px 15px;
        }

        .table td {
            vertical-align: middle;
            font-size: 0.85rem;
            padding: 10px 15px;
            border-bottom: 1px solid #eef1f7;
            color: #444;
        }

        .total-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border-radius: 10px;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .total-card .label {
            font-size: 0.9rem;
            text-transform: uppercase;
            font-weight: 600;
            opacity: 0.9;
        }

        .total-card .monto {
            font-size: 1.6rem;
            font-weight: 700;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #888;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .empty-state i {
            font-size: 2.5rem;
            color: #ddd;
            margin-bottom: 10px;
        }

        .btn-excel {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.2s;
        }

        .btn-excel:hover {
            background: #218838;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title" style="text-align: center; margin-bottom: 20px; font-size: 1.4rem; color: #333;">
            <i class="fas fa-search-dollar"></i> Consulta de Movimientos
        </h1>

        <div class="filtros-card">
            <form method="GET" action="movimientos_chofer.php">
                <div class="filtros-grid">
                    <div>
                        <label class="form-label">Chofer</label>
                        <select name="chofer_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($choferes as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php echo ($chofer_id == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Período</label>
                        <select name="periodo" class="form-select">
                            <option value="dia" <?php echo ($periodo === 'dia') ? 'selected' : ''; ?>>Día Específico</option>
                            <option value="semanal" <?php echo ($periodo === 'semanal') ? 'selected' : ''; ?>>Semanal</option>
                            <option value="quincenal" <?php echo ($periodo === 'quincenal') ? 'selected' : ''; ?>>Quincenal</option>
                            <option value="mensual" <?php echo ($periodo === 'mensual') ? 'selected' : ''; ?>>Mensual</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Fecha Base</label>
                        <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha_base); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div>
                        <button type="submit" class="btn-buscar"><i class="fas fa-search"></i> Buscar</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($chofer_id > 0): ?>
            <div class="resultado-header">
                <h2 class="resultado-titulo"><?php echo htmlspecialchars($nombre_chofer); ?></h2>
                <div class="d-flex align-items-center gap-2">
                    <span class="resultado-rango"><i class="fas fa-calendar-alt"></i> <?php echo $titulo_rango; ?></span>
                    <?php if (!empty($movimientos)): ?>
                        <button type="button" onclick="exportarTablaExcel()" class="btn-excel">
                            <i class="fas fa-file-excel"></i> Exportar Excel
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (empty($movimientos)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No hay movimientos registrados para este chofer en el período seleccionado.</p>
                </div>
            <?php else: ?>
                <div class="tabla-movimientos" id="tabla-detalle">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Móvil / Turno</th>
                                <th class="text-end">Abono</th>
                                <th class="text-end">Voucher (Neto)</th>
                                <th class="text-end">MP</th>
                                <th class="text-end">FT</th>
                                <th class="text-end">Total del Día</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $m):
                                $total_dia = floatval($m['total']) + floatval($m['mp']) + floatval($m['ft']);
                            ?>
                                <tr>
                                    <td><strong><?php echo date('d/m/Y', strtotime($m['fecha'])); ?></strong></td>
                                    <td>
                                        <span class="badge bg-secondary">#<?php echo $m['movil']; ?></span>
                                        <span class="badge bg-<?php echo $m['turno'] === 'DIA' ? 'warning text-dark' : 'dark'; ?>"><?php echo $m['turno']; ?></span>
                                    </td>
                                    <td class="text-end text-muted">$<?php echo number_format($m['abono_diario'], 0, ',', '.'); ?></td>
                                    <td class="text-end">$<?php echo number_format($m['total'], 0, ',', '.'); ?></td>
                                    <td class="text-end">$<?php echo number_format($m['mp'], 0, ',', '.'); ?></td>
                                    <td class="text-end">$<?php echo number_format($m['ft'], 0, ',', '.'); ?></td>
                                    <td class="text-end fw-bold text-success">$<?php echo number_format($total_dia, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="total-card">
                    <span class="label"><i class="fas fa-chart-line"></i> Total Recaudado en el Período</span>
                    <span class="monto">$<?php echo number_format($total_periodo, 0, ',', '.'); ?></span>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-hand-pointer"></i>
                <p>Seleccioná un chofer y un período para ver sus movimientos.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script>
        function exportarTablaExcel() {
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.table_to_sheet(document.getElementById('tabla-detalle'));

            ws['!cols'] = [{
                wch: 15
            }, {
                wch: 15
            }, {
                wch: 12
            }, {
                wch: 15
            }, {
                wch: 12
            }, {
                wch: 12
            }, {
                wch: 15
            }];

            XLSX.utils.book_append_sheet(wb, ws, "Movimientos");

            const chofer = "<?php echo $nombre_chofer; ?>";
            const fecha = "<?php echo date('Y-m-d'); ?>";
            XLSX.writeFile(wb, `Movimientos_${chofer.replace(/ /g, '_')}_${fecha}.xlsx`);
        }
    </script>
</body>

</html>