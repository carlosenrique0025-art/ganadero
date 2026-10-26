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
| Obtener movimientos
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        m.id,
        m.fecha_movimiento,
        m.tipo_movimiento,
        m.peso,
        m.precio,
        m.origen,
        m.destino,
        m.motivo,
        m.observaciones,

        g.arete,
        g.nombre AS nombre_ganado,

        s.nombre AS socio_nombre,
        s.apellido_paterno,
        s.apellido_materno

    FROM movimientos_ganado m

    INNER JOIN ganado g
        ON g.id = m.ganado_id

    LEFT JOIN socios s
        ON s.id = m.socio_id

    WHERE m.rancho_id = :rancho_id

    ORDER BY
        m.fecha_movimiento DESC,
        m.id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$movimientos = $stmt->fetchAll();

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
        Movimientos de ganado | Sistema Ganadero
    </title>

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
                Movimientos de ganado
            </h1>

            <p class="text-muted">
                Registro y administración de movimientos del ganado
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
                + Registrar movimiento
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


            <?php if (empty($movimientos)): ?>


                <div class="text-center p-4">

                    <h4>
                        No hay movimientos registrados
                    </h4>

                    <p class="text-muted">
                        Comienza registrando el primer movimiento del ganado.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar movimiento
                    </a>

                </div>


            <?php else: ?>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Fecha
                                </th>

                                <th>
                                    Arete
                                </th>

                                <th>
                                    Animal
                                </th>

                                <th>
                                    Movimiento
                                </th>

                                <th>
                                    Socio
                                </th>

                                <th>
                                    Peso
                                </th>

                                <th>
                                    Precio
                                </th>

                                <th>
                                    Origen
                                </th>

                                <th>
                                    Destino
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php foreach ($movimientos as $movimiento): ?>


                            <tr>


                                <!-- FECHA -->

                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $movimiento['fecha_movimiento']
                                        )
                                    ) ?>

                                </td>


                                <!-- ARETE -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $movimiento['arete']
                                        ) ?>

                                    </strong>

                                </td>


                                <!-- ANIMAL -->

                                <td>

                                    <?php if (
                                        $movimiento['nombre_ganado']
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento['nombre_ganado']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin nombre
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TIPO MOVIMIENTO -->

                                <td>

                                    <?php

                                    $tipo =
                                        $movimiento['tipo_movimiento'];

                                    $clase = 'bg-secondary';

                                    $texto = $tipo;

                                    switch ($tipo) {

                                        case 'ENTRADA':
                                            $clase = 'bg-success';
                                            $texto = 'Entrada';
                                            break;

                                        case 'SALIDA':
                                            $clase = 'bg-danger';
                                            $texto = 'Salida';
                                            break;

                                        case 'COMPRA':
                                            $clase = 'bg-primary';
                                            $texto = 'Compra';
                                            break;

                                        case 'VENTA':
                                            $clase = 'bg-warning text-dark';
                                            $texto = 'Venta';
                                            break;

                                        case 'TRASLADO':
                                            $clase = 'bg-info text-dark';
                                            $texto = 'Traslado';
                                            break;

                                        case 'MUERTE':
                                            $clase = 'bg-dark';
                                            $texto = 'Muerte';
                                            break;

                                        case 'BAJA':
                                            $clase = 'bg-secondary';
                                            $texto = 'Baja';
                                            break;

                                        case 'OTRO':
                                            $clase = 'bg-secondary';
                                            $texto = 'Otro';
                                            break;

                                    }

                                    ?>

                                    <span
                                        class="badge <?= $clase ?>"
                                    >
                                        <?= htmlspecialchars($texto) ?>
                                    </span>

                                </td>


                                <!-- SOCIO -->

                                <td>

                                    <?php if (
                                        $movimiento['socio_nombre']
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento['socio_nombre']
                                        ) ?>

                                        <?= htmlspecialchars(
                                            $movimiento['apellido_paterno'] ?? ''
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin socio
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PESO -->

                                <td>

                                    <?php if (
                                        $movimiento['peso'] !== null
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento['peso']
                                        ) ?>
                                        kg

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PRECIO -->

                                <td>

                                    <?php if (
                                        $movimiento['precio'] !== null
                                    ): ?>

                                        $
                                        <?= number_format(
                                            (float)$movimiento['precio'],
                                            2
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ORIGEN -->

                                <td>

                                    <?php if (
                                        $movimiento['origen']
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento['origen']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- DESTINO -->

                                <td>

                                    <?php if (
                                        $movimiento['destino']
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $movimiento['destino']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            —
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <a
                                        href="editar.php?id=<?= (int)$movimiento['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Editar
                                    </a>


                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este movimiento?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$movimiento['id'] ?>"
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
