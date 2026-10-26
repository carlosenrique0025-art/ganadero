<?php

session_start();

/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$nombre = $_SESSION['nombre'] ?? 'Usuario';
$nombreRancho = $_SESSION['nombre_rancho'] ?? 'Sin rancho';
$username = $_SESSION['username'] ?? '';
$rol = $_SESSION['rol'] ?? '';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard | Sistema Ganadero</title>

    <link
        rel="stylesheet"
        href="../assets/css/portal.css"
    >

</head>

<body>

<div class="app">

    <!-- =========================================================
         BARRA LATERAL
    ========================================================== -->

    <aside class="sidebar" id="sidebar">

        <div class="logo">

            <div class="logo-icon">
                🐄
            </div>

            <div>
                <h2>Ganadero</h2>
                <span>Sistema de gestión</span>
            </div>

        </div>


        <nav class="menu">

            <p class="menu-title">
                PRINCIPAL
            </p>

            <a
                href="#"
                class="menu-item active"
            >
                <span>📊</span>
                Dashboard
            </a>


            <p class="menu-title">
                ADMINISTRACIÓN
            </p>

            <a
                href="../ganado/index.php"
                class="menu-item"
            >
                <span>🐄</span>
                Ganado
            </a>

            <a
                href="../razas/index.php"
                class="menu-item"
            >
                <span>🧬</span>
                Razas
            </a>


            <p class="menu-title">
                CUENTA
            </p>

            <a
                href="../auth/logout.php"
                class="menu-item logout"
            >
                <span>🚪</span>
                Cerrar sesión
            </a>

        </nav>

    </aside>


    <!-- =========================================================
         CONTENIDO PRINCIPAL
    ========================================================== -->

    <main class="main">

        <!-- HEADER -->

        <header class="topbar">

            <button
                class="menu-toggle"
                id="menuToggle"
                type="button"
            >
                ☰
            </button>


            <div class="topbar-title">

                <h1>
                    Dashboard
                </h1>

                <p>
                    Sistema de gestión ganadera
                </p>

            </div>


            <div class="user-menu">

                <div class="user-avatar">
                    <?= strtoupper(substr($nombre, 0, 1)) ?>
                </div>

                <div class="user-info">

                    <strong>
                        <?= htmlspecialchars($nombre) ?>
                    </strong>

                    <span>
                        <?= htmlspecialchars($rol) ?>
                    </span>

                </div>

            </div>

        </header>


        <!-- CONTENIDO -->

        <section class="content">


            <!-- BIENVENIDA -->

            <div class="welcome">

                <div>

                    <span class="welcome-label">
                        BIENVENIDO
                    </span>

                    <h2>
                        Hola, <?= htmlspecialchars($nombre) ?> 👋
                    </h2>

                    <p>
                        Administra la información de tu rancho
                        desde este panel.
                    </p>

                </div>

                <div class="welcome-cow">
                    🐄
                </div>

            </div>


            <!-- INFORMACIÓN DEL USUARIO -->

            <div class="info-grid">

                <div class="info-card">

                    <div class="info-icon ranch">
                        🌾
                    </div>

                    <div>

                        <span>
                            Rancho
                        </span>

                        <strong>
                            <?= htmlspecialchars($nombreRancho) ?>
                        </strong>

                    </div>

                </div>


                <div class="info-card">

                    <div class="info-icon user">
                        👤
                    </div>

                    <div>

                        <span>
                            Usuario
                        </span>

                        <strong>
                            <?= htmlspecialchars($username) ?>
                        </strong>

                    </div>

                </div>


                <div class="info-card">

                    <div class="info-icon role">
                        🔐
                    </div>

                    <div>

                        <span>
                            Rol
                        </span>

                        <strong>
                            <?= htmlspecialchars($rol) ?>
                        </strong>

                    </div>

                </div>

            </div>


            <!-- MÓDULOS -->

            <div class="section-header">

                <div>

                    <h2>
                        Módulos del sistema
                    </h2>

                    <p>
                        Selecciona una opción para comenzar.
                    </p>

                </div>

            </div>


            <div class="modules">


                <!-- GANADO -->

                <a
                    href="../ganado/index.php"
                    class="module-card"
                >

                    <div class="module-icon ganado-icon">
                        🐄
                    </div>

                    <div class="module-content">

                        <h3>
                            Ganado
                        </h3>

                        <p>
                            Registra y administra los animales
                            de tu rancho.
                        </p>

                        <span class="module-link">
                            Administrar →
                        </span>

                    </div>

                </a>


                <!-- RAZAS -->

                <a
                    href="../razas/index.php"
                    class="module-card"
                >

                    <div class="module-icon razas-icon">
                        🧬
                    </div>

                    <div class="module-content">

                        <h3>
                            Razas
                        </h3>

                        <p>
                            Administra las diferentes razas
                            de ganado.
                        </p>

                        <span class="module-link">
                            Administrar →
                        </span>

                    </div>

                </a>


            </div>


            <!-- PIE -->

            <footer class="footer">

                <span>
                    Sistema Ganadero
                </span>

                <span>
                    © <?= date('Y') ?>
                </span>

            </footer>


        </section>

    </main>

</div>


<script src="../assets/js/portal.js"></script>

</body>

</html>
