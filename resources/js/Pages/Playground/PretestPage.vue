<script setup>
import { ref, computed, onMounted } from "vue";
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
            gambar: "/images/templates/background.png",
            totalSoal: 8,
            durasi: 10,
        }),
    },
    soalList: {
        type: Array,
        default: () => [
            {
                id: 1,
                pertanyaan: "Apa yang dimaksud dengan sungai?",
                gambar: "/images/templates/background.png",
                pilihan: [
                    { key: "A", teks: "Aliran air yang mengalir di daratan" },
                    { key: "B", teks: "Kumpulan air yang diam di danau" },
                    { key: "C", teks: "Air yang ada di lautan" },
                    { key: "D", teks: "Air hujan yang turun ke bumi" },
                ],
                jawaban: "A",
            },
            {
                id: 2,
                pertanyaan: "Darimana sumber air sungai berasal?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Dari laut" },
                    { key: "B", teks: "Dari mata air dan hujan" },
                    { key: "C", teks: "Dari sumur" },
                    { key: "D", teks: "Dari danau buatan" },
                ],
                jawaban: "B",
            },
            {
                id: 3,
                pertanyaan: "Apa fungsi utama sungai bagi manusia?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Tempat bermain" },
                    { key: "B", teks: "Sumber air dan irigasi" },
                    { key: "C", teks: "Tempat membuang sampah" },
                    { key: "D", teks: "Pembatas antar negara" },
                ],
                jawaban: "B",
            },
            {
                id: 4,
                pertanyaan: "Bagian sungai yang paling lebar disebut?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Hulu" },
                    { key: "B", teks: "Tengah" },
                    { key: "C", teks: "Hilir" },
                    { key: "D", teks: "Muara" },
                ],
                jawaban: "D",
            },
            {
                id: 5,
                pertanyaan: "Hewan apa yang biasa hidup di sungai?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Ikan" },
                    { key: "B", teks: "Unta" },
                    { key: "C", teks: "Gajah" },
                    { key: "D", teks: "Serigala" },
                ],
                jawaban: "A",
            },
            {
                id: 6,
                pertanyaan: "Apa yang harus kita lakukan untuk menjaga sungai?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Membuang sampah ke sungai" },
                    { key: "B", teks: "Tidak peduli dengan kebersihan" },
                    { key: "C", teks: "Menjaga kebersihan dan tidak mencemari" },
                    { key: "D", teks: "Membuang limbah pabrik" },
                ],
                jawaban: "C",
            },
            {
                id: 7,
                pertanyaan: "Sungai yang mengalir ke laut disebut?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Danau" },
                    { key: "B", teks: "Rawa" },
                    { key: "C", teks: "Muara" },
                    { key: "D", teks: "Waduk" },
                ],
                jawaban: "C",
            },
            {
                id: 8,
                pertanyaan: "Mengapa air sungai bisa keruh?",
                gambar: null,
                pilihan: [
                    { key: "A", teks: "Karena warnanya biru" },
                    { key: "B", teks: "Karena tercampur lumpur dan sampah" },
                    { key: "C", teks: "Karena terlalu dalam" },
                    { key: "D", teks: "Karena airnya segar" },
                ],
                jawaban: "B",
            },
        ],
    },
});

// ── View State ──
// 'intro' | 'soal' | 'selesai'
const currentView = ref("intro");
const ready = ref(false);

// ── Soal State ──
const currentSoalIndex = ref(0);
const jawabanDipilih = ref(null); // key jawaban yang dipilih
const jawabanUser = ref({}); // { soalId: key }
const isAnswerLocked = ref(false);

// ── Computed ──
const currentSoal = computed(() => props.soalList[currentSoalIndex.value]);
const totalSoal = computed(() => props.soalList.length);
const isLastSoal = computed(
    () => currentSoalIndex.value === totalSoal.value - 1
);
const skorAkhir = computed(() => {
    let benar = 0;
    props.soalList.forEach((s) => {
        if (jawabanUser.value[s.id] === s.jawaban) benar++;
    });
    return benar;
});

// ── Lifecycle ──
onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
});

// ── Methods ──
function handleMulai() {
    currentView.value = "soal";
    currentSoalIndex.value = 0;
    jawabanDipilih.value = null;
    isAnswerLocked.value = false;
}

function pilihJawaban(key) {
    if (isAnswerLocked.value) return;
    jawabanDipilih.value = key;
}

function handleBerikutnya() {
    if (!jawabanDipilih.value) return;

    // Simpan jawaban
    jawabanUser.value[currentSoal.value.id] = jawabanDipilih.value;

    if (isLastSoal.value) {
        // Selesai
        currentView.value = "selesai";
    } else {
        currentSoalIndex.value++;
        jawabanDipilih.value = null;
        isAnswerLocked.value = false;
    }
}

function handleLanjut() {
    // TODO: navigate ke peta petualangan
    // router.visit(route('peta.petualangan'))
    alert("Lanjut ke Peta Petualangan!");
}

function getPilihanClass(key) {
    if (!jawabanDipilih.value) return "";
    if (jawabanDipilih.value === key) return "pilihan-selected";
    return "";
}
</script>

<template>
    <div class="pretest-page">

        <!-- ░░░░░░░░░░░░░░░░░░ -->
        <!-- VIEW 1 : INTRO           -->
        <!-- ░░░░░░░░░░░░░░░░░░░░░░░░ -->
        <Transition name="view-fade" mode="out-in">
            <div v-if="currentView === 'intro'" key="intro" class="view-wrap">

                <!-- Card Header -->
                <div class="card pretest-card" :class="{ 'card-in': ready }">
                    <div class="pretest-inner">
                        <div class="title-area">
                            <div class="label-row">
                                <div class="label-icon">
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
                                    {{ totalSoal }} Soal
                                </div>
                            </div>
                        </div>

                        <div class="illustration-area">
                            <div class="illustration-wrap">
                                <img :src="modul.gambar" alt="Ilustrasi" class="illustration-img" />
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

                <!-- Card Petunjuk -->
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
                        <div class="mascot-area">
                            <div class="mascot-bubble">
                                <span>Semangat!</span>
                                <span>Kamu pasti bisa 💪</span>
                            </div>
                            <img src="/images/templates/pose_jempol.png" alt="Mascot" class="mascot-img" />
                        </div>
                    </div>
                </div>

            </div>
        </Transition>

        <!-- ░░░░░░░░░░░░░░░░░░░░░░░░ -->
        <!-- VIEW 2 : SOAL (QUIZ)     -->
        <!-- ░░░░░░░░░░░░░░░░░░░░░░░░ -->
        <Transition name="view-fade" mode="out-in">
            <div v-if="currentView === 'soal'" key="soal" class="view-wrap soal-view-wrap">
                <div class="soal-card">

                    <!-- ── Header: progress dots + timer ── -->
                    <div class="soal-header">
                        <div class="soal-progress-row">
                            <!-- Dot indicators -->
                            <div class="dots-wrap">
                                <div
                                    v-for="(_, i) in soalList"
                                    :key="i"
                                    class="dot"
                                    :class="{
                                        'dot-done': i < currentSoalIndex,
                                        'dot-active': i === currentSoalIndex,
                                    }"
                                ></div>
                            </div>
                            <span class="soal-counter">
                                Soal {{ currentSoalIndex + 1 }} dari {{ totalSoal }}
                            </span>
                        </div>
                        <!-- Timer display (visual only, timer logic ada di layout) -->
                        <div class="soal-timer">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            <span>10:00</span>
                        </div>
                    </div>

                    <!-- ── Nomor soal ── -->
                    <div class="soal-nomor">{{ currentSoalIndex + 1 }}.</div>

                    <!-- ── Konten soal + Ilustrasi ── -->
                    <div class="soal-body">
                        <div class="soal-konten-wrap">
                            <!-- Teks pertanyaan (jika ada) -->
                            <div v-if="currentSoal.pertanyaan" class="soal-teks-box">
                                <p class="soal-teks">{{ currentSoal.pertanyaan }}</p>
                            </div>
                            <!-- Kotak kosong jika tidak ada teks (sesuai desain) -->
                            <div v-else class="soal-blank-box"></div>
                        </div>

                        <!-- Ilustrasi soal -->
                        <div class="soal-ilustrasi-wrap">
                            <img
                                v-if="currentSoal.gambar"
                                :src="currentSoal.gambar"
                                alt="Ilustrasi Soal"
                                class="soal-ilustrasi"
                            />
                            <img
                                v-else
                                src="/images/templates/background.png"
                                alt="Ilustrasi"
                                class="soal-ilustrasi"
                            />
                        </div>
                    </div>

                    <!-- ── Pilihan Jawaban + Tombol ── -->
                    <div class="soal-footer">
                        <div class="pilihan-grid">
                            <button
                                v-for="p in currentSoal.pilihan"
                                :key="p.key"
                                class="pilihan-btn"
                                :class="getPilihanClass(p.key)"
                                @click="pilihJawaban(p.key)"
                            >
                                <span class="pilihan-key">{{ p.key }}</span>
                                <span class="pilihan-teks">{{ p.teks }}</span>
                            </button>
                        </div>

                        <button
                            class="btn-berikutnya"
                            :class="{ 'btn-disabled': !jawabanDipilih }"
                            :disabled="!jawabanDipilih"
                            @click="handleBerikutnya"
                        >
                            {{ isLastSoal ? "Selesai" : "Berikutnya" }}
                        </button>
                    </div>

                </div>
            </div>
        </Transition>

        <!-- ░░░░░░░░░░░░░░░░░░░░░░░░ -->
        <!-- VIEW 3 : SELESAI         -->
        <!-- ░░░░░░░░░░░░░░░░░░░░░░░░ -->
        <Transition name="view-fade" mode="out-in">
            <div v-if="currentView === 'selesai'" key="selesai" class="view-wrap selesai-view-wrap">
                <div class="selesai-card">
                    <div class="selesai-inner">

                        <div class="selesai-text">
                            <!-- Confetti badge -->
                            <div class="selesai-badge">🎉 Luar Biasa!</div>
                            <h1 class="selesai-judul">
                                Kamu telah menyelesaikan tantangan awal!
                            </h1>
                            <p class="selesai-sub">
                                Sekarang mari kita lanjutkan petualangan untuk lebih memahami sungai!
                            </p>

                            <!-- Skor -->
                            <div class="skor-row">
                                <div class="skor-chip">
                                    <span class="skor-icon">⭐</span>
                                    <div>
                                        <span class="skor-val">{{ skorAkhir }}/{{ totalSoal }}</span>
                                        <span class="skor-lbl">Jawaban Benar</span>
                                    </div>
                                </div>
                                <div class="skor-chip">
                                    <span class="skor-icon">🏆</span>
                                    <div>
                                        <span class="skor-val">{{ Math.round((skorAkhir / totalSoal) * 100) }}</span>
                                        <span class="skor-lbl">Nilai Kamu</span>
                                    </div>
                                </div>
                            </div>

                            <button class="btn-lanjut-peta" @click="handleLanjut">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M5 12h14M12 5l7 7-7 7" />
                                </svg>
                                Lanjut ke peta petualangan
                            </button>
                        </div>

                        <!-- Mascot besar -->
                        <div class="selesai-mascot">
                            <img
                                src="/images/templates/pose_jempol.png"
                                alt="Mascot"
                                class="selesai-mascot-img"
                            />
                        </div>

                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>

<style scoped>
/* ─── PAGE WRAPPER ─── */
.pretest-page {
    max-width: 1000px;
    margin: 0 auto;
    padding-bottom: 40px;
}

.view-wrap {
    width: 100%;
}

/* ─── VIEW TRANSITION ─── */
.view-fade-enter-active,
.view-fade-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.view-fade-enter-from {
    opacity: 0;
    transform: translateY(20px) scale(0.98);
}
.view-fade-leave-to {
    opacity: 0;
    transform: translateY(-20px) scale(0.98);
}

/* ─── SHARED CARD ─── */
.card {
    background: rgba(255, 255, 255, 0.88);
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
        transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
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

/* ════════════════════════════════ */
/*  VIEW 1 : INTRO                 */
/* ════════════════════════════════ */

.pretest-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 36px;
}
.title-area { flex: 1; }

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
.label-icon svg { width: 20px; height: 20px; }

.label-pretest {
    font-family: "Baloo 2", cursive;
    font-size: 1.25rem;
    font-weight: 900;
    color: #2a9de0;
}
.title-main {
    font-family: "Baloo 2", cursive;
    font-size: 2rem;
    font-weight: 900;
    color: #1a3a5c;
    line-height: 1.15;
    margin-bottom: 16px;
}
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
.meta-chip svg { width: 14px; height: 14px; }

.illustration-area {
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
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
    bottom: 0; left: 0; right: 0;
    height: 50px;
    background: linear-gradient(to top, rgba(255,255,255,0.2), transparent);
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
.btn-mulai svg { width: 17px; height: 17px; }
.btn-mulai:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 10px 32px rgba(26, 126, 200, 0.48);
}

/* Petunjuk Card */
.petunjuk-inner {
    display: flex;
    align-items: flex-end;
    gap: 28px;
}
.petunjuk-text { flex: 1; }
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
.petunjuk-icon-wrap svg { width: 18px; height: 18px; }
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
}
.mascot-img {
    width: 110px;
    height: 110px;
    object-fit: contain;
    animation: mascot-float 3s ease-in-out infinite;
    filter: drop-shadow(0 6px 14px rgba(0, 100, 180, 0.15));
}
@keyframes mascot-float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* ════════════════════════════════ */
/*  VIEW 2 : SOAL                  */
/* ════════════════════════════════ */

.soal-view-wrap {
    display: flex;
    justify-content: center;
}

.soal-card {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 28px;
    padding: 32px 36px 28px;
    width: 100%;
    max-width: 860px;
    box-shadow:
        0 12px 48px rgba(50, 120, 180, 0.14),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
    border: 1.5px solid rgba(255, 255, 255, 0.75);
}

/* Header */
.soal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 28px;
}
.soal-progress-row {
    display: flex;
    align-items: center;
    gap: 14px;
}
.dots-wrap {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.dot {
    width: 32px;
    height: 14px;
    border-radius: 50px;
    background: #dbeafe;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.dot.dot-done {
    background: #3aabeb;
    width: 32px;
}
.dot.dot-active {
    background: #1a7ec8;
    width: 40px;
    box-shadow: 0 3px 10px rgba(26, 126, 200, 0.35);
}
.soal-counter {
    font-size: 0.88rem;
    font-weight: 800;
    color: #4b5563;
    white-space: nowrap;
}
.soal-timer {
    display: flex;
    align-items: center;
    gap: 6px;
    font-family: "Baloo 2", cursive;
    font-size: 1.1rem;
    font-weight: 900;
    color: #1a3a5c;
}
.soal-timer svg {
    width: 22px;
    height: 22px;
    color: #3aabeb;
}

/* Nomor */
.soal-nomor {
    font-family: "Baloo 2", cursive;
    font-size: 2.4rem;
    font-weight: 900;
    color: #1a3a5c;
    margin-bottom: 20px;
    line-height: 1;
}

/* Body (teks + ilustrasi) */
.soal-body {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 28px;
}
.soal-konten-wrap {
    flex: 1;
}
.soal-teks-box {
    background: #f8faff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    min-height: 130px;
    display: flex;
    align-items: center;
}
.soal-teks {
    font-size: 1rem;
    font-weight: 700;
    color: #1a3a5c;
    line-height: 1.65;
    margin: 0;
}
.soal-blank-box {
    background: #f8faff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    min-height: 160px;
}
.soal-ilustrasi-wrap {
    flex-shrink: 0;
}
.soal-ilustrasi {
    width: 160px;
    height: 160px;
    object-fit: cover;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0, 100, 180, 0.15);
    display: block;
}

/* Footer (pilihan + tombol) */
.soal-footer {
    display: flex;
    align-items: flex-end;
    gap: 16px;
}
.pilihan-grid {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}
.pilihan-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    background: white;
    border: 2px solid #bfdbfe;
    border-radius: 50px;
    padding: 13px 20px;
    cursor: pointer;
    font-family: "Nunito", sans-serif;
    font-size: 0.92rem;
    font-weight: 700;
    color: #374151;
    text-align: left;
    transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.pilihan-btn:hover {
    border-color: #3aabeb;
    background: #f0f9ff;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(58, 171, 235, 0.18);
}
.pilihan-btn.pilihan-selected {
    border-color: #1a7ec8;
    background: linear-gradient(135deg, #e0f4ff, #dbeafe);
    color: #1a3a5c;
    box-shadow: 0 4px 20px rgba(26, 126, 200, 0.22);
    transform: translateY(-2px);
}
.pilihan-key {
    font-family: "Baloo 2", cursive;
    font-size: 1rem;
    font-weight: 900;
    color: #1a7ec8;
    flex-shrink: 0;
    min-width: 20px;
}
.pilihan-selected .pilihan-key {
    color: #1a3a5c;
}
.pilihan-teks {
    line-height: 1.3;
}

.btn-berikutnya {
    flex-shrink: 0;
    background: linear-gradient(135deg, #3aabeb, #1a7ec8);
    color: white;
    font-family: "Baloo 2", cursive;
    font-size: 1rem;
    font-weight: 900;
    padding: 15px 28px;
    border: none;
    border-radius: 18px;
    cursor: pointer;
    box-shadow: 0 6px 24px rgba(26, 126, 200, 0.35);
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    white-space: nowrap;
}
.btn-berikutnya:hover:not(.btn-disabled) {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 10px 30px rgba(26, 126, 200, 0.45);
}
.btn-disabled {
    background: linear-gradient(135deg, #cbd5e1, #94a3b8);
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.7;
}

/* ════════════════════════════════ */
/*  VIEW 3 : SELESAI               */
/* ════════════════════════════════ */

.selesai-view-wrap {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 75vh;
}

.selesai-card {
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    border-radius: 32px;
    padding: 52px 52px 48px;
    width: 100%;
    max-width: 900px;
    box-shadow:
        0 16px 60px rgba(50, 120, 180, 0.16),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
    border: 1.5px solid rgba(255, 255, 255, 0.75);
    animation: card-pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes card-pop {
    from { opacity: 0; transform: scale(0.92) translateY(20px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.selesai-inner {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 32px;
}
.selesai-text {
    flex: 1;
}

.selesai-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #fef9c3, #fde68a);
    border: 1.5px solid #fcd34d;
    color: #a16207;
    font-size: 0.85rem;
    font-weight: 900;
    border-radius: 50px;
    padding: 5px 16px;
    margin-bottom: 16px;
}

.selesai-judul {
    font-family: "Baloo 2", cursive;
    font-size: 2.2rem;
    font-weight: 900;
    color: #1a3a5c;
    line-height: 1.2;
    margin: 0 0 14px;
}
.selesai-sub {
    font-size: 1rem;
    font-weight: 600;
    color: #4b5563;
    line-height: 1.7;
    margin: 0 0 28px;
    max-width: 440px;
}

/* Skor */
.skor-row {
    display: flex;
    gap: 12px;
    margin-bottom: 32px;
    flex-wrap: wrap;
}
.skor-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8faff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 12px 20px;
    min-width: 130px;
}
.skor-icon { font-size: 1.8rem; }
.skor-val {
    display: block;
    font-family: "Baloo 2", cursive;
    font-size: 1.5rem;
    font-weight: 900;
    color: #1a3a5c;
    line-height: 1;
}
.skor-lbl {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #9ca3af;
    margin-top: 2px;
}

.btn-lanjut-peta {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: linear-gradient(135deg, #3aabeb, #1a7ec8);
    color: white;
    font-family: "Baloo 2", cursive;
    font-size: 1.05rem;
    font-weight: 900;
    padding: 16px 36px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 8px 28px rgba(26, 126, 200, 0.38);
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn-lanjut-peta svg { width: 18px; height: 18px; }
.btn-lanjut-peta:hover {
    transform: translateY(-3px) scale(1.04);
    box-shadow: 0 12px 36px rgba(26, 126, 200, 0.5);
}

/* Mascot selesai */
.selesai-mascot {
    flex-shrink: 0;
}
.selesai-mascot-img {
    width: 260px;
    height: 280px;
    object-fit: contain;
    filter: drop-shadow(0 16px 32px rgba(0, 100, 180, 0.18));
    animation: mascot-float-big 3s ease-in-out infinite;
}
@keyframes mascot-float-big {
    0%, 100% { transform: translateY(0) rotate(-1deg); }
    50% { transform: translateY(-14px) rotate(1deg); }
}

/* ─── RESPONSIVE ─── */
@media (max-width: 860px) {
    .pretest-inner,
    .petunjuk-inner,
    .selesai-inner {
        flex-direction: column;
    }
    .illustration-img { width: 100%; height: 180px; }
    .btn-mulai { margin-top: -16px; }
    .title-main { font-size: 1.5rem; }
    .mascot-area { flex-direction: row; align-items: flex-end; }
    .soal-body { flex-direction: column; }
    .soal-ilustrasi { width: 100%; height: 140px; }
    .soal-footer { flex-direction: column; align-items: stretch; }
    .btn-berikutnya { width: 100%; }
    .selesai-judul { font-size: 1.6rem; }
    .selesai-mascot-img { width: 180px; height: 200px; }
}

@media (max-width: 560px) {
    .pilihan-grid { grid-template-columns: 1fr; }
    .soal-card, .selesai-card { padding: 24px 18px; }
    .card { padding: 24px 20px; }
}
</style>
