
// Ripple effect en botones
document.addEventListener('click', function (e) {
    var btn = e.target.closest('.btn, .filter-btn, .fav-tab');
    if (!btn || btn.disabled) return;
    var r = btn.getBoundingClientRect();
    var size = Math.max(r.width, r.height);
    var el = document.createElement('span');
    el.className = 'btn-ripple';
    el.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + (e.clientX - r.left - size / 2) + 'px;top:' + (e.clientY - r.top - size / 2) + 'px;';
    btn.style.position = btn.style.position || 'relative';
    btn.style.overflow = 'hidden';
    btn.appendChild(el);
    setTimeout(function () { el.remove(); }, 600);
});

document.addEventListener('DOMContentLoaded', function () {
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
    if (!toggle) return;

    updateDarkModeIcon();

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        toggle.classList.remove('spinning');
        void toggle.offsetWidth;
        toggle.classList.add('spinning');
        setTimeout(function () { toggle.classList.remove('spinning'); }, 450);

        const html = document.documentElement;
        const isDark = html.getAttribute('data-theme') === 'dark';

        if (isDark) {
            html.removeAttribute('data-theme');
            localStorage.setItem('bieniestar-theme', 'light');
        } else {
            html.setAttribute('data-theme', 'dark');
            localStorage.setItem('bieniestar-theme', 'dark');
        }

        updateDarkModeIcon();

        if (typeof renderCalendar === 'function') renderCalendar();
    });
}

function updateDarkModeIcon() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const moon = document.getElementById('darkIconMoon');
    const sun = document.getElementById('darkIconSun');
    if (moon) moon.style.display = isDark ? 'none' : 'block';
    if (sun) sun.style.display = isDark ? 'block' : 'none';
}