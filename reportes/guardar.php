<?php

session_start();

require_once '../config/database.php';
require_once '../vendor/autoload.php';
require_once 'generar_archivo.php';


/*
|--------------------------------------------------------------------------
| VERIFICAR SESIÓN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| VERIFICAR MÉTODO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| DATOS DE SESIÓN
|--------------------------------------------------------------------------
*/

$rancho_id = (int)($_SESSION['rancho_id'] ?? 0);
$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);


if ($rancho_id <= 0 || $usuario_id <= 0) {

    $_SESSION['error'] =
        'La sesión del usuario no es válida.';

    header('Location: ../index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| RECIBIR DATOS
|--------------------------------------------------------------------------
*/

$tipo_reporte =
    trim($_POST['tipo_reporte'] ?? '');

$titulo =
    trim($_POST['titulo'] ?? '');

$fecha_inicio =
    trim($_POST['fecha_inicio'] ?? '');

$fecha_fin =
    trim($_POST['fecha_fin'] ?? '');

$formato =
    strtoupper(trim($_POST['formato'] ?? 'PDF'));

$observaciones =
    trim($_POST['observaciones'] ?? '');


/*
|--------------------------------------------------------------------------
| TIPOS PERMITIDOS
|--------------------------------------------------------------------------
*/

$tipos_validos = [

    'GANADO',
    'MOVIMIENTOS',
    'SOCIOS',
    'GUIAS',
    'FACTURAS',
    'PROVEEDORES',
    'GENERAL',
    'OTRO'

];


/*
|--------------------------------------------------------------------------
| FORMATOS PERMITIDOS
|--------------------------------------------------------------------------
*/

$formatos_validos = [

    'PDF',
    'EXCEL',
    'WORD',
    'CSV'

];


/*
|--------------------------------------------------------------------------
| VALIDAR TIPO
|--------------------------------------------------------------------------
*/

if (!in_array(
    $tipo_reporte,
    $tipos_validos,
    true
)) {

    $_SESSION['error'] =
        'Tipo de reporte no válido.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDAR FORMATO
|--------------------------------------------------------------------------
*/

if (!in_array(
    $formato,
    $formatos_validos,
    true
)) {

    $_SESSION['error'] =
        'Formato de descarga no válido.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDAR TÍTULO
|--------------------------------------------------------------------------
*/

if ($titulo === '') {

    $_SESSION['error'] =
        'Debes escribir un título para el reporte.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDAR FECHA INICIO
|--------------------------------------------------------------------------
*/

if ($fecha_inicio !== '') {

    $fecha = DateTime::createFromFormat(
        'Y-m-d',
        $fecha_inicio
    );

    if (
        !$fecha ||
        $fecha->format('Y-m-d') !== $fecha_inicio
    ) {

        $_SESSION['error'] =
            'La fecha de inicio no es válida.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| VALIDAR FECHA FIN
|--------------------------------------------------------------------------
*/

if ($fecha_fin !== '') {

    $fecha = DateTime::createFromFormat(
        'Y-m-d',
        $fecha_fin
    );

    if (
        !$fecha ||
        $fecha->format('Y-m-d') !== $fecha_fin
    ) {

        $_SESSION['error'] =
            'La fecha de fin no es válida.';

        header('Location: crear.php');
        exit;

    }

}


/*
|--------------------------------------------------------------------------
| VALIDAR RANGO
|--------------------------------------------------------------------------
*/

if (
    $fecha_inicio !== '' &&
    $fecha_fin !== '' &&
    $fecha_inicio > $fecha_fin
) {

    $_SESSION['error'] =
        'La fecha de inicio no puede ser mayor que la fecha de fin.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| CONVERTIR FECHAS VACÍAS A NULL
|--------------------------------------------------------------------------
*/

$fecha_inicio =
    $fecha_inicio !== ''
        ? $fecha_inicio
        : null;

$fecha_fin =
    $fecha_fin !== ''
        ? $fecha_fin
        : null;


/*
|--------------------------------------------------------------------------
| GENERAR ARCHIVO
|--------------------------------------------------------------------------
*/

try {

    $resultado = generarArchivoReporte(

        $conexion,

        $rancho_id,

        $tipo_reporte,

        $titulo,

        $fecha_inicio,

        $fecha_fin,

        $formato,

        $observaciones

    );


    /*
    |--------------------------------------------------------------------------
    | VERIFICAR RESULTADO
    |--------------------------------------------------------------------------
    */

    if (
        !is_array($resultado) ||
        empty($resultado['archivoBD'])
    ) {

        throw new Exception(
            'La función generarArchivoReporte() no devolvió un archivo válido.'
        );

    }


    /*
    |--------------------------------------------------------------------------
    | GUARDAR REPORTE EN BD
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO reportes
        (
            rancho_id,
            usuario_id,
            tipo_reporte,
            titulo,
            fecha_inicio,
            fecha_fin,
            formato,
            archivo,
            observaciones,
            estado
        )
        VALUES
        (
            :rancho_id,
            :usuario_id,
            :tipo_reporte,
            :titulo,
            :fecha_inicio,
            :fecha_fin,
            :formato,
            :archivo,
            :observaciones,
            1
        )
    ";


    $stmt = $conexion->prepare($sql);


    $stmt->execute([

        ':rancho_id' =>
            $rancho_id,

        ':usuario_id' =>
            $usuario_id,

        ':tipo_reporte' =>
            $tipo_reporte,

        ':titulo' =>
            $titulo,

        ':fecha_inicio' =>
            $fecha_inicio,

        ':fecha_fin' =>
            $fecha_fin,

        ':formato' =>
            $formato,

        ':archivo' =>
            $resultado['archivoBD'],

        ':observaciones' =>
            $observaciones !== ''
                ? $observaciones
                : null

    ]);


    /*
    |--------------------------------------------------------------------------
    | ID DEL REPORTE
    |--------------------------------------------------------------------------
    */

    $reporte_id =
        (int)$conexion->lastInsertId();


    /*
    |--------------------------------------------------------------------------
    | GUARDAR MENSAJE
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        'Reporte generado correctamente.';


    /*
    |--------------------------------------------------------------------------
    | DESCARGA AUTOMÁTICA
    |--------------------------------------------------------------------------
    |
    | index.php recibirá este ID.
    | Después podremos hacer que descargue automáticamente
    | y finalmente permanezca en index.php.
    |
    */

    $_SESSION['descargar_reporte'] =
        $reporte_id;


    /*
    |--------------------------------------------------------------------------
    | REGRESAR A REPORTES
    |--------------------------------------------------------------------------
    */

    header('Location: index.php');
    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */

    $_SESSION['error'] =
        'Error al generar el reporte: ' .
        $e->getMessage();


    header('Location: crear.php');
    exit;

}
