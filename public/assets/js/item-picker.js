
; (function () {
    var PAGE = 8;
    var panel = null, currentTrigger = null, onSelectCb = null;
    var allItems = [], filtered = [], filterKey = 'categoria';
    var activeFilter = 'all', term = '', shown = PAGE;

    function buildPanel() {
        if (panel) return;
        panel = document.createElement('div');
        panel.id = 'itemPickerPanel';
        panel.innerHTML =
            '<div class="ip-search-wrap"><input type="text" class="ip-search" placeholder="Buscar..."></div>' +
            '<div class="ip-filters"></div>' +
            '<div class="ip-count"></div>' +
            '<div class="ip-list"></div>';
        document.body.appendChild(panel);
        panel.querySelector('.ip-search').addEventListener('input', function () {
            term = this.value.toLowerCase();
            shown = PAGE;
            applyFilter();
        });
        document.addEventListener('mousedown', function (e) {
            if (!panel || !panel.classList.contains('ip-open')) return;
            if (!panel.contains(e.target) && currentTrigger && !currentTrigger.contains(e.target)) {
                closePanel();
            }
        });
    }

    function applyFilter() {
        filtered = allItems.filter(function (item) {
            var tOk = !term || (item.titulo || '').toLowerCase().includes(term) ||
                (item.descripcion || '').toLowerCase().includes(term);
            var fOk = activeFilter === 'all' || (item[filterKey] || '') === activeFilter;
            return tOk && fOk;
        });
        renderList();
    }

    function renderFilters() {
        var el = panel.querySelector('.ip-filters');
        var cats = ['all'];
        allItems.forEach(function (item) {
            var v = item[filterKey] || '';
            if (v && !cats.includes(v)) cats.push(v);
        });
        if (cats.length <= 2) { el.style.display = 'none'; return; }
        el.style.display = '';
        el.innerHTML = cats.map(function (c) {
            return '<button class="ip-filter' + (activeFilter === c ? ' active' : '') + '" data-f="' + ipEsc(c) + '">' +
                (c === 'all' ? 'Todos' : ipCap(c)) + '</button>';
        }).join('');
        el.querySelectorAll('.ip-filter').forEach(function (btn) {
            btn.addEventListener('click', function () {
                activeFilter = this.dataset.f;
                shown = PAGE;
                el.querySelectorAll('.ip-filter').forEach(function (b) { b.classList.remove('active'); });
                this.classList.add('active');
                applyFilter();
            });
        });
    }

    function renderList() {
        var listEl = panel.querySelector('.ip-list');
        var countEl = panel.querySelector('.ip-count');
        countEl.textContent = filtered.length + ' resultado' + (filtered.length !== 1 ? 's' : '');
        if (!filtered.length) {
            listEl.innerHTML = '<div class="ip-empty">Sin resultados' + (term ? ' para "' + ipEsc(term) + '"' : '') + '</div>';
            return;
        }
        var curVal = currentTrigger ? (currentTrigger.dataset.value || '') : '';
        var html = filtered.map(function (item) {
            var isRec = filterKey === 'categoria';
            var icon = isRec ? '🍽️' : '💪';
            var bg = isRec ? 'rgba(76,175,80,.1)' : 'rgba(255,107,53,.1)';
            var thumb = item.imagen
                ? '<img src="' + ipEsc(item.imagen) + '" loading="lazy" onerror="this.style.display=\'none\'">'
                : '<div class="ip-item-icon" style="background:' + bg + ';">' + icon + '</div>';
            var sub = [];
            if (item.calorias) sub.push(Math.round(item.calorias) + ' kcal');
            if (item.tiempo_preparacion) sub.push(item.tiempo_preparacion + ' min');
            if (item.duracion) sub.push(item.duracion + ' min');
            if (item.musculo_objetivo) sub.push(ipCap(item.musculo_objetivo));
            var subHtml = sub.length ? '<div class="ip-item-sub">' + sub.join(' · ') + '</div>' : '';
            var badges = '';
            if (isRec) {
                if (item.categoria) badges += '<span class="ip-badge ip-badge-cat">' + ipEsc(ipCap(item.categoria)) + '</span>';
            } else {
                if (item.tipo) badges += '<span class="ip-badge ip-badge-type">' + ipEsc(ipCap(item.tipo)) + '</span>';
                if (item.nivel) badges += '<span class="ip-badge ip-badge-nivel nivel-' + item.nivel + '">' + ipEsc(ipCap(item.nivel)) + '</span>';
            }
            var selCls = String(item.id) === String(curVal) ? ' ip-selected' : '';
            return '<div class="ip-item' + selCls + '" data-id="' + item.id + '">' +
                thumb +
                '<div class="ip-item-body"><div class="ip-item-title">' + ipEsc(item.titulo) + '</div>' + subHtml + '</div>' +
                '<div class="ip-item-badges">' + badges + '</div></div>';
        }).join('');

        listEl.innerHTML = html;
        listEl.querySelectorAll('.ip-item').forEach(function (el) {
            el.addEventListener('click', function () {
                var found = allItems.find(function (x) { return String(x.id) === String(this.dataset.id); }, this);
                if (found && onSelectCb) onSelectCb(found);
                closePanel();
            });
        });
        var moreBtn = listEl.querySelector('.ip-show-more');
        if (moreBtn) {
            moreBtn.addEventListener('click', function () {
                shown += PAGE;
                renderList();
                listEl.scrollTop = listEl.scrollHeight;
            });
        }
    }

    function openPanel(trigger, items, fKey, cb, accent) {
        buildPanel();
        currentTrigger = trigger;
        allItems = items;
        filterKey = fKey;
        onSelectCb = cb;
        activeFilter = 'all';
        term = '';
        shown = PAGE;
        panel.querySelector('.ip-search').value = '';
        panel.className = accent === 'green' ? 'ip-green' : '';
        applyFilter();
        renderFilters();
        var rect = trigger.getBoundingClientRect();
        var panelW = Math.max(rect.width, 360);
        var left = rect.left;
        if (left + panelW > window.innerWidth - 8) left = Math.max(8, window.innerWidth - panelW - 8);
        var topPos = rect.bottom + 4;
        if (topPos + 360 > window.innerHeight) topPos = Math.max(8, rect.top - 364);
        panel.style.cssText = 'left:' + left + 'px;top:' + topPos + 'px;width:' + panelW + 'px;max-height:360px;';
        panel.classList.add('ip-open');
        panel.querySelector('.ip-search').focus();
        trigger.classList.add('open');
    }

    function closePanel() {
        if (!panel) return;
        panel.classList.remove('ip-open');
        if (currentTrigger) currentTrigger.classList.remove('open');
        currentTrigger = null;
    }

    function ipCap(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
    function ipEsc(s) {
        if (!s) return '';
        var d = document.createElement('div'); d.textContent = String(s); return d.innerHTML;
    }

    window.openItemPicker = openPanel;
    window.closeItemPickerPanel = closePanel;
})();
