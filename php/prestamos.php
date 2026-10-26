<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if (isset($_GET['mora']) && isset($_GET['id'])) {
    $id_mora = (int)$_GET['id'];
    $con->query("UPDATE prestamos SET estado_mora = 'EN MORA' WHERE id = $id_mora");
    echo "<script>window.location='prestamos.php';</script>";
    exit;
}

if (isset($_GET['archivar']) && isset($_GET['id'])) {
    $id_archivar = (int)$_GET['id'];
    $check = $con->query("SELECT estado FROM prestamos WHERE id = $id_archivar")->fetch_assoc();
    if ($check && $check['estado'] === 'PAGADO') {
        $con->query("UPDATE prestamos SET estado = 'ARCHIVADO' WHERE id = $id_archivar");
    }
    echo "<script>window.location='prestamos.php';</script>";
    exit;
}

$ver_historial = isset($_GET['historial']) && $_GET['historial'] == '1';

if ($ver_historial) {
    $where_estado = "p.estado IN ('PAGADO', 'CANCELADO', 'ARCHIVADO')";
} else {
    $where_estado = "p.estado IN ('ACTIVO', 'PAGADO')";
}

$sql = "
    SELECT 
        p.*,
        c.nombre AS chofer_nombre,
        c.foto AS chofer_foto,
        COALESCE((SELECT SUM(monto) FROM prestamos_pagos WHERE prestamo_id = p.id), 0) AS total_pagado
    FROM prestamos p
    INNER JOIN choferes c ON c.id = p.chofer_id
    WHERE $where_estado
    ORDER BY p.fecha DESC, p.id DESC
";
$prestamos = $con->query($sql)->fetch_all(MYSQLI_ASSOC);

$total_activos = 0;
$monto_total = 0;
$en_mora = 0;

if (!$ver_historial) {
    foreach ($prestamos as $p) {
        if ($p['estado'] === 'ACTIVO') {
            $total_activos++;
            $saldo_restante = floatval($p['monto_total']) - floatval($p['total_pagado']);
            $monto_total += max(0, $saldo_restante);
            if ($p['estado_mora'] === 'EN MORA') $en_mora++;
        }
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Préstamos y Deudas</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 10px 15px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 15px 20px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.06);
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .page-title {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-botones {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .btn-nuevo {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-nuevo:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(232, 62, 140, 0.3);
        }

        .btn-filtro {
            background: #eef1f7;
            color: #555;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            border: 1px solid #dfe6ef;
        }

        .btn-filtro:hover {
            background: #dfe6ef;
            color: #333;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .stat-icon {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .stat-icon.naranja {
            background: linear-gradient(135deg, #fd7e14, #e83e8c);
        }

        .stat-icon.verde {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .stat-icon.rojo {
            background: linear-gradient(135deg, #dc3545, #c82333);
        }

        .stat-data {
            display: flex;
            flex-direction: column;
        }

        .stat-numero {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #888;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .prestamos-lista {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .prestamo-card {
            background: white;
            border-radius: 10px;
            padding: 12px 15px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 4px solid #fd7e14;
            transition: all 0.2s;
            position: relative;
            flex-direction: column;
        }

        .card-fila-principal {
            display: flex;
            align-items: center;
            gap: 15px;
            width: 100%;
        }

        .prestamo-card:hover {
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
        }

        .prestamo-card.pagado {
            border-left-color: #28a745;
            background: #f8fdf9;
        }

        .prestamo-card.archivado {
            border-left-color: #6c757d;
            opacity: 0.8;
            background: #f8f9fa;
        }

        .prestamo-card.mora {
            border-left-color: #dc3545;
            background: #fff9f9;
        }

        .prestamo-foto img,
        .prestamo-foto .sin-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #eef1f7;
        }

        .prestamo-foto .sin-foto {
            background: #eef1f7;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
        }

        .prestamo-info {
            flex: 1;
            min-width: 0;
        }

        .prestamo-chofer {
            font-weight: 700;
            font-size: 0.95rem;
            color: #333;
            margin-bottom: 2px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .prestamo-desc {
            font-size: 0.78rem;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .prestamo-obs {
            font-size: 0.75rem;
            color: #0c5460;
            background: #e2f1f5;
            padding: 2px 6px;
            border-radius: 4px;
            display: inline-block;
            margin-top: 3px;
        }

        .prestamo-monto {
            text-align: right;
            min-width: 110px;
            border-right: 1px solid #eee;
            padding-right: 15px;
            margin-right: 5px;
        }

        .prestamo-monto .monto {
            font-size: 1.1rem;
            font-weight: 700;
            color: #e83e8c;
        }

        .prestamo-monto .fecha {
            font-size: 0.7rem;
            color: #888;
        }

        .prestamo-detalles {
            text-align: right;
            min-width: 120px;
        }

        .detalle-fila {
            font-size: 0.75rem;
            color: #666;
            margin-bottom: 2px;
        }

        .detalle-fila span {
            font-weight: 700;
            color: #333;
        }

        .progreso-container {
            margin-top: 5px;
            width: 100%;
            height: 6px;
            background: #eef1f7;
            border-radius: 3px;
            overflow: hidden;
        }

        .progreso-barra {
            height: 100%;
            background: #fd7e14;
            transition: width 0.3s;
        }

        .progreso-barra.completo {
            background: #28a745;
        }

        .badge-estado {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-activo {
            background: #cce5ff;
            color: #004085;
        }

        .badge-pagado {
            background: #d4edda;
            color: #155724;
        }

        .badge-cancelado,
        .badge-archivado {
            background: #e2e3e5;
            color: #383d41;
        }

        .badge-mora {
            background: #f8d7da;
            color: #721c24;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }

            100% {
                opacity: 1;
            }
        }

        .acciones-botones {
            display: flex;
            gap: 5px;
            margin-left: 10px;
            align-items: center;
        }

        .btn-accion {
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 3px;
            transition: all 0.2s;
        }

        .btn-pagar {
            background: #28a745;
            color: white;
        }

        .btn-pagar:hover {
            background: #218838;
        }

        .btn-mora {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .btn-mora:hover {
            background: #ffeeba;
        }

        .btn-archivar {
            background: #6c757d;
            color: white;
        }

        .btn-archivar:hover {
            background: #5a6268;
        }

        .btn-historial-pagos {
            background: #17a2b8;
            color: white;
        }

        .btn-historial-pagos:hover {
            background: #138496;
        }

        .pagos-lista {
            width: 100%;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            display: none;
        }

        .pagos-lista.activo {
            display: block;
        }

        .pago-fila {
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            padding: 4px 8px;
            background: #f8f9fc;
            border-radius: 4px;
            margin-bottom: 4px;
            color: #555;
        }

        .pago-fila .fecha-pago {
            font-weight: 700;
            color: #17a2b8;
        }

        .pago-fila .monto-pago {
            font-weight: 700;
            color: #28a745;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            color: #888;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-file-invoice-dollar"></i>
                <?php echo $ver_historial ? 'Historial de Préstamos' : 'Préstamos y Deudas'; ?>
            </h1>
            <div class="header-botones">
                <?php if ($ver_historial): ?>
                    <a href="prestamos.php" class="btn-filtro"><i class="fas fa-arrow-left"></i> Volver a Activos</a>
                <?php else: ?>
                    <a href="prestamos.php?historial=1" class="btn-filtro"><i class="fas fa-history"></i> Ver Historial</a>
                    <a href="prestamo_nuevo.php" class="btn-nuevo"><i class="fas fa-plus"></i> Nuevo Préstamo</a>
                <?php endif; ?>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'pagado'): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-left: 4px solid #28a745; box-shadow: 0 2px 6px rgba(0,0,0,0.05);">
                <i class="fas fa-check-circle"></i> <b>¡Pago registrado con éxito!</b>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!$ver_historial): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon naranja"><i class="fas fa-hand-holding-usd"></i></div>
                    <div class="stat-data">
                        <span class="stat-numero"><?php echo $total_activos; ?></span>
                        <span class="stat-label">Préstamos Activos</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon verde"><i class="fas fa-money-bill-wave"></i></div>
                    <div class="stat-data">
                        <span class="stat-numero">$<?php echo number_format($monto_total, 0, ',', '.'); ?></span>
                        <span class="stat-label">Saldo x Cobrar</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon rojo"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="stat-data">
                        <span class="stat-numero"><?php echo $en_mora; ?></span>
                        <span class="stat-label">En Mora</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($prestamos)): ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <p><?php echo $ver_historial ? 'No hay préstamos en el historial.' : 'No hay préstamos activos ni pendientes de archivar.'; ?></p>
            </div>
        <?php else: ?>
            <div class="prestamos-lista">
                <?php foreach ($prestamos as $p):
                    $clase_estado = strtolower($p['estado']);
                    if ($p['estado_mora'] === 'EN MORA' && $p['estado'] === 'ACTIVO') $clase_estado = 'mora';

                    $total_pagado = floatval($p['total_pagado']);
                    $saldo_restante = floatval($p['monto_total']) - $total_pagado;
                    if ($saldo_restante < 0) $saldo_restante = 0;

                    $porcentaje_pagado = ($p['monto_total'] > 0) ? ($total_pagado / $p['monto_total']) * 100 : 0;
                    if ($porcentaje_pagado > 100) $porcentaje_pagado = 100;

                    $cuotas_pagadas_equiv = ($p['valor_cuota'] > 0) ? round($total_pagado / $p['valor_cuota'], 1) : 0;

                    // Calculamos el monto por defecto que se le va a mostrar en el input del modal
                    $monto_a_cobrar = ($saldo_restante < floatval($p['valor_cuota'])) ? $saldo_restante : floatval($p['valor_cuota']);
                ?>
                    <div class="prestamo-card <?php echo $clase_estado; ?>">

                        <div class="card-fila-principal">
                            <div class="prestamo-foto">
                                <?php if (!empty($p['chofer_foto'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($p['chofer_foto']); ?>" alt="Foto">
                                <?php else: ?>
                                    <div class="sin-foto"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </div>

                            <div class="prestamo-info">
                                <div class="prestamo-chofer">
                                    <?php echo htmlspecialchars($p['chofer_nombre']); ?>
                                    <span class="badge-estado badge-<?php echo $clase_estado; ?>">
                                        <?php echo ($p['estado'] === 'ACTIVO') ? $p['estado_mora'] : $p['estado']; ?>
                                    </span>
                                    <?php if ($p['estado_mora'] === 'EN MORA'): ?>
                                        <span style="font-size:0.65rem; color:#dc3545; font-weight:700;"><i class="fas fa-history"></i> Tuvo mora</span>
                                    <?php endif; ?>
                                </div>
                                <div class="prestamo-desc"><?php echo htmlspecialchars($p['descripcion'] ?: 'Sin descripción'); ?></div>

                                <?php if (!empty($p['observaciones'])): ?>
                                    <div class="prestamo-obs"><i class="fas fa-comment-dots"></i> <?php echo htmlspecialchars($p['observaciones']); ?></div>
                                <?php endif; ?>

                                <div class="progreso-container">
                                    <div class="progreso-barra <?php echo ($porcentaje_pagado >= 100) ? 'completo' : ''; ?>" style="width: <?php echo $porcentaje_pagado; ?>%"></div>
                                </div>
                                <div style="font-size: 0.65rem; color: #888; margin-top: 2px;">
                                    Pagado: $<?php echo number_format($total_pagado, 0, ',', '.'); ?>
                                    (<?php echo $cuotas_pagadas_equiv; ?> de <?php echo $p['cuotas']; ?> cuotas aprox.)
                                </div>
                            </div>

                            <div class="prestamo-monto">
                                <div class="monto">$<?php echo number_format($p['monto_total'], 0, ',', '.'); ?></div>
                                <div class="fecha">Inicio: <?php echo date('d/m/Y', strtotime($p['fecha'])); ?></div>
                            </div>

                            <div class="prestamo-detalles">
                                <div class="detalle-fila">Saldo Restante: <span>$<?php echo number_format($saldo_restante, 0, ',', '.'); ?></span></div>
                                <div class="detalle-fila">Valor Cuota: <span>$<?php echo number_format($p['valor_cuota'], 0, ',', '.'); ?></span></div>
                            </div>

                            <div class="acciones-botones">
                                <?php if ($p['estado'] === 'ACTIVO'): ?>
                                    <!-- BOTÓN MODIFICADO: Ahora abre el modal -->
                                    <button type="button" class="btn-accion btn-pagar" onclick="abrirModalPago(<?php echo $p['id']; ?>, <?php echo $saldo_restante; ?>, '<?php echo addslashes(htmlspecialchars($p['chofer_nombre'])); ?>', <?php echo $monto_a_cobrar; ?>)">
                                        <i class="fas fa-check"></i> Pagar
                                    </button>
                                    <?php if ($p['estado_mora'] === 'AL DIA'): ?>
                                        <a href="prestamos.php?mora=1&id=<?php echo $p['id']; ?>" class="btn-accion btn-mora" title="Marcar en mora" onclick="return confirm('¿Marcar este préstamo como EN MORA?');"><i class="fas fa-exclamation-triangle"></i></a>
                                    <?php endif; ?>
                                <?php elseif ($p['estado'] === 'PAGADO'): ?>
                                    <a href="prestamos.php?archivar=1&id=<?php echo $p['id']; ?>" class="btn-accion btn-archivar" title="Mover al historial" onclick="return confirm('¿Archivar este préstamo?');"><i class="fas fa-box"></i> Archivar</a>
                                <?php endif; ?>

                                <button type="button" class="btn-accion btn-historial-pagos" onclick="togglePagos(<?php echo $p['id']; ?>)"><i class="fas fa-list"></i> Pagos</button>
                            </div>
                        </div>

                        <!-- PANEL DE HISTORIAL DE PAGOS -->
                        <div class="pagos-lista" id="pagos-<?php echo $p['id']; ?>">
                            <div style="font-size:0.75rem; font-weight:700; color:#888; text-transform:uppercase; margin-bottom:5px;">Historial de pagos:</div>
                            <?php
                            $stmt_p = $con->prepare("SELECT monto, fecha_pago FROM prestamos_pagos WHERE prestamo_id = ? ORDER BY fecha_pago ASC");
                            $stmt_p->bind_param("i", $p['id']);
                            $stmt_p->execute();
                            $res_p = $stmt_p->get_result();

                            if ($res_p->num_rows > 0):
                                while ($pago = $res_p->fetch_assoc()):
                            ?>
                                    <div class="pago-fila">
                                        <span><i class="far fa-calendar-alt"></i> <span class="fecha-pago"><?php echo date('d/m/Y', strtotime($pago['fecha_pago'])); ?></span></span>
                                        <span class="monto-pago">+ $<?php echo number_format($pago['monto'], 2, ',', '.'); ?></span>
                                    </div>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <div style="font-size:0.8rem; color:#999; font-style:italic;">No hay pagos registrados aún.</div>
                            <?php endif;
                            $stmt_p->close(); ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- MODAL PARA INGRESAR EL MONTO A PAGAR -->
    <div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 12px;">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-money-bill-wave"></i> Registrar Pago de Préstamo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Chofer: <strong id="pago-nombre"></strong></p>
                    <div class="d-flex justify-content-between mb-3" style="font-size: 1rem;">
                        <span>Saldo Restante:</span>
                        <strong class="text-danger">$<span id="pago-saldo"></span></strong>
                    </div>

                    <label class="form-label fw-bold">¿Cuánto dinero vas a cobrar?</label>
                    <input type="number" id="pago-monto" class="form-control form-control-lg text-end" step="0.01">
                    <small class="text-muted">Podés cobrar cualquier monto, siempre que no supere el saldo restante.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" onclick="procesarPagoPrestamo()">Confirmar Pago</button>
                </div>
            </div>
        </div>
    </div>

    <!-- LIBRERÍA BOOTSTRAP JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function togglePagos(id) {
            const panel = document.getElementById('pagos-' + id);
            if (panel.classList.contains('activo')) {
                panel.classList.remove('activo');
            } else {
                panel.classList.add('activo');
            }
        }

        // === LÓGICA PARA REGISTRAR PAGO MANUAL ===
        let pagoPrestamoId = 0;
        let pagoSaldoMax = 0;

        function abrirModalPago(id, saldo_restante, nombre, monto_sugerido) {
            pagoPrestamoId = id;
            pagoSaldoMax = parseFloat(saldo_restante);

            document.getElementById('pago-nombre').innerText = nombre;
            document.getElementById('pago-saldo').innerText = pagoSaldoMax.toLocaleString('es-AR', {
                minimumFractionDigits: 2
            });

            const input = document.getElementById('pago-monto');
            input.value = parseFloat(monto_sugerido).toFixed(2); // Precarga con la cuota o el saldo
            input.max = pagoSaldoMax;
            input.min = 0.01;

            var myModal = new bootstrap.Modal(document.getElementById('modalPago'));
            myModal.show();
        }

        function procesarPagoPrestamo() {
            const monto = parseFloat(document.getElementById('pago-monto').value);

            if (isNaN(monto) || monto <= 0) {
                alert('Por favor, ingresá un monto válido.');
                return;
            }
            if (monto > pagoSaldoMax) {
                alert('El monto no puede superar el saldo restante ($' + pagoSaldoMax.toFixed(2) + ').');
                return;
            }

            // Enviamos el pago al archivo PHP
            const fd = new FormData();
            fd.append('prestamo_id', pagoPrestamoId);
            fd.append('monto_pago', monto);

            fetch('prestamo_pagar.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) {
                        window.location.href = 'prestamos.php?msg=pagado';
                    } else {
                        alert('❌ Error: ' + data.error);
                    }
                })
                .catch(() => {
                    alert('❌ Error de conexión al procesar el pago.');
                });
        }
    </script>
</body>

</html>