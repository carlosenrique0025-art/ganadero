<?php

session_start();

/*
|--------------------------------------------------------------------------
| Redirección si ya hay una sesión activa
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['usuario_id'])) {
    if (($_SESSION['rol'] ?? '') === 'superadmin') {
        header('Location: admin/index.php');
    } else {
        header('Location: dashboard/index.php');
    }
    exit;
}

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

unset($_SESSION['error'], $_SESSION['success']);

?>
<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Sistema Ganadero</title>
    <link rel="stylesheet" href="assets/css/portal.css">

</head>

<body class="app app-login p-0">

<div class="row g-0 app-auth-wrapper">

    <!-- LOGIN -->
    <div class="col-12 col-md-7 col-lg-6 auth-main-col text-center p-5">

        <div class="d-flex flex-column align-content-end">

            <div class="app-auth-body mx-auto">

                <div class="app-auth-branding mb-4">
                    <a class="app-logo">
                        <img
                            class="logo-icon me-2"
                            src="assets/images/logo3.png"
                            alt="Logo"
                        >
                    </a>
                </div>

                <h2 class="auth-heading text-center mb-5">
                    Sistema Ganadero
                </h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <div class="auth-form-container text-start">

                    <form
                        class="auth-form login-form"
                        action="auth/procesar_login.php"
                        method="POST"
                    >

                        <!-- USUARIO -->
                        <div class="mb-3">
                            <label for="username">Usuario o Correo</label>
                            <input
                                id="username"
                                name="username"
                                type="text"
                                class="form-control"
                                placeholder="Nombre de usuario o correo"
                                maxlength="100"
                                autocomplete="username"
                                required
                            >
                        </div>

                        <!-- CONTRASEÑA -->
                        <div class="mb-3">
                            <label for="password">Contraseña</label>
                            <input
                                id="password"
                                name="password"
                                type="password"
                                class="form-control"
                                placeholder="Contraseña"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <!-- BOTÓN -->
                        <div class="text-center">
                            <button
                                type="submit"
                                class="btn app-btn-primary w-100 theme-btn"
                            >
                                Iniciar sesión
                            </button>
                        </div>

                    </form>

                    <div class="auth-option text-center pt-5">
                        ¿No tienes cuenta?
                        <a class="text-link" href="registro.php">
                            Crear una cuenta
                        </a>
                    </div>

                </div>

            </div>

        </div>

    </div>

    <!-- IMAGEN -->
    <div class="col-12 col-md-5 col-lg-6 h-100 auth-background-col">
        <div class="auth-background-holder"></div>
        <div class="auth-background-mask"></div>
    </div>

</div>

</body>
</html>