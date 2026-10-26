<?php
session_start();

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | Sistema Ganadero</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lora:ital,wght@1,600;1,700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ----- FONDO VERDE CLARO ----- */
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

        /* ----- LADO IZQUIERDO: LOGO + FRASE (CENTRADOS) ----- */
        .brand-side {
            flex: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            gap: 30px;
        }

        /* Logo transparente: solo se ve el dibujo y las letras */
        .brand-logo-img {
            width: 480px;
            max-width: 100%;
            height: auto;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.18));
        }

        .brand-tagline-text {
            font-family: 'Lora', Georgia, 'Times New Roman', serif;
            font-style: italic;
            font-size: 1.6rem;
            font-weight: 600;
            line-height: 1.4;
            color: #052e1d;
        }

        /* ----- TARJETA DEL FORMULARIO ----- */
        .form-col {
            flex: 1;
            max-width: 460px;
            padding: 35px 38px;
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
            margin-bottom: 18px;
            border-radius: 2px;
        }

        .instruction-text {
            font-size: 0.9rem;
            color: #333333;
            margin-bottom: 16px;
        }

        .input-group-custom {
            display: flex;
            align-items: center;
            background-color: #dce3e8;
            border-radius: 6px;
            margin-bottom: 13px;
            padding: 2px 14px;
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

        .btn-submit:hover { background-color: #222222; }

        .auth-footer { margin-top: 18px; font-size: 0.85rem; color: #333; }

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
            width: 100px;
            opacity: 0.25;
            pointer-events: none;
        }

        @media (max-width: 900px) {
            .page-content { flex-direction: column; gap: 20px; }
            .brand-tagline-text { font-size: 1.3rem; }
            .brand-logo-img { width: 280px; }
            .form-col { max-width: 100%; padding: 30px 25px; }
        }
    </style>
</head>
<body>

<div class="page-content">

    <div class="brand-side">
        <img src="../assets/images/dd.png" alt="Logo Asociación Ganadera Local de San Juan Evangelista" class="brand-logo-img">
        <div class="brand-tagline-text">
            “Innovación y tecnología al servicio del campo,<br>
            fortaleciendo la gestión ganadera y el desarrollo de nuestros productores.” 🐄🌿
        </div>
    </div>

    <div class="form-col">

        <div class="form-header">
            <h2>Crear cuenta</h2>
            <p class="subheading">Asociación Ganadera Local de San Juan Evangelista</p>
            <div class="divider-line"></div>
        </div>

        <p class="instruction-text">Completa tus datos para registrarte</p>

        <?php if ($error): ?>
            <div class="alert alert-danger py-2 fs-6" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form action="procesar_registro.php" method="POST" autocomplete="on">

            <div class="input-group-custom">
                <i class="fa-solid fa-house-chimney"></i>
                <input type="text" id="nombre_rancho" name="nombre_rancho"
                       placeholder="Nombre del rancho (Ej. Rancho El Paraíso)"
                       maxlength="150" required>
            </div>

            <div class="input-group-custom">
                <i class="fa-regular fa-id-card"></i>
                <input type="text" id="nombre" name="nombre"
                       placeholder="Nombre completo"
                       maxlength="150" required autocomplete="name">
            </div>

            <div class="input-group-custom">
                <i class="fa-regular fa-envelope"></i>
                <input type="email" id="email" name="email"
                       placeholder="Correo electrónico"
                       maxlength="150" required autocomplete="email">
            </div>

            <div class="input-group-custom">
                <i class="fa-regular fa-user"></i>
                <input type="text" id="username" name="username"
                       placeholder="Nombre de usuario"
                       maxlength="50" required autocomplete="username">
            </div>

            <div class="input-group-custom">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password" name="password"
                       placeholder="Contraseña (mínimo 8 caracteres)"
                       minlength="8" required autocomplete="new-password">
            </div>

            <div class="input-group-custom">
                <i class="fa-solid fa-lock"></i>
                <input type="password" id="password_confirm" name="password_confirm"
                       placeholder="Repite la contraseña"
                       minlength="8" required autocomplete="new-password">
            </div>

            <button type="submit" class="btn-submit">
                <i class="fa-solid fa-user-plus"></i> Registrar cuenta
            </button>
        </form>

        <div class="auth-footer">
            ¿Ya tienes cuenta? <a href="../index.php">Iniciar sesión</a>
        </div>

        <svg class="leaf-decoration" viewBox="0 0 100 100" fill="#12ec8a">
            <path d="M100,100 C70,90 50,60 50,30 C60,50 80,70 100,100 Z" />
            <path d="M100,100 C80,70 60,40 40,10 C50,30 70,60 100,100 Z" />
        </svg>

    </div>

</div>

</body>
</html>