
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showToast(message, type) {
    var toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    toast.className = 'toast-notification toast-' + (type || 'info');
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.opacity = '1';
    setTimeout(function () {
        toast.style.opacity = '0';
        setTimeout(function () { toast.style.display = 'none'; }, 300);
    }, 3000);
}

function clearFieldError(field) {
    if (!field) return;
    field.classList.remove('field-error');
    var errorMsg = field.parentElement.querySelector('.error-message');
    if (errorMsg) errorMsg.remove();
}

function showLoader() {
    var button = document.querySelector('.btn-primary[type="submit"]');
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-small"></span> Iniciando sesión...';
    }
}

function initLoginForm(form) {
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var emailInput = document.getElementById('correo');
        var passwordInput = document.getElementById('password');
        if (!emailInput || !passwordInput) return;

        var email = emailInput.value.trim();
        var password = passwordInput.value.trim();

        if (!email || !password) {
            e.preventDefault();
            showToast('Por favor completa todos los campos', 'error');
            return false;
        }

        if (!isValidEmail(email)) {
            e.preventDefault();
            showToast('Email inválido', 'error');
            return false;
        }

        showLoader();
        return true;
    });

    var inputs = form.querySelectorAll('input');
    inputs.forEach(function (input) {
        input.addEventListener('input', function () {
            clearFieldError(this);
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var loginForm = document.getElementById('loginForm');
    if (loginForm) initLoginForm(loginForm);
});
