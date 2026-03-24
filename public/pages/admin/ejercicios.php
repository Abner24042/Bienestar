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
$pageTitle = 'Gestión de Ejercicios';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>💪 Gestión de Ejercicios</h1>
        <p>Administrar ejercicios del sistema BIENIESTAR</p>
    </div>

    <div class="admin-dashboard">
        <!-- Tabla de ejercicios -->
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Lista de Ejercicios</h2>
                <button class="btn btn-primary" id="btnNuevoEjercicio">+ Nuevo Ejercicio</button>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th></th>
                            <th>Ejercicio</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Calorías</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="ejerciciosTableBody">
                        <tr>
                            <td colspan="8" class="empty-message">
                                Cargando ejercicios...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo/Editar Ejercicio -->
<div id="modalEjercicio" class="modal" style="display: none;">
    <div class="modal-content modal-lg">
        <div class="modal-header">
            <h3 id="modalEjercicioTitle">Nuevo Ejercicio</h3>
            <button class="modal-close" onclick="cerrarModalEjercicio()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formEjercicio">
                <input type="hidden" id="ejercicio_id" name="id">

                <div class="form-section">
                    <div class="form-section-title">💪 Información básica</div>
                    <div class="form-group">
                        <label for="ejercicio_titulo">Título <span class="req">*</span></label>
                        <input type="text" id="ejercicio_titulo" name="titulo" placeholder="Ej. Press de banca" required>
                    </div>
                    <div class="form-group">
                        <label for="ejercicio_descripcion">Descripción</label>
                        <textarea id="ejercicio_descripcion" name="descripcion" rows="2" placeholder="Breve descripción del ejercicio…"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">📊 Detalles</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ejercicio_tipo">Tipo <span class="req">*</span></label>
                            <select id="ejercicio_tipo" name="tipo" required>
                                <option value="cardio">Cardio</option>
                                <option value="fuerza">Fuerza</option>
                                <option value="flexibilidad">Flexibilidad</option>
                                <option value="equilibrio">Equilibrio</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ejercicio_nivel">Nivel <span class="req">*</span></label>
                            <select id="ejercicio_nivel" name="nivel" required>
                                <option value="principiante">Principiante</option>
                                <option value="intermedio">Intermedio</option>
                                <option value="avanzado">Avanzado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ejercicio_duracion">Duración (min)</label>
                            <input type="number" id="ejercicio_duracion" name="duracion" placeholder="30" min="1">
                        </div>
                        <div class="form-group">
                            <label for="ejercicio_calorias">Calorías quemadas</label>
                            <input type="number" id="ejercicio_calorias" name="calorias_quemadas" placeholder="200" min="0">
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🦵 Músculos y equipamiento</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ejercicio_musculo">Músculo objetivo</label>
                            <input type="text" id="ejercicio_musculo" name="musculo_objetivo" placeholder="Ej. Pecho, Cuádriceps">
                        </div>
                        <div class="form-group">
                            <label for="ejercicio_secundarios">Músculos secundarios</label>
                            <input type="text" id="ejercicio_secundarios" name="musculos_secundarios" placeholder="Ej. Tríceps, Glúteos">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ejercicio_equipamiento">Equipamiento</label>
                        <input type="text" id="ejercicio_equipamiento" name="equipamiento" placeholder="Ej. Mancuernas, Sin equipo…">
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">📋 Instrucciones y video</div>
                    <div class="form-group">
                        <label for="ejercicio_instrucciones">Instrucciones</label>
                        <textarea id="ejercicio_instrucciones" name="instrucciones" rows="3" placeholder="Una instrucción por línea…"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="ejercicio_video">URL del Video</label>
                        <input type="url" id="ejercicio_video" name="video_url" placeholder="https://youtube.com/…">
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🖼️ Imagen</div>
                    <div class="file-upload-zone">
                        <span class="upload-icon">📁</span>
                        <div>Haz clic o arrastra una imagen aquí</div>
                        <div class="file-upload-hint">JPG, PNG, WebP — máx. 5 MB</div>
                        <input type="file" id="ejercicio_imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="file-upload-preview" id="ejercicio_preview_wrap" style="display:none;">
                        <img id="ejercicio_imagen_preview" src="" alt="Preview">
                        <span id="ejercicio_preview_name" style="font-size:0.85rem;color:#666;"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalEjercicio()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Ejercicio</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-ejercicios.js'];
include '../../../app/views/layouts/footer.php';
?>
