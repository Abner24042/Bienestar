
let proNoticiasData = [];

document.addEventListener('DOMContentLoaded', function () {
    cargarProNoticias();

    document.getElementById('btnNuevaNoticiaPro').addEventListener('click', function () {
        document.getElementById('modalNoticiaProTitle').textContent = 'Nueva Publicación';
        document.getElementById('formNoticiaPro').reset();
        document.getElementById('pro_noticia_id').value = '';
        document.getElementById('modalNoticiaPro').style.display = 'flex';
    });

    document.getElementById('formNoticiaPro').addEventListener('submit', function (e) {
        e.preventDefault();
        guardarProNoticia();
    });
});

async function cargarProNoticias() {
    try {
        const response = await fetch(API_URL + '/pro/noticias');
        const data = await response.json();
        const tbody = document.getElementById('proNoticiasBody');

        if (data.success && data.noticias.length > 0) {
            proNoticiasData = data.noticias;
            tbody.innerHTML = data.noticias.map(n => `
                <tr>
                    <td>${esc(n.titulo)}</td>
                    <td>${getCatLabel(n.categoria)}</td>
                    <td><span style="color:${n.publicado == 1 ? '#34A853' : '#999'};font-weight:600;">${n.publicado == 1 ? 'Sí' : 'No'}</span></td>
                    <td>${formatDate(n.fecha_publicacion)}</td>
                    <td style="display:flex;gap:0.4rem;">
                        <button class="btn btn-secondary btn-sm" onclick="editarProNoticia(${n.id})">Editar</button>
                        <button class="btn btn-sm" style="background:#F44336;color:white;" onclick="eliminarProNoticia(${n.id})">Eliminar</button>
                    </td>
                </tr>
            `).join('');
        } else {
            proNoticiasData = [];
            tbody.innerHTML = '<tr><td colspan="5" class="empty-message">No tienes publicaciones aún. ¡Crea tu primera!</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function editarProNoticia(id) {
    const n = proNoticiasData.find(item => item.id == id);
    if (!n) return;

    document.getElementById('modalNoticiaProTitle').textContent = 'Editar Publicación';
    document.getElementById('pro_noticia_id').value = n.id;
    document.getElementById('pro_noticia_titulo').value = n.titulo || '';
    document.getElementById('pro_noticia_resumen').value = n.resumen || '';
    document.getElementById('pro_noticia_contenido').value = n.contenido || '';
    document.getElementById('pro_noticia_categoria').value = n.categoria || 'general';
    document.getElementById('pro_noticia_publicado').checked = n.publicado == 1;
    document.getElementById('modalNoticiaPro').style.display = 'flex';
}

async function guardarProNoticia() {
    const form = document.getElementById('formNoticiaPro');
    const formData = new FormData(form);

    // Handle checkbox
    if (!document.getElementById('pro_noticia_publicado').checked) {
        formData.set('publicado', '0');
    } else {
        formData.set('publicado', '1');
    }

    try {
        const response = await fetch(API_URL + '/pro/noticias/save', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            document.getElementById('modalNoticiaPro').style.display = 'none';
            cargarProNoticias();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

async function eliminarProNoticia(id) {
    if (!confirm('¿Estás seguro de que quieres eliminar esta publicación?')) return;

    try {
        const response = await fetch(API_URL + '/pro/noticias/delete', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id })
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cargarProNoticias();
        } else {
            showToast(result.message || 'Error al eliminar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicación', 'error');
    }
}

function getCatLabel(cat) {
    const labels = {
        'alimentacion': 'Alimentación',
        'ejercicio': 'Ejercicio',
        'salud-mental': 'Salud Mental',
        'general': 'General'
    };
    return labels[cat] || cat || 'General';
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.getDate() + '/' + (d.getMonth() + 1) + '/' + d.getFullYear();
    } catch (e) {
        return dateStr;
    }
}

function esc(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
