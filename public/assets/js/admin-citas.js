/**
 * BIENIESTAR - Admin Gestión de Citas
 */

document.addEventListener('DOMContentLoaded', function() {
    loadAllAppointments();
});

async function loadAllAppointments() {
    try {
        const response = await fetch(API_URL + '/admin/appointments');
        const data = await response.json();

        if (data.success) {
            renderAppointments(data.appointments || []);
            updateStats(data.appointments || []);
        } else {
            showError(data.message || 'Error al cargar citas');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('Error de conexión al cargar citas');
    }
}

function updateStats(appointments) {
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('totalCitas').textContent = appointments.length;

    const proximas = appointments.filter(apt => apt.fecha >= today).length;
    document.getElementById('proximasCitas').textContent = proximas;

    const hoy = appointments.filter(apt => apt.fecha === today).length;
    document.getElementById('citasHoy').textContent = hoy;
}

function renderAppointments(appointments) {
    const tbody = document.getElementById('citasBody');

    if (!appointments || appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="empty-message">No hay citas registradas</td></tr>';
        return;
    }

    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    tbody.innerHTML = appointments.map(apt => {
        const parts = apt.fecha.split('-');
        const fechaStr = `${parseInt(parts[2])} ${meses[parseInt(parts[1]) - 1]} ${parts[0]}`;
        const hora = apt.hora ? apt.hora.substring(0, 5) : '--:--';
        const paciente = apt.nombre || apt.correo || 'Desconocido';
        const profesional = apt.profesional_correo || 'Sin asignar';

        return `<tr>
            <td>${apt.id}</td>
            <td>${escapeHtml(paciente)}</td>
            <td>${escapeHtml(apt.titulo)}</td>
            <td>${fechaStr}</td>
            <td>${hora}</td>
            <td class="text-small">${escapeHtml(profesional)}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="adminCancelAppointment(${apt.id})">
                    Cancelar
                </button>
            </td>
        </tr>`;
    }).join('');
}

async function adminCancelAppointment(id) {
    if (!confirm('¿Estás seguro de que deseas cancelar esta cita? Esta acción no se puede deshacer.')) {
        return;
    }

    try {
        const response = await fetch(API_URL + '/appointments/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });

        const result = await response.json();

        if (result.success) {
            alert('Cita cancelada exitosamente');
            loadAllAppointments();
        } else {
            alert(result.message || 'Error al cancelar la cita');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error de conexión');
    }
}

function showError(message) {
    const tbody = document.getElementById('citasBody');
    tbody.innerHTML = `<tr><td colspan="7" class="empty-message" style="color: #e53935;">${escapeHtml(message)}</td></tr>`;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
