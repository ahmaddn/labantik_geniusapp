import { ref } from 'vue'

let sharedCtx = null;

function getAudioContext() {
    if (typeof window === 'undefined') return null;
    if (!sharedCtx) {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (AudioContextClass) {
            sharedCtx = new AudioContextClass();
        }
    }
    // Resume context if suspended (browser security policy)
    if (sharedCtx && sharedCtx.state === 'suspended') {
        sharedCtx.resume().catch(() => {});
    }
    return sharedCtx;
}

export function useSfx() {
    const playPop = () => {
        const ctx = getAudioContext();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        // Rise frequency quickly to simulate a "pop"
        osc.frequency.setValueAtTime(350, ctx.currentTime);
        osc.frequency.exponentialRampToValueAtTime(1000, ctx.currentTime + 0.08);

        gain.gain.setValueAtTime(0.2, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.08);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 0.08);
    };

    const playSuccess = () => {
        const ctx = getAudioContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        // Happy major chord arpeggio: C5 -> E5 -> G5 -> C6
        const notes = [523.25, 659.25, 783.99, 1046.50];
        
        notes.forEach((freq, idx) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'triangle';
            osc.frequency.setValueAtTime(freq, now + idx * 0.09);

            gain.gain.setValueAtTime(0, now + idx * 0.09);
            gain.gain.linearRampToValueAtTime(0.15, now + idx * 0.09 + 0.04);
            gain.gain.exponentialRampToValueAtTime(0.01, now + idx * 0.09 + 0.25);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now + idx * 0.09);
            osc.stop(now + idx * 0.09 + 0.25);
        });
    };

    const playFail = () => {
        const ctx = getAudioContext();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sawtooth';
        osc.frequency.setValueAtTime(180, ctx.currentTime);
        osc.frequency.linearRampToValueAtTime(90, ctx.currentTime + 0.35);

        gain.gain.setValueAtTime(0.15, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.35);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(ctx.currentTime + 0.35);
    };

    const playRetry = () => {
        const ctx = getAudioContext();
        if (!ctx) return;

        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'triangle';
        const now = ctx.currentTime;
        // Frekuensi menurun lalu naik cepat (seperti suara memutar balik / rewind)
        osc.frequency.setValueAtTime(550, now);
        osc.frequency.exponentialRampToValueAtTime(150, now + 0.08);
        osc.frequency.exponentialRampToValueAtTime(400, now + 0.22);

        gain.gain.setValueAtTime(0.2, now);
        gain.gain.exponentialRampToValueAtTime(0.01, now + 0.22);

        osc.connect(gain);
        gain.connect(ctx.destination);

        osc.start();
        osc.stop(now + 0.22);
    };
    const playClick = () => {
        const ctx = getAudioContext();
        if (!ctx) return;

        const now = ctx.currentTime;
        // Double bubble chirp sound
        [0, 0.02].forEach((delay) => {
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();

            osc.type = 'sine';
            osc.frequency.setValueAtTime(500 + delay * 1000, now + delay);
            osc.frequency.exponentialRampToValueAtTime(800 + delay * 500, now + delay + 0.035);

            gain.gain.setValueAtTime(0, now + delay);
            gain.gain.linearRampToValueAtTime(0.12, now + delay + 0.005);
            gain.gain.exponentialRampToValueAtTime(0.01, now + delay + 0.035);

            osc.connect(gain);
            gain.connect(ctx.destination);

            osc.start(now + delay);
            osc.stop(now + delay + 0.035);
        });
    };

    return { playPop, playSuccess, playFail, playRetry, playClick };
}
