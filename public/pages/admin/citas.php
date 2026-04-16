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
        <p>Ver y administrar todas las citas y solicitudes del sistema</p>
    </div>

    <div class="admin-dashboard">
        <!-- Stats -->
        <div class="admin-stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="background:#fff3e0;">
                    <span style="font-size:1.5rem;">📅</span>
                </div>
                <div class="stat-content">
                    <h3>TOTAL CITAS</h3>
                    <p class="stat-number" id="totalCitas">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#e8f5e9;">
                    <span style="font-size:1.5rem;">✅</span>
                </div>
                <div class="stat-content">
                    <h3>PRÓXIMAS</h3>
                    <p class="stat-number" id="proximasCitas">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#fce4ec;">
                    <span style="font-size:1.5rem;">📆</span>
                </div>
                <div class="stat-content">
                    <h3>HOY</h3>
                    <p class="stat-number" id="citasHoy">0</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background:#ede7f6;">
                    <span style="font-size:1.5rem;">⏳</span>
                </div>
                <div class="stat-content">
                    <h3>SOLICITUDES PENDIENTES</h3>
                    <p class="stat-number" id="solicitudesPendientes">0</p>
                </div>
            </div>
        </div>

        <!-- Filtros + búsqueda -->
        <div class="admin-section" style="width:100%;">
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:1.2rem;">
                <h2 style="margin:0;">Todas las Citas</h2>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <input type="text" id="searchCitas" placeholder="Buscar por paciente o tipo..." style="padding:7px 12px;border:1px solid #ddd;border-radius:6px;font-size:0.87rem;min-width:220px;">
                    <button class="btn-filter active" data-filter="all"     onclick="setFilter('all',this)">Todas</button>
                    <button class="btn-filter"         data-filter="hoy"    onclick="setFilter('hoy',this)">Hoy</button>
                    <button class="btn-filter"         data-filter="proxima" onclick="setFilter('proxima',this)">Próximas</button>
                    <button class="btn-filter"         data-filter="pasada" onclick="setFilter('pasada',this)">Pasadas</button>
                    <button class="btn-filter"         data-filter="solicitud" onclick="setFilter('solicitud',this)">Solicitudes</button>
                    <button class="btn btn-secondary btn-sm" onclick="loadAllAppointments()">↻ Recargar</button>
                </div>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo / Título</th>
                            <th>Paciente</th>
                            <th>Fecha y Hora</th>
                            <th>Profesional</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="citasBody">
                        <tr><td colspan="7" class="empty-message">Cargando citas...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="adminCitaToast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;min-width:240px;padding:12px 18px;border-radius:8px;font-size:0.9rem;font-weight:600;box-shadow:0 4px 16px rgba(0,0,0,.15);"></div>

<style>
.btn-filter {
    padding: 5px 14px;
    border: 1px solid #ddd;
    background: #fff;
    border-radius: 20px;
    cursor: pointer;
    font-size: 0.82rem;
    font-weight: 600;
    color: #555;
    transition: all .15s;
}
.btn-filter:hover { background: #f5f5f5; }
.btn-filter.active { background: #ff6b35; color: #fff; border-color: #ff6b35; }
.badge-solicitud { background:#ede7f6;color:#5e35b1;border-radius:4px;padding:2px 7px;font-size:0.75rem;font-weight:700; }
.badge-pendiente { background:#fff8e1;color:#f57f17; border-radius:4px;padding:2px 7px;font-size:0.75rem;font-weight:700; }
.badge-aceptada  { background:#e8f5e9;color:#2e7d32; border-radius:4px;padding:2px 7px;font-size:0.75rem;font-weight:700; }
.badge-denegada  { background:#fce4ec;color:#c62828; border-radius:4px;padding:2px 7px;font-size:0.75rem;font-weight:700; }
.badge-reasignada{ background:#e3f2fd;color:#1565c0; border-radius:4px;padding:2px 7px;font-size:0.75rem;font-weight:700; }
</style>

<?php
$additionalJS = ['admin-citas.js'];
include '../../../app/views/layouts/footer.php';
?>
