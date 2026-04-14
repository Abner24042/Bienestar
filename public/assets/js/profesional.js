
document.addEventListener('DOMContentLoaded', function () {
    loadUsersList();
    loadProfessionalAppointments();

    const form = document.getElementById('formProfessionalAppointment');
    if (form) {
        form.addEventListener('submit', handleCreateAppointment);
    }
});

/**
 * Cargar lista de usuarios regulares para el dropdown
 */
async function loadUsersList() {
    try {
        const response = await fetch(API_URL + '/users');
        const data = await response.json();

        if (data.success) {
            const select = document.getElementById('select_user');
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

/**
 * Cargar citas del profesional
 */
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

/**
 * Mostrar citas en la tabla
 */
function displayAppointmentsTable(appointments) {
    const tbody = document.getElementById('appointmentsTableBody');

    if (!appointments || appointments.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay citas programadas</td></tr>';
        return;
    }

    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    tbody.innerHTML = appointments.map(apt => {
        const estadoColors = {
            pendiente:  '#FF9800',
            confirmada: '#4CAF50',
            aceptada:   '#4CAF50',
            cancelada:  '#F44336',
            completada: '#2196F3'
        };
        // Solicitudes aceptadas usan sol_estado; citas normales usan estado
        const estadoRaw = (apt.es_solicitud == 1 && apt.sol_estado)
            ? apt.sol_estado
            : (apt.estado || 'pendiente');
        const estadoLabel = estadoRaw === 'aceptada' ? 'confirmada' : estadoRaw;
        const color = estadoColors[estadoRaw] || '#999';

        // Formatear fecha
        const parts = apt.fecha.split('-');
        const fechaStr = `${parseInt(parts[2])} ${meses[parseInt(parts[1]) - 1]} ${parts[0]}`;

        // Formatear hora
        const hora = apt.hora.substring(0, 5);

        return `<tr>
            <td>${fechaStr}</td>
            <td>${hora}</td>
            <td>${apt.titulo}</td>
            <td>
                ${apt.usuario_nombre || 'N/A'}
                <br><small class="pro-agenda-email">${apt.usuario_correo || ''}</small>
            </td>
            <td>
                <span style="background:${color};color:white;padding:0.25rem 0.75rem;border-radius:12px;font-size:0.85rem;">
                    ${estadoLabel}
                </span>
            </td>
        </tr>`;
    }).join('');
}

/**
 * Actualizar estadisticas
 */
function updateStats(appointments) {
    const today = new Date().toISOString().split('T')[0];

    const citasHoy = appointments.filter(a => a.fecha === today).length;
    const el1 = document.getElementById('citasHoy');
    if (el1) el1.textContent = citasHoy;

    const el2 = document.getElementById('citasProximas');
    if (el2) el2.textContent = appointments.length;
}

/**
 * Crear cita como profesional
 */
async function handleCreateAppointment(e) {
    e.preventDefault();

    const form = e.target;
    const btnText = form.querySelector('.btn-text');
    const btnLoader = form.querySelector('.btn-loader');
    const submitBtn = form.querySelector('button[type="submit"]');

    const userEmail = document.getElementById('select_user').value;
    const title = document.getElementById('pro_title').value;
    const date = document.getElementById('pro_date').value;
    const time = document.getElementById('pro_time').value;
    const description = document.getElementById('pro_description').value;
    const syncGoogle = document.getElementById('syncGoogleCalendar').checked;

    if (!userEmail || !title || !date || !time) {
        showProToast('Completa todos los campos requeridos', 'error');
        return;
    }

    if (btnText) btnText.style.display = 'none';
    if (btnLoader) btnLoader.style.display = 'inline';
    if (submitBtn) submitBtn.disabled = true;

    try {
        // 1. Guardar en base de datos
        const response = await fetch(API_URL + '/appointments/save-professional', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_email: userEmail,
                title: title,
                date: date,
                time: time,
                description: description
            })
        });

        const result = await response.json();

        if (result.success) {
            // 2. Enviar email de notificacion al usuario (mismo template que cita normal)
            if (typeof sendAppointmentEmail === 'function' && typeof isEmailJSConfigured === 'function' && isEmailJSConfigured()) {
                try {
                    const selectedOption = document.getElementById('select_user').selectedOptions[0];
                    const userName = selectedOption ? selectedOption.textContent.split(' (')[0] : '';

                    await sendAppointmentEmail({
                        userName: userName,
                        userEmail: userEmail,
                        date: date,
                        time: time,
                        type: title,
                        doctorName: PROFESSIONAL_USER.nombre,
                        notes: description || 'Cita agendada por ' + PROFESSIONAL_USER.nombre
                    });
                } catch (emailErr) {
                    console.error('Error enviando email:', emailErr);
                }
            }

            // 3. Sincronizar con Google Calendar si esta marcado
            if (syncGoogle && typeof createGoogleCalendarEventWithAttendee === 'function') {
                try {
                    const gcResult = await syncProfessionalToGoogleCalendar({
                        title: title,
                        date: date,
                        time: time,
                        description: description,
                        attendeeEmail: userEmail
                    });

                    if (gcResult && gcResult.success) {
                        showProToast('Cita creada y sincronizada con Google Calendar', 'success');
                    } else {
                        showProToast('Cita creada (Google Calendar: error de sincronizacion)', 'warning');
                    }
                } catch (gcError) {
                    console.error('Google Calendar error:', gcError);
                    showProToast('Cita creada (Google Calendar no disponible)', 'warning');
                }
            } else {
                showProToast('Cita creada exitosamente', 'success');
            }

            form.reset();
            await loadProfessionalAppointments();
        } else {
            showProToast(result.message || 'Error al crear cita', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showProToast('Error al comunicarse con el servidor', 'error');
    } finally {
        if (btnText) btnText.style.display = 'inline';
        if (btnLoader) btnLoader.style.display = 'none';
        if (submitBtn) submitBtn.disabled = false;
    }
}

/**
 * Sincronizar con Google Calendar incluyendo attendee
 */
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

/**
 * Toast notification (showToast es el alias usado por profesional-ejercicios/recetas/noticias)
 */
function showToast(message, type) {
    showProToast(message, type);
}

function showProToast(message, type) {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }

    const colors = {
        success: '#4CAF50',
        error: '#F44336',
        warning: '#FF9800',
        info: '#2196F3'
    };

    toast.textContent = message;
    toast.style.cssText = `position:fixed;top:20px;right:20px;padding:1rem 1.5rem;border-radius:8px;color:white;background:${colors[type] || colors.info};box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:10000;display:block;opacity:1;transition:opacity 0.3s;`;

    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}
