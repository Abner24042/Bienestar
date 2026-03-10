<?php
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();
if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();
$currentPage = 'calendario';
$pageTitle = 'Mi Calendario';
$additionalCSS = ['calendario.css'];
?>

<?php include '../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:8px"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>Mi Calendario</h1>
        <p>Gestiona tus eventos y citas de bienestar</p>
    </div>

    <div class="calendar-layout">
        <!-- Panel izquierdo - Información y controles -->
        <div class="calendar-sidebar">
            <div class="calendar-info-card">
                <h3>Información</h3>
                <p class="calendar-user">
                    <strong><?php echo htmlspecialchars($user['nombre']); ?></strong><br>
                    <span><?php echo htmlspecialchars($user['correo']); ?></span>
                </p>
            </div>

            <div class="calendar-legend">
                <h3>Tipos de Eventos</h3>
                <div class="legend-item">
                    <span class="legend-color" style="background: #4285F4;"></span>
                    <span>Citas de Bienestar</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #34A853;"></span>
                    <span>Entrenamientos</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #FBBC04;"></span>
                    <span>Recordatorios</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color" style="background: #EA4335;"></span>
                    <span>Importantes</span>
                </div>
            </div>

            <div class="calendar-actions">
                <?php if (isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'google'): ?>
                <div class="google-sync-status" style="background: #e8f5e9; padding: 15px; border-radius: 8px; text-align: center; margin-bottom: 15px;">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" style="display: inline-block; margin-right: 8px;">
                        <path d="M17.64 9.20456C17.64 8.56637 17.5827 7.95274 17.4764 7.36365H9V10.845H13.8436C13.635 11.97 13.0009 12.9232 12.0477 13.5614V15.8196H14.9564C16.6582 14.2527 17.64 11.9455 17.64 9.20456Z" fill="#4285F4"/>
                        <path d="M9 18C11.43 18 13.4673 17.1941 14.9564 15.8195L12.0477 13.5614C11.2418 14.1013 10.2109 14.4204 9 14.4204C6.65591 14.4204 4.67182 12.8372 3.96409 10.71H0.957275V13.0418C2.43818 15.9832 5.48182 18 9 18Z" fill="#34A853"/>
                        <path d="M3.96409 10.7098C3.78409 10.1698 3.68182 9.59301 3.68182 8.99983C3.68182 8.40665 3.78409 7.82983 3.96409 7.28983V4.95801H0.957273C0.347727 6.17301 0 7.54755 0 8.99983C0 10.4521 0.347727 11.8266 0.957273 13.0416L3.96409 10.7098Z" fill="#FBBC05"/>
                        <path d="M9 3.57955C10.3214 3.57955 11.5077 4.03364 12.4405 4.92545L15.0218 2.34409C13.4632 0.891818 11.4259 0 9 0C5.48182 0 2.43818 2.01682 0.957275 4.95818L3.96409 7.29C4.67182 5.16273 6.65591 3.57955 9 3.57955Z" fill="#EA4335"/>
                    </svg>
                    <span style="color: #2e7d32; font-weight: 600;">Sincronizado con Google</span>
                </div>
                <?php else: ?>
                <button class="btn btn-primary btn-block" id="btnSyncGoogle">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                        <path d="M17.64 9.20456C17.64 8.56637 17.5827 7.95274 17.4764 7.36365H9V10.845H13.8436C13.635 11.97 13.0009 12.9232 12.0477 13.5614V15.8196H14.9564C16.6582 14.2527 17.64 11.9455 17.64 9.20456Z" fill="#4285F4"/>
                        <path d="M9 18C11.43 18 13.4673 17.1941 14.9564 15.8195L12.0477 13.5614C11.2418 14.1013 10.2109 14.4204 9 14.4204C6.65591 14.4204 4.67182 12.8372 3.96409 10.71H0.957275V13.0418C2.43818 15.9832 5.48182 18 9 18Z" fill="#34A853"/>
                        <path d="M3.96409 10.7098C3.78409 10.1698 3.68182 9.59301 3.68182 8.99983C3.68182 8.40665 3.78409 7.82983 3.96409 7.28983V4.95801H0.957273C0.347727 6.17301 0 7.54755 0 8.99983C0 10.4521 0.347727 11.8266 0.957273 13.0416L3.96409 10.7098Z" fill="#FBBC05"/>
                        <path d="M9 3.57955C10.3214 3.57955 11.5077 4.03364 12.4405 4.92545L15.0218 2.34409C13.4632 0.891818 11.4259 0 9 0C5.48182 0 2.43818 2.01682 0.957275 4.95818L3.96409 7.29C4.67182 5.16273 6.65591 3.57955 9 3.57955Z" fill="#EA4335"/>
                    </svg>
                    Sincronizar con Google
                </button>
                <?php endif; ?>
            </div>

            <div class="calendar-stats">
                <h3>Este Mes</h3>
                <div class="stat-row">
                    <span>Eventos totales:</span>
                    <strong id="totalEvents">0</strong>
                </div>
                <div class="stat-row">
                    <span>Próximas citas:</span>
                    <strong id="upcomingEvents">0</strong>
                </div>
            </div>
        </div>

        <!-- Panel principal - Calendario personalizado -->
        <div class="calendar-main">
            <div class="calendar-container">
                <div id="calendarView" style="padding: 2rem;">
                    <!-- Navegación del calendario -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                        <button class="btn btn-secondary" id="btnPrevMonth">‹ Anterior</button>
                        <h2 id="currentMonthYear" style="margin: 0;"></h2>
                        <button class="btn btn-secondary" id="btnNextMonth">Siguiente ›</button>
                    </div>

                    <!-- Grid del calendario -->
                    <div id="calendarGrid" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: #ddd; border: 1px solid #ddd;">
                        <!-- Días de la semana -->
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Dom</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Lun</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Mar</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Mié</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Jue</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Vie</div>
                        <div style="background: #ff6b35; color: white; padding: 1rem; text-align: center; font-weight: 600;">Sáb</div>
                    </div>

                    <!-- Días del mes (generados por JS) -->
                    <div id="calendarDays"></div>
                </div>
            </div>

            <div class="calendar-help">
                <?php if (isset($_SESSION['login_method']) && $_SESSION['login_method'] === 'google'): ?>
                <p>
                    <strong>✅ Sincronizado:</strong> Tu calendario está conectado con tu cuenta de Google. Todos los eventos se sincronizan automáticamente.
                </p>
                <p>
                    Puedes agregar eventos desde aquí o directamente desde Google Calendar.
                </p>
                <?php else: ?>
                <p>
                    <strong>💡 Consejo:</strong> Sincroniza tu calendario con Google Calendar para ver todos tus eventos de bienestar en un solo lugar.
                </p>
                <p>
                    Para sincronizar, haz clic en "Sincronizar con Google" y autoriza el acceso a tu calendario.
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$additionalJS = ['googleCalendar.js', 'emailConfig.js', 'calendario.js'];
include '../../app/views/layouts/footer.php';
?>
