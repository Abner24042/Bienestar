
let favoritosEjercicioIds = new Set();

document.addEventListener('DOMContentLoaded', function () {
    loadEjercicios();
    loadFavoritosEjercicioIds();
    initFilters();
    initSearch();
});

async function loadFavoritosEjercicioIds() {
    try {
        const res = await fetch(API_URL + '/favoritos');
        const data = await res.json();
        if (data.success) {
            favoritosEjercicioIds = new Set(data.ejercicio_ids.map(String));
            renderEjerciciosPaginated();
        }
    } catch (e) { }
}

let _exerciseModalCurrentId = null;

async function toggleFavoritoModal(tipo, btn) {
    const id = _exerciseModalCurrentId;
    if (!id) return;
    try {
        const res = await fetch(API_URL + '/favoritos/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo, id }),
        });
        const data = await res.json();
        if (!data.success) return;
        const svg = btn.querySelector('svg polygon');
        const label = btn.querySelector('.modal-fav-label');
        if (data.action === 'added') {
            favoritosEjercicioIds.add(String(id));
            btn.classList.add('fav-active');
            if (svg) svg.setAttribute('fill', 'currentColor');
            if (label) label.textContent = 'Guardado';
        } else {
            favoritosEjercicioIds.delete(String(id));
            btn.classList.remove('fav-active');
            if (svg) svg.setAttribute('fill', 'none');
            if (label) label.textContent = 'Guardar';
        }
    } catch (e) { }
}

let exercisesData = [];
let filteredExercises = [];
let exerciseVisible = 4;

function porFilaEj() {
    const el = document.getElementById('exercisesGrid');
    const w = el ? (el.clientWidth || el.offsetWidth) : (window.innerWidth - 260);
    return Math.max(1, Math.floor((w + 16) / (320 + 16)));
}

async function loadEjercicios() {
    try {
        const response = await fetch(API_URL + '/ejercicios');
        const data = await response.json();

        if (data.success) {
            exercisesData = data.ejercicios || [];
            applyFilters();
        } else {
            document.getElementById('exercisesGrid').innerHTML =
                '<p style="text-align:center;color:#999;grid-column:1/-1;">No hay ejercicios disponibles</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('exercisesGrid').innerHTML =
            '<p style="text-align:center;color:#e53935;grid-column:1/-1;">Error al cargar ejercicios</p>';
    }
}

function applyFilters() {
    const activeBtn = document.querySelector('.filter-btn.active');
    const filter = activeBtn ? activeBtn.dataset.filter : 'all';
    const term = (document.getElementById('searchExercises')?.value || '').toLowerCase().trim();

    filteredExercises = exercisesData.filter(e => {
        const typeOk = filter === 'all' || e.tipo === filter;
        const termOk = !term ||
            (e.titulo || '').toLowerCase().includes(term) ||
            (e.descripcion || '').toLowerCase().includes(term);
        return typeOk && termOk;
    });

    exerciseVisible = porFilaEj();
    renderEjerciciosPaginated();
}

function renderEjerciciosPaginated() {
    const grid = document.getElementById('exercisesGrid');
    const visible = filteredExercises.slice(0, exerciseVisible);

    if (filteredExercises.length === 0) {
        grid.innerHTML = '<p style="text-align:center;color:#999;grid-column:1/-1;">No hay ejercicios disponibles</p>';
        return;
    }

    let html = visible.map((e, i) => renderEjercicioCard(e, i)).join('');

    if (exerciseVisible < filteredExercises.length) {
        const restantes = filteredExercises.length - exerciseVisible;
        html += `<div style="grid-column:1/-1;text-align:center;margin:1.5rem 0 0.5rem;">
            <button class="btn btn-secondary" onclick="mostrarMasEjercicios()" style="min-width:190px;">
                Mostrar más (${restantes} restante${restantes !== 1 ? 's' : ''})
            </button>
        </div>`;
    }

    grid.innerHTML = html;
}

function mostrarMasEjercicios() {
    exerciseVisible += porFilaEj();
    renderEjerciciosPaginated();
}

function renderEjercicioCard(e, idx = 0) {
    const img = e.imagen || 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&q=80';
    const tipo = capitalize(e.tipo || 'cardio');
    const nivel = capitalize(e.nivel || 'principiante');
    const levelClass = 'level-' + (e.nivel || 'principiante');
    const delay = (idx * 0.07).toFixed(2);

    const duracionStat = e.duracion ? `
        <div class="meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2"/></svg>
            <span>${escapeHtml(String(e.duracion))} min</span>
        </div>` : '';

    const caloriasStat = e.calorias_quemadas ? `
        <div class="meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2"/></svg>
            <span>${escapeHtml(String(e.calorias_quemadas))} kcal</span>
        </div>` : '';

    const musculoStat = e.musculo_objetivo ? `
        <div class="meta-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M6.5 6.5h11M6.5 17.5h11M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            <span>${escapeHtml(capitalize(e.musculo_objetivo))}</span>
        </div>` : '';

    const statsSection = (duracionStat || caloriasStat || musculoStat)
        ? `<div class="exercise-stats">${duracionStat}${caloriasStat}${musculoStat}</div>`
        : '';

    return `<div class="exercise-card" data-type="${escapeHtml(e.tipo)}" data-level="${escapeHtml(e.nivel)}"
        tabindex="0" role="button" aria-label="${escapeHtml(e.titulo)}"
        onclick="showExerciseModal(${e.id})" style="cursor:pointer;animation:cardEnter 0.35s ease ${delay}s both;">
        <div class="exercise-image">
            <img src="${escapeHtml(img)}" alt="${escapeHtml(e.titulo)}"
                 loading="lazy" decoding="async"
                 onerror="this.style.display='none'">
            <div class="exercise-badges">
                <span class="badge badge-type">${escapeHtml(tipo)}</span>
                <span class="badge badge-level ${levelClass}">${escapeHtml(nivel)}</span>
            </div>
            <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.52);color:#fff;font-size:0.7rem;text-align:center;padding:5px 0;letter-spacing:0.3px;">
                Dar click para ver detalles
            </div>
        </div>
        <div class="exercise-content">
            <h3>${escapeHtml(e.titulo)}</h3>
            <p class="exercise-description">${escapeHtml(e.descripcion || '')}</p>
            ${statsSection}
        </div>
    </div>`;
}

function showExerciseModal(id) {
    const e = exercisesData.find(item => item.id == id);
    if (!e) return;

    _exerciseModalCurrentId = id;
    const isFav = favoritosEjercicioIds.has(String(id));
    const favBtn = document.getElementById('exerciseModalFavBtn');
    if (favBtn) {
        const svg = favBtn.querySelector('svg polygon');
        const label = favBtn.querySelector('.modal-fav-label');
        favBtn.classList.toggle('fav-active', isFav);
        if (svg) svg.setAttribute('fill', isFav ? 'currentColor' : 'none');
        if (label) label.textContent = isFav ? 'Guardado' : 'Guardar';
    }

    const img = e.imagen || 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&q=80';

    document.getElementById('exerciseModalTitle').textContent = e.titulo;

    let videoSection = '';
    if (e.video_url) {
        const ytMatch = e.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
        if (ytMatch) {
            videoSection = `<div class="exercise-modal-video">
                <iframe width="100%" height="300" src="https://www.youtube.com/embed/${ytMatch[1]}" frameborder="0" allowfullscreen style="border-radius:12px;"></iframe>
            </div>`;
        } else {
            videoSection = `<div class="exercise-modal-video">
                <a href="${escapeHtml(e.video_url)}" target="_blank" class="video-placeholder">
                    <svg width="60" height="60" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>
                    <p>Ver Video</p>
                </a>
            </div>`;
        }
    } else if (e.imagen) {
        videoSection = `<div class="exercise-modal-video" id="exerciseGifWrap">
            <img src="${escapeHtml(e.imagen)}" alt="${escapeHtml(e.titulo)}"
                 style="width:100%;max-height:340px;object-fit:contain;border-radius:12px;background:#111;"
                 onerror="this.parentElement.style.display='none'">
        </div>`;
    }

    const duracionBox = e.duracion ? `<div class="stat-box"><span class="stat-label">Duración</span><span class="stat-value">${escapeHtml(String(e.duracion))} min</span></div>` : '';
    const nivelBox = `<div class="stat-box"><span class="stat-label">Nivel</span><span class="stat-value">${capitalize(e.nivel || 'principiante')}</span></div>`;
    const tipoBox = `<div class="stat-box"><span class="stat-label">Tipo</span><span class="stat-value">${capitalize(e.tipo || 'cardio')}</span></div>`;
    const caloriasBox = e.calorias_quemadas ? `<div class="stat-box"><span class="stat-label">Calorías</span><span class="stat-value">${escapeHtml(String(e.calorias_quemadas))} kcal</span></div>` : '';
    const musculoBox = e.musculo_objetivo ? `<div class="stat-box"><span class="stat-label">Músculo</span><span class="stat-value">${escapeHtml(capitalize(e.musculo_objetivo))}</span></div>` : '';
    const equipoBox = e.equipamiento ? `<div class="stat-box"><span class="stat-label">Equipo</span><span class="stat-value">${escapeHtml(capitalize(e.equipamiento))}</span></div>` : '';

    let secundariosSection = '';
    if (e.musculos_secundarios) {
        const lista = e.musculos_secundarios.split(',').map(s => s.trim()).filter(s => s);
        if (lista.length) {
            const chips = lista.map(s => `<span class="muscle-chip">${escapeHtml(capitalize(s))}</span>`).join('');
            secundariosSection = `
                <div class="modal-detail-block">
                    <h3>Músculos secundarios</h3>
                    <div class="muscle-chips">${chips}</div>
                </div>`;
        }
    }

    document.getElementById('exerciseModalBody').innerHTML = `
        <div class="exercise-modal-content">
            ${videoSection}
            <div class="exercise-modal-info">
                <div class="exercise-modal-stats" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                    ${duracionBox}${nivelBox}${tipoBox}${caloriasBox}${musculoBox}${equipoBox}
                </div>
                ${secundariosSection}
                <h3>Instrucciones</h3>
                <ol class="exercises-list">${formatList(e.instrucciones)}</ol>
            </div>
        </div>
    `;

    document.getElementById('dynamicExerciseModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeExerciseModal() {
    document.getElementById('dynamicExerciseModal').classList.remove('active');
    document.body.style.overflow = '';
}

function formatList(text) {
    if (!text) return '<li>No disponible</li>';
    const items = text.includes('\n') ? text.split('\n') : text.split(',');
    return items.map(item => item.trim()).filter(item => item).map(item => `<li>${escapeHtml(item)}</li>`).join('');
}

function initFilters() {
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });
}

function initSearch() {
    const input = document.getElementById('searchExercises');
    if (!input) return;
    input.addEventListener('input', applyFilters);
}

function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
