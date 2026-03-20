/**
 * BIENIESTAR - Liquid swipe indicator para el sidebar
 * El blob vive en .sidebar (z-index 0) y sidebar-nav vive en z-index 1,
 * así los items siempre son visibles encima del blob.
 */
(function () {
    'use strict';

    var sidebar    = document.querySelector('.sidebar');
    var sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebar || !sidebarNav) return;

    // ── SVG wrapper dentro del sidebar (NO del sidebar-nav) ──────────────────
    // sidebar-nav queda en z-index:1, el blob en z-index:0 → items siempre visibles
    var NS     = 'http://www.w3.org/2000/svg';
    var wrapper = document.createElement('div');
    wrapper.style.cssText = 'position:absolute;inset:0;z-index:0;pointer-events:none;overflow:hidden;';

    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;overflow:visible;';

    var blobEl = document.createElementNS(NS, 'path');
    blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
    svg.appendChild(blobEl);
    wrapper.appendChild(svg);

    // sidebar necesita position para contener el wrapper
    if (!sidebar.style.position) sidebar.style.position = 'relative';
    sidebar.insertBefore(wrapper, sidebar.firstChild);

    // sidebar-nav sobre el blob
    sidebarNav.style.position = 'relative';
    sidebarNav.style.zIndex   = '1';

    // ── Dimensiones ───────────────────────────────────────────────────────────
    var sW = 0, BH = 54;

    function measure() {
        sW = sidebar.getBoundingClientRect().width;
        var first = sidebarNav.querySelector('.nav-item');
        if (first) BH = Math.round(first.getBoundingClientRect().height) || 54;
    }

    // ── Construcción del path ─────────────────────────────────────────────────
    // Coordenadas relativas al TOP del sidebar (no del sidebarNav).
    // La cara DERECHA del blob es cóncava (mordida líquida).
    function buildPath(topY, botY, stretch) {
        var h    = botY - topY;
        var midY = (topY + botY) / 2;
        var rX   = sW - 2;                 // borde derecho del blob
        var bite = 18 - stretch * 10;      // profundidad concavidad
        var cX   = rX - bite;
        var tOff = h * 0.18;
        var r    = 8;                       // radio esquinas izq

        return [
            'M', 0, topY + r,
            'Q', 0, topY,     r, topY,
            'C', rX * 0.45, topY,  rX, topY + tOff,  rX, topY + tOff * 2,
            'C', rX, midY - tOff,  cX, midY,          rX, midY + tOff,
            'C', rX, botY - tOff * 2,  rX * 0.45, botY,  r, botY,
            'Q', 0, botY,   0, botY - r,
            'Z'
        ].join(' ');
    }

    // ── Centro Y de un item relativo al TOP del sidebar ───────────────────────
    // (el SVG está dentro del sidebar, no del sidebarNav)
    function itemCenterY(item) {
        var sr = sidebar.getBoundingClientRect();
        var ir = item.getBoundingClientRect();
        return (ir.top - sr.top) + ir.height / 2;
    }

    // ── Spring animation ──────────────────────────────────────────────────────
    var centerY = 0, targetY = 0, vel = 0, rafId = null;

    function tick() {
        var d = targetY - centerY;
        vel     = vel * 0.62 + d * 0.22;
        centerY += vel;

        var stretch = Math.min(Math.abs(vel) / 30, 1);
        blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, stretch));

        if (Math.abs(vel) > 0.15 || Math.abs(d) > 0.2) {
            rafId = requestAnimationFrame(tick);
        } else {
            centerY = targetY;
            blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
            rafId = null;
        }
    }

    function moveTo(y) {
        targetY = y;
        if (!rafId) rafId = requestAnimationFrame(tick);
    }

    // ── Init (doble RAF para layout estable) ──────────────────────────────────
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            measure();

            var activeItem = sidebarNav.querySelector('.nav-item.active');
            if (activeItem) {
                centerY = targetY = itemCenterY(activeItem);
                blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
                sidebarNav.classList.add('liquid-ready');
            }

            // Hover: blob sigue al cursor
            sidebarNav.querySelectorAll('.nav-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    sidebarNav.classList.add('liquid-ready');
                    moveTo(itemCenterY(item));
                });
            });

            // Al salir: vuelve al activo
            sidebarNav.addEventListener('mouseleave', function () {
                if (activeItem) moveTo(itemCenterY(activeItem));
            });

            // Resize
            window.addEventListener('resize', function () {
                measure();
                if (activeItem) {
                    centerY = targetY = itemCenterY(activeItem);
                    blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
                }
            }, { passive: true });

            // Scroll del sidebar
            sidebar.addEventListener('scroll', function () {
                if (activeItem) {
                    centerY = targetY = itemCenterY(activeItem);
                    blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
                }
            }, { passive: true });
        });
    });

})();
