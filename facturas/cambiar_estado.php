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
| Datos
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$estado = $_POST['estado'] ?? '';


/*
|--------------------------------------------------------------------------
| Validar
|--------------------------------------------------------------------------
*/

$estados_validos = [
    'PENDIENTE',
    'PAGADA',
    'CANCELADA'
];


if (
    !$id ||
    !in_array(
        $estado,
        $estados_validos,
        true
    )
) {

    $_SESSION['error'] =
        'Datos no válidos.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE facturas
    SET estado_pago = :estado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':estado' => $estado,
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);


if ($stmt->rowCount() > 0) {

    $_SESSION['success'] =
        'El estado de la factura se actualizó correctamente.';

} else {

    $_SESSION['error'] =
        'No se pudo actualizar el estado de la factura.';

}


header('Location: index.php');

exit;
