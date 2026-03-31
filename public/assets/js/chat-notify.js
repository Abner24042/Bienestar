
// chat-notify.js — corre en TODAS las paginas, no solo en chat/dashboard
// se encarga del polling de badges y el sonido de notificacion
// chat.js (que solo carga en dashboard y chat) ya no duplica esto

if (!CURRENT_USER_ID) { /* si no hay sesion no hace nada */ }
else (function () {

    // web audio api — no necesita ningun archivo de audio externo
    let audioCtx = null;
    let badgePrev = -1; // -1 = primera carga, no suena para no asustar

    function unlockAudio() {
        if (audioCtx) return;
        try { audioCtx = new (window.AudioContext || window.webkitAudioContext)(); } catch (e) { }
    }

    // expuesto globalmente pa que chat.js lo llame cuando llega mensaje en conv activa
    window.chatSonido = function () {
        if (!audioCtx) return;
        try {
            const ctx = audioCtx;
            if (ctx.state === 'suspended') ctx.resume(); // ios lo suspende a veces

            const t = ctx.currentTime;
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);

            // dos tonos tipo ding — 880hz luego sube a 1100hz
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, t);
            osc.frequency.setValueAtTime(1100, t + 0.08);

            gain.gain.setValueAtTime(0, t);
            gain.gain.linearRampToValueAtTime(0.14, t + 0.01);
            gain.gain.exponentialRampToValueAtTime(0.001, t + 0.28);

            osc.start(t);
            osc.stop(t + 0.28);
        } catch (e) { }
    };

    // actualiza los badges del nav y fab — compartida con chat.js
    window.chatActualizarBadgeGlobal = async function () {
        try {
            const res = await fetch(API_URL + '/chat/no-leidos');
            const data = await res.json();
            if (!data.success) return;
            const total = data.total;

            // suena solo si subieron los no-leidos, no en la primera carga
            // y solo si chat.js no tiene ya un polling activo de mensajes (evita doble ding)
            if (badgePrev >= 0 && total > badgePrev && !window.chatPollingInt) {
                window.chatSonido();
            }
            badgePrev = total;

            document.querySelectorAll('.chat-nav-badge').forEach(el => {
                el.textContent = total;
                el.style.display = total > 0 ? 'inline' : 'none';
            });

            const fabBadge = document.getElementById('chatFabBadge');
            if (fabBadge) {
                fabBadge.textContent = total;
                fabBadge.style.display = total > 0 ? 'flex' : 'none';
            }
        } catch (e) { }
    };

    // arranca el polling global — chat.js ya no necesita su propio setInterval para badges
    window.chatNotifyActive = true;
    setInterval(window.chatActualizarBadgeGlobal, 5000);
    window.chatActualizarBadgeGlobal(); // carga inmediata al entrar a cualquier pagina

    // desbloquear audio en el primer gesto del usuario (politica de autoplay)
    document.addEventListener('DOMContentLoaded', function () {
        var unlockOnce = function () {
            unlockAudio();
            ['click', 'touchstart', 'keydown'].forEach(function (ev) {
                document.removeEventListener(ev, unlockOnce);
            });
        };
        ['click', 'touchstart', 'keydown'].forEach(function (ev) {
            document.addEventListener(ev, unlockOnce);
        });
    });

})();
