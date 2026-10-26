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
| Datos de sesión
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];
$usuario_id = $_SESSION['usuario_id'];


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
        'Guía no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$numero_guia = trim(
    $_POST['numero_guia'] ?? ''
);

$tipo = $_POST['tipo'] ?? '';

$fecha_emision =
    $_POST['fecha_emision'] ?? '';

$fecha_vencimiento =
    $_POST['fecha_vencimiento'] ?? '';

$socio_id = filter_input(
    INPUT_POST,
    'socio_id',
    FILTER_VALIDATE_INT
);

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

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);

$estado = $_POST['estado'] ?? '';

$ganado_ids = $_POST['ganado_ids'] ?? [];


/*
|--------------------------------------------------------------------------
| Tipos permitidos
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
| Estados permitidos
|--------------------------------------------------------------------------
*/

$estados_validos = [
    'ACTIVA',
    'UTILIZADA',
    'VENCIDA',
    'CANCELADA'
];


/*
|--------------------------------------------------------------------------
| Validar campos obligatorios
|--------------------------------------------------------------------------
*/

if (
    $numero_guia === '' ||
    !in_array($tipo, $tipos_validos, true) ||
    $fecha_emision === '' ||
    !in_array($estado, $estados_validos, true)
) {

    $_SESSION['error'] =
        'Completa correctamente todos los campos obligatorios.';

    header('Location: editar.php?id=' . $id);
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

        header('Location: editar.php?id=' . $id);
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
| Limpiar IDs
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
| Eliminar duplicados
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

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que la guía pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
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

if (!$stmt->fetch()) {

    $_SESSION['error'] =
        'La guía no existe o no pertenece a tu rancho.';

    header('Location: index.php');
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
      AND id <> :id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':numero_guia' => $numero_guia,
    ':id' => $id
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe otra guía con ese número en tu rancho.';

    header('Location: editar.php?id=' . $id);
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

        header('Location: editar.php?id=' . $id);
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Verificar animales
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

$parametros =
    $ganado_ids_limpios;

$parametros[] =
    $rancho_id;

$stmt->execute($parametros);

$animales_validos =
    $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Comparar cantidad de animales
|--------------------------------------------------------------------------
*/

if (
    count($animales_validos)
    !== count($ganado_ids_limpios)
) {

    $_SESSION['error'] =
        'Uno o más animales seleccionados no son válidos o no pertenecen a tu rancho.';

    header('Location: editar.php?id=' . $id);
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
| Convertir valores vacíos a NULL
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
    | Actualizar guía
    |--------------------------------------------------------------------------
    */

    $sql = "
        UPDATE guias
        SET
            socio_id = :socio_id,
            usuario_id = :usuario_id,
            numero_guia = :numero_guia,
            tipo = :tipo,
            fecha_emision = :fecha_emision,
            fecha_vencimiento = :fecha_vencimiento,
            origen = :origen,
            destino = :destino,
            transportista = :transportista,
            placas = :placas,
            cantidad_animales = :cantidad_animales,
            observaciones = :observaciones,
            estado = :estado
        WHERE id = :id
          AND rancho_id = :rancho_id
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([

        ':socio_id' =>
            $socio_id_db,

        ':usuario_id' =>
            $usuario_id,

        ':numero_guia' =>
            $numero_guia,

        ':tipo' =>
            $tipo,

        ':fecha_emision' =>
            $fecha_emision,

        ':fecha_vencimiento' =>
            $fecha_vencimiento_db,

        ':origen' =>
            $origen_db,

        ':destino' =>
            $destino_db,

        ':transportista' =>
            $transportista_db,

        ':placas' =>
            $placas_db,

        ':cantidad_animales' =>
            $cantidad_animales,

        ':observaciones' =>
            $observaciones_db,

        ':estado' =>
            $estado,

        ':id' =>
            $id,

        ':rancho_id' =>
            $rancho_id

    ]);


    /*
    |--------------------------------------------------------------------------
    | Eliminar animales anteriores
    |--------------------------------------------------------------------------
    */

    $sql = "
        DELETE FROM guia_ganado
        WHERE guia_id = :guia_id
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':guia_id' => $id
    ]);


    /*
    |--------------------------------------------------------------------------
    | Insertar nuevamente los animales seleccionados
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

            ':guia_id' =>
                $id,

            ':ganado_id' =>
                $ganado_id

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | Confirmar cambios
    |--------------------------------------------------------------------------
    */

    $conexion->commit();


    /*
    |--------------------------------------------------------------------------
    | Mensaje
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        'La guía se actualizó correctamente.';


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
    | Cancelar cambios
    |--------------------------------------------------------------------------
    */

    if ($conexion->inTransaction()) {

        $conexion->rollBack();

    }


    /*
    |--------------------------------------------------------------------------
    | Mensaje de error
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] =
        'No fue posible actualizar la guía. Verifica los datos e inténtalo nuevamente.';


    /*
    |--------------------------------------------------------------------------
    | Regresar
    |--------------------------------------------------------------------------
    */

    header('Location: editar.php?id=' . $id);

    exit;

}
