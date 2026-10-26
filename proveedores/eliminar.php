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
        'Proveedor no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el proveedor pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM proveedores
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$proveedor = $stmt->fetch();


if (!$proveedor) {

    $_SESSION['error'] =
        'El proveedor no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar proveedor
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM proveedores
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
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El proveedor fue eliminado correctamente.';


/*
|--------------------------------------------------------------------------
| Regresar
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
