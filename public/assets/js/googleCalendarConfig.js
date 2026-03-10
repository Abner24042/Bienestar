/**
 * Configuración de Google Calendar API
 */

// IMPORTANTE: Reemplaza estos valores con los tuyos
const GOOGLE_CALENDAR_CONFIG = {
    clientId: '<?php echo GOOGLE_CALENDAR_CLIENT_ID; ?>',
    apiKey: '<?php echo GOOGLE_CALENDAR_API_KEY; ?>',
    discoveryDocs: ['https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest'],
    scopes: 'https://www.googleapis.com/auth/calendar.events'
};

// Variables globales
let gapiInited = false;
let gisInited = false;
let tokenClient;
let accessToken = null;

/**
 * Inicializar Google API
 */
function gapiLoaded() {
    gapi.load('client', initializeGapiClient);
}

/**
 * Inicializar cliente GAPI
 */
async function initializeGapiClient() {
    try {
        await gapi.client.init({
            apiKey: GOOGLE_CALENDAR_CONFIG.apiKey,
            discoveryDocs: GOOGLE_CALENDAR_CONFIG.discoveryDocs,
        });
        gapiInited = true;
        console.log('✅ Google API Client inicializado');
    } catch (error) {
        console.error('❌ Error al inicializar GAPI:', error);
    }
}

/**
 * Inicializar Google Identity Services
 */
function gisLoaded() {
    tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: GOOGLE_CALENDAR_CONFIG.clientId,
        scope: GOOGLE_CALENDAR_CONFIG.scopes,
        callback: '', // Se define en runtime
    });
    gisInited = true;
    console.log('✅ Google Identity Services inicializado');
}

/**
 * Conectar con Google Calendar
 */
function connectGoogleCalendar() {
    if (!gapiInited || !gisInited) {
        alert('Google Calendar API aún no está listo. Intenta de nuevo en unos segundos.');
        return;
    }
    
    tokenClient.callback = async (response) => {
        if (response.error !== undefined) {
            console.error('Error de autenticación:', response);
            showNotification('Error al conectar con Google Calendar', 'error');
            return;
        }
        
        accessToken = response.access_token;
        console.log('✅ Token de acceso obtenido');
        showNotification('¡Conectado con Google Calendar!', 'success');
        
        // Actualizar UI
        document.getElementById('btnConnectCalendar').style.display = 'none';
        document.getElementById('calendarConnected').style.display = 'block';
    };
    
    // Solicitar token
    if (gapi.client.getToken() === null) {
        tokenClient.requestAccessToken({prompt: 'consent'});
    } else {
        tokenClient.requestAccessToken({prompt: ''});
    }
}

/**
 * Desconectar Google Calendar
 */
function disconnectGoogleCalendar() {
    const token = gapi.client.getToken();
    if (token !== null) {
        google.accounts.oauth2.revoke(token.access_token);
        gapi.client.setToken('');
        accessToken = null;
        
        // Actualizar UI
        document.getElementById('btnConnectCalendar').style.display = 'block';
        document.getElementById('calendarConnected').style.display = 'none';
        
        showNotification('Desconectado de Google Calendar', 'info');
    }
}

/**
 * Crear evento en Google Calendar
 */
async function createGoogleCalendarEvent(eventData) {
    if (!accessToken) {
        console.log('No hay token de acceso');
        return { success: false, error: 'No conectado' };
    }
    
    // Construir fechas en formato ISO
    const startDateTime = `${eventData.fecha}T${eventData.hora}:00`;
    const endDateTime = `${eventData.fecha}T${addOneHour(eventData.hora)}:00`;
    
    const event = {
        'summary': `BIENIESTAR - ${eventData.tipo}`,
        'location': 'IEST Anáhuac, Tampico',
        'description': `Cita con ${eventData.doctor_nombre}\n\nNotas: ${eventData.notas || 'Sin notas'}`,
        'start': {
            'dateTime': startDateTime,
            'timeZone': 'America/Monterrey'
        },
        'end': {
            'dateTime': endDateTime,
            'timeZone': 'America/Monterrey'
        },
        'reminders': {
            'useDefault': false,
            'overrides': [
                {'method': 'email', 'minutes': 24 * 60}, // 1 día antes
                {'method': 'popup', 'minutes': 60}       // 1 hora antes
            ]
        },
        'colorId': '4' // Rosa/Rojo para citas médicas
    };
    
    try {
        const response = await gapi.client.calendar.events.insert({
            'calendarId': 'primary',
            'resource': event
        });
        
        console.log('✅ Evento creado en Google Calendar:', response.result);
        return { 
            success: true, 
            eventId: response.result.id,
            htmlLink: response.result.htmlLink
        };
    } catch (error) {
        console.error('❌ Error al crear evento:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Eliminar evento de Google Calendar
 */
async function deleteGoogleCalendarEvent(eventId) {
    if (!accessToken || !eventId) {
        return { success: false };
    }
    
    try {
        await gapi.client.calendar.events.delete({
            'calendarId': 'primary',
            'eventId': eventId
        });
        
        console.log('✅ Evento eliminado de Google Calendar');
        return { success: true };
    } catch (error) {
        console.error('❌ Error al eliminar evento:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Listar próximos eventos
 */
async function listUpcomingEvents(maxResults = 10) {
    if (!accessToken) {
        return [];
    }
    
    try {
        const response = await gapi.client.calendar.events.list({
            'calendarId': 'primary',
            'timeMin': (new Date()).toISOString(),
            'showDeleted': false,
            'singleEvents': true,
            'maxResults': maxResults,
            'orderBy': 'startTime'
        });
        
        return response.result.items || [];
    } catch (error) {
        console.error('Error al listar eventos:', error);
        return [];
    }
}

/**
 * Agregar una hora a la hora de inicio
 */
function addOneHour(timeString) {
    const [hours, minutes] = timeString.split(':');
    let newHours = parseInt(hours) + 1;
    
    // Formato 24h
    if (newHours > 23) newHours = 23;
    
    return `${newHours.toString().padStart(2, '0')}:${minutes}`;
}

/**
 * Verificar si está conectado
 */
function isConnectedToGoogleCalendar() {
    return accessToken !== null;
}

// Inicializar cuando se carguen los scripts
console.log('Google Calendar Config cargado');