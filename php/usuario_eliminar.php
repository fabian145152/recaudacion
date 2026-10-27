<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: usuarios.php');
    exit;
}

checkCsrf();

$con = conexion();
$con->set_charset("utf8mb4");

$id = (int)($_POST['id'] ?? 0);
$mi_id = (int)$_SESSION['id_usuario'];

if ($id <= 0) {
    $con->close();
    setFlash('error', 'ID inválido.');
    header('Location: usuarios.php');
    exit;
}

if ($id === $mi_id) {
    $con->close();
    setFlash('error', 'No podés eliminarte a vos mismo.');
    header('Location: usuarios.php');
    exit;
}

// Obtener nombre antes de borrar (para el mensaje)
$stmt = $con->prepare("SELECT usuario FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $con->close();
    setFlash('error', 'Usuario no encontrado.');
    header('Location: usuarios.php');
    exit;
}

// Evitar borrar el último admin activo
$activos = (int)$con->query("SELECT COUNT(*) FROM usuarios WHERE activo = 1")->fetch_row()[0];
$stmt = $con->prepare("SELECT activo FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$activo_este = (int)$stmt->get_result()->fetch_assoc()['activo'];
$stmt->close();

if ($activos <= 1 && $activo_este === 1) {
    $con->close();
    setFlash('error', 'No podés borrar el último usuario activo del sistema.');
    header('Location: usuarios.php');
    exit;
}

$stmt = $con->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    setFlash('success', "Usuario '{$row['usuario']}' eliminado.");
} else {
    setFlash('error', 'Error al eliminar: ' . $stmt->error);
}
$stmt->close();
$con->close();

header('Location: usuarios.php');
exit;
