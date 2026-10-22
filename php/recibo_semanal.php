<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// === FILTROS ===
$movil_filtro = isset($_GET['movil']) ? (int)$_GET['movil'] : 0;
$fecha_base = $_GET['fecha'] ?? date('Y-m-d');

// Calcular Lunes y Domingo de la semana seleccionada
$dt = new DateTime($fecha_base);
$dia_semana = $dt->format('N'); // 1 = Lunes, 7 = Domingo
$dt->modify('-' . ($dia_semana - 1) . ' days');
$fecha_desde = $dt->format('Y-m-d');

// Obtener lista de móviles
$vehiculos = $con->query("
    SELECT DISTINCT v.movil, c.nombre 
    FROM vehiculos v
    LEFT JOIN asignaciones_auto a ON v.id = a.vehiculo_id AND a.activa = 1
    LEFT JOIN choferes c ON a.chofer_id = c.id
    WHERE v.activo = 1
    ORDER BY v.movil ASC
")->fetch_all(MYSQLI_ASSOC);

$datos_recibo = null;

if ($movil_filtro > 0) {
    $stmt = $con->prepare("
        SELECT c.id AS chofer_id, c.nombre, c.cel, c.foto, v.marca, v.modelo, v.dominio
        FROM asignaciones_auto a
        INNER JOIN choferes c ON c.id = a.chofer_id
        INNER JOIN vehiculos v ON v.id = a.vehiculo_id
        WHERE v.movil = ? AND a.activa = 1 LIMIT 1
    ");
    $stmt->bind_param("i", $movil_filtro);
    $stmt->execute();
    $chofer = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($chofer) {
        $chofer_id = $chofer['chofer_id'];

        // Deuda Anterior
        $stmt = $con->prepare("SELECT COALESCE(SUM(monto), 0) AS saldo FROM cuenta_corriente WHERE chofer_id = ?");
        $stmt->bind_param("i", $chofer_id);
        $stmt->execute();
        $saldo_global = floatval($stmt->get_result()->fetch_assoc()['saldo']);
        $stmt->close();

        $deuda_anterior = $saldo_global < 0 ? abs($saldo_global) : 0;
        $saldo_a_favor = $saldo_global > 0 ? $saldo_global : 0;

        // Traer todos los días de la semana
        $fecha_actual = new DateTime($fecha_desde);
        $fecha_actual->modify('+7 days'); // Extendemos un día más
        $fecha_hasta = $fecha_actual->format('Y-m-d');

        $stmt = $con->prepare("
            SELECT * FROM recauda 
            WHERE movil = ? AND fecha BETWEEN ? AND ?
            ORDER BY fecha ASC
        ");
        $stmt->bind_param("iss", $movil_filtro, $fecha_desde, $fecha_hasta);
        $stmt->execute();
        $res = $stmt->get_result();

        $dias_cargados = [];
        while ($row = $res->fetch_assoc()) {
            $dias_cargados[$row['fecha']] = $row;
        }
        $stmt->close();

        $datos_recibo = [
            'chofer' => $chofer,
            'deuda_anterior' => $deuda_anterior,
            'saldo_a_favor' => $saldo_a_favor,
            'dias_cargados' => $dias_cargados,
            'fecha_desde' => $fecha_desde
        ];
    }
}
$con->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Recibo Semanal de Recaudación</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 10px;
        }

        .filtros-card {
            max-width: 800px;
            margin: 0 auto 15px;
            background: white;
            border-radius: 12px;
            padding: 10px 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        /* HOJA A4 PARA PANTALLA */
        .recibo-hoja {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 15px 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            color: #333;
        }

        .recibo-header {
            text-align: center;
            border-bottom: 1.5px solid #333;
            padding-bottom: 5px;
            margin-bottom: 8px;
        }

        .recibo-header h1 {
            margin: 0;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .recibo-header p {
            margin: 2px 0 0;
            font-size: 0.75rem;
            color: #666;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2px 15px;
            margin-bottom: 8px;
            font-size: 0.75rem;
        }

        .info-grid .fila {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dotted #ccc;
            padding: 2px 0;
        }

        .info-grid .fila strong {
            color: #555;
        }

        /* ALERTA DE DEUDA */
        .alerta-deuda {
            background: #fff3cd;
            border: 1.5px dashed #ffc107;
            border-radius: 4px;
            padding: 5px 10px;
            margin-bottom: 8px;
            font-size: 0.8rem;
            color: #856404;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: 700;
        }

        /* BLOQUES DIARIOS */
        .contenedor-dias {
            display: flex;
            flex-direction: column;
        }

        .bloque-dia {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 6px;
            padding: 5px 8px;
            background: #fdfdfd;
            page-break-inside: avoid;
        }

        .bloque-dia-header {
            font-size: 0.8rem;
            font-weight: 700;
            color: #444;
            border-bottom: 1px solid #eee;
            padding-bottom: 2px;
            margin-bottom: 3px;
            display: flex;
            justify-content: space-between;
        }

        .bloque-dia-header.vacio {
            color: #888;
            font-style: italic;
        }

        .resumen-dia {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            margin-bottom: 2px;
        }

        .resumen-dia .item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .resumen-dia .item span {
            font-size: 0.6rem;
            color: #777;
            text-transform: uppercase;
        }

        .resumen-dia .item strong {
            font-size: 0.8rem;
            color: #333;
        }

        .puntos-relleno {
            font-size: 0.75rem;
            color: #999;
            letter-spacing: 1px;
            display: block;
            margin-bottom: 2px;
        }

        /* FIRMAS */
        .firmas-dia {
            display: flex;
            justify-content: space-between;
            margin-top: 18px;
        }

        .firma-dia-box {
            width: 45%;
            text-align: center;
        }

        .firma-dia-linea {
            border-top: 1px solid #555;
            padding-top: 1px;
            font-size: 0.6rem;
            color: #555;
            font-weight: 600;
        }

        /* REGLAS PARA LLENAR TODA LA PÁGINA AL IMPRIMIR */
        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }

            html,
            body {
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                background: white;
            }

            .filtros-card {
                display: none !important;
            }

            .recibo-hoja {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
                height: 100%;
                /* Fuerza a ocupar todo el alto */
                display: flex;
                flex-direction: column;
            }

            .alerta-deuda {
                background: #fff3cd !important;
                -webkit-print-color-adjust: exact;
            }

            .contenedor-dias {
                flex-grow: 1;
                /* Hace que la lista de días se expanda */
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                /* Distribuye el espacio equitativamente */
                height: 100%;
            }

            .bloque-dia {
                flex-grow: 1;
                /* Cada bloque de día se estira para llenar el espacio */
                margin-bottom: 4px;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .firmas-dia {
                margin-top: 15px;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>

    <div class="filtros-card no-print">
        <form method="GET" action="recibo_semanal.php" style="display: flex; gap: 10px; align-items: flex-end; flex-wrap: wrap;">
            <div>
                <label class="form-label" style="font-size:0.8rem; font-weight:700;">Semana del:</label>
                <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($fecha_base); ?>" required>
            </div>
            <div>
                <label class="form-label" style="font-size:0.8rem; font-weight:700;">Móvil:</label>
                <select name="movil" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <?php foreach ($vehiculos as $v): ?>
                        <option value="<?php echo $v['movil']; ?>" <?php echo ($movil_filtro == $v['movil']) ? 'selected' : ''; ?>>
                            #<?php echo $v['movil']; ?> - <?php echo htmlspecialchars($v['nombre'] ?: 'Sin chofer'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="background: #667eea; border:none;">Generar Recibo</button>

            <?php if ($datos_recibo): ?>
                <button type="button" onclick="window.print()" class="btn btn-success ms-auto"><i class="fas fa-print"></i> Imprimir</button>
            <?php endif; ?>
        </form>
    </div>

    <?php if ($datos_recibo): ?>
        <div class="recibo-hoja">

            <div class="recibo-header">
                <h1>Recibo de Recaudación Semanal</h1>
                <p>Semana del <?php echo date('d/m/Y', strtotime($datos_recibo['fecha_desde'])); ?> al <?php echo date('d/m/Y', strtotime($datos_recibo['fecha_desde'] . ' +6 days')); ?></p>
            </div>

            <div class="info-grid">
                <div class="fila"><strong>Chofer:</strong> <span><?php echo htmlspecialchars($datos_recibo['chofer']['nombre']); ?></span></div>
                <div class="fila"><strong>Móvil:</strong> <span>#<?php echo $movil_filtro; ?></span></div>
                <div class="fila"><strong>Teléfono:</strong> <span><?php echo htmlspecialchars($datos_recibo['chofer']['cel']); ?></span></div>
                <div class="fila"><strong>Vehículo:</strong> <span><?php echo htmlspecialchars($datos_recibo['chofer']['marca'] . ' ' . $datos_recibo['chofer']['modelo']); ?></span></div>
            </div>

            <div class="alerta-deuda">
                <span><i class="fas fa-exclamation-triangle"></i> DEUDA ANTERIOR ACUMULADA:</span>
                <span>$<?php echo number_format($datos_recibo['deuda_anterior'], 2, ',', '.'); ?></span>
            </div>

            <!-- CONTENEDOR DE DÍAS -->
            <div class="contenedor-dias">
                <?php
                $fecha_actual = new DateTime($datos_recibo['fecha_desde']);
                $dias_espanol = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo', 'Lunes (Próxima Semana)'];

                for ($i = 0; $i < 8; $i++):
                    $fecha_str = $fecha_actual->format('Y-m-d');
                    $tiene_datos = isset($datos_recibo['dias_cargados'][$fecha_str]);
                    $dia_data = $tiene_datos ? $datos_recibo['dias_cargados'][$fecha_str] : null;
                ?>
                    <div class="bloque-dia">
                        <div>
                            <div class="bloque-dia-header <?php echo !$tiene_datos ? 'vacio' : ''; ?>">
                                <span><?php echo $dias_espanol[$i]; ?></span>
                                <span><?php echo $fecha_actual->format('d/m/Y'); ?></span>
                            </div>

                            <?php if ($tiene_datos): ?>
                                <?php
                                $dep = floatval($dia_data['total']) + floatval($dia_data['mp']) + floatval($dia_data['ft']);
                                ?>
                                <div class="resumen-dia">
                                    <div class="item"><span>Abono</span><strong>$<?php echo number_format($dia_data['abono_diario'], 2, ',', '.'); ?></strong></div>
                                    <div class="item"><span>V. Neto</span><strong>$<?php echo number_format($dia_data['total'], 2, ',', '.'); ?></strong></div>
                                    <div class="item"><span>MP</span><strong>$<?php echo number_format($dia_data['mp'], 2, ',', '.'); ?></strong></div>
                                    <div class="item"><span>FT</span><strong>$<?php echo number_format($dia_data['ft'], 2, ',', '.'); ?></strong></div>
                                    <div class="item" style="color: #28a745;"><span>Depositado</span><strong>$<?php echo number_format($dep, 2, ',', '.'); ?></strong></div>
                                </div>
                            <?php else: ?>
                                <span class="puntos-relleno">Abono: $...................... | V. Neto: $...................... | MP: $...................... | FT: $...................... | Total: $......................</span>
                            <?php endif; ?>
                        </div>

                        <!-- FIRMAS -->
                        <div class="firmas-dia">
                            <div class="firma-dia-box">
                                <div class="firma-dia-linea">Firma del Chofer</div>
                            </div>
                            <div class="firma-dia-box">
                                <div class="firma-dia-linea">Firma del Cobrador</div>
                            </div>
                        </div>
                    </div>
                <?php
                    $fecha_actual->modify('+1 day');
                endfor;
                ?>
            </div>

        </div>
    <?php endif; ?>

</body>

</html>