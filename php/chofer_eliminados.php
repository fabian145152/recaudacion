<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();

include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$sql = "SELECT 
            c.id, c.nombre, c.cel, c.email, c.foto,
            c.creado_por, c.creado_fecha,
            c.eliminado_por, c.eliminado_fecha
        FROM choferes c
        WHERE c.eliminado = 1
        ORDER BY c.eliminado_fecha DESC";
$result = $con->query($sql);

$total = $result->num_rows;
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Choferes Eliminados</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
            font-size: 13px;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 25px;
        }

        .page-title {
            text-align: center;
            margin: 15px 0 8px;
            font-size: 1.4rem;
            color: #333;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .page-title i {
            color: #dc3545;
        }

        .subtitle {
            text-align: center;
            color: #888;
            font-size: 0.88rem;
            margin-bottom: 20px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .btn-volver {
            background: #eef1f7;
            color: #555;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .btn-volver:hover {
            background: #dde3ec;
            color: #333;
            text-decoration: none;
        }

        .contador {
            background: #dc3545;
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .alerta {
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 15px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-size: 0.88rem;
        }

        thead {
            background: linear-gradient(135deg, #721c24, #dc3545);
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eef1f7;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #fdf6f7;
        }

        img.foto-chof {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #f8d7da;
            opacity: 0.85;
        }

        .sin-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8d7da;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #dc3545;
            font-size: 1rem;
        }

        .nombre-eliminado {
            font-weight: 600;
            color: #555;
            text-decoration: line-through;
        }

        .info-cell {
            display: flex;
            flex-direction: column;
            gap: 2px;
            font-size: 0.75rem;
            line-height: 1.3;
        }

        .info-cell .quien {
            display: flex;
            align-items: center;
            gap: 4px;
            font-weight: 700;
            white-space: nowrap;
        }

        .info-cell .quien.creado {
            color: #667eea;
        }

        .info-cell .quien.eliminado {
            color: #dc3545;
        }

        .info-cell .cuando {
            font-family: 'Consolas', 'Courier New', monospace;
            font-size: 0.68rem;
            color: #888;
            white-space: nowrap;
        }

        .btn-accion {
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-restaurar {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .btn-restaurar:hover {
            opacity: 0.9;
            transform: translateY(-1px);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 3rem;
            color: #28a745;
            margin-bottom: 15px;
            display: block;
        }

        .empty-state h3 {
            color: #333;
            font-size: 1.1rem;
            margin: 0 0 8px 0;
        }

        .empty-state p {
            color: #888;
            font-size: 0.88rem;
            margin: 0;
        }

        .empty-state a {
            display: inline-block;
            margin-top: 15px;
            color: #667eea;
            font-weight: 600;
            text-decoration: none;
        }
    </style>
    <script>
        function restaurarChofer(id, nombre) {
            if (confirm('¿Restaurar al chofer "' + nombre + '"?\n\nVolverá a aparecer en la lista de choferes activos.')) {
                window.location = "chofer_restaurar.php?q=" + id;
            }
        }
    </script>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title"><i class="fas fa-trash-alt"></i> Choferes Eliminados</h1>
        <p class="subtitle">Papelera · Acá podés ver y restaurar los choferes que se borraron</p>

        <?php if (isset($_GET['ok']) && $_GET['ok'] === 'restaurado'): ?>
            <div class="alerta alerta-success">
                <i class="fas fa-check-circle"></i> Chofer restaurado correctamente. Ya aparece nuevamente en la lista activa.
            </div>
        <?php elseif (isset($_GET['err'])): ?>
            <div class="alerta alerta-error">
                <i class="fas fa-times-circle"></i> Error al restaurar el chofer. Probá de nuevo.
            </div>
        <?php endif; ?>

        <div class="top-bar">
            <a href="taxis.php" class="btn-volver">
                <i class="fas fa-arrow-left"></i> Volver a Choferes
            </a>
            <?php if ($total > 0): ?>
                <span class="contador">
                    <i class="fas fa-trash-alt"></i> <?php echo $total; ?> eliminado<?php echo $total == 1 ? '' : 's'; ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($total === 0): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>No hay choferes eliminados</h3>
                <p>Cuando borres un chofer, va a aparecer acá y podrás recuperarlo.</p>
                <a href="taxis.php"><i class="fas fa-arrow-left"></i> Volver a Choferes</a>
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th>Chofer</th>
                        <th>Contacto</th>
                        <th>Creado por</th>
                        <th>Eliminado por</th>
                        <th style="width:130px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($c = $result->fetch_assoc()):
                        $creado_por   = trim($c['creado_por'] ?? '');
                        $creado_fecha = $c['creado_fecha'] ?? null;
                        $creado_fecha_fmt = $creado_fecha ? date('d/m/Y H:i', strtotime($creado_fecha)) : '';

                        $eliminado_por   = trim($c['eliminado_por'] ?? '');
                        $eliminado_fecha = $c['eliminado_fecha'] ?? null;
                        $eliminado_fecha_fmt = $eliminado_fecha ? date('d/m/Y H:i', strtotime($eliminado_fecha)) : '';
                    ?>
                        <tr>
                            <td>
                                <?php if (!empty($c['foto'])): ?>
                                    <img src="../images/<?php echo htmlspecialchars($c['foto']); ?>" class="foto-chof">
                                <?php else: ?>
                                    <div class="sin-foto"><i class="fas fa-user"></i></div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="nombre-eliminado"><?php echo htmlspecialchars($c['nombre']); ?></div>
                                <div style="font-size:0.7rem; color:#bbb; margin-top:2px;">
                                    ID #<?php echo (int)$c['id']; ?>
                                </div>
                            </td>

                            <td style="font-size:0.78rem; color:#666;">
                                <?php if (!empty($c['cel'])): ?>
                                    <div><i class="fas fa-phone" style="color:#aaa;"></i> <?php echo htmlspecialchars($c['cel']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($c['email'])): ?>
                                    <div><i class="fas fa-envelope" style="color:#aaa;"></i> <?php echo htmlspecialchars($c['email']); ?></div>
                                <?php endif; ?>
                                <?php if (empty($c['cel']) && empty($c['email'])): ?>
                                    <span style="color:#bbb; font-style:italic;">—</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <div class="info-cell">
                                    <?php if ($creado_por !== '' || $creado_fecha): ?>
                                        <span class="quien creado">
                                            <i class="fas fa-user-plus" style="font-size:0.65rem;"></i>
                                            <?php echo $creado_por !== '' ? htmlspecialchars($creado_por) : 'Sin nombre'; ?>
                                        </span>
                                        <?php if ($creado_fecha_fmt): ?>
                                            <span class="cuando"><?php echo $creado_fecha_fmt; ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="cuando" style="font-style:italic;">Sin registro</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <div class="info-cell">
                                    <span class="quien eliminado">
                                        <i class="fas fa-user-slash" style="font-size:0.65rem;"></i>
                                        <?php echo $eliminado_por !== '' ? htmlspecialchars($eliminado_por) : 'Sin nombre'; ?>
                                    </span>
                                    <?php if ($eliminado_fecha_fmt): ?>
                                        <span class="cuando"><?php echo $eliminado_fecha_fmt; ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td>
                                <button class="btn-accion btn-restaurar"
                                    onclick="restaurarChofer(<?php echo (int)$c['id']; ?>, '<?php echo htmlspecialchars(addslashes($c['nombre'])); ?>')">
                                    <i class="fas fa-undo"></i> Restaurar
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>

</html>