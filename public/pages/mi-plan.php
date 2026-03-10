<?php
$pageTitle   = 'Mi Plan Personal';
$currentPage = 'mi-plan';
$additionalCSS = ['mi-plan.css'];
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();
if (!$authController->isAuthenticated()) redirect('login');

$user = $authController->getCurrentUser();
include '../../app/views/layouts/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Mi Plan Personal
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:6px"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </h1>
        <p>Rutinas, recetas y recomendaciones asignadas especialmente para ti</p>
    </div>

    <!-- Resumen -->
    <div class="plan-summary-grid" id="planSummary">
        <div class="plan-summary-card" id="countEjercicios">
            <div class="summary-icon" style="background:#fff3e0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2"><path d="M3 12h18M3 9v6M7 10v4M17 10v4M21 9v6"/></svg>
            </div>
            <div>
                <div class="summary-num" id="numEjercicios">—</div>
                <div class="summary-label">Ejercicios</div>
            </div>
        </div>
        <div class="plan-summary-card" id="countRecetas">
            <div class="summary-icon" style="background:#e8f5e9;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/></svg>
            </div>
            <div>
                <div class="summary-num" id="numRecetas">—</div>
                <div class="summary-label">Recetas</div>
            </div>
        </div>
        <div class="plan-summary-card" id="countRecomendaciones">
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
        <button class="plan-tab" data-tab="recetas">🥗 Recetas</button>
        <button class="plan-tab" data-tab="recomendaciones">💡 Recomendaciones</button>
    </div>

    <!-- Sección Ejercicios -->
    <div class="plan-section active" id="tab-ejercicios">
        <div class="plan-grid" id="planEjerciciosGrid">
            <p class="plan-loading">Cargando tu plan...</p>
        </div>
    </div>

    <!-- Sección Recetas -->
    <div class="plan-section" id="tab-recetas">
        <div class="plan-grid" id="planRecetasGrid">
            <p class="plan-loading">Cargando tu plan...</p>
        </div>
    </div>

    <!-- Sección Recomendaciones -->
    <div class="plan-section" id="tab-recomendaciones">
        <div id="planRecomendacionesGrid">
            <p class="plan-loading">Cargando tu plan...</p>
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

<?php
$additionalJS = ['mi-plan.js'];
include '../../app/views/layouts/footer.php';
?>
