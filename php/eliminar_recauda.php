<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

$id_recauda = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$volver = isset($_GET['volver']) ? $_GET['volver'] : 'index.php';

if ($id_recauda > 0) {
    $con->begin_transaction();
    try {
        // 1. Obtener datos del registro antes de borrarlo (para saber qué borrar de la cuenta corriente)
        $stmt = $con->prepare("SELECT chofer_id, fecha FROM recauda WHERE id = ?");
        $stmt->bind_param("i", $id_recauda);
        $stmt->execute();
        $reca = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($reca) {
            $chofer_id = $reca['chofer_id'];
            $fecha = $reca['fecha'];

            // 2. Borrar la recaudación
            $stmt_del = $con->prepare("DELETE FROM recauda WHERE id = ?");
            $stmt_del->bind_param("i", $id_recauda);
            $stmt_del->execute();
            $stmt_del->close();

            // 3. Borrar el movimiento en la cuenta corriente generado por esta recaudación
            $stmt_cc = $con->prepare("DELETE FROM cuenta_corriente WHERE chofer_id = ? AND fecha = ? AND (tipo = 'DEUDA' OR tipo = 'A CUENTA')");
            $stmt_cc->bind_param("is", $chofer_id, $fecha);
            $stmt_cc->execute();
            $stmt_cc->close();
        }

        $con->commit();
        echo "<script>alert('✅ Registro eliminado correctamente.'); window.location='$volver';</script>";
    } catch (Exception $e) {
        $con->rollback();
        echo "<script>alert('❌ Error al eliminar: " . addslashes($e->getMessage()) . "'); window.location='$volver';</script>";
    }
} else {
    echo "<script>window.location='$volver';</script>";
}

$con->close();
?>