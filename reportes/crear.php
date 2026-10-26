<?php

session_start();

require_once '../config/database.php';

if (!isset($_SESSION['usuario_id'])) {

    header('Location: ../index.php');
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

    <title>Crear reporte | Sistema Ganadero</title>

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
                        Crear reporte
                    </h2>

                    <p class="text-muted mb-4">
                        Selecciona la información y el formato que deseas descargar.
                    </p>


                    <?php if ($error): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form
                        action="guardar.php"
                        method="POST"
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

                                <option value="">
                                    Seleccionar
                                </option>

                                <option value="GANADO">
                                    Ganado
                                </option>

                                <option value="MOVIMIENTOS">
                                    Movimientos de ganado
                                </option>

                                <option value="SOCIOS">
                                    Socios
                                </option>

                                <option value="GUIAS">
                                    Guías
                                </option>

                                <option value="FACTURAS">
                                    Facturas
                                </option>

                                <option value="PROVEEDORES">
                                    Proveedores
                                </option>

                                <option value="GENERAL">
                                    Reporte general
                                </option>

                                <option value="OTRO">
                                    Otro
                                </option>

                            </select>

                        </div>


                        <div class="mb-3">

                            <label
                                for="titulo"
                                class="form-label"
                            >
                                Título del reporte *
                            </label>

                            <input
                                type="text"
                                id="titulo"
                                name="titulo"
                                class="form-control"
                                maxlength="200"
                                placeholder="Ej. Inventario de ganado"
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
                                >

                            </div>

                        </div>


                        <div class="mb-3">

                            <label
                                for="formato"
                                class="form-label"
                            >
                                Formato de descarga *
                            </label>

                            <select
                                id="formato"
                                name="formato"
                                class="form-select"
                                required
                            >

                                <option value="PDF">
                                    PDF
                                </option>

                                <option value="WORD">
                                    Word (.docx)
                                </option>

                                <option value="CSV">
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
                                placeholder="Observaciones del reporte"
                            ></textarea>

                        </div>


                        <div class="alert alert-info">

                            <strong>
                                Importante:
                            </strong>

                            Al crear el reporte, el sistema consultará
                            la información correspondiente y generará
                            automáticamente el archivo para descargarlo.

                        </div>


                        <div class="d-flex gap-2 mt-4">

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
                                Generar y descargar reporte
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
