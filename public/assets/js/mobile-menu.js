/**
 * Menú Hamburguesa Móvil - BIENIESTAR
 * JavaScript para el nuevo menú móvil
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('📱 Inicializando menú móvil...');

    const menuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const menuOverlay = document.querySelector('.mobile-menu-overlay');
    const menuItems = document.querySelectorAll('.mobile-menu-item');

    if (!menuBtn || !mobileMenu) {
        console.error('❌ No se encontraron los elementos del menú móvil');
        return;
    }

    console.log('✅ Menú móvil encontrado');

    // Abrir/cerrar menú
    menuBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const isOpen = mobileMenu.classList.contains('active');

        if (isOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    });

    // Cerrar al hacer clic en el overlay
    if (menuOverlay) {
        menuOverlay.addEventListener('click', closeMenu);
    }

    // Cerrar al hacer clic en un enlace
    menuItems.forEach(item => {
        item.addEventListener('click', function() {
            // Dar tiempo para que se inicie la navegación antes de cerrar
            setTimeout(closeMenu, 100);
        });
    });

    // Cerrar con la tecla Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });

    // Funciones auxiliares
    function openMenu() {
        console.log('🍔 Abriendo menú móvil');
        menuBtn.classList.add('open');
        mobileMenu.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
        console.log('✖️ Cerrando menú móvil');
        menuBtn.classList.remove('open');
        mobileMenu.classList.remove('active');
        document.body.style.overflow = '';
    }

    // Cerrar menú si la ventana se redimensiona a escritorio
    window.addEventListener('resize', function() {
        if (window.innerWidth > 992 && mobileMenu.classList.contains('active')) {
            closeMenu();
        }
    });

    console.log('✅ Menú móvil inicializado correctamente');
});
