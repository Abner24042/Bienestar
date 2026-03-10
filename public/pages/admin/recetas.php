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
$pageTitle = 'Gestión de Recetas';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>🍽️ Gestión de Recetas</h1>
        <p>Administrar recetas saludables del sistema BIENIESTAR</p>
    </div>

    <div class="admin-dashboard">
        <!-- Tabla de recetas -->
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Lista de Recetas</h2>
                <button class="btn btn-primary" id="btnNuevaReceta">+ Nueva Receta</button>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Calorías</th>
                            <th>Activo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="recetasTableBody">
                        <tr>
                            <td colspan="7" class="empty-message">
                                Cargando recetas...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nueva/Editar Receta -->
<div id="modalReceta" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalRecetaTitle">Nueva Receta</h3>
            <button class="modal-close" onclick="cerrarModalReceta()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formReceta">
                <input type="hidden" id="receta_id" name="id">

                <div class="form-group">
                    <label for="receta_titulo">Título</label>
                    <input type="text" id="receta_titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="receta_descripcion">Descripción</label>
                    <textarea id="receta_descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="receta_ingredientes">Ingredientes</label>
                    <textarea id="receta_ingredientes" name="ingredientes" rows="4" placeholder="Un ingrediente por línea"></textarea>
                </div>

                <div class="form-group">
                    <label for="receta_instrucciones">Instrucciones</label>
                    <textarea id="receta_instrucciones" name="instrucciones" rows="4" placeholder="Una instrucción por línea"></textarea>
                </div>

                <div class="form-group">
                    <label for="receta_tiempo_preparacion">Tiempo de Preparación (minutos)</label>
                    <input type="number" id="receta_tiempo_preparacion" name="tiempo_preparacion" min="0">
                </div>

                <div class="form-group">
                    <label for="receta_porciones">Porciones</label>
                    <input type="number" id="receta_porciones" name="porciones" min="0">
                </div>

                <div class="form-group">
                    <label for="receta_calorias">Calorías</label>
                    <input type="number" id="receta_calorias" name="calorias" min="0">
                </div>

                <div class="form-group">
                    <label for="receta_categoria">Categoría</label>
                    <select id="receta_categoria" name="categoria" required>
                        <option value="desayuno">Desayuno</option>
                        <option value="comida">Comida</option>
                        <option value="cena">Cena</option>
                        <option value="snack">Snack</option>
                        <option value="postre">Postre</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="receta_imagen">Imagen</label>
                    <input type="file" id="receta_imagen" name="imagen" accept="image/*">
                    <img id="receta_imagen_preview" src="" alt="Vista previa" style="display: none; margin-top: 0.5rem; width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalReceta()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-recetas.js'];
include '../../../app/views/layouts/footer.php';
?>
