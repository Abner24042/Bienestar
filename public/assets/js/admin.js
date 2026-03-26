
document.addEventListener('DOMContentLoaded', function () {
    initAdminPanel();
    loadStats();
});

/**
 * Inicializar panel de administrador
 */
function initAdminPanel() {
    // Gestión de Usuarios
    const btnVerUsuarios = document.getElementById('btnVerUsuarios');
    if (btnVerUsuarios) {
        btnVerUsuarios.addEventListener('click', verTodosUsuarios);
    }

    const btnAgregarUsuario = document.getElementById('btnAgregarUsuario');
    if (btnAgregarUsuario) {
        btnAgregarUsuario.addEventListener('click', agregarUsuario);
    }

    // Gestión de Citas
    const btnVerCitas = document.getElementById('btnVerCitas');
    if (btnVerCitas) {
        btnVerCitas.addEventListener('click', verTodasCitas);
    }

    const btnGenerarReporte = document.getElementById('btnGenerarReporte');
    if (btnGenerarReporte) {
        btnGenerarReporte.addEventListener('click', generarReporte);
    }

    // Reportes y Estadísticas
    const btnVerDashboard = document.getElementById('btnVerDashboard');
    if (btnVerDashboard) {
        btnVerDashboard.addEventListener('click', verDashboard);
    }

    const btnExportarDatos = document.getElementById('btnExportarDatos');
    if (btnExportarDatos) {
        btnExportarDatos.addEventListener('click', exportarDatos);
    }

    // Configuración
    const btnConfiguracion = document.getElementById('btnConfiguracion');
    if (btnConfiguracion) {
        btnConfiguracion.addEventListener('click', abrirConfiguracion);
    }

    const btnLogs = document.getElementById('btnLogs');
    if (btnLogs) {
        btnLogs.addEventListener('click', verLogs);
    }
}

/**
 * Cargar estadísticas reales y actualizar las tarjetas
 */
let cachedStats = null;

async function loadStats() {
    try {
        const response = await fetch(API_URL + '/admin/stats');
        const data = await response.json();
        if (!data.success) return;

        cachedStats = data.stats;
        const s = data.stats;

        const elUsuarios = document.getElementById('statUsuarios');
        const elUsuariosNuevos = document.getElementById('statUsuariosNuevos');
        const elCitas = document.getElementById('statCitas');
        const elCitasNuevos = document.getElementById('statCitasNuevos');

        if (elUsuarios) elUsuarios.textContent = s.usuarios;
        if (elUsuariosNuevos) elUsuariosNuevos.textContent = '+' + s.usuarios_nuevos_mes + ' este mes';
        if (elCitas) elCitas.textContent = s.citas_futuras;
        if (elCitasNuevos) elCitasNuevos.textContent = s.citas_semana + ' esta semana';

    } catch (error) {
        console.error('Error al cargar estadísticas:', error);
    }
}

/**
 * Ver todos los usuarios
 */
function verTodosUsuarios() {
    window.location.href = BASE_URL + '/admin/usuarios';
}

/**
 * Agregar nuevo usuario
 */
function agregarUsuario() {
    window.location.href = BASE_URL + '/admin/usuarios?action=add';
}

/**
 * Ver todas las citas
 */
function verTodasCitas() {
    window.location.href = BASE_URL + '/admin/citas';
}

/**
 * Generar reporte de citas (exportar CSV)
 */
function generarReporte() {
    descargarCSV('citas');
}

/**
 * Ver dashboard de estadísticas (modal con datos detallados)
 */
async function verDashboard() {
    const modal = document.getElementById('modalDashboard');
    const content = document.getElementById('dashboardStatsContent');
    if (!modal || !content) return;

    modal.style.display = 'flex';
    content.innerHTML = '<p style="text-align:center;color:#666;padding:2rem 0;">Cargando estadísticas...</p>';

    try {
        const response = await fetch(API_URL + '/admin/stats');
        const data = await response.json();
        if (!data.success) throw new Error('Error al obtener stats');

        const s = data.stats;
        cachedStats = s;

        content.innerHTML = `
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem; margin-bottom:1rem;">
                <div style="background:#f0f4ff; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#4285F4;">${s.usuarios}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Usuarios totales</div>
                    <div style="font-size:0.75rem; color:#34A853; margin-top:0.25rem;">+${s.usuarios_nuevos_mes} este mes</div>
                </div>
                <div style="background:#f0fff4; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#34A853;">${s.citas_futuras}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Citas próximas</div>
                    <div style="font-size:0.75rem; color:#34A853; margin-top:0.25rem;">${s.citas_semana} esta semana</div>
                </div>
                <div style="background:#fffbf0; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#FBBC04;">${s.citas}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Citas totales</div>
                </div>
            </div>
            <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:1rem;">
                <div style="background:#fdf0ff; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#9C27B0;">${s.ejercicios}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Ejercicios activos</div>
                </div>
                <div style="background:#fff0f0; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#EA4335;">${s.recetas}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Recetas</div>
                </div>
                <div style="background:#f0f8ff; border-radius:10px; padding:1.25rem; text-align:center;">
                    <div style="font-size:2rem; font-weight:700; color:#00acc1;">${s.noticias}</div>
                    <div style="font-size:0.8rem; color:#555; margin-top:0.25rem;">Noticias publicadas</div>
                </div>
            </div>`;

        // Update main cards too
        const elUsuarios = document.getElementById('statUsuarios');
        const elUsuariosNuevos = document.getElementById('statUsuariosNuevos');
        const elCitas = document.getElementById('statCitas');
        const elCitasNuevos = document.getElementById('statCitasNuevos');
        if (elUsuarios) elUsuarios.textContent = s.usuarios;
        if (elUsuariosNuevos) elUsuariosNuevos.textContent = '+' + s.usuarios_nuevos_mes + ' este mes';
        if (elCitas) elCitas.textContent = s.citas_futuras;
        if (elCitasNuevos) elCitasNuevos.textContent = s.citas_semana + ' esta semana';

    } catch (error) {
        content.innerHTML = '<p style="text-align:center;color:#EA4335;padding:2rem 0;">Error al cargar estadísticas</p>';
    }
}

function cerrarModalDashboard() {
    const modal = document.getElementById('modalDashboard');
    if (modal) modal.style.display = 'none';
}

/**
 * Mostrar modal de selección de exportación
 */
function exportarDatos() {
    const modal = document.getElementById('modalExportar');
    if (modal) modal.style.display = 'flex';
}

function cerrarModalExportar() {
    const modal = document.getElementById('modalExportar');
    if (modal) modal.style.display = 'none';
}

/**
 * Descargar CSV del tipo indicado (usuarios | citas)
 */
function descargarCSV(type) {
    cerrarModalExportar();
    showToast('Preparando descarga...', 'info');
    window.location.href = API_URL + '/admin/export?type=' + type;
}

/**
 * Abrir configuración
 */
function abrirConfiguracion() {
    window.location.href = BASE_URL + '/admin/configuracion';
}

/**
 * Ver logs del sistema
 */
function verLogs() {
    window.location.href = BASE_URL + '/admin/logs';
}

/**
 * Mostrar toast/notificación
 */
function showToast(message, type = 'info') {
    let toast = document.querySelector('.toast-notification');

    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }

    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;top:20px;right:20px;padding:1rem 1.5rem;border-radius:8px;background:white;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:10000;display:block;opacity:1;';

    setTimeout(function () {
        toast.style.opacity = '0';
        setTimeout(function () {
            toast.style.display = 'none';
        }, 300);
    }, 3000);
}
