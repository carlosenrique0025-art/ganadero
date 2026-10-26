<?php
session_start();

/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

function e($valor): string {
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

/*
|--------------------------------------------------------------------------
| Funciones para consultar la base de datos (PDO o mysqli)
|--------------------------------------------------------------------------
*/
function total($db, string $sql): int {
    try {
        if ($db instanceof PDO) {
            return (int)$db->query($sql)->fetchColumn();
        }
        if ($db instanceof mysqli) {
            $r = $db->query($sql);
            if ($r) {
                $fila = $r->fetch_row();
                return (int)($fila[0] ?? 0);
            }
        }
    } catch (Throwable $e) {}
    return 0;
}

function filas($db, string $sql): array {
    try {
        if ($db instanceof PDO) {
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        }
        if ($db instanceof mysqli) {
            $r = $db->query($sql);
            if ($r) {
                return $r->fetch_all(MYSQLI_ASSOC);
            }
        }
    } catch (Throwable $e) {}
    return [];
}

/*
|--------------------------------------------------------------------------
| Conexión a la base de datos
|--------------------------------------------------------------------------
*/
$archivoDb = __DIR__ . '/../config/database.php';
if (file_exists($archivoDb)) {
    require_once $archivoDb;
}

$db = null;
foreach (get_defined_vars() as $valorVar) {
    if ($valorVar instanceof PDO || $valorVar instanceof mysqli) {
        $db = $valorVar;
        break;
    }
}

$avisos = [];
$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
$rancho_id  = (int)($_SESSION['rancho_id'] ?? 0);

function columnas($db, string $tabla): array {
    $cols = [];
    foreach (filas($db, "SHOW COLUMNS FROM `$tabla`") as $f) {
        $cols[] = $f['Field'];
    }
    return $cols;
}

function condicion($db, string $tabla): ?string {
    global $usuario_id, $rancho_id;
    $cols = columnas($db, $tabla);

    if ($rancho_id > 0) {
        foreach (['rancho_id', 'id_rancho'] as $col) {
            if (in_array($col, $cols, true)) {
                return "$col = " . $rancho_id;
            }
        }
    }
    foreach (['usuario_id', 'id_usuario'] as $col) {
        if (in_array($col, $cols, true)) {
            return "$col = " . $usuario_id;
        }
    }
    return null;
}

function contar($db, string $tabla, array &$avisos, string $extra = ''): int {
    $cond = condicion($db, $tabla);
    if ($cond === null) {
        $avisos[] = $tabla;
        return 0;
    }
    return total($db, "SELECT COUNT(*) FROM `$tabla` WHERE $cond $extra");
}

/* Ganado activo del rancho */
$extraGanado = in_array('estado', columnas($db, 'ganado'), true) ? 'AND estado = 1' : '';
$ganado = contar($db, 'ganado', $avisos, $extraGanado);
$socios = contar($db, 'socios', $avisos);
$guias  = contar($db, 'guias',  $avisos);

/* Vacunas */
$vacunas = 0;
$vacunasTitulo = 'Vacunas aplicadas';
$vacunasPie = 'Control sanitario';
$tablaAplicadas = null;

foreach (['vacunaciones', 'aplicaciones_vacunas', 'aplicacion_vacunas', 'vacunas_aplicadas', 'historial_vacunas', 'vacunacion', 'aplicaciones'] as $t) {
    $existe = filas($db, "SHOW TABLES LIKE '" . $t . "'");
    if ($existe && in_array('ganado_id', columnas($db, $t), true)) {
        $tablaAplicadas = $t;
        break;
    }
}

if ($tablaAplicadas !== null && $rancho_id > 0 && in_array('rancho_id', columnas($db, 'ganado'), true)) {
    $vacunas = total(
        $db,
        "SELECT COUNT(*) FROM `$tablaAplicadas` t
         INNER JOIN ganado g ON g.id = t.ganado_id
         WHERE g.rancho_id = $rancho_id"
    );
} else {
    $vacunas = total($db, "SELECT COUNT(*) FROM vacunas WHERE estado = 1");
    $vacunasTitulo = 'Vacunas en catálogo';
    $vacunasPie = 'Catálogo general';
}

/* Últimos animales registrados */
$ultimos = [];
$condGanado = condicion($db, 'ganado');
if ($condGanado !== null) {
    $ultimos = filas(
        $db,
        "SELECT * FROM ganado WHERE $condGanado ORDER BY id DESC LIMIT 5"
    );
}

/*
|--------------------------------------------------------------------------
| Datos del usuario y Rancho en tiempo real desde la Base de Datos
|--------------------------------------------------------------------------
*/
$nombre       = 'Usuario';
$username     = '';
$rol          = 'Usuario';
$nombreRancho = '';
$logoRancho   = '';
$escalaLogo   = 100;
$logoX        = 0;
$logoY        = 0;

if ($usuario_id > 0 && $db !== null) {
    try {
        // ¿Existe la columna logo_escala?
        $colsRancho = columnas($db, 'ranchos');
        $selRancho  = 'nombre, logo';
        foreach (['logo_escala', 'logo_x', 'logo_y'] as $c) {
            if (in_array($c, $colsRancho, true)) {
                $selRancho .= ", $c";
            }
        }

        if ($db instanceof PDO) {
            $stmtU = $db->prepare("SELECT nombre, username, rol, rancho_id FROM usuarios WHERE id = ? LIMIT 1");
            $stmtU->execute([$usuario_id]);
            $usrData = $stmtU->fetch(PDO::FETCH_ASSOC);

            if ($usrData) {
                $nombre   = $usrData['nombre']   ?: $nombre;
                $username = $usrData['username'] ?: $username;
                $rol      = $usrData['rol']      ?: $rol;

                if ($rancho_id <= 0 && !empty($usrData['rancho_id'])) {
                    $rancho_id = (int)$usrData['rancho_id'];
                    $_SESSION['rancho_id'] = $rancho_id;
                }
            }

            if ($rancho_id > 0) {
                $stmtR = $db->prepare("SELECT $selRancho FROM ranchos WHERE id = ? LIMIT 1");
                $stmtR->execute([$rancho_id]);
                $ranchoData = $stmtR->fetch(PDO::FETCH_ASSOC);
                if ($ranchoData) {
                    $nombreRancho = $ranchoData['nombre'] ?? '';
                    $logoRancho   = $ranchoData['logo'] ?? '';
                    $escalaLogo   = (int)($ranchoData['logo_escala'] ?? 100);
                    $logoX        = (int)($ranchoData['logo_x'] ?? 0);
                    $logoY        = (int)($ranchoData['logo_y'] ?? 0);
                }
            }

        } elseif ($db instanceof mysqli) {
            $stmtU = $db->prepare("SELECT nombre, username, rol, rancho_id FROM usuarios WHERE id = ? LIMIT 1");
            $stmtU->bind_param("i", $usuario_id);
            $stmtU->execute();
            $resU = $stmtU->get_result();
            if ($usrData = $resU->fetch_assoc()) {
                $nombre   = $usrData['nombre']   ?: $nombre;
                $username = $usrData['username'] ?: $username;
                $rol      = $usrData['rol']      ?: $rol;

                if ($rancho_id <= 0 && !empty($usrData['rancho_id'])) {
                    $rancho_id = (int)$usrData['rancho_id'];
                    $_SESSION['rancho_id'] = $rancho_id;
                }
            }

            if ($rancho_id > 0) {
                $stmtR = $db->prepare("SELECT $selRancho FROM ranchos WHERE id = ? LIMIT 1");
                $stmtR->bind_param("i", $rancho_id);
                $stmtR->execute();
                $resR = $stmtR->get_result();
                if ($rowR = $resR->fetch_assoc()) {
                    $nombreRancho = $rowR['nombre'] ?? '';
                    $logoRancho   = $rowR['logo'] ?? '';
                    $escalaLogo   = (int)($rowR['logo_escala'] ?? 100);
                    $logoX        = (int)($rowR['logo_x'] ?? 0);
                    $logoY        = (int)($rowR['logo_y'] ?? 0);
                }
            }
        }

        $_SESSION['nombre']        = $nombre;
        $_SESSION['username']      = $username;
        $_SESSION['nombre_rancho'] = $nombreRancho;
        $_SESSION['logo_rancho']   = $logoRancho;
        $_SESSION['logo_escala']   = $escalaLogo;
        $_SESSION['logo_x']        = $logoX;
        $_SESSION['logo_y']        = $logoY;

    } catch (Throwable $e) {
        $nombre       = $_SESSION['nombre']        ?? $nombre;
        $username     = $_SESSION['username']      ?? $username;
        $rol          = $_SESSION['rol']           ?? $rol;
        $nombreRancho = $_SESSION['nombre_rancho'] ?? '';
        $logoRancho   = $_SESSION['logo_rancho']   ?? '';
        $escalaLogo   = (int)($_SESSION['logo_escala'] ?? 100);
        $logoX        = (int)($_SESSION['logo_x'] ?? 0);
        $logoY        = (int)($_SESSION['logo_y'] ?? 0);
    }
}

$escalaLogo = max(30, min(200, $escalaLogo ?: 100));
$logoX      = max(-150, min(150, $logoX));
$logoY      = max(-80,  min(80,  $logoY));
$transformLogo = 'translate(' . $logoX . 'px, ' . $logoY . 'px) scale(' . ($escalaLogo / 100) . ')';

// Ruta final del logo
$rutaLogoFinal = '../assets/images/dd.png';
if (!empty($logoRancho) && file_exists(__DIR__ . '/../' . $logoRancho)) {
    $rutaLogoFinal = '../' . $logoRancho;
}

$modulos = [
    ['Ganado',      '../ganado/index.php',      'fa-cow',            'Registro y control del hato'],
    ['Vacunas',     '../vacunas/index.php',     'fa-syringe',        'Control sanitario'],
    ['Razas',       '../razas/index.php',       'fa-dna',            'Catálogo de razas'],
    ['Socios',      '../socios/index.php',      'fa-users',          'Padrón de socios'],
    ['Movimientos', '../movimientos/index.php', 'fa-right-left',     'Entradas y salidas'],
    ['Guías',       '../guias/index.php',       'fa-clipboard-list', 'Guías de tránsito'],
    ['Facturas',    '../facturas/index.php',    'fa-file-invoice',   'Facturación'],
    ['Proveedores', '../proveedores/index.php', 'fa-truck',          'Directorio de proveedores'],
    ['Reportes',    '../reportes/index.php',    'fa-chart-column',   'Consultas y estadísticas'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Sistema Ganadero</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --verde-oscuro: #1b4332;
            --verde: #2d6a4f;
            --verde-claro: #d8ead9;
            --fondo: #f1f5f0;
            --tarjeta: #ffffff;
            --texto: #1c2a22;
            --texto-suave: #5b6b61;
            --borde: #e0e8e1;
            --crema: #f6efe0;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--fondo);
            color: var(--texto);
            min-height: 100vh;
        }

        a { text-decoration: none; color: inherit; }

        .app-shell { display: flex; min-height: 100vh; }

        /* ----- BARRA LATERAL ----- */
        .sidebar {
            width: 260px;
            flex-shrink: 0;
            background: linear-gradient(180deg, var(--verde-oscuro) 0%, #143528 100%);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            padding: 22px 16px;
            position: sticky;
            top: 0;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-brand {
            background: var(--crema);
            border-radius: 14px;
            padding: 12px;
            margin-bottom: 22px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            width: 100%;
            height: 130px;
            overflow: hidden;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.05);
        }

        .logo-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-brand img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            display: block;
            margin: 0 auto;
            transform-origin: center center;
            transition: transform 0.15s ease;
        }

        .btn-cambiar-logo {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 28px;
            height: 28px;
            padding: 0;
            background: var(--verde);
            color: white;
            border: 2px solid #fff;
            border-radius: 50%;
            font-size: 0.7rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0.85;
            box-shadow: 0 1px 4px rgba(0,0,0,0.25);
            transition: background 0.2s, opacity 0.2s;
            z-index: 2;
        }

        .btn-cambiar-logo:hover { opacity: 1; }

        .btn-cambiar-logo:hover { background: var(--verde-oscuro); }

        .nav-label {
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            color: rgba(255, 255, 255, 0.55);
            margin: 4px 12px 10px;
        }

        .nav-list { display: flex; flex-direction: column; gap: 3px; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 99px;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.85);
            transition: background 0.15s ease, color 0.15s ease;
        }

        .nav-link i { width: 20px; text-align: center; font-size: 0.95rem; }
        .nav-link:hover { background: rgba(255, 255, 255, 0.1); color: #ffffff; }
        .nav-link.active { background: #ffffff; color: var(--verde-oscuro); font-weight: 600; }

        .sidebar-bottom {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .nav-link.salir:hover { background: rgba(255, 90, 90, 0.2); }

        /* ----- ÁREA PRINCIPAL ----- */
        .main-area { flex: 1; min-width: 0; display: flex; flex-direction: column; }

        .topbar {
            background: var(--tarjeta);
            border-bottom: 1px solid var(--borde);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .crumb { font-size: 0.9rem; color: var(--texto-suave); }
        .crumb strong { color: var(--texto); font-weight: 600; }

        .user-chip { display: flex; align-items: center; gap: 12px; }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--verde);
            color: #ffffff;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .user-name { font-size: 0.9rem; font-weight: 600; line-height: 1.2; }
        .user-role { font-size: 0.78rem; color: var(--texto-suave); }

        .content { padding: 30px 32px; flex: 1; }

        /* ----- BIENVENIDA ----- */
        .welcome-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 26px;
        }

        .welcome-row h1 {
            font-family: 'Lora', Georgia, serif;
            font-size: 1.9rem;
            color: var(--verde-oscuro);
            line-height: 1.2;
            margin-bottom: 6px;
        }

        .welcome-row p { color: var(--texto-suave); font-size: 0.95rem; }

        .welcome-meta { display: flex; gap: 10px; flex-wrap: wrap; }

        .pill {
            background: var(--tarjeta);
            border: 1px solid var(--borde);
            border-radius: 999px;
            padding: 8px 16px;
            font-size: 0.85rem;
            color: var(--texto-suave);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pill i { color: var(--verde); }
        .pill strong { color: var(--texto); font-weight: 600; }

        /* ----- ESTADÍSTICAS ----- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: var(--tarjeta);
            border: 1px solid var(--borde);
            border-radius: 14px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
            background: var(--verde-claro);
            color: var(--verde);
        }

        .stat-icon.azul { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.dorado { background: #fdf0cf; color: #a16207; }
        .stat-icon.morado { background: #ede4fb; color: #6d28d9; }

        .stat-label { font-size: 0.82rem; color: var(--texto-suave); }
        .stat-value { font-size: 1.8rem; font-weight: 700; line-height: 1.15; color: var(--texto); }
        .stat-foot { font-size: 0.76rem; color: var(--texto-suave); }

        /* ----- PANELES ----- */
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 18px;
            margin-bottom: 26px;
        }

        .panel {
            background: var(--tarjeta);
            border: 1px solid var(--borde);
            border-radius: 14px;
            overflow: hidden;
        }

        .panel-head {
            padding: 16px 20px;
            border-bottom: 1px solid var(--borde);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .panel-head h3 { font-size: 1rem; font-weight: 700; }
        .panel-head a { font-size: 0.82rem; color: var(--verde); font-weight: 600; }

        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; font-size: 0.88rem; }

        th {
            text-align: left;
            font-weight: 600;
            font-size: 0.8rem;
            color: var(--texto-suave);
            padding: 12px 20px;
            background: #f7faf6;
        }

        td { padding: 13px 20px; border-top: 1px solid var(--borde); }

        .status {
            background: var(--verde-claro);
            color: var(--verde-oscuro);
            padding: 3px 11px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .info-list { padding: 8px 20px 14px; }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--borde);
            font-size: 0.9rem;
        }

        .info-row:last-child { border-bottom: none; }
        .info-row span { color: var(--texto-suave); }
        .info-row strong { text-align: right; word-break: break-word; }

        /* ----- MÓDULOS ----- */
        .section-title { font-size: 1.05rem; font-weight: 700; margin-bottom: 4px; }
        .section-sub { font-size: 0.88rem; color: var(--texto-suave); margin-bottom: 16px; }

        .module-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
            gap: 14px;
        }

        .module-card {
            background: var(--tarjeta);
            border: 1px solid var(--borde);
            border-radius: 14px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: border-color 0.15s ease, background 0.15s ease;
        }

        .module-card:hover { border-color: var(--verde); background: #f7fbf6; }

        .module-card .m-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--verde-claro);
            color: var(--verde);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        /* ----- MODAL DEL LOGO ----- */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background-color: rgba(0,0,0,0.55);
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .modal-content {
            background: white;
            padding: 24px;
            border-radius: 16px;
            max-width: 480px;
            width: 100%;
            max-height: 94vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        }

        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .modal-header h3 { font-size: 1.1rem; color: var(--verde-oscuro); }
        .close-modal { cursor: pointer; font-size: 1.4rem; color: #888; line-height: 1; }

        .preview-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--texto-suave);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Vista previa: réplica exacta de la barra lateral */
        .preview-stage {
            background: linear-gradient(180deg, var(--verde-oscuro) 0%, #143528 100%);
            border-radius: 14px;
            padding: 22px 16px;
            display: flex;
            justify-content: center;
            margin-bottom: 18px;
        }

        .preview-stage .sidebar-brand {
            width: 228px;
            margin-bottom: 0;
            flex-shrink: 0;
        }

        .preview-stage .btn-cambiar-logo { cursor: default; }

        /* Arrastrar el logo con el mouse o el dedo */
        .preview-stage .sidebar-brand { cursor: grab; touch-action: none; user-select: none; -webkit-user-select: none; }
        .preview-stage .sidebar-brand.arrastrando { cursor: grabbing; }
        .preview-stage .sidebar-brand.arrastrando img { transition: none; }
        .preview-stage img { pointer-events: none; }

        .drag-tip {
            font-size: 0.78rem;
            color: var(--texto-suave);
            text-align: center;
            margin: -8px 0 16px;
        }

        .size-row + .size-row { margin-top: 12px; }
        .size-row .eje { width: 18px; font-size: 0.8rem; font-weight: 700; color: var(--texto-suave); text-align: center; }
        .size-row .num { min-width: 42px; text-align: right; font-size: 0.8rem; color: var(--texto-suave); }

        .size-control {
            background: #f7faf6;
            border: 1px solid var(--borde);
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 18px;
        }

        .size-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            font-size: 0.88rem;
            font-weight: 600;
        }

        .size-value {
            background: var(--verde);
            color: #fff;
            padding: 2px 12px;
            border-radius: 999px;
            font-size: 0.82rem;
        }

        .size-row { display: flex; align-items: center; gap: 12px; }
        .size-row i { color: var(--texto-suave); }

        input[type="range"] {
            flex: 1;
            -webkit-appearance: none;
            appearance: none;
            height: 6px;
            border-radius: 99px;
            background: var(--verde-claro);
            outline: none;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 22px; height: 22px;
            border-radius: 50%;
            background: var(--verde);
            border: 3px solid #fff;
            box-shadow: 0 1px 5px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        input[type="range"]::-moz-range-thumb {
            width: 18px; height: 18px;
            border-radius: 50%;
            background: var(--verde);
            border: 3px solid #fff;
            box-shadow: 0 1px 5px rgba(0,0,0,0.3);
            cursor: pointer;
        }

        .btn {
            padding: 9px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.88rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-primario { background: var(--verde); color: #fff; }
        .btn-primario:hover { background: var(--verde-oscuro); }
        .btn-claro { background: #e5e7eb; color: #1f2937; }
        .btn-claro:hover { background: #d1d5db; }
        .btn-borde { background: #fff; color: var(--verde); border: 1px solid var(--verde); }
        .btn-borde:hover { background: #f1f8f2; }

        .modal-actions { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .modal-actions .grupo { display: flex; gap: 8px; flex-wrap: wrap; }

        .file-hint { font-size: 0.78rem; color: #666; margin: -8px 0 16px; }

        .footer {
            text-align: center;
            font-size: 0.8rem;
            color: var(--texto-suave);
            padding: 18px;
            border-top: 1px solid var(--borde);
            background: var(--tarjeta);
        }
    </style>
</head>
<body>

<div class="app-shell">

    <!-- ===== BARRA LATERAL ===== -->
    <aside class="sidebar">

        <div class="sidebar-brand">
            <div class="logo-container">
                <img src="<?= e($rutaLogoFinal) ?>" alt="Logo Rancho" style="transform: <?= e($transformLogo) ?>;">
            </div>
            <button class="btn-cambiar-logo" onclick="abrirModalLogo()" title="Cambiar logo" aria-label="Cambiar logo">
                <i class="fa-solid fa-camera"></i>
            </button>
        </div>

        <div class="nav-label">Menú principal</div>

        <nav class="nav-list">
            <a class="nav-link active" href="index.php">
                <i class="fa-solid fa-house"></i> Inicio
            </a>
            <?php foreach ($modulos as $m): ?>
                <a class="nav-link" href="<?= e($m[1]) ?>">
                    <i class="fa-solid <?= e($m[2]) ?>"></i> <?= e($m[0]) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-bottom">
            <a class="nav-link salir" href="../auth/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión
            </a>
        </div>

    </aside>

    <!-- ===== CONTENIDO PRINCIPAL ===== -->
    <main class="main-area">

        <header class="topbar">
            <div class="crumb">Sistema Ganadero / <strong>Panel principal</strong></div>

            <div class="user-chip">
                <div class="avatar"><?= e(mb_strtoupper(mb_substr($nombre, 0, 1))) ?></div>
                <div>
                    <div class="user-name"><?= e($nombre) ?></div>
                    <div class="user-role"><?= e($rol) ?></div>
                </div>
            </div>
        </header>

        <section class="content">

            <?php if (isset($_SESSION['mensaje_exito'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <i class="fa-solid fa-circle-check"></i> <?= e($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['mensaje_error'])): ?>
                <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 8px; margin-bottom: 15px;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= e($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
                </div>
            <?php endif; ?>

            <div class="welcome-row">
                <div>
                    <h1>Bienvenido, <?= e($nombre) ?></h1>
                    <p>Administra la información de tu actividad ganadera desde un solo lugar.</p>
                </div>

                <div class="welcome-meta">
                    <?php if ($nombreRancho !== ''): ?>
                        <div class="pill"><i class="fa-solid fa-house-chimney"></i> <strong><?= e($nombreRancho) ?></strong></div>
                    <?php endif; ?>
                    <div class="pill"><i class="fa-regular fa-calendar"></i> <?= date('d/m/Y') ?></div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <article class="stat-card">
                    <div class="stat-icon"><i class="fa-solid fa-cow"></i></div>
                    <div>
                        <div class="stat-label">Total de ganado</div>
                        <div class="stat-value"><?= $ganado ?></div>
                        <div class="stat-foot">Registros en el sistema</div>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon azul"><i class="fa-solid fa-syringe"></i></div>
                    <div>
                        <div class="stat-label"><?= e($vacunasTitulo) ?></div>
                        <div class="stat-value"><?= $vacunas ?></div>
                        <div class="stat-foot"><?= e($vacunasPie) ?></div>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon dorado"><i class="fa-solid fa-users"></i></div>
                    <div>
                        <div class="stat-label">Socios</div>
                        <div class="stat-value"><?= $socios ?></div>
                        <div class="stat-foot">Socios registrados</div>
                    </div>
                </article>

                <article class="stat-card">
                    <div class="stat-icon morado"><i class="fa-solid fa-clipboard-list"></i></div>
                    <div>
                        <div class="stat-label">Guías de tránsito</div>
                        <div class="stat-value"><?= $guias ?></div>
                        <div class="stat-foot">Documentos registrados</div>
                    </div>
                </article>
            </div>

            <!-- TABLAS Y SECCIÓN TU CUENTA -->
            <div class="dashboard-grid">

                <section class="panel">
                    <div class="panel-head">
                        <h3>Últimos registros de ganado</h3>
                        <a href="../ganado/index.php">Ver módulo</a>
                    </div>

                    <?php if ($ultimos): ?>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Arete</th>
                                        <th>Nombre</th>
                                        <th>Sexo</th>
                                        <th>Registro</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ultimos as $r): ?>
                                        <?php
                                            $fechaReg = $r['fecha_registro'] ?? $r['created_at'] ?? $r['fecha_creacion'] ?? '—';
                                            $activo = !isset($r['estado']) || (string)$r['estado'] === '1' || strtolower((string)$r['estado']) === 'activo';
                                        ?>
                                        <tr>
                                            <td><?= e($r['arete'] ?? '—') ?></td>
                                            <td><?= e(($r['nombre'] ?? '') !== '' ? $r['nombre'] : 'Sin nombre') ?></td>
                                            <td><?= e($r['sexo'] ?? '—') ?></td>
                                            <td><?= e($fechaReg) ?></td>
                                            <td><span class="status"><?= $activo ? 'Activo' : 'Inactivo' ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="padding: 30px; text-align: center; color: #666;">
                            <i class="fa-solid fa-cow" style="font-size: 2rem; color: var(--verde);"></i><br><br>
                            Aún no hay registros de ganado.
                        </div>
                    <?php endif; ?>
                </section>

                <section class="panel">
                    <div class="panel-head">
                        <h3>Tu cuenta</h3>
                    </div>
                    <div class="info-list">
                        <div class="info-row">
                            <span>Rancho</span>
                            <strong><?= e($nombreRancho !== '' ? $nombreRancho : 'Sin definir') ?></strong>
                        </div>
                        <div class="info-row">
                            <span>Logo del Rancho</span>
                            <button onclick="abrirModalLogo()" style="background: none; border: none; color: var(--verde); font-weight: 600; cursor: pointer; text-decoration: underline;">
                                <i class="fa-solid fa-image"></i> Cambiar
                            </button>
                        </div>
                        <div class="info-row"><span>Usuario</span><strong><?= e($username) ?></strong></div>
                        <div class="info-row"><span>Rol</span><strong><?= e($rol) ?></strong></div>
                        <div class="info-row"><span>Nombre</span><strong><?= e($nombre) ?></strong></div>
                    </div>
                </section>

            </div>

            <!-- MÓDULOS DEL SISTEMA -->
            <h2 class="section-title">Módulos del sistema</h2>
            <p class="section-sub">Selecciona el módulo que deseas administrar.</p>

            <div class="module-grid">
                <?php foreach ($modulos as $m): ?>
                    <a class="module-card" href="<?= e($m[1]) ?>">
                        <div class="m-icon"><i class="fa-solid <?= e($m[2]) ?>"></i></div>
                        <div>
                            <strong><?= e($m[0]) ?></strong>
                            <small><?= e($m[3]) ?></small>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        </section>

        <footer class="footer">
            Sistema Ganadero &copy; <?= date('Y') ?> Todos los derechos reservados.
        </footer>

    </main>

</div>

<!-- ===== MODAL: CAMBIAR / AJUSTAR LOGO CON VISTA PREVIA ===== -->
<div id="modalLogo" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-image"></i> Logo del Rancho</h3>
            <span class="close-modal" onclick="cerrarModalLogo()">&times;</span>
        </div>

        <form action="cambiar_logo.php" method="post" enctype="multipart/form-data">

            <!-- Vista previa en vivo (igual que la barra lateral) -->
            <div class="preview-label"><i class="fa-regular fa-eye"></i> Así se verá en tu panel</div>
            <div class="preview-stage">
                <div class="sidebar-brand">
                    <div class="logo-container">
                        <img id="previewImg" src="<?= e($rutaLogoFinal) ?>" alt="Vista previa" style="transform: <?= e($transformLogo) ?>;">
                    </div>
                    <span class="btn-cambiar-logo"><i class="fa-solid fa-camera"></i></span>
                </div>
            </div>

            <!-- Control de tamaño -->
            <div class="size-control">
                <div class="size-head">
                    <span><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Tamaño del logo</span>
                    <span class="size-value" id="valorEscala"><?= $escalaLogo ?>%</span>
                </div>
                <div class="size-row">
                    <i class="fa-solid fa-minus"></i>
                    <input type="range" id="logo_escala" name="logo_escala" min="30" max="200" step="5" value="<?= $escalaLogo ?>">
                    <i class="fa-solid fa-plus"></i>
                </div>
            </div>

            <!-- Control de posición -->
            <p class="drag-tip"><i class="fa-solid fa-hand"></i> Arrastra el logo con el mouse dentro del recuadro, o usa los deslizadores</p>
            <div class="size-control">
                <div class="size-head">
                    <span><i class="fa-solid fa-arrows-up-down-left-right"></i> Posición del logo</span>
                </div>
                <div class="size-row">
                    <span class="eje">X</span>
                    <input type="range" id="logo_x" name="logo_x" min="-150" max="150" step="1" value="<?= $logoX ?>">
                    <span class="num" id="valorX"><?= $logoX ?></span>
                </div>
                <div class="size-row">
                    <span class="eje">Y</span>
                    <input type="range" id="logo_y" name="logo_y" min="-80" max="80" step="1" value="<?= $logoY ?>">
                    <span class="num" id="valorY"><?= $logoY ?></span>
                </div>
            </div>

            <!-- Selector de imagen (opcional) -->
            <input type="file" id="inputLogo" name="logo_rancho" accept="image/*,.ico,.svg,.bmp" style="display:none;">
            <p class="file-hint" id="nombreArchivo">Puedes solo ajustar el tamaño, o elegir una imagen nueva (PNG, JPG, WEBP, ICO, SVG, GIF… máx. 5MB).</p>

            <div class="modal-actions">
                <div class="grupo">
                    <button type="button" class="btn btn-borde" onclick="document.getElementById('inputLogo').click()">
                        <i class="fa-solid fa-upload"></i> Elegir imagen
                    </button>
                    <button type="button" class="btn btn-claro" onclick="restablecerEscala()">
                        <i class="fa-solid fa-rotate-left"></i> Restablecer
                    </button>
                </div>
                <div class="grupo">
                    <button type="button" class="btn btn-claro" onclick="cerrarModalLogo()">Cancelar</button>
                    <button type="submit" class="btn btn-primario">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    var modal       = document.getElementById('modalLogo');
    var inputLogo   = document.getElementById('inputLogo');
    var previewImg  = document.getElementById('previewImg');
    var rango       = document.getElementById('logo_escala');
    var valorEscala = document.getElementById('valorEscala');
    var hint        = document.getElementById('nombreArchivo');

    var logoOriginal   = previewImg.getAttribute('src');
    var escalaOriginal = rango.value;

    var rangoX = document.getElementById('logo_x');
    var rangoY = document.getElementById('logo_y');
    var valorX = document.getElementById('valorX');
    var valorY = document.getElementById('valorY');
    var cajaPreview = document.querySelector('.preview-stage .sidebar-brand');
    var xOriginal = rangoX.value;
    var yOriginal = rangoY.value;
    var urlTemporal    = null;

    function aplicarEscala() {
        var v = parseInt(rango.value, 10);
        var x = parseInt(rangoX.value, 10);
        var y = parseInt(rangoY.value, 10);
        valorEscala.textContent = v + '%';
        valorX.textContent = x;
        valorY.textContent = y;
        previewImg.style.transform = 'translate(' + x + 'px, ' + y + 'px) scale(' + (v / 100) + ')';
    }

    rango.addEventListener('input', aplicarEscala);
    rangoX.addEventListener('input', aplicarEscala);
    rangoY.addEventListener('input', aplicarEscala);

    // Arrastrar el logo con el mouse (o el dedo en el celular)
    var arrastre = null;

    function limitar(valor, min, max) {
        return Math.max(min, Math.min(max, valor));
    }

    cajaPreview.addEventListener('pointerdown', function (ev) {
        if (ev.target.closest('.btn-cambiar-logo')) return;
        arrastre = {
            x0: ev.clientX,
            y0: ev.clientY,
            vx: parseInt(rangoX.value, 10),
            vy: parseInt(rangoY.value, 10)
        };
        cajaPreview.classList.add('arrastrando');
        cajaPreview.setPointerCapture(ev.pointerId);
        ev.preventDefault();
    });

    cajaPreview.addEventListener('pointermove', function (ev) {
        if (!arrastre) return;
        rangoX.value = limitar(Math.round(arrastre.vx + (ev.clientX - arrastre.x0)), -150, 150);
        rangoY.value = limitar(Math.round(arrastre.vy + (ev.clientY - arrastre.y0)), -80, 80);
        aplicarEscala();
    });

    function soltarLogo() {
        arrastre = null;
        cajaPreview.classList.remove('arrastrando');
    }
    cajaPreview.addEventListener('pointerup', soltarLogo);
    cajaPreview.addEventListener('pointercancel', soltarLogo);

    // Al elegir una imagen, se muestra al instante en la vista previa
    inputLogo.addEventListener('change', function () {
        var archivo = this.files[0];
        if (!archivo) return;

        if (archivo.size > 5 * 1024 * 1024) {
            alert('El archivo es muy pesado. El límite máximo es de 5MB.');
            this.value = '';
            return;
        }

        if (urlTemporal) URL.revokeObjectURL(urlTemporal);
        urlTemporal = URL.createObjectURL(archivo);
        previewImg.src = urlTemporal;
        hint.textContent = 'Imagen seleccionada: ' + archivo.name;
    });

    function restablecerEscala() {
        rango.value = 100;
        rangoX.value = 0;
        rangoY.value = 0;
        aplicarEscala();
    }

    function abrirModalLogo() {
        modal.style.display = 'flex';
    }

    function cerrarModalLogo() {
        modal.style.display = 'none';
        // Deshacer cualquier cambio no guardado
        inputLogo.value = '';
        if (urlTemporal) { URL.revokeObjectURL(urlTemporal); urlTemporal = null; }
        previewImg.src = logoOriginal;
        rango.value = escalaOriginal;
        rangoX.value = xOriginal;
        rangoY.value = yOriginal;
        aplicarEscala();
        hint.textContent = 'Puedes solo ajustar el tamaño, o elegir una imagen nueva (PNG, JPG, WEBP, ICO, SVG, GIF… máx. 5MB).';
    }

    // Cerrar al hacer clic fuera de la ventana
    window.addEventListener('click', function (event) {
        if (event.target === modal) cerrarModalLogo();
    });
</script>

</body>
</html>