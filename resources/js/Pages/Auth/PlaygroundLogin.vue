<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { useMusic } from "@/Composable/useMusic";

const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();

const props = defineProps({
    backsound: { type: String, default: null },
    background: { type: String, default: null },
    classes: { type: Array, default: () => [] },
});

const ready = ref(false);
const mascotBounce = ref(false);

/* ── State Partikel Latar Belakang ── */
const particles = ref([]);

function generateParticles() {
    const count = 25; // Jumlah partikel melayang
    const generated = [];
    for (let i = 0; i < count; i++) {
        generated.push({
            id: i,
            style: {
                left: `${Math.random() * 100}%`,
                top: `${Math.random() * 100}%`,
                width: `${Math.random() * 8 + 6}px`,
                height: `${Math.random() * 8 + 6}px`,
                animationDelay: `${Math.random() * 5}s`,
                animationDuration: `${Math.random() * 10 + 10}s`,
                opacity: Math.random() * 0.5 + 0.2,
            },
        });
    }
    particles.value = generated;
}

/* ── Form & Validation ── */
const form = useForm({ nama: "" });
const loginUrl = route("login.admin");
const localErrors = ref({ nama: "" });
const inputRef = ref(null);

const errors = {
    get nama() {
        return localErrors.value.nama || form.errors.nama || "";
    },
};

function validateForm() {
    let isValid = true;
    if (!form.nama.trim()) {
        localErrors.value.nama = "Username wajib diisi!";
        isValid = false;
    } else {
        localErrors.value.nama = "";
    }
    return isValid;
}

function handleLogin() {
    if (!validateForm()) {
        return;
    }
    form.post(route("playground.authenticate"), {
        onSuccess: () => router.visit(route("playground.index")),
        onError: () => {},
    });
}

function goBack() {
    router.visit(loginUrl);
}

onMounted(() => {
    // Generate efek partikel hidup
    generateParticles();

    // Page ready transition
    setTimeout(() => {
        ready.value = true;
        inputRef.value?.focus();
    }, 120);

    // Mascot bounce on load
    setTimeout(() => {
        mascotBounce.value = true;
        setTimeout(() => {
            mascotBounce.value = false;
        }, 1200);
    }, 800);

    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => initAutoMusic(null), 500);
});

onUnmounted(() => {
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});
</script>

<template>
    <div style="display: none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap"
            rel="stylesheet"
        />
    </div>

    <div class="page" :class="{ 'page--on': ready }">
        <!-- Elemen Partikel Latar Belakang -->
        <div class="particle-container">
            <div
                v-for="p in particles"
                :key="p.id"
                class="particle"
                :style="p.style"
            ></div>
        </div>

        <nav class="navbar">
            <button
                @click="goBack"
                class="nav-logo"
                title="Masuk sebagai Admin"
                style="
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    text-align: left;
                "
            >
                <div class="logo-icon-wrap">
                    <img v-if="$page.props.global_settings?.platform_logo" :src="$page.props.global_settings?.platform_logo" alt="Logo" style="width: 32px; height: 32px; object-fit: contain; border-radius: 10px;" />
                    <svg v-else width="32" height="32" viewBox="0 0 32 32" fill="none">
                        <rect width="32" height="32" rx="10" fill="#fff" />
                        <path
                            d="M10 16C10 12.686 12.686 10 16 10C19.314 10 22 12.686 22 16C22 19.314 19.314 22 16 22"
                            stroke="#38BDF8"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <circle cx="16" cy="16" r="3" fill="#38BDF8" />
                    </svg>
                </div>
                <span class="logo-name">{{
                    $page.props.global_settings?.platform_name || "Geniuss"
                }}</span>
            </button>

            <button
                class="music-btn"
                :class="{ 'music-btn--on': musicOn }"
                @click="toggleMusic(null)"
                :aria-label="musicOn ? 'Matikan musik' : 'Nyalakan musik'"
            >
                <svg
                    v-if="musicOn"
                    class="music-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                >
                    <path d="M9 18V5l12-2v13" />
                    <circle cx="6" cy="18" r="3" />
                    <circle cx="18" cy="16" r="3" />
                </svg>
                <svg
                    v-else
                    class="music-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.2"
                    stroke-linecap="round"
                >
                    <line x1="2" y1="2" x2="22" y2="22" />
                    <path d="M9 9v9" />
                    <circle cx="6" cy="18" r="3" />
                    <path d="M21 15.34V5L13 6.5" />
                </svg>
                <span class="music-label">{{
                    musicOn ? "Musik" : "Musik"
                }}</span>
                <svg
                    class="chevron-icon"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.8"
                    stroke-linecap="round"
                >
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
        </nav>

        <main class="main">
            <!-- Sisi Kiri Hero (Akan otomatis hilang total di mobile) -->
            <div class="hero-side">
                <div class="mascot-wrap" :class="{ bounce: mascotBounce }">
                    <img
                        :src="
                            $page.props.global_settings?.platform_mascot ||
                            '/images/templates/pose_keren.png'
                        "
                        alt="Maskot Geni"
                        class="mascot-img"
                        draggable="false"
                    />
                </div>

                <div class="hero-tagline">
                    <h1 class="hero-title">{{ $page.props.global_settings?.platform_subtitle || 'Cara seru belajar interaktif!' }}</h1>
                    <p class="hero-sub">
                        Quiz, modul, dan materi lengkap untuk kamu
                    </p>
                </div>
            </div>

            <div class="form-side">
                <div class="form-card">
                    <div class="card-header">
                        <h2 class="card-title">Masuk ke Playground</h2>
                        <p class="card-sub">
                            Masukkan username kamu untuk mulai belajar
                        </p>
                    </div>

                    <Transition name="shake">
                        <div v-if="errors.nama" class="error-box">
                            <svg
                                width="15"
                                height="15"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                            {{ errors.nama }}
                        </div>
                    </Transition>

                    <div
                        class="field-group"
                        :class="{ 'field--error': !!errors.nama }"
                    >
                        <label class="field-label" for="pg-user"
                            >Username</label
                        >
                        <div class="input-wrap">
                            <svg
                                class="input-icon"
                                width="17"
                                height="17"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2.2"
                                stroke-linecap="round"
                            >
                                <path
                                    d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"
                                />
                                <circle cx="12" cy="7" r="4" />
                            </svg>
                            <input
                                id="pg-user"
                                ref="inputRef"
                                v-model="form.nama"
                                type="text"
                                class="field-input"
                                placeholder="Ketik username kamu..."
                                autocomplete="off"
                                maxlength="60"
                                @focus="
                                    localErrors.nama = '';
                                    form.clearErrors('nama');
                                "
                                @blur="validateForm"
                                @input="
                                    localErrors.nama = '';
                                    form.clearErrors('nama');
                                "
                                @keyup.enter="handleLogin"
                            />
                        </div>
                    </div>

                    <button
                        class="btn-masuk"
                        :disabled="form.processing || !form.nama.trim()"
                        @click.prevent="handleLogin"
                    >
                        <span v-if="!form.processing">MASUK</span>
                        <span v-else class="loading-dots">
                            <span></span><span></span><span></span>
                        </span>
                    </button>

                    <div class="divider">
                        <span class="divider-line"></span>
                        <span class="divider-text">INFO</span>
                        <span class="divider-line"></span>
                    </div>

                    <div class="info-card">
                        <svg
                            width="15"
                            height="15"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.2"
                            stroke-linecap="round"
                        >
                            <path
                                d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"
                            />
                            <path
                                d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"
                            />
                        </svg>
                        <p>
                            Username diberikan oleh gurumu. Hubungi guru jika
                            belum punya username.
                        </p>
                    </div>
                </div>
            </div>
        </main>
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

/* ══════════════════════════════
   PAGE — flat solid biru dengan sistem partikel
══════════════════════════════ */
.page {
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    font-family: "Nunito", sans-serif;
    background-color: #e0f2fe;
    opacity: 0;
    transition: opacity 0.4s ease;
    position: relative;
    overflow: hidden;
}
.page--on {
    opacity: 1;
}

/* ── Animasi Partikel di Latar Belakang ── */
.particle-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
    z-index: 1;
}
.particle {
    position: absolute;
    background-color: #4fafe2;
    border-radius: 50%;
    animation: floatParticle linear infinite;
}
@keyframes floatParticle {
    0% {
        transform: translateY(100vh) scale(0.5) rotate(0deg);
    }
    50% {
        transform: translateY(50vh) scale(1.2) rotate(180deg);
    }
    100% {
        transform: translateY(-10vh) scale(0.5) rotate(360deg);
    }
}

/* ══════════════════════════════
   NAVBAR (Dibuat Transparan & Absolute)
══════════════════════════════ */
.navbar {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 100;
    height: 72px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 40px;
    background: transparent; /* Full Transparan */
    border-bottom: none; /* Hilangkan border pengganggu */
}

/* Logo & Brand text */
.nav-logo {
    display: flex;
    align-items: center;
    gap: 10px;
    text-decoration: none;
    transition: transform 0.2s;
}
.nav-logo:hover {
    transform: scale(1.03);
}
.logo-icon-wrap {
    display: flex;
    align-items: center;
    flex-shrink: 0;
    filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.05));
}
.logo-name {
    font-size: 22px;
    font-weight: 900;
    color: #0369a1;
    letter-spacing: -0.3px;
    text-transform: uppercase;
    line-height: 1;
    text-shadow: 0 2px 4px rgba(255, 255, 255, 0.5);
}

/* Music button */
.music-btn {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 10px 16px;
    background: #fff;
    border: 2.5px solid #7dd3fc;
    border-radius: 14px;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #0369a1;
    cursor: pointer;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: 0 4px 0 0 #bae6fd;
    transition: all 0.1s ease;
    outline: none;
    flex-shrink: 0;
}
.music-btn:hover {
    border-color: #0ea5e9;
    transform: translateY(-2px);
    box-shadow: 0 6px 0 0 #bae6fd;
}
.music-btn:active {
    transform: translateY(2px);
    box-shadow: 0 2px 0 0 #bae6fd;
}
.music-btn--on {
    background: #0ea5e9;
    border-color: #0ea5e9;
    color: #fff;
    box-shadow: 0 4px 0 0 #0284c7;
}
.music-btn--on:hover {
    border-color: #0284c7;
    box-shadow: 0 6px 0 0 #0284c7;
}
.music-btn--on:active {
    box-shadow: 0 2px 0 0 #0284c7;
}
.music-icon {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}
.chevron-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
    opacity: 0.6;
}
.music-label {
    white-space: nowrap;
}

/* ══════════════════════════════
   MAIN LAYOUT
══════════════════════════════ */
.main {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 64px;
    padding: 100px 40px 56px; /* Ditambah padding atas agar tidak tertutup navbar absolute */
    max-width: 1080px;
    margin: 0 auto;
    width: 100%;
    position: relative;
    z-index: 2; /* Di atas partikel background */
}

/* ══════════════════════════════
   HERO SIDE — kiri
══════════════════════════════ */
.hero-side {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 24px;
}

.mascot-wrap {
    width: 100%;
    max-width: 340px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: float 4s ease-in-out infinite;
    filter: drop-shadow(0 20px 32px rgba(3, 105, 161, 0.25));
}
.mascot-img {
    width: 100%;
    height: auto;
    display: block;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-16px);
    }
}

.mascot-wrap.bounce {
    animation:
        mascot-bounce 1.1s cubic-bezier(0.34, 1.56, 0.64, 1),
        float 4s ease-in-out 1.2s infinite;
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

.hero-tagline {
    text-align: center;
}
.hero-title {
    font-size: clamp(24px, 2.8vw, 32px);
    font-weight: 900;
    color: #0c4a6e;
    line-height: 1.2;
    margin-bottom: 10px;
}
.hero-sub {
    font-size: 16px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.5;
}

/* ══════════════════════════════
   FORM SIDE — kanan
══════════════════════════════ */
.form-side {
    flex: 0 0 auto;
    width: 390px;
    max-width: 100%;
}

.form-card {
    background: #fff;
    border-radius: 24px;
    padding: 36px 32px 32px;
    border: 2.5px solid #bae6fd;
    box-shadow:
        0 6px 0 0 #bae6fd,
        0 12px 32px rgba(3, 105, 161, 0.12);
    display: flex;
    flex-direction: column;
    gap: 20px;
    transition: transform 0.3s ease;
}

/* Card header */
.card-header {
    margin-bottom: 2px;
}
.card-title {
    font-size: 24px;
    font-weight: 900;
    color: #0c4a6e;
    line-height: 1.2;
    margin-bottom: 6px;
}
.card-sub {
    font-size: 14px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.5;
}

/* Error Alert Box */
.error-box {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #fef2f2;
    border: 2px solid #fecaca;
    border-radius: 12px;
    padding: 11px 13px;
    font-size: 13px;
    font-weight: 800;
    color: #dc2626;
}
.shake-enter-active {
    animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97);
}
@keyframes shake {
    0%,
    100% {
        transform: translateX(0);
    }
    20% {
        transform: translateX(-7px);
    }
    40% {
        transform: translateX(7px);
    }
    60% {
        transform: translateX(-4px);
    }
    80% {
        transform: translateX(4px);
    }
}

/* Input Fields */
.field-group {
    display: flex;
    flex-direction: column;
    gap: 7px;
}
.field-label {
    font-size: 12px;
    font-weight: 900;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.input-wrap {
    display: flex;
    align-items: center;
    border: 2.5px solid #e5e7eb;
    border-radius: 16px;
    background: #f9fafb;
    transition:
        border-color 0.15s,
        box-shadow 0.15s,
        background 0.15s;
    overflow: hidden;
}
.input-wrap:focus-within {
    border-color: #38bdf8;
    background: #fff;
    box-shadow: 0 0 0 4px rgba(56, 189, 248, 0.15);
}
.field--error .input-wrap {
    border-color: #fca5a5;
    background: #fff5f5;
    box-shadow: none;
}
.input-icon {
    flex-shrink: 0;
    width: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    transition: color 0.15s;
}
.input-wrap:focus-within .input-icon {
    color: #38bdf8;
}
.field-input {
    flex: 1;
    border: none;
    outline: none;
    background: transparent;
    font-family: "Nunito", sans-serif;
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    padding: 14px 14px 14px 0;
}
.field-input::placeholder {
    color: #d1d5db;
    font-weight: 600;
}

/* Chunky Duolingo Button */
.btn-masuk {
    width: 100%;
    height: 54px;
    border: none;
    border-radius: 16px;
    background: #38bdf8;
    box-shadow: 0 5px 0 0 #0284c7;
    color: #fff;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    font-weight: 900;
    letter-spacing: 0.8px;
    text-transform: uppercase;
    cursor: pointer;
    position: relative;
    top: 0;
    transition:
        background 0.1s,
        top 0.08s,
        box-shadow 0.08s;
}
.btn-masuk:hover:not(:disabled) {
    background: #0ea5e9;
    box-shadow: 0 5px 0 0 #0369a1;
}
.btn-masuk:active:not(:disabled) {
    top: 5px;
    box-shadow: 0 0 0 0 transparent;
}
.btn-masuk:disabled {
    background: #bae6fd;
    box-shadow: 0 5px 0 0 #7dd3fc;
    color: rgba(255, 255, 255, 0.65);
    cursor: not-allowed;
    top: 0;
}

/* Loading dots */
.loading-dots {
    display: inline-flex;
    gap: 6px;
    align-items: center;
}
.loading-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.9);
    animation: dot-bounce 0.7s ease-in-out infinite;
}
.loading-dots span:nth-child(2) {
    animation-delay: 0.1s;
}
.loading-dots span:nth-child(3) {
    animation-delay: 0.2s;
}
@keyframes dot-bounce {
    0%,
    80%,
    100% {
        transform: scale(1);
        opacity: 0.8;
    }
    40% {
        transform: scale(1.3);
        opacity: 1;
    }
}

/* Divider */
.divider {
    display: flex;
    align-items: center;
    gap: 10px;
}
.divider-line {
    flex: 1;
    height: 1.5px;
    background: #e5e7eb;
    border-radius: 2px;
}
.divider-text {
    font-size: 10.5px;
    font-weight: 900;
    color: #9ca3af;
    letter-spacing: 1px;
}

/* Info card */
.info-card {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    padding: 14px 16px;
    background: #f0f9ff;
    border: 2px solid #bae6fd;
    border-radius: 14px;
    box-shadow: 0 4px 0 0 #bae6fd;
}
.info-card svg {
    flex-shrink: 0;
    margin-top: 2px;
    color: #0ea5e9;
}
.info-card p {
    font-size: 13px;
    font-weight: 700;
    color: #0369a1;
    line-height: 1.55;
}

/* ══════════════════════════════
   RESPONSIVE MEDIA QUERIES (DYNAMIC RESPONSIVE)
══════════════════════════════ */
@media (max-width: 820px) {
    /* Sembunyikan Maskot & Tagline Kata-kata Sepenuhnya di Mobile/Tablet Kecil */
    .hero-side {
        display: none !important;
    }

    .navbar {
        padding: 0 24px;
        height: 64px;
    }

    .main {
        flex-direction: column;
        gap: 0;
        padding: 90px 24px 40px;
        align-items: center;
        justify-content: center;
    }

    .form-side {
        width: 100%;
        max-width: 440px;
    }
}

@media (max-width: 480px) {
    .navbar {
        padding: 0 16px;
        height: 60px;
    }
    .logo-name {
        font-size: 18px;
    }
    .music-label {
        display: none; /* Sembunyikan teks musik di layar sangat kecil agar fit */
    }
    .music-btn {
        padding: 8px 12px;
        gap: 5px;
        border-radius: 12px;
    }
    .main {
        padding: 84px 16px 32px;
    }
    .form-card {
        padding: 28px 20px 24px;
        border-radius: 20px;
    }
    .card-title {
        font-size: 21px;
    }
}
</style>
