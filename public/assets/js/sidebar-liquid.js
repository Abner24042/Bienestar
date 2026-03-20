/**
 * BIENIESTAR - Liquid blob indicator para el sidebar
 * Cubre el item completo con forma líquida cóncava en el borde derecho.
 */
(function () {
    'use strict';

    var sidebar    = document.querySelector('.sidebar');
    var sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebar || !sidebarNav) return;

    // ── SVG absoluto dentro del sidebar-nav ───────────────────────────────────
    var NS  = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;overflow:visible;z-index:0;';

    var blobEl = document.createElementNS(NS, 'path');
    blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
    svg.appendChild(blobEl);

    // Insertar como primer hijo para que quede detrás del texto
    sidebarNav.style.position = 'relative';
    sidebarNav.insertBefore(svg, sidebarNav.firstChild);

    // ── Dimensiones ───────────────────────────────────────────────────────────
    var sW  = 0;   // ancho del sidebar-nav
    var BH  = 54;  // altura del blob (= altura del nav item)

    function measure() {
        var r = sidebarNav.getBoundingClientRect();
        sW  = r.width;
        var first = sidebarNav.querySelector('.nav-item');
        if (first) BH = first.getBoundingClientRect().height;
    }

    // ── Constructor del path ──────────────────────────────────────────────────
    // El blob va de x=0 (borde izq del sidebar) hasta cerca del borde der,
    // con una mordida cóncava líquida en el lado derecho.
    function buildPath(topY, botY, stretch) {
        var h        = botY - topY;
        var midY     = (topY + botY) / 2;
        var rightX   = sW - 2;                   // borde derecho del blob
        var concave  = sW - 2 - 18 + stretch * 8; // profundidad de la concavidad
        var tOff     = h * 0.22;
        var r        = 10;                        // radio de esquinas izq

        return [
            // Esquina superior izquierda redondeada
            'M', 0, topY + r,
            'Q', 0, topY, r, topY,
            // Curva superior → borde derecho
            'C', rightX * 0.5, topY,  rightX, topY + tOff * 0.4,  rightX, topY + tOff,
            // Cara derecha CÓNCAVA (el punto de control tira hacia la izq)
            'C', rightX, midY - tOff * 0.4,  concave, midY,  rightX, midY + tOff * 0.4,
            // Curva inferior → borde izquierdo
            'C', rightX, botY - tOff,  rightX * 0.5, botY,  r, botY,
            // Esquina inferior izquierda redondeada
            'Q', 0, botY, 0, botY - r,
            'Z'
        ].join(' ');
    }

    // ── Spring animation ──────────────────────────────────────────────────────
    var centerY = 0;
    var targetY = 0;
    var vel     = 0;
    var rafId   = null;

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

    // Centro Y de un item relativo al top del sidebar-nav
    function itemCenterY(item) {
        var nr = sidebarNav.getBoundingClientRect();
        var ir = item.getBoundingClientRect();
        return (ir.top - nr.top) + ir.height / 2;
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    measure();

    var activeItem = sidebarNav.querySelector('.nav-item.active');
    if (activeItem) {
        centerY = targetY = itemCenterY(activeItem);
        blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
    } else {
        blobEl.setAttribute('fill', 'transparent');
    }

    // ── Hover ─────────────────────────────────────────────────────────────────
    sidebarNav.querySelectorAll('.nav-item').forEach(function (item) {
        item.addEventListener('mouseenter', function () {
            blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
            moveTo(itemCenterY(item));
        });
    });

    sidebarNav.addEventListener('mouseleave', function () {
        if (activeItem) moveTo(itemCenterY(activeItem));
        else blobEl.setAttribute('fill', 'transparent');
    });

    // ── Resize ───────────────────────────────────────────────────────────────
    window.addEventListener('resize', function () {
        measure();
        if (activeItem) {
            centerY = targetY = itemCenterY(activeItem);
            blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
        }
    }, { passive: true });

})();
