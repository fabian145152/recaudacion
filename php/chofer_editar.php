<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (!isset($_GET['q']) || !is_numeric($_GET['q'])) {
    die("ID de chofer inválido.");
}
$id = (int)$_GET['q'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre  = trim($_POST['nombre'] ?? '');
    $cel     = trim($_POST['cel'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $barrio  = trim($_POST['barrio'] ?? '');
    $dir     = trim($_POST['dir'] ?? '');
    $cp      = trim($_POST['cp'] ?? '');
    $activo  = isset($_POST['activo']) ? (int)$_POST['activo'] : 1;

    if (empty($nombre)) {
        die("<script>alert('El nombre es obligatorio.'); window.history.back();</script>");
    }

    // Foto
    $stmt = $con->prepare("SELECT foto FROM choferes WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $foto_actual = $stmt->get_result()->fetch_assoc()['foto'];
    $stmt->close();

    $foto_nombre = $foto_actual;
    if (!empty($_FILES['foto']['name'])) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $tipo = mime_content_type($_FILES['foto']['tmp_name']);
        if (in_array($tipo, $permitidos)) {
            $foto_nombre = uniqid('chofer_') . "_" . basename($_FILES["foto"]["name"]);
            if (move_uploaded_file($_FILES["foto"]["tmp_name"], "../images/" . $foto_nombre)) {
                if (!empty($foto_actual) && file_exists("../images/" . $foto_actual)) {
                    unlink("../images/" . $foto_actual);
                }
            }
        }
    }

    $stmt = $con->prepare("
        UPDATE choferes SET nombre=?, cel=?, email=?, barrio=?, dir=?, cp=?, foto=?, activo=?
        WHERE id=?
    ");
    $stmt->bind_param("sssssssii", $nombre, $cel, $email, $barrio, $dir, $cp, $foto_nombre, $activo, $id);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Chofer actualizado.'); window.location='taxis.php';</script>";
    } else {
        echo "<script>alert('❌ Error: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
    exit;
}

$stmt = $con->prepare("SELECT * FROM choferes WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) die("Chofer no encontrado.");
?>

<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar Chofer</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body { background: #f4f6fa; font-family: 'Segoe UI', Arial, sans-serif; }
        .page-wrapper { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .header-card {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            border-radius: 12px; padding: 20px 25px; color: white;
            margin-bottom: 20px; display: flex; align-items: center; gap: 15px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        }
        .header-card .icon-big {
            font-size: 2rem; width: 55px; height: 55px;
            display: flex; align-items: center; justify-content: center;
            background: rgba(255,255,255,0.2); border-radius: 12px;
        }
        .header-card h1 { font-size: 1.4rem; margin: 0; font-weight: 600; }
        .header-card p { margin: 3px 0 0 0; opacity: 0.95; font-size: 0.9rem; }
        .card-section {
            background: white; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            padding: 25px; margin-bottom: 20px;
        }
        .card-section h5 {
            color: #667eea; font-weight: 600;
            border-bottom: 2px solid #eef1f7;
            padding-bottom: 10px; margin-bottom: 18px; font-size: 1rem;
        }
        .foto-preview { text-align: center; margin-bottom: 20px; }
        .foto-preview img {
            width: 120px; height: 120px; object-fit: cover;
            border-radius: 50%; border: 4px solid #eef1f7;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .form-group label { font-weight: 600; font-size: 0.9rem; color: #555; }
        .form-control { padding: 10px 12px; border-radius: 8px; border: 1px solid #dfe6ef; }
        .acciones { display: flex; justify-content: flex-end; gap: 10px; }
        .btn-guardar {
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white; border: none; padding: 12px 28px;
            border-radius: 8px; font-weight: 600; cursor: pointer;
        }
        .btn-cancelar {
            background: #eef1f7; color: #555; padding: 12px 24px;
            border-radius: 8px; font-weight: 600; text-decoration: none;
        }
        .btn-cancelar:hover { background: #dde3ec; color: #333; text-decoration: none; }
    </style>
</head>
<body>

<?php include "menu.php"; ?>

<div class="page-wrapper">
    <div class="header-card">
        <div class="icon-big"><i class="fas fa-user-edit"></i></div>
        <div>
            <h1>Editar Chofer</h1>
            <p><?php echo htmlspecialchars($chofer['nombre']); ?></p>
        </div>
    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="card-section">
            <h5><i class="fas fa-user"></i> Datos personales</h5>

            <div class="foto-preview">
                <img src="../images/<?php echo !empty($chofer['foto']) ? htmlspecialchars($chofer['foto']) : 'sin-foto.png'; ?>" id="previewFoto">
                <br>
                <input type="file" name="foto" id="inputFoto" accept="image/*" style="display:none;" onchange="preview(event)">
                <button type="button" class="btn btn-primary btn-sm mt-2" onclick="document.getElementById('inputFoto').click()">
                    <i class="fas fa-camera"></i> Cambiar foto
                </button>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Nombre <span style="color:#dc3545;">*</span></label>
                        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($chofer['nombre']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="cel" class="form-control" value="<?php echo htmlspecialchars($chofer['cel']); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($chofer['email']); ?>">
            </div>

            <div class="row">
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Barrio</label>
                        <input type="text" name="barrio" class="form-control" value="<?php echo htmlspecialchars($chofer['barrio']); ?>">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label>Dirección</label>
                        <input type="text" name="dir" class="form-control" value="<?php echo htmlspecialchars($chofer['dir']); ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label>CP</label>
                        <input type="text" name="cp" class="form-control" value="<?php echo htmlspecialchars($chofer['cp']); ?>">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="activo" class="form-control">
                    <option value="1" <?php echo $chofer['activo'] ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo !$chofer['activo'] ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="acciones">
            <a href="taxis.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
            <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar cambios</button>
        </div>
    </form>
</div>

<script>
    function preview(e) {
        const file = e.target.files[0];
        if (file) document.getElementById('previewFoto').src = URL.createObjectURL(file);
    }
</script>
</body>
</html>