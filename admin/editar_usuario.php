<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Validar que el usuario logueado sea superadmin
$admin_id = require_superadmin($conexion);

$mensaje_error = '';
$usuario_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?? filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$usuario_id) {
    header('Location: index.php');
    exit;
}

// Consultar usuario a editar
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    $_SESSION['mensaje_error'] = "El usuario solicitado no existe.";
    header('Location: index.php');
    exit;
}

// Consultar el nombre del rancho actual si el usuario tiene uno asignado
$nombre_rancho_actual = '';
if (!empty($usuario['rancho_id'])) {
    $stmtR = $conexion->prepare("SELECT nombre FROM ranchos WHERE id = ? LIMIT 1");
    $stmtR->execute([$usuario['rancho_id']]);
    $nombre_rancho_actual = $stmtR->fetchColumn() ?: '';
}

// PROCESAR FORMULARIO AL ENVIAR (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nombre          = trim($_POST['nombre'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $email           = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $rol             = $_POST['rol'] ?? 'usuario';
    $estado          = isset($_POST['estado']) ? 1 : 0;
    $nombre_rancho   = trim($_POST['nombre_rancho'] ?? '');
    $nueva_password = $_POST['nueva_password'] ?? '';

    if ($nombre && $username && $email) {
        // Verificar si el correo o usuario ya pertenecen a otro usuario registrado
        $stmtCheck = $conexion->prepare("SELECT id FROM usuarios WHERE (email = ? OR username = ?) AND id != ? LIMIT 1");
        $stmtCheck->execute([$email, $username, $usuario_id]);

        if ($stmtCheck->fetch()) {
            $mensaje_error = "El correo o el nombre de usuario ya están en uso por otra cuenta.";
        } else {
            // Manejo de la actualización / creación del Rancho para el usuario editado
            $rancho_id = $usuario['rancho_id'] ?? null;

            if (!empty($nombre_rancho)) {
                if ($rancho_id) {
                    // Si el usuario ya tiene un rancho asignado, actualizar su nombre
                    $stmtUpR = $conexion->prepare("UPDATE ranchos SET nombre = ? WHERE id = ?");
                    $stmtUpR->execute([$nombre_rancho, $rancho_id]);
                } else {
                    // Si no tiene rancho asignado, crear un nuevo registro de rancho para este usuario
                    $stmtInsR = $conexion->prepare("INSERT INTO ranchos (nombre, usuario_id) VALUES (?, ?)");
                    $stmtInsR->execute([$nombre_rancho, $usuario_id]);
                    $rancho_id = $conexion->lastInsertId();
                }
            }

            // 1. Actualizar datos básicos (Nombre, Username, Email, Rol, Estado, Rancho ID)
            $stmtUp = $conexion->prepare("
                UPDATE usuarios 
                SET nombre = ?, username = ?, email = ?, rol = ?, estado = ?, rancho_id = ? 
                WHERE id = ?
            ");
            $stmtUp->execute([$nombre, $username, $email, $rol, $estado, $rancho_id, $usuario_id]);

            // 2. Si se ingresó una nueva contraseña manual, actualizarla
            if (!empty($nueva_password)) {
                if (strlen($nueva_password) >= 6) {
                    $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
                    $stmtPass = $conexion->prepare("UPDATE usuarios SET password = ?, debe_cambiar_password = 0 WHERE id = ?");
                    $stmtPass->execute([$hash, $usuario_id]);
                } else {
                    $mensaje_error = "La contraseña debe tener al menos 6 caracteres.";
                }
            }

            if (!$mensaje_error) {
                // Registrar acción en auditoría
                registrar_auditoria($conexion, $admin_id, 'EDITAR_USUARIO', $usuario_id, "Se actualizaron los datos, rancho y rol ({$rol}) de {$nombre}");

                $_SESSION['mensaje_exito'] = "Datos de {$nombre} actualizados correctamente con rol de {$rol}.";
                header('Location: index.php');
                exit;
            }
        }
    } else {
        $mensaje_error = "Por favor completa todos los campos requeridos con datos válidos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | Sistema Ganadero</title>
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
        .container { max-width: 600px; margin: 0 auto; background: var(--tarjeta); padding: 25px; border-radius: 12px; border: 1px solid var(--borde); }
        h1 { font-size: 1.5rem; color: var(--verde-oscuro); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .campo { margin-bottom: 15px; }
        label { display: block; font-size: 0.88rem; font-weight: 600; margin-bottom: 6px; color: #333; }
        input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; border: 1px solid var(--borde); border-radius: 6px; font-size: 0.95rem; box-sizing: border-box; }
        .separador { margin: 20px 0 15px 0; border-bottom: 1px solid var(--borde); padding-bottom: 5px; font-size: 0.95rem; font-weight: bold; color: var(--verde); }
        .checkbox-container { display: flex; align-items: center; gap: 8px; margin-top: 15px; }
        .acciones { display: flex; justify-content: space-between; margin-top: 25px; }
        .btn { padding: 10px 18px; border-radius: 6px; text-decoration: none; font-weight: 600; border: none; cursor: pointer; font-size: 0.9rem; }
        .btn-verde { background: var(--verde); color: white; }
        .btn-gris { background: #e5e7eb; color: #374151; }
        .alerta-error { background: #fee2e2; color: #991b1b; padding: 10px; border-radius: 6px; margin-bottom: 15px; font-size: 0.88rem; }
    </style>
</head>
<body>

<div class="container">
    <h1><i class="fa-solid fa-user-pen"></i> Editar Datos de Usuario</h1>

    <?php if ($mensaje_error): ?>
        <div class="alerta-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($mensaje_error) ?></div>
    <?php endif; ?>

    <form method="post" action="editar_usuario.php">
        <?= csrf_campo() ?>
        <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">

        <div class="campo">
            <label>Nombre Completo</label>
            <input type="text" name="nombre" value="<?= e($usuario['nombre']) ?>" required>
        </div>

        <div class="campo">
            <label>Nombre de Usuario (Username)</label>
            <input type="text" name="username" value="<?= e($usuario['username']) ?>" required>
        </div>

        <div class="campo">
            <label>Correo Electrónico</label>
            <input type="email" name="email" value="<?= e($usuario['email']) ?>" required>
        </div>

        <div class="campo">
            <label><i class="fa-solid fa-house-chimney"></i> Nombre del Rancho</label>
            <input type="text" name="nombre_rancho" value="<?= e($nombre_rancho_actual) ?>" placeholder="Ej. Rancho San José">
        </div>

        <div class="campo">
            <label>Rol en el Sistema</label>
            <select name="rol">
                <option value="usuario" <?= $usuario['rol'] === 'usuario' ? 'selected' : '' ?>>Usuario (Ganadero / Estándar)</option>
                <option value="admin" <?= $usuario['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                <option value="superadmin" <?= $usuario['rol'] === 'superadmin' ? 'selected' : '' ?>>Superadministrador</option>
            </select>
        </div>

        <div class="checkbox-container">
            <input type="checkbox" id="estado" name="estado" value="1" <?= (int)$usuario['estado'] === 1 ? 'checked' : '' ?>>
            <label for="estado" style="margin:0;">Cuenta Activa</label>
        </div>

        <div class="separador"><i class="fa-solid fa-key"></i> Cambiar Contraseña Manualmente (Opcional)</div>
        <p style="font-size:0.8rem; color:#666; margin-bottom: 12px;">Si deseas asignarle una clave personalizada directamente, escríbela aquí. De lo contrario, déjalo en blanco.</p>

        <div class="campo">
            <label>Nueva Contraseña Directa</label>
            <input type="password" name="nueva_password" placeholder="••••••••">
        </div>

        <div class="acciones">
            <a href="index.php" class="btn btn-gris"><i class="fa-solid fa-arrow-left"></i> Cancelar</a>
            <button type="submit" class="btn btn-verde"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
        </div>
    </form>
</div>

</body>
</html>