<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

if (!empty($_SESSION['admin_original'])) {
    $admin_id = (int)$_SESSION['admin_original'];

    // Buscar datos del superadmin original
    $stmt = $conexion->prepare("SELECT id, rancho_id, nombre, username, email, rol FROM usuarios WHERE id = ? LIMIT 1");
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Registrar en auditoría
        registrar_auditoria($conexion, $admin_id, 'SALIR_SUPLANTACION', null, "Salió de la cuenta de {$_SESSION['nombre']}");

        // Restaurar sesión de superadmin
        unset($_SESSION['admin_original']);
        iniciar_sesion_usuario($admin);

        header('Location: index.php');
        exit;
    }
}

session_destroy();
header('Location: ../index.php');
exit;