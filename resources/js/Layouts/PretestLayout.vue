<script setup>
import { onMounted, onBeforeUnmount } from "vue";
import { Music2, VolumeX } from "lucide-vue-next";
import { useMusic } from "@/Composable/useMusic";

// ── Props ──
const props = defineProps({
    timerDisplay: { type: String, default: "00:00" },
    isWarning: { type: Boolean, default: false },
    progressPercent: { type: Number, default: 0 },
    showProgress: { type: Boolean, default: true },
    backsound: { type: String, default: null },
    platformName: { type: String, default: null },
    siswa: { type: Object, default: null },
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
});

const emit = defineEmits(["timeout"]);

// ── Music ──
const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => initAutoMusic(props.backsound ?? null), 100);
    document.addEventListener("visibilitychange", handleVisibility);
});

onBeforeUnmount(() => {
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});

// ── Expose ──
defineExpose({ musicOn, toggleMusic });
</script>

<template>
    <div class="app-layout">
        <!-- ░░ BACKGROUND ░░ -->
        <div class="bg-scene">
            <div class="sky-gradient"></div>
            <div class="bg-particles">
                <div class="particle p-1"></div>
                <div class="particle p-2"></div>
                <div class="particle p-3"></div>
                <div class="particle p-4"></div>
                <div class="particle p-5"></div>
            </div>
            <!-- Educational floating icons -->
            <div class="edu-particles">
                <svg
                    class="edu-p ep-1"
                    style="top: 10%; left: 8%; color: #1cb0f6"
                    viewBox="0 0 24 24"
                    fill="currentColor"
                >
                    <polygon
                        points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
                    />
                </svg>
                <svg
                    class="edu-p ep-2"
                    style="top: 18%; right: 10%; color: #ffc800"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"
                    />
                    <path d="m15 5 4 4" />
                </svg>
                <svg
                    class="edu-p ep-3"
                    style="top: 35%; left: 12%; color: #78c257"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"
                    />
                    <path d="M6 6h10M6 10h10" />
                </svg>
                <svg
                    class="edu-p ep-4"
                    style="top: 40%; right: 12%; color: #ff847c"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path
                        d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5 5 0 0 0 8 8c0 1 .3 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"
                    />
                    <path d="M9 18h6M10 22h4" />
                </svg>
                <svg
                    class="edu-p ep-5"
                    style="top: 65%; left: 8%; color: #845ef7"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3.5"
                    stroke-linecap="round"
                >
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <svg
                    class="edu-p ep-6"
                    style="top: 72%; right: 14%; color: #00bcd4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3.5"
                    stroke-linecap="round"
                >
                    <line x1="6" y1="6" x2="18" y2="18" />
                    <line x1="6" y1="18" x2="18" y2="6" />
                </svg>
                <svg
                    class="edu-p ep-7"
                    style="top: 85%; left: 22%; color: #e91e63"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="8" r="7" />
                    <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12" />
                </svg>
                <svg
                    class="edu-p ep-8"
                    style="top: 25%; left: 26%; color: #1cb0f6"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 3v4M12 17v4M3 12h4M17 12h4" />
                </svg>
            </div>
        </div>

        <!-- ░░ TOP NAV ░░ -->
        <header class="top-nav">
            <div class="nav-inner">
                <!-- Left: Back button slot -->
                <div class="nav-left">
                    <slot name="nav-left" />
                </div>

                <!-- Center: Progress Bar or Title -->
                <div class="nav-center">
                    <Transition name="slide-fade" mode="out-in">
                        <div
                            class="prog-wrapper"
                            v-if="showProgress"
                            key="progress"
                        >
                            <div class="prog-track">
                                <div
                                    class="prog-fill"
                                    :style="{ width: progressPercent + '%' }"
                                >
                                    <div class="prog-shine"></div>
                                    <div
                                        class="prog-tip"
                                        v-if="progressPercent > 5"
                                    >
                                        <svg
                                            width="12"
                                            height="12"
                                            viewBox="0 0 24 24"
                                            fill="white"
                                        >
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"
                                            />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="nav-title-text" key="title">
                            <span class="title-pretest">PRETEST</span>
                        </div>
                    </Transition>
                </div>

                <!-- Right: Timer -->
                <div class="nav-right">

                    <!-- Timer -->
                    <Transition name="slide-fade">
                        <div
                            v-if="showProgress"
                            class="timer-badge"
                            :class="{ 'timer-warning': isWarning }"
                        >
                            <div class="timer-icon-wrap">
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    width="15"
                                    height="15"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <circle cx="12" cy="12" r="10" />
                                    <polyline points="12 6 12 12 16 14" />
                                </svg>
                            </div>
                            <span class="timer-val">{{ timerDisplay }}</span>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <!-- ░░ SCROLL CONTENT ░░ -->
        <main class="main-scroll">
            <slot />
        </main>

        <!-- ░░ MUSIC FAB — hanya tampil di desktop ░░ -->
        <button
            class="music-fab"
            @click="toggleMusic(props.backsound ?? null)"
            :class="{ 'music-on': musicOn }"
            title="Musik Latar"
        >
            <svg
                v-if="musicOn"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.5"
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
                stroke-width="2.5"
            >
                <path d="M9 18V5l12-2v13" />
                <circle cx="6" cy="18" r="3" />
                <circle cx="18" cy="16" r="3" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@400;500;600;700;800&family=Righteous&display=swap");

/* ─── BASE ─── */
.app-layout {
    position: relative;
    width: 100vw;
    min-height: 100vh;
    font-family: "Nunito", "Baloo 2", sans-serif;
    overflow-x: hidden;
}

/* ─── BG SCENE ─── */
.bg-scene {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}

.sky-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 40%, #f0fdf4 100%);
}

.bg-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.bg-particles .particle {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.35;
}

.bg-particles .p-1 {
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, #bfdbfe, #93c5fd);
    top: -100px;
    left: -150px;
    animation: blobDrift1 18s ease-in-out infinite;
}
.bg-particles .p-2 {
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, #d1fae5, #6ee7b7);
    top: 30%;
    right: -100px;
    animation: blobDrift2 22s ease-in-out infinite;
}
.bg-particles .p-3 {
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, #fce7f3, #f9a8d4);
    bottom: 10%;
    left: 20%;
    animation: blobDrift3 16s ease-in-out infinite;
}
.bg-particles .p-4 {
    width: 350px;
    height: 350px;
    background: radial-gradient(circle, #ede9fe, #c4b5fd);
    top: 50%;
    left: 40%;
    animation: blobDrift1 20s ease-in-out infinite reverse;
}
.bg-particles .p-5 {
    width: 250px;
    height: 250px;
    background: radial-gradient(circle, #fef3c7, #fde68a);
    bottom: 20%;
    right: 20%;
    animation: blobDrift2 14s ease-in-out infinite reverse;
}

@keyframes blobDrift1 {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(30px, -40px) scale(1.05);
    }
    66% {
        transform: translate(-20px, 20px) scale(0.95);
    }
}
@keyframes blobDrift2 {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    50% {
        transform: translate(-35px, 25px) scale(1.08);
    }
}
@keyframes blobDrift3 {
    0%,
    100% {
        transform: translate(0, 0) scale(1);
    }
    40% {
        transform: translate(20px, -30px) scale(0.92);
    }
    80% {
        transform: translate(-10px, 15px) scale(1.04);
    }
}

/* Educational floating icons */
.edu-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}

.edu-p {
    position: absolute;
    width: 28px;
    height: 28px;
    opacity: 0.18;
    transform-origin: center;
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.06));
}

.ep-1,
.ep-4,
.ep-7 {
    animation: eduFloat1 15s ease-in-out infinite;
}
.ep-2,
.ep-5,
.ep-8 {
    animation: eduFloat2 19s ease-in-out infinite;
}
.ep-3,
.ep-6 {
    animation: eduFloat3 23s ease-in-out infinite;
}

.ep-1 {
    animation-delay: 0s;
}
.ep-2 {
    animation-delay: -3s;
}
.ep-3 {
    animation-delay: -6s;
}
.ep-4 {
    animation-delay: -9s;
}
.ep-5 {
    animation-delay: -12s;
}
.ep-6 {
    animation-delay: -2s;
}
.ep-7 {
    animation-delay: -5s;
}
.ep-8 {
    animation-delay: -8s;
}

@keyframes eduFloat1 {
    0% {
        transform: translate(0, 0) rotate(0deg) scale(1);
    }
    25% {
        transform: translate(12px, -20px) rotate(45deg) scale(1.1);
    }
    50% {
        transform: translate(18px, -8px) rotate(90deg) scale(0.95);
    }
    75% {
        transform: translate(5px, -25px) rotate(135deg) scale(1.05);
    }
    100% {
        transform: translate(0, 0) rotate(180deg) scale(1);
    }
}
@keyframes eduFloat2 {
    0% {
        transform: translate(0, 0) rotate(0deg);
    }
    33% {
        transform: translate(-16px, -18px) rotate(-60deg);
    }
    66% {
        transform: translate(10px, -30px) rotate(30deg);
    }
    100% {
        transform: translate(0, 0) rotate(0deg);
    }
}
@keyframes eduFloat3 {
    0% {
        transform: translate(0, 0) scale(1) rotate(0deg);
    }
    50% {
        transform: translate(22px, -12px) scale(1.12) rotate(90deg);
    }
    100% {
        transform: translate(0, 0) scale(1) rotate(0deg);
    }
}

/* ─── TOP NAV ─── */
.top-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 50;
    height: 70px;
    background: rgba(255, 255, 255, 0.55);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1.5px solid rgba(203, 213, 225, 0.35);
    box-shadow: 0 2px 16px rgba(0, 0, 0, 0.04);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
}

.nav-inner {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    height: 100%;
}

.nav-left {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    min-width: 40px;
}

.nav-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center; /* Diubah menjadi center untuk penempatan yang lebih proporsional */
    max-width: 560px;
    overflow: hidden;
}

.nav-title-text {
    display: flex;
    align-items: baseline;
    gap: 8px;
    line-height: 1;
}

.title-pretest {
    font-family: "Baloo 2", cursive;
    font-size: 38px; /* Ukuran diperbesar */
    font-weight: 900;
    color: #38bdf8;
    letter-spacing: 2px;
    text-transform: uppercase;
}

/* Progress Bar */
.prog-wrapper {
    width: 100%;
}

.prog-track {
    width: 100%;
    height: 18px;
    background: rgba(229, 229, 229, 0.7);
    border-radius: 99px;
    position: relative;
    overflow: visible;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.06);
}

.prog-fill {
    height: 100%;
    background: linear-gradient(90deg, #1cb0f6, #0ea5e9, #38bdf8);
    border-radius: 99px;
    transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    min-width: 18px;
    box-shadow: 0 2px 8px rgba(28, 176, 246, 0.4);
}

.prog-shine {
    position: absolute;
    top: 3px;
    left: 8px;
    right: 8px;
    height: 5px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 99px;
}

.prog-tip {
    position: absolute;
    right: -4px;
    top: 50%;
    transform: translateY(-50%);
    width: 22px;
    height: 22px;
    background: #1cb0f6;
    border-radius: 50%;
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(28, 176, 246, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    animation: tipPulse 1.5s ease-in-out infinite;
}

@keyframes tipPulse {
    0%,
    100% {
        box-shadow: 0 0 0 0 rgba(28, 176, 246, 0.4);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(28, 176, 246, 0);
    }
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}

/* ─── MUSIC NAV BUTTON (hanya di mobile) ─── */
.music-nav-btn {
    display: none; /* tersembunyi di desktop */
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 2px solid #e5e5e5;
    border-bottom: 3px solid #cbd5e1;
    background: rgba(255, 255, 255, 0.9);
    cursor: pointer;
    color: #94a3b8;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.music-nav-btn:active {
    transform: translateY(2px);
    border-bottom-width: 1px;
}
.music-nav-btn.music-on {
    background: #1cb0f6;
    border-color: #1cb0f6;
    border-bottom-color: #1899d6;
    color: white;
}

/* ─── TIMER ─── */
.timer-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.85);
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    color: #ff9600;
    padding: 6px 12px;
    border-radius: 12px;
    font-family: "Righteous", cursive;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    flex-shrink: 0;
}

.timer-icon-wrap {
    display: flex;
    align-items: center;
    animation: timerTick 1s ease-in-out infinite;
}

@keyframes timerTick {
    0%,
    100% {
        transform: rotate(0deg);
    }
    25% {
        transform: rotate(-8deg);
    }
    75% {
        transform: rotate(8deg);
    }
}

.timer-warning {
    border-color: #ff4b4b !important;
    border-bottom-color: #ea2b2b !important;
    color: #ff4b4b !important;
    background: rgba(255, 75, 75, 0.08) !important;
    animation: pulseWarn 0.8s ease infinite !important;
}

@keyframes pulseWarn {
    0%,
    100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.06);
    }
}

/* ─── MAIN SCROLL ─── */
.main-scroll {
    position: relative;
    z-index: 10;
    padding-top: 70px;
    min-height: 100vh;
}

:deep(.footer-bar) {
    background: rgba(255, 255, 255, 0.35) !important;
    backdrop-filter: blur(16px) !important;
    -webkit-backdrop-filter: blur(16px) !important;
    border-top: 1.5px solid rgba(255, 255, 255, 0.2) !important;
    box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.03) !important;
}

/* ─── MUSIC FAB — desktop only ─── */
.music-fab {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 100;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #afafaf;
    transition: all 0.15s ease;
    pointer-events: all;
}
.music-fab:hover {
    background: #f7f7f7;
    transform: translateY(-2px);
    border-bottom-width: 5px;
}
.music-fab:active {
    transform: translateY(2px);
    border-bottom-width: 2px;
}
.music-fab.music-on {
    background: #1cb0f6;
    border-color: #1cb0f6;
    border-bottom-color: #1899d6;
    color: white;
}
.music-fab svg {
    width: 22px;
    height: 22px;
}

/* ─── TRANSITIONS ─── */
.slide-fade-enter-active {
    transition: all 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.slide-fade-leave-active {
    transition: all 0.28s cubic-bezier(0.4, 0, 1, 1);
}
.slide-fade-enter-from {
    opacity: 0;
    transform: translateY(12px) scale(0.94);
}
.slide-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.94);
}

/* ─── RESPONSIVE ─── */
@media (max-width: 768px) {
    .nav-inner {
        padding: 0 12px;
        gap: 8px;
    }
    .timer-badge {
        font-size: 13px;
        padding: 5px 8px;
        gap: 4px;
    }
    .timer-badge .timer-icon-wrap svg {
        width: 13px;
        height: 13px;
    }

    /* FAB disembunyikan di mobile */
    .music-fab {
        display: none;
    }

    .title-pretest {
        font-size: 32px; /* Diperbesar dari 26px */
    }
    .prog-track {
        height: 14px;
    }
    .prog-tip {
        width: 18px;
        height: 18px;
    }
}

@media (max-width: 480px) {
    .nav-inner {
        gap: 6px;
    }
    .timer-badge {
        font-size: 12px;
        padding: 4px 7px;
    }
}
</style>
