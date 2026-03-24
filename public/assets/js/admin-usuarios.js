// Cargar usuarios al iniciar
document.addEventListener('DOMContentLoaded', function() {
    cargarUsuarios();

    // Botón nuevo usuario
    document.getElementById('btnNuevoUsuario').addEventListener('click', function() {
        document.getElementById('modalUsuarioTitle').textContent = 'Nuevo Usuario';
        document.getElementById('formUsuario').reset();
        document.getElementById('usuario_id').value = '';
        document.getElementById('passwordGroup').querySelector('small').style.display = 'none';
        document.getElementById('modalUsuario').style.display = 'flex';
    });

    // Form submit
    document.getElementById('formUsuario').addEventListener('submit', function(e) {
        e.preventDefault();
        guardarUsuario();
    });
});

async function cargarUsuarios() {
    try {
        const response = await fetch(API_URL + '/admin/users');
        const data = await response.json();
        const tbody = document.getElementById('usuariosTableBody');

        if (data.success && data.users.length > 0) {
            tbody.innerHTML = data.users.map(user => `
                <tr>
                    <td class="td-id">#${user.id}</td>
                    <td>
                        <div class="td-title">${user.nombre}</div>
                        <div class="td-sub">${user.correo}</div>
                    </td>
                    <td><span class="rol-badge" style="background:${getRolColor(user.rol)};">${getRolLabel(user.rol)}</span></td>
                    <td class="td-sub">${user.fecha || '—'}</td>
                    <td>
                        <div class="action-btns">
                            <button class="btn btn-secondary btn-sm"
                                    onclick="editarUsuario(${user.id}, '${escapar(user.nombre)}', '${escapar(user.correo)}', '${user.rol}', '${escapar(user.area || '')}')">Editar</button>
                        </div>
                    </td>
                </tr>
            `).join('');
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="empty-message">No hay usuarios</td></tr>';
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function getRolColor(rol) {
    const colors = {
        'usuario': '#4285F4',
        'Administrador': '#EA4335',
        'coach': '#34A853',
        'nutriologo': '#FBBC04',
        'psicologo': '#9C27B0'
    };
    return colors[rol] || '#999';
}

function getRolLabel(rol) {
    const labels = {
        'usuario': 'Usuario',
        'Administrador': 'Admin',
        'coach': 'Coach',
        'nutriologo': 'Nutriologo',
        'psicologo': 'Psicologo'
    };
    return labels[rol] || rol;
}

function escapar(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

function editarUsuario(id, nombre, correo, rol, area) {
    document.getElementById('modalUsuarioTitle').textContent = 'Editar Usuario';
    document.getElementById('usuario_id').value = id;
    document.getElementById('usuario_nombre').value = nombre;
    document.getElementById('usuario_correo').value = correo;
    document.getElementById('usuario_rol').value = rol;
    document.getElementById('usuario_area').value = area;
    document.getElementById('usuario_password').value = '';
    document.getElementById('passwordGroup').querySelector('small').style.display = 'block';
    document.getElementById('modalUsuario').style.display = 'flex';
}

async function guardarUsuario() {
    const data = {
        id: document.getElementById('usuario_id').value || null,
        nombre: document.getElementById('usuario_nombre').value,
        correo: document.getElementById('usuario_correo').value,
        rol: document.getElementById('usuario_rol').value,
        area: document.getElementById('usuario_area').value,
        password: document.getElementById('usuario_password').value || null
    };

    try {
        const response = await fetch(API_URL + '/admin/users/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const result = await response.json();

        if (result.success) {
            showToast(result.message, 'success');
            cerrarModalUsuario();
            cargarUsuarios();
        } else {
            showToast(result.message || 'Error al guardar', 'error');
        }
    } catch (error) {
        showToast('Error de comunicacion', 'error');
    }
}

function cerrarModalUsuario() {
    document.getElementById('modalUsuario').style.display = 'none';
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
