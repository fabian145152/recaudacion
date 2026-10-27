<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCsrf();

    $actual  = $_POST['actual'] ?? '';
    $nueva   = $_POST['nueva'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if ($actual === '' || $nueva === '') {
        $error = 'Completá todos los campos.';
    } elseif (strlen($nueva) < 6) {
        $error = 'La nueva contraseña debe tener al menos 6 caracteres.';
    } elseif ($nueva !== $confirm) {
        $error = 'Las contraseñas nuevas no coinciden.';
    } else {
        $stmt = $con->prepare("SELECT password FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $_SESSION['id_usuario']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row || !password_verify($actual, $row['password'])) {
            $error = 'La contraseña actual no es correcta.';
        } else {
            $hash = password_hash($nueva, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hash, $_SESSION['id_usuario']);
            if ($stmt->execute()) {
                $stmt->close();
                $con->close();
                setFlash('success', 'Contraseña actualizada correctamente.');
                header('Location: ../reca.php');
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

        <?php if ($error): ?>
            <div class="alerta alerta-error"><i class="fas fa-times-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="card">
            <?php echo csrfField(); ?>

            <div class="campo">
                <label for="actual">Contraseña actual *</label>
                <input type="password" id="actual" name="actual" required autocomplete="current-password">
            </div>

            <div class="campo">
                <label for="nueva">Nueva contraseña *</label>
                <input type="password" id="nueva" name="nueva" required minlength="6" autocomplete="new-password">
            </div>

            <div class="campo">
                <label for="confirm">Repetir nueva contraseña *</label>
                <input type="password" id="confirm" name="confirm" required minlength="6" autocomplete="new-password">
            </div>

            <div class="acciones">
                <a href="../reca.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Cambiar</button>
            </div>
        </form>
    </div>

</body>

</html>