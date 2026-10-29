<?php
session_start();

/*
|--------------------------------------------------------------------------
| Si ya inició sesión, enviarlo al dashboard
|--------------------------------------------------------------------------
*/
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard/index.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Mensajes
|--------------------------------------------------------------------------
*/
$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;

/*
|--------------------------------------------------------------------------
| Limpiar mensajes después de mostrarlos
|--------------------------------------------------------------------------
*/
unset($_SESSION['error']);
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de gestión ganadera">
    <title>Iniciar sesión | Sistema Ganadero</title>

    <!-- Bootstrap, FontAwesome y fuente Lora -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@1,600;1,700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ----- FONDO VERDE ----- */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #dcebd5 0%, #0ad451 100%);
            background-attachment: fixed;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
        }

        .page-content {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 1300px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 40px;
        }

        /* ----- LADO IZQUIERDO: FRASE ARRIBA + LOGO ABAJO ----- */
        .brand-side {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 30px;
        }

        .brand-tagline-text {
            font-family: 'Lora', Georgia, 'Times New Roman', serif;
            font-style: italic;
            font-size: 1.6rem;
            font-weight: 600;
            line-height: 1.4;
            color: #052e1d;
        }

        .brand-logo-img {
            width: 480px;
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.18));
        }

        /* ----- TARJETA DEL FORMULARIO ----- */
        .form-col {
            flex: 1;
            max-width: 460px;
            padding: 45px 40px;
            background-color: rgba(245, 247, 248, 0.96);
            border-radius: 12px;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
            position: relative;
            overflow: hidden;
        }

        .form-header h2 {
            font-weight: 700;
            color: #111111;
            font-size: 1.6rem;
            margin-bottom: 2px;
        }

        .form-header .subheading {
            font-size: 0.85rem;
            color: #444444;
            margin-bottom: 8px;
        }

        .divider-line {
            width: 45px;
            height: 3px;
            background-color: #111111;
            margin-bottom: 25px;
            border-radius: 2px;
        }

        .instruction-text {
            font-size: 0.9rem;
            color: #333333;
            margin-bottom: 22px;
        }

        .input-group-custom {
            display: flex;
            align-items: center;
            background-color: #dce3e8;
            border-radius: 6px;
            margin-bottom: 18px;
            padding: 4px 14px;
        }

        .input-group-custom i {
            color: #555555;
            font-size: 1rem;
            width: 28px;
        }

        .input-group-custom input {
            border: none;
            background: transparent;
            width: 100%;
            padding: 10px 6px;
            font-size: 0.95rem;
            color: #222222;
            outline: none;
        }

        .input-group-custom input::placeholder { color: #6c757d; }

        .btn-submit {
            width: 100%;
            background-color: #000000;
            color: #ffffff;
            border: none;
            padding: 12px;
            border-radius: 6px;
            font-size: 0.95rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 8px;
            transition: background 0.2s ease;
        }

        .btn-submit:hover { background-color: #222222; color: #ffffff; }

        .auth-footer { margin-top: 20px; font-size: 0.85rem; color: #333; }

        .auth-footer a {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
            margin-left: 4px;
        }

        .auth-footer a:hover { text-decoration: underline; }

        .leaf-decoration {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 110px;
            opacity: 0.25;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .page-content { flex-direction: column; gap: 20px; }
            .brand-tagline-text { font-size: 1.3rem; }
            .brand-logo-img { width: 280px; }
            .form-col { max-width: 100%; padding: 35px 25px; }
        }
    </style>
</head>
<body>

<div class="page-content">

    <!-- LADO IZQUIERDO: FRASE + LOGO DEBAJO -->
    <div class="brand-side">
        <div class="brand-tagline-text">
            “Innovación y tecnología al servicio del campo,<br>
            fortaleciendo la gestión ganadera y el desarrollo de nuestros productores.” 🐄🌿
        </div>
        <img src="assets/images/pj.jpg" alt="Logo Asociación Ganadera Local de San Juan Evangelista" class="brand-logo-img">
    </div>

    <!-- PANEL DE LOGIN -->
    <div class="form-col">

        <div class="form-header">
            <h2>Sistema Ganadero</h2>
            <p class="subheading">GANADEROS EN ACCIÓN, MUNICIPIO DE UXPANAPA VER.</p>
            <div class="divider-line"></div>
        </div>

        <p class="instruction-text">Inicia sesión para continuar</p>

        <!-- MENSAJE DE ERROR -->
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 fs-6" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- MENSAJE DE ÉXITO -->
        <?php if ($success): ?>
            <div class="alert alert-success py-2 fs-6" role="alert">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <!-- FORMULARIO -->
        <form action="auth/procesar_login.php" method="POST" autocomplete="on">

            <div class="input-group-custom">
                <i class="fa-regular fa-user"></i>
                <input
                    type="text"
                    id="username"
                    name="username"
                    placeholder="Usuario"
                    maxlength="50"
                    required
                    autocomplete="username"
                >
            </div>

            <div class="input-group-custom">
                <i class="fa-solid fa-lock"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Contraseña"
                    required
                    autocomplete="current-password"
                >
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-right-to-bracket"></i> Iniciar sesión
            </button>
        </form>

        <div class="auth-footer">
            ¿No tienes una cuenta? <a href="auth/registro.php">Crear una cuenta</a>
        </div>

        <svg class="leaf-decoration" viewBox="0 0 100 100" fill="#12ec8a">
            <path d="M100,100 C70,90 50,60 50,30 C60,50 80,70 100,100 Z" />
            <path d="M100,100 C80,70 60,40 40,10 C50,30 70,60 100,100 Z" />
        </svg>

    </div>

</div>

</body>
</html>