
const GOOGLE_CONFIG = {
    clientId: '386216986813-g64cpooclgvge23jip5rh8g0abg268cs.apps.googleusercontent.com',
    apiKey: 'AIzaSyBFOX3aOVmHiDpCrF0xq7z_APd3jqiATgQ',
    discoveryDocs: ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"],
    scopes: "https://www.googleapis.com/auth/calendar.events"
};

let gapiInited = false;
let gisInited = false;
let tokenClient;

/**
 * Inicializar Google API
 */
function gapiLoaded() {
    gapi.load('client', initializeGapiClient);
}

async function initializeGapiClient() {
    await gapi.client.init({
        apiKey: GOOGLE_CONFIG.apiKey,
        discoveryDocs: GOOGLE_CONFIG.discoveryDocs,
    });
    gapiInited = true;
    console.log('✅ Google API inicializada');
}

/**
 * Inicializar Google Identity Services
 */
function gisLoaded() {
    tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CONFIG.clientId,
        scope: GOOGLE_CONFIG.scopes,
        callback: '', // Se define en tiempo de ejecución
    });
    gisInited = true;
    console.log('✅ Google Identity Services inicializado');
}

/**
 * Obtener token de autorización
 */
function handleAuthClick(callback) {
    tokenClient.callback = async (resp) => {
        if (resp.error !== undefined) {
            throw (resp);
        }
        if (callback) callback();
    };

    if (gapi.client.getToken() === null) {
        // Solicitar token nuevo
        tokenClient.requestAccessToken({ prompt: 'consent' });
    } else {
        // Solicitar nuevo token (revocar el anterior)
        tokenClient.requestAccessToken({ prompt: '' });
    }
}

/**
 * Revocar token
 */
function handleSignoutClick() {
    const token = gapi.client.getToken();
    if (token !== null) {
        google.accounts.oauth2.revoke(token.access_token);
        gapi.client.setToken('');
    }
}

/**
 * Crear evento en Google Calendar
 * @param {Object} appointmentData - Datos de la cita
 */
async function createGoogleCalendarEvent(appointmentData) {
    const { title, date, time, type, description } = appointmentData;

    // Construir fecha y hora de inicio (formato: 2026-01-30T14:30:00)
    const startDateTime = `${date}T${time}:00`;

    // Calcular hora de fin (1 hora después)
    const start = new Date(`${date}T${time}`);
    const end = new Date(start.getTime() + 60 * 60 * 1000); // +1 hora en milisegundos
    const endDateTime = end.toISOString().slice(0, 19);

    const event = {
        'summary': title,
        'location': 'BIENIESTAR - IEST',
        'description': `Tipo: ${type}\n\n${description || 'Sin descripción'}`,
        'start': {
            'dateTime': startDateTime,
            'timeZone': 'America/Mexico_City'
        },
        'end': {
            'dateTime': endDateTime,
            'timeZone': 'America/Mexico_City'
        },
        'reminders': {
            'useDefault': false,
            'overrides': [
                { 'method': 'email', 'minutes': 24 * 60 }, // 1 día antes
                { 'method': 'popup', 'minutes': 60 }        // 1 hora antes
            ]
        }
    };

    console.log('📤 Enviando evento a Google Calendar:', event);

    try {
        const request = await gapi.client.calendar.events.insert({
            'calendarId': 'primary',
            'resource': event
        });

        console.log('✅ Evento creado en Google Calendar:', request.result);
        return {
            success: true,
            eventId: request.result.id,
            eventLink: request.result.htmlLink
        };
    } catch (error) {
        console.error('❌ Error al crear evento en Google Calendar:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

/**
 * Crear evento en Google Calendar con invitado (attendee)
 * Usado por profesionales para enviar invitación al usuario
 * @param {Object} data - {title, date, time, description, attendeeEmail}
 */
async function createGoogleCalendarEventWithAttendee(data) {
    const { title, date, time, description, attendeeEmail } = data;

    const startDateTime = `${date}T${time}:00`;
    const start = new Date(`${date}T${time}`);
    const end = new Date(start.getTime() + 60 * 60 * 1000);
    const endDateTime = end.toISOString().slice(0, 19);

    const event = {
        'summary': title,
        'location': 'BIENIESTAR - IEST',
        'description': description || 'Cita agendada por profesional',
        'start': {
            'dateTime': startDateTime,
            'timeZone': 'America/Mexico_City'
        },
        'end': {
            'dateTime': endDateTime,
            'timeZone': 'America/Mexico_City'
        },
        'attendees': [
            { 'email': attendeeEmail }
        ],
        'reminders': {
            'useDefault': false,
            'overrides': [
                { 'method': 'email', 'minutes': 24 * 60 },
                { 'method': 'popup', 'minutes': 60 }
            ]
        }
    };

    console.log('📤 Enviando evento con invitado a Google Calendar:', event);

    try {
        const request = await gapi.client.calendar.events.insert({
            'calendarId': 'primary',
            'resource': event,
            'sendUpdates': 'all'
        });

        console.log('✅ Evento con invitación creado:', request.result);
        return {
            success: true,
            eventId: request.result.id,
            eventLink: request.result.htmlLink
        };
    } catch (error) {
        console.error('❌ Error al crear evento con invitado:', error);
        return {
            success: false,
            error: error.message
        };
    }
}

/**
 * Sincronizar cita con Google Calendar
 * (Autoriza y luego crea el evento)
 */
function syncAppointmentToGoogleCalendar(appointmentData) {
    return new Promise((resolve, reject) => {
        if (!gapiInited || !gisInited) {
            reject(new Error('Google Calendar no está inicializado'));
            return;
        }

        handleAuthClick(async () => {
            try {
                const result = await createGoogleCalendarEvent(appointmentData);
                resolve(result);
            } catch (error) {
                reject(error);
            }
        });
    });
}

/**
 * Verificar si el usuario está autorizado
 */
function isGoogleCalendarAuthorized() {
    return gapi.client.getToken() !== null;
}

// Cargar Google APIs
(function () {
    // Crear scripts para cargar Google APIs
    const gapiScript = document.createElement('script');
    gapiScript.src = 'https://apis.google.com/js/api.js';
    gapiScript.onload = gapiLoaded;
    document.head.appendChild(gapiScript);

    const gisScript = document.createElement('script');
    gisScript.src = 'https://accounts.google.com/gsi/client';
    gisScript.onload = gisLoaded;
    document.head.appendChild(gisScript);
})();
