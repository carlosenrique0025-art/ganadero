<?php

session_start();

require_once '../config/database.php';

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
        'El reporte no existe.';

    header('Location: index.php');
    exit;
}

try {

    $conexion->beginTransaction();

    $sql = "
        DELETE FROM reportes
        WHERE id = :id
          AND rancho_id = :rancho_id
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':id' => $id,
        ':rancho_id' => $rancho_id
    ]);

    $conexion->commit();

    if (!empty($reporte['archivo'])) {

        $ruta = __DIR__ .
            DIRECTORY_SEPARATOR .
            str_replace(
                ['/', '\\'],
                DIRECTORY_SEPARATOR,
                $reporte['archivo']
            );

        if (file_exists($ruta)) {
            @unlink($ruta);
        }
    }

    $_SESSION['success'] =
        'Reporte eliminado correctamente.';

} catch (Throwable $e) {

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    $_SESSION['error'] =
        'No fue posible eliminar el reporte.';
}

header('Location: index.php');
exit;
