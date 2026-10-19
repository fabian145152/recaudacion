<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$sql = "SELECT 
            c.id AS chofer_id,
            c.nombre, c.cel, c.email, c.foto, c.activo,
            a.id AS asignacion_id, a.turno, a.fecha_desde, a.observaciones,
            v.id AS vehiculo_id, v.movil, v.marca, v.modelo, v.dominio
        FROM choferes c
        LEFT JOIN asignaciones_auto a ON a.chofer_id = c.id AND a.activa = 1
        LEFT JOIN vehiculos v ON v.id = a.vehiculo_id
        WHERE c.activo = 1
        ORDER BY v.movil ASC, a.turno ASC, c.nombre ASC";
$result = $con->query($sql);
?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Choferes y Asignaciones</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 0;
        }

        .page-wrapper {
            max-width: 1400px;
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

        .subtitle {
            text-align: center;
            color: #888;
            font-size: 0.9rem;
            margin-bottom: 20px;
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
            background: linear-gradient(135deg, #667eea, #764ba2);
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

        img.foto-chof {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #eef1f7;
        }

        .sin-foto {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #eef1f7;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #aaa;
            font-size: 1rem;
        }

        .badge-turno {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 10px;
            font-size: 0.72rem;
            font-weight: 700;
            color: white;
        }

        .badge-dia {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .badge-noche {
            background: linear-gradient(135deg, #34495e, #2c3e50);
        }

        .movil-numero {
            font-size: 1.2rem;
            font-weight: 700;
            color: #667eea;
            background: #eef1f7;
            padding: 4px 10px;
            border-radius: 8px;
            display: inline-block;
        }

        .vehiculo-info {
            font-weight: 600;
            color: #333;
        }

        .vehiculo-dominio {
            font-size: 0.78rem;
            color: #888;
        }

        .sin-auto {
            color: #bbb;
            font-style: italic;
            font-size: 0.85rem;
        }

        .acciones-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
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
            transition: all 0.2s;
        }

        .btn-accion:hover {
            opacity: 0.85;
            transform: translateY(-1px);
            color: white;
        }

        .btn-cargar {
            background-color: #28a745;
        }

        .btn-editar {
            background-color: #2196F3;
        }

        .btn-cambiar {
            background-color: #ff9800;
        }

        .btn-historial {
            background-color: #6c757d;
        }

        .btn-liberar {
            background-color: #dc3545;
        }

        /* Botón deshabilitado */
        .btn-accion:disabled {
            background-color: #e9ecef;
            color: #adb5bd;
            cursor: not-allowed;
            transform: none;
        }
    </style>
    <script>
        function editarChofer(id) {
            window.location = "chofer_editar.php?q=" + id;
        }

        function cargarRecaudacion(id) {
            window.location = "cargar.php?chofer=" + id;
        }

        function cambiarAuto(id) {
            window.location = "reasignar_auto.php?q=" + id;
        }

        function verHistorial(id) {
            window.location = "historial.php?q=" + id;
        }

        function liberarChofer(id) {
            if (confirm('¿Estás seguro que querés liberar a este chofer? Se le quitará el auto y el turno actual.')) {
                window.location = "liberar_chofer.php?q=" + id;
            }
        }
    </script>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="page-wrapper">
        <h1 class="page-title"><i class="fas fa-users"></i> Choferes y sus Asignaciones</h1>
        <p class="subtitle">Cada fila es un chofer con su auto y turno actual</p>

        <table>
            <thead>
                <tr>
                    <th style="width:40px;"></th>
                    <th>Chofer</th>
                    <th>Móvil</th>
                    <th>Turno</th>
                    <th>Auto actual</th>
                    <th>Desde</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($c = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if (!empty($c['foto'])): ?>
                                <img src="../images/<?php echo htmlspecialchars($c['foto']); ?>" class="foto-chof">
                            <?php else: ?>
                                <div class="sin-foto"><i class="fas fa-user"></i></div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <div style="font-weight:600;"><?php echo htmlspecialchars($c['nombre']); ?></div>
                            <div style="font-size:0.78rem; color:#888;">
                                <?php if (!empty($c['cel'])): ?>
                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($c['cel']); ?>
                                <?php endif; ?>
                            </div>
                        </td>

                        <td>
                            <?php if (!empty($c['movil'])): ?>
                                <span class="movil-numero">#<?php echo htmlspecialchars($c['movil']); ?></span>
                            <?php else: ?>
                                <span style="color:#bbb;">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!empty($c['turno'])): ?>
                                <span class="badge-turno <?php echo ($c['turno'] === 'DIA') ? 'badge-dia' : 'badge-noche'; ?>">
                                    <?php echo htmlspecialchars($c['turno']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#bbb;">—</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!empty($c['vehiculo_id'])): ?>
                                <div class="vehiculo-info"><?php echo htmlspecialchars($c['marca'] . ' ' . $c['modelo']); ?></div>
                                <div class="vehiculo-dominio"><?php echo htmlspecialchars($c['dominio']); ?></div>
                            <?php else: ?>
                                <span class="sin-auto"><i class="fas fa-minus-circle"></i> Sin auto asignado</span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if (!empty($c['fecha_desde'])): ?>
                                <span style="font-size:0.82rem; color:#666;">
                                    <?php echo date('d/m/Y', strtotime($c['fecha_desde'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#bbb;">—</span>
                            <?php endif; ?>
                        </td>

                        <td style="max-width:200px; font-size:0.78rem; color:#555;">
                            <?php echo !empty($c['observaciones']) ? htmlspecialchars($c['observaciones']) : '<span style="color:#bbb; font-style:italic;">—</span>'; ?>
                        </td>

                        <td>
                            <div class="acciones-cell">
                                <button class="btn-accion btn-cargar" onclick="cargarRecaudacion(<?php echo $c['chofer_id']; ?>)">
                                    <i class="fas fa-dollar-sign"></i> Cargar
                                </button>
                                <button class="btn-accion btn-editar" onclick="editarChofer(<?php echo $c['chofer_id']; ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="btn-accion btn-cambiar" onclick="cambiarAuto(<?php echo $c['chofer_id']; ?>)">
                                    <i class="fas fa-exchange-alt"></i> Cambiar auto
                                </button>

                                <!-- NUEVO BOTÓN LIBERAR -->
                                <?php if (!empty($c['asignacion_id'])): ?>
                                    <button class="btn-accion btn-liberar" onclick="liberarChofer(<?php echo $c['chofer_id']; ?>)" title="Quitar auto y turno">
                                        <i class="fas fa-user-slash"></i> Liberar
                                    </button>
                                <?php else: ?>
                                    <button class="btn-accion btn-liberar" disabled title="No tiene auto asignado">
                                        <i class="fas fa-user-slash"></i> Liberar
                                    </button>
                                <?php endif; ?>

                                <button class="btn-accion btn-historial" onclick="verHistorial(<?php echo $c['chofer_id']; ?>)">
                                    <i class="fas fa-history"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>