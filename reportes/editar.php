<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
    exit;
}

$rancho_id = (int)$_SESSION['rancho_id'];

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    $_SESSION['error'] =
        'Reporte no válido.';

    header('Location: index.php');
    exit;
}

$sql = "
    SELECT
        id,
        tipo_reporte,
        titulo,
        fecha_inicio,
        fecha_fin,
        formato,
        observaciones,
        estado
    FROM reportes
    WHERE id = :id
      AND rancho_id = :rancho_id
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':id' => $id,
    ':rancho_id' => $rancho_id
]);

$reporte = $stmt->fetch(
    PDO::FETCH_ASSOC
);

if (!$reporte) {

    $_SESSION['error'] =
        'El reporte no existe.';

    header('Location: index.php');
    exit;
}

$error = $_SESSION['error'] ?? null;

unset($_SESSION['error']);

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Editar reporte | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-lg-8">

            <div class="card shadow-sm">

                <div class="card-body">

                    <h2 class="mb-2">
                        Editar reporte
                    </h2>

                    <p class="text-muted mb-4">
                        Modifica los datos y vuelve a generar el archivo.
                    </p>

                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>

                    <form
                        action="actualizar.php"
                        method="POST"
                    >

                        <input
                            type="hidden"
                            name="id"
                            value="<?= (int)$reporte['id'] ?>"
                        >

                        <div class="mb-3">

                            <label
                                for="tipo_reporte"
                                class="form-label"
                            >
                                Tipo de reporte *
                            </label>

                            <select
                                id="tipo_reporte"
                                name="tipo_reporte"
                                class="form-select"
                                required
                            >

                                <?php
                                $tipos = [
                                    'GANADO' => 'Ganado',
                                    'MOVIMIENTOS' => 'Movimientos de ganado',
                                    'SOCIOS' => 'Socios',
                                    'GUIAS' => 'Guías',
                                    'FACTURAS' => 'Facturas',
                                    'PROVEEDORES' => 'Proveedores',
                                    'GENERAL' => 'Reporte general',
                                    'OTRO' => 'Otro'
                                ];
                                ?>

                                <?php foreach ($tipos as $valor => $nombre): ?>

                                    <option
                                        value="<?= $valor ?>"
                                        <?= $reporte['tipo_reporte'] === $valor
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >
                                        <?= htmlspecialchars($nombre) ?>
                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label
                                for="titulo"
                                class="form-label"
                            >
                                Título *
                            </label>

                            <input
                                type="text"
                                id="titulo"
                                name="titulo"
                                class="form-control"
                                maxlength="200"
                                value="<?= htmlspecialchars(
                                    $reporte['titulo']
                                ) ?>"
                                required
                            >

                        </div>

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_inicio"
                                    class="form-label"
                                >
                                    Fecha de inicio
                                </label>

                                <input
                                    type="date"
                                    id="fecha_inicio"
                                    name="fecha_inicio"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $reporte['fecha_inicio'] ?? ''
                                    ) ?>"
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <label
                                    for="fecha_fin"
                                    class="form-label"
                                >
                                    Fecha de fin
                                </label>

                                <input
                                    type="date"
                                    id="fecha_fin"
                                    name="fecha_fin"
                                    class="form-control"
                                    value="<?= htmlspecialchars(
                                        $reporte['fecha_fin'] ?? ''
                                    ) ?>"
                                >

                            </div>

                        </div>

                        <div class="mb-3">

                            <label
                                for="formato"
                                class="form-label"
                            >
                                Formato *
                            </label>

                            <select
                                id="formato"
                                name="formato"
                                class="form-select"
                                required
                            >

                                <option
                                    value="PDF"
                                    <?= $reporte['formato'] === 'PDF'
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    PDF
                                </option>

                                <option
                                    value="EXCEL"
                                    <?= $reporte['formato'] === 'EXCEL'
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Excel (.xlsx)
                                </option>

                                <option
                                    value="WORD"
                                    <?= $reporte['formato'] === 'WORD'
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    Word (.docx)
                                </option>

                                <option
                                    value="CSV"
                                    <?= $reporte['formato'] === 'CSV'
                                        ? 'selected'
                                        : ''
                                    ?>
                                >
                                    CSV
                                </option>

                            </select>

                        </div>

                        <div class="mb-3">

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
                            ><?= htmlspecialchars(
                                $reporte['observaciones'] ?? ''
                            ) ?></textarea>

                        </div>

                        <div class="alert alert-info">

                            Al guardar los cambios se volverá a consultar
                            la información y se generará nuevamente el
                            archivo seleccionado.

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
                                Guardar y regenerar
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
