/**
 * BIENIESTAR - Gestión de Planes (Panel Profesional)
 */

let planUsuarioActual = null;
let planDataPro = {};

document.addEventListener('DOMContentLoaded', function () {
    cargarUsuariosPlan();
    // Sección exclusiva psicólogo
    if (document.getElementById('proRecomendacionesBody')) {
        cargarRecomendacionesPro();
        document.getElementById('btnNuevaRecPro').addEventListener('click', abrirModalNuevaRecPro);
    }
});

async function cargarUsuariosPlan() {
    try {
        const res = await fetch(API_URL + '/pro/usuarios-list');
        const data = await res.json();
        if (!data.success) throw new Error();
        const sel = document.getElementById('planUsuarioSelect');
        if (!sel) return;
        sel.innerHTML = '<option value="">— Selecciona un usuario —</option>' +
            data.usuarios.map(u => `<option value="${u.id}">${escP(u.nombre)} (${escP(u.correo)})</option>`).join('');
    } catch (e) {
        const sel = document.getElementById('planUsuarioSelect');
        if (sel) sel.innerHTML = '<option value="">Error al cargar usuarios</option>';
    }
}

async function cargarPlanUsuario(userId) {
    if (!userId) {
        document.getElementById('planUsuarioContainer').style.display = 'none';
        return;
    }
    planUsuarioActual = userId;
    document.getElementById('planUsuarioContainer').style.display = 'block';
    setPlanLoading(true);

    try {
        const res = await fetch(API_URL + '/pro/plan/get-usuario?usuario_id=' + userId);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'Error');
        planDataPro = data;
        renderPlanPro(data);
    } catch (e) {
        setPlanLoading(false);
        showToastPlan('Error al cargar el plan del usuario', 'error');
    }
}

function setPlanLoading(on) {
    ['planEjerciciosList', 'planRecetasList', 'planRecomendacionesList'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.innerHTML = on ? '<p style="color:#999;padding:8px 0;">Cargando...</p>' : '';
    });
}

function renderPlanPro(data) {
    // Ejercicios (coach)
    const elE = document.getElementById('planEjerciciosList');
    if (elE) {
        const list = data.plan && data.plan.ejercicios ? data.plan.ejercicios : [];
        elE.innerHTML = list.length
            ? list.map(e => planProItem(escP(e.titulo), e.notas, 'ejercicio', e.asignacion_id)).join('')
            : '<p style="color:#999;padding:8px 0;">Sin ejercicios asignados aún.</p>';

        const sel = document.getElementById('planEjercicioSelect');
        if (sel && data.ejercicios_disponibles) {
            sel.innerHTML = '<option value="">— Elige un ejercicio —</option>' +
                data.ejercicios_disponibles.map(e => `<option value="${e.id}">${escP(e.titulo)}</option>`).join('');
        }
    }

    // Recetas (nutriologo)
    const elR = document.getElementById('planRecetasList');
    if (elR) {
        const list = data.plan && data.plan.recetas ? data.plan.recetas : [];
        elR.innerHTML = list.length
            ? list.map(r => planProItem(escP(r.titulo), r.notas, 'receta', r.asignacion_id)).join('')
            : '<p style="color:#999;padding:8px 0;">Sin recetas asignadas aún.</p>';

        const sel = document.getElementById('planRecetaSelect');
        if (sel && data.recetas_disponibles) {
            sel.innerHTML = '<option value="">— Elige una receta —</option>' +
                data.recetas_disponibles.map(r => `<option value="${r.id}">${escP(r.titulo)}</option>`).join('');
        }
    }

    // Recomendaciones (todos los roles)
    const elRec = document.getElementById('planRecomendacionesList');
    if (elRec) {
        const list = data.plan && data.plan.recomendaciones ? data.plan.recomendaciones : [];
        elRec.innerHTML = list.length
            ? list.map(r => planProItem(escP(r.titulo), r.contenido, 'recomendacion', r.id, escP(r.tipo))).join('')
            : '<p style="color:#999;padding:8px 0;">Sin recomendaciones aún.</p>';
    }
}

function planProItem(titulo, notas, tipo, id, badge = '') {
    return `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;border:1px solid rgba(255,107,53,0.2);border-radius:8px;margin-bottom:8px;gap:12px;background:rgba(255,107,53,0.04);">
        <div style="flex:1;min-width:0;">
            <div style="font-weight:600;font-size:0.9rem;color:var(--color-text-primary);">${titulo}${badge ? ` <span style="font-size:0.72rem;background:#ff6b3520;color:#ff6b35;padding:1px 7px;border-radius:10px;font-weight:500;">${badge}</span>` : ''}</div>
            ${notas ? `<div style="font-size:0.8rem;color:var(--color-text-secondary);margin-top:2px;">${escP(notas)}</div>` : ''}
        </div>
        <button onclick="quitarDelPlan('${tipo}', ${id})" style="padding:5px 12px;border:1px solid #f44336;background:transparent;color:#f44336;border-radius:6px;cursor:pointer;font-size:0.8rem;white-space:nowrap;flex-shrink:0;">Quitar</button>
    </div>`;
}

function abrirModalAsignarEjercicio() {
    if (!planUsuarioActual) { showToastPlan('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('planEjercicioNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarEjercicio').style.display = 'flex';
}

function abrirModalAsignarReceta() {
    if (!planUsuarioActual) { showToastPlan('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('planRecetaNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarReceta').style.display = 'flex';
}

function abrirModalRecomendacion() {
    if (!planUsuarioActual) { showToastPlan('Selecciona un usuario primero', 'error'); return; }
    document.getElementById('recTitulo').value = '';
    document.getElementById('recContenido').value = '';
    document.getElementById('recTipo').value = 'general';
    document.getElementById('modalRecomendacion').style.display = 'flex';
}

async function confirmarAsignarEjercicio() {
    const ejercicioId = document.getElementById('planEjercicioSelect').value;
    const notas = document.getElementById('planEjercicioNotas').value.trim();
    if (!ejercicioId) { showToastPlan('Selecciona un ejercicio', 'error'); return; }

    try {
        const res = await fetch(API_URL + '/pro/plan/asignar-ejercicio', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: planUsuarioActual, ejercicio_id: ejercicioId, notas: notas || null })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarEjercicio').style.display = 'none';
        showToastPlan('Ejercicio asignado al plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) {
        showToastPlan(e.message || 'Error al asignar', 'error');
    }
}

async function confirmarAsignarReceta() {
    const recetaId = document.getElementById('planRecetaSelect').value;
    const notas = document.getElementById('planRecetaNotas').value.trim();
    if (!recetaId) { showToastPlan('Selecciona una receta', 'error'); return; }

    try {
        const res = await fetch(API_URL + '/pro/plan/asignar-receta', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: planUsuarioActual, receta_id: recetaId, notas: notas || null })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarReceta').style.display = 'none';
        showToastPlan('Receta asignada al plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) {
        showToastPlan(e.message || 'Error al asignar', 'error');
    }
}

async function confirmarRecomendacion() {
    const titulo = document.getElementById('recTitulo').value.trim();
    const contenido = document.getElementById('recContenido').value.trim();
    const tipo = document.getElementById('recTipo').value;
    if (!titulo) { showToastPlan('El título es requerido', 'error'); return; }

    try {
        const res = await fetch(API_URL + '/pro/plan/recomendar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: planUsuarioActual, titulo, contenido, tipo })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalRecomendacion').style.display = 'none';
        showToastPlan('Recomendación agregada');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) {
        showToastPlan(e.message || 'Error al agregar', 'error');
    }
}

async function quitarDelPlan(tipo, id) {
    if (!confirm('¿Quitar este elemento del plan del usuario?')) return;
    try {
        const res = await fetch(API_URL + '/pro/plan/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo, id })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showToastPlan('Eliminado del plan');
        cargarPlanUsuario(planUsuarioActual);
    } catch (e) {
        showToastPlan(e.message || 'Error al eliminar', 'error');
    }
}

function showToastPlan(msg, type = 'success') {
    if (typeof showToast === 'function') { showToast(msg, type); return; }
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:${type === 'error' ? '#f44336' : '#4caf50'};color:#fff;padding:10px 24px;border-radius:8px;z-index:9999;font-size:0.9rem;box-shadow:0 4px 12px rgba(0,0,0,0.2);`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escP(str) {
    if (str === null || str === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}

// ─── Sección Psicólogo: Mis Recomendaciones ───────────────────────────────

async function cargarRecomendacionesPro() {
    const tbody = document.getElementById('proRecomendacionesBody');
    if (!tbody) return;
    try {
        const res = await fetch(API_URL + '/pro/recomendaciones');
        const data = await res.json();
        if (!data.success) throw new Error();
        if (!data.recomendaciones.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay recomendaciones aún.</td></tr>';
            return;
        }
        const tipoColor = { psicologia: '#9c27b0', ejercicio: '#ff6b35', alimentacion: '#4caf50', general: '#2196f3' };
        tbody.innerHTML = data.recomendaciones.map(r => {
            const color = tipoColor[r.tipo] || tipoColor.general;
            const fecha = r.created_at ? r.created_at.substring(0, 10) : '—';
            return `<tr>
                <td>${escP(r.usuario_nombre)}<br><small style="color:#999;">${escP(r.usuario_correo)}</small></td>
                <td>${escP(r.titulo)}${r.contenido ? `<br><small style="color:#999;">${escP(r.contenido.substring(0, 60))}${r.contenido.length > 60 ? '…' : ''}</small>` : ''}</td>
                <td><span style="color:${color};font-weight:600;font-size:0.82rem;">${escP(r.tipo)}</span></td>
                <td style="color:#999;font-size:0.85rem;">${fecha}</td>
                <td><button onclick="eliminarRecPro(${r.id})" style="padding:4px 12px;border:1px solid #f44336;background:transparent;color:#f44336;border-radius:6px;cursor:pointer;font-size:0.8rem;">Eliminar</button></td>
            </tr>`;
        }).join('');
    } catch (e) {
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar.</td></tr>';
    }
}

async function abrirModalNuevaRecPro() {
    // Poblar usuarios si no están cargados
    const selU = document.getElementById('recProUsuario');
    if (selU && (selU.options.length <= 1 || selU.options[0].value === '')) {
        try {
            const res = await fetch(API_URL + '/pro/usuarios-list');
            const data = await res.json();
            if (data.success) {
                selU.innerHTML = '<option value="">— Selecciona un usuario —</option>' +
                    data.usuarios.map(u => `<option value="${u.id}">${escP(u.nombre)} (${escP(u.correo)})</option>`).join('');
            }
        } catch (e) {}
    }
    document.getElementById('recProTitulo').value = '';
    document.getElementById('recProContenido').value = '';
    document.getElementById('recProTipo').value = 'psicologia';
    document.getElementById('modalNuevaRecPro').style.display = 'flex';
}

async function guardarRecPro() {
    const usuarioId = document.getElementById('recProUsuario').value;
    const titulo    = document.getElementById('recProTitulo').value.trim();
    const contenido = document.getElementById('recProContenido').value.trim();
    const tipo      = document.getElementById('recProTipo').value;
    if (!usuarioId) { showToastPlan('Selecciona un usuario', 'error'); return; }
    if (!titulo)    { showToastPlan('El título es requerido', 'error'); return; }

    try {
        const res = await fetch(API_URL + '/pro/plan/recomendar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: usuarioId, titulo, contenido, tipo })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalNuevaRecPro').style.display = 'none';
        showToastPlan('Recomendación guardada');
        cargarRecomendacionesPro();
    } catch (e) {
        showToastPlan(e.message || 'Error al guardar', 'error');
    }
}

async function eliminarRecPro(id) {
    if (!confirm('¿Eliminar esta recomendación?')) return;
    try {
        const res = await fetch(API_URL + '/pro/plan/remove', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo: 'recomendacion', id })
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showToastPlan('Recomendación eliminada');
        cargarRecomendacionesPro();
    } catch (e) {
        showToastPlan(e.message || 'Error al eliminar', 'error');
    }
}
