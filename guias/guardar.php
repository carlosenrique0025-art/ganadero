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
| Datos de sesión
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];
$usuario_id = $_SESSION['usuario_id'];


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$numero_guia = trim(
    $_POST['numero_guia'] ?? ''
);

$tipo = $_POST['tipo'] ?? '';

$fecha_emision = $_POST['fecha_emision'] ?? '';

$fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';

$origen = trim(
    $_POST['origen'] ?? ''
);

$destino = trim(
    $_POST['destino'] ?? ''
);

$transportista = trim(
    $_POST['transportista'] ?? ''
);

$placas = trim(
    $_POST['placas'] ?? ''
);

$socio_id = filter_input(
    INPUT_POST,
    'socio_id',
    FILTER_VALIDATE_INT
);

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);

$ganado_ids = $_POST['ganado_ids'] ?? [];


/*
|--------------------------------------------------------------------------
| Tipos de guía permitidos
|--------------------------------------------------------------------------
*/

$tipos_validos = [
    'MOVILIZACION',
    'TRASLADO',
    'VENTA',
    'COMPRA',
    'OTRA'
];


/*
|--------------------------------------------------------------------------
| Validar datos obligatorios
|--------------------------------------------------------------------------
*/

if (
    $numero_guia === '' ||
    !in_array($tipo, $tipos_validos, true) ||
    $fecha_emision === ''
) {

    $_SESSION['error'] =
        'Completa todos los campos obligatorios.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar fecha de vencimiento
|--------------------------------------------------------------------------
*/

if ($fecha_vencimiento !== '') {

    if ($fecha_vencimiento < $fecha_emision) {

        $_SESSION['error'] =
            'La fecha de vencimiento no puede ser anterior a la fecha de emisión.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Validar animales
|--------------------------------------------------------------------------
*/

if (!is_array($ganado_ids)) {

    $ganado_ids = [];

}


/*
|--------------------------------------------------------------------------
| Limpiar IDs de ganado
|--------------------------------------------------------------------------
*/

$ganado_ids_limpios = [];

foreach ($ganado_ids as $ganado_id) {

    $ganado_id = filter_var(
        $ganado_id,
        FILTER_VALIDATE_INT
    );

    if ($ganado_id) {

        $ganado_ids_limpios[] =
            (int)$ganado_id;

    }

}


/*
|--------------------------------------------------------------------------
| Eliminar IDs repetidos
|--------------------------------------------------------------------------
*/

$ganado_ids_limpios = array_values(
    array_unique($ganado_ids_limpios)
);


/*
|--------------------------------------------------------------------------
| Debe existir al menos un animal
|--------------------------------------------------------------------------
*/

if (empty($ganado_ids_limpios)) {

    $_SESSION['error'] =
        'Debes seleccionar al menos un animal para la guía.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar número de guía duplicado
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM guias
    WHERE rancho_id = :rancho_id
      AND numero_guia = :numero_guia
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':numero_guia' => $numero_guia
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe una guía con ese número en tu rancho.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar socio
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

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar que todos los animales pertenecen
| al rancho y están activos
|--------------------------------------------------------------------------
*/

$placeholders = implode(
    ',',
    array_fill(
        0,
        count($ganado_ids_limpios),
        '?'
    )
);

$sql = "
    SELECT id
    FROM ganado
    WHERE id IN ($placeholders)
      AND rancho_id = ?
      AND estado = 1
";

$stmt = $conexion->prepare($sql);

$parametros = $ganado_ids_limpios;

$parametros[] = $rancho_id;

$stmt->execute($parametros);

$animales_validos = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Verificar cantidad
|--------------------------------------------------------------------------
*/

if (
    count($animales_validos)
    !== count($ganado_ids_limpios)
) {

    $_SESSION['error'] =
        'Uno o más animales seleccionados no son válidos o no pertenecen a tu rancho.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Cantidad de animales
|--------------------------------------------------------------------------
*/

$cantidad_animales =
    count($ganado_ids_limpios);


/*
|--------------------------------------------------------------------------
| Preparar valores NULL
|--------------------------------------------------------------------------
*/

$fecha_vencimiento_db =
    $fecha_vencimiento !== ''
        ? $fecha_vencimiento
        : null;

$socio_id_db =
    $socio_id
        ? $socio_id
        : null;

$origen_db =
    $origen !== ''
        ? $origen
        : null;

$destino_db =
    $destino !== ''
        ? $destino
        : null;

$transportista_db =
    $transportista !== ''
        ? $transportista
        : null;

$placas_db =
    $placas !== ''
        ? $placas
        : null;

$observaciones_db =
    $observaciones !== ''
        ? $observaciones
        : null;


/*
|--------------------------------------------------------------------------
| Iniciar transacción
|--------------------------------------------------------------------------
*/

try {

    $conexion->beginTransaction();


    /*
    |--------------------------------------------------------------------------
    | Insertar guía
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO guias
        (
            rancho_id,
            socio_id,
            usuario_id,
            numero_guia,
            tipo,
            fecha_emision,
            fecha_vencimiento,
            origen,
            destino,
            transportista,
            placas,
            cantidad_animales,
            observaciones,
            estado
        )
        VALUES
        (
            :rancho_id,
            :socio_id,
            :usuario_id,
            :numero_guia,
            :tipo,
            :fecha_emision,
            :fecha_vencimiento,
            :origen,
            :destino,
            :transportista,
            :placas,
            :cantidad_animales,
            :observaciones,
            'ACTIVA'
        )
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([

        ':rancho_id' => $rancho_id,

        ':socio_id' => $socio_id_db,

        ':usuario_id' => $usuario_id,

        ':numero_guia' => $numero_guia,

        ':tipo' => $tipo,

        ':fecha_emision' => $fecha_emision,

        ':fecha_vencimiento' =>
            $fecha_vencimiento_db,

        ':origen' => $origen_db,

        ':destino' => $destino_db,

        ':transportista' =>
            $transportista_db,

        ':placas' => $placas_db,

        ':cantidad_animales' =>
            $cantidad_animales,

        ':observaciones' =>
            $observaciones_db

    ]);


    /*
    |--------------------------------------------------------------------------
    | Obtener ID de la guía
    |--------------------------------------------------------------------------
    */

    $guia_id =
        $conexion->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | Insertar animales de la guía
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO guia_ganado
        (
            guia_id,
            ganado_id
        )
        VALUES
        (
            :guia_id,
            :ganado_id
        )
    ";

    $stmt = $conexion->prepare($sql);


    foreach ($ganado_ids_limpios as $ganado_id) {

        $stmt->execute([

            ':guia_id' => $guia_id,

            ':ganado_id' => $ganado_id

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar transacción
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


    /*
    |--------------------------------------------------------------------------
    | Mensaje de éxito
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        'La guía se registró correctamente.';


    /*
    |--------------------------------------------------------------------------
    | Regresar
    |--------------------------------------------------------------------------
    */

    header('Location: index.php');

    exit;


} catch (PDOException $e) {


    /*
    |--------------------------------------------------------------------------
    | Cancelar transacción
    |--------------------------------------------------------------------------
    */

    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }


    /*
    |--------------------------------------------------------------------------
    | Mensaje
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] =
        'No fue posible registrar la guía. Verifica los datos e inténtalo nuevamente.';


    /*
    |--------------------------------------------------------------------------
    | Regresar
    |--------------------------------------------------------------------------
    */

    header('Location: crear.php');

    exit;

}
