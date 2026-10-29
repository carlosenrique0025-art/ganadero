<?php
require_once __DIR__ . '/config/database.php';

// Compatibilidad de variables de conexion
if (!isset($conexion) && isset($pdo)) {
    $conexion = $pdo;
}

$nueva_clave = 'admin123';
$hash_real = password_hash($nueva_clave, PASSWORD_BCRYPT);

$stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE username = 'INGRID'");
$stmt->execute([$hash_real]);

echo "<h3>¡Contraseña restablecida correctamente!</h3>";
echo "<p>Usuario: <strong>INGRID</strong></p>";
echo "<p>Contraseña: <strong>admin123</strong></p>";
echo "<a href='index.php'>Ir al Login</a>";