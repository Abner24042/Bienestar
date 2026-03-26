<?php

if (!isset($modalId))
    $modalId = 'modal-' . uniqid();
if (!isset($modalTitle))
    $modalTitle = '';
if (!isset($modalContent))
    $modalContent = '';
if (!isset($modalSize))
    $modalSize = 'medium'; // small, medium, large
?>

<div class="modal" id="<?php echo $modalId; ?>">
    <div class="modal-overlay"></div>
    <div class="modal-container modal-<?php echo $modalSize; ?>">
        <div class="modal-header">
            <h3 class="modal-title"><?php echo htmlspecialchars($modalTitle); ?></h3>
            <button class="modal-close" data-modal-close="<?php echo $modalId; ?>">&times;</button>
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
        color: #999;
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
    // Funcionalidad del modal
    document.addEventListener('DOMContentLoaded', function () {
        // Abrir modal
        document.querySelectorAll('[data-modal-open]').forEach(button => {
            button.addEventListener('click', function () {
                const modalId = this.dataset.modalOpen;
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.add('active');
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Cerrar modal
        document.querySelectorAll('[data-modal-close]').forEach(button => {
            button.addEventListener('click', function () {
                const modalId = this.dataset.modalClose;
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });

        // Cerrar al hacer clic en el overlay
        document.querySelectorAll('.modal-overlay').forEach(overlay => {
            overlay.addEventListener('click', function () {
                const modal = this.closest('.modal');
                if (modal) {
                    modal.classList.remove('active');
                    document.body.style.overflow = '';
                }
            });
        });
    });
</script>