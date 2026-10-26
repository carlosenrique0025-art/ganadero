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
| Obtener ID de la guía
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);


if (!$id) {

    $_SESSION['error'] =
        'Guía no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener guía
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        rancho_id,
        socio_id,
        usuario_id,
        numero_guia,
        tipo,
        fecha_emision,
        fecha_vencimiento,
        origen,
        destino,
        transportista,
        placas,
        cantidad_animales,
        observaciones,
        estado
    FROM guias
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$guia = $stmt->fetch();


if (!$guia) {

    $_SESSION['error'] =
        'La guía no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}


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
| Obtener ganado activo del rancho
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        g.id,
        g.arete,
        g.nombre,
        g.sexo,
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
| Obtener animales actualmente asociados a la guía
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT ganado_id
    FROM guia_ganado
    WHERE guia_id = :guia_id
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':guia_id' => $id
]);

$animales_guia = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| Crear arreglo de IDs seleccionados
|--------------------------------------------------------------------------
*/

$ganado_seleccionado = [];

foreach ($animales_guia as $animal) {

    $ganado_seleccionado[] =
        (int)$animal['ganado_id'];

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

    <title>Editar guía | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-lg-10">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-2">
                        Editar guía
                    </h2>

                    <p class="text-muted mb-4">
                        Modifica la información de la guía y los animales asociados.
                    </p>


                    <!-- MENSAJE DE ERROR -->

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="actualizar.php"
                        method="POST"
                    >

                        <!-- ID DE LA GUÍA -->

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$guia['id'] ?>"
                        >


                        <div class="row">


                            <!-- NÚMERO DE GUÍA -->

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
                                    value="<?= htmlspecialchars($guia['numero_guia']) ?>"
                                    required
                                >

                            </div>


                            <!-- TIPO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="tipo"
                                    class="form-label"
                                >
                                    Tipo de guía *
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

                                    <option
                                        value="MOVILIZACION"
                                        <?= $guia['tipo'] === 'MOVILIZACION'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Movilización
                                    </option>

                                    <option
                                        value="TRASLADO"
                                        <?= $guia['tipo'] === 'TRASLADO'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Traslado
                                    </option>

                                    <option
                                        value="VENTA"
                                        <?= $guia['tipo'] === 'VENTA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Venta
                                    </option>

                                    <option
                                        value="COMPRA"
                                        <?= $guia['tipo'] === 'COMPRA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Compra
                                    </option>

                                    <option
                                        value="OTRA"
                                        <?= $guia['tipo'] === 'OTRA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Otra
                                    </option>

                                </select>

                            </div>


                            <!-- SOCIO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="socio_id"
                                    class="form-label"
                                >
                                    Socio / cliente
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

                                        $nombre_completo =
                                            $socio['nombre'];

                                        if (
                                            !empty(
                                                $socio['apellido_paterno']
                                            )
                                        ) {

                                            $nombre_completo .=
                                                ' ' .
                                                $socio['apellido_paterno'];

                                        }

                                        if (
                                            !empty(
                                                $socio['apellido_materno']
                                            )
                                        ) {

                                            $nombre_completo .=
                                                ' ' .
                                                $socio['apellido_materno'];

                                        }

                                        ?>

                                        <option
                                            value="<?= (int)$socio['id'] ?>"
                                            <?= (int)$guia['socio_id'] === (int)$socio['id']
                                                ? 'selected'
                                                : ''
                                            ?>
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


                            <!-- FECHA EMISIÓN -->

                            <div class="col-md-3 mb-3">

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
                                    value="<?= htmlspecialchars($guia['fecha_emision']) ?>"
                                    required
                                >

                            </div>


                            <!-- FECHA VENCIMIENTO -->

                            <div class="col-md-3 mb-3">

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
                                    value="<?= htmlspecialchars($guia['fecha_vencimiento'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($guia['origen'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($guia['destino'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($guia['transportista'] ?? '') ?>"
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
                                    value="<?= htmlspecialchars($guia['placas'] ?? '') ?>"
                                    placeholder="Ej. ABC-123"
                                >

                            </div>


                            <!-- ANIMALES -->

                            <div class="col-12 mb-4">

                                <label class="form-label">

                                    Animales de la guía *

                                </label>

                                <div class="card">

                                    <div class="card-body">

                                        <?php if (empty($ganado)): ?>

                                            <div class="alert alert-warning mb-0">

                                                No hay ganado activo disponible
                                                para seleccionar.

                                            </div>

                                        <?php else: ?>

                                            <div
                                                class="table-responsive"
                                                style="max-height: 400px; overflow-y: auto;"
                                            >

                                                <table
                                                    class="table table-bordered table-hover"
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

                                                        </tr>

                                                    </thead>


                                                    <tbody>

                                                    <?php foreach ($ganado as $animal): ?>

                                                        <tr>

                                                            <td>

                                                                <input
                                                                    type="checkbox"
                                                                    name="ganado_ids[]"
                                                                    value="<?= (int)$animal['id'] ?>"
                                                                    <?= in_array(
                                                                        (int)$animal['id'],
                                                                        $ganado_seleccionado,
                                                                        true
                                                                    )
                                                                        ? 'checked'
                                                                        : ''
                                                                    ?>
                                                                >

                                                            </td>


                                                            <td>

                                                                <strong>
                                                                    <?= htmlspecialchars(
                                                                        $animal['arete']
                                                                    ) ?>
                                                                </strong>

                                                            </td>


                                                            <td>

                                                                <?= !empty($animal['nombre'])
                                                                    ? htmlspecialchars(
                                                                        $animal['nombre']
                                                                    )
                                                                    : '<span class="text-muted">Sin nombre</span>'
                                                                ?>

                                                            </td>


                                                            <td>

                                                                <?= htmlspecialchars(
                                                                    $animal['sexo']
                                                                ) ?>

                                                            </td>


                                                            <td>

                                                                <?= !empty($animal['raza'])
                                                                    ? htmlspecialchars(
                                                                        $animal['raza']
                                                                    )
                                                                    : '<span class="text-muted">Sin raza</span>'
                                                                ?>

                                                            </td>

                                                        </tr>

                                                    <?php endforeach; ?>

                                                    </tbody>

                                                </table>

                                            </div>

                                            <p class="text-muted mt-2 mb-0">

                                                Marca los animales que deben
                                                pertenecer a esta guía.

                                            </p>

                                        <?php endif; ?>

                                    </div>

                                </div>

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
                                    placeholder="Observaciones de la guía"
                                ><?= htmlspecialchars($guia['observaciones'] ?? '') ?></textarea>

                            </div>


                            <!-- ESTADO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="estado"
                                    class="form-label"
                                >
                                    Estado
                                </label>

                                <select
                                    id="estado"
                                    name="estado"
                                    class="form-select"
                                >

                                    <option
                                        value="ACTIVA"
                                        <?= $guia['estado'] === 'ACTIVA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Activa
                                    </option>

                                    <option
                                        value="UTILIZADA"
                                        <?= $guia['estado'] === 'UTILIZADA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Utilizada
                                    </option>

                                    <option
                                        value="VENCIDA"
                                        <?= $guia['estado'] === 'VENCIDA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Vencida
                                    </option>

                                    <option
                                        value="CANCELADA"
                                        <?= $guia['estado'] === 'CANCELADA'
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        Cancelada
                                    </option>

                                </select>

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
