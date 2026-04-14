<?php
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();
$currentPage = 'citas';
$pageTitle = 'Mis Citas';
$additionalCSS = ['citas.css'];
?>

<?php include '../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Mis Citas <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg></h1>
        <p>Consulta las citas agendadas por tu especialista</p>
        <?php if ($user['rol'] === 'usuario'): ?>
        <button class="btn btn-primary" data-modal-open="modalSolicitarCita" style="margin-top:12px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Solicitar Cita
        </button>
        <?php endif; ?>
    </div>

    <?php if ($user['rol'] === 'usuario'): ?>
    <!-- Mis solicitudes -->
    <div id="misSolicitudesSection" style="margin-bottom:2rem;display:none;">
        <h3 style="margin-bottom:0.75rem;font-size:1rem;color:var(--color-text-secondary);">📋 Mis Solicitudes</h3>
        <div id="misSolicitudesList" style="display:flex;flex-direction:column;gap:0.75rem;"></div>
    </div>
    <?php endif; ?>

    <div class="appointment-container">
        <!-- Izquierda: Calendario -->
        <div class="calendar-container">
            <div class="calendar-header">
                <h3><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:6px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Calendario de Citas</h3>
                <div class="calendar-controls">
                    <button type="button" id="prevMonth">‹</button>
                    <span class="calendar-month-year" id="currentMonthYear"></span>
                    <button type="button" id="nextMonth">›</button>
                </div>
            </div>
            <div id="calendar"></div>
        </div>

        <!-- Derecha: Detalle día + Lista de citas -->
        <div class="citas-panel">
            <div id="dayDetail" class="appointments-list" style="display: none;">
                <h3 id="dayDetailTitle">Citas del día</h3>
                <div id="dayDetailContent"></div>
            </div>
            <div class="appointments-list">
                <h3>Mis Citas Programadas</h3>
                <div id="appointmentsList"></div>
            </div>
        </div>
    </div>
</div>

<script>
const CURRENT_USER = {
    nombre: '<?php echo addslashes($user['nombre']); ?>',
    correo: '<?php echo addslashes($user['correo']); ?>',
    rol: '<?php echo addslashes($user['rol']); ?>'
};
</script>

<?php if ($user['rol'] === 'usuario'): ?>
<?php
$modalId = 'modalSolicitarCita';
$modalTitle = 'Solicitar Cita con Especialista';
$modalSize = 'medium';
$modalContent = '
<form id="formSolicitarCita">
    <div class="form-group">
        <label for="sol_tipo">Tipo de consulta <span style="color:#e55a00">*</span></label>
        <select id="sol_tipo" name="tipo" required onchange="cargarEspecialistas(this.value)">
            <option value="">Selecciona una opción</option>
            <option value="Nutrición">Nutrición</option>
            <option value="Ejercicio / Coach">Ejercicio / Coach</option>
            <option value="Psicología">Psicología</option>
        </select>
    </div>
    <div class="form-group" id="grupoespecialista" style="display:none;">
        <label for="sol_especialista">Especialista <span style="color:#e55a00">*</span></label>
        <select id="sol_especialista" name="especialista" required>
            <option value="">Cargando...</option>
        </select>
    </div>
    <div class="form-group">
        <label for="sol_fecha">Fecha preferida <span style="color:#e55a00">*</span></label>
        <input type="date" id="sol_fecha" name="fecha" required min="' . date('Y-m-d', strtotime('+1 day')) . '">
    </div>
    <div class="form-group">
        <label for="sol_motivo">Motivo de la consulta</label>
        <textarea id="sol_motivo" name="motivo" rows="3" placeholder="Describe brevemente el motivo de tu consulta..."></textarea>
    </div>
    <div id="solicitarMsg" style="display:none;margin-bottom:12px;"></div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-modal-close="modalSolicitarCita">Cancelar</button>
        <button type="submit" class="btn btn-primary" id="btnEnviarSolicitud">Enviar Solicitud</button>
    </div>
</form>
<script>
async function cargarEspecialistas(tipo) {
    const grupo = document.getElementById("grupoespecialista");
    const sel   = document.getElementById("sol_especialista");
    if (!tipo) { grupo.style.display = "none"; sel.required = false; return; }
    grupo.style.display = "";
    sel.required = true;
    sel.innerHTML = "<option value=\\"\\">Cargando especialistas...</option>";
    try {
        const res  = await fetch(API_URL + "/especialistas?tipo=" + encodeURIComponent(tipo));
        const data = await res.json();
        if (data.success && data.especialistas.length) {
            sel.innerHTML = "<option value=\\"\\">Selecciona un especialista</option>" +
                data.especialistas.map(e =>
                    `<option value="${e.correo}">${e.nombre}${e.area ? " — " + e.area : ""}</option>`
                ).join("");
        } else {
            sel.innerHTML = "<option value=\\"\\">No hay especialistas disponibles</option>";
        }
    } catch(err) {
        sel.innerHTML = "<option value=\\"\\">Error al cargar</option>";
    }
}

document.getElementById("formSolicitarCita").addEventListener("submit", async function(e) {
    e.preventDefault();
    const btn  = document.getElementById("btnEnviarSolicitud");
    const msg  = document.getElementById("solicitarMsg");
    const tipo = document.getElementById("sol_tipo").value;
    const esp  = document.getElementById("sol_especialista").value;
    const fecha= document.getElementById("sol_fecha").value;
    const mot  = document.getElementById("sol_motivo").value;

    if (!esp) {
        msg.style.cssText = "display:block;padding:10px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:12px;";
        msg.textContent   = "Selecciona un especialista.";
        return;
    }
    btn.disabled = true; btn.textContent = "Enviando...";
    try {
        const res  = await fetch(API_URL + "/appointments/request", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tipo, fecha, motivo: mot, especialista: esp })
        });
        const data = await res.json();
        msg.style.display = "";
        if (data.success) {
            msg.style.cssText = "display:block;padding:10px;background:#e8f5e9;color:#2e7d32;border-radius:6px;margin-bottom:12px;";
            msg.textContent   = "✓ Solicitud enviada. El especialista la revisará pronto.";
            setTimeout(() => {
                document.querySelector("[data-modal-close=modalSolicitarCita]").click();
                if (typeof cargarMisSolicitudes === "function") cargarMisSolicitudes();
            }, 1800);
        } else {
            msg.style.cssText = "display:block;padding:10px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:12px;";
            msg.textContent   = data.message || "Error al enviar la solicitud.";
            btn.disabled = false; btn.textContent = "Enviar Solicitud";
        }
    } catch(err) {
        msg.style.cssText = "display:block;padding:10px;background:#ffebee;color:#c62828;border-radius:6px;margin-bottom:12px;";
        msg.textContent   = "Error de conexión. Intenta de nuevo.";
        btn.disabled = false; btn.textContent = "Enviar Solicitud";
    }
});
</script>
';
include '../../app/views/components/modal.php';
?>

<script>
const ESTADO_LABELS = {
    pendiente : { txt: '⏳ Pendiente',   color: '#f59e0b' },
    aceptada  : { txt: '✅ Aceptada',    color: '#16a34a' },
    denegada  : { txt: '❌ Denegada',    color: '#dc2626' },
    reasignada: { txt: '🔄 Reasignada', color: '#7c3aed' },
};

// IDs de solicitudes que el usuario ya vio y descartó
function getSolVistas() {
    try { return JSON.parse(localStorage.getItem('sol_vistas') || '[]'); } catch(e) { return []; }
}
function marcarSolVista(id) {
    const vistas = getSolVistas();
    const sid = String(id);
    if (!vistas.includes(sid)) { vistas.push(sid); localStorage.setItem('sol_vistas', JSON.stringify(vistas)); }
}
function descartarSolicitud(id, esAceptada = false) {
    marcarSolVista(id);
    // Las aceptadas son citas reales: solo descartar en localStorage, no cambiar el backend
    if (!esAceptada) {
        fetch(API_URL + '/mis-solicitudes/vista', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: +id })
        }).catch(() => {});
    }
    const el = document.getElementById('sol-usr-' + id);
    if (el) { el.style.transition = 'opacity .3s'; el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
    setTimeout(() => {
        if (!document.querySelector('[id^="sol-usr-"]')) {
            document.getElementById('misSolicitudesSection').style.display = 'none';
        }
    }, 350);
}

async function cargarMisSolicitudes() {
    try {
        const res  = await fetch(API_URL + '/mis-solicitudes');
        const data = await res.json();
        if (!data.success) return;

        const vistas  = getSolVistas();
        const visibles = (data.solicitudes || []).filter(s =>
            s.sol_estado === 'pendiente' || !vistas.includes(String(s.id))
        );

        const section = document.getElementById('misSolicitudesSection');
        const list    = document.getElementById('misSolicitudesList');

        if (!visibles.length) { section.style.display = 'none'; return; }
        section.style.display = '';

        list.innerHTML = visibles.map(s => {
            const est   = ESTADO_LABELS[s.sol_estado] || { txt: s.sol_estado, color: '#999' };
            const fecha = s.fecha ? new Date(s.fecha + 'T00:00:00').toLocaleDateString('es-MX', { day:'2-digit', month:'short', year:'numeric' }) : '—';
            let extra = '';

            if (s.sol_estado === 'aceptada') {
                extra = `<p style="font-size:0.82rem;color:#16a34a;margin:4px 0 0;">Tu cita fue confirmada para el ${fecha}.</p>`;
            }
            if (s.sol_estado === 'denegada') {
                extra = s.sol_motivo ? `<p style="font-size:0.82rem;color:#dc2626;margin:4px 0 0;">Motivo: ${esc(s.sol_motivo)}</p>` : '';
            }
            if (s.sol_estado === 'reasignada') {
                extra = `<p style="font-size:0.82rem;color:#7c3aed;margin:4px 0 0;">Reasignada a <strong>${esc(s.nombre_reasignado || s.sol_reasignado)}</strong></p>` +
                        (s.sol_motivo ? `<p style="font-size:0.8rem;color:var(--color-text-light);margin:2px 0 0;">Motivo: ${esc(s.sol_motivo)}</p>` : '') +
                        `<div style="display:flex;gap:8px;margin-top:8px;">
                            <button onclick="aceptarReasignacion(${s.id})" class="btn btn-primary" style="padding:5px 14px;font-size:0.8rem;">Aceptar nuevo especialista</button>
                            <button onclick="rechazarReasignacion(${s.id})" class="btn btn-secondary" style="padding:5px 14px;font-size:0.8rem;">No, busco otro</button>
                         </div>`;
            }

            return `<div id="sol-usr-${s.id}" style="padding:12px 16px;border-radius:10px;border:1px solid var(--color-border,#e8e8e8);background:var(--color-bg-primary,#fff);">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
                    <div>
                        <span style="font-weight:600;font-size:0.92rem;">${esc(s.sol_tipo)}</span>
                        <span style="margin-left:8px;font-size:0.8rem;color:var(--color-text-light);">con ${esc(s.nombre_profesional || s.sol_profesional)}</span>
                        <span style="margin-left:8px;font-size:0.78rem;color:var(--color-text-light);">${fecha}</span>
                    </div>
                    <span style="font-size:0.82rem;font-weight:600;color:${est.color};">${est.txt}</span>
                </div>
                ${extra}
            </div>`;
        }).join('');

        // Auto-dismiss aceptadas y denegadas después de 4 segundos de verlas
        visibles.forEach(s => {
            if (s.sol_estado === 'aceptada') {
                setTimeout(() => descartarSolicitud(s.id, true), 4000);   // solo localStorage
            } else if (s.sol_estado === 'denegada') {
                setTimeout(() => descartarSolicitud(s.id, false), 4000);  // también backend
            }
        });
    } catch(e) {}
}

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

async function aceptarReasignacion(id) {
    // El usuario acepta: creamos una nueva solicitud con el reasignado como especialista
    // Obtenemos los datos de la solicitud original para rellenar la nueva
    try {
        const res  = await fetch(API_URL + '/mis-solicitudes');
        const data = await res.json();
        const sol  = data.solicitudes.find(s => s.id == id);
        if (!sol || !sol.sol_reasignado) return;

        const res2 = await fetch(API_URL + '/appointments/request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                tipo        : sol.sol_tipo,
                fecha       : sol.fecha,
                motivo      : sol.descripcion || '',
                especialista: sol.sol_reasignado,
            })
        });
        const d2 = await res2.json();
        if (d2.success) {
            await fetch(API_URL + '/appointments/cancel', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            descartarSolicitud(id);
            cargarMisSolicitudes();
        }
    } catch(e) {}
}

async function rechazarReasignacion(id) {
    try {
        await fetch(API_URL + '/appointments/cancel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        descartarSolicitud(id);
        cargarMisSolicitudes();
    } catch(e) {}
}

document.addEventListener('DOMContentLoaded', cargarMisSolicitudes);
</script>
<?php endif; ?>

<?php
$additionalJS = ['emailConfig.js', 'citas.js'];
include '../../app/views/layouts/footer.php';
?>
