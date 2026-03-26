
let currentDate = new Date();
let allAppointments = [];

document.addEventListener('DOMContentLoaded', function () {
    initCalendar();
    loadUserAppointments();

    document.getElementById('prevMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    });

    document.getElementById('nextMonth').addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    });
});

function initCalendar() {
    renderCalendar();
}

function renderCalendar() {
    const calendar = document.getElementById('calendar');
    const monthYear = document.getElementById('currentMonthYear');

    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();

    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    monthYear.textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const daysInPrevMonth = new Date(year, month, 0).getDate();

    let html = '<div class="calendar-grid">';

    const dayHeaders = ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'];
    dayHeaders.forEach(day => {
        html += `<div class="calendar-day-header">${day}</div>`;
    });

    for (let i = firstDay - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        html += `<div class="calendar-day other-month"><span class="calendar-day-number">${day}</span></div>`;
    }

    const today = new Date();
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const dateStr = formatDateToISO(date);
        const isToday = date.toDateString() === today.toDateString();
        const appointmentsOnDay = getAppointmentsForDate(dateStr);
        const hasAppointment = appointmentsOnDay.length > 0;

        let classes = 'calendar-day';
        if (isToday) classes += ' today';
        if (hasAppointment) classes += ' has-appointment';

        let tooltip = '';
        if (hasAppointment) {
            const titles = appointmentsOnDay.map(apt =>
                `${apt.hora.substring(0, 5)} - ${apt.titulo}`
            ).join('<br>');
            tooltip = `<div class="calendar-tooltip">${titles}</div>`;
        }

        html += `<div class="${classes}" data-date="${dateStr}">
                    <span class="calendar-day-number">${day}</span>
                    ${hasAppointment ? '<span class="calendar-day-indicator"></span>' : ''}
                    ${tooltip}
                 </div>`;
    }

    const totalCells = firstDay + daysInMonth;
    const remainingCells = totalCells % 7 === 0 ? 0 : 7 - (totalCells % 7);
    for (let day = 1; day <= remainingCells; day++) {
        html += `<div class="calendar-day other-month"><span class="calendar-day-number">${day}</span></div>`;
    }

    html += '</div>';
    calendar.innerHTML = html;

    document.querySelectorAll('.calendar-day:not(.other-month)').forEach(dayEl => {
        dayEl.style.cursor = 'pointer';
        dayEl.addEventListener('click', function () {
            // Quitar seleccion anterior
            document.querySelectorAll('.calendar-day.selected').forEach(el => el.classList.remove('selected'));
            this.classList.add('selected');
            showAppointmentsForDate(this.dataset.date);
        });
    });
}

function getAppointmentsForDate(dateStr) {
    return allAppointments.filter(apt => apt.fecha === dateStr);
}

function showAppointmentsForDate(dateStr) {
    const detail = document.getElementById('dayDetail');
    const title = document.getElementById('dayDetailTitle');
    const content = document.getElementById('dayDetailContent');
    const formattedDate = formatDate(dateStr);
    const appointments = getAppointmentsForDate(dateStr);

    detail.style.display = 'block';
    title.textContent = formattedDate;

    if (appointments.length === 0) {
        content.innerHTML = '<p class="no-appointments">No hay citas registradas</p>';
        return;
    }

    content.innerHTML = appointments.map(apt => {
        const hora = apt.hora.substring(0, 5);
        return `<div class="appointment-item">
            <div class="apt-icon">📋</div>
            <div class="apt-details">
                <h4>${apt.titulo}</h4>
                <p>⏰ ${hora}</p>
            </div>
        </div>`;
    }).join('');
}

function formatDateToISO(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function loadUserAppointments() {
    try {
        const response = await fetch(API_URL + '/appointments');
        const data = await response.json();

        if (data.success) {
            allAppointments = data.appointments || [];
        } else {
            allAppointments = [];
        }

        displayAppointments(allAppointments);
        renderCalendar();
    } catch (error) {
        console.error('Error:', error);
        allAppointments = [];
    }
}

function displayAppointments(appointments) {
    const container = document.getElementById('appointmentsList');

    if (!appointments || appointments.length === 0) {
        container.innerHTML = '<p class="no-appointments">No tienes citas programadas</p>';
        return;
    }

    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

    const isAdmin = typeof CURRENT_USER !== 'undefined' && CURRENT_USER.rol === 'Administrador';

    container.innerHTML = appointments.map(apt => {
        const parts = apt.fecha.split('-');
        const fechaStr = `${parseInt(parts[2])} ${meses[parseInt(parts[1]) - 1]} ${parts[0]}`;
        const hora = apt.hora.substring(0, 5);

        const cancelBtn = isAdmin
            ? `<button class="btn-cancel" onclick="cancelAppointment(${apt.id})">Cancelar</button>`
            : '';

        return `<div class="appointment-item">
            <div class="apt-icon">📋</div>
            <div class="apt-details">
                <h4>${apt.titulo}</h4>
                <p>📅 ${fechaStr} - ⏰ ${hora}</p>
            </div>
            ${cancelBtn}
        </div>`;
    }).join('');
}

async function cancelAppointment(id) {
    if (!confirm('Estas seguro de que deseas cancelar esta cita?')) {
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
            await loadUserAppointments();
        } else {
            alert('Error al cancelar la cita');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al cancelar la cita');
    }
}

function formatDate(dateString) {
    const date = new Date(dateString + 'T00:00:00');
    return date.toLocaleDateString('es-MX', {
        day: '2-digit',
        month: 'long',
        year: 'numeric'
    });
}
