<?php

session_start();

require_once '../config/database.php';

// Compatibilidad de variable de base de datos PDO / mysqli
if (!isset($conexion) && isset($pdo)) {
    $conexion = $pdo;
}

/*
|--------------------------------------------------------------------------
| Verificar método POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($username) || empty($password)) {
    $_SESSION['error'] = 'Por favor, ingresa tu usuario y contraseña.';
    header('Location: ../index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Consultar usuario
|--------------------------------------------------------------------------
*/

try {
    // Incluimos debe_cambiar_password en la consulta
    $sql = "
        SELECT id, rancho_id, nombre, email, username, password, rol, estado, debe_cambiar_password
        FROM usuarios
        WHERE username = ? OR email = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([$username, $username]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['password'])) {
        
        // Validar si la cuenta está activa
        if (isset($usuario['estado']) && (int)$usuario['estado'] !== 1) {
            $_SESSION['error'] = 'Tu cuenta se encuentra desactivada.';
            header('Location: ../index.php');
            exit;
        }

        session_regenerate_id(true);

        $_SESSION['usuario_id'] = (int)$usuario['id'];
        $_SESSION['rancho_id']  = (int)$usuario['rancho_id'];
        $_SESSION['nombre']     = $usuario['nombre'];
        $_SESSION['username']   = $usuario['username'];
        $_SESSION['rol']        = $usuario['rol'] ?? 'usuario';

        /*
        |--------------------------------------------------------------------------
        | Verificar cambio forzado de contraseña
        |--------------------------------------------------------------------------
        */
        if (isset($usuario['debe_cambiar_password']) && (int)$usuario['debe_cambiar_password'] === 1) {
            header('Location: cambiar_password_forzado.php');
            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | Redirección por Rol
        |--------------------------------------------------------------------------
        */

        if ($_SESSION['rol'] === 'superadmin') {
            header('Location: ../admin/index.php');
        } else {
            header('Location: ../dashboard/index.php');
        }
        exit;

    } else {
        $_SESSION['error'] = 'Usuario o contraseña incorrectos.';
        header('Location: ../index.php');
        exit;
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Error al procesar el inicio de sesión: ' . $e->getMessage();
    header('Location: ../index.php');
    exit;
}