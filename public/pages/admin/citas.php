<?php
require_once '../../../app/config/config.php';
require_once '../../../app/controllers/AuthController.php';

$authController = new AuthController();

if (!$authController->isAuthenticated()) {
    redirect('login');
}

if (!isAdmin()) {
    redirect('dashboard');
}

$user = $authController->getCurrentUser();
$currentPage = 'admin';
$pageTitle = 'Gestión de Citas';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Gestión de Citas</h1>
        <p>Ver y administrar todas las citas del sistema</p>
    </div>

    <div class="admin-dashboard">
        <!-- Stats -->
        <div class="admin-stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background: #fff3e0;">
                    <span style="font-size: 1.5rem;">📅</span>
                </div>
                <div class="stat-content">
                    <h3>TOTAL CITAS</h3>
                    <p class="stat-number" id="totalCitas">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #e8f5e9;">
                    <span style="font-size: 1.5rem;">✅</span>
                </div>
                <div class="stat-content">
                    <h3>PRÓXIMAS</h3>
                    <p class="stat-number" id="proximasCitas">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #fce4ec;">
                    <span style="font-size: 1.5rem;">📆</span>
                </div>
                <div class="stat-content">
                    <h3>HOY</h3>
                    <p class="stat-number" id="citasHoy">0</p>
                </div>
            </div>
        </div>

        <!-- Tabla de citas -->
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Todas las Citas</h2>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cita</th>
                            <th>Paciente</th>
                            <th>Fecha</th>
                            <th>Profesional</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="citasBody">
                        <tr>
                            <td colspan="7" class="empty-message">
                                Cargando citas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin-citas.js'];
include '../../../app/views/layouts/footer.php';
?>
