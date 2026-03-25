<?php
if (!isset($pageTitle)) {
    $pageTitle = 'BIENIESTAR';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <script>
        (function() {
            var theme = localStorage.getItem('bieniestar-theme');
            if (theme === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BIENIESTAR - Sistema integral de bienestar">
    <title><?php echo htmlspecialchars($pageTitle); ?> - BIENIESTAR</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo asset('img/content/AAX-Form-Grafico.svg'); ?>">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/dashboard.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/mobile-menu.css'); ?>">
<?php if (isset($additionalCSS)): ?>
    <?php foreach ($additionalCSS as $css): ?>
        <link rel="stylesheet" href="<?php echo asset('css/' . $css); ?>">
    <?php endforeach; ?>
<?php endif; ?>

    <!-- EmailJS para envío de correos (defer para no bloquear render) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script defer src="<?php echo asset('js/emailConfigLoader.php'); ?>"></script>
</head>
<body>
<a href="#main-content" class="skip-link">Saltar al contenido</a>
<script>
    const BASE_URL = '<?php echo BASE_URL; ?>';
    const API_URL  = BASE_URL + '/api';
    const CURRENT_USER_ID = <?php echo (int)($_SESSION['user_id'] ?? 0); ?>;
</script>
    <!-- Header -->
    <header class="dashboard-header">
        <div class="header-container">
            <div class="header-left">
                <!-- Nuevo Botón Hamburguesa -->
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Abrir menú">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <div class="header-logo">
                    <h2>BIEN<span>IEST</span>AR</h2>
                </div>
            </div>
            <div class="header-user">
                <button class="dark-mode-toggle" id="darkModeToggle" aria-label="Cambiar modo oscuro" title="Modo oscuro">
                    <span id="darkModeIcon">🌙</span>
                </button>
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <img src="<?php echo !empty(currentUser()['foto']) ? currentUser()['foto'] : asset('img/icons/default-avatar.svg'); ?>" alt="Avatar" class="user-avatar">
                <div class="user-menu">
                    <a href="<?php echo url('perfil'); ?>">Mi Perfil</a>
                    <?php if (isProfessional()): ?>
                        <a href="<?php echo url('profesional'); ?>">Panel Profesional</a>
                    <?php endif; ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo url('admin'); ?>">Panel Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo url('auth/logout'); ?>">Cerrar Sesión</a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="sidebar-nav">
            <a href="<?php echo url('dashboard'); ?>" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Dashboard</span>
            </a>
            <a href="<?php echo url('alimentacion'); ?>" class="nav-item <?php echo $currentPage === 'alimentacion' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                    <path d="M7 2v20"/>
                    <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                </svg>
                <span>Alimentación</span>
            </a>
            <a href="<?php echo url('ejercicio'); ?>" class="nav-item <?php echo $currentPage === 'ejercicio' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12h18"/>
                    <path d="M3 9v6"/>
                    <path d="M7 10v4"/>
                    <path d="M17 10v4"/>
                    <path d="M21 9v6"/>
                </svg>
                <span>Ejercicio</span>
            </a>
            <a href="<?php echo url('favoritos'); ?>" class="nav-item <?php echo $currentPage === 'favoritos' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
                <span>Favoritos</span>
            </a>
            <a href="<?php echo url('mi-plan'); ?>" class="nav-item <?php echo $currentPage === 'mi-plan' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <span>Mi Plan</span>
            </a>
            <a href="<?php echo url('salud-mental'); ?>" class="nav-item <?php echo $currentPage === 'salud-mental' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7564 11.2728 22.0329 10.6054C22.3095 9.93789 22.4518 9.2225 22.4518 8.5C22.4518 7.7775 22.3095 7.06211 22.0329 6.39464C21.7564 5.72718 21.351 5.12084 20.84 4.61V4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Salud Mental</span>
            </a>
            <a href="<?php echo url('noticias'); ?>" class="nav-item <?php echo $currentPage === 'noticias' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M4 22H20C20.5304 22 21.0391 21.7893 21.4142 21.4142C21.7893 21.0391 22 20.5304 22 20V4C22 3.46957 21.7893 2.96086 21.4142 2.58579C21.0391 2.21071 20.5304 2 20 2H8C7.46957 2 6.96086 2.21071 6.58579 2.58579C6.21071 2.96086 6 3.46957 6 4V20C6 20.5304 5.78929 21.0391 5.41421 21.4142C5.03914 21.7893 4.53043 22 4 22ZM4 22C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V9C2 8.46957 2.21071 7.96086 2.58579 7.58579C2.96086 7.21071 3.46957 7 4 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Noticias</span>
            </a>
            <a href="<?php echo url('citas'); ?>" class="nav-item <?php echo $currentPage === 'citas' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M8 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M16 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.6947 13.7H15.7037" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M15.6947 16.7H15.7037" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9955 13.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M11.9955 16.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.29431 13.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Citas</span>
            </a>
            <?php if (isProfessional()): ?>
            <a href="<?php echo url('profesional'); ?>" class="nav-item <?php echo $currentPage === 'profesional' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M20 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M23 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Panel Profesional</span>
            </a>
            <?php endif; ?>
            <a href="<?php echo url('chat'); ?>" class="nav-item <?php echo $currentPage === 'chat' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <span>Mensajes <span class="chat-nav-badge" id="chatNavBadge"></span></span>
            </a>
            <?php if (isAdmin()): ?>
            <a href="<?php echo url('admin'); ?>" class="nav-item <?php echo $currentPage === 'admin' ? 'active' : ''; ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                    <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.258 9.77251 19.9887C9.5799 19.7194 9.31074 19.5143 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.742 9.96512 4.0113 9.77251C4.28059 9.5799 4.48572 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <span>Panel Admin</span>
            </a>
            <?php endif; ?>
        </nav>
    </aside>

    <!-- Nuevo Menú Móvil -->
    <div class="mobile-menu" id="mobileMenu">
        <div class="mobile-menu-overlay"></div>
        <div class="mobile-menu-panel">
            <div class="mobile-menu-header">
                <h3>BIENIESTAR</h3>
                <p><?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></p>
            </div>
            <nav class="mobile-menu-nav">
                <a href="<?php echo url('dashboard'); ?>" class="mobile-menu-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M3 9L12 2L21 9V20C21 20.5304 20.7893 21.0391 20.4142 21.4142C20.0391 21.7893 19.5304 22 19 22H5C4.46957 22 3.96086 21.7893 3.58579 21.4142C3.21071 21.0391 3 20.5304 3 20V9Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Dashboard</span>
                </a>
                <a href="<?php echo url('alimentacion'); ?>" class="mobile-menu-item <?php echo $currentPage === 'alimentacion' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/>
                        <path d="M7 2v20"/>
                        <path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3zm0 0v7"/>
                    </svg>
                    <span>Alimentación</span>
                </a>
                <a href="<?php echo url('ejercicio'); ?>" class="mobile-menu-item <?php echo $currentPage === 'ejercicio' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 12h18"/>
                        <path d="M3 9v6"/>
                        <path d="M7 10v4"/>
                        <path d="M17 10v4"/>
                        <path d="M21 9v6"/>
                    </svg>
                    <span>Ejercicio</span>
                </a>
                <a href="<?php echo url('favoritos'); ?>" class="mobile-menu-item <?php echo $currentPage === 'favoritos' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <span>Favoritos</span>
                </a>
                <a href="<?php echo url('mi-plan'); ?>" class="mobile-menu-item <?php echo $currentPage === 'mi-plan' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <span>Mi Plan</span>
                </a>
                <a href="<?php echo url('salud-mental'); ?>" class="mobile-menu-item <?php echo $currentPage === 'salud-mental' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M20.84 4.61C20.3292 4.099 19.7228 3.69364 19.0554 3.41708C18.3879 3.14052 17.6725 2.99817 16.95 2.99817C16.2275 2.99817 15.5121 3.14052 14.8446 3.41708C14.1772 3.69364 13.5708 4.099 13.06 4.61L12 5.67L10.94 4.61C9.9083 3.57831 8.50903 2.99871 7.05 2.99871C5.59096 2.99871 4.19169 3.57831 3.16 4.61C2.1283 5.64169 1.54871 7.04097 1.54871 8.5C1.54871 9.95903 2.1283 11.3583 3.16 12.39L4.22 13.45L12 21.23L19.78 13.45L20.84 12.39C21.351 11.8792 21.7564 11.2728 22.0329 10.6054C22.3095 9.93789 22.4518 9.2225 22.4518 8.5C22.4518 7.7775 22.3095 7.06211 22.0329 6.39464C21.7564 5.72718 21.351 5.12084 20.84 4.61V4.61Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Salud Mental</span>
                </a>
                <a href="<?php echo url('noticias'); ?>" class="mobile-menu-item <?php echo $currentPage === 'noticias' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M4 22H20C20.5304 22 21.0391 21.7893 21.4142 21.4142C21.7893 21.0391 22 20.5304 22 20V4C22 3.46957 21.7893 2.96086 21.4142 2.58579C21.0391 2.21071 20.5304 2 20 2H8C7.46957 2 6.96086 2.21071 6.58579 2.58579C6.21071 2.96086 6 3.46957 6 4V20C6 20.5304 5.78929 21.0391 5.41421 21.4142C5.03914 21.7893 4.53043 22 4 22ZM4 22C3.46957 22 2.96086 21.7893 2.58579 21.4142C2.21071 21.0391 2 20.5304 2 20V9C2 8.46957 2.21071 7.96086 2.58579 7.58579C2.96086 7.21071 3.46957 7 4 7H6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Noticias</span>
                </a>
                <a href="<?php echo url('citas'); ?>" class="mobile-menu-item <?php echo $currentPage === 'citas' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M8 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M16 2V5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M3.5 9.09H20.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M21 8.5V17C21 20 19.5 22 16 22H8C4.5 22 3 20 3 17V8.5C3 5.5 4.5 3.5 8 3.5H16C19.5 3.5 21 5.5 21 8.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.6947 13.7H15.7037" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15.6947 16.7H15.7037" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9955 13.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M11.9955 16.7H12.0045" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.29431 13.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.29431 16.7H8.30329" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Citas</span>
                </a>
                <?php if (isProfessional()): ?>
                <a href="<?php echo url('profesional'); ?>" class="mobile-menu-item <?php echo $currentPage === 'profesional' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M16 21V19C16 17.9391 15.5786 16.9217 14.8284 16.1716C14.0783 15.4214 13.0609 15 12 15H5C3.93913 15 2.92172 15.4214 2.17157 16.1716C1.42143 16.9217 1 17.9391 1 19V21" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8.5 11C10.7091 11 12.5 9.20914 12.5 7C12.5 4.79086 10.7091 3 8.5 3C6.29086 3 4.5 4.79086 4.5 7C4.5 9.20914 6.29086 11 8.5 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M20 8V14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M23 11H17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Panel Profesional</span>
                </a>
                <?php endif; ?>
                <a href="<?php echo url('chat'); ?>" class="mobile-menu-item <?php echo $currentPage === 'chat' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                    </svg>
                    <span>Mensajes <span class="chat-nav-badge"></span></span>
                </a>
                <?php if (isAdmin()): ?>
                <a href="<?php echo url('admin'); ?>" class="mobile-menu-item <?php echo $currentPage === 'admin' ? 'active' : ''; ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                        <path d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.258 9.77251 19.9887C9.5799 19.7194 9.31074 19.5143 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.742 9.96512 4.0113 9.77251C4.28059 9.5799 4.48572 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <span>Panel Admin</span>
                </a>
                <?php endif; ?>
            </nav>
        </div>
    </div>

<!-- Scripts -->
<script src="<?php echo asset('js/main.js'); ?>"></script>
<script src="<?php echo asset('js/mobile-menu.js'); ?>"></script>
<script defer src="<?php echo asset('js/keyboard-nav.js'); ?>"></script>

    <!-- Main Content -->
    <main class="main-content" id="main-content">