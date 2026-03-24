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
        <!-- Card principal -->
        <div class="profile-card">

            <!-- Sidebar izquierda -->
            <div class="profile-left">
                <div class="profile-avatar-wrap">
                    <img src="<?php echo !empty($user['foto']) ? $user['foto'] : asset('img/icons/default-avatar.svg'); ?>"
                         alt="Avatar"
                         class="profile-avatar"
                         crossorigin="anonymous"
                         onerror="this.onerror=null; this.src='<?php echo asset('img/icons/default-avatar.svg'); ?>';">
                </div>

                <h2 class="profile-name"><?php echo htmlspecialchars($user['nombre']); ?></h2>

                <?php
                $rolClass = match(strtolower($user['rol'])) {
                    'usuario'       => 'role-usuario',
                    'coach'         => 'role-coach',
                    'nutriologo'    => 'role-nutriologo',
                    'psicologo'     => 'role-psicologo',
                    default         => ''
                };
                ?>
                <span class="profile-role-badge <?php echo $rolClass; ?>"><?php echo htmlspecialchars($user['rol']); ?></span>

                <?php if ($user['area']): ?>
                <p class="profile-area-text"><?php echo htmlspecialchars($user['area']); ?></p>
                <?php endif; ?>

                <?php if (!isset($_SESSION['login_method']) || $_SESSION['login_method'] !== 'google'): ?>
                <div class="profile-photo-actions">
                    <button class="btn btn-secondary" data-modal-open="modalChangePhoto">📷 Cambiar foto</button>
                    <?php if ($user['foto']): ?>
                    <button class="btn btn-secondary" id="btnRemovePhoto">Quitar</button>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p class="profile-google-note">Foto sincronizada desde Google</p>
                <?php endif; ?>

                <?php if ($user['rol'] === 'Administrador'): ?>
                <div class="profile-action-btns">
                    <button class="btn btn-primary" data-modal-open="modalEditProfile">✏️ Editar perfil</button>
                    <?php if (!isset($_SESSION['login_method']) || $_SESSION['login_method'] !== 'google'): ?>
                    <button class="btn btn-secondary" data-modal-open="modalChangePassword">🔒 Contraseña</button>
                    <?php else: ?>
                    <p class="profile-google-password-note">Contraseña gestionada desde Google</p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <p class="profile-since">📅 Miembro desde <?php echo date('M Y', strtotime($user['fecha'])); ?></p>
            </div>

            <!-- Panel derecho -->
            <div class="profile-right">
                <p class="profile-section-label">Información de la cuenta</p>
                <div class="profile-fields">
                    <div class="profile-field">
                        <label>Nombre completo</label>
                        <div class="field-value"><?php echo htmlspecialchars($user['nombre']); ?></div>
                    </div>
                    <div class="profile-field">
                        <label>Correo electrónico</label>
                        <div class="field-value"><?php echo htmlspecialchars($user['correo']); ?></div>
                    </div>
                    <div class="profile-field">
                        <label>Rol en el sistema</label>
                        <div class="field-value"><?php echo htmlspecialchars($user['rol']); ?></div>
                    </div>
                    <?php if ($user['area']): ?>
                    <div class="profile-field">
                        <label>Área</label>
                        <div class="field-value"><?php echo htmlspecialchars($user['area']); ?></div>
                    </div>
                    <?php endif; ?>
                    <div class="profile-field">
                        <label>Fecha de registro</label>
                        <div class="field-value"><?php echo date('d/m/Y', strtotime($user['fecha'])); ?></div>
                    </div>
                    <div class="profile-field">
                        <label>Método de acceso</label>
                        <div class="field-value">
                            <?php echo (isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'google') ? '🔵 Google' : '🔑 Correo y contraseña'; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($user['rol'] === 'Administrador'): ?>
        <!-- Panel de Administrador -->
        <div class="admin-panel">
            <div class="admin-panel-header">
                <h3>⚙️ Acceso rápido — Administrador</h3>
            </div>
            <div class="admin-actions">
                <a href="<?php echo url('admin'); ?>" class="admin-link">
                    <div class="admin-icon">👥</div>
                    <div>
                        <strong>Usuarios</strong>
                        <span>Gestionar cuentas</span>
                    </div>
                </a>
                <a href="<?php echo url('admin/citas'); ?>" class="admin-link">
                    <div class="admin-icon">📅</div>
                    <div>
                        <strong>Citas</strong>
                        <span>Ver todas las citas</span>
                    </div>
                </a>
                <a href="<?php echo url('admin/recetas'); ?>" class="admin-link">
                    <div class="admin-icon">🍽️</div>
                    <div>
                        <strong>Recetas</strong>
                        <span>Gestionar recetas</span>
                    </div>
                </a>
                <a href="<?php echo url('admin/ejercicios'); ?>" class="admin-link">
                    <div class="admin-icon">💪</div>
                    <div>
                        <strong>Ejercicios</strong>
                        <span>Gestionar ejercicios</span>
                    </div>
                </a>
                <a href="<?php echo url('admin/noticias'); ?>" class="admin-link">
                    <div class="admin-icon">📰</div>
                    <div>
                        <strong>Noticias</strong>
                        <span>Gestionar noticias</span>
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