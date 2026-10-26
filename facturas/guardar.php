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

$archivo = null;


/*
|--------------------------------------------------------------------------
| NULL
|--------------------------------------------------------------------------
*/

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
        'Completa todos los campos obligatorios.';

    header('Location: crear.php');
    exit;

}


if (
    !is_numeric($subtotal) ||
    (float)$subtotal < 0
) {

    $_SESSION['error'] =
        'El subtotal no es válido.';

    header('Location: crear.php');
    exit;

}


if (
    !is_numeric($iva) ||
    (float)$iva < 0
) {

    $_SESSION['error'] =
        'El IVA no es válido.';

    header('Location: crear.php');
    exit;

}


$subtotal = (float)$subtotal;

$iva = (float)$iva;

$total = $subtotal + $iva;


/*
|--------------------------------------------------------------------------
| Validar método de pago
|--------------------------------------------------------------------------
*/

$metodos_validos = [
    'EFECTIVO',
    'TRANSFERENCIA',
    'TARJETA',
    'CHEQUE',
    'OTRO'
];

if (
    $metodo_pago !== null &&
    !in_array(
        $metodo_pago,
        $metodos_validos,
        true
    )
) {

    $_SESSION['error'] =
        'El método de pago no es válido.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar estado
|--------------------------------------------------------------------------
*/

$estados_validos = [
    'PENDIENTE',
    'PAGADA',
    'CANCELADA'
];

if (
    !in_array(
        $estado_pago,
        $estados_validos,
        true
    )
) {

    $_SESSION['error'] =
        'El estado de pago no es válido.';

    header('Location: crear.php');
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
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':folio' => $folio
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe una factura con ese folio.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar socio
|--------------------------------------------------------------------------
*/

if ($socio_id !== null) {

    $sql = "
        SELECT id
        FROM socios
        WHERE id = :socio_id
          AND rancho_id = :rancho_id
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
| Verificar proveedor
|--------------------------------------------------------------------------
*/

if ($proveedor_id !== null) {

    $sql = "
        SELECT id
        FROM proveedores
        WHERE id = :proveedor_id
          AND rancho_id = :rancho_id
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':proveedor_id' => $proveedor_id,
        ':rancho_id' => $rancho_id
    ]);

    if (!$stmt->fetch()) {

        $_SESSION['error'] =
            'El proveedor seleccionado no es válido.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| Subir archivo
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
            'Ocurrió un error al subir el archivo.';

        header('Location: crear.php');
        exit;

    }


    /*
    | Tamaño máximo: 10 MB
    */

    if (
        $_FILES['archivo']['size'] >
        10 * 1024 * 1024
    ) {

        $_SESSION['error'] =
            'El archivo no puede superar los 10 MB.';

        header('Location: crear.php');
        exit;

    }


    /*
    | Extensión
    */

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

        header('Location: crear.php');
        exit;

    }


    /*
    | Carpeta
    */

    $carpeta = __DIR__
        . DIRECTORY_SEPARATOR
        . 'archivos';


    if (!is_dir($carpeta)) {

        if (
            !mkdir(
                $carpeta,
                0775,
                true
            )
        ) {

            $_SESSION['error'] =
                'No se pudo crear la carpeta de archivos.';

            header('Location: crear.php');
            exit;

        }

    }


    /*
    | Nombre único
    */

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


    $ruta_archivo =
        $carpeta .
        DIRECTORY_SEPARATOR .
        $nombre_archivo;


    /*
    | Guardar archivo
    */

    if (
        !move_uploaded_file(
            $_FILES['archivo']['tmp_name'],
            $ruta_archivo
        )
    ) {

        $_SESSION['error'] =
            'No se pudo guardar el archivo.';

        header('Location: crear.php');
        exit;

    }


    /*
    | Ruta para BD
    */

    $archivo =
        'archivos/' .
        $nombre_archivo;

}


/*
|--------------------------------------------------------------------------
| Insertar
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO facturas
    (
        rancho_id,
        usuario_id,
        socio_id,
        proveedor_id,
        folio,
        tipo,
        fecha,
        concepto,
        subtotal,
        iva,
        total,
        metodo_pago,
        estado_pago,
        archivo,
        observaciones
    )
    VALUES
    (
        :rancho_id,
        :usuario_id,
        :socio_id,
        :proveedor_id,
        :folio,
        :tipo,
        :fecha,
        :concepto,
        :subtotal,
        :iva,
        :total,
        :metodo_pago,
        :estado_pago,
        :archivo,
        :observaciones
    )
";


$stmt = $conexion->prepare($sql);


$stmt->execute([

    ':rancho_id' => $rancho_id,

    ':usuario_id' => $usuario_id,

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

    ':observaciones' => $observaciones

]);


/*
|--------------------------------------------------------------------------
| Éxito
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'La factura se registró correctamente.';


header('Location: index.php');

exit;
