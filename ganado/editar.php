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


$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Obtener ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    $_SESSION['error'] =
        'Animal no válido.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener ganado
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        raza_id,
        arete,
        nombre,
        sexo,
        fecha_nacimiento,
        peso_actual,
        color,
        procedencia,
        fecha_ingreso,
        estado,
        observaciones
    FROM ganado
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$animal = $stmt->fetch();


if (!$animal) {

    $_SESSION['error'] =
        'El animal no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener razas activas
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre
    FROM razas
    WHERE rancho_id = :rancho_id
      AND estado = 1
    ORDER BY nombre ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$razas = $stmt->fetchAll();


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

    <title>Editar ganado | Sistema Ganadero</title>

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
                        Editar ganado
                    </h2>

                    <p class="text-muted mb-4">
                        Modifica la información del animal.
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="actualizar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$animal['id'] ?>"
                        >


                        <div class="row">


                            <!-- ARETE -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="arete"
                                    class="form-label"
                                >
                                    Número de arete *
                                </label>

                                <input
                                    type="text"
                                    id="arete"
                                    name="arete"
                                    class="form-control"
                                    maxlength="50"
                                    value="<?= htmlspecialchars($animal['arete']) ?>"
                                    required
                                >

                            </div>


                            <!-- NOMBRE -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="nombre"
                                    class="form-label"
                                >
                                    Nombre
                                </label>

                                <input
                                    type="text"
                                    id="nombre"
                                    name="nombre"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($animal['nombre'] ?? '') ?>"
                                >

                            </div>


                            <!-- RAZA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="raza_id"
                                    class="form-label"
                                >
                                    Raza *
                                </label>

                                <select
                                    id="raza_id"
                                    name="raza_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Seleccionar raza
                                    </option>

                                    <?php foreach ($razas as $raza): ?>

                                        <option
                                            value="<?= (int)$raza['id'] ?>"
                                            <?= (int)$animal['raza_id'] === (int)$raza['id']
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >
                                            <?= htmlspecialchars($raza['nombre']) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- SEXO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="sexo"
                                    class="form-label"
                                >
                                    Sexo *
                                </label>

                                <select
                                    id="sexo"
                                    name="sexo"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Seleccionar sexo
                                    </option>

                                    <option
                                        value="MACHO"
                                        <?= $animal['sexo'] === 'MACHO'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Macho
                                    </option>

                                    <option
                                        value="HEMBRA"
                                        <?= $animal['sexo'] === 'HEMBRA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Hembra
                                    </option>

                                </select>

                            </div>


                            <!-- FECHA NACIMIENTO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_nacimiento"
                                    class="form-label"
                                >
                                    Fecha de nacimiento
                                </label>

                                <input
                                    type="date"
                                    id="fecha_nacimiento"
                                    name="fecha_nacimiento"
                                    class="form-control"
                                    value="<?= htmlspecialchars($animal['fecha_nacimiento'] ?? '') ?>"
                                >

                            </div>


                            <!-- PESO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="peso_actual"
                                    class="form-label"
                                >
                                    Peso actual (kg)
                                </label>

                                <input
                                    type="number"
                                    id="peso_actual"
                                    name="peso_actual"
                                    class="form-control"
                                    min="0"
                                    max="999999.99"
                                    step="0.01"
                                    value="<?= htmlspecialchars($animal['peso_actual'] ?? '') ?>"
                                >

                            </div>


                            <!-- COLOR -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="color"
                                    class="form-label"
                                >
                                    Color
                                </label>

                                <input
                                    type="text"
                                    id="color"
                                    name="color"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars($animal['color'] ?? '') ?>"
                                >

                            </div>


                            <!-- PROCEDENCIA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="procedencia"
                                    class="form-label"
                                >
                                    Procedencia
                                </label>

                                <input
                                    type="text"
                                    id="procedencia"
                                    name="procedencia"
                                    class="form-control"
                                    maxlength="150"
                                    value="<?= htmlspecialchars($animal['procedencia'] ?? '') ?>"
                                >

                            </div>


                            <!-- FECHA INGRESO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_ingreso"
                                    class="form-label"
                                >
                                    Fecha de ingreso *
                                </label>

                                <input
                                    type="date"
                                    id="fecha_ingreso"
                                    name="fecha_ingreso"
                                    class="form-control"
                                    value="<?= htmlspecialchars($animal['fecha_ingreso']) ?>"
                                    required
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
                                ><?= htmlspecialchars($animal['observaciones'] ?? '') ?></textarea>

                            </div>

                        </div>


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
