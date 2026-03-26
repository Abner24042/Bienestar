// cliente HTTP centralizado para todos los fetch del proyecto
// en vez de repetir el mismo fetch con headers y manejo de errores en cada archivo,
// lo ponemos aqui una vez y desde todos lados hacemos Api.get('/recetas') etc.
// disponible globalmente como window.Api porque se carga en el header

const Api = (() => {
    // toma el API_URL que inyecta el header de PHP, o string vacio si no esta definido
    const base = typeof API_URL !== 'undefined' ? API_URL : '';

    async function request(method, endpoint, data = null) {
        const options = { method };
        const isFormData = data instanceof FormData;

        if (data !== null) {
            if (isFormData) {
                options.body = data;
                // con FormData NO se pone Content-Type manualmente
                // el browser lo setea solo y agrega el boundary que necesita multipart
                // si lo pones manualmente rompe el upload de imagenes - aprendi eso a las malas
            } else {
                options.headers = { 'Content-Type': 'application/json' };
                options.body = JSON.stringify(data);
            }
        }

        let res;
        try {
            res = await fetch(base + endpoint, options);
        } catch (networkErr) {
            // esto pasa cuando no hay internet o el servidor no responde
            throw new ApiError('Sin conexión. Verifica tu red.', 0);
        }

        // intentamos parsear JSON siempre, si falla es error del servidor
        let json;
        try {
            json = await res.json();
        } catch {
            throw new ApiError(`Error del servidor (${res.status})`, res.status);
        }

        // todos los endpoints del backend regresan {success: true/false}
        // si success es false lanzamos un error con el mensaje que vino del servidor
        if (!json.success) {
            throw new ApiError(json.message || 'Error desconocido', res.status, json);
        }

        return json;
    }

    return {
        get:    (endpoint)        => request('GET',    endpoint),
        post:   (endpoint, data)  => request('POST',   endpoint, data),
        put:    (endpoint, data)  => request('PUT',    endpoint, data),
        delete: (endpoint)        => request('DELETE', endpoint),
    };
})();

// clase de error personalizada para distinguir errores de API de errores de JS normales
// con esto en el catch podemos hacer: if (err instanceof ApiError) y saber que fue del servidor
class ApiError extends Error {
    constructor(message, status = 0, data = null) {
        super(message);
        this.name   = 'ApiError';
        this.status = status;
        this.data   = data;
    }
}
