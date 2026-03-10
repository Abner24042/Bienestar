<?php
require_once '../../../app/config/config.php';
require_once '../../../app/controllers/AuthController.php';

$authController = new AuthController();

// Verificar autenticación
if (!$authController->isAuthenticated()) {
    redirect('login');
}

// Verificar que sea administrador
if (!isAdmin()) {
    redirect('dashboard');
}

$user = $authController->getCurrentUser();
$currentPage = 'admin';
$pageTitle = 'Configuración del Sistema';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>⚙️ Configuración del Sistema</h1>
        <p>Ajustes generales y parámetros del sistema</p>
    </div>

    <div class="admin-dashboard">
        <div class="admin-sections">
            <div class="admin-section">
                <h2>General</h2>
                <p>Configuraciones básicas del sistema</p>
                <div class="section-actions">
                    <button class="btn btn-secondary">Editar</button>
                </div>
            </div>

            <div class="admin-section">
                <h2>Notificaciones</h2>
                <p>Configurar emails y alertas</p>
                <div class="section-actions">
                    <button class="btn btn-secondary">Configurar</button>
                </div>
            </div>

            <div class="admin-section">
                <h2>Seguridad</h2>
                <p>Opciones de seguridad y permisos</p>
                <div class="section-actions">
                    <button class="btn btn-secondary">Administrar</button>
                </div>
            </div>

            <div class="admin-section">
                <h2>Base de Datos</h2>
                <p>Backup y mantenimiento</p>
                <div class="section-actions">
                    <button class="btn btn-secondary">Ver Opciones</button>
                </div>
            </div>
        </div>

        <div class="admin-activity">
            <h2>📋 Información del Sistema</h2>
            <div style="background: #f9f9f9; padding: 1.5rem; border-radius: 8px; margin-top: 1rem;">
                <p style="margin: 0.5rem 0;"><strong>Versión:</strong> 1.0.0</p>
                <p style="margin: 0.5rem 0;"><strong>PHP:</strong> <?php echo phpversion(); ?></p>
                <p style="margin: 0.5rem 0;"><strong>Servidor:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconocido'; ?></p>
                <p style="margin: 0.5rem 0;"><strong>Base de Datos:</strong> MySQL</p>
                <p style="margin: 0.5rem 0; color: #999; font-size: 0.9rem;">
                    Funcionalidad de configuración en desarrollo
                </p>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js'];
include '../../../app/views/layouts/footer.php';
?>
