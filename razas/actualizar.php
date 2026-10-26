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
| Recibir datos
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$nombre = trim($_POST['nombre'] ?? '');

$descripcion = trim(
    $_POST['descripcion'] ?? ''
);

$estado = isset($_POST['estado'])
    ? (int)$_POST['estado']
    : 1;

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Validar ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    $_SESSION['error'] =
        'Raza no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar nombre
|--------------------------------------------------------------------------
*/

if ($nombre === '') {

    $_SESSION['error'] =
        'El nombre de la raza es obligatorio.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Validar estado
|--------------------------------------------------------------------------
*/

if ($estado !== 0 && $estado !== 1) {

    $_SESSION['error'] =
        'Estado no válido.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar que la raza pertenece al rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM razas
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
        'La raza no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Verificar nombre duplicado
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM razas
    WHERE rancho_id = :rancho_id
      AND LOWER(nombre) = LOWER(:nombre)
      AND id != :id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id,
    ':nombre' => $nombre,
    ':id' => $id
]);

if ($stmt->fetch()) {

    $_SESSION['error'] =
        'Ya existe otra raza con ese nombre en tu rancho.';

    header('Location: editar.php?id=' . $id);
    exit;

}


/*
|--------------------------------------------------------------------------
| Actualizar
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE razas
    SET
        nombre = :nombre,
        descripcion = :descripcion,
        estado = :estado
    WHERE id = :id
      AND rancho_id = :rancho_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':nombre' => $nombre,
    ':descripcion' => $descripcion !== ''
        ? $descripcion
        : null,
    ':estado' => $estado,
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);


/*
|--------------------------------------------------------------------------
| Mensaje
|--------------------------------------------------------------------------
*/

$_SESSION['success'] =
    'La raza se actualizó correctamente.';

header('Location: index.php');

exit;
