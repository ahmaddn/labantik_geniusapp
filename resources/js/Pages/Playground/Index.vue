<script setup>
import { ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";
import PlaygroundAppLayout from "@/Layouts/PlaygroundAppLayout.vue";

defineOptions({ layout: PlaygroundAppLayout });

// ── Props dari controller ──
const props = defineProps({
    siswa: {
        type: Object,
        default: () => ({ nama: "Budi Santoso", kelas: "SD-4" }),
    },
});

// ── State ──
const ready = ref(false);
const activeFilter = ref("semua");

onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
});

// ── Filter kategori ──
const filters = [
    { key: "semua", label: "Semua" },
    { key: "matematika", label: "Matematika" },
    { key: "ipa", label: "IPA" },
    { key: "bahasa", label: "Bahasa" },
    { key: "sosial", label: "IPS" },
];

// ── Data Modul Dummy ──
const modules = ref([
    {
        id: 1,
        title: "Penjumlahan & Pengurangan",
        description:
            "Belajar berhitung dasar dengan cara yang menyenangkan melalui mini game interaktif.",
        kategori: "matematika",
        icon: "➕",
        color: "#e8470a",
        colorLight: "#fff3ee",
        level: "Mudah",
        xp: 100,
        durasi: "15 menit",
        soal: 10,
        selesai: true,
        progress: 100,
    },
    {
        id: 2,
        title: "Perkalian Seru",
        description:
            "Kuasai tabel perkalian 1–10 dengan tantangan kecepatan dan hadiah bintang.",
        kategori: "matematika",
        icon: "✖️",
        color: "#f59e0b",
        colorLight: "#fffbeb",
        level: "Sedang",
        xp: 150,
        durasi: "20 menit",
        soal: 15,
        selesai: true,
        progress: 100,
    },
    {
        id: 3,
        title: "Pecahan & Desimal",
        description:
            "Memahami konsep pecahan dan desimal melalui animasi visual yang menarik.",
        kategori: "matematika",
        icon: "🔢",
        color: "#8b5cf6",
        colorLight: "#f5f3ff",
        level: "Sedang",
        xp: 200,
        durasi: "25 menit",
        soal: 12,
        selesai: false,
        progress: 60,
    },
    {
        id: 4,
        title: "Sistem Tata Surya",
        description:
            "Jelajahi planet-planet di tata surya kita dan pelajari fakta-fakta menakjubkan.",
        kategori: "ipa",
        icon: "🪐",
        color: "#0ea5e9",
        colorLight: "#f0f9ff",
        level: "Mudah",
        xp: 120,
        durasi: "18 menit",
        soal: 10,
        selesai: true,
        progress: 100,
    },
    {
        id: 5,
        title: "Daur Hidup Hewan",
        description:
            "Pelajari proses metamorfosis kupu-kupu, katak, dan hewan lainnya.",
        kategori: "ipa",
        icon: "🦋",
        color: "#10b981",
        colorLight: "#f0fdf4",
        level: "Mudah",
        xp: 130,
        durasi: "20 menit",
        soal: 12,
        selesai: false,
        progress: 30,
    },
    {
        id: 6,
        title: "Energi & Perubahannya",
        description:
            "Kenali berbagai bentuk energi dan bagaimana energi dapat berubah bentuk.",
        kategori: "ipa",
        icon: "⚡",
        color: "#f59e0b",
        colorLight: "#fffbeb",
        level: "Sulit",
        xp: 250,
        durasi: "30 menit",
        soal: 18,
        selesai: false,
        progress: 0,
    },
    {
        id: 7,
        title: "Membaca & Memahami Teks",
        description:
            "Tingkatkan kemampuan memahami isi bacaan dengan latihan cerita pendek.",
        kategori: "bahasa",
        icon: "📖",
        color: "#ec4899",
        colorLight: "#fdf2f8",
        level: "Mudah",
        xp: 110,
        durasi: "20 menit",
        soal: 8,
        selesai: false,
        progress: 75,
    },
    {
        id: 8,
        title: "Kosakata Bahasa Indonesia",
        description:
            "Perkaya kosakata dengan latihan kata sinonim, antonim, dan idiom.",
        kategori: "bahasa",
        icon: "💬",
        color: "#6366f1",
        colorLight: "#eef2ff",
        level: "Sedang",
        xp: 160,
        durasi: "22 menit",
        soal: 14,
        selesai: false,
        progress: 0,
    },
    {
        id: 9,
        title: "Peta & Wilayah Indonesia",
        description:
            "Mengenal nama pulau, provinsi, dan ibu kota di seluruh Indonesia.",
        kategori: "sosial",
        icon: "🗺️",
        color: "#14b8a6",
        colorLight: "#f0fdfa",
        level: "Sedang",
        xp: 180,
        durasi: "25 menit",
        soal: 15,
        selesai: false,
        progress: 45,
    },
    {
        id: 10,
        title: "Keragaman Budaya Nusantara",
        description:
            "Jelajahi kebudayaan, tarian, dan adat istiadat dari berbagai daerah Indonesia.",
        kategori: "sosial",
        icon: "🎭",
        color: "#e8470a",
        colorLight: "#fff3ee",
        level: "Mudah",
        xp: 140,
        durasi: "20 menit",
        soal: 12,
        selesai: false,
        progress: 0,
    },
    {
        id: 11,
        title: "Bangun Datar & Ruang",
        description:
            "Kenali berbagai bentuk geometri, sifat, dan cara menghitung luas & volume.",
        kategori: "matematika",
        icon: "📐",
        color: "#3b82f6",
        colorLight: "#eff6ff",
        level: "Sulit",
        xp: 220,
        durasi: "28 menit",
        soal: 16,
        selesai: false,
        progress: 0,
    },
    {
        id: 12,
        title: "Sejarah Kemerdekaan RI",
        description:
            "Pelajari perjuangan para pahlawan dalam meraih kemerdekaan Indonesia.",
        kategori: "sosial",
        icon: "🏳️",
        color: "#ef4444",
        colorLight: "#fef2f2",
        level: "Sedang",
        xp: 170,
        durasi: "22 menit",
        soal: 14,
        selesai: false,
        progress: 0,
    },
]);

const filteredModules = () => {
    if (activeFilter.value === "semua") return modules.value;
    return modules.value.filter((m) => m.kategori === activeFilter.value);
};

const levelColor = (level) => {
    if (level === "Mudah") return { bg: "#dcfce7", text: "#15803d" };
    if (level === "Sedang") return { bg: "#fef9c3", text: "#a16207" };
    return { bg: "#fee2e2", text: "#b91c1c" };
};

const totalXp = () =>
    modules.value.filter((m) => m.selesai).reduce((a, m) => a + m.xp, 0);
const totalSelesai = () => modules.value.filter((m) => m.selesai).length;
</script>

<template>
    <div class="index-page">
        <!-- ░░ HERO GREETING ░░ -->
        <section class="hero-section" :class="{ 'hero-in': ready }">
            <div class="hero-inner">
                <div class="hero-text">
                    <p class="hero-salam">🌟 Halo, selamat datang kembali!</p>
                    <h1 class="hero-nama">{{ siswa.nama }}</h1>
                    <p class="hero-desc">
                        Yuk lanjutkan petualangan belajarmu hari ini! Ada
                        <strong>{{ modules.length }}</strong> modul seru
                        menunggumu.
                    </p>
                </div>

                <!-- Stats row -->
                <div class="stats-row">
                    <div class="stat-chip">
                        <span class="stat-icon">⭐</span>
                        <div>
                            <span class="stat-val">{{ totalXp() }}</span>
                            <span class="stat-lbl">Total XP</span>
                        </div>
                    </div>
                    <div class="stat-chip">
                        <span class="stat-icon">✅</span>
                        <div>
                            <span class="stat-val"
                                >{{ totalSelesai() }}/{{ modules.length }}</span
                            >
                            <span class="stat-lbl">Selesai</span>
                        </div>
                    </div>
                    <div class="stat-chip">
                        <span class="stat-icon">🎒</span>
                        <div>
                            <span class="stat-val">{{ siswa.kelas }}</span>
                            <span class="stat-lbl">Kelasmu</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ░░ FILTER TABS ░░ -->
        <div class="filter-wrap" :class="{ 'filter-in': ready }">
            <div class="filter-scroll">
                <button
                    v-for="f in filters"
                    :key="f.key"
                    class="filter-btn"
                    :class="{ active: activeFilter === f.key }"
                    @click="activeFilter = f.key"
                >
                    {{ f.label }}
                </button>
            </div>
        </div>

        <!-- ░░ MODULE GRID ░░ -->
        <section class="module-section">
            <div class="module-grid">
                <div
                    v-for="(mod, idx) in filteredModules()"
                    :key="mod.id"
                    class="mod-card"
                    :class="{ 'card-in': ready, 'card-done': mod.selesai }"
                    :style="{
                        '--delay': `${idx * 55}ms`,
                        '--accent': mod.color,
                        '--accent-light': mod.colorLight,
                    }"
                >
                    <!-- Done badge -->
                    <div v-if="mod.selesai" class="done-badge">
                        <svg viewBox="0 0 20 20" fill="currentColor">
                            <path
                                fill-rule="evenodd"
                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        Selesai
                    </div>

                    <!-- Card header -->
                    <div class="mod-header">
                        <div class="mod-icon-wrap">
                            <span class="mod-icon">{{ mod.icon }}</span>
                        </div>
                        <div
                            class="mod-level"
                            :style="{
                                background: levelColor(mod.level).bg,
                                color: levelColor(mod.level).text,
                            }"
                        >
                            {{ mod.level }}
                        </div>
                    </div>

                    <!-- Card body -->
                    <div class="mod-body">
                        <h3 class="mod-title">{{ mod.title }}</h3>
                        <p class="mod-desc">{{ mod.description }}</p>
                    </div>

                    <!-- Meta info -->
                    <div class="mod-meta">
                        <span class="meta-item">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 16 14" />
                            </svg>
                            {{ mod.durasi }}
                        </span>
                        <span class="meta-item">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M9 11l3 3L22 4" />
                                <path
                                    d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"
                                />
                            </svg>
                            {{ mod.soal }} Soal
                        </span>
                        <span class="meta-item xp-item">
                            <svg viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"
                                />
                            </svg>
                            {{ mod.xp }} XP
                        </span>
                    </div>

                    <!-- Progress bar (jika belum selesai & sedang berjalan) -->
                    <div
                        v-if="!mod.selesai && mod.progress > 0"
                        class="mod-progress"
                    >
                        <div class="prog-label">
                            <span>Lanjutkan</span>
                            <span>{{ mod.progress }}%</span>
                        </div>
                        <div class="prog-track">
                            <div
                                class="prog-fill"
                                :style="{ width: mod.progress + '%' }"
                            ></div>
                        </div>
                    </div>

                    <!-- CTA Button -->
                    <button
                        class="mod-btn"
                        :class="{
                            'btn-lanjut': !mod.selesai && mod.progress > 0,
                            'btn-mulai': !mod.selesai && mod.progress === 0,
                            'btn-ulang': mod.selesai,
                        }"
                    >
                        <svg
                            v-if="mod.selesai"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <polyline points="23 4 23 10 17 10" />
                            <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10" />
                        </svg>
                        <svg
                            v-else
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                        >
                            <polygon points="5 3 19 12 5 21 5 3" />
                        </svg>
                        {{
                            mod.selesai
                                ? "Ulangi"
                                : mod.progress > 0
                                  ? "Lanjutkan"
                                  : "Mulai"
                        }}
                    </button>
                </div>
            </div>

            <!-- Empty state -->
            <div v-if="filteredModules().length === 0" class="empty-state">
                <span class="empty-icon">🔍</span>
                <p>Belum ada modul di kategori ini.</p>
            </div>
        </section>
    </div>
</template>

<style scoped>
.index-page {
    padding-bottom: 80px;
}

/* ─── HERO ─── */
.hero-section {
    margin: 20px auto 0;
    max-width: 1100px;
    padding: 28px 24px 20px;
    opacity: 0;
    transform: translateY(16px);
    transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.hero-section.hero-in {
    opacity: 1;
    transform: none;
}
.hero-inner {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    padding: 28px 32px;
    box-shadow:
        0 8px 32px rgba(0, 80, 150, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
}
.hero-salam {
    font-size: 13px;
    font-weight: 700;
    color: #5a9e24;
    margin-bottom: 4px;
}
.hero-nama {
    font-family: "Baloo 2", cursive;
    font-size: 28px;
    font-weight: 900;
    color: #1a1a2e;
    margin: 0 0 6px;
    line-height: 1.1;
}
.hero-desc {
    font-size: 14px;
    color: #4b5563;
    font-weight: 600;
    margin: 0;
}
.hero-desc strong {
    color: #e8470a;
}

.stats-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.stat-chip {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8faff;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 10px 16px;
    min-width: 110px;
}
.stat-icon {
    font-size: 22px;
}
.stat-val {
    display: block;
    font-family: "Baloo 2", cursive;
    font-size: 18px;
    font-weight: 800;
    color: #1a1a2e;
    line-height: 1;
}
.stat-lbl {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    margin-top: 1px;
}

/* ─── FILTER TABS ─── */
.filter-wrap {
    max-width: 1100px;
    margin: 16px auto 0;
    padding: 0 24px;
    opacity: 0;
    transform: translateY(10px);
    transition: all 0.45s 0.1s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.filter-wrap.filter-in {
    opacity: 1;
    transform: none;
}
.filter-scroll {
    display: flex;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.filter-scroll::-webkit-scrollbar {
    display: none;
}

.filter-btn {
    flex-shrink: 0;
    padding: 8px 20px;
    border-radius: 50px;
    border: 2px solid #e2e8f0;
    background: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(8px);
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.22s;
}
.filter-btn:hover {
    border-color: #5a9e24;
    color: #5a9e24;
}
.filter-btn.active {
    background: #5aaa2e;
    border-color: #5aaa2e;
    color: white;
    box-shadow: 0 4px 14px rgba(90, 170, 46, 0.3);
}

/* ─── MODULE GRID ─── */
.module-section {
    max-width: 1100px;
    margin: 20px auto 0;
    padding: 0 24px;
}
.module-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(290px, 1fr));
    gap: 18px;
}

/* ─── MODULE CARD ─── */
.mod-card {
    position: relative;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(14px);
    border-radius: 20px;
    padding: 22px 22px 20px;
    border: 2px solid rgba(255, 255, 255, 0.8);
    box-shadow:
        0 4px 20px rgba(0, 60, 120, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.95);
    display: flex;
    flex-direction: column;
    gap: 14px;
    opacity: 0;
    transform: translateY(20px) scale(0.97);
    transition:
        opacity 0.4s var(--delay, 0ms) ease,
        transform 0.4s var(--delay, 0ms) cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.25s ease;
    overflow: hidden;
}
.mod-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--accent, #e8470a);
    border-radius: 20px 20px 0 0;
}
.mod-card.card-in {
    opacity: 1;
    transform: translateY(0) scale(1);
}
.mod-card:hover {
    box-shadow:
        0 8px 32px rgba(0, 60, 120, 0.16),
        0 2px 8px rgba(0, 0, 0, 0.06);
    transform: translateY(-4px) scale(1.01);
}
.mod-card.card-done {
    border-color: rgba(90, 170, 46, 0.25);
}
.mod-card.card-done::before {
    background: linear-gradient(90deg, #5aaa2e, #3d8c1e);
}

/* DONE BADGE */
.done-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    background: #dcfce7;
    color: #15803d;
    font-size: 10px;
    font-weight: 800;
    border-radius: 50px;
    padding: 3px 10px 3px 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.done-badge svg {
    width: 12px;
    height: 12px;
}

/* HEADER */
.mod-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.mod-icon-wrap {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    background: var(--accent-light, #fff3ee);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
}
.mod-level {
    font-size: 11px;
    font-weight: 800;
    border-radius: 50px;
    padding: 4px 12px;
    letter-spacing: 0.3px;
}

/* BODY */
.mod-title {
    font-family: "Baloo 2", cursive;
    font-size: 17px;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 4px;
    line-height: 1.25;
}
.mod-desc {
    font-size: 13px;
    font-weight: 600;
    color: #6b7280;
    margin: 0;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* META */
.mod-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 700;
    color: #6b7280;
}
.meta-item svg {
    width: 13px;
    height: 13px;
}
.xp-item {
    color: #f59e0b;
}
.xp-item svg {
    fill: #f59e0b;
}

/* PROGRESS */
.mod-progress {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.prog-label {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
    font-weight: 700;
    color: #9ca3af;
}
.prog-track {
    width: 100%;
    height: 7px;
    background: #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
}
.prog-fill {
    height: 100%;
    border-radius: 10px;
    background: linear-gradient(
        90deg,
        var(--accent, #e8470a),
        color-mix(in srgb, var(--accent, #e8470a) 70%, white)
    );
    transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}

/* BUTTON */
.mod-btn {
    width: 100%;
    height: 44px;
    border: none;
    border-radius: 14px;
    font-family: "Baloo 2", cursive;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    margin-top: auto;
}
.mod-btn svg {
    width: 17px;
    height: 17px;
}

.btn-mulai {
    background: linear-gradient(
        135deg,
        var(--accent, #e8470a),
        color-mix(in srgb, var(--accent, #e8470a) 80%, white 20%)
    );
    color: white;
    box-shadow: 0 4px 14px
        color-mix(in srgb, var(--accent, #e8470a) 40%, transparent);
}
.btn-mulai:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px
        color-mix(in srgb, var(--accent, #e8470a) 50%, transparent);
}

.btn-lanjut {
    background: linear-gradient(135deg, #f59e0b, #fbbf24);
    color: white;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
}
.btn-lanjut:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
}

.btn-ulang {
    background: #f1f5f9;
    color: #64748b;
}
.btn-ulang:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

/* ─── EMPTY STATE ─── */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #9ca3af;
}
.empty-icon {
    font-size: 48px;
    display: block;
    margin-bottom: 12px;
}
.empty-state p {
    font-size: 16px;
    font-weight: 600;
}

/* ─── RESPONSIVE ─── */
@media (max-width: 640px) {
    .hero-inner {
        flex-direction: column;
    }
    .module-grid {
        grid-template-columns: 1fr;
    }
    .hero-nama {
        font-size: 22px;
    }
}
</style>
