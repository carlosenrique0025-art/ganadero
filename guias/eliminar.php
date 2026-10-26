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
| Obtener ID de la guía
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
| Verificar que la guía pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        numero_guia,
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
| Verificar existencia
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
| No permitir eliminar guías utilizadas
|--------------------------------------------------------------------------
*/

if ($guia['estado'] === 'UTILIZADA') {

    $_SESSION['error'] =
        'No se puede eliminar una guía que ya fue utilizada.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar guía
|--------------------------------------------------------------------------
|
| guia_ganado se eliminará automáticamente debido a:
|
| FOREIGN KEY (guia_id)
| REFERENCES guias(id)
| ON DELETE CASCADE
|
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM guias
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
        'La guía fue eliminada correctamente.';

} else {

    $_SESSION['error'] =
        'No fue posible eliminar la guía.';

}


/*
|--------------------------------------------------------------------------
| Regresar al listado
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
