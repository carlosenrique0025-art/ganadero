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
| Obtener datos de sesión
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
        'Movimiento no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$ganado_id = filter_input(
    INPUT_POST,
    'ganado_id',
    FILTER_VALIDATE_INT
);

$socio_id = filter_input(
    INPUT_POST,
    'socio_id',
    FILTER_VALIDATE_INT
);

$tipo_movimiento = $_POST['tipo_movimiento'] ?? '';

$fecha_movimiento = $_POST['fecha_movimiento'] ?? '';

$peso = $_POST['peso'] ?? '';

$precio = $_POST['precio'] ?? '';

$origen = trim(
    $_POST['origen'] ?? ''
);

$destino = trim(
    $_POST['destino'] ?? ''
);

$motivo = trim(
    $_POST['motivo'] ?? ''
);

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);


/*
|--------------------------------------------------------------------------
| Validar campos obligatorios
|--------------------------------------------------------------------------
*/

$tipos_validos = [
    'ENTRADA',
    'SALIDA',
    'COMPRA',
    'VENTA',
    'TRASLADO',
    'MUERTE',
    'BAJA',
    'OTRO'
];


if (
    !$ganado_id ||
    !in_array($tipo_movimiento, $tipos_validos, true) ||
    $fecha_movimiento === ''
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

if ($peso !== '') {

    if (
        !is_numeric($peso) ||
        (float)$peso < 0
    ) {

        $_SESSION['error'] =
            'El peso ingresado no es válido.';

        header('Location: editar.php?id=' . $id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Validar precio
|--------------------------------------------------------------------------
*/

if ($precio !== '') {

    if (
        !is_numeric($precio) ||
        (float)$precio < 0
    ) {

        $_SESSION['error'] =
            'El precio ingresado no es válido.';

        header('Location: editar.php?id=' . $id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar que el movimiento pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM movimientos_ganado
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
        'El movimiento no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el ganado pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM ganado
    WHERE id = :ganado_id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':ganado_id' => $ganado_id,
    ':rancho_id' => $rancho_id
]);

if (!$stmt->fetch()) {

    $_SESSION['error'] =
        'El animal seleccionado no es válido.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que el socio pertenece al rancho
|--------------------------------------------------------------------------
*/

if ($socio_id) {

    $sql = "
        SELECT id
        FROM socios
        WHERE id = :socio_id
          AND rancho_id = :rancho_id
          AND estado = 1
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':socio_id' => $socio_id,
        ':rancho_id' => $rancho_id
    ]);

    if (!$stmt->fetch()) {

        $_SESSION['error'] =
            'El socio seleccionado no es válido.';

        header('Location: editar.php?id=' . $id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Preparar valores NULL
|--------------------------------------------------------------------------
*/

$peso_db = $peso !== ''
    ? $peso
    : null;

$precio_db = $precio !== ''
    ? $precio
    : null;

$socio_db = $socio_id
    ? $socio_id
    : null;

$origen_db = $origen !== ''
    ? $origen
    : null;

$destino_db = $destino !== ''
    ? $destino
    : null;

$motivo_db = $motivo !== ''
    ? $motivo
    : null;

$observaciones_db = $observaciones !== ''
    ? $observaciones
    : null;


/*
|--------------------------------------------------------------------------
| Actualizar movimiento
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE movimientos_ganado
    SET
        ganado_id = :ganado_id,
        socio_id = :socio_id,
        tipo_movimiento = :tipo_movimiento,
        fecha_movimiento = :fecha_movimiento,
        peso = :peso,
        precio = :precio,
        origen = :origen,
        destino = :destino,
        motivo = :motivo,
        observaciones = :observaciones
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':ganado_id' => $ganado_id,

    ':socio_id' => $socio_db,

    ':tipo_movimiento' => $tipo_movimiento,

    ':fecha_movimiento' => $fecha_movimiento,

    ':peso' => $peso_db,

    ':precio' => $precio_db,

    ':origen' => $origen_db,

    ':destino' => $destino_db,

    ':motivo' => $motivo_db,

    ':observaciones' => $observaciones_db,

    ':id' => $id,

    ':rancho_id' => $rancho_id

]);


/*
|--------------------------------------------------------------------------
| Mensaje de éxito
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'El movimiento se actualizó correctamente.';


/*
|--------------------------------------------------------------------------
| Regresar al listado
|--------------------------------------------------------------------------
*/

header('Location: index.php');

exit;
