<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Validar que el usuario logueado sea superadmin
$admin_id = require_superadmin($conexion);

// Validar que el envío sea por POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Verificar token CSRF
csrf_check();

$usuario_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$usuario_id) {
    header('Location: index.php');
    exit;
}

// Consultar los datos del usuario objetivo
$stmt = $conexion->prepare("
    SELECT u.*, r.nombre AS nombre_rancho 
    FROM usuarios u 
    LEFT JOIN ranchos r ON r.id = u.rancho_id 
    WHERE u.id = ? AND u.estado = 1 
    LIMIT 1
");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // Guardar la sesión original del superadmin para poder retornar
    $_SESSION['admin_original'] = $admin_id;

    // Registrar acción en la bitácora
    registrar_auditoria($conexion, $admin_id, 'SUPLANTAR_USUARIO', $usuario_id, "Acceso a la cuenta de {$usuario['nombre']}");

    // Iniciar sesión con los datos del usuario objetivo
    iniciar_sesion_usuario($usuario);

    header('Location: ../dashboard/index.php');
    exit;
}

header('Location: index.php');
exit;