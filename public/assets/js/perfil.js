
/**
 * Validar email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Mostrar toast/notificación
 */
function showToast(message, type = 'info') {
    // Crear elemento toast si no existe
    let toast = document.querySelector('.toast-notification');

    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }

    // Configurar tipo
    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.opacity = '1';

    // Ocultar después de 3 segundos
    setTimeout(function () {
        toast.style.opacity = '0';
        setTimeout(function () {
            toast.style.display = 'none';
        }, 300);
    }, 3000);
}

/**
 * Mostrar loader en botón
 */
function showLoader() {
    const button = document.querySelector('.btn-primary[type="submit"]');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-small"></span> Guardando...';
    }
}

// ============================================
// FUNCIONES DE MANEJO DE FORMULARIOS
// ============================================

/**
 * Manejar edición de perfil
 */
function handleEditProfile(e) {
    e.preventDefault();

    const nombre = document.getElementById('edit_nombre').value.trim();
    const correo = document.getElementById('edit_correo').value.trim();

    if (!nombre || !correo) {
        showToast('Por favor completa todos los campos', 'error');
        return;
    }

    if (!isValidEmail(correo)) {
        showToast('Email inválido', 'error');
        return;
    }

    showLoader();
    this.submit();
}

/**
 * Manejar cambio de contraseña
 */
function handleChangePassword(e) {
    e.preventDefault();

    const currentPassword = document.getElementById('current_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    if (!currentPassword || !newPassword || !confirmPassword) {
        showToast('Por favor completa todos los campos', 'error');
        return;
    }

    if (newPassword.length < 6) {
        showToast('La contraseña debe tener al menos 6 caracteres', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showToast('Las contraseñas no coinciden', 'error');
        return;
    }

    showLoader();
    this.submit();
}

/**
 * Manejar cambio de foto
 */
function handleChangePhoto(e) {
    e.preventDefault();

    const photoInput = document.getElementById('photo');

    if (!photoInput.files || !photoInput.files[0]) {
        showToast('Por favor selecciona una foto', 'error');
        return;
    }

    const file = photoInput.files[0];
    const maxSize = 5 * 1024 * 1024; // 5MB

    if (file.size > maxSize) {
        showToast('La foto es demasiado grande (máx. 5MB)', 'error');
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showToast('Formato de imagen no permitido', 'error');
        return;
    }

    showLoader();
    this.submit();
}

/**
 * Manejar eliminación de foto
 */
function handleRemovePhoto() {
    console.log('🗑️ Botón de eliminar foto clickeado');

    if (!confirm('¿Estás seguro de que deseas eliminar tu foto de perfil?')) {
        console.log('❌ Usuario canceló la eliminación');
        return;
    }

    console.log('✅ Usuario confirmó eliminación, enviando formulario...');

    // Crear formulario temporal para enviar POST
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = API_URL + '/profile/remove-photo';
    document.body.appendChild(form);
    form.submit();
}

/**
 * Inicializar formularios del perfil
 */
function initProfileForms() {
    // Formulario de editar perfil
    const formEditProfile = document.getElementById('formEditProfile');
    if (formEditProfile) {
        formEditProfile.addEventListener('submit', handleEditProfile);
    }

    // Formulario de cambiar contraseña
    const formChangePassword = document.getElementById('formChangePassword');
    if (formChangePassword) {
        formChangePassword.addEventListener('submit', handleChangePassword);
    }

    // Formulario de cambiar foto
    const formChangePhoto = document.getElementById('formChangePhoto');
    if (formChangePhoto) {
        formChangePhoto.addEventListener('submit', handleChangePhoto);
    }

    // Botón de quitar foto
    const btnRemovePhoto = document.getElementById('btnRemovePhoto');
    if (btnRemovePhoto) {
        console.log('✅ Botón de quitar foto encontrado, agregando event listener');
        btnRemovePhoto.addEventListener('click', handleRemovePhoto);
    } else {
        console.log('⚠️ Botón de quitar foto NO encontrado en el DOM');
    }
}

/**
 * Manejo del menú de usuario con delay
 */
function initUserMenu() {
    const headerUser = document.querySelector('.header-user');
    const userMenu = document.querySelector('.user-menu');

    if (headerUser && userMenu) {
        let menuTimeout;

        // Mostrar menú al hacer hover en el usuario
        headerUser.addEventListener('mouseenter', function () {
            clearTimeout(menuTimeout);
            userMenu.style.display = 'block';
            setTimeout(function () {
                userMenu.style.opacity = '1';
                userMenu.style.transform = 'translateY(0)';
            }, 10);
        });

        // Ocultar menú con delay al salir del usuario
        headerUser.addEventListener('mouseleave', function () {
            menuTimeout = setTimeout(function () {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    userMenu.style.display = 'none';
                }, 200);
            }, 300); // 300ms de delay antes de cerrar
        });

        // Mantener abierto si el cursor está sobre el menú
        userMenu.addEventListener('mouseenter', function () {
            clearTimeout(menuTimeout);
        });

        // Cerrar cuando el cursor sale del menú
        userMenu.addEventListener('mouseleave', function () {
            menuTimeout = setTimeout(function () {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(function () {
                    userMenu.style.display = 'none';
                }, 200);
            }, 200);
        });
    }
}

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', function () {
    initProfileForms();
    initUserMenu();
});
