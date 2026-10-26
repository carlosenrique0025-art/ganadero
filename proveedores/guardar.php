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

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Rancho
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];


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

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar correo
|--------------------------------------------------------------------------
*/

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION['error'] =
        'El correo electrónico no es válido.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Insertar proveedor
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO proveedores
    (
        rancho_id,
        nombre,
        empresa,
        rfc,
        telefono,
        email,
        direccion,
        ciudad,
        estado_direccion,
        productos_servicios,
        observaciones,
        estado
    )
    VALUES
    (
        :rancho_id,
        :nombre,
        :empresa,
        :rfc,
        :telefono,
        :email,
        :direccion,
        :ciudad,
        :estado_direccion,
        :productos_servicios,
        :observaciones,
        1
    )
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':rancho_id' => $rancho_id,

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
        : null
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El proveedor se registró correctamente.';


header('Location: index.php');

exit;
