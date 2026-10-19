<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$id = $_GET['q'] ?? null;
if (!$id || !is_numeric($id)) {
    die("ID inválido");
}

$stmt = $con->prepare("SELECT * FROM moviles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Unidad no encontrada");
}

$row = $result->fetch_assoc();
$stmt->close();

$foto_dir = "../images/";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id        = (int)$_POST['id'];
    $turno     = $_POST['turno']; // solo lectura (viene readonly, pero igual lo usamos)
    $nombre    = trim($_POST['nombre']);
    $email     = trim($_POST['email']);
    $cel       = trim($_POST['cel']);
    $barrio    = trim($_POST['barrio']);
    $dir       = trim($_POST['dir']);
    $cp        = trim($_POST['cp']);
    $tipo      = $_POST['tipo'];
    $movil     = (int)$_POST['movil'];
    $marca     = strtoupper(trim($_POST['marca']));
    $modelo    = strtoupper(trim($_POST['modelo']));
    $dominio   = strtoupper(trim($_POST['dominio']));
    $color     = strtoupper(trim($_POST['color']));

    // Si el nombre queda vacío, lo dejamos como LIBRE
    if ($nombre === '') {
        $nombre = 'LIBRE';
    }

    // --- Foto del chofer ---
    $foto_chofer = $row['foto_chof'];
    if (!empty($_FILES['foto_chofer']['name'])) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
        $tipoMime = mime_content_type($_FILES['foto_chofer']['tmp_name']);
        if (in_array($tipoMime, $permitidos)) {
            $nombreArchivo = uniqid('chofer_') . "_" . basename($_FILES["foto_chofer"]["name"]);
            $rutaDestino = $foto_dir . $nombreArchivo;
            if (move_uploaded_file($_FILES["foto_chofer"]["tmp_name"], $rutaDestino)) {
                if (!empty($row['foto_chof']) && file_exists($foto_dir . $row['foto_chof'])) {
                    unlink($foto_dir . $row['foto_chof']);
                }
                $foto_chofer = $nombreArchivo;
            }
        }
    }

    // --- Actualizar base de datos ---
    $sql = "UPDATE moviles SET 
        turno = ?, nombre = ?, email = ?, cel = ?, barrio = ?, dir = ?, cp = ?, 
        tipo = ?, movil = ?, marca = ?, modelo = ?, dominio = ?, color = ?, foto_chof = ?
        WHERE id = ?";

    $stmt = $con->prepare($sql);
    $stmt->bind_param(
        "ssssssssisssssi",
        $turno,
        $nombre,
        $email,
        $cel,
        $barrio,
        $dir,
        $cp,
        $tipo,
        $movil,
        $marca,
        $modelo,
        $dominio,
        $color,
        $foto_chofer,
        $id
    );

    if ($stmt->execute()) {
        echo "<script>alert('✅ Registro actualizado correctamente'); window.location='taxis.php';</script>";
    } else {
        echo "<script>alert('❌ Error al actualizar: " . $stmt->error . "');</script>";
    }
    $stmt->close();
    exit;
}

// Determinar si el chofer está LIBRE
$esLibre = (empty($row['nombre']) || strtoupper($row['nombre']) === 'LIBRE');
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Turno - Móvil <?php echo htmlspecialchars($row['movil']); ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background-color: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .page-container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #666;
            font-size: 0.95rem;
        }

        .badge-turno {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            color: white;
            margin-left: 8px;
        }

        .badge-dia {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .badge-noche {
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }

        .badge-libre {
            background: #6c757d;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.75rem;
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
            margin-bottom: 20px;
        }

        .foto-preview {
            text-align: center;
            margin-bottom: 20px;
        }

        .foto-preview img {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #eef1f7;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .foto-preview .btn-cambiar {
            display: block;
            margin: 10px auto 0;
        }

        .form-group label {
            font-weight: 500;
            font-size: 0.9rem;
            color: #555;
        }

        .form-control:read-only {
            background-color: #eef1f7;
            color: #888;
        }

        .acciones {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .btn-guardar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-guardar:hover {
            background: linear-gradient(135deg, #218838, #17a689);
            color: white;
        }

        .btn-cancelar {
            background: #eef1f7;
            color: #555;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }

        .btn-cancelar:hover {
            background: #dde3ec;
            color: #333;
            text-decoration: none;
        }
    </style>
</head>

<body>

    <!-- MENÚ COMÚN -->
    <?php include "menu.php"; ?>

    <div class="page-container">

        <div class="page-header">
            <h2>
                Editando Móvil <strong><?php echo htmlspecialchars($row['movil']); ?></strong>
                <span class="badge-turno <?php echo ($row['turno'] === 'DIA') ? 'badge-dia' : 'badge-noche'; ?>">
                    <?php echo htmlspecialchars($row['turno']); ?>
                </span>
            </h2>
            <p>
                <?php if ($esLibre): ?>
                    <span class="badge-libre">LIBRE</span> Este turno no tiene chofer asignado aún.
                <?php else: ?>
                    Chofer actual: <strong><?php echo htmlspecialchars($row['nombre']); ?></strong>
                <?php endif; ?>
            </p>
        </div>

        <form method="POST" action="" enctype="multipart/form-data">

            <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
            <input type="hidden" name="turno" value="<?php echo htmlspecialchars($row['turno']); ?>">
            <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($row['tipo']); ?>">
            <input type="hidden" name="movil" value="<?php echo htmlspecialchars($row['movil']); ?>">

            <div class="row">

                <!-- COLUMNA 1: CHOFER -->
                <div class="col-md-6">
                    <div class="card-section">
                        <h5><i class="fas fa-user"></i> Datos del Chofer</h5>

                        <div class="foto-preview">
                            <img id="previewChofer"
                                src="../images/<?php echo !empty($row['foto_chof']) ? htmlspecialchars($row['foto_chof']) : 'sin-foto.png'; ?>"
                                alt="Foto del chofer">
                            <input type="file" id="inputFotoChofer" name="foto_chofer" accept="image/*" style="display:none;" onchange="previewFoto(event,'previewChofer')">
                            <button type="button" class="btn btn-primary btn-sm btn-cambiar" onclick="document.getElementById('inputFotoChofer').click();">
                                <i class="fas fa-camera"></i> Cambiar foto
                            </button>
                        </div>

                        <div class="form-group">
                            <label>Nombre del chofer</label>
                            <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($row['nombre']); ?>" placeholder="Ej: AUGUSTO, o dejar vacío para LIBRE">
                            <small class="text-muted">Si lo dejas vacío, el turno se marcará como <strong>LIBRE</strong>.</small>
                        </div>

                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($row['email']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" class="form-control" name="cel" value="<?php echo htmlspecialchars($row['cel']); ?>">
                        </div>

                        <h5 class="mt-4"><i class="fas fa-map-marker-alt"></i> Dirección</h5>

                        <div class="form-group">
                            <label>Barrio</label>
                            <input type="text" class="form-control" name="barrio" value="<?php echo htmlspecialchars($row['barrio']); ?>">
                        </div>

                        <div class="form-group">
                            <label>Dirección</label>
                            <input type="text" class="form-control" name="dir" value="<?php echo htmlspecialchars($row['dir']); ?>">
                        </div>

                        <div class="form-group">
                            <label>CP</label>
                            <input type="text" class="form-control" name="cp" value="<?php echo htmlspecialchars($row['cp']); ?>">
                        </div>
                    </div>
                </div>

                <!-- COLUMNA 2: VEHÍCULO -->
                <div class="col-md-6">
                    <div class="card-section">
                        <h5><i class="fas fa-taxi"></i> Datos del Vehículo</h5>

                        <div class="form-group">
                            <label>Móvil</label>
                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($row['movil']); ?>" readonly>
                            <small class="text-muted">Para cambiar el número de móvil, editá ambos turnos desde la base de datos.</small>
                        </div>

                        <div class="form-group">
                            <label>Marca</label>
                            <input type="text" class="form-control" name="marca" value="<?php echo htmlspecialchars($row['marca']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Modelo</label>
                            <input type="text" class="form-control" name="modelo" value="<?php echo htmlspecialchars($row['modelo']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Dominio</label>
                            <input type="text" class="form-control" name="dominio" value="<?php echo htmlspecialchars($row['dominio']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Color</label>
                            <input type="text" class="form-control" name="color" value="<?php echo htmlspecialchars($row['color']); ?>" required>
                        </div>

                        <div class="alert alert-info mt-3" style="font-size:0.85rem;">
                            <i class="fas fa-info-circle"></i>
                            Al guardar, los datos del <strong>vehículo</strong> se actualizarán <strong>solo en este turno</strong>.
                            Si querés que ambos turnos tengan los mismos datos del vehículo, editá los dos por separado.
                        </div>
                    </div>
                </div>

            </div>

            <div class="acciones">
                <a href="taxis.php" class="btn-cancelar">
                    <i class="fas fa-times"></i> Cancelar
                </a>
                <button type="submit" class="btn-guardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>

        </form>
    </div>

    <script>
        function previewFoto(event, idPreview) {
            const file = event.target.files[0];
            if (file) {
                const preview = document.getElementById(idPreview);
                preview.src = URL.createObjectURL(file);
            }
        }
    </script>

</body>

</html>