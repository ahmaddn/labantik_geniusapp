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
    particles.value = generateParticles(activeEffect.value === 'snow' || activeEffect.value === 'rain_heavy' ? 40 : 20);
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
    top: -20px;
    width: 2px;
    height: 15px;
    background: rgba(255,255,255,0.6);
    animation: rainFall linear infinite;
}
.rain-drop.heavy {
    width: 3px; height: 25px;
    background: rgba(255,255,255,0.8);
}
.heavy-rain-bg { background: rgba(0,0,0,0.3); }
@keyframes rainFall { to { transform: translateY(100vh); } }

/* SNOW */
.snow-flake {
    position: absolute;
    top: -30px;
    animation: snowFall linear infinite;
    opacity: 0.8;
}
@keyframes snowFall { 
    0% { transform: translateY(-30px) rotate(0deg) translateX(0); }
    50% { transform: translateY(50vh) rotate(180deg) translateX(20px); }
    100% { transform: translateY(100vh) rotate(360deg) translateX(-20px); }
}

/* BUBBLES */
.bubble {
    position: absolute;
    bottom: -20px;
    width: 12px; height: 12px;
    border-radius: 50%;
    border: 2px solid rgba(255,255,255,0.8);
    background: rgba(255,255,255,0.2);
    animation: bubbleRise linear infinite;
}
@keyframes bubbleRise {
    0% { transform: translateY(0) scale(1); opacity: 0; }
    10% { opacity: 1; }
    100% { transform: translateY(-100vh) scale(1.5); opacity: 0; }
}

/* FIRE SPARKS */
.fire-spark {
    position: absolute;
    bottom: 0;
    animation: sparkRise linear infinite;
}
@keyframes sparkRise {
    0% { transform: translateY(0) scale(1); opacity: 1; }
    100% { transform: translateY(-100px) scale(0); opacity: 0; }
}

/* WIND LEAVES */
.wind-leaf {
    position: absolute;
    left: -30px;
    animation: leafBlow linear infinite;
}
@keyframes leafBlow {
    0% { transform: translateX(-30px) rotate(0deg) translateY(0); opacity: 0; }
    10% { opacity: 1; }
    100% { transform: translateX(100vw) rotate(720deg) translateY(100px); opacity: 0; }
}

/* DUST */
.dust-particle {
    position: absolute;
    width: 4px; height: 4px;
    background: rgba(200,180,150,0.6);
    border-radius: 50%;
    animation: dustFloat linear infinite;
}
@keyframes dustFloat {
    0% { transform: translate(0,0); opacity: 0; }
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
</style>
