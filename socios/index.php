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
| Obtener socios
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        apellido_paterno,
        apellido_materno,
        telefono,
        email,
        rfc,
        curp,
        tipo,
        estado,
        fecha_registro
    FROM socios
    WHERE rancho_id = :rancho_id
    ORDER BY id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$socios = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Socios | Sistema Ganadero</title>

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
                Socios
            </h1>

            <p class="text-muted">
                Registro y administración de socios, clientes y compradores
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
                + Registrar socio
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

            <?php if (empty($socios)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay socios registrados
                    </h4>

                    <p class="text-muted">
                        Comienza registrando tu primer socio.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar socio
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>
                                    Nombre
                                </th>

                                <th>
                                    Teléfono
                                </th>

                                <th>
                                    Email
                                </th>

                                <th>
                                    RFC
                                </th>

                                <th>
                                    Tipo
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

                        <?php foreach ($socios as $socio): ?>

                            <tr>

                                <!-- NOMBRE -->

                                <td>

                                    <strong>

                                        <?= htmlspecialchars(
                                            $socio['nombre']
                                        ) ?>

                                        <?php if ($socio['apellido_paterno']): ?>

                                            <?= htmlspecialchars(
                                                $socio['apellido_paterno']
                                            ) ?>

                                        <?php endif; ?>

                                        <?php if ($socio['apellido_materno']): ?>

                                            <?= htmlspecialchars(
                                                $socio['apellido_materno']
                                            ) ?>

                                        <?php endif; ?>

                                    </strong>

                                </td>


                                <!-- TELÉFONO -->

                                <td>

                                    <?= $socio['telefono']
                                        ? htmlspecialchars($socio['telefono'])
                                        : '<span class="text-muted">No registrado</span>'
                                    ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?= $socio['email']
                                        ? htmlspecialchars($socio['email'])
                                        : '<span class="text-muted">No registrado</span>'
                                    ?>

                                </td>


                                <!-- RFC -->

                                <td>

                                    <?= $socio['rfc']
                                        ? htmlspecialchars($socio['rfc'])
                                        : '<span class="text-muted">No registrado</span>'
                                    ?>

                                </td>


                                <!-- TIPO -->

                                <td>

                                    <?php if ($socio['tipo'] === 'SOCIO'): ?>

                                        <span class="badge bg-primary">
                                            Socio
                                        </span>

                                    <?php elseif ($socio['tipo'] === 'CLIENTE'): ?>

                                        <span class="badge bg-info">
                                            Cliente
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Comprador
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php if ((int)$socio['estado'] === 1): ?>

                                        <span class="badge bg-success">
                                            Activo
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactivo
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ACCIONES -->

                                <td>

                                    <!-- EDITAR -->

                                    <a
                                        href="editar.php?id=<?= (int)$socio['id'] ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Editar
                                    </a>


                                    <!-- CAMBIAR ESTADO -->

                                    <form
                                        action="cambiar_estado.php"
                                        method="POST"
                                        style="display:inline;"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$socio['id'] ?>"
                                        >

                                        <?php if ((int)$socio['estado'] === 1): ?>

                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-secondary"
                                            >
                                                Desactivar
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


                                    <!-- ELIMINAR -->

                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este socio? Esta acción no se puede deshacer.');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$socio['id'] ?>"
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
