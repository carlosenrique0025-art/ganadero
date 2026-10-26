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


$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Recibir ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    $_SESSION['error'] =
        'Animal no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$arete = trim($_POST['arete'] ?? '');

$nombre = trim($_POST['nombre'] ?? '');

$raza_id = filter_input(
    INPUT_POST,
    'raza_id',
    FILTER_VALIDATE_INT
);

$sexo = $_POST['sexo'] ?? '';

$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

$peso_actual = $_POST['peso_actual'] ?? '';

$color = trim($_POST['color'] ?? '');

$procedencia = trim($_POST['procedencia'] ?? '');

$fecha_ingreso = $_POST['fecha_ingreso'] ?? '';

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validar campos obligatorios
|--------------------------------------------------------------------------
*/

if (
    $arete === '' ||
    !$raza_id ||
    ($sexo !== 'MACHO' && $sexo !== 'HEMBRA') ||
    $fecha_ingreso === ''
) {

    $_SESSION['error'] =
        'Completa todos los campos obligatorios.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar peso
|--------------------------------------------------------------------------
*/

if ($peso_actual !== '') {

    if (
        !is_numeric($peso_actual) ||
        (float)$peso_actual < 0
    ) {

        $_SESSION['error'] =
            'El peso ingresado no es válido.';

        header('Location: editar.php?id=' . $id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar que el animal pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM ganado
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
        'El animal no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar arete duplicado
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM ganado
    WHERE rancho_id = :rancho_id
      AND arete = :arete
      AND id <> :id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':arete' => $arete,
    ':id' => $id
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe otro animal con ese número de arete en tu rancho.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar raza
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM razas
    WHERE id = :raza_id
      AND rancho_id = :rancho_id
      AND estado = 1
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':raza_id' => $raza_id,
    ':rancho_id' => $rancho_id
]);

if (!$stmt->fetch()) {

    $_SESSION['error'] =
        'La raza seleccionada no es válida.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar ganado
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE ganado
    SET
        raza_id = :raza_id,
        arete = :arete,
        nombre = :nombre,
        sexo = :sexo,
        fecha_nacimiento = :fecha_nacimiento,
        peso_actual = :peso_actual,
        color = :color,
        procedencia = :procedencia,
        fecha_ingreso = :fecha_ingreso,
        observaciones = :observaciones
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':raza_id' => $raza_id,

    ':arete' => $arete,

    ':nombre' => $nombre !== ''
        ? $nombre
        : null,

    ':sexo' => $sexo,

    ':fecha_nacimiento' => $fecha_nacimiento !== ''
        ? $fecha_nacimiento
        : null,

    ':peso_actual' => $peso_actual !== ''
        ? $peso_actual
        : null,

    ':color' => $color !== ''
        ? $color
        : null,

    ':procedencia' => $procedencia !== ''
        ? $procedencia
        : null,

    ':fecha_ingreso' => $fecha_ingreso,

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
    'El ganado se actualizó correctamente.';


header('Location: index.php');

exit;
