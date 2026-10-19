<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // --- DATOS DEL VEHÍCULO ---
    $tipo      = $_POST['tipo'] ?? 'TAXI';
    $movil     = filter_var($_POST['movil'] ?? 0, FILTER_VALIDATE_INT);
    $marca     = trim($_POST['marca'] ?? '');
    $modelo    = trim($_POST['modelo'] ?? '');
    $dominio   = strtoupper(trim($_POST['dominio'] ?? ''));
    $color     = strtoupper(trim($_POST['color'] ?? ''));

    // --- VALIDACIONES ---
    if ($movil === false || $movil < 1 || $movil > 2999) {
        die("<script>alert('Error: El número de móvil debe estar entre 1 y 2999.'); window.history.back();</script>");
    }
    if (empty($marca) || empty($modelo) || empty($dominio)) {
        die("<script>alert('Error: Marca, modelo y dominio son obligatorios.'); window.history.back();</script>");
    }

    // Verificar que el móvil no exista ya
    $check = $con->prepare("SELECT COUNT(*) as total FROM moviles WHERE movil = ?");
    $check->bind_param("i", $movil);
    $check->execute();
    $res = $check->get_result()->fetch_assoc();
    $check->close();

    if ($res['total'] > 0) {
        die("<script>alert('Error: Ya existe un móvil con el número $movil.'); window.history.back();</script>");
    }

    // --- DATOS POR DEFECTO PARA CHOFERES (LIBRES) ---
    $nombre = "LIBRE";
    $email = "";
    $cel = "";
    $barrio = "";
    $dir = "";
    $cp = "";
    $foto_auto = "";

    // --- FUNCIÓN INSERTAR ---
    function insertarUnidad($con, $turno, $nombre, $email, $cel, $barrio, $dir, $cp, $tipo, $movil, $marca, $modelo, $dominio, $color, $foto_auto)
    {
        $stmt = $con->prepare("INSERT INTO moviles 
            (turno, nombre, email, cel, barrio, dir, cp, tipo, movil, marca, modelo, dominio, color, foto_auto)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "ssssssssisssss",
            $turno,
            $nombre,
            $email,
            $cel,
            $barrio,
            $dir,
            $cp,
            $tipo,
            $movil,
            $marca,
            $modelo,
            $dominio,
            $color,
            $foto_auto
        );
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    // --- INSERTAR LOS DOS TURNOS ---
    $okDia = insertarUnidad($con, "DIA", $nombre, $email, $cel, $barrio, $dir, $cp, $tipo, $movil, $marca, $modelo, $dominio, $color, $foto_auto);
    $okNoche = insertarUnidad($con, "NOCHE", $nombre, $email, $cel, $barrio, $dir, $cp, $tipo, $movil, $marca, $modelo, $dominio, $color, $foto_auto);

    $con->close();

    if ($okDia && $okNoche) {
        echo "<script>alert('✅ Unidad $movil creada correctamente con los turnos DÍA y NOCHE.'); window.location.href='taxis.php';</script>";
    } else {
        echo "<script>alert('⚠️ Hubo un problema al crear uno de los turnos.'); window.location.href='taxis.php';</script>";
    }
}
