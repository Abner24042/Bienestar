
document.addEventListener('DOMContentLoaded', function () {
    insertRefreshBar();
    loadNoticias();
    initNewsFilters();
});

let newsData = [];
let filteredNews = [];
let newsVisible = 3;

const NEWS_CACHE_KEY = 'noticias';
const NEWS_TTL = 20 * 60 * 1000; // 20 minutos - las noticias no cambian tan seguido

// inserta la barra de estado del cache y el boton de actualizar debajo de los filtros
function insertRefreshBar() {
    const filters = document.querySelector('.news-filters');
    if (!filters) return;

    const bar = document.createElement('div');
    bar.id = 'newsCacheBar';
    bar.style.cssText = 'display:flex;align-items:center;gap:10px;margin:10px 0 4px;font-size:0.8rem;color:#999;min-height:28px;';
    bar.innerHTML = '<span id="newsCacheInfo"></span>';
    filters.insertAdjacentElement('afterend', bar);
}

// actualiza el texto de la barra (ej: "Actualizado hace 5 min · Actualizar")
function updateCacheBar(fromCache) {
    const info = document.getElementById('newsCacheInfo');
    if (!info) return;

    const age = AppCache.ageMinutes(NEWS_CACHE_KEY);
    const ageText = age !== null ? `Actualizado hace ${age === 0 ? 'menos de 1' : age} min` : 'Recién cargado';
    const origen = fromCache ? '(desde caché)' : '(actualizado)';

    info.innerHTML = `${ageText} ${origen} · <button onclick="refrescarNoticias()" style="background:none;border:none;color:#ff6b35;cursor:pointer;font-size:0.8rem;padding:0;font-weight:600;">↻ Actualizar</button>`;
}

// fuerza un refetch borrando el cache
function refrescarNoticias() {
    AppCache.clear(NEWS_CACHE_KEY);
    const grid = document.getElementById('newsGrid');
    if (grid) grid.innerHTML = '<p style="text-align:center;color:#999;grid-column:1/-1;">Actualizando noticias...</p>';
    const featured = document.getElementById('featuredNews');
    if (featured) featured.style.display = 'none';
    loadNoticias();
}

function porFilaN() {
    const el = document.getElementById('newsGrid');
    const w = el ? (el.clientWidth || el.offsetWidth) : (window.innerWidth - 260);
    return Math.max(1, Math.floor((w + 16) / (320 + 16)));
}

async function loadNoticias() {
    // primero intenta usar el cache para mostrar algo de inmediato
    const cached = AppCache.get(NEWS_CACHE_KEY);
    if (cached) {
        newsData = cached.data;
        if (newsData.length > 0) {
            renderFeatured(newsData[0]);
            applyNewsFilter();
        }
        updateCacheBar(true);
        return; // cache vigente, no hace fetch
    }

    // no hay cache o expiro, ir al servidor
    try {
        const response = await fetch(API_URL + '/noticias');
        const data = await response.json();

        if (data.success) {
            newsData = data.noticias || [];
            AppCache.set(NEWS_CACHE_KEY, newsData, NEWS_TTL);

            if (newsData.length > 0) {
                renderFeatured(newsData[0]);
                applyNewsFilter();
            } else {
                document.getElementById('newsGrid').innerHTML =
                    '<p style="text-align:center;color:#999;grid-column:1/-1;">No hay noticias disponibles</p>';
            }
            updateCacheBar(false);
        } else {
            document.getElementById('newsGrid').innerHTML =
                '<p style="text-align:center;color:#999;grid-column:1/-1;">No hay noticias disponibles</p>';
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('newsGrid').innerHTML =
            '<p style="text-align:center;color:#e53935;grid-column:1/-1;">Error al cargar noticias</p>';
    }
}

function applyNewsFilter() {
    const activeBtn = document.querySelector('.news-filters .filter-btn.active');
    const filter = activeBtn ? activeBtn.dataset.filter : 'all';

    // El primer item es el destacado, no va en el grid
    const pool = newsData.slice(1);
    filteredNews = filter === 'all' ? pool : pool.filter(n => n.categoria === filter);

    newsVisible = porFilaN();
    renderNoticiasPaginated();
}

function renderNoticiasPaginated() {
    const grid = document.getElementById('newsGrid');
    const visible = filteredNews.slice(0, newsVisible);

    if (filteredNews.length === 0) {
        grid.innerHTML = '';
        return;
    }

    let html = visible.map((n, i) => renderNoticiaCard(n, i)).join('');

    if (newsVisible < filteredNews.length) {
        const restantes = filteredNews.length - newsVisible;
        html += `<div style="grid-column:1/-1;text-align:center;margin:1.5rem 0 0.5rem;">
            <button class="btn btn-secondary" onclick="mostrarMasNoticias()" style="min-width:190px;">
                Mostrar más (${restantes} restante${restantes !== 1 ? 's' : ''})
            </button>
        </div>`;
    }

    grid.innerHTML = html;
}

function mostrarMasNoticias() {
    newsVisible += porFilaN();
    renderNoticiasPaginated();
}

function renderNoticiaCard(n, idx = 0) {
    const img = n.imagen || 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&q=80';
    const cat = capitalize(n.categoria || 'general');
    const delay = (idx * 0.08).toFixed(2);
    return `<article class="news-card" data-category="${escapeHtml(n.categoria)}"
        tabindex="0" aria-label="${escapeHtml(n.titulo)}"
        onclick="showNewsModal(${n.id})" style="cursor:pointer;animation:cardEnter 0.35s ease ${delay}s both;">
        <div class="news-image" style="position:relative;">
            <img src="${escapeHtml(img)}" alt="${escapeHtml(n.titulo)}" loading="lazy"
                 onerror="this.src='https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&q=80'">
            <div class="news-category-badge">${escapeHtml(cat)}</div>
            <div style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.52);color:#fff;font-size:0.7rem;text-align:center;padding:5px 0;letter-spacing:0.3px;">
                Dar click para ver detalles
            </div>
        </div>
        <div class="news-content">
            <div class="news-meta">
                <span class="news-author">${n.autor ? 'Por: ' + escapeHtml(n.autor) : ''}</span>
                <span class="news-date">${formatDate(n.fecha_publicacion)}</span>
            </div>
            <h3>${escapeHtml(n.titulo)}</h3>
            <p>${escapeHtml(n.resumen || truncate(n.contenido, 180))}</p>
        </div>
    </article>`;
}

function renderFeatured(noticia) {
    const featured = document.getElementById('featuredNews');
    const img = noticia.imagen || 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80';

    document.getElementById('featuredImg').src = img;
    document.getElementById('featuredImg').onerror = function () {
        this.src = 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1200&q=80';
    };
    document.getElementById('featuredCategory').textContent = capitalize(noticia.categoria || 'general');
    document.getElementById('featuredDate').textContent = formatDate(noticia.fecha_publicacion);
    document.getElementById('featuredTitle').textContent = noticia.titulo;
    document.getElementById('featuredResumen').textContent = noticia.resumen || truncate(noticia.contenido, 200);

    var btn = document.getElementById('featuredBtn');
    btn.onclick = function () { showNewsModal(noticia.id); };

    featured.style.display = '';
}

function showNewsModal(id) {
    const n = newsData.find(item => item.id == id);
    if (!n) return;

    const img = n.imagen || 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=1000&q=80';
    const bodyText = (n.contenido && n.contenido.trim()) ? n.contenido : (n.resumen || '');
    const sourceLink = n.url_fuente
        ? `<div class="article-source"><a href="${escapeHtml(n.url_fuente)}" target="_blank" rel="noopener">Leer artículo completo →</a></div>`
        : '';

    document.getElementById('newsModalTitle').textContent = n.titulo;
    document.getElementById('newsModalBody').innerHTML = `
        <div class="article-content">
            <div class="article-meta">
                <span class="article-author">${n.autor ? 'Por: ' + escapeHtml(n.autor) : ''}</span>
                <span class="article-date">${formatDate(n.fecha_publicacion)}</span>
                <span class="article-category">${escapeHtml(capitalize(n.categoria || 'general'))}</span>
            </div>
            <img src="${escapeHtml(img)}" alt="${escapeHtml(n.titulo)}" class="article-image" loading="lazy" onerror="this.style.display='none'">
            <div class="article-body">
                ${formatContent(bodyText)}
            </div>
            ${sourceLink}
        </div>
    `;

    document.getElementById('dynamicNewsModal').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeNewsModal() {
    document.getElementById('dynamicNewsModal').classList.remove('active');
    document.body.style.overflow = '';
}

function initNewsFilters() {
    document.querySelectorAll('.news-filters .filter-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.news-filters .filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyNewsFilter();

            // Featured siempre visible
            const featured = document.getElementById('featuredNews');
            if (featured) featured.style.display = '';
        });
    });
}

function formatContent(text) {
    if (!text) return '<p>Sin contenido disponible.</p>';
    return text.split('\n').filter(p => p.trim()).map(p => `<p>${escapeHtml(p.trim())}</p>`).join('');
}

function formatDate(dateStr) {
    if (!dateStr) return '';
    try {
        const date = new Date(dateStr);
        const months = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
    } catch (e) {
        return dateStr;
    }
}

function truncate(text, maxLen) {
    if (!text) return '';
    if (text.length <= maxLen) return text;
    return text.substring(0, maxLen) + '...';
}

function capitalize(str) {
    if (!str) return '';
    if (str === 'salud-mental') return 'Salud Mental';
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
