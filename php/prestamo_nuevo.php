<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$choferes = $con->query("SELECT id, nombre FROM choferes WHERE activo = 1 ORDER BY nombre ASC")->fetch_all(MYSQLI_ASSOC);

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chofer_id = (int)$_POST['chofer_id'];
    $monto = floatval($_POST['monto']);
    $interes = floatval($_POST['interes']);
    $cuotas = (int)$_POST['cuotas'];
    $fecha = $_POST['fecha'];
    $descripcion = trim($_POST['descripcion']);

    $calculo_auto = isset($_POST['calculo_auto']) ? true : false;

    // Cálculos
    if ($calculo_auto) {
        $monto_interes = $monto * ($interes / 100);
        $monto_total = $monto + $monto_interes;
        $valor_cuota = ($cuotas > 0) ? ($monto_total / $cuotas) : 0;
    } else {
        $valor_cuota = floatval($_POST['valor_cuota_manual']);
        $monto_total = $valor_cuota * $cuotas;
    }

    if ($chofer_id > 0 && $monto > 0 && $cuotas > 0 && !empty($fecha) && $valor_cuota > 0) {
        $stmt = $con->prepare("INSERT INTO prestamos (chofer_id, monto, interes, monto_total, cuotas, valor_cuota, fecha, descripcion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        // CORREGIDO: idididss (sin espacios)
        $stmt->bind_param("idididss", $chofer_id, $monto, $interes, $monto_total, $cuotas, $valor_cuota, $fecha, $descripcion);

        if ($stmt->execute()) {
            echo "<script>window.location='prestamos.php';</script>";
            exit;
        } else {
            $mensaje = "<div class='alert alert-danger'>Error al guardar el préstamo.</div>";
        }
    } else {
        $mensaje = "<div class='alert alert-warning'>Completá todos los campos obligatorios.</div>";
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nuevo Préstamo</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        .page-wrapper {
            max-width: 650px;
            margin: 20px auto;
            padding: 0 15px;
        }

        .card-form {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .page-title {
            margin: 0 0 20px 0;
            font-size: 1.2rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #555;
        }

        .form-control,
        .form-select {
            font-size: 0.9rem;
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #dfe6ef;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #e83e8c;
            box-shadow: 0 0 0 3px rgba(232, 62, 140, 0.15);
        }

        .btn-guardar {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .btn-guardar:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(232, 62, 140, 0.3);
        }

        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            color: #888;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 15px;
            transition: color 0.2s;
        }

        .btn-volver:hover {
            color: #333;
        }

        .panel-calculo {
            background: #f8f9fc;
            border-left: 4px solid #fd7e14;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .panel-calculo .fila {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            margin-bottom: 5px;
            color: #555;
        }

        .panel-calculo .fila.total {
            font-weight: 700;
            font-size: 1.1rem;
            color: #e83e8c;
            border-top: 1px dashed #ccc;
            padding-top: 8px;
            margin-top: 8px;
        }

        .switch-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #eef1f7;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .switch-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #e83e8c;
        }

        .switch-container label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            cursor: pointer;
        }

        .campo-manual {
            display: none;
        }

        /* Oculto por defecto */
    </style>
</head>

<body>

    <div class="page-wrapper">
        <a href="prestamos.php" class="btn-volver"><i class="fas fa-arrow-left"></i> Volver al listado</a>

        <div class="card-form">
            <h1 class="page-title"><i class="fas fa-plus-circle"></i> Registrar Nuevo Préstamo</h1>

            <?php echo $mensaje; ?>

            <form method="POST" action="" id="form-prestamo">
                <div class="mb-3">
                    <label class="form-label">Chofer</label>
                    <select name="chofer_id" class="form-select" required>
                        <option value="">Seleccione un chofer...</option>
                        <?php foreach ($choferes as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Monto Prestado ($)</label>
                        <input type="number" id="monto" name="monto" class="form-control" step="0.01" min="1" placeholder="0.00" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Interés (%)</label>
                        <input type="number" id="interes" name="interes" class="form-control" step="0.01" min="0" value="0" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cantidad de Cuotas</label>
                        <input type="number" id="cuotas" name="cuotas" class="form-control" min="1" value="1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha del préstamo</label>
                        <input type="date" name="fecha" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>

                <!-- SWITCH PARA CÁLCULO AUTOMÁTICO -->
                <div class="switch-container">
                    <input type="checkbox" id="calculo_auto" name="calculo_auto" checked>
                    <label for="calculo_auto">Calcular valor de cuota automáticamente</label>
                </div>

                <!-- CAMPO MANUAL (Oculto si está activado el auto) -->
                <div class="mb-3 campo-manual" id="div-cuota-manual">
                    <label class="form-label" style="color: #e83e8c; font-weight: 700;">Valor de la Cuota (Manual) ($)</label>
                    <input type="number" id="valor_cuota_manual" name="valor_cuota_manual" class="form-control" step="0.01" min="1" placeholder="0.00" style="border-color: #e83e8c;">
                </div>

                <!-- PANEL DE CÁLCULO -->
                <div class="panel-calculo" id="panel-calculo">
                    <div class="fila">
                        <span>Monto base:</span>
                        <span id="lbl_monto">$0,00</span>
                    </div>
                    <div class="fila">
                        <span>Intereses:</span>
                        <span id="lbl_interes">$0,00</span>
                    </div>
                    <div class="fila total">
                        <span>Monto Total a Pagar:</span>
                        <span id="lbl_total">$0,00</span>
                    </div>
                    <div class="fila total" style="color:#667eea; font-size:1rem;">
                        <span>Valor de la cuota:</span>
                        <span id="lbl_cuota">$0,00</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label">Descripción / Motivo (opcional)</label>
                    <input type="text" name="descripcion" class="form-control" placeholder="Ej: Arreglo de motor...">
                </div>

                <button type="submit" class="btn-guardar"><i class="fas fa-save"></i> Guardar Préstamo</button>
            </form>
        </div>
    </div>

    <script>
        const inputMonto = document.getElementById('monto');
        const inputInteres = document.getElementById('interes');
        const inputCuotas = document.getElementById('cuotas');
        const checkAuto = document.getElementById('calculo_auto');

        const divManual = document.getElementById('div-cuota-manual');
        const inputManual = document.getElementById('valor_cuota_manual');

        const lblMonto = document.getElementById('lbl_monto');
        const lblInteres = document.getElementById('lbl_interes');
        const lblTotal = document.getElementById('lbl_total');
        const lblCuota = document.getElementById('lbl_cuota');

        function formatMoneda(valor) {
            return '$' + valor.toLocaleString('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcular() {
            const monto = parseFloat(inputMonto.value) || 0;
            const interes = parseFloat(inputInteres.value) || 0;
            const cuotas = parseInt(inputCuotas.value) || 1;

            if (checkAuto.checked) {
                // CÁLCULO AUTOMÁTICO
                const montoInteres = monto * (interes / 100);
                const total = monto + montoInteres;
                const valorCuota = total / cuotas;

                lblMonto.textContent = formatMoneda(monto);
                lblInteres.textContent = formatMoneda(montoInteres);
                lblTotal.textContent = formatMoneda(total);
                lblCuota.textContent = formatMoneda(valorCuota);
            } else {
                // CÁLCULO MANUAL
                const cuotaManual = parseFloat(inputManual.value) || 0;
                const total = cuotaManual * cuotas;

                lblMonto.textContent = formatMoneda(monto);
                lblInteres.textContent = "---"; // Ocultamos interés porque es manual
                lblTotal.textContent = formatMoneda(total);
                lblCuota.textContent = formatMoneda(cuotaManual);
            }
        }

        // Evento para mostrar/ocultar el campo manual
        checkAuto.addEventListener('change', function() {
            if (this.checked) {
                divManual.style.display = 'none';
                inputManual.removeAttribute('required');
                inputManual.value = ''; // Limpiamos
            } else {
                divManual.style.display = 'block';
                inputManual.setAttribute('required', 'required');
            }
            calcular();
        });

        // Escuchar cambios en los inputs
        inputMonto.addEventListener('input', calcular);
        inputInteres.addEventListener('input', calcular);
        inputCuotas.addEventListener('input', calcular);
        inputManual.addEventListener('input', calcular);
    </script>

</body>

</html>