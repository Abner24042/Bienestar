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
    // Blob rectangular que cubre todo el item, con concavidad líquida solo en borde derecho.
    function buildPath(topY, botY, stretch) {
        var h    = botY - topY;
        var midY = (topY + botY) / 2;
        var rX   = sW - 2;               // borde derecho
        var bite = 20 - stretch * 12;    // profundidad de la mordida cóncava
        var cX   = rX - bite;
        var r    = 8;                    // radio esquinas izquierdas
        var cr   = Math.min(10, h * 0.2); // radio curvas esquinas derechas

        return [
            // Esquina superior izquierda
            'M', 0, topY + r,
            'Q', 0, topY,  r, topY,
            // Línea recta por arriba hasta cerca del borde derecho
            'L', rX - cr, topY,
            // Esquina superior derecha redondeada
            'Q', rX, topY,  rX, topY + cr,
            // Cara derecha CÓNCAVA: de (rX, topY+cr) baja hasta (rX, botY-cr) con mordida en el medio
            'C', rX, midY - h * 0.15,  cX, midY,  rX, midY + h * 0.15,
            // Esquina inferior derecha redondeada
            'Q', rX, botY,  rX - cr, botY,
            // Línea recta por abajo de vuelta a la izquierda
            'L', r, botY,
            // Esquina inferior izquierda
            'Q', 0, botY,  0, botY - r,
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
            // Cuando el blob se aleja del item activo, ese item necesita colores legibles
            sidebarNav.querySelectorAll('.nav-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    sidebarNav.classList.add('liquid-ready');
                    // Si el blob se va del item activo, marcar para mostrar fallback
                    if (activeItem && item !== activeItem) {
                        sidebarNav.classList.add('blob-away');
                    } else {
                        sidebarNav.classList.remove('blob-away');
                    }
                    moveTo(itemCenterY(item));
                });
            });

            // Al salir: vuelve al activo, quitar blob-away
            sidebarNav.addEventListener('mouseleave', function () {
                sidebarNav.classList.remove('blob-away');
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
