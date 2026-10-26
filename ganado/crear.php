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

    <title>Registrar ganado | Sistema Ganadero</title>

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
                        Registrar ganado
                    </h2>

                    <p class="text-muted mb-4">
                        Ingresa la información del animal.
                    </p>


                    <!-- ERROR -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <?php if (empty($razas)): ?>

                        <div class="alert alert-warning">

                            No tienes razas activas registradas.

                            <br>

                            Debes registrar una raza antes de registrar ganado.

                            <br><br>

                            <a
                                href="../razas/crear.php"
                                class="btn btn-warning"
                            >
                                Registrar raza
                            </a>

                        </div>

                    <?php else: ?>


                    <form
                        action="guardar.php"
                        method="POST"
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
                                    placeholder="Ej. 00125"
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
                                    placeholder="Ej. Lucero"
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

                                    <option value="MACHO">
                                        Macho
                                    </option>

                                    <option value="HEMBRA">
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
                                    placeholder="Ej. 450.50"
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
                                    placeholder="Ej. Blanco con negro"
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
                                    placeholder="Ej. Rancho vecino"
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
                                    value="<?= date('Y-m-d') ?>"
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
                                    placeholder="Observaciones adicionales del animal"
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
                                Guardar ganado
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
