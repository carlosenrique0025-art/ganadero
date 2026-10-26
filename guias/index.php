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

unset(
    $_SESSION['success'],
    $_SESSION['error']
);


/*
|--------------------------------------------------------------------------
| Obtener guías
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        g.id,
        g.numero_guia,
        g.tipo,
        g.fecha_emision,
        g.fecha_vencimiento,
        g.origen,
        g.destino,
        g.transportista,
        g.placas,
        g.cantidad_animales,
        g.estado,
        CONCAT(
            COALESCE(s.nombre, ''),
            ' ',
            COALESCE(s.apellido_paterno, ''),
            ' ',
            COALESCE(s.apellido_materno, '')
        ) AS socio
    FROM guias g

    LEFT JOIN socios s
        ON s.id = g.socio_id

    WHERE g.rancho_id = :rancho_id

    ORDER BY g.id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$guias = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Guías | Sistema Ganadero</title>

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
                Guías
            </h1>

            <p class="text-muted">
                Registro y administración de guías de movilización
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
                + Nueva guía
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

            <?php if (empty($guias)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay guías registradas
                    </h4>

                    <p class="text-muted">
                        Comienza registrando tu primera guía.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar guía
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Número de guía
                                </th>

                                <th>
                                    Tipo
                                </th>

                                <th>
                                    Fecha emisión
                                </th>

                                <th>
                                    Origen
                                </th>

                                <th>
                                    Destino
                                </th>

                                <th>
                                    Socio
                                </th>

                                <th>
                                    Animales
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($guias as $guia): ?>

                            <tr>


                                <!-- NÚMERO -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $guia['numero_guia']
                                        ) ?>
                                    </strong>

                                </td>


                                <!-- TIPO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $guia['tipo']
                                    ) ?>

                                </td>


                                <!-- FECHA -->

                                <td>

                                    <?= date(
                                        'd/m/Y',
                                        strtotime(
                                            $guia['fecha_emision']
                                        )
                                    ) ?>

                                </td>


                                <!-- ORIGEN -->

                                <td>

                                    <?= $guia['origen']
                                        ? htmlspecialchars(
                                            $guia['origen']
                                        )
                                        : '<span class="text-muted">
                                            No registrado
                                           </span>'
                                    ?>

                                </td>


                                <!-- DESTINO -->

                                <td>

                                    <?= $guia['destino']
                                        ? htmlspecialchars(
                                            $guia['destino']
                                        )
                                        : '<span class="text-muted">
                                            No registrado
                                           </span>'
                                    ?>

                                </td>


                                <!-- SOCIO -->

                                <td>

                                    <?php if (
                                        trim($guia['socio']) !== ''
                                    ): ?>

                                        <?= htmlspecialchars(
                                            trim($guia['socio'])
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Sin socio
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ANIMALES -->

                                <td>

                                    <span class="badge bg-info">

                                        <?= (int)$guia[
                                            'cantidad_animales'
                                        ] ?>

                                    </span>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php

                                    $estado = $guia['estado'];

                                    if ($estado === 'ACTIVA'):

                                    ?>

                                        <span class="badge bg-success">
                                            Activa
                                        </span>

                                    <?php elseif (
                                        $estado === 'UTILIZADA'
                                    ): ?>

                                        <span class="badge bg-primary">
                                            Utilizada
                                        </span>

                                    <?php elseif (
                                        $estado === 'VENCIDA'
                                    ): ?>

                                        <span class="badge bg-warning text-dark">
                                            Vencida
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-danger">
                                            Cancelada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <a
                                        href="editar.php?id=<?= (int)$guia['id'] ?>"
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
                                            value="<?= (int)$guia['id'] ?>"
                                        >


                                        <?php if (
                                            $guia['estado'] === 'ACTIVA'
                                        ): ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-secondary"
                                            >
                                                Cancelar
                                            </button>

                                        <?php else: ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-success"
                                            >
                                                Activar
                                            </button>

                                        <?php endif; ?>

                                    </form>


                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar esta guía?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$guia['id'] ?>"
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
