// utilidad de cache cliente usando localStorage
// guarda respuestas de API con tiempo de expiracion para no hacer fetch en cada visita
// si el storage esta lleno o deshabilitado simplemente no cachea, no rompe nada

const AppCache = (() => {
    const PREFIX = 'bc_'; // prefijo para no chocar con otras llaves del localStorage

    // regresa { data, saved, exp } si existe y no expiro, null si no hay o ya caduco
    function get(key) {
        try {
            const raw = localStorage.getItem(PREFIX + key);
            if (!raw) return null;
            const item = JSON.parse(raw);
            if (Date.now() > item.exp) {
                localStorage.removeItem(PREFIX + key);
                return null;
            }
            return item;
        } catch {
            return null;
        }
    }

    // guarda data con tiempo de vida en milisegundos
    function set(key, data, ttlMs) {
        try {
            localStorage.setItem(PREFIX + key, JSON.stringify({
                data,
                exp:   Date.now() + ttlMs,
                saved: Date.now()
            }));
        } catch {
            // localStorage lleno o modo privado sin storage - no es critico, solo no cachea
        }
    }

    // borra una entrada especifica para forzar refetch
    function clear(key) {
        try { localStorage.removeItem(PREFIX + key); } catch {}
    }

    // cuantos minutos tiene guardado el cache (para mostrar "actualizado hace X min")
    function ageMinutes(key) {
        try {
            const raw = localStorage.getItem(PREFIX + key);
            if (!raw) return null;
            const item = JSON.parse(raw);
            return Math.floor((Date.now() - item.saved) / 60000);
        } catch { return null; }
    }

    return { get, set, clear, ageMinutes };
})();
