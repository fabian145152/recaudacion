<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$error = '';
$ok    = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $usuario  = trim($_POST['usuario'] ?? '');
    $nombre   = trim($_POST['nombre'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['password_confirm'] ?? '';
    $activo   = isset($_POST['activo']) ? 1 : 0;

    // Validaciones
    if ($usuario === '' || $password === '') {
        $error = 'Usuario y contraseña son obligatorios.';
    } elseif (!preg_match('/^[a-zA-Z0-9_.\-]{3,50}$/', $usuario)) {
        $error = 'El usuario debe tener entre 3 y 50 caracteres, solo letras, números, guion, guion bajo o punto.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $confirm) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        // ¿Ya existe?
        $stmt = $con->prepare("SELECT id FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $existe = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if ($existe) {
            $error = "Ya existe un usuario con el nombre '$usuario'.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $con->prepare("INSERT INTO usuarios (usuario, password, nombre, activo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $usuario, $hash, $nombre, $activo);

            if ($stmt->execute()) {
                $ok = true;
                $stmt->close();
                $con->close();
                setFlash('success', "Usuario '$usuario' creado correctamente.");
                header('Location: usuarios.php');
                exit;
            } else {
                $error = 'Error al crear el usuario: ' . $stmt->error;
            }
            $stmt->close();
        }
    }
}

$con->close();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Nuevo Usuario</title>
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
            max-width: 520px;
            margin: 0 auto;
            padding: 20px 15px;
        }

        h2 {
            color: #333;
            font-size: 1.4rem;
            margin: 10px 0 15px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        h2 i {
            color: #667eea;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 22px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .campo {
            margin-bottom: 14px;
        }

        .campo label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #555;
            margin-bottom: 5px;
        }

        .campo input[type=text],
        .campo input[type=password] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #dfe6ef;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .campo input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .15);
        }

        .campo small {
            color: #999;
            font-size: 0.72rem;
            display: block;
            margin-top: 4px;
        }

        .check-row {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .check-row input[type=checkbox] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .check-row label {
            margin: 0;
            cursor: pointer;
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

        .alerta-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .btn {
            padding: 9px 18px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.88rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
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

        .acciones {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <?php include "menu.php"; ?>

    <div class="container-u">
        <h2><i class="fas fa-user-plus"></i> Nuevo Usuario</h2>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <?php echo csrfField(); ?>

            <div class="campo">
                <label for="usuario">Usuario *</label>
                <input type="text" id="usuario" name="usuario" required maxlength="50"
                    value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
                    pattern="[a-zA-Z0-9_.\-]{3,50}"
                    title="3-50 caracteres, letras, números, guion, guion bajo o punto"
                    autofocus>
                <small>Solo letras, números, guion, guion bajo o punto. Mínimo 3 caracteres.</small>
            </div>

            <div class="campo">
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" maxlength="100"
                    value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>">
                <small>Opcional. Se muestra en el menú superior.</small>
            </div>

            <div class="campo">
                <label for="password">Contraseña *</label>
                <input type="password" id="password" name="password" required minlength="6">
                <small>Mínimo 6 caracteres.</small>
            </div>

            <div class="campo">
                <label for="password_confirm">Repetir contraseña *</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="6">
            </div>

            <div class="campo">
                <div class="check-row">
                    <input type="checkbox" id="activo" name="activo" checked>
                    <label for="activo">Usuario activo (puede ingresar al sistema)</label>
                </div>
            </div>

            <div class="acciones">
                <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Crear usuario</button>
            </div>
        </form>
    </div>

</body>

</html>