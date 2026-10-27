<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$id = (int)($_GET['q'] ?? 0);
if ($id <= 0) {
    echo "<script>alert('ID inválido.'); window.location='chofer_eliminados.php?err=1';</script>";
    exit;
}

// Verificar que existe y esté eliminado
$stmt = $con->prepare("SELECT nombre, eliminado FROM choferes WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) {
    echo "<script>alert('Chofer no encontrado.'); window.location='chofer_eliminados.php?err=1';</script>";
    exit;
}

if ((int)($chofer['eliminado'] ?? 0) === 0) {
    echo "<script>alert('Este chofer no está eliminado.'); window.location='taxis.php';</script>";
    exit;
}

// Restaurar
$stmt = $con->prepare("
    UPDATE choferes 
    SET activo = 1,
        eliminado = 0,
        eliminado_por = NULL,
        eliminado_fecha = NULL
    WHERE id = ?
");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    $con->close();
    header('Location: chofer_eliminados.php?ok=restaurado');
    exit;
} else {
    $err = addslashes($stmt->error);
    $stmt->close();
    $con->close();
    echo "<script>alert('❌ Error: $err'); window.location='chofer_eliminados.php?err=1';</script>";
    exit;
}
