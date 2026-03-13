<?php
$pageTitle    = 'Favoritos';
$currentPage  = 'favoritos';
$additionalCSS = ['alimentacion.css', 'ejercicio.css', 'favoritos.css'];

require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    redirect('login');
}

include '../../app/views/layouts/header.php';
?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Favoritos</h1>
        <p>Tus recetas y ejercicios guardados</p>
    </div>

    <!-- Tabs -->
    <div class="fav-tabs">
        <button class="fav-tab active" onclick="favCambiarTab('recetas', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/>
                <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
            </svg>
            Recetas <span class="fav-tab-count" id="favCountRecetas"></span>
        </button>
        <button class="fav-tab" onclick="favCambiarTab('ejercicios', this)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12h18"/><path d="M3 9v6"/><path d="M7 10v4"/><path d="M17 10v4"/><path d="M21 9v6"/>
            </svg>
            Ejercicios <span class="fav-tab-count" id="favCountEjercicios"></span>
        </button>
    </div>

    <!-- Grid recetas -->
    <div id="favGridRecetas" class="recipes-grid" style="margin-top:1.5rem;"></div>

    <!-- Grid ejercicios -->
    <div id="favGridEjercicios" class="exercises-grid" style="margin-top:1.5rem;display:none;"></div>
</div>

<!-- Modal Receta -->
<div class="modal" id="dynamicRecipeModal">
    <div class="modal-overlay" onclick="closeFavRecipeModal()"></div>
    <div class="modal-container modal-large">
        <div class="modal-header">
            <h3 class="modal-title" id="recipeModalTitle"></h3>
            <button class="modal-close" onclick="closeFavRecipeModal()">&times;</button>
        </div>
        <div class="modal-body" id="recipeModalBody"></div>
    </div>
</div>

<!-- Modal Ejercicio -->
<div class="modal" id="dynamicExerciseModal">
    <div class="modal-overlay" onclick="closeFavExerciseModal()"></div>
    <div class="modal-container modal-large">
        <div class="modal-header">
            <h3 class="modal-title" id="exerciseModalTitle"></h3>
            <button class="modal-close" onclick="closeFavExerciseModal()">&times;</button>
        </div>
        <div class="modal-body" id="exerciseModalBody"></div>
    </div>
</div>

<?php
$additionalJS = ['favoritos.js'];
include '../../app/views/layouts/footer.php';
?>
