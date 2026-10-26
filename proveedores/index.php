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
| Obtener proveedores
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        nombre,
        empresa,
        rfc,
        telefono,
        email,
        direccion,
        ciudad,
        estado_direccion,
        productos_servicios,
        observaciones,
        estado,
        fecha_registro
    FROM proveedores
    WHERE rancho_id = :rancho_id
    ORDER BY id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$proveedores = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Proveedores | Sistema Ganadero</title>

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
                Proveedores
            </h1>

            <p class="text-muted">
                Registro y administración de proveedores
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
                + Registrar proveedor
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

            <?php if (empty($proveedores)): ?>

                <div class="text-center p-4">

                    <h4>
                        No hay proveedores registrados
                    </h4>

                    <p class="text-muted">
                        Comienza registrando tu primer proveedor.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Registrar proveedor
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
                                    Empresa
                                </th>

                                <th>
                                    RFC
                                </th>

                                <th>
                                    Teléfono
                                </th>

                                <th>
                                    Correo
                                </th>

                                <th>
                                    Ciudad
                                </th>

                                <th>
                                    Productos / Servicios
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

                        <?php foreach ($proveedores as $proveedor): ?>

                            <tr>


                                <!-- NOMBRE -->

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $proveedor['nombre']
                                        ) ?>
                                    </strong>

                                </td>


                                <!-- EMPRESA -->

                                <td>

                                    <?php if ($proveedor['empresa']): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['empresa']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- RFC -->

                                <td>

                                    <?php if ($proveedor['rfc']): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['rfc']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- TELÉFONO -->

                                <td>

                                    <?php if ($proveedor['telefono']): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['telefono']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?php if ($proveedor['email']): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['email']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrado
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- CIUDAD -->

                                <td>

                                    <?php if ($proveedor['ciudad']): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['ciudad']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrada
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- PRODUCTOS / SERVICIOS -->

                                <td>

                                    <?php if (
                                        $proveedor['productos_servicios']
                                    ): ?>

                                        <?= htmlspecialchars(
                                            $proveedor['productos_servicios']
                                        ) ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            No registrados
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- ESTADO -->

                                <td>

                                    <?php if (
                                        (int)$proveedor['estado'] === 1
                                    ): ?>

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

                                    <a
                                        href="editar.php?id=<?= (int)$proveedor['id'] ?>"
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
                                            value="<?= (int)$proveedor['id'] ?>"
                                        >


                                        <?php if (
                                            (int)$proveedor['estado'] === 1
                                        ): ?>

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
                                        onsubmit="return confirm('¿Estás seguro de eliminar este proveedor?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$proveedor['id'] ?>"
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
