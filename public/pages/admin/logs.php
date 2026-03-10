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
$pageTitle = 'Logs del Sistema';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>📝 Logs del Sistema</h1>
        <p>Registro de actividades y errores del sistema</p>
    </div>

    <div class="admin-dashboard">
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Registro de Actividad</h2>
                <div style="display: flex; gap: 1rem;">
                    <select style="padding: 0.5rem; border-radius: 6px; border: 1px solid #ddd;">
                        <option>Últimas 24 horas</option>
                        <option>Última semana</option>
                        <option>Último mes</option>
                    </select>
                    <button class="btn btn-secondary">Filtrar</button>
                    <button class="btn btn-primary">Descargar</button>
                </div>
            </div>

            <div style="background: white; border-radius: 12px; padding: 1.5rem;">
                <div style="font-family: monospace; font-size: 0.9rem; line-height: 1.8; color: #333;">
                    <p style="color: #4caf50;">[2026-01-29 10:15:23] INFO: Usuario "Administrador" inició sesión</p>
                    <p style="color: #2196f3;">[2026-01-29 09:45:12] INFO: Sistema iniciado correctamente</p>
                    <p style="color: #4caf50;">[2026-01-29 09:30:45] INFO: Backup automático completado</p>
                    <p style="color: #ff9800;">[2026-01-29 08:22:18] WARNING: Intento de acceso no autorizado bloqueado</p>
                    <p style="color: #4caf50;">[2026-01-29 07:15:33] INFO: Cita agendada por usuario123</p>
                    <hr style="border: none; border-top: 1px solid #f0f0f0; margin: 1rem 0;">
                    <p style="color: #999; text-align: center;">
                        Sistema de logs en desarrollo - Los registros mostrados son ejemplos
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js'];
include '../../../app/views/layouts/footer.php';
?>
