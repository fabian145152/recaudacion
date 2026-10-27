<?php
// auth.php ya se encarga de ob_start() y session_start() de forma segura
require_once __DIR__ . '/funciones/auth.php';
require_once __DIR__ . '/funciones/funciones.php';

// Si ya está logueado, directo al dashboard (preservando query params si los hay)
if (isset($_SESSION['id_usuario'])) {
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    header('Location: reca.php' . ($qs ? '?' . $qs : ''));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario  = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($usuario === '' || $password === '') {
        $error = 'Completá el usuario y la contraseña.';
    } else {
        $con = conexion();
        $con->set_charset("utf8mb4");

        $stmt = $con->prepare("SELECT id, usuario, password, nombre, activo FROM usuarios WHERE usuario = ? LIMIT 1");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $con->close();

        if ($fila && (int)$fila['activo'] === 1 && password_verify($password, $fila['password'])) {
            session_regenerate_id(true);

            $_SESSION['id_usuario'] = (int)$fila['id'];
            $_SESSION['usuario']    = $fila['usuario'];
            $_SESSION['nombre']     = $fila['nombre'] ?: $fila['usuario'];

            // Preservar query params al entrar al dashboard
            $qs = $_SERVER['QUERY_STRING'] ?? '';
            header('Location: reca.php' . ($qs ? '?' . $qs : ''));
            exit;
        } else {
            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="css/all.min.css">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login {
            background: white;
            width: 380px;
            max-width: 92%;
            padding: 32px;
            border-radius: 14px;
            box-shadow: 0 12px 32px rgba(0, 0, 0, .25);
        }

        h2 {
            text-align: center;
            color: #243447;
            margin: 0 0 6px 0;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        h2 i {
            color: #667eea;
        }

        .sub {
            text-align: center;
            color: #888;
            font-size: 0.85rem;
            margin: 0 0 20px 0;
        }

        label {
            display: block;
            margin: 14px 0 6px;
            font-weight: 600;
            color: #444;
            font-size: 0.85rem;
        }

        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccd3dc;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color .2s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .15);
        }

        button {
            width: 100%;
            margin-top: 22px;
            padding: 12px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 15px;
            font-weight: 600;
            transition: opacity .2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        button:hover {
            opacity: 0.9;
        }

        .error {
            color: #b42318;
            background: #feeceb;
            padding: 10px 12px;
            border-radius: 6px;
            font-size: 13px;
            border-left: 4px solid #b42318;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>

<body>
    <div class="login">
        <h2><i class="fas fa-taxi"></i> Sistema de Taxis</h2>
        <p class="sub">Ingresá con tu usuario y contraseña</p>

        <?php if ($error !== ''): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="on">
            <label for="usuario">Usuario</label>
            <input
                type="text"
                id="usuario"
                name="usuario"
                required
                autocomplete="username"
                autofocus
                value="<?= htmlspecialchars($_POST['usuario'] ?? '', ENT_QUOTES, 'UTF-8') ?>">

            <label for="password">Contraseña</label>
            <input
                type="password"
                id="password"
                name="password"
                required
                autocomplete="current-password">

            <button type="submit">
                <i class="fas fa-sign-in-alt"></i> Ingresar
            </button>
        </form>
    </div>
</body>

</html>