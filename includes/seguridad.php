<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

/*
|--------------------------------------------------------------------------
| CSRF: evita que otra página mande acciones en nombre del administrador
|--------------------------------------------------------------------------
*/
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void {
    $enviado = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $enviado)) {
        http_response_code(400);
        die('Solicitud no válida. Regresa e inténtalo de nuevo.');
    }
}

/*
|--------------------------------------------------------------------------
| Solo superadmin. Se revisa en la base de datos, no solo en la sesión.
| Devuelve el id del administrador.
|--------------------------------------------------------------------------
*/
function require_superadmin(PDO $conexion): int {

    $id = (int)($_SESSION['usuario_id'] ?? 0);

    if ($id <= 0) {
        header('Location: ../index.php');
        exit;
    }

    // Si está dentro de la cuenta de otro usuario, primero debe volver
    if (!empty($_SESSION['admin_original'])) {
        header('Location: ../dashboard/index.php');
        exit;
    }

    $stmt = $conexion->prepare("SELECT rol, estado FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fila || $fila['rol'] !== 'superadmin' || (int)$fila['estado'] !== 1) {
        http_response_code(403);
        die('Acceso restringido.');
    }

    return $id;
}

/*
|--------------------------------------------------------------------------
| Bitácora de acciones del administrador
|--------------------------------------------------------------------------
*/
function registrar_auditoria(PDO $conexion, int $admin_id, string $accion, ?int $objetivo = null, ?string $detalle = null): void {
    try {
        $stmt = $conexion->prepare("
            INSERT INTO auditoria_admin (admin_id, usuario_objetivo_id, accion, detalle, ip)
            VALUES (:admin, :objetivo, :accion, :detalle, :ip)
        ");
        $stmt->execute([
            ':admin'    => $admin_id,
            ':objetivo' => $objetivo,
            ':accion'   => $accion,
            ':detalle'  => $detalle !== null ? mb_substr($detalle, 0, 255) : null,
            ':ip'       => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Throwable $e) {
        // Si la tabla no existe todavía, no se rompe el sistema
    }
}

/*
|--------------------------------------------------------------------------
| Crea la sesión de un usuario (la usan el login y "entrar como")
|--------------------------------------------------------------------------
*/
function iniciar_sesion_usuario(array $u): void {
    session_regenerate_id(true);

    $_SESSION['usuario_id']    = (int)$u['id'];
    $_SESSION['rancho_id']     = (int)$u['rancho_id'];
    $_SESSION['nombre']        = $u['nombre'];
    $_SESSION['username']      = $u['username'];
    $_SESSION['rol']           = $u['rol'];
    $_SESSION['nombre_rancho'] = $u['nombre_rancho'];
}
