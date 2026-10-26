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
        'Socio no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$nombre = trim(
    $_POST['nombre'] ?? ''
);

$apellido_paterno = trim(
    $_POST['apellido_paterno'] ?? ''
);

$apellido_materno = trim(
    $_POST['apellido_materno'] ?? ''
);

$telefono = trim(
    $_POST['telefono'] ?? ''
);

$email = trim(
    $_POST['email'] ?? ''
);

$direccion = trim(
    $_POST['direccion'] ?? ''
);

$rfc = trim(
    $_POST['rfc'] ?? ''
);

$curp = trim(
    $_POST['curp'] ?? ''
);

$tipo = $_POST['tipo'] ?? '';


/*
|--------------------------------------------------------------------------
| Validar nombre
|--------------------------------------------------------------------------
*/

if ($nombre === '') {

    $_SESSION['error'] =
        'El nombre es obligatorio.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar tipo
|--------------------------------------------------------------------------
*/

$tipos_validos = [
    'SOCIO',
    'CLIENTE',
    'COMPRADOR'
];

if (!in_array($tipo, $tipos_validos, true)) {

    $_SESSION['error'] =
        'El tipo de socio no es válido.';

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
| Verificar que el socio pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id
    FROM socios
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
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
| Actualizar socio
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE socios
    SET
        nombre = :nombre,
        apellido_paterno = :apellido_paterno,
        apellido_materno = :apellido_materno,
        telefono = :telefono,
        email = :email,
        direccion = :direccion,
        rfc = :rfc,
        curp = :curp,
        tipo = :tipo
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':nombre' => $nombre,

    ':apellido_paterno' =>
        $apellido_paterno !== ''
            ? $apellido_paterno
            : null,

    ':apellido_materno' =>
        $apellido_materno !== ''
            ? $apellido_materno
            : null,

    ':telefono' =>
        $telefono !== ''
            ? $telefono
            : null,

    ':email' =>
        $email !== ''
            ? $email
            : null,

    ':direccion' =>
        $direccion !== ''
            ? $direccion
            : null,

    ':rfc' =>
        $rfc !== ''
            ? $rfc
            : null,

    ':curp' =>
        $curp !== ''
            ? $curp
            : null,

    ':tipo' => $tipo,

    ':id' => $id,

    ':rancho_id' => $rancho_id

]);


/*
|--------------------------------------------------------------------------
| Mensaje de éxito
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El socio se actualizó correctamente.';


/*
|--------------------------------------------------------------------------
| Regresar a socios
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
