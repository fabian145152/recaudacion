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

// Recibimos si estamos en modo edición
$modo_edicion = isset($_POST['modo_edicion']) && $_POST['modo_edicion'] == '1';

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
$mp_prev           = floatval($reca_actual['mp'] ?? 0);
$ft_prev           = floatval($reca_actual['ft'] ?? 0);
$abono_diario_prev = floatval($reca_actual['abono_diario'] ?? 0);
$obs_prev          = trim($reca_actual['observaciones'] ?? '');

// El formulario SIEMPRE envía abono_diario (aunque sea "" -> 0). Si el usuario
// lo deja vacío o pone 0, respetamos ese valor. Solo restauramos el previo si
// por algún motivo el campo NO vino en el POST (ej. submit externo).
if (!array_key_exists('abono_diario', $_POST) && $abono_diario_prev > 0) {
    $abono_diario = $abono_diario_prev;
}

// Si no escribió observaciones y había una guardada, mantenemos la guardada
if ($observaciones === '' && $obs_prev !== '') {
    $observaciones = $obs_prev;
}

// === SUMAR O REEMPLAZAR SEGÚN EL MODO ===
if ($modo_edicion) {
    // MODO EDICIÓN: Pisa el importe exacto
    $voucher_total = $voucher_nuevo;
    $mp_total      = $mp_nuevo;
    $ft_total      = $ft_nuevo;
} else {
    // MODO NORMAL: Suma lo nuevo a lo que ya estaba
    $voucher_total = $voucher_prev + $voucher_nuevo;
    $mp_total      = $mp_prev + $mp_nuevo;
    $ft_total      = $ft_prev + $ft_nuevo;
}

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
    $stmt = $con->prepare("
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
    ");
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
    $stmt->execute();
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

    // === ACTUALIZAR CUENTA CORRIENTE ===
    $stmt = $con->prepare("
        SELECT id FROM cuenta_corriente 
        WHERE chofer_id = ? AND fecha = ? AND (tipo = 'DEUDA' OR tipo = 'A CUENTA')
    ");
    $stmt->bind_param("is", $chofer_id, $fecha);
    $stmt->execute();
    $cc_existente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

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

        if ($cc_existente) {
            $stmt = $con->prepare("
                UPDATE cuenta_corriente 
                SET monto = ?, tipo = ?, descripcion = ?, recauda_id = ? 
                WHERE id = ?
            ");
            $stmt->bind_param("dssii", $monto_cc, $tipo, $descripcion, $recauda_id, $cc_existente['id']);
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $con->prepare("
                INSERT INTO cuenta_corriente (chofer_id, fecha, monto, tipo, descripcion, recauda_id)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("isdssi", $chofer_id, $fecha, $monto_cc, $tipo, $descripcion, $recauda_id);
            $stmt->execute();
            $stmt->close();
        }
    } else {
        if ($cc_existente) {
            $stmt = $con->prepare("DELETE FROM cuenta_corriente WHERE id = ?");
            $stmt->bind_param("i", $cc_existente['id']);
            $stmt->execute();
            $stmt->close();
        }
    }

    $con->commit();

    // === OBTENER NOMBRE DEL CHOFER PARA EL CONFIRM ===
    $stmt_nombre = $con->prepare("SELECT nombre FROM choferes WHERE id = ?");
    $stmt_nombre->bind_param("i", $chofer_id);
    $stmt_nombre->execute();
    $row_chofer = $stmt_nombre->get_result()->fetch_assoc();
    $nombre_chofer = $row_chofer ? $row_chofer['nombre'] : 'Desconocido';
    $stmt_nombre->close();

    $nueva_redirect = (isset($_POST['nueva']) && $_POST['nueva'] == '1') ? '&nueva=1' : '';
    $fecha_url = urlencode($fecha);

    // Formatear montos y fechas para el mensaje
    $fmt_deposito = number_format($deposito, 2, ',', '.');
    $fmt_abono = number_format($abono_diario, 2, ',', '.');
    $fecha_mostrar = date('d/m/Y', strtotime($fecha));

    $msj = $modo_edicion ? 'Importes corregidos' : 'Datos guardados';

    // Si hay observaciones, agregarlas al mensaje
    $texto_obs = "";
    if (!empty($observaciones)) {
        $texto_obs = "\\nObservaciones: " . addslashes($observaciones);
    }

    // Generar el mensaje de confirmación con FECHA
    echo "<script>
        var confirmar = confirm('✅ $msj correctamente.\\n\\nFecha: $fecha_mostrar\\nChofer: $nombre_chofer\\nAbono esperado: \\$$fmt_abono\\nTotal Depositado: \\$$fmt_deposito$texto_obs\\n\\n¿Confirmás que los datos son correctos?');
        if (confirmar) {
            window.location='cargar_selector.php?fecha=$fecha_url$nueva_redirect';
        } else {
            window.history.back();
        }
    </script>";
} catch (Exception $e) {
    $con->rollback();
    echo "<script>alert('❌ Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
}

$con->close();
