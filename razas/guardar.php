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
| Datos
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];

$nombre = trim($_POST['nombre'] ?? '');

$descripcion = trim($_POST['descripcion'] ?? '');


/*
|--------------------------------------------------------------------------
| Validar nombre
|--------------------------------------------------------------------------
*/

if ($nombre === '') {

    $_SESSION['error'] =
        'El nombre de la raza es obligatorio.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que no exista la raza
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM razas
    WHERE rancho_id = :rancho_id
      AND LOWER(nombre) = LOWER(:nombre)
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':nombre' => $nombre
]);

$raza_existente = $stmt->fetch();


if ($raza_existente) {

    $_SESSION['error'] =
        'Esta raza ya está registrada en tu rancho.';

    header('Location: crear.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Insertar raza
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO razas
    (
        rancho_id,
        nombre,
        descripcion,
        estado
    )
    VALUES
    (
        :rancho_id,
        :nombre,
        :descripcion,
        1
    )
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':nombre' => $nombre,
    ':descripcion' => $descripcion !== ''
        ? $descripcion
        : null
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'La raza se registró correctamente.';


header('Location: index.php');

exit;
