<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de chofer inválido.");
}
$chofer_id = (int)$_GET['q'];

// Cargar chofer
$stmt = $con->prepare("SELECT * FROM choferes WHERE id = ?");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) die("Chofer no encontrado.");

// Asignación actual (auto que tiene AHORA)
$stmt = $con->prepare("
    SELECT a.*, v.movil, v.marca, v.modelo, v.dominio
    FROM asignaciones_auto a
    INNER JOIN vehiculos v ON v.id = a.vehiculo_id
    WHERE a.chofer_id = ? AND a.activa = 1
    ORDER BY a.id DESC
    LIMIT 1
");
$stmt->bind_param("i", $chofer_id);
$stmt->execute();
$asig_actual = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Procesar cambio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevo_vehiculo_id = (int)($_POST['nuevo_vehiculo_id'] ?? 0);
    $observaciones     = trim($_POST['observaciones'] ?? '');
    $fecha_cambio      = $_POST['fecha_cambio'] ?? date('Y-m-d');
    $turno             = $_POST['turno'] ?? 'DIA';

    if ($nuevo_vehiculo_id <= 0) {
        die("<script>alert('Seleccioná un vehículo.'); window.history.back();</script>");
    }
    if (empty($observaciones)) {
        die("<script>alert('Las observaciones son obligatorias.'); window.history.back();</script>");
    }
    if (!in_array($turno, ['DIA', 'NOCHE'])) {
        die("<script>alert('Turno inválido.'); window.history.back();</script>");
    }

    // Validar que el nuevo auto no esté ocupado en ese turno por OTRO chofer
    $check = $con->prepare("
        SELECT COUNT(*) AS cant FROM asignaciones_auto 
        WHERE vehiculo_id = ? AND turno = ? AND activa = 1 AND chofer_id != ?
    ");
    $check->bind_param("isi", $nuevo_vehiculo_id, $turno, $chofer_id);
    $check->execute();
    $ocupado = $check->get_result()->fetch_assoc()['cant'];
    $check->close();

    if ($ocupado > 0) {
        die("<script>alert('❌ Ese auto ya está en uso por otro chofer en el turno $turno.'); window.history.back();</script>");
    }

    // Validar que no sea el mismo auto en el mismo turno
    if ($asig_actual && $asig_actual['vehiculo_id'] == $nuevo_vehiculo_id && $asig_actual['turno'] == $turno) {
        die("<script>alert('El chofer ya tiene ese auto en ese turno.'); window.history.back();</script>");
    }

    $con->begin_transaction();
    try {
        // === CERRAR TODAS LAS ASIGNACIONES ACTIVAS DEL CHOFER (no solo una) ===
        $stmt = $con->prepare("
            UPDATE asignaciones_auto 
            SET activa = 0, fecha_hasta = ? 
            WHERE chofer_id = ? AND activa = 1
        ");
        $stmt->bind_param("si", $fecha_cambio, $chofer_id);
        $stmt->execute();
        $stmt->close();

        // === CREAR LA NUEVA ASIGNACIÓN ===
        $stmt = $con->prepare("
            INSERT INTO asignaciones_auto (chofer_id, vehiculo_id, turno, fecha_desde, activa, observaciones)
            VALUES (?, ?, ?, ?, 1, ?)
        ");
        $stmt->bind_param("iisss", $chofer_id, $nuevo_vehiculo_id, $turno, $fecha_cambio, $observaciones);
        $stmt->execute();
        $stmt->close();

        $con->commit();
        echo "<script>alert('✅ Auto cambiado correctamente.'); window.location='taxis.php';</script>";
    } catch (Exception $e) {
        $con->rollback();
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
    exit;
}

// === TRAIGO TODOS LOS VEHÍCULOS CON SU ESTADO EN DÍA Y NOCHE ===
$sql = "
    SELECT 
        v.id, v.movil, v.marca, v.modelo, v.dominio,
        (SELECT c2.nombre FROM asignaciones_auto a2 
         INNER JOIN choferes c2 ON c2.id = a2.chofer_id
         WHERE a2.vehiculo_id = v.id AND a2.turno = 'DIA' AND a2.activa = 1 
         LIMIT 1) AS chofer_dia,
        (SELECT c2.nombre FROM asignaciones_auto a2 
         INNER JOIN choferes c2 ON c2.id = a2.chofer_id
         WHERE a2.vehiculo_id = v.id AND a2.turno = 'NOCHE' AND a2.activa = 1 
         LIMIT 1) AS chofer_noche
    FROM vehiculos v
    WHERE v.activo = 1
    ORDER BY v.movil ASC
";
$result = $con->query($sql);
$vehiculos = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

// Turno por defecto
$turno_default = $asig_actual['turno'] ?? 'DIA';
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Cambiar Auto - <?php echo htmlspecialchars($chofer['nombre']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, #ff9800, #f57c00);
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

        .card-section h5 {
            color: #667eea;
            font-weight: 600;
            border-bottom: 2px solid #eef1f7;
            padding-bottom: 10px;
            margin-bottom: 18px;
            font-size: 1rem;
        }

        .asignacion-actual {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .asignacion-actual .titulo {
            font-size: 0.82rem;
            color: #856404;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .asignacion-actual .detalle {
            font-size: 1rem;
            color: #333;
            font-weight: 600;
        }

        .sin-auto-actual {
            background: #f8f9fc;
            border-left: 4px solid #6c757d;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #666;
            font-style: italic;
        }

        /* === GRILLA DE AUTOS === */
        .autos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 14px;
            margin-bottom: 20px;
        }

        .auto-card {
            position: relative;
            background: white;
            border: 2px solid #dfe6ef;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.2s;
            display: block;
            margin: 0;
        }

        .auto-card:hover {
            border-color: #ff9800;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(255, 152, 0, 0.2);
        }

        .auto-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .auto-card-header {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #eef1f7;
            margin-bottom: 12px;
        }

        .auto-movil {
            font-size: 1.6rem;
            font-weight: 800;
            color: #ff9800;
            background: #fff3e0;
            padding: 6px 14px;
            border-radius: 10px;
            line-height: 1;
            flex-shrink: 0;
        }

        .auto-vehiculo {
            flex: 1;
            min-width: 0;
        }

        .auto-marca-modelo {
            font-weight: 700;
            color: #333;
            font-size: 0.95rem;
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .auto-dominio {
            font-size: 0.8rem;
            color: #888;
            font-family: monospace;
            letter-spacing: 1.5px;
        }

        .badge-actual {
            background: #2196F3;
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.68rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        /* === BLOQUES DE TURNO === */
        .turnos-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .turno-bloque {
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            transition: all 0.2s;
        }

        .turno-bloque .turno-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            opacity: 0.85;
        }

        .turno-bloque .turno-info {
            font-size: 0.85rem;
            font-weight: 600;
            line-height: 1.2;
            word-break: break-word;
        }

        .turno-libre {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #8fd19e;
        }

        .turno-libre .turno-libre-texto {
            font-weight: 800;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .turno-ocupado {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #f1b0b7;
        }

        .turno-chofer {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 0.82rem;
        }

        .turno-chofer i {
            opacity: 0.7;
        }

        .auto-card.ocupado-total {
            opacity: 0.6;
            cursor: not-allowed;
            background: #f8f9fc;
        }

        .auto-card.ocupado-total:hover {
            border-color: #dfe6ef;
            transform: none;
            box-shadow: none;
        }

        .auto-card.actual {
            border-color: #2196F3;
            background: #e3f2fd;
            box-shadow: 0 0 0 3px rgba(33, 150, 243, 0.15);
        }

        .auto-card:has(input:checked) {
            border-color: #ff9800;
            background: #fff3e0;
            box-shadow: 0 0 0 3px rgba(255, 152, 0, 0.2);
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
            background: linear-gradient(135deg, #ff9800, #f57c00);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-guardar:hover {
            color: white;
            opacity: 0.9;
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
            <div class="icon-big"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <h1>Cambiar Auto</h1>
                <p>Chofer: <strong><?php echo htmlspecialchars($chofer['nombre']); ?></strong></p>
            </div>
        </div>

        <form method="POST">

            <div class="card-section">
                <h5><i class="fas fa-info-circle"></i> Auto actual</h5>

                <?php if ($asig_actual): ?>
                    <div class="asignacion-actual">
                        <div class="titulo">Auto asignado actualmente</div>
                        <div class="detalle">
                            Móvil #<?php echo htmlspecialchars($asig_actual['movil']); ?>
                            — <?php echo htmlspecialchars($asig_actual['marca'] . ' ' . $asig_actual['modelo']); ?>
                            — <?php echo htmlspecialchars($asig_actual['dominio']); ?>
                            <span style="background:#34495e; color:white; padding:2px 8px; border-radius:10px; font-size:0.75rem; margin-left:8px;">
                                <?php echo htmlspecialchars($asig_actual['turno']); ?>
                            </span>
                        </div>
                        <small style="color:#856404;">
                            Desde el <?php echo date('d/m/Y', strtotime($asig_actual['fecha_desde'])); ?>
                        </small>
                    </div>
                <?php else: ?>
                    <div class="sin-auto-actual">
                        <i class="fas fa-minus-circle"></i> Este chofer no tiene auto asignado actualmente.
                    </div>
                <?php endif; ?>

                <h5 class="mt-4"><i class="fas fa-arrow-right"></i> Elegí el nuevo auto</h5>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fecha del cambio</label>
                            <input type="date" name="fecha_cambio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Turno <span style="color:#dc3545;">*</span></label>
                            <select name="turno" class="form-control" required>
                                <option value="DIA" <?php echo ($turno_default === 'DIA') ? 'selected' : ''; ?>>DÍA</option>
                                <option value="NOCHE" <?php echo ($turno_default === 'NOCHE') ? 'selected' : ''; ?>>NOCHE</option>
                            </select>
                        </div>
                    </div>
                </div>

                <label style="font-weight:600; font-size:0.9rem; color:#555; margin-bottom:8px; display:block;">
                    Auto <span style="color:#dc3545;">*</span>
                </label>

                <div class="autos-grid">
                    <?php foreach ($vehiculos as $v):
                        $esActual = ($asig_actual && $asig_actual['vehiculo_id'] == $v['id']);
                        $tieneDia   = !empty($v['chofer_dia']);
                        $tieneNoche = !empty($v['chofer_noche']);
                        $ambosOcupados = $tieneDia && $tieneNoche && !$esActual;
                    ?>
                        <label class="auto-card <?php echo $ambosOcupados ? 'ocupado-total' : ''; ?> <?php echo $esActual ? 'actual' : ''; ?>">
                            <input type="radio" name="nuevo_vehiculo_id" value="<?php echo $v['id']; ?>"
                                <?php echo $ambosOcupados ? 'disabled' : ''; ?>
                                required>

                            <div class="auto-card-header">
                                <span class="auto-movil">#<?php echo htmlspecialchars($v['movil']); ?></span>
                                <div class="auto-vehiculo">
                                    <div class="auto-marca-modelo">
                                        <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?>
                                    </div>
                                    <div class="auto-dominio"><?php echo htmlspecialchars($v['dominio']); ?></div>
                                </div>
                                <?php if ($esActual): ?>
                                    <span class="badge-actual"><i class="fas fa-check-circle"></i> ACTUAL</span>
                                <?php endif; ?>
                            </div>

                            <div class="turnos-grid">
                                <div class="turno-bloque <?php echo $tieneDia ? 'turno-ocupado' : 'turno-libre'; ?>">
                                    <div class="turno-header">
                                        <i class="fas fa-sun"></i> DÍA
                                    </div>
                                    <div class="turno-info">
                                        <?php if ($tieneDia): ?>
                                            <span class="turno-chofer">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($v['chofer_dia']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="turno-libre-texto">
                                                <i class="fas fa-check"></i> LIBRE
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="turno-bloque <?php echo $tieneNoche ? 'turno-ocupado' : 'turno-libre'; ?>">
                                    <div class="turno-header">
                                        <i class="fas fa-moon"></i> NOCHE
                                    </div>
                                    <div class="turno-info">
                                        <?php if ($tieneNoche): ?>
                                            <span class="turno-chofer">
                                                <i class="fas fa-user"></i>
                                                <?php echo htmlspecialchars($v['chofer_noche']); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="turno-libre-texto">
                                                <i class="fas fa-check"></i> LIBRE
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="form-group">
                    <label>Observaciones <span style="color:#dc3545;">*</span></label>
                    <textarea name="observaciones" class="form-control" rows="3" placeholder="Motivo del cambio (ej: auto en reparación, chofer de vacaciones...)" required></textarea>
                </div>
            </div>

            <div class="acciones">
                <a href="taxis.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn-guardar"><i class="fas fa-check"></i> Confirmar cambio</button>
            </div>
        </form>
    </div>
</body>

</html>