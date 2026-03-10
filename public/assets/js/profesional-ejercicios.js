/**
 * BIENIESTAR - Profesional: Gestión de Ejercicios (Coach)
 */

let proEjerciciosData = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarProEjercicios();

    document.getElementById('btnNuevoEjercicioPro').addEventListener('click', function() {
        document.getElementById('modalEjercicioProTitle').textContent = 'Nuevo Ejercicio';
        document.getElementById('formEjercicioPro').reset();
        document.getElementById('pro_ejercicio_id').value = '';
        document.getElementById('modalEjercicioPro').style.display = 'flex';
    });

    document.getElementById('formEjercicioPro').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarProEjercicio();
    });
});

async function cargarProEjercicios() {
    try {
        const response = await fetch(API_URL + '/pro/ejercicios');
        const data = await response.json();
        const tbody = document.getElementById('proEjerciciosBody');

        if (data.success && data.ejercicios.length > 0) {
            proEjerciciosData = data.ejercicios;
            tbody.innerHTML = data.ejercicios.map(e => `
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
        } else {
            proEjerciciosData = [];
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes ejercicios aún. ¡Crea tu primero!</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function editarProEjercicio(id) {
    const e = proEjerciciosData.find(item => item.id == id);
    if (!e) return;

    document.getElementById('modalEjercicioProTitle').textContent = 'Editar Ejercicio';
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
        const response = await fetch(API_URL + '/pro/ejercicios/save', {
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
        const response = await fetch(API_URL + '/pro/ejercicios/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
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
