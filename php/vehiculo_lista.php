<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$sql = "SELECT v.*, 
            (SELECT COUNT(*) FROM asignaciones_auto a WHERE a.vehiculo_id = v.id AND a.activa = 1) AS asignados
        FROM vehiculos v
        ORDER BY v.movil ASC";
$result = $con->query($sql);
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Vehículos</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        .page-wrapper {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-title {
            text-align: center;
            margin: 15px 0 20px;
            font-size: 1.4rem;
            color: #333;
        }

        .btn-nuevo {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 15px;
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
            font-size: 0.9rem;
        }

        thead {
            background: linear-gradient(135deg, #17a2b8, #0dcaf0);
            color: white;
        }

        th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #eef1f7;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8f9fc;
        }

        .movil-numero {
            font-weight: 700;
            color: #17a2b8;
            font-size: 1rem;
        }

        .badge-asignado {
            background: #ffc107;
            color: #333;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-libre {
            background: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-inactivo {
            background: #6c757d;
            color: white;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .acciones-cell {
            display: flex;
            gap: 5px;
        }

        .btn-accion {
            padding: 5px 10px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            color: white;
            font-size: 0.78rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-decoration: none;
        }

        .btn-editar {
            background-color: #2196F3;
        }

        .btn-editar:hover {
            background-color: #1976D2;
            color: white;
        }

        .btn-toggle {
            background-color: #ffc107;
            color: #333;
        }

        .btn-toggle:hover {
            background-color: #e0a800;
            color: #333;
        }

        .btn-toggle.activar {
            background-color: #28a745;
            color: white;
        }

        .btn-toggle.activar:hover {
            background-color: #218838;
            color: white;
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
    </script>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title"><i class="fas fa-car-side"></i> Vehículos de la Flota</h1>

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
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($v = $result->fetch_assoc()): ?>
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
                                <span class="badge-asignado">Completo (2 turnos)</span>
                            <?php elseif ($v['asignados'] == 1): ?>
                                <span class="badge-asignado">1 turno asignado</span>
                            <?php else: ?>
                                <span class="badge-libre">Libre</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="acciones-cell">
                                <button class="btn-accion btn-editar" onclick="editarVehiculo(<?php echo $v['id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>

                                <?php if ($v['activo']): ?>
                                    <button class="btn-accion btn-toggle" onclick="toggleVehiculo(<?php echo $v['id']; ?>, 1, '<?php echo htmlspecialchars($v['movil']); ?>')">
                                        <i class="fas fa-power-off"></i> Desactivar
                                    </button>
                                <?php else: ?>
                                    <button class="btn-accion btn-toggle activar" onclick="toggleVehiculo(<?php echo $v['id']; ?>, 0, '<?php echo htmlspecialchars($v['movil']); ?>')">
                                        <i class="fas fa-check"></i> Activar
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>