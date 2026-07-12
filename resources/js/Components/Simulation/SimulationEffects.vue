<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

const props = defineProps({
    effect: {
        type: String,
        default: 'none'
    }
});

const activeEffect = ref(props.effect);
watch(() => props.effect, (newVal) => {
    activeEffect.value = newVal;
});

const generateParticles = (count) => {
    return Array.from({ length: count }).map((_, i) => {
        return {
            id: i,
            left: `${Math.random() * 100}%`,
            delay: `${Math.random() * 2}s`,
            duration: `${Math.random() * 2 + 1}s`,
            scale: Math.random() * 0.5 + 0.5
        };
    });
};

const particles = ref(generateParticles(20));

watch(activeEffect, () => {
    let count = 20;
    if (['snow', 'rain_heavy', 'confetti'].includes(activeEffect.value)) {
        count = 50;
    } else if (['stars', 'clouds'].includes(activeEffect.value)) {
        count = 30;
    }
    particles.value = generateParticles(count);
});
</script>

<template>
    <div class="effect-layer" v-if="activeEffect !== 'none'">
        
        <!-- RAIN LIGHT -->
        <div v-if="activeEffect === 'rain_light'" class="particles-container">
            <div v-for="p in particles" :key="'rl'+p.id" class="rain-drop" :style="{ left: p.left, animationDelay: p.delay, animationDuration: p.duration }"></div>
        </div>

        <!-- RAIN HEAVY -->
        <div v-if="activeEffect === 'rain_heavy'" class="particles-container heavy-rain-bg">
            <div v-for="p in particles" :key="'rh'+p.id" class="rain-drop heavy" :style="{ left: p.left, animationDelay: p.delay, animationDuration: '0.6s' }"></div>
        </div>

        <!-- SNOW -->
        <div v-if="activeEffect === 'snow'" class="particles-container">
            <div v-for="p in particles" :key="'sn'+p.id" class="snow-flake" :style="{ left: p.left, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})` }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5l-10 14M7 5l10 14M22 12H2M19.07 19.07l-14.14-14.14M19.07 4.93L4.93 19.07"/></svg>
            </div>
        </div>

        <!-- BUBBLES -->
        <div v-if="activeEffect === 'bubbles'" class="particles-container">
            <div v-for="p in particles" :key="'bb'+p.id" class="bubble" :style="{ left: p.left, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})` }"></div>
        </div>

        <!-- FIRE SPARKS -->
        <div v-if="activeEffect === 'fire_sparks'" class="particles-container">
            <div v-for="p in particles" :key="'fs'+p.id" class="fire-spark" :style="{ left: p.left, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})` }">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="#ff4b4b" stroke="#ea2b2b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/></svg>
            </div>
        </div>

        <!-- WIND LEAVES -->
        <div v-if="activeEffect === 'wind_leaves'" class="particles-container">
            <div v-for="p in particles" :key="'wl'+p.id" class="wind-leaf" :style="{ top: p.left, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})` }">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="#58cc02" stroke="#46a302" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/></svg>
            </div>
        </div>

        <!-- DUST -->
        <div v-if="activeEffect === 'dust'" class="particles-container">
            <div v-for="p in particles" :key="'ds'+p.id" class="dust-particle" :style="{ left: p.left, top: p.delay, animationDelay: p.delay, animationDuration: '4s', transform: `scale(${p.scale})` }"></div>
        </div>

        <!-- SUNBEAMS -->
        <div v-if="activeEffect === 'sunbeams'" class="sunbeams-container">
            <div class="sunbeam b1"></div>
            <div class="sunbeam b2"></div>
            <div class="sunbeam b3"></div>
        </div>

        <!-- EARTHQUAKE DUST (Earthquake shake is handled by parent, this just adds dust) -->
        <div v-if="activeEffect === 'earthquake'" class="particles-container earthquake-dust">
            <div v-for="p in particles" :key="'eq'+p.id" class="quake-dust" :style="{ left: p.left, animationDelay: p.delay, animationDuration: '2s' }"></div>
        </div>

        <!-- CONFETTI -->
        <div v-if="activeEffect === 'confetti'" class="particles-container">
            <div v-for="p in particles" :key="'cf'+p.id" class="confetti-piece" :style="{ left: p.left, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})`, backgroundColor: `hsl(${Math.random() * 360}, 100%, 60%)` }"></div>
        </div>

        <!-- LIGHTNING -->
        <div v-if="activeEffect === 'lightning'" class="lightning-flash"></div>

        <!-- STARS -->
        <div v-if="activeEffect === 'stars'" class="particles-container">
            <div v-for="p in particles" :key="'st'+p.id" class="star-twinkle" :style="{ left: p.left, top: `${Math.random() * 80}%`, animationDelay: p.delay, animationDuration: p.duration, transform: `scale(${p.scale})` }">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="#ffffff" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2" /></svg>
            </div>
        </div>

        <!-- FOG -->
        <div v-if="activeEffect === 'fog'" class="fog-container">
            <div class="fog-layer fog-1"></div>
            <div class="fog-layer fog-2"></div>
        </div>

        <!-- CLOUDS -->
        <div v-if="activeEffect === 'clouds'" class="particles-container">
            <div v-for="p in particles.slice(0, 5)" :key="'cl'+p.id" class="cloud-float" :style="{ top: `${Math.random() * 30}%`, animationDelay: p.delay, animationDuration: `${Math.random() * 20 + 20}s`, transform: `scale(${p.scale * 2 + 1})` }">
                <svg width="60" height="40" viewBox="0 0 24 24" fill="#ffffff" stroke="none" opacity="0.8"><path d="M17.5,19 C19.9852814,19 22,16.9852814 22,14.5 C22,12.0147186 19.9852814,10 17.5,10 C17.1856758,10 16.8791054,10.0322234 16.5828608,10.0926521 C15.8217316,7.21447952 13.1537255,5 10,5 C6.13400675,5 3,8.13400675 3,12 C3,12.0534241 3.00059885,12.1067086 3.00178652,12.1598426 C1.85404457,12.8252277 1,14.1130722 1,15.5 C1,17.4329966 2.56700338,19 4.5,19 L17.5,19 Z"/></svg>
            </div>
        </div>

    </div>
</template>

<style scoped>
.effect-layer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 20;
    overflow: hidden;
}
.particles-container { position: absolute; inset: 0; }

/* RAIN */
.rain-drop {
    position: absolute;
    top: -50px;
    width: 2px;
    height: 15px;
    background: rgba(255,255,255,0.6);
    animation: rainFall linear infinite;
    animation-fill-mode: backwards;
}
.rain-drop.heavy {
    width: 3px; height: 25px;
    background: rgba(255,255,255,0.8);
}
.heavy-rain-bg { background: rgba(0,0,0,0.3); }
@keyframes rainFall { 
    0% { transform: translateY(0); opacity: 0; }
    10% { opacity: 1; }
    100% { transform: translateY(120vh); opacity: 1; }
}

/* SNOW */
.snow-flake {
    position: absolute;
    top: -50px;
    animation: snowFall linear infinite;
    animation-fill-mode: backwards;
    opacity: 0.8;
}
@keyframes snowFall { 
    0% { transform: translateY(0) rotate(0deg) translateX(0); opacity: 0; }
    10% { opacity: 0.8; }
    50% { transform: translateY(50vh) rotate(180deg) translateX(20px); opacity: 0.8; }
    100% { transform: translateY(120vh) rotate(360deg) translateX(-20px); opacity: 0.8; }
}

/* BUBBLES */
.bubble {
    position: absolute;
    bottom: -50px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.8);
    background: rgba(255,255,255,0.2);
    animation: bubbleRise linear infinite;
    animation-fill-mode: backwards;
}
@keyframes bubbleRise {
    0% { transform: translateY(0) scale(1); opacity: 0; }
    10% { opacity: 1; }
    100% { transform: translateY(-110vh) scale(1.5); opacity: 0; }
}

/* FIRE SPARKS */
.fire-spark {
    position: absolute;
    bottom: -20px;
    animation: sparkRise linear infinite;
    animation-fill-mode: backwards;
}
@keyframes sparkRise {
    0% { transform: translateY(0) scale(1); opacity: 0; }
    10% { opacity: 1; }
    100% { transform: translateY(-120px) scale(0); opacity: 0; }
}

/* WIND LEAVES */
.wind-leaf {
    position: absolute;
    left: -50px;
    animation: leafBlow linear infinite;
    animation-fill-mode: backwards;
}
@keyframes leafBlow {
    0% { transform: translateX(0) rotate(0deg) translateY(0); opacity: 0; }
    15% { opacity: 1; }
    100% { transform: translateX(110vw) rotate(720deg) translateY(100px); opacity: 0; }
}

/* DUST */
.dust-particle {
    position: absolute;
    width: 4px; height: 4px;
    background: rgba(200,180,150,0.6);
    border-radius: 50%;
    animation: dustFloat linear infinite;
    animation-fill-mode: backwards;
}
@keyframes dustFloat {
    0% { transform: translate(0,0); opacity: 0; }
    10% { opacity: 1; }
    50% { transform: translate(20px, -20px); opacity: 1; }
    100% { transform: translate(-20px, -40px); opacity: 0; }
}

/* SUNBEAMS */
.sunbeams-container { position: absolute; inset: 0; background: rgba(255,255,255,0.1); }
.sunbeam {
    position: absolute;
    top: -50%; left: 50%;
    width: 200%; height: 200%;
    background: linear-gradient(rgba(255,255,255,0.4), transparent);
    transform-origin: top center;
    animation: beamRotate 10s infinite alternate ease-in-out;
}
.b1 { transform: translateX(-50%) rotate(-15deg); }
.b2 { transform: translateX(-50%) rotate(0deg); animation-delay: -3s; }
.b3 { transform: translateX(-50%) rotate(15deg); animation-delay: -6s; }
@keyframes beamRotate {
    0% { transform: translateX(-50%) rotate(-5deg); opacity: 0.5; }
    100% { transform: translateX(-50%) rotate(5deg); opacity: 0.8; }
}

/* EARTHQUAKE DUST */
.quake-dust {
    position: absolute;
    bottom: -10px;
    width: 6px; height: 6px;
    background: rgba(150,130,110,0.8);
    border-radius: 50%;
    animation: quakeDustRise linear infinite;
}
@keyframes quakeDustRise {
    0% { transform: translateY(0) scale(1); opacity: 1; }
    100% { transform: translateY(-50px) scale(3); opacity: 0; }
}

/* CONFETTI */
.confetti-piece {
    position: absolute;
    top: -20px;
    width: 10px; height: 15px;
    animation: confettiFall linear infinite;
}
@keyframes confettiFall {
    0% { transform: translateY(-20px) rotate(0deg) translateX(0); opacity: 1; }
    50% { transform: translateY(50vh) rotate(180deg) translateX(20px); }
    100% { transform: translateY(100vh) rotate(360deg) translateX(-20px); opacity: 0; }
}

/* LIGHTNING */
.lightning-flash {
    position: absolute;
    inset: 0;
    background: white;
    opacity: 0;
    animation: lightningStrike 6s infinite;
}
@keyframes lightningStrike {
    0%, 91%, 93%, 95%, 100% { opacity: 0; }
    92%, 94% { opacity: 0.8; }
}

/* STARS */
.star-twinkle {
    position: absolute;
    animation: twinkleStar linear infinite alternate;
}
@keyframes twinkleStar {
    0% { opacity: 0.2; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1.2); }
}

/* FOG */
.fog-container {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}
.fog-layer {
    position: absolute;
    top: 0;
    width: 200vw;
    height: 100%;
    background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><filter id="noise"><feTurbulence type="fractalNoise" baseFrequency="0.015" numOctaves="3" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23noise)" opacity="0.3" fill="white"/></svg>') repeat-x;
    background-size: 50% 100%;
    opacity: 0.4;
    animation: fogMove linear infinite;
}
.fog-1 { animation-duration: 60s; }
.fog-2 { animation-duration: 40s; opacity: 0.2; animation-direction: reverse; transform: scaleY(-1); }
@keyframes fogMove {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50vw); }
}

/* CLOUDS */
.cloud-float {
    position: absolute;
    left: -100px;
    animation: cloudMove linear infinite;
    opacity: 0.6;
}
@keyframes cloudMove {
    0% { transform: translateX(-100px); }
    100% { transform: translateX(110vw); }
}
</style>
