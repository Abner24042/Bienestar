/**
 * BIENIESTAR - Página de Favoritos
 */

let favRecetas    = [];
let favEjercicios = [];
let favTabActual  = 'recetas';
let favCatActual  = 'all';
let favTipoActual = 'all';

document.addEventListener('DOMContentLoaded', () => {
    cargarFavoritos();

    // Filtros recetas
    document.querySelectorAll('[data-fav-cat]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-fav-cat]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            favCatActual = btn.dataset.favCat;
            renderFavRecetas();
        });
    });

    // Filtros ejercicios
    document.querySelectorAll('[data-fav-tipo]').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('[data-fav-tipo]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            favTipoActual = btn.dataset.favTipo;
            renderFavEjercicios();
        });
    });
});

async function cargarFavoritos() {
    try {
        const res  = await fetch(API_URL + '/favoritos');
        const data = await res.json();
        if (!data.success) return;

        favRecetas    = data.recetas    || [];
        favEjercicios = data.ejercicios || [];

        renderFavRecetas();
        renderFavEjercicios();

        const cr = document.getElementById('favCountRecetas');
        const ce = document.getElementById('favCountEjercicios');
        if (cr) cr.textContent = favRecetas.length    ? `(${favRecetas.length})`    : '';
        if (ce) ce.textContent = favEjercicios.length ? `(${favEjercicios.length})` : '';
    } catch (e) {}
}

function favCambiarTab(tab, btn) {
    favTabActual = tab;
    document.querySelectorAll('.fav-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const isRecetas    = tab === 'recetas';
    const isEjercicios = tab === 'ejercicios';

    document.getElementById('favGridRecetas').style.display       = isRecetas    ? '' : 'none';
    document.getElementById('favFiltrosRecetas').style.display    = isRecetas    ? '' : 'none';
    document.getElementById('favGridEjercicios').style.display    = isEjercicios ? '' : 'none';
    document.getElementById('favFiltrosEjercicios').style.display = isEjercicios ? '' : 'none';
}

/* ── Recetas ────────────────────────────────────────────────────────── */

function renderFavRecetas() {
    const grid = document.getElementById('favGridRecetas');
    if (!grid) return;

    if (!favRecetas.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#999;padding:3rem 0;">No tienes recetas guardadas aún. Usa la ⭐ en la página de Alimentación.</p>';
        return;
    }

    const filtered = favCatActual === 'all'
        ? favRecetas
        : favRecetas.filter(r => (r.categoria || '').toLowerCase() === favCatActual);

    if (!filtered.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#999;padding:3rem 0;">No tienes recetas en esta categoría.</p>';
        return;
    }

    grid.innerHTML = filtered.map((r, i) => {
        const img      = r.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80';
        const cat      = favCapitalize(r.categoria || 'comida');
        const delay    = (i * 0.07).toFixed(2);
        const deleted  = r._deleted === true;
        const cardStyle = `cursor:pointer;animation:cardEnter 0.35s ease ${delay}s both;${deleted ? 'opacity:0.72;' : ''}`;
        const deletedBadge = deleted
            ? `<span style="position:absolute;top:8px;left:8px;background:rgba(180,0,0,0.82);color:#fff;font-size:0.68rem;font-weight:700;padding:3px 8px;border-radius:20px;letter-spacing:0.04em;">Eliminada</span>`
            : '';

        return `<div class="recipe-card" tabindex="0" role="button" aria-label="${favEsc(r.titulo)}"
            onclick="favShowRecipeModal(${r.id})" style="${cardStyle}">
            <div class="recipe-image" style="position:relative;">
                <img src="${favEsc(img)}" alt="${favEsc(r.titulo)}"
                     onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80'">
                <span class="recipe-badge">${favEsc(cat)}</span>
                ${deletedBadge}
                <button class="fav-btn fav-active" title="Quitar de favoritos"
                        onclick="event.stopPropagation(); favQuitarReceta(${r.id}, this, ${deleted})">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </button>
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.52);color:#fff;font-size:0.7rem;text-align:center;padding:5px 0;letter-spacing:0.3px;">
                    Dar click para ver detalles
                </div>
            </div>
            <div class="recipe-content">
                <h3>${favEsc(r.titulo)}</h3>
                <div class="recipe-meta">
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        ${r.tiempo_preparacion ? r.tiempo_preparacion + ' min' : '—'}
                    </div>
                    <div class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Z"/></svg>
                        ${r.porciones ? r.porciones + ' porciones' : '—'}
                    </div>
                </div>
            </div>
        </div>`;
    }).join('');
}

async function favQuitarReceta(id, btn, isDeleted) {
    if (isDeleted) {
        const ok = confirm('Esta receta ya no está en la base de datos. Si la quita de favoritos ya no la podrá recuperar. ¿Seguro que la quiere quitar?');
        if (!ok) return;
    }
    await toggleFavoritoPage('receta', id, btn);
    favRecetas = favRecetas.filter(r => r.id != id);
    renderFavRecetas();
    const cr = document.getElementById('favCountRecetas');
    if (cr) cr.textContent = favRecetas.length ? `(${favRecetas.length})` : '';
}

function favShowRecipeModal(id) {
    const r = favRecetas.find(x => x.id == id);
    if (!r) return;
    const img = r.imagen || 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80';

    const nutBar = (r.proteinas || r.carbohidratos || r.grasas || r.calorias) ? `
        <div class="recipe-modal-nutbar">
            ${r.calorias      ? `<div class="nutbar-item nutbar-kcal"><span class="nutbar-val">${Math.round(r.calorias)}</span><span class="nutbar-lbl">kcal</span></div>` : ''}
            ${r.proteinas     ? `<div class="nutbar-item nutbar-prot"><span class="nutbar-val">${r.proteinas}g</span><span class="nutbar-lbl">Proteínas</span></div>` : ''}
            ${r.carbohidratos ? `<div class="nutbar-item nutbar-carbs"><span class="nutbar-val">${r.carbohidratos}g</span><span class="nutbar-lbl">Carbos</span></div>` : ''}
            ${r.grasas        ? `<div class="nutbar-item nutbar-fat"><span class="nutbar-val">${r.grasas}g</span><span class="nutbar-lbl">Grasas</span></div>` : ''}
        </div>` : '';

    document.getElementById('recipeModalTitle').textContent = r.titulo;
    document.getElementById('recipeModalBody').innerHTML = `
        <div class="recipe-modal-content">
            <div class="recipe-modal-top">
                <div class="recipe-modal-image">
                    <img src="${favEsc(img)}" alt="${favEsc(r.titulo)}" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=600&q=80'">
                </div>
                <div class="recipe-modal-side">
                    <div class="stat-box"><span class="stat-label">⏱ Tiempo</span><span class="stat-value">${r.tiempo_preparacion ? r.tiempo_preparacion + ' min' : '—'}</span></div>
                    <div class="stat-box"><span class="stat-label">🍽 Porciones</span><span class="stat-value">${r.porciones || 1}</span></div>
                </div>
            </div>
            ${nutBar}
            <div class="recipe-modal-detail">
                <h3>Ingredientes:</h3>
                <ul class="ingredients-list">${favFormatList(r.ingredientes)}</ul>
                ${r.instrucciones ? `<h3>Preparación:</h3><ol class="instructions-list">${favFormatList(r.instrucciones)}</ol>` : ''}
                ${r.url_fuente ? `<a href="${favEsc(r.url_fuente)}" target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:1rem;color:#ff6b35;font-weight:600;text-decoration:none;">Ver receta completa →</a>` : ''}
            </div>
        </div>`;

    document.getElementById('dynamicRecipeModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeFavRecipeModal() {
    document.getElementById('dynamicRecipeModal').classList.remove('active');
    document.body.style.overflow = '';
}

/* ── Ejercicios ─────────────────────────────────────────────────────── */

function renderFavEjercicios() {
    const grid = document.getElementById('favGridEjercicios');
    if (!grid) return;

    if (!favEjercicios.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#999;padding:3rem 0;">No tienes ejercicios guardados aún. Usa la ⭐ en la página de Ejercicio.</p>';
        return;
    }

    const filtered = favTipoActual === 'all'
        ? favEjercicios
        : favEjercicios.filter(e => (e.tipo || '').toLowerCase() === favTipoActual);

    if (!filtered.length) {
        grid.innerHTML = '<p style="grid-column:1/-1;text-align:center;color:#999;padding:3rem 0;">No tienes ejercicios de este tipo.</p>';
        return;
    }

    grid.innerHTML = filtered.map((e, i) => {
        const img        = e.imagen || 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&q=80';
        const tipo       = favCapitalize(e.tipo || 'cardio');
        const nivel      = favCapitalize(e.nivel || 'principiante');
        const levelClass = 'level-' + (e.nivel || 'principiante');
        const delay      = (i * 0.07).toFixed(2);

        const duracionStat = e.duracion ? `<div class="meta-item"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><path d="M12 6V12L16 14" stroke="currentColor" stroke-width="2"/></svg><span>${e.duracion} min</span></div>` : '';
        const caloriasStat = e.calorias_quemadas ? `<div class="meta-item"><svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M13 2L3 14H12L11 22L21 10H12L13 2Z" stroke="currentColor" stroke-width="2"/></svg><span>${e.calorias_quemadas} kcal</span></div>` : '';
        const statsSection = (duracionStat || caloriasStat) ? `<div class="exercise-stats">${duracionStat}${caloriasStat}</div>` : '';

        return `<div class="exercise-card" data-type="${favEsc(e.tipo)}" data-level="${favEsc(e.nivel)}"
            tabindex="0" role="button" aria-label="${favEsc(e.titulo)}"
            onclick="favShowExerciseModal(${e.id})" style="cursor:pointer;animation:cardEnter 0.35s ease ${delay}s both;">
            <div class="exercise-image" style="position:relative;">
                <img src="${favEsc(img)}" alt="${favEsc(e.titulo)}" loading="lazy" onerror="this.style.display='none'">
                <div class="exercise-badges">
                    <span class="badge badge-type">${favEsc(tipo)}</span>
                    <span class="badge badge-level ${levelClass}">${favEsc(nivel)}</span>
                </div>
                <button class="fav-btn fav-active" title="Quitar de favoritos"
                        onclick="event.stopPropagation(); favQuitarEjercicio(${e.id}, this)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                </button>
                <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.52);color:#fff;font-size:0.7rem;text-align:center;padding:5px 0;letter-spacing:0.3px;">
                    Dar click para ver detalles
                </div>
            </div>
            <div class="exercise-content">
                <h3>${favEsc(e.titulo)}</h3>
                <p class="exercise-description">${favEsc(e.descripcion || '')}</p>
                ${statsSection}
            </div>
        </div>`;
    }).join('');
}

async function favQuitarEjercicio(id, btn) {
    await toggleFavoritoPage('ejercicio', id, btn);
    favEjercicios = favEjercicios.filter(e => e.id != id);
    renderFavEjercicios();
    const ce = document.getElementById('favCountEjercicios');
    if (ce) ce.textContent = favEjercicios.length ? `(${favEjercicios.length})` : '';
}

function favShowExerciseModal(id) {
    const e = favEjercicios.find(x => x.id == id);
    if (!e) return;
    const img = e.imagen || 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=600&q=80';

    let videoSection = '';
    if (e.video_url) {
        const ytMatch = e.video_url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&]+)/);
        videoSection = ytMatch
            ? `<div class="exercise-modal-video"><iframe width="100%" height="300" src="https://www.youtube.com/embed/${ytMatch[1]}" frameborder="0" allowfullscreen style="border-radius:12px;"></iframe></div>`
            : `<div class="exercise-modal-video"><a href="${favEsc(e.video_url)}" target="_blank" class="video-placeholder"><svg width="60" height="60" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg><p>Ver Video</p></a></div>`;
    } else if (e.imagen) {
        videoSection = `<div class="exercise-modal-video"><img src="${favEsc(e.imagen)}" alt="${favEsc(e.titulo)}" style="width:100%;max-height:340px;object-fit:contain;border-radius:12px;background:#111;" onerror="this.parentElement.style.display='none'"></div>`;
    }

    document.getElementById('exerciseModalTitle').textContent = e.titulo;
    document.getElementById('exerciseModalBody').innerHTML = `
        <div class="exercise-modal-content">
            ${videoSection}
            <div class="exercise-modal-info">
                <div class="exercise-modal-stats" style="grid-template-columns:repeat(auto-fill,minmax(120px,1fr));">
                    ${e.duracion         ? `<div class="stat-box"><span class="stat-label">Duración</span><span class="stat-value">${e.duracion} min</span></div>` : ''}
                    <div class="stat-box"><span class="stat-label">Nivel</span><span class="stat-value">${favCapitalize(e.nivel || 'principiante')}</span></div>
                    <div class="stat-box"><span class="stat-label">Tipo</span><span class="stat-value">${favCapitalize(e.tipo || 'cardio')}</span></div>
                    ${e.calorias_quemadas ? `<div class="stat-box"><span class="stat-label">Calorías</span><span class="stat-value">${e.calorias_quemadas} kcal</span></div>` : ''}
                    ${e.musculo_objetivo  ? `<div class="stat-box"><span class="stat-label">Músculo</span><span class="stat-value">${favCapitalize(e.musculo_objetivo)}</span></div>` : ''}
                </div>
                <h3>Instrucciones</h3>
                <ol class="exercises-list">${favFormatList(e.instrucciones)}</ol>
            </div>
        </div>`;

    document.getElementById('dynamicExerciseModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeFavExerciseModal() {
    document.getElementById('dynamicExerciseModal').classList.remove('active');
    document.body.style.overflow = '';
}

/* ── Toggle favorito desde esta página ─────────────────────────────── */

async function toggleFavoritoPage(tipo, id, btn) {
    try {
        await fetch(API_URL + '/favoritos/toggle', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ tipo, id }),
        });
    } catch (e) {}
}

/* ── Helpers ────────────────────────────────────────────────────────── */

function favEsc(text) {
    if (text === null || text === undefined) return '';
    const d = document.createElement('div');
    d.textContent = String(text);
    return d.innerHTML;
}

function favCapitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function favFormatList(text) {
    if (!text) return '<li>No disponible</li>';
    const items = text.includes('\n') ? text.split('\n') : text.split(',');
    return items.map(i => i.trim()).filter(i => i).map(i => `<li>${favEsc(i)}</li>`).join('');
}

/* Cerrar modales con Escape o click fuera */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        closeFavRecipeModal();
        closeFavExerciseModal();
    }
});
document.getElementById('dynamicRecipeModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeFavRecipeModal();
});
document.getElementById('dynamicExerciseModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeFavExerciseModal();
});
