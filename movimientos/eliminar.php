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


/*
|--------------------------------------------------------------------------
| Obtener rancho
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
        'Movimiento no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el movimiento pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM movimientos_ganado
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$movimiento = $stmt->fetch();


if (!$movimiento) {

    $_SESSION['error'] =
        'El movimiento no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar movimiento
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM movimientos_ganado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);


/*
|--------------------------------------------------------------------------
| Verificar eliminación
|--------------------------------------------------------------------------
*/

if ($stmt->rowCount() > 0) {

    $_SESSION['success'] =
        'El movimiento fue eliminado correctamente.';

} else {

    $_SESSION['error'] =
        'No fue posible eliminar el movimiento.';

}


/*
|--------------------------------------------------------------------------
| Regresar al listado
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
