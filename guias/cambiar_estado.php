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
| Rancho del usuario
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Obtener ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    $_SESSION['error'] =
        'Guía no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener estado actual
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        estado
    FROM guias
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$guia = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verificar que existe
|--------------------------------------------------------------------------
*/

if (!$guia) {

    $_SESSION['error'] =
        'La guía no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Determinar nuevo estado
|--------------------------------------------------------------------------
|
| ACTIVA      → CANCELADA
| CANCELADA   → ACTIVA
|
| UTILIZADA y VENCIDA no se cambian con este botón.
|
*/

$estado_actual = $guia['estado'];


if ($estado_actual === 'ACTIVA') {

    $nuevo_estado = 'CANCELADA';

} elseif ($estado_actual === 'CANCELADA') {

    $nuevo_estado = 'ACTIVA';

} else {

    $_SESSION['error'] =
        'Las guías utilizadas o vencidas no pueden cambiarse mediante este botón.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar estado
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE guias
    SET estado = :estado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':estado' =>
        $nuevo_estado,

    ':id' =>
        $id,

    ':rancho_id' =>
        $rancho_id

]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

if ($nuevo_estado === 'ACTIVA') {

    $_SESSION['success'] =
        'La guía fue activada correctamente.';

} else {

    $_SESSION['success'] =
        'La guía fue cancelada correctamente.';

}


/*
|--------------------------------------------------------------------------
| Regresar
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
