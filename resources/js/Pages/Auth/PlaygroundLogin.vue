<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { Eye, EyeOff, Zap, LogIn, User, Lock } from "lucide-vue-next";

const ready = ref(false);
const shakeBtn = ref(false);
const showPassword = ref(false);
const musicOn = ref(false);
const audioRef = ref(null);

// ── Speech bubble ─────────────────────────────────────────────────
const BUBBLE_LINES = [
    "Hai! Aku Geni 👋",
    "Udah siap belajar? 😎",
    "Kamu pasti bisa! 💪",
    "Yuk, masuk dulu~",
    "Siap jadi jagoan Geniuss? 🚀",
    "Ayo kita berkompetisi 🏆",
    "Halo, sobat Geniuss! 😊",
];
const bubbleText = ref(BUBBLE_LINES[0]);
const bubbleVisible = ref(true);
const bubbleIndex = ref(0);
let bubbleTimer = null;

function startBubble() {
    bubbleTimer = setInterval(() => {
        bubbleVisible.value = false;
        setTimeout(() => {
            bubbleIndex.value = (bubbleIndex.value + 1) % BUBBLE_LINES.length;
            bubbleText.value = BUBBLE_LINES[bubbleIndex.value];
            bubbleVisible.value = true;
        }, 380);
    }, 2800);
}

const form = useForm({ nama: "", password: "" });
const loginUrl = route("login.admin");
const localErrors = ref({ nama: "", password: "" });

const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    setTimeout(() => startBubble(), 1200);
    document.addEventListener("visibilitychange", handleVisibility);
});

onUnmounted(() => {
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
    if (bubbleTimer) {
        clearInterval(bubbleTimer);
        bubbleTimer = null;
    }
});

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
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

function validateNama() {
    if (!form.nama.trim()) {
        localErrors.value.nama = "Nama siswa wajib diisi!";
        return false;
    }

    localErrors.value.nama = "";
    return true;
}
function validatePassword() {
    if (!form.password) {
        localErrors.value.password = "Password wajib diisi!";
        return false;
    }
    if (form.password.length < 6) {
        localErrors.value.password = "Password minimal 6 karakter";
        return false;
    }
    localErrors.value.password = "";
    return true;
}
const errors = {
    get nama() {
        return localErrors.value.nama || form.errors.nama || "";
    },
    get password() {
        return localErrors.value.password || form.errors.password || "";
    },
};
function handleLogin() {
    if (!validateNama() | !validatePassword()) {
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
</script>

<template>
    <div style="display: none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap"
            rel="stylesheet"
        />
    </div>

    <div class="pg">
        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div class="tw flex items-center justify-between">
                <div class="brand">
                    <div class="brand-ico">
                        <Zap
                            :size="14"
                            color="#fff"
                            fill="white"
                            :stroke-width="2.5"
                        />
                    </div>
                    <span class="brand-nm">Geniuss</span>
                </div>
                <div>
                    <a :href="loginUrl" class="btn btn--secondary"
                        >Kembali ke Login</a
                    >
                </div>
            </div>
        </header>

        <!-- ══ HERO SCENE ══ -->
        <div class="scene" :class="{ ready }">
            <div class="layout">
                <!-- ══ LEFT COL ══ -->
                <div class="left-col" :class="{ show: ready }">
                    <div class="tagline-row">
                        <div class="tagline-badge">
                            <Zap
                                :size="11"
                                color="#fff"
                                fill="white"
                                :stroke-width="2.5"
                            />
                            <span>Selamat Datang!</span>
                        </div>
                    </div>

                    <h1 class="hero-title">
                        Belajar jadi<br />
                        <span class="ht-accent">Lebih Seru!</span>
                    </h1>
                    <p class="hero-sub">
                        Masuk dan mulai petualangan belajarmu<br />bersama
                        Geniuss hari ini.
                    </p>

                    <!-- Mascot + Speech Bubble -->
                    <div class="mascot-wrap">
                        <!-- Speech Bubble -->
                        <Transition name="bubble">
                            <div v-if="bubbleVisible" class="speech-bubble">
                                <span class="bubble-text">{{
                                    bubbleText
                                }}</span>
                            </div>
                        </Transition>

                        <div class="mascot-glow"></div>
                        <img
                            src="/images/templates/pose_keren.png"
                            alt="Maskot Geniuss"
                            class="mascot-img"
                        />
                    </div>
                </div>

                <!-- ══ RIGHT COL ══ -->
                <div class="right-col" :class="{ show: ready }">
                    <div class="card">
                        <div class="card-top-bar"></div>

                        <div class="card-inner">
                            <div class="logo-row">
                                <div class="logo-ico">
                                    <Zap
                                        :size="19"
                                        color="#fff"
                                        fill="white"
                                        :stroke-width="2.4"
                                    />
                                </div>
                                <div>
                                    <div class="logo-name">
                                        Geni<span class="la">uss</span>
                                    </div>
                                    <div class="logo-sub">
                                        Web Education Platform
                                    </div>
                                </div>
                            </div>

                            <div class="divider-light"></div>

                            <h2 class="card-title">Masuk ke Playground</h2>
                            <p class="card-hint">
                                Masukkan nama &amp; password dari gurumu
                            </p>

                            <div class="form-stack">
                                <!-- Nama -->
                                <div class="fw">
                                    <label class="flabel"
                                        >Nama Siswa
                                        <span class="req">*</span></label
                                    >
                                    <div class="if-wrap">
                                        <User
                                            :size="16"
                                            :stroke-width="2.2"
                                            class="if-icon"
                                        />
                                        <input
                                            v-model="form.nama"
                                            type="text"
                                            placeholder="Tulis nama lengkapmu"
                                            maxlength="60"
                                            autocomplete="off"
                                            :class="[
                                                'if-input',
                                                errors.nama
                                                    ? 'if-err'
                                                    : 'if-blue',
                                            ]"
                                            @focus="
                                                localErrors.nama = '';
                                                form.clearErrors('nama');
                                            "
                                            @blur="validateNama()"
                                            @input="
                                                localErrors.nama = '';
                                                form.clearErrors('nama');
                                            "
                                            @keyup.enter="handleLogin"
                                        />
                                    </div>
                                    <p v-if="errors.nama" class="errmsg">
                                        {{ errors.nama }}
                                    </p>
                                </div>

                                <!-- Password -->
                                <div class="fw">
                                    <label class="flabel"
                                        >Password
                                        <span class="req">*</span></label
                                    >
                                    <div class="if-wrap">
                                        <Lock
                                            :size="16"
                                            :stroke-width="2.2"
                                            class="if-icon"
                                        />
                                        <input
                                            v-model="form.password"
                                            :type="
                                                showPassword
                                                    ? 'text'
                                                    : 'password'
                                            "
                                            placeholder="Masukkan password"
                                            maxlength="60"
                                            autocomplete="off"
                                            :class="[
                                                'if-input',
                                                'if-input--pr',
                                                errors.password
                                                    ? 'if-err'
                                                    : 'if-blue',
                                            ]"
                                            @focus="
                                                localErrors.password = '';
                                                form.clearErrors('password');
                                            "
                                            @blur="validatePassword()"
                                            @input="
                                                localErrors.password = '';
                                                form.clearErrors('password');
                                            "
                                            @keyup.enter="handleLogin"
                                        />
                                        <button
                                            type="button"
                                            class="if-eye"
                                            @click="
                                                showPassword = !showPassword
                                            "
                                        >
                                            <Eye
                                                v-if="showPassword"
                                                :size="18"
                                                :stroke-width="2.2"
                                            />
                                            <EyeOff
                                                v-else
                                                :size="18"
                                                :stroke-width="2.2"
                                            />
                                        </button>
                                    </div>
                                    <p v-if="errors.password" class="errmsg">
                                        {{ errors.password }}
                                    </p>
                                </div>

                                <!-- Submit -->
                                <button
                                    class="btn"
                                    :class="{
                                        active: form.nama && form.password,
                                        loading: form.processing,
                                        shake: shakeBtn,
                                    }"
                                    :disabled="form.processing"
                                    @click="handleLogin"
                                >
                                    <template v-if="!form.processing">
                                        <LogIn :size="17" :stroke-width="2.5" />
                                        Masuk Sekarang
                                    </template>
                                    <template v-else>
                                        <span class="spinner"></span>
                                        Memproses...
                                    </template>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ MUSIC FAB ══ -->
        <button
            class="music-fab"
            :class="{ on: musicOn }"
            @click="toggleMusic"
            :title="musicOn ? 'Matikan musik' : 'Nyalakan musik'"
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
            <span v-if="musicOn" class="fab-pulse"></span>
        </button>
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

/* ════════════════════════════════
   PAGE
════════════════════════════════ */
.pg {
    height: 100vh;
    max-height: 100vh;
    overflow: hidden;
    font-family: "Nunito", sans-serif;
    background: url("/images/templates/background.png") top center / cover
        no-repeat;
    display: flex;
    flex-direction: column;
}

/* ════════════════════════════════
   TOPBAR
════════════════════════════════ */
.topbar {
    flex-shrink: 0;
    height: 52px;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.35);
    padding: 0 32px;
    z-index: 20;
}
.tw {
    width: 100%;
}
.brand {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.brand-ico {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(140deg, #60a5fa, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(29, 78, 216, 0.4);
}
.brand-nm {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #fff;
    letter-spacing: -0.1px;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.18);
}

/* ════════════════════════════════
   SCENE + LAYOUT
════════════════════════════════ */
.scene {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px 40px;
    overflow: hidden;
}
.layout {
    width: 100%;
    max-width: 960px;
    display: grid;
    grid-template-columns: 1fr 420px;
    align-items: center;
    gap: 48px;
}

/* ════════════════════════════════
   LEFT COL
════════════════════════════════ */
.left-col {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    opacity: 0;
    transform: translateX(-32px);
    transition:
        opacity 0.65s ease,
        transform 0.65s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.left-col.show {
    opacity: 1;
    transform: none;
}

.tagline-row {
    margin-bottom: 14px;
}
.tagline-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(29, 78, 216, 0.28);
    border: 1.5px solid #aeb6e6(29, 78, 216, 0.28);
    border-radius: 50px;
    padding: 5px 14px;
    font-size: 11.5px;
    font-weight: 800;
    color: #fff;
    letter-spacing: 0.3px;
    backdrop-filter: blur(8px);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.15);
}

.hero-title {
    font-family: "Righteous", cursive;
    font-size: clamp(28px, 3.6vw, 42px);
    line-height: 1.18;
    color: #fff;
    text-shadow:
        0 2px 16px rgba(0, 0, 0, 0.2),
        0 1px 0 rgba(0, 0, 0, 0.08);
    margin-bottom: 12px;
}
.ht-accent {
    color: #4570e6;
    text-shadow:
        0 2px 20px rgba(191, 219, 254, 0.5),
        0 1px 0 rgba(0, 0, 0, 0.1);
}
.hero-sub {
    font-size: clamp(12.5px, 1.5vw, 14.5px);
    font-weight: 700;
    color: rgba(255, 255, 255, 0.88);
    line-height: 1.65;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.15);
    margin-bottom: 20px;
}

/* ════════════════════════════════
   MASCOT + SPEECH BUBBLE
════════════════════════════════ */
.mascot-wrap {
    position: relative;
    align-self: flex-start;
    margin-left: 20px;
}

/* ── Speech Bubble ── */
.speech-bubble {
    position: absolute;
    /* naik ke atas gambar maskot */
    bottom: calc(100% - 24px);
    left: 68%;
    /* border biru muda, sudut bulat khas balon kata */
    background: #fff;
    border: 2.5px solid #93c5fd;
    border-radius: 20px;
    padding: 10px 16px;
    min-width: 148px;
    max-width: 220px;
    white-space: nowrap;
    box-shadow:
        0 8px 28px rgba(29, 78, 216, 0.16),
        0 2px 0 rgba(29, 78, 216, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    z-index: 10;
    /* sedikit melayang */
    animation: bubble-float 3.5s ease-in-out infinite;
}

/* Ekor balon — mengarah ke kiri bawah (ke arah kepala maskot) */
.speech-bubble::before,
.speech-bubble::after {
    content: "";
    position: absolute;
    bottom: -14px;
    left: 18px;
    width: 0;
    height: 0;
}
.speech-bubble::before {
    border-left: 11px solid transparent;
    border-right: 7px solid transparent;
    border-top: 14px solid #93c5fd;
    bottom: -16px;
    left: 17px;
}
.speech-bubble::after {
    border-left: 9px solid transparent;
    border-right: 6px solid transparent;
    border-top: 13px solid #fff;
    bottom: -13px;
    left: 18px;
}

.bubble-text {
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #1e3a8a;
    display: block;
}

/* Balon mengambang naik-turun perlahan */
@keyframes bubble-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

/* ── Bubble Vue Transition ── */
.bubble-enter-active {
    transition:
        opacity 0.35s ease,
        transform 0.38s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bubble-leave-active {
    transition:
        opacity 0.26s ease,
        transform 0.22s ease;
}
.bubble-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.88);
}
.bubble-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.94);
}

/* Mascot */
.mascot-glow {
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    width: 180px;
    height: 40px;
    background: radial-gradient(
        ellipse,
        rgba(0, 0, 0, 0.18) 0%,
        transparent 70%
    );
    border-radius: 50%;
    pointer-events: none;
}
.mascot-img {
    position: relative;
    z-index: 2;
    width: clamp(180px, 22vw, 260px);
    height: auto;
    display: block;
    filter: drop-shadow(0 12px 32px rgba(0, 0, 0, 0.22));
    animation: mascot-bob 3.5s ease-in-out infinite;
    transform-origin: bottom center;
}
@keyframes mascot-bob {
    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }
    45% {
        transform: translateY(-10px) rotate(0.6deg);
    }
    70% {
        transform: translateY(-6px) rotate(-0.4deg);
    }
}

/* ════════════════════════════════
   RIGHT COL — CARD
════════════════════════════════ */
.right-col {
    opacity: 0;
    transform: translateY(28px);
    transition:
        opacity 0.6s 0.15s ease,
        transform 0.6s 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.right-col.show {
    opacity: 1;
    transform: none;
}

.card {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(24px) saturate(1.5);
    -webkit-backdrop-filter: blur(24px) saturate(1.5);
    border-radius: 24px;
    overflow: hidden;
    border: 1.5px solid rgba(255, 255, 255, 0.8);
    box-shadow:
        0 24px 64px rgba(0, 0, 0, 0.15),
        0 4px 0 rgba(29, 78, 216, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 1);
}
.card-top-bar {
    height: 5px;
    background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 40%, #1d4ed8 100%);
}
.card-inner {
    padding: 32px 36px 36px;
}

.logo-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}
.logo-ico {
    width: 44px;
    height: 44px;
    border-radius: 13px;
    flex-shrink: 0;
    background: linear-gradient(140deg, #60a5fa, #1d4ed8);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.4);
}
.logo-name {
    font-family: "Righteous", cursive;
    font-size: 22px;
    color: #1e3a8a;
    letter-spacing: -0.2px;
    line-height: 1.1;
}
.la {
    color: #1d4ed8;
}
.logo-sub {
    font-size: 10px;
    font-weight: 800;
    color: #6b7280;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 2px;
}

.divider-light {
    height: 1px;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(29, 78, 216, 0.15),
        transparent
    );
    margin-bottom: 20px;
}
.card-title {
    font-family: "Righteous", cursive;
    font-size: 20px;
    color: #1e3a8a;
    margin-bottom: 4px;
}
.card-hint {
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
    margin-bottom: 22px;
    line-height: 1.6;
}

/* ── Form ── */
.form-stack {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.fw {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 14px;
}
.flabel {
    font-size: 11.5px;
    font-weight: 800;
    color: #374151;
    letter-spacing: 0.2px;
    padding-left: 2px;
}
.req {
    color: #ef4444;
}

.if-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.if-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    pointer-events: none;
    flex-shrink: 0;
    transition: color 0.15s;
}
.if-input {
    width: 100%;
    padding: 12px 16px 12px 42px;
    border-width: 4px;
    border-style: solid;
    border-radius: 16px;
    outline: none;
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #111827;
    background: #fff;
    transition:
        border-color 0.2s,
        background 0.2s,
        box-shadow 0.2s;
}
.if-input--pr {
    padding-right: 46px;
}
.if-input::placeholder {
    color: #9ca3af;
    font-weight: 600;
}

.if-blue {
    border-color: #bfdbfe;
}
.if-blue:hover {
    border-color: #93c5fd;
}
.if-blue:focus {
    border-color: #3b82f6;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.if-wrap:focus-within .if-icon {
    color: #3b82f6;
}

.if-err {
    border-color: #fca5a5;
    background: #fef2f2;
}
.if-err:focus {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.if-eye {
    position: absolute;
    right: 13px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    color: #9ca3af;
    transition: color 0.15s;
}
.if-eye:hover {
    color: #3b82f6;
}

.errmsg {
    font-size: 11.5px;
    font-weight: 700;
    color: #ef4444;
    padding-left: 2px;
    animation: errin 0.18s ease;
}
@keyframes errin {
    from {
        opacity: 0;
        transform: translateY(-3px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

/* ── Submit Button ── */
.btn {
    width: 100%;
    height: 50px;
    border: none;
    border-radius: 12px;
    font-family: "Righteous", cursive;
    font-size: 15.5px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, #1d4ed8, #1d4ed8);
    color: #fff;
    box-shadow:
        0 4px 16px rgba(29, 78, 216, 0.35),
        0 2px 0 rgba(14, 57, 165, 0.2);
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
    margin-top: 4px;
}
.btn::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2) 0%,
        transparent 50%
    );
    pointer-events: none;
}
.btn:hover:not(:disabled):not(.loading) {
    transform: translateY(-2px) scale(1.015);
    box-shadow:
        0 8px 22px rgba(29, 78, 216, 0.45),
        0 2px 0 rgba(14, 57, 165, 0.2);
}
.btn:active:not(:disabled) {
    transform: scale(0.98);
}
.btn.active {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    animation: pulse-blue 2.5s ease-in-out infinite;
}
.btn.loading {
    opacity: 0.8;
    cursor: wait;
}
.btn.shake {
    animation: shake 0.5s ease;
}
.btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* Secondary/alternate button for topbar back link */
.btn--secondary {
    height: auto;
    padding: 8px 12px;
    min-height: 36px;
    background: linear-gradient(135deg, #6b7280, #374151);
    color: #fff;
    box-shadow: 0 6px 18px rgba(55, 65, 81, 0.25);
    font-size: 13px;
    border-radius: 10px;
}
.btn--secondary:hover:not(:disabled) {
    transform: translateY(-2px) scale(1.02);
    box-shadow: 0 10px 28px rgba(55, 65, 81, 0.35);
}
.btn--secondary:active:not(:disabled) {
    transform: scale(0.98);
}
@keyframes pulse-blue {
    0%,
    100% {
        box-shadow: 0 4px 16px rgba(29, 78, 216, 0.35);
    }
    50% {
        box-shadow:
            0 6px 24px rgba(29, 78, 216, 0.6),
            0 0 0 7px rgba(29, 78, 216, 0.1);
    }
}
@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    20% {
        transform: translateX(-8px);
    }
    40% {
        transform: translateX(8px);
    }
    60% {
        transform: translateX(-5px);
    }
    80% {
        transform: translateX(5px);
    }
}
.spinner {
    display: inline-block;
    width: 18px;
    height: 18px;
    border: 2.5px solid rgba(255, 255, 255, 0.4);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ════════════════════════════════
   MUSIC FAB
════════════════════════════════ */
.music-fab {
    position: fixed;
    bottom: 22px;
    left: 22px;
    z-index: 301;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(10px);
    color: #1d4ed8;
    box-shadow: 0 3px 14px rgba(0, 0, 0, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.music-fab:hover {
    transform: scale(1.1);
    background: #fff;
}
.music-fab.on {
    background: linear-gradient(135deg, #60a5fa, #1d4ed8);
    color: #fff;
    box-shadow: 0 5px 18px rgba(29, 78, 216, 0.45);
}
.music-fab svg {
    width: 19px;
    height: 19px;
}
.fab-pulse {
    position: absolute;
    inset: -5px;
    border-radius: 50%;
    border: 2px solid rgba(29, 78, 216, 0.4);
    animation: fab-ring 2s ease-out infinite;
    pointer-events: none;
}
@keyframes fab-ring {
    0% {
        transform: scale(1);
        opacity: 0.8;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

/* ════════════════════════════════
   RESPONSIVE
════════════════════════════════ */
@media (max-width: 860px) {
    .pg {
        height: auto;
        min-height: 100vh;
        overflow: auto;
    }
    .scene {
        padding: 32px 20px 40px;
        align-items: flex-start;
    }
    .layout {
        grid-template-columns: 1fr;
        gap: 0;
        max-width: 440px;
    }
    .left-col {
        display: none;
    }
}
@media (max-width: 480px) {
    .card-inner {
        padding: 26px 22px 30px;
    }
    .card {
        border-radius: 20px;
    }
    .hero-title {
        font-size: 26px;
    }
}
</style>
