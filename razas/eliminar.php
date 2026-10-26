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
| Verificar que pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id, nombre
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
| Verificar si la raza está siendo utilizada
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT COUNT(*)
    FROM ganado
    WHERE raza_id = :raza_id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':raza_id' => $id,
    ':rancho_id' => $rancho_id
]);

$total_ganado = (int)$stmt->fetchColumn();


/*
|--------------------------------------------------------------------------
| Si está siendo utilizada
|--------------------------------------------------------------------------
*/

if ($total_ganado > 0) {

    $_SESSION['error'] =
        'No puedes eliminar esta raza porque está asociada a '
        . $total_ganado
        . ' animal(es). Puedes desactivarla en su lugar.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar raza
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM razas
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
    'La raza "' . $raza['nombre'] . '" fue eliminada correctamente.';


header('Location: index.php');

exit;
