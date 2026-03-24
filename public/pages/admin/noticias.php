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
$pageTitle = 'Gestión de Noticias';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>📰 Gestión de Noticias</h1>
        <p>Administrar noticias y artículos del sistema BIENIESTAR</p>
    </div>

    <div class="admin-dashboard">
        <!-- Tabla de noticias -->
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Lista de Noticias</h2>
                <button class="btn btn-primary" id="btnNuevaNoticia">+ Nueva Noticia</button>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th></th>
                            <th>Noticia</th>
                            <th>Categoría</th>
                            <th>Estado</th>
                            <th>Destacado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="noticiasTableBody">
                        <tr>
                            <td colspan="7" class="empty-message">
                                Cargando noticias...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nueva/Editar Noticia -->
<div id="modalNoticia" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalNoticiaTitle">Nueva Noticia</h3>
            <button class="modal-close" onclick="cerrarModalNoticia()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formNoticia">
                <input type="hidden" id="noticia_id" name="id">

                <div class="form-section">
                    <div class="form-section-title">📰 Información básica</div>
                    <div class="form-group">
                        <label for="noticia_titulo">Título <span class="req">*</span></label>
                        <input type="text" id="noticia_titulo" name="titulo" placeholder="Título de la noticia" required>
                    </div>
                    <div class="form-group">
                        <label for="noticia_resumen">Resumen <small>(máx. 500 caracteres)</small></label>
                        <textarea id="noticia_resumen" name="resumen" maxlength="500" rows="2" placeholder="Resumen breve que aparecerá en la lista…"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">📄 Contenido</div>
                    <div class="form-group">
                        <label for="noticia_contenido">Contenido completo <span class="req">*</span></label>
                        <textarea id="noticia_contenido" name="contenido" required rows="7" style="min-height:160px;" placeholder="Escribe aquí el artículo completo…"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🏷️ Detalles y publicación</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="noticia_categoria">Categoría <span class="req">*</span></label>
                            <select id="noticia_categoria" name="categoria" required>
                                <option value="alimentacion">Alimentación</option>
                                <option value="ejercicio">Ejercicio</option>
                                <option value="salud-mental">Salud Mental</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="noticia_autor">Autor</label>
                            <input type="text" id="noticia_autor" name="autor" placeholder="Nombre del autor">
                        </div>
                    </div>
                    <label class="form-check">
                        <input type="checkbox" id="noticia_publicado" name="publicado" value="1">
                        <span class="form-check-label">Publicar inmediatamente</span>
                    </label>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🖼️ Imagen destacada</div>
                    <div class="file-upload-zone">
                        <span class="upload-icon">📁</span>
                        <div>Haz clic o arrastra una imagen aquí</div>
                        <div class="file-upload-hint">JPG, PNG, WebP — máx. 5 MB</div>
                        <input type="file" id="noticia_imagen" name="imagen" accept="image/*">
                    </div>
                    <div class="file-upload-preview" id="noticia_preview_wrap" style="display:none;">
                        <img id="noticia_imagen_preview" src="" alt="Preview">
                        <span id="noticia_preview_name" style="font-size:0.85rem;color:#666;"></span>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalNoticia()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Noticia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-noticias.js'];
include '../../../app/views/layouts/footer.php';
?>
