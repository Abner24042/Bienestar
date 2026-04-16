let allAppointments = [];
let currentFilter   = 'all';

document.addEventListener('DOMContentLoaded', function () {
    loadAllAppointments();

    document.getElementById('searchCitas').addEventListener('input', function () {
        renderAppointments(applyFilters(allAppointments));
    });
});

async function loadAllAppointments() {
    try {
        const response = await fetch(API_URL + '/admin/appointments');
        const data     = await response.json();

        if (data.success) {
            allAppointments = data.appointments || [];
            updateStats(allAppointments, data.pendientes || 0);
            renderAppointments(applyFilters(allAppointments));
        } else {
            showError(data.message || 'Error al cargar citas');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error de conexión al cargar citas');
    }
}

function updateStats(appointments, pendientes) {
    const today = new Date().toISOString().split('T')[0];
    const reales = appointments.filter(a => !a.es_solicitud || a.es_solicitud == 0 || a.sol_estado === 'aceptada');

    document.getElementById('totalCitas').textContent         = reales.length;
    document.getElementById('proximasCitas').textContent      = reales.filter(a => a.fecha > today).length;
    document.getElementById('citasHoy').textContent           = reales.filter(a => a.fecha === today).length;
    document.getElementById('solicitudesPendientes').textContent = pendientes;
}

function setFilter(filter, btn) {
    currentFilter = filter;
    document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    renderAppointments(applyFilters(allAppointments));
}

function applyFilters(appointments) {
    const today  = new Date().toISOString().split('T')[0];
    const search = (document.getElementById('searchCitas')?.value || '').toLowerCase();

    return appointments.filter(apt => {
        // Filtro de tab
        if (currentFilter === 'hoy')      { if (apt.fecha !== today || (apt.es_solicitud == 1 && apt.sol_estado !== 'aceptada')) return false; }
        if (currentFilter === 'proxima')  { if (apt.fecha <= today  || (apt.es_solicitud == 1 && apt.sol_estado !== 'aceptada')) return false; }
        if (currentFilter === 'pasada')   { if (apt.fecha >= today  || (apt.es_solicitud == 1 && apt.sol_estado !== 'aceptada')) return false; }
        if (currentFilter === 'solicitud'){ if (!apt.es_solicitud || apt.es_solicitud == 0) return false; }

        // Búsqueda
        if (search) {
            const paciente  = (apt.nombre || apt.correo || '').toLowerCase();
            const titulo    = (apt.titulo  || '').toLowerCase();
            const profesional = (apt.profesional_correo || apt.sol_profesional || '').toLowerCase();
            if (!paciente.includes(search) && !titulo.includes(search) && !profesional.includes(search)) return false;
        }

        return true;
    });
}

function renderAppointments(appointments) {
    const tbody = document.getElementById('citasBody');

    if (!appointments || appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-message">No hay citas que coincidan</td></tr>';
        return;
    }

    const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    const today = new Date().toISOString().split('T')[0];

    tbody.innerHTML = appointments.map(apt => {
        const parts     = (apt.fecha || '').split('-');
        const fechaStr  = parts.length === 3 ? `${parseInt(parts[2])} ${meses[parseInt(parts[1])-1]} ${parts[0]}` : apt.fecha;
        const hora      = apt.hora ? apt.hora.substring(0, 5) : '--:--';
        const paciente  = esc(apt.nombre || apt.correo || 'Desconocido');
        const correo    = esc(apt.correo || '');
        const esSolicitud = apt.es_solicitud == 1;
        const solEstado   = apt.sol_estado || '';
        const profesional = esc(apt.profesional_correo || apt.sol_profesional || 'Sin asignar');

        // Badge de estado
        let estadoBadge = '';
        if (esSolicitud) {
            estadoBadge = `<span class="badge-solicitud">Solicitud</span> `;
            if      (solEstado === 'pendiente')  estadoBadge += `<span class="badge-pendiente">Pendiente</span>`;
            else if (solEstado === 'aceptada')   estadoBadge += `<span class="badge-aceptada">Aceptada</span>`;
            else if (solEstado === 'denegada')   estadoBadge += `<span class="badge-denegada">Denegada</span>`;
            else if (solEstado === 'reasignada') estadoBadge += `<span class="badge-reasignada">Reasignada</span>`;
            else                                 estadoBadge += `<span class="badge-pendiente">${esc(solEstado)}</span>`;
        } else if (apt.fecha < today) {
            estadoBadge = '<span class="status-badge status-inactive">Pasada</span>';
        } else if (apt.fecha === today) {
            estadoBadge = '<span class="status-badge" style="background:#fff3e0;color:#e65100;">Hoy</span>';
        } else {
            estadoBadge = '<span class="status-badge status-active">Próxima</span>';
        }

        // Descripción extra para solicitudes
        const descExtra = esSolicitud && apt.descripcion
            ? `<div class="td-sub" title="${esc(apt.descripcion)}" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${esc(apt.descripcion)}</div>`
            : '';

        return `<tr>
            <td class="td-id">#${apt.id}</td>
            <td>
                <div class="td-title">${esc(apt.titulo)}</div>
                ${esSolicitud && apt.sol_tipo ? `<div class="td-sub">Tipo: ${esc(apt.sol_tipo)}</div>` : ''}
                ${descExtra}
            </td>
            <td>
                <div class="td-title">${paciente}</div>
                ${correo ? `<div class="td-sub">${correo}</div>` : ''}
            </td>
            <td>
                <div class="td-title">${fechaStr}</div>
                <div class="td-sub">⏰ ${hora}</div>
            </td>
            <td class="td-sub">${profesional}</td>
            <td>${estadoBadge}</td>
            <td>
                <div class="action-btns">
                    <button class="btn btn-danger btn-sm" onclick="adminDeleteAppointment(${apt.id}, '${esc(apt.titulo)}')">
                        ${esSolicitud ? 'Eliminar' : 'Cancelar'}
                    </button>
                </div>
            </td>
        </tr>`;
    }).join('');
}

async function adminDeleteAppointment(id, titulo) {
    const label = titulo ? `"${titulo}"` : 'esta cita';
    if (!confirm(`¿Eliminar ${label} del sistema? Esta acción no se puede deshacer.`)) return;

    try {
        const response = await fetch(API_URL + '/admin/appointments/delete', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ id })
        });
        const result = await response.json();

        if (result.success) {
            showToastCita(result.message || 'Cita eliminada', 'success');
            // Quitar de la lista local sin recargar
            allAppointments = allAppointments.filter(a => a.id != id);
            const today = new Date().toISOString().split('T')[0];
            const pendientes = allAppointments.filter(a => a.es_solicitud == 1 && a.sol_estado === 'pendiente').length;
            updateStats(allAppointments, pendientes);
            renderAppointments(applyFilters(allAppointments));
        } else {
            showToastCita(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToastCita('Error de conexión', 'error');
    }
}

function showError(message) {
    const tbody = document.getElementById('citasBody');
    tbody.innerHTML = `<tr><td colspan="7" class="empty-message" style="color:#e53935;">${esc(message)}</td></tr>`;
}

function showToastCita(msg, type = 'success') {
    const t = document.getElementById('adminCitaToast');
    if (!t) return;
    t.textContent = msg;
    t.style.background = type === 'success' ? '#43a047' : '#e53935';
    t.style.color = '#fff';
    t.style.display = 'block';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => { t.style.display = 'none'; }, 3000);
}

function esc(text) {
    if (!text) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}
