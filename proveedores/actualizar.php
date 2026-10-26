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
| Recibir datos
|--------------------------------------------------------------------------
*/

$nombre = trim($_POST['nombre'] ?? '');

$empresa = trim($_POST['empresa'] ?? '');

$rfc = trim($_POST['rfc'] ?? '');

$telefono = trim($_POST['telefono'] ?? '');

$email = trim($_POST['email'] ?? '');

$direccion = trim($_POST['direccion'] ?? '');

$ciudad = trim($_POST['ciudad'] ?? '');

$estado_direccion = trim(
    $_POST['estado_direccion'] ?? ''
);

$productos_servicios = trim(
    $_POST['productos_servicios'] ?? ''
);

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validar nombre
|--------------------------------------------------------------------------
*/

if ($nombre === '') {

    $_SESSION['error'] =
        'El nombre del proveedor es obligatorio.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar correo
|--------------------------------------------------------------------------
*/

if (
    $email !== '' &&
    !filter_var($email, FILTER_VALIDATE_EMAIL)
) {

    $_SESSION['error'] =
        'El correo electrónico no es válido.';

    header('Location: editar.php?id=' . $id);
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

if (!$stmt->fetch()) {

    $_SESSION['error'] =
        'El proveedor no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar proveedor
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE proveedores
    SET
        nombre = :nombre,
        empresa = :empresa,
        rfc = :rfc,
        telefono = :telefono,
        email = :email,
        direccion = :direccion,
        ciudad = :ciudad,
        estado_direccion = :estado_direccion,
        productos_servicios = :productos_servicios,
        observaciones = :observaciones
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':nombre' => $nombre,

    ':empresa' => $empresa !== ''
        ? $empresa
        : null,

    ':rfc' => $rfc !== ''
        ? $rfc
        : null,

    ':telefono' => $telefono !== ''
        ? $telefono
        : null,

    ':email' => $email !== ''
        ? $email
        : null,

    ':direccion' => $direccion !== ''
        ? $direccion
        : null,

    ':ciudad' => $ciudad !== ''
        ? $ciudad
        : null,

    ':estado_direccion' => $estado_direccion !== ''
        ? $estado_direccion
        : null,

    ':productos_servicios' => $productos_servicios !== ''
        ? $productos_servicios
        : null,

    ':observaciones' => $observaciones !== ''
        ? $observaciones
        : null,

    ':id' => $id,

    ':rancho_id' => $rancho_id
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El proveedor se actualizó correctamente.';


header('Location: index.php');

exit;
