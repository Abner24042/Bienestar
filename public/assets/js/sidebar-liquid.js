/**
 * BIENIESTAR - Liquid blob indicator para el sidebar
 * Fallback CSS naranja → blob JS toma el control al estar listo.
 */
(function () {
    'use strict';

    var sidebar    = document.querySelector('.sidebar');
    var sidebarNav = document.querySelector('.sidebar-nav');
    if (!sidebar || !sidebarNav) return;

    var NS  = 'http://www.w3.org/2000/svg';
    var svg = document.createElementNS(NS, 'svg');
    svg.setAttribute('aria-hidden', 'true');
    svg.style.cssText = 'position:absolute;left:0;top:0;width:100%;height:100%;pointer-events:none;overflow:visible;z-index:0;';

    var blobEl = document.createElementNS(NS, 'path');
    svg.appendChild(blobEl);

    sidebarNav.style.position = 'relative';
    sidebarNav.insertBefore(svg, sidebarNav.firstChild);

    var sW = 0;
    var BH = 54;

    function measure() {
        sW = sidebarNav.getBoundingClientRect().width;
        var first = sidebarNav.querySelector('.nav-item');
        if (first) BH = first.getBoundingClientRect().height || 54;
    }

    // x=0 → borde izq del sidebar-nav  |  borde derecho = sW con mordida cóncava
    function buildPath(topY, botY, stretch) {
        var h       = botY - topY;
        var midY    = (topY + botY) / 2;
        var rX      = sW - 2;                    // borde derecho
        var tOff    = h * 0.15;                  // curvas top/bot (más pequeño = más lleno)
        // profundidad de la concavidad: se agranda mientras más rápido se mueve
        var bite    = 16 - stretch * 8;
        var cX      = rX - bite;
        var r       = 8;                          // radio esquinas izquierdas

        return [
            'M', 0, (topY + r),
            'Q', 0, topY,   r, topY,
            'C', rX * 0.4, topY,   rX, topY + tOff,   rX, topY + tOff * 2,
            'C', rX, midY - tOff,   cX, midY,   rX, midY + tOff,
            'C', rX, botY - tOff * 2,   rX * 0.4, botY,   r, botY,
            'Q', 0, botY,   0, (botY - r),
            'Z'
        ].join(' ');
    }

    // Centro Y del item relativo al top del sidebarNav (coordenadas absolutas dentro del nav)
    function itemCenterY(item) {
        var nr = sidebarNav.getBoundingClientRect();
        var ir = item.getBoundingClientRect();
        return (ir.top - nr.top) + (ir.height / 2);
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

    // ── Init (doble RAF para asegurar layout estable) ─────────────────────────
    requestAnimationFrame(function () {
        requestAnimationFrame(function () {
            measure();

            var activeItem = sidebarNav.querySelector('.nav-item.active');
            if (activeItem) {
                centerY = targetY = itemCenterY(activeItem);
                blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
                blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
                // Activar modo blob: el CSS cambia a texto blanco y fondo transparente
                sidebarNav.classList.add('liquid-ready');
            }

            // ── Hover ─────────────────────────────────────────────────────────
            sidebarNav.querySelectorAll('.nav-item').forEach(function (item) {
                item.addEventListener('mouseenter', function () {
                    blobEl.setAttribute('fill', 'var(--color-primary, #ff6b35)');
                    sidebarNav.classList.add('liquid-ready');
                    moveTo(itemCenterY(item));
                });
            });

            sidebarNav.addEventListener('mouseleave', function () {
                if (activeItem) moveTo(itemCenterY(activeItem));
            });

            // ── Resize ────────────────────────────────────────────────────────
            window.addEventListener('resize', function () {
                measure();
                if (activeItem) {
                    centerY = targetY = itemCenterY(activeItem);
                    blobEl.setAttribute('d', buildPath(centerY - BH / 2, centerY + BH / 2, 0));
                }
            }, { passive: true });
        });
    });

})();
