/**
 * BIENIESTAR - Profesional: Gestión de Recetas (Nutriólogo)
 */

let proRecetasData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarProRecetas();
    cargarPendingRecetas();

    document.getElementById('btnNuevaRecetaPro').addEventListener('click', function() {
        document.getElementById('modalRecetaProTitle').textContent = 'Nueva Receta';
        document.getElementById('formRecetaPro').reset();
        document.getElementById('pro_receta_id').value = '';
        document.getElementById('modalRecetaPro').style.display = 'flex';
    });

    document.getElementById('formRecetaPro').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarProReceta();
    });
});

async function cargarProRecetas() {
    try {
        const response = await fetch(API_URL + '/pro/recetas');
        const data = await response.json();
        const tbody = document.getElementById('proRecetasBody');

        if (data.success && data.recetas.length > 0) {
            proRecetasData = data.recetas;
            tbody.innerHTML = data.recetas.map(r => `
                <tr>
                    <td>${esc(r.titulo)}</td>
                    <td>${cap(r.categoria || 'comida')}</td>
                    <td>${r.calorias || '-'} kcal</td>
                    <td><span style="color:${r.activo == 1 ? '#34A853' : '#999'};font-weight:600;">${r.activo == 1 ? 'Activa' : 'Inactiva'}</span></td>
                    <td style="display:flex;gap:0.4rem;">
                        <button class="btn btn-secondary btn-sm" onclick="editarProReceta(${r.id})">Editar</button>
                        <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProReceta(${r.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        } else {
            proRecetasData = [];
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes recetas aún. ¡Crea tu primera!</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function editarProReceta(id) {
    const r = proRecetasData.find(item => item.id == id);
    if (!r) return;

    document.getElementById('modalRecetaProTitle').textContent = 'Editar Receta';
    document.getElementById('pro_receta_id').value = r.id;
    document.getElementById('pro_receta_titulo').value = r.titulo || '';
    document.getElementById('pro_receta_descripcion').value = r.descripcion || '';
    document.getElementById('pro_receta_ingredientes').value = r.ingredientes || '';
    document.getElementById('pro_receta_instrucciones').value = r.instrucciones || '';
    document.getElementById('pro_receta_tiempo').value = r.tiempo_preparacion || '';
    document.getElementById('pro_receta_porciones').value = r.porciones || '';
    document.getElementById('pro_receta_calorias').value = r.calorias || '';
    document.getElementById('pro_receta_proteinas').value = r.proteinas || '';
    document.getElementById('pro_receta_carbohidratos').value = r.carbohidratos || '';
    document.getElementById('pro_receta_grasas').value = r.grasas || '';
    document.getElementById('pro_receta_fibra').value = r.fibra || '';
    document.getElementById('pro_receta_categoria').value = r.categoria || 'comida';
    document.getElementById('modalRecetaPro').style.display = 'flex';
}

async function cargarPendingRecetas() {
    try {
        const res  = await fetch(API_URL + '/pro/recetas/pending');
        const data = await res.json();
        const grid = document.getElementById('pendingRecetasGrid');
        const badge = document.getElementById('pendingCount');

        if (!data.success || data.recetas.length === 0) {
            document.getElementById('sectionPendingRecetas').style.display = 'none';
            return;
        }

        badge.textContent = data.recetas.length;
        grid.innerHTML = data.recetas.map(r => {
            const img = r.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70';
            const cal = r.calorias ? Math.round(r.calorias) + ' kcal' : '—';
            const cat = cap(r.categoria || 'receta');
            return `<div style="background:#1e1e1e;border-radius:12px;overflow:hidden;border:1px solid #333;">
                <img src="${esc(img)}" alt="${esc(r.titulo)}" style="width:100%;height:140px;object-fit:cover;"
                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=300&q=70'">
                <div style="padding:0.75rem;">
                    <p style="font-weight:600;font-size:0.85rem;margin-bottom:4px;line-height:1.3;">${esc(r.titulo)}</p>
                    <p style="font-size:0.75rem;color:#999;margin-bottom:0.75rem;">${cat} · ${cal}</p>
                    <div style="display:flex;gap:0.4rem;">
                        <button onclick="aprobarReceta(${r.id})"
                            style="flex:1;padding:6px;background:#34A853;color:white;border:none;border-radius:6px;cursor:pointer;font-size:0.75rem;font-weight:600;">
                            ✓ Aprobar
                        </button>
                        <button onclick="rechazarReceta(${r.id})"
                            style="flex:1;padding:6px;background:#F44336;color:white;border:none;border-radius:6px;cursor:pointer;font-size:0.75rem;font-weight:600;">
                            ✕ Eliminar
                        </button>
                    </div>
                </div>
            </div>`;
        }).join('');
    } catch (e) {
        console.error('Error cargando pendientes:', e);
    }
}

async function aprobarReceta(id) {
    try {
        const res    = await fetch(API_URL + '/pro/recetas/approve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            showToast('Receta aprobada — se quedará permanentemente', 'success');
            cargarPendingRecetas();
        } else {
            showToast('Error al aprobar', 'error');
        }
    } catch (e) {
        showToast('Error de comunicación', 'error');
    }
}

async function rechazarReceta(id) {
    if (!confirm('¿Eliminar esta receta?')) return;
    try {
        const res    = await fetch(API_URL + '/pro/recetas/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        const result = await res.json();
        if (result.success) {
            showToast('Receta eliminada', 'success');
            cargarPendingRecetas();
        } else {
            showToast('Error al eliminar', 'error');
        }
    } catch (e) {
        showToast('Error de comunicación', 'error');
    }
}

async function guardarProReceta() {
    const form = document.getElementById('formRecetaPro');
    const formData = new FormData(form);

    try {
        const response = await fetch(API_URL + '/pro/recetas/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('modalRecetaPro').style.display = 'none';
            cargarProRecetas();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

async function eliminarProReceta(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta receta?')) return;

    try {
        const response = await fetch(API_URL + '/pro/recetas/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarProRecetas();
        } else {
            showToast(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

function esc(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function cap(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
