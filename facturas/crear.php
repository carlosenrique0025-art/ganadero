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
| Mensajes
|--------------------------------------------------------------------------
*/

$error = $_SESSION['error'] ?? null;

unset($_SESSION['error']);


/*
|--------------------------------------------------------------------------
| Obtener socios
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
| Obtener proveedores
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

    <title>Registrar factura | Sistema Ganadero</title>

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
                        Registrar factura
                    </h2>

                    <p class="text-muted">
                        Registra una nueva factura.
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="guardar.php"
                        method="POST"
                        enctype="multipart/form-data"
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
                                        Seleccionar
                                    </option>

                                    <option value="INGRESO">
                                        Ingreso
                                    </option>

                                    <option value="EGRESO">
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
                                    value="0"
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
                                    value="0"
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

                                    <option value="EFECTIVO">
                                        Efectivo
                                    </option>

                                    <option value="TRANSFERENCIA">
                                        Transferencia
                                    </option>

                                    <option value="TARJETA">
                                        Tarjeta
                                    </option>

                                    <option value="CHEQUE">
                                        Cheque
                                    </option>

                                    <option value="OTRO">
                                        Otro
                                    </option>

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

                                    <option value="PENDIENTE">
                                        Pendiente
                                    </option>

                                    <option value="PAGADA">
                                        Pagada
                                    </option>

                                    <option value="CANCELADA">
                                        Cancelada
                                    </option>

                                </select>

                            </div>


                            <!-- ARCHIVO -->

                            <div class="col-md-6 mb-3">

                                <label
                                    for="archivo"
                                    class="form-label"
                                >
                                    Archivo de factura
                                </label>

                                <input
                                    type="file"
                                    id="archivo"
                                    name="archivo"
                                    class="form-control"
                                    accept=".pdf,.xml,.jpg,.jpeg,.png"
                                >

                                <small class="text-muted">
                                    PDF, XML, JPG o PNG. Máximo 10 MB.
                                </small>

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
                                    maxlength="1000"
                                ></textarea>

                            </div>

                        </div>


                        <!-- BOTONES -->

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
                                Guardar factura
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
