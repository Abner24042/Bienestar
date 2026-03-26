/**
 * BIENESTAR — Api Service
 * Cliente HTTP centralizado. Disponible globalmente como window.Api
 *
 * Uso:
 *   const { recetas } = await Api.get('/admin/recetas');
 *   await Api.post('/admin/recetas', formData);
 *   await Api.put('/admin/recetas/5', { titulo: 'Nuevo' });
 *   await Api.delete('/admin/recetas/5');
 */
const Api = (() => {
    const base = typeof API_URL !== 'undefined' ? API_URL : '';

    async function request(method, endpoint, data = null) {
        const options = { method };
        const isFormData = data instanceof FormData;

        if (data !== null) {
            if (isFormData) {
                options.body = data;
                // No poner Content-Type: el browser lo setea con boundary automático
            } else {
                options.headers = { 'Content-Type': 'application/json' };
                options.body = JSON.stringify(data);
            }
        }

        let res;
        try {
            res = await fetch(base + endpoint, options);
        } catch (networkErr) {
            throw new ApiError('Sin conexión. Verifica tu red.', 0);
        }

        // Intentar parsear JSON siempre
        let json;
        try {
            json = await res.json();
        } catch {
            throw new ApiError(`Error del servidor (${res.status})`, res.status);
        }

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

/** Error tipado para distinguir errores de API vs errores JS */
class ApiError extends Error {
    constructor(message, status = 0, data = null) {
        super(message);
        this.name   = 'ApiError';
        this.status = status;
        this.data   = data;
    }
}
