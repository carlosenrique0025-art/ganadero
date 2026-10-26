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
| Mensaje
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

    <title>Registrar socio | Sistema Ganadero</title>

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
                        Registrar socio
                    </h2>

                    <p class="text-muted mb-4">
                        Ingresa la información del socio, cliente o comprador.
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="guardar.php"
                        method="POST"
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

                                    <option value="">
                                        Seleccionar tipo
                                    </option>

                                    <option value="SOCIO">
                                        Socio
                                    </option>

                                    <option value="CLIENTE">
                                        Cliente
                                    </option>

                                    <option value="COMPRADOR">
                                        Comprador
                                    </option>

                                </select>

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
                                    type="tel"
                                    id="telefono"
                                    name="telefono"
                                    class="form-control"
                                    maxlength="20"
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
                                    style="text-transform: uppercase;"
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
                                    maxlength="18"
                                    style="text-transform: uppercase;"
                                >

                            </div>


                            <!-- DIRECCIÓN -->

                            <div class="col-12 mb-3">

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
                                >

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
                                Guardar socio
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
