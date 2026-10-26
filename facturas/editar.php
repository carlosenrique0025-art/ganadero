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
        'Factura no válida.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Obtener factura
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT *
    FROM facturas
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$factura = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$factura) {

    $_SESSION['error'] =
        'La factura no existe.';

    header('Location: index.php');
    exit;

}


/*
|--------------------------------------------------------------------------
| Socios
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido_paterno,
        apellido_materno
    FROM socios
    WHERE rancho_id = :rancho_id
      AND estado = 1
    ORDER BY nombre ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$socios = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Proveedores
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        empresa
    FROM proveedores
    WHERE rancho_id = :rancho_id
      AND estado = 1
    ORDER BY nombre ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar factura | Sistema Ganadero</title>

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

                    <h2>
                        Editar factura
                    </h2>

                    <p class="text-muted">
                        Modifica la información de la factura.
                    </p>


                    <form
                        action="actualizar.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$factura['id'] ?>"
                        >


                        <div class="row">


                            <!-- FOLIO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="folio"
                                    class="form-label"
                                >
                                    Folio *
                                </label>

                                <input
                                    type="text"
                                    id="folio"
                                    name="folio"
                                    class="form-control"
                                    maxlength="100"
                                    value="<?= htmlspecialchars(
                                        $factura['folio']
                                    ) ?>"
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

                                    <option
                                        value="INGRESO"
                                        <?= $factura['tipo'] === 'INGRESO'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Ingreso
                                    </option>

                                    <option
                                        value="EGRESO"
                                        <?= $factura['tipo'] === 'EGRESO'
                                            ? 'selected'
                                            : '' ?>
                                    >
                                        Egreso
                                    </option>

                                </select>

                            </div>


                            <!-- FECHA -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha"
                                    class="form-label"
                                >
                                    Fecha *
                                </label>

                                <input
                                    type="date"
                                    id="fecha"
                                    name="fecha"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $factura['fecha']
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- SOCIO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="socio_id"
                                    class="form-label"
                                >
                                    Socio
                                </label>

                                <select
                                    id="socio_id"
                                    name="socio_id"
                                    class="form-select"
                                >

                                    <option value="">
                                        Sin socio
                                    </option>

                                    <?php foreach (
                                        $socios as $socio
                                    ): ?>

                                        <option
                                            value="<?= (int)$socio['id'] ?>"
                                            <?= (
                                                (int)$factura['socio_id']
                                                ===
                                                (int)$socio['id']
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            <?= htmlspecialchars(
                                                trim(
                                                    $socio['nombre']
                                                    . ' '
                                                    . (
                                                        $socio[
                                                            'apellido_paterno'
                                                        ] ?? ''
                                                    )
                                                    . ' '
                                                    . (
                                                        $socio[
                                                            'apellido_materno'
                                                        ] ?? ''
                                                    )
                                                )
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- PROVEEDOR -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="proveedor_id"
                                    class="form-label"
                                >
                                    Proveedor
                                </label>

                                <select
                                    id="proveedor_id"
                                    name="proveedor_id"
                                    class="form-select"
                                >

                                    <option value="">
                                        Sin proveedor
                                    </option>

                                    <?php foreach (
                                        $proveedores as $proveedor
                                    ): ?>

                                        <option
                                            value="<?= (int)$proveedor['id'] ?>"
                                            <?= (
                                                (int)$factura['proveedor_id']
                                                ===
                                                (int)$proveedor['id']
                                            )
                                                ? 'selected'
                                                : '' ?>
                                        >

                                            <?= htmlspecialchars(
                                                $proveedor['empresa']
                                                ?: $proveedor['nombre']
                                            ) ?>

                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- CONCEPTO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="concepto"
                                    class="form-label"
                                >
                                    Concepto *
                                </label>

                                <input
                                    type="text"
                                    id="concepto"
                                    name="concepto"
                                    class="form-control"
                                    maxlength="255"
                                    value="<?= htmlspecialchars(
                                        $factura['concepto']
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- SUBTOTAL -->

                            <div class="col-md-4 mb-3">

                                <label
                                    for="subtotal"
                                    class="form-label"
                                >
                                    Subtotal *
                                </label>

                                <input
                                    type="number"
                                    id="subtotal"
                                    name="subtotal"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="<?= htmlspecialchars(
                                        $factura['subtotal']
                                    ) ?>"
                                    required
                                >

                            </div>


                            <!-- IVA -->

                            <div class="col-md-4 mb-3">

                                <label
                                    for="iva"
                                    class="form-label"
                                >
                                    IVA
                                </label>

                                <input
                                    type="number"
                                    id="iva"
                                    name="iva"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="<?= htmlspecialchars(
                                        $factura['iva']
                                    ) ?>"
                                >

                            </div>


                            <!-- MÉTODO -->

                            <div class="col-md-4 mb-3">

                                <label
                                    for="metodo_pago"
                                    class="form-label"
                                >
                                    Método de pago
                                </label>

                                <select
                                    id="metodo_pago"
                                    name="metodo_pago"
                                    class="form-select"
                                >

                                    <option value="">
                                        Seleccionar
                                    </option>

                                    <?php
                                    $metodos = [
                                        'EFECTIVO',
                                        'TRANSFERENCIA',
                                        'TARJETA',
                                        'CHEQUE',
                                        'OTRO'
                                    ];
                                    ?>

                                    <?php foreach (
                                        $metodos as $metodo
                                    ): ?>

                                        <option
                                            value="<?= $metodo ?>"
                                            <?= $factura[
                                                'metodo_pago'
                                            ] === $metodo
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= $metodo ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ESTADO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="estado_pago"
                                    class="form-label"
                                >
                                    Estado de pago
                                </label>

                                <select
                                    id="estado_pago"
                                    name="estado_pago"
                                    class="form-select"
                                >

                                    <?php
                                    $estados = [
                                        'PENDIENTE',
                                        'PAGADA',
                                        'CANCELADA'
                                    ];
                                    ?>

                                    <?php foreach (
                                        $estados as $estado
                                    ): ?>

                                        <option
                                            value="<?= $estado ?>"
                                            <?= $factura[
                                                'estado_pago'
                                            ] === $estado
                                                ? 'selected'
                                                : '' ?>
                                        >
                                            <?= $estado ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </div>


                            <!-- ARCHIVO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="archivo"
                                    class="form-label"
                                >
                                    Reemplazar archivo
                                </label>

                                <input
                                    type="file"
                                    id="archivo"
                                    name="archivo"
                                    class="form-control"
                                    accept=".pdf,.xml,.jpg,.jpeg,.png"
                                >

                                <?php if (
                                    !empty(
                                        $factura['archivo']
                                    )
                                ): ?>

                                    <small class="text-muted">

                                        Archivo actual:

                                        <a
                                            href="<?= htmlspecialchars(
                                                $factura['archivo']
                                            ) ?>"
                                            target="_blank"
                                        >
                                            Ver archivo
                                        </a>

                                    </small>

                                <?php endif; ?>

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
                                ><?= htmlspecialchars(
                                    $factura['observaciones'] ?? ''
                                ) ?></textarea>

                            </div>

                        </div>


                        <div class="d-flex gap-2">

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
