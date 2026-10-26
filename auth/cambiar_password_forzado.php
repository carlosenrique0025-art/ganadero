<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$error = $_SESSION['error'] ?? null;
$exito = $_SESSION['exito'] ?? null;
unset($_SESSION['error'], $_SESSION['exito']);

// Si el usuario envía el formulario con su nueva clave
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nueva_password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';

    if (strlen($nueva_password) < 6) {
        $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
        header('Location: cambiar_password_forzado.php');
        exit;
    }

    if ($nueva_password !== $confirmar_password) {
        $_SESSION['error'] = 'Las contraseñas no coinciden.';
        header('Location: cambiar_password_forzado.php');
        exit;
    }

    // Actualizar contraseña y desactivar la bandera de cambio forzado
    $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
    $stmt = $conexion->prepare("
        UPDATE usuarios 
        SET password = ?, debe_cambiar_password = 0 
        WHERE id = ?
    ");
    $stmt->execute([$hash, $_SESSION['usuario_id']]);

    // Redirigir según el rol
    if (($_SESSION['rol'] ?? '') === 'superadmin') {
        header('Location: ../admin/index.php');
    } else {
        header('Location: ../dashboard/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña Requerido | Sistema Ganadero</title>
    <link rel="stylesheet" href="../assets/css/portal.css">
    <style>
        body { background: #f1f5f0; display: flex; justify-content: center; align-items: center; min-height: 100vh; font-family: sans-serif; }
        .box { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        .btn-submit { background: #2d6a4f; color: white; border: none; padding: 10px; width: 100%; border-radius: 6px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
<div class="box">
    <h2>Cambio de contraseña requerido</h2>
    <p>Se te ha asignado una contraseña temporal. Por seguridad, debes definir una nueva contraseña antes de continuar.</p>

    <?php if ($error): ?>
        <div style="color: red; margin-bottom: 15px;"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div style="margin-bottom: 15px;">
            <label>Nueva contraseña:</label>
            <input type="password" name="password" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>
        <div style="margin-bottom: 20px;">
            <label>Confirmar contraseña:</label>
            <input type="password" name="confirmar_password" required style="width: 100%; padding: 8px; margin-top: 5px;">
        </div>
        <button type="submit" class="btn-submit">Guardar nueva contraseña</button>
    </form>
</div>
</body>
</html>