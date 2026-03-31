
document.addEventListener('DOMContentLoaded', function () {
    const menuBtn    = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    if (!menuBtn || !mobileMenu) return;

    const menuPanel   = mobileMenu.querySelector('.mobile-menu-panel');
    const menuOverlay = mobileMenu.querySelector('.mobile-menu-overlay');

    // el panel empieza cerrado: inert lo saca del tab order Y del arbol de accesibilidad
    // sin esto los links del menu reciben foco con Tab aunque el panel este oculto visualmente
    menuPanel.inert = true;
    menuPanel.id    = 'mobileMenuPanel';

    // aria-expanded le dice al lector de pantalla si el menu esta abierto o cerrado
    menuBtn.setAttribute('aria-expanded', 'false');
    menuBtn.setAttribute('aria-controls', 'mobileMenuPanel');

    function openMenu() {
        menuPanel.inert = false; // restaura foco y accesibilidad del panel
        menuBtn.classList.add('open');
        mobileMenu.classList.add('active');
        menuBtn.setAttribute('aria-expanded', 'true');
        menuBtn.setAttribute('aria-label', 'Cerrar menú');
        document.body.style.overflow = 'hidden';

        // manda el foco al primer elemento interactivo del panel
        const firstFocusable = menuPanel.querySelector('a, button');
        if (firstFocusable) firstFocusable.focus();
    }

    function closeMenu() {
        menuPanel.inert = true; // vuelve a bloquear foco del panel
        menuBtn.classList.remove('open');
        mobileMenu.classList.remove('active');
        menuBtn.setAttribute('aria-expanded', 'false');
        menuBtn.setAttribute('aria-label', 'Abrir menú');
        document.body.style.overflow = '';

        // regresa el foco al boton hamburguesa para no perder el contexto del usuario
        menuBtn.focus();
    }

    menuBtn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        mobileMenu.classList.contains('active') ? closeMenu() : openMenu();
    });

    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeMenu);
    }

    // cerrar al navegar a otra pagina
    mobileMenu.querySelectorAll('.mobile-menu-item').forEach(item => {
        item.addEventListener('click', () => setTimeout(closeMenu, 80));
    });

    // cerrar con Escape - comportamiento estandar de dialogs/menus
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });

    // focus trap: mientras el menu esta abierto, Tab y Shift+Tab solo ciclan dentro del panel
    // sin esto el usuario puede tabular hasta elementos detras del overlay
    mobileMenu.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return;

        const focusables = Array.from(menuPanel.querySelectorAll(
            'a[href], button:not([disabled])'
        ));
        if (focusables.length === 0) return;

        const first = focusables[0];
        const last  = focusables[focusables.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            // Shift+Tab desde el primero salta al ultimo
            e.preventDefault();
            last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            // Tab desde el ultimo vuelve al primero
            e.preventDefault();
            first.focus();
        }
    });

    // si la ventana crece a desktop y el menu estaba abierto, lo cerramos
    window.addEventListener('resize', function () {
        if (window.innerWidth > 992 && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });
});
