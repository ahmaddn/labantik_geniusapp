<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import PlaygroundLayout from "@/Layouts/PlaygroundAuthLayout.vue";

// ── Inertia Layout ──
defineOptions({ layout: PlaygroundLayout });

// ── State ──
const cardReady = ref(false);
const mascotBounce = ref(false);
const shakeBtn = ref(false);
const focusNama = ref(false);
const focusKelas = ref(false);

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
    <PlaygroundLayout>
        <!-- ░░ MASCOT WATER DROP ░░ -->
        <div class="mascot" :class="{ bounce: mascotBounce }">
            <svg
                viewBox="0 0 130 200"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
            >
                <!-- Main body - water drop -->
                <path
                    d="M65 8 C65 8 8 78 8 118 C8 158 33 188 65 188 C97 188 122 158 122 118 C122 78 65 8 65 8Z"
                    fill="url(#bodyGrad)"
                />
                <!-- Shine highlight -->
                <ellipse
                    cx="45"
                    cy="85"
                    rx="11"
                    ry="22"
                    fill="rgba(255,255,255,0.42)"
                    transform="rotate(-18 45 85)"
                />
                <ellipse
                    cx="55"
                    cy="65"
                    rx="6"
                    ry="12"
                    fill="rgba(255,255,255,0.28)"
                    transform="rotate(-18 55 65)"
                />
                <!-- Eyes white -->
                <ellipse cx="48" cy="115" rx="13" ry="15" fill="white" />
                <ellipse cx="82" cy="115" rx="13" ry="15" fill="white" />
                <!-- Pupils -->
                <circle cx="50" cy="117" r="8" fill="#154360" />
                <circle cx="84" cy="117" r="8" fill="#154360" />
                <!-- Eye shine -->
                <circle cx="53" cy="113" r="3" fill="white" />
                <circle cx="87" cy="113" r="3" fill="white" />
                <!-- Smile -->
                <path
                    d="M48 136 Q65 152 82 136"
                    stroke="#154360"
                    stroke-width="3.5"
                    stroke-linecap="round"
                    fill="none"
                />
                <!-- Cheeks -->
                <ellipse
                    cx="38"
                    cy="128"
                    rx="9"
                    ry="5.5"
                    fill="rgba(255,140,140,0.4)"
                />
                <ellipse
                    cx="92"
                    cy="128"
                    rx="9"
                    ry="5.5"
                    fill="rgba(255,140,140,0.4)"
                />
                <!-- Left waving arm -->
                <path
                    d="M12 105 Q0 78 14 60 Q22 50 32 57 Q18 72 26 94Z"
                    fill="url(#armGrad)"
                    :class="{ 'arm-anim': mascotBounce }"
                />
                <!-- Right arm -->
                <path
                    d="M118 105 Q132 82 120 65 Q113 54 103 60 Q118 78 110 98Z"
                    fill="url(#armGrad)"
                />
                <!-- Legs -->
                <path
                    d="M52 183 Q44 196 36 198 Q30 199 28 194 Q38 193 44 183Z"
                    fill="url(#bodyGrad)"
                />
                <path
                    d="M78 183 Q86 196 94 198 Q100 199 102 194 Q92 193 86 183Z"
                    fill="url(#bodyGrad)"
                />
                <!-- Water drops on top -->
                <path
                    d="M65 0 C65 0 58 10 58 15 C58 18.9 61.1 22 65 22 C68.9 22 72 18.9 72 15 C72 10 65 0 65 0Z"
                    fill="url(#dropTopGrad)"
                    opacity="0.9"
                />
                <path
                    d="M80 4 C80 4 75 11 75 15 C75 17.8 77.2 20 80 20 C82.8 20 85 17.8 85 15 C85 11 80 4 80 4Z"
                    fill="url(#dropTopGrad)"
                    opacity="0.75"
                />
                <path
                    d="M50 6 C50 6 45 13 45 17 C45 19.8 47.2 22 50 22 C52.8 22 55 19.8 55 17 C55 13 50 6 50 6Z"
                    fill="url(#dropTopGrad)"
                    opacity="0.65"
                />
                <defs>
                    <linearGradient
                        id="bodyGrad"
                        x1="20"
                        y1="8"
                        x2="110"
                        y2="188"
                        gradientUnits="userSpaceOnUse"
                    >
                        <stop offset="0%" stop-color="#74d7f7" />
                        <stop offset="55%" stop-color="#2e9fd4" />
                        <stop offset="100%" stop-color="#1a6fa0" />
                    </linearGradient>
                    <linearGradient
                        id="armGrad"
                        x1="0"
                        y1="0"
                        x2="1"
                        y2="0"
                        gradientUnits="objectBoundingBox"
                    >
                        <stop offset="0%" stop-color="#5ecef5" />
                        <stop offset="100%" stop-color="#2790c2" />
                    </linearGradient>
                    <linearGradient
                        id="dropTopGrad"
                        x1="0"
                        y1="0"
                        x2="0"
                        y2="1"
                        gradientUnits="objectBoundingBox"
                    >
                        <stop offset="0%" stop-color="#b0eaff" />
                        <stop offset="100%" stop-color="#2e9fd4" />
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- ░░ CARD ░░ -->
        <div class="start-card" :class="{ 'card-in': cardReady }">
            <!-- Logo -->
            <div class="logo-wrap">
                <div class="logo-title">
                    <span class="logo-geni">Geni</span
                    ><span class="logo-uss">uss</span>
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
                    <svg
                        class="field-icon"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                        <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                    </svg>
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
                        <polyline points="6 9 12 15 18 9" />
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
                        <svg
                            class="btn-icon"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <polygon points="5 3 19 12 5 21 5 3" />
                        </svg>
                        Mulai Petaulangan
                    </template>
                    <template v-else>
                        <span class="spinner"></span>
                        Memulai...
                    </template>
                </button>
            </div>
        </div>
    </PlaygroundLayout>
</template>

<style scoped>
/* ─── MASCOT ─── */
.mascot {
    position: fixed;
    right: calc(50% - 460px);
    bottom: 12%;
    width: 170px;
    z-index: 10;
    filter: drop-shadow(0 12px 24px rgba(0, 80, 140, 0.28));
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@media (max-width: 900px) {
    .mascot {
        right: 10px;
        width: 120px;
        bottom: 8%;
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
        transform: translateY(-28px) rotate(-5deg);
    }
    55% {
        transform: translateY(-14px) rotate(4deg);
    }
    75% {
        transform: translateY(-6px) rotate(-2deg);
    }
    100% {
        transform: translateY(0) rotate(0deg);
    }
}
.arm-anim {
    transform-origin: 22px 60px;
    animation: arm-wave 0.5s ease-in-out 3;
}
@keyframes arm-wave {
    0%,
    100% {
        transform: rotate(0deg);
    }
    50% {
        transform: rotate(-25deg);
    }
}

/* ─── CARD ─── */
.start-card {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-60%, -50%) scale(0.85);
    opacity: 0;
    transition: all 0.55s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 20;
    width: 360px;
    background: rgba(255, 255, 255, 0.93);
    backdrop-filter: blur(14px);
    border-radius: 28px;
    padding: 36px 36px 38px;
    box-shadow:
        0 8px 40px rgba(0, 80, 150, 0.16),
        0 2px 8px rgba(0, 0, 0, 0.08),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
}
.start-card.card-in {
    opacity: 1;
    transform: translate(-60%, -50%) scale(1);
}
@media (max-width: 900px) {
    .start-card {
        width: 320px;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.85);
    }
    .start-card.card-in {
        transform: translate(-50%, -50%) scale(1);
    }
}

/* ─── LOGO ─── */
.logo-wrap {
    text-align: center;
    margin-bottom: 28px;
}
.logo-title {
    font-family: "Baloo 2", cursive;
    font-size: 36px;
    font-weight: 900;
    line-height: 1;
    letter-spacing: -0.5px;
}
.logo-geni {
    color: #1a1a2e;
}
.logo-uss {
    color: #e8470a;
}
.logo-sub {
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

/* ─── FORM ─── */
.form-body {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.field {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f4f7ff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 0 14px;
    height: 52px;
    transition: all 0.22s;
    margin-bottom: 4px;
}
.field.focused {
    border-color: #3b9fd4;
    background: #eef7ff;
    box-shadow: 0 0 0 3px rgba(59, 159, 212, 0.12);
}
.field.filled {
    border-color: #5aaa2e;
    background: #f4fff0;
}
.field.errored {
    border-color: #ef4444;
    background: #fff5f5;
}

.field-icon {
    width: 18px;
    height: 18px;
    color: #9ca3af;
    flex-shrink: 0;
    transition: color 0.2s;
}
.field.focused .field-icon,
.field.filled .field-icon {
    color: #3b9fd4;
}
.field.errored .field-icon {
    color: #ef4444;
}

.field input,
.field select {
    flex: 1;
    border: none;
    background: transparent;
    font-family: "Nunito", sans-serif;
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    outline: none;
    min-width: 0;
}
.field input::placeholder {
    color: #9ca3af;
    font-weight: 500;
}
.field select {
    cursor: pointer;
    appearance: none;
    color: #1e293b;
}
.field select option {
    color: #1e293b;
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
    width: 18px;
    height: 18px;
    color: #9ca3af;
    flex-shrink: 0;
    pointer-events: none;
    transition:
        transform 0.2s,
        color 0.2s;
}
.field.focused .arrow-icon {
    transform: rotate(180deg);
    color: #3b9fd4;
}

.clear-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 4px;
    display: flex;
    align-items: center;
    color: #9ca3af;
    transition: color 0.2s;
}
.clear-btn:hover {
    color: #ef4444;
}
.clear-btn svg {
    width: 14px;
    height: 14px;
}

.err-text {
    font-size: 12px;
    font-weight: 600;
    color: #ef4444;
    padding-left: 4px;
    margin-top: -2px;
    margin-bottom: 4px;
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
    margin-top: 14px;
    width: 100%;
    height: 54px;
    border: none;
    border-radius: 16px;
    font-family: "Baloo 2", cursive;
    font-size: 18px;
    font-weight: 800;
    letter-spacing: 0.3px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    background: linear-gradient(135deg, #e8470a 0%, #ff6b2b 100%);
    color: white;
    box-shadow:
        0 4px 20px rgba(232, 71, 10, 0.35),
        0 2px 6px rgba(0, 0, 0, 0.1);
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
        rgba(255, 255, 255, 0.15) 0%,
        transparent 60%
    );
    pointer-events: none;
}
.btn-mulai:hover:not(:disabled):not(.btn-loading) {
    transform: translateY(-3px) scale(1.02);
    box-shadow:
        0 8px 28px rgba(232, 71, 10, 0.45),
        0 4px 10px rgba(0, 0, 0, 0.12);
}
.btn-mulai:active:not(:disabled) {
    transform: translateY(0) scale(0.98);
}
.btn-mulai.btn-active {
    background: linear-gradient(135deg, #e8470a 0%, #ff7a40 100%);
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
.btn-icon {
    width: 20px;
    height: 20px;
}

@keyframes pulse-btn {
    0%,
    100% {
        box-shadow: 0 4px 20px rgba(232, 71, 10, 0.35);
    }
    50% {
        box-shadow:
            0 6px 30px rgba(232, 71, 10, 0.55),
            0 0 0 6px rgba(232, 71, 10, 0.1);
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
    width: 20px;
    height: 20px;
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
</style>
