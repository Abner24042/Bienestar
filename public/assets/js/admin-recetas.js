// Datos globales de recetas
let recetasData = [];

// Cargar recetas al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarRecetas();

    // Botón nueva receta
    document.getElementById('btnNuevaReceta').addEventListener('click', function() {
        document.getElementById('modalRecetaTitle').textContent = 'Nueva Receta';
        document.getElementById('formReceta').reset();
        document.getElementById('receta_id').value = '';
        document.getElementById('receta_imagen_preview').style.display = 'none';
        document.getElementById('modalReceta').style.display = 'flex';
    });

    // Form submit
    document.getElementById('formReceta').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarReceta();
    });

    // Image preview on file input change
    document.getElementById('receta_imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('receta_imagen_preview');
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

async function cargarRecetas() {
    try {
        const response = await fetch(API_URL + '/admin/recetas');
        const data = await response.json();
        const tbody = document.getElementById('recetasTableBody');

        if (data.success && data.recetas.length > 0) {
            recetasData = data.recetas;
            tbody.innerHTML = data.recetas.map(receta => `
                <tr>
                    <td>${receta.id}</td>
                    <td>
                        ${receta.imagen
                            ? `<img src="${escapar(receta.imagen)}" alt="Imagen" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">`
                            : '<span style="color: #999;">Sin imagen</span>'}
                    </td>
                    <td>${escapar(receta.titulo)}</td>
                    <td>
                        <span class="rol-badge" style="background: ${getCategoriaColor(receta.categoria)};">
                            ${getCategoriaLabel(receta.categoria)}
                        </span>
                    </td>
                    <td>${receta.calorias ? receta.calorias + ' kcal' : 'N/A'}</td>
                    <td>
                        <span class="rol-badge" style="background: ${receta.activo == 1 ? '#34A853' : '#EA4335'};">
                            ${receta.activo == 1 ? 'Activo' : 'Inactivo'}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editarReceta(${receta.id})">
                            Editar
                        </button>
                        <button class="btn btn-sm" style="background: ${receta.activo == 1 ? '#EA4335' : '#34A853'}; color: white; border: none; margin-left: 4px;"
                                onclick="toggleReceta(${receta.id}, ${receta.activo == 1 ? 0 : 1})">
                            ${receta.activo == 1 ? 'Desactivar' : 'Activar'}
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="7" class="empty-message">No hay recetas</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function getCategoriaColor(categoria) {
    const colors = {
        'desayuno': '#FBBC04',
        'comida': '#34A853',
        'cena': '#4285F4',
        'snack': '#FF6D01',
        'postre': '#9C27B0'
    };
    return colors[categoria] || '#999';
}

function getCategoriaLabel(categoria) {
    const labels = {
        'desayuno': 'Desayuno',
        'comida': 'Comida',
        'cena': 'Cena',
        'snack': 'Snack',
        'postre': 'Postre'
    };
    return labels[categoria] || categoria;
}

function escapar(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function editarReceta(id) {
    const receta = recetasData.find(r => r.id == id);
    if (!receta) return;

    document.getElementById('modalRecetaTitle').textContent = 'Editar Receta';
    document.getElementById('receta_id').value = receta.id;
    document.getElementById('receta_titulo').value = receta.titulo || '';
    document.getElementById('receta_descripcion').value = receta.descripcion || '';
    document.getElementById('receta_ingredientes').value = receta.ingredientes || '';
    document.getElementById('receta_instrucciones').value = receta.instrucciones || '';
    document.getElementById('receta_tiempo_preparacion').value = receta.tiempo_preparacion || '';
    document.getElementById('receta_porciones').value = receta.porciones || '';
    document.getElementById('receta_calorias').value = receta.calorias || '';
    document.getElementById('receta_categoria').value = receta.categoria || 'desayuno';

    // Show existing image preview
    const preview = document.getElementById('receta_imagen_preview');
    if (receta.imagen) {
        preview.src = receta.imagen;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }

    // Reset file input
    document.getElementById('receta_imagen').value = '';

    document.getElementById('modalReceta').style.display = 'flex';
}

async function guardarReceta() {
    const formData = new FormData();
    formData.append('id', document.getElementById('receta_id').value || '');
    formData.append('titulo', document.getElementById('receta_titulo').value);
    formData.append('descripcion', document.getElementById('receta_descripcion').value);
    formData.append('ingredientes', document.getElementById('receta_ingredientes').value);
    formData.append('instrucciones', document.getElementById('receta_instrucciones').value);
    formData.append('tiempo_preparacion', document.getElementById('receta_tiempo_preparacion').value);
    formData.append('porciones', document.getElementById('receta_porciones').value);
    formData.append('calorias', document.getElementById('receta_calorias').value);
    formData.append('categoria', document.getElementById('receta_categoria').value);

    const imagenInput = document.getElementById('receta_imagen');
    if (imagenInput.files.length > 0) {
        formData.append('imagen', imagenInput.files[0]);
    }

    try {
        const response = await fetch(API_URL + '/admin/recetas/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cerrarModalReceta();
            cargarRecetas();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

async function toggleReceta(id, activo) {
    try {
        const response = await fetch(API_URL + '/admin/recetas/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: 'toggle', activo: activo })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarRecetas();
        } else {
            showToast(result.message || 'Error al cambiar estado', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

function cerrarModalReceta() {
    document.getElementById('modalReceta').style.display = 'none';
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
