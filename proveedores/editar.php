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
| Obtener ID del proveedor
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    $_SESSION['error'] =
        'Proveedor no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener proveedor
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        empresa,
        rfc,
        telefono,
        email,
        direccion,
        ciudad,
        estado_direccion,
        productos_servicios,
        observaciones,
        estado
    FROM proveedores
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$proveedor = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verificar proveedor
|--------------------------------------------------------------------------
*/

if (!$proveedor) {

    $_SESSION['error'] =
        'El proveedor no existe o no pertenece a tu rancho.';

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

    <title>Editar proveedor | Sistema Ganadero</title>

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
                        Editar proveedor
                    </h2>

                    <p class="text-muted mb-4">
                        Modifica la información del proveedor.
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
                            value="<?= (int)$proveedor['id'] ?>"
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
                                    value="<?= htmlspecialchars($proveedor['nombre']) ?>"
                                    required
                                >

                            </div>


                            <!-- EMPRESA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="empresa"
                                    class="form-label"
                                >
                                    Empresa
                                </label>

                                <input
                                    type="text"
                                    id="empresa"
                                    name="empresa"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($proveedor['empresa'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($proveedor['rfc'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($proveedor['telefono'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($proveedor['email'] ?? '') ?>"
                                >

                            </div>


                            <!-- CIUDAD -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="ciudad"
                                    class="form-label"
                                >
                                    Ciudad
                                </label>

                                <input
                                    type="text"
                                    id="ciudad"
                                    name="ciudad"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($proveedor['ciudad'] ?? '') ?>"
                                >

                            </div>


                            <!-- ESTADO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="estado_direccion"
                                    class="form-label"
                                >
                                    Estado
                                </label>

                                <input
                                    type="text"
                                    id="estado_direccion"
                                    name="estado_direccion"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($proveedor['estado_direccion'] ?? '') ?>"
                                >

                            </div>


                            <!-- DIRECCIÓN -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="direccion"
                                    class="form-label"
                                >
                                    Dirección
                                </label>

                                <input
                                    type="text"
                                    id="direccion"
                                    name="direccion"
                                    class="form-control"
                                    maxlength="255"
                                    value="<?= htmlspecialchars($proveedor['direccion'] ?? '') ?>"
                                >

                            </div>


                            <!-- PRODUCTOS / SERVICIOS -->

                            <div class="col-12 mb-3">

                                <label
                                    for="productos_servicios"
                                    class="form-label"
                                >
                                    Productos / Servicios
                                </label>

                                <input
                                    type="text"
                                    id="productos_servicios"
                                    name="productos_servicios"
                                    class="form-control"
                                    maxlength="255"
                                    value="<?= htmlspecialchars($proveedor['productos_servicios'] ?? '') ?>"
                                >

                            </div>


                            <!-- OBSERVACIONES -->

                            <div class="col-12 mb-3">

                                <label
                                    for="observaciones"
                                    class="form-label"
                                >
                                    Observaciones
                                </label>

                                <textarea
                                    id="observaciones"
                                    name="observaciones"
                                    class="form-control"
                                    rows="4"
                                ><?= htmlspecialchars($proveedor['observaciones'] ?? '') ?></textarea>

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
