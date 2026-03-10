// Datos globales de ejercicios
let ejerciciosData = [];

// Cargar ejercicios al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarEjercicios();

    // Botón nuevo ejercicio
    document.getElementById('btnNuevoEjercicio').addEventListener('click', function() {
        document.getElementById('modalEjercicioTitle').textContent = 'Nuevo Ejercicio';
        document.getElementById('formEjercicio').reset();
        document.getElementById('ejercicio_id').value = '';
        document.getElementById('ejercicio_imagen_preview').style.display = 'none';
        document.getElementById('modalEjercicio').style.display = 'flex';
    });

    // Form submit
    document.getElementById('formEjercicio').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarEjercicio();
    });

    // Image preview on file change
    document.getElementById('ejercicio_imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('ejercicio_imagen_preview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
});

async function cargarEjercicios() {
    try {
        const response = await fetch(API_URL + '/admin/ejercicios');
        const data = await response.json();
        const tbody = document.getElementById('ejerciciosTableBody');

        if (data.success && data.ejercicios.length > 0) {
            ejerciciosData = data.ejercicios;
            tbody.innerHTML = data.ejercicios.map(ej => `
                <tr>
                    <td>${ej.id}</td>
                    <td>
                        ${ej.imagen
                            ? `<img src="${escapar(ej.imagen)}" alt="img" style="width:50px; height:50px; object-fit:cover; border-radius:6px;">`
                            : '<span style="color:#aaa;">Sin imagen</span>'}
                    </td>
                    <td>${escapar(ej.titulo)}</td>
                    <td>
                        <span class="rol-badge" style="background: #4285F4;">
                            ${escapar(ej.tipo)}
                        </span>
                    </td>
                    <td>
                        <span class="rol-badge" style="background: ${getNivelColor(ej.nivel)};">
                            ${escapar(ej.nivel)}
                        </span>
                    </td>
                    <td>${ej.duracion ? ej.duracion + ' min' : 'N/A'}</td>
                    <td>
                        <span class="rol-badge" style="background: ${ej.activo == 1 ? '#34A853' : '#EA4335'};">
                            ${ej.activo == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm"
                                onclick="editarEjercicio(${ej.id})">
                            Editar
                        </button>
                        <button class="btn btn-sm"
                                style="background: ${ej.activo == 1 ? '#EA4335' : '#34A853'}; color: white; border: none; margin-left: 4px;"
                                onclick="toggleEjercicio(${ej.id}, ${ej.activo == 1 ? 0 : 1})">
                            ${ej.activo == 1 ? 'Desactivar' : 'Activar'}
                        </button>
                        <button class="btn btn-sm"
                                style="background: #c0392b; color: white; border: none; margin-left: 4px;"
                                onclick="eliminarEjercicio(${ej.id}, '${escapar(ej.titulo)}')">
                            Eliminar
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-message">No hay ejercicios</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function getNivelColor(nivel) {
    const colors = {
        'principiante': '#4caf50',
        'intermedio': '#ff9800',
        'avanzado': '#f44336'
    };
    return colors[nivel] || '#999';
}

function escapar(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function editarEjercicio(id) {
    const ej = ejerciciosData.find(e => e.id == id);
    if (!ej) return;

    document.getElementById('modalEjercicioTitle').textContent = 'Editar Ejercicio';
    document.getElementById('ejercicio_id').value = ej.id;
    document.getElementById('ejercicio_titulo').value = ej.titulo || '';
    document.getElementById('ejercicio_descripcion').value = ej.descripcion || '';
    document.getElementById('ejercicio_duracion').value = ej.duracion || '';
    document.getElementById('ejercicio_nivel').value = ej.nivel || 'principiante';
    document.getElementById('ejercicio_tipo').value = ej.tipo || 'cardio';
    document.getElementById('ejercicio_calorias').value = ej.calorias_quemadas || '';
    document.getElementById('ejercicio_video').value = ej.video_url || '';
    document.getElementById('ejercicio_instrucciones').value = ej.instrucciones || '';

    // Show existing image preview
    const preview = document.getElementById('ejercicio_imagen_preview');
    if (ej.imagen) {
        preview.src = ej.imagen;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    document.getElementById('ejercicio_imagen').value = '';
    document.getElementById('modalEjercicio').style.display = 'flex';
}

async function guardarEjercicio() {
    const formData = new FormData();
    formData.append('id', document.getElementById('ejercicio_id').value || '');
    formData.append('titulo', document.getElementById('ejercicio_titulo').value);
    formData.append('descripcion', document.getElementById('ejercicio_descripcion').value);
    formData.append('duracion', document.getElementById('ejercicio_duracion').value);
    formData.append('nivel', document.getElementById('ejercicio_nivel').value);
    formData.append('tipo', document.getElementById('ejercicio_tipo').value);
    formData.append('calorias_quemadas', document.getElementById('ejercicio_calorias').value);
    formData.append('video_url', document.getElementById('ejercicio_video').value);
    formData.append('instrucciones', document.getElementById('ejercicio_instrucciones').value);

    const imagenInput = document.getElementById('ejercicio_imagen');
    if (imagenInput.files[0]) {
        formData.append('imagen', imagenInput.files[0]);
    }

    try {
        const response = await fetch(API_URL + '/admin/ejercicios/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cerrarModalEjercicio();
            cargarEjercicios();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

async function toggleEjercicio(id, activo) {
    try {
        const response = await fetch(API_URL + '/admin/ejercicios/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: 'toggle', activo: activo })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarEjercicios();
        } else {
            showToast(result.message || 'Error al cambiar estado', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

async function eliminarEjercicio(id, titulo) {
    if (!confirm(`¿Eliminar el ejercicio "${titulo}"?\nEsta acción no se puede deshacer.`)) return;

    try {
        const response = await fetch(API_URL + '/admin/ejercicios/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: 'delete' })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Ejercicio eliminado', 'success');
            cargarEjercicios();
        } else {
            showToast(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

function cerrarModalEjercicio() {
    document.getElementById('modalEjercicio').style.display = 'none';
}

function showToast(message, type = 'info') {
    let toast = document.querySelector('.toast-notification');
    if (!toast) {
        toast = document.createElement('div');
        toast.className = 'toast-notification';
        document.body.appendChild(toast);
    }
    toast.className = 'toast-notification toast-' + type;
    toast.textContent = message;
    toast.style.cssText = 'position: fixed; top: 20px; right: 20px; padding: 1rem 1.5rem; border-radius: 8px; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 10000; display: block; opacity: 1;';
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3000);
}
