(function () {
    const SESSION_KEY = 'bieniestar-tutorial-shown-' + (window.CURRENT_USER_ID || 0);

    /* =====================
       TOUR GENERAL (primer acceso)
       ===================== */
    const stepsGeneralUsuario = [
        {
            target: null,
            title: '👋 ¡Bienvenido a BIENIESTAR!',
            description: 'Tu plataforma integral de salud, alimentación y ejercicio. En este recorrido rápido te mostraremos todo lo que puedes hacer.',
        },
        {
            target: '.sidebar',
            title: '🧭 Navegación Principal',
            description: 'Desde aquí accedes a todas las secciones. El menú siempre está disponible en el lado izquierdo.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="dashboard"]',
            title: '📊 Dashboard',
            description: 'Tu panel central con un resumen de salud, próxima cita y plan del día.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="alimentacion"]',
            title: '🥗 Alimentación',
            description: 'Explora recetas saludables y filtras por categoría. Tu nutriólogo puede asignarte un plan específico.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="ejercicio"]',
            title: '💪 Ejercicio',
            description: 'Descubre rutinas adaptadas. Filtra por tipo, nivel y duración según tus objetivos.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="salud-mental"]',
            title: '🧠 Salud Mental',
            description: 'Realiza tests de bienestar, practica mindfulness y lee consejos para tu salud emocional.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="mi-plan"]',
            title: '📋 Mi Plan',
            description: 'El plan personalizado que tu profesional ha diseñado específicamente para ti.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="favoritos"]',
            title: '⭐ Favoritos',
            description: 'Guarda recetas y ejercicios que más te gusten para acceder rápido a ellos.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="citas"]',
            title: '📅 Citas',
            description: 'Agenda consultas con tu coach, nutriólogo o psicólogo.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="chat"]',
            title: '💬 Mensajes',
            description: 'Chatea directamente con tu especialista de forma privada.',
            position: 'right',
        },
        {
            target: '.header-user',
            title: '👤 Tu Perfil',
            description: 'Actualiza tus datos, foto y preferencias. También está el modo oscuro aquí.',
            position: 'bottom-left',
        },
        {
            target: null,
            title: '🎉 ¡Listo para empezar!',
            description: 'Eso es todo. Puedes ver el tutorial de cualquier página en cualquier momento con el botón "?" flotante o desde tu menú de perfil.',
        },
    ];

    const stepsGeneralProfesional = [
        {
            target: null,
            title: '👋 ¡Bienvenido, Especialista!',
            description: 'Tu espacio de trabajo en BIENIESTAR para gestionar usuarios y coordinar su bienestar.',
        },
        {
            target: '.sidebar a[href*="profesional"]',
            title: '🏥 Panel Profesional',
            description: 'Tu centro de control: gestiona usuarios, asigna planes, crea recomendaciones y revisa solicitudes.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="citas"]',
            title: '📅 Citas',
            description: 'Gestiona y confirma las citas que tus usuarios agenden contigo.',
            position: 'right',
        },
        {
            target: '.sidebar a[href*="chat"]',
            title: '💬 Mensajes',
            description: 'Comunícate con tus usuarios. Puedes iniciar conversaciones nuevas desde el botón "+".',
            position: 'right',
        },
        {
            target: '.header-user',
            title: '👤 Tu Perfil',
            description: 'Mantén actualizado tu perfil profesional; los usuarios lo ven al buscar especialistas.',
            position: 'bottom-left',
        },
        {
            target: null,
            title: '🎉 ¡Todo listo!',
            description: 'Puedes ver el tutorial de cualquier página con el botón "?" flotante o desde tu menú de perfil.',
        },
    ];

    /* =====================
       PASOS POR PÁGINA
       ===================== */
    const PAGE_STEPS = {

        dashboard: [
            {
                target: null,
                title: '📊 Tu Dashboard',
                description: 'Aquí tienes un vistazo rápido de todo tu bienestar: estado mental, próxima cita y tu plan del día.',
            },
            {
                target: '#mentalStatCard',
                title: '🧠 Estado Mental',
                description: 'Muestra tu nivel de bienestar mental basado en tu último test. Haz clic para ir a Salud Mental.',
                position: 'bottom',
            },
            {
                target: '#citaCard',
                title: '📅 Próxima Cita',
                description: 'Tu próxima cita agendada. Haz clic para ir a la sección de Citas y ver detalles.',
                position: 'bottom',
            },
            {
                target: '#planSummary',
                title: '📋 Resumen de tu Plan',
                description: 'Cuántos ejercicios, recetas y recomendaciones tiene tu plan actual asignado por tu especialista.',
                position: 'bottom',
            },
            {
                target: '.plan-tabs',
                title: '🗂️ Tabs del Plan',
                description: 'Cambia entre Ejercicios, Recetas y Recomendaciones del día con estas pestañas.',
                position: 'bottom',
            },
            {
                target: '.chat-fab',
                title: '💬 Botón de Mensajes',
                description: 'Acceso rápido al chat con tu especialista sin salir del dashboard.',
                position: 'top',
            },
        ],

        alimentacion: [
            {
                target: null,
                title: '🥗 Alimentación',
                description: 'Explora el catálogo de recetas saludables, filtra por categoría y abre cualquiera para ver los detalles completos.',
            },
            {
                target: '.filters-section',
                title: '🔍 Filtros y Búsqueda',
                description: 'Filtra recetas por categoría (desayuno, almuerzo, cena, etc.) o escribe en el buscador para encontrar algo específico.',
                position: 'bottom',
            },
            {
                target: '#recipesGrid',
                title: '🃏 Tarjetas de Recetas',
                description: 'Haz clic en cualquier tarjeta para ver la receta completa: ingredientes, instrucciones, calorías y más.',
                position: 'top',
            },
            {
                target: '.nutrition-tips',
                title: '💡 Consejos Nutricionales',
                description: 'En la parte inferior encontrarás tips de nutrición para complementar tu alimentación diaria.',
                position: 'top',
            },
        ],

        ejercicio: [
            {
                target: null,
                title: '💪 Ejercicio',
                description: 'Explora el catálogo de ejercicios y rutinas. Cada uno incluye duración, calorías quemadas y músculo objetivo.',
            },
            {
                target: '.filters-section',
                title: '🔍 Filtros y Búsqueda',
                description: 'Filtra por tipo (Cardio, Fuerza, Flexibilidad, Equilibrio) o usa el buscador para encontrar un ejercicio específico.',
                position: 'bottom',
            },
            {
                target: '#exercisesGrid',
                title: '🃏 Tarjetas de Ejercicios',
                description: 'Haz clic en una tarjeta para ver el ejercicio completo: descripción, instrucciones, duración y cómo guardar en favoritos.',
                position: 'top',
            },
            {
                target: '.exercise-benefits',
                title: '✅ Beneficios',
                description: 'Al final de la página encontrarás los beneficios clave de mantenerse activo para motivarte a seguir.',
                position: 'top',
            },
        ],

        'salud-mental': [
            {
                target: null,
                title: '🧠 Salud Mental',
                description: 'Tu espacio para evaluar y cuidar tu bienestar emocional. Aquí encontrarás tests, ejercicios y recursos de apoyo.',
            },
            {
                target: '.sm-hero',
                title: '📝 Test de Bienestar',
                description: 'Realiza el test para evaluar tu estado mental actual. También puedes registrar cómo te sientes hoy con los emojis de ánimo.',
                position: 'bottom',
            },
            {
                target: '.breathing-widget',
                title: '🌬️ Ejercicio de Respiración',
                description: 'Un ejercicio guiado de respiración para reducir el estrés. Presiona "Iniciar" y sigue el ritmo del círculo animado.',
                position: 'bottom',
            },
            {
                target: '.mental-health-resources',
                title: '📚 Recursos de Apoyo',
                description: 'Técnicas de meditación, manejo del estrés y terapia cognitiva. Haz clic en "Ver Técnicas" para expandir cada sección.',
                position: 'top',
            },
            {
                target: '.quick-tips',
                title: '💡 Consejos de Bienestar',
                description: 'Desliza horizontalmente para ver todos los consejos rápidos para mejorar tu salud mental día a día.',
                position: 'top',
            },
        ],

        noticias: [
            {
                target: null,
                title: '📰 Noticias',
                description: 'Mantente informado con artículos y noticias sobre salud, alimentación, ejercicio y bienestar mental.',
            },
            {
                target: '#featuredNews',
                title: '⭐ Noticia Destacada',
                description: 'La noticia más relevante del momento aparece aquí de forma destacada. Haz clic en "Leer más" para ver el artículo completo.',
                position: 'bottom',
            },
            {
                target: '.news-filters',
                title: '🗂️ Filtros por Categoría',
                description: 'Filtra las noticias por tema: Alimentación, Ejercicio, Salud Mental o General.',
                position: 'bottom',
            },
            {
                target: '#newsGrid',
                title: '📄 Artículos',
                description: 'Haz clic en cualquier artículo para leerlo completo. Se muestran los más recientes primero.',
                position: 'top',
            },
        ],

        citas: [
            {
                target: null,
                title: '📅 Citas',
                description: 'Agenda y gestiona tus consultas con especialistas. Aquí puedes solicitar nuevas citas y ver las existentes.',
            },
            {
                target: '.page-header .btn-primary',
                title: '➕ Solicitar Cita',
                description: 'Haz clic aquí para agendar una nueva cita. Puedes elegir el tipo de especialista, fecha y motivo de consulta.',
                position: 'bottom',
            },
            {
                target: '.calendar-container',
                title: '📆 Calendario',
                description: 'Navega por el calendario para ver en qué días tienes citas. Los días marcados indican citas agendadas.',
                position: 'right',
            },
            {
                target: '.citas-panel',
                title: '📋 Panel de Citas',
                description: 'Aquí aparecen los detalles al seleccionar un día del calendario, y la lista de todas tus citas próximas.',
                position: 'left',
            },
        ],

        chat: [
            {
                target: null,
                title: '💬 Mensajes',
                description: 'Comunícate de forma privada y directa con tu especialista asignado.',
            },
            {
                target: '.page-header .btn-primary',
                title: '➕ Nueva Conversación',
                description: 'Haz clic aquí para iniciar una conversación con un nuevo especialista. Puedes buscar por nombre.',
                position: 'bottom',
            },
            {
                target: '.chat-sidebar',
                title: '💬 Lista de Conversaciones',
                description: 'Todas tus conversaciones activas aparecen aquí. Las nuevas tienen un indicador de mensajes no leídos.',
                position: 'right',
            },
            {
                target: '.chat-main',
                title: '📨 Área de Mensajes',
                description: 'Selecciona una conversación y escribe tu mensaje. También puedes adjuntar archivos con el botón de clip.',
                position: 'left',
            },
        ],

        'mi-plan': [
            {
                target: null,
                title: '📋 Mi Plan Personalizado',
                description: 'Aquí está el plan que tu especialista diseñó para ti. Incluye ejercicios, recetas y recomendaciones específicas.',
            },
            {
                target: '#planSummary',
                title: '📊 Resumen del Plan',
                description: 'Un vistazo rápido de cuántos ejercicios, recetas y recomendaciones tiene tu plan activo.',
                position: 'bottom',
            },
            {
                target: '.plan-left',
                title: '💪🥗 Ejercicios y Recetas',
                description: 'Lista de ejercicios y recetas asignados. Haz clic en cualquiera para ver los detalles completos.',
                position: 'right',
            },
            {
                target: '.plan-right',
                title: '💡 Recomendaciones',
                description: 'Consejos y recomendaciones personalizadas que tu especialista escribió especialmente para ti.',
                position: 'left',
            },
        ],

        favoritos: [
            {
                target: null,
                title: '⭐ Favoritos',
                description: 'Todo lo que guardaste como favorito: recetas y ejercicios que te gustaron.',
            },
            {
                target: '.fav-tabs',
                title: '🗂️ Tabs',
                description: 'Cambia entre tus Recetas favoritas y tus Ejercicios favoritos con estas pestañas.',
                position: 'bottom',
            },
            {
                target: '#favFiltrosRecetas',
                title: '🔍 Filtros',
                description: 'Filtra tus favoritos por categoría para encontrar rápido lo que buscas.',
                position: 'bottom',
            },
            {
                target: '#favGridRecetas',
                title: '🃏 Tus Favoritos',
                description: 'Haz clic en cualquier tarjeta para abrirla. Para quitar un favorito, ábrela y haz clic en el ícono de estrella.',
                position: 'top',
            },
        ],

        perfil: [
            {
                target: null,
                title: '👤 Tu Perfil',
                description: 'Aquí puedes ver y actualizar toda tu información personal en la plataforma.',
            },
            {
                target: '.profile-avatar-wrap',
                title: '📷 Foto de Perfil',
                description: 'Cambia o actualiza tu foto. Puede ser desde tu galería o sincronizada desde Google si usas esa cuenta.',
                position: 'right',
            },
            {
                target: '.profile-action-btns',
                title: '✏️ Editar Perfil',
                description: 'Actualiza tu nombre, área de estudio u otros datos. También puedes cambiar tu contraseña desde aquí.',
                position: 'right',
            },
            {
                target: '.profile-right',
                title: '📄 Información de la Cuenta',
                description: 'Aquí se muestra tu correo, rol y fecha de registro en la plataforma.',
                position: 'left',
            },
        ],

        profesional: [
            {
                target: null,
                title: '🏥 Panel Profesional',
                description: 'Tu centro de operaciones. Gestiona a tus usuarios asignados, crea planes y revisa solicitudes pendientes.',
            },
            {
                target: '#seccionSolicitudes',
                title: '🔔 Solicitudes Pendientes',
                description: 'Usuarios que quieren que seas su especialista. Puedes aceptar o redirigir a otro colega.',
                position: 'bottom',
            },
            {
                target: '.page-header',
                title: '⚙️ Acciones Principales',
                description: 'Desde el encabezado puedes buscar usuarios, ver métricas y acceder a las opciones de gestión.',
                position: 'bottom',
            },
        ],
    };

    /* =====================
       ESTADO
       ===================== */
    let rawSteps = [];          // pasos originales sin transformar (para reconstruir al cambiar breakpoint)
    let steps = [];             // pasos activos (transformados según tamaño actual)
    let currentStep = 0;
    let backdropEl, spotlightEl, tooltipEl;
    let _mobileMenuOpenedByTutorial = false;
    let _lastWasMobile = null;  // null = tutorial inactivo

    /* =====================
       MOBILE MENU HELPERS
       ===================== */
    function isMobile() {
        return window.innerWidth <= 992;
    }

    function openMobileMenuForTutorial() {
        const mobileMenu = document.getElementById('mobileMenu');
        const menuPanel  = document.getElementById('mobileMenuPanel');
        const menuBtn    = document.getElementById('mobileMenuBtn');
        if (!mobileMenu || mobileMenu.classList.contains('active')) return;
        if (menuPanel) menuPanel.inert = false;
        if (menuBtn)   menuBtn.classList.add('open');
        mobileMenu.classList.add('active');
        _mobileMenuOpenedByTutorial = true;
    }

    function closeMobileMenuForTutorial() {
        if (!_mobileMenuOpenedByTutorial) return;
        const mobileMenu = document.getElementById('mobileMenu');
        const menuPanel  = document.getElementById('mobileMenuPanel');
        const menuBtn    = document.getElementById('mobileMenuBtn');
        if (!mobileMenu) return;
        if (menuPanel) menuPanel.inert = true;
        if (menuBtn)   menuBtn.classList.remove('open');
        mobileMenu.classList.remove('active');
        _mobileMenuOpenedByTutorial = false;
    }

    function stepNeedsMobileMenu(step) {
        return !!(step && step.target && step.target.includes('mobile-menu'));
    }

    // On mobile, replaces .sidebar steps with hamburger + mobile-menu-item equivalents
    function getMobileAwareSteps(rawSteps) {
        if (!isMobile()) return rawSteps;
        const result = [];
        for (const step of rawSteps) {
            const t = step.target || '';
            if (t === '.sidebar') {
                // Paso 1: spotlight al botón sin abrir el menú
                result.push({
                    target: '#mobileMenuBtn',
                    title: '☰ Menú de Navegación',
                    description: 'En pantallas pequeñas la barra lateral se oculta. Toca este ícono para ver todas las secciones de la plataforma.',
                    position: 'bottom',
                });
                // Paso 2: abrir el menú y hacer spotlight al panel entero
                result.push({
                    target: '.mobile-menu-panel',
                    title: '📋 Panel de Secciones',
                    description: 'Al tocar el ícono ☰ se despliega este panel. Aquí están todas las secciones disponibles — te explicamos cada una a continuación.',
                    position: 'right',
                    openMobileMenu: true,
                });
            } else if (t.startsWith('.sidebar a')) {
                // Redirect target to the equivalent item in the mobile nav panel
                result.push({
                    ...step,
                    target: t.replace('.sidebar a', '.mobile-menu-nav .mobile-menu-item'),
                });
            } else {
                result.push(step);
            }
        }
        return result;
    }

    /* =====================
       INIT
       ===================== */
    function init() {
        createElements();
        bindKeyboard();
    }

    function shouldAutoShow() {
        // Login recién hecho: ignorar sessionStorage contaminado y mostrar
        if (window.TUTORIAL_FRESH) {
            sessionStorage.removeItem(SESSION_KEY);
            return true;
        }
        const count = window.LOGIN_COUNT || 0;
        if (count === 0 || count > 3) return false;
        return sessionStorage.getItem(SESSION_KEY) !== '1';
    }

    function markShownThisSession() {
        sessionStorage.setItem(SESSION_KEY, '1');
    }

    /* =====================
       DOM
       ===================== */
    function createElements() {
        backdropEl = document.createElement('div');
        backdropEl.id = 'tutorial-backdrop';
        backdropEl.addEventListener('click', function (e) {
            if (e.target === backdropEl) finish();
        });
        document.body.appendChild(backdropEl);

        spotlightEl = document.createElement('div');
        spotlightEl.id = 'tutorial-spotlight';
        document.body.appendChild(spotlightEl);

        tooltipEl = document.createElement('div');
        tooltipEl.id = 'tutorial-tooltip';
        document.body.appendChild(tooltipEl);

        const fab = document.createElement('button');
        fab.id = 'tutorial-fab';
        fab.title = 'Ver tutorial de esta página';
        fab.setAttribute('aria-label', 'Ver tutorial');
        fab.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
        fab.addEventListener('click', () => { hideFabHint(false); startPage(); });
        document.body.appendChild(fab);

        // Burbuja de hint para usuarios nuevos
        const hint = document.createElement('div');
        hint.id = 'tutorial-fab-hint';
        hint.innerHTML = `
            <span class="tutorial-hint-text">¿Primera vez aquí? Haz clic para ver qué hace cada apartado</span>
            <div class="tutorial-hint-actions">
                <button class="tutorial-hint-start" onclick="window.tutorialHintStart()">Ver tutorial ›</button>
                <button class="tutorial-hint-dismiss" onclick="window.tutorialHintDismiss()">No mostrar más</button>
            </div>`;
        document.body.appendChild(hint);

        const HINT_KEY = 'bieniestar-hint-hidden-' + (window.CURRENT_USER_ID || 0);
        if (!localStorage.getItem(HINT_KEY)) {
            setTimeout(() => hint.classList.add('visible'), 800);
        }

        window.addEventListener('resize', debounce(onResize, 150));
    }

    function bindKeyboard() {
        document.addEventListener('keydown', function (e) {
            if (!isActive()) return;
            if (e.key === 'ArrowRight' || e.key === 'Enter') { e.preventDefault(); next(); }
            else if (e.key === 'ArrowLeft') { e.preventDefault(); prev(); }
            else if (e.key === 'Escape') finish();
        });
    }

    function isActive() {
        return backdropEl && backdropEl.style.display === 'block';
    }

    /* =====================
       LANZAR TUTORIAL
       ===================== */

    // Tour general: primer acceso o cuando se pide desde el menú sin página específica
    function startGeneral() {
        rawSteps = window.IS_PROFESSIONAL ? stepsGeneralProfesional : stepsGeneralUsuario;
        steps = getMobileAwareSteps(rawSteps);
        _lastWasMobile = isMobile();
        currentStep = 0;
        showStep(0);
    }

    // Tour de la página actual: FAB o "Ver Tutorial" desde menú
    function startPage() {
        const page = (window.CURRENT_PAGE || '').trim();
        const pageSteps = PAGE_STEPS[page];
        rawSteps = (pageSteps && pageSteps.length)
            ? pageSteps
            : (window.IS_PROFESSIONAL ? stepsGeneralProfesional : stepsGeneralUsuario);
        steps = getMobileAwareSteps(rawSteps);
        _lastWasMobile = isMobile();
        currentStep = 0;
        showStep(0);
    }

    /* =====================
       RENDERIZADO
       ===================== */
    function showStep(index) {
        const step = steps[index];
        if (!step) { finish(); return; }

        if (step.openMobileMenu) {
            openMobileMenuForTutorial();
            // Wait for the CSS slide-in animation (0.3s) before positioning the spotlight
            setTimeout(() => _doShowStep(index), 370);
            return;
        }
        if (!stepNeedsMobileMenu(step)) {
            closeMobileMenuForTutorial();
        }
        _doShowStep(index);
    }

    function _doShowStep(index) {
        const step = steps[index];
        if (!step) return;

        let target = step.target ? document.querySelector(step.target) : null;

        // En móvil, si el elemento está fuera de pantalla (sidebar oculto), ignorarlo
        if (target) {
            const r = target.getBoundingClientRect();
            if (r.width === 0 || r.right <= 0) target = null;
        }

        backdropEl.style.display = 'block';
        document.body.classList.add('tutorial-active');

        if (target) {
            positionSpotlight(target);
            spotlightEl.style.display = 'block';
            backdropEl.classList.remove('tutorial-overlay-dark');
            tooltipEl.classList.remove('tutorial-centered');
            target.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        } else {
            spotlightEl.style.display = 'none';
            backdropEl.classList.add('tutorial-overlay-dark');
            tooltipEl.classList.add('tutorial-centered');
        }

        renderTooltip(step, index);
        tooltipEl.style.animation = 'none';
        tooltipEl.offsetHeight;
        tooltipEl.style.animation = '';
        tooltipEl.style.display = 'block';
        setTimeout(() => positionTooltip(step, target), 10);
    }

    function positionSpotlight(target) {
        const rect = target.getBoundingClientRect();
        const pad = 6;
        spotlightEl.style.top          = (rect.top - pad) + 'px';
        spotlightEl.style.left         = (rect.left - pad) + 'px';
        spotlightEl.style.width        = (rect.width + pad * 2) + 'px';
        spotlightEl.style.height       = (rect.height + pad * 2) + 'px';
        const br = window.getComputedStyle(target).borderRadius;
        spotlightEl.style.borderRadius = (br && br !== '0px') ? br : '10px';
    }

    function renderTooltip(step, index) {
        const isFirst = index === 0;
        const isLast  = index === steps.length - 1;
        const pct     = Math.round((index + 1) / steps.length * 100);

        tooltipEl.innerHTML = `
            <div class="tutorial-header">
                <span class="tutorial-step-count">Paso ${index + 1} de ${steps.length}</span>
                <button class="tutorial-btn-skip" onclick="window.tutorialFinish()">Saltar</button>
            </div>
            <div class="tutorial-progress">
                <div class="tutorial-progress-fill" style="width:${pct}%"></div>
            </div>
            <div class="tutorial-title">${step.title}</div>
            <div class="tutorial-description">${step.description}</div>
            <div class="tutorial-actions">
                ${!isFirst ? '<button class="tutorial-btn-prev" onclick="window.tutorialPrev()">← Anterior</button>' : ''}
                <button class="tutorial-btn-next" onclick="window.${isLast ? 'tutorialFinish' : 'tutorialNext'}()">
                    ${isLast ? '¡Listo! 🎉' : 'Siguiente →'}
                </button>
            </div>
            <div class="tutorial-dots">
                ${steps.map((_, i) => `<div class="tutorial-dot${i === index ? ' active' : ''}"></div>`).join('')}
            </div>`;
    }

    function positionTooltip(step, target) {
        const margin = 14;
        const vw = window.innerWidth;
        const vh = window.innerHeight;
        const tw = tooltipEl.offsetWidth  || 310;
        const th = tooltipEl.offsetHeight || 240;

        tooltipEl.style.top = tooltipEl.style.left = tooltipEl.style.right = tooltipEl.style.bottom = '';
        tooltipEl.style.transform = '';

        if (!target) {
            tooltipEl.style.top       = '50%';
            tooltipEl.style.left      = '50%';
            tooltipEl.style.transform = 'translate(-50%, -50%)';
            return;
        }

        const rect = target.getBoundingClientRect();
        const pos  = step.position || 'right';
        let top, left;

        if (pos === 'right') {
            left = rect.right + margin;
            top  = rect.top;
            if (left + tw > vw - margin) left = rect.left - tw - margin;
            if (left < margin) left = margin;
        } else if (pos === 'left') {
            left = rect.left - tw - margin;
            top  = rect.top;
            if (left < margin) left = rect.right + margin;
        } else if (pos === 'bottom') {
            left = rect.left + rect.width / 2 - tw / 2;
            top  = rect.bottom + margin;
            if (top + th > vh - margin) top = rect.top - th - margin;
            if (left < margin) left = margin;
            if (left + tw > vw - margin) left = vw - tw - margin;
        } else if (pos === 'top') {
            left = rect.left + rect.width / 2 - tw / 2;
            top  = rect.top - th - margin;
            if (top < margin) top = rect.bottom + margin;
            if (left < margin) left = margin;
            if (left + tw > vw - margin) left = vw - tw - margin;
        } else if (pos === 'bottom-left') {
            left = rect.right - tw;
            top  = rect.bottom + margin;
            if (top + th > vh - margin) top = rect.top - th - margin;
            if (left < margin) left = margin;
        }

        if (top < margin) top = margin;
        if (top + th > vh - margin) top = vh - th - margin;

        tooltipEl.style.top  = top + 'px';
        tooltipEl.style.left = left + 'px';
    }

    /* =====================
       NAVEGACIÓN
       ===================== */
    function next() {
        if (currentStep < steps.length - 1) { currentStep++; showStep(currentStep); }
        else finish();
    }
    function prev() {
        if (currentStep > 0) { currentStep--; showStep(currentStep); }
    }
    function finish() {
        markShownThisSession();
        closeMobileMenuForTutorial();
        backdropEl.style.display  = 'none';
        spotlightEl.style.display = 'none';
        tooltipEl.style.display   = 'none';
        document.body.classList.remove('tutorial-active');
        _lastWasMobile = null;
    }

    function onResize() {
        if (!isActive()) return;

        const nowMobile = isMobile();

        // Crossed the 992px breakpoint: rebuild steps for the new screen size
        if (_lastWasMobile !== null && _lastWasMobile !== nowMobile) {
            if (!nowMobile) closeMobileMenuForTutorial();

            const currentTitle = steps[currentStep] ? steps[currentStep].title : null;
            steps          = getMobileAwareSteps(rawSteps);
            _lastWasMobile = nowMobile;

            // Try to land on the same logical step by matching the title
            if (currentTitle) {
                const matched = steps.findIndex(s => s.title === currentTitle);
                currentStep  = matched !== -1 ? matched : Math.min(currentStep, steps.length - 1);
            } else {
                currentStep = Math.min(currentStep, steps.length - 1);
            }

            showStep(currentStep);
            return;
        }

        _lastWasMobile = nowMobile;

        // Same breakpoint: just reposition spotlight and tooltip
        const step   = steps[currentStep];
        const target = step && step.target ? document.querySelector(step.target) : null;
        if (target) positionSpotlight(target);
        if (step)   positionTooltip(step, target);
    }

    function debounce(fn, ms) {
        let t;
        return function () { clearTimeout(t); t = setTimeout(fn, ms); };
    }

    function hideFabHint(permanent) {
        const hint = document.getElementById('tutorial-fab-hint');
        if (hint) hint.classList.remove('visible');
        if (permanent) {
            const HINT_KEY = 'bieniestar-hint-hidden-' + (window.CURRENT_USER_ID || 0);
            localStorage.setItem(HINT_KEY, '1');
        }
    }

    /* =====================
       API PÚBLICA
       ===================== */
    window.tutorialStart       = startPage;
    window.tutorialNext        = next;
    window.tutorialPrev        = prev;
    window.tutorialFinish      = finish;
    window.tutorialHintStart   = () => { hideFabHint(true); startPage(); };
    window.tutorialHintDismiss = () => hideFabHint(true);

    document.addEventListener('DOMContentLoaded', init);
})();
