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

$rfc = strtoupper(
    trim($_POST['rfc'] ?? '')
);

$curp = strtoupper(
    trim($_POST['curp'] ?? '')
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

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar tipo
|--------------------------------------------------------------------------
*/

if (!in_array(
    $tipo,
    ['SOCIO', 'CLIENTE', 'COMPRADOR'],
    true
)) {

    $_SESSION['error'] =
        'El tipo de socio seleccionado no es válido.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar email
|--------------------------------------------------------------------------
*/

if ($email !== '') {

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['error'] =
            'El correo electrónico no es válido.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Validar RFC
|--------------------------------------------------------------------------
*/

if ($rfc !== '') {

    if (
        strlen($rfc) < 12 ||
        strlen($rfc) > 13
    ) {

        $_SESSION['error'] =
            'El RFC debe tener entre 12 y 13 caracteres.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Validar CURP
|--------------------------------------------------------------------------
*/

if ($curp !== '') {

    if (strlen($curp) !== 18) {

        $_SESSION['error'] =
            'La CURP debe contener exactamente 18 caracteres.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar RFC duplicado dentro del rancho
|--------------------------------------------------------------------------
*/

if ($rfc !== '') {

    $sql = "
        SELECT id
        FROM socios
        WHERE rancho_id = :rancho_id
          AND rfc = :rfc
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':rancho_id' => $rancho_id,
        ':rfc' => $rfc
    ]);

    if ($stmt->fetch()) {

        $_SESSION['error'] =
            'Ya existe un socio con ese RFC en tu rancho.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar CURP duplicada dentro del rancho
|--------------------------------------------------------------------------
*/

if ($curp !== '') {

    $sql = "
        SELECT id
        FROM socios
        WHERE rancho_id = :rancho_id
          AND curp = :curp
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':rancho_id' => $rancho_id,
        ':curp' => $curp
    ]);

    if ($stmt->fetch()) {

        $_SESSION['error'] =
            'Ya existe un socio con esa CURP en tu rancho.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Insertar socio
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO socios
    (
        rancho_id,
        nombre,
        apellido_paterno,
        apellido_materno,
        telefono,
        email,
        direccion,
        rfc,
        curp,
        tipo,
        estado
    )
    VALUES
    (
        :rancho_id,
        :nombre,
        :apellido_paterno,
        :apellido_materno,
        :telefono,
        :email,
        :direccion,
        :rfc,
        :curp,
        :tipo,
        1
    )
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':rancho_id' => $rancho_id,

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

    ':tipo' => $tipo
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El socio se registró correctamente.';


header('Location: index.php');

exit;
