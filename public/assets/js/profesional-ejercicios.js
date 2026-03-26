
let proEjerciciosData = [];
let proEjerciciosFiltrados = [];
let proEjerciciosVisible = 8;

document.addEventListener('DOMContentLoaded', function () {
    cargarProEjercicios();

    document.getElementById('btnNuevoEjercicioPro').addEventListener('click', function () {
        document.getElementById('modalEjercicioProTitle').textContent = '💪 Nuevo Ejercicio';
        document.getElementById('formEjercicioPro').reset();
        document.getElementById('pro_ejercicio_id').value = '';
        const wrap = document.getElementById('pro_ejercicio_preview_wrap');
        if (wrap) wrap.style.display = 'none';
        document.getElementById('modalEjercicioPro').style.display = 'flex';
    });

    document.getElementById('formEjercicioPro').addEventListener('submit', function (e) {
        e.preventDefault();
        guardarProEjercicio();
    });
});

async function cargarProEjercicios() {
    try {
        const response = await fetch(API_URL + '/pro/ejercicios');
        const data = await response.json();
        proEjerciciosData = (data.success ? data.ejercicios : []) || [];
        proEjerciciosFiltrados = proEjerciciosData;
        proEjerciciosVisible = 8;
        renderProEjerciciosTable();
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('proEjerciciosBody').innerHTML =
            '<tr><td colspan="5" class="empty-message">Error al cargar ejercicios.</td></tr>';
    }
}

function renderProEjerciciosTable() {
    const tbody = document.getElementById('proEjerciciosBody');
    const wrap = document.getElementById('proEjerciciosMostrarMasWrap');
    if (!proEjerciciosFiltrados.length) {
        tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No hay ejercicios disponibles.</td></tr>';
        if (wrap) wrap.innerHTML = '';
        return;
    }
    tbody.innerHTML = proEjerciciosFiltrados.slice(0, proEjerciciosVisible).map(e => `
        <tr>
            <td>${esc(e.titulo)}</td>
            <td>${cap(e.tipo || 'cardio')}</td>
            <td><span style="color:${getNivelColor(e.nivel)};font-weight:600;">${cap(e.nivel || 'principiante')}</span></td>
            <td>${e.duracion || '-'} min</td>
            <td style="display:flex;gap:0.4rem;">
                <button class="btn btn-secondary btn-sm" onclick="editarProEjercicio(${e.id})">Editar</button>
                <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProEjercicio(${e.id})">Eliminar</button>
            </td>
        </tr>
    `).join('');
    if (wrap) {
        const remaining = proEjerciciosFiltrados.length - proEjerciciosVisible;
        wrap.innerHTML = remaining > 0
            ? `<button class="btn btn-secondary" style="margin-top:0.75rem;" onclick="mostrarMasProEjercicios()">Mostrar más (${remaining})</button>`
            : '';
    }
}

function mostrarMasProEjercicios() {
    proEjerciciosVisible += 8;
    renderProEjerciciosTable();
}

function editarProEjercicio(id) {
    const e = proEjerciciosData.find(item => item.id == id);
    if (!e) return;

    document.getElementById('modalEjercicioProTitle').textContent = '💪 Editar Ejercicio';
    document.getElementById('pro_ejercicio_id').value = e.id;
    document.getElementById('pro_ejercicio_titulo').value = e.titulo || '';
    document.getElementById('pro_ejercicio_descripcion').value = e.descripcion || '';
    document.getElementById('pro_ejercicio_duracion').value = e.duracion || '';
    document.getElementById('pro_ejercicio_nivel').value = e.nivel || 'principiante';
    document.getElementById('pro_ejercicio_tipo').value = e.tipo || 'cardio';
    document.getElementById('pro_ejercicio_calorias').value = e.calorias_quemadas || '';
    document.getElementById('pro_ejercicio_musculo').value = e.musculo_objetivo || '';
    document.getElementById('pro_ejercicio_equipamiento').value = e.equipamiento || '';
    document.getElementById('pro_ejercicio_secundarios').value = e.musculos_secundarios || '';
    document.getElementById('pro_ejercicio_video').value = e.video_url || '';
    document.getElementById('pro_ejercicio_instrucciones').value = e.instrucciones || '';
    document.getElementById('modalEjercicioPro').style.display = 'flex';
}

async function guardarProEjercicio() {
    const form = document.getElementById('formEjercicioPro');
    const formData = new FormData(form);

    try {
        const response = await fetch(API_URL + '/pro/ejercicios', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('modalEjercicioPro').style.display = 'none';
            cargarProEjercicios();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

async function eliminarProEjercicio(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar este ejercicio?')) return;

    try {
        const response = await fetch(API_URL + `/pro/ejercicios/${id}`, {
            method: 'DELETE'
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarProEjercicios();
        } else {
            showToast(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

function proEjercicioPreviewImagen(input) {
    const preview = document.getElementById('pro_ejercicio_imagen_preview');
    const wrap = document.getElementById('pro_ejercicio_preview_wrap');
    const nameEl = document.getElementById('pro_ejercicio_preview_name');
    const file = input.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            if (preview) { preview.src = e.target.result; preview.style.display = 'block'; }
            if (wrap) wrap.style.display = 'flex';
            if (nameEl) nameEl.textContent = file.name;
        };
        reader.readAsDataURL(file);
    } else {
        if (preview) preview.style.display = 'none';
        if (wrap) wrap.style.display = 'none';
    }
}

function getNivelColor(nivel) {
    const colors = { 'principiante': '#4caf50', 'intermedio': '#ff9800', 'avanzado': '#f44336' };
    return colors[nivel] || '#999';
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
