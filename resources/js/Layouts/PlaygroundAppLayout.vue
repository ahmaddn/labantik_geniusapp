<script setup>
import { ref } from "vue";

const musicOn = ref(false);

function toggleMusic() {
    musicOn.value = !musicOn.value;
}

defineProps({
    siswa: {
        type: Object,
        default: () => ({ nama: "Siswa", kelas: "" }),
    },
});
</script>

<template>
    <div class="app-layout">
        <!-- ░░ BACKGROUND ░░ -->
        <div class="bg-scene">
            <div class="sky"></div>

            <!-- Sun -->
            <div class="sun">
                <div
                    class="sun-ray"
                    v-for="i in 8"
                    :key="i"
                    :style="{ transform: `rotate(${i * 45}deg)` }"
                ></div>
            </div>

            <!-- Clouds -->
            <div class="cloud cloud-1">
                <div class="cp p1"></div>
                <div class="cp p2"></div>
                <div class="cp p3"></div>
            </div>
            <div class="cloud cloud-2">
                <div class="cp p1"></div>
                <div class="cp p2"></div>
                <div class="cp p3"></div>
            </div>
            <div class="cloud cloud-3">
                <div class="cp p1"></div>
                <div class="cp p2"></div>
                <div class="cp p3"></div>
            </div>

            <!-- Hills -->
            <div class="hills">
                <div class="hill h1"></div>
                <div class="hill h2"></div>
                <div class="hill h3"></div>
            </div>

            <!-- Trees left -->
            <div class="tree-strip tree-left">
                <div class="tree tl">
                    <div class="canopy c-dk"></div>
                    <div class="canopy c-md"></div>
                    <div class="trunk"></div>
                </div>
                <div class="tree tm">
                    <div class="canopy c-dk"></div>
                    <div class="canopy c-md"></div>
                    <div class="trunk"></div>
                </div>
            </div>

            <!-- Trees right -->
            <div class="tree-strip tree-right">
                <div class="tree tm">
                    <div class="canopy c-dk"></div>
                    <div class="canopy c-md"></div>
                    <div class="trunk"></div>
                </div>
                <div class="tree tl">
                    <div class="canopy c-dk"></div>
                    <div class="canopy c-md"></div>
                    <div class="trunk"></div>
                </div>
            </div>

            <!-- Ground strip -->
            <div class="ground-strip"></div>

            <!-- Grass tufts -->
            <div class="grass-tuft gt1"></div>
            <div class="grass-tuft gt2"></div>
            <div class="grass-tuft gt3"></div>
            <div class="grass-tuft gt4"></div>
        </div>

        <!-- ░░ TOP NAV ░░ -->
        <header class="top-nav">
            <div class="nav-inner">
                <!-- Logo -->
                <div class="nav-logo">
                    <span class="logo-g">Geni</span
                    ><span class="logo-u">uss</span>
                    <span class="logo-dot">·</span>
                    <span class="logo-edu">Playground</span>
                </div>

                <!-- Siswa info -->
                <div class="nav-siswa">
                    <div class="siswa-avatar">
                        {{ siswa.nama.charAt(0).toUpperCase() }}
                    </div>
                    <div class="siswa-info">
                        <span class="siswa-nama">{{ siswa.nama }}</span>
                        <span class="siswa-kelas">{{ siswa.kelas }}</span>
                    </div>
                </div>
            </div>
        </header>

        <!-- ░░ SCROLL CONTENT ░░ -->
        <main class="main-scroll">
            <slot />
        </main>

        <!-- ░░ MUSIC FAB ░░ -->
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

.sky {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        #7ec8e3 0%,
        #b8e4f0 38%,
        #d6f0fa 52%,
        #b5da7e 52%,
        #8fc94a 62%,
        #6aaf2e 72%,
        #5a9e24 100%
    );
}

/* SUN */
.sun {
    position: absolute;
    top: 5%;
    right: 10%;
    width: 52px;
    height: 52px;
    background: radial-gradient(circle, #ffe066 60%, #ffb700 100%);
    border-radius: 50%;
    box-shadow: 0 0 28px 8px rgba(255, 220, 50, 0.3);
    animation: sun-pulse 4s ease-in-out infinite;
}
.sun-ray {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 62px;
    height: 2.5px;
    margin-top: -1.25px;
    transform-origin: left center;
    background: linear-gradient(
        to right,
        rgba(255, 220, 50, 0.45),
        transparent
    );
}
@keyframes sun-pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.82;
        transform: scale(1.07);
    }
}

/* CLOUDS */
.cloud {
    position: absolute;
    display: flex;
    align-items: flex-end;
}
.cp {
    background: white;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.cloud .p1 {
    width: 44px;
    height: 32px;
}
.cloud .p2 {
    width: 62px;
    height: 44px;
    margin-left: -13px;
}
.cloud .p3 {
    width: 40px;
    height: 28px;
    margin-left: -10px;
}

.cloud-1 {
    top: 6%;
    left: 5%;
    opacity: 0.9;
    animation: cloud-drift 30s linear infinite;
}
.cloud-2 {
    top: 12%;
    left: 32%;
    opacity: 0.82;
    animation: cloud-drift 42s linear infinite -18s;
}
.cloud-3 {
    top: 4%;
    left: 60%;
    opacity: 0.88;
    animation: cloud-drift 36s linear infinite -9s;
}

@keyframes cloud-drift {
    from {
        transform: translateX(-180px);
    }
    to {
        transform: translateX(105vw);
    }
}

/* HILLS */
.hills {
    position: absolute;
    bottom: 40%;
    left: 0;
    right: 0;
    height: 140px;
}
.hill {
    position: absolute;
    border-radius: 50%;
}
.h1 {
    width: 340px;
    height: 160px;
    background: #5aaa30;
    bottom: 0;
    left: -50px;
}
.h2 {
    width: 400px;
    height: 180px;
    background: #4e9e28;
    bottom: 0;
    left: 26%;
}
.h3 {
    width: 310px;
    height: 145px;
    background: #57a82e;
    bottom: 0;
    right: -35px;
}

/* TREES */
.tree-strip {
    position: absolute;
    bottom: 32%;
    display: flex;
    align-items: flex-end;
    z-index: 1;
}
.tree-left {
    left: 0;
}
.tree-right {
    right: 0;
}

.tree {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.canopy {
    border-radius: 50% 50% 45% 45%;
    margin-bottom: -7px;
}
.c-dk {
    background: #2d7a2f;
}
.c-md {
    background: #3d9e40;
}
.trunk {
    background: linear-gradient(to right, #7c4d1e, #a0621a);
    border-radius: 3px;
}

.tl .c-dk {
    width: 90px;
    height: 80px;
}
.tl .c-md {
    width: 76px;
    height: 66px;
}
.tl .trunk {
    width: 18px;
    height: 44px;
}
.tm .c-dk {
    width: 70px;
    height: 64px;
}
.tm .c-md {
    width: 58px;
    height: 52px;
}
.tm .trunk {
    width: 14px;
    height: 36px;
}

/* GROUND STRIP */
.ground-strip {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 34%;
    background: linear-gradient(180deg, #7ec94a 0%, #4fa822 100%);
}

/* GRASS TUFTS */
.grass-tuft {
    position: absolute;
    bottom: 34%;
}
.grass-tuft::before,
.grass-tuft::after {
    content: "";
    position: absolute;
    width: 5px;
    height: 12px;
    background: #4fa822;
    border-radius: 50% 50% 0 0;
}
.grass-tuft::before {
    transform: rotate(-15deg);
    left: 0;
}
.grass-tuft::after {
    transform: rotate(15deg);
    left: 5px;
}
.gt1 {
    left: 7%;
}
.gt2 {
    left: 20%;
}
.gt3 {
    right: 20%;
}
.gt4 {
    right: 7%;
}

/* ─── TOP NAV ─── */
.top-nav {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 50;
    height: 64px;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(16px);
    border-bottom: 1.5px solid rgba(255, 255, 255, 0.7);
    box-shadow: 0 2px 20px rgba(0, 80, 150, 0.1);
}
.nav-inner {
    max-width: 1100px;
    margin: 0 auto;
    height: 100%;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.nav-logo {
    font-family: "Baloo 2", cursive;
    font-size: 22px;
    font-weight: 900;
    display: flex;
    align-items: center;
    gap: 3px;
    letter-spacing: -0.3px;
}
.logo-g {
    color: #1a1a2e;
}
.logo-u {
    color: #e8470a;
}
.logo-dot {
    color: #9ca3af;
    font-size: 18px;
    margin: 0 4px;
}
.logo-edu {
    color: #5a9e24;
    font-size: 16px;
    font-weight: 700;
}

.nav-siswa {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(90, 170, 46, 0.08);
    border: 1.5px solid rgba(90, 170, 46, 0.2);
    border-radius: 50px;
    padding: 6px 16px 6px 8px;
}
.siswa-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: linear-gradient(135deg, #5aaa2e, #3d8c1e);
    color: white;
    font-family: "Baloo 2", cursive;
    font-size: 16px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
}
.siswa-info {
    display: flex;
    flex-direction: column;
    line-height: 1.2;
}
.siswa-nama {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
}
.siswa-kelas {
    font-size: 11px;
    font-weight: 600;
    color: #5a9e24;
}

/* ─── MAIN SCROLL ─── */
.main-scroll {
    position: relative;
    z-index: 10;
    padding-top: 64px;
    min-height: 100vh;
}

/* ─── MUSIC FAB ─── */
.music-fab {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 100;
    width: 46px;
    height: 46px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(8px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.14);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #5a9e24;
    transition: all 0.25s;
    pointer-events: all;
}
.music-fab:hover {
    transform: scale(1.1);
    background: white;
}
.music-fab.music-on {
    background: #5aaa2e;
    color: white;
}
.music-fab svg {
    width: 20px;
    height: 20px;
}
</style>
