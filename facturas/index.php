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
| Mensajes
|--------------------------------------------------------------------------
*/

$success = $_SESSION['success'] ?? null;
$error   = $_SESSION['error'] ?? null;

unset($_SESSION['success'], $_SESSION['error']);


/*
|--------------------------------------------------------------------------
| Obtener facturas
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        f.id,
        f.folio,
        f.tipo,
        f.fecha,
        f.concepto,
        f.subtotal,
        f.iva,
        f.total,
        f.metodo_pago,
        f.estado_pago,
        f.archivo,
        f.observaciones,

        s.nombre AS socio_nombre,
        s.apellido_paterno AS socio_apellido,

        p.nombre AS proveedor_nombre,
        p.empresa AS proveedor_empresa

    FROM facturas f

    LEFT JOIN socios s
        ON s.id = f.socio_id

    LEFT JOIN proveedores p
        ON p.id = f.proveedor_id

    WHERE f.rancho_id = :rancho_id

    ORDER BY f.id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Facturas | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">


    <!-- ENCABEZADO -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1>
                Facturas
            </h1>

            <p class="text-muted">
                Registro y administración de facturas
            </p>

        </div>


        <div>

            <a
                href="../dashboard/index.php"
                class="btn btn-secondary"
            >
                Volver
            </a>

            <a
                href="crear.php"
                class="btn btn-primary"
            >
                + Registrar factura
            </a>

        </div>

    </div>


    <!-- MENSAJE ÉXITO -->

    <?php if ($success): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <!-- MENSAJE ERROR -->

    <?php if ($error): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- TABLA -->

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if (empty($facturas)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay facturas registradas
                    </h4>

                    <p class="text-muted">
                        Comienza registrando tu primera factura.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar factura
                    </a>

                </div>

            <?php else: ?>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>Folio</th>

                                <th>Tipo</th>

                                <th>Fecha</th>

                                <th>Concepto</th>

                                <th>Socio / Proveedor</th>

                                <th>Total</th>

                                <th>Método de pago</th>

                                <th>Estado</th>

                                <th>Archivo</th>

                                <th>Acciones</th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($facturas as $factura): ?>

                            <tr>

                                <!-- FOLIO -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $factura['folio']
                                        ) ?>
                                    </strong>

                                </td>


                                <!-- TIPO -->

                                <td>

                                    <?php if (
                                        $factura['tipo'] === 'INGRESO'
                                    ): ?>

                                        <span class="badge bg-success">
                                            Ingreso
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Egreso
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime($factura['fecha'])
                                    ) ?>

                                </td>


                                <!-- CONCEPTO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $factura['concepto']
                                    ) ?>

                                </td>


                                <!-- SOCIO / PROVEEDOR -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $factura['socio_nombre']
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            trim(
                                                $factura['socio_nombre']
                                                . ' '
                                                . (
                                                    $factura[
                                                        'socio_apellido'
                                                    ] ?? ''
                                                )
                                            )
                                        );

                                    } elseif (
                                        !empty(
                                            $factura[
                                                'proveedor_nombre'
                                            ]
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            $factura[
                                                'proveedor_empresa'
                                            ]
                                            ?: $factura[
                                                'proveedor_nombre'
                                            ]
                                        );

                                    } else {

                                        echo '<span class="text-muted">
                                                No registrado
                                              </span>';

                                    }

                                    ?>

                                </td>


                                <!-- TOTAL -->

                                <td>

                                    <strong>

                                        $
                                        <?= number_format(
                                            (float)$factura['total'],
                                            2
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- MÉTODO -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $factura['metodo_pago']
                                        )
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $factura['metodo_pago']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php if (
                                        $factura['estado_pago']
                                        === 'PAGADA'
                                    ): ?>

                                        <span class="badge bg-success">
                                            Pagada
                                        </span>

                                    <?php elseif (
                                        $factura['estado_pago']
                                        === 'PENDIENTE'
                                    ): ?>

                                        <span
                                            class="badge bg-warning text-dark"
                                        >
                                            Pendiente
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Cancelada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ARCHIVO -->

                                <td>

                                    <?php if (
                                        !empty(
                                            $factura['archivo']
                                        )
                                    ): ?>

                                        <a
                                            href="<?= htmlspecialchars(
                                                $factura['archivo']
                                            ) ?>"
                                            target="_blank"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Ver archivo
                                        </a>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin archivo
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <a
                                        href="editar.php?id=<?= (int)$factura['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Editar
                                    </a>


                                    <form
                                        action="cambiar_estado.php"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$factura['id'] ?>"
                                        >


                                        <?php if (
                                            $factura['estado_pago']
                                            === 'PENDIENTE'
                                        ): ?>

                                            <input
                                                type="hidden"
                                                name="estado"
                                                value="PAGADA"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                            >
                                                Marcar pagada
                                            </button>

                                        <?php elseif (
                                            $factura['estado_pago']
                                            === 'PAGADA'
                                        ): ?>

                                            <input
                                                type="hidden"
                                                name="estado"
                                                value="PENDIENTE"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-warning"
                                            >
                                                Marcar pendiente
                                            </button>

                                        <?php endif; ?>

                                    </form>


                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar esta factura? Esta acción no se puede deshacer.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$factura['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >
                                            Eliminar
                                        </button>

                                    </form>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
