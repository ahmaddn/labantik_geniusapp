<script setup>
import { ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import PretestLayout from "@/Layouts/PretestLayout.vue";

defineOptions({ layout: PretestLayout });

// ── Props dari controller ──
const props = defineProps({
    siswa: {
        type: Object,
        default: () => ({ nama: "Budi Santoso", kelas: "SD-4" }),
    },
    modul: {
        type: Object,
        default: () => ({
            judul: "Tantangan Awal",
            deskripsi:
                "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis nec ornare nisl. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas risus felis, dapibus ut nisl eget, malesuada finibus nisl. Vivamus id tincidunt arcu. In purus quam, mollis nec sapien vel, dictum aliquet sem. Pellentesque in dignissim elit. Donec sed nisl eu",
            gambar: "/images/templates/river_landscape.png",
            totalSoal: 10,
            durasi: 10,
        }),
    },
});

// ── State ──
const ready = ref(false);

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
});

// ── Methods ──
function handleMulai() {
    // TODO: navigate ke halaman soal pertama
    // router.visit(route('pretest.soal', { id: props.modul.id }))
    alert("Memulai Pretest: " + props.modul.judul);
}
</script>

<template>
    <div class="pretest-page">

        <!-- ░░ CARD 1 : PRETEST HEADER ░░ -->
        <div class="card pretest-card" :class="{ 'card-in': ready }">
            <div class="pretest-inner">

                <!-- Judul & Label -->
                <div class="title-area">
                    <div class="label-row">
                        <div class="label-icon">
                            <!-- Target / Bullseye icon -->
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                                <line x1="22" y1="2" x2="16" y2="8" />
                                <line x1="17" y1="3" x2="17" y2="8" />
                                <line x1="21" y1="3" x2="16" y2="3" />
                            </svg>
                        </div>
                        <span class="label-pretest">Pretest</span>
                    </div>
                    <h1 class="title-main">{{ modul.judul }}</h1>

                    <!-- Meta chips -->
                    <div class="meta-row">
                        <div class="meta-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ modul.durasi }} Menit
                        </div>
                        <div class="meta-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 11l3 3L22 4" />
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                            </svg>
                            {{ modul.totalSoal }} Soal
                        </div>
                    </div>
                </div>

                <!-- Ilustrasi + Tombol -->
                <div class="illustration-area">
                    <div class="illustration-wrap">
                        <img
                            :src="modul.gambar"
                            alt="Ilustrasi Modul"
                            class="illustration-img"
                        />
                        <!-- Overlay gradient bottom -->
                        <div class="illustration-overlay"></div>
                    </div>
                    <button class="btn-mulai" @click="handleMulai">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polygon points="5 3 19 12 5 21 5 3" />
                        </svg>
                        Mulai
                    </button>
                </div>

            </div>
        </div>

        <!-- ░░ CARD 2 : PETUNJUK PENGERJAAN ░░ -->
        <div class="card petunjuk-card" :class="{ 'card-in-delay': ready }">
            <div class="petunjuk-inner">

                <div class="petunjuk-text">
                    <div class="petunjuk-header">
                        <div class="petunjuk-icon-wrap">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" y1="8" x2="12" y2="12" />
                                <line x1="12" y1="16" x2="12.01" y2="16" />
                            </svg>
                        </div>
                        <h2 class="petunjuk-title">Petunjuk Pengerjaan :</h2>
                    </div>
                    <p class="petunjuk-body">{{ modul.deskripsi }}</p>
                </div>

                <!-- Mascot Bubble -->
                <div class="mascot-area">
                    <div class="mascot-bubble">
                        <span>Semangat!</span>
                        <span>Kamu pasti bisa 💪</span>
                    </div>
                    <img
                        src="/images/templates/pose_keren.png"
                        alt="Mascot"
                        class="mascot-img"
                    />
                </div>

            </div>
        </div>

    </div>
</template>

<style scoped>
/* ─── PAGE ─── */
.pretest-page {
    max-width: 1000px;
    margin: 0 auto;
    padding-bottom: 40px;
}

/* ─── CARDS ─── */
.card {
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 24px;
    padding: 32px 36px;
    margin-bottom: 22px;
    box-shadow:
        0 8px 32px rgba(50, 120, 180, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
    border: 1.5px solid rgba(255, 255, 255, 0.7);
    opacity: 0;
    transform: translateY(22px);
    transition:
        opacity 0.5s cubic-bezier(0.34, 1.56, 0.64, 1),
        transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.25s ease;
}
.card:hover {
    box-shadow:
        0 12px 40px rgba(50, 120, 180, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
}
.card.card-in {
    opacity: 1;
    transform: translateY(0);
}
.card.card-in-delay {
    opacity: 1;
    transform: translateY(0);
    transition-delay: 0.15s;
}

/* ─── CARD 1 : PRETEST INNER ─── */
.pretest-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 36px;
}

/* Title Area */
.title-area {
    flex: 1;
}
.label-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 10px;
}
.label-icon {
    width: 36px;
    height: 36px;
    background: linear-gradient(135deg, #e0f4ff, #b3e5fc);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0288d1;
    flex-shrink: 0;
}
.label-icon svg {
    width: 20px;
    height: 20px;
}
.label-pretest {
    font-family: "Baloo 2", cursive;
    font-size: 1.25rem;
    font-weight: 900;
    color: #2a9de0;
    letter-spacing: 0.5px;
}
.title-main {
    font-family: "Baloo 2", cursive;
    font-size: 2rem;
    font-weight: 900;
    color: #1a3a5c;
    line-height: 1.15;
    margin-bottom: 16px;
}

/* Meta chips */
.meta-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.meta-chip {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #f0f9ff;
    border: 1.5px solid #bae6fd;
    color: #0369a1;
    font-size: 0.82rem;
    font-weight: 800;
    border-radius: 50px;
    padding: 5px 14px;
}
.meta-chip svg {
    width: 14px;
    height: 14px;
}

/* Illustration */
.illustration-area {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0;
}
.illustration-wrap {
    position: relative;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 8px 28px rgba(0, 100, 180, 0.18);
}
.illustration-img {
    width: 380px;
    height: 210px;
    object-fit: cover;
    display: block;
}
.illustration-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 50px;
    background: linear-gradient(to top, rgba(255, 255, 255, 0.2), transparent);
}

.btn-mulai {
    margin-top: -18px;
    background: linear-gradient(135deg, #3aabeb, #1a7ec8);
    color: white;
    font-family: "Baloo 2", cursive;
    font-size: 1.05rem;
    font-weight: 900;
    padding: 13px 44px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 6px 24px rgba(26, 126, 200, 0.38);
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    z-index: 2;
}
.btn-mulai svg {
    width: 17px;
    height: 17px;
}
.btn-mulai:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 10px 32px rgba(26, 126, 200, 0.48);
}
.btn-mulai:active {
    transform: translateY(0) scale(0.98);
}

/* ─── CARD 2 : PETUNJUK INNER ─── */
.petunjuk-inner {
    display: flex;
    align-items: flex-end;
    gap: 28px;
}
.petunjuk-text {
    flex: 1;
}
.petunjuk-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 14px;
}
.petunjuk-icon-wrap {
    width: 34px;
    height: 34px;
    background: linear-gradient(135deg, #fef9c3, #fef08a);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #a16207;
    flex-shrink: 0;
}
.petunjuk-icon-wrap svg {
    width: 18px;
    height: 18px;
}
.petunjuk-title {
    font-family: "Baloo 2", cursive;
    font-size: 1.35rem;
    font-weight: 900;
    color: #2a9de0;
    margin: 0;
}
.petunjuk-body {
    font-size: 1rem;
    font-weight: 600;
    color: #374151;
    line-height: 1.82;
    margin: 0;
}

/* Mascot Area */
.mascot-area {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.mascot-bubble {
    background: white;
    border: 2px solid #bae6fd;
    border-radius: 16px;
    padding: 8px 14px;
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 0.78rem;
    font-weight: 800;
    color: #0369a1;
    box-shadow: 0 4px 14px rgba(0, 100, 180, 0.1);
    position: relative;
    text-align: center;
    line-height: 1.4;
}
.mascot-bubble::after {
    content: "";
    position: absolute;
    bottom: -10px;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: white;
    filter: drop-shadow(0 2px 0 #bae6fd);
}
.mascot-img {
    width: 110px;
    height: 110px;
    object-fit: contain;
    animation: mascot-float 3s ease-in-out infinite;
    filter: drop-shadow(0 6px 14px rgba(0, 100, 180, 0.15));
}
@keyframes mascot-float {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

/* ─── RESPONSIVE ─── */
@media (max-width: 860px) {
    .pretest-inner {
        flex-direction: column;
    }
    .illustration-img {
        width: 100%;
        height: 180px;
    }
    .btn-mulai {
        margin-top: -16px;
    }
    .title-main {
        font-size: 1.5rem;
    }
    .petunjuk-inner {
        flex-direction: column;
        align-items: center;
    }
    .mascot-area {
        flex-direction: row;
        align-items: flex-end;
    }
}

@media (max-width: 640px) {
    .card {
        padding: 24px 20px;
    }
}
</style>
