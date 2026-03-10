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
        <h1>Salud Mental <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><path d="M9.5 2A2.5 2.5 0 0 1 12 4.5v15a2.5 2.5 0 0 1-4.96-.44 2.5 2.5 0 0 1-2.96-3.08 3 3 0 0 1-.34-5.58 2.5 2.5 0 0 1 1.32-4.24 2.5 2.5 0 0 1 1.98-3A2.5 2.5 0 0 1 9.5 2Z"/><path d="M14.5 2A2.5 2.5 0 0 0 12 4.5v15a2.5 2.5 0 0 0 4.96-.44 2.5 2.5 0 0 0 2.96-3.08 3 3 0 0 0 .34-5.58 2.5 2.5 0 0 0-1.32-4.24 2.5 2.5 0 0 0-1.98-3A2.5 2.5 0 0 0 14.5 2Z"/></svg></h1>
        <p>Cuida tu bienestar emocional y mental</p>
    </div>

    <!-- Test Destacado -->
    <div class="featured-test">
        <div class="test-content">
            <h2>Test de Bienestar Mental</h2>
            <p>Evalúa tu estado emocional actual con nuestro test científicamente validado</p>
            <button class="btn btn-primary btn-large" data-modal-open="modalTest">Realizar Test</button>
        </div>
    </div>

    <!-- Recursos de Salud Mental -->
    <div class="mental-health-resources">
        <h2>Recursos y Técnicas <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/></svg></h2>
        <div class="resources-grid">
            <!-- Recurso 1: Meditación -->
            <div class="resource-card">
                <div class="resource-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#9c27b0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="4" r="1"/><path d="M12 8c-2 2-4 3-4 6h8c0-3-2-4-4-6Z"/><path d="M8 14c0 3 1.5 5 4 5s4-2 4-5"/><path d="M6 14H4m14 0h2"/></svg></div>
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

            <!-- Recurso 2: Manejo del Estrés -->
            <div class="resource-card">
                <div class="resource-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2196f3" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 12H3"/><path d="M21 6H3"/><path d="M21 18H3"/></svg></div>
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

            <!-- Recurso 3: Sueño Saludable -->
            <div class="resource-card">
                <div class="resource-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#3f51b5" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg></div>
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

            <!-- Recurso 4: Mindfulness -->
            <div class="resource-card">
                <div class="resource-icon"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#e91e63" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 7.5a4.5 4.5 0 1 1 4.5 4.5M12 7.5A4.5 4.5 0 1 0 7.5 12M12 7.5V9m-4.5 3a4.5 4.5 0 1 0 4.5 4.5M7.5 12H9m7.5 0a4.5 4.5 0 1 1-4.5 4.5m4.5-4.5H15m-3 4.5V15"/><circle cx="12" cy="12" r="3"/></svg></div>
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

    <!-- Consejos Rápidos -->
    <div class="quick-tips">
        <h2>Consejos para tu Bienestar <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ff6b35" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:4px"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></h2>
        <div class="tips-container">
            <div class="tip-item">
                <div class="tip-number">1</div>
                <div class="tip-content">
                    <h3>Conexión Social</h3>
                    <p>Mantén contacto regular con amigos y familia. Las relaciones sociales son fundamentales para la salud mental.</p>
                </div>
            </div>

            <div class="tip-item">
                <div class="tip-number">2</div>
                <div class="tip-content">
                    <h3>Actividad Física</h3>
                    <p>El ejercicio regular libera endorfinas que mejoran el estado de ánimo y reducen el estrés.</p>
                </div>
            </div>

            <div class="tip-item">
                <div class="tip-number">3</div>
                <div class="tip-content">
                    <h3>Establece Límites</h3>
                    <p>Aprende a decir no y establece límites saludables en tu vida personal y académica.</p>
                </div>
            </div>

            <div class="tip-item">
                <div class="tip-number">4</div>
                <div class="tip-content">
                    <h3>Busca Ayuda</h3>
                    <p>No dudes en buscar apoyo profesional si lo necesitas. Pedir ayuda es un signo de fortaleza.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Línea de Ayuda -->
    <div class="help-line">
        <div class="help-line-content">
            <h2>¿Necesitas Hablar con Alguien?</h2>
            <p>Si estás pasando por un momento difícil, recuerda que no estás solo.</p>
            <div class="help-contacts">
                <div class="contact-item">
                    <strong>Línea de Crisis 24/7:</strong>
                    <a href="tel:8005553535">800-555-3535</a>
                </div>
                <div class="contact-item">
                    <strong>Servicios Psicológicos IEST:</strong>
                    <a href="mailto:psicologia@iest.edu.mx">psicologia@iest.edu.mx</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Meditación Guiada -->
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

<!-- Modal: Manejo del Estrés -->
<?php
$modalId = 'modalEstres';
$modalTitle = 'Manejo del Estrés';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>El estrés académico es una de las principales preocupaciones de los estudiantes universitarios. Aquí encontrarás estrategias probadas para manejarlo efectivamente.</p>
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
        <p>Respuestas rápidas cuando sientes que el estrés te supera.</p>
        <ul>
            <li><strong>Respiración cuadrada:</strong> Inhala 4s, sostén 4s, exhala 4s, sostén 4s</li>
            <li><strong>Suspiro fisiológico:</strong> Doble inhalación por nariz + exhalación larga por boca</li>
            <li><strong>Respiración abdominal:</strong> Pon la mano en el abdomen, respira para que se eleve (no el pecho)</li>
        </ul>
    </div>

    <div class="technique-section">
        <h4>Pausas Activas</h4>
        <p>Rompe el ciclo de estrés con movimiento.</p>
        <ul>
            <li>Cada hora de estudio, levántate y estira por 5 minutos</li>
            <li>Camina al aire libre por 10 minutos entre clases</li>
            <li>Haz rotaciones de cuello y hombros cuando sientas tensión</li>
            <li>Sacude manos y pies para liberar energía acumulada</li>
        </ul>
    </div>

    <div class="resource-tip">
        <strong>Recuerda:</strong> El estrés moderado es normal y puede ser motivador. El objetivo no es eliminarlo, sino manejarlo para que no afecte tu bienestar.
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<!-- Modal: Sueño Saludable -->
<?php
$modalId = 'modalSueno';
$modalTitle = 'Sueño Saludable';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>Dormir bien es fundamental para el rendimiento académico, la memoria y la salud emocional. Un estudiante necesita entre 7 y 9 horas de sueño de calidad.</p>
    </div>

    <div class="technique-section">
        <h4>Rutina de Sueño</h4>
        <p>Crea un ritual nocturno que le indique a tu cuerpo que es hora de dormir.</p>
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
        <p>Hábitos diarios que mejoran la calidad de tu descanso.</p>
        <ul>
            <li><strong>Evita la cafeína</strong> después de las 2:00 PM</li>
            <li><strong>No hagas siestas</strong> mayores a 20 minutos después de las 3:00 PM</li>
            <li><strong>Haz ejercicio</strong> regularmente, pero no 3 horas antes de dormir</li>
            <li><strong>Evita comidas pesadas</strong> en las 2 horas previas a acostarte</li>
            <li><strong>Limita el alcohol:</strong> aunque da sueño, reduce la calidad del descanso</li>
            <li><strong>Usa la cama solo para dormir:</strong> no estudies ni trabajes en ella</li>
        </ul>
    </div>

    <div class="technique-section">
        <h4>Relajación Nocturna</h4>
        <p>Técnicas para calmar la mente antes de dormir.</p>
        <ul>
            <li><strong>Escritura terapéutica:</strong> Escribe tus preocupaciones en un cuaderno para "sacarlas" de tu mente</li>
            <li><strong>Lista de gratitud:</strong> Anota 3 cosas buenas que pasaron hoy</li>
            <li><strong>Meditación guiada:</strong> Usa una app de meditación para dormir (5-10 min)</li>
            <li><strong>Relajación muscular:</strong> Tensa y relaja cada grupo muscular</li>
        </ul>
    </div>

    <div class="technique-section">
        <h4>Ambiente Óptimo para Dormir</h4>
        <p>Tu espacio de descanso importa más de lo que crees.</p>
        <ul>
            <li><strong>Temperatura:</strong> Mantén el cuarto entre 18-22°C</li>
            <li><strong>Oscuridad:</strong> Usa cortinas oscuras o antifaz</li>
            <li><strong>Silencio:</strong> Usa tapones o sonidos blancos si hay ruido</li>
            <li><strong>Orden:</strong> Un espacio limpio y organizado reduce la ansiedad</li>
            <li><strong>Modo nocturno:</strong> Activa el filtro de luz azul en tus dispositivos</li>
        </ul>
    </div>

    <div class="resource-tip">
        <strong>Dato:</strong> Después de 17 horas sin dormir, tu rendimiento cognitivo es equivalente a tener un nivel de alcohol en sangre de 0.05%. ¡Prioriza tu descanso!
    </div>
</div>
';
include '../../app/views/components/modal.php';
?>

<!-- Modal: Mindfulness -->
<?php
$modalId = 'modalMindfulness';
$modalTitle = 'Mindfulness - Atención Plena';
$modalSize = 'large';
$modalContent = '
<div class="resource-modal-content">
    <div class="resource-modal-intro">
        <p>El mindfulness o atención plena consiste en prestar atención al momento presente sin juzgar. Esta práctica reduce la ansiedad, mejora la concentración y aumenta el bienestar general.</p>
    </div>

    <div class="technique-section">
        <h4>Ejercicios Diarios de Mindfulness</h4>
        <p>Incorpora la atención plena en actividades cotidianas.</p>
        <ul>
            <li><strong>Comer consciente:</strong> En una comida al día, come sin distracciones. Observa colores, texturas, sabores y aromas</li>
            <li><strong>Caminar consciente:</strong> Al caminar a clase, nota cada paso, la temperatura del aire, los sonidos a tu alrededor</li>
            <li><strong>Escucha activa:</strong> En una conversación, enfócate completamente en lo que la otra persona dice sin planear tu respuesta</li>
            <li><strong>Ducha consciente:</strong> Siente el agua, la temperatura, los aromas del jabón, las sensaciones en tu piel</li>
        </ul>
    </div>

    <div class="technique-section">
        <h4>Atención al Presente (Técnica 5-4-3-2-1)</h4>
        <p>Cuando te sientas ansioso o desconectado, usa tus sentidos para anclarte al presente.</p>
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
        <p>Aprende a estar con tus emociones sin luchar contra ellas.</p>
        <ol>
            <li><strong>Reconoce:</strong> "Estoy sintiendo [emoción]" - ponle nombre</li>
            <li><strong>Permite:</strong> No intentes eliminar la emoción, déjala estar</li>
            <li><strong>Observa:</strong> ¿Dónde la sientes en tu cuerpo? ¿Tiene forma, color, temperatura?</li>
            <li><strong>Respira:</strong> Dirige tu respiración hacia esa sensación</li>
            <li><strong>Suelta:</strong> Con cada exhalación, imagina que la intensidad disminuye naturalmente</li>
        </ol>
    </div>

    <div class="technique-section">
        <h4>Práctica de Gratitud Diaria</h4>
        <p>La gratitud entrena tu cerebro para enfocarse en lo positivo.</p>
        <ul>
            <li>Cada mañana, piensa en 3 cosas por las que estás agradecido</li>
            <li>Sé específico: no solo "mi familia", sino "la llamada que tuve con mi mamá ayer"</li>
            <li>Incluye cosas pequeñas: un buen café, una sonrisa de alguien, el clima agradable</li>
            <li>Antes de dormir, repasa los mejores momentos del día</li>
        </ul>
    </div>

    <div class="resource-tip">
        <strong>Dato científico:</strong> Estudios demuestran que practicar mindfulness por 8 semanas cambia físicamente las áreas del cerebro asociadas con la memoria, la empatía y el estrés.
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