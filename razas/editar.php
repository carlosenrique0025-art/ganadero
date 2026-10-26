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
| Obtener ID
|--------------------------------------------------------------------------
*/

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {

    $_SESSION['error'] =
        'Raza no válida.';

    header('Location: index.php');
    exit;

}


$rancho_id = $_SESSION['rancho_id'];


/*
|--------------------------------------------------------------------------
| Buscar raza
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        descripcion,
        estado
    FROM razas
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$raza = $stmt->fetch();


/*
|--------------------------------------------------------------------------
| Verificar existencia
|--------------------------------------------------------------------------
*/

if (!$raza) {

    $_SESSION['error'] =
        'La raza no existe o no pertenece a tu rancho.';

    header('Location: index.php');
    exit;

}

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar raza | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-4">
                        Editar raza
                    </h2>


                    <form
                        action="actualizar.php"
                        method="POST"
                    >

                        <!-- ID -->

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$raza['id'] ?>"
                        >


                        <!-- NOMBRE -->

                        <div class="mb-3">

                            <label
                                for="nombre"
                                class="form-label"
                            >
                                Nombre de la raza
                            </label>

                            <input
                                type="text"
                                id="nombre"
                                name="nombre"
                                class="form-control"
                                maxlength="100"
                                value="<?= htmlspecialchars($raza['nombre']) ?>"
                                required
                            >

                        </div>


                        <!-- DESCRIPCIÓN -->

                        <div class="mb-3">

                            <label
                                for="descripcion"
                                class="form-label"
                            >
                                Descripción
                            </label>

                            <textarea
                                id="descripcion"
                                name="descripcion"
                                class="form-control"
                                maxlength="255"
                                rows="4"
                            ><?= htmlspecialchars($raza['descripcion'] ?? '') ?></textarea>

                        </div>


                        <!-- ESTADO -->

                        <div class="mb-3">

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
                                    value="1"
                                    <?= (int)$raza['estado'] === 1 ? 'selected' : '' ?>
                                >
                                    Activa
                                </option>

                                <option
                                    value="0"
                                    <?= (int)$raza['estado'] === 0 ? 'selected' : '' ?>
                                >
                                    Inactiva
                                </option>

                            </select>

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
                                Actualizar raza
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
