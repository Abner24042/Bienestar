<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BIENIESTAR - Plataforma exclusiva de bienestar para trabajadores de base del IEST Anáhuac">
    <meta name="keywords" content="bienestar, salud, alimentación, ejercicio, IEST, Anáhuac, personal, trabajadores">
    <meta name="author" content="IEST Anáhuac">
    <title>BIENIESTAR - Plataforma de Bienestar para Personal IEST Anáhuac</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?php echo asset('img/content/AAX-Form-Grafico.svg'); ?>">

    <!-- Preload imagen hero (LCP) -->
    <link rel="preload" as="image" href="<?php echo asset('img/content/DSC07783.jpg'); ?>">

    <!-- Preconnect para imágenes externas -->
    <link rel="preconnect" href="https://images.unsplash.com">
    <link rel="dns-prefetch" href="https://images.unsplash.com">

    <!-- Estilos -->
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/landing.css'); ?>">
</head>
<body>

    <!-- Navegación Sticky -->
    <nav class="sticky-nav" id="stickyNav">
        <div class="nav-container">
            <div class="nav-logo">
                <h3>BIEN<span>IEST</span>AR</h3>
            </div>
            <div class="nav-links">
                <a href="#inicio">Inicio</a>
                <a href="#servicios">Servicios</a>
                <a href="#beneficios">Beneficios</a>
                <a href="#contacto">Contacto</a>
            </div>
            <div class="nav-actions">
                <a href="<?php echo url('login'); ?>" class="btn-login">Iniciar Sesión</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section con efecto parallax -->
    <section class="hero-section" id="heroSection">
        <div class="hero-background" id="heroBackground"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content" id="heroContent">
            <h1 class="hero-title">Bienestar Integral para el Personal del IEST Anáhuac</h1>
            <p class="hero-subtitle">Plataforma exclusiva para trabajadores de base. Cuida tu salud física, mental y emocional con nuestros programas personalizados de nutrición, ejercicio y bienestar.</p>
            <a href="<?php echo url('login'); ?>" class="btn-cta">
                <span>Acceder a mi portal</span>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M4 10h12M10 4l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </a>
        </div>

        <!-- Indicador de scroll -->
        <div class="scroll-indicator">
            <div class="mouse"></div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section" id="servicios">
        <div class="container">
            <div class="section-header">
                <h2>¿Por qué usar BIENIESTAR?</h2>
                <p>Una plataforma integral para el bienestar de nuestro equipo de trabajo</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <path d="M22 12H18L15 21L9 3L6 12H2" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="feature-number">24/7</h3>
                    <h4 class="feature-title">Acceso continuo</h4>
                    <p class="feature-desc">Accede a tus programas de bienestar en cualquier momento y lugar.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <path d="M20 21V19C20 17.9391 19.5786 16.9217 18.8284 16.1716C18.0783 15.4214 17.0609 15 16 15H8C6.93913 15 5.92172 15.4214 5.17157 16.1716C4.42143 16.9217 4 17.9391 4 19V21" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="feature-number">100%</h3>
                    <h4 class="feature-title">Personalizado</h4>
                    <p class="feature-desc">Programas adaptados a tus necesidades y objetivos personales.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <path d="M9 11L12 14L22 4" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M21 12V19C21 19.5304 20.7893 20.0391 20.4142 20.4142C20.0391 20.7893 19.5304 21 19 21H5C4.46957 21 3.96086 20.7893 3.58579 20.4142C3.21071 20.0391 3 19.5304 3 19V5C3 4.46957 3.21071 3.96086 3.58579 3.58579C3.96086 3.21071 4.46957 3 5 3H16" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="feature-number">3</h3>
                    <h4 class="feature-title">Áreas de bienestar</h4>
                    <p class="feature-desc">Nutrición, ejercicio y salud mental integrados en una sola plataforma.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none">
                            <path d="M12 2L2 7L12 12L22 7L12 2Z" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 17L12 22L22 17" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M2 12L12 17L22 12" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <h3 class="feature-number">0</h3>
                    <h4 class="feature-title">Costo</h4>
                    <p class="feature-desc">Beneficio exclusivo para todo el personal de base del IEST Anáhuac.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Preview Section -->
    <section class="services-section" id="beneficios">
        <div class="container">
            <div class="section-header">
                <h2>Nuestros Servicios de Bienestar</h2>
                <p>Todo lo que necesitas para tu desarrollo integral</p>
            </div>

            <div class="services-grid">
                <div class="service-card" onclick="location.href='<?php echo url('login'); ?>'">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=500&q=75&auto=format" alt="Alimentación Saludable" width="500" height="333" loading="lazy" decoding="async">
                    </div>
                    <div class="service-content">
                        <h3>Alimentación Saludable</h3>
                        <p>Recetas balanceadas y planes nutricionales personalizados</p>
                        <span class="service-arrow">→</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='<?php echo url('login'); ?>'">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=500&q=75&auto=format" alt="Ejercicio" width="500" height="333" loading="lazy" decoding="async">
                    </div>
                    <div class="service-content">
                        <h3>Rutinas de Ejercicio</h3>
                        <p>Planes de entrenamiento adaptados a tu nivel</p>
                        <span class="service-arrow">→</span>
                    </div>
                </div>

                <div class="service-card" onclick="location.href='<?php echo url('login'); ?>'">
                    <div class="service-image">
                        <img src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=500&q=75&auto=format" alt="Salud Mental" width="500" height="333" loading="lazy" decoding="async">
                    </div>
                    <div class="service-content">
                        <h3>Salud Mental</h3>
                        <p>Tests psicológicos y recursos para tu bienestar emocional</p>
                        <span class="service-arrow">→</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container">
            <div class="cta-content">
                <h2>¿Listo para mejorar tu bienestar?</h2>
                <p>Comienza hoy tu camino hacia una vida más saludable y equilibrada</p>
                <a href="<?php echo url('login'); ?>" class="btn-cta-large">Acceder ahora</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer" id="contacto">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h4>BIEN<span>IEST</span>AR</h4>
                    <p>Plataforma integral de bienestar para el personal del IEST Anáhuac</p>
                </div>
                <div class="footer-col">
                    <h5>Enlaces Rápidos</h5>
                    <ul>
                        <li><a href="<?php echo url('about'); ?>">Sobre Nosotros</a></li>
                        <li><a href="<?php echo url('login'); ?>">Iniciar Sesión</a></li>
                        <li><a href="#servicios">Servicios</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Contacto</h5>
                    <ul>
                        <li>contacto@bieniestar.mx</li>
                        <li>(833) 123-4567</li>
                        <li>IEST Anáhuac, Tampico</li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Síguenos</h5>
                    <ul>
                        <li><a href="https://www.facebook.com/iestanahuac/?locale=es_LA" target="_blank" rel="noopener">Facebook</a></li>
                        <li><a href="https://www.instagram.com/iestanahuac/?hl=es" target="_blank" rel="noopener">Instagram</a></li>
                        <li><a href="https://x.com/iestanahuac?lang=es" target="_blank" rel="noopener">Twitter</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BIENIESTAR. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts (defer para no bloquear render) -->
    <script>const BASE_URL = '<?php echo BASE_URL; ?>';</script>
    <script defer src="<?php echo asset('js/main.js'); ?>"></script>
    <script defer src="<?php echo asset('js/landing.js'); ?>"></script>
</body>
</html>
