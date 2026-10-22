<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// === ¿FUE ABIERTO EN NUEVA PESTAÑA? ===
$es_nueva_pestana = isset($_GET['nueva']) && $_GET['nueva'] == '1';
$param_nueva = $es_nueva_pestana ? '&nueva=1' : '';

// === FILTRO DE FECHA ===
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_filtro)) {
    $fecha_filtro = date('Y-m-d');
}

$fecha_dt = new DateTime($fecha_filtro);
$fecha_mostrar = $fecha_dt->format('d/m/Y');
$es_hoy  = ($fecha_filtro === date('Y-m-d'));
$es_ayer = ($fecha_filtro === date('Y-m-d', strtotime('-1 day')));

if ($es_hoy) {
    $titulo_recauda = "Movimiento de hoy ($fecha_mostrar)";
    $aviso_sin = "Sin recaudación cargada hoy";
} elseif ($es_ayer) {
    $titulo_recauda = "Movimiento de ayer ($fecha_mostrar)";
    $aviso_sin = "Sin recaudación cargada ayer";
} else {
    $titulo_recauda = "Movimiento del $fecha_mostrar";
    $aviso_sin = "Sin recaudación cargada el $fecha_mostrar";
}

// === CHOFERES ===
$sql = "
    SELECT 
        c.id,
        c.nombre,
        c.cel,
        c.foto,
        a.turno,
        v.movil,
        v.marca,
        v.modelo,
        v.dominio,
        COALESCE((
            SELECT SUM(monto) FROM cuenta_corriente 
            WHERE chofer_id = c.id AND fecha <= ? 
            AND descripcion NOT LIKE '%Pago cuota%'
        ), 0) AS saldo_acumulado,
        (
            SELECT MIN(fecha) FROM cuenta_corriente 
            WHERE chofer_id = c.id AND monto < 0 AND fecha <= ?
        ) AS primera_deuda_fecha,
        r.abono_diario  AS r_abono_diario,
        r.total         AS r_total,
        r.mp            AS r_mp,
        r.ft            AS r_ft,
        r.debe          AS r_debe,
        r.id            AS r_id
    FROM choferes c
    LEFT JOIN asignaciones_auto a ON a.chofer_id = c.id AND a.activa = 1
    LEFT JOIN vehiculos v ON v.id = a.vehiculo_id
    LEFT JOIN recauda r ON r.chofer_id = c.id AND r.fecha = ?
    WHERE c.activo = 1
    ORDER BY v.movil ASC, a.turno ASC, c.nombre ASC
";

$stmt = $con->prepare($sql);
$stmt->bind_param("sss", $fecha_filtro, $fecha_filtro, $fecha_filtro);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("❌ Error en la consulta: " . $con->error);
}

$choferes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// === OBSERVACIONES PERSISTENTES ===
$obs_por_chofer = [];
$stmt_obs = $con->prepare("
    SELECT id, chofer_id, fecha_creacion, observacion
    FROM observaciones
    WHERE fecha_creacion <= ?
    ORDER BY fecha_creacion ASC, id ASC
");
$stmt_obs->bind_param("s", $fecha_filtro);
$stmt_obs->execute();
$res_obs = $stmt_obs->get_result();
while ($o = $res_obs->fetch_assoc()) {
    $obs_por_chofer[(int)$o['chofer_id']][] = $o;
}
$stmt_obs->close();

// === OBTENER PRÉSTAMOS ACTIVOS (Con subconsulta para el total pagado) ===
$prestamos_por_chofer = [];
$res_prestamos = $con->query("
    SELECT pr.id, pr.chofer_id, pr.valor_cuota, pr.monto_total, pr.cuotas, pr.cuotas_pagas, pr.estado, pr.estado_mora,
           COALESCE((SELECT SUM(monto) FROM prestamos_pagos WHERE prestamo_id = pr.id), 0) AS total_pagado
    FROM prestamos pr
    WHERE pr.estado = 'ACTIVO'
");
if ($res_prestamos) {
    while ($pr = $res_prestamos->fetch_assoc()) {
        $prestamos_por_chofer[(int)$pr['chofer_id']][] = $pr;
    }
}

$con->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cargar Recaudación</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        .page-title {
            text-align: center;
            margin: 6px 0 3px;
            font-size: 1.15rem;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .subtitle {
            text-align: center;
            color: #888;
            font-size: 0.78rem;
            margin-bottom: 10px;
        }

        .fecha-filtro-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            padding: 8px 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 12px;
            font-size: 0.82rem;
        }

        .fecha-filtro-container label {
            font-weight: 600;
            font-size: 0.82rem;
            color: #555;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .fecha-filtro-container input[type="date"] {
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #dfe6ef;
            font-size: 0.82rem;
            font-weight: 600;
            color: #333;
            cursor: pointer;
            background: white;
        }

        .fecha-filtro-container input[type="date"]:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.15);
        }

        .btn-fecha {
            padding: 4px 10px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
            color: white;
        }

        .btn-hoy {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .btn-ayer {
            background: linear-gradient(135deg, #6c757d, #495057);
        }

        .btn-fecha:hover {
            color: white;
            opacity: 0.9;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .badge-fecha-actual {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .choferes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 10px;
        }

        .chofer-card {
            background: white;
            border: 2px solid #dfe6ef;
            border-radius: 10px;
            padding: 10px 12px;
            color: #333;
            display: block;
            transition: all 0.2s;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .chofer-card:hover {
            border-color: #28a745;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(40, 167, 69, 0.18);
        }

        .chofer-card .link-ficha {
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .card-header-chofer {
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eef1f7;
            margin-bottom: 8px;
        }

        .card-header-chofer img,
        .card-header-chofer .sin-foto {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #eef1f7;
            flex-shrink: 0;
        }

        .card-header-chofer .sin-foto {
            background: #eef1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 1rem;
        }

        .chofer-datos {
            flex: 1;
            min-width: 0;
        }

        .chofer-nombre {
            font-weight: 700;
            color: #333;
            font-size: 0.9rem;
            margin-bottom: 3px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .chofer-meta {
            display: flex;
            align-items: center;
            gap: 5px;
            flex-wrap: wrap;
        }

        .movil-badge {
            display: inline-block;
            background: #eef1f7;
            color: #667eea;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 0.68rem;
            font-weight: 700;
        }

        .badge-turno {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 0.62rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
        }

        .badge-dia {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .badge-noche {
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }

        .sin-auto-badge,
        .cargado-hoy {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 0.62rem;
            font-weight: 700;
        }

        .sin-auto-badge {
            background: #fff3cd;
            color: #856404;
        }

        .cargado-hoy {
            background: #cce5ff;
            color: #004085;
        }

        .prestamo-badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 10px;
            font-size: 0.62rem;
            font-weight: 700;
            color: white;
            text-transform: uppercase;
        }

        .prestamo-badge.al-dia {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
        }

        .prestamo-badge.mora {
            background: linear-gradient(135deg, #dc3545, #c82333);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        .alerta-prestamo {
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
            border: 1px dashed;
        }

        .alerta-prestamo.al-dia {
            background: #fff3cd;
            color: #856404;
            border-color: #ffc107;
        }

        .alerta-prestamo.mora {
            background: #f8d7da;
            color: #721c24;
            border-color: #dc3545;
        }

        .recauda-hoy {
            background: #f8f9fc;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 6px;
        }

        .recauda-titulo {
            font-size: 0.65rem;
            color: #888;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .recauda-fila {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.78rem;
            padding: 2px 0;
            color: #555;
        }

        .recauda-fila .etiqueta {
            color: #888;
        }

        .recauda-fila .valor {
            font-weight: 700;
            color: #333;
        }

        .fila-abono {
            background: #fff3cd;
            padding: 5px 8px;
            border-radius: 5px;
            border-left: 3px solid #ffc107;
            margin-bottom: 5px;
        }

        .fila-abono .etiqueta {
            color: #856404;
            font-weight: 700;
        }

        .fila-abono .valor {
            color: #856404;
            font-size: 0.88rem;
        }

        .resultado-dia {
            margin-top: 5px;
            padding: 6px 10px;
            border-radius: 6px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .resultado-deuda {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .resultado-favor {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .resultado-neutro {
            background: linear-gradient(135deg, #e2e3e5, #d6d8db);
            color: #383d41;
            border-left: 4px solid #6c757d;
        }

        .resultado-dia .icono {
            font-size: 0.95rem;
            margin-right: 4px;
        }

        .sin-recauda {
            text-align: center;
            padding: 8px;
            background: #f8f9fc;
            border-radius: 6px;
            color: #999;
            font-size: 0.75rem;
            font-style: italic;
        }

        .estado-chofer {
            padding-top: 6px;
            border-top: 1px dashed #eef1f7;
        }

        .estado-badge {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.78rem;
        }

        .estado-deuda {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .estado-favor {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .estado-aldia {
            background: linear-gradient(135deg, #e2e3e5, #d6d8db);
            color: #383d41;
            border-left: 4px solid #6c757d;
            justify-content: flex-start;
        }

        .estado-badge .estado-monto {
            font-size: 0.88rem;
            margin-left: auto;
        }

        .observacion-container {
            margin-top: 6px;
            padding-top: 6px;
            border-top: 1px dashed #eef1f7;
        }

        .observacion-label {
            display: block;
            font-size: 0.65rem;
            color: #888;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .observaciones-lista {
            display: flex;
            flex-direction: column;
            gap: 3px;
            margin-bottom: 4px;
        }

        .observacion-item {
            display: flex;
            align-items: center;
            gap: 6px;
            background: #fff8e1;
            border-left: 3px solid #ffc107;
            border-radius: 5px;
            padding: 3px 6px;
            font-size: 0.75rem;
        }

        .observacion-texto {
            flex: 1;
            color: #7a5c00;
            font-weight: 600;
            word-break: break-word;
            line-height: 1.25;
        }

        .observacion-fecha {
            font-size: 0.62rem;
            color: #b58a00;
            background: rgba(255, 193, 7, 0.25);
            padding: 1px 6px;
            border-radius: 8px;
            font-weight: 700;
            white-space: nowrap;
        }

        .btn-observacion-borrar {
            flex-shrink: 0;
            background: transparent;
            color: #b58a00;
            border: none;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.65rem;
            transition: all 0.2s;
        }

        .btn-observacion-borrar:hover {
            background: #dc3545;
            color: white;
        }

        .observacion-nueva {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .observacion-input {
            flex: 1;
            padding: 4px 8px;
            border: 1px dashed #dfe6ef;
            border-radius: 6px;
            font-size: 0.75rem;
            background: #fbfcfe;
            font-family: inherit;
            transition: all 0.2s;
            min-width: 0;
        }

        .observacion-input:focus {
            outline: none;
            border-style: solid;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }

        .btn-observacion-agregar {
            flex-shrink: 0;
            background: #667eea;
            color: white;
            border: none;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            transition: all 0.2s;
        }

        .btn-observacion-agregar:hover {
            background: #5568d3;
            transform: scale(1.05);
        }

        .observacion-estado {
            display: block;
            font-size: 0.65rem;
            height: 10px;
            margin-top: 2px;
        }

        .observacion-estado.ok {
            color: #28a745;
        }

        .observacion-estado.error {
            color: #dc3545;
        }

        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #888;
            background: white;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 6px;
            color: #ccc;
        }

        .empty-state a {
            color: #28a745;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title"><i class="fas fa-dollar-sign"></i> Cargar Recaudación</h1>
        <p class="subtitle">Elegí el chofer y la fecha del movimiento</p>

        <div class="fecha-filtro-container">
            <form method="GET" action="cargar_selector.php" style="display: flex; align-items: center; gap: 10px; margin: 0;">
                <?php if ($es_nueva_pestana): ?>
                    <input type="hidden" name="nueva" value="1">
                <?php endif; ?>
                <label for="fecha"><i class="fas fa-calendar-alt"></i> Fecha:</label>
                <input type="date" id="fecha" name="fecha" value="<?php echo htmlspecialchars($fecha_filtro); ?>" max="<?php echo date('Y-m-d'); ?>" onchange="this.form.submit()">
            </form>

            <?php if ($es_hoy): ?>
                <span class="badge-fecha-actual"><i class="fas fa-calendar-day"></i> Hoy · <?php echo $fecha_mostrar; ?></span>
            <?php elseif ($es_ayer): ?>
                <span class="badge-fecha-actual" style="background: linear-gradient(135deg, #6c757d, #495057);"><i class="fas fa-calendar-minus"></i> Ayer · <?php echo $fecha_mostrar; ?></span>
            <?php else: ?>
                <span class="badge-fecha-actual" style="background: linear-gradient(135deg, #17a2b8, #0dcaf0);"><i class="fas fa-calendar"></i> <?php echo $fecha_mostrar; ?></span>
            <?php endif; ?>

            <?php if (!$es_hoy): ?>
                <a href="cargar_selector.php<?php echo $es_nueva_pestana ? '?nueva=1' : ''; ?>" class="btn-fecha btn-hoy"><i class="fas fa-calendar-day"></i> Ver hoy</a>
            <?php endif; ?>

            <?php if (!$es_ayer): ?>
                <a href="cargar_selector.php?fecha=<?php echo date('Y-m-d', strtotime('-1 day')); ?><?php echo $param_nueva; ?>" class="btn-fecha btn-ayer"><i class="fas fa-calendar-minus"></i> Ver ayer</a>
            <?php endif; ?>
        </div>

        <?php if (empty($choferes)): ?>
            <div class="empty-state">
                <i class="fas fa-users-slash"></i>
                <p>No hay choferes activos.</p>
                <p>Creá uno desde <a href="chofer_nuevo.php">Nuevo Chofer</a>.</p>
            </div>
        <?php else: ?>
            <div class="choferes-grid">
                <?php foreach ($choferes as $c):
                    $saldo = floatval($c['saldo_acumulado']);
                    $tieneAuto = !empty($c['movil']);
                    $tieneRecauda = !is_null($c['r_id']);
                    $fecha_deuda = $c['primera_deuda_fecha'] ?? null;

                    $abono_diario = 0;
                    $deposito     = 0;
                    $debe         = 0;

                    if ($tieneRecauda) {
                        $abono_diario = floatval($c['r_abono_diario'] ?? 0);
                        $deposito     = floatval($c['r_total']) + floatval($c['r_mp']) + floatval($c['r_ft']);
                        $debe         = floatval($c['r_debe']);
                    }

                    $obs_list = $obs_por_chofer[(int)$c['id']] ?? [];
                    $chofer_id_card = (int)$c['id'];

                    // === LÓGICA INDEPENDIENTE DE PRÉSTAMOS ===
                    $prestamos_chofer = $prestamos_por_chofer[$chofer_id_card] ?? [];
                    $tiene_prestamo = !empty($prestamos_chofer);
                    $tiene_mora = false;
                    $valor_cuota_mostrar = 0;
                    $deuda_prestamo = 0;

                    if ($tiene_prestamo) {
                        foreach ($prestamos_chofer as $pr) {
                            // AHORA LA DEUDA SE CALCULA CON EL TOTAL PAGADO REAL
                            $deuda_real = floatval($pr['monto_total']) - floatval($pr['total_pagado']);

                            if ($pr['estado_mora'] === 'EN MORA') {
                                $tiene_mora = true;
                                $valor_cuota_mostrar = floatval($pr['valor_cuota']);
                                $deuda_prestamo = $deuda_real;
                                break;
                            }
                        }
                        if (!$tiene_mora) {
                            $valor_cuota_mostrar = floatval($prestamos_chofer[0]['valor_cuota']);
                            $deuda_prestamo = floatval($prestamos_chofer[0]['monto_total']) - floatval($prestamos_chofer[0]['total_pagado']);
                        }
                    }
                ?>
                    <div class="chofer-card" data-chofer-card="<?php echo $chofer_id_card; ?>">

                        <a class="link-ficha" href="cargar.php?chofer=<?php echo $chofer_id_card; ?>&fecha=<?php echo urlencode($fecha_filtro); ?><?php echo $param_nueva; ?>">

                            <div class="card-header-chofer">
                                <?php if (!empty($c['foto'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($c['foto']); ?>" alt="Foto">
                                <?php else: ?>
                                    <div class="sin-foto"><i class="fas fa-user"></i></div>
                                <?php endif; ?>

                                <div class="chofer-datos">
                                    <div class="chofer-nombre"><?php echo htmlspecialchars($c['nombre']); ?></div>
                                    <div class="chofer-meta">
                                        <?php if ($tieneAuto): ?>
                                            <span class="movil-badge">#<?php echo htmlspecialchars($c['movil']); ?></span>
                                            <span class="badge-turno <?php echo ($c['turno'] === 'DIA') ? 'badge-dia' : 'badge-noche'; ?>">
                                                <?php echo htmlspecialchars($c['turno']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="sin-auto-badge"><i class="fas fa-exclamation-triangle"></i> Sin auto</span>
                                        <?php endif; ?>

                                        <?php if ($tieneRecauda): ?>
                                            <span class="cargado-hoy"><i class="fas fa-check"></i> <?php echo $es_hoy ? 'Hoy' : ($es_ayer ? 'Ayer' : $fecha_mostrar); ?></span>
                                        <?php endif; ?>

                                        <?php if ($tiene_prestamo): ?>
                                            <span class="prestamo-badge <?php echo $tiene_mora ? 'mora' : 'al-dia'; ?>">
                                                <i class="fas fa-file-invoice-dollar"></i> <?php echo $tiene_mora ? 'MORA' : 'PRÉSTAMO'; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($tieneRecauda): ?>
                                <div class="recauda-hoy">
                                    <div class="recauda-titulo"><?php echo $titulo_recauda; ?></div>
                                    <div class="recauda-fila fila-abono">
                                        <span class="etiqueta"><i class="fas fa-file-invoice-dollar"></i> Abono diario:</span>
                                        <span class="valor">$<?php echo number_format($abono_diario, 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="recauda-fila">
                                        <span class="etiqueta"><i class="fas fa-hand-holding-usd"></i> Depósito del chofer:</span>
                                        <span class="valor">$<?php echo number_format($deposito, 0, ',', '.'); ?></span>
                                    </div>

                                    <?php if ($debe > 0.001): ?>
                                        <div class="resultado-dia resultado-deuda"><span><i class="fas fa-exclamation-circle icono"></i> DEUDA DEL DÍA</span><span>-$<?php echo number_format($debe, 0, ',', '.'); ?></span></div>
                                    <?php elseif ($debe < -0.001): ?>
                                        <div class="resultado-dia resultado-favor"><span><i class="fas fa-check-circle icono"></i> A FAVOR DEL DÍA</span><span>+$<?php echo number_format(abs($debe), 0, ',', '.'); ?></span></div>
                                    <?php else: ?>
                                        <div class="resultado-dia resultado-neutro"><span><i class="fas fa-check icono"></i> AL DÍA</span><span>$0</span></div>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <div class="sin-recauda"><i class="fas fa-inbox"></i> <?php echo $aviso_sin; ?></div>
                            <?php endif; ?>

                            <div class="estado-chofer">
                                <?php if ($saldo < 0): ?>
                                    <div class="estado-badge estado-deuda"><i class="fas fa-exclamation-circle"></i><span>Deuda desde el <?php echo !empty($fecha_deuda) ? date('d/m/Y', strtotime($fecha_deuda)) : '—'; ?></span><span class="estado-monto">-$<?php echo number_format(abs($saldo), 0, ',', '.'); ?></span></div>
                                <?php elseif ($saldo > 0): ?>
                                    <div class="estado-badge estado-favor"><i class="fas fa-check-circle"></i><span>Saldo a favor</span><span class="estado-monto">+$<?php echo number_format($saldo, 0, ',', '.'); ?></span></div>
                                <?php else: ?>
                                    <div class="estado-badge estado-aldia"><i class="fas fa-check"></i><span>Al día</span></div>
                                <?php endif; ?>
                            </div>
                        </a>

                        <?php if ($tiene_prestamo): ?>
                            <?php if ($tiene_mora): ?>
                                <div class="alerta-prestamo mora" style="margin-top: 6px; background: #fff;">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>PRÉSTAMO EN MORA - Saldo: $<?php echo number_format($deuda_prestamo, 0, ',', '.'); ?></span>
                                </div>
                            <?php else: ?>
                                <div class="alerta-prestamo al-dia" style="margin-top: 6px; background: #fff;">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Tiene préstamo al día - Saldo: $<?php echo number_format($deuda_prestamo, 0, ',', '.'); ?></span>
                                </div>
                            <?php endif; ?>

                            <?php if ($saldo > 0 && $deuda_prestamo > 0): ?>
                                <div style="margin-top: 5px;">
                                    <button type="button"
                                        class="btn btn-warning btn-sm w-100"
                                        style="font-weight: 700; font-size: 0.75rem;"
                                        data-chofer="<?php echo $chofer_id_card; ?>"
                                        data-saldo="<?php echo $saldo; ?>"
                                        data-deuda="<?php echo $deuda_prestamo; ?>"
                                        data-nombre="<?php echo htmlspecialchars($c['nombre']); ?>"
                                        onclick="abrirModalDeuda(this)">
                                        <i class="fas fa-hand-holding-usd"></i> Aplicar Saldo a Favor a Deuda
                                    </button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <div class="observacion-container" data-chofer="<?php echo $chofer_id_card; ?>">
                            <label class="observacion-label"><i class="fas fa-comment-dots"></i> Observaciones</label>
                            <div class="observaciones-lista">
                                <?php foreach ($obs_list as $o): $fecha_cre = date('d/m/Y', strtotime($o['fecha_creacion'])); ?>
                                    <div class="observacion-item">
                                        <span class="observacion-texto"><?php echo htmlspecialchars($o['observacion']); ?></span>
                                        <span class="observacion-fecha"><?php echo $fecha_cre; ?></span>
                                        <button type="button" class="btn-observacion-borrar" title="Borrar observación" data-id="<?php echo (int)$o['id']; ?>" data-chofer="<?php echo $chofer_id_card; ?>"><i class="fas fa-times"></i></button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="observacion-nueva">
                                <input type="text" class="observacion-input" data-chofer="<?php echo $chofer_id_card; ?>" placeholder="Nueva observación..." maxlength="500">
                                <button type="button" class="btn-observacion-agregar" title="Agregar observación" data-chofer="<?php echo $chofer_id_card; ?>" data-fecha="<?php echo htmlspecialchars($fecha_filtro); ?>"><i class="fas fa-plus"></i></button>
                            </div>
                            <small class="observacion-estado" data-chofer="<?php echo $chofer_id_card; ?>"></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL -->
    <div class="modal fade" id="modalDeuda" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title text-dark fw-bold"><i class="fas fa-hand-holding-usd"></i> Aplicar Saldo a Favor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Chofer: <strong id="modal-nombre"></strong></p>
                    <div class="d-flex justify-content-between mb-1" style="font-size: 0.9rem;">
                        <span>Deuda del Préstamo:</span>
                        <strong class="text-danger">$<span id="modal-deuda"></span></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-3" style="font-size: 0.9rem;">
                        <span>Saldo a Favor Disponible:</span>
                        <strong class="text-success">$<span id="modal-saldo"></span></strong>
                    </div>
                    <label class="form-label fw-bold">¿Cuánto querés aplicar a la deuda?</label>
                    <input type="number" id="monto_aplicar" class="form-control form-control-lg text-end" step="0.01">
                    <small class="text-muted">Máximo a aplicar: $<span id="modal-maximo"></span></small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="procesarPagoDeuda()">Aplicar Pago</button>
                </div>
            </div>
        </div>
    </div>

    <!-- BOOTSTRAP JS DESDE CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // === LÓGICA DE OBSERVACIONES ===
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-observacion-agregar').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const choferId = this.getAttribute('data-chofer');
                    const fecha = this.getAttribute('data-fecha');
                    if (!choferId || !fecha) return;
                    const container = this.closest('.observacion-container');
                    const input = container.querySelector('.observacion-input');
                    if (!input || input.value.trim() === '') return;
                    agregarObservacion(choferId, fecha, input, container);
                });
            });

            document.querySelectorAll('.observacion-input').forEach(input => {
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const container = this.closest('.observacion-container');
                        const btn = container.querySelector('.btn-observacion-agregar');
                        if (btn) btn.click();
                    }
                });
                input.addEventListener('click', e => e.stopPropagation());
            });

            document.querySelectorAll('.btn-observacion-borrar').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const id = this.getAttribute('data-id');
                    const choferId = this.getAttribute('data-chofer');
                    if (!confirm('¿Borrar esta observación?')) return;
                    borrarObservacion(id, choferId, this.closest('.observacion-item'), this.closest('.observacion-container'));
                });
            });

            function agregarObservacion(choferId, fecha, input, container) {
                const texto = input.value.trim();
                if (texto === '') return;
                const estado = container.querySelector('.observacion-estado');
                if (estado) {
                    estado.textContent = 'Guardando...';
                    estado.className = 'observacion-estado';
                }
                const fd = new FormData();
                fd.append('accion', 'crear');
                fd.append('chofer_id', choferId);
                fd.append('fecha', fecha);
                fd.append('observacion', texto);
                fetch('guardar_observacion.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(async r => {
                        const text = await r.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error('Respuesta no-JSON');
                        }
                    })
                    .then(data => {
                        if (data.ok) {
                            const lista = container.querySelector('.observaciones-lista');
                            const fechaFmt = formatearFecha(fecha);
                            const item = document.createElement('div');
                            item.className = 'observacion-item';
                            item.innerHTML = `<span class="observacion-texto">${escapeHtml(texto)}</span><span class="observacion-fecha">${fechaFmt}</span><button type="button" class="btn-observacion-borrar" title="Borrar observación" data-id="${data.id}" data-chofer="${choferId}"><i class="fas fa-times"></i></button>`;
                            lista.appendChild(item);
                            item.querySelector('.btn-observacion-borrar').addEventListener('click', function(ev) {
                                ev.preventDefault();
                                ev.stopPropagation();
                                if (!confirm('¿Borrar esta observación?')) return;
                                borrarObservacion(this.getAttribute('data-id'), choferId, item, container);
                            });
                            input.value = '';
                            if (estado) {
                                estado.textContent = 'Guardada ✓';
                                estado.className = 'observacion-estado ok';
                                setTimeout(() => {
                                    estado.textContent = '';
                                }, 2000);
                            }
                        } else {
                            if (estado) {
                                estado.textContent = 'Error';
                                estado.className = 'observacion-estado error';
                            }
                        }
                    }).catch(() => {
                        if (estado) {
                            estado.textContent = 'Error de red';
                            estado.className = 'observacion-estado error';
                        }
                    });
            }

            function borrarObservacion(id, choferId, itemEl, container) {
                const estado = container.querySelector('.observacion-estado');
                const fd = new FormData();
                fd.append('accion', 'borrar');
                fd.append('id', id);
                fetch('guardar_observacion.php', {
                        method: 'POST',
                        body: fd
                    })
                    .then(async r => {
                        const text = await r.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error();
                        }
                    })
                    .then(data => {
                        if (data.ok) {
                            itemEl.remove();
                            if (estado) {
                                estado.textContent = 'Borrada ✓';
                                estado.className = 'observacion-estado ok';
                                setTimeout(() => {
                                    estado.textContent = '';
                                }, 2000);
                            }
                        }
                    }).catch(() => {});
            }

            function formatearFecha(iso) {
                const [y, m, d] = iso.split('-');
                return `${d}/${m}/${y}`;
            }

            function escapeHtml(texto) {
                const div = document.createElement('div');
                div.textContent = texto;
                return div.innerHTML;
            }
        });

        // === LÓGICA PARA APLICAR SALDO A FAVOR ===
        let deudaActual = 0;
        let saldoActual = 0;
        let idChoferActual = 0;

        function abrirModalDeuda(btn) {
            idChoferActual = btn.getAttribute('data-chofer');
            saldoActual = parseFloat(btn.getAttribute('data-saldo')) || 0;
            deudaActual = parseFloat(btn.getAttribute('data-deuda')) || 0;
            const nombre = btn.getAttribute('data-nombre');

            document.getElementById('modal-nombre').innerText = nombre;
            document.getElementById('modal-deuda').innerText = deudaActual.toLocaleString('es-AR', {
                minimumFractionDigits: 2
            });
            document.getElementById('modal-saldo').innerText = saldoActual.toLocaleString('es-AR', {
                minimumFractionDigits: 2
            });

            const maximo = Math.min(saldoActual, deudaActual);
            document.getElementById('modal-maximo').innerText = maximo.toLocaleString('es-AR', {
                minimumFractionDigits: 2
            });

            const inputMonto = document.getElementById('monto_aplicar');
            inputMonto.value = maximo.toFixed(2);
            inputMonto.max = maximo;
            inputMonto.min = 0.01;

            var modalElement = document.getElementById('modalDeuda');
            if (typeof bootstrap !== 'undefined') {
                var myModal = new bootstrap.Modal(modalElement);
                myModal.show();
            } else {
                modalElement.style.display = 'block';
                modalElement.classList.add('show');
                document.body.classList.add('modal-open');
            }
        }

        function procesarPagoDeuda() {
            const monto = parseFloat(document.getElementById('monto_aplicar').value);
            const maximo = Math.min(saldoActual, deudaActual);

            if (isNaN(monto) || monto <= 0) {
                alert('Por favor, ingresá un monto válido.');
                return;
            }
            if (monto > maximo) {
                alert('El monto no puede superar el máximo permitido ($' + maximo + ').');
                return;
            }

            const fd = new FormData();
            fd.append('chofer_id', idChoferActual);
            fd.append('monto', monto);

            fetch('aplicar_saldo_deuda.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        alert('✅ Saldo aplicado a la deuda correctamente.');
                        window.location.reload();
                    } else {
                        alert('❌ Error: ' + data.error);
                    }
                })
                .catch(err => {
                    alert('❌ Error de conexión al procesar el pago.');
                });
        }
    </script>
</body>

</html>