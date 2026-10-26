<?php
// admin/limpiar_auditoria.php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Validar que el usuario sea superadmin
$admin_id = require_superadmin($conexion);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_check();

try {
    // 1. Registrar primero la acción de limpieza en la propia bitácora
    registrar_auditoria($conexion, $admin_id, 'LIMPIAR_AUDITORIA', null, "El superadministrador vació la bitácora de auditoría.");

    // 2. Vaciar la tabla (mantiene el registro de la limpieza o elimina todo)
    $stmt = $conexion->prepare("TRUNCATE TABLE auditoria_admin");
    $stmt->execute();

    // 3. Volver a registrar el evento de vaciado como único registro inicial
    registrar_auditoria($conexion, $admin_id, 'LIMPIAR_AUDITORIA', null, "Bitácora reiniciada correctamente por el superadministrador.");

    $_SESSION['mensaje_exito'] = "La bitácora de auditoría ha sido vaciada correctamente.";
} catch (PDOException $e) {
    $_SESSION['mensaje_error'] = "Error al intentar vaciar la bitácora: " . $e->getMessage();
}

header('Location: index.php');
exit;