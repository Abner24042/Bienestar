<?php
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();

// Verificar autenticación
if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();
$currentPage = 'perfil';
$pageTitle = 'Mi Perfil';

// Variables para el header
$additionalCSS = ['perfil.css'];
?>

<?php include '../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Mi Perfil</h1>
        <p>Gestiona tu información personal</p>
    </div>

    <div class="profile-container">
        <!-- Card de información personal -->
        <div class="profile-card">
            <div class="profile-avatar-section">
                <img src="<?php echo !empty($user['foto']) ? $user['foto'] : asset('img/icons/default-avatar.svg'); ?>"
                     alt="Avatar"
                     class="profile-avatar-large"
                     crossorigin="anonymous"
                     onerror="console.error('Error loading image:', this.src); this.onerror=null; this.src='<?php echo asset('img/icons/default-avatar.svg'); ?>';">
                <?php if (!isset($_SESSION['login_method']) || $_SESSION['login_method'] !== 'google'): ?>
                <div style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                    <button class="btn btn-primary btn-change-photo" data-modal-open="modalChangePhoto">
                        Cambiar Foto
                    </button>
                    <?php if ($user['foto']): ?>
                    <button class="btn btn-secondary" id="btnRemovePhoto">
                        Quitar Foto
                    </button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
                    Foto sincronizada desde Google
                </p>
                <?php endif; ?>
            </div>
            
            <div class="profile-info">
                <div class="info-row">
                    <label>Nombre Completo</label>
                    <p><?php echo htmlspecialchars($user['nombre']); ?></p>
                </div>
                
                <div class="info-row">
                    <label>Correo Electrónico</label>
                    <p><?php echo htmlspecialchars($user['correo']); ?></p>
                </div>
                
                <div class="info-row">
                    <label>Rol</label>
                    <p>
                        <span class="badge badge-<?php echo $user['rol'] === 'Administrador' ? 'primary' : 'secondary'; ?>">
                            <?php echo htmlspecialchars($user['rol']); ?>
                        </span>
                    </p>
                </div>
                
                <?php if ($user['area']): ?>
                <div class="info-row">
                    <label>Área</label>
                    <p><?php echo htmlspecialchars($user['area']); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="info-row">
                    <label>Miembro desde</label>
                    <p><?php echo date('d/m/Y', strtotime($user['fecha'])); ?></p>
                </div>
                
                <div class="profile-actions">
                    <?php if ($user['rol'] === 'Administrador'): ?>
                    <button class="btn btn-primary" data-modal-open="modalEditProfile">
                        Editar Perfil
                    </button>

                    <?php if (!isset($_SESSION['login_method']) || $_SESSION['login_method'] !== 'google'): ?>
                    <button class="btn btn-secondary" data-modal-open="modalChangePassword">
                        Cambiar Contraseña
                    </button>
                    <?php else: ?>
                    <p style="color: #666; font-size: 0.9rem;">
                        La contraseña se gestiona desde tu cuenta de Google
                    </p>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($user['rol'] === 'Administrador'): ?>
        <!-- Panel de Administrador -->
        <div class="admin-panel">
            <h3>⚙️ Panel de Administrador</h3>
            <p class="admin-description">Acceso a funciones administrativas del sistema</p>

            <div class="admin-actions">
                <a href="<?php echo url('admin'); ?>" class="admin-link">
                    <div class="admin-icon">👥</div>
                    <div>
                        <strong>Gestionar Usuarios</strong>
                        <span>Ver y administrar usuarios</span>
                    </div>
                </a>

                <a href="<?php echo url('admin/citas'); ?>" class="admin-link">
                    <div class="admin-icon">📅</div>
                    <div>
                        <strong>Ver Todas las Citas</strong>
                        <span>Administrar citas del sistema</span>
                    </div>
                </a>

                <a href="<?php echo url('admin'); ?>" class="admin-link">
                    <div class="admin-icon">📊</div>
                    <div>
                        <strong>Reportes</strong>
                        <span>Estadísticas y análisis</span>
                    </div>
                </a>

                <a href="<?php echo url('admin/configuracion'); ?>" class="admin-link">
                    <div class="admin-icon">⚙️</div>
                    <div>
                        <strong>Configuración</strong>
                        <span>Ajustes del sistema</span>
                    </div>
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Editar Perfil (Solo para administradores) -->
<?php if ($user['rol'] === 'Administrador'): ?>
<?php
$modalId = 'modalEditProfile';
$modalTitle = 'Editar Perfil';
$modalSize = 'medium';
$isAdmin = ($user['rol'] === 'Administrador');
$readonlyAttr = $isAdmin ? '' : 'readonly style="background-color: #f5f5f5; cursor: not-allowed;"';
$modalContent = '
<form id="formEditProfile" action="' . BASE_URL . '/api/profile/update" method="POST">
    <div class="form-group">
        <label for="edit_nombre">Nombre Completo' . ($isAdmin ? '' : ' <small style="color: #999;">(Solo admin puede modificar)</small>') . '</label>
        <input type="text" id="edit_nombre" name="nombre" value="' . htmlspecialchars($user['nombre']) . '" required ' . $readonlyAttr . '>
    </div>

    <div class="form-group">
        <label for="edit_correo">Correo Electrónico' . ($isAdmin ? '' : ' <small style="color: #999;">(Solo admin puede modificar)</small>') . '</label>
        <input type="email" id="edit_correo" name="correo" value="' . htmlspecialchars($user['correo']) . '" required ' . $readonlyAttr . '>
    </div>

    <div class="form-group">
        <label for="edit_area">Área' . ($isAdmin ? '' : ' <small style="color: #999;">(Solo admin puede modificar)</small>') . '</label>
        <input type="text" id="edit_area" name="area" value="' . htmlspecialchars($user['area'] ?? '') . '" ' . $readonlyAttr . '>
    </div>
    ' . ($isAdmin ? '' : '
    <div style="padding: 15px; background: #fff3e0; border-left: 4px solid #ff9800; border-radius: 4px; margin-bottom: 15px;">
        <p style="margin: 0; color: #e65100; font-size: 0.9rem;">
            ⚠️ Solo los administradores pueden modificar nombre, correo y área.
        </p>
    </div>
    ') . '
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close="modalEditProfile">Cancelar</button>
        ' . ($isAdmin ? '<button type="submit" class="btn btn-primary">Guardar Cambios</button>' : '<button type="button" class="btn btn-secondary" data-modal-close="modalEditProfile">Cerrar</button>') . '
    </div>
</form>
';
include '../../app/views/components/modal.php';
?>
<?php endif; ?>

<!-- Modal: Cambiar Contraseña (Solo para administradores) -->
<?php if ($user['rol'] === 'Administrador'): ?>
<?php
$modalId = 'modalChangePassword';
$modalTitle = 'Cambiar Contraseña';
$modalSize = 'medium';
$modalContent = '
<form id="formChangePassword" action="' . BASE_URL . '/api/profile/change-password" method="POST">
    <div class="form-group">
        <label for="current_password">Contraseña Actual</label>
        <input type="password" id="current_password" name="current_password" required>
    </div>
    
    <div class="form-group">
        <label for="new_password">Nueva Contraseña</label>
        <input type="password" id="new_password" name="new_password" minlength="6" required>
    </div>
    
    <div class="form-group">
        <label for="confirm_password">Confirmar Nueva Contraseña</label>
        <input type="password" id="confirm_password" name="confirm_password" minlength="6" required>
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close="modalChangePassword">Cancelar</button>
        <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
    </div>
</form>
';
include '../../app/views/components/modal.php';
?>
<?php endif; ?>

<!-- Modal: Cambiar Foto -->
<?php
$modalId = 'modalChangePhoto';
$modalTitle = 'Cambiar Foto de Perfil';
$modalSize = 'medium';
$modalContent = '
<form id="formChangePhoto" action="' . BASE_URL . '/api/profile/upload-photo" method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="photo">Seleccionar Foto</label>
        <input type="file" id="photo" name="photo" accept="image/*" required>
        <small>Formatos permitidos: JPG, PNG, GIF (máx. 5MB)</small>
    </div>
    
    <div class="preview-container" id="photoPreview" style="display: none;">
        <img id="previewImage" src="" alt="Preview" style="max-width: 100%; border-radius: 8px;">
    </div>
    
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close="modalChangePhoto">Cancelar</button>
        <button type="submit" class="btn btn-primary">Subir Foto</button>
    </div>
</form>

<script>
document.getElementById("photo").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById("photoPreview").style.display = "block";
            document.getElementById("previewImage").src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});
</script>
';
include '../../app/views/components/modal.php';
?>

<?php
$additionalJS = ['perfil.js'];
include '../../app/views/layouts/footer.php';
?>