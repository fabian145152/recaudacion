<?php

/**
 * Helper de autenticación
 * Uso desde archivos en /php/ o raíz:
 *   require_once "../funciones/auth.php";
 *   requireLogin();
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin()
{
    if (!isset($_SESSION['id_usuario'])) {
        $script = $_SERVER['SCRIPT_NAME'] ?? '';
        $en_php = (strpos($script, '/php/') !== false);
        $destino = $en_php ? '../index.php' : 'index.php';
        header('Location: ' . $destino);
        exit;
    }
}

function usuarioActual()
{
    return [
        'id'      => $_SESSION['id_usuario'] ?? null,
        'usuario' => $_SESSION['usuario'] ?? '',
        'nombre'  => $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? ''),
    ];
}

// ============================================================
// CSRF (protección contra formularios falsos)
// ============================================================
function csrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars(csrfToken()) . '">';
}

function checkCsrf()
{
    $token = $_POST['csrf_token'] ?? '';
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Token CSRF inválido. Recargá la página e intentá de nuevo.');
    }
}

// ============================================================
// Flash messages (mensajes entre redirecciones)
// ============================================================
function setFlash($tipo, $mensaje)
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensaje];
}

function getFlash()
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}
