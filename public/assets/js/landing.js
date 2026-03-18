/**
 * BIENIESTAR - JavaScript del Landing Page
 * Efectos parallax y scroll optimizados con rAF
 */

// Transición suave al ir al login: desvanece el contenido, deja el fondo
(function() {
    document.querySelectorAll('a[href*="login"]').forEach(function(link) {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            var href = this.href;
            var els = document.querySelectorAll('.sticky-nav, .hero-content, .hero-overlay, .scroll-indicator, .features-section, .services-section, .cta-section, .footer');
            els.forEach(function(el) {
                el.style.transition = 'opacity 0.4s ease';
                el.style.opacity = '0';
            });
            setTimeout(function() { window.location.href = href; }, 420);
        });
    });
})();

(function() {
    var heroSection = document.getElementById('heroSection');
    if (!heroSection) return;

    var heroBackground = document.getElementById('heroBackground');
    var heroContent = document.getElementById('heroContent');
    var stickyNav = document.getElementById('stickyNav');

    var ticking = false;

    // Scroll listener con passive y requestAnimationFrame
    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(handleScroll);
            ticking = true;
        }
    }, { passive: true });

    function handleScroll() {
        ticking = false;
        var scrollPosition = window.pageYOffset;
        var windowHeight = window.innerHeight;

        // Parallax solo en la parte superior
        if (scrollPosition < windowHeight * 1.5) {
            var scrollPercent = scrollPosition / windowHeight;

            if (scrollPosition > 50) {
                heroSection.classList.add('scrolled');
                heroSection.style.height = Math.max(30, 100 - (scrollPercent * 70)) + 'vh';
                heroBackground.style.transform = 'scale(' + (1 + scrollPercent * 0.3) + ')';
                heroContent.style.opacity = Math.max(0, 1 - scrollPercent * 2);
                heroContent.style.transform = 'translateY(-' + (scrollPercent * 50) + 'px)';
            } else {
                heroSection.classList.remove('scrolled');
                heroSection.style.height = '100vh';
                heroBackground.style.transform = 'scale(1)';
                heroContent.style.opacity = '1';
                heroContent.style.transform = 'translateY(0)';
            }
        }

        // Nav sticky
        if (scrollPosition > 500) {
            stickyNav.classList.add('show');
        } else {
            stickyNav.classList.remove('show');
        }
    }

    // IntersectionObserver para animaciones de cards
    var cardObserver = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
                cardObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15 });

    document.querySelectorAll('.feature-card, .service-card').forEach(function(card) {
        cardObserver.observe(card);
    });

    // Animación de números cuando features section es visible
    var numbersAnimated = false;
    var featuresSection = document.querySelector('.features-section');
    if (featuresSection) {
        var featuresObserver = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting && !numbersAnimated) {
                numbersAnimated = true;
                animateNumbers();
                featuresObserver.unobserve(featuresSection);
            }
        }, { threshold: 0.3 });
        featuresObserver.observe(featuresSection);
    }

    function animateNumbers() {
        document.querySelectorAll('.feature-number').forEach(function(number) {
            var originalText = number.textContent;
            var finalValue, suffix = '';

            if (originalText.includes('/')) {
                var parts = originalText.split('/');
                finalValue = parseInt(parts[0]);
                suffix = '/' + parts[1];
            } else if (originalText.includes('%')) {
                finalValue = parseInt(originalText.replace('%', ''));
                suffix = '%';
            } else {
                finalValue = parseInt(originalText.replace(/\D/g, ''));
            }

            var duration = 1500;
            var steps = 40;
            var increment = finalValue / steps;
            var current = 0;

            var timer = setInterval(function() {
                current += increment;
                if (current >= finalValue) {
                    number.textContent = finalValue + suffix;
                    clearInterval(timer);
                } else {
                    number.textContent = Math.floor(current) + suffix;
                }
            }, duration / steps);
        });
    }
})();
