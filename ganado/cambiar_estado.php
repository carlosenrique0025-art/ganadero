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
| Verificar método
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');
    exit;

}


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
        'Animal no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener estado actual
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT estado
    FROM ganado
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$animal = $stmt->fetch();


if (!$animal) {

    $_SESSION['error'] =
        'El animal no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Cambiar estado
|--------------------------------------------------------------------------
*/

$nuevo_estado = ((int)$animal['estado'] === 1)
    ? 0
    : 1;


/*
|--------------------------------------------------------------------------
| Actualizar estado
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE ganado
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
        'El animal fue activado correctamente.';

} else {

    $_SESSION['success'] =
        'El animal fue desactivado correctamente.';

}


header('Location: index.php');

exit;
