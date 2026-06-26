import { ref } from 'vue'

const isMuted = () => {
    try {
        return localStorage.getItem('geniuss_music_on') === 'false';
    } catch {
        return false;
    }
};

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
        if (isMuted()) return;
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
        if (isMuted()) return;
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
        if (isMuted()) return;
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

    return { playPop, playSuccess, playFail };
}
