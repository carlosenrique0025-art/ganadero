<?php
session_start();

require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../index.php');
    exit;
}

$db = null;
foreach (get_defined_vars() as $valorVar) {
    if ($valorVar instanceof PDO || $valorVar instanceof mysqli) {
        $db = $valorVar;
        break;
    }
}

$usuario_id = (int)($_SESSION['usuario_id'] ?? 0);
$rancho_id  = (int)($_SESSION['rancho_id'] ?? 0);

/* Ejecuta una consulta preparada con PDO o mysqli */
function ejecutar($db, string $sql, array $params, string $tipos): void {
    if ($db instanceof PDO) {
        $db->prepare($sql)->execute($params);
    } elseif ($db instanceof mysqli) {
        $stmt = $db->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
    }
}

/* Crea las columnas logo_escala, logo_x y logo_y si todavía no existen */
function asegurarColumnaEscala($db): void {
    $columnas = [
        'logo_escala' => 100,
        'logo_x'      => 0,
        'logo_y'      => 0,
    ];
    foreach ($columnas as $col => $defecto) {
        try {
            $existe = false;
            if ($db instanceof PDO) {
                $existe = (bool)$db->query("SHOW COLUMNS FROM `ranchos` LIKE '$col'")->fetch();
            } elseif ($db instanceof mysqli) {
                $r = $db->query("SHOW COLUMNS FROM `ranchos` LIKE '$col'");
                $existe = $r && $r->num_rows > 0;
            }
            if (!$existe) {
                $db->query("ALTER TABLE `ranchos` ADD COLUMN `$col` INT NOT NULL DEFAULT $defecto");
            }
        } catch (Throwable $e) {}
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Tamaño del logo (30% a 200%)
    $escala = (int)($_POST['logo_escala'] ?? 100);
    $escala = max(30, min(200, $escala));

    // Posición del logo (en píxeles, respecto al centro del recuadro)
    $posX = max(-150, min(150, (int)($_POST['logo_x'] ?? 0)));
    $posY = max(-80,  min(80,  (int)($_POST['logo_y'] ?? 0)));

    $hayArchivo = isset($_FILES['logo_rancho']) && $_FILES['logo_rancho']['error'] !== UPLOAD_ERR_NO_FILE;
    $ruta_relativa = null;
    $error = null;

    if ($hayArchivo) {
        if ($_FILES['logo_rancho']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath   = $_FILES['logo_rancho']['tmp_name'];
            $fileName      = $_FILES['logo_rancho']['name'];
            $fileSize      = $_FILES['logo_rancho']['size'];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            $fileMimeType  = mime_content_type($fileTmpPath) ?: '';

            $extensiones_permitidas = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'ico', 'svg', 'bmp', 'tif', 'tiff', 'jfif'];

            if (in_array($fileExtension, $extensiones_permitidas, true) || str_starts_with($fileMimeType, 'image/')) {
                if ($fileSize <= 5 * 1024 * 1024) {
                    if ($fileExtension === '') {
                        $fileExtension = 'png';
                    }
                    $nuevo_nombre = 'rancho_' . ($rancho_id > 0 ? $rancho_id : 'usr_' . $usuario_id) . '_' . time() . '.' . $fileExtension;
                    $directorio_destino = __DIR__ . '/../uploads/logos/';

                    if (!is_dir($directorio_destino)) {
                        mkdir($directorio_destino, 0755, true);
                    }

                    if (move_uploaded_file($fileTmpPath, $directorio_destino . $nuevo_nombre)) {
                        $ruta_relativa = 'uploads/logos/' . $nuevo_nombre;
                    } else {
                        $error = "Error al mover el archivo al servidor.";
                    }
                } else {
                    $error = "El archivo es muy pesado. El límite máximo es de 5MB.";
                }
            } else {
                $error = "El archivo seleccionado no es un formato de imagen válido.";
            }
        } else {
            $error = "Error al cargar el archivo de imagen.";
        }
    }

    if ($error !== null) {
        $_SESSION['mensaje_error'] = $error;
    } elseif ($rancho_id <= 0) {
        $_SESSION['mensaje_error'] = "No hay un rancho asociado a tu usuario.";
    } else {
        try {
            asegurarColumnaEscala($db);

            if ($ruta_relativa !== null) {
                ejecutar($db, "UPDATE ranchos SET logo = ?, logo_escala = ?, logo_x = ?, logo_y = ? WHERE id = ?", [$ruta_relativa, $escala, $posX, $posY, $rancho_id], "siiii");
                $_SESSION['logo_rancho'] = $ruta_relativa;
            } else {
                ejecutar($db, "UPDATE ranchos SET logo_escala = ?, logo_x = ?, logo_y = ? WHERE id = ?", [$escala, $posX, $posY, $rancho_id], "iiii");
            }

            $_SESSION['logo_escala'] = $escala;
            $_SESSION['logo_x'] = $posX;
            $_SESSION['logo_y'] = $posY;
            $_SESSION['mensaje_exito'] = $ruta_relativa !== null
                ? "Logo actualizado con éxito."
                : "Tamaño del logo actualizado con éxito.";
        } catch (Throwable $e) {
            $_SESSION['mensaje_error'] = "No se pudo guardar en la base de datos.";
        }
    }
}

header('Location: index.php');
exit;