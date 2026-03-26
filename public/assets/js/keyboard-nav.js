
(function () {
    'use strict';

    // ── 1. Enter/Space activa divs clickeables con tabindex="0" ───────────────
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var el = document.activeElement;
        var tag = el ? el.tagName.toLowerCase() : '';
        if ((tag === 'div' || tag === 'article') && el.getAttribute('tabindex') === '0') {
            if (e.key === ' ') e.preventDefault();
            el.click();
        }
    });

    // ── 2. Escape cierra modales abiertos ────────────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        // Modales estándar (.modal.active o display:block)
        var modal = document.querySelector('.modal.active');
        if (!modal) {
            // Buscar modal visible por estilo inline
            document.querySelectorAll('.modal').forEach(function (m) {
                if (m.style.display === 'block' || m.style.display === 'flex') modal = m;
            });
        }
        if (modal) {
            var closeBtn = modal.querySelector('.modal-close, [data-close-modal]');
            if (closeBtn) { closeBtn.click(); return; }
        }
        // Overlay de favoritos/ejercicios con clase propia
        var overlay = document.querySelector('[id$="Modal"][style*="flex"], [id$="Modal"][style*="block"]');
        if (overlay) {
            var btn = overlay.querySelector('.modal-close, .close-btn');
            if (btn) btn.click();
        }
    });

    // ── 3. Focus trap dentro de modales abiertos ──────────────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab') return;
        var modal = document.querySelector('.modal.active');
        if (!modal) {
            document.querySelectorAll('.modal').forEach(function (m) {
                if (m.style.display === 'block' || m.style.display === 'flex') modal = m;
            });
        }
        if (!modal) return;

        var focusable = Array.from(modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), ' +
            'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) { return el.offsetParent !== null; });

        if (focusable.length < 2) return;
        var first = focusable[0], last = focusable[focusable.length - 1];

        if (e.shiftKey && document.activeElement === first) {
            e.preventDefault(); last.focus();
        } else if (!e.shiftKey && document.activeElement === last) {
            e.preventDefault(); first.focus();
        }
    });

    // ── 4. Foco inicial al abrir modal ────────────────────────────────────────
    // Observa cuándo un modal se hace visible y mueve el foco al close button
    var _observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mut) {
            if (mut.type !== 'attributes') return;
            var el = mut.target;
            if (!el.classList.contains('modal') && !el.id.endsWith('Modal')) return;
            var visible = el.classList.contains('active') ||
                el.style.display === 'block' ||
                el.style.display === 'flex';
            if (visible) {
                var btn = el.querySelector('.modal-close, [autofocus]');
                if (btn) setTimeout(function () { btn.focus(); }, 50);
            }
        });
    });
    _observer.observe(document.body, { attributes: true, attributeFilter: ['class', 'style'], subtree: true });

    // ── 5. Teclas de flecha para grupos de tabs / filtros ─────────────────────
    document.addEventListener('keydown', function (e) {
        if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
        var el = document.activeElement;
        if (!el) return;

        var isTab = el.classList.contains('plan-tab') ||
            el.classList.contains('fav-tab') ||
            el.classList.contains('filter-btn');
        if (!isTab) return;

        e.preventDefault();
        var siblings = Array.from(el.parentElement.querySelectorAll(
            '.plan-tab, .fav-tab, .filter-btn'
        ));
        var idx = siblings.indexOf(el);
        var next = e.key === 'ArrowRight'
            ? siblings[(idx + 1) % siblings.length]
            : siblings[(idx - 1 + siblings.length) % siblings.length];
        if (next) { next.focus(); next.click(); }
    });

    // ── 6. Menú de usuario con teclado ────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        var headerUser = document.querySelector('.header-user');
        var userMenu = document.querySelector('.user-menu');
        if (!headerUser || !userMenu) return;

        // Hacer el área de usuario focusable
        headerUser.setAttribute('tabindex', '0');
        headerUser.setAttribute('role', 'button');
        headerUser.setAttribute('aria-haspopup', 'menu');
        headerUser.setAttribute('aria-expanded', 'false');
        headerUser.setAttribute('aria-label', 'Menú de usuario');

        function openMenu() {
            userMenu.style.display = 'block';
            requestAnimationFrame(function () {
                userMenu.style.opacity = '1';
                userMenu.style.transform = 'translateY(0)';
            });
            headerUser.setAttribute('aria-expanded', 'true');
            var first = userMenu.querySelector('a');
            if (first) setTimeout(function () { first.focus(); }, 50);
        }

        function closeMenu() {
            userMenu.style.opacity = '0';
            userMenu.style.transform = 'translateY(-10px)';
            setTimeout(function () { userMenu.style.display = 'none'; }, 200);
            headerUser.setAttribute('aria-expanded', 'false');
        }

        headerUser.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openMenu(); }
            if (e.key === 'Escape') closeMenu();
            if (e.key === 'ArrowDown') { e.preventDefault(); openMenu(); }
        });

        userMenu.addEventListener('keydown', function (e) {
            var items = Array.from(userMenu.querySelectorAll('a'));
            var idx = items.indexOf(document.activeElement);
            if (e.key === 'ArrowDown') { e.preventDefault(); items[(idx + 1) % items.length].focus(); }
            if (e.key === 'ArrowUp') { e.preventDefault(); items[(idx - 1 + items.length) % items.length].focus(); }
            if (e.key === 'Escape') { closeMenu(); headerUser.focus(); }
            if (e.key === 'Tab') { closeMenu(); }
        });
    });

})();
