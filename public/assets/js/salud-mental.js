/**
 * BIENIESTAR - JavaScript de Salud Mental
 */

document.addEventListener('DOMContentLoaded', function() {
    initMentalHealthTest();
});

/**
 * Inicializar test de salud mental
 */
function initMentalHealthTest() {
    const testForm = document.getElementById('mentalHealthTest');
    
    if (testForm) {
        testForm.addEventListener('submit', handleTestSubmit);
    }
}

/**
 * Manejar envío del test
 */
function handleTestSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    // Verificar que todas las preguntas estén respondidas
    const questions = ['q1', 'q2', 'q3', 'q4', 'q5'];
    let allAnswered = true;
    
    for (let q of questions) {
        if (!formData.get(q)) {
            allAnswered = false;
            break;
        }
    }
    
    if (!allAnswered) {
        showToast('Por favor responde todas las preguntas', 'warning');
        return;
    }
    
    // Calcular puntaje
    let score = 0;
    for (let q of questions) {
        score += parseInt(formData.get(q));
    }

    // Mostrar resultados
    showTestResults(score);

    // Guardar resultado en la API
    saveTestResult(score);
}

/**
 * Mostrar resultados del test
 */
function showTestResults(score) {
    const testForm = document.getElementById('mentalHealthTest');
    const resultsDiv = document.getElementById('testResults');
    const scoreValue = document.getElementById('scoreValue');
    const interpretation = document.getElementById('resultInterpretation');
    const recommendations = document.getElementById('resultRecommendations');
    
    // Ocultar formulario
    testForm.style.display = 'none';
    
    // Mostrar resultados
    resultsDiv.style.display = 'block';
    
    // Animar puntaje
    animateScore(scoreValue, score);
    
    // Generar interpretación
    const result = interpretScore(score);
    
    interpretation.innerHTML = `
        <h4>${result.title}</h4>
        <p>${result.description}</p>
    `;
    
    recommendations.innerHTML = `
        <h4>Recomendaciones:</h4>
        <ul>
            ${result.recommendations.map(r => `<li>${r}</li>`).join('')}
        </ul>
    `;
}

/**
 * Animar puntaje
 */
function animateScore(element, finalScore) {
    let current = 0;
    const duration = 1500;
    const increment = finalScore / (duration / 16);
    
    const timer = setInterval(() => {
        current += increment;
        if (current >= finalScore) {
            current = finalScore;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current);
    }, 16);
}

/**
 * Interpretar puntaje
 */
function interpretScore(score) {
    if (score <= 5) {
        return {
            title: 'Excelente Bienestar Mental',
            description: 'Tu bienestar emocional está en un nivel muy saludable. Continúa con tus buenos hábitos y prácticas de autocuidado.',
            recommendations: [
                'Mantén tu rutina actual de autocuidado',
                'Comparte tus estrategias de bienestar con otros',
                'Sigue practicando mindfulness y meditación',
                'Mantén conexiones sociales saludables'
            ]
        };
    } else if (score <= 10) {
        return {
            title: 'Buen Bienestar Mental',
            description: 'Tu bienestar emocional está en un nivel saludable, aunque hay áreas que podrías mejorar. Considera implementar algunas estrategias adicionales de manejo del estrés.',
            recommendations: [
                'Practica técnicas de relajación regularmente',
                'Establece una rutina de sueño consistente',
                'Dedica tiempo a actividades que disfrutes',
                'Mantén una red de apoyo social activa',
                'Considera practicar ejercicio físico regularmente'
            ]
        };
    } else if (score <= 15) {
        return {
            title: 'Bienestar Mental Moderado',
            description: 'Estás experimentando algunos desafíos emocionales. Es importante que tomes medidas proactivas para mejorar tu bienestar. Considera hablar con amigos, familiares o un profesional.',
            recommendations: [
                'Habla con alguien de confianza sobre cómo te sientes',
                'Establece límites saludables en tus actividades',
                'Practica técnicas de manejo del estrés diariamente',
                'Prioriza el autocuidado y el descanso',
                'Considera buscar apoyo profesional',
                'Reduce el consumo de cafeína y mejora tu alimentación'
            ]
        };
    } else {
        return {
            title: 'Se Recomienda Apoyo Profesional',
            description: 'Parece que estás experimentando desafíos significativos en tu bienestar emocional. Es muy importante que busques apoyo profesional. Recuerda que pedir ayuda es un signo de fortaleza, no de debilidad.',
            recommendations: [
                '⚠️ Contacta con los servicios de psicología del IEST',
                '⚠️ Habla con un profesional de salud mental',
                'No enfrentes esto solo, busca apoyo inmediato',
                'Llama a la línea de crisis 24/7: 800-555-3535',
                'Mantente en contacto con personas de confianza',
                'Evita tomar decisiones importantes en este momento',
                'Recuerda: esto es temporal y hay ayuda disponible'
            ]
        };
    }
}

/**
 * Resetear test
 */
function resetTest() {
    const testForm = document.getElementById('mentalHealthTest');
    const resultsDiv = document.getElementById('testResults');

    testForm.reset();
    testForm.style.display = 'block';
    resultsDiv.style.display = 'none';
}

/**
 * Guardar resultado del test en la API
 */
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
    } catch (e) {
        // Silencioso - no interrumpir al usuario si falla el guardado
    }
}