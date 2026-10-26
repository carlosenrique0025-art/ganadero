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
| Obtener ganado
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        g.id,
        g.arete,
        g.nombre,
        g.sexo,
        g.fecha_nacimiento,
        g.peso_actual,
        g.color,
        g.procedencia,
        g.fecha_ingreso,
        g.estado,
        g.observaciones,
        r.nombre AS raza
    FROM ganado g

    LEFT JOIN razas r
        ON r.id = g.raza_id

    WHERE g.rancho_id = :rancho_id

    ORDER BY g.id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$ganado = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Ganado | Sistema Ganadero</title>

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
                Ganado
            </h1>

            <p class="text-muted">
                Registro y administración del ganado
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
                + Registrar ganado
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

            <?php if (empty($ganado)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay ganado registrado
                    </h4>

                    <p class="text-muted">
                        Comienza registrando tu primer animal.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar ganado
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                            <tr>

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
                                    Fecha nacimiento
                                </th>

                                <th>
                                    Peso
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

                        <?php foreach ($ganado as $animal): ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars($animal['arete']) ?>
                                    </strong>

                                </td>


                                <td>

                                    <?= $animal['nombre']
                                        ? htmlspecialchars($animal['nombre'])
                                        : '<span class="text-muted">Sin nombre</span>'
                                    ?>

                                </td>


                                <td>

                                    <?php if ($animal['sexo'] === 'MACHO'): ?>

                                        <span class="badge bg-info">
                                            Macho
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Hembra
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?= $animal['raza']
                                        ? htmlspecialchars($animal['raza'])
                                        : '<span class="text-muted">Sin raza</span>'
                                    ?>

                                </td>


                                <td>

                                    <?php if ($animal['fecha_nacimiento']): ?>

                                        <?= date(
                                            'd/m/Y',
                                            strtotime($animal['fecha_nacimiento'])
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ($animal['peso_actual'] !== null): ?>

                                        <?= htmlspecialchars($animal['peso_actual']) ?>
                                        kg

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <?php if ((int)$animal['estado'] === 1): ?>

                                        <span class="badge bg-success">
                                            Activo
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-secondary">
                                            Inactivo
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <td>

                                    <a
                                        href="editar.php?id=<?= (int)$animal['id'] ?>"
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
                                            value="<?= (int)$animal['id'] ?>"
                                        >

                                        <?php if ((int)$animal['estado'] === 1): ?>

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
                                        onsubmit="return confirm('¿Estás seguro de eliminar este animal?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$animal['id'] ?>"
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
