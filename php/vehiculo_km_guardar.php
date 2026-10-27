<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: vehiculo_lista.php?err=id');
    exit;
}

$km_guardados = max(0, (int)($_POST['km_guardados'] ?? 0));
$km_actuales  = max(0, (int)($_POST['km_actuales'] ?? 0));

function parseNullableInt($v)
{
    if ($v === null || $v === '') return null;
    $n = (int)$v;
    return $n > 0 ? $n : null;
}
$km_aceite = parseNullableInt($_POST['km_aceite'] ?? null);
$km_bujias = parseNullableInt($_POST['km_bujias'] ?? null);
$km_correa = parseNullableInt($_POST['km_correa'] ?? null);
$km_grasa  = parseNullableInt($_POST['km_grasa'] ?? null);

// === QUIÉN ESTÁ LOGUEADO Y CUÁNDO ===
$usuario_actual = $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? 'Desconocido');
$ahora = date('Y-m-d H:i:s');

$stmt = $con->prepare("
    UPDATE vehiculos SET 
        km_guardados     = ?, 
        km_actuales      = ?, 
        km_aceite        = ?, 
        km_bujias        = ?, 
        km_correa        = ?, 
        km_grasa         = ?,
        km_editado_por   = ?,
        km_editado_fecha = ?
    WHERE id = ?
");
$stmt->bind_param(
    "iiiiiissi",
    $km_guardados,
    $km_actuales,
    $km_aceite,
    $km_bujias,
    $km_correa,
    $km_grasa,
    $usuario_actual,
    $ahora,
    $id
);
$stmt->execute();
$stmt->close();
$con->close();

header('Location: vehiculo_lista.php?ok=km');
exit;
