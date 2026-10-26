<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;
}

$rancho_id = (int)$_SESSION['rancho_id'];

$id = filter_input(
    INPUT_GET,
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
        id,
        archivo,
        formato,
        titulo,
        estado
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

if (empty($reporte['archivo'])) {

    $_SESSION['error'] =
        'Este reporte no tiene archivo.';

    header('Location: index.php');
    exit;
}

$ruta = __DIR__ .
    DIRECTORY_SEPARATOR .
    str_replace(
        ['/', '\\'],
        DIRECTORY_SEPARATOR,
        $reporte['archivo']
    );

if (!file_exists($ruta)) {

    $_SESSION['error'] =
        'El archivo no fue encontrado.';

    header('Location: index.php');
    exit;
}

$extension = strtolower(
    pathinfo(
        $ruta,
        PATHINFO_EXTENSION
    )
);

switch ($extension) {

    case 'pdf':
        $mime = 'application/pdf';
        break;

    case 'xlsx':
        $mime =
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        break;

    case 'docx':
        $mime =
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;

    case 'csv':
        $mime = 'text/csv; charset=UTF-8';
        break;

    default:
        $mime = 'application/octet-stream';
        break;
}

$nombre = basename($ruta);

while (ob_get_level()) {
    ob_end_clean();
}

header(
    'Content-Type: ' . $mime
);

header(
    'Content-Disposition: attachment; filename="' .
    $nombre .
    '"'
);

header(
    'Content-Length: ' .
    filesize($ruta)
);

header(
    'Cache-Control: private, max-age=0, must-revalidate'
);

header(
    'Pragma: public'
);

readfile($ruta);

exit;
