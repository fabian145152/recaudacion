<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de vehículo inválido.");
}
$id = (int)$_GET['q'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $movil   = (int)($_POST['movil'] ?? 0);
    $marca   = strtoupper(trim($_POST['marca'] ?? ''));
    $modelo  = strtoupper(trim($_POST['modelo'] ?? ''));
    $dominio = strtoupper(trim($_POST['dominio'] ?? ''));
    $tipo    = $_POST['tipo'] ?? 'TAXI';

    if ($movil <= 0 || $movil > 9999) {
        die("<script>alert('El número de móvil debe ser entre 1 y 9999.'); window.history.back();</script>");
    }
    if (empty($marca) || empty($modelo) || empty($dominio)) {
        die("<script>alert('Marca, modelo y dominio son obligatorios.'); window.history.back();</script>");
    }

    // Verificar que el móvil no esté en uso por otro vehículo
    $check = $con->prepare("SELECT id FROM vehiculos WHERE movil = ? AND id != ?");
    $check->bind_param("ii", $movil, $id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        die("<script>alert('Ya existe otro vehículo con el móvil $movil.'); window.history.back();</script>");
    }
    $check->close();

    $stmt = $con->prepare("UPDATE vehiculos SET movil=?, marca=?, modelo=?, dominio=?, tipo=? WHERE id=?");
    $stmt->bind_param("issssi", $movil, $marca, $modelo, $dominio, $tipo, $id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Vehículo actualizado.'); window.location='vehiculo_lista.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . addslashes($stmt->error) . "'); window.history.back();</script>";
    }
    $stmt->close();
    exit;
}

$stmt = $con->prepare("SELECT * FROM vehiculos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$vehiculo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$vehiculo) die("Vehículo no encontrado.");

// Saber si está asignado (para mostrar aviso)
$stmt = $con->prepare("SELECT COUNT(*) AS cant FROM asignaciones_auto WHERE vehiculo_id = ? AND activa = 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$asignaciones_activas = (int)$stmt->get_result()->fetch_assoc()['cant'];
$stmt->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Editar Vehículo</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 700px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .header-card {
            background: linear-gradient(135deg, #2196F3, #1976D2);
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

        .aviso-asignado {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
            font-size: 0.88rem;
            display: flex;
            align-items: center;
            gap: 10px;
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
            background: linear-gradient(135deg, #2196F3, #1976D2);
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
            <div class="icon-big"><i class="fas fa-car-side"></i></div>
            <div>
                <h1>Editar Vehículo</h1>
                <p>Móvil #<?php echo htmlspecialchars($vehiculo['movil']); ?> — <?php echo htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']); ?></p>
            </div>
        </div>

        <?php if ($asignaciones_activas > 0): ?>
            <div class="aviso-asignado">
                <i class="fas fa-info-circle"></i>
                <span>
                    Este vehículo tiene <strong><?php echo $asignaciones_activas; ?> turno(s) asignado(s)</strong> actualmente.
                    Los cambios en el número de móvil se reflejarán en el listado de choferes.
                </span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="card-section">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Número de móvil <span style="color:#dc3545;">*</span></label>
                            <input type="number" name="movil" class="form-control" min="1" max="9999" value="<?php echo htmlspecialchars($vehiculo['movil']); ?>" required>
                            <small class="text-muted">Podés cambiarlo mientras no exista otro vehículo con ese número.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Marca <span style="color:#dc3545;">*</span></label>
                            <input type="text" name="marca" class="form-control" value="<?php echo htmlspecialchars($vehiculo['marca']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Modelo <span style="color:#dc3545;">*</span></label>
                            <input type="text" name="modelo" class="form-control" value="<?php echo htmlspecialchars($vehiculo['modelo']); ?>" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dominio <span style="color:#dc3545;">*</span></label>
                            <input type="text" name="dominio" class="form-control" value="<?php echo htmlspecialchars($vehiculo['dominio']); ?>" required style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select name="tipo" class="form-control">
                                <option value="TAXI" <?php echo ($vehiculo['tipo'] === 'TAXI') ? 'selected' : ''; ?>>TAXI</option>
                                <option value="REMIS" <?php echo ($vehiculo['tipo'] === 'REMIS') ? 'selected' : ''; ?>>REMIS</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="acciones">
                <a href="vehiculo_lista.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar cambios</button>
            </div>
        </form>
    </div>
</body>

</html>