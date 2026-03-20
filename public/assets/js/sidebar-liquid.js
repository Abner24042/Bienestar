/**
 * BIENIESTAR - Liquid blob indicator para el sidebar
 * Se mueve con spring physics y morfea mientras se desplaza.
 */
(function () {
    'use strict';

    var sidebar    = document.querySelector('.sidebar');
    var sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebar || !sidebarNav) return;

    // ── SVG fixed (así el overflow:auto del sidebar no lo recorta) ────────────
    var NS  = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:fixed;width:30px;pointer-events:none;z-index:300;overflow:visible;top:0;left:0;';

    var blobEl = document.createElementNS(NS, 'path');
    blobEl.style.filter = 'drop-shadow(3px 0 7px rgba(255,107,53,0.38))';
    svg.appendChild(blobEl);
    document.body.appendChild(svg);

    // BH se mide dinámicamente desde el primer nav-item real
    var BH = 54;
    var firstItem = sidebarNav.querySelector('.nav-item');
    if (firstItem) BH = firstItem.getBoundingClientRect().height;

    // ── Constructor del path ──────────────────────────────────────────────────
    // x=0 → borde derecho del sidebar  /  x positivo → entra al contenido
    // La cara derecha del blob es CÓNCAVA gracias al punto de control tirado a la izquierda
    function buildPath(topY, botY, stretch) {
        var h       = botY - topY;
        var midY    = (topY + botY) / 2;
        var W       = 28;                         // ancho máximo del blob
        var concave = 8 - stretch * 7;            // cuanto se hunde la cara derecha
        var tOff    = h * 0.22;                   // profundidad de las curvas top/bot

        return [
            'M 0,' + topY,
            'C 8,' + topY + ' ' + W + ',' + (topY + tOff * 0.4) + ' ' + W + ',' + (topY + tOff),
            'C ' + W + ',' + (midY - tOff * 0.4) + ' ' + concave + ',' + midY + ' ' + W + ',' + (midY + tOff * 0.4),
            'C ' + W + ',' + (botY - tOff) + ' 8,' + botY + ' 0,' + botY,
            'Z'
        ].join(' ');
    }

    // ── Métricas del sidebar ──────────────────────────────────────────────────
    var svgOffsetLeft = 0;
    var svgOffsetTop  = 0;

    function reposition() {
        var r = sidebar.getBoundingClientRect();
        svgOffsetLeft = r.right - 1;
        svgOffsetTop  = r.top;
        svg.style.left   = svgOffsetLeft + 'px';
        svg.style.top    = svgOffsetTop  + 'px';
        svg.style.height = r.height + 'px';
    }

    // Centro Y del item (basado en el icono SVG si existe) relativo al top del sidebar
    function itemCenterY(item) {
        var sr   = sidebar.getBoundingClientRect();
        var icon = item.querySelector('svg');
        var ref  = icon ? icon.getBoundingClientRect() : item.getBoundingClientRect();
        return (ref.top - sr.top) + ref.height / 2;
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

    // ── Init ─────────────────────────────────────────────────────────────────
    reposition();

    var activeItem = sidebarNav.querySelector('.nav-item.active');
    if (activeItem) {
        centerY = targetY = itemCenterY(activeItem);
        blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
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

    // ── Resize / scroll del sidebar ───────────────────────────────────────────
    window.addEventListener('resize', function () {
        reposition();
        if (activeItem) {
            centerY = targetY = itemCenterY(activeItem);
            blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
        }
    }, { passive: true });

    sidebar.addEventListener('scroll', function () {
        reposition();
        if (activeItem) {
            centerY = targetY = itemCenterY(activeItem);
            blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
        }
    }, { passive: true });

})();
