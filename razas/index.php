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
| Obtener rancho del usuario
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
| Obtener razas
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        descripcion,
        estado,
        fecha_registro
    FROM razas
    WHERE rancho_id = :rancho_id
    ORDER BY nombre ASC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$razas = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Razas | Sistema Ganadero</title>

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
                Razas
            </h1>

            <p class="text-muted">
                Administración de razas de ganado
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
                + Nueva raza
            </a>

        </div>

    </div>


    <!-- MENSAJE DE ÉXITO -->

    <?php if ($success): ?>

        <div class="alert alert-success">

            <?= htmlspecialchars($success) ?>

        </div>

    <?php endif; ?>


    <!-- MENSAJE DE ERROR -->

    <?php if ($error): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- TABLA -->

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if (empty($razas)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay razas registradas
                    </h4>

                    <p class="text-muted">
                        Comienza registrando la primera raza.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar raza
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Nombre
                                </th>

                                <th>
                                    Descripción
                                </th>

                                <th>
                                    Estado
                                </th>

                                <th>
                                    Fecha de registro
                                </th>

                                <th>
                                    Acciones
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php foreach ($razas as $raza): ?>

                            <tr>

                                <td>
                                    <?= (int)$raza['id'] ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($raza['nombre']) ?>
                                    </strong>

                                </td>

                                <td>

                                    <?= $raza['descripcion']
                                        ? htmlspecialchars($raza['descripcion'])
                                        : '<span class="text-muted">Sin descripción</span>'
                                    ?>

                                </td>

                                <td>

                                    <?php if ((int)$raza['estado'] === 1): ?>

                                        <span class="badge bg-success">
                                            Activa
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactiva
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime($raza['fecha_registro'])
                                    ) ?>

                                </td>

                                <td>

                                    <a
                                        href="editar.php?id=<?= (int)$raza['id'] ?>"
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
                                            value="<?= (int)$raza['id'] ?>"
                                        >

                                        <?php if ((int)$raza['estado'] === 1): ?>

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


                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Estás seguro de eliminar esta raza?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$raza['id'] ?>"
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
