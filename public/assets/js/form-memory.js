/**
 * form-memory.js — Persistencia de borradores en localStorage
 * Guarda automáticamente los campos de formulario mientras el usuario escribe.
 * Restaura el borrador cuando se abre un formulario en modo "nuevo" (no edición).
 */
(function () {
    'use strict';

    const PREFIX = 'bdr_';
    const SKIP_TYPES = new Set(['password', 'file', 'submit', 'button', 'reset', 'hidden', 'checkbox', 'radio']);

    // ── Helpers ─────────────────────────────────────────────────────────────────

    function storageKey(formId) {
        return PREFIX + formId;
    }

    /** Devuelve true si el formulario tiene un campo ID con valor (modo edición) */
    function isEditMode(form) {
        const hiddenInputs = form.querySelectorAll('input[type="hidden"]');
        for (const input of hiddenInputs) {
            if (/_id$/i.test(input.name) || /^id$/i.test(input.name)) {
                if (input.value && input.value !== '0') return true;
            }
        }
        return false;
    }

    /** Recopila los valores guardables del formulario */
    function collectData(form) {
        const data = {};
        for (const el of form.elements) {
            if (!el.name || SKIP_TYPES.has(el.type)) continue;
            if (el.tagName === 'SELECT') {
                data[el.name] = el.value;
            } else {
                data[el.name] = el.value;
            }
        }
        return data;
    }

    /** Guarda el borrador en localStorage */
    function saveDraft(form) {
        if (form.dataset.noDraft !== undefined) return;
        try {
            localStorage.setItem(storageKey(form.id), JSON.stringify(collectData(form)));
        } catch (e) { /* quota exceeded — ignore */ }
    }

    /** Elimina el borrador del localStorage */
    function clearDraft(formId) {
        try {
            localStorage.removeItem(storageKey(formId));
        } catch (e) {}
    }

    /** Restaura el borrador y muestra el badge */
    function restoreDraft(form) {
        if (form.dataset.noDraft !== undefined) return;
        if (isEditMode(form)) return;

        let saved;
        try {
            const raw = localStorage.getItem(storageKey(form.id));
            if (!raw) return;
            saved = JSON.parse(raw);
        } catch (e) { return; }

        // Verificar que haya algo que restaurar (al menos un campo no vacío)
        const hasData = Object.values(saved).some(v => v && v.trim && v.trim() !== '');
        if (!hasData) return;

        // Restaurar valores
        let restored = false;
        for (const el of form.elements) {
            if (!el.name || SKIP_TYPES.has(el.type)) continue;
            if (saved[el.name] !== undefined && saved[el.name] !== '') {
                el.value = saved[el.name];
                restored = true;
            }
        }

        if (restored) showRestoredBadge(form);
    }

    /** Muestra el badge "Borrador restaurado" encima del formulario */
    function showRestoredBadge(form) {
        // No duplicar badge
        if (form.querySelector('.bdr-badge')) return;

        const badge = document.createElement('div');
        badge.className = 'bdr-badge';
        badge.innerHTML = '📋 Borrador restaurado &nbsp;<button type="button" onclick="this.parentElement.remove()" style="background:none;border:none;cursor:pointer;font-size:0.9rem;color:inherit;padding:0;line-height:1;">&times;</button>';
        badge.style.cssText = [
            'display:flex', 'align-items:center', 'gap:6px',
            'padding:6px 12px', 'margin-bottom:10px',
            'background:#fff7e6', 'color:#b45309',
            'border:1px solid #fcd34d', 'border-radius:6px',
            'font-size:0.82rem', 'font-weight:600'
        ].join(';');
        form.insertBefore(badge, form.firstChild);
    }

    // ── Registro de formulario ───────────────────────────────────────────────────

    function registerForm(form) {
        if (!form.id || form.dataset.noDraft !== undefined) return;
        if (form._bdrRegistered) return;
        form._bdrRegistered = true;

        // Guardar en cada cambio
        form.addEventListener('input', () => saveDraft(form));
        form.addEventListener('change', () => saveDraft(form));

        // Limpiar al hacer submit (el handler de éxito también puede llamar clearFormDraft)
        form.addEventListener('submit', () => clearDraft(form.id));
    }

    /** Intenta restaurar después de un breve delay para que el modal renderice */
    function tryRestore(form) {
        setTimeout(() => restoreDraft(form), 80);
    }

    // ── Observar apertura de modales ─────────────────────────────────────────────

    function setupModalObserver() {
        const observer = new MutationObserver((mutations) => {
            for (const mutation of mutations) {
                for (const node of mutation.addedNodes) {
                    if (!(node instanceof HTMLElement)) continue;
                    // El modal aparece (display cambia a block/flex) o se añade al DOM
                    const forms = node.querySelectorAll ? node.querySelectorAll('form[id]') : [];
                    for (const form of forms) {
                        registerForm(form);
                        tryRestore(form);
                    }
                }
                // También detectar cambio de display (modals que ya existen en DOM)
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const el = mutation.target;
                    if (el.style && el.style.display !== 'none' && el.style.display !== '') {
                        const forms = el.querySelectorAll('form[id]');
                        for (const form of forms) {
                            registerForm(form);
                            tryRestore(form);
                        }
                    }
                }
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['style', 'class']
        });
    }

    // ── Inicialización ────────────────────────────────────────────────────────────

    function init() {
        // Registrar formularios ya presentes en el DOM
        document.querySelectorAll('form[id]').forEach(form => {
            registerForm(form);
            // Formularios visibles (no dentro de modales ocultos): restaurar
            const modal = form.closest('[id*="modal"], [id*="Modal"], .modal, [class*="modal"]');
            if (!modal) {
                tryRestore(form);
            }
        });

        setupModalObserver();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    // ── API global ────────────────────────────────────────────────────────────────

    /** Llamar después de guardar exitosamente para limpiar el borrador */
    window.clearFormDraft = clearDraft;

    /** Exponer para debug */
    window._formMemory = { saveDraft, restoreDraft, clearDraft, isEditMode };

})();
