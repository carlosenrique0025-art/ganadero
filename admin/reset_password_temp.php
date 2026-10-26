
        <?php
session_start();

// 1. CARGAR AUTOLOAD DE COMPOSER PRIMERO
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Error crítico: No se encontró el archivo de autocarga de Composer en: " . htmlspecialchars($autoloadPath));
}

require_once $autoloadPath;

// 2. DECLARAR LOS NAMESPACES DE PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 3. CARGAR RECURSOS DEL SISTEMA
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/seguridad.php';

// Validar permisos de Superadmin
$admin_id = require_superadmin($conexion);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

csrf_check();

$usuario_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$usuario_id) {
    header('Location: index.php');
    exit;
}

// Consultar datos del usuario objetivo
$stmt = $conexion->prepare("SELECT id, nombre, email, username FROM usuarios WHERE id = ? LIMIT 1");
$stmt->execute([$usuario_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    // 1. Generar contraseña temporal aleatoria de 8 caracteres
    $clave_temporal = substr(bin2hex(random_bytes(4)), 0, 8);
    $hash = password_hash($clave_temporal, PASSWORD_BCRYPT);

    // 2. Actualizar BD y forzar cambio obligatorio de clave
    $stmtUp = $conexion->prepare("
        UPDATE usuarios 
        SET password = ?, debe_cambiar_password = 1 
        WHERE id = ?
    ");
    $stmtUp->execute([$hash, $usuario_id]);

    // 3. Registrar acción en la auditoría
    registrar_auditoria($conexion, $admin_id, 'RESET_PASSWORD_TEMP', $usuario_id, "Se generó contraseña temporal para {$usuario['nombre']}");

    // 4. Instanciar y enviar correo mediante PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP con Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'carlosenrique.0025@itsjc.edu.mx';         // <-- Tu Gmail aquí
        $mail->Password   = 'teyh hhmv ayan tezr'; // <-- Clave de aplicación de 16 caracteres
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Remitente y Destinatario
        $mail->setFrom('carlosenrique.0025@itsjc.edu.mx', 'Sistema Ganadero');
        $mail->addAddress($usuario['email'], $usuario['nombre']);

        // Mensaje en formato HTML
        $mail->isHTML(true);
        $mail->Subject = 'Clave temporal - Sistema Ganadero';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e8e1; border-radius: 8px;'>
                <h2 style='color: #2d6a4f;'>Asociación Ganadera Local</h2>
                <p>Hola <strong>" . htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                <p>Se ha restablecido tu contraseña del Sistema Ganadero. Utiliza la siguiente clave temporal para ingresar:</p>
                
                <div style='background-color: #f1f5f0; padding: 15px; border-radius: 6px; text-align: center; margin: 20px 0;'>
                    <span style='font-size: 1.4rem; font-weight: bold; letter-spacing: 3px; color: #1b4332;'>" . htmlspecialchars($clave_temporal, ENT_QUOTES, 'UTF-8') . "</span>
                </div>

                <p>Al iniciar sesión con esta clave, el sistema te solicitará cambiarla obligatoriamente por una de tu preferencia.</p>
                <p style='font-size: 0.85rem; color: #777; margin-top: 25px;'>Si no solicitaste este cambio, por favor contacta a la administración.</p>
            </div>
        ";

        $mail->send();
        $_SESSION['mensaje_exito'] = "Contraseña temporal asignada a {$usuario['nombre']} ({$clave_temporal}) y enviada a {$usuario['email']}.";

    } catch (Exception $e) {
        $_SESSION['mensaje_error'] = "Clave generada ({$clave_temporal}), pero falló el envío por correo: {$mail->ErrorInfo}";
    }
}

header('Location: index.php');
exit;