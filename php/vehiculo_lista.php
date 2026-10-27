<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();

include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$sql = "SELECT v.*, 
            (SELECT COUNT(*) FROM asignaciones_auto a WHERE a.vehiculo_id = v.id AND a.activa = 1) AS asignados
        FROM vehiculos v
        ORDER BY v.movil ASC";
$result = $con->query($sql);

/**
 * Calcula el estado de un ítem de mantenimiento.
 */
function calcMant($km_actuales, $km_guardados, $km_ultimo, $intervalo, $nombre)
{
    if ($km_actuales <= 0) {
        return [
            'class'   => 'badge-sin',
            'short'   => '—',
            'tooltip' => 'Sin km actuales registrados',
        ];
    }

    $base = ($km_ultimo !== null && (int)$km_ultimo > 0) ? (int)$km_ultimo : (int)$km_guardados;

    if ($base <= 0) {
        return [
            'class'   => 'badge-sin',
            'short'   => '—',
            'tooltip' => 'Sin datos de ' . $nombre,
        ];
    }

    $proximo = $base + $intervalo;
    $diff = $km_actuales - $proximo;

    if ($diff >= 0) {
        return [
            'class'   => 'badge-vencido',
            'short'   => '-' . number_format($diff, 0, ',', '.') . ' km',
            'tooltip' => $nombre . ' · Último: ' . number_format($base, 0, ',', '.')
                . ' km · Debía cambiarse en ' . number_format($proximo, 0, ',', '.')
                . ' km · Vencido hace ' . number_format($diff, 0, ',', '.') . ' km',
        ];
    }

    $faltantes = abs($diff);
    $class = ($faltantes <= 1000) ? 'badge-proximo' : 'badge-ok';
    $short = ($faltantes <= 1000)
        ? 'Próx: ' . number_format($faltantes, 0, ',', '.') . ' km'
        : number_format($faltantes, 0, ',', '.') . ' km';

    return [
        'class'   => $class,
        'short'   => $short,
        'tooltip' => $nombre . ' · Último: ' . number_format($base, 0, ',', '.')
            . ' km · Próximo: ' . number_format($proximo, 0, ',', '.')
            . ' km · Faltan ' . number_format($faltantes, 0, ',', '.') . ' km',
    ];
}

// === INTERVALOS DE MANTENIMIENTO ===
define('INTERVALO_ACEITE', 13000);
define('INTERVALO_BUJIAS', 40000);
define('INTERVALO_GRASA',  60000);
define('INTERVALO_CORREA', 50000);

// === USUARIO LOGUEADO ===
$usuario_nombre = $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? 'Invitado');
$usuario_login  = $_SESSION['usuario'] ?? '—';

$flash_ok = isset($_GET['ok']);
$flash_err = isset($_GET['err']);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Vehículos</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
        }

        .page-wrapper {
            max-width: 100%;
            margin: 15px auto;
            padding: 0 15px;
            overflow-x: auto;
        }

        .page-title {
            text-align: center;
            margin: 10px 0 15px;
            font-size: 1.3rem;
            color: #333;
        }

        /* Banner de usuario logueado */
        .user-banner {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.25);
            font-size: 0.85rem;
        }

        .user-banner .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-banner .user-icon {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .user-banner .user-text strong {
            display: block;
            font-size: 0.92rem;
            margin-bottom: 1px;
        }

        .user-banner .user-text small {
            opacity: 0.85;
            font-size: 0.72rem;
        }

        .user-banner .fecha-hoy {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-nuevo {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 12px;
        }

        .btn-nuevo:hover {
            color: white;
            opacity: 0.9;
            text-decoration: none;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-size: 0.78rem;
            min-width: 1250px;
        }

        thead {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
        }

        th {
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 0.68rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 8px 6px;
            border-bottom: 1px solid #eef1f7;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9fc;
        }

        .movil-numero {
            font-weight: 700;
            color: #17a2b8;
            font-size: 0.92rem;
        }

        .badge-asignado,
        .badge-libre,
        .badge-inactivo {
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-asignado {
            background: #ffc107;
            color: #333;
        }

        .badge-libre {
            background: #28a745;
            color: white;
        }

        .badge-inactivo {
            background: #6c757d;
            color: white;
        }

        .acciones-cell {
            display: flex;
            gap: 4px;
            flex-wrap: wrap;
        }

        .btn-accion {
            padding: 4px 8px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 0.68rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-editar {
            background: #2196F3;
        }

        .btn-editar:hover {
            background: #1976D2;
            color: white;
        }

        .btn-km {
            background: #6f42c1;
        }

        .btn-km:hover {
            background: #5a32a3;
            color: white;
        }

        .btn-toggle {
            background: #ffc107;
            color: #333;
        }

        .btn-toggle:hover {
            background: #e0a800;
            color: #333;
        }

        .btn-toggle.activar {
            background: #28a745;
            color: white;
        }

        .btn-toggle.activar:hover {
            background: #218838;
            color: white;
        }

        /* Badges de mantenimiento */
        .badge-mant {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 700;
            white-space: nowrap;
            cursor: help;
        }

        .badge-ok {
            background: #d4edda;
            color: #155724;
        }

        .badge-proximo {
            background: #fff3cd;
            color: #856404;
        }

        .badge-vencido {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-sin {
            background: #e2e3e5;
            color: #6c757d;
        }

        .km-valor {
            font-family: 'Consolas', 'Courier New', monospace;
            font-weight: 700;
            font-size: 0.75rem;
            color: #333;
            white-space: nowrap;
        }

        .km-valor.cero {
            color: #bbb;
            font-weight: 400;
        }

        /* Info de última edición */
        .ultima-edicion {
            display: flex;
            flex-direction: column;
            gap: 1px;
            font-size: 0.68rem;
            line-height: 1.2;
        }

        .ultima-edicion .quien {
            color: #667eea;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 3px;
            white-space: nowrap;
        }

        .ultima-edicion .cuando {
            color: #888;
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.65rem;
            white-space: nowrap;
        }

        .ultima-edicion.sin-datos .quien {
            color: #bbb;
            font-weight: 400;
        }

        /* Alertas */
        .alerta {
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alerta-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alerta-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        /* Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 15px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: white;
            border-radius: 12px;
            padding: 24px;
            width: 520px;
            max-width: 100%;
            max-height: 92vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-box h3 {
            margin: 0 0 6px 0;
            color: #333;
            font-size: 1.15rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-box h3 i {
            color: #17a2b8;
        }

        .modal-edit-info {
            background: #f0f4ff;
            border-left: 3px solid #667eea;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 0.72rem;
            color: #4a5aa8;
            margin-bottom: 14px;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .modal-edit-info strong {
            color: #667eea;
        }

        .km-section {
            margin-bottom: 16px;
            padding-bottom: 16px;
            border-bottom: 1px solid #eef1f7;
        }

        .km-section h4 {
            font-size: 0.72rem;
            text-transform: uppercase;
            color: #17a2b8;
            letter-spacing: 0.5px;
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .km-section.small-label h4 {
            color: #888;
            font-weight: 600;
        }

        .km-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .km-field label {
            display: block;
            font-size: 0.72rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 4px;
        }

        .km-field input {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #dfe6ef;
            border-radius: 6px;
            font-size: 0.88rem;
            font-family: inherit;
        }

        .km-field input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.15);
        }

        .km-field small {
            display: block;
            font-size: 0.65rem;
            color: #999;
            margin-top: 3px;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 8px;
        }

        .modal-actions button {
            padding: 9px 18px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
        }

        .btn-cancel {
            background: #eef1f7;
            color: #555;
        }

        .btn-cancel:hover {
            background: #dde3ec;
        }

        .btn-save {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-save:hover {
            opacity: 0.9;
        }

        [title] {
            position: relative;
        }
    </style>
    <script>
        function editarVehiculo(id) {
            window.location = "vehiculo_editar.php?q=" + id;
        }

        function toggleVehiculo(id, activo, movil) {
            const accion = activo == 1 ? 'desactivar' : 'activar';
            if (confirm("¿Querés " + accion + " el vehículo #" + movil + "?")) {
                window.location = "vehiculo_toggle.php?q=" + id;
            }
        }

        function abrirModalKm(btn) {
            const d = btn.dataset;
            document.getElementById('modal-id').value = d.id;
            document.getElementById('modal-movil').innerText = d.movil;
            document.getElementById('modal-km_guardados').value = d.kmg || 0;
            document.getElementById('modal-km_actuales').value = d.kma || 0;
            document.getElementById('modal-km_aceite').value = d.aceite || '';
            document.getElementById('modal-km_bujias').value = d.bujias || '';
            document.getElementById('modal-km_correa').value = d.correa || '';
            document.getElementById('modal-km_grasa').value = d.grasa || '';

            // Info de última edición
            const ultimoPor = d.editpor || '—';
            const ultimaFecha = d.editfecha || '—';
            document.getElementById('modal-edit-por').innerText = ultimoPor;
            document.getElementById('modal-edit-fecha').innerText = ultimaFecha;

            document.getElementById('modalKm').classList.add('active');
            document.getElementById('modal-km_actuales').focus();
        }

        function cerrarModal() {
            document.getElementById('modalKm').classList.remove('active');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') cerrarModal();
        });

        document.getElementById('modalKm').addEventListener('click', function(e) {
            if (e.target === this) cerrarModal();
        });
    </script>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title"><i class="fas fa-car-side"></i> Vehículos de la Flota</h1>

        <!-- BANNER DE USUARIO LOGUEADO -->
        <div class="user-banner">
            <div class="user-info">
                <div class="user-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-text">
                    <strong><?php echo htmlspecialchars($usuario_nombre); ?></strong>
                    <small>Sesión iniciada como <code style="background:rgba(255,255,255,0.2); padding:1px 6px; border-radius:4px;"><?php echo htmlspecialchars($usuario_login); ?></code></small>
                </div>
            </div>
            <div class="fecha-hoy">
                <i class="fas fa-calendar-day"></i>
                <?php
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                echo $dias[date('w')] . ' · ' . date('d/m/Y');
                ?>
            </div>
        </div>

        <?php if ($flash_ok): ?>
            <div class="alerta alerta-success">
                <i class="fas fa-check-circle"></i> Kilometraje actualizado correctamente.
            </div>
        <?php elseif ($flash_err): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-times-circle"></i> Error al guardar los km. Probá de nuevo.
            </div>
        <?php endif; ?>

        <a href="vehiculo_nuevo.php" class="btn-nuevo"><i class="fas fa-plus"></i> Nuevo Vehículo</a>

        <table>
            <thead>
                <tr>
                    <th>Móvil</th>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Dominio</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th title="Odómetro base desde donde se calcula el mantenimiento">Km Base</th>
                    <th title="Odómetro actual del vehículo">Km Actual</th>
                    <th title="Cambio cada <?php echo number_format(INTERVALO_ACEITE, 0, ',', '.'); ?> km">Aceite</th>
                    <th title="Cambio cada <?php echo number_format(INTERVALO_BUJIAS, 0, ',', '.'); ?> km">Bujías</th>
                    <th title="Cambio cada <?php echo number_format(INTERVALO_GRASA, 0, ',', '.'); ?> km">Grasa Caja</th>
                    <th title="Cambio cada <?php echo number_format(INTERVALO_CORREA, 0, ',', '.'); ?> km">Correa</th>
                    <th title="Quién actualizó los km y cuándo">Últ. Edición</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($v = $result->fetch_assoc()):
                    $kmg = (int)($v['km_guardados'] ?? 0);
                    $kma = (int)($v['km_actuales'] ?? 0);

                    $aceite = calcMant($kma, $kmg, $v['km_aceite'] ?? null, INTERVALO_ACEITE, 'Aceite');
                    $bujias = calcMant($kma, $kmg, $v['km_bujias'] ?? null, INTERVALO_BUJIAS, 'Bujías');
                    $grasa  = calcMant($kma, $kmg, $v['km_grasa']  ?? null, INTERVALO_GRASA,  'Grasa de caja');
                    $correa = calcMant($kma, $kmg, $v['km_correa'] ?? null, INTERVALO_CORREA, 'Correa');

                    $edit_por = trim($v['km_editado_por'] ?? '');
                    $edit_fecha_raw = $v['km_editado_fecha'] ?? null;
                    $tiene_edit = ($edit_por !== '' && $edit_fecha_raw !== null);

                    if ($tiene_edit) {
                        $dt = new DateTime($edit_fecha_raw);
                        $fecha_formato = $dt->format('d/m/Y H:i');
                    } else {
                        $fecha_formato = '';
                    }
                ?>
                    <tr>
                        <td><span class="movil-numero">#<?php echo htmlspecialchars($v['movil']); ?></span></td>
                        <td><?php echo htmlspecialchars($v['marca']); ?></td>
                        <td><?php echo htmlspecialchars($v['modelo']); ?></td>
                        <td><strong><?php echo htmlspecialchars($v['dominio']); ?></strong></td>
                        <td><?php echo htmlspecialchars($v['tipo']); ?></td>
                        <td>
                            <?php if (!$v['activo']): ?>
                                <span class="badge-inactivo">Inactivo</span>
                            <?php elseif ($v['asignados'] >= 2): ?>
                                <span class="badge-asignado">Completo</span>
                            <?php elseif ($v['asignados'] == 1): ?>
                                <span class="badge-asignado">1 turno</span>
                            <?php else: ?>
                                <span class="badge-libre">Libre</span>
                            <?php endif; ?>
                        </td>

                        <td><span class="km-valor <?php echo $kmg === 0 ? 'cero' : ''; ?>"><?php echo $kmg > 0 ? number_format($kmg, 0, ',', '.') : '—'; ?></span></td>
                        <td><span class="km-valor <?php echo $kma === 0 ? 'cero' : ''; ?>"><?php echo $kma > 0 ? number_format($kma, 0, ',', '.') : '—'; ?></span></td>

                        <td><span class="badge-mant <?php echo $aceite['class']; ?>" title="<?php echo htmlspecialchars($aceite['tooltip']); ?>"><?php echo $aceite['short']; ?></span></td>
                        <td><span class="badge-mant <?php echo $bujias['class']; ?>" title="<?php echo htmlspecialchars($bujias['tooltip']); ?>"><?php echo $bujias['short']; ?></span></td>
                        <td><span class="badge-mant <?php echo $grasa['class']; ?>" title="<?php echo htmlspecialchars($grasa['tooltip']); ?>"><?php echo $grasa['short']; ?></span></td>
                        <td><span class="badge-mant <?php echo $correa['class']; ?>" title="<?php echo htmlspecialchars($correa['tooltip']); ?>"><?php echo $correa['short']; ?></span></td>

                        <!-- ÚLTIMA EDICIÓN -->
                        <td>
                            <div class="ultima-edicion <?php echo !$tiene_edit ? 'sin-datos' : ''; ?>">
                                <span class="quien" title="Usuario que actualizó los km">
                                    <i class="fas fa-user" style="font-size:0.6rem;"></i>
                                    <?php echo $tiene_edit ? htmlspecialchars($edit_por) : 'Sin cambios'; ?>
                                </span>
                                <?php if ($tiene_edit): ?>
                                    <span class="cuando" title="<?php echo htmlspecialchars($edit_fecha_raw); ?>">
                                        <?php echo $fecha_formato; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <div class="acciones-cell">
                                <button class="btn-accion btn-km"
                                    title="Editar kilometraje"
                                    onclick="abrirModalKm(this)"
                                    data-id="<?php echo $v['id']; ?>"
                                    data-movil="<?php echo htmlspecialchars($v['movil']); ?>"
                                    data-kmg="<?php echo $kmg; ?>"
                                    data-kma="<?php echo $kma; ?>"
                                    data-aceite="<?php echo (int)($v['km_aceite'] ?? ''); ?>"
                                    data-bujias="<?php echo (int)($v['km_bujias'] ?? ''); ?>"
                                    data-correa="<?php echo (int)($v['km_correa'] ?? ''); ?>"
                                    data-grasa="<?php echo (int)($v['km_grasa'] ?? ''); ?>"
                                    data-editpor="<?php echo htmlspecialchars($edit_por); ?>"
                                    data-editfecha="<?php echo htmlspecialchars($fecha_formato); ?>">
                                    <i class="fas fa-tachometer-alt"></i> KM
                                </button>
                                <button class="btn-accion btn-editar" onclick="editarVehiculo(<?php echo $v['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <?php if ($v['activo']): ?>
                                    <button class="btn-accion btn-toggle" onclick="toggleVehiculo(<?php echo $v['id']; ?>, 1, '<?php echo htmlspecialchars($v['movil']); ?>')">
                                        <i class="fas fa-power-off"></i> Off
                                    </button>
                                <?php else: ?>
                                    <button class="btn-accion btn-toggle activar" onclick="toggleVehiculo(<?php echo $v['id']; ?>, 0, '<?php echo htmlspecialchars($v['movil']); ?>')">
                                        <i class="fas fa-check"></i> On
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL DE EDICIÓN DE KM -->
    <div class="modal-overlay" id="modalKm">
        <div class="modal-box">
            <h3><i class="fas fa-tachometer-alt"></i> Kilometraje · Móvil #<span id="modal-movil"></span></h3>

            <!-- Info de quién hizo el último cambio -->
            <div class="modal-edit-info">
                <span><i class="fas fa-history"></i> Última edición: <strong id="modal-edit-por">—</strong></span>
                <span><i class="fas fa-clock"></i> Fecha: <strong id="modal-edit-fecha">—</strong></span>
                <span style="margin-top:4px; padding-top:4px; border-top:1px dashed #c5d3f0;">
                    <i class="fas fa-user-check"></i> Vas a guardar como: <strong><?php echo htmlspecialchars($usuario_nombre); ?></strong>
                </span>
            </div>

            <form method="POST" action="vehiculo_km_guardar.php">
                <input type="hidden" name="id" id="modal-id">

                <div class="km-section">
                    <h4>Odómetro</h4>
                    <div class="km-grid">
                        <div class="km-field">
                            <label for="modal-km_guardados">Km Base</label>
                            <input type="number" name="km_guardados" id="modal-km_guardados" min="0" step="1" value="0">
                            <small>Punto de partida del ciclo</small>
                        </div>
                        <div class="km-field">
                            <label for="modal-km_actuales">Km Actuales</label>
                            <input type="number" name="km_actuales" id="modal-km_actuales" min="0" step="1" value="0">
                            <small>Odómetro del vehículo hoy</small>
                        </div>
                    </div>
                </div>

                <div class="km-section small-label">
                    <h4>Último cambio por ítem</h4>
                    <p style="font-size:0.7rem; color:#888; margin:-4px 0 10px 0;">
                        Dejá vacío para usar el Km Base como referencia.
                    </p>
                    <div class="km-grid">
                        <div class="km-field">
                            <label for="modal-km_aceite">Aceite</label>
                            <input type="number" name="km_aceite" id="modal-km_aceite" min="0" step="1" placeholder="Vacío = Base">
                            <small>Cada <?php echo number_format(INTERVALO_ACEITE, 0, ',', '.'); ?> km</small>
                        </div>
                        <div class="km-field">
                            <label for="modal-km_bujias">Bujías</label>
                            <input type="number" name="km_bujias" id="modal-km_bujias" min="0" step="1" placeholder="Vacío = Base">
                            <small>Cada <?php echo number_format(INTERVALO_BUJIAS, 0, ',', '.'); ?> km</small>
                        </div>
                        <div class="km-field">
                            <label for="modal-km_grasa">Grasa de caja</label>
                            <input type="number" name="km_grasa" id="modal-km_grasa" min="0" step="1" placeholder="Vacío = Base">
                            <small>Cada <?php echo number_format(INTERVALO_GRASA, 0, ',', '.'); ?> km</small>
                        </div>
                        <div class="km-field">
                            <label for="modal-km_correa">Correa</label>
                            <input type="number" name="km_correa" id="modal-km_correa" min="0" step="1" placeholder="Vacío = Base">
                            <small>Cada <?php echo number_format(INTERVALO_CORREA, 0, ',', '.'); ?> km</small>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-save"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>

</body>

</html>