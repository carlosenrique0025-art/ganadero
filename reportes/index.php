<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$rancho_id = (int)$_SESSION['rancho_id'];

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;

$descargar_id = $_SESSION['descargar_reporte'] ?? null;

unset(
    $_SESSION['success'],
    $_SESSION['error'],
    $_SESSION['descargar_reporte']
);

$sql = "
    SELECT
        r.id,
        r.tipo_reporte,
        r.titulo,
        r.fecha_inicio,
        r.fecha_fin,
        r.formato,
        r.archivo,
        r.observaciones,
        r.estado,
        r.fecha_registro,
        u.nombre AS usuario
    FROM reportes r
    LEFT JOIN usuarios u
        ON u.id = r.usuario_id
    WHERE r.rancho_id = :rancho_id
    ORDER BY r.id DESC
";

$stmt = $conexion->prepare($sql);

$stmt->execute([
    ':rancho_id' => $rancho_id
]);

$reportes = $stmt->fetchAll(PDO::FETCH_ASSOC);

function nombreTipoReporte($tipo)
{
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

    return $tipos[$tipo] ?? $tipo;
}

function formatoBadge($formato)
{
    switch ($formato) {

        case 'PDF':
            return '<span class="badge bg-danger">PDF</span>';

        case 'EXCEL':
            return '<span class="badge bg-success">Excel</span>';

        case 'WORD':
            return '<span class="badge bg-primary">Word</span>';

        case 'CSV':
            return '<span class="badge bg-secondary">CSV</span>';

        default:
            return '<span class="badge bg-secondary">' .
                htmlspecialchars($formato) .
                '</span>';
    }
}

function estadoBadge($estado)
{
    if ((int)$estado === 1) {
        return '<span class="badge bg-success">Activo</span>';
    }

    return '<span class="badge bg-secondary">Inactivo</span>';
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

    <title>Reportes | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1>Reportes</h1>

            <p class="text-muted">
                Generación y descarga de reportes del rancho
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
                + Crear reporte
            </a>

        </div>

    </div>

    <?php if ($success): ?>

        <div class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

    <?php endif; ?>

    <?php if ($error): ?>

        <div class="alert alert-danger">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>

    <div class="card shadow-sm">

        <div class="card-body">

            <?php if (empty($reportes)): ?>

                <div class="text-center p-5">

                    <h4>
                        No hay reportes registrados
                    </h4>

                    <p class="text-muted">
                        Crea tu primer reporte.
                    </p>

                    <a
                        href="crear.php"
                        class="btn btn-primary"
                    >
                        Crear reporte
                    </a>

                </div>

            <?php else: ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead>

                        <tr>

                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Periodo</th>
                            <th>Formato</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th>Archivo</th>
                            <th>Fecha</th>
                            <th>Acciones</th>

                        </tr>

                        </thead>

                        <tbody>

                        <?php foreach ($reportes as $reporte): ?>

                            <tr>

                                <td>

                                    <strong>
                                        <?= htmlspecialchars(
                                            $reporte['titulo']
                                        ) ?>
                                    </strong>

                                </td>

                                <td>

                                    <?= htmlspecialchars(
                                        nombreTipoReporte(
                                            $reporte['tipo_reporte']
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        $reporte['fecha_inicio'] ||
                                        $reporte['fecha_fin']
                                    ): ?>

                                        <?= $reporte['fecha_inicio']
                                            ? date(
                                                'd/m/Y',
                                                strtotime(
                                                    $reporte['fecha_inicio']
                                                )
                                            )
                                            : '--'
                                        ?>

                                        <br>

                                        <span class="text-muted">
                                            hasta
                                        </span>

                                        <br>

                                        <?= $reporte['fecha_fin']
                                            ? date(
                                                'd/m/Y',
                                                strtotime(
                                                    $reporte['fecha_fin']
                                                )
                                            )
                                            : '--'
                                        ?>

                                    <?php else: ?>

                                        <span class="text-muted">
                                            Todo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= formatoBadge(
                                        $reporte['formato']
                                    ) ?>
                                </td>

                                <td>
                                    <?= estadoBadge(
                                        $reporte['estado']
                                    ) ?>
                                </td>

                                <td>

                                    <?= $reporte['usuario']
                                        ? htmlspecialchars(
                                            $reporte['usuario']
                                        )
                                        : 'Sin usuario'
                                    ?>

                                </td>

                                <td>

                                    <?php if (
                                        !empty($reporte['archivo'])
                                    ): ?>

                                        <span class="badge bg-success">
                                            Disponible
                                        </span>

                                    <?php else: ?>

                                        <span class="badge bg-warning text-dark">
                                            Sin archivo
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $reporte['fecha_registro']
                                        )
                                    ) ?>

                                </td>

                                <td>

                                    <?php if (
                                        !empty($reporte['archivo'])
                                    ): ?>

                                        <a
                                            href="descargar.php?id=<?= (int)$reporte['id'] ?>"
                                            class="btn btn-sm btn-success"
                                        >
                                            Descargar
                                        </a>

                                    <?php endif; ?>

                                    <a
                                        href="editar.php?id=<?= (int)$reporte['id'] ?>"
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
                                            value="<?= (int)$reporte['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-secondary"
                                        >
                                            <?= (int)$reporte['estado'] === 1
                                                ? 'Desactivar'
                                                : 'Activar'
                                            ?>
                                        </button>

                                    </form>

                                    <form
                                        action="eliminar.php"
                                        method="POST"
                                        style="display:inline;"
                                        onsubmit="return confirm('¿Eliminar este reporte y su archivo?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?= (int)$reporte['id'] ?>"
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

<?php if ($descargar_id): ?>

<script>

window.addEventListener('load', function () {

    const iframe = document.createElement('iframe');

    iframe.style.display = 'none';

    iframe.src =
        'descargar.php?id=<?= (int)$descargar_id ?>';

    document.body.appendChild(iframe);

});

</script>

<?php endif; ?>

</body>
</html>
