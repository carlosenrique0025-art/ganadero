<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// 1. Validar que la persona logueada sea superadmin
$admin_id = require_superadmin($conexion);

// Mensajes de sesión
$mensaje_exito = $_SESSION['mensaje_exito'] ?? null;
$mensaje_error = $_SESSION['mensaje_error'] ?? null;
unset($_SESSION['mensaje_exito'], $_SESSION['mensaje_error']);

// 2. Consultar todos los usuarios y sus ranchos
$stmtUsuarios = $conexion->query("
    SELECT u.id, u.nombre, u.username, u.email, u.rol, u.estado, r.nombre AS rancho, r.estado AS estado_rancho
    FROM usuarios u
    LEFT JOIN ranchos r ON r.id = u.rancho_id
    ORDER BY u.id DESC
");
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

// 3. Consultar las últimas 15 acciones de la bitácora de auditoría
$stmtAuditoria = $conexion->query("
    SELECT a.*, u_admin.nombre AS admin_nombre, u_obj.nombre AS objetivo_nombre
    FROM auditoria_admin a
    LEFT JOIN usuarios u_admin ON u_admin.id = a.admin_id
    LEFT JOIN usuarios u_obj ON u_obj.id = a.usuario_objetivo_id
    ORDER BY a.creado_en DESC
    LIMIT 15
");
$auditoria = $stmtAuditoria->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración | Sistema Ganadero</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --verde-oscuro: #1b4332;
            --verde: #2d6a4f;
            --fondo: #f1f5f0;
            --tarjeta: #ffffff;
            --texto: #1c2a22;
            --texto-suave: #5b6b61;
            --borde: #e0e8e1;
            --naranja: #d97706;
            --rojo: #ef4444;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--fondo); color: var(--texto); padding: 30px; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px; }
        .header h1 { font-size: 1.8rem; color: var(--verde-oscuro); }
        .header-acciones { display: flex; gap: 10px; }
        .panel { background: var(--tarjeta); border: 1px solid var(--borde); border-radius: 12px; padding: 20px; margin-bottom: 30px; overflow-x: auto; }
        .panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .panel h2 { font-size: 1.2rem; color: var(--verde); display: flex; align-items: center; gap: 8px; margin: 0; }
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th { text-align: left; padding: 12px; background: #f7faf6; color: var(--texto-suave); font-weight: 600; border-bottom: 1px solid var(--borde); }
        td { padding: 12px; border-bottom: 1px solid var(--borde); }
        .etiqueta { padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: bold; background: #d8ead9; color: var(--verde-oscuro); }
        .etiqueta.off { background: #fde8e8; color: #9b1c1c; }
        .etiqueta.rol { background: #e1effe; color: #1e429f; }
        .etiqueta.super { background: #fef3c7; color: #92400e; }
        .btn { padding: 8px 12px; border-radius: 6px; font-size: 0.82rem; border: none; cursor: pointer; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 4px; }
        .btn.verde { background: var(--verde); color: white; }
        .btn.naranja { background: var(--naranja); color: white; }
        .btn.rojo { background: var(--rojo); color: white; }
        .btn.rojo:hover { background: #dc2626; }
        .btn.gris { background: #e5e7eb; color: #374151; }
        .btn.azul { background: #3b82f6; color: white; }
        .acciones { display: flex; gap: 6px; align-items: center; }
        .alerta { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
        .alerta-exito { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alerta-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
    </style>
</head>
<body>

<div class="container">
    
    <div class="header">
        <h1><i class="fa-solid fa-user-shield"></i> Panel Administrador del Sistema</h1>
        <div class="header-acciones">
            <a class="btn verde" href="perfil.php"><i class="fa-solid fa-user-gear"></i> Mi Perfil / Clave</a>
            <a class="btn gris" href="../dashboard/index.php"><i class="fa-solid fa-house"></i> Ir a mi Dashboard</a>
        </div>
    </div>

    <!-- NOTIFICACIONES -->
    <?php if ($mensaje_exito): ?>
        <div class="alerta alerta-exito">
            <i class="fa-solid fa-circle-check"></i> <?= e($mensaje_exito) ?>
        </div>
    <?php endif; ?>

    <?php if ($mensaje_error): ?>
        <div class="alerta alerta-error">
            <i class="fa-solid fa-circle-exclamation"></i> <?= e($mensaje_error) ?>
        </div>
    <?php endif; ?>

    <!-- TABLA DE USUARIOS DEL SISTEMA -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-users"></i> Gestión de Cuentas de Usuario</h2>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rancho</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <?php $esMismoUsuario = ((int)$u['id'] === (int)$admin_id); ?>
                <tr>
                    <td><?= e($u['nombre']) ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td><?= e($u['rancho'] ?? 'Sin rancho') ?></td>
                    <td>
                        <span class="etiqueta <?= $u['rol'] === 'superadmin' ? 'super' : 'rol' ?>">
                            <?= e($u['rol']) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ((int)$u['estado'] === 1): ?>
                            <span class="etiqueta">Activo</span>
                        <?php else: ?>
                            <span class="etiqueta off">Desactivado</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($esMismoUsuario): ?>
                            <a class="btn verde" href="perfil.php" title="Mi Cuenta Actual">
                                <i class="fa-solid fa-user-check"></i> Mi Cuenta
                            </a>
                        <?php else: ?>
                            <div class="acciones">
                                <!-- BOTÓN SUPLANTAR -->
                                <?php if ((int)$u['estado'] === 1): ?>
                                    <form method="post" action="entrar.php" style="display:inline;" onsubmit="return confirm('¿Entrar a la cuenta de <?= e(addslashes($u['nombre'])) ?>?');">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                        <button class="btn naranja" type="submit" title="Suplantar usuario">
                                            <i class="fa-solid fa-user-secret"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- BOTÓN EDITAR (Permite cambiar Rol a Usuario/Admin/Superadmin y clave) -->
                                <a class="btn gris" href="editar_usuario.php?id=<?= (int)$u['id'] ?>" title="Editar datos y cambiar Rol">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                                <!-- BOTÓN CLAVE TEMPORAL VIA EMAIL -->
                                <form method="post" action="reset_password_temp.php" style="display:inline;" onsubmit="return confirm('¿Enviar clave temporal por correo a <?= e(addslashes($u['nombre'])) ?>?');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button class="btn azul" type="submit" title="Asignar clave temporal por correo">
                                        <i class="fa-solid fa-key"></i>
                                    </button>
                                </form>

                                <!-- BOTÓN ELIMINAR -->
                                <form method="post" action="eliminar_usuario.php" style="display:inline;" onsubmit="return confirm('¿Estás seguro de ELIMINAR permanentemente a <?= e(addslashes($u['nombre'])) ?>?');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                    <button class="btn rojo" type="submit" title="Eliminar cuenta">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- TABLA DE BITÁCORA DE AUDITORÍA -->
    <div class="panel">
        <div class="panel-header">
            <h2><i class="fa-solid fa-list-check"></i> Bitácora de Auditoría (Últimos movimientos)</h2>
            
            <?php if ($auditoria): ?>
                <form method="post" action="limpiar_auditoria.php" onsubmit="return confirm('¿Estás seguro de VACIAR la bitácora de auditoría?');">
                    <?= csrf_campo() ?>
                    <button class="btn rojo" type="submit">
                        <i class="fa-solid fa-broom"></i> Limpiar Bitácora
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Administrador</th>
                    <th>Acción</th>
                    <th>Usuario Objetivo</th>
                    <th>Detalle</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($auditoria): ?>
                <?php foreach ($auditoria as $a): ?>
                    <tr>
                        <td><?= e($a['creado_en']) ?></td>
                        <td><strong><?= e($a['admin_nombre'] ?? 'ID: ' . $a['admin_id']) ?></strong></td>
                        <td><span class="etiqueta rol"><?= e($a['accion']) ?></span></td>
                        <td><?= e($a['objetivo_nombre'] ?? ($a['usuario_objetivo_id'] ? 'ID: ' . $a['usuario_objetivo_id'] : 'N/A')) ?></td>
                        <td><?= e($a['detalle']) ?></td>
                        <td><small><?= e($a['ip']) ?></small></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align:center; color: #5b6b61; padding: 20px;">
                        Aún no hay registros en la bitácora de auditoría.
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>