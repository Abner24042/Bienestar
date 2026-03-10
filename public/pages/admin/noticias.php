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
                            <th>Imagen</th>
                            <th>Título</th>
                            <th>Categoría</th>
                            <th>Autor</th>
                            <th>Publicado</th>
                            <th>Destacado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="noticiasTableBody">
                        <tr>
                            <td colspan="8" class="empty-message">
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

                <div class="form-group">
                    <label for="noticia_titulo">Título</label>
                    <input type="text" id="noticia_titulo" name="titulo" required>
                </div>

                <div class="form-group">
                    <label for="noticia_resumen">Resumen <small>(máx. 500 caracteres)</small></label>
                    <textarea id="noticia_resumen" name="resumen" maxlength="500" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label for="noticia_contenido">Contenido</label>
                    <textarea id="noticia_contenido" name="contenido" required style="min-height: 200px;" rows="8"></textarea>
                </div>

                <div class="form-group">
                    <label for="noticia_categoria">Categoría</label>
                    <select id="noticia_categoria" name="categoria" required>
                        <option value="alimentacion">Alimentación</option>
                        <option value="ejercicio">Ejercicio</option>
                        <option value="salud-mental">Salud Mental</option>
                        <option value="general">General</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="noticia_autor">Autor</label>
                    <input type="text" id="noticia_autor" name="autor">
                </div>

                <div class="form-group">
                    <label for="noticia_publicado">
                        <input type="checkbox" id="noticia_publicado" name="publicado" value="1"> Publicado
                    </label>
                </div>

                <div class="form-group">
                    <label for="noticia_imagen">Imagen</label>
                    <input type="file" id="noticia_imagen" name="imagen" accept="image/*">
                    <img id="noticia_imagen_preview" src="" alt="Preview" style="max-width: 150px; max-height: 100px; margin-top: 0.5rem; display: none; border-radius: 4px;">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalNoticia()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-noticias.js'];
include '../../../app/views/layouts/footer.php';
?>
