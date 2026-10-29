<?php
require_once __DIR__ . '/funciones/auth.php';
requireLogin();



include_once "funciones/funciones.php";
$con = conexion();

$con->set_charset("utf8mb4");

// === FILTRO DE FECHA Y PERÍODO ===
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');
$periodo = $_GET['periodo'] ?? 'dia';
$chofer_filtro = isset($_GET['chofer_id']) ? (int)$_GET['chofer_id'] : 0; // 0 = Todos

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_filtro)) {
    $fecha_filtro = date('Y-m-d');
}

$fecha_desde = $fecha_filtro;
$fecha_hasta = $fecha_filtro;
$titulo_periodo = "Recaudación del Día";
$label_periodo = "DÍA";

if ($periodo === 'semanal') {
    $dt = new DateTime($fecha_filtro);
    $dia_semana = $dt->format('N');
    $dt->modify('-' . ($dia_semana - 1) . ' days');
    $fecha_desde = $dt->format('Y-m-d');
    $dt->modify('+6 days');
    $fecha_hasta = $dt->format('Y-m-d');
    $titulo_periodo = "Resumen Semanal";
    $label_periodo = "SEMANA";
} elseif ($periodo === 'quincenal') {
    $dt = new DateTime($fecha_filtro);
    $dia = (int)$dt->format('d');
    if ($dia <= 15) {
        $fecha_desde = $dt->format('Y-m-01');
        $fecha_hasta = $dt->format('Y-m-15');
        $titulo_periodo = "Resumen 1ra Quincena";
    } else {
        $fecha_desde = $dt->format('Y-m-16');
        $fecha_hasta = $dt->format('Y-m-t');
        $titulo_periodo = "Resumen 2da Quincena";
    }
    $label_periodo = "QUINCENA";
} elseif ($periodo === 'mensual') {
    $dt = new DateTime($fecha_filtro);
    $fecha_desde = $dt->format('Y-m-01');
    $fecha_hasta = $dt->format('Y-m-t');
    $titulo_periodo = "Resumen Mensual";
    $label_periodo = "MES";
}

// === ESTADÍSTICAS GENERALES ===
$totalChoferes     = $con->query("SELECT COUNT(*) AS t FROM choferes WHERE activo = 1")->fetch_assoc()['t'];
$totalVehiculos    = $con->query("SELECT COUNT(*) AS t FROM vehiculos WHERE activo = 1")->fetch_assoc()['t'];
$totalAsignaciones = $con->query("SELECT COUNT(DISTINCT vehiculo_id) AS t FROM asignaciones_auto WHERE activa = 1")->fetch_assoc()['t'];
$turnosDia         = $con->query("SELECT COUNT(*) AS t FROM asignaciones_auto WHERE activa = 1 AND turno = 'DIA'")->fetch_assoc()['t'];
$turnosNoche       = $con->query("SELECT COUNT(*) AS t FROM asignaciones_auto WHERE activa = 1 AND turno = 'NOCHE'")->fetch_assoc()['t'];
$lista_choferes    = $con->query("SELECT id, nombre FROM choferes WHERE activo = 1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

// === RECAUDACIÓN DEL PERÍODO ===
$where_chofer = "";
$params = [$fecha_desde, $fecha_hasta];
$tipos  = "ss";

if ($chofer_filtro > 0) {
    $where_chofer = " AND chofer_id = ?";
    $params[] = $chofer_filtro;
    $tipos .= "i";
}

$stmt = $con->prepare("
    SELECT 
        COALESCE(SUM(voucher), 0)              AS voucher_bruto,
        COALESCE(SUM(comision), 0)             AS comision,
        COALESCE(SUM(total), 0)                AS voucher_neto,
        COALESCE(SUM(mp), 0)                   AS mp,
        COALESCE(SUM(ft), 0)                   AS ft,
        COALESCE(SUM(total + mp + ft), 0)      AS total_recaudado,
        COALESCE(SUM(abono_diario), 0)         AS abono_esperado,
        COUNT(*)                               AS cant
    FROM recauda 
    WHERE fecha BETWEEN ? AND ? $where_chofer
");
$stmt->bind_param($tipos, ...$params);
$stmt->execute();
$dia = $stmt->get_result()->fetch_assoc();
$stmt->close();

// === SALDOS GLOBALES ===
$deudaTotal = $con->query("SELECT COALESCE(SUM(saldo), 0) AS total FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo < 0) AS sub")->fetch_assoc()['total'];
$aFavorTotal = $con->query("SELECT COALESCE(SUM(saldo), 0) AS total FROM (SELECT chofer_id, SUM(monto) AS saldo FROM cuenta_corriente GROUP BY chofer_id HAVING saldo > 0) AS sub")->fetch_assoc()['total'];
$choferesConDeuda = $con->query("SELECT COUNT(*) AS cant FROM (SELECT chofer_id FROM cuenta_corriente GROUP BY chofer_id HAVING SUM(monto) < 0) AS sub")->fetch_assoc()['cant'];

$con->close();

$fecha_dt = new DateTime($fecha_filtro);
$fecha_mostrar = $fecha_dt->format('d/m/Y');
$es_hoy = ($fecha_filtro === date('Y-m-d'));
$es_ayer = ($fecha_filtro === date('Y-m-d', strtotime('-1 day')));
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Taxis</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }

        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            color: white;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 1.7rem;
            font-weight: 300;
            margin: 0 0 2px 0;
        }

        .header p {
            opacity: 0.9;
            font-size: 0.9rem;
            margin: 0;
        }

        .section-title {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            font-weight: 600;
            margin: 10px 0 5px 0;
            display: flex;
            align-items: center;
            gap: 6px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .fecha-filtro {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            color: white;
            margin-bottom: 6px;
            font-size: 0.82rem;
        }

        .fecha-filtro label {
            font-weight: 600;
            font-size: 0.82rem;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .fecha-filtro input[type="date"] {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.9);
            font-size: 0.82rem;
            font-weight: 600;
            color: #333;
            cursor: pointer;
        }

        .form-select-filtro {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            background: rgba(255, 255, 255, 0.9);
            font-size: 0.82rem;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            outline: none;
            max-width: 180px;
        }

        .fecha-filtro .btn-hoy,
        .fecha-filtro .btn-ayer {
            padding: 4px 10px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.78rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-hoy {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-hoy:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-ayer {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }

        .btn-ayer:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-periodo {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-decoration: none;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-periodo:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            text-decoration: none;
        }

        .btn-periodo.active {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
            border-color: #17a2b8;
            box-shadow: 0 0 10px rgba(23, 162, 184, 0.4);
        }

        .badge-fecha {
            background: rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 6px;
            margin-bottom: 4px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            padding: 6px 8px;
            color: white;
            text-align: center;
            transition: transform 0.2s;
            line-height: 1.1;
            min-height: auto;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card .icon-small {
            font-size: 0.95rem;
            opacity: 0.85;
            margin-bottom: 1px;
            display: block;
        }

        .stat-card .number {
            font-size: 1.15rem;
            font-weight: bold;
            display: block;
        }

        .stat-card .label {
            font-size: 0.68rem;
            opacity: 0.9;
            margin-top: 1px;
            line-height: 1.15;
        }

        .stat-voucher {
            background: rgba(255, 193, 7, 0.25);
            border-color: rgba(255, 193, 7, 0.5);
        }

        .stat-mp {
            background: rgba(23, 162, 184, 0.25);
            border-color: rgba(23, 162, 184, 0.5);
        }

        .stat-ft {
            background: rgba(40, 167, 69, 0.25);
            border-color: rgba(40, 167, 69, 0.5);
        }

        .stat-paga {
            background: rgba(108, 117, 125, 0.25);
            border-color: rgba(108, 117, 125, 0.5);
        }

        .stat-total {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .stat-deuda {
            background: rgba(220, 53, 69, 0.3);
            border-color: rgba(220, 53, 69, 0.6);
        }

        .stat-favor {
            background: rgba(40, 167, 69, 0.3);
            border-color: rgba(40, 167, 69, 0.6);
        }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .menu-card {
            background: white;
            border-radius: 12px;
            padding: 18px 15px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.25);
            text-decoration: none;
            color: #333;
        }

        .menu-card .icon {
            font-size: 2rem;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: white;
        }

        .menu-card h3 {
            font-size: 1.05rem;
            margin: 0;
            font-weight: 600;
        }

        .menu-card p {
            font-size: 0.82rem;
            color: #666;
            margin: 0;
        }

        .icon-taxis {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .icon-chofer {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .icon-vehiculo {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
        }

        .icon-recauda {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .icon-planilla {
            background: linear-gradient(135deg, #6f42c1, #6610f2);
        }

        .icon-cuenta {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .icon-prestamos {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
        }

        .icon-movimientos {
            background: linear-gradient(135deg, #6f42c1, #6610f2);
        }

        .footer {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            margin-top: 20px;
            font-size: 0.8rem;
        }

        /* =========================================
           MODAL SELECCIÓN TIPO VEHÍCULO
           ========================================= */
        .modal-tipo-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.65);
            backdrop-filter: blur(5px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .modal-tipo-overlay.active {
            display: flex;
        }

        .modal-tipo-box {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 850px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: modalTipoIn 0.3s ease;
        }

        @keyframes modalTipoIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .modal-tipo-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-tipo-header h3 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-tipo-close {
            background: transparent;
            border: none;
            color: white;
            font-size: 2rem;
            cursor: pointer;
            line-height: 1;
            opacity: 0.8;
            transition: opacity 0.2s;
        }

        .modal-tipo-close:hover {
            opacity: 1;
        }

        .modal-tipo-body {
            padding: 30px;
        }

        .tipo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .tipo-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 35px 20px;
            border-radius: 14px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            border: 3px solid transparent;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .tipo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            color: white;
        }

        .tipo-card i {
            font-size: 3.5rem;
            margin-bottom: 15px;
        }

        .tipo-card h4 {
            margin: 0 0 8px 0;
            font-size: 1.25rem;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .tipo-card p {
            margin: 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .tipo-taxi {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .tipo-remis {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
        }

        .tipo-alquiler {
            background: linear-gradient(135deg, #6f42c1, #6610f2);
        }

        .tipo-base {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.3rem;
            }

            .stat-card .number {
                font-size: 1rem;
            }

            .stat-card .label {
                font-size: 0.62rem;
            }

            .section-title {
                font-size: 0.8rem;
                margin: 8px 0 4px 0;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <div class="header" style="position: relative;">
            <div style="position: absolute; top: 0; right: 0; display: flex; align-items: center; gap: 12px; color: white; font-size: 0.9rem;">
                <span>
                    <i class="fas fa-user-circle"></i>
                    <?= htmlspecialchars($_SESSION['nombre']) ?>
                </span>
                <a href="cerrar_sesion.php"
                    style="background: rgba(255,255,255,0.25); color: white; padding: 4px 12px; border-radius: 20px; text-decoration: none; font-size: 0.82rem; font-weight: 600;">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </div>
            <h1><i class="fas fa-taxi"></i> Sistema de Gestión de Taxis</h1>
            <p>Panel de control principal</p>
        </div>

        <div class="section-title"><i class="fas fa-chart-pie"></i> Resumen General</div>
        <div class="stats-grid">
            <div class="stat-card"><span class="icon-small"><i class="fas fa-users"></i></span><span class="number"><?php echo $totalChoferes; ?></span>
                <div class="label">Choferes Activos</div>
            </div>
            <div class="stat-card"><span class="icon-small"><i class="fas fa-car"></i></span><span class="number"><?php echo $totalVehiculos; ?></span>
                <div class="label">Vehículos en Flota</div>
            </div>
            <div class="stat-card"><span class="icon-small"><i class="fas fa-handshake"></i></span><span class="number"><?php echo $totalAsignaciones; ?></span>
                <div class="label">Autos Asignados</div>
            </div>
            <div class="stat-card stat-deuda"><span class="icon-small"><i class="fas fa-user-clock"></i></span><span class="number"><?php echo $choferesConDeuda; ?></span>
                <div class="label">Choferes con Deuda</div>
            </div>
        </div>

        <div class="section-title"><i class="fas fa-clock"></i> Cobertura de Turnos</div>
        <div class="stats-grid">
            <div class="stat-card" style="background: rgba(255,193,7,0.25); border-color: rgba(255,193,7,0.5);"><span class="icon-small"><i class="fas fa-sun"></i></span><span class="number"><?php echo $turnosDia; ?></span>
                <div class="label">Turnos DÍA cubiertos</div>
            </div>
            <div class="stat-card" style="background: rgba(52,73,94,0.35); border-color: rgba(52,73,94,0.6);"><span class="icon-small"><i class="fas fa-moon"></i></span><span class="number"><?php echo $turnosNoche; ?></span>
                <div class="label">Turnos NOCHE cubiertos</div>
            </div>
        </div>

        <div class="section-title">
            <i class="fas fa-calendar-day"></i>
            <?php echo $titulo_periodo; ?>
            <?php if ($periodo !== 'dia'): ?>
                <span class="badge-fecha"><?php echo date('d/m/Y', strtotime($fecha_desde)); ?> al <?php echo date('d/m/Y', strtotime($fecha_hasta)); ?></span>
            <?php else: ?>
                <span class="badge-fecha"><?php echo $es_hoy ? 'Hoy · ' : ($es_ayer ? 'Ayer · ' : '') . $fecha_mostrar; ?></span>
            <?php endif; ?>

            <?php if ((int)$dia['cant'] > 0): ?>
                <span class="badge-fecha" style="background: rgba(40,167,69,0.6);">
                    ✓ <?php echo $dia['cant']; ?> registro<?php echo $dia['cant'] == 1 ? '' : 's'; ?>
                </span>
            <?php else: ?>
                <span class="badge-fecha" style="background: rgba(220,53,69,0.6);">
                    ⚠ 0 registros en este período
                </span>
            <?php endif; ?>
        </div>

        <?php if ((int)$dia['cant'] === 0 && $periodo === 'dia'): ?>
            <div style="background: rgba(255,243,205,0.95); border-left: 4px solid #856404; padding: 8px 14px; border-radius: 6px; color: #856404; font-size: 0.85rem; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-info-circle"></i>
                <span>
                    No hay registros cargados <strong>hoy</strong>.
                    ¿Querés ver <a href="reca.php?periodo=semanal&fecha=<?php echo $fecha_filtro; ?>&chofer_id=<?php echo $chofer_filtro; ?>" style="color:#856404; font-weight:700;">esta semana</a> o
                    <a href="reca.php?periodo=mensual&fecha=<?php echo $fecha_filtro; ?>&chofer_id=<?php echo $chofer_filtro; ?>" style="color:#856404; font-weight:700;">este mes</a>?
                </span>
            </div>
        <?php endif; ?>

        <div class="fecha-filtro">
            <form method="GET" action="reca.php" style="display: flex; align-items: center; gap: 10px; margin: 0; flex-wrap: wrap;">
                <input type="hidden" name="periodo" value="<?php echo htmlspecialchars($periodo); ?>">

                <label for="fecha"><i class="fas fa-calendar"></i> Fecha base:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>" max="<?php echo date('Y-m-d'); ?>" onchange="this.form.submit()">

                <select name="chofer_id" class="form-select-filtro" onchange="this.form.submit()" title="Filtrar por chofer">
                    <option value="0">👥 Todos los choferes</option>
                    <?php foreach ($lista_choferes as $ch): ?>
                        <option value="<?php echo $ch['id']; ?>" <?php echo ($chofer_filtro == $ch['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ch['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <div style="display: flex; gap: 5px; margin-left: 10px; border-left: 1px solid rgba(255,255,255,0.3); padding-left: 10px;">
                    <a href="reca.php?fecha=<?php echo $fecha_filtro; ?>&periodo=semanal&chofer_id=<?php echo $chofer_filtro; ?>" class="btn-periodo <?php echo $periodo === 'semanal' ? 'active' : ''; ?>"><i class="fas fa-calendar-week"></i> Semanal</a>
                    <a href="reca.php?fecha=<?php echo $fecha_filtro; ?>&periodo=quincenal&chofer_id=<?php echo $chofer_filtro; ?>" class="btn-periodo <?php echo $periodo === 'quincenal' ? 'active' : ''; ?>"><i class="fas fa-calendar-alt"></i> Quincenal</a>
                    <a href="reca.php?fecha=<?php echo $fecha_filtro; ?>&periodo=mensual&chofer_id=<?php echo $chofer_filtro; ?>" class="btn-periodo <?php echo $periodo === 'mensual' ? 'active' : ''; ?>"><i class="fas fa-calendar"></i> Mensual</a>
                    <button type="button" onclick="exportarResumen()" class="btn-periodo" style="background: rgba(40, 167, 69, 0.8); border-color: #28a745; color: white;" title="Exportar a Excel"><i class="fas fa-file-excel"></i> Excel</button>
                </div>
            </form>

            <?php if (!$es_hoy && $periodo === 'dia'): ?>
                <a href="reca.php?chofer_id=<?php echo $chofer_filtro; ?>" class="btn-hoy"><i class="fas fa-calendar-day"></i> Hoy</a>
            <?php endif; ?>
            <?php if (!$es_ayer && $periodo === 'dia'): ?>
                <a href="reca.php?fecha=<?php echo date('Y-m-d', strtotime('-1 day')); ?>&chofer_id=<?php echo $chofer_filtro; ?>" class="btn-ayer"><i class="fas fa-calendar-minus"></i> Ayer</a>
            <?php endif; ?>
        </div>

        <div class="stats-grid" id="tabla-exportar">
            <div class="stat-card stat-voucher">
                <span class="icon-small"><i class="fas fa-ticket-alt"></i></span>
                <span class="number">$<?php echo number_format($dia['voucher_neto'], 0, ',', '.'); ?></span>
                <div class="label">DEPÓSITO EN VOUCHER (<?php echo $label_periodo; ?>)</div>
                <input type="hidden" id="excel-voucher-bruto" value="<?php echo $dia['voucher_bruto']; ?>">
                <input type="hidden" id="excel-comision" value="<?php echo $dia['comision']; ?>">
            </div>
            <div class="stat-card stat-mp">
                <span class="icon-small"><i class="fas fa-mobile-alt"></i></span>
                <span class="number">$<?php echo number_format($dia['mp'], 0, ',', '.'); ?></span>
                <div class="label">DEPÓSITO EN MP (<?php echo $label_periodo; ?>)</div>
            </div>
            <div class="stat-card stat-ft">
                <span class="icon-small"><i class="fas fa-file-invoice"></i></span>
                <span class="number">$<?php echo number_format($dia['ft'], 0, ',', '.'); ?></span>
                <div class="label">DEPÓSITO EN EFECTIVO (FT)</div>
            </div>
            <div class="stat-card stat-paga">
                <span class="icon-small"><i class="fas fa-hand-holding-usd"></i></span>
                <span class="number">$<?php echo number_format($dia['abono_esperado'], 0, ',', '.'); ?></span>
                <div class="label">ABONO ESPERADO (<?php echo $label_periodo; ?>)</div>
            </div>
            <div class="stat-card stat-total">
                <span class="icon-small"><i class="fas fa-money-bill-wave"></i></span>
                <span class="number">$<?php echo number_format($dia['total_recaudado'], 0, ',', '.'); ?></span>
                <div class="label">RECAUDACIÓN TOTAL (<?php echo $label_periodo; ?>)</div>
            </div>
        </div>

        <div class="section-title"><i class="fas fa-wallet"></i> Saldos Acumulados (globales)</div>
        <div class="stats-grid">
            <div class="stat-card stat-deuda"><span class="icon-small"><i class="fas fa-exclamation-triangle"></i></span><span class="number">$<?php echo number_format(abs($deudaTotal), 0, ',', '.'); ?></span>
                <div class="label">DEUDA TOTAL</div>
            </div>
            <div class="stat-card stat-favor"><span class="icon-small"><i class="fas fa-check-circle"></i></span><span class="number">$<?php echo number_format($aFavorTotal, 0, ',', '.'); ?></span>
                <div class="label">A FAVOR TOTAL</div>
            </div>
        </div>

        <div class="section-title"><i class="fas fa-th-large"></i> Acciones</div>
        <div class="menu-grid">

            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/usuarios.php', event)" class="menu-card">
                <div class="icon" style="background: linear-gradient(135deg, #6c757d, #495057);"><i class="fas fa-user-shield"></i></div>
                <h3>Usuarios del Sistema</h3>
                <p>Crear, editar y eliminar usuarios</p>
            </a>

            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/chofer_nuevo.php', event)" class="menu-card">
                <div class="icon icon-chofer"><i class="fas fa-user-plus"></i></div>
                <h3>Nuevo Chofer</h3>
                <p>Registrar un chofer y asignarle móvil + auto</p>
            </a>
            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/vehiculo_lista.php', event)" class="menu-card">
                <div class="icon icon-vehiculo"><i class="fas fa-car-side"></i></div>
                <h3>Vehículos</h3>
                <p>Agregar y ver autos de la flota</p>
            </a>
            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/taxis.php', event)" class="menu-card">
                <div class="icon icon-taxis"><i class="fas fa-users"></i></div>
                <h3>Choferes y Asignaciones</h3>
                <p>Ver choferes, sus móviles y cambiar autos</p>
            </a>

            <!-- MODIFICADO: Abre el modal de selección de tipo -->
            <a href="javascript:void(0)" onclick="abrirModalTipo(event)" class="menu-card">
                <div class="icon icon-recauda"><i class="fas fa-dollar-sign"></i></div>
                <h3>Cargar Recaudación</h3>
                <p>Cargar los importes del día de un chofer</p>
            </a>

            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/prestamos.php', event)" class="menu-card">
                <div class="icon icon-prestamos"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Préstamos y Deudas</h3>
                <p>Gestionar préstamos y deudas de los choferes</p>
            </a>
            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/movimientos_chofer.php', event)" class="menu-card">
                <div class="icon icon-movimientos"><i class="fas fa-search-dollar"></i></div>
                <h3>Consulta Movimientos</h3>
                <p>Ver recaudación detallada por período</p>
            </a>
            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/reporte_movil.php', event)" class="menu-card">
                <div class="icon" style="background: linear-gradient(135deg, #ffc107, #fd7e14);">
                    <i class="fas fa-table"></i>
                </div>
                <h3>Planilla de Caja</h3>
                <p>Reporte mensual por móvil para Excel</p>
            </a>

            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/recibo_semanal.php', event)" class="menu-card">
                <div class="icon" style="background: linear-gradient(135deg, #17a2b8, #0dcaf0);"><i class="fas fa-file-invoice"></i></div>
                <h3>Recibo Semanal</h3>
                <p>Imprimir liquidación semanal del chofer</p>
            </a>

            <a href="javascript:void(0)" onclick="abrirEnNuevaPestana('php/caja_movimientos.php', event)" class="menu-card">
                <div class="icon" style="background: linear-gradient(135deg, #667eea, #764ba2);"><i class="fas fa-cash-register"></i></div>
                <h3>Movimientos de Caja</h3>
                <p>Ingresos y extracciones de FT y MP</p>
            </a>

            <a href="backup.php" target="_blank" class="menu-card" style="background: #fff3cd; border: 1px solid #ffeeba;">
                <div class="icon" style="background: linear-gradient(135deg, #28a745, #20c997);"><i class="fas fa-download"></i></div>
                <h3>Backup de Base de Datos</h3>
                <p>Descargar copia de seguridad en .SQL</p>
            </a>
        </div>

        <div class="footer">Sistema de Gestión de Taxis &copy; <?php echo date('Y'); ?></div>
    </div>

    <!-- MODAL SELECCION TIPO VEHICULO -->
    <div class="modal-tipo-overlay" id="modalTipoVehiculo">
        <div class="modal-tipo-box">
            <div class="modal-tipo-header">
                <h3><i class="fas fa-car"></i> ¿Qué tipo de vehículo vas a gestionar?</h3>
                <button class="modal-tipo-close" onclick="cerrarModalTipo()">&times;</button>
            </div>
            <div class="modal-tipo-body">
                <div class="tipo-grid">
                    <a href="javascript:void(0)" onclick="abrirTipo('TAXI')" class="tipo-card tipo-taxi">
                        <i class="fas fa-taxi"></i>
                        <h4>TAXI</h4>
                        <p>Cargar recaudación de taxis</p>
                    </a>
                    <a href="" onclick="abrirTipo('REMIS')" class="tipo-card tipo-remis">
                        <i class="fas fa-car"></i>
                        <h4>REMIS</h4>
                        <p>Cargar recaudación de remises</p>
                    </a>
                    <a href="javascript:void(0)" onclick="abrirTipo('ALQUILER')" class="tipo-card tipo-alquiler">
                        <i class="fas fa-car-side"></i>
                        <h4>AUTO DE ALQUILER</h4>
                        <p>Cargar recaudación de autos de alquiler</p>
                    </a>
                    <!-- NUEVO: BASE -->
                    <a href="" onclick="abrirTipo('BASE')" class="tipo-card tipo-base">
                        <i class="fas fa-warehouse"></i>
                        <h4>AUTOS DE BASE</h4>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.sheetjs.com/xlsx-0.20.0/package/dist/xlsx.full.min.js"></script>
    <script>
        function exportarResumen() {
            const wb = XLSX.utils.book_new();

            const selectChofer = document.querySelector('select[name="chofer_id"]');
            const choferTexto = selectChofer.options[selectChofer.selectedIndex].text;
            const tituloPagina = document.querySelectorAll('.section-title')[2].innerText.replace(/\s+/g, ' ').trim();

            const vBruto = parseFloat(document.getElementById('excel-voucher-bruto').value) || 0;
            const comision = parseFloat(document.getElementById('excel-comision').value) || 0;
            const vNeto = vBruto - comision;

            const mp = parseFloat(document.querySelector('.stat-mp .number').innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0;
            const ft = parseFloat(document.querySelector('.stat-ft .number').innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0;
            const abono = parseFloat(document.querySelector('.stat-paga .number').innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0;
            const total = parseFloat(document.querySelector('.stat-total .number').innerText.replace('$', '').replace(/\./g, '').replace(',', '.')) || 0;
            const saldo = total - abono;

            const datos = [
                ["CIERRE DE CAJA - RECAUDACIÓN"],
                ["Filtro:", choferTexto],
                ["Período:", tituloPagina],
                ["Fecha de exportación:", new Date().toLocaleString()],
                [],
                ["CONCEPTO", "MONTO ($)"],
                ["VOUCHER BRUTO", vBruto],
                ["(-) COMISIÓN", comision],
                ["VOUCHER NETO", vNeto],
                ["MERCADO PAGO (MP)", mp],
                ["EFECTIVO (FT)", ft],
                ["ABONO ESPERADO", abono],
                ["TOTAL RECAUDADO", total],
                [],
                ["RESULTADO DEL PERÍODO", saldo],
                [saldo >= 0 ? "A FAVOR DEL CHOFER" : "DEUDA DEL CHOFER", Math.abs(saldo)]
            ];

            const ws = XLSX.utils.aoa_to_sheet(datos);
            ws['!cols'] = [{
                wch: 35
            }, {
                wch: 15
            }];
            XLSX.utils.book_append_sheet(wb, ws, "Resumen");

            const nombreArchivo = `Resumen_${choferTexto.replace(/ /g, '_')}.xlsx`;
            XLSX.writeFile(wb, nombreArchivo);
        }

        function abrirEnNuevaPestana(url, event) {
            event.preventDefault();
            const separador = url.includes('?') ? '&' : '?';
            window.open(url + separador + 'nueva=1', '_blank');
        }

        // === LÓGICA DEL MODAL TIPO VEHÍCULO ===
        function abrirModalTipo(event) {
            if (event) event.preventDefault();
            document.getElementById('modalTipoVehiculo').classList.add('active');
        }

        function cerrarModalTipo() {
            document.getElementById('modalTipoVehiculo').classList.remove('active');
        }

        function abrirTipo(tipo) {
            window.open('php/cargar_selector.php?tipo=' + tipo + '&nueva=1', '_blank');
        }

        document.getElementById('modalTipoVehiculo').addEventListener('click', function(e) {
            if (e.target === this) cerrarModalTipo();
        });
    </script>
</body>

</html>