<?php
include_once "../funciones/funciones.php";

$con = conexion();
$con->set_charset("utf8mb4");

function limpiar($v)
{
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

function subirFoto($inputName, $dir = "../images/")
{
    if (!isset($_FILES[$inputName]) || $_FILES[$inputName]['error'] !== UPLOAD_ERR_OK) return null;
    $permitidos = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    if (!in_array(mime_content_type($_FILES[$inputName]['tmp_name']), $permitidos)) return null;
    $ext = pathinfo(basename($_FILES[$inputName]['name']), PATHINFO_EXTENSION);
    $nuevo = uniqid("foto_", true) . "." . strtolower($ext);
    return move_uploaded_file($_FILES[$inputName]['tmp_name'], $dir . $nuevo) ? $nuevo : null;
}

// === CAPTURAR DATOS DEL FORM ===
$turno        = strtoupper(limpiar($_POST['turno'] ?? ''));
$movil        = (int)($_POST['movil'] ?? 0);
$chofer_id    = (int)($_POST['chofer_id'] ?? 0);
$vehiculo_id  = (int)($_POST['vehiculo_id'] ?? 0) ?: null;
$fecha        = limpiar($_POST['fecha'] ?? date("Y-m-d"));
$observaciones = limpiar($_POST['observaciones'] ?? '');

$abono_diario     = floatval($_POST['abono_diario'] ?? 0);
$voucher_nuevo    = floatval($_POST['voucher_nuevo'] ?? 0);
$porcentaje_nuevo = floatval($_POST['porcentaje_nuevo'] ?? 10);
$mp_nuevo         = floatval($_POST['mp_nuevo'] ?? 0);
$ft_nuevo         = floatval($_POST['ft_nuevo'] ?? 0);

// === VALIDACIONES ===
if (empty($movil) || empty($turno) || empty($chofer_id)) {
    die("Error: Faltan datos obligatorios.");
}
if (!in_array($turno, ['DIA', 'NOCHE'])) {
    die("Error: El turno debe ser DÍA o NOCHE.");
}
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    $fecha = date("Y-m-d");
}

// === LEER VALORES ACTUALES DESDE LA BASE ===
$stmt = $con->prepare("
    SELECT voucher, porcent, comision, total, mp, ft, paga, abono_diario, observaciones 
    FROM recauda 
    WHERE fecha = ? AND movil = ? AND turno = ?
");
$stmt->bind_param("sis", $fecha, $movil, $turno);
$stmt->execute();
$reca_actual = $stmt->get_result()->fetch_assoc();
$stmt->close();

$voucher_prev      = floatval($reca_actual['voucher'] ?? 0);
$porcent_prev      = floatval($reca_actual['porcent'] ?? 10);
$comision_prev     = floatval($reca_actual['comision'] ?? 0);
$total_prev        = floatval($reca_actual['total'] ?? 0);
$mp_prev           = floatval($reca_actual['mp'] ?? 0);
$ft_prev           = floatval($reca_actual['ft'] ?? 0);
$abono_diario_prev = floatval($reca_actual['abono_diario'] ?? 0);
$obs_prev          = trim($reca_actual['observaciones'] ?? '');

// Si el usuario no ingresó un abono diario nuevo, usar el que ya estaba
if ($abono_diario <= 0 && $abono_diario_prev > 0) {
    $abono_diario = $abono_diario_prev;
}

// Si no escribió observaciones y había una guardada, mantenemos la guardada
if ($observaciones === '' && $obs_prev !== '') {
    $observaciones = $obs_prev;
}

// === SUMAR: nuevo + previo ===
$voucher_total = $voucher_prev + $voucher_nuevo;
$mp_total      = $mp_prev + $mp_nuevo;
$ft_total      = $ft_prev + $ft_nuevo;

// Comisión SIEMPRE 10%
$porcentaje_final = 10;
$comision_final = $voucher_total * $porcentaje_final / 100;
$total_final    = $voucher_total - $comision_final;

// Depósito del chofer: voucher neto + mp + ft
$deposito = $total_final + $mp_total + $ft_total;

// === DEUDA DEL DÍA ===
$debe_final = $abono_diario - $deposito;

// === SUBIR FOTO (si se subió una nueva) ===
$foto = subirFoto("foto_chofer");
if ($foto) {
    $stmt = $con->prepare("UPDATE choferes SET foto=? WHERE id=?");
    $stmt->bind_param("si", $foto, $chofer_id);
    $stmt->execute();
    $stmt->close();
}

$con->begin_transaction();
try {
    // === INSERT / UPDATE RECAUDA ===
    $sql = "
        INSERT INTO recauda 
        (fecha, chofer_id, vehiculo_id, movil, turno, abono_diario, paga, voucher, porcent, comision, total, mp, ft, debe, observaciones)
        VALUES (?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            chofer_id    = VALUES(chofer_id),
            vehiculo_id  = VALUES(vehiculo_id),
            abono_diario = VALUES(abono_diario),
            voucher      = VALUES(voucher),
            porcent      = VALUES(porcent),
            comision     = VALUES(comision),
            total        = VALUES(total),
            mp           = VALUES(mp),
            ft           = VALUES(ft),
            debe         = VALUES(debe),
            observaciones= VALUES(observaciones)
    ";

    $stmt = $con->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $con->error);
    }

    // 14 variables exactas, sin espacios en el string de tipos
    $stmt->bind_param(
        "siisssddddddds",
        $fecha,
        $chofer_id,
        $vehiculo_id,
        $movil,
        $turno,
        $abono_diario,
        $voucher_total,
        $porcentaje_final,
        $comision_final,
        $total_final,
        $mp_total,
        $ft_total,
        $debe_final,
        $observaciones
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al guardar: " . $stmt->error);
    }
    $stmt->close();

    // === OBTENER ID DE LA RECAUDA ===
    $stmt = $con->prepare("SELECT id FROM recauda WHERE fecha = ? AND movil = ? AND turno = ?");
    $stmt->bind_param("sis", $fecha, $movil, $turno);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) {
        throw new Exception("No se encontró la recauda recién guardada.");
    }
    $recauda_id = (int)$row['id'];

    // === BORRAR MOVIMIENTOS PREVIOS DE CUENTA CORRIENTE ===
    $stmt = $con->prepare("
        DELETE FROM cuenta_corriente 
        WHERE chofer_id = ? AND fecha = ? AND (tipo = 'DEUDA' OR tipo = 'A CUENTA')
    ");
    $stmt->bind_param("is", $chofer_id, $fecha);
    $stmt->execute();
    $stmt->close();

    // === CREAR MOVIMIENTO EN CUENTA CORRIENTE ===
    if (abs($debe_final) > 0.001) {
        if ($debe_final > 0) {
            $tipo = 'DEUDA';
            $monto_cc = -$debe_final;
            $descripcion = "Quedó debiendo del turno $turno del $fecha";
        } else {
            $tipo = 'A CUENTA';
            $monto_cc = -$debe_final;
            $descripcion = "A cuenta del turno $turno del $fecha";
        }

        $stmt = $con->prepare("
            INSERT INTO cuenta_corriente (chofer_id, fecha, monto, tipo, descripcion, recauda_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("isdssi", $chofer_id, $fecha, $monto_cc, $tipo, $descripcion, $recauda_id);
        $stmt->execute();
        $stmt->close();
    }

    $con->commit();

    $nueva_redirect = (isset($_POST['nueva']) && $_POST['nueva'] == '1') ? '&nueva=1' : '';
    $fecha_url = urlencode($fecha);
    echo "<script>alert('✅ Datos y observaciones guardados correctamente.'); window.location='cargar_selector.php?fecha=$fecha_url$nueva_redirect';</script>";
} catch (Exception $e) {
    $con->rollback();
    echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}

$con->close();
