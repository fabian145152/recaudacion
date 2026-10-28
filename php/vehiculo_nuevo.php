<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $movil   = (int)($_POST['movil'] ?? 0);
    $marca   = strtoupper(trim($_POST['marca'] ?? ''));
    $modelo  = strtoupper(trim($_POST['modelo'] ?? ''));
    $dominio = strtoupper(trim($_POST['dominio'] ?? ''));
    $tipo    = $_POST['tipo'] ?? 'TAXI';

    if ($movil <= 0 || $movil > 9999) {
        header("Location: vehiculo_nuevo.php?msg=err_movil");
        exit;
    }
    if (empty($marca) || empty($modelo) || empty($dominio)) {
        header("Location: vehiculo_nuevo.php?msg=err_campos");
        exit;
    }

    // Verificar que el móvil no exista
    $check = $con->prepare("SELECT id FROM vehiculos WHERE movil = ?");
    $check->bind_param("i", $movil);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $check->close();
        header("Location: vehiculo_nuevo.php?msg=err_duplicado&movil=" . urlencode($movil));
        exit;
    }
    $check->close();

    $stmt = $con->prepare("INSERT INTO vehiculos (movil, marca, modelo, dominio, tipo) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $movil, $marca, $modelo, $dominio, $tipo);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: vehiculo_lista.php?ok=creado&movil=" . urlencode($movil));
        exit;
    } else {
        $err = addslashes($stmt->error);
        $stmt->close();
        header("Location: vehiculo_nuevo.php?msg=err_db&detalle=" . urlencode($err));
        exit;
    }
}

// Mensaje de error si viene por GET
$mensaje_error = '';
if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'err_movil':
            $mensaje_error = 'El número de móvil debe estar entre 1 y 9999.';
            break;
        case 'err_campos':
            $mensaje_error = 'Marca, modelo y dominio son obligatorios.';
            break;
        case 'err_duplicado':
            $mensaje_error = 'Ya existe un vehículo con el móvil #' . htmlspecialchars($_GET['movil'] ?? '');
            break;
        case 'err_db':
            $mensaje_error = 'Error en la base de datos: ' . htmlspecialchars($_GET['detalle'] ?? '');
            break;
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Nuevo Vehículo</title>
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
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
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

        .alert-error {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
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
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
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

        /* Modal de confirmación */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.show {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 14px;
            max-width: 480px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: modalIn 0.25s ease;
        }

        @keyframes modalIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
            padding: 18px 25px;
            font-size: 1.1rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modal-body {
            padding: 25px;
        }

        .dato-confirmacion {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px dashed #eef1f7;
            font-size: 0.95rem;
        }

        .dato-confirmacion:last-child {
            border-bottom: none;
        }

        .dato-confirmacion .etiqueta {
            color: #888;
            font-weight: 500;
        }

        .dato-confirmacion .valor {
            color: #222;
            font-weight: 700;
            text-align: right;
        }

        .dato-confirmacion .valor.movil {
            color: #17a2b8;
            font-size: 1.15rem;
        }

        .modal-footer {
            padding: 15px 25px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            background: #f8f9fc;
        }

        .btn-confirmar {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-confirmar:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-cancelar-modal {
            background: #eef1f7;
            color: #555;
            border: none;
            padding: 10px 22px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-cancelar-modal:hover {
            background: #dde3ec;
            color: #333;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <div class="header-card">
            <div class="icon-big"><i class="fas fa-car-side"></i></div>
            <div>
                <h1>Nuevo Vehículo</h1>
                <p style="margin: 3px 0 0 0; opacity: 0.95; font-size: 0.9rem;">Registrar un nuevo taxi con su número de móvil</p>
            </div>
        </div>

        <?php if (!empty($mensaje_error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span><?php echo $mensaje_error; ?></span>
            </div>
        <?php endif; ?>

        <form id="formVehiculo" method="POST" onsubmit="return confirmarDatos(event)">
            <div class="card-section">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Número de móvil <span style="color:#dc3545;">*</span></label>
                            <input type="number" id="movil" name="movil" class="form-control" min="1" max="9999" required>
                            <small class="text-muted">Número único, no se puede cambiar después.</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Marca <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="marca" name="marca" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Modelo <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="modelo" name="modelo" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Dominio <span style="color:#dc3545;">*</span></label>
                            <input type="text" id="dominio" name="dominio" class="form-control" required style="text-transform:uppercase;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Tipo</label>
                            <select id="tipo" name="tipo" class="form-control">
                                <option value="TAXI">TAXI</option>
                                <option value="REMIS">REMIS</option>
                                <option value="ALQUILER">AUTO DE ALQUILER</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="acciones">
                <a href="vehiculo_lista.php" class="btn-cancelar"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar vehículo</button>
            </div>
        </form>
    </div>

    <!-- MODAL DE CONFIRMACIÓN -->
    <div class="modal-overlay" id="modalConfirmacion">
        <div class="modal-box">
            <div class="modal-header">
                <i class="fas fa-check-circle"></i>
                Confirmar datos del vehículo
            </div>
            <div class="modal-body">
                <p style="color:#666; font-size:0.9rem; margin-bottom:15px;">
                    Verificá que los datos sean correctos antes de guardar:
                </p>

                <div class="dato-confirmacion">
                    <span class="etiqueta">Móvil:</span>
                    <span class="valor movil" id="conf_movil">—</span>
                </div>
                <div class="dato-confirmacion">
                    <span class="etiqueta">Marca:</span>
                    <span class="valor" id="conf_marca">—</span>
                </div>
                <div class="dato-confirmacion">
                    <span class="etiqueta">Modelo:</span>
                    <span class="valor" id="conf_modelo">—</span>
                </div>
                <div class="dato-confirmacion">
                    <span class="etiqueta">Dominio:</span>
                    <span class="valor" id="conf_dominio">—</span>
                </div>
                <div class="dato-confirmacion">
                    <span class="etiqueta">Tipo:</span>
                    <span class="valor" id="conf_tipo">—</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar-modal" onclick="cerrarModal()">
                    <i class="fas fa-arrow-left"></i> Volver a editar
                </button>
                <button type="button" class="btn-confirmar" onclick="enviarFormulario()">
                    <i class="fas fa-check"></i> Confirmar y guardar
                </button>
            </div>
        </div>
    </div>

    <script>
        let formularioListo = false;

        function confirmarDatos(event) {
            if (formularioListo) return true;
            event.preventDefault();

            const form = document.getElementById('formVehiculo');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            document.getElementById('conf_movil').textContent = '#' + document.getElementById('movil').value;
            document.getElementById('conf_marca').textContent = document.getElementById('marca').value.toUpperCase();
            document.getElementById('conf_modelo').textContent = document.getElementById('modelo').value.toUpperCase();
            document.getElementById('conf_dominio').textContent = document.getElementById('dominio').value.toUpperCase();

            // Mostrar el texto amigable si es ALQUILER
            const tipoVal = document.getElementById('tipo').value;
            document.getElementById('conf_tipo').textContent = (tipoVal === 'ALQUILER') ? 'AUTO DE ALQUILER' : tipoVal;

            document.getElementById('modalConfirmacion').classList.add('show');
            return false;
        }

        function enviarFormulario() {
            formularioListo = true;
            document.getElementById('formVehiculo').submit();
        }

        function cerrarModal() {
            document.getElementById('modalConfirmacion').classList.remove('show');
        }

        document.getElementById('modalConfirmacion').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModal();
        });

        // === BLOQUEAR LA RUEDA DEL MOUSE EN LOS INPUTS NUMÉRICOS ===
        document.querySelectorAll('input[type=number]').forEach(function(input) {
            input.addEventListener('wheel', function(e) {
                e.preventDefault();
            }, {
                passive: false
            });
        });
    </script>

</body>

</html>