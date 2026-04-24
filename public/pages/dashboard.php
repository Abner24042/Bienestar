<?php
$pageTitle = 'Dashboard';
$currentPage = 'dashboard';
$additionalCSS = ['dashboard.css', 'mi-plan.css', 'chat.css'];
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();

// Verificar autenticación
if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();

include '../../app/views/layouts/header.php';
?>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="page-header">
        <h1>Bienvenido, <?php echo htmlspecialchars(explode(' ', $user['nombre'])[0]); ?> <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-left:2px;"><circle cx="12" cy="12" r="10"/><path d="M8 13s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg></h1>
        <p>Aquí está tu resumen de bienestar de hoy</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid" id="homeStatsGrid">
        <div class="stat-card" id="mentalStatCard" tabindex="0" role="button" aria-label="Estado Mental - ir a Salud Mental" style="cursor:pointer;" onclick="window.location.href='<?php echo url('salud-mental'); ?>'">
            <div class="stat-icon" style="background: #e3f2fd;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none">
                    <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7564 11.2728 22.0329 10.6054C22.3095 9.93789 22.4518 9.2225 22.4518 8.5C22.4518 7.7775 22.3095 7.06211 22.0329 6.39464C21.7564 5.72718 21.351 5.12084 20.84 4.61V4.61Z" stroke="#2196f3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="stat-content">
                <h3>Estado Mental</h3>
                <p class="stat-value" id="mentalNivel">—</p>
                <p class="stat-label" id="mentalLabel">Cargando...</p>
            </div>
        </div>

        <div class="stat-card" id="citaCard" tabindex="0" role="button" aria-label="Próxima Cita - ir a Citas" style="cursor:pointer; padding:0; overflow:hidden; align-items:stretch;" onclick="window.location.href='<?php echo url('citas'); ?>'">
            <div id="citaDateBadge" style="background: linear-gradient(160deg, #e91e63 0%, #ad1457 100%); min-width:130px; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:28px 20px; flex-shrink:0; gap:6px;">
                <span id="citaDia" style="font-size:4rem; font-weight:800; color:#fff; line-height:1; letter-spacing:-2px;">?</span>
                <span id="citaMes" style="font-size:0.78rem; font-weight:700; color:rgba(255,255,255,0.85); text-transform:uppercase; letter-spacing:2.5px;">—</span>
            </div>
            <div style="flex:1; padding:24px 30px; display:flex; flex-direction:column; justify-content:center; gap:8px;">
                <span style="font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:2px; color:#e91e63; display:flex; align-items:center; gap:6px;">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6V12L16 14"/></svg>
                    Próxima Cita
                </span>
                <p id="citaLabel" style="margin:0; font-size:1.25rem; font-weight:700; color:var(--color-text-primary); line-height:1.3;">Verificando citas...</p>
                <p id="citaDescripcion" style="margin:0; font-size:0.9rem; color:var(--color-text-secondary); display:none;"></p>
            </div>
        </div>
    </div>

    <!-- Mi Plan Personal -->
    <div style="margin-top: 28px;">
        <h2 style="font-size:1.3rem; font-weight:700; color:var(--color-text-primary); margin-bottom:16px; display:flex; align-items:center; gap:10px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            Mi Plan Personal
        </h2>

        <!-- Resumen -->
        <div class="plan-summary-grid" id="planSummary">
            <div class="plan-summary-card">
                <div class="summary-icon" style="background:#fff3e0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2"><path d="M3 12h18M3 9v6M7 10v4M17 10v4M21 9v6"/></svg>
                </div>
                <div>
                    <div class="summary-num" id="numEjercicios">—</div>
                    <div class="summary-label">Ejercicios</div>
                </div>
            </div>
            <div class="plan-summary-card">
                <div class="summary-icon" style="background:#e8f5e9;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
                </div>
                <div>
                    <div class="summary-num" id="numRecetas">—</div>
                    <div class="summary-label">Recetas</div>
                </div>
            </div>
            <div class="plan-summary-card">
                <div class="summary-icon" style="background:#e3f2fd;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2196f3" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <div>
                    <div class="summary-num" id="numRecomendaciones">—</div>
                    <div class="summary-label">Recomendaciones</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="plan-tabs">
            <button class="plan-tab active" data-tab="ejercicios">💪 Ejercicios</button>
            <button class="plan-tab" data-tab="recetas">🥗 Recetas de hoy</button>
            <button class="plan-tab" data-tab="recomendaciones">💡 Recomendaciones</button>
        </div>

        <div class="plan-section active" id="tab-ejercicios">
            <div class="plan-grid" id="planEjerciciosGrid">
                <p class="plan-loading">Cargando tu plan...</p>
            </div>
        </div>
        <div class="plan-section" id="tab-recetas">
            <div class="plan-grid" id="planRecetasGrid">
                <p class="plan-loading">Cargando tu plan...</p>
            </div>
        </div>
        <div class="plan-section" id="tab-recomendaciones">
            <div id="planRecomendacionesGrid">
                <p class="plan-loading">Cargando tu plan...</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal genérico para ejercicio/receta -->
<div id="planModal" class="plan-modal-overlay" onclick="closePlanModal(event)">
    <div class="plan-modal-box">
        <button class="plan-modal-close" onclick="closePlanModal()">&times;</button>
        <h2 id="planModalTitle"></h2>
        <div id="planModalBody"></div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    chatInitUser(<?php echo (int)$user['id']; ?>);
});
</script>

<?php
$additionalJS = ['dashboard.js', 'mi-plan.js', 'chat.js'];
include '../../app/views/layouts/footer.php';
?>
