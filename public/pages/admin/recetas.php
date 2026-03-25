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
                            <th></th>
                            <th>Receta</th>
                            <th>Categoría</th>
                            <th>Calorías</th>
                            <th>Estado</th>
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

                <div class="form-section">
                    <div class="form-section-title">🍽️ Información básica</div>
                    <div class="form-group">
                        <label for="receta_titulo">Título <span class="req">*</span></label>
                        <input type="text" id="receta_titulo" name="titulo" placeholder="Ej. Ensalada mediterránea" required>
                    </div>
                    <div class="form-group">
                        <label for="receta_descripcion">Descripción</label>
                        <textarea id="receta_descripcion" name="descripcion" rows="2" placeholder="Breve descripción de la receta…"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">📝 Ingredientes e instrucciones</div>
                    <div class="form-group">
                        <label for="receta_ingredientes">Ingredientes</label>
                        <textarea id="receta_ingredientes" name="ingredientes" rows="3" placeholder="Un ingrediente por línea…"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="receta_instrucciones">Instrucciones</label>
                        <textarea id="receta_instrucciones" name="instrucciones" rows="3" placeholder="Una instrucción por línea…"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">⏱️ Datos generales</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="receta_tiempo_preparacion">Tiempo de preparación (min)</label>
                            <input type="number" id="receta_tiempo_preparacion" name="tiempo_preparacion" placeholder="30" min="0">
                        </div>
                        <div class="form-group">
                            <label for="receta_porciones">Porciones</label>
                            <input type="number" id="receta_porciones" name="porciones" placeholder="4" min="1">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="receta_calorias">Calorías (kcal)</label>
                            <input type="number" id="receta_calorias" name="calorias" placeholder="350" min="0">
                        </div>
                        <div class="form-group">
                            <label for="receta_categoria">Categoría <span class="req">*</span></label>
                            <select id="receta_categoria" name="categoria" required>
                                <option value="desayuno">Desayuno</option>
                                <option value="almuerzo">Almuerzo</option>
                                <option value="comida">Comida</option>
                                <option value="merienda">Merienda</option>
                                <option value="cena">Cena</option>
                                <option value="snack">Snacks</option>
                                <option value="postre">Postre</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">📊 Macronutrientes por porción <span style="font-weight:400;font-size:0.8rem;color:#aaa;">(opcional)</span></div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="receta_proteinas">Proteínas (g)</label>
                            <input type="number" step="0.1" id="receta_proteinas" name="proteinas" placeholder="Ej. 28">
                        </div>
                        <div class="form-group">
                            <label for="receta_carbohidratos">Carbohidratos (g)</label>
                            <input type="number" step="0.1" id="receta_carbohidratos" name="carbohidratos" placeholder="Ej. 45">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="receta_grasas">Grasas (g)</label>
                            <input type="number" step="0.1" id="receta_grasas" name="grasas" placeholder="Ej. 12">
                        </div>
                        <div class="form-group">
                            <label for="receta_fibra">Fibra (g)</label>
                            <input type="number" step="0.1" id="receta_fibra" name="fibra" placeholder="Ej. 5">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🖼️ Imagen</div>
                    <div class="file-upload-zone">
                        <span class="upload-icon">📁</span>
                        <div>Haz clic o arrastra una imagen aquí</div>
                        <div class="file-upload-hint">JPG, PNG, WebP — máx. 5 MB</div>
                        <input type="file" id="receta_imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="file-upload-preview" id="receta_preview_wrap" style="display:none;">
                        <img id="receta_imagen_preview" src="" alt="Vista previa">
                        <span id="receta_preview_name" style="font-size:0.85rem;color:#666;"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalReceta()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Receta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-recetas.js'];
include '../../../app/views/layouts/footer.php';
?>
