<?php

session_start();

require_once '../config/database.php';


/*
|--------------------------------------------------------------------------
| Sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| POST
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

$folio = trim($_POST['folio'] ?? '');

$tipo = $_POST['tipo'] ?? '';

$fecha = $_POST['fecha'] ?? '';

$concepto = trim($_POST['concepto'] ?? '');

$subtotal = $_POST['subtotal'] ?? '0';

$iva = $_POST['iva'] ?? '0';

$metodo_pago = $_POST['metodo_pago'] ?? '';

$estado_pago = $_POST['estado_pago'] ?? 'PENDIENTE';

$socio_id = filter_input(
    INPUT_POST,
    'socio_id',
    FILTER_VALIDATE_INT
);

$proveedor_id = filter_input(
    INPUT_POST,
    'proveedor_id',
    FILTER_VALIDATE_INT
);

$observaciones = trim(
    $_POST['observaciones'] ?? ''
);


$socio_id = $socio_id ?: null;

$proveedor_id = $proveedor_id ?: null;

$metodo_pago = $metodo_pago !== ''
    ? $metodo_pago
    : null;

$observaciones = $observaciones !== ''
    ? $observaciones
    : null;


/*
|--------------------------------------------------------------------------
| Validaciones
|--------------------------------------------------------------------------
*/

if (
    !$id ||
    $folio === '' ||
    !in_array(
        $tipo,
        ['INGRESO', 'EGRESO'],
        true
    ) ||
    $fecha === '' ||
    $concepto === ''
) {

    $_SESSION['error'] =
        'Los datos de la factura no son válidos.';

    header('Location: index.php');
    exit;

}


if (
    !is_numeric($subtotal) ||
    (float)$subtotal < 0 ||
    !is_numeric($iva) ||
    (float)$iva < 0
) {

    $_SESSION['error'] =
        'Los importes no son válidos.';

    header('Location: editar.php?id=' . $id);
    exit;

}


$subtotal = (float)$subtotal;

$iva = (float)$iva;

$total = $subtotal + $iva;


/*
|--------------------------------------------------------------------------
| Buscar factura actual
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
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

$factura_actual = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$factura_actual) {

    $_SESSION['error'] =
        'La factura no existe.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar folio
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM facturas
    WHERE rancho_id = :rancho_id
      AND folio = :folio
      AND id <> :id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':folio' => $folio,
    ':id' => $id
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe otra factura con ese folio.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Archivo actual
|--------------------------------------------------------------------------
*/

$archivo = $factura_actual['archivo'];


/*
|--------------------------------------------------------------------------
| Nuevo archivo
|--------------------------------------------------------------------------
*/

if (
    isset($_FILES['archivo']) &&
    $_FILES['archivo']['error'] !== UPLOAD_ERR_NO_FILE
) {

    if (
        $_FILES['archivo']['error'] !== UPLOAD_ERR_OK
    ) {

        $_SESSION['error'] =
            'Error al subir el nuevo archivo.';

        header('Location: editar.php?id=' . $id);
        exit;

    }


    if (
        $_FILES['archivo']['size'] >
        10 * 1024 * 1024
    ) {

        $_SESSION['error'] =
            'El archivo no puede superar los 10 MB.';

        header('Location: editar.php?id=' . $id);
        exit;

    }


    $extension = strtolower(
        pathinfo(
            $_FILES['archivo']['name'],
            PATHINFO_EXTENSION
        )
    );


    $permitidas = [
        'pdf',
        'xml',
        'jpg',
        'jpeg',
        'png'
    ];


    if (
        !in_array(
            $extension,
            $permitidas,
            true
        )
    ) {

        $_SESSION['error'] =
            'Tipo de archivo no permitido.';

        header('Location: editar.php?id=' . $id);
        exit;

    }


    $carpeta = __DIR__
        . DIRECTORY_SEPARATOR
        . 'archivos';


    if (!is_dir($carpeta)) {

        mkdir(
            $carpeta,
            0775,
            true
        );

    }


    $nombre_archivo =
        'factura_' .
        $rancho_id .
        '_' .
        date('Ymd_His') .
        '_' .
        bin2hex(
            random_bytes(5)
        ) .
        '.' .
        $extension;


    $ruta_nueva =
        $carpeta .
        DIRECTORY_SEPARATOR .
        $nombre_archivo;


    if (
        !move_uploaded_file(
            $_FILES['archivo']['tmp_name'],
            $ruta_nueva
        )
    ) {

        $_SESSION['error'] =
            'No se pudo guardar el nuevo archivo.';

        header('Location: editar.php?id=' . $id);
        exit;

    }


    /*
    | Eliminar archivo anterior
    */

    if (!empty($factura_actual['archivo'])) {

        $archivo_anterior =
            __DIR__
            . DIRECTORY_SEPARATOR
            . $factura_actual['archivo'];

        if (
            is_file($archivo_anterior)
        ) {

            unlink($archivo_anterior);

        }

    }


    $archivo =
        'archivos/' .
        $nombre_archivo;

}


/*
|--------------------------------------------------------------------------
| Actualizar
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE facturas
    SET
        socio_id = :socio_id,
        proveedor_id = :proveedor_id,
        folio = :folio,
        tipo = :tipo,
        fecha = :fecha,
        concepto = :concepto,
        subtotal = :subtotal,
        iva = :iva,
        total = :total,
        metodo_pago = :metodo_pago,
        estado_pago = :estado_pago,
        archivo = :archivo,
        observaciones = :observaciones
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([

    ':socio_id' => $socio_id,

    ':proveedor_id' => $proveedor_id,

    ':folio' => $folio,

    ':tipo' => $tipo,

    ':fecha' => $fecha,

    ':concepto' => $concepto,

    ':subtotal' => $subtotal,

    ':iva' => $iva,

    ':total' => $total,

    ':metodo_pago' => $metodo_pago,

    ':estado_pago' => $estado_pago,

    ':archivo' => $archivo,

    ':observaciones' => $observaciones,

    ':id' => $id,

    ':rancho_id' => $rancho_id

]);


/*
|--------------------------------------------------------------------------
| Éxito
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'La factura se actualizó correctamente.';


header('Location: index.php');

exit;
