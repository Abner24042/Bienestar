// Datos globales de noticias
let noticiasData = [];

// Cargar noticias al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarNoticias();

    // Botón nueva noticia
    document.getElementById('btnNuevaNoticia').addEventListener('click', function() {
        document.getElementById('modalNoticiaTitle').textContent = 'Nueva Noticia';
        document.getElementById('formNoticia').reset();
        document.getElementById('noticia_id').value = '';
        document.getElementById('noticia_imagen_preview').style.display = 'none';
        document.getElementById('noticia_imagen_preview').src = '';
        document.getElementById('modalNoticia').style.display = 'flex';
    });

    // Form submit
    document.getElementById('formNoticia').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarNoticia();
    });

    // Image preview on file input change
    document.getElementById('noticia_imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('noticia_imagen_preview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
            preview.src = '';
        }
    });
});

async function cargarNoticias() {
    try {
        const response = await fetch(API_URL + '/admin/noticias');
        const data = await response.json();
        const tbody = document.getElementById('noticiasTableBody');

        if (data.success && data.noticias.length > 0) {
            noticiasData = data.noticias;
            tbody.innerHTML = data.noticias.map(noticia => `
                <tr>
                    <td>${noticia.id}</td>
                    <td>
                        ${noticia.imagen
                            ? `<img src="${escapar(noticia.imagen)}" alt="Imagen" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">`
                            : '<span style="color: #999;">Sin imagen</span>'}
                    </td>
                    <td>${escapar(noticia.titulo)}</td>
                    <td>
                        <span class="rol-badge" style="background: ${getCategoriaColor(noticia.categoria)};">
                            ${getCategoriaLabel(noticia.categoria)}
                        </span>
                    </td>
                    <td>${escapar(noticia.autor || 'N/A')}</td>
                    <td>
                        <span class="rol-badge" style="background: ${noticia.publicado == 1 ? '#34A853' : '#999'};">
                            ${noticia.publicado == 1 ? 'Publicado' : 'Borrador'}
                        </span>
                    </td>
                    <td>
                        ${noticia.destacado == 1
                            ? '<span class="rol-badge" style="background: #f59e0b;">⭐ Destacada</span>'
                            : '<span style="color:#ccc; font-size:0.8rem;">—</span>'}
                    </td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="editarNoticia(${noticia.id})">
                            Editar
                        </button>
                        <button class="btn btn-sm" style="background: ${noticia.publicado == 1 ? '#999' : '#34A853'}; color: white; border: none; margin-left: 4px;"
                                onclick="toggleNoticia(${noticia.id}, ${noticia.publicado})">
                            ${noticia.publicado == 1 ? 'Ocultar' : 'Publicar'}
                        </button>
                        <button class="btn btn-sm" style="background: ${noticia.destacado == 1 ? '#d97706' : '#f59e0b'}; color: white; border: none; margin-left: 4px;"
                                onclick="destacarNoticia(${noticia.id})"
                                ${noticia.destacado == 1 ? 'disabled title="Ya es la destacada"' : ''}>
                            ⭐ Destacar
                        </button>
                    </td>
                </tr>
            `).join('');
        } else {
            noticiasData = [];
            tbody.innerHTML = '<tr><td colspan="7" class="empty-message">No hay noticias</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function getCategoriaColor(categoria) {
    const colors = {
        'alimentacion': '#34A853',
        'ejercicio': '#4285F4',
        'salud-mental': '#9C27B0',
        'general': '#FBBC04'
    };
    return colors[categoria] || '#999';
}

function getCategoriaLabel(categoria) {
    const labels = {
        'alimentacion': 'Alimentación',
        'ejercicio': 'Ejercicio',
        'salud-mental': 'Salud Mental',
        'general': 'General'
    };
    return labels[categoria] || categoria;
}

function escapar(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function editarNoticia(id) {
    const noticia = noticiasData.find(n => n.id == id);
    if (!noticia) return;

    document.getElementById('modalNoticiaTitle').textContent = 'Editar Noticia';
    document.getElementById('noticia_id').value = noticia.id;
    document.getElementById('noticia_titulo').value = noticia.titulo || '';
    document.getElementById('noticia_resumen').value = noticia.resumen || '';
    document.getElementById('noticia_contenido').value = noticia.contenido || '';
    document.getElementById('noticia_categoria').value = noticia.categoria || 'general';
    document.getElementById('noticia_autor').value = noticia.autor || '';
    document.getElementById('noticia_publicado').checked = noticia.publicado == 1;
    document.getElementById('noticia_imagen').value = '';

    const preview = document.getElementById('noticia_imagen_preview');
    if (noticia.imagen) {
        preview.src = noticia.imagen;
        preview.style.display = 'block';
    } else {
        preview.src = '';
        preview.style.display = 'none';
    }

    document.getElementById('modalNoticia').style.display = 'flex';
}

async function guardarNoticia() {
    const formData = new FormData();
    const id = document.getElementById('noticia_id').value;
    if (id) {
        formData.append('id', id);
    }
    formData.append('titulo', document.getElementById('noticia_titulo').value);
    formData.append('resumen', document.getElementById('noticia_resumen').value);
    formData.append('contenido', document.getElementById('noticia_contenido').value);
    formData.append('categoria', document.getElementById('noticia_categoria').value);
    formData.append('autor', document.getElementById('noticia_autor').value);
    formData.append('publicado', document.getElementById('noticia_publicado').checked ? '1' : '0');

    const imagenInput = document.getElementById('noticia_imagen');
    if (imagenInput.files.length > 0) {
        formData.append('imagen', imagenInput.files[0]);
    }

    try {
        const response = await fetch(API_URL + '/admin/noticias/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cerrarModalNoticia();
            cargarNoticias();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

async function toggleNoticia(id, publicado) {
    try {
        const response = await fetch(API_URL + '/admin/noticias/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: 'toggle', publicado: publicado == 1 ? 0 : 1 })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarNoticias();
        } else {
            showToast(result.message || 'Error al cambiar estado', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

async function destacarNoticia(id) {
    try {
        const response = await fetch(API_URL + '/admin/noticias/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, action: 'destacar' })
        });
        const result = await response.json();

        if (result.success) {
            showToast('Noticia marcada como destacada', 'success');
            cargarNoticias();
        } else {
            showToast(result.message || 'Error al destacar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

function cerrarModalNoticia() {
    document.getElementById('modalNoticia').style.display = 'none';
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
