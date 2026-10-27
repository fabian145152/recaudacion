<?php
require_once __DIR__ . '/../funciones/auth.php';
requireLogin();
require_once __DIR__ . '/../funciones/funciones.php';

$con = conexion();
$con->set_charset("utf8mb4");

$id = (int)($_GET['q'] ?? 0);
if ($id <= 0) {
    echo "<script>alert('ID inválido.'); window.location='taxis.php';</script>";
    exit;
}

// Verificar que existe y no esté ya eliminado
$stmt = $con->prepare("SELECT nombre, eliminado FROM choferes WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$chofer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chofer) {
    echo "<script>alert('Chofer no encontrado.'); window.location='taxis.php';</script>";
    exit;
}

if ((int)($chofer['eliminado'] ?? 0) === 1) {
    echo "<script>alert('Este chofer ya fue eliminado.'); window.location='taxis.php';</script>";
    exit;
}

// === QUIÉN Y CUÁNDO ===
$usuario = $_SESSION['nombre'] ?? ($_SESSION['usuario'] ?? 'Desconocido');
$ahora   = date('Y-m-d H:i:s');

$con->begin_transaction();
try {
    // Soft delete del chofer + tracking
    $stmt = $con->prepare("
        UPDATE choferes 
        SET activo = 0, 
            eliminado = 1, 
            eliminado_por = ?, 
            eliminado_fecha = ?
        WHERE id = ?
    ");
    $stmt->bind_param("ssi", $usuario, $ahora, $id);
    if (!$stmt->execute()) {
        throw new Exception("Error al eliminar el chofer: " . $stmt->error);
    }
    $stmt->close();

    // Desactivar asignaciones activas del chofer
    $stmt = $con->prepare("
        UPDATE asignaciones_auto 
        SET activa = 0, fecha_hasta = CURDATE()
        WHERE chofer_id = ? AND activa = 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    $con->commit();

    $nombre = addslashes($chofer['nombre']);
    $user   = addslashes($usuario);
    $fecha  = addslashes($ahora);

    echo "<script>
        alert('✅ Chofer eliminado correctamente.\\n\\nChofer: $nombre\\nEliminado por: $user\\nFecha: $fecha\\n\\nSus datos históricos (recaudación, deudas, etc.) se conservan.');
        window.location='taxis.php';
    </script>";
    exit;
} catch (Exception $e) {
    $con->rollback();
    $msg = addslashes($e->getMessage());
    echo "<script>alert('❌ Error: $msg'); window.location='taxis.php';</script>";
    exit;
}

$con->close();
