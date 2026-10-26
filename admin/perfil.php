<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Validar que la persona logueada sea superadmin
$admin_id = require_superadmin($conexion);

$mensaje_error = '';

// Consultar datos del Superadmin logueado
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$admin_id]);
$superadmin = $stmt->fetch(PDO::FETCH_ASSOC);

// Consultar el rancho actual si tiene uno asignado
$nombre_rancho_actual = '';
if (!empty($superadmin['rancho_id'])) {
    $stmtR = $conexion->prepare("SELECT nombre FROM ranchos WHERE id = ? LIMIT 1");
    $stmtR->execute([$superadmin['rancho_id']]);
    $nombre_rancho_actual = $stmtR->fetchColumn() ?: '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nombre          = trim($_POST['nombre'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $email           = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $nombre_rancho   = trim($_POST['nombre_rancho'] ?? '');
    $nueva_password  = $_POST['nueva_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($nombre && $username && $email) {
        // Verificar duplicados de correo o usuario con otros registros
        $stmtCheck = $conexion->prepare("SELECT id FROM usuarios WHERE (email = ? OR username = ?) AND id != ? LIMIT 1");
        $stmtCheck->execute([$email, $username, $admin_id]);

        if ($stmtCheck->fetch()) {
            $mensaje_error = "El correo o nombre de usuario ya está registrado por otro usuario.";
        } else {
            // Manejo de la actualización / creación del Rancho
            $rancho_id = $superadmin['rancho_id'] ?? null;

            if (!empty($nombre_rancho)) {
                if ($rancho_id) {
                    // Si ya tiene rancho asignado, actualizar su nombre
                    $stmtUpR = $conexion->prepare("UPDATE ranchos SET nombre = ? WHERE id = ?");
                    $stmtUpR->execute([$nombre_rancho, $rancho_id]);
                } else {
                    // Si no tiene rancho asignado, crear un nuevo registro de rancho
                    $stmtInsR = $conexion->prepare("INSERT INTO ranchos (nombre, usuario_id) VALUES (?, ?)");
                    $stmtInsR->execute([$nombre_rancho, $admin_id]);
                    $rancho_id = $conexion->lastInsertId();
                }
            }

            // Actualizar información del perfil del Superadmin (incluyendo el rancho_id)
            $stmtUp = $conexion->prepare("UPDATE usuarios SET nombre = ?, username = ?, email = ?, rancho_id = ? WHERE id = ?");
            $stmtUp->execute([$nombre, $username, $email, $rancho_id, $admin_id]);

            // Actualizar las variables de sesión al instante
            $_SESSION['rancho_id'] = $rancho_id;
            $_SESSION['nombre_rancho'] = $nombre_rancho;

            // Si escribió una nueva contraseña, actualizarla
            if (!empty($nueva_password)) {
                if ($nueva_password === $confirm_password) {
                    if (strlen($nueva_password) >= 6) {
                        $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                        $stmtPass = $conexion->prepare("UPDATE usuarios SET password = ?, debe_cambiar_password = 0 WHERE id = ?");
                        $stmtPass->execute([$hash, $admin_id]);
                    } else {
                        $mensaje_error = "La nueva contraseña debe tener al menos 6 caracteres.";
                    }
                } else {
                    $mensaje_error = "Las contraseñas no coinciden.";
                }
            }

            if (!$mensaje_error) {
                registrar_auditoria($conexion, $admin_id, 'EDITAR_PERFIL_SUPERADMIN', $admin_id, "El superadministrador actualizó sus datos de perfil y nombre de rancho.");
                $_SESSION['mensaje_exito'] = "Perfil y datos del rancho actualizados correctamente.";
                header('Location: index.php');
                exit;
            }
        }
    } else {
        $mensaje_error = "Por favor ingresa un nombre, usuario y correo válidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil | Sistema Ganadero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde-oscuro: #1b4332;
            --verde: #2d6a4f;
            --fondo: #f1f5f0;
            --tarjeta: #ffffff;
            --borde: #e0e8e1;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--fondo); padding: 30px; }
        .container { max-width: 550px; margin: 0 auto; background: var(--tarjeta); padding: 25px; border-radius: 12px; border: 1px solid var(--borde); }
        h1 { font-size: 1.5rem; color: var(--verde-oscuro); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .campo { margin-bottom: 15px; }
        label { display: block; font-size: 0.88rem; font-weight: 600; margin-bottom: 6px; color: #333; }
        input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 10px; border: 1px solid var(--borde); border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
        .separador { margin: 20px 0 15px 0; border-bottom: 1px solid var(--borde); padding-bottom: 5px; font-size: 0.95rem; font-weight: bold; color: var(--verde); }
        .acciones { display: flex; justify-content: space-between; margin-top: 25px; }
        .btn { padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 0.9rem; }
        .btn-verde { background: var(--verde); color: white; }
        .btn-gris { background: #e5e7eb; color: #374151; }
        .alerta-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.88rem; }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fa-solid fa-user-gear"></i> Editar Mi Perfil de Superadmin</h1>

    <?php if ($mensaje_error): ?>
        <div class="alerta-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($mensaje_error) ?></div>
    <?php endif; ?>

    <form method="post" action="perfil.php">
        <?= csrf_campo() ?>

        <div class="campo">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" value="<?= e($superadmin['nombre']) ?>" required>
        </div>

        <div class="campo">
            <label>Nombre de Usuario</label>
            <input type="text" name="username" value="<?= e($superadmin['username']) ?>" required>
        </div>

        <div class="campo">
            <label>Correo Electrónico</label>
            <input type="email" name="email" value="<?= e($superadmin['email']) ?>" required>
        </div>

        <div class="campo">
            <label><i class="fa-solid fa-house-chimney"></i> Nombre de Tu Rancho</label>
            <input type="text" name="nombre_rancho" value="<?= e($nombre_rancho_actual) ?>" placeholder="Ej. Rancho San José">
        </div>

        <div class="separador"><i class="fa-solid fa-lock"></i> Cambiar Contraseña (Opcional)</div>
        <p style="font-size:0.8rem; color:#666; margin-bottom: 12px;">Deja estos campos en blanco si no deseas cambiar tu clave actual.</p>

        <div class="campo">
            <label>Nueva Contraseña</label>
            <input type="password" name="nueva_password" placeholder="••••••••">
        </div>

        <div class="campo">
            <label>Confirmar Nueva Contraseña</label>
            <input type="password" name="confirm_password" placeholder="••••••••">
        </div>

        <div class="acciones">
            <a href="index.php" class="btn btn-gris"><i class="fa-solid fa-arrow-left"></i> Cancelar</a>
            <button type="submit" class="btn btn-verde"><i class="fa-solid fa-floppy-disk"></i> Guardar Mi Perfil</button>
        </div>
    </form>
</div>

</body>
</html>