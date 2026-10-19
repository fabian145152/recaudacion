<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// === VALIDAR ID ===
if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de asignación inválido.");
}
$asignacion_id = (int)$_GET['q'];

// === OBTENER DATOS DE LA ASIGNACIÓN ACTUAL ===
$stmt = $con->prepare("
    SELECT a.*, c.nombre AS chofer_nombre, c.foto AS chofer_foto,
           v.movil AS movil_actual, v.marca, v.modelo, v.dominio
    FROM asignaciones a
    INNER JOIN choferes c ON c.id = a.chofer_id
    INNER JOIN vehiculos v ON v.id = a.vehiculo_id
    WHERE a.id = ?
");
$stmt->bind_param("i", $asignacion_id);
$stmt->execute();
$asig = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$asig) {
    die("Asignación no encontrada.");
}

if ((int)$asig['activa'] !== 1) {
    die("Esta asignación ya fue cerrada. No se puede reasignar.");
}

// === PROCESAR POST ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nuevo_vehiculo_id = (int)($_POST['nuevo_vehiculo_id'] ?? 0);
    $observaciones     = trim($_POST['observaciones'] ?? '');
    $fecha_cambio      = $_POST['fecha_cambio'] ?? date('Y-m-d');

    if ($nuevo_vehiculo_id <= 0) {
        die("<script>alert('Debés seleccionar un vehículo.'); window.history.back();</script>");
    }
    if (empty($observaciones)) {
        die("<script>alert('Las observaciones son obligatorias para dejar registro del cambio.'); window.history.back();</script>");
    }
    if ($nuevo_vehiculo_id == $asig['vehiculo_id']) {
        die("<script>alert('El vehículo seleccionado es el mismo que tiene asignado.'); window.history.back();</script>");
    }

    // Verificar que el nuevo vehículo exista
    $check = $con->prepare("SELECT id FROM vehiculos WHERE id = ?");
    $check->bind_param("i", $nuevo_vehiculo_id);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        die("<script>alert('El vehículo seleccionado no existe.'); window.history.back();</script>");
    }
    $check->close();

    // === INICIAR TRANSACCIÓN ===
    $con->begin_transaction();

    try {
        // 1. Cerrar la asignación actual
        $stmt = $con->prepare("
            UPDATE asignaciones 
            SET activa = 0, fecha_hasta = ?, observaciones = CONCAT(observaciones, ' | CAMBIO: ', ?)
            WHERE id = ?
        ");
        $stmt->bind_param("ssi", $fecha_cambio, $observaciones, $asignacion_id);
        $stmt->execute();
        $stmt->close();

        // 2. Crear nueva asignación con el nuevo vehículo
        $stmt = $con->prepare("
            INSERT INTO asignaciones 
            (chofer_id, vehiculo_id, turno, fecha_desde, activa, observaciones)
            VALUES (?, ?, ?, ?, 1, ?)
        ");
        $stmt->bind_param(
            "iisss",
            $asig['chofer_id'],
            $nuevo_vehiculo_id,
            $asig['turno'],
            $fecha_cambio,
            $observaciones
        );
        $stmt->execute();
        $stmt->close();

        $con->commit();

        echo "<script>
            alert('✅ Cambio de auto realizado correctamente.');
            window.location = 'taxis.php';
        </script>";

    } catch (Exception $e) {
        $con->rollback();
        echo "<script>
            alert('❌ Error al reasignar: " . addslashes($e->getMessage()) . "');
            window.history.back();
        </script>";
    }
    exit;
}

// === OBTENER LISTA DE VEHÍCULOS DISPONIBLES ===
// Excluye el vehículo actual y muestra cuáles ya están ocupados en el mismo turno
$sql_vehiculos = "
    SELECT v.id, v.movil, v.marca, v.modelo, v.dominio,
        (SELECT COUNT(*) FROM asignaciones a2 
         WHERE a2.vehiculo_id = v.id AND a2.activa = 1 AND a2.turno = ?) AS ocupado
    FROM vehiculos v
    ORDER BY v.movil ASC
";
$stmt = $con->prepare($sql_vehiculos);
$stmt->bind_param("s", $asig['turno']);
$stmt->execute();
$vehiculos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Cambiar Auto - <?php echo htmlspecialchars($asig['chofer_nombre']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .page-wrapper { max-width: 800px; margin: 30px auto; padding: 0 20px; }

        .header-card {
            background: linear-gradient(135deg, #ff9800, #f57c00);
            border-radius: 12px;
            padding: 20px 25px;
            color: white;
            margin-bottom: 20px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-card .icon-big {
            font-size: 2rem;
            width: 55px;
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
        }

        .header-card h1 { font-size: 1.4rem; margin: 0; font-weight: 600; }
        .header-card p { margin: 3px 0 0 0; opacity: 0.95; font-size: 0.9rem; }

        .card-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
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

        .form-group label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 6px;
        }

        .form-control {
            padding: 10px 12px;
            border: 1px solid #dfe6ef;
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        select.form-control option:disabled {
            color: #ccc;
        }

        .info-vehiculo {
            font-size: 0.82rem;
            color: #888;
            margin-top: 4px;
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
        .btn-guardar:hover { background: linear-gradient(135deg, #e68900, #d36f00); color: white; }

        .btn-cancelar {
            background: #eef1f7;
            color: #555;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-cancelar:hover { background: #dde3ec; color: #333; text-decoration: none; }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">

        <div class="header-card">
            <div class="icon-big"><i class="fas fa-exchange-alt"></i></div>
            <div>
                <h1>Cambiar Auto</h1>
                <p>Chofer: <strong><?php echo htmlspecialchars($asig['chofer_nombre']); ?></strong></p>
            </div>
        </div>

        <form method="POST" action="">

            <div class="card-section">
                <h5><i class="fas fa-info-circle"></i> Asignación actual</h5>

                <div class="asignacion-actual">
                    <div class="titulo">Vehículo actual</div>
                    <div class="detalle">
                        Móvil #<?php echo htmlspecialchars($asig['movil_actual']); ?>
                        · <?php echo htmlspecialchars($asig['marca'] . ' ' . $asig['modelo']); ?>
                        · <?php echo htmlspecialchars($asig['dominio']); ?>
                        · Turno <?php echo htmlspecialchars($asig['turno']); ?>
                    </div>
                    <div class="info-vehiculo">
                        Asignado desde: <?php echo date('d/m/Y', strtotime($asig['fecha_desde'])); ?>
                    </div>
                </div>

                <h5 class="mt-4"><i class="fas fa-arrow-right"></i> Nueva asignación</h5>

                <div class="form-group">
                    <label for="fecha_cambio">Fecha del cambio</label>
                    <input type="date" id="fecha_cambio" name="fecha_cambio" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label for="nuevo_vehiculo_id">Nuevo vehículo <span style="color:#dc3545;">*</span></label>
                    <select id="nuevo_vehiculo_id" name="nuevo_vehiculo_id" class="form-control" required>
                        <option value="">— Seleccionar vehículo —</option>
                        <?php foreach ($vehiculos as $v): ?>
                            <?php
                                $esActual = ($v['id'] == $asig['vehiculo_id']);
                                $estaOcupado = ($v['ocupado'] > 0 && !$esActual);
                            ?>
                            <option value="<?php echo $v['id']; ?>"
                                <?php echo $esActual ? 'disabled' : ''; ?>
                                <?php echo $estaOcupado ? 'disabled' : ''; ?>>
                                Móvil #<?php echo $v['movil']; ?> —
                                <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?> —
                                <?php echo htmlspecialchars($v['dominio']); ?>
                                <?php if ($esActual): ?>
                                    (ACTUAL)
                                <?php elseif ($estaOcupado): ?>
                                    (OCUPADO en turno <?php echo htmlspecialchars($asig['turno']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="info-vehiculo">
                        Los vehículos ocupados en el mismo turno no se pueden seleccionar.
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones <span style="color:#dc3545;">*</span></label>
                    <textarea id="observaciones" name="observaciones" class="form-control" rows="3"
                        placeholder="Ej: Chofer de vacaciones, auto en reparación, cambio por mejor unidad..."
                        required></textarea>
                    <div class="info-vehiculo">
                        Este campo es obligatorio. Queda registrado en el historial del chofer.
                    </div>
                </div>

            </div>

            <div class="acciones">
                <a href="taxis.php" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-guardar">
                    <i class="fas fa-check"></i> Confirmar cambio
                </button>
            </div>

        </form>
    </div>
</body>

</html>