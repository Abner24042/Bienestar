<?php
require_once '../../app/config/config.php';
require_once '../../app/controllers/AuthController.php';

$authController = new AuthController();

if (!$authController->isAuthenticated()) {
    redirect('login');
}

$user = $authController->getCurrentUser();
$currentPage = 'salud-mental';
$pageTitle = 'Salud Mental';
$additionalCSS = ['salud-mental.css'];
?>

<?php include '../../app/views/layouts/header.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Salud Mental <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg></h1>
        <p>Cuida tu bienestar emocional y mental</p>
    </div>

    <!-- Hero compacto: Test + Mood check -->
    <div class="sm-hero">
        <div class="sm-hero-left">
            <span class="sm-hero-badge">Test de Bienestar Mental</span>
            <h2>¿Cómo está tu salud mental hoy?</h2>
            <p>Evalúa tu estado emocional con nuestro test validado y recibe recomendaciones personalizadas.</p>
            <button class="btn btn-primary" data-modal-open="modalTest" style="font-size:1rem;padding:12px 28px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align:middle;margin-right:6px"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                Realizar Test
            </button>
        </div>
        <div class="sm-hero-right">
            <p class="mood-label">¿Cómo te sientes ahora?</p>
            <div class="mood-options">
                <button class="mood-btn" data-mood="5" title="Muy bien">😄</button>
                <button class="mood-btn" data-mood="4" title="Bien">🙂</button>
                <button class="mood-btn" data-mood="3" title="Regular">😐</button>
                <button class="mood-btn" data-mood="2" title="Mal">😔</button>
                <button class="mood-btn" data-mood="1" title="Muy mal">😟</button>
            </div>
            <p class="mood-response" id="moodResponse"></p>
        </div>
    </div>

    <!-- Widget de Respiración Guiada -->
    <div class="breathing-widget sm-reveal">
        <div class="breathing-info">
            <div class="breathing-badge">Ejercicio interactivo</div>
            <h3>Respiración 4-4-4</h3>
            <p>Reduce el estrés en menos de 2 minutos. Sigue el ritmo del círculo.</p>
            <div class="breathing-phase-label" id="breathingPhase">Presiona iniciar cuando estés listo</div>
            <div class="breathing-counter" id="breathingCounter" style="display:none;">
                <span id="breathingCount">4</span>
            </div>
            <button class="btn btn-outline breathing-btn" id="breathingBtn" onclick="toggleBreathing()">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:6px"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                Iniciar
            </button>
        </div>
        <div class="breathing-visual">
            <div class="breathing-ring-outer"></div>
            <div class="breathing-ring-mid"></div>
            <div class="breathing-circle" id="breathingCircle">
                <span id="breathingEmoji">🌬️</span>
            </div>
        </div>
    </div>

    <!-- Recursos de Salud Mental -->
    <div class="mental-health-resources">
        <h2>Recursos y Técnicas <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg></h2>
        <div class="resources-grid">
            <div class="resource-card sm-reveal">
                <div class="resource-icon" style="color:#9c27b0;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4" r="1"/><path d="M12 8c-2 2-4 3-4 6h8c0-3-2-4-4-6Z"/><path d="M8 14c0 3 1.5 5 4 5s4-2 4-5"/><path d="M6 14H4m14 0h2"/></svg>
                </div>
                <h3>Meditación Guiada</h3>
                <p>Técnicas de meditación para reducir el estrés y mejorar el enfoque</p>
                <ul class="resource-list">
                    <li>Meditación de 5 minutos</li>
                    <li>Respiración consciente</li>
                    <li>Escaneo corporal</li>
                    <li>Visualización positiva</li>
                </ul>
                <button class="btn btn-outline" data-modal-open="modalMeditacion">Ver Técnicas</button>
            </div>

            <div class="resource-card sm-reveal">
                <div class="resource-icon" style="color:#2196f3;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 12H3"/><path d="M21 6H3"/><path d="M21 18H3"/></svg>
                </div>
                <h3>Manejo del Estrés</h3>
                <p>Estrategias efectivas para manejar el estrés académico y personal</p>
                <ul class="resource-list">
                    <li>Técnicas de relajación</li>
                    <li>Organización del tiempo</li>
                    <li>Ejercicios de respiración</li>
                    <li>Pausas activas</li>
                </ul>
                <button class="btn btn-outline" data-modal-open="modalEstres">Ver Estrategias</button>
            </div>

            <div class="resource-card sm-reveal">
                <div class="resource-icon" style="color:#3f51b5;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
                </div>
                <h3>Sueño Saludable</h3>
                <p>Mejora la calidad de tu sueño con hábitos saludables</p>
                <ul class="resource-list">
                    <li>Rutina de sueño</li>
                    <li>Higiene del sueño</li>
                    <li>Relajación nocturna</li>
                    <li>Ambiente óptimo</li>
                </ul>
                <button class="btn btn-outline" data-modal-open="modalSueno">Ver Consejos</button>
            </div>

            <div class="resource-card sm-reveal">
                <div class="resource-icon" style="color:#e91e63;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m7.5 0a4.5 4.5 0 1 1-4.5 4.5m4.5-4.5H15m-3 4.5V15"/><circle cx="12" cy="12" r="3"/></svg>
                </div>
                <h3>Mindfulness</h3>
                <p>Practica la atención plena en tu vida diaria</p>
                <ul class="resource-list">
                    <li>Ejercicios diarios</li>
                    <li>Atención al presente</li>
                    <li>Aceptación emocional</li>
                    <li>Gratitud diaria</li>
                </ul>
                <button class="btn btn-outline" data-modal-open="modalMindfulness">Ver Prácticas</button>
            </div>
        </div>
    </div>

    <!-- Consejos — tarjetas horizontales -->
    <div class="quick-tips sm-reveal">
        <h2>Consejos para tu Bienestar <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" style="vertical-align:middle;margin-left:4px"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></h2>
        <div class="tips-scroll">
            <div class="tip-card">
                <div class="tip-icon">🤝</div>
                <div class="tip-num">01</div>
                <h3>Conexión Social</h3>
                <p>Mantén contacto regular con amigos y familia. Las relaciones sociales son fundamentales para la salud mental.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">🏃</div>
                <div class="tip-num">02</div>
                <h3>Actividad Física</h3>
                <p>El ejercicio regular libera endorfinas que mejoran el estado de ánimo y reducen el estrés.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">🛑</div>
                <div class="tip-num">03</div>
                <h3>Establece Límites</h3>
                <p>Aprende a decir no y establece límites saludables en tu vida personal y académica.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">💬</div>
                <div class="tip-num">04</div>
                <h3>Busca Ayuda</h3>
                <p>No dudes en buscar apoyo profesional si lo necesitas. Pedir ayuda es un signo de fortaleza.</p>
            </div>
            <div class="tip-card">
                <div class="tip-icon">📖</div>
                <div class="tip-num">05</div>
                <h3>Desconéctate</h3>
                <p>Tómate momentos sin pantallas. Leer, caminar o simplemente descansar recarga tu energía mental.</p>
            </div>
        </div>
    </div>

    <!-- Línea de Ayuda -->
    <div class="help-line sm-reveal">
        <div class="help-line-content">
            <div class="help-line-text">
                <h2>¿Necesitas Hablar con Alguien?</h2>
                <p>Si estás pasando por un momento difícil, recuerda que no estás solo.</p>
            </div>
            <div class="help-contacts">
                <div class="contact-item">
                    <span class="contact-icon">📞</span>
                    <div>
                        <strong>Línea de Crisis 24/7</strong>
                        <a href="tel:8005553535">800-555-3535</a>
                    </div>
                </div>
                <div class="contact-item">
                    <span class="contact-icon">✉️</span>
                    <div>
                        <strong>Psicología IEST</strong>
                        <a href="mailto:psicologia@iest.edu.mx">psicologia@iest.edu.mx</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modales (sin cambios) -->
<?php
$modalId = 'modalMeditacion';
$modalTitle = 'Meditación Guiada';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>La meditación es una práctica que te ayuda a entrenar tu mente para enfocarte y redirigir tus pensamientos. Aquí tienes técnicas que puedes practicar en cualquier momento.</p>
    </div>
    <div class="technique-section">
        <h4>Meditación de 5 Minutos</h4>
        <p>Ideal para principiantes o cuando tienes poco tiempo.</p>
        <ol>
            <li>Siéntate cómodamente con la espalda recta</li>
            <li>Cierra los ojos suavemente</li>
            <li>Respira profundamente: inhala 4 segundos, sostén 4 segundos, exhala 6 segundos</li>
            <li>Enfoca tu atención en la respiración</li>
            <li>Si tu mente divaga, regresa suavemente a la respiración</li>
            <li>Continúa por 5 minutos</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Respiración Consciente (4-7-8)</h4>
        <p>Técnica poderosa para calmar el sistema nervioso rápidamente.</p>
        <ol>
            <li>Coloca la punta de la lengua detrás de los dientes superiores</li>
            <li><strong>Inhala</strong> por la nariz contando hasta <strong>4</strong></li>
            <li><strong>Sostén</strong> la respiración contando hasta <strong>7</strong></li>
            <li><strong>Exhala</strong> por la boca contando hasta <strong>8</strong></li>
            <li>Repite el ciclo 4 veces</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Escaneo Corporal</h4>
        <p>Conecta con tu cuerpo y libera la tensión acumulada.</p>
        <ol>
            <li>Acuéstate o siéntate cómodamente</li>
            <li>Cierra los ojos y respira profundamente 3 veces</li>
            <li>Comienza por los pies: nota cualquier sensación sin juzgarla</li>
            <li>Sube lentamente: piernas, abdomen, pecho, brazos, cuello, cabeza</li>
            <li>En cada zona, respira hacia esa parte y libera la tensión al exhalar</li>
            <li>Finaliza con 3 respiraciones profundas</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Visualización Positiva</h4>
        <p>Usa el poder de tu imaginación para generar calma y bienestar.</p>
        <ol>
            <li>Cierra los ojos y respira profundamente</li>
            <li>Imagina un lugar que te transmita paz (playa, bosque, montaña)</li>
            <li>Involucra todos tus sentidos: ¿qué ves, escuchas, hueles, sientes?</li>
            <li>Permanece en ese lugar mental por 5-10 minutos</li>
            <li>Antes de abrir los ojos, lleva esa sensación de paz contigo</li>
        </ol>
    </div>
    <div class="resource-tip">
        <strong>Consejo:</strong> Practica al menos una de estas técnicas diariamente durante 21 días para crear un hábito. Puedes empezar con solo 5 minutos al día.
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<?php
$modalId = 'modalEstres';
$modalTitle = 'Manejo del Estrés';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>El estrés es una respuesta natural. Aquí encontrarás estrategias probadas para manejarlo efectivamente.</p>
    </div>
    <div class="technique-section">
        <h4>Técnicas de Relajación Muscular Progresiva</h4>
        <p>Reduce la tensión física que acompaña al estrés.</p>
        <ol>
            <li>Siéntate o acuéstate en un lugar tranquilo</li>
            <li>Comienza por los pies: tensa los músculos por 5 segundos</li>
            <li>Suelta y relaja por 15 segundos, notando la diferencia</li>
            <li>Avanza hacia arriba: pantorrillas, muslos, abdomen, manos, brazos, hombros, cara</li>
            <li>Termina tensando todo el cuerpo 5 segundos y soltando</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Organización del Tiempo</h4>
        <p>Planificar reduce la sensación de estar abrumado.</p>
        <ul>
            <li><strong>Técnica Pomodoro:</strong> Trabaja 25 minutos, descansa 5. Cada 4 ciclos, descansa 15-30 minutos</li>
            <li><strong>Matriz de Eisenhower:</strong> Clasifica tareas en urgente/importante para priorizar</li>
            <li><strong>Regla de los 2 minutos:</strong> Si algo toma menos de 2 minutos, hazlo ahora</li>
            <li><strong>Planificación semanal:</strong> Dedica 15 minutos cada domingo a planear tu semana</li>
        </ul>
    </div>
    <div class="technique-section">
        <h4>Ejercicios de Respiración Anti-Estrés</h4>
        <ul>
            <li><strong>Respiración cuadrada:</strong> Inhala 4s, sostén 4s, exhala 4s, sostén 4s</li>
            <li><strong>Suspiro fisiológico:</strong> Doble inhalación por nariz + exhalación larga por boca</li>
            <li><strong>Respiración abdominal:</strong> Pon la mano en el abdomen, respira para que se eleve (no el pecho)</li>
        </ul>
    </div>
    <div class="resource-tip">
        <strong>Recuerda:</strong> El estrés moderado es normal y puede ser motivador. El objetivo no es eliminarlo, sino manejarlo.
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<?php
$modalId = 'modalSueno';
$modalTitle = 'Sueño Saludable';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>Dormir bien es fundamental para el rendimiento, la memoria y la salud emocional. Necesitas entre 7 y 9 horas de sueño de calidad.</p>
    </div>
    <div class="technique-section">
        <h4>Rutina de Sueño</h4>
        <ol>
            <li>Establece una hora fija para dormir y despertar (incluso fines de semana)</li>
            <li>30 minutos antes: apaga pantallas (celular, laptop, TV)</li>
            <li>20 minutos antes: haz algo relajante (leer, estiramientos, té caliente)</li>
            <li>10 minutos antes: prepara tu espacio (oscuridad, temperatura fresca)</li>
            <li>En la cama: practica respiración profunda o escaneo corporal</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Higiene del Sueño</h4>
        <ul>
            <li><strong>Evita la cafeína</strong> después de las 2:00 PM</li>
            <li><strong>No hagas siestas</strong> mayores a 20 minutos después de las 3:00 PM</li>
            <li><strong>Haz ejercicio</strong> regularmente, pero no 3 horas antes de dormir</li>
            <li><strong>Evita comidas pesadas</strong> en las 2 horas previas a acostarte</li>
            <li><strong>Usa la cama solo para dormir:</strong> no estudies ni trabajes en ella</li>
        </ul>
    </div>
    <div class="resource-tip">
        <strong>Dato:</strong> Después de 17 horas sin dormir, tu rendimiento cognitivo es equivalente a un nivel de alcohol de 0.05%. ¡Prioriza tu descanso!
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<?php
$modalId = 'modalMindfulness';
$modalTitle = 'Mindfulness - Atención Plena';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>El mindfulness consiste en prestar atención al momento presente sin juzgar. Reduce la ansiedad, mejora la concentración y aumenta el bienestar general.</p>
    </div>
    <div class="technique-section">
        <h4>Atención al Presente (Técnica 5-4-3-2-1)</h4>
        <p>Cuando te sientas ansioso, usa tus sentidos para anclarte al presente.</p>
        <ol>
            <li>Nombra <strong>5 cosas</strong> que puedes <strong>ver</strong></li>
            <li>Nombra <strong>4 cosas</strong> que puedes <strong>tocar</strong></li>
            <li>Nombra <strong>3 cosas</strong> que puedes <strong>escuchar</strong></li>
            <li>Nombra <strong>2 cosas</strong> que puedes <strong>oler</strong></li>
            <li>Nombra <strong>1 cosa</strong> que puedes <strong>saborear</strong></li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Aceptación Emocional</h4>
        <ol>
            <li><strong>Reconoce:</strong> "Estoy sintiendo [emoción]" - ponle nombre</li>
            <li><strong>Permite:</strong> No intentes eliminar la emoción, déjala estar</li>
            <li><strong>Observa:</strong> ¿Dónde la sientes en tu cuerpo?</li>
            <li><strong>Respira:</strong> Dirige tu respiración hacia esa sensación</li>
            <li><strong>Suelta:</strong> Con cada exhalación, la intensidad disminuye</li>
        </ol>
    </div>
    <div class="technique-section">
        <h4>Práctica de Gratitud Diaria</h4>
        <ul>
            <li>Cada mañana, piensa en 3 cosas por las que estás agradecido</li>
            <li>Sé específico: no solo "mi familia", sino "la llamada que tuve con mi mamá ayer"</li>
            <li>Incluye cosas pequeñas: un buen café, una sonrisa, el clima agradable</li>
        </ul>
    </div>
    <div class="resource-tip">
        <strong>Dato científico:</strong> Practicar mindfulness por 8 semanas cambia físicamente las áreas del cerebro asociadas con la memoria, la empatía y el estrés.
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<!-- Modal: Test de Bienestar Mental -->
<?php
$modalId = 'modalTest';
$modalTitle = 'Test de Bienestar Mental';
$modalSize = 'large';
$modalContent = '
<div class="test-intro">
    <p><strong>Este test te ayudará a evaluar tu bienestar emocional actual.</strong></p>
    <p>Responde honestamente las siguientes preguntas. No hay respuestas correctas o incorrectas.</p>
</div>

<form id="mentalHealthTest" class="mental-test-form">
    <div class="test-question">
        <h4>1. ¿Con qué frecuencia te has sentido nervioso o estresado?</h4>
        <div class="test-options">
            <label><input type="radio" name="q1" value="0"> Nunca</label>
            <label><input type="radio" name="q1" value="1"> Rara vez</label>
            <label><input type="radio" name="q1" value="2"> A veces</label>
            <label><input type="radio" name="q1" value="3"> Frecuentemente</label>
            <label><input type="radio" name="q1" value="4"> Siempre</label>
        </div>
    </div>
    <div class="test-question">
        <h4>2. ¿Has tenido dificultad para concentrarte?</h4>
        <div class="test-options">
            <label><input type="radio" name="q2" value="0"> Nunca</label>
            <label><input type="radio" name="q2" value="1"> Rara vez</label>
            <label><input type="radio" name="q2" value="2"> A veces</label>
            <label><input type="radio" name="q2" value="3"> Frecuentemente</label>
            <label><input type="radio" name="q2" value="4"> Siempre</label>
        </div>
    </div>
    <div class="test-question">
        <h4>3. ¿Te has sentido triste o decaído?</h4>
        <div class="test-options">
            <label><input type="radio" name="q3" value="0"> Nunca</label>
            <label><input type="radio" name="q3" value="1"> Rara vez</label>
            <label><input type="radio" name="q3" value="2"> A veces</label>
            <label><input type="radio" name="q3" value="3"> Frecuentemente</label>
            <label><input type="radio" name="q3" value="4"> Siempre</label>
        </div>
    </div>
    <div class="test-question">
        <h4>4. ¿Has tenido problemas para dormir?</h4>
        <div class="test-options">
            <label><input type="radio" name="q4" value="0"> Nunca</label>
            <label><input type="radio" name="q4" value="1"> Rara vez</label>
            <label><input type="radio" name="q4" value="2"> A veces</label>
            <label><input type="radio" name="q4" value="3"> Frecuentemente</label>
            <label><input type="radio" name="q4" value="4"> Siempre</label>
        </div>
    </div>
    <div class="test-question">
        <h4>5. ¿Te sientes optimista sobre el futuro?</h4>
        <div class="test-options">
            <label><input type="radio" name="q5" value="4"> Siempre</label>
            <label><input type="radio" name="q5" value="3"> Frecuentemente</label>
            <label><input type="radio" name="q5" value="2"> A veces</label>
            <label><input type="radio" name="q5" value="1"> Rara vez</label>
            <label><input type="radio" name="q5" value="0"> Nunca</label>
        </div>
    </div>
    <div class="test-actions">
        <button type="submit" class="btn btn-primary btn-block btn-large">Ver Resultados</button>
    </div>
</form>

<div id="testResults" class="test-results" style="display: none;">
    <h3>Tus Resultados</h3>
    <div class="result-score">
        <div class="score-circle">
            <span class="score-value" id="scoreValue">0</span>
            <span class="score-max">/20</span>
        </div>
    </div>
    <div class="result-interpretation" id="resultInterpretation"></div>
    <div class="result-recommendations" id="resultRecommendations"></div>
</div>
';
include '../../app/views/components/modal.php';
?>

<?php
$additionalJS = ['salud-mental.js'];
include '../../app/views/layouts/footer.php';
?>
