
document.addEventListener('DOMContentLoaded', function () {
    initDashboard();
    animateStats();
    loadMentalStatus();
    loadNextAppointment();
});

/**
 * Inicializar dashboard
 */
function initDashboard() {
    // Animar cards al entrar
    animateCardsOnScroll();

    // Gráficas simples (si se requieren)
    initSimpleCharts();

    // Actualizar hora actual
    updateCurrentTime();
    setInterval(updateCurrentTime, 60000); // Cada minuto
}

/**
 * Animar estadísticas con contador
 */
function animateStats() {
    const statValues = document.querySelectorAll('.stat-value');

    statValues.forEach(stat => {
        const text = stat.textContent;
        const number = parseInt(text.replace(/\D/g, ''));

        if (!isNaN(number) && number > 0) {
            animateValue(stat, 0, number, 1500);
        }
    });
}

/**
 * Animar valor numérico
 */
function animateValue(element, start, end, duration) {
    const text = element.textContent;
    const suffix = text.replace(/[0-9]/g, '').trim();

    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const current = Math.floor(progress * (end - start) + start);

        element.textContent = current + ' ' + suffix;

        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

/**
 * Animar cards al hacer scroll
 */
function animateCardsOnScroll() {
    const cards = document.querySelectorAll('.stat-card, .action-card');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '0';
                entry.target.style.transform = 'translateY(30px)';

                setTimeout(() => {
                    entry.target.style.transition = 'all 0.6s ease';
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, 100);

                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    cards.forEach(card => observer.observe(card));
}

/**
 * Inicializar gráficas simples
 */
function initSimpleCharts() {
    // Aquí puedes agregar gráficas con Chart.js si lo necesitas
    // Charts ready
}

/**
 * Actualizar hora actual
 */
function updateCurrentTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('es-MX', {
        hour: '2-digit',
        minute: '2-digit'
    });

    const timeElement = document.getElementById('currentTime');
    if (timeElement) {
        timeElement.textContent = timeString;
    }
}

/**
 * Obtener saludo según la hora
 */
function getGreeting() {
    const hour = new Date().getHours();

    if (hour < 12) return '¡Buenos días';
    if (hour < 18) return '¡Buenas tardes';
    return '¡Buenas noches';
}

/**
 * Mostrar notificación en el dashboard
 */
function showDashboardNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `dashboard-notification notification-${type}`;
    notification.innerHTML = `
        <span class="notification-icon">${getNotificationIcon(type)}</span>
        <span class="notification-message">${message}</span>
        <button class="notification-close">&times;</button>
    `;

    document.body.appendChild(notification);

    // Mostrar
    setTimeout(() => notification.classList.add('show'), 100);

    // Botón de cerrar
    notification.querySelector('.notification-close').addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });

    // Auto-ocultar
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 5000);
}

async function loadMentalStatus() {
    const nivelEl = document.getElementById('mentalNivel');
    const labelEl = document.getElementById('mentalLabel');
    if (!nivelEl) return;

    // resultado del test no cambia con frecuencia, cache 10 minutos
    const cached = AppCache.get('mental_status');
    if (cached) {
        nivelEl.textContent = cached.data.nivel;
        labelEl.textContent = cached.data.hace + ' · Test de bienestar';
        return;
    }

    try {
        const resp = await fetch(API_URL + '/test/last');
        const data = await resp.json();

        if (data.success && data.result) {
            const r = data.result;
            nivelEl.textContent = r.nivel;
            labelEl.textContent = r.hace + ' · Test de bienestar';
            AppCache.set('mental_status', r, 10 * 60 * 1000);
        } else {
            nivelEl.textContent = 'Sin datos';
            labelEl.textContent = 'Realiza el test de bienestar';
        }
    } catch (e) {
        nivelEl.textContent = '—';
        labelEl.textContent = 'No disponible';
    }
}

async function loadNextAppointment() {
    const citaDiaEl  = document.getElementById('citaDia');
    const citaMesEl  = document.getElementById('citaMes');
    const citaLabelEl = document.getElementById('citaLabel');
    const citaDescEl = document.getElementById('citaDescripcion');
    const badgeEl    = document.getElementById('citaDateBadge');
    if (!citaDiaEl) return;

    // citas: cache 5 minutos (pueden agendarse durante la sesion)
    const cached = AppCache.get('next_appointment');
    if (cached) {
        const d = cached.data;
        citaDiaEl.textContent = d.dia;
        citaMesEl.textContent = d.mes;
        if (citaLabelEl) citaLabelEl.textContent = d.titulo + ' · ' + d.hora;
        if (citaDescEl && d.descripcion) { citaDescEl.textContent = d.descripcion; citaDescEl.style.display = ''; }
        return;
    }

    try {
        const resp = await fetch(API_URL + '/appointments/next');
        const data = await resp.json();

        if (data.success && data.found) {
            citaDiaEl.textContent = data.dia;
            citaMesEl.textContent = data.mes;
            if (citaLabelEl) citaLabelEl.textContent = data.titulo + ' · ' + data.hora;
            if (citaDescEl && data.descripcion) {
                citaDescEl.textContent = data.descripcion;
                citaDescEl.style.display = '';
            }
            AppCache.set('next_appointment', data, 5 * 60 * 1000);
        } else {
            if (badgeEl) badgeEl.style.background = 'linear-gradient(160deg, #aaa 0%, #888 100%)';
            citaDiaEl.textContent = '—';
            citaMesEl.textContent = '—';
            if (citaLabelEl) citaLabelEl.textContent = 'No hay citas cercanas';
        }
    } catch (e) {
        if (badgeEl) badgeEl.style.background = 'linear-gradient(160deg, #aaa 0%, #888 100%)';
        citaDiaEl.textContent = '—';
        if (citaLabelEl) citaLabelEl.textContent = 'No disponible';
    }
}

function getNotificationIcon(type) {
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        warning: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>'
    };
    return icons[type] || icons.info;
}

/**
 * Manejo del menú de usuario con delay
 */
document.addEventListener('DOMContentLoaded', function () {
    const headerUser = document.querySelector('.header-user');
    const userMenu = document.querySelector('.user-menu');

    if (headerUser && userMenu) {
        let menuTimeout;

        // Mostrar menú al hacer hover en el usuario
        headerUser.addEventListener('mouseenter', function () {
            clearTimeout(menuTimeout);
            userMenu.style.display = 'block';
            setTimeout(() => {
                userMenu.style.opacity = '1';
                userMenu.style.transform = 'translateY(0)';
            }, 10);
        });

        // Ocultar menú con delay al salir del usuario
        headerUser.addEventListener('mouseleave', function () {
            menuTimeout = setTimeout(() => {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    userMenu.style.display = 'none';
                }, 200);
            }, 300); // 300ms de delay antes de cerrar
        });

        // Mantener abierto si el cursor está sobre el menú
        userMenu.addEventListener('mouseenter', function () {
            clearTimeout(menuTimeout);
        });

        // Cerrar cuando el cursor sale del menú
        userMenu.addEventListener('mouseleave', function () {
            menuTimeout = setTimeout(() => {
                userMenu.style.opacity = '0';
                userMenu.style.transform = 'translateY(-10px)';
                setTimeout(() => {
                    userMenu.style.display = 'none';
                }, 200);
            }, 200);
        });
    }
});