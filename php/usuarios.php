<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$usuarios = $con->query("
    SELECT id, usuario, nombre, activo, creado
    FROM usuarios
    ORDER BY activo DESC, usuario ASC
")->fetch_all(MYSQLI_ASSOC);

$con->close();

$flash = getFlash();
$mi_id = (int)$_SESSION['id_usuario'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Usuarios del Sistema</title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <style>
        body {
            background: #f4f6fa;
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 13px;
            margin: 0;
        }

        .container-u {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        h2 {
            color: #333;
            font-size: 1.4rem;
            margin: 10px 0 15px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        h2 span.titulo {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        h2 i {
            color: #667eea;
        }

        .btn {
            padding: 7px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-warning {
            background: #ffc107;
            color: #856404;
        }

        .btn-warning:hover {
            color: #856404;
            opacity: 0.9;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            color: white;
            opacity: 0.9;
        }

        .btn-secondary {
            background: #eef1f7;
            color: #555;
        }

        .btn-secondary:hover {
            background: #dde3ec;
            color: #333;
        }

        .btn-sm {
            padding: 4px 9px;
            font-size: 0.75rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        table.usuarios {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        table.usuarios th {
            background: #f8f9fc;
            color: #555;
            padding: 10px;
            text-align: left;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            border-bottom: 2px solid #eef1f7;
        }

        table.usuarios td {
            padding: 10px;
            border-bottom: 1px solid #f0f3f9;
            vertical-align: middle;
        }

        table.usuarios tr:hover {
            background: #fafbfe;
        }

        .badge-activo {
            background: #d4edda;
            color: #155724;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-inactivo {
            background: #f8d7da;
            color: #721c24;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .badge-yo {
            background: #cce5ff;
            color: #004085;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.68rem;
            font-weight: 700;
            margin-left: 5px;
        }

        .acciones {
            display: flex;
            gap: 5px;
            justify-content: flex-end;
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

        .alerta-info {
            background: #cce5ff;
            color: #004085;
            border-left: 4px solid #004085;
        }

        form.inline {
            display: inline;
            margin: 0;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="container-u">
        <h2>
            <span class="titulo"><i class="fas fa-user-shield"></i> Usuarios del Sistema</span>
            <a href="usuario_nuevo.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo usuario</a>
        </h2>

        <?php if ($flash): ?>
            <div class="alerta alerta-<?php echo $flash['tipo']; ?>">
                <i class="fas <?php echo $flash['tipo'] === 'success' ? 'fa-check-circle' : ($flash['tipo'] === 'error' ? 'fa-times-circle' : 'fa-info-circle'); ?>"></i>
                <?php echo htmlspecialchars($flash['msg']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <?php if (empty($usuarios)): ?>
                <p style="text-align:center; color:#999; padding:20px; margin:0;">No hay usuarios cargados.</p>
            <?php else: ?>
                <table class="usuarios">
                    <thead>
                        <tr>
                            <th style="width:50px;">ID</th>
                            <th>Usuario</th>
                            <th>Nombre</th>
                            <th style="width:100px;">Estado</th>
                            <th style="width:130px;">Creado</th>
                            <th style="width:230px; text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <?php $es_yo = ((int)$u['id'] === $mi_id); ?>
                            <tr>
                                <td><?php echo $u['id']; ?></td>
                                <td><strong><?php echo htmlspecialchars($u['usuario']); ?></strong><?php if ($es_yo): ?><span class="badge-yo">VOS</span><?php endif; ?></td>
                                <td><?php echo htmlspecialchars($u['nombre'] ?: '—'); ?></td>
                                <td>
                                    <?php if ((int)$u['activo'] === 1): ?>
                                        <span class="badge-activo">Activo</span>
                                    <?php else: ?>
                                        <span class="badge-inactivo">Inactivo</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($u['creado'])); ?></td>
                                <td>
                                    <div class="acciones">
                                        <a href="usuario_editar.php?id=<?php echo (int)$u['id']; ?>" class="btn btn-warning btn-sm" title="Editar"><i class="fas fa-pen"></i> Editar</a>

                                        <?php if (!$es_yo): ?>
                                            <form method="POST" action="usuario_eliminar.php" class="inline"
                                                onsubmit="return confirm('¿Eliminar definitivamente al usuario <?php echo htmlspecialchars(addslashes($u['usuario'])); ?>?\n\nEsta acción no se puede deshacer.');">
                                                <?php echo csrfField(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int)$u['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <p style="margin-top:15px; text-align:center;">
            <a href="../reca.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al dashboard</a>
        </p>
    </div>

</body>

</html>