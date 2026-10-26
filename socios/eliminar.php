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
| Obtener datos de sesión
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Obtener ID del socio
|--------------------------------------------------------------------------
*/

$socio_id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$socio_id) {

    $_SESSION['error'] =
        'El socio seleccionado no es válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el socio pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido_paterno
    FROM socios
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $socio_id,
    ':rancho_id' => $rancho_id
]);

$socio = $stmt->fetch();


if (!$socio) {

    $_SESSION['error'] =
        'El socio no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar si el socio tiene movimientos relacionados
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT COUNT(*)
    FROM movimientos_ganado
    WHERE socio_id = :socio_id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':socio_id' => $socio_id,
    ':rancho_id' => $rancho_id
]);

$movimientos = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Verificar si el socio tiene facturas relacionadas
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT COUNT(*)
    FROM facturas
    WHERE socio_id = :socio_id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':socio_id' => $socio_id,
    ':rancho_id' => $rancho_id
]);

$facturas = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| No permitir eliminar si tiene información relacionada
|--------------------------------------------------------------------------
*/

if ($movimientos > 0 || $facturas > 0) {

    $_SESSION['error'] =
        'No se puede eliminar este socio porque tiene '
        . $movimientos
        . ' movimiento(s) y '
        . $facturas
        . ' factura(s) relacionadas.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar socio
|--------------------------------------------------------------------------
*/

try {

    $sql = "
        DELETE FROM socios
        WHERE id = :id
          AND rancho_id = :rancho_id
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':id' => $socio_id,
        ':rancho_id' => $rancho_id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Verificar eliminación
    |--------------------------------------------------------------------------
    */

    if ($stmt->rowCount() === 0) {

        $_SESSION['error'] =
            'No se pudo eliminar el socio.';

        header('Location: index.php');
        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | Mensaje de éxito
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        'El socio '
        . trim(
            $socio['nombre']
            . ' '
            . ($socio['apellido_paterno'] ?? '')
        )
        . ' fue eliminado correctamente.';


} catch (PDOException $e) {

    $_SESSION['error'] =
        'No se pudo eliminar el socio porque tiene información relacionada.';

}


/*
|--------------------------------------------------------------------------
| Regresar al listado
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
