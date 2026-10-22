<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// === DETERMINAR EL CHOFER ===
$chofer_id = null;
$chofer = null;

if (isset($_GET['chofer']) && is_numeric($_GET['chofer'])) {
    $chofer_id = (int)$_GET['chofer'];
    $stmt = $con->prepare("SELECT * FROM choferes WHERE id = ?");
    $stmt->bind_param("i", $chofer_id);
    $stmt->execute();
    $chofer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} elseif (isset($_GET['movil']) && isset($_GET['turno'])) {
    $movil_get = (int)$_GET['movil'];
    $turno_get = $_GET['turno'];
    $stmt = $con->prepare("
        SELECT c.* 
        FROM choferes c
        INNER JOIN asignaciones_auto a ON a.chofer_id = c.id
        INNER JOIN vehiculos v ON v.id = a.vehiculo_id
        WHERE v.movil = ? AND a.turno = ? AND a.activa = 1
    ");
    $stmt->bind_param("is", $movil_get, $turno_get);
    $stmt->execute();
    $chofer = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if ($chofer) $chofer_id = $chofer['id'];
} else {
    die("Falta el parámetro chofer, o móvil + turno.");
}

if (!$chofer) {
    die("Chofer no encontrado.");
}

// === BUSCAR LA ASIGNACIÓN ACTIVA DEL CHOFER ===
$stmt = $con->prepare("
    SELECT 
        a.id AS asignacion_id,
        a.turno,
        v.id AS vehiculo_id,
        v.movil,
        v.marca,
        v.modelo,
        v.dominio
    FROM asignaciones_auto a
    INNER JOIN vehiculos v ON v.id = a.vehiculo_id
    WHERE a.chofer_id = ? AND a.activa = 1
    LIMIT 1
");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$asignacion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$asignacion) {
    die("Este chofer no tiene un auto asignado. Asignale uno desde <a href='taxis.php'>Choferes</a>.");
}

$turno       = strtoupper(trim($asignacion['turno'] ?? ''));
$vehiculo_id = (int)($asignacion['vehiculo_id'] ?? 0);
$movil       = (int)($asignacion['movil'] ?? 0);
$marca       = $asignacion['marca'] ?? '-';
$modelo      = $asignacion['modelo'] ?? '-';
$dominio     = $asignacion['dominio'] ?? '-';

if (!in_array($turno, ['DIA', 'NOCHE'])) {
    die("Error: El chofer tiene un turno inválido en la asignación.");
}

$nombre    = $chofer['nombre'];
$cel       = $chofer['cel'];
$email     = $chofer['email'];
$barrio    = $chofer['barrio'];
$direccion = $chofer['dir'];
$cp        = $chofer['cp'];
$foto      = $chofer['foto'];

// === SALDO DE CUENTA CORRIENTE ===
$stmt = $con->prepare("SELECT COALESCE(SUM(monto), 0) AS saldo FROM cuenta_corriente WHERE chofer_id = ?");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$saldo = floatval($stmt->get_result()->fetch_assoc()['saldo']);
$stmt->close();

$saldo_deudor  = $saldo < 0 ? abs($saldo) : 0;
$saldo_a_favor = $saldo > 0 ? $saldo : 0;

// === FECHA ===
$fecha = $_GET['fecha'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date('Y-m-d');
}
if ($fecha > date('Y-m-d')) {
    $fecha = date('Y-m-d');
}

$es_hoy = ($fecha === date('Y-m-d'));
$es_nueva_pestana = isset($_GET['nueva']) && $_GET['nueva'] == '1';
$param_nueva = $es_nueva_pestana ? '&nueva=1' : '';

// === RECAUDACIÓN DEL DÍA ===
$stmt = $con->prepare("SELECT * FROM recauda WHERE fecha = ? AND chofer_id = ?");
$stmt->bind_param("si", $fecha, $chofer_id);
$stmt->execute();
$reca = $stmt->get_result()->fetch_assoc();
$stmt->close();

$abono_diario_actual = floatval($reca['abono_diario'] ?? 0);
$voucher_actual      = floatval($reca['voucher'] ?? 0);
$comision_actual     = floatval($reca['comision'] ?? 0);
$total_actual        = floatval($reca['total'] ?? 0);
$mp_actual           = floatval($reca['mp'] ?? 0);
$ft_actual           = floatval($reca['ft'] ?? 0);
$debe_actual         = floatval($reca['debe'] ?? 0);
$observaciones_actual = $reca['observaciones'] ?? '';

$yaCargado = ($reca !== null);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cargar Importes - <?php echo htmlspecialchars($nombre); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        /* ... (todo el CSS igual que antes) ... */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background-color: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            overflow-y: auto;
        }

        .app-container {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 10px 15px;
            gap: 8px;
        }

        .turno-banner {
            border-radius: 8px;
            padding: 8px 18px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            height: 50px;
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.12);
            flex-shrink: 0;
        }

        .turno-banner.turno-dia {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .turno-banner.turno-noche {
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }

        .turno-banner .turno-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .turno-banner .turno-icon {
            font-size: 1.3rem;
            width: 34px;
            height: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 7px;
        }

        .turno-banner .turno-label {
            font-size: 1.05rem;
            font-weight: 700;
            letter-spacing: 2px;
            margin: 0;
        }

        .turno-banner .turno-info {
            font-size: 0.85rem;
            opacity: 0.95;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }

        .turno-banner .turno-info .fecha-destacada {
            background: rgba(255, 255, 255, 0.35);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .turno-banner .turno-info .fecha-destacada input[type="date"] {
            background: transparent;
            border: none;
            color: inherit;
            font-weight: 700;
            font-size: 0.95rem;
            font-family: inherit;
            cursor: pointer;
            outline: none;
            padding: 0;
            width: 120px;
        }

        .turno-banner .turno-info .fecha-destacada input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
            cursor: pointer;
            opacity: 0.9;
        }

        .btn-hoy-banner {
            background: rgba(255, 255, 255, 0.9);
            color: #333;
            padding: 3px 10px;
            border-radius: 15px;
            font-size: 0.75rem;
            font-weight: 700;
            text-decoration: none;
            margin-left: 5px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }

        .btn-hoy-banner:hover {
            background: white;
            color: #000;
            text-decoration: none;
            transform: scale(1.05);
        }

        .aviso-compacto {
            background: linear-gradient(135deg, #cce5ff, #b3d9ff);
            border-left: 4px solid #004085;
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #004085;
            flex-shrink: 0;
        }

        .aviso-otro-dia {
            background: linear-gradient(135deg, #fff3cd, #ffe69c);
            border-left: 4px solid #856404;
            padding: 8px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #856404;
            font-weight: 600;
            flex-shrink: 0;
        }

        .aviso-deuda {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            border-left: 4px solid #dc3545;
            padding: 6px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            color: #721c24;
            font-weight: 600;
            flex-shrink: 0;
        }

        .aviso-a-favor {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            border-left: 4px solid #28a745;
            padding: 6px 12px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.82rem;
            color: #155724;
            font-weight: 600;
            flex-shrink: 0;
        }

        .contenido-grid {
            display: grid;
            grid-template-columns: 260px 240px 1fr;
            gap: 10px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
        }

        .card h6 {
            color: #667eea;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            border-bottom: 1px solid #eef1f7;
            padding-bottom: 5px;
            margin: 0 0 8px 0;
        }

        .foto-chofer {
            text-align: center;
            margin-bottom: 8px;
        }

        .foto-chofer img {
            width: 75px;
            height: 75px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #eef1f7;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .foto-chofer img:hover {
            transform: scale(1.05);
        }

        .foto-chofer small {
            display: block;
            font-size: 0.68rem;
            color: #999;
            margin-top: 3px;
        }

        .datos-lista {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.78rem;
        }

        .datos-lista .fila {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            padding: 2px 0;
            border-bottom: 1px dashed #f0f3f9;
            line-height: 1.3;
        }

        .datos-lista .fila:last-child {
            border-bottom: none;
        }

        .datos-lista .etiqueta {
            color: #888;
            font-weight: 500;
            white-space: nowrap;
        }

        .datos-lista .valor {
            color: #222;
            font-weight: 600;
            text-align: right;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .importes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 10px;
        }

        .campo {
            display: flex;
            flex-direction: column;
        }

        .campo label {
            font-size: 0.72rem;
            color: #666;
            font-weight: 500;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .campo input,
        .campo textarea {
            padding: 4px 7px;
            font-size: 0.85rem;
            border: 1px solid #dfe6ef;
            border-radius: 5px;
            width: 100%;
            background: white;
        }

        .campo input:read-only {
            background-color: #eef1f7;
            color: #666;
        }

        .campo-abono {
            grid-column: 1 / -1;
            background: #fff3cd;
            padding: 8px 10px;
            border-radius: 6px;
            border-left: 3px solid #ffc107;
            margin-bottom: 4px;
        }

        .campo-abono label {
            font-weight: 700;
            color: #856404;
            font-size: 0.8rem;
        }

        .campo-abono input {
            font-size: 1.1rem;
            font-weight: 700;
            text-align: right;
            padding: 6px 10px;
        }

        .ya-cargado-panel {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #d1ecf1, #bee5eb);
            border-left: 4px solid #17a2b8;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 6px;
            position: relative;
        }

        .ya-cargado-panel .titulo {
            font-size: 0.75rem;
            font-weight: 700;
            color: #0c5460;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn-editar-carga {
            background: #ffc107;
            color: #856404;
            border: none;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 0.7rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-editar-carga:hover {
            background: #ffca2c;
        }

        .ya-cargado-filas {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4px 15px;
        }

        .ya-cargado-fila {
            display: flex;
            justify-content: space-between;
            font-size: 0.82rem;
            padding: 3px 0;
            color: #0c5460;
            border-bottom: 1px dashed #9fcdd5;
        }

        .ya-cargado-fila:last-child {
            border-bottom: none;
        }

        .ya-cargado-fila .etiqueta {
            color: #0c5460;
        }

        .ya-cargado-fila .valor {
            font-weight: 700;
        }

        .resultado-final {
            grid-column: 1 / -1;
            margin-top: 4px;
            padding-top: 8px;
            border-top: 2px solid #eef1f7;
        }

        .resultado-final input {
            font-size: 1.5rem;
            font-weight: 700;
            padding: 6px;
            text-align: center;
            border-width: 2px;
        }

        .resultado-final .ayuda {
            font-size: 0.68rem;
            color: #999;
            text-align: center;
            margin-top: 3px;
        }

        .acciones {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            padding-top: 4px;
        }

        .btn-enviar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 8px 22px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .btn-enviar:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-volver {
            background: #eef1f7;
            color: #555;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-volver:hover {
            background: #dde3ec;
            color: #333;
            text-decoration: none;
        }

        .campo-porcentaje input[type="text"] {
            background-color: #eef1f7;
            color: #667eea;
            font-weight: 700;
            text-align: center;
            cursor: not-allowed;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="app-container">
        <div class="turno-banner <?php echo ($turno === 'DIA') ? 'turno-dia' : 'turno-noche'; ?>">
            <div class="turno-left">
                <div class="turno-icon">
                    <?php if ($turno === 'DIA'): ?><i class="fas fa-sun"></i><?php else: ?><i class="fas fa-moon"></i><?php endif; ?>
                </div>
                <h1 class="turno-label">TURNO <?php echo htmlspecialchars($turno); ?></h1>
            </div>
            <div class="turno-info">
                Móvil <strong>#<?php echo htmlspecialchars($movil); ?></strong>
                &nbsp;·&nbsp; <?php echo htmlspecialchars($nombre); ?>
                &nbsp;·&nbsp; <?php echo htmlspecialchars($marca . ' ' . $modelo); ?>
                &nbsp;·&nbsp;
                <!-- Fecha destacada con input date -->

                <span class="fecha-destacada" style="display: flex; align-items: center;">
                    <i class="fas fa-calendar-day"></i>
                    <span style="font-weight: 700; margin: 0 5px; text-transform: uppercase;">
                        <?php
                        // Array para traducir los días al español
                        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        $dia_semana = $dias[date('w', strtotime($fecha))];
                        echo $dia_semana;
                        ?>
                    </span>
                    <input type="date" id="selector-fecha" value="<?php echo htmlspecialchars($fecha); ?>" max="<?php echo date('Y-m-d'); ?>" onchange="cambiarFecha(this.value)" style="width: 115px;">
                    <?php if ($es_hoy): ?><span style="font-size:0.7rem; opacity:0.85; margin-left: 5px;">HOY</span><?php endif; ?>
                </span>

                <!-- ------------------------------ -->
                <?php if (!$es_hoy): ?>
                    <a href="?<?php $params = $_GET;
                                $params['fecha'] = date('Y-m-d');
                                echo htmlspecialchars(http_build_query($params)); ?>" class="btn-hoy-banner"><i class="fas fa-undo"></i> HOY</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$es_hoy): ?>
            <div class="aviso-otro-dia"><i class="fas fa-exclamation-triangle"></i><span><strong>Atención:</strong> estás cargando importes del día <strong><?php echo date('d/m/Y', strtotime($fecha)); ?></strong>, no de hoy.</span></div>
        <?php endif; ?>

        <?php if ($yaCargado): ?>
            <div class="aviso-compacto"><i class="fas fa-info-circle"></i><span><strong>Ya hay movimientos cargados en esta fecha.</strong> Lo que ingreses ahora se <strong>sumará</strong> a lo ya cargado.</span></div>
        <?php endif; ?>

        <?php if ($saldo_deudor > 0): ?>
            <div class="aviso-deuda"><i class="fas fa-exclamation-circle"></i><span>⚠️ Este chofer debe <strong>$<?php echo number_format($saldo_deudor, 2, ',', '.'); ?></strong> de períodos anteriores.</span></div>
        <?php endif; ?>

        <?php if ($saldo_a_favor > 0): ?>
            <div class="aviso-a-favor"><i class="fas fa-check-circle"></i><span>💰 Este chofer tiene <strong>$<?php echo number_format($saldo_a_favor, 2, ',', '.'); ?></strong> a favor.</span></div>
        <?php endif; ?>

        <form id="formulario" action="guarda_importes.php" method="post" enctype="multipart/form-data">
            <input type="hidden" name="turno" value="<?php echo htmlspecialchars($turno); ?>">
            <input type="hidden" name="movil" value="<?php echo htmlspecialchars($movil); ?>">
            <input type="hidden" name="chofer_id" value="<?php echo htmlspecialchars($chofer_id); ?>">
            <input type="hidden" name="vehiculo_id" value="<?php echo htmlspecialchars($vehiculo_id); ?>">
            <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">

            <!-- ⚠️ ESTE ES EL CAMPO QUE FALTABA -->
            <input type="hidden" name="modo_edicion" id="modo_edicion" value="0">

            <?php if ($es_nueva_pestana): ?>
                <input type="hidden" name="nueva" value="1">
            <?php endif; ?>

            <div class="contenido-grid">
                <!-- COL 1: CHOFER -->
                <div class="card">
                    <h6><i class="fas fa-user"></i> Chofer</h6>
                    <div class="foto-chofer">
                        <label for="inputFoto1" style="cursor:pointer;">
                            <img id="previewFoto1" src="<?php echo !empty($foto) ? '../images/' . htmlspecialchars($foto) : 'https://via.placeholder.com/75/aaaaaa/ffffff?text=?'; ?>">
                        </label>
                        <input type="file" id="inputFoto1" name="foto_chofer" accept="image/*" style="display:none;">
                        <small>Clic para cambiar</small>
                    </div>
                    <div class="datos-lista">
                        <div class="fila"><span class="etiqueta">Nombre:</span><span class="valor"><?php echo htmlspecialchars($nombre); ?></span></div>
                        <div class="fila"><span class="etiqueta">Teléfono:</span><span class="valor"><?php echo htmlspecialchars($cel ?: '-'); ?></span></div>
                        <div class="fila"><span class="etiqueta">Email:</span><span class="valor"><?php echo htmlspecialchars($email ?: '-'); ?></span></div>
                        <div class="fila"><span class="etiqueta">Barrio:</span><span class="valor"><?php echo htmlspecialchars($barrio ?: '-'); ?></span></div>
                        <div class="fila"><span class="etiqueta">Calle:</span><span class="valor"><?php echo htmlspecialchars($direccion ?: '-'); ?></span></div>
                        <div class="fila"><span class="etiqueta">CP:</span><span class="valor"><?php echo htmlspecialchars($cp ?: '-'); ?></span></div>
                    </div>
                </div>

                <!-- COL 2: VEHÍCULO -->
                <div class="card">
                    <h6><i class="fas fa-taxi"></i> Vehículo</h6>
                    <div class="datos-lista">
                        <div class="fila"><span class="etiqueta">Móvil:</span><span class="valor">#<?php echo htmlspecialchars($movil); ?></span></div>
                        <div class="fila"><span class="etiqueta">Marca:</span><span class="valor"><?php echo htmlspecialchars($marca); ?></span></div>
                        <div class="fila"><span class="etiqueta">Modelo:</span><span class="valor"><?php echo htmlspecialchars($modelo); ?></span></div>
                        <div class="fila"><span class="etiqueta">Dominio:</span><span class="valor"><?php echo htmlspecialchars($dominio); ?></span></div>
                        <div class="fila"><span class="etiqueta">Turno:</span><span class="valor"><?php echo htmlspecialchars($turno); ?></span></div>
                        <?php if ($saldo_deudor > 0): ?>
                            <div class="fila" style="color:#dc3545; font-weight:600;">
                                <span class="etiqueta">Deuda previa:</span>
                                <span class="valor" style="color:#dc3545;">-$<?php echo number_format($saldo_deudor, 2, ',', '.'); ?></span>
                            </div>
                        <?php elseif ($saldo_a_favor > 0): ?>
                            <div class="fila" style="color:#28a745; font-weight:600;">
                                <span class="etiqueta">A favor:</span>
                                <span class="valor" style="color:#28a745;">+$<?php echo number_format($saldo_a_favor, 2, ',', '.'); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- COL 3: IMPORTES -->
                <div class="card">
                    <h6><i class="fas fa-money-bill-wave"></i> Importes del Día</h6>
                    <div class="importes-grid">
                        <!-- ABONO DIARIO -->
                        <div class="campo campo-abono">
                            <label><i class="fas fa-file-invoice-dollar"></i> Abono diario (lo que debe pagar hoy)</label>
                            <input type="number" id="abono_diario" name="abono_diario" step="0.01" value="<?php echo ($abono_diario_actual > 0) ? $abono_diario_actual : '' ?>" placeholder="0.00">
                        </div>

                        <!-- PANEL YA CARGADO -->
                        <?php if ($yaCargado): ?>
                            <div class="ya-cargado-panel" id="panel-ya-cargado">
                                <div class="titulo">
                                    <span><i class="fas fa-check-circle"></i> Ya cargado en esta fecha</span>
                                    <button type="button" class="btn-editar-carga" onclick="habilitarEdicion()"><i class="fas fa-pen"></i> Editar / Corregir</button>
                                </div>
                                <div class="ya-cargado-filas">
                                    <div class="ya-cargado-fila"><span class="etiqueta">Voucher:</span><span class="valor">$<?php echo number_format($voucher_actual, 0, ',', '.'); ?></span></div>
                                    <div class="ya-cargado-fila"><span class="etiqueta">MP:</span><span class="valor">$<?php echo number_format($mp_actual, 0, ',', '.'); ?></span></div>
                                    <div class="ya-cargado-fila"><span class="etiqueta">FT:</span><span class="valor">$<?php echo number_format($ft_actual, 0, ',', '.'); ?></span></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- NUEVOS MOVIMIENTOS -->
                        <div class="campo">
                            <label id="lbl-voucher">Nuevo Voucher</label>
                            <input type="number" id="voucher" name="voucher_nuevo" step="0.01" value="" placeholder="0.00">
                        </div>
                        <div class="campo campo-porcentaje">
                            <label>Comisión aplicada</label>
                            <input type="text" id="porcentaje_mostrar" value="10%" readonly>
                            <input type="hidden" id="porcentaje" name="porcentaje_nuevo" value="10">
                        </div>
                        <div class="campo">
                            <label id="lbl-mp">Nuevo MP</label>
                            <input type="number" id="mp" name="mp_nuevo" step="0.01" value="" placeholder="0.00">
                        </div>
                        <div class="campo">
                            <label id="lbl-ft">Nuevo Ft (efectivo)</label>
                            <input type="number" id="ft" name="ft_nuevo" step="0.01" value="" placeholder="0.00">
                        </div>

                        <!-- READONLY -->
                        <div class="campo">
                            <label>Comisión (10%)</label>
                            <input type="number" id="comi" step="0.01" readonly value="<?php echo $comision_actual ?>">
                        </div>
                        <div class="campo">
                            <label>Total Voucher c/desc acumulado</label>
                            <input type="number" id="tot_vou" step="0.01" readonly value="<?php echo $total_actual ?>">
                        </div>

                        <!-- RESULTADO FINAL -->
                        <div class="campo resultado-final">
                            <label style="font-size:0.85rem; font-weight:600; text-align:center;">Deuda / A favor del día</label>
                            <input type="number" id="faltante" step="0.01" readonly value="<?php echo $debe_actual ?>">
                            <div class="ayuda"><span style="color:#dc3545;">Rojo = debe</span> · <span style="color:#28a745;">Verde = a favor</span> · Negro = al día</div>
                        </div>

                        <!-- OBSERVACIONES -->
                        <div class="campo" style="grid-column: 1 / -1; margin-top: 5px;">
                            <label><i class="fas fa-comment-dots"></i> Observaciones del día</label>
                            <textarea name="observaciones" rows="2" placeholder="Escribí alguna nota o detalle del día..." style="resize: none;"><?php echo htmlspecialchars($observaciones_actual); ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="acciones">
                <a href="cargar_selector.php?fecha=<?php echo urlencode($fecha); ?><?php echo $param_nueva; ?>" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver</a>
                <button type="submit" class="btn-enviar"><i class="fas fa-save"></i> Guardar y sumar</button>
            </div>
        </form>
    </div>

    <script>
        const VOUCHER_PREV = <?php echo $voucher_actual; ?>;
        const MP_PREV = <?php echo $mp_actual; ?>;
        const FT_PREV = <?php echo $ft_actual; ?>;
        let modoEdicion = false;

        function calcular() {
            const abono = parseFloat(document.getElementById('abono_diario').value) || 0;
            const voucherNuevo = parseFloat(document.getElementById('voucher').value) || 0;
            const mpNuevo = parseFloat(document.getElementById('mp').value) || 0;
            const ftNuevo = parseFloat(document.getElementById('ft').value) || 0;

            // Lógica clave: si es edición, pisa el valor. Si no, suma.
            const voucherTotal = modoEdicion ? voucherNuevo : (VOUCHER_PREV + voucherNuevo);
            const mpTotal = modoEdicion ? mpNuevo : (MP_PREV + mpNuevo);
            const ftTotal = modoEdicion ? ftNuevo : (FT_PREV + ftNuevo);

            const comision = voucherTotal * 10 / 100;
            const totalVoucher = voucherTotal - comision;

            document.getElementById('comi').value = comision.toFixed(2);
            document.getElementById('tot_vou').value = totalVoucher.toFixed(2);

            const deposito = totalVoucher + mpTotal + ftTotal;
            const faltante = abono - deposito;

            const f = document.getElementById('faltante');
            f.value = faltante.toFixed(2);

            if (faltante > 0) {
                f.style.color = "red";
                f.style.backgroundColor = "#f8d7da";
                f.style.borderColor = "#dc3545";
            } else if (faltante < 0) {
                f.style.color = "green";
                f.style.backgroundColor = "#d4edda";
                f.style.borderColor = "#28a745";
            } else {
                f.style.color = "black";
                f.style.backgroundColor = "white";
                f.style.borderColor = "#ced4da";
            }
        }

        function habilitarEdicion() {
            if (!confirm('Atención: Estás por editar los importes ya guardados. Los valores que pongas ahora REEMPLAZARÁN a los anteriores, no se sumarán. ¿Continuar?')) return;

            modoEdicion = true;

            // ⚠️ ESTA ES LA LÍNEA QUE ACTIVA EL MODO EDICIÓN EN EL SERVIDOR
            document.getElementById('modo_edicion').value = '1';

            // Precargar los inputs con los valores anteriores
            document.getElementById('voucher').value = VOUCHER_PREV;
            document.getElementById('mp').value = MP_PREV;
            document.getElementById('ft').value = FT_PREV;
            document.getElementById('abono_diario').value = <?php echo $abono_diario_actual; ?>;

            // Ocultar el panel de "Ya cargado"
            document.getElementById('panel-ya-cargado').style.display = 'none';

            // Cambiar los labels
            document.getElementById('lbl-voucher').innerText = "Editar Voucher Total";
            document.getElementById('lbl-mp').innerText = "Editar MP Total";
            document.getElementById('lbl-ft').innerText = "Editar FT Total";

            // Resaltar los inputs
            ['voucher', 'mp', 'ft'].forEach(id => {
                document.getElementById(id).style.border = '2px solid #ffc107';
                document.getElementById(id).style.backgroundColor = '#fff3cd';
            });

            calcular();
        }

        function cambiarFecha(nuevaFecha) {
            const params = new URLSearchParams(window.location.search);
            params.set('fecha', nuevaFecha);
            window.location.search = params.toString();
        }

        ['abono_diario', 'voucher', 'porcentaje', 'mp', 'ft'].forEach(id => {
            document.getElementById(id).addEventListener('input', calcular);
        });
        calcular();

        document.querySelectorAll('input[type=number]').forEach(function(input) {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            }, {
                passive: false
            });
        });

        document.getElementById('inputFoto1').addEventListener('change', e => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = ev => document.getElementById('previewFoto1').src = ev.target.result;
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>