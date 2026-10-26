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
| Obtener rancho
|--------------------------------------------------------------------------
*/

$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Obtener ID del socio
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


/*
|--------------------------------------------------------------------------
| Verificar ID
|--------------------------------------------------------------------------
*/

if (!$id) {

    $_SESSION['error'] =
        'Socio no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener socio
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido_paterno,
        apellido_materno,
        telefono,
        email,
        direccion,
        rfc,
        curp,
        tipo,
        estado
    FROM socios
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$socio = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verificar socio
|--------------------------------------------------------------------------
*/

if (!$socio) {

    $_SESSION['error'] =
        'El socio no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Mensaje de error
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? null;

unset($_SESSION['error']);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar socio | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-lg-9">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-2">
                        Editar socio
                    </h2>

                    <p class="text-muted mb-4">
                        Modifica la información del socio.
                    </p>


                    <!-- ERROR -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <!-- FORMULARIO -->

                    <form
                        action="actualizar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$socio['id'] ?>"
                        >


                        <div class="row">


                            <!-- NOMBRE -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="nombre"
                                    class="form-label"
                                >
                                    Nombre *
                                </label>

                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($socio['nombre']) ?>"
                                    required
                                >

                            </div>


                            <!-- APELLIDO PATERNO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="apellido_paterno"
                                    class="form-label"
                                >
                                    Apellido paterno
                                </label>

                                <input
                                    type="text"
                                    id="apellido_paterno"
                                    name="apellido_paterno"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($socio['apellido_paterno'] ?? '') ?>"
                                >

                            </div>


                            <!-- APELLIDO MATERNO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="apellido_materno"
                                    class="form-label"
                                >
                                    Apellido materno
                                </label>

                                <input
                                    type="text"
                                    id="apellido_materno"
                                    name="apellido_materno"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($socio['apellido_materno'] ?? '') ?>"
                                >

                            </div>


                            <!-- TELÉFONO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="telefono"
                                    class="form-label"
                                >
                                    Teléfono
                                </label>

                                <input
                                    type="text"
                                    id="telefono"
                                    name="telefono"
                                    class="form-control"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($socio['telefono'] ?? '') ?>"
                                >

                            </div>


                            <!-- EMAIL -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="email"
                                    class="form-label"
                                >
                                    Correo electrónico
                                </label>

                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($socio['email'] ?? '') ?>"
                                >

                            </div>


                            <!-- RFC -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="rfc"
                                    class="form-label"
                                >
                                    RFC
                                </label>

                                <input
                                    type="text"
                                    id="rfc"
                                    name="rfc"
                                    class="form-control"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($socio['rfc'] ?? '') ?>"
                                >

                            </div>


                            <!-- CURP -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="curp"
                                    class="form-label"
                                >
                                    CURP
                                </label>

                                <input
                                    type="text"
                                    id="curp"
                                    name="curp"
                                    class="form-control"
                                    maxlength="20"
                                    value="<?= htmlspecialchars($socio['curp'] ?? '') ?>"
                                >

                            </div>


                            <!-- TIPO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="tipo"
                                    class="form-label"
                                >
                                    Tipo *
                                </label>

                                <select
                                    id="tipo"
                                    name="tipo"
                                    class="form-select"
                                    required
                                >

                                    <option
                                        value="SOCIO"
                                        <?= $socio['tipo'] === 'SOCIO' ? 'selected' : '' ?>
                                    >
                                        Socio
                                    </option>

                                    <option
                                        value="CLIENTE"
                                        <?= $socio['tipo'] === 'CLIENTE' ? 'selected' : '' ?>
                                    >
                                        Cliente
                                    </option>

                                    <option
                                        value="COMPRADOR"
                                        <?= $socio['tipo'] === 'COMPRADOR' ? 'selected' : '' ?>
                                    >
                                        Comprador
                                    </option>

                                </select>

                            </div>


                            <!-- DIRECCIÓN -->

                            <div class="col-12 mb-3">

                                <label
                                    for="direccion"
                                    class="form-label"
                                >
                                    Dirección
                                </label>

                                <textarea
                                    id="direccion"
                                    name="direccion"
                                    class="form-control"
                                    rows="3"
                                    maxlength="255"
                                ><?= htmlspecialchars($socio['direccion'] ?? '') ?></textarea>

                            </div>


                        </div>


                        <!-- BOTONES -->

                        <div class="d-flex gap-2 mt-3">

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                Cancelar
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Guardar cambios
                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
