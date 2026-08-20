function solicitarPermisoNotificaciones() {
    if (!('Notification' in window)) {
        return;
    }

    if (Notification.permission === 'default') {
        Notification.requestPermission();
    }
}

let audioContext = null;

function reproducirSonidoNotificacion() {
    try {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) {
            return;
        }

        audioContext = audioContext || new AudioContextClass();
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }

        const ahora = audioContext.currentTime;
        const notas = [{ freq: 880, inicio: 0 }, { freq: 1318.51, inicio: 0.12 }];

        notas.forEach(({ freq, inicio }) => {
            const oscilador = audioContext.createOscillator();
            const ganancia = audioContext.createGain();

            oscilador.type = 'sine';
            oscilador.frequency.setValueAtTime(freq, ahora + inicio);

            ganancia.gain.setValueAtTime(0, ahora + inicio);
            ganancia.gain.linearRampToValueAtTime(0.25, ahora + inicio + 0.02);
            ganancia.gain.exponentialRampToValueAtTime(0.001, ahora + inicio + 0.28);

            oscilador.connect(ganancia);
            ganancia.connect(audioContext.destination);

            oscilador.start(ahora + inicio);
            oscilador.stop(ahora + inicio + 0.3);
        });
    } catch (error) {
        // Silently ignore — audio is a nice-to-have, never block the notification.
    }
}

function mostrarNotificacionNavegador(titulo, mensaje) {
    reproducirSonidoNotificacion();

    if (!('Notification' in window) || Notification.permission !== 'granted') {
        return;
    }

    const notificacion = new Notification(titulo, {
        body: mensaje,
        tag: `pedido-${Date.now()}`,
    });

    notificacion.onclick = () => {
        window.focus();
        window.location.href = '/notificaciones';
        notificacion.close();
    };
}

document.addEventListener('DOMContentLoaded', solicitarPermisoNotificaciones);

// Browsers only allow audio playback after a user gesture — this unlocks the
// AudioContext on first click/keypress so later notification sounds aren't blocked.
function desbloquearAudio() {
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (AudioContextClass) {
        audioContext = audioContext || new AudioContextClass();
        if (audioContext.state === 'suspended') {
            audioContext.resume();
        }
    }
    document.removeEventListener('click', desbloquearAudio);
    document.removeEventListener('keydown', desbloquearAudio);
}
document.addEventListener('click', desbloquearAudio);
document.addEventListener('keydown', desbloquearAudio);

document.addEventListener('livewire:init', () => {
    Livewire.on('notificacion-nueva', (event) => {
        mostrarNotificacionNavegador(event.titulo, event.mensaje);
    });
});
