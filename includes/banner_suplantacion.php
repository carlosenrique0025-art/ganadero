<?php
// Si la sesión no ha iniciado, se inicia
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Muestra el banner SOLO si el superadmin está dentro de la cuenta de un usuario
if (!empty($_SESSION['admin_original'])): 
?>
    <div style="background-color: #d97706; color: #ffffff; padding: 10px 16px; display: flex; justify-content: space-between; align-items: center; font-family: sans-serif; font-size: 0.9rem; position: sticky; top: 0; z-index: 9999;">
        <div>
            <strong>Modo Suplantación Activo:</strong> Estás navegando como el usuario <strong><?= htmlspecialchars($_SESSION['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>.
        </div>
        <form method="post" action="../admin/salir_suplantacion.php" style="margin: 0;">
            <button type="submit" style="background-color: #ffffff; color: #d97706; border: none; padding: 6px 12px; font-weight: bold; border-radius: 4px; cursor: pointer;">
                Volver a Superadmin
            </button>
        </form>
    </div>
<?php endif; ?>