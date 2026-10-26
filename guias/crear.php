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
| Obtener ganado activo
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        g.id,
        g.arete,
        g.nombre,
        g.sexo,
        g.peso_actual,
        r.nombre AS raza
    FROM ganado g

    LEFT JOIN razas r
        ON r.id = g.raza_id

    WHERE g.rancho_id = :rancho_id
      AND g.estado = 1

    ORDER BY g.arete ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$ganado = $stmt->fetchAll();


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

    <title>Registrar guía | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-lg-11">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-2">
                        Registrar guía
                    </h2>

                    <p class="text-muted mb-4">
                        Ingresa la información de la guía y selecciona los animales.
                    </p>


                    <!-- ERROR -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <?php if (empty($ganado)): ?>

                        <div class="alert alert-warning">

                            No tienes ganado activo disponible para registrar
                            en una guía.

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


                        <!--
                        |--------------------------------------------------------------------------
                        | DATOS DE LA GUÍA
                        |--------------------------------------------------------------------------
                        -->

                        <h5 class="mb-3">
                            Datos de la guía
                        </h5>


                        <div class="row">


                            <!-- NÚMERO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="numero_guia"
                                    class="form-label"
                                >
                                    Número de guía *
                                </label>

                                <input
                                    type="text"
                                    id="numero_guia"
                                    name="numero_guia"
                                    class="form-control"
                                    maxlength="100"
                                    placeholder="Ej. GUIA-0001"
                                    required
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

                                    <option value="MOVILIZACION">
                                        Movilización
                                    </option>

                                    <option value="TRASLADO">
                                        Traslado
                                    </option>

                                    <option value="VENTA">
                                        Venta
                                    </option>

                                    <option value="COMPRA">
                                        Compra
                                    </option>

                                    <option value="OTRA">
                                        Otra
                                    </option>

                                </select>

                            </div>


                            <!-- FECHA EMISIÓN -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_emision"
                                    class="form-label"
                                >
                                    Fecha de emisión *
                                </label>

                                <input
                                    type="date"
                                    id="fecha_emision"
                                    name="fecha_emision"
                                    class="form-control"
                                    value="<?= date('Y-m-d') ?>"
                                    required
                                >

                            </div>


                            <!-- FECHA VENCIMIENTO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_vencimiento"
                                    class="form-label"
                                >
                                    Fecha de vencimiento
                                </label>

                                <input
                                    type="date"
                                    id="fecha_vencimiento"
                                    name="fecha_vencimiento"
                                    class="form-control"
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
                                    placeholder="Lugar de origen"
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
                                    placeholder="Lugar de destino"
                                >

                            </div>


                            <!-- TRANSPORTISTA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="transportista"
                                    class="form-label"
                                >
                                    Transportista
                                </label>

                                <input
                                    type="text"
                                    id="transportista"
                                    name="transportista"
                                    class="form-control"
                                    maxlength="150"
                                    placeholder="Nombre del transportista"
                                >

                            </div>


                            <!-- PLACAS -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="placas"
                                    class="form-label"
                                >
                                    Placas
                                </label>

                                <input
                                    type="text"
                                    id="placas"
                                    name="placas"
                                    class="form-control"
                                    maxlength="30"
                                    placeholder="Ej. XYZ-123-A"
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
                                        Sin socio
                                    </option>

                                    <?php foreach ($socios as $socio): ?>

                                        <?php

                                        $nombre_completo = trim(
                                            $socio['nombre'] . ' ' .
                                            ($socio['apellido_paterno'] ?? '') . ' ' .
                                            ($socio['apellido_materno'] ?? '')
                                        );

                                        ?>

                                        <option
                                            value="<?= (int)$socio['id'] ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $nombre_completo
                                            ) ?>

                                            -

                                            <?= htmlspecialchars(
                                                $socio['tipo']
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- OBSERVACIONES -->

                            <div class="col-12 mb-4">

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
                                    rows="3"
                                    placeholder="Observaciones de la guía"
                                ></textarea>

                            </div>

                        </div>


                        <hr>


                        <!--
                        |--------------------------------------------------------------------------
                        | SELECCIÓN DE GANADO
                        |--------------------------------------------------------------------------
                        -->

                        <div class="d-flex justify-content-between align-items-center mb-3">

                            <div>

                                <h5 class="mb-1">
                                    Animales de la guía
                                </h5>

                                <p class="text-muted mb-0">
                                    Selecciona los animales que serán incluidos.
                                </p>

                            </div>


                            <div>

                                <strong>
                                    Seleccionados:
                                    <span id="contador">
                                        0
                                    </span>
                                </strong>

                            </div>

                        </div>


                        <!-- BUSCADOR -->

                        <div class="mb-3">

                            <input
                                type="text"
                                id="buscarGanado"
                                class="form-control"
                                placeholder="Buscar por arete, nombre o raza..."
                            >

                        </div>


                        <!-- TABLA GANADO -->

                        <div class="table-responsive">

                            <table
                                class="table table-bordered table-hover"
                                id="tablaGanado"
                            >

                                <thead>

                                    <tr>

                                        <th>
                                            Seleccionar
                                        </th>

                                        <th>
                                            Arete
                                        </th>

                                        <th>
                                            Nombre
                                        </th>

                                        <th>
                                            Sexo
                                        </th>

                                        <th>
                                            Raza
                                        </th>

                                        <th>
                                            Peso
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php foreach ($ganado as $animal): ?>

                                    <tr class="fila-ganado">


                                        <!-- CHECKBOX -->

                                        <td>

                                            <input
                                                type="checkbox"
                                                name="ganado_ids[]"
                                                value="<?= (int)$animal['id'] ?>"
                                                class="form-check-input animal-checkbox"
                                            >

                                        </td>


                                        <!-- ARETE -->

                                        <td>

                                            <strong>
                                                <?= htmlspecialchars(
                                                    $animal['arete']
                                                ) ?>
                                            </strong>

                                        </td>


                                        <!-- NOMBRE -->

                                        <td>

                                            <?= $animal['nombre']
                                                ? htmlspecialchars(
                                                    $animal['nombre']
                                                )
                                                : '<span class="text-muted">
                                                    Sin nombre
                                                   </span>'
                                            ?>

                                        </td>


                                        <!-- SEXO -->

                                        <td>

                                            <?= htmlspecialchars(
                                                $animal['sexo']
                                            ) ?>

                                        </td>


                                        <!-- RAZA -->

                                        <td>

                                            <?= $animal['raza']
                                                ? htmlspecialchars(
                                                    $animal['raza']
                                                )
                                                : '<span class="text-muted">
                                                    Sin raza
                                                   </span>'
                                            ?>

                                        </td>


                                        <!-- PESO -->

                                        <td>

                                            <?php if (
                                                $animal['peso_actual'] !== null
                                            ): ?>

                                                <?= htmlspecialchars(
                                                    $animal['peso_actual']
                                                ) ?>

                                                kg

                                            <?php else: ?>

                                                <span class="text-muted">
                                                    No registrado
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>


                        <!-- BOTONES -->

                        <div class="d-flex gap-2 mt-4">

                            <a
                                href="index.php"
                                class="btn btn-secondary"
                            >
                                Cancelar
                            </a>


                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="btnGuardar"
                            >
                                Guardar guía
                            </button>

                        </div>


                    </form>

                    <?php endif; ?>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

/*
|--------------------------------------------------------------------------
| Contador de animales
|--------------------------------------------------------------------------
*/

const checkboxes = document.querySelectorAll(
    '.animal-checkbox'
);

const contador = document.getElementById(
    'contador'
);


function actualizarContador() {

    const seleccionados =
        document.querySelectorAll(
            '.animal-checkbox:checked'
        ).length;

    contador.textContent =
        seleccionados;

}


checkboxes.forEach(function(checkbox) {

    checkbox.addEventListener(
        'change',
        actualizarContador
    );

});


/*
|--------------------------------------------------------------------------
| Buscador de ganado
|--------------------------------------------------------------------------
*/

const buscador =
    document.getElementById(
        'buscarGanado'
    );


buscador.addEventListener(
    'keyup',
    function() {

        const texto =
            this.value.toLowerCase();

        const filas =
            document.querySelectorAll(
                '.fila-ganado'
            );


        filas.forEach(function(fila) {

            const contenido =
                fila.textContent.toLowerCase();

            if (contenido.includes(texto)) {

                fila.style.display = '';

            } else {

                fila.style.display = 'none';

            }

        });

    }
);

</script>

</body>

</html>
