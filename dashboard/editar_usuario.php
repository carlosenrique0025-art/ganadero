<?php

require_once __DIR__ . '/../includes/seguridad.php';
require_once __DIR__ . '/../config/database.php';

$admin_id = require_superadmin($conexion);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

function cargarUsuario(PDO $conexion, int $id) {
    $stmt = $conexion->prepare("
        SELECT u.id, u.rancho_id, u.nombre, u.email, u.username, u.rol, u.estado,
               r.nombre AS rancho
        FROM usuarios u
        LEFT JOIN ranchos r ON r.id = u.rancho_id
        WHERE u.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$u = cargarUsuario($conexion, $id);

if (!$u) {
    $_SESSION['flash_admin'] = ['tipo' => 'error', 'msg' => 'El usuario no existe.'];
    header('Location: index.php');
    exit;
}

if ($u['rol'] === 'superadmin') {
    $_SESSION['flash_admin'] = ['tipo' => 'error', 'msg' => 'No se puede editar a un administrador del sistema desde aquí.'];
    header('Location: index.php');
    exit;
}

$errores = [];
$exito = null;
$claveTemporal = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    $accion = $_POST['accion'] ?? '';

    /*
    |----------------------------------------------------------------------
    | GUARDAR CAMBIOS
    |----------------------------------------------------------------------
    */
    if ($accion === 'guardar') {

        $nombre   = trim($_POST['nombre'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $rol      = $_POST['rol'] ?? '';
        $estado   = (int)($_POST['estado'] ?? 1) === 1 ? 1 : 0;

        if ($nombre === '' || mb_strlen($nombre) > 150) {
            $errores[] = 'El nombre es obligatorio (máximo 150 caracteres).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El correo electrónico no es válido.';
        }
        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
            $errores[] = 'El usuario debe tener de 3 a 50 caracteres: letras, números, punto, guion o guion bajo.';
        }
        if (!in_array($rol, ['admin', 'usuario'], true)) {
            $errores[] = 'El rol no es válido.';
        }

        if (!$errores) {
            $dup = $conexion->prepare("
                SELECT id FROM usuarios
                WHERE (username = :username OR email = :email) AND id <> :id
                LIMIT 1
            ");
            $dup->execute([':username' => $username, ':email' => $email, ':id' => $id]);
            if ($dup->fetch()) {
                $errores[] = 'Ese usuario o correo ya lo usa otra cuenta.';
            }
        }

        if (!$errores) {

            $cambios = [];
            foreach (['nombre' => $nombre, 'email' => $email, 'username' => $username, 'rol' => $rol, 'estado' => $estado] as $campo => $nuevo) {
                if ((string)$u[$campo] !== (string)$nuevo) {
                    $cambios[] = $campo;
                }
            }

            $upd = $conexion->prepare("
                UPDATE usuarios
                SET nombre = :nombre, email = :email, username = :username, rol = :rol, estado = :estado
                WHERE id = :id
            ");
            $upd->execute([
                ':nombre' => $nombre, ':email' => $email, ':username' => $username,
                ':rol' => $rol, ':estado' => $estado, ':id' => $id,
            ]);

            registrar_auditoria(
                $conexion, $admin_id, 'editar_usuario', $id,
                $cambios ? 'Campos: ' . implode(', ', $cambios) : 'Sin cambios'
            );

            $exito = $cambios ? 'Cambios guardados.' : 'No había nada que cambiar.';
            $u = cargarUsuario($conexion, $id);
        }
    }

    /*
    |----------------------------------------------------------------------
    | RESTABLECER CONTRASEÑA (genera una temporal; nunca se ve la actual)
    |----------------------------------------------------------------------
    */
    if ($accion === 'reset_password') {

        $alfabeto = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        $temp = '';
        for ($i = 0; $i < 10; $i++) {
            $temp .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }

        $upd = $conexion->prepare("
            UPDATE usuarios
            SET password = :password, debe_cambiar_password = 1
            WHERE id = :id
        ");
        $upd->execute([':password' => password_hash($temp, PASSWORD_DEFAULT), ':id' => $id]);

        registrar_auditoria($conexion, $admin_id, 'restablecer_password', $id, 'Contraseña temporal generada');

        $claveTemporal = $temp;
    }
}

$titulo = 'Editar usuario';
require __DIR__ . '/_cabecera.php';
?>

<h1>Editar usuario</h1>
<p class="sub">Rancho: <strong><?= e($u['rancho'] ?? 'Sin rancho') ?></strong></p>

<?php if ($errores): ?>
    <div class="msg error"><?= implode('<br>', array_map('e', $errores)) ?></div>
<?php endif; ?>

<?php if ($exito): ?>
    <div class="msg ok"><?= e($exito) ?></div>
<?php endif; ?>

<div class="tarjeta">
    <h2>Datos de la cuenta</h2>

    <form method="post">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">

        <div class="fila2">
            <div>
                <label for="nombre">Nombre completo</label>
                <input type="text" id="nombre" name="nombre" maxlength="150" required value="<?= e($u['nombre']) ?>">
            </div>
            <div>
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" maxlength="50" required value="<?= e($u['username']) ?>">
            </div>
        </div>

        <label for="email">Correo electrónico</label>
        <input type="email" id="email" name="email" maxlength="150" required value="<?= e($u['email']) ?>">

        <div class="fila2">
            <div>
                <label for="rol">Rol</label>
                <select id="rol" name="rol">
                    <option value="admin"   <?= $u['rol'] === 'admin'   ? 'selected' : '' ?>>admin (dueño del rancho)</option>
                    <option value="usuario" <?= $u['rol'] === 'usuario' ? 'selected' : '' ?>>usuario</option>
                </select>
            </div>
            <div>
                <label for="estado">Estado de la cuenta</label>
                <select id="estado" name="estado">
                    <option value="1" <?= (int)$u['estado'] === 1 ? 'selected' : '' ?>>Activa</option>
                    <option value="0" <?= (int)$u['estado'] !== 1 ? 'selected' : '' ?>>Desactivada</option>
                </select>
            </div>
        </div>

        <div class="acciones" style="margin-top:20px;">
            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn gris" href="index.php">Volver a la lista</a>
        </div>
    </form>
</div>

<div class="tarjeta">
    <h2>¿Olvidó su contraseña?</h2>
    <p class="sub" style="margin-bottom:12px;">
        Nadie puede ver la contraseña actual. Se genera una temporal y el usuario
        tendrá que cambiarla al iniciar sesión.
    </p>

    <?php if ($claveTemporal !== null): ?>
        <div class="msg ok">
            Contraseña temporal creada. <strong>Se muestra una sola vez</strong>, pásasela al usuario:
            <div class="clave-temp"><?= e($claveTemporal) ?></div>
        </div>
    <?php endif; ?>

    <form method="post" onsubmit="return confirm('¿Generar una contraseña temporal? La actual dejará de funcionar.');">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="reset_password">
        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
        <button class="btn naranja" type="submit">Generar contraseña temporal</button>
    </form>
</div>

</div>
</body>
</html>
