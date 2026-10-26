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
| Datos
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Validar ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    $_SESSION['error'] =
        'Raza no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Buscar estado actual
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT estado
    FROM razas
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$raza = $stmt->fetch();


if (!$raza) {

    $_SESSION['error'] =
        'La raza no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Cambiar estado
|--------------------------------------------------------------------------
*/

$nuevo_estado = ((int)$raza['estado'] === 1)
    ? 0
    : 1;


$sql = "
    UPDATE razas
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
        'La raza fue activada correctamente.';

} else {

    $_SESSION['success'] =
        'La raza fue desactivada correctamente.';

}


header('Location: index.php');

exit;
