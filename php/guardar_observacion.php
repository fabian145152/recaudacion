<?php
// Forzar que no se imprima nada raro antes del JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');

try {
    include_once "../funciones/funciones.php";

    if (!function_exists('conexion')) {
        throw new Exception('No se encontró la función conexion()');
    }

    $con = conexion();
    if (!$con) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    $con->set_charset("utf8mb4");

    $accion = $_POST['accion'] ?? 'crear';

    // === BORRAR ===
    if ($accion === 'borrar') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) throw new Exception('id inválido: ' . var_export($_POST['id'] ?? null, true));

        $stmt = $con->prepare("DELETE FROM observaciones WHERE id = ?");
        if (!$stmt) throw new Exception('Prepare falló: ' . $con->error);
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) throw new Exception('Execute falló: ' . $stmt->error);
        $stmt->close();
        $con->close();

        echo json_encode(['ok' => true, 'accion' => 'borrada', 'id' => $id]);
        exit;
    }

    // === CREAR ===
    $chofer_id = (int)($_POST['chofer_id'] ?? 0);
    $fecha     = trim($_POST['fecha'] ?? '');
    $texto     = trim($_POST['observacion'] ?? '');

    // Mensajes de error detallados para debug
    if ($chofer_id <= 0) {
        throw new Exception('chofer_id inválido. POST recibido: ' . json_encode($_POST));
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception('fecha inválida: ' . $fecha);
    }
    if ($texto === '') {
        throw new Exception('observación vacía');
    }

    $stmt = $con->prepare("
        INSERT INTO observaciones (chofer_id, fecha_creacion, observacion)
        VALUES (?, ?, ?)
    ");
    if (!$stmt) throw new Exception('Prepare falló: ' . $con->error);
    $stmt->bind_param("iss", $chofer_id, $fecha, $texto);
    if (!$stmt->execute()) throw new Exception('Execute falló: ' . $stmt->error);
    $nuevo_id = $stmt->insert_id;
    $stmt->close();
    $con->close();

    echo json_encode(['ok' => true, 'accion' => 'creada', 'id' => $nuevo_id]);
} catch (Throwable $e) {
    http_response_code(200); // Que el navegador no lo trate como error de red
    echo json_encode([
        'ok'    => false,
        'error' => $e->getMessage(),
        'file'  => basename($e->getFile()),
        'line'  => $e->getLine()
    ]);
}
