<script setup>
import { ref, onMounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import PlaygroundAuthLayout from "@/Layouts/PlaygroundAuthLayout.vue";

// ── State ──
const cardReady = ref(false);
const shakeBtn = ref(false);
const focusNama = ref(false);
const focusPassword = ref(false);
const showPassword = ref(false);
const layoutRef = ref(null);

// ── Inertia Form (handles loading, errors, CSRF otomatis) ──
const form = useForm({
    nama: "",
    password: "",
});

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => {
        cardReady.value = true;
    }, 100);
});

// ── Validation lokal ──
const localErrors = ref({ nama: "", password: "" });

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

// ── Computed errors (gabung local + inertia server errors) ──
const errors = {
    get nama() {
        return localErrors.value.nama || form.errors.nama || "";
    },
    get password() {
        return localErrors.value.password || form.errors.password || "";
    },
};

// ── Submit via Inertia ──
function handleLogin() {
    const namaOk = validateNama();
    const passwordOk = validatePassword();

    if (!namaOk || !passwordOk) {
        shakeBtn.value = true;
        setTimeout(() => {
            shakeBtn.value = false;
        }, 600);
        return;
    }

    if (layoutRef.value) {
        layoutRef.value.mascotBounce = true;
    }

    form.post(route("playground.authenticate"), {
        onSuccess: () => {
            if (layoutRef.value) {
                layoutRef.value.mascotBounce = false;
            }
            // Redirect ke playground.index
            router.visit(route("playground.index"));
        },
        onError: () => {
            if (layoutRef.value) {
                layoutRef.value.mascotBounce = false;
            }
            shakeBtn.value = true;
            setTimeout(() => {
                shakeBtn.value = false;
            }, 600);
        },
        onFinish: () => {
            if (layoutRef.value) {
                layoutRef.value.mascotBounce = false;
            }
        },
    });
}
</script>

<template>
    <PlaygroundAuthLayout ref="layoutRef">
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

                <!-- Input Password -->
                <div
                    class="field"
                    :class="{
                        focused: focusPassword,
                        filled: form.password,
                        errored: errors.password,
                    }"
                >
                    <input
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        placeholder="Password"
                        maxlength="60"
                        autocomplete="off"
                        @focus="
                            focusPassword = true;
                            localErrors.password = '';
                            form.clearErrors('password');
                        "
                        @blur="
                            focusPassword = false;
                            validatePassword();
                        "
                        @input="
                            localErrors.password = '';
                            form.clearErrors('password');
                        "
                    />
                    <button
                        v-if="form.password"
                        type="button"
                        class="toggle-password-btn"
                        @click="showPassword = !showPassword"
                    >
                        <svg
                            v-if="showPassword"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <svg
                            v-else
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <path
                                d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                            />
                            <line x1="1" y1="1" x2="23" y2="23" />
                        </svg>
                    </button>
                </div>
                <p v-if="errors.password" class="err-text">{{ errors.password }}</p>

                <button
                    class="btn-mulai"
                    :class="{
                        'btn-active': form.nama && form.password,
                        'btn-loading': form.processing,
                        'btn-shake': shakeBtn,
                    }"
                    @click="handleLogin"
                    :disabled="form.processing"
                >
                    <template v-if="!form.processing">
                        Masuk
                    </template>
                    <template v-else>
                        <span class="spinner"></span>
                        Masuk...
                    </template>
                </button>
            </div>
        </div>
    </PlaygroundAuthLayout>
</template>

<style scoped>
/* ─── RESET & BASE ─── */
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

.toggle-password-btn {
    background: none;
    border: none;
    cursor: pointer;
    padding: 6px;
    display: flex;
    align-items: center;
    color: #9ca3af;
    transition: color 0.2s;
}
.toggle-password-btn:hover {
    color: #ff7b3d;
}
.toggle-password-btn svg {
    width: 18px;
    height: 18px;
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

</style>
