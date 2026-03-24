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
$pageTitle = 'Gestión de Usuarios';
$additionalCSS = ['admin.css'];
?>

<?php include '../../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>👥 Gestión de Usuarios</h1>
        <p>Administrar cuentas de usuario del sistema BIENIESTAR</p>
    </div>

    <div class="admin-dashboard">
        <!-- Tabla de usuarios -->
        <div class="admin-section" style="width: 100%;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2>Lista de Usuarios</h2>
                <button class="btn btn-primary" id="btnNuevoUsuario">+ Nuevo Usuario</button>
            </div>

            <div class="admin-table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="usuariosTableBody">
                        <tr>
                            <td colspan="5" class="empty-message">
                                Cargando usuarios...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Nuevo/Editar Usuario -->
<div id="modalUsuario" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalUsuarioTitle">Nuevo Usuario</h3>
            <button class="modal-close" onclick="cerrarModalUsuario()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="formUsuario">
                <input type="hidden" id="usuario_id" name="id">

                <div class="form-section">
                    <div class="form-section-title">👤 Información personal</div>
                    <div class="form-group">
                        <label for="usuario_nombre">Nombre Completo <span class="req">*</span></label>
                        <input type="text" id="usuario_nombre" name="nombre" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario_correo">Correo Electrónico <span class="req">*</span></label>
                        <input type="email" id="usuario_correo" name="correo" placeholder="correo@ejemplo.com" required>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title">🏷️ Rol y área</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="usuario_rol">Rol <span class="req">*</span></label>
                            <select id="usuario_rol" name="rol" required>
                                <option value="usuario">Usuario</option>
                                <option value="Administrador">Administrador</option>
                                <option value="coach">Coach</option>
                                <option value="nutriologo">Nutriólogo</option>
                                <option value="psicologo">Psicólogo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="usuario_area">Área</label>
                            <input type="text" id="usuario_area" name="area" placeholder="Ej. Deportes">
                        </div>
                    </div>
                </div>

                <div class="form-section" id="passwordGroup">
                    <div class="form-section-title">🔒 Seguridad</div>
                    <div class="form-group">
                        <label for="usuario_password">Contraseña</label>
                        <input type="password" id="usuario_password" name="password" minlength="6" placeholder="Mínimo 6 caracteres">
                        <small>Dejar en blanco para mantener la contraseña actual (solo al editar)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalUsuario()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$additionalJS = ['admin.js', 'admin-usuarios.js'];
include '../../../app/views/layouts/footer.php';
?>
