<?php
include_once "../funciones/funciones.php";
$con = conexion();
$con->set_charset("utf8mb4");

// Auto-crear tabla
$con->query("CREATE TABLE IF NOT EXISTS caja_movimientos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    tipo ENUM('INGRESO','EXTRACCION') NOT NULL,
    medio ENUM('MP','FT') NOT NULL,
    monto DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descripcion VARCHAR(255) DEFAULT '',
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_fecha (fecha),
    INDEX idx_medio (medio),
    INDEX idx_tipo (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$fecha = $_POST['fecha'] ?? date('Y-m-d');
$tipo = $_POST['tipo'] ?? 'INGRESO';
$medio = $_POST['medio'] ?? 'FT';
$monto = floatval($_POST['monto'] ?? 0);
$descripcion = trim($_POST['descripcion'] ?? '');

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) $fecha = date('Y-m-d');
if (!in_array($tipo, ['INGRESO', 'EXTRACCION'])) die("Tipo inválido.");
if (!in_array($medio, ['MP', 'FT'])) die("Medio inválido.");
if ($monto <= 0) die("El monto debe ser mayor a 0.");

$stmt = $con->prepare("INSERT INTO caja_movimientos (fecha, tipo, medio, monto, descripcion) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssds", $fecha, $tipo, $medio, $monto, $descripcion);
$stmt->execute();
$stmt->close();
$con->close();

header("Location: caja_movimientos.php?fecha=" . urlencode($fecha) . "&msg=add");
exit;
