<script setup>
import { ref, onMounted, computed } from "vue";
import {
    BookOpen,
    Rocket,
    Star,
    CheckCircle2,
    Backpack,
    FileQuestion,
    ChevronRight,
    ChevronDown,
    RefreshCw,
    Play,
    LayoutGrid,
    FlaskConical,
    Globe2,
    Calculator,
    BookMarked,
    TrendingUp,
    Target,
    Sparkles,
    Leaf,
    UserCircle,
    Settings,
    LogOut,
} from "lucide-vue-next";
import { Link, router } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Budi Santoso", class: { name: "SD-4" } }),
    },
    learningModules: { type: Array, default: () => [] },
});

const ready = ref(false);
const activeCategory = ref("semua");
const dropdownOpen = ref(false);
const menuRef = ref(null);
const musicOn = ref(false);
const audioRef = ref(null);

const toggleMusic = () => {
    musicOn.value = !musicOn.value;
    if (!audioRef.value) {
        audioRef.value = new Audio("backsound/backsound.mp3");
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
    }
    musicOn.value ? audioRef.value.play() : audioRef.value.pause();
};

// Tambahkan 2 handler ini SEBELUM onMounted
const handleVisibilityChange = () => {
    if (!audioRef.value) return;
    if (document.hidden) {
        audioRef.value.pause();
    } else if (musicOn.value) {
        audioRef.value.play();
    }
};

const handleBeforeUnload = () => {
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
};

// Ganti onMounted yang lama
onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("visibilitychange", handleVisibilityChange); // ← tambahan
    window.addEventListener("beforeunload", handleBeforeUnload); // ← tambahan
});

import { onUnmounted } from "vue";
onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("visibilitychange", handleVisibilityChange); // ← tambahan
    window.removeEventListener("beforeunload", handleBeforeUnload); // ← tambahan
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
});

const toggleDropdown = () => {
    dropdownOpen.value = !dropdownOpen.value;
};

const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target)) {
        dropdownOpen.value = false;
    }
};

const logout = () => {
    dropdownOpen.value = false;
    router.post(route("playground.logout"));
};

const categories = [
    { key: "semua", label: "Semua", icon: LayoutGrid },
    { key: "matematika", label: "Matematika", icon: Calculator },
    { key: "ipa", label: "IPA", icon: FlaskConical },
    { key: "bahasa", label: "Bahasa", icon: BookMarked },
    { key: "ips", label: "IPS", icon: Globe2 },
];

const modules = ref([
    {
        id: "m-1",
        name: "Penjumlahan & Pengurangan",
        description: "Berhitung seru dengan soal interaktif yang menyenangkan.",
        thumbnail: null,
        category: "matematika",
        accent: "#E8624A",
        missions_count: 3,
        missions_done: 3,
        best_score: 100,
        has_attempt: true,
        finished: true,
    },
    {
        id: "m-2",
        name: "Perkalian Seru",
        description: "Kuasai tabel perkalian 1–10 dengan tantangan kecepatan.",
        thumbnail: null,
        category: "matematika",
        accent: "#E09B2D",
        missions_count: 4,
        missions_done: 4,
        best_score: 92,
        has_attempt: true,
        finished: true,
    },
    {
        id: "m-3",
        name: "Pecahan & Desimal",
        description: "Pahami konsep pecahan lewat animasi visual yang keren.",
        thumbnail: null,
        category: "matematika",
        accent: "#8B5CF6",
        missions_count: 5,
        missions_done: 3,
        best_score: 75,
        has_attempt: true,
        finished: false,
    },
    {
        id: "m-4",
        name: "Sistem Tata Surya",
        description: "Jelajahi planet-planet dan pelajari fakta luar angkasa.",
        thumbnail: null,
        category: "ipa",
        accent: "#0891B2",
        missions_count: 3,
        missions_done: 3,
        best_score: 100,
        has_attempt: true,
        finished: true,
    },
    {
        id: "m-5",
        name: "Daur Hidup Hewan",
        description:
            "Pelajari metamorfosis kupu-kupu, katak, dan hewan lainnya.",
        thumbnail: null,
        category: "ipa",
        accent: "#16A34A",
        missions_count: 4,
        missions_done: 1,
        best_score: 40,
        has_attempt: true,
        finished: false,
    },
    {
        id: "m-6",
        name: "Energi & Perubahannya",
        description: "Kenali berbagai bentuk energi dan cara kerjanya di alam.",
        thumbnail: null,
        category: "ipa",
        accent: "#CA8A04",
        missions_count: 5,
        missions_done: 0,
        best_score: 0,
        has_attempt: false,
        finished: false,
    },
    {
        id: "m-7",
        name: "Membaca & Memahami Teks",
        description: "Tingkatkan kemampuan memahami isi bacaan cerita pendek.",
        thumbnail: null,
        category: "bahasa",
        accent: "#DC2626",
        missions_count: 4,
        missions_done: 3,
        best_score: 80,
        has_attempt: true,
        finished: false,
    },
    {
        id: "m-8",
        name: "Kosakata Bahasa Indonesia",
        description: "Perkaya kosakata dengan sinonim, antonim, dan idiom.",
        thumbnail: null,
        category: "bahasa",
        accent: "#2563EB",
        missions_count: 3,
        missions_done: 0,
        best_score: 0,
        has_attempt: false,
        finished: false,
    },
    {
        id: "m-9",
        name: "Peta & Wilayah Indonesia",
        description: "Kenali pulau, provinsi, dan ibu kota seluruh Indonesia.",
        thumbnail: null,
        category: "ips",
        accent: "#0D9488",
        missions_count: 4,
        missions_done: 2,
        best_score: 50,
        has_attempt: true,
        finished: false,
    },
    {
        id: "m-10",
        name: "Keragaman Budaya Nusantara",
        description:
            "Jelajahi tarian, adat, dan budaya dari seluruh Indonesia.",
        thumbnail: null,
        category: "ips",
        accent: "#E8624A",
        missions_count: 3,
        missions_done: 0,
        best_score: 0,
        has_attempt: false,
        finished: false,
    },
    {
        id: "m-11",
        name: "Bangun Datar & Ruang",
        description: "Kenali geometri dan cara menghitung luas serta volume.",
        thumbnail: null,
        category: "matematika",
        accent: "#0369A1",
        missions_count: 5,
        missions_done: 0,
        best_score: 0,
        has_attempt: false,
        finished: false,
    },
    {
        id: "m-12",
        name: "Sejarah Kemerdekaan RI",
        description:
            "Perjuangan para pahlawan dalam meraih kemerdekaan Indonesia.",
        thumbnail: null,
        category: "ips",
        accent: "#BE185D",
        missions_count: 4,
        missions_done: 0,
        best_score: 0,
        has_attempt: false,
        finished: false,
    },
]);

const filtered = computed(() =>
    activeCategory.value === "semua"
        ? modules.value
        : modules.value.filter((m) => m.category === activeCategory.value),
);

const totalFinished = computed(
    () => modules.value.filter((m) => m.finished).length,
);
const avgScore = computed(() => {
    const a = modules.value.filter((m) => m.has_attempt);
    return a.length
        ? Math.round(a.reduce((s, m) => s + m.best_score, 0) / a.length)
        : 0;
});
const overallProgress = computed(() => {
    const total = modules.value.reduce((s, m) => s + m.missions_count, 0);
    const done = modules.value.reduce((s, m) => s + m.missions_done, 0);
    return total ? Math.round((done / total) * 100) : 0;
});
const moduleProgress = (m) =>
    m.missions_count
        ? Math.round((m.missions_done / m.missions_count) * 100)
        : 0;
const statusLabel = (m) =>
    m.finished ? "Ulangi" : m.has_attempt ? "Lanjutkan" : "Mulai";

const scoreColor = (s) =>
    s >= 80 ? "#16A34A" : s >= 60 ? "#CA8A04" : "#DC2626";
const scoreBg = (s) =>
    s >= 80
        ? "rgba(22,163,74,.12)"
        : s >= 60
          ? "rgba(202,138,4,.12)"
          : "rgba(220,38,38,.12)";
</script>

<template>
    <div style="display: none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@600;700;800;900&display=swap"
            rel="stylesheet"
        />
    </div>

    <div class="pg">
        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div class="wrap row-between">
                <div class="brand">
                    <div class="brand-logo">
                        <Rocket :size="16" color="#fff" :stroke-width="2.5" />
                    </div>
                    <span class="brand-name">Geniuss</span>
                </div>
                <!-- User menu with dropdown -->
                <div class="user-menu" ref="menuRef">
                    <button
                        class="user-pill"
                        @click="toggleDropdown"
                        :class="{ open: dropdownOpen }"
                    >
                        <div class="avatar">{{ user.name.charAt(0) }}</div>
                        <div class="user-info">
                            <span class="u-name">{{ user.name }}</span>
                            <span class="u-kelas"
                                >Kelas {{ user.class?.name }}</span
                            >
                        </div>
                        <ChevronDown
                            :size="14"
                            :stroke-width="2.5"
                            class="pill-chevron"
                        />
                    </button>

                    <Transition name="dropdown">
                        <div v-if="dropdownOpen" class="dropdown">
                            <!-- Header -->
                            <div class="dd-header">
                                <div class="dd-avatar">
                                    {{ user.name.charAt(0) }}
                                </div>
                                <div class="dd-meta">
                                    <span class="dd-name">{{ user.name }}</span>
                                    <span class="dd-email">{{
                                        user.email
                                    }}</span>
                                </div>
                            </div>
                            <div class="dd-sep"></div>

                            <!-- Items -->
                            <!-- <Link
                                href="/profile"
                                class="dd-item"
                                @click="dropdownOpen = false"
                            >
                                <UserCircle :size="15" :stroke-width="2.2" />
                                Profil Saya
                            </Link>
                            <Link
                                href="/settings"
                                class="dd-item"
                                @click="dropdownOpen = false"
                            >
                                <Settings :size="15" :stroke-width="2.2" />
                                Pengaturan
                            </Link> -->

                            <div class="dd-sep"></div>

                            <button class="dd-item dd-logout" @click="logout">
                                <LogOut :size="15" :stroke-width="2.2" />
                                Keluar
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <!-- ══ HERO ══ -->
        <section class="hero-wrap" :class="{ 'fade-in': ready }">
            <div class="wrap">
                <div class="hero-card">
                    <div class="hero-left">
                        <span class="greet-tag">
                            <Sparkles :size="12" :stroke-width="2.5" />
                            Selamat datang kembali!
                        </span>
                        <h1 class="hero-name">{{ user.name }}</h1>
                        <p class="hero-desc">
                            Semangat belajar! Ada
                            <strong>{{ modules.length }}</strong> modul yang
                            siap dijelajahi hari ini.
                        </p>
                    </div>

                    <div class="stat-row">
                        <div class="stat-box stat-red">
                            <CheckCircle2
                                :size="22"
                                color="rgba(255,255,255,.9)"
                                :stroke-width="2"
                            />
                            <span class="s-val"
                                >{{ totalFinished
                                }}<small>/{{ modules.length }}</small></span
                            >
                            <span class="s-lbl">Selesai</span>
                        </div>
                        <div class="stat-box stat-green">
                            <Target
                                :size="22"
                                color="rgba(255,255,255,.9)"
                                :stroke-width="2"
                            />
                            <span class="s-val"
                                >{{ avgScore }}<small>%</small></span
                            >
                            <span class="s-lbl">Rata Skor</span>
                        </div>
                        <div class="stat-box stat-blue">
                            <Backpack
                                :size="22"
                                color="rgba(255,255,255,.9)"
                                :stroke-width="2"
                            />
                            <span class="s-val">{{ user.class?.name }}</span>
                            <span class="s-lbl">Kelas</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ FILTER ══ -->
        <div class="filter-outer" :class="{ 'fade-in': ready }">
            <div class="wrap">
                <div class="filter-scroll">
                    <button
                        v-for="cat in categories"
                        :key="cat.key"
                        class="ftab"
                        :class="{ active: activeCategory === cat.key }"
                        @click="activeCategory = cat.key"
                    >
                        <component
                            :is="cat.icon"
                            :size="14"
                            :stroke-width="2.2"
                        />
                        {{ cat.label }}
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ GRID ══ -->
        <main class="content-wrap">
            <div class="wrap">
                <div class="section-label">
                    <Leaf :size="14" color="#16A34A" :stroke-width="2.5" />
                    <span>{{ filtered.length }} modul tersedia</span>
                </div>

                <div class="mod-grid">
                    <article
                        v-for="(mod, i) in filtered"
                        :key="mod.id"
                        class="mod-card"
                        :class="{ 'card-in': ready, 'is-done': mod.finished }"
                        :style="{ '--ac': mod.accent, '--d': i * 40 + 'ms' }"
                    >
                        <!-- Thumbnail zone -->
                        <div class="card-thumb">
                            <div
                                class="thumb-bg"
                                :style="{ background: `${mod.accent}18` }"
                            >
                                <template v-if="mod.thumbnail">
                                    <img
                                        :src="mod.thumbnail"
                                        :alt="mod.name"
                                        class="thumb-img"
                                    />
                                </template>
                                <template v-else>
                                    <div
                                        class="thumb-icon-ring"
                                        :style="{
                                            background: `${mod.accent}22`,
                                            borderColor: `${mod.accent}44`,
                                        }"
                                    >
                                        <BookOpen
                                            :size="24"
                                            :color="mod.accent"
                                            :stroke-width="1.8"
                                        />
                                    </div>
                                </template>
                            </div>

                            <!-- Accent bottom line -->
                            <div
                                class="thumb-line"
                                :style="{ background: mod.accent }"
                            ></div>
                        </div>

                        <!-- Card content -->
                        <div class="card-content">
                            <h3 class="mod-title">{{ mod.name }}</h3>
                            <p class="mod-desc">{{ mod.description }}</p>

                            <!-- Score pill (if attempted) -->
                            <div
                                v-if="mod.has_attempt"
                                class="score-pill"
                                :style="{
                                    color: scoreColor(mod.best_score),
                                    background: scoreBg(mod.best_score),
                                }"
                            >
                                <Star
                                    :size="10"
                                    :stroke-width="0"
                                    :fill="scoreColor(mod.best_score)"
                                />
                                Skor terbaik:
                                <strong>{{ mod.best_score }}</strong>
                            </div>

                            <div class="card-divider"></div>

                            <!-- Meta -->
                            <div class="mod-meta">
                                <span class="meta-chip">
                                    <Target
                                        :size="11"
                                        :stroke-width="2.5"
                                        :color="mod.accent"
                                    />
                                    {{ mod.missions_done }}/{{
                                        mod.missions_count
                                    }}
                                    Misi
                                </span>
                                <span class="meta-chip">
                                    <FileQuestion
                                        :size="11"
                                        :stroke-width="2.5"
                                        :color="mod.accent"
                                    />
                                    Quiz & Materi
                                </span>
                            </div>

                            <button
                                class="mod-btn"
                                :class="{
                                    'btn-start': !mod.has_attempt,
                                    'btn-continue':
                                        mod.has_attempt && !mod.finished,
                                    'btn-done': mod.finished,
                                }"
                                :style="
                                    !mod.finished && !mod.has_attempt
                                        ? `--btnc:${mod.accent}`
                                        : ''
                                "
                            >
                                <component
                                    :is="mod.finished ? RefreshCw : Play"
                                    :size="13"
                                    :stroke-width="2.5"
                                    :fill="
                                        !mod.finished ? 'currentColor' : 'none'
                                    "
                                />
                                {{ statusLabel(mod) }}
                            </button>
                        </div>
                    </article>
                </div>

                <!-- Empty -->
                <div v-if="filtered.length === 0" class="empty-state">
                    <div class="empty-box">
                        <BookOpen
                            :size="32"
                            color="#a3c4a0"
                            :stroke-width="1.5"
                        />
                    </div>
                    <p class="empty-t">Belum ada modul</p>
                    <p class="empty-s">Tidak ada modul di kategori ini.</p>
                </div>
            </div>
        </main>

        <!-- ══ MUSIC FAB ══ -->
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
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.pg {
    min-height: 100vh;
    background: url("/public/images/templates/background.png") top center /
        cover no-repeat fixed;
    font-family: "Nunito", sans-serif;
    padding-bottom: 80px;
}

.wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 22px;
}
.row-between {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ══ TOPBAR — menyatu dengan langit biru background ══ */
.topbar {
    background: rgba(255, 255, 255, 0.22);
    backdrop-filter: blur(20px) saturate(1.4);
    -webkit-backdrop-filter: blur(20px) saturate(1.4);
    border-bottom: 1px solid rgba(255, 255, 255, 0.35);
    position: sticky;
    top: 0;
    z-index: 100;
}
.topbar .wrap {
    padding-top: 11px;
    padding-bottom: 11px;
}

.brand {
    display: flex;
    align-items: center;
    gap: 10px;
}
.brand-logo {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    background: linear-gradient(140deg, #34c45a, #1a9e3e);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 10px rgba(30, 160, 60, 0.4);
    flex-shrink: 0;
}
.brand-name {
    font-family: "Fredoka One", cursive;
    font-size: 22px;
    color: #1a4a1e;
    text-shadow: 0 1px 2px rgba(255, 255, 255, 0.7);
}

/* ══ USER MENU & DROPDOWN ══ */
.user-menu {
    position: relative;
}

.user-pill {
    display: flex;
    align-items: center;
    gap: 9px;
    background: rgba(255, 255, 255, 0.75);
    border: 1.5px solid rgba(255, 255, 255, 0.9);
    border-radius: 50px;
    padding: 5px 12px 5px 5px;
    backdrop-filter: blur(8px);
    cursor: pointer;
    transition: all 0.18s;
    outline: none;
}
.user-pill:hover,
.user-pill.open {
    background: rgba(255, 255, 255, 0.95);
    border-color: rgba(52, 196, 90, 0.4);
}

.avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, #34c45a, #1a9e3e);
    color: #fff;
    font-family: "Fredoka One", cursive;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(30, 160, 60, 0.35);
}
.user-info {
    display: flex;
    flex-direction: column;
    text-align: left;
}
.u-name {
    font-size: 12px;
    font-weight: 900;
    color: #1a3a1e;
    line-height: 1.2;
}
.u-kelas {
    font-size: 10px;
    font-weight: 700;
    color: #4a7a50;
}

.pill-chevron {
    color: #4a7a50;
    transition: transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    flex-shrink: 0;
}
.user-pill.open .pill-chevron {
    transform: rotate(180deg);
}

/* Dropdown panel */
.dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    min-width: 224px;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(24px);
    -webkit-backdrop-filter: blur(24px);
    border: 1.5px solid rgba(255, 255, 255, 0.95);
    border-radius: 16px;
    box-shadow:
        0 12px 40px rgba(30, 80, 30, 0.18),
        0 2px 8px rgba(0, 0, 0, 0.07);
    overflow: hidden;
    z-index: 200;
}

/* Dropdown header */
.dd-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 14px 12px;
}
.dd-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    flex-shrink: 0;
    background: linear-gradient(135deg, #34c45a, #1a9e3e);
    color: #fff;
    font-family: "Fredoka One", cursive;
    font-size: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(30, 160, 60, 0.3);
}
.dd-meta {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    min-width: 0;
}
.dd-name {
    font-size: 13px;
    font-weight: 900;
    color: #1a3a1e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.dd-email {
    font-size: 11px;
    font-weight: 700;
    color: #7a9a7e;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.dd-sep {
    height: 1px;
    background: rgba(30, 80, 30, 0.08);
    margin: 2px 10px;
}

/* Dropdown items */
.dd-item {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 10px 14px;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #3a5a3e;
    background: none;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.14s;
    text-align: left;
}
.dd-item:hover {
    background: rgba(30, 80, 30, 0.06);
    color: #1a3a1e;
}
.dd-logout {
    color: #dc2626;
}
.dd-logout:hover {
    background: rgba(220, 38, 38, 0.06);
    color: #b91c1c;
}

/* Dropdown animation */
.dropdown-enter-active {
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.dropdown-leave-active {
    transition: all 0.15s ease;
}
.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
}

/* ══ HERO ══ */
.hero-wrap {
    padding: 22px 0 0;
    opacity: 0;
    transform: translateY(18px);
    transition:
        opacity 0.55s ease,
        transform 0.55s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.hero-wrap.fade-in {
    opacity: 1;
    transform: none;
}

.hero-card {
    background: rgba(255, 255, 255, 0.82);
    backdrop-filter: blur(24px) saturate(1.3);
    -webkit-backdrop-filter: blur(24px) saturate(1.3);
    border: 1.5px solid rgba(255, 255, 255, 0.92);
    border-radius: 22px;
    padding: 26px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 28px;
    flex-wrap: wrap;
    box-shadow:
        0 8px 32px rgba(30, 100, 40, 0.12),
        0 1px 0 rgba(255, 255, 255, 0.9) inset;
}

.hero-left {
    flex: 1;
    min-width: 220px;
}
.greet-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 900;
    color: #1a9e3e;
    background: rgba(52, 196, 90, 0.15);
    border: 1.5px solid rgba(52, 196, 90, 0.3);
    border-radius: 50px;
    padding: 4px 12px;
    margin-bottom: 10px;
}
.hero-name {
    font-family: "Fredoka One", cursive;
    font-size: 30px;
    color: #1a3a1e;
    line-height: 1.1;
    margin-bottom: 5px;
}
.hero-desc {
    font-size: 13.5px;
    font-weight: 700;
    color: #4a6a4e;
}
.hero-desc strong {
    color: #1a9e3e;
    font-weight: 900;
}

/* Stat boxes */
.stat-row {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.stat-box {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    border-radius: 18px;
    padding: 16px 20px;
    min-width: 96px;
    backdrop-filter: blur(8px);
}
.stat-red {
    background: linear-gradient(145deg, #f87171, #ef4444);
    box-shadow: 0 5px 0 #b91c1c;
}
.stat-green {
    background: linear-gradient(145deg, #4ade80, #16a34a);
    box-shadow: 0 5px 0 #166534;
}
.stat-blue {
    background: linear-gradient(145deg, #60a5fa, #2563eb);
    box-shadow: 0 5px 0 #1e40af;
}
.s-val {
    font-family: "Fredoka One", cursive;
    font-size: 22px;
    color: #fff;
    line-height: 1;
}
.s-val small {
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 700;
    opacity: 0.85;
}
.s-lbl {
    font-size: 10px;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.9);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ══ FILTER ══ */
.filter-outer {
    padding: 16px 0 0;
    opacity: 0;
    transform: translateY(8px);
    transition:
        opacity 0.4s 0.14s ease,
        transform 0.4s 0.14s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.filter-outer.fade-in {
    opacity: 1;
    transform: none;
}
.filter-scroll {
    display: flex;
    gap: 7px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-bottom: 2px;
}
.filter-scroll::-webkit-scrollbar {
    display: none;
}

.ftab {
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 12px;
    border: 1.5px solid rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.68);
    backdrop-filter: blur(10px);
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #3a5a3e;
    cursor: pointer;
    transition: all 0.18s;
    white-space: nowrap;
}
.ftab:hover {
    background: rgba(255, 255, 255, 0.88);
    border-color: rgba(52, 196, 90, 0.5);
    color: #1a7a30;
}
.ftab.active {
    background: linear-gradient(135deg, #34c45a, #1a9e3e);
    border-color: #1a9e3e;
    color: #fff;
    box-shadow:
        0 4px 0 #166534,
        0 0 12px rgba(52, 196, 90, 0.25);
}

/* ══ GRID ══ */
.content-wrap {
    padding: 14px 0 0;
}
.section-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12.5px;
    font-weight: 800;
    color: rgba(255, 255, 255, 0.85);
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    margin-bottom: 12px;
}

.mod-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

/* ══ CARD ══ */
.mod-card {
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(16px) saturate(1.2);
    -webkit-backdrop-filter: blur(16px) saturate(1.2);
    border: 1.5px solid rgba(255, 255, 255, 0.95);
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    opacity: 0;
    transform: translateY(20px) scale(0.98);
    transition:
        opacity 0.4s var(--d, 0ms) ease,
        transform 0.4s var(--d, 0ms) cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.22s ease;
    box-shadow:
        0 4px 16px rgba(30, 80, 30, 0.1),
        0 1px 0 rgba(255, 255, 255, 0.8) inset;
}
.mod-card.card-in {
    opacity: 1;
    transform: none;
}
.mod-card:hover {
    transform: translateY(-5px) scale(1.01);
    box-shadow:
        0 12px 32px rgba(30, 80, 30, 0.18),
        0 1px 0 rgba(255, 255, 255, 0.9) inset;
    border-color: rgba(var(--ac), 0.4);
}
.mod-card.is-done {
    border-color: rgba(52, 196, 90, 0.4);
}

/* Thumbnail */
.card-thumb {
    position: relative;
    flex-shrink: 0;
}
.thumb-bg {
    height: 88px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.thumb-icon-ring {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
}
.thumb-line {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    opacity: 0.7;
}

/* Status badges */
.sbadge {
    position: absolute;
    top: 8px;
    right: 8px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 900;
    border-radius: 50px;
    padding: 3px 9px;
    backdrop-filter: blur(8px);
}
.sbadge-done {
    background: rgba(220, 252, 231, 0.92);
    color: #166534;
    border: 1px solid rgba(134, 239, 172, 0.6);
}
.sbadge-prog {
    background: rgba(254, 243, 199, 0.92);
    color: #92400e;
    border: 1px solid rgba(253, 230, 138, 0.6);
}
.sbadge-new {
    background: rgba(239, 246, 255, 0.92);
    color: #1d4ed8;
    border: 1px solid rgba(191, 219, 254, 0.6);
}

/* Card content */
.card-content {
    padding: 13px 14px 12px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    flex: 1;
}
.mod-title {
    font-family: "Fredoka One", cursive;
    font-size: 15px;
    color: #1a2e1c;
    line-height: 1.3;
}
.mod-desc {
    font-size: 11.5px;
    font-weight: 700;
    color: #6a8a6e;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Score pill */
.score-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 800;
    border-radius: 50px;
    padding: 3px 10px;
    width: fit-content;
}
.score-pill strong {
    font-weight: 900;
}

.card-divider {
    height: 1px;
    background: rgba(30, 80, 30, 0.08);
}

/* Meta chips */
.mod-meta {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.meta-chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 800;
    color: #5a7a5e;
    background: rgba(30, 80, 30, 0.07);
    border-radius: 8px;
    padding: 3px 9px;
}

/* Progress removed */

/* CTA button */
.mod-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    height: 38px;
    border: none;
    border-radius: 10px;
    font-family: "Fredoka One", cursive;
    font-size: 14px;
    letter-spacing: 0.3px;
    cursor: pointer;
    margin-top: auto;
    transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.mod-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.07);
}
.mod-btn:active {
    transform: translateY(1px);
}

.btn-start {
    background: var(--ac, #34c45a);
    color: #fff;
    box-shadow: 0 3px 0 rgba(0, 0, 0, 0.15);
}
.btn-continue {
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    box-shadow: 0 3px 0 rgba(180, 100, 0, 0.3);
}
.btn-done {
    background: rgba(30, 80, 30, 0.08);
    color: #4a7a50;
    border: 1.5px solid rgba(30, 80, 30, 0.15);
}

/* ══ EMPTY ══ */
.empty-state {
    text-align: center;
    padding: 64px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.empty-box {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: rgba(255, 255, 255, 0.7);
    border: 1.5px solid rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(8px);
}
.empty-t {
    font-family: "Fredoka One", cursive;
    font-size: 18px;
    color: rgba(255, 255, 255, 0.9);
    text-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
}
.empty-s {
    font-size: 13px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.7);
}

/* ══ MUSIC FAB ══ */
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
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(10px);
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.13);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #34c45a;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.music-fab:hover {
    transform: scale(1.12);
    background: #fff;
}
.music-fab.music-on {
    background: linear-gradient(135deg, #34c45a, #1a9e3e);
    color: #fff;
    box-shadow: 0 6px 24px rgba(52, 196, 90, 0.4);
}
.music-fab svg {
    width: 22px;
    height: 22px;
}

/* ══ RESPONSIVE ══ */
@media (max-width: 1100px) {
    .mod-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 768px) {
    .mod-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .hero-card {
        flex-direction: column;
        padding: 20px 18px;
    }
    .stat-row {
        width: 100%;
        justify-content: center;
    }
    .hero-left {
        min-width: unset;
        width: 100%;
    }
    .hero-prog-wrap {
        max-width: 100%;
    }
    .stat-box {
        min-width: 88px;
    }
}
@media (max-width: 480px) {
    .mod-grid {
        grid-template-columns: 1fr;
    }
    .hero-name {
        font-size: 25px;
    }
    .stat-box {
        min-width: 76px;
        padding: 12px 14px;
    }
    .wrap {
        padding: 0 14px;
    }
    .thumb-bg {
        height: 72px;
    }
}
</style>
