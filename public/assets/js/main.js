/**
 * BIENIESTAR - JavaScript Global
 */

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar menú de usuario
    const headerUser = document.querySelector('.header-user');
    const userMenu = document.querySelector('.user-menu');

    if (headerUser && userMenu) {
        let menuTimeout;

        headerUser.addEventListener('mouseenter', () => {
            clearTimeout(menuTimeout);
            userMenu.style.display = 'block';
            requestAnimationFrame(() => {
                userMenu.style.opacity = '1';
                userMenu.style.transform = 'translateY(0)';
            });
        });

        headerUser.addEventListener('mouseleave', () => {
            menuTimeout = setTimeout(() => {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => userMenu.style.display = 'none', 200);
            }, 500);
        });

        userMenu.addEventListener('mouseenter', () => clearTimeout(menuTimeout));

        userMenu.addEventListener('mouseleave', () => {
            menuTimeout = setTimeout(() => {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => userMenu.style.display = 'none', 200);
            }, 200);
        });
    }

    // Dark mode toggle
    initDarkMode();

    // El nuevo menú hamburguesa se cargará desde mobile-menu.js
});

function initDarkMode() {
    const toggle = document.getElementById('darkModeToggle');
    const icon = document.getElementById('darkModeIcon');
    if (!toggle || !icon) return;

    // Actualizar icono según tema actual
    updateDarkModeIcon(icon);

    toggle.addEventListener('click', function(e) {
        e.stopPropagation();
        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';

        if (isDark) {
            html.removeAttribute('data-theme');
            localStorage.setItem('bieniestar-theme', 'light');
        } else {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('bieniestar-theme', 'dark');
        }

        updateDarkModeIcon(icon);

        // Re-renderizar calendario si existe
        if (typeof renderCalendar === 'function') renderCalendar();
    });
}

function updateDarkModeIcon(icon) {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    icon.textContent = isDark ? '☀️' : '🌙';
}