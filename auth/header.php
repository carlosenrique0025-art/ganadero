<?php
/* Cabecera común del panel. Antes de incluirla define $titulo. */
$flash = $_SESSION['flash_admin'] ?? null;
unset($_SESSION['flash_admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo ?? 'Administración') ?> | Sistema Ganadero</title>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f1f5f0;
            color: #1c2a22;
        }

        a { color: inherit; text-decoration: none; }
        :focus-visible { outline: 3px solid #0ad451; outline-offset: 2px; }

        .barra {
            background: #1b4332;
            color: #fff;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
        }

        .barra strong { font-size: 1.05rem; }
        .barra nav { display: flex; gap: 8px; flex-wrap: wrap; }

        .barra nav a {
            padding: 8px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            background: rgba(255, 255, 255, 0.12);
        }

        .barra nav a:hover { background: rgba(255, 255, 255, 0.25); }

        .contenido { max-width: 1150px; margin: 0 auto; padding: 28px 20px 50px; }

        h1 { font-size: 1.5rem; color: #1b4332; margin-bottom: 4px; }
        .sub { color: #5b6b61; font-size: 0.92rem; margin-bottom: 20px; }

        .tarjeta {
            background: #fff;
            border: 1px solid #e0e8e1;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 20px;
        }

        .tarjeta h2 { font-size: 1.05rem; margin-bottom: 14px; }

        .msg { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-size: 0.92rem; }
        .msg.ok { background: #d8ead9; color: #14532d; border: 1px solid #b6d8b8; }
        .msg.error { background: #f8d7da; color: #842029; border: 1px solid #f5c2c7; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; font-size: 0.8rem; color: #5b6b61; background: #f7faf6; padding: 11px 14px; }
        td { padding: 12px 14px; border-top: 1px solid #e0e8e1; vertical-align: middle; }
        .tabla { overflow-x: auto; }

        .etiqueta {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            background: #d8ead9;
            color: #1b4332;
        }

        .etiqueta.off { background: #f8d7da; color: #842029; }
        .etiqueta.rol { background: #e0e7ff; color: #3730a3; }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            background: #2d6a4f;
            color: #fff;
            font-family: inherit;
        }

        .btn:hover { background: #1b4332; }
        .btn.gris { background: #e5ebe6; color: #1c2a22; }
        .btn.gris:hover { background: #d4ddd5; }
        .btn.naranja { background: #c2410c; }
        .btn.naranja:hover { background: #9a3412; }

        .acciones { display: flex; gap: 6px; flex-wrap: wrap; }
        .acciones form { margin: 0; }

        label { display: block; font-size: 0.85rem; font-weight: 600; margin: 14px 0 5px; }

        input[type=text], input[type=email], input[type=password], select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cdd8cf;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            background: #fff;
        }

        .fila2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0 18px; }

        .buscador { display: flex; gap: 8px; margin-bottom: 18px; flex-wrap: wrap; }
        .buscador input { flex: 1; min-width: 220px; }

        .clave-temp {
            font-family: Consolas, monospace;
            font-size: 1.4rem;
            letter-spacing: 0.08em;
            background: #fff7e0;
            border: 1px dashed #d4a017;
            padding: 12px 16px;
            border-radius: 10px;
            display: inline-block;
            margin: 8px 0;
        }

        @media (max-width: 700px) { .fila2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="barra">
    <strong>Administración del sistema</strong>
    <nav>
        <a href="index.php">Usuarios</a>
        <a href="../dashboard/index.php">Mi panel</a>
        <a href="../auth/logout.php">Cerrar sesión</a>
    </nav>
</div>

<div class="contenido">

<?php if ($flash): ?>
    <div class="msg <?= $flash['tipo'] === 'ok' ? 'ok' : 'error' ?>"><?= e($flash['msg']) ?></div>
<?php endif; ?>
