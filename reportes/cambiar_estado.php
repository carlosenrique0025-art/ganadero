<?php

session_start();

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar método POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener rancho
|--------------------------------------------------------------------------
*/

$rancho_id = (int)($_SESSION['rancho_id'] ?? 0);


/*
|--------------------------------------------------------------------------
| Obtener ID del reporte
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Obtener nuevo estado
|--------------------------------------------------------------------------
*/

$estado = trim(
    $_POST['estado'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Estados permitidos
|--------------------------------------------------------------------------
|
| Ajusta estos valores si tu columna estado utiliza otros valores.
|
*/

$estados_permitidos = [
    'PENDIENTE',
    'GENERADO',
    'DESCARGADO',
    'CANCELADO'
];


if (!in_array(
    $estado,
    $estados_permitidos,
    true
)) {

    $_SESSION['error'] =
        'El estado seleccionado no es válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el reporte pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
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

$reporte = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$reporte) {

    $_SESSION['error'] =
        'El reporte no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar estado
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE reportes
    SET estado = :estado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':estado' => $estado,
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El estado del reporte se actualizó correctamente.';


/*
|--------------------------------------------------------------------------
| Regresar a reportes
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
