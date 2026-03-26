
document.addEventListener('DOMContentLoaded', function () {
    initMentalHealthTest();
    initMoodPicker();
    initScrollReveal();
});

/* =====================
   MOOD PICKER
   ===================== */
var moodMessages = {
    5: '¡Qué bien! Sigue cuidándote 🌟',
    4: 'Está bien, un día normal 😊',
    3: 'Recuerda respirar y tomarte un descanso',
    2: 'Prueba el ejercicio de respiración abajo ↓',
    1: 'Considera hablar con alguien de confianza 💙'
};

function initMoodPicker() {
    document.querySelectorAll('.mood-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.mood-btn').forEach(function (b) { b.classList.remove('selected'); });
            btn.classList.add('selected');

            var response = document.getElementById('moodResponse');
            if (response) {
                response.textContent = moodMessages[btn.dataset.mood] || '';
                response.classList.add('visible');
            }
        });
    });
}

/* =====================
   EJERCICIO DE RESPIRACIÓN
   ===================== */
var breathingActive = false;
var breathingTimeout = null;
var breathingPhases = [
    { name: 'Inhala…', emoji: '🫁', duration: 4000, scale: 1.55 },
    { name: 'Sostén', emoji: '⏸️', duration: 4000, scale: 1.55 },
    { name: 'Exhala…', emoji: '💨', duration: 4000, scale: 0.65 },
    { name: 'Espera', emoji: '🌙', duration: 2000, scale: 0.65 },
];
var breathPhaseIndex = 0;
var breathCountInterval = null;

function toggleBreathing() {
    breathingActive = !breathingActive;
    var btn = document.getElementById('breathingBtn');
    var phase = document.getElementById('breathingPhase');
    var counter = document.getElementById('breathingCounter');
    var circle = document.getElementById('breathingCircle');

    if (breathingActive) {
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:6px"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>Pausar';
        counter.style.display = 'block';
        breathPhaseIndex = 0;
        runBreathPhase();
    } else {
        btn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;margin-right:6px"><polygon points="5 3 19 12 5 21 5 3"/></svg>Iniciar';
        clearTimeout(breathingTimeout);
        clearInterval(breathCountInterval);
        phase.textContent = 'Presiona iniciar cuando estés listo';
        counter.style.display = 'none';
        circle.style.transition = 'transform 0.5s ease';
        circle.style.transform = 'scale(1)';
        document.getElementById('breathingEmoji').textContent = '🌬️';
    }
}

function runBreathPhase() {
    if (!breathingActive) return;

    var p = breathingPhases[breathPhaseIndex];
    var phase = document.getElementById('breathingPhase');
    var counter = document.getElementById('breathingCounter');
    var count = document.getElementById('breathingCount');
    var circle = document.getElementById('breathingCircle');
    var emoji = document.getElementById('breathingEmoji');

    phase.textContent = p.name;
    emoji.textContent = p.emoji;

    // Animar círculo
    circle.classList.add('animating');
    circle.style.setProperty('--breath-duration', p.duration + 'ms');
    circle.style.transition = 'transform ' + p.duration + 'ms ease-in-out';
    circle.style.transform = 'scale(' + p.scale + ')';

    // Contador regresivo
    var secs = p.duration / 1000;
    count.textContent = secs;
    clearInterval(breathCountInterval);
    breathCountInterval = setInterval(function () {
        secs--;
        count.textContent = Math.max(0, secs);
        if (secs <= 0) clearInterval(breathCountInterval);
    }, 1000);

    breathingTimeout = setTimeout(function () {
        breathPhaseIndex = (breathPhaseIndex + 1) % breathingPhases.length;
        runBreathPhase();
    }, p.duration);
}

/* =====================
   SCROLL REVEAL
   ===================== */
function initScrollReveal() {
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('sm-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });

    document.querySelectorAll('.sm-reveal').forEach(function (el) {
        observer.observe(el);
    });
}

/* =====================
   TEST DE BIENESTAR
   ===================== */
function initMentalHealthTest() {
    var testForm = document.getElementById('mentalHealthTest');
    if (testForm) testForm.addEventListener('submit', handleTestSubmit);
}

function handleTestSubmit(e) {
    e.preventDefault();
    var formData = new FormData(e.target);
    var questions = ['q1', 'q2', 'q3', 'q4', 'q5'];
    var allAnswered = questions.every(function (q) { return formData.get(q); });

    if (!allAnswered) {
        showToast('Por favor responde todas las preguntas', 'warning');
        return;
    }

    var score = questions.reduce(function (sum, q) { return sum + parseInt(formData.get(q)); }, 0);
    showTestResults(score);
    saveTestResult(score);
}

function showTestResults(score) {
    var testForm = document.getElementById('mentalHealthTest');
    var resultsDiv = document.getElementById('testResults');
    var scoreValue = document.getElementById('scoreValue');

    testForm.style.display = 'none';
    resultsDiv.style.display = 'block';
    animateScore(scoreValue, score);

    var result = interpretScore(score);
    document.getElementById('resultInterpretation').innerHTML = '<h4>' + result.title + '</h4><p>' + result.description + '</p>';
    document.getElementById('resultRecommendations').innerHTML = '<h4>Recomendaciones:</h4><ul>' + result.recommendations.map(function (r) { return '<li>' + r + '</li>'; }).join('') + '</ul>';
}

function animateScore(element, finalScore) {
    var current = 0;
    var inc = finalScore / (1500 / 16);
    var timer = setInterval(function () {
        current += inc;
        if (current >= finalScore) { current = finalScore; clearInterval(timer); }
        element.textContent = Math.floor(current);
    }, 16);
}

function interpretScore(score) {
    if (score <= 5) return {
        title: 'Excelente Bienestar Mental',
        description: 'Tu bienestar emocional está en un nivel muy saludable. Continúa con tus buenos hábitos y prácticas de autocuidado.',
        recommendations: ['Mantén tu rutina actual de autocuidado', 'Comparte tus estrategias con otros', 'Sigue practicando mindfulness', 'Mantén conexiones sociales saludables']
    };
    if (score <= 10) return {
        title: 'Buen Bienestar Mental',
        description: 'Tu bienestar está en un nivel saludable, aunque hay áreas que podrías mejorar.',
        recommendations: ['Practica técnicas de relajación regularmente', 'Establece una rutina de sueño consistente', 'Dedica tiempo a actividades que disfrutes', 'Mantén una red de apoyo social activa', 'Considera el ejercicio físico regular']
    };
    if (score <= 15) return {
        title: 'Bienestar Mental Moderado',
        description: 'Estás experimentando algunos desafíos emocionales. Toma medidas proactivas para mejorar tu bienestar.',
        recommendations: ['Habla con alguien de confianza', 'Establece límites saludables en tus actividades', 'Practica manejo del estrés diariamente', 'Prioriza el autocuidado y el descanso', 'Considera buscar apoyo profesional']
    };
    return {
        title: 'Se Recomienda Apoyo Profesional',
        description: 'Parece que estás experimentando desafíos significativos. Buscar apoyo profesional es un signo de fortaleza, no de debilidad.',
        recommendations: ['⚠️ Contacta con los servicios de psicología del IEST', '⚠️ Habla con un profesional de salud mental', 'No enfrentes esto solo, busca apoyo', 'Línea de crisis 24/7: 800-555-3535', 'Mantente en contacto con personas de confianza']
    };
}

function resetTest() {
    var testForm = document.getElementById('mentalHealthTest');
    var resultsDiv = document.getElementById('testResults');
    testForm.reset();
    testForm.style.display = 'block';
    resultsDiv.style.display = 'none';
}

function getNivelFromScore(score) {
    if (score <= 5) return 'Excelente';
    if (score <= 10) return 'Bueno';
    if (score <= 15) return 'Moderado';
    return 'Atención';
}

async function saveTestResult(score) {
    try {
        await fetch(API_URL + '/test/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ puntaje: score, nivel: getNivelFromScore(score) })
        });
    } catch (e) { }
}
