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

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Obtener ID del socio
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


/*
|--------------------------------------------------------------------------
| Verificar ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    $_SESSION['error'] =
        'Socio no válido.';

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
    FROM socios
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$socio = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verificar socio
|--------------------------------------------------------------------------
*/

if (!$socio) {

    $_SESSION['error'] =
        'El socio no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Cambiar estado
|--------------------------------------------------------------------------
*/

$nuevo_estado = ((int)$socio['estado'] === 1)
    ? 0
    : 1;


/*
|--------------------------------------------------------------------------
| Actualizar estado
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE socios
    SET estado = :estado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':estado' => $nuevo_estado,

    ':id' => $id,

    ':rancho_id' => $rancho_id

]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

if ($nuevo_estado === 1) {

    $_SESSION['success'] =
        'El socio fue activado correctamente.';

} else {

    $_SESSION['success'] =
        'El socio fue desactivado correctamente.';

}


/*
|--------------------------------------------------------------------------
| Regresar al listado
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
