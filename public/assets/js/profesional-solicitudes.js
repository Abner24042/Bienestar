/* ═══════════════════════════════════════════════════════════════
   SOLICITUDES DE CITA — Panel Profesional
   Polling cada 5 s · sonido si hay nuevas · accept / deny / reassign
   ═══════════════════════════════════════════════════════════════ */

let _solCount    = -1;   // -1 = primera carga (sin sonido)
let _solInterval = null;

document.addEventListener('DOMContentLoaded', function () {
    cargarSolicitudes();
    _solInterval = setInterval(pollSolicitudes, 5000);
});

/* ── Sonido de notificación (Web Audio API, sin archivo externo) ── */
function playNotifSol() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.type = 'sine';
        osc.frequency.setValueAtTime(880, ctx.currentTime);
        osc.frequency.setValueAtTime(660, ctx.currentTime + 0.15);
        gain.gain.setValueAtTime(0.25, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
        osc.start(ctx.currentTime);
        osc.stop(ctx.currentTime + 0.5);
    } catch (e) { /* contexto suspendido o no soportado */ }
}

/* ── Polling: solo cuenta, dispara sonido si aumentó ── */
async function pollSolicitudes() {
    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/count');
        const data = await res.json();
        const cnt  = data.count || 0;

        actualizarBadgeSol(cnt);

        if (_solCount >= 0 && cnt > _solCount) {
            playNotifSol();
            cargarSolicitudes();   // refrescar lista
        }
        _solCount = cnt;
    } catch (e) {}
}

/* ── Actualiza el badge del nav y del header de sección ── */
function actualizarBadgeSol(cnt) {
    // Nav sidebar
    const navBadge = document.getElementById('solNavBadge');
    if (navBadge) {
        navBadge.textContent = cnt > 0 ? cnt : '';
        navBadge.style.display = cnt > 0 ? 'inline' : 'none';
    }
    // Header de la sección
    const hdrBadge = document.getElementById('solBadgeHeader');
    if (hdrBadge) {
        hdrBadge.textContent = cnt > 0 ? cnt + ' nueva' + (cnt > 1 ? 's' : '') : '';
        hdrBadge.style.display = cnt > 0 ? '' : 'none';
    }
}

/* ── Carga y renderiza la lista completa ── */
async function cargarSolicitudes() {
    try {
        const res  = await fetch(API_URL + '/pro/solicitudes');
        const data = await res.json();
        if (!data.success) return;

        const section = document.getElementById('seccionSolicitudes');
        const grid    = document.getElementById('solicitudesGrid');

        const sols = data.solicitudes || [];

        // Cachear datos para pre-rellenar modal de aceptar
        sols.forEach(s => { _solDataCache[s.id] = s; });

        // También actualiza el count en memoria
        actualizarBadgeSol(sols.length);
        if (_solCount === -1) _solCount = sols.length;

        if (!sols.length) {
            section.style.display = 'none';
            return;
        }

        section.style.display = '';
        grid.innerHTML = sols.map(s => renderSolicitud(s)).join('');
    } catch (e) {}
}

function renderSolicitud(s) {
    const fecha = s.fecha
        ? new Date(s.fecha + 'T00:00:00').toLocaleDateString('es-MX',
            { day: '2-digit', month: 'short', year: 'numeric' })
        : '—';

    return `<div id="sol-${s.id}" style="
        padding:14px 18px;border-radius:12px;
        border:1.5px solid var(--color-border,#e8e8e8);
        background:var(--color-bg-primary,#fff);
        display:flex;align-items:center;justify-content:space-between;
        flex-wrap:wrap;gap:12px;">
        <div style="flex:1;min-width:0;">
            <p style="margin:0;font-weight:700;font-size:0.95rem;">${escS(s.usuario_nombre || s.correo)}</p>
            <p style="margin:4px 0 0;font-size:0.82rem;color:var(--color-text-secondary);">
                ${escS(s.sol_tipo)} · ${fecha}
            </p>
            ${s.descripcion ? `<p style="margin:4px 0 0;font-size:0.8rem;color:var(--color-text-light);">${escS(s.descripcion)}</p>` : ''}
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0;">
            <button onclick="aceptarSolicitud(${s.id})"
                style="padding:7px 16px;background:#16a34a;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.85rem;">
                ✓ Aceptar
            </button>
            <button onclick="abrirModalDenegar(${s.id}, '${escS(s.sol_tipo)}')"
                style="padding:7px 16px;background:#dc2626;color:white;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.85rem;">
                ✕ Denegar
            </button>
        </div>
    </div>`;
}

/* ── Aceptar: abrir modal de confirmación ── */
let _solDataCache = {};   // guarda datos de solicitudes para pre-rellenar modal

function aceptarSolicitud(id) {
    const sol = _solDataCache[id] || {};
    document.getElementById('aceptarSolId').value = id;
    document.getElementById('aceptarTitulo').value = 'Consulta de ' + (sol.sol_tipo || '');
    document.getElementById('aceptarFecha').value  = sol.fecha || '';
    document.getElementById('aceptarHora').value   = '';
    document.getElementById('aceptarNotas').value  = '';
    document.getElementById('aceptarMsg').style.display = 'none';
    document.getElementById('modalAceptarSol').style.display = 'flex';
}

function cerrarModalAceptar() {
    document.getElementById('modalAceptarSol').style.display = 'none';
}

async function confirmarAceptar() {
    const id     = +document.getElementById('aceptarSolId').value;
    const titulo = document.getElementById('aceptarTitulo').value.trim();
    const fecha  = document.getElementById('aceptarFecha').value;
    const hora   = document.getElementById('aceptarHora').value;
    const notas  = document.getElementById('aceptarNotas').value.trim();
    const msg    = document.getElementById('aceptarMsg');

    if (!titulo || !fecha || !hora) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Completa el título, fecha y hora.';
        return;
    }

    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/accion', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ id, accion: 'aceptar', titulo, fecha, hora, notas: notas || null }),
        });
        const data = await res.json();
        if (data.success) {
            cerrarModalAceptar();
            document.getElementById('sol-' + id)?.remove();
            _solCount = Math.max(0, _solCount - 1);
            actualizarBadgeSol(_solCount);
            if (!document.querySelector('[id^="sol-"]')) {
                document.getElementById('seccionSolicitudes').style.display = 'none';
            }
            showToastSol('Cita confirmada ✓', 'success');
        } else {
            msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
            msg.textContent   = data.message || 'Error al aceptar';
        }
    } catch (e) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Error de conexión';
    }
}

/* ── Modal denegar ── */
async function abrirModalDenegar(id, tipo) {
    document.getElementById('denegarSolId').value = id;
    document.getElementById('denegarMotivo').value = '';
    document.getElementById('denegarMsg').style.display = 'none';

    // Cargar colegas del mismo tipo para reasignar
    const sel = document.getElementById('denegarReasignar');
    sel.innerHTML = '<option value="">Cargando...</option>';
    try {
        const res  = await fetch(API_URL + '/especialistas?tipo=' + encodeURIComponent(tipo));
        const data = await res.json();
        const proProp = typeof CURRENT_USER_EMAIL !== 'undefined' ? CURRENT_USER_EMAIL : '';
        sel.innerHTML = '<option value="">— Sin reasignar —</option>' +
            (data.especialistas || [])
                .filter(e => e.correo !== proProp)
                .map(e => `<option value="${escS(e.correo)}">${escS(e.nombre)}${e.area ? ' — ' + e.area : ''}</option>`)
                .join('');
    } catch (e) {
        sel.innerHTML = '<option value="">Error al cargar</option>';
    }

    document.getElementById('modalDenegarSol').style.display = 'flex';
}

function cerrarModalDenegar() {
    document.getElementById('modalDenegarSol').style.display = 'none';
}

async function confirmarDenegar() {
    const id     = document.getElementById('denegarSolId').value;
    const motivo = document.getElementById('denegarMotivo').value.trim();
    const rea    = document.getElementById('denegarReasignar').value;
    const msg    = document.getElementById('denegarMsg');

    if (!motivo) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Escribe el motivo de la denegación.';
        return;
    }

    try {
        const res  = await fetch(API_URL + '/pro/solicitudes/accion', {
            method : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body   : JSON.stringify({ id: +id, accion: 'denegar', motivo, reasignado_a: rea || null }),
        });
        const data = await res.json();
        if (data.success) {
            cerrarModalDenegar();
            document.getElementById('sol-' + id)?.remove();
            _solCount = Math.max(0, _solCount - 1);
            actualizarBadgeSol(_solCount);
            if (!document.querySelector('[id^="sol-"]')) {
                document.getElementById('seccionSolicitudes').style.display = 'none';
            }
            showToastSol(rea ? 'Solicitud denegada y reasignada' : 'Solicitud denegada', 'info');
        } else {
            msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
            msg.textContent   = data.message || 'Error al denegar';
        }
    } catch (e) {
        msg.style.cssText = 'display:block;padding:8px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:8px;font-size:0.85rem;';
        msg.textContent   = 'Error de conexión';
    }
}

/* ── Toast ── */
function showToastSol(msg, type = 'success') {
    const colors = { success: '#16a34a', error: '#dc2626', info: '#7c3aed' };
    const t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:99999;
        padding:12px 20px;border-radius:10px;font-size:0.9rem;font-weight:600;
        color:white;background:${colors[type] || colors.success};
        box-shadow:0 4px 16px rgba(0,0,0,0.2);transition:opacity .3s;`;
    document.body.appendChild(t);
    setTimeout(() => { t.style.opacity = '0'; setTimeout(() => t.remove(), 300); }, 2500);
}

/* ── Escape helper ── */
function escS(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
