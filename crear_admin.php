<?php
require_once 'funciones/funciones.php';
$con = conexion();
$con->set_charset("utf8mb4");

$usuario  = 'admin';
$password = 'admin123';   // ← CAMBIÁ ESTA CLAVE
$nombre   = 'Administrador';

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $con->prepare("
    INSERT INTO usuarios (usuario, password, nombre)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE password = VALUES(password), nombre = VALUES(nombre)
");
$stmt->bind_param("sss", $usuario, $hash, $nombre);

if ($stmt->execute()) {
    echo "<h2>✅ Usuario creado/actualizado</h2>";
    echo "<p>Usuario: <strong>$usuario</strong><br>Contraseña: <strong>$password</strong></p>";
    echo "<p style='color:red'><strong>⚠️ BORRÁ ESTE ARCHIVO AHORA</strong></p>";
    echo "<p><a href='index.php'>Ir al login →</a></p>";
} else {
    echo "❌ Error: " . $stmt->error;
}
$stmt->close();
$con->close();
