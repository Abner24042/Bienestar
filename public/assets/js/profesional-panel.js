/* ═══════════════════════════════════════════════════════════════
   PANEL PROFESIONAL — JavaScript consolidado
   Incluye: agenda · ejercicios · noticias · recetas · rutinas
            planes · planes alimenticios · solicitudes
   ═══════════════════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════
   VARIABLES GLOBALES DE MÓDULOS
   ════════════════════════════════════════════════ */

// Agenda
// (sin variables de módulo propias — usa datos locales en funciones)

// Ejercicios
let proEjerciciosData      = [];
let proEjerciciosFiltrados = [];
let proEjerciciosVisible   = 8;

// Noticias
let proNoticiasData = [];

// Recetas
let proRecetasData      = [];
let proRecetasFiltrados = [];
let proRecetasVisible   = 8;
let allPendingRecetas   = [];
let pendingFiltrados    = [];
let pendingVisible      = 4;

// Rutinas
let ejerciciosDisponibles = [];
const NIVEL_COLORS = { principiante: '#4caf50', intermedio: '#ff9800', avanzado: '#f44336' };

// Planes de usuario
let planUsuarioActual  = null;
let planDataPro        = {};
let planUsuariosCache  = [];

// Planes alimenticios
let recetasDisponiblesPA = [];
const DIAS_SEMANA    = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const TIEMPOS_COMIDA = ['desayuno', 'almuerzo', 'merienda', 'cena', 'comida'];

// Solicitudes
let _solCount     = -1;
let _solInterval  = null;
let _solDataCache = {};


/* ════════════════════════════════════════════════
   HELPERS COMPARTIDOS
   ════════════════════════════════════════════════ */

function esc(str) {
    if (str === null || str === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

function cap(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function getNivelColor(nivel) {
    return NIVEL_COLORS[nivel] || '#999';
}

function showToast(message, type) { showProToast(message, type); }

function showProToast(message, type) {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    const colors = { success: '#4CAF50', error: '#F44336', warning: '#FF9800', info: '#2196F3' };
    toast.textContent = message;
    toast.style.cssText = `position:fixed;top:20px;right:20px;padding:1rem 1.5rem;border-radius:8px;color:white;background:${colors[type] || colors.info};box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:10000;display:block;opacity:1;transition:opacity 0.3s;`;
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}


/* ════════════════════════════════════════════════
   INIT ÚNICO — DOMContentLoaded
   ════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', function () {

    /* ── Agenda ── */
    loadUsersList();
    loadProfessionalAppointments();
    const apptForm = document.getElementById('formProfessionalAppointment');
    if (apptForm) apptForm.addEventListener('submit', handleCreateAppointment);

    /* ── Ejercicios ── */
    if (document.getElementById('proEjerciciosBody')) {
        cargarProEjercicios();
        document.getElementById('btnNuevoEjercicioPro').addEventListener('click', function () {
            document.getElementById('modalEjercicioProTitle').textContent = '💪 Nuevo Ejercicio';
            document.getElementById('formEjercicioPro').reset();
            document.getElementById('pro_ejercicio_id').value = '';
            const wrap = document.getElementById('pro_ejercicio_preview_wrap');
            if (wrap) wrap.style.display = 'none';
            document.getElementById('modalEjercicioPro').style.display = 'flex';
        });
        document.getElementById('formEjercicioPro').addEventListener('submit', function (e) {
            e.preventDefault();
            guardarProEjercicio();
        });
    }

    /* ── Noticias ── */
    if (document.getElementById('proNoticiasBody')) {
        cargarProNoticias();
        document.getElementById('btnNuevaNoticiaPro').addEventListener('click', function () {
            document.getElementById('modalNoticiaProTitle').textContent = 'Nueva Publicación';
            document.getElementById('formNoticiaPro').reset();
            document.getElementById('pro_noticia_id').value = '';
            document.getElementById('modalNoticiaPro').style.display = 'flex';
        });
        document.getElementById('formNoticiaPro').addEventListener('submit', function (e) {
            e.preventDefault();
            guardarProNoticia();
        });
    }

    /* ── Recetas ── */
    if (document.getElementById('proRecetasBody')) {
        cargarProRecetas();
        cargarPendingRecetas();
        document.getElementById('btnNuevaRecetaPro').addEventListener('click', function () {
            document.getElementById('modalRecetaProTitle').textContent = 'Nueva Receta';
            document.getElementById('formRecetaPro').reset();
            document.getElementById('pro_receta_id').value = '';
            document.getElementById('modalRecetaPro').style.display = 'flex';
        });
        document.getElementById('formRecetaPro').addEventListener('submit', function (e) {
            e.preventDefault();
            guardarProReceta();
        });
    }

    /* ── Rutinas ── */
    if (document.getElementById('proRutinasBody')) {
        cargarRutinas();
        cargarEjerciciosDisponibles();
        document.getElementById('btnNuevaRutina').addEventListener('click', () => abrirModalRutina());
    }
    if (document.getElementById('modalAsignarRutina')) {
        cargarRutinasSelector();
    }

    /* ── Planes de usuario ── */
    cargarUsuariosPlan();
    if (document.getElementById('proRecomendacionesBody')) {
        cargarRecomendacionesPro();
        document.getElementById('btnNuevaRecPro').addEventListener('click', abrirModalNuevaRecPro);
    }
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#planUsuarioBuscar') && !e.target.closest('#planUsuarioResultados')) {
            const r = document.getElementById('planUsuarioResultados');
            if (r) r.innerHTML = '';
        }
    });

    /* ── Planes alimenticios ── */
    if (document.getElementById('proPlanesAlimBody')) {
        cargarPlanesAlimenticios();
        cargarRecetasDisponiblesPA();
        document.getElementById('btnNuevoPlanAlim').addEventListener('click', () => abrirModalPlanAlim());
    }
    if (document.getElementById('modalAsignarPlanAlim')) {
        cargarPlanesAlimSelector();
    }

    /* ── Solicitudes ── */
    cargarSolicitudes();
    _solInterval = setInterval(pollSolicitudes, 5000);
});


/* ════════════════════════════════════════════════
   SECCIÓN: AGENDA
   ════════════════════════════════════════════════ */

async function loadUsersList() {
    try {
        const response = await fetch(API_URL + '/users');
        const data = await response.json();
        if (data.success) {
            const select = document.getElementById('select_user');
            if (!select) return;
            select.innerHTML = '<option value="">Selecciona un usuario</option>';
            data.users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.correo;
                option.textContent = `${user.nombre} (${user.correo})`;
                select.appendChild(option);
            });
            const el = document.getElementById('totalPacientes');
            if (el) el.textContent = data.users.length;
        }
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    }
}

async function loadProfessionalAppointments() {
    try {
        const response = await fetch(API_URL + '/professional-appointments?upcoming=true');
        const data = await response.json();
        if (data.success) {
            displayAppointmentsTable(data.appointments);
            updateStats(data.appointments);
        }
    } catch (error) {
        console.error('Error cargando citas:', error);
    }
}

function displayAppointmentsTable(appointments) {
    const tbody = document.getElementById('appointmentsTableBody');
    if (!tbody) return;
    if (!appointments || appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay citas programadas</td></tr>';
        return;
    }
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    tbody.innerHTML = appointments.map(apt => {
        const estadoColors = { pendiente: '#FF9800', confirmada: '#4CAF50', aceptada: '#4CAF50', cancelada: '#F44336', completada: '#2196F3' };
        const estadoRaw   = (apt.es_solicitud == 1 && apt.sol_estado) ? apt.sol_estado : (apt.estado || 'pendiente');
        const estadoLabel = estadoRaw === 'aceptada' ? 'confirmada' : estadoRaw;
        const color       = estadoColors[estadoRaw] || '#999';
        const parts       = apt.fecha.split('-');
        const fechaStr    = `${parseInt(parts[2])} ${meses[parseInt(parts[1]) - 1]} ${parts[0]}`;
        const hora        = apt.hora.substring(0, 5);
        return `<tr>
            <td>${fechaStr}</td>
            <td>${hora}</td>
            <td>${apt.titulo}</td>
            <td>${apt.usuario_nombre || 'N/A'}<br><small class="pro-agenda-email">${apt.usuario_correo || ''}</small></td>
            <td><span style="background:${color};color:white;padding:0.25rem 0.75rem;border-radius:12px;font-size:0.85rem;">${estadoLabel}</span></td>
        </tr>`;
    }).join('');
}

function updateStats(appointments) {
    const today = new Date().toISOString().split('T')[0];
    const el1 = document.getElementById('citasHoy');
    if (el1) el1.textContent = appointments.filter(a => a.fecha === today).length;
    const el2 = document.getElementById('citasProximas');
    if (el2) el2.textContent = appointments.length;
}

async function handleCreateAppointment(e) {
    e.preventDefault();
    const form      = e.target;
    const btnText   = form.querySelector('.btn-text');
    const btnLoader = form.querySelector('.btn-loader');
    const submitBtn = form.querySelector('button[type="submit"]');
    const userEmail = document.getElementById('select_user').value;
    const title     = document.getElementById('pro_title').value;
    const date      = document.getElementById('pro_date').value;
    const time      = document.getElementById('pro_time').value;
    const description  = document.getElementById('pro_description').value;
    const syncGoogle   = document.getElementById('syncGoogleCalendar').checked;

    if (!userEmail || !title || !date || !time) { showProToast('Completa todos los campos requeridos', 'error'); return; }
    if (btnText)   btnText.style.display   = 'none';
    if (btnLoader) btnLoader.style.display = 'inline';
    if (submitBtn) submitBtn.disabled      = true;

    try {
        const response = await fetch(API_URL + '/appointments/save-professional', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_email: userEmail, title, date, time, description })
        });
        const result = await response.json();
        if (result.success) {
            if (typeof sendAppointmentEmail === 'function' && typeof isEmailJSConfigured === 'function' && isEmailJSConfigured()) {
                try {
                    const selectedOption = document.getElementById('select_user').selectedOptions[0];
                    const userName = selectedOption ? selectedOption.textContent.split(' (')[0] : '';
                    await sendAppointmentEmail({ userName, userEmail, date, time, type: title, doctorName: PROFESSIONAL_USER.nombre, notes: description || 'Cita agendada por ' + PROFESSIONAL_USER.nombre });
                } catch (emailErr) { console.error('Error enviando email:', emailErr); }
            }
            if (syncGoogle && typeof createGoogleCalendarEventWithAttendee === 'function') {
                try {
                    const gcResult = await syncProfessionalToGoogleCalendar({ title, date, time, description, attendeeEmail: userEmail });
                    showProToast(gcResult && gcResult.success ? 'Cita creada y sincronizada con Google Calendar' : 'Cita creada (Google Calendar: error de sincronizacion)', gcResult && gcResult.success ? 'success' : 'warning');
                } catch (gcError) {
                    console.error('Google Calendar error:', gcError);
                    showProToast('Cita creada (Google Calendar no disponible)', 'warning');
                }
            } else {
                showProToast('Cita creada exitosamente', 'success');
            }
            clearFormDraft('formProfessionalAppointment');
            form.reset();
            await loadProfessionalAppointments();
        } else {
            showProToast(result.message || 'Error al crear cita', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showProToast('Error al comunicarse con el servidor', 'error');
    } finally {
        if (btnText)   btnText.style.display   = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        if (submitBtn) submitBtn.disabled      = false;
    }
}

async function syncProfessionalToGoogleCalendar(data) {
    return new Promise((resolve) => {
        if (typeof gapiInited === 'undefined' || !gapiInited || typeof gisInited === 'undefined' || !gisInited) {
            resolve({ success: false, error: 'Google Calendar no inicializado' });
            return;
        }
        handleAuthClick(async () => {
            try {
                const result = await createGoogleCalendarEventWithAttendee(data);
                resolve(result);
            } catch (error) {
                console.error('Error syncing:', error);
                resolve({ success: false, error: error.message });
            }
        });
    });
}


/* ════════════════════════════════════════════════
   SECCIÓN: EJERCICIOS
   ════════════════════════════════════════════════ */

async function cargarProEjercicios() {
    try {
        const response = await fetch(API_URL + '/pro/ejercicios');
        const data = await response.json();
        proEjerciciosData      = (data.success ? data.ejercicios : []) || [];
        proEjerciciosFiltrados = proEjerciciosData;
        proEjerciciosVisible   = 8;
        renderProEjerciciosTable();
    } catch (error) {
        console.error('Error:', error);
        const el = document.getElementById('proEjerciciosBody');
        if (el) el.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar ejercicios.</td></tr>';
    }
}

function renderProEjerciciosTable() {
    const tbody = document.getElementById('proEjerciciosBody');
    const wrap  = document.getElementById('proEjerciciosMostrarMasWrap');
    if (!proEjerciciosFiltrados.length) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay ejercicios disponibles.</td></tr>';
        if (wrap)  wrap.innerHTML  = '';
        return;
    }
    if (tbody) tbody.innerHTML = proEjerciciosFiltrados.slice(0, proEjerciciosVisible).map(e => `
        <tr>
            <td>${esc(e.titulo)}</td>
            <td>${cap(e.tipo || 'cardio')}</td>
            <td><span style="color:${getNivelColor(e.nivel)};font-weight:600;">${cap(e.nivel || 'principiante')}</span></td>
            <td>${e.duracion || '-'} min</td>
            <td style="display:flex;gap:0.4rem;">
                <button class="btn btn-secondary btn-sm" onclick="editarProEjercicio(${e.id})">Editar</button>
                <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProEjercicio(${e.id})">Eliminar</button>
            </td>
        </tr>
    `).join('');
    if (wrap) {
        const remaining = proEjerciciosFiltrados.length - proEjerciciosVisible;
        wrap.innerHTML = remaining > 0
            ? `<button class="btn btn-secondary" style="margin-top:0.75rem;" onclick="mostrarMasProEjercicios()">Mostrar más (${remaining})</button>`
            : '';
    }
}

function mostrarMasProEjercicios() { proEjerciciosVisible += 8; renderProEjerciciosTable(); }

function editarProEjercicio(id) {
    const e = proEjerciciosData.find(item => item.id == id);
    if (!e) return;
    document.getElementById('modalEjercicioProTitle').textContent          = '💪 Editar Ejercicio';
    document.getElementById('pro_ejercicio_id').value                      = e.id;
    document.getElementById('pro_ejercicio_titulo').value                  = e.titulo || '';
    document.getElementById('pro_ejercicio_descripcion').value             = e.descripcion || '';
    document.getElementById('pro_ejercicio_duracion').value                = e.duracion || '';
    document.getElementById('pro_ejercicio_nivel').value                   = e.nivel || 'principiante';
    document.getElementById('pro_ejercicio_tipo').value                    = e.tipo || 'cardio';
    document.getElementById('pro_ejercicio_calorias').value                = e.calorias_quemadas || '';
    document.getElementById('pro_ejercicio_musculo').value                 = e.musculo_objetivo || '';
    document.getElementById('pro_ejercicio_equipamiento').value            = e.equipamiento || '';
    document.getElementById('pro_ejercicio_secundarios').value             = e.musculos_secundarios || '';
    document.getElementById('pro_ejercicio_video').value                   = e.video_url || '';
    document.getElementById('pro_ejercicio_instrucciones').value           = e.instrucciones || '';
    document.getElementById('modalEjercicioPro').style.display             = 'flex';
}

async function guardarProEjercicio() {
    const form = document.getElementById('formEjercicioPro');
    try {
        const response = await fetch(API_URL + '/pro/ejercicios', { method: 'POST', body: new FormData(form) });
        const result   = await response.json();
        if (result.success) {
            clearFormDraft('formEjercicioPro');
            showProToast(result.message, 'success');
            document.getElementById('modalEjercicioPro').style.display = 'none';
            cargarProEjercicios();
        } else { showProToast(result.message || 'Error al guardar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

async function eliminarProEjercicio(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este ejercicio?')) return;
    try {
        const response = await fetch(API_URL + `/pro/ejercicios/${id}`, { method: 'DELETE' });
        const result   = await response.json();
        if (result.success) { showProToast(result.message, 'success'); cargarProEjercicios(); }
        else { showProToast(result.message || 'Error al eliminar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

function proEjercicioPreviewImagen(input) {
    const preview = document.getElementById('pro_ejercicio_imagen_preview');
    const wrap    = document.getElementById('pro_ejercicio_preview_wrap');
    const nameEl  = document.getElementById('pro_ejercicio_preview_name');
    const file    = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
            if (wrap)    wrap.style.display = 'flex';
            if (nameEl)  nameEl.textContent = file.name;
        };
        reader.readAsDataURL(file);
    } else {
        if (preview) preview.style.display = 'none';
        if (wrap)    wrap.style.display    = 'none';
    }
}


/* ════════════════════════════════════════════════
   SECCIÓN: NOTICIAS
   ════════════════════════════════════════════════ */

async function cargarProNoticias() {
    try {
        const response = await fetch(API_URL + '/pro/noticias');
        const data     = await response.json();
        const tbody    = document.getElementById('proNoticiasBody');
        if (data.success && data.noticias.length > 0) {
            proNoticiasData = data.noticias;
            tbody.innerHTML = data.noticias.map(n => `
                <tr>
                    <td>${esc(n.titulo)}</td>
                    <td>${getCatLabel(n.categoria)}</td>
                    <td><span style="color:${n.publicado == 1 ? '#34A853' : '#999'};font-weight:600;">${n.publicado == 1 ? 'Sí' : 'No'}</span></td>
                    <td>${formatDate(n.fecha_publicacion)}</td>
                    <td style="display:flex;gap:0.4rem;">
                        <button class="btn btn-secondary btn-sm" onclick="editarProNoticia(${n.id})">Editar</button>
                        <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProNoticia(${n.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        } else {
            proNoticiasData = [];
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes publicaciones aún. ¡Crea tu primera!</td></tr>';
        }
    } catch (error) { console.error('Error:', error); }
}

function editarProNoticia(id) {
    const n = proNoticiasData.find(item => item.id == id);
    if (!n) return;
    document.getElementById('modalNoticiaProTitle').textContent       = 'Editar Publicación';
    document.getElementById('pro_noticia_id').value                   = n.id;
    document.getElementById('pro_noticia_titulo').value               = n.titulo || '';
    document.getElementById('pro_noticia_resumen').value              = n.resumen || '';
    document.getElementById('pro_noticia_contenido').value            = n.contenido || '';
    document.getElementById('pro_noticia_categoria').value            = n.categoria || 'general';
    document.getElementById('pro_noticia_publicado').checked          = n.publicado == 1;
    document.getElementById('modalNoticiaPro').style.display          = 'flex';
}

async function guardarProNoticia() {
    const form     = document.getElementById('formNoticiaPro');
    const formData = new FormData(form);
    formData.set('publicado', document.getElementById('pro_noticia_publicado').checked ? '1' : '0');
    try {
        const response = await fetch(API_URL + '/pro/noticias/save', { method: 'POST', body: formData });
        const result   = await response.json();
        if (result.success) {
            clearFormDraft('formNoticiaPro');
            showProToast(result.message, 'success');
            document.getElementById('modalNoticiaPro').style.display = 'none';
            cargarProNoticias();
        } else { showProToast(result.message || 'Error al guardar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

async function eliminarProNoticia(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta publicación?')) return;
    try {
        const response = await fetch(API_URL + '/pro/noticias/delete', {
            method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id })
        });
        const result = await response.json();
        if (result.success) { showProToast(result.message, 'success'); cargarProNoticias(); }
        else { showProToast(result.message || 'Error al eliminar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

function getCatLabel(cat) {
    const labels = { alimentacion: 'Alimentación', ejercicio: 'Ejercicio', 'salud-mental': 'Salud Mental', general: 'General' };
    return labels[cat] || cat || 'General';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();
    } catch (e) { return dateStr; }
}


/* ════════════════════════════════════════════════
   SECCIÓN: RECETAS
   ════════════════════════════════════════════════ */

function porFilaP() {
    const el = document.getElementById('pendingRecetasGrid');
    const w  = el ? (el.clientWidth || el.offsetWidth) : (window.innerWidth - 260);
    return Math.max(1, Math.floor((w + 16) / (220 + 16)));
}

async function cargarProRecetas() {
    try {
        const response = await fetch(API_URL + '/pro/recetas');
        const data     = await response.json();
        proRecetasData      = (data.success ? data.recetas : []) || [];
        proRecetasFiltrados = proRecetasData;
        proRecetasVisible   = 8;
        renderProRecetasTable();
    } catch (error) {
        console.error('Error:', error);
        const el = document.getElementById('proRecetasBody');
        if (el) el.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar recetas.</td></tr>';
    }
}

function renderProRecetasTable() {
    const tbody = document.getElementById('proRecetasBody');
    const wrap  = document.getElementById('proRecetasMostrarMasWrap');
    if (!proRecetasFiltrados.length) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay recetas.</td></tr>';
        if (wrap)  wrap.innerHTML  = '';
        return;
    }
    if (tbody) tbody.innerHTML = proRecetasFiltrados.slice(0, proRecetasVisible).map(r => `
        <tr>
            <td>${esc(r.titulo)}</td>
            <td>${cap(r.categoria || 'comida')}</td>
            <td>${r.calorias || '-'} kcal</td>
            <td><span style="color:${r.activo == 1 ? '#34A853' : '#999'};font-weight:600;">${r.activo == 1 ? 'Activa' : 'Inactiva'}</span></td>
            <td style="display:flex;gap:0.4rem;">
                <button class="btn btn-secondary btn-sm" onclick="editarProReceta(${r.id})">Editar</button>
                <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProReceta(${r.id})">Eliminar</button>
            </td>
        </tr>
    `).join('');
    if (wrap) {
        const remaining = proRecetasFiltrados.length - proRecetasVisible;
        wrap.innerHTML = remaining > 0
            ? `<button class="btn btn-secondary" style="margin-top:0.75rem;" onclick="mostrarMasProRecetas()">Mostrar más (${remaining})</button>`
            : '';
    }
}

function mostrarMasProRecetas() { proRecetasVisible += 8; renderProRecetasTable(); }

function filtrarProRecetas() {
    const q = (document.getElementById('proRecetasSearch')?.value || '').toLowerCase().trim();
    proRecetasFiltrados = !q ? proRecetasData : proRecetasData.filter(r =>
        (r.titulo || '').toLowerCase().includes(q) || (r.categoria || '').toLowerCase().includes(q)
    );
    proRecetasVisible = 8;
    renderProRecetasTable();
}

function editarProReceta(id) {
    const r = proRecetasData.find(item => item.id == id);
    if (!r) return;
    document.getElementById('modalRecetaProTitle').textContent        = 'Editar Receta';
    document.getElementById('pro_receta_id').value                    = r.id;
    document.getElementById('pro_receta_titulo').value                = r.titulo || '';
    document.getElementById('pro_receta_descripcion').value           = r.descripcion || '';
    document.getElementById('pro_receta_ingredientes').value          = r.ingredientes || '';
    document.getElementById('pro_receta_instrucciones').value         = r.instrucciones || '';
    document.getElementById('pro_receta_tiempo').value                = r.tiempo_preparacion || '';
    document.getElementById('pro_receta_porciones').value             = r.porciones || '';
    document.getElementById('pro_receta_calorias').value              = r.calorias || '';
    document.getElementById('pro_receta_proteinas').value             = r.proteinas || '';
    document.getElementById('pro_receta_carbohidratos').value         = r.carbohidratos || '';
    document.getElementById('pro_receta_grasas').value                = r.grasas || '';
    document.getElementById('pro_receta_fibra').value                 = r.fibra || '';
    document.getElementById('pro_receta_categoria').value             = r.categoria || 'comida';
    document.getElementById('modalRecetaPro').style.display           = 'flex';
}

async function guardarProReceta() {
    const form = document.getElementById('formRecetaPro');
    try {
        const response = await fetch(API_URL + '/pro/recetas', { method: 'POST', body: new FormData(form) });
        const result   = await response.json();
        if (result.success) {
            clearFormDraft('formRecetaPro');
            showProToast(result.message, 'success');
            document.getElementById('modalRecetaPro').style.display = 'none';
            cargarProRecetas();
        } else { showProToast(result.message || 'Error al guardar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

async function eliminarProReceta(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta receta?')) return;
    try {
        const response = await fetch(API_URL + `/pro/recetas/${id}`, { method: 'DELETE' });
        const result   = await response.json();
        if (result.success) { showProToast(result.message, 'success'); cargarProRecetas(); }
        else { showProToast(result.message || 'Error al eliminar', 'error'); }
    } catch (error) { showProToast('Error de comunicación', 'error'); }
}

function proRecetaPreviewImagen(input) {
    const preview = document.getElementById('pro_receta_imagen_preview');
    const wrap    = document.getElementById('pro_receta_preview_wrap');
    const nameEl  = document.getElementById('pro_receta_preview_name');
    const file    = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
            if (wrap)    wrap.style.display = 'flex';
            if (nameEl)  nameEl.textContent = file.name;
        };
        reader.readAsDataURL(file);
    } else {
        if (preview) preview.style.display = 'none';
        if (wrap)    wrap.style.display    = 'none';
    }
}

/* ── Recetas pendientes ── */

async function cargarPendingRecetas() {
    try {
        const res  = await fetch(API_URL + '/pro/recetas/pending');
        const data = await res.json();
        if (!data.success || data.recetas.length === 0) {
            const sec = document.getElementById('sectionPendingRecetas');
            if (sec) sec.style.display = 'none';
            return;
        }
        allPendingRecetas = data.recetas;
        const cntEl = document.getElementById('pendingCount');
        if (cntEl) cntEl.textContent = data.recetas.length;
        aplicarFiltrosPending();
    } catch (e) { console.error('Error cargando pendientes:', e); }
}

function aplicarFiltrosPending() {
    const q   = (document.getElementById('pendingSearch')?.value || '').toLowerCase().trim();
    const cat = (document.getElementById('pendingCatFilter')?.value || '').toLowerCase();
    pendingFiltrados = allPendingRecetas.filter(r => {
        const matchText = !q   || r.titulo.toLowerCase().includes(q) || (r.categoria || '').toLowerCase().includes(q);
        const matchCat  = !cat || (r.categoria || '').toLowerCase() === cat;
        return matchText && matchCat;
    });
    pendingVisible = porFilaP();
    renderPendingGrid();
}

function mostrarMasPending() { pendingVisible += porFilaP(); renderPendingGrid(); }

function renderPendingGrid() {
    const grid  = document.getElementById('pendingRecetasGrid');
    const btn   = document.getElementById('btnMostrarMasPending');
    const items = pendingFiltrados.slice(0, pendingVisible);
    if (!grid) return;
    grid.innerHTML = items.length
        ? items.map(r => pendingCard(r)).join('')
        : '<p style="color:#999;grid-column:1/-1;">No se encontraron recetas con ese filtro.</p>';
    if (btn) btn.style.display = pendingVisible < pendingFiltrados.length ? '' : 'none';
}

function pendingCard(r) {
    const img = r.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70';
    const cal = r.calorias ? Math.round(r.calorias) + ' kcal' : '—';
    const cat = cap(r.categoria || 'receta');
    return `<div onclick="abrirDetallePending(${r.id})" class="pending-recipe-card">
        <img src="${esc(img)}" alt="${esc(r.titulo)}" style="width:100%;height:140px;object-fit:cover;"
             onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70'">
        <div style="padding:0.75rem;">
            <p style="font-weight:600;font-size:0.85rem;margin-bottom:4px;line-height:1.3;">${esc(r.titulo)}</p>
            <p style="font-size:0.75rem;color:var(--color-text-light,#999);margin-bottom:0.75rem;">${cat} · ${cal}</p>
            <div style="display:flex;gap:0.4rem;">
                <button onclick="event.stopPropagation();aprobarReceta(${r.id})"
                    style="flex:1;padding:6px;background:#34A853;color:white;border:none;border-radius:6px;cursor:pointer;font-size:0.75rem;font-weight:600;">✓ Aprobar</button>
                <button onclick="event.stopPropagation();rechazarReceta(${r.id})"
                    style="flex:1;padding:6px;background:#F44336;color:white;border:none;border-radius:6px;cursor:pointer;font-size:0.75rem;font-weight:600;">✕ Eliminar</button>
            </div>
        </div>
    </div>`;
}

function abrirDetallePending(id) {
    const r = allPendingRecetas.find(x => x.id == id);
    if (!r) return;
    const img    = r.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70';
    const cat    = cap(r.categoria || 'receta');
    const cal    = r.calorias ? Math.round(r.calorias) + ' kcal' : '—';
    const prot   = r.proteinas     ? r.proteinas     + 'g proteínas' : '';
    const carb   = r.carbohidratos ? r.carbohidratos + 'g carbos'    : '';
    const gras   = r.grasas        ? r.grasas        + 'g grasas'    : '';
    const macros = [prot, carb, gras].filter(Boolean).join(' · ');
    const ingList = r.ingredientes
        ? r.ingredientes.split('\n').map(i => i.trim()).filter(Boolean).map(i => `<li style="margin-bottom:4px;">${esc(i)}</li>`).join('')
        : '<li style="color:#999;">—</li>';
    const insList = r.instrucciones
        ? r.instrucciones.split('\n').map(i => i.trim()).filter(Boolean).map(i => `<li style="margin-bottom:6px;">${esc(i)}</li>`).join('')
        : '<li style="color:#999;">—</li>';

    document.getElementById('modalPendingContent').innerHTML = `
        <div style="display:flex;min-height:240px;">
            <img src="${esc(img)}" style="width:45%;min-width:160px;max-width:260px;object-fit:cover;border-radius:0;flex-shrink:0;"
                 onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70'">
            <div style="flex:1;padding:1.25rem 1.25rem 1rem;display:flex;flex-direction:column;justify-content:center;gap:0.6rem;overflow:hidden;">
                <h2 style="font-size:1.1rem;line-height:1.3;margin:0;">${esc(r.titulo)}</h2>
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    <span style="background:#ff6b35;color:white;padding:3px 10px;border-radius:20px;font-size:0.75rem;">${cat}</span>
                    <span class="pending-cal-badge">🔥 ${cal}</span>
                </div>
                ${r.tiempo_preparacion ? `<div class="pending-info-row"><span class="pending-info-label">Tiempo</span><span style="color:#ff6b35;font-weight:700;">${r.tiempo_preparacion} min</span></div>` : ''}
                ${r.porciones ? `<div class="pending-info-row"><span class="pending-info-label">Porciones</span><span style="color:#ff6b35;font-weight:700;">${r.porciones}</span></div>` : ''}
                ${macros ? `<p style="color:var(--color-text-light,#888);font-size:0.75rem;margin:0;">${macros}</p>` : ''}
                ${r.descripcion ? `<p class="pending-desc">${esc(r.descripcion)}</p>` : ''}
            </div>
        </div>
        <div style="padding:1.25rem 1.5rem 1.5rem;">
            <h3 style="margin-bottom:0.5rem;font-size:0.9rem;color:#ff6b35;text-transform:uppercase;letter-spacing:.5px;">Ingredientes</h3>
            <ul class="pending-list">${ingList}</ul>
            <h3 style="margin-bottom:0.5rem;font-size:0.9rem;color:#ff6b35;text-transform:uppercase;letter-spacing:.5px;">Instrucciones</h3>
            <ol class="pending-list" style="line-height:1.8;margin-bottom:1.5rem;">${insList}</ol>
            <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                <button onclick="aprobarReceta(${r.id});cerrarDetallePending()" style="flex:1;padding:10px;background:#34A853;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">✓ Aprobar</button>
                <button onclick="rechazarReceta(${r.id});cerrarDetallePending()" style="flex:1;padding:10px;background:#F44336;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;">✕ Eliminar</button>
                <button onclick="cerrarDetallePending()" class="pending-btn-cerrar">Cerrar</button>
            </div>
        </div>`;
    document.getElementById('modalPendingDetalle').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function cerrarDetallePending() {
    document.getElementById('modalPendingDetalle').style.display = 'none';
    document.body.style.overflow = '';
}

async function aprobarReceta(id) {
    try {
        const res    = await fetch(API_URL + `/pro/recetas/${id}/approve`, { method: 'POST' });
        const result = await res.json();
        if (result.success) { showProToast('Receta aprobada — se quedará permanentemente', 'success'); cargarPendingRecetas(); }
        else { showProToast('Error al aprobar', 'error'); }
    } catch (e) { showProToast('Error de comunicación', 'error'); }
}

async function rechazarReceta(id) {
    if (!confirm('¿Eliminar esta receta?')) return;
    try {
        const res    = await fetch(API_URL + `/pro/recetas/${id}`, { method: 'DELETE' });
        const result = await res.json();
        if (result.success) { showProToast('Receta eliminada', 'success'); cargarPendingRecetas(); }
        else { showProToast('Error al eliminar', 'error'); }
    } catch (e) { showProToast('Error de comunicación', 'error'); }
}


/* ════════════════════════════════════════════════
   SECCIÓN: RUTINAS
   ════════════════════════════════════════════════ */

const EJ_CHEVRON = `<svg class="picker-trigger-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>`;

async function cargarRutinas() {
    const tbody = document.getElementById('proRutinasBody');
    if (!tbody) return;
    try {
        const res  = await fetch(API_URL + '/pro/rutinas');
        const data = await res.json();
        if (!data.success) throw new Error();
        if (!data.rutinas.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes rutinas aún. ¡Crea tu primera!</td></tr>';
            return;
        }
        tbody.innerHTML = data.rutinas.map(r => `
            <tr>
                <td><strong>${esc(r.nombre)}</strong>${r.descripcion ? `<br><small style="color:#999;">${esc(r.descripcion.substring(0, 60))}${r.descripcion.length > 60 ? '…' : ''}</small>` : ''}</td>
                <td><span style="color:${NIVEL_COLORS[r.nivel] || '#999'};font-weight:600;font-size:0.82rem;">${esc(r.nivel)}</span></td>
                <td style="text-align:center;">${r.num_ejercicios}</td>
                <td style="color:#999;">${r.duracion_total ? r.duracion_total + ' min' : '—'}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editarRutina(${r.id})">Editar</button>
                    <button class="btn btn-sm" style="background:#ff6b35;color:white;border:none;margin-left:4px;" onclick="exportarRutinaPDF(${r.id})">PDF</button>
                    <button class="btn btn-sm" style="background:#c0392b;color:white;border:none;margin-left:4px;" onclick="eliminarRutina(${r.id},'${esc(r.nombre)}')">Eliminar</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar rutinas.</td></tr>';
    }
}

async function cargarEjerciciosDisponibles() {
    try {
        const res  = await fetch(API_URL + '/ejercicios');
        const data = await res.json();
        if (data.success) ejerciciosDisponibles = data.ejercicios || [];
    } catch (e) { }
}

async function cargarRutinasSelector() {
    try {
        const res  = await fetch(API_URL + '/pro/rutinas');
        const data = await res.json();
        if (!data.success) return;
        const sel = document.getElementById('asignarRutinaSelect');
        if (!sel) return;
        sel.innerHTML = '<option value="">— Elige una rutina —</option>' +
            data.rutinas.map(r => `<option value="${r.id}">${esc(r.nombre)} (${r.num_ejercicios} ejerc.)</option>`).join('');
    } catch (e) { }
}

function abrirModalRutina(id = null) {
    document.getElementById('rutina_id').value          = id || '';
    document.getElementById('rutina_nombre').value      = '';
    document.getElementById('rutina_descripcion').value = '';
    document.getElementById('rutina_nivel').value       = 'principiante';
    document.getElementById('rutina_duracion').value    = '';
    document.getElementById('rutinaEjerciciosList').innerHTML = '';
    document.getElementById('modalRutinaTitle').textContent   = id ? 'Editar Rutina' : 'Nueva Rutina';
    document.getElementById('modalRutina').style.display      = 'flex';
}

async function editarRutina(id) {
    abrirModalRutina(id);
    try {
        const res  = await fetch(API_URL + '/pro/rutinas/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        const r = data.rutina;
        document.getElementById('rutina_nombre').value      = r.nombre;
        document.getElementById('rutina_descripcion').value = r.descripcion || '';
        document.getElementById('rutina_nivel').value       = r.nivel;
        document.getElementById('rutina_duracion').value    = r.duracion_total || '';
        document.getElementById('rutinaEjerciciosList').innerHTML = '';
        (r.ejercicios || []).forEach(ej => agregarEjercicioRutina(ej));
    } catch (e) { showProToast('Error al cargar la rutina', 'error'); }
}

function cerrarModalRutina() {
    window.closeItemPickerPanel && closeItemPickerPanel();
    document.getElementById('modalRutina').style.display = 'none';
}

function buildEjercicioTriggerR(ej) {
    if (!ej) {
        return `<div class="picker-trigger-icon" style="background:rgba(255,107,53,.1);">💪</div>
                <span class="picker-trigger-text"><span class="picker-trigger-title" style="opacity:.45;">— Buscar ejercicio... —</span></span>`;
    }
    const thumb = ej.imagen
        ? `<img class="picker-trigger-thumb" src="${esc(ej.imagen)}" onerror="this.style.display='none'">`
        : `<div class="picker-trigger-icon" style="background:rgba(255,107,53,.1);">💪</div>`;
    const nivel = ej.nivel
        ? `<span class="picker-trigger-badge" style="background:rgba(255,107,53,.12);color:var(--color-primary);">${esc(ej.nivel)}</span>`
        : '';
    return `${thumb}<span class="picker-trigger-text"><span class="picker-trigger-title">${esc(ej.titulo)}</span></span>${nivel}`;
}

function abrirPickerEjercicioR(trigger) {
    if (trigger.classList.contains('open')) { window.closeItemPickerPanel && closeItemPickerPanel(); return; }
    window.openItemPicker(trigger, ejerciciosDisponibles, 'tipo', function (ej) {
        const row = trigger.closest('.rutina-ej-row');
        row.querySelector('.rej-ejercicio').value = ej.id;
        trigger.dataset.value = ej.id;
        trigger.classList.add('has-value');
        trigger.innerHTML = buildEjercicioTriggerR(ej) + EJ_CHEVRON;
    }, 'orange');
}

function agregarEjercicioRutina(datos = null) {
    const container = document.getElementById('rutinaEjerciciosList');
    const row       = document.createElement('div');
    row.className   = 'picker-row picker-row-orange rutina-ej-row';
    const selEj     = datos ? ejerciciosDisponibles.find(e => e.id == datos.ejercicio_id) : null;
    row.innerHTML = `
        <div class="picker-row-top">
            <input type="hidden" class="rej-ejercicio" value="${datos ? esc(String(datos.ejercicio_id || '')) : ''}">
            <button type="button" class="item-picker-trigger ${selEj ? 'has-value' : ''}"
                    data-value="${datos ? esc(String(datos.ejercicio_id || '')) : ''}"
                    onclick="abrirPickerEjercicioR(this)">
                ${buildEjercicioTriggerR(selEj)}
                ${EJ_CHEVRON}
            </button>
            <button type="button" class="picker-row-remove" onclick="this.closest('.rutina-ej-row').remove()">✕</button>
        </div>
        <div class="picker-row-fields">
            <div class="picker-field"><label>Series</label>
                <input type="number" class="rej-series" value="${datos ? esc(String(datos.series || 3)) : 3}" min="1"></div>
            <div class="picker-field"><label>Reps / Duración</label>
                <input type="text" class="rej-reps" value="${datos ? esc(datos.repeticiones || '') : ''}" placeholder="Ej: 12 o 30 seg"></div>
            <div class="picker-field"><label>Descanso (seg)</label>
                <input type="number" class="rej-descanso" value="${datos ? esc(String(datos.descanso_seg || 60)) : 60}" min="0"></div>
            <div class="picker-field"><label>Notas</label>
                <input type="text" class="rej-notas" value="${datos ? esc(datos.notas || '') : ''}" placeholder="Opcional"></div>
        </div>
    `;
    container.appendChild(row);
}

async function guardarRutina() {
    const nombre = document.getElementById('rutina_nombre').value.trim();
    if (!nombre) { showProToast('El nombre es requerido', 'error'); return; }

    const rows     = document.querySelectorAll('.rutina-ej-row');
    const ejercicios = [];
    for (const row of rows) {
        const ejId = row.querySelector('.rej-ejercicio').value;
        if (!ejId) { showProToast('Selecciona un ejercicio en cada fila', 'error'); return; }
        ejercicios.push({
            ejercicio_id: ejId,
            series:       parseInt(row.querySelector('.rej-series').value)   || 3,
            repeticiones: row.querySelector('.rej-reps').value.trim()        || null,
            descanso_seg: parseInt(row.querySelector('.rej-descanso').value) || 60,
            notas:        row.querySelector('.rej-notas').value.trim()       || null,
        });
    }

    const payload = {
        id:            document.getElementById('rutina_id').value || null,
        nombre,
        descripcion:   document.getElementById('rutina_descripcion').value.trim() || null,
        nivel:         document.getElementById('rutina_nivel').value,
        duracion_total: parseInt(document.getElementById('rutina_duracion').value) || null,
        ejercicios,
    };

    try {
        const res  = await fetch(API_URL + '/pro/rutinas/save', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        if (!payload.id) clearFormDraft('formRutina');
        cerrarModalRutina();
        showProToast(payload.id ? 'Rutina actualizada' : 'Rutina creada');
        cargarRutinas();
        cargarRutinasSelector();
    } catch (e) { showProToast(e.message || 'Error al guardar', 'error'); }
}

async function eliminarRutina(id, nombre) {
    if (!confirm(`¿Eliminar la rutina "${nombre}"?\nEsta acción no se puede deshacer.`)) return;
    try {
        const res  = await fetch(API_URL + '/pro/rutinas/delete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showProToast('Rutina eliminada');
        cargarRutinas();
    } catch (e) { showProToast(e.message || 'Error al eliminar', 'error'); }
}

async function exportarRutinaPDF(id) {
    try {
        const res  = await fetch(API_URL + '/pro/rutinas/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        generarRutinaPDF(data.rutina);
    } catch (e) { showProToast('Error al generar PDF', 'error'); }
}

function generarRutinaPDF(rutina) {
    const { jsPDF }    = window.jspdf;
    const doc          = new jsPDF();
    const orange       = [255, 107, 53];
    const dark         = [17, 17, 17];
    const gray         = [100, 100, 100];
    const especialista = (typeof PROFESSIONAL_USER !== 'undefined' && PROFESSIONAL_USER.nombre) ? PROFESSIONAL_USER.nombre : '';

    doc.setFillColor(...orange);
    doc.rect(0, 0, 210, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(17);
    doc.setFont('helvetica', 'bold');
    doc.text('RUTINA DE ENTRENAMIENTO', 14, 13);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Bienestar — Plan Personalizado', 14, 21);
    if (especialista) doc.text('Coach: ' + especialista, 14, 28);

    let y = 42;
    doc.setTextColor(...dark);
    doc.setFontSize(15);
    doc.setFont('helvetica', 'bold');
    doc.text(rutina.nombre, 14, y);
    y += 7;

    const nivelCap = (rutina.nivel || 'principiante').charAt(0).toUpperCase() + (rutina.nivel || '').slice(1);
    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...gray);
    doc.text(['Nivel: ' + nivelCap, 'Ejercicios: ' + (rutina.ejercicios || []).length, 'Duración: ' + (rutina.duracion_total ? rutina.duracion_total + ' min' : 'N/D')].join('   |   '), 14, y);
    y += 5;

    if (rutina.descripcion) {
        y += 2;
        const lines = doc.splitTextToSize(rutina.descripcion, 182);
        doc.text(lines, 14, y);
        y += lines.length * 4.5 + 1;
    }

    y += 4;
    doc.setDrawColor(...orange);
    doc.setLineWidth(0.5);
    doc.line(14, y, 196, y);
    y += 7;

    if (rutina.ejercicios && rutina.ejercicios.length) {
        doc.autoTable({
            startY: y, margin: { left: 14, right: 14 },
            head: [['#', 'Ejercicio', 'Series', 'Reps', 'Descanso', 'Músculo', 'Notas']],
            body: rutina.ejercicios.map((ej, i) => [i + 1, ej.ejercicio_titulo || '—', ej.series != null ? String(ej.series) : '—', ej.repeticiones || '—', ej.descanso_seg != null ? ej.descanso_seg + 's' : '—', ej.musculo_objetivo || '—', ej.notas || '']),
            styles: { fontSize: 9, cellPadding: { top: 4, right: 4, bottom: 4, left: 4 }, valign: 'middle', overflow: 'linebreak' },
            headStyles: { fillColor: orange, textColor: 255, fontStyle: 'bold', valign: 'middle', halign: 'center' },
            alternateRowStyles: { fillColor: [255, 248, 244] },
            columnStyles: { 0: { cellWidth: 10, halign: 'center' }, 1: { cellWidth: 52 }, 2: { cellWidth: 15, halign: 'center' }, 3: { cellWidth: 22, halign: 'center' }, 4: { cellWidth: 18, halign: 'center' }, 5: { cellWidth: 30 }, 6: { cellWidth: 35 } },
        });
    } else {
        doc.setTextColor(...gray);
        doc.setFontSize(10);
        doc.text('Sin ejercicios registrados.', 14, y);
    }

    const pageCount = doc.internal.getNumberOfPages();
    const fechaHoy  = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(180, 180, 180);
        doc.text('Bienestar — Generado el ' + fechaHoy, 14, 290);
        doc.text('Página ' + i + ' de ' + pageCount, 196, 290, { align: 'right' });
    }

    doc.save('rutina-' + rutina.nombre.replace(/[^a-z0-9]/gi, '_').toLowerCase() + '.pdf');
}

function abrirModalAsignarRutina() {
    if (!planUsuarioActual) { showProToast('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('asignarRutinaNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarRutina').style.display = 'flex';
}

async function confirmarAsignarRutina() {
    const rutinaId = document.getElementById('asignarRutinaSelect').value;
    const notas    = document.getElementById('asignarRutinaNotas').value.trim();
    if (!rutinaId) { showProToast('Selecciona una rutina', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/rutinas/asignar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: planUsuarioActual, rutina_id: rutinaId, notas: notas || null }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarRutina').style.display = 'none';
        showProToast(data.message || 'Rutina asignada');
        if (typeof cargarPlanUsuario === 'function') cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al asignar', 'error'); }
}


/* ════════════════════════════════════════════════
   SECCIÓN: PLANES DE USUARIO
   ════════════════════════════════════════════════ */

async function cargarUsuariosPlan() {
    try {
        const res  = await fetch(API_URL + '/pro/usuarios-list');
        const data = await res.json();
        planUsuariosCache = data.success ? (data.usuarios || []) : [];
    } catch (e) { planUsuariosCache = []; }
}

function planFiltrarUsuarios(query) {
    const results = document.getElementById('planUsuarioResultados');
    if (!results) return;
    const q = query.trim().toLowerCase();
    if (!q) { results.innerHTML = ''; return; }

    const matches = planUsuariosCache.filter(u =>
        u.nombre.toLowerCase().includes(q) || u.correo.toLowerCase().includes(q)
    ).slice(0, 8);

    if (!matches.length) {
        results.innerHTML = '<div class="pro-user-search-empty">No se encontraron usuarios</div>';
        return;
    }
    results.innerHTML = matches.map(u => `
        <div class="pro-user-search-item" onclick="planSeleccionarUsuario(${u.id}, '${esc(u.nombre)}')">
            <div class="pro-user-search-avatar">${esc(u.nombre.charAt(0).toUpperCase())}</div>
            <div>
                <div class="pro-user-search-name">${esc(u.nombre)}</div>
                <div class="pro-user-search-correo">${esc(u.correo)}</div>
            </div>
        </div>`).join('');
}

function planSeleccionarUsuario(id, nombre) {
    document.getElementById('planUsuarioBuscar').value         = '';
    document.getElementById('planUsuarioResultados').innerHTML = '';
    document.getElementById('planUsuarioId').value             = id;
    const tag = document.getElementById('planUsuarioTag');
    tag.style.display = 'flex';
    document.getElementById('planUsuarioNombreTag').textContent = nombre;
    cargarPlanUsuario(id);
}

function planLimpiarUsuario() {
    document.getElementById('planUsuarioId').value             = '';
    document.getElementById('planUsuarioBuscar').value         = '';
    document.getElementById('planUsuarioTag').style.display    = 'none';
    document.getElementById('planUsuarioContainer').style.display = 'none';
    const saludEl = document.getElementById('planSaludUsuario');
    if (saludEl) saludEl.style.display = 'none';
    planUsuarioActual = null;
}

async function cargarPlanUsuario(userId) {
    if (!userId) { document.getElementById('planUsuarioContainer').style.display = 'none'; return; }
    planUsuarioActual = userId;
    document.getElementById('planUsuarioContainer').style.display = 'block';
    setPlanLoading(true);
    try {
        const res  = await fetch(API_URL + '/pro/plan/get-usuario?usuario_id=' + userId);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Error');
        planDataPro = data;
        renderPlanPro(data);
    } catch (e) {
        setPlanLoading(false);
        showProToast('Error al cargar el plan del usuario', 'error');
    }
}

function setPlanLoading(on) {
    ['planEjerciciosList', 'planRecetasList', 'planRecomendacionesList'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = on ? '<p style="color:#999;padding:8px 0;">Cargando...</p>' : '';
    });
}

function renderPlanPro(data) {
    const saludEl = document.getElementById('planSaludUsuario');
    if (saludEl && data.salud) {
        const s = data.salud;
        document.getElementById('planSaludPeso').textContent   = 'Peso: '   + (s.peso   ? s.peso   + ' kg' : 'No registrado');
        document.getElementById('planSaludAltura').textContent = 'Altura: ' + (s.altura ? s.altura + ' m'  : 'No registrado');
        document.getElementById('planSaludImc').textContent    = 'IMC: '    + (s.imc    ? s.imc             : 'No calculado');
        saludEl.style.display = 'flex';
    }

    const elE = document.getElementById('planEjerciciosList');
    if (elE) {
        const list = data.plan && data.plan.ejercicios ? data.plan.ejercicios : [];
        elE.innerHTML = list.length
            ? list.map(e => planProItem(esc(e.titulo), e.notas, 'ejercicio', e.asignacion_id)).join('')
            : '<p style="color:#999;padding:8px 0;">Sin ejercicios asignados aún.</p>';
        const sel = document.getElementById('planEjercicioSelect');
        if (sel && data.ejercicios_disponibles) {
            sel.innerHTML = '<option value="">— Elige un ejercicio —</option>' +
                data.ejercicios_disponibles.map(e => `<option value="${e.id}">${esc(e.titulo)}</option>`).join('');
        }
    }

    const elR = document.getElementById('planRecetasList');
    if (elR) {
        const list = data.plan && data.plan.recetas ? data.plan.recetas : [];
        elR.innerHTML = list.length
            ? list.map(r => planProItem(esc(r.titulo), r.notas, 'receta', r.asignacion_id)).join('')
            : '<p style="color:#999;padding:8px 0;">Sin recetas asignadas aún.</p>';
        const sel = document.getElementById('planRecetaSelect');
        if (sel && data.recetas_disponibles) {
            sel.innerHTML = '<option value="">— Elige una receta —</option>' +
                data.recetas_disponibles.map(r => `<option value="${r.id}">${esc(r.titulo)}</option>`).join('');
        }
    }

    const elRec = document.getElementById('planRecomendacionesList');
    if (elRec) {
        const list = data.plan && data.plan.recomendaciones ? data.plan.recomendaciones : [];
        elRec.innerHTML = list.length
            ? list.map(r => planProItem(esc(r.titulo), r.contenido, 'recomendacion', r.id, esc(r.tipo))).join('')
            : '<p style="color:#999;padding:8px 0;">Sin recomendaciones aún.</p>';
    }
}

function planProItem(titulo, notas, tipo, id, badge = '') {
    return `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border:1px solid rgba(255,107,53,0.2);border-radius:8px;margin-bottom:8px;gap:12px;background:rgba(255,107,53,0.04);">
        <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:0.9rem;color:var(--color-text-primary);">${titulo}${badge ? ` <span style="font-size:0.72rem;background:#ff6b3520;color:#ff6b35;padding:1px 7px;border-radius:10px;font-weight:500;">${badge}</span>` : ''}</div>
            ${notas ? `<div style="font-size:0.8rem;color:var(--color-text-secondary);margin-top:2px;">${esc(notas)}</div>` : ''}
        </div>
        <button onclick="quitarDelPlan('${tipo}', ${id})" style="padding:5px 12px;border:1px solid #f44336;background:transparent;color:#f44336;border-radius:6px;cursor:pointer;font-size:0.8rem;white-space:nowrap;flex-shrink:0;">Quitar</button>
    </div>`;
}

function abrirModalAsignarEjercicio() {
    if (!planUsuarioActual) { showProToast('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('planEjercicioNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarEjercicio').style.display = 'flex';
}

function abrirModalAsignarReceta() {
    if (!planUsuarioActual) { showProToast('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('planRecetaNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarReceta').style.display = 'flex';
}

function abrirModalRecomendacion() {
    if (!planUsuarioActual) { showProToast('Selecciona un usuario primero', 'error'); return; }
    document.getElementById('recTitulo').value   = '';
    document.getElementById('recContenido').value = '';
    document.getElementById('recTipo').value     = 'general';
    document.getElementById('modalRecomendacion').style.display = 'flex';
}

async function confirmarAsignarEjercicio() {
    const ejercicioId = document.getElementById('planEjercicioSelect').value;
    const notas       = document.getElementById('planEjercicioNotas').value.trim();
    if (!ejercicioId) { showProToast('Selecciona un ejercicio', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/plan/asignar-ejercicio', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: planUsuarioActual, ejercicio_id: ejercicioId, notas: notas || null }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarEjercicio').style.display = 'none';
        showProToast('Ejercicio asignado al plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al asignar', 'error'); }
}

async function confirmarAsignarReceta() {
    const recetaId = document.getElementById('planRecetaSelect').value;
    const notas    = document.getElementById('planRecetaNotas').value.trim();
    if (!recetaId) { showProToast('Selecciona una receta', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/plan/asignar-receta', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: planUsuarioActual, receta_id: recetaId, notas: notas || null }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarReceta').style.display = 'none';
        showProToast('Receta asignada al plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al asignar', 'error'); }
}

async function confirmarRecomendacion() {
    const titulo    = document.getElementById('recTitulo').value.trim();
    const contenido = document.getElementById('recContenido').value.trim();
    const tipo      = document.getElementById('recTipo').value;
    if (!titulo) { showProToast('El título es requerido', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/plan/recomendar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: planUsuarioActual, titulo, contenido, tipo }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalRecomendacion').style.display = 'none';
        showProToast('Recomendación agregada');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al agregar', 'error'); }
}

async function quitarDelPlan(tipo, id) {
    if (!confirm('¿Quitar este elemento del plan del usuario?')) return;
    try {
        const res  = await fetch(API_URL + '/pro/plan/remove', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tipo, id }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showProToast('Eliminado del plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al eliminar', 'error'); }
}

/* ── Modal editar peso / altura ── */
function abrirModalSalud() {
    const modal = document.getElementById('modalSaludUsuario');
    if (!modal) return;
    const s = planDataPro.salud || {};
    document.getElementById('saludPesoInput').value   = s.peso   || '';
    document.getElementById('saludAlturaInput').value = s.altura || '';
    document.getElementById('saludMsg').style.display = 'none';
    modal.style.display = 'flex';
}

function cerrarModalSalud() {
    const modal = document.getElementById('modalSaludUsuario');
    if (modal) modal.style.display = 'none';
}

async function guardarSalud() {
    const userId = document.getElementById('planUsuarioId').value;
    const peso   = parseFloat(document.getElementById('saludPesoInput').value)   || null;
    const altura = parseFloat(document.getElementById('saludAlturaInput').value) || null;
    const msg    = document.getElementById('saludMsg');
    if (!userId) return;
    try {
        const res  = await fetch(API_URL + '/pro/usuario/salud', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: +userId, peso, altura }) });
        const data = await res.json();
        if (data.success) {
            if (!planDataPro.salud) planDataPro.salud = {};
            planDataPro.salud.peso   = peso;
            planDataPro.salud.altura = altura;
            planDataPro.salud.imc    = data.imc;
            document.getElementById('planSaludPeso').textContent   = 'Peso: '   + (peso   ? peso   + ' kg' : 'No registrado');
            document.getElementById('planSaludAltura').textContent = 'Altura: ' + (altura ? altura + ' m'  : 'No registrado');
            document.getElementById('planSaludImc').textContent    = 'IMC: '    + (data.imc ? data.imc   : 'No calculado');
            cerrarModalSalud();
            showProToast('Datos de salud actualizados ✓', 'success');
        } else {
            msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;font-size:0.85rem;';
            msg.textContent   = data.message || 'Error al guardar';
        }
    } catch (e) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;font-size:0.85rem;';
        msg.textContent   = 'Error de conexión';
    }
}

/* ── Psicólogo: Mis Recomendaciones ── */

async function cargarRecomendacionesPro() {
    const tbody = document.getElementById('proRecomendacionesBody');
    if (!tbody) return;
    try {
        const res  = await fetch(API_URL + '/pro/recomendaciones');
        const data = await res.json();
        if (!data.success) throw new Error();
        if (!data.recomendaciones.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay recomendaciones aún.</td></tr>';
            return;
        }
        const tipoColor = { psicologia: '#9c27b0', ejercicio: '#ff6b35', alimentacion: '#4caf50', general: '#2196f3' };
        tbody.innerHTML = data.recomendaciones.map(r => {
            const color = tipoColor[r.tipo] || tipoColor.general;
            const fecha = r.created_at ? r.created_at.substring(0, 10) : '—';
            return `<tr>
                <td>${esc(r.usuario_nombre)}<br><small style="color:#999;">${esc(r.usuario_correo)}</small></td>
                <td>${esc(r.titulo)}${r.contenido ? `<br><small style="color:#999;">${esc(r.contenido.substring(0, 60))}${r.contenido.length > 60 ? '…' : ''}</small>` : ''}</td>
                <td><span style="color:${color};font-weight:600;font-size:0.82rem;">${esc(r.tipo)}</span></td>
                <td style="color:#999;font-size:0.85rem;">${fecha}</td>
                <td><button onclick="eliminarRecPro(${r.id})" style="padding:4px 12px;border:1px solid #f44336;background:transparent;color:#f44336;border-radius:6px;cursor:pointer;font-size:0.8rem;">Eliminar</button></td>
            </tr>`;
        }).join('');
    } catch (e) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar.</td></tr>';
    }
}

async function abrirModalNuevaRecPro() {
    const selU = document.getElementById('recProUsuario');
    if (selU && (selU.options.length <= 1 || selU.options[0].value === '')) {
        try {
            const res  = await fetch(API_URL + '/pro/usuarios-list');
            const data = await res.json();
            if (data.success) {
                selU.innerHTML = '<option value="">— Selecciona un usuario —</option>' +
                    data.usuarios.map(u => `<option value="${u.id}">${esc(u.nombre)} (${esc(u.correo)})</option>`).join('');
            }
        } catch (e) { }
    }
    document.getElementById('recProTitulo').value   = '';
    document.getElementById('recProContenido').value = '';
    document.getElementById('recProTipo').value     = 'psicologia';
    document.getElementById('modalNuevaRecPro').style.display = 'flex';
}

async function guardarRecPro() {
    const usuarioId = document.getElementById('recProUsuario').value;
    const titulo    = document.getElementById('recProTitulo').value.trim();
    const contenido = document.getElementById('recProContenido').value.trim();
    const tipo      = document.getElementById('recProTipo').value;
    if (!usuarioId) { showProToast('Selecciona un usuario', 'error'); return; }
    if (!titulo)    { showProToast('El título es requerido', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/plan/recomendar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: usuarioId, titulo, contenido, tipo }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        clearFormDraft('formNuevaRecPro');
        document.getElementById('modalNuevaRecPro').style.display = 'none';
        showProToast('Recomendación guardada');
        cargarRecomendacionesPro();
    } catch (e) { showProToast(e.message || 'Error al guardar', 'error'); }
}

async function eliminarRecPro(id) {
    if (!confirm('¿Eliminar esta recomendación?')) return;
    try {
        const res  = await fetch(API_URL + '/pro/plan/remove', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ tipo: 'recomendacion', id }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showProToast('Recomendación eliminada');
        cargarRecomendacionesPro();
    } catch (e) { showProToast(e.message || 'Error al eliminar', 'error'); }
}


/* ════════════════════════════════════════════════
   SECCIÓN: PLANES ALIMENTICIOS
   ════════════════════════════════════════════════ */

const CHEVRON_SVG = `<svg class="picker-trigger-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>`;

async function cargarPlanesAlimenticios() {
    const tbody = document.getElementById('proPlanesAlimBody');
    if (!tbody) return;
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios');
        const data = await res.json();
        if (!data.success) throw new Error();
        if (!data.planes.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes planes alimenticios aún. ¡Crea el primero!</td></tr>';
            return;
        }
        tbody.innerHTML = data.planes.map(p => `
            <tr>
                <td><strong>${esc(p.nombre)}</strong>${p.descripcion ? `<br><small style="color:#999;">${esc(p.descripcion.substring(0, 60))}${p.descripcion.length > 60 ? '…' : ''}</small>` : ''}</td>
                <td style="color:#999;font-size:0.85rem;">${p.objetivo ? esc(p.objetivo) : '—'}</td>
                <td style="text-align:center;">${p.duracion_semanas} sem.</td>
                <td style="text-align:center;">${p.num_recetas}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editarPlanAlim(${p.id})">Editar</button>
                    <button class="btn btn-sm" style="background:#4caf50;color:white;border:none;margin-left:4px;" onclick="exportarPlanAlimPDF(${p.id})">PDF</button>
                    <button class="btn btn-sm" style="background:#c0392b;color:white;border:none;margin-left:4px;" onclick="eliminarPlanAlim(${p.id},'${esc(p.nombre)}')">Eliminar</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar planes.</td></tr>';
    }
}

async function cargarRecetasDisponiblesPA() {
    try {
        const res  = await fetch(API_URL + '/pro/recetas');
        const data = await res.json();
        if (data.success) recetasDisponiblesPA = data.recetas || [];
    } catch (e) { }
}

async function cargarPlanesAlimSelector() {
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios');
        const data = await res.json();
        if (!data.success) return;
        const sel = document.getElementById('asignarPlanAlimSelect');
        if (!sel) return;
        sel.innerHTML = '<option value="">— Elige un plan —</option>' +
            data.planes.map(p => `<option value="${p.id}">${esc(p.nombre)} (${p.num_recetas} recetas)</option>`).join('');
    } catch (e) { }
}

function abrirModalPlanAlim(id = null) {
    document.getElementById('plan_alim_id').value          = id || '';
    document.getElementById('plan_alim_nombre').value      = '';
    document.getElementById('plan_alim_descripcion').value = '';
    document.getElementById('plan_alim_objetivo').value    = '';
    document.getElementById('plan_alim_duracion').value    = '1';
    document.getElementById('planAlimRecetasList').innerHTML = '';
    document.getElementById('modalPlanAlimTitle').textContent = id ? 'Editar Plan Alimenticio' : 'Nuevo Plan Alimenticio';
    document.getElementById('modalPlanAlim').style.display    = 'flex';
}

async function editarPlanAlim(id) {
    abrirModalPlanAlim(id);
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        const p = data.plan;
        document.getElementById('plan_alim_nombre').value      = p.nombre;
        document.getElementById('plan_alim_descripcion').value = p.descripcion || '';
        document.getElementById('plan_alim_objetivo').value    = p.objetivo || '';
        document.getElementById('plan_alim_duracion').value    = p.duracion_semanas || 1;
        document.getElementById('planAlimRecetasList').innerHTML = '';
        (p.recetas || []).forEach(r => agregarRecetaPlanAlim(r));
    } catch (e) { showProToast('Error al cargar el plan', 'error'); }
}

function cerrarModalPlanAlim() {
    closeItemPickerPanel && closeItemPickerPanel();
    document.getElementById('modalPlanAlim').style.display = 'none';
}

function buildRecetaTriggerPA(receta) {
    if (!receta) {
        return `<div class="picker-trigger-icon" style="background:rgba(76,175,80,.1);">🍽️</div>
                <span class="picker-trigger-text"><span class="picker-trigger-title" style="opacity:.45;">— Buscar receta... —</span></span>`;
    }
    const thumb = receta.imagen
        ? `<img class="picker-trigger-thumb" src="${esc(receta.imagen)}" onerror="this.style.display='none'">`
        : `<div class="picker-trigger-icon" style="background:rgba(76,175,80,.1);">🍽️</div>`;
    const cat = receta.categoria
        ? `<span class="picker-trigger-badge" style="background:rgba(76,175,80,.12);color:#4caf50;">${esc(receta.categoria)}</span>`
        : '';
    return `${thumb}<span class="picker-trigger-text"><span class="picker-trigger-title">${esc(receta.titulo)}</span></span>${cat}`;
}

function abrirPickerRecetaPA(trigger) {
    if (trigger.classList.contains('open')) { closeItemPickerPanel(); return; }
    openItemPicker(trigger, recetasDisponiblesPA, 'categoria', function (receta) {
        const row = trigger.closest('.plan-rec-row');
        row.querySelector('.prec-receta').value = receta.id;
        trigger.dataset.value = receta.id;
        trigger.classList.add('has-value');
        trigger.innerHTML = buildRecetaTriggerPA(receta) + CHEVRON_SVG;
    }, 'green');
}

function agregarRecetaPlanAlim(datos = null) {
    const container = document.getElementById('planAlimRecetasList');
    const row       = document.createElement('div');
    row.className   = 'picker-row picker-row-green plan-rec-row';
    const selRec    = datos ? recetasDisponiblesPA.find(r => r.id == datos.receta_id) : null;

    const optsDia = DIAS_SEMANA.slice(1).map((d, i) =>
        `<option value="${i + 1}" ${datos && datos.dia_semana == i + 1 ? 'selected' : ''}>${d}</option>`
    ).join('');

    const optsTiempo = TIEMPOS_COMIDA.map(t =>
        `<option value="${t}" ${datos && datos.tiempo_comida === t ? 'selected' : ''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`
    ).join('');

    row.innerHTML = `
        <div class="picker-row-top">
            <input type="hidden" class="prec-receta" value="${datos ? esc(String(datos.receta_id || '')) : ''}">
            <button type="button" class="item-picker-trigger ${selRec ? 'has-value' : ''}"
                    data-value="${datos ? esc(String(datos.receta_id || '')) : ''}"
                    onclick="abrirPickerRecetaPA(this)">
                ${buildRecetaTriggerPA(selRec)}
                ${CHEVRON_SVG}
            </button>
            <button type="button" class="picker-row-remove" onclick="this.closest('.plan-rec-row').remove()">✕</button>
        </div>
        <div class="picker-row-fields">
            <div class="picker-field"><label>Día</label><select class="prec-dia">${optsDia}</select></div>
            <div class="picker-field"><label>Momento</label><select class="prec-tiempo">${optsTiempo}</select></div>
            <div class="picker-field"><label>Porciones</label>
                <input type="number" class="prec-porciones" value="${datos ? esc(String(datos.porciones || 1)) : 1}" min="0.5" step="0.5"></div>
            <div class="picker-field"><label>Notas</label>
                <input type="text" class="prec-notas" value="${datos ? esc(datos.notas || '') : ''}" placeholder="Opcional"></div>
        </div>
    `;
    container.appendChild(row);
}

async function guardarPlanAlim() {
    const nombre = document.getElementById('plan_alim_nombre').value.trim();
    if (!nombre) { showProToast('El nombre es requerido', 'error'); return; }

    const rows   = document.querySelectorAll('.plan-rec-row');
    const recetas = [];
    for (const row of rows) {
        const recId = row.querySelector('.prec-receta').value;
        if (!recId) { showProToast('Selecciona una receta en cada fila', 'error'); return; }
        recetas.push({
            receta_id:    recId,
            dia_semana:   parseInt(row.querySelector('.prec-dia').value),
            tiempo_comida: row.querySelector('.prec-tiempo').value,
            porciones:    parseFloat(row.querySelector('.prec-porciones').value) || 1,
            notas:        row.querySelector('.prec-notas').value.trim() || null,
        });
    }

    const payload = {
        id:               document.getElementById('plan_alim_id').value || null,
        nombre,
        descripcion:      document.getElementById('plan_alim_descripcion').value.trim() || null,
        objetivo:         document.getElementById('plan_alim_objetivo').value.trim()    || null,
        duracion_semanas: parseInt(document.getElementById('plan_alim_duracion').value) || 1,
        recetas,
    };

    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios/save', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        if (!payload.id) clearFormDraft('formPlanAlim');
        cerrarModalPlanAlim();
        showProToast(payload.id ? 'Plan actualizado' : 'Plan creado');
        cargarPlanesAlimenticios();
        cargarPlanesAlimSelector();
    } catch (e) { showProToast(e.message || 'Error al guardar', 'error'); }
}

async function eliminarPlanAlim(id, nombre) {
    if (!confirm(`¿Eliminar el plan "${nombre}"?\nEsta acción no se puede deshacer.`)) return;
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios/delete', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showProToast('Plan eliminado');
        cargarPlanesAlimenticios();
    } catch (e) { showProToast(e.message || 'Error al eliminar', 'error'); }
}

async function exportarPlanAlimPDF(id) {
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        generarPlanAlimPDF(data.plan);
    } catch (e) { showProToast('Error al generar PDF', 'error'); }
}

function generarPlanAlimPDF(plan) {
    const { jsPDF }    = window.jspdf;
    const doc          = new jsPDF();
    const green        = [76, 175, 80];
    const dark         = [17, 17, 17];
    const gray         = [100, 100, 100];
    const especialista = (typeof PROFESSIONAL_USER !== 'undefined' && PROFESSIONAL_USER.nombre) ? PROFESSIONAL_USER.nombre : '';

    doc.setFillColor(...green);
    doc.rect(0, 0, 210, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(17);
    doc.setFont('helvetica', 'bold');
    doc.text('PLAN ALIMENTICIO', 14, 13);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Bienestar — Plan Nutricional Personalizado', 14, 21);
    if (especialista) doc.text('Nutriólogo/a: ' + especialista, 14, 28);

    let y = 42;
    doc.setTextColor(...dark);
    doc.setFontSize(15);
    doc.setFont('helvetica', 'bold');
    doc.text(plan.nombre, 14, y);
    y += 7;

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...gray);
    const partes = ['Duración: ' + plan.duracion_semanas + ' semana(s)', 'Recetas: ' + (plan.recetas || []).length];
    if (plan.objetivo) partes.push('Objetivo: ' + plan.objetivo);
    doc.text(partes.join('   |   '), 14, y);
    y += 5;

    if (plan.descripcion) {
        y += 2;
        const lines = doc.splitTextToSize(plan.descripcion, 182);
        doc.text(lines, 14, y);
        y += lines.length * 4.5 + 1;
    }

    y += 4;
    doc.setDrawColor(...green);
    doc.setLineWidth(0.5);
    doc.line(14, y, 196, y);
    y += 7;

    if (plan.recetas && plan.recetas.length) {
        const byDay = {};
        plan.recetas.forEach(r => { const d = r.dia_semana; if (!byDay[d]) byDay[d] = []; byDay[d].push(r); });

        const tableBody = [];
        Object.keys(byDay).sort((a, b) => a - b).forEach(dia => {
            byDay[dia].forEach((r, i) => {
                tableBody.push([
                    i === 0 ? (DIAS_SEMANA[dia] || 'Día ' + dia) : '',
                    r.tiempo_comida ? r.tiempo_comida.charAt(0).toUpperCase() + r.tiempo_comida.slice(1) : '—',
                    r.receta_titulo || '—',
                    r.porciones != null ? String(r.porciones) : '1',
                    r.calorias ? r.calorias + ' kcal' : '—',
                    r.notas || '',
                ]);
            });
        });

        doc.autoTable({
            startY: y, margin: { left: 14, right: 14 },
            head: [['Día', 'Momento', 'Receta', 'Porc.', 'Calorías', 'Notas']],
            body: tableBody,
            styles: { fontSize: 9, cellPadding: { top: 4, right: 4, bottom: 4, left: 4 }, valign: 'middle', overflow: 'linebreak' },
            headStyles: { fillColor: green, textColor: 255, fontStyle: 'bold', valign: 'middle', halign: 'center' },
            alternateRowStyles: { fillColor: [240, 249, 240] },
            columnStyles: { 0: { cellWidth: 25, fontStyle: 'bold' }, 1: { cellWidth: 22, halign: 'center' }, 2: { cellWidth: 58 }, 3: { cellWidth: 15, halign: 'center' }, 4: { cellWidth: 24, halign: 'center' }, 5: { cellWidth: 38 } },
        });
    } else {
        doc.setTextColor(...gray);
        doc.setFontSize(10);
        doc.text('Sin recetas registradas.', 14, y);
    }

    const pageCount = doc.internal.getNumberOfPages();
    const fechaHoy  = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(180, 180, 180);
        doc.text('Bienestar — Generado el ' + fechaHoy, 14, 290);
        doc.text(`Página ${i} de ${pageCount}`, 196, 290, { align: 'right' });
    }

    doc.save(`plan-alimenticio-${plan.nombre.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`);
}

function abrirModalAsignarPlanAlim() {
    if (!planUsuarioActual) { showProToast('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('asignarPlanAlimNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarPlanAlim').style.display = 'flex';
}

async function confirmarAsignarPlanAlim() {
    const planId = document.getElementById('asignarPlanAlimSelect').value;
    const notas  = document.getElementById('asignarPlanAlimNotas').value.trim();
    if (!planId) { showProToast('Selecciona un plan', 'error'); return; }
    try {
        const res  = await fetch(API_URL + '/pro/planes-alimenticios/asignar', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ usuario_id: planUsuarioActual, plan_id: planId, notas: notas || null }) });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarPlanAlim').style.display = 'none';
        showProToast(data.message || 'Plan asignado');
        if (typeof cargarPlanUsuario === 'function') cargarPlanUsuario(planUsuarioActual);
    } catch (e) { showProToast(e.message || 'Error al asignar', 'error'); }
}


/* ════════════════════════════════════════════════
   SECCIÓN: HISTORIAL DE USUARIO
   ════════════════════════════════════════════════ */

let _historialData = null;
let _histTabActual = 'salud';

async function abrirHistorial() {
    const userId = document.getElementById('planUsuarioId').value;
    const nombre = document.getElementById('planUsuarioNombreTag').textContent;
    if (!userId) return;

    const modal = document.getElementById('modalHistorial');
    if (!modal) return;

    document.getElementById('historialUsuarioNombre').textContent = nombre;
    document.getElementById('histPanelSalud').innerHTML  = '<p style="color:#999;text-align:center;padding:2rem 0;">Cargando...</p>';
    document.getElementById('histPanelPlanes').innerHTML = '<p style="color:#999;text-align:center;padding:2rem 0;">Cargando...</p>';
    modal.style.display = 'flex';
    switchHistTab('salud');

    try {
        const res  = await fetch(API_URL + '/pro/historial-usuario?usuario_id=' + userId);
        const data = await res.json();
        if (!data.success) throw new Error();
        _historialData = data;
        renderHistorialSalud(data.historial_salud);
        renderHistorialPlanes(data.historial_planes);
    } catch (e) {
        document.getElementById('histPanelSalud').innerHTML  = '<p style="color:#f44336;text-align:center;padding:2rem 0;">Error al cargar historial.</p>';
        document.getElementById('histPanelPlanes').innerHTML = '<p style="color:#f44336;text-align:center;padding:2rem 0;">Error al cargar historial.</p>';
    }
}

function cerrarHistorial() {
    const modal = document.getElementById('modalHistorial');
    if (modal) modal.style.display = 'none';
}

function switchHistTab(tab) {
    _histTabActual = tab;
    const isSalud  = tab === 'salud';
    document.getElementById('histPanelSalud').style.display  = isSalud  ? '' : 'none';
    document.getElementById('histPanelPlanes').style.display = !isSalud ? '' : 'none';

    const btnSalud  = document.getElementById('histTabSalud');
    const btnPlanes = document.getElementById('histTabPlanes');
    if (isSalud) {
        btnSalud.style.borderBottomColor  = '#ff6b35';
        btnSalud.style.color              = '#ff6b35';
        btnSalud.style.fontWeight         = '700';
        btnPlanes.style.borderBottomColor = 'transparent';
        btnPlanes.style.color             = 'var(--color-text-secondary)';
        btnPlanes.style.fontWeight        = '600';
    } else {
        btnPlanes.style.borderBottomColor = '#ff6b35';
        btnPlanes.style.color             = '#ff6b35';
        btnPlanes.style.fontWeight        = '700';
        btnSalud.style.borderBottomColor  = 'transparent';
        btnSalud.style.color              = 'var(--color-text-secondary)';
        btnSalud.style.fontWeight         = '600';
    }
}

function renderHistorialSalud(lista) {
    const panel = document.getElementById('histPanelSalud');
    if (!lista || !lista.length) {
        panel.innerHTML = `
            <div style="text-align:center;padding:2.5rem 0;color:#999;">
                <div style="font-size:2rem;margin-bottom:0.5rem;">⚖️</div>
                <p style="margin:0;">Sin registros de salud aún.</p>
                <p style="margin:4px 0 0;font-size:0.82rem;">Los cambios de peso y altura aparecerán aquí.</p>
            </div>`;
        return;
    }

    panel.innerHTML = `
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:0.87rem;">
                <thead>
                    <tr style="border-bottom:2px solid var(--color-border,#eee);">
                        <th style="text-align:left;padding:8px 10px;color:#ff6b35;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">Fecha</th>
                        <th style="text-align:center;padding:8px 10px;color:#ff6b35;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">Peso</th>
                        <th style="text-align:center;padding:8px 10px;color:#ff6b35;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">Altura</th>
                        <th style="text-align:center;padding:8px 10px;color:#ff6b35;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">IMC</th>
                        <th style="text-align:left;padding:8px 10px;color:#ff6b35;font-size:0.78rem;text-transform:uppercase;letter-spacing:.5px;">Registrado por</th>
                    </tr>
                </thead>
                <tbody>
                    ${lista.map((r, i) => {
                        const fecha = r.fecha ? r.fecha.substring(0, 16).replace('T', ' ') : '—';
                        const imc   = r.imc ? parseFloat(r.imc).toFixed(1) : '—';
                        const imcColor = r.imc
                            ? (r.imc < 18.5 ? '#2196F3' : r.imc < 25 ? '#4CAF50' : r.imc < 30 ? '#FF9800' : '#F44336')
                            : '#999';
                        const isFirst = i === 0;
                        return `<tr style="border-bottom:1px solid var(--color-border,#f0f0f0);${isFirst ? 'background:rgba(255,107,53,0.04);' : ''}">
                            <td style="padding:9px 10px;color:var(--color-text-secondary);">${esc(fecha)}</td>
                            <td style="padding:9px 10px;text-align:center;font-weight:600;">${r.peso ? parseFloat(r.peso).toFixed(1) + ' kg' : '—'}</td>
                            <td style="padding:9px 10px;text-align:center;font-weight:600;">${r.altura ? parseFloat(r.altura).toFixed(2) + ' m' : '—'}</td>
                            <td style="padding:9px 10px;text-align:center;font-weight:700;color:${imcColor};">${imc}</td>
                            <td style="padding:9px 10px;color:var(--color-text-secondary);font-size:0.82rem;">${esc(r.profesional_nombre || r.profesional_email || '—')}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <p style="margin:10px 0 0;font-size:0.78rem;color:#bbb;">IMC: <span style="color:#2196F3;">&#60;18.5 bajo peso</span> · <span style="color:#4CAF50;">18.5–24.9 normal</span> · <span style="color:#FF9800;">25–29.9 sobrepeso</span> · <span style="color:#F44336;">≥30 obesidad</span></p>`;
}

function renderHistorialPlanes(lista) {
    const panel = document.getElementById('histPanelPlanes');
    if (!lista || !lista.length) {
        panel.innerHTML = `
            <div style="text-align:center;padding:2.5rem 0;color:#999;">
                <div style="font-size:2rem;margin-bottom:0.5rem;">📄</div>
                <p style="margin:0;">Sin historial de planes aún.</p>
                <p style="margin:4px 0 0;font-size:0.82rem;">Las asignaciones y cambios de plan aparecerán aquí.</p>
            </div>`;
        return;
    }

    const tipoIcon = {
        ejercicio:        '💪',
        receta:           '🍽️',
        recomendacion:    '💬',
        rutina:           '🏃',
        plan_alimenticio: '🥗',
    };
    const tipoLabel = {
        ejercicio:        'Ejercicio',
        receta:           'Receta',
        recomendacion:    'Recomendación',
        rutina:           'Rutina',
        plan_alimenticio: 'Plan alimenticio',
    };
    const accionColor = { asignado: '#4CAF50', removido: '#F44336' };
    const accionLabel = { asignado: '+ Asignado', removido: '− Removido' };

    panel.innerHTML = `
        <div style="display:flex;flex-direction:column;gap:8px;">
            ${lista.map(r => {
                const fecha = r.fecha ? r.fecha.substring(0, 16).replace('T', ' ') : '—';
                const icon  = tipoIcon[r.tipo]  || '📋';
                const label = tipoLabel[r.tipo] || r.tipo;
                const aColor = accionColor[r.accion] || '#999';
                const aLabel = accionLabel[r.accion] || r.accion;
                return `<div style="display:flex;align-items:flex-start;gap:12px;padding:10px 12px;border-radius:10px;border:1px solid var(--color-border,#eee);background:var(--color-bg-primary,#fff);">
                    <div style="font-size:1.3rem;line-height:1;margin-top:1px;">${icon}</div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <span style="font-weight:700;font-size:0.9rem;">${esc(r.item_titulo || '—')}</span>
                            <span style="font-size:0.72rem;padding:2px 8px;border-radius:10px;background:${aColor}20;color:${aColor};font-weight:600;">${aLabel}</span>
                            <span style="font-size:0.72rem;padding:2px 8px;border-radius:10px;background:rgba(0,0,0,0.06);color:var(--color-text-secondary);">${label}</span>
                        </div>
                        ${r.notas ? `<p style="margin:3px 0 0;font-size:0.8rem;color:var(--color-text-secondary);">${esc(r.notas)}</p>` : ''}
                        <p style="margin:3px 0 0;font-size:0.78rem;color:#bbb;">${esc(fecha)} · ${esc(r.profesional_nombre || r.profesional_email || '—')}</p>
                    </div>
                </div>`;
            }).join('')}
        </div>`;
}


/* ════════════════════════════════════════════════
   SECCIÓN: SOLICITUDES
   ════════════════════════════════════════════════ */

function playNotifSol() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.setValueAtTime(660, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.25, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    } catch (e) { }
}

async function pollSolicitudes() {
    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/count');
        const data = await res.json();
        const cnt  = data.count || 0;
        actualizarBadgeSol(cnt);
        if (_solCount >= 0 && cnt > _solCount) { playNotifSol(); cargarSolicitudes(); }
        _solCount = cnt;
    } catch (e) { }
}

function actualizarBadgeSol(cnt) {
    const navBadge = document.getElementById('solNavBadge');
    if (navBadge) { navBadge.textContent = cnt > 0 ? cnt : ''; navBadge.style.display = cnt > 0 ? 'inline' : 'none'; }
    const hdrBadge = document.getElementById('solBadgeHeader');
    if (hdrBadge) { hdrBadge.textContent = cnt > 0 ? cnt + ' nueva' + (cnt > 1 ? 's' : '') : ''; hdrBadge.style.display = cnt > 0 ? '' : 'none'; }
}

async function cargarSolicitudes() {
    try {
        const res  = await fetch(API_URL + '/pro/solicitudes');
        const data = await res.json();
        if (!data.success) return;

        const section = document.getElementById('seccionSolicitudes');
        const grid    = document.getElementById('solicitudesGrid');
        const sols    = data.solicitudes || [];

        sols.forEach(s => { _solDataCache[s.id] = s; });
        actualizarBadgeSol(sols.length);
        if (_solCount === -1) _solCount = sols.length;

        if (!sols.length) { if (section) section.style.display = 'none'; return; }
        if (section) section.style.display = '';
        if (grid)    grid.innerHTML = sols.map(s => renderSolicitud(s)).join('');
    } catch (e) { }
}

function renderSolicitud(s) {
    const fecha = s.fecha
        ? new Date(s.fecha + 'T00:00:00').toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' })
        : '—';
    return `<div id="sol-${s.id}" style="
        padding:14px 18px;border-radius:12px;
        border:1.5px solid var(--color-border,#e8e8e8);
        background:var(--color-bg-primary,#fff);
        display:flex;align-items:center;justify-content:space-between;
        flex-wrap:wrap;gap:12px;">
        <div style="flex:1;min-width:0;">
            <p style="margin:0;font-weight:700;font-size:0.95rem;">${esc(s.usuario_nombre || s.correo)}</p>
            <p style="margin:4px 0 0;font-size:0.82rem;color:var(--color-text-secondary);">${esc(s.sol_tipo)} · ${fecha}</p>
            ${s.descripcion ? `<p style="margin:4px 0 0;font-size:0.8rem;color:var(--color-text-light);">${esc(s.descripcion)}</p>` : ''}
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button onclick="aceptarSolicitud(${s.id})" style="padding:7px 16px;background:#16a34a;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.85rem;">✓ Aceptar</button>
            <button onclick="abrirModalDenegar(${s.id}, '${esc(s.sol_tipo)}')" style="padding:7px 16px;background:#dc2626;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.85rem;">✕ Denegar</button>
        </div>
    </div>`;
}

function aceptarSolicitud(id) {
    const sol = _solDataCache[id] || {};
    document.getElementById('aceptarSolId').value   = id;
    document.getElementById('aceptarTitulo').value  = 'Consulta de ' + (sol.sol_tipo || '');
    document.getElementById('aceptarFecha').value   = sol.fecha || '';
    document.getElementById('aceptarHora').value    = '';
    document.getElementById('aceptarNotas').value   = '';
    document.getElementById('aceptarMsg').style.display = 'none';
    document.getElementById('modalAceptarSol').style.display = 'flex';
}

function cerrarModalAceptar() {
    document.getElementById('modalAceptarSol').style.display = 'none';
}

async function confirmarAceptar() {
    const id     = +document.getElementById('aceptarSolId').value;
    const titulo = document.getElementById('aceptarTitulo').value.trim();
    const fecha  = document.getElementById('aceptarFecha').value;
    const hora   = document.getElementById('aceptarHora').value;
    const notas  = document.getElementById('aceptarNotas').value.trim();
    const msg    = document.getElementById('aceptarMsg');

    if (!titulo || !fecha || !hora) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Completa el título, fecha y hora.';
        return;
    }

    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/accion', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id, accion: 'aceptar', titulo, fecha, hora, notas: notas || null }) });
        const data = await res.json();
        if (data.success) {
            cerrarModalAceptar();
            document.getElementById('sol-' + id)?.remove();
            _solCount = Math.max(0, _solCount - 1);
            actualizarBadgeSol(_solCount);
            if (!document.querySelector('[id^="sol-"]')) {
                const sec = document.getElementById('seccionSolicitudes');
                if (sec) sec.style.display = 'none';
            }
            showProToast('Cita confirmada ✓', 'success');
        } else {
            msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
            msg.textContent   = data.message || 'Error al aceptar';
        }
    } catch (e) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Error de conexión';
    }
}

async function abrirModalDenegar(id, tipo) {
    document.getElementById('denegarSolId').value     = id;
    document.getElementById('denegarMotivo').value    = '';
    document.getElementById('denegarMsg').style.display = 'none';

    const sel = document.getElementById('denegarReasignar');
    sel.innerHTML = '<option value="">Cargando...</option>';
    try {
        const res    = await fetch(API_URL + '/especialistas?tipo=' + encodeURIComponent(tipo));
        const data   = await res.json();
        const proProp = typeof CURRENT_USER_EMAIL !== 'undefined' ? CURRENT_USER_EMAIL : '';
        sel.innerHTML = '<option value="">— Sin reasignar —</option>' +
            (data.especialistas || [])
                .filter(e => e.correo !== proProp)
                .map(e => `<option value="${esc(e.correo)}">${esc(e.nombre)}${e.area ? ' — ' + e.area : ''}</option>`)
                .join('');
    } catch (e) { sel.innerHTML = '<option value="">Error al cargar</option>'; }

    document.getElementById('modalDenegarSol').style.display = 'flex';
}

function cerrarModalDenegar() {
    document.getElementById('modalDenegarSol').style.display = 'none';
}

async function confirmarDenegar() {
    const id     = document.getElementById('denegarSolId').value;
    const motivo = document.getElementById('denegarMotivo').value.trim();
    const rea    = document.getElementById('denegarReasignar').value;
    const msg    = document.getElementById('denegarMsg');

    if (!motivo) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Escribe el motivo de la denegación.';
        return;
    }

    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/accion', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ id: +id, accion: 'denegar', motivo, reasignado_a: rea || null }) });
        const data = await res.json();
        if (data.success) {
            cerrarModalDenegar();
            document.getElementById('sol-' + id)?.remove();
            _solCount = Math.max(0, _solCount - 1);
            actualizarBadgeSol(_solCount);
            if (!document.querySelector('[id^="sol-"]')) {
                const sec = document.getElementById('seccionSolicitudes');
                if (sec) sec.style.display = 'none';
            }
            showProToast(rea ? 'Solicitud denegada y reasignada' : 'Solicitud denegada', 'info');
        } else {
            msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
            msg.textContent   = data.message || 'Error al denegar';
        }
    } catch (e) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Error de conexión';
    }
}
