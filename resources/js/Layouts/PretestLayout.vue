<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";

// ── Props ──
const props = defineProps({
    totalSeconds: {
        type: Number,
        default: 600,
    },
    progressPercent: {
        type: Number,
        default: 55,
    },
});

// ── State ──
const remainingSeconds = ref(props.totalSeconds);
const musicOn = ref(false);
const mascotBounce = ref(false);
let interval = null;

// ── Computed ──
const formattedTime = computed(() => {
    const m = Math.floor(remainingSeconds.value / 60)
        .toString()
        .padStart(2, "0");
    const s = (remainingSeconds.value % 60).toString().padStart(2, "0");
    return `${m}:${s}`;
});

const isWarning = computed(() => remainingSeconds.value <= 60);

// ── Lifecycle ──
onMounted(() => {
    interval = setInterval(() => {
        if (remainingSeconds.value > 0) {
            remainingSeconds.value--;
        } else {
            clearInterval(interval);
        }
    }, 1000);

    setTimeout(() => {
        mascotBounce.value = true;
        setTimeout(() => {
            mascotBounce.value = false;
        }, 1200);
    }, 800);
});

onBeforeUnmount(() => {
    clearInterval(interval);
});

// ── Methods ──
function toggleMusic() {
    musicOn.value = !musicOn.value;
}

// ── Expose untuk parent ──
defineExpose({ mascotBounce });
</script>

<template>
    <!-- ░░ FULL PAGE WRAPPER ░░ -->
    <div class="layout-page">
        <!-- Background Image -->
        <div class="bg-image"></div>

        <!-- ░░ PROGRESS BAR ░░ -->
        <div class="progress-bar-track">
            <div
                class="progress-bar-fill"
                :style="{ width: progressPercent + '%' }"
            ></div>
        </div>

        <!-- ░░ TIMER BADGE ░░ -->
        <div class="timer-badge" :class="{ 'timer-warning': isWarning }">
            <svg
                class="timer-icon"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
            >
                <circle cx="12" cy="12" r="10" />
                <polyline points="12 6 12 12 16 14" />
            </svg>
            <span class="timer-text">{{ formattedTime }}</span>
        </div>

        <!-- ░░ MASCOT CHARACTER ░░ -->
        

        <!-- ░░ SLOT FOR PAGE CONTENT ░░ -->
        <main class="layout-content">
            <slot />
        </main>

        <!-- ░░ MUSIC BUTTON ░░ -->
        <button
            class="music-fab"
            @click="toggleMusic"
            :class="{ 'music-on': musicOn }"
            :title="musicOn ? 'Matikan Musik' : 'Nyalakan Musik'"
        >
            <svg
                v-if="musicOn"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
            >
                <path d="M9 18V5l12-2v13" />
                <circle cx="6" cy="18" r="3" />
                <circle cx="18" cy="16" r="3" />
            </svg>
            <svg
                v-else
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
            >
                <path d="M9 18V5l12-2v13" />
                <circle cx="6" cy="18" r="3" />
                <circle cx="18" cy="16" r="3" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
        </button>

        <!-- ░░ DECORATIVE SPARKLES ░░ -->
        <span class="sparkle sp-1">✦</span>
        <span class="sparkle sp-2">✦</span>
        <span class="sparkle sp-3">✦</span>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@700;800;900&display=swap");

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* ─── PAGE WRAPPER ─── */
.layout-page {
    position: relative;
    width: 100vw;
    min-height: 100vh;
    overflow-x: hidden;
    font-family: "Nunito", "Baloo 2", sans-serif;
}

/* ─── BACKGROUND ─── */
.bg-image {
    position: fixed;
    inset: 0;
    width: 100%;
    height: 100%;
    background-image: url("/images/templates/background-pretest.png");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-color: #cce8f5;
    z-index: 0;
}

/* ─── PROGRESS BAR ─── */
.progress-bar-track {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 10px;
    background: rgba(168, 216, 240, 0.5);
    z-index: 200;
    border-radius: 0 0 8px 8px;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3aabeb, #5bc4f5);
    border-radius: 0 8px 8px 0;
    transition: width 0.6s ease;
    box-shadow: 0 2px 8px rgba(58, 171, 235, 0.4);
}

/* ─── TIMER BADGE ─── */
.timer-badge {
    position: fixed;
    top: 18px;
    right: 24px;
    z-index: 201;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 50px;
    padding: 8px 20px 8px 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 20px rgba(0, 100, 180, 0.14);
    transition: background 0.3s;
}
.timer-badge.timer-warning {
    background: rgba(254, 226, 226, 0.95);
    animation: pulse-warn 1s ease-in-out infinite;
}
.timer-icon {
    width: 20px;
    height: 20px;
    color: #3aabeb;
    flex-shrink: 0;
}
.timer-warning .timer-icon {
    color: #ef4444;
}
.timer-text {
    font-family: "Baloo 2", cursive;
    font-size: 1.15rem;
    font-weight: 900;
    color: #1a3a5c;
    letter-spacing: 1px;
}
.timer-warning .timer-text {
    color: #b91c1c;
}
@keyframes pulse-warn {
    0%,
    100% {
        box-shadow: 0 4px 20px rgba(239, 68, 68, 0.2);
    }
    50% {
        box-shadow: 0 4px 28px rgba(239, 68, 68, 0.45);
    }
}

/* ─── MASCOT ─── */
.mascot {
    position: fixed;
    right: 70%;
    bottom: 5%;
    width: 460px;
    height: auto;
    z-index: 100;
    filter: drop-shadow(0 12px 24px rgba(0, 80, 140, 0.18));
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    pointer-events: none;
}
.mascot-img {
    width: 100%;
    height: auto;
    display: block;
}
.mascot.bounce {
    animation: mascot-bounce 1.1s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes mascot-bounce {
    0% {
        transform: translateY(0) rotate(0deg);
    }
    25% {
        transform: translateY(-28px) rotate(-3deg);
    }
    55% {
        transform: translateY(-14px) rotate(2deg);
    }
    75% {
        transform: translateY(-6px) rotate(-1deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}
@media (max-width: 1200px) {
    .mascot {
        right: 4%;
        width: 200px;
    }
}
@media (max-width: 768px) {
    .mascot {
        right: 8px;
        width: 130px;
        bottom: 8%;
        opacity: 0.8;
    }
}

/* ─── MAIN CONTENT ─── */
.layout-content {
    position: relative;
    z-index: 10;
    padding: 30px 40px 60px;
    min-height: 100vh;
}
@media (max-width: 768px) {
    .layout-content {
        padding: 24px 16px 60px;
    }
}

/* ─── MUSIC FAB ─── */
.music-fab {
    position: fixed;
    bottom: 28px;
    left: 28px;
    z-index: 201;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4fc3f7;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.music-fab:hover {
    transform: scale(1.12);
    background: white;
    box-shadow: 0 6px 24px rgba(0, 0, 0, 0.18);
}
.music-fab.music-on {
    background: linear-gradient(135deg, #4fc3f7, #0288d1);
    color: white;
    box-shadow: 0 4px 20px rgba(79, 195, 247, 0.4);
}
.music-fab svg {
    width: 22px;
    height: 22px;
}

/* ─── SPARKLES ─── */
.sparkle {
    position: fixed;
    color: white;
    z-index: 5;
    pointer-events: none;
    animation: sparkle-pulse 3s ease-in-out infinite;
    font-style: normal;
}
.sp-1 {
    bottom: 32px;
    right: 32px;
    font-size: 2rem;
}
.sp-2 {
    top: 130px;
    left: 22px;
    font-size: 1.2rem;
    animation-delay: 1.5s;
}
.sp-3 {
    bottom: 120px;
    right: 18%;
    font-size: 0.9rem;
    animation-delay: 0.8s;
}
@keyframes sparkle-pulse {
    0%,
    100% {
        opacity: 0.3;
        transform: scale(1) rotate(0deg);
    }
    50% {
        opacity: 0.85;
        transform: scale(1.3) rotate(20deg);
    }
}
</style>
