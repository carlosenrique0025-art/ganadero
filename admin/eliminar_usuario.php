<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

$admin_id = require_superadmin($conexion);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_check();

$usuario_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

// Evitar que el superadmin se elimine a sí mismo
if (!$usuario_id || $usuario_id === $admin_id) {
    header('Location: index.php');
    exit;
}

// Consultar usuario antes de borrar para registro en auditoría
$stmt = $conexion->prepare("SELECT id, nombre, username, rol FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Si el objetivo también es superadmin, bloquear eliminación
    if ($usuario['rol'] === 'superadmin') {
        $_SESSION['mensaje_error'] = "No se puede eliminar a otro superadministrador.";
        header('Location: index.php');
        exit;
    }

    // Registrar en auditoría antes de eliminar
    registrar_auditoria($conexion, $admin_id, 'ELIMINAR_USUARIO', $usuario_id, "Se eliminó permanentemente la cuenta de {$usuario['nombre']} ({$usuario['username']})");

    // Eliminar de la base de datos
    $stmtDel = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmtDel->execute([$usuario_id]);

    $_SESSION['mensaje_exito'] = "El usuario {$usuario['nombre']} ha sido eliminado correctamente de la base de datos.";
}

header('Location: index.php');
exit;