/**
 * BIENESTAR - Gestión de Planes Alimenticios (Nutriólogo)
 */

let recetasDisponiblesPA = [];

const DIAS_SEMANA = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
const TIEMPOS_COMIDA = ['desayuno', 'almuerzo', 'merienda', 'cena', 'comida'];

// ── Global Item Picker Singleton ──────────────────────────────────────────────
;(function () {
    var PAGE = 8;
    var panel = null, currentTrigger = null, onSelectCb = null;
    var allItems = [], filtered = [], filterKey = 'categoria';
    var activeFilter = 'all', term = '', shown = PAGE;

    function buildPanel() {
        if (panel) return;
        panel = document.createElement('div');
        panel.id = 'itemPickerPanel';
        panel.innerHTML =
            '<div class="ip-search-wrap"><input type="text" class="ip-search" placeholder="Buscar..."></div>' +
            '<div class="ip-filters"></div>' +
            '<div class="ip-count"></div>' +
            '<div class="ip-list"></div>';
        document.body.appendChild(panel);
        panel.querySelector('.ip-search').addEventListener('input', function () {
            term = this.value.toLowerCase();
            shown = PAGE;
            applyFilter();
        });
        document.addEventListener('mousedown', function (e) {
            if (!panel || !panel.classList.contains('ip-open')) return;
            if (!panel.contains(e.target) && currentTrigger && !currentTrigger.contains(e.target)) {
                closePanel();
            }
        });
    }

    function applyFilter() {
        filtered = allItems.filter(function (item) {
            var tOk = !term || (item.titulo || '').toLowerCase().includes(term) ||
                      (item.descripcion || '').toLowerCase().includes(term);
            var fOk = activeFilter === 'all' || (item[filterKey] || '') === activeFilter;
            return tOk && fOk;
        });
        renderList();
    }

    function renderFilters() {
        var el = panel.querySelector('.ip-filters');
        var cats = ['all'];
        allItems.forEach(function (item) {
            var v = item[filterKey] || '';
            if (v && !cats.includes(v)) cats.push(v);
        });
        if (cats.length <= 2) { el.style.display = 'none'; return; }
        el.style.display = '';
        el.innerHTML = cats.map(function (c) {
            return '<button class="ip-filter' + (activeFilter === c ? ' active' : '') + '" data-f="' + ipEsc(c) + '">' +
                (c === 'all' ? 'Todos' : ipCap(c)) + '</button>';
        }).join('');
        el.querySelectorAll('.ip-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                activeFilter = this.dataset.f;
                shown = PAGE;
                el.querySelectorAll('.ip-filter').forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                applyFilter();
            });
        });
    }

    function renderList() {
        var listEl  = panel.querySelector('.ip-list');
        var countEl = panel.querySelector('.ip-count');
        countEl.textContent = filtered.length + ' resultado' + (filtered.length !== 1 ? 's' : '');
        if (!filtered.length) {
            listEl.innerHTML = '<div class="ip-empty">Sin resultados' + (term ? ' para "' + ipEsc(term) + '"' : '') + '</div>';
            return;
        }
        var curVal  = currentTrigger ? (currentTrigger.dataset.value || '') : '';
        var visible = filtered.slice(0, shown);
        var html = visible.map(function (item) {
            var isRec  = filterKey === 'categoria';
            var icon   = isRec ? '🍽️' : '💪';
            var bg     = isRec ? 'rgba(76,175,80,.1)' : 'rgba(255,107,53,.1)';
            var thumb  = item.imagen
                ? '<img src="' + ipEsc(item.imagen) + '" loading="lazy" onerror="this.style.display=\'none\'">'
                : '<div class="ip-item-icon" style="background:' + bg + ';">' + icon + '</div>';
            var sub = [];
            if (item.calorias)           sub.push(Math.round(item.calorias) + ' kcal');
            if (item.tiempo_preparacion) sub.push(item.tiempo_preparacion + ' min');
            if (item.duracion)           sub.push(item.duracion + ' min');
            if (item.musculo_objetivo)   sub.push(ipCap(item.musculo_objetivo));
            var subHtml = sub.length ? '<div class="ip-item-sub">' + sub.join(' · ') + '</div>' : '';
            var badges = '';
            if (isRec) {
                if (item.categoria) badges += '<span class="ip-badge ip-badge-cat">' + ipEsc(ipCap(item.categoria)) + '</span>';
            } else {
                if (item.tipo)  badges += '<span class="ip-badge ip-badge-type">' + ipEsc(ipCap(item.tipo)) + '</span>';
                if (item.nivel) badges += '<span class="ip-badge ip-badge-nivel nivel-' + item.nivel + '">' + ipEsc(ipCap(item.nivel)) + '</span>';
            }
            var selCls = String(item.id) === String(curVal) ? ' ip-selected' : '';
            return '<div class="ip-item' + selCls + '" data-id="' + item.id + '">' +
                thumb +
                '<div class="ip-item-body"><div class="ip-item-title">' + ipEsc(item.titulo) + '</div>' + subHtml + '</div>' +
                '<div class="ip-item-badges">' + badges + '</div></div>';
        }).join('');

        var remaining = filtered.length - shown;
        if (remaining > 0) {
            html += '<button class="ip-show-more">Mostrar más (' + remaining + ' restante' + (remaining !== 1 ? 's' : '') + ')</button>';
        }

        listEl.innerHTML = html;
        listEl.querySelectorAll('.ip-item').forEach(function (el) {
            el.addEventListener('click', function () {
                var found = allItems.find(function (x) { return String(x.id) === String(this.dataset.id); }, this);
                if (found && onSelectCb) onSelectCb(found);
                closePanel();
            });
        });
        var moreBtn = listEl.querySelector('.ip-show-more');
        if (moreBtn) {
            moreBtn.addEventListener('click', function () {
                shown += PAGE;
                renderList();
                // Keep scroll position
                listEl.scrollTop = listEl.scrollHeight;
            });
        }
    }

    function openPanel(trigger, items, fKey, cb, accent) {
        buildPanel();
        currentTrigger = trigger;
        allItems = items;
        filterKey = fKey;
        onSelectCb = cb;
        activeFilter = 'all';
        term = '';
        shown = PAGE;
        panel.querySelector('.ip-search').value = '';
        // Accent class (green = recetas, orange = ejercicios)
        panel.className = accent === 'green' ? 'ip-green' : '';
        applyFilter();
        renderFilters();
        // Position
        var rect    = trigger.getBoundingClientRect();
        var panelW  = Math.max(rect.width, 360);
        var left    = rect.left;
        if (left + panelW > window.innerWidth - 8) left = Math.max(8, window.innerWidth - panelW - 8);
        var topPos  = rect.bottom + 4;
        if (topPos + 360 > window.innerHeight) topPos = Math.max(8, rect.top - 364);
        panel.style.cssText = 'left:' + left + 'px;top:' + topPos + 'px;width:' + panelW + 'px;max-height:360px;';
        panel.classList.add('ip-open');
        panel.querySelector('.ip-search').focus();
        trigger.classList.add('open');
    }

    function closePanel() {
        if (!panel) return;
        panel.classList.remove('ip-open');
        if (currentTrigger) currentTrigger.classList.remove('open');
        currentTrigger = null;
    }

    function ipCap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function ipEsc(s) {
        if (!s) return '';
        var d = document.createElement('div'); d.textContent = String(s); return d.innerHTML;
    }

    window.openItemPicker       = openPanel;
    window.closeItemPickerPanel = closePanel;
})();

// ─────────────────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('proPlanesAlimBody')) {
        cargarPlanesAlimenticios();
        cargarRecetasDisponiblesPA();
        document.getElementById('btnNuevoPlanAlim').addEventListener('click', () => abrirModalPlanAlim());
    }
    if (document.getElementById('modalAsignarPlanAlim')) {
        cargarPlanesAlimSelector();
    }
});

async function cargarPlanesAlimenticios() {
    const tbody = document.getElementById('proPlanesAlimBody');
    if (!tbody) return;
    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios');
        const data = await res.json();
        if (!data.success) throw new Error();
        if (!data.planes.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes planes alimenticios aún. ¡Crea el primero!</td></tr>';
            return;
        }
        tbody.innerHTML = data.planes.map(p => `
            <tr>
                <td><strong>${escPA(p.nombre)}</strong>${p.descripcion ? `<br><small style="color:#999;">${escPA(p.descripcion.substring(0,60))}${p.descripcion.length>60?'…':''}</small>` : ''}</td>
                <td style="color:#999;font-size:0.85rem;">${p.objetivo ? escPA(p.objetivo) : '—'}</td>
                <td style="text-align:center;">${p.duracion_semanas} sem.</td>
                <td style="text-align:center;">${p.num_recetas}</td>
                <td>
                    <button class="btn btn-secondary btn-sm" onclick="editarPlanAlim(${p.id})">Editar</button>
                    <button class="btn btn-sm" style="background:#4caf50;color:white;border:none;margin-left:4px;" onclick="exportarPlanAlimPDF(${p.id})">PDF</button>
                    <button class="btn btn-sm" style="background:#c0392b;color:white;border:none;margin-left:4px;" onclick="eliminarPlanAlim(${p.id},'${escPA(p.nombre)}')">Eliminar</button>
                </td>
            </tr>
        `).join('');
    } catch (e) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">Error al cargar planes.</td></tr>';
    }
}

async function cargarRecetasDisponiblesPA() {
    try {
        const res = await fetch(API_URL + '/pro/recetas');
        const data = await res.json();
        if (data.success) recetasDisponiblesPA = data.recetas || [];
    } catch (e) {}
}

async function cargarPlanesAlimSelector() {
    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios');
        const data = await res.json();
        if (!data.success) return;
        const sel = document.getElementById('asignarPlanAlimSelect');
        if (!sel) return;
        sel.innerHTML = '<option value="">— Elige un plan —</option>' +
            data.planes.map(p => `<option value="${p.id}">${escPA(p.nombre)} (${p.num_recetas} recetas)</option>`).join('');
    } catch (e) {}
}

function abrirModalPlanAlim(id = null) {
    document.getElementById('plan_alim_id').value = id || '';
    document.getElementById('plan_alim_nombre').value = '';
    document.getElementById('plan_alim_descripcion').value = '';
    document.getElementById('plan_alim_objetivo').value = '';
    document.getElementById('plan_alim_duracion').value = '1';
    document.getElementById('planAlimRecetasList').innerHTML = '';
    document.getElementById('modalPlanAlimTitle').textContent = id ? 'Editar Plan Alimenticio' : 'Nuevo Plan Alimenticio';
    document.getElementById('modalPlanAlim').style.display = 'flex';
}

async function editarPlanAlim(id) {
    abrirModalPlanAlim(id);
    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        const p = data.plan;
        document.getElementById('plan_alim_nombre').value = p.nombre;
        document.getElementById('plan_alim_descripcion').value = p.descripcion || '';
        document.getElementById('plan_alim_objetivo').value = p.objetivo || '';
        document.getElementById('plan_alim_duracion').value = p.duracion_semanas || 1;
        document.getElementById('planAlimRecetasList').innerHTML = '';
        (p.recetas || []).forEach(r => agregarRecetaPlanAlim(r));
    } catch (e) {
        showToastPA('Error al cargar el plan', 'error');
    }
}

function cerrarModalPlanAlim() {
    closeItemPickerPanel && closeItemPickerPanel();
    document.getElementById('modalPlanAlim').style.display = 'none';
}

// ── Picker de recetas ─────────────────────────────────────────────────────────

function buildRecetaTriggerPA(receta) {
    if (!receta) {
        return `<div class="picker-trigger-icon" style="background:rgba(76,175,80,.1);">🍽️</div>
                <span class="picker-trigger-text"><span class="picker-trigger-title" style="opacity:.45;">— Buscar receta... —</span></span>`;
    }
    const thumb = receta.imagen
        ? `<img class="picker-trigger-thumb" src="${escPA(receta.imagen)}" onerror="this.style.display='none'">`
        : `<div class="picker-trigger-icon" style="background:rgba(76,175,80,.1);">🍽️</div>`;
    const cat = receta.categoria
        ? `<span class="picker-trigger-badge" style="background:rgba(76,175,80,.12);color:#4caf50;">${escPA(receta.categoria)}</span>`
        : '';
    return `${thumb}<span class="picker-trigger-text"><span class="picker-trigger-title">${escPA(receta.titulo)}</span></span>${cat}`;
}

const CHEVRON_SVG = `<svg class="picker-trigger-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>`;

function abrirPickerRecetaPA(trigger) {
    if (trigger.classList.contains('open')) { closeItemPickerPanel(); return; }
    openItemPicker(trigger, recetasDisponiblesPA, 'categoria', function (receta) {
        const row = trigger.closest('.plan-rec-row');
        row.querySelector('.prec-receta').value = receta.id;
        trigger.dataset.value = receta.id;
        trigger.classList.add('has-value');
        trigger.innerHTML = buildRecetaTriggerPA(receta) + CHEVRON_SVG;
    }, 'green');
}

function agregarRecetaPlanAlim(datos = null) {
    const container = document.getElementById('planAlimRecetasList');
    const row = document.createElement('div');
    row.className = 'picker-row picker-row-green plan-rec-row';

    const selRec = datos ? recetasDisponiblesPA.find(r => r.id == datos.receta_id) : null;

    const optsDia = DIAS_SEMANA.slice(1).map((d, i) =>
        `<option value="${i+1}" ${datos && datos.dia_semana == i+1 ? 'selected' : ''}>${d}</option>`
    ).join('');

    const optsTiempo = TIEMPOS_COMIDA.map(t =>
        `<option value="${t}" ${datos && datos.tiempo_comida === t ? 'selected' : ''}>${t.charAt(0).toUpperCase() + t.slice(1)}</option>`
    ).join('');

    row.innerHTML = `
        <div class="picker-row-top">
            <input type="hidden" class="prec-receta" value="${datos ? escPA(String(datos.receta_id || '')) : ''}">
            <button type="button" class="item-picker-trigger ${selRec ? 'has-value' : ''}"
                    data-value="${datos ? escPA(String(datos.receta_id || '')) : ''}"
                    onclick="abrirPickerRecetaPA(this)">
                ${buildRecetaTriggerPA(selRec)}
                ${CHEVRON_SVG}
            </button>
            <button type="button" class="picker-row-remove" onclick="this.closest('.plan-rec-row').remove()">✕</button>
        </div>
        <div class="picker-row-fields">
            <div class="picker-field"><label>Día</label><select class="prec-dia">${optsDia}</select></div>
            <div class="picker-field"><label>Momento</label><select class="prec-tiempo">${optsTiempo}</select></div>
            <div class="picker-field"><label>Porciones</label>
                <input type="number" class="prec-porciones" value="${datos ? escPA(String(datos.porciones || 1)) : 1}" min="0.5" step="0.5"></div>
            <div class="picker-field"><label>Notas</label>
                <input type="text" class="prec-notas" value="${datos ? escPA(datos.notas || '') : ''}" placeholder="Opcional"></div>
        </div>
    `;
    container.appendChild(row);
}

async function guardarPlanAlim() {
    const nombre = document.getElementById('plan_alim_nombre').value.trim();
    if (!nombre) { showToastPA('El nombre es requerido', 'error'); return; }

    const rows = document.querySelectorAll('.plan-rec-row');
    const recetas = [];
    for (const row of rows) {
        const recId = row.querySelector('.prec-receta').value;
        if (!recId) { showToastPA('Selecciona una receta en cada fila', 'error'); return; }
        recetas.push({
            receta_id: recId,
            dia_semana: parseInt(row.querySelector('.prec-dia').value),
            tiempo_comida: row.querySelector('.prec-tiempo').value,
            porciones: parseFloat(row.querySelector('.prec-porciones').value) || 1,
            notas: row.querySelector('.prec-notas').value.trim() || null,
        });
    }

    const payload = {
        id: document.getElementById('plan_alim_id').value || null,
        nombre,
        descripcion: document.getElementById('plan_alim_descripcion').value.trim() || null,
        objetivo: document.getElementById('plan_alim_objetivo').value.trim() || null,
        duracion_semanas: parseInt(document.getElementById('plan_alim_duracion').value) || 1,
        recetas,
    };

    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        cerrarModalPlanAlim();
        showToastPA(payload.id ? 'Plan actualizado' : 'Plan creado');
        cargarPlanesAlimenticios();
        cargarPlanesAlimSelector();
    } catch (e) {
        showToastPA(e.message || 'Error al guardar', 'error');
    }
}

async function eliminarPlanAlim(id, nombre) {
    if (!confirm(`¿Eliminar el plan "${nombre}"?\nEsta acción no se puede deshacer.`)) return;
    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        showToastPA('Plan eliminado');
        cargarPlanesAlimenticios();
    } catch (e) {
        showToastPA(e.message || 'Error al eliminar', 'error');
    }
}

async function exportarPlanAlimPDF(id) {
    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios/detail?id=' + id);
        const data = await res.json();
        if (!data.success) throw new Error();
        generarPlanAlimPDF(data.plan);
    } catch (e) {
        showToastPA('Error al generar PDF', 'error');
    }
}

function generarPlanAlimPDF(plan) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const green = [76, 175, 80];
    const dark = [17, 17, 17];
    const gray = [100, 100, 100];
    const especialista = (typeof PROFESSIONAL_USER !== 'undefined' && PROFESSIONAL_USER.nombre) ? PROFESSIONAL_USER.nombre : '';

    doc.setFillColor(...green);
    doc.rect(0, 0, 210, 32, 'F');
    doc.setTextColor(255, 255, 255);
    doc.setFontSize(17);
    doc.setFont('helvetica', 'bold');
    doc.text('PLAN ALIMENTICIO', 14, 13);
    doc.setFontSize(10);
    doc.setFont('helvetica', 'normal');
    doc.text('Bienestar — Plan Nutricional Personalizado', 14, 21);
    if (especialista) doc.text('Nutriólogo/a: ' + especialista, 14, 28);

    let y = 42;
    doc.setTextColor(...dark);
    doc.setFontSize(15);
    doc.setFont('helvetica', 'bold');
    doc.text(plan.nombre, 14, y);
    y += 7;

    doc.setFontSize(9);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...gray);
    const partes = ['Duración: ' + plan.duracion_semanas + ' semana(s)', 'Recetas: ' + (plan.recetas || []).length];
    if (plan.objetivo) partes.push('Objetivo: ' + plan.objetivo);
    doc.text(partes.join('   |   '), 14, y);
    y += 5;

    if (plan.descripcion) {
        y += 2;
        const lines = doc.splitTextToSize(plan.descripcion, 182);
        doc.text(lines, 14, y);
        y += lines.length * 4.5 + 1;
    }

    y += 4;
    doc.setDrawColor(...green);
    doc.setLineWidth(0.5);
    doc.line(14, y, 196, y);
    y += 7;

    if (plan.recetas && plan.recetas.length) {
        const byDay = {};
        plan.recetas.forEach(r => {
            const d = r.dia_semana;
            if (!byDay[d]) byDay[d] = [];
            byDay[d].push(r);
        });

        const tableBody = [];
        Object.keys(byDay).sort((a, b) => a - b).forEach(dia => {
            byDay[dia].forEach((r, i) => {
                const diaLabel = i === 0 ? (DIAS_SEMANA[dia] || 'Día ' + dia) : '';
                tableBody.push([
                    diaLabel,
                    r.tiempo_comida ? r.tiempo_comida.charAt(0).toUpperCase() + r.tiempo_comida.slice(1) : '—',
                    r.receta_titulo || '—',
                    r.porciones != null ? String(r.porciones) : '1',
                    r.calorias ? r.calorias + ' kcal' : '—',
                    r.notas || '',
                ]);
            });
        });

        doc.autoTable({
            startY: y, margin: { left: 14, right: 14 },
            head: [['Día', 'Momento', 'Receta', 'Porc.', 'Calorías', 'Notas']],
            body: tableBody,
            styles: { fontSize: 9, cellPadding: { top: 4, right: 4, bottom: 4, left: 4 }, valign: 'middle', overflow: 'linebreak' },
            headStyles: { fillColor: green, textColor: 255, fontStyle: 'bold', valign: 'middle', halign: 'center' },
            alternateRowStyles: { fillColor: [240, 249, 240] },
            columnStyles: {
                0: { cellWidth: 25, fontStyle: 'bold' }, 1: { cellWidth: 22, halign: 'center' },
                2: { cellWidth: 58 }, 3: { cellWidth: 15, halign: 'center' },
                4: { cellWidth: 24, halign: 'center' }, 5: { cellWidth: 38 },
            },
        });
    } else {
        doc.setTextColor(...gray);
        doc.setFontSize(10);
        doc.text('Sin recetas registradas.', 14, y);
    }

    const pageCount = doc.internal.getNumberOfPages();
    const fechaHoy = new Date().toLocaleDateString('es-MX', { day: '2-digit', month: 'long', year: 'numeric' });
    for (let i = 1; i <= pageCount; i++) {
        doc.setPage(i);
        doc.setFontSize(8);
        doc.setTextColor(180, 180, 180);
        doc.text('Bienestar — Generado el ' + fechaHoy, 14, 290);
        doc.text(`Página ${i} de ${pageCount}`, 196, 290, { align: 'right' });
    }

    doc.save(`plan-alimenticio-${plan.nombre.replace(/[^a-z0-9]/gi, '_').toLowerCase()}.pdf`);
}

// ─── Asignar plan a usuario ────────────────────────────────────────

function abrirModalAsignarPlanAlim() {
    if (!planUsuarioActual) { showToastPA('Selecciona un usuario primero', 'error'); return; }
    const notas = document.getElementById('asignarPlanAlimNotas');
    if (notas) notas.value = '';
    document.getElementById('modalAsignarPlanAlim').style.display = 'flex';
}

async function confirmarAsignarPlanAlim() {
    const planId = document.getElementById('asignarPlanAlimSelect').value;
    const notas = document.getElementById('asignarPlanAlimNotas').value.trim();
    if (!planId) { showToastPA('Selecciona un plan', 'error'); return; }

    try {
        const res = await fetch(API_URL + '/pro/planes-alimenticios/asignar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: planUsuarioActual, plan_id: planId, notas: notas || null }),
        });
        const data = await res.json();
        if (!data.success) throw new Error(data.message);
        document.getElementById('modalAsignarPlanAlim').style.display = 'none';
        showToastPA(data.message || 'Plan asignado');
        if (typeof cargarPlanUsuario === 'function') cargarPlanUsuario(planUsuarioActual);
    } catch (e) {
        showToastPA(e.message || 'Error al asignar', 'error');
    }
}

function showToastPA(msg, type = 'success') {
    if (typeof showToast === 'function') { showToast(msg, type); return; }
    const toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:${type === 'error' ? '#f44336' : '#4caf50'};color:#fff;padding:10px 24px;border-radius:8px;z-index:9999;font-size:0.9rem;`;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function escPA(str) {
    if (str === null || str === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(str);
    return d.innerHTML;
}
