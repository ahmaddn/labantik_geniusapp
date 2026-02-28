<script setup>
import { ref } from "vue";

const musicOn = ref(false);

function toggleMusic() {
    musicOn.value = !musicOn.value;
    // Integrate audio here if needed
    // e.g. audioRef.value.play() / .pause()
}
</script>

<template>
    <div class="playground-layout">
        <!-- ░░ ANIMATED NATURE SCENE BACKGROUND ░░ -->
        <div class="scene-bg">
            <!-- Sky gradient -->
            <div class="sky-layer"></div>

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
                <div class="cloud-puff p1"></div>
                <div class="cloud-puff p2"></div>
                <div class="cloud-puff p3"></div>
            </div>
            <div class="cloud cloud-2">
                <div class="cloud-puff p1"></div>
                <div class="cloud-puff p2"></div>
                <div class="cloud-puff p3"></div>
            </div>
            <div class="cloud cloud-3">
                <div class="cloud-puff p1"></div>
                <div class="cloud-puff p2"></div>
                <div class="cloud-puff p3"></div>
            </div>

            <!-- Back hills -->
            <div class="hills-back">
                <div class="hill hb-1"></div>
                <div class="hill hb-2"></div>
                <div class="hill hb-3"></div>
            </div>

            <!-- Trees Left -->
            <div class="tree-zone tree-left">
                <div class="tree t-large">
                    <div class="tree-canopy c-dark"></div>
                    <div class="tree-canopy c-mid"></div>
                    <div class="tree-trunk"></div>
                </div>
                <div class="tree t-medium" style="margin-left: -10px">
                    <div class="tree-canopy c-dark"></div>
                    <div class="tree-canopy c-mid"></div>
                    <div class="tree-trunk"></div>
                </div>
                <div class="tree t-small" style="margin-left: -5px">
                    <div class="tree-canopy c-light"></div>
                    <div class="tree-trunk"></div>
                </div>
            </div>

            <!-- Trees Right -->
            <div class="tree-zone tree-right">
                <div class="tree t-small" style="margin-right: -5px">
                    <div class="tree-canopy c-light"></div>
                    <div class="tree-trunk"></div>
                </div>
                <div class="tree t-medium" style="margin-right: -10px">
                    <div class="tree-canopy c-dark"></div>
                    <div class="tree-canopy c-mid"></div>
                    <div class="tree-trunk"></div>
                </div>
                <div class="tree t-large">
                    <div class="tree-canopy c-dark"></div>
                    <div class="tree-canopy c-mid"></div>
                    <div class="tree-trunk"></div>
                </div>
            </div>

            <!-- Ground mid -->
            <div class="ground-mid"></div>

            <!-- River -->
            <div class="river-container">
                <div class="river-body">
                    <div class="ripple r1"></div>
                    <div class="ripple r2"></div>
                    <div class="ripple r3"></div>
                    <div class="ripple r4"></div>
                </div>
                <div class="river-edge re-top"></div>
                <div class="river-edge re-bottom"></div>
            </div>

            <!-- Ground front -->
            <div class="ground-front">
                <!-- Flowers left -->
                <div class="flower-group fg-left">
                    <div class="flower">
                        <div class="bloom bloom-white"></div>
                        <div class="flower-stem"></div>
                    </div>
                    <div class="flower" style="margin-left: 8px">
                        <div class="bloom bloom-yellow"></div>
                        <div class="flower-stem"></div>
                    </div>
                    <div class="flower" style="margin-left: 6px">
                        <div class="bloom bloom-pink"></div>
                        <div class="flower-stem"></div>
                    </div>
                </div>

                <!-- Flowers right -->
                <div class="flower-group fg-right">
                    <div class="flower">
                        <div class="bloom bloom-yellow"></div>
                        <div class="flower-stem"></div>
                    </div>
                    <div class="flower" style="margin-left: 8px">
                        <div class="bloom bloom-white"></div>
                        <div class="flower-stem"></div>
                    </div>
                    <div class="flower" style="margin-left: 6px">
                        <div class="bloom bloom-pink"></div>
                        <div class="flower-stem"></div>
                    </div>
                </div>

                <!-- Frog left -->
                <div class="frog frog-l">
                    <div class="frog-head">
                        <div class="frog-eye fe-l"></div>
                        <div class="frog-eye fe-r"></div>
                        <div class="frog-smile"></div>
                    </div>
                    <div class="frog-body-shape"></div>
                </div>

                <!-- Frog right -->
                <div class="frog frog-r">
                    <div class="frog-head">
                        <div class="frog-eye fe-l"></div>
                        <div class="frog-eye fe-r"></div>
                        <div class="frog-smile"></div>
                    </div>
                    <div class="frog-body-shape"></div>
                </div>

                <!-- Stone -->
                <div class="stone"></div>

                <!-- Grass tufts -->
                <div class="grass-tuft gt1"></div>
                <div class="grass-tuft gt2"></div>
                <div class="grass-tuft gt3"></div>
                <div class="grass-tuft gt4"></div>
            </div>
        </div>

        <!-- ░░ SLOT - Main Content ░░ -->
        <slot />

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
.playground-layout {
    position: relative;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    font-family: "Nunito", "Baloo 2", sans-serif;
}

/* ─── SCENE BG ─── */
.scene-bg {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 0;
}

/* SKY */
.sky-layer {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        180deg,
        #7ec8e3 0%,
        #b8e4f0 40%,
        #d6f0fa 55%,
        #b5da7e 55%,
        #8fc94a 65%,
        #6aaf2e 75%,
        #5a9e24 100%
    );
}

/* SUN */
.sun {
    position: absolute;
    top: 6%;
    right: 12%;
    width: 60px;
    height: 60px;
    background: radial-gradient(circle, #ffe066 60%, #ffb700 100%);
    border-radius: 50%;
    box-shadow: 0 0 30px 10px rgba(255, 220, 50, 0.35);
    animation: sun-pulse 4s ease-in-out infinite;
}
.sun-ray {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 70px;
    height: 3px;
    margin-top: -1.5px;
    transform-origin: left center;
    background: linear-gradient(to right, rgba(255, 220, 50, 0.5), transparent);
}
@keyframes sun-pulse {
    0%,
    100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.85;
        transform: scale(1.06);
    }
}

/* CLOUDS */
.cloud {
    position: absolute;
    display: flex;
    align-items: flex-end;
}
.cloud-puff {
    background: white;
    border-radius: 50%;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.cloud .p1 {
    width: 50px;
    height: 36px;
}
.cloud .p2 {
    width: 70px;
    height: 50px;
    margin-left: -15px;
}
.cloud .p3 {
    width: 45px;
    height: 32px;
    margin-left: -12px;
}

.cloud-1 {
    top: 7%;
    left: 6%;
    animation: cloud-drift 30s linear infinite;
    opacity: 0.92;
}
.cloud-2 {
    top: 13%;
    left: 35%;
    animation: cloud-drift 40s linear infinite -15s;
    opacity: 0.85;
}
.cloud-3 {
    top: 5%;
    left: 65%;
    animation: cloud-drift 35s linear infinite -8s;
    opacity: 0.9;
}

@keyframes cloud-drift {
    from {
        transform: translateX(-200px);
    }
    to {
        transform: translateX(105vw);
    }
}

/* HILLS BACK */
.hills-back {
    position: absolute;
    bottom: 38%;
    left: 0;
    right: 0;
    height: 160px;
}
.hill {
    position: absolute;
    border-radius: 50%;
}
.hb-1 {
    width: 380px;
    height: 180px;
    background: #5aaa30;
    bottom: 0;
    left: -60px;
}
.hb-2 {
    width: 450px;
    height: 200px;
    background: #4e9e28;
    bottom: 0;
    left: 28%;
}
.hb-3 {
    width: 350px;
    height: 160px;
    background: #57a82e;
    bottom: 0;
    right: -40px;
}

/* TREES */
.tree-zone {
    position: absolute;
    bottom: 30%;
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
.tree-canopy {
    border-radius: 50% 50% 45% 45%;
    position: relative;
    margin-bottom: -8px;
}
.c-dark {
    background: #2d7a2f;
}
.c-mid {
    background: #3d9e40;
}
.c-light {
    background: #52bc55;
}
.tree-trunk {
    background: linear-gradient(to right, #7c4d1e, #a0621a);
    border-radius: 3px;
}
.t-large .c-dark {
    width: 100px;
    height: 90px;
}
.t-large .c-mid {
    width: 85px;
    height: 75px;
}
.t-large .tree-trunk {
    width: 20px;
    height: 50px;
}
.t-medium .c-dark {
    width: 80px;
    height: 72px;
}
.t-medium .c-mid {
    width: 68px;
    height: 60px;
}
.t-medium .tree-trunk {
    width: 16px;
    height: 40px;
}
.t-small .c-light {
    width: 60px;
    height: 55px;
}
.t-small .tree-trunk {
    width: 12px;
    height: 30px;
}

/* GROUND MID */
.ground-mid {
    position: absolute;
    bottom: 22%;
    left: 0;
    right: 0;
    height: 12%;
    background: linear-gradient(180deg, #7ec94a 0%, #5aaa2e 100%);
}

/* RIVER */
.river-container {
    position: absolute;
    bottom: 18%;
    left: 0;
    right: 0;
    height: 70px;
}
.river-body {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, #5bc8e8 0%, #2ba8cc 100%);
    overflow: hidden;
}
.ripple {
    position: absolute;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.28);
    animation: ripple-move 4s ease-in-out infinite;
}
.r1 {
    width: 120px;
    top: 15px;
    left: 10%;
    animation-delay: 0s;
}
.r2 {
    width: 80px;
    top: 30px;
    left: 38%;
    animation-delay: 1s;
}
.r3 {
    width: 100px;
    top: 20px;
    left: 58%;
    animation-delay: 2s;
}
.r4 {
    width: 60px;
    top: 40px;
    left: 78%;
    animation-delay: 0.5s;
}
@keyframes ripple-move {
    0%,
    100% {
        opacity: 0.28;
        transform: scaleX(1);
    }
    50% {
        opacity: 0.5;
        transform: scaleX(1.1);
    }
}

.river-edge {
    position: absolute;
    left: 0;
    right: 0;
    height: 14px;
    background: #4ab832;
    border-radius: 50%;
}
.re-top {
    top: -7px;
}
.re-bottom {
    bottom: -7px;
}

/* GROUND FRONT */
.ground-front {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 20%;
    background: linear-gradient(180deg, #6ec240 0%, #4fa822 100%);
}

/* FLOWERS */
.flower-group {
    position: absolute;
    bottom: 55%;
    display: flex;
    align-items: flex-end;
}
.fg-left {
    left: 3%;
}
.fg-right {
    right: 3%;
}
.flower {
    display: flex;
    flex-direction: column;
    align-items: center;
}
.bloom {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.5);
}
.bloom-white {
    background: #f0f0f0;
    box-shadow: 0 0 0 4px rgba(240, 240, 240, 0.6);
}
.bloom-yellow {
    background: #ffde00;
    box-shadow: 0 0 0 4px rgba(255, 222, 0, 0.4);
}
.bloom-pink {
    background: #ff7eb3;
    box-shadow: 0 0 0 4px rgba(255, 126, 179, 0.4);
}
.flower-stem {
    width: 3px;
    height: 22px;
    background: #3a8c1a;
    border-radius: 2px;
}

/* FROGS */
.frog {
    position: absolute;
    bottom: 55%;
}
.frog-l {
    left: 15%;
}
.frog-r {
    right: 8%;
}
.frog-head {
    width: 28px;
    height: 22px;
    background: #3dba4e;
    border-radius: 50% 50% 45% 45%;
    position: relative;
}
.frog-eye {
    position: absolute;
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    top: -4px;
}
.fe-l {
    left: 4px;
}
.fe-r {
    right: 4px;
}
.frog-eye::after {
    content: "";
    position: absolute;
    width: 5px;
    height: 5px;
    background: #1a1a1a;
    border-radius: 50%;
    top: 1.5px;
    left: 1.5px;
}
.frog-smile {
    position: absolute;
    bottom: 5px;
    left: 50%;
    transform: translateX(-50%);
    width: 12px;
    height: 5px;
    border-bottom: 2px solid #2a8838;
    border-radius: 50%;
}
.frog-body-shape {
    width: 32px;
    height: 14px;
    background: #3dba4e;
    border-radius: 50%;
    margin: 0 auto;
    margin-top: -3px;
}

/* STONE */
.stone {
    position: absolute;
    bottom: 30%;
    left: 42%;
    width: 34px;
    height: 20px;
    background: linear-gradient(180deg, #a09080, #7a6a5a);
    border-radius: 50% 50% 45% 45%;
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
}

/* GRASS TUFTS */
.grass-tuft {
    position: absolute;
    bottom: 85%;
}
.grass-tuft::before,
.grass-tuft::after {
    content: "";
    position: absolute;
    width: 6px;
    height: 14px;
    background: #4fa822;
    border-radius: 50% 50% 0 0;
}
.grass-tuft::before {
    transform: rotate(-15deg);
    left: 0;
}
.grass-tuft::after {
    transform: rotate(15deg);
    left: 6px;
}
.gt1 {
    left: 8%;
}
.gt2 {
    left: 22%;
}
.gt3 {
    right: 22%;
}
.gt4 {
    right: 8%;
}

/* ─── MUSIC FAB ─── */
.music-fab {
    position: fixed;
    bottom: 24px;
    left: 24px;
    z-index: 100;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(6px);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
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
    width: 22px;
    height: 22px;
}
</style>
