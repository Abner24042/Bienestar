
let currentDate = new Date();
let appointments = [];

document.addEventListener('DOMContentLoaded', function () {
    initCalendar();
    loadAppointments();
});

function initCalendar() {
    const btnPrevMonth = document.getElementById('btnPrevMonth');
    const btnNextMonth = document.getElementById('btnNextMonth');

    if (btnPrevMonth) {
        btnPrevMonth.addEventListener('click', function () {
            currentDate.setDate(1);
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });
    }

    if (btnNextMonth) {
        btnNextMonth.addEventListener('click', function () {
            currentDate.setDate(1);
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });
    }

    const btnSyncGoogle = document.getElementById('btnSyncGoogle');
    if (btnSyncGoogle) {
        btnSyncGoogle.addEventListener('click', handleSyncGoogle);
    }

    renderCalendar();
}

function isDarkMode() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function renderCalendar() {
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const dark = isDarkMode();

    document.getElementById('currentMonthYear').textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const calendarDays = document.getElementById('calendarDays');
    calendarDays.innerHTML = '';
    calendarDays.style.cssText = `display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: ${dark ? '#333' : '#ddd'};`;

    for (let i = 0; i < firstDay; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.style.cssText = `background: ${dark ? '#1a1a1a' : '#f9f9f9'}; min-height: 100px; padding: 0.5rem;`;
        calendarDays.appendChild(emptyDay);
    }

    const bgColor = dark ? '#1e1e1e' : 'white';
    const hoverColor = dark ? '#2a2a2a' : '#f5f5f5';
    const textColor = dark ? '#e8e8e8' : '#333';

    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const dayDiv = document.createElement('div');
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const isToday = today.getDate() === day &&
            today.getMonth() === month &&
            today.getFullYear() === year;

        dayDiv.style.cssText = `
            background: ${bgColor};
            min-height: 100px;
            padding: 0.5rem;
            cursor: pointer;
            transition: background 0.2s;
            ${isToday ? 'border: 2px solid #ff6b35;' : ''}
        `;

        dayDiv.innerHTML = `
            <div style="font-weight: 600; color: ${isToday ? '#ff6b35' : textColor}; margin-bottom: 0.5rem;">
                ${day}
            </div>
            <div class="day-appointments" data-date="${dateStr}"></div>
        `;

        dayDiv.addEventListener('mouseenter', () => { dayDiv.style.background = hoverColor; });
        dayDiv.addEventListener('mouseleave', () => { dayDiv.style.background = bgColor; });

        dayDiv.addEventListener('click', (e) => {
            if (e.target.classList.contains('appointment-item')) return;
            const citasDelDia = appointments.filter(apt => apt.fecha === dateStr);
            showDayAppointments(dateStr, citasDelDia);
        });

        calendarDays.appendChild(dayDiv);
    }

    displayAppointmentsOnCalendar();
}

async function loadAppointments() {
    try {
        const response = await fetch(API_URL + '/appointments');
        const data = await response.json();

        if (data.success) {
            appointments = data.appointments || [];
            displayAppointmentsOnCalendar();
            updateCalendarStats();
        }
    } catch (error) {
        console.error('Error al cargar citas:', error);
    }
}

function displayAppointmentsOnCalendar() {
    document.querySelectorAll('.day-appointments').forEach(container => {
        container.innerHTML = '';
    });

    const dark = isDarkMode();
    appointments.forEach(appointment => {
        const container = document.querySelector(`[data-date="${appointment.fecha}"]`);
        if (container) {
            const appointmentDiv = document.createElement('div');
            appointmentDiv.className = 'appointment-item';
            appointmentDiv.style.cssText = `
                background: ${dark ? '#2a1a10' : '#fff5f0'};
                color: ${dark ? '#e8e8e8' : 'inherit'};
                padding: 0.25rem;
                border-radius: 4px;
                font-size: 0.75rem;
                margin-bottom: 0.25rem;
                border-left: 3px solid #ff6b35;
                cursor: pointer;
            `;
            appointmentDiv.textContent = `${appointment.hora} - ${appointment.titulo}`;
            appointmentDiv.title = appointment.titulo;

            appointmentDiv.addEventListener('click', (e) => {
                e.stopPropagation();
                const citasDelDia = appointments.filter(apt => apt.fecha === appointment.fecha);
                showDayAppointments(appointment.fecha, citasDelDia);
            });

            container.appendChild(appointmentDiv);
        }
    });
}

function showDayAppointments(date, citasDelDia) {
    const [year, month, day] = date.split('-');
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const fechaFormateada = `${parseInt(day)} de ${monthNames[parseInt(month) - 1]} de ${year}`;

    let contenido;

    const dark = isDarkMode();
    if (citasDelDia.length === 0) {
        contenido = `
            <div style="text-align: center; padding: 20px; color: ${dark ? '#888' : '#999'};">
                <p style="font-size: 1.1rem;">No hay citas registradas</p>
            </div>
        `;
    } else {
        contenido = citasDelDia.map(cita => `
            <div style="background: ${dark ? '#2a2a2a' : '#f5f5f5'}; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid #ff6b35;">
                <div style="font-weight: 600; color: ${dark ? '#e8e8e8' : '#333'};">
                    🕐 ${cita.hora} - ${cita.titulo}
                </div>
            </div>
        `).join('');
    }

    const modalContent = `
        <div style="text-align: left;">
            <h3 style="color: #ff6b35; margin-bottom: 15px;">📅 ${fechaFormateada}</h3>
            ${contenido}
        </div>
    `;

    let modal = document.getElementById('modalDayAppointments');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modalDayAppointments';
        modal.className = 'modal';
        modal.style.cssText = 'display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;';
        modal.innerHTML = `
            <div class="modal-content" style="background: white; border-radius: 12px; padding: 30px; max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">
                <div id="modalDayContent"></div>
                <button onclick="closeModal('modalDayAppointments')" class="btn btn-secondary" style="margin-top: 20px;">Cerrar</button>
            </div>
        `;
        document.body.appendChild(modal);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal('modalDayAppointments');
        });
    }

    document.getElementById('modalDayContent').innerHTML = modalContent;
    openModal('modalDayAppointments');
}

function updateCalendarStats() {
    const totalEvents = document.getElementById('totalEvents');
    const upcomingEvents = document.getElementById('upcomingEvents');

    if (totalEvents) totalEvents.textContent = appointments.length;

    if (upcomingEvents) {
        const today = new Date().toISOString().split('T')[0];
        const upcoming = appointments.filter(apt => apt.fecha >= today).length;
        upcomingEvents.textContent = upcoming;
    }
}

function handleSyncGoogle() {
    if (typeof syncAppointmentToGoogleCalendar === 'undefined') {
        showToast('Google Calendar no esta disponible', 'error');
        return;
    }

    if (appointments.length === 0) {
        showToast('No hay citas para sincronizar', 'info');
        return;
    }

    showToast('Autorizando con Google...', 'info');

    const firstAppointment = appointments[0];
    syncAppointmentToGoogleCalendar({
        title: firstAppointment.titulo,
        date: firstAppointment.fecha,
        time: firstAppointment.hora,
        type: 'Consulta',
        description: firstAppointment.titulo
    }).then(result => {
        if (result.success) {
            showToast('Cita sincronizada con Google Calendar', 'success');
        } else {
            showToast('Error al sincronizar con Google Calendar', 'error');
        }
    }).catch(() => {
        showToast('Error al conectar con Google Calendar', 'error');
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

function showToast(message, type) {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.opacity = '1';
    setTimeout(function () {
        toast.style.opacity = '0';
        setTimeout(function () { toast.style.display = 'none'; }, 300);
    }, 3000);
}

window.addEventListener('click', function (e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});
