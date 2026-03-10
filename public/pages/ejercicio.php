<?php
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();

if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();
$currentPage = 'ejercicio';
$pageTitle = 'Ejercicio';
$additionalCSS = ['filters.css', 'ejercicio.css'];
?>

<?php include '../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Rutinas de Ejercicio <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg></h1>
        <p>Encuentra la rutina perfecta para alcanzar tus objetivos</p>
    </div>

    <!-- Filtros -->
    <div class="filters-section">
        <div class="filter-group">
            <button class="filter-btn active" data-filter="all">Todas</button>
            <button class="filter-btn" data-filter="cardio">Cardio</button>
            <button class="filter-btn" data-filter="fuerza">Fuerza</button>
            <button class="filter-btn" data-filter="flexibilidad">Flexibilidad</button>
            <button class="filter-btn" data-filter="equilibrio">Equilibrio</button>
        </div>

        <div class="search-box">
            <input type="text" id="searchExercises" placeholder="Buscar rutinas...">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

    <!-- Grid de Ejercicios (cargado dinamicamente) -->
    <div class="exercises-grid" id="exercisesGrid">
        <p style="text-align:center;color:#999;grid-column:1/-1;">Cargando ejercicios...</p>
    </div>

    <!-- Beneficios del Ejercicio -->
    <div class="exercise-benefits">
        <h2>Beneficios del Ejercicio Regular <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></h2>
        <div class="benefits-grid">
            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#e91e63" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></div>
                <h3>Salud Cardiovascular</h3>
                <p>Mejora la salud del corazón y reduce el riesgo de enfermedades</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2196f3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg></div>
                <h3>Salud Mental</h3>
                <p>Reduce el estrés, ansiedad y mejora el estado de ánimo</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 6.5h11"/><path d="M6.5 17.5h11"/><path d="M3 12h18"/><path d="M6.5 6.5a1.5 1.5 0 0 0-3 0v11a1.5 1.5 0 0 0 3 0"/><path d="M20.5 6.5a1.5 1.5 0 0 1 0 3"/><path d="M20.5 17.5a1.5 1.5 0 0 0 0-3"/></svg></div>
                <h3>Fuerza y Resistencia</h3>
                <p>Aumenta la masa muscular y la resistencia física</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3f51b5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg></div>
                <h3>Mejor Sueño</h3>
                <p>Ayuda a conciliar el sueño y mejora su calidad</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg></div>
                <h3>Más Energía</h3>
                <p>Aumenta los niveles de energía durante el día</p>
            </div>

            <div class="benefit-card">
                <div class="benefit-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg></div>
                <h3>Control de Peso</h3>
                <p>Ayuda a mantener un peso saludable</p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Dinamico de Ejercicio -->
<div class="modal" id="dynamicExerciseModal">
    <div class="modal-overlay" onclick="closeExerciseModal()"></div>
    <div class="modal-container modal-large">
        <div class="modal-header">
            <h3 class="modal-title" id="exerciseModalTitle"></h3>
            <button class="modal-close" onclick="closeExerciseModal()">&times;</button>
        </div>
        <div class="modal-body" id="exerciseModalBody"></div>
    </div>
</div>

<?php
$additionalJS = ['ejercicio.js'];
include '../../app/views/layouts/footer.php';
?>
