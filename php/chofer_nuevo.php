<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre   = trim($_POST['nombre'] ?? '');
    $cel      = trim($_POST['cel'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $barrio   = trim($_POST['barrio'] ?? '');
    $dir      = trim($_POST['dir'] ?? '');
    $cp       = trim($_POST['cp'] ?? '');

    // Asignación opcional
    $vehiculo_id = (int)($_POST['vehiculo_id'] ?? 0);
    $turno       = $_POST['turno'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');

    if (empty($nombre)) {
        die("<script>alert('El nombre es obligatorio.'); window.history.back();</script>");
    }

    // Foto
    $foto_nombre = null;
    if (!empty($_FILES['foto']['name'])) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $tipoMime = mime_content_type($_FILES['foto']['tmp_name']);
        if (in_array($tipoMime, $permitidos)) {
            $foto_nombre = uniqid('chofer_') . "_" . basename($_FILES["foto"]["name"]);
            move_uploaded_file($_FILES["foto"]["tmp_name"], "../images/" . $foto_nombre);
        }
    }

    $con->begin_transaction();
    try {
        // Insertar chofer
        $stmt = $con->prepare("
            INSERT INTO choferes (nombre, cel, email, barrio, dir, cp, foto, activo, fecha_alta)
            VALUES (?, ?, ?, ?, ?, ?, ?, 1, CURDATE())
        ");
        $stmt->bind_param("sssssss", $nombre, $cel, $email, $barrio, $dir, $cp, $foto_nombre);
        $stmt->execute();
        $chofer_id = $stmt->insert_id;
        $stmt->close();

        // Asignar auto si se eligió uno y un turno
        if ($vehiculo_id > 0 && in_array($turno, ['DIA', 'NOCHE'])) {
            // Verificar que el vehículo no esté ocupado en ese turno
            $check = $con->prepare("SELECT id FROM asignaciones_auto WHERE vehiculo_id = ? AND turno = ? AND activa = 1");
            $check->bind_param("is", $vehiculo_id, $turno);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                throw new Exception("Ese vehículo ya está asignado en el turno $turno.");
            }
            $check->close();

            $stmt = $con->prepare("
                INSERT INTO asignaciones_auto (chofer_id, vehiculo_id, turno, fecha_desde, activa, observaciones)
                VALUES (?, ?, ?, CURDATE(), 1, ?)
            ");
            $stmt->bind_param("iiss", $chofer_id, $vehiculo_id, $turno, $observaciones);
            $stmt->execute();
            $stmt->close();
        }

        $con->commit();
        echo "<script>alert('✅ Chofer registrado.'); window.location='taxis.php';</script>";
    } catch (Exception $e) {
        $con->rollback();
        echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
    exit;
}

// Vehículos con sus turnos ocupados
$vehiculos = $con->query("
    SELECT v.id, v.movil, v.marca, v.modelo, v.dominio,
        (SELECT COUNT(*) FROM asignaciones_auto WHERE vehiculo_id = v.id AND activa = 1 AND turno = 'DIA')   AS ocupado_dia,
        (SELECT COUNT(*) FROM asignaciones_auto WHERE vehiculo_id = v.id AND activa = 1 AND turno = 'NOCHE') AS ocupado_noche
    FROM vehiculos v WHERE v.activo = 1
    ORDER BY v.movil ASC
")->fetch_all(MYSQLI_ASSOC);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Nuevo Chofer</title>
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
            <div class="icon-big"><i class="fas fa-user-plus"></i></div>
            <div>
                <h1>Nuevo Chofer</h1>
                <p>Registrar un chofer y opcionalmente asignarle un auto</p>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data">

            <div class="card-section">
                <h5><i class="fas fa-user"></i> Datos personales</h5>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Nombre <span style="color:#dc3545;">*</span></label>
                            <input type="text" name="nombre" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="cel" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Barrio</label>
                            <input type="text" name="barrio" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" name="dir" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>CP</label>
                            <input type="text" name="cp" class="form-control">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Foto</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>
            </div>

            <div class="card-section">
                <h5><i class="fas fa-car-side"></i> Asignación inicial (opcional)</h5>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Vehículo</label>
                            <select name="vehiculo_id" id="selectVehiculo" class="form-control">
                                <option value="">— Sin asignar —</option>
                                <?php foreach ($vehiculos as $v):
                                    $ocupadoDia   = (int)$v['ocupado_dia'] > 0;
                                    $ocupadoNoche = (int)$v['ocupado_noche'] > 0;
                                    $libreDia     = !$ocupadoDia;
                                    $libreNoche   = !$ocupadoNoche;

                                    // Definir el estado general y su clase CSS
                                    if ($ocupadoDia && $ocupadoNoche) {
                                        $estado = 'completo';
                                        $prefijo = '🔴';
                                        $sufijo = ' — ❌ OCUPADO (DÍA y NOCHE)';
                                    } elseif ($ocupadoDia) {
                                        $estado = 'dia';
                                        $prefijo = '🟠';
                                        $sufijo = ' — ⚠️ DÍA ocupado, 🌙 NOCHE libre';
                                    } elseif ($ocupadoNoche) {
                                        $estado = 'noche';
                                        $prefijo = '🟣';
                                        $sufijo = ' — ⚠️ NOCHE ocupado, ☀️ DÍA libre';
                                    } else {
                                        $estado = 'libre';
                                        $prefijo = '🟢';
                                        $sufijo = ' — ✅ LIBRE';
                                    }
                                ?>
                                    <option value="<?php echo $v['id']; ?>"
                                        data-estado="<?php echo $estado; ?>"
                                        class="opcion-<?php echo $estado; ?>">
                                        <?php echo $prefijo; ?>
                                        #<?php echo $v['movil']; ?>
                                        — <?php echo htmlspecialchars($v['marca'] . ' ' . $v['modelo']); ?>
                                        — <?php echo htmlspecialchars($v['dominio']); ?>
                                        <?php echo $sufijo; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">
                                🟢 Libre · 🟠 DÍA ocupado · 🟣 NOCHE ocupado · 🔴 Completo
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Turno</label>
                            <select name="turno" class="form-control">
                                <option value="">—</option>
                                <option value="DIA">DÍA</option>
                                <option value="NOCHE">NOCHE</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"></textarea>
                </div>
            </div>

            <div class="acciones">
                <a href="taxis.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar chofer</button>
            </div>
        </form>
    </div>
</body>

</html>