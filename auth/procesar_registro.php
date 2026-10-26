<?php

session_start();

require_once '../config/database.php';

/*
|--------------------------------------------------------------------------
| Verificar método
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Recibir datos
|--------------------------------------------------------------------------
*/

$nombre_rancho    = trim($_POST['nombre_rancho'] ?? '');
$nombre           = trim($_POST['nombre'] ?? '');
$email            = trim($_POST['email'] ?? '');
$username         = trim($_POST['username'] ?? '');
$password         = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';


/*
|--------------------------------------------------------------------------
| Validaciones
|--------------------------------------------------------------------------
*/

if (
    empty($nombre_rancho) ||
    empty($nombre) ||
    empty($email) ||
    empty($username) ||
    empty($password) ||
    empty($password_confirm)
) {
    $_SESSION['error'] = 'Todos los campos son obligatorios.';
    header('Location: registro.php');
    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'El correo electrónico no es válido.';
    header('Location: registro.php');
    exit;
}


if (strlen($password) < 8) {
    $_SESSION['error'] = 'La contraseña debe tener al menos 8 caracteres.';
    header('Location: registro.php');
    exit;
}


if ($password !== $password_confirm) {
    $_SESSION['error'] = 'Las contraseñas no coinciden.';
    header('Location: registro.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Verificar usuario y correo
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM usuarios
    WHERE username = :username
       OR email = :email
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':username' => $username,
    ':email'    => $email
]);

$usuario_existente = $stmt->fetch();


if ($usuario_existente) {
    $_SESSION['error'] = 'El nombre de usuario o correo ya está registrado.';
    header('Location: registro.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| Crear contraseña segura
|--------------------------------------------------------------------------
*/

$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| Crear rancho y usuario (Rol asignado: 'admin' de rancho / 'usuario')
| Nota: 'superadmin' jamás se asigna desde el registro público.
|--------------------------------------------------------------------------
*/

try {

    $conexion->beginTransaction();


    /*
    | Crear rancho
    */

    $sql = "
        INSERT INTO ranchos (nombre)
        VALUES (:nombre)
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->execute([
        ':nombre' => $nombre_rancho
    ]);


    $rancho_id = $conexion->lastInsertId();


    /*
    | Crear usuario del rancho
    */

    $sql = "
        INSERT INTO usuarios
        (
            rancho_id,
            nombre,
            email,
            username,
            password,
            rol
        )
        VALUES
        (
            :rancho_id,
            :nombre,
            :email,
            :username,
            :password,
            :rol
        )
    ";

    $stmt = $conexion->prepare($sql);

    // Asignamos 'admin' (administrador de su rancho) o 'usuario'.
    // Los módulos globales de tu sistema revisarán que solo 'superadmin' pueda gestionar cuentas.
    $stmt->execute([
        ':rancho_id' => $rancho_id,
        ':nombre'    => $nombre,
        ':email'     => $email,
        ':username'  => $username,
        ':password'  => $password_hash,
        ':rol'       => 'admin'
    ]);


    /*
    | Confirmar operación
    */

    $conexion->commit();


    $_SESSION['success'] = 'Cuenta creada correctamente. Ya puedes iniciar sesión.';

    header('Location: ../index.php');
    exit;


} catch (PDOException $e) {

    /*
    | Cancelar operación si ocurre un error
    */

    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    $_SESSION['error'] = 'No fue posible crear la cuenta.';

    header('Location: registro.php');
    exit;
}