<?php
require_once '../../app/config/config.php';
$isLoggedIn = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nosotros - BIENIESTAR</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo asset('img/content/AAX-Form-Grafico.svg'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/main.css'); ?>">
    <link rel="stylesheet" href="<?php echo asset('css/about.css'); ?>">
    <!-- aplica el tema guardado antes de que cargue el CSS pa evitar el flash blanco -->
    <script>
        (function () {
            if (localStorage.getItem('bieniestar-theme') === 'dark')
                document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>
</head>
<body>
    <!-- Header Simple -->
    <header class="simple-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo BASE_URL; ?>/" class="logo">
                    <h2>BIEN<span>IEST</span>AR</h2>
                </a>
                <nav class="header-nav">
                    <a href="<?php echo BASE_URL; ?>/">Inicio</a>
                    <a href="<?php echo url('about'); ?>" class="active">Nosotros</a>
                    <button class="about-dark-toggle" id="aboutDarkToggle" aria-label="Cambiar modo oscuro" title="Modo oscuro">
                        <!-- luna para light mode, sol para dark mode -->
                        <svg id="aboutIconMoon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                        <svg id="aboutIconSun" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                    </button>
                    <?php if ($isLoggedIn): ?>
                    <a href="<?php echo url('dashboard'); ?>" class="btn btn-primary">Mi Panel</a>
                    <?php else: ?>
                    <a href="<?php echo url('login'); ?>" class="btn btn-primary">Iniciar Sesión</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Hero About -->
    <section class="about-hero">
        <div class="container">
            <div class="hero-content">
                <h1>Sobre BIENIESTAR</h1>
                <p class="hero-subtitle">Tu aliado en el camino hacia un estilo de vida más saludable</p>
            </div>
        </div>
    </section>

    <!-- Misión y Visión -->
    <section class="mission-vision">
        <div class="container">
            <div class="mv-grid">
                <div class="mv-card">
                    <div class="mv-icon">🎯</div>
                    <h2>Nuestra Misión</h2>
                    <p>Proporcionar al personal de base del IEST Anáhuac herramientas, recursos y conocimientos para alcanzar y mantener un estilo de vida saludable integral, abarcando nutrición, ejercicio y bienestar mental.</p>
                </div>

                <div class="mv-card">
                    <div class="mv-icon">🌟</div>
                    <h2>Nuestra Visión</h2>
                    <p>Ser la plataforma de bienestar líder en instituciones educativas, reconocida por transformar positivamente los hábitos de salud de miles de trabajadores y contribuir a su éxito profesional y personal.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Historia -->
    <section class="our-story">
        <div class="container">
            <div class="story-content">
                <div class="story-text">
                    <h2>Nuestra Historia</h2>
                    <p>BIENIESTAR nació en 2025 como una iniciativa del IEST Anáhuac para atender una necesidad creciente: el bienestar integral de nuestros trabajadores de base.</p>

                    <p>Observamos que muchos trabajadores de base enfrentaban desafíos relacionados con la alimentación poco saludable, sedentarismo y estrés laboral. Fue entonces cuando decidimos crear una solución integral.</p>
                </div>
                
                <div class="story-image">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=600&q=75&auto=format" alt="Trabajadores IEST" loading="lazy" decoding="async" width="600" height="400">
                </div>
            </div>
        </div>
    </section>

    <!-- Valores -->
    <section class="our-values">
        <div class="container">
            <h2 class="section-title">Nuestros Valores</h2>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon">💪</div>
                    <h3>Compromiso</h3>
                    <p>Nos comprometemos con tu bienestar y éxito a largo plazo</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🤝</div>
                    <h3>Inclusión</h3>
                    <p>Todas las personas son bienvenidas, sin importar su nivel de condición física</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🔬</div>
                    <h3>Evidencia Científica</h3>
                    <p>Toda nuestra información está respaldada por investigación científica</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🌱</div>
                    <h3>Crecimiento</h3>
                    <p>Creemos en el desarrollo continuo y la mejora constante</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">❤️</div>
                    <h3>Empatía</h3>
                    <p>Entendemos los desafíos únicos de la vida laboral</p>
                </div>
                
                <div class="value-card">
                    <div class="value-icon">🎓</div>
                    <h3>Educación</h3>
                    <p>Empoderamos a través del conocimiento y la información</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipo -->
    <section class="our-team">
        <div class="container">
            <h2 class="section-title">Nuestro Equipo</h2>
            <p class="section-subtitle">Conoce a los profesionales detrás de BIENIESTAR</p>
            
            <div class="team-grid">
                <div class="team-member">
                    <div class="member-photo">
                        <img src="<?php echo asset('img/icons/HanniaPerez.jpeg'); ?>" alt="Hannia Perez Trejo" loading="lazy" decoding="async" width="300" height="300">
                    </div>
                    <h3>Hannia Perez Trejo</h3>
                    <p class="member-role">Coordinadora de Bienestar</p>
                    <p class="member-bio">Especialista en desarrollo de programas de salud integral para la comunidad laboral.</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <img src="<?php echo asset('img/icons/AnaPaula.jpeg'); ?>" alt="Ana Paula Marin Granados" loading="lazy" decoding="async" width="300" height="300">
                    </div>
                    <h3>Ana Paula Marin Granados</h3>
                    <p class="member-role">Especialista en Nutrición</p>
                    <p class="member-bio">Experta en planes nutricionales personalizados y educación alimentaria.</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <img src="<?php echo asset('img/icons/FridaIsabel.jpeg'); ?>" alt="Frida Isabel Azuara Salazar" loading="lazy" decoding="async" width="300" height="300">
                    </div>
                    <h3>Frida Isabel Azuara Salazar</h3>
                    <p class="member-role">Coordinadora de Actividad Física</p>
                    <p class="member-bio">Especialista en diseño de rutinas de ejercicio y promoción del movimiento.</p>
                </div>

                <div class="team-member">
                    <div class="member-photo">
                        <img src="<?php echo asset('img/icons/AbnerBorrego.jpeg'); ?>" alt="Abner Borrego Vargas" loading="lazy" decoding="async" width="300" height="300" style="object-position: center 30%;">
                    </div>
                    <h3>Abner Borrego Vargas</h3>
                    <p class="member-role">Director de Tecnología</p>
                    <p class="member-bio">Ingeniero en sistemas enfocado en crear soluciones digitales innovadoras para el bienestar.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-col">
                    <h4>BIEN<span>IEST</span>AR</h4>
                    <p>Tu plataforma integral de bienestar laboral</p>
                </div>
                <div class="footer-col">
                    <h5>Enlaces</h5>
                    <ul>
                        <li><a href="<?php echo BASE_URL; ?>/">Inicio</a></li>
                        <li><a href="<?php echo url('about'); ?>">Nosotros</a></li>
                        <li><a href="<?php echo url('login'); ?>">Iniciar Sesión</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h5>Contacto</h5>
                    <ul>
                        <li>📧 contacto@bieniestar.mx</li>
                        <li>📱 (833) 123-4567</li>
                        <li>📍 IEST Anáhuac, Tampico</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> BIENIESTAR. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script defer src="<?php echo asset('js/main.js'); ?>"></script>
    <script>
        (function () {
            var toggle = document.getElementById('aboutDarkToggle');
            var moon = document.getElementById('aboutIconMoon');
            var sun = document.getElementById('aboutIconSun');

            function applyTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    if (moon) moon.style.display = 'none';
                    if (sun) sun.style.display = 'block';
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    if (moon) moon.style.display = 'block';
                    if (sun) sun.style.display = 'none';
                }
            }

            applyTheme(localStorage.getItem('bieniestar-theme') || 'light');

            if (toggle) {
                toggle.addEventListener('click', function () {
                    var current = document.documentElement.getAttribute('data-theme');
                    var next = current === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('bieniestar-theme', next);
                    applyTheme(next);
                });
            }
        })();
    </script>
</body>
</html>