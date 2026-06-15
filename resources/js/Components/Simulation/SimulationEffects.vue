<script setup>
import { computed } from "vue";

const props = defineProps({
    effect: {
        type: String,
        default: "none",
    },
});

// Generate multiple items for particles
const generateParticles = (count) => Array.from({ length: count }, (_, i) => i);
</script>

<template>
    <div
        v-if="effect !== 'none'"
        class="absolute inset-0 pointer-events-none overflow-hidden rounded-xl z-20 simulation-effects-container"
    >
        <!-- RAIN LIGHT -->
        <template v-if="effect === 'rain_light'">
            <div
                class="rain-drop light"
                v-for="i in generateParticles(30)"
                :key="'rl' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDuration: 0.8 + Math.random() * 0.5 + 's',
                    animationDelay: Math.random() * 2 + 's',
                }"
            ></div>
        </template>

        <!-- RAIN HEAVY -->
        <template v-else-if="effect === 'rain_heavy'">
            <div class="absolute inset-0 bg-slate-900/30"></div>
            <div
                class="rain-drop heavy"
                v-for="i in generateParticles(80)"
                :key="'rh' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDuration: 0.4 + Math.random() * 0.3 + 's',
                    animationDelay: Math.random() * 1 + 's',
                }"
            ></div>
        </template>

        <!-- SNOW -->
        <template v-else-if="effect === 'snow'">
            <div
                class="snow-flake"
                v-for="i in generateParticles(40)"
                :key="'sn' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDuration: 3 + Math.random() * 4 + 's',
                    animationDelay: Math.random() * 5 + 's',
                    width: 4 + Math.random() * 6 + 'px',
                    height: 4 + Math.random() * 6 + 'px',
                    opacity: 0.4 + Math.random() * 0.6,
                }"
            ></div>
        </template>

        <!-- BUBBLES -->
        <template v-else-if="effect === 'bubbles'">
            <div
                class="bubble"
                v-for="i in generateParticles(25)"
                :key="'bb' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDuration: 4 + Math.random() * 6 + 's',
                    animationDelay: Math.random() * 4 + 's',
                    width: 10 + Math.random() * 20 + 'px',
                    height: 10 + Math.random() * 20 + 'px',
                }"
            ></div>
        </template>

        <!-- FIRE SPARKS -->
        <template v-else-if="effect === 'fire_sparks'">
            <div
                class="absolute inset-0 bg-orange-600/10 mix-blend-overlay"
            ></div>
            <div
                class="fire-spark"
                v-for="i in generateParticles(40)"
                :key="'fs' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    animationDuration: 1.5 + Math.random() * 2 + 's',
                    animationDelay: Math.random() * 3 + 's',
                    width: 3 + Math.random() * 5 + 'px',
                    height: 3 + Math.random() * 5 + 'px',
                }"
            ></div>
        </template>

        <!-- WIND LEAVES -->
        <template v-else-if="effect === 'wind_leaves'">
            <div
                class="wind-leaf"
                v-for="i in generateParticles(15)"
                :key="'wl' + i"
                :style="{
                    top: Math.random() * 50 + '%',
                    animationDuration: 4 + Math.random() * 3 + 's',
                    animationDelay: Math.random() * 5 + 's',
                }"
            ></div>
        </template>

        <!-- DUST / POLLUTION -->
        <template v-else-if="effect === 'dust'">
            <div
                class="absolute inset-0 bg-amber-900/20 mix-blend-multiply"
            ></div>
            <div
                class="dust-particle"
                v-for="i in generateParticles(60)"
                :key="'dp' + i"
                :style="{
                    left: Math.random() * 100 + '%',
                    top: Math.random() * 100 + '%',
                    animationDuration: 8 + Math.random() * 10 + 's',
                    animationDelay: Math.random() * 5 + 's',
                }"
            ></div>
        </template>

        <!-- SUNBEAMS -->
        <template v-else-if="effect === 'sunbeams'">
            <div
                class="sunbeam"
                v-for="i in generateParticles(5)"
                :key="'sb' + i"
                :style="{
                    left: 10 + Math.random() * 80 + '%',
                    animationDuration: 5 + Math.random() * 4 + 's',
                    animationDelay: Math.random() * 2 + 's',
                }"
            ></div>
        </template>

        <!-- EARTHQUAKE handled by CSS class on the container in parent, but we can add a dust overlay here -->
        <template v-else-if="effect === 'earthquake'">
            <div class="absolute inset-0 bg-stone-800/10"></div>
        </template>
    </div>
</template>

<style scoped>
/* RAIN */
.rain-drop {
    position: absolute;
    top: -20px;
    background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0),
        rgba(255, 255, 255, 0.8)
    );
    width: 2px;
    height: 15px;
    animation: fall linear infinite;
}
.rain-drop.heavy {
    width: 3px;
    height: 30px;
    background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0),
        rgba(255, 255, 255, 0.9)
    );
}
@keyframes fall {
    to {
        transform: translateY(500px);
    }
}

/* SNOW */
.snow-flake {
    position: absolute;
    top: -20px;
    background: white;
    border-radius: 50%;
    filter: blur(1px);
    animation: snowFall linear infinite;
}
@keyframes snowFall {
    0% {
        transform: translateY(-20px) translateX(0);
    }
    50% {
        transform: translateY(200px) translateX(20px);
    }
    100% {
        transform: translateY(500px) translateX(-20px);
    }
}

/* BUBBLES */
.bubble {
    position: absolute;
    bottom: -30px;
    border: 2px solid rgba(255, 255, 255, 0.6);
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: floatUp linear infinite;
}
@keyframes floatUp {
    0% {
        transform: translateY(0) translateX(0) scale(1);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    50% {
        transform: translateY(-200px) translateX(15px) scale(1.2);
    }
    100% {
        transform: translateY(-500px) translateX(-15px) scale(1.5);
        opacity: 0;
    }
}

/* FIRE SPARKS */
.fire-spark {
    position: absolute;
    bottom: -10px;
    background: #ff5722;
    border-radius: 50%;
    box-shadow:
        0 0 10px #ff9800,
        0 0 20px #ffeb3b;
    animation: sparkUp ease-in infinite;
}
@keyframes sparkUp {
    0% {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(-200px) scale(0);
        opacity: 0;
    }
}

/* WIND LEAVES */
.wind-leaf {
    position: absolute;
    left: -20px;
    width: 15px;
    height: 10px;
    background: #8bc34a;
    border-radius: 50% 0 50% 0;
    opacity: 0.8;
    animation: blowRight linear infinite;
}
@keyframes blowRight {
    0% {
        transform: translateX(0) translateY(0) rotate(0deg);
        opacity: 0;
    }
    10% {
        opacity: 1;
    }
    50% {
        transform: translateX(300px) translateY(50px) rotate(180deg);
    }
    90% {
        opacity: 1;
    }
    100% {
        transform: translateX(600px) translateY(-20px) rotate(360deg);
        opacity: 0;
    }
}

/* DUST */
.dust-particle {
    position: absolute;
    width: 3px;
    height: 3px;
    background: #a1887f;
    border-radius: 50%;
    opacity: 0.6;
    animation: drift linear infinite;
}
@keyframes drift {
    0% {
        transform: translate(0, 0);
        opacity: 0;
    }
    20% {
        opacity: 0.8;
    }
    80% {
        opacity: 0.8;
    }
    100% {
        transform: translate(100px, -50px);
        opacity: 0;
    }
}

/* SUNBEAMS */
.sunbeam {
    position: absolute;
    top: -50%;
    width: 60px;
    height: 200%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transform-origin: top;
    transform: rotate(30deg);
    animation: beamSweep alternate infinite ease-in-out;
}
@keyframes beamSweep {
    0% {
        transform: rotate(20deg) scaleX(1);
        opacity: 0.3;
    }
    100% {
        transform: rotate(40deg) scaleX(1.5);
        opacity: 0.7;
    }
}
</style>
