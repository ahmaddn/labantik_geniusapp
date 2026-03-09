<script setup>
import { ref, onMounted } from "vue";

// ── State ──
const mascotBounce = ref(false);
const musicOn = ref(false);

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => {
        mascotBounce.value = true;
        setTimeout(() => {
            mascotBounce.value = false;
        }, 1200);
    }, 800);
});

// ── Music Toggle ──
function toggleMusic() {
    musicOn.value = !musicOn.value;
}

// ── Expose untuk parent component ──
defineExpose({
    mascotBounce,
});
</script>

<template>
    <!-- ░░ FULL PAGE WITH BACKGROUND IMAGE ░░ -->
    <div class="landing-page">
        <!-- Background Image -->

        <!-- ░░ MASCOT CHARACTER IMAGE ░░ -->
        <div class="mascot" :class="{ bounce: mascotBounce }">
            <img
                src="/images/templates/pose_keren.png"
                alt="Character Mascot"
                class="mascot-img"
            />
        </div>

        <!-- ░░ SLOT FOR PAGE CONTENT ░░ -->
        <slot></slot>

        <!-- ░░ MUSIC BUTTON ░░ -->
        <button
            class="music-fab"
            @click="toggleMusic"
            :class="{ 'music-on': musicOn }"
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
    </div>
</template>

<style scoped>
/* ─── RESET & BASE ─── */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* ─── LANDING PAGE ─── */
.landing-page {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    font-family: "Nunito", "Baloo 2", sans-serif;
}

/* ─── BACKGROUND IMAGE ─── */
.bg-image {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    background-image: url("/images/templates/background.png");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* ─── MASCOT ─── */
.mascot {
    position: fixed;
    right: 70%;
    bottom: 5%;
    width: 480px;
    height: auto;
    z-index: 300;
    filter: drop-shadow(0 12px 24px rgba(0, 80, 140, 0.18));
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.mascot-img {
    width: 100%;
    height: auto;
    display: block;
}
@media (max-width: 1200px) {
    .mascot {
        right: 5%;
        width: 220px;
    }
}
@media (max-width: 900px) {
    .mascot {
        right: 10px;
        width: 160px;
        bottom: 8%;
        opacity: 0.9;
    }
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

/* ─── MUSIC FAB ─── */
.music-fab {
    position: fixed;
    bottom: 28px;
    left: 28px;
    z-index: 301;
    width: 52px;
    height: 52px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #4fc3f7;
    transition: all 0.25s;
}
.music-fab:hover {
    transform: scale(1.1);
    background: white;
}
.music-fab.music-on {
    background: #4fc3f7;
    color: white;
}
.music-fab svg {
    width: 24px;
    height: 24px;
}
</style>
