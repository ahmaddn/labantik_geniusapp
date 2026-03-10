<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Zap,
    LogIn,
    User,
    Music2,
    VolumeX,
    ArrowLeft,
    ChevronRight,
    Star,
    Sparkles,
} from "lucide-vue-next";

const ready = ref(false);
const musicOn = ref(false);
const audioRef = ref(null);

const props = defineProps({
    backsound: { type: String, default: null },
});

/* ── Speech bubble ─────────────────────────────── */
const BUBBLE_LINES = [
    "Hai! Aku Geni!",
    "Selamat datang!",
    "Siap belajar?",
    "Masukkan username ya!",
    "Ayo kita mulai!",
    "Belajar itu seru!",
    "Aku menunggumu!",
    "Yuk masuk!",
];
const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLE_LINES.length;
        bubbleVisible.value = true;
    }, 280);
};

/* ── Form ──────────────────────────────────────── */
const form = useForm({ nama: "" });
const loginUrl = route("login.admin");
const localErrors = ref({ nama: "" });
const shakeBtn = ref(false);
const inputFocused = ref(false);

const errors = {
    get nama() {
        return localErrors.value.nama || form.errors.nama || "";
    },
};

/* ── Music ─────────────────────────────────────── */
const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio(props.backsound);
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
        audioRef.value.addEventListener("error", () => {
            audioRef.value = null;
            musicOn.value = false;
        });
    }
    if (musicOn.value) {
        audioRef.value.pause();
        musicOn.value = false;
    } else {
        try {
            await audioRef.value.play();
            musicOn.value = true;
        } catch {
            musicOn.value = false;
        }
    }
};

const initAutoMusic = async () => {
    if (!props.backsound) return;
    audioRef.value = new Audio(props.backsound);
    audioRef.value.loop = true;
    audioRef.value.volume = 0.4;
    audioRef.value.addEventListener("error", () => {
        audioRef.value = null;
        musicOn.value = false;
    });
    try {
        await audioRef.value.play();
        musicOn.value = true;
    } catch {
        musicOn.value = false;
    }
};

/* ── Validation ────────────────────────────────── */
function validateNama() {
    if (!form.nama.trim()) {
        localErrors.value.nama = "Username wajib diisi!";
        return false;
    }
    localErrors.value.nama = "";
    return true;
}

function handleLogin() {
    if (!validateNama()) {
        shakeBtn.value = true;
        setTimeout(() => (shakeBtn.value = false), 600);
        return;
    }
    form.post(route("playground.authenticate"), {
        onSuccess: () => router.visit(route("playground.index")),
        onError: () => {
            shakeBtn.value = true;
            setTimeout(() => (shakeBtn.value = false), 600);
        },
    });
}

function goBack() {
    router.visit(loginUrl);
}

/* ── Lifecycle ─────────────────────────────────── */
onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 100);
    setTimeout(() => {
        bubbleTimer = setInterval(rotateBubble, 2800);
    }, 1400);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(initAutoMusic, 500);
});
onUnmounted(() => {
    clearInterval(bubbleTimer);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
});
</script>

<template>
    <!-- Font loader -->
    <div style="display: none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap"
            rel="stylesheet"
        />
    </div>

    <div class="root">
        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <button class="tbtn" @click="goBack">
                <ArrowLeft :size="15" :stroke-width="2.5" />
                <span class="tbtn-lbl">Login Ke Admin</span>
            </button>

            <div class="brand">
                <div class="brand-dot">
                    <Zap
                        :size="13"
                        color="#fff"
                        fill="white"
                        :stroke-width="2"
                    />
                </div>
                <span class="brand-name">Geniuss</span>
            </div>

            <div class="topbar-r">
                <button
                    class="tbtn tbtn-sq"
                    :class="{ 'tbtn--on': musicOn }"
                    @click="toggleMusic"
                >
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2" />
                    <VolumeX v-else :size="15" :stroke-width="2" />
                </button>
            </div>
        </header>

        <!-- ══ SPLIT BODY ══ -->
        <div class="split" :class="{ 'split--on': ready }">
            <!-- LEFT PANEL — mascot -->
            <div class="left-panel">
                <div class="lp-blob lp-blob-1"></div>
                <div class="lp-blob lp-blob-2"></div>
                <div class="lp-rings">
                    <div class="lp-ring r1"></div>
                    <div class="lp-ring r2"></div>
                </div>

                <!-- Partikel mengambang -->
                <div class="particles" aria-hidden="true">
                    <span class="p p1"
                        ><Star :size="16" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p2"
                        ><Sparkles :size="18" :stroke-width="1.5"
                    /></span>
                    <span class="p p3"
                        ><Star :size="10" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p4"
                        ><Zap :size="13" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p5"
                        ><Star :size="20" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p6"
                        ><Sparkles :size="14" :stroke-width="1.5"
                    /></span>
                    <span class="p p7"
                        ><Zap :size="11" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p8"
                        ><Star :size="13" fill="currentColor" :stroke-width="0"
                    /></span>
                    <span class="p p9"
                        ><Sparkles :size="11" :stroke-width="1.5"
                    /></span>
                    <span class="p p10"
                        ><Star :size="8" fill="currentColor" :stroke-width="0"
                    /></span>
                </div>

                <div class="lp-text">
                    <h1 class="lp-title">Selamat Datang!</h1>
                    <p class="lp-sub">
                        Masuk dan mulai petualangan belajarmu bersama Geni hari
                        ini.
                    </p>
                </div>

                <div class="mascot-wrap" @click="rotateBubble">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble">
                            <p>{{ BUBBLE_LINES[bubbleIdx] }}</p>
                            <span class="bub-o"></span>
                            <span class="bub-i"></span>
                        </div>
                    </Transition>
                    <div class="mascot-glow"></div>
                    <img
                        src="/images/templates/pose_keren.png"
                        alt="Maskot Geni"
                        class="mascot"
                        draggable="false"
                    />
                    <div class="mascot-shadow"></div>
                </div>
            </div>

            <!-- RIGHT PANEL — form card -->
            <div class="right-panel">
                <div class="form-card">
                    <!-- Avatar icon -->
                    <div class="fc-avatar">
                        <User :size="26" :stroke-width="1.8" color="#2563eb" />
                    </div>

                    <h2 class="fc-title">Masuk ke Playground</h2>
                    <p class="fc-sub">
                        Masukkan username dari gurumu untuk memulai.
                    </p>

                    <!-- Username field -->
                    <div class="field-block">
                        <label class="f-label" for="pg-user">Username</label>
                        <div
                            class="f-row"
                            :class="{
                                'f-row--on': inputFocused,
                                'f-row--err': !!errors.nama,
                            }"
                        >
                            <User
                                :size="16"
                                :stroke-width="2.2"
                                class="f-ico"
                            />
                            <input
                                id="pg-user"
                                v-model="form.nama"
                                type="text"
                                placeholder="Tulis username kamu..."
                                maxlength="60"
                                autocomplete="off"
                                class="f-input"
                                @focus="
                                    inputFocused = true;
                                    localErrors.nama = '';
                                    form.clearErrors('nama');
                                "
                                @blur="
                                    inputFocused = false;
                                    validateNama();
                                "
                                @input="
                                    localErrors.nama = '';
                                    form.clearErrors('nama');
                                "
                                @keyup.enter="handleLogin"
                            />
                        </div>
                        <Transition name="err">
                            <p v-if="errors.nama" class="f-err" role="alert">
                                {{ errors.nama }}
                            </p>
                        </Transition>
                    </div>

                    <!-- CTA -->
                    <button
                        class="cta-btn"
                        :class="{
                            'cta--shake': shakeBtn,
                            'cta--loading': form.processing,
                        }"
                        :disabled="form.processing"
                        @click="handleLogin"
                    >
                        <template v-if="!form.processing">
                            <LogIn :size="17" :stroke-width="2.5" />
                            <span>Masuk Sekarang</span>
                        </template>
                        <template v-else>
                            <span class="spinner"></span>
                            <span>Sedang masuk...</span>
                        </template>
                    </button>

                    <p class="fc-hint">
                        Belum punya username? Tanya guru kelasmu!
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ─────────── ROOT ─────────── */
.root {
    height: 100dvh;
    display: flex;
    flex-direction: column;
    font-family: "Nunito", sans-serif;
    overflow: hidden;
    position: relative;
}

/* Background foto di belakang */
.root::after {
    content: "";
    position: fixed;
    inset: 0;
    background: url("/images/templates/background.png") center / cover no-repeat;
    z-index: -2;
}

/* Kaca biru transparan di atasnya */
.root::before {
    content: "";
    position: fixed;
    inset: 0;
    background:
        radial-gradient(ellipse 90% 70% at 10% 5%, rgba(30, 64, 175, 0.78) 0%, transparent 55%),
        radial-gradient(ellipse 75% 60% at 90% 95%, rgba(29, 78, 216, 0.72) 0%, transparent 55%),
        rgba(30, 58, 138, 0.65);
    backdrop-filter: blur(0.3px);
    -webkit-backdrop-filter: blur(0.3px);
    z-index: -1;
}

/* ─────────── TOPBAR ─────────── */
.topbar {
    position: relative;
    z-index: 50;
    height: 60px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    padding: 0 24px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.10);
    box-shadow: 0 2px 24px rgba(0, 0, 0, 0.12);
}
.tbtn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 10px;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    cursor: pointer;
    transition: background 0.18s, transform 0.15s;
    flex-shrink: 0;
}
.tbtn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}
.tbtn-sq { padding: 7px 10px; }
.tbtn--on {
    background: #2563eb !important;
    border-color: #bfdbfe !important;
}

.brand {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    align-items: center;
    gap: 8px;
    pointer-events: none;
}
.brand-dot {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: #2563eb;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.5);
}
.brand-name {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #fff;
}
.topbar-r {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-left: auto;
    z-index: 3;
}

/* ─────────── SPLIT BODY ─────────── */
.split {
    position: relative;
    z-index: 10;
    flex: 1;
    display: flex;
    min-height: 0;
    opacity: 0;
    transition: opacity 0.5s;
}
.split--on { opacity: 1; }

/* ─────────── LEFT PANEL ─────────── */
.left-panel {
    width: 44%;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 36px 40px;
    overflow: hidden;
    border-radius: 0;
    background: rgba(29, 78, 216, 0.38);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-right: 1px solid rgba(255, 255, 255, 0.16);
    box-shadow: 6px 0 40px rgba(0, 0, 0, 0.22);
    transform: translateX(-30px);
    opacity: 0;
    animation: slideInLeft 0.55s 0.1s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes slideInLeft {
    to { transform: translateX(0); opacity: 1; }
}

/* ─── PARTICLES ─── */
.particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.p {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: pFloat ease-in-out infinite;
    opacity: 0;
}
.p1, .p3, .p5, .p8, .p10 { color: #fcd34d; }
.p2, .p6, .p9             { color: rgba(255, 255, 255, 0.65); }
.p4, .p7                  { color: #bfdbfe; }

.p1  { left: 8%;  top: 15%; animation-duration: 6s;   animation-delay: 0s;   }
.p2  { left: 78%; top: 20%; animation-duration: 8s;   animation-delay: 1.2s; }
.p3  { left: 18%; top: 60%; animation-duration: 7s;   animation-delay: 2.5s; }
.p4  { left: 70%; top: 65%; animation-duration: 9s;   animation-delay: 0.8s; }
.p5  { left: 85%; top: 35%; animation-duration: 11s;  animation-delay: 3.5s; }
.p6  { left: 32%; top: 82%; animation-duration: 7.5s; animation-delay: 1.8s; }
.p7  { left: 50%; top: 10%; animation-duration: 10s;  animation-delay: 4.2s; }
.p8  { left: 60%; top: 75%; animation-duration: 8.5s; animation-delay: 5s;   }
.p9  { left: 25%; top: 30%; animation-duration: 9.5s; animation-delay: 2s;   }
.p10 { left: 92%; top: 50%; animation-duration: 12s;  animation-delay: 6s;   }

@keyframes pFloat {
    0%   { transform: translateY(0)     rotate(0deg)   scale(1);    opacity: 0;   }
    10%  { opacity: 0.8; }
    45%  { transform: translateY(-22px) rotate(15deg)  scale(1.18); opacity: 0.6; }
    80%  { transform: translateY(-40px) rotate(-8deg)  scale(0.95); opacity: 0.3; }
    100% { transform: translateY(-58px) rotate(5deg)   scale(0.85); opacity: 0;   }
}

/* Blobs inside left panel */
.lp-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    pointer-events: none;
}
.lp-blob-1 {
    width: 320px; height: 320px;
    top: -100px; left: -80px;
    background: #3b82f6; opacity: 0.35;
    animation: blobDrift 20s ease-in-out infinite alternate;
}
.lp-blob-2 {
    width: 220px; height: 220px;
    bottom: -60px; right: -40px;
    background: #34d399; opacity: 0.18;
    animation: blobDrift 26s ease-in-out 6s infinite alternate;
}
@keyframes blobDrift {
    0%   { transform: translate(0, 0); }
    50%  { transform: translate(20px, 14px) scale(1.05); }
    100% { transform: translate(-14px, 24px); }
}

/* Rings */
.lp-rings { position: absolute; inset: 0; pointer-events: none; }
.lp-ring {
    position: absolute;
    border-radius: 50%;
    border: 1.5px solid rgba(191, 219, 254, 0.16);
    background: transparent;
    animation: ringPulse ease-out infinite;
}
.r1 { width: 260px; height: 260px; top: -60px;  left: -60px;  animation-duration: 10s; }
.r2 { width: 180px; height: 180px; bottom: -40px; right: -40px; animation-duration: 13s; animation-delay: 4s; }
@keyframes ringPulse {
    0%   { transform: scale(1);   opacity: 0.4; }
    70%  { transform: scale(1.5); opacity: 0.05; }
    100% { transform: scale(1.8); opacity: 0; }
}

/* Text block */
.lp-text {
    position: relative;
    z-index: 2;
    text-align: center;
    margin-bottom: 28px;
}
.lp-title {
    font-family: "Righteous", cursive;
    font-size: clamp(28px, 3.5vw, 44px);
    color: #fff;
    line-height: 1.15;
    margin-bottom: 10px;
    text-shadow: 0 3px 18px rgba(0, 0, 0, 0.25);
}
.lp-sub {
    font-size: 13.5px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.78);
    line-height: 1.65;
    max-width: 260px;
    margin: 0 auto;
}

/* Mascot */
.mascot-wrap {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
    user-select: none;
}
.bubble {
    position: relative;
    background: #fff;
    border: 2.5px solid #ffffff;
    border-radius: 18px;
    padding: 10px 16px;
    max-width: 210px;
    box-shadow: 0 6px 22px rgba(17, 11, 0, 0.22);
    margin-bottom: 6px;
    animation: bubFloat 3.5s ease-in-out infinite;
}
.bubble p { font-size: 13px; font-weight: 800; color: #2b2b2b; }
.bub-o, .bub-i {
    position: absolute;
    bottom: -13px; left: 22px;
    width: 0; height: 0;
}
.bub-o {
    border-left: 10px solid transparent;
    border-right: 6px solid transparent;
    border-top: 13px solid #ffffff;
}
.bub-i {
    bottom: -10px; left: 23px;
    border-left: 8px solid transparent;
    border-right: 5px solid transparent;
    border-top: 11px solid #fff;
}
@keyframes bubFloat {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-6px); }
}
.bbl-enter-active { transition: opacity 0.28s, transform 0.34s cubic-bezier(0.34, 1.56, 0.64, 1); }
.bbl-leave-active { transition: opacity 0.18s; }
.bbl-enter-from   { opacity: 0; transform: translateY(10px) scale(0.9); }
.bbl-leave-to     { opacity: 0; }

.mascot-glow {
    position: absolute;
    width: 180px; height: 180px;
    bottom: 12px; left: 50%;
    transform: translateX(-50%);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, transparent 70%);
    filter: blur(24px);
    animation: glowPulse 3.5s ease-in-out infinite;
    pointer-events: none;
}
@keyframes glowPulse {
    0%, 100% { opacity: 0.5; }
    50%       { opacity: 1; }
}
.mascot {
    position: relative;
    z-index: 2;
    width: clamp(140px, 16vw, 210px);
    height: auto;
    display: block;
    filter: drop-shadow(0 16px 32px rgba(0, 0, 0, 0.28));
    animation: charBob 3.5s ease-in-out infinite;
    transform-origin: bottom center;
}
@keyframes charBob {
    0%, 100% { transform: translateY(0) rotate(0); }
    45%       { transform: translateY(-10px) rotate(0.6deg); }
    70%       { transform: translateY(-5px) rotate(-0.4deg); }
}
.mascot-shadow {
    width: 68%; height: 14px;
    border-radius: 50%;
    background: radial-gradient(ellipse, rgba(0, 0, 0, 0.28) 0%, transparent 70%);
    position: relative; z-index: 1;
    margin: -2px auto 0;
}

/* ─────────── RIGHT PANEL ─────────── */
.right-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 48px;
    transform: translateY(20px);
    opacity: 0;
    animation: slideInRight 0.55s 0.18s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
@keyframes slideInRight {
    to { transform: translateY(0); opacity: 1; }
}

/* ─────────── FORM CARD ─────────── */
.form-card {
    width: 100%;
    max-width: 420px;
    background: rgba(255, 255, 255, 0.97);
    border-radius: 28px;
    padding: 44px 40px 40px;
    box-shadow:
        0 20px 60px rgba(0, 0, 0, 0.22),
        0 2px 0 rgba(255, 255, 255, 0.8) inset;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.form-card::before {
    content: "";
    position: absolute;
    top: 0; left: 50%;
    transform: translateX(-50%);
    width: 60%; height: 3px;
    background: linear-gradient(90deg, transparent, #bfdbfe 30%, #3b82f6 50%, #bfdbfe 70%, transparent);
    border-radius: 0 0 4px 4px;
}

/* Avatar */
.fc-avatar {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #eff6ff;
    border: 3px solid #bfdbfe;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 20px;
    box-shadow: 0 4px 18px rgba(37, 99, 235, 0.14);
    animation: avatarPop 2.8s ease-in-out infinite;
}
@keyframes avatarPop {
    0%, 100% { transform: scale(1); }
    50%       { transform: scale(1.07); }
}
.fc-title {
    font-family: "Righteous", cursive;
    font-size: clamp(20px, 2.4vw, 26px);
    color: #111827;
    text-align: center;
    margin-bottom: 8px;
    line-height: 1.2;
}
.fc-sub {
    font-size: 13.5px;
    font-weight: 700;
    color: #6b7280;
    text-align: center;
    line-height: 1.6;
    margin-bottom: 28px;
    max-width: 290px;
}

/* Field */
.field-block { width: 100%; margin-bottom: 20px; }
.f-label {
    display: block;
    font-size: 12px; font-weight: 900;
    color: #374151;
    margin-bottom: 7px;
    letter-spacing: 0.2px;
}
.f-row {
    display: flex; align-items: center;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    background: #f8faff;
    overflow: hidden;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.f-row--on  { border-color: #3b82f6; background: #eff6ff; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1); }
.f-row--err { border-color: #fca5a5; background: #fef2f2; }
.f-ico {
    flex-shrink: 0; width: 48px;
    color: #9ca3af;
    display: flex; align-items: center; justify-content: center;
    transition: color 0.2s;
}
.f-row--on  .f-ico { color: #3b82f6; }
.f-row--err .f-ico { color: #f87171; }
.f-input {
    flex: 1; height: 52px;
    padding: 0 16px 0 0;
    border: none; outline: none;
    background: transparent;
    font-family: "Nunito", sans-serif;
    font-size: 15px; font-weight: 700;
    color: #111827;
}
.f-input::placeholder { color: #9ca3af; font-weight: 600; }
.f-err { font-size: 12px; font-weight: 800; color: #ef4444; margin-top: 6px; padding-left: 4px; }
.err-enter-active { transition: opacity 0.2s, transform 0.22s; }
.err-leave-active { transition: opacity 0.15s; }
.err-enter-from   { opacity: 0; transform: translateY(-4px); }
.err-leave-to     { opacity: 0; }

/* CTA button */
.cta-btn {
    width: 100%; height: 52px;
    display: flex; align-items: center; justify-content: center;
    gap: 9px;
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none; border-radius: 14px;
    font-family: "Righteous", cursive;
    font-size: 16px; color: #fff;
    cursor: pointer;
    box-shadow: 0 5px 0 #1e3a8a, 0 10px 28px rgba(37, 99, 235, 0.38);
    transition: transform 0.15s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.15s;
    position: relative; overflow: hidden;
    margin-bottom: 16px;
}
.cta-btn::before {
    content: "";
    position: absolute; inset: 0;
    background: linear-gradient(to bottom, rgba(255, 255, 255, 0.12) 0%, transparent 55%);
    pointer-events: none;
}
.cta-btn:hover:not(:disabled)  { transform: translateY(-3px); box-shadow: 0 8px 0 #1e3a8a, 0 16px 36px rgba(37, 99, 235, 0.42); }
.cta-btn:active:not(:disabled) { transform: translateY(2px);  box-shadow: 0 2px 0 #1e3a8a, 0 4px 14px rgba(37, 99, 235, 0.28); }
.cta-btn:disabled  { opacity: 0.6; cursor: not-allowed; }
.cta--shake   { animation: ctaShake 0.5s ease; }
.cta--loading { opacity: 0.8; cursor: wait; }
@keyframes ctaShake {
    0%, 100% { transform: translateX(0); }
    20%       { transform: translateX(-8px); }
    40%       { transform: translateX(8px); }
    60%       { transform: translateX(-5px); }
    80%       { transform: translateX(5px); }
}

.spinner {
    width: 19px; height: 19px; flex-shrink: 0;
    border: 3px solid rgba(255, 255, 255, 0.35);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

.fc-hint { font-size: 12px; font-weight: 700; color: #9ca3af; text-align: center; }

/* ─────────── RESPONSIVE ≤ 820px ─────────── */
@media (max-width: 820px) {
    .split { flex-direction: column; }

    .left-panel { display: none; }

    .right-panel {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 20px;
        margin-top: 50px;
    }

    .form-card {
        max-width: 420px;
        width: 100%;
        padding: 32px 28px;
    }

    .fc-avatar  { width: 60px; height: 60px; margin-bottom: 16px; }
    .fc-title   { font-size: 22px; }
    .fc-sub     { font-size: 12.5px; margin-bottom: 22px; }
    .f-input    { height: 48px; font-size: 14px; }
    .f-ico      { width: 44px; }
    .cta-btn    { height: 50px; font-size: 15px; }
}

/* ─────────── RESPONSIVE ≤ 480px ─────────── */
@media (max-width: 480px) {
    .topbar     { height: 52px; padding: 0 14px; }
    .tbtn-lbl   { display: none; }
    .tbtn       { padding: 7px 10px; }
    .brand-name { font-size: 16px; }
    .brand-dot  { width: 25px; height: 25px; }

    .right-panel { padding: 24px 14px; margin-top: 40px; }

    .form-card  { padding: 28px 20px 24px; border-radius: 22px; }
    .fc-avatar  { width: 54px; height: 54px; margin-bottom: 13px; }
    .fc-title   { font-size: 20px; }
    .fc-sub     { font-size: 12px; margin-bottom: 18px; }
    .f-row      { border-radius: 12px; }
    .f-input    { height: 46px; font-size: 14px; }
    .cta-btn    { height: 48px; font-size: 15px; border-radius: 12px; margin-bottom: 12px; }
    .fc-hint    { font-size: 11.5px; }
}
</style>
