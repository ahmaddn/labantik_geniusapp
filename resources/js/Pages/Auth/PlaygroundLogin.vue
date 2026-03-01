<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";


// ── NO LAYOUT - Self-contained component ──

// ── State ──
const cardReady = ref(false);
const mascotBounce = ref(false);
const shakeBtn = ref(false);
const focusNama = ref(false);
const focusKelas = ref(false);
const musicOn = ref(false);

// ── Inertia Form (handles loading, errors, CSRF otomatis) ──
const form = useForm({
    nama: "",
    kelas: "",
});

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => {
        cardReady.value = true;
    }, 100);
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
    // Integrate audio here if needed
    // e.g. audioRef.value.play() / .pause()
}

// ── Validation lokal ──
const localErrors = ref({ nama: "", kelas: "" });

function validateNama() {
    if (!form.nama.trim()) {
        localErrors.value.nama = "Nama siswa wajib diisi!";
        return false;
    }
    if (form.nama.trim().length < 2) {
        localErrors.value.nama = "Nama minimal 2 karakter";
        return false;
    }
    localErrors.value.nama = "";
    return true;
}

function validateKelas() {
    if (!form.kelas) {
        localErrors.value.kelas = "Pilih kelas terlebih dahulu!";
        return false;
    }
    localErrors.value.kelas = "";
    return true;
}

// ── Computed errors (gabung local + inertia server errors) ──
const errors = {
    get nama() {
        return localErrors.value.nama || form.errors.nama || "";
    },
    get kelas() {
        return localErrors.value.kelas || form.errors.kelas || "";
    },
};

// ── Submit via Inertia ──
function handleMulai() {
    const namaOk = validateNama();
    const kelasOk = validateKelas();

    if (!namaOk || !kelasOk) {
        shakeBtn.value = true;
        setTimeout(() => {
            shakeBtn.value = false;
        }, 600);
        return;
    }

    mascotBounce.value = true;

    form.post(route("playground.start"), {
        onSuccess: () => {
            mascotBounce.value = false;
            // router.visit(route("playground.index"));
            // Inertia akan redirect otomatis sesuai response dari controller
        },
        onError: () => {
            mascotBounce.value = false;
            shakeBtn.value = true;
            setTimeout(() => {
                shakeBtn.value = false;
            }, 600);
        },
        onFinish: () => {
            mascotBounce.value = false;
        },
    });
}
</script>

<template>
    <!-- ░░ FULL PAGE WITH BACKGROUND IMAGE ░░ -->
    <div class="landing-page">
        <!-- Background Image -->
        <div class="bg-image"></div>

        <!-- ░░ MASCOT CHARACTER IMAGE ░░ -->
        <div class="mascot" :class="{ bounce: mascotBounce }">
            <img
                src="/images/templates/pose_keren.png"
                alt="Character Mascot"
                class="mascot-img"
            />
        </div>

        <!-- ░░ CARD ░░ -->
        <div class="start-card" :class="{ 'card-in': cardReady }">
            <!-- Logo -->
            <div class="logo-wrap">
                <div class="logo-title">
                    <span class="logo-geni">Geni</span
                    ><span class="logo-uss">us</span>
                </div>
                <div class="logo-sub">Web Education</div>
            </div>

            <!-- Form -->
            <div class="form-body">
                <!-- Input Nama Siswa -->
                <div
                    class="field"
                    :class="{
                        focused: focusNama,
                        filled: form.nama,
                        errored: errors.nama,
                    }"
                >
                    <input
                        v-model="form.nama"
                        type="text"
                        placeholder="Nama Siswa"
                        maxlength="60"
                        autocomplete="off"
                        @focus="
                            focusNama = true;
                            localErrors.nama = '';
                            form.clearErrors('nama');
                        "
                        @blur="
                            focusNama = false;
                            validateNama();
                        "
                        @input="
                            localErrors.nama = '';
                            form.clearErrors('nama');
                        "
                    />
                    <button
                        v-if="form.nama"
                        class="clear-btn"
                        @click="
                            form.nama = '';
                            localErrors.nama = '';
                            form.clearErrors('nama');
                        "
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </button>
                </div>
                <p v-if="errors.nama" class="err-text">{{ errors.nama }}</p>

                <!-- Select Kelas -->
                <div
                    class="field field-select"
                    :class="{
                        focused: focusKelas,
                        filled: form.kelas,
                        errored: errors.kelas,
                    }"
                >
                    <select
                        v-model="form.kelas"
                        @focus="
                            focusKelas = true;
                            localErrors.kelas = '';
                            form.clearErrors('kelas');
                        "
                        @blur="
                            focusKelas = false;
                            validateKelas();
                        "
                        @change="
                            localErrors.kelas = '';
                            form.clearErrors('kelas');
                        "
                    >
                        <option value="" disabled>Pilih Klass</option>
                        <optgroup label="Sekolah Dasar (SD)">
                            <option value="SD-1">Kelas 1 SD</option>
                            <option value="SD-2">Kelas 2 SD</option>
                            <option value="SD-3">Kelas 3 SD</option>
                            <option value="SD-4">Kelas 4 SD</option>
                            <option value="SD-5">Kelas 5 SD</option>
                            <option value="SD-6">Kelas 6 SD</option>
                        </optgroup>
                        <optgroup label="Sekolah Menengah Pertama (SMP)">
                            <option value="SMP-7">Kelas 7 SMP</option>
                            <option value="SMP-8">Kelas 8 SMP</option>
                            <option value="SMP-9">Kelas 9 SMP</option>
                        </optgroup>
                        <optgroup label="Sekolah Menengah Atas (SMA)">
                            <option value="SMA-10">Kelas 10 SMA</option>
                            <option value="SMA-11">Kelas 11 SMA</option>
                            <option value="SMA-12">Kelas 12 SMA</option>
                        </optgroup>
                    </select>
                    <svg
                        class="arrow-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <circle cx="12" cy="12" r="1" fill="currentColor" />
                    </svg>
                </div>
                <p v-if="errors.kelas" class="err-text">{{ errors.kelas }}</p>

                <button
                    class="btn-mulai"
                    :class="{
                        'btn-active': form.nama && form.kelas,
                        'btn-loading': form.processing,
                        'btn-shake': shakeBtn,
                    }"
                    @click="handleMulai"
                    :disabled="form.processing"
                >
                    <template v-if="!form.processing">
                        Mulai Petulangan
                    </template>
                    <template v-else>
                        <span class="spinner"></span>
                        Memulai...
                    </template>
                </button>
            </div>
        </div>

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

/* ─── CARD ─── */
.start-card {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.85);
    opacity: 0;
    transition: all 0.55s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 20;
    width: 520px;
    max-width: 90vw;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 32px;
    padding: 48px 52px;
    box-shadow:
        0 20px 60px rgba(0, 80, 150, 0.15),
        0 8px 20px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}
.start-card.card-in {
    opacity: 1;
    transform: translate(-50%, -50%) scale(1);
}
@media (max-width: 600px) {
    .start-card {
        width: 360px;
        padding: 36px 32px;
    }
}

/* ─── LOGO ─── */
.logo-wrap {
    text-align: center;
    margin-bottom: 36px;
}
.logo-title {
    font-family: "Baloo 2", cursive;
    font-size: 48px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.5px;
}
.logo-geni {
    color: #2c5f7c;
}
.logo-uss {
    color: #ff7b3d;
}
.logo-sub {
    font-family: "Nunito", sans-serif;
    font-size: 18px;
    font-weight: 600;
    color: #5a7c8f;
    letter-spacing: 0.3px;
    margin-top: 4px;
}
@media (max-width: 600px) {
    .logo-title {
        font-size: 40px;
    }
    .logo-sub {
        font-size: 16px;
    }
}

/* ─── FORM ─── */
.form-body {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.field {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 18px;
    padding: 0 20px;
    height: 62px;
    transition: all 0.25s;
    margin-bottom: 6px;
}
.field.focused {
    border-color: #ff7b3d;
    background: #fff9f5;
    box-shadow: 0 0 0 4px rgba(255, 123, 61, 0.12);
}
.field.filled {
    border-color: #5aaa2e;
    background: #f4fff0;
}
.field.errored {
    border-color: #ef4444;
    background: #fff5f5;
}

.field input,
.field select {
    flex: 1;
    border: none;
    background: transparent;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    outline: none;
    min-width: 0;
}
.field input::placeholder {
    color: #9ca3af;
    font-weight: 500;
}
.field input:disabled {
    cursor: not-allowed;
}
.field select {
    cursor: pointer;
    appearance: none;
    color: #1f2937;
}
.field select option {
    color: #1f2937;
    font-weight: 500;
}
.field select:invalid,
.field select option[value=""] {
    color: #9ca3af;
}

.field-select {
    position: relative;
}
.arrow-icon {
    width: 12px;
    height: 12px;
    color: #9ca3af;
    flex-shrink: 0;
    pointer-events: none;
    transition:
        transform 0.2s,
        color 0.2s;
}
.field.focused .arrow-icon {
    color: #ff7b3d;
}

.clear-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    color: #9ca3af;
    transition: color 0.2s;
}
.clear-btn:hover {
    color: #ef4444;
}
.clear-btn svg {
    width: 16px;
    height: 16px;
}

.err-text {
    font-size: 13px;
    font-weight: 600;
    color: #ef4444;
    padding-left: 6px;
    margin-top: -4px;
    margin-bottom: 6px;
    animation: err-in 0.2s ease;
}
@keyframes err-in {
    from {
        opacity: 0;
        transform: translateY(-4px);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

/* ─── BUTTON ─── */
.btn-mulai {
    margin-top: 18px;
    width: 100%;
    height: 60px;
    border: none;
    border-radius: 30px;
    font-family: "Baloo 2", cursive;
    font-size: 20px;
    font-weight: 800;
    letter-spacing: 0.3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #ff8a4d 0%, #ff9f5a 100%);
    color: white;
    box-shadow:
        0 6px 24px rgba(255, 123, 61, 0.4),
        0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}
.btn-mulai::before {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        135deg,
        rgba(255, 255, 255, 0.2) 0%,
        transparent 60%
    );
    pointer-events: none;
}
.btn-mulai:hover:not(:disabled):not(.btn-loading) {
    transform: translateY(-2px) scale(1.02);
    box-shadow:
        0 10px 32px rgba(255, 123, 61, 0.5),
        0 4px 12px rgba(0, 0, 0, 0.12);
}
.btn-mulai:active:not(:disabled) {
    transform: translateY(0) scale(0.98);
}
.btn-mulai.btn-active {
    background: linear-gradient(135deg, #ff7b3d 0%, #ff8a4d 100%);
    animation: pulse-btn 2.5s ease-in-out infinite;
}
.btn-mulai.btn-loading {
    opacity: 0.8;
    cursor: wait;
}
.btn-mulai.btn-shake {
    animation: shake 0.5s ease;
}
.btn-mulai:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

@keyframes pulse-btn {
    0%,
    100% {
        box-shadow: 0 6px 24px rgba(255, 123, 61, 0.4);
    }
    50% {
        box-shadow:
            0 8px 32px rgba(255, 123, 61, 0.6),
            0 0 0 8px rgba(255, 123, 61, 0.1);
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

/* Spinner */
.spinner {
    display: inline-block;
    width: 22px;
    height: 22px;
    border: 3px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.75s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
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
