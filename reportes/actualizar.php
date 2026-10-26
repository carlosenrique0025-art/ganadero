<?php

session_start();

require_once '../config/database.php';
require_once '../vendor/autoload.php';
require_once 'generar_archivo.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;
}

$rancho_id = (int)$_SESSION['rancho_id'];

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    $_SESSION['error'] =
        'Reporte no válido.';

    header('Location: index.php');
    exit;
}

$tipo_reporte = $_POST['tipo_reporte'] ?? '';
$titulo = trim($_POST['titulo'] ?? '');
$fecha_inicio = $_POST['fecha_inicio'] ?? '';
$fecha_fin = $_POST['fecha_fin'] ?? '';
$formato = $_POST['formato'] ?? '';
$observaciones = trim($_POST['observaciones'] ?? '');

$tipos_validos = [
    'GANADO',
    'MOVIMIENTOS',
    'SOCIOS',
    'GUIAS',
    'FACTURAS',
    'PROVEEDORES',
    'GENERAL',
    'OTRO'
];

$formatos_validos = [
    'PDF',
    'EXCEL',
    'WORD',
    'CSV'
];

if (!in_array($tipo_reporte, $tipos_validos, true)) {

    $_SESSION['error'] =
        'Tipo de reporte no válido.';

    header('Location: editar.php?id=' . $id);
    exit;
}

if (!in_array($formato, $formatos_validos, true)) {

    $_SESSION['error'] =
        'Formato no válido.';

    header('Location: editar.php?id=' . $id);
    exit;
}

if ($titulo === '') {

    $_SESSION['error'] =
        'El título es obligatorio.';

    header('Location: editar.php?id=' . $id);
    exit;
}

$fecha_inicio = $fecha_inicio !== ''
    ? $fecha_inicio
    : null;

$fecha_fin = $fecha_fin !== ''
    ? $fecha_fin
    : null;

if (
    $fecha_inicio !== null &&
    $fecha_fin !== null &&
    $fecha_inicio > $fecha_fin
) {

    $_SESSION['error'] =
        'La fecha de inicio no puede ser mayor que la fecha de fin.';

    header('Location: editar.php?id=' . $id);
    exit;
}

$sql = "
    SELECT
        archivo
    FROM reportes
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$reporte = $stmt->fetch(
    PDO::FETCH_ASSOC
);

if (!$reporte) {

    $_SESSION['error'] =
        'El reporte no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;
}

try {

    $resultado = generarArchivoReporte(
        $conexion,
        $rancho_id,
        $tipo_reporte,
        $titulo,
        $fecha_inicio,
        $fecha_fin,
        $formato,
        $observaciones
    );

    /*
    |--------------------------------------------------------------------------
    | Eliminar archivo anterior
    |--------------------------------------------------------------------------
    */

    if (!empty($reporte['archivo'])) {

        $archivoAnterior = __DIR__ .
            DIRECTORY_SEPARATOR .
            str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $reporte['archivo']
            );

        if (
            file_exists($archivoAnterior) &&
            realpath($archivoAnterior) !==
            realpath($resultado['rutaArchivo'])
        ) {

            @unlink($archivoAnterior);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar BD
    |--------------------------------------------------------------------------
    */

    $sql = "
        UPDATE reportes
        SET
            tipo_reporte = :tipo_reporte,
            titulo = :titulo,
            fecha_inicio = :fecha_inicio,
            fecha_fin = :fecha_fin,
            formato = :formato,
            archivo = :archivo,
            observaciones = :observaciones
        WHERE id = :id
          AND rancho_id = :rancho_id
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':tipo_reporte' => $tipo_reporte,
        ':titulo' => $titulo,
        ':fecha_inicio' => $fecha_inicio,
        ':fecha_fin' => $fecha_fin,
        ':formato' => $formato,
        ':archivo' => $resultado['archivoBD'],
        ':observaciones' => $observaciones !== ''
            ? $observaciones
            : null,
        ':id' => $id,
        ':rancho_id' => $rancho_id
    ]);

    $_SESSION['success'] =
        'Reporte actualizado y regenerado correctamente.';

    $_SESSION['descargar_reporte'] =
        $id;

    header('Location: index.php');
    exit;

} catch (Throwable $e) {

    $_SESSION['error'] =
        'Error al actualizar el reporte: ' .
        $e->getMessage();

    header('Location: editar.php?id=' . $id);
    exit;
}
