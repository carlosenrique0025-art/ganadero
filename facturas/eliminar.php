<?php

session_start();

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| Mostrar errores temporalmente
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);

ini_set(
    'display_errors',
    1
);


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
| Verificar POST
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
        'La factura seleccionada no es válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Buscar factura
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        archivo
    FROM facturas
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$factura = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$factura) {

    $_SESSION['error'] =
        'La factura no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Eliminar archivo
|--------------------------------------------------------------------------
*/

if (!empty($factura['archivo'])) {

    $ruta_archivo =
        __DIR__
        . DIRECTORY_SEPARATOR
        . $factura['archivo'];


    /*
    | Solo eliminar archivos dentro de /archivos/
    */

    $carpeta_archivos = realpath(
        __DIR__
        . DIRECTORY_SEPARATOR
        . 'archivos'
    );


    $archivo_real = realpath(
        $ruta_archivo
    );


    if (
        $carpeta_archivos !== false &&
        $archivo_real !== false &&
        strpos(
            $archivo_real,
            $carpeta_archivos
            . DIRECTORY_SEPARATOR
        ) === 0
    ) {

        if (
            is_file($archivo_real)
        ) {

            unlink($archivo_real);

        }

    }

}


/*
|--------------------------------------------------------------------------
| Eliminar factura de BD
|--------------------------------------------------------------------------
*/

$sql = "
    DELETE FROM facturas
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);


/*
|--------------------------------------------------------------------------
| Resultado
|--------------------------------------------------------------------------
*/

if ($stmt->rowCount() > 0) {

    $_SESSION['success'] =
        'La factura se eliminó correctamente.';

} else {

    $_SESSION['error'] =
        'No se pudo eliminar la factura.';

}


/*
|--------------------------------------------------------------------------
| Regresar
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
