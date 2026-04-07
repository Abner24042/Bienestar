<?php

if (!isset($modalId))
    $modalId = 'modal-' . uniqid();
if (!isset($modalTitle))
    $modalTitle = '';
if (!isset($modalContent))
    $modalContent = '';
if (!isset($modalSize))
    $modalSize = 'medium'; // small, medium, large

// id unico para el titulo — necesario para aria-labelledby
$titleId = $modalId . '-title';
?>

<div class="modal"
     id="<?php echo $modalId; ?>"
     role="dialog"
     aria-modal="true"
     aria-labelledby="<?php echo $titleId; ?>">
    <div class="modal-overlay"></div>
    <div class="modal-container modal-<?php echo $modalSize; ?>">
        <div class="modal-header">
            <h3 class="modal-title" id="<?php echo $titleId; ?>"><?php echo htmlspecialchars($modalTitle); ?></h3>
            <button class="modal-close" data-modal-close="<?php echo $modalId; ?>" aria-label="Cerrar diálogo">&times;</button>
        </div>
        <div class="modal-body">
            <?php echo $modalContent; ?>
        </div>
        <?php if (isset($modalFooter)): ?>
            <div class="modal-footer">
                <?php echo $modalFooter; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1050;
        align-items: center;
        justify-content: center;
    }

    .modal.active {
        display: flex;
    }

    .modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
    }

    .modal-container {
        position: relative;
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        max-height: 90vh;
        overflow-y: auto;
        z-index: 1;
        animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-small {
        width: 90%;
        max-width: 400px;
    }

    .modal-medium {
        width: 90%;
        max-width: 600px;
    }

    .modal-large {
        width: 90%;
        max-width: 900px;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 25px;
        border-bottom: 1px solid #eee;
    }

    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #111;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        font-size: 2rem;
        /* #666 pasa WCAG AA (5.74:1) sobre fondo blanco — antes era #999 que fallaba */
        color: #666;
        cursor: pointer;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .modal-close:hover {
        color: #111;
    }

    .modal-body {
        padding: 25px;
    }

    .modal-footer {
        padding: 20px 25px;
        border-top: 1px solid #eee;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    /* Dark Mode */
    [data-theme="dark"] .modal-container {
        background: #1e1e1e;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
    }

    [data-theme="dark"] .modal-header {
        border-bottom-color: #333;
    }

    [data-theme="dark"] .modal-title {
        color: #e8e8e8;
    }

    [data-theme="dark"] .modal-close {
        color: #777;
    }

    [data-theme="dark"] .modal-close:hover {
        color: #e8e8e8;
    }

    [data-theme="dark"] .modal-footer {
        border-top-color: #333;
    }
</style>

<script>
// guard pa que no se registre el mismo listener N veces si hay varios modales en la pagina
if (!window._modalSistemaIniciado) {
    window._modalSistemaIniciado = true;

    // elemento que tenia el focus antes de abrir el modal — pa devolverlo al cerrar
    var _modalOpener = null;

    // devuelve todos los elementos que se pueden enfocar dentro del modal
    function _modalFocusables(modal) {
        return Array.from(modal.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), ' +
            'select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
        )).filter(function (el) { return el.offsetParent !== null; });
    }

    function _abrirModal(modalId) {
        var modal = document.getElementById(modalId);
        if (!modal) return;
        _modalOpener = document.activeElement; // guarda quien tenia el focus
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        // mover el focus al primer elemento interactivo del modal
        var focusables = _modalFocusables(modal);
        if (focusables.length) focusables[0].focus();
    }

    function _cerrarModal(modal) {
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = '';
        // devolver el focus a donde estaba antes de abrir — importante pa teclado
        if (_modalOpener && typeof _modalOpener.focus === 'function') {
            _modalOpener.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // abrir
        document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
            btn.addEventListener('click', function () { _abrirModal(this.dataset.modalOpen); });
        });

        // cerrar con boton
        document.querySelectorAll('[data-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                _cerrarModal(document.getElementById(this.dataset.modalClose));
            });
        });

        // cerrar al click en overlay
        document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
            overlay.addEventListener('click', function () {
                _cerrarModal(this.closest('.modal'));
            });
        });
    });

    // Escape pa cerrar + focus trap con Tab/Shift+Tab
    document.addEventListener('keydown', function (e) {
        var modal = document.querySelector('.modal.active');
        if (!modal) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            _cerrarModal(modal);
            return;
        }

        if (e.key === 'Tab') {
            var focusables = _modalFocusables(modal);
            if (!focusables.length) return;
            var first = focusables[0];
            var last = focusables[focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                // shift+tab en el primero -> ir al ultimo
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                // tab en el ultimo -> ir al primero
                e.preventDefault();
                first.focus();
            }
        }
    });
}
</script>