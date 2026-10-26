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
| Obtener ganado activo
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        arete,
        nombre,
        sexo
    FROM ganado
    WHERE rancho_id = :rancho_id
      AND estado = 1
    ORDER BY arete ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$ganado = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Obtener socios activos
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido_paterno,
        apellido_materno,
        tipo
    FROM socios
    WHERE rancho_id = :rancho_id
      AND estado = 1
    ORDER BY nombre ASC, apellido_paterno ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$socios = $stmt->fetchAll();


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

    <title>
        Registrar movimiento | Sistema Ganadero
    </title>

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
                        Registrar movimiento
                    </h2>

                    <p class="text-muted mb-4">
                        Registra un movimiento relacionado con el ganado.
                    </p>


                    <!-- ERROR -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <!-- VALIDAR GANADO -->

                    <?php if (empty($ganado)): ?>

                        <div class="alert alert-warning">

                            No tienes ganado activo registrado.

                            <br><br>

                            Debes registrar un animal antes de registrar
                            un movimiento.

                            <br><br>

                            <a
                                href="../ganado/crear.php"
                                class="btn btn-warning"
                            >
                                Registrar ganado
                            </a>

                        </div>


                    <?php else: ?>


                    <form
                        action="guardar.php"
                        method="POST"
                    >


                        <div class="row">


                            <!-- GANADO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="ganado_id"
                                    class="form-label"
                                >
                                    Animal *
                                </label>

                                <select
                                    id="ganado_id"
                                    name="ganado_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Seleccionar animal
                                    </option>


                                    <?php foreach ($ganado as $animal): ?>

                                        <option
                                            value="<?= (int)$animal['id'] ?>"
                                        >

                                            Arete:
                                            <?= htmlspecialchars(
                                                $animal['arete']
                                            ) ?>

                                            <?php if ($animal['nombre']): ?>

                                                -
                                                <?= htmlspecialchars(
                                                    $animal['nombre']
                                                ) ?>

                                            <?php endif; ?>

                                            -
                                            <?= htmlspecialchars(
                                                $animal['sexo']
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- TIPO MOVIMIENTO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="tipo_movimiento"
                                    class="form-label"
                                >
                                    Tipo de movimiento *
                                </label>

                                <select
                                    id="tipo_movimiento"
                                    name="tipo_movimiento"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Seleccionar movimiento
                                    </option>

                                    <option value="ENTRADA">
                                        Entrada
                                    </option>

                                    <option value="SALIDA">
                                        Salida
                                    </option>

                                    <option value="COMPRA">
                                        Compra
                                    </option>

                                    <option value="VENTA">
                                        Venta
                                    </option>

                                    <option value="TRASLADO">
                                        Traslado
                                    </option>

                                    <option value="MUERTE">
                                        Muerte
                                    </option>

                                    <option value="BAJA">
                                        Baja
                                    </option>

                                    <option value="OTRO">
                                        Otro
                                    </option>

                                </select>

                            </div>


                            <!-- FECHA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_movimiento"
                                    class="form-label"
                                >
                                    Fecha del movimiento *
                                </label>

                                <input
                                    type="date"
                                    id="fecha_movimiento"
                                    name="fecha_movimiento"
                                    class="form-control"
                                    value="<?= date('Y-m-d') ?>"
                                    required
                                >

                            </div>


                            <!-- SOCIO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="socio_id"
                                    class="form-label"
                                >
                                    Socio / Cliente
                                </label>

                                <select
                                    id="socio_id"
                                    name="socio_id"
                                    class="form-select"
                                >

                                    <option value="">
                                        Sin socio / cliente
                                    </option>


                                    <?php foreach ($socios as $socio): ?>

                                        <option
                                            value="<?= (int)$socio['id'] ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $socio['nombre']
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $socio['apellido_paterno'] ?? ''
                                            ) ?>

                                            <?= htmlspecialchars(
                                                $socio['apellido_materno'] ?? ''
                                            ) ?>

                                            -
                                            <?= htmlspecialchars(
                                                $socio['tipo']
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- PESO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="peso"
                                    class="form-label"
                                >
                                    Peso (kg)
                                </label>

                                <input
                                    type="number"
                                    id="peso"
                                    name="peso"
                                    class="form-control"
                                    min="0"
                                    max="999999.99"
                                    step="0.01"
                                    placeholder="Ej. 450.50"
                                >

                            </div>


                            <!-- PRECIO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="precio"
                                    class="form-label"
                                >
                                    Precio
                                </label>

                                <input
                                    type="number"
                                    id="precio"
                                    name="precio"
                                    class="form-control"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    placeholder="Ej. 35000.00"
                                >

                            </div>


                            <!-- ORIGEN -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="origen"
                                    class="form-label"
                                >
                                    Origen
                                </label>

                                <input
                                    type="text"
                                    id="origen"
                                    name="origen"
                                    class="form-control"
                                    maxlength="200"
                                    placeholder="Ej. Rancho Los Pinos"
                                >

                            </div>


                            <!-- DESTINO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="destino"
                                    class="form-label"
                                >
                                    Destino
                                </label>

                                <input
                                    type="text"
                                    id="destino"
                                    name="destino"
                                    class="form-control"
                                    maxlength="200"
                                    placeholder="Ej. Coatzacoalcos"
                                >

                            </div>


                            <!-- MOTIVO -->

                            <div class="col-12 mb-3">

                                <label
                                    for="motivo"
                                    class="form-label"
                                >
                                    Motivo
                                </label>

                                <input
                                    type="text"
                                    id="motivo"
                                    name="motivo"
                                    class="form-control"
                                    maxlength="255"
                                    placeholder="Ej. Venta de animal"
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
                                    placeholder="Observaciones adicionales"
                                ></textarea>

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
                                Guardar movimiento
                            </button>

                        </div>


                    </form>


                    <?php endif; ?>


                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>
