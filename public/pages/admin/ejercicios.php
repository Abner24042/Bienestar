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
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Tipo</th>
                            <th>Nivel</th>
                            <th>Duración</th>
                            <th>Activo</th>
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
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalEjercicioTitle">Nuevo Ejercicio</h3>
            <button class="modal-close" onclick="cerrarModalEjercicio()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formEjercicio">
                <input type="hidden" id="ejercicio_id" name="id">

                <div class="form-group">
                    <label for="ejercicio_titulo">Título</label>
                    <input type="text" id="ejercicio_titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="ejercicio_descripcion">Descripción</label>
                    <textarea id="ejercicio_descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="ejercicio_duracion">Duración</label>
                    <input type="number" id="ejercicio_duracion" name="duracion" placeholder="minutos" min="1">
                </div>

                <div class="form-group">
                    <label for="ejercicio_nivel">Nivel</label>
                    <select id="ejercicio_nivel" name="nivel" required>
                        <option value="principiante">Principiante</option>
                        <option value="intermedio">Intermedio</option>
                        <option value="avanzado">Avanzado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ejercicio_tipo">Tipo</label>
                    <select id="ejercicio_tipo" name="tipo" required>
                        <option value="cardio">Cardio</option>
                        <option value="fuerza">Fuerza</option>
                        <option value="flexibilidad">Flexibilidad</option>
                        <option value="equilibrio">Equilibrio</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="ejercicio_calorias">Calorías Quemadas</label>
                    <input type="number" id="ejercicio_calorias" name="calorias_quemadas" min="0">
                </div>

                <div class="form-group">
                    <label for="ejercicio_video">URL del Video</label>
                    <input type="url" id="ejercicio_video" name="video_url" placeholder="https://...">
                </div>

                <div class="form-group">
                    <label for="ejercicio_musculo">Músculo objetivo</label>
                    <input type="text" id="ejercicio_musculo" name="musculo_objetivo" placeholder="ej. Pecho, Cuádriceps...">
                </div>

                <div class="form-group">
                    <label for="ejercicio_secundarios">Músculos secundarios</label>
                    <input type="text" id="ejercicio_secundarios" name="musculos_secundarios" placeholder="ej. Tríceps, Glúteos...">
                </div>

                <div class="form-group">
                    <label for="ejercicio_equipamiento">Equipamiento</label>
                    <input type="text" id="ejercicio_equipamiento" name="equipamiento" placeholder="ej. Mancuernas, Sin equipo...">
                </div>

                <div class="form-group">
                    <label for="ejercicio_instrucciones">Instrucciones</label>
                    <textarea id="ejercicio_instrucciones" name="instrucciones" rows="4" placeholder="Una instrucción por línea"></textarea>
                </div>

                <div class="form-group">
                    <label for="ejercicio_imagen">Imagen</label>
                    <input type="file" id="ejercicio_imagen" name="imagen" accept="image/*">
                    <img id="ejercicio_imagen_preview" src="" alt="Preview" style="display: none; width: 80px; height: 80px; object-fit: cover; margin-top: 0.5rem; border-radius: 6px;">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalEjercicio()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-ejercicios.js'];
include '../../../app/views/layouts/footer.php';
?>
