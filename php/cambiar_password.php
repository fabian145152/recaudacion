<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$error = '';

// === DETERMINAR A QUÉ USUARIO LE CAMBIAMOS LA CLAVE ===
// Si viene un ID por la URL, es un admin cambiando la clave de otro. Si no, es mi propia cuenta.
$id_objetivo = isset($_GET['id']) ? (int)$_GET['id'] : (int)$_SESSION['id_usuario'];
$es_mi_propia_cuenta = ($id_objetivo === (int)$_SESSION['id_usuario']);

// Obtener los datos del usuario objetivo para mostrar en pantalla
$stmt = $con->prepare("SELECT usuario, nombre FROM usuarios WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id_objetivo);
$stmt->execute();
$usuario_objetivo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$usuario_objetivo) {
    $con->close();
    setFlash('error', 'Usuario no encontrado.');
    header('Location: usuarios.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $actual  = $_POST['actual'] ?? '';
    $nueva   = $_POST['nueva'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($nueva === '' || ($es_mi_propia_cuenta && $actual === '')) {
        $error = 'Completá todos los campos obligatorios.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirm) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } else {
        // Si es mi propia cuenta, verificar que la contraseña actual sea correcta
        if ($es_mi_propia_cuenta) {
            $stmt = $con->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $id_objetivo);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$row || !password_verify($actual, $row['password'])) {
                $error = 'La contraseña actual no es correcta.';
            }
        }

        // Si no hay errores, actualizamos
        if (!$error) {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $id_objetivo);

            if ($stmt->execute()) {
                $stmt->close();
                $con->close();
                setFlash('success', 'Contraseña actualizada correctamente.');

                // Si cambié mi propia clave, voy al dashboard. Si cambié la de otro, vuelvo a la lista.
                if ($es_mi_propia_cuenta) {
                    header('Location: ../reca.php');
                } else {
                    header('Location: usuarios.php');
                }
                exit;
            } else {
                $error = 'Error al guardar: ' . $stmt->error;
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
    <title>Cambiar contraseña</title>
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
            max-width: 480px;
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

        .campo input {
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
        <h2><i class="fas fa-key"></i> Cambiar contraseña</h2>

        <?php if (!$es_mi_propia_cuenta): ?>
            <div class="alerta" style="background: #cce5ff; color: #004085; border-left: 4px solid #004085; margin-bottom: 15px;">
                <i class="fas fa-info-circle"></i> Estás cambiando la clave del usuario: <strong><?php echo htmlspecialchars($usuario_objetivo['usuario']); ?></strong>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alerta alerta-error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <?php echo csrfField(); ?>

            <!-- Mantenemos el ID para que el POST sepa a quién actualizar -->
            <input type="hidden" name="id_objetivo" value="<?php echo $id_objetivo; ?>">

            <?php if ($es_mi_propia_cuenta): ?>
                <div class="campo">
                    <label for="actual">Contraseña actual *</label>
                    <input type="password" id="actual" name="actual" required autocomplete="current-password">
                </div>
            <?php endif; ?>

            <div class="campo">
                <label for="nueva">Nueva contraseña *</label>
                <input type="password" id="nueva" name="nueva" required minlength="6" autocomplete="new-password">
            </div>

            <div class="campo">
                <label for="confirm">Repetir nueva contraseña *</label>
                <input type="password" id="confirm" name="confirm" required minlength="6" autocomplete="new-password">
            </div>

            <div class="acciones">
                <?php if ($es_mi_propia_cuenta): ?>
                    <a href="../reca.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <?php else: ?>
                    <a href="usuarios.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <?php endif; ?>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cambiar</button>
            </div>
        </form>
    </div>

</body>

</html>