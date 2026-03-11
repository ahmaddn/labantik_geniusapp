<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
    ArrowLeft,
    Play,
    Clock,
    CheckCircle2,
    BookOpen,
    Trophy,
    Zap,
    ChevronRight,
    Target,
    Award,
    Music2,
    VolumeX,
    Sparkles,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";
import { useMusic } from "@/Composable/useMusic";

const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();

const props = defineProps({
    module: {
        type: Object,
        default: () => ({ id: null, name: "Module", description: "" }),
    },
    missions: { type: Array, default: () => [] },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    all_missions_done: { type: Boolean, default: false },
    backsound: { type: String, default: null },
    background: { type: String, default: null },
});

const ready = ref(false);

// Floating decoration items — stars, sparkles, dots
const floaters = [
    {
        id: 1,
        type: "star",
        size: 18,
        color: "#fcd34d",
        x: 6,
        y: 20,
        dur: 6,
        delay: 0,
    },
    {
        id: 2,
        type: "circle",
        size: 10,
        color: "#93c5fd",
        x: 14,
        y: 65,
        dur: 8,
        delay: 1.2,
    },
    {
        id: 3,
        type: "star",
        size: 12,
        color: "#f9a8d4",
        x: 22,
        y: 35,
        dur: 7,
        delay: 2.5,
    },
    {
        id: 4,
        type: "circle",
        size: 14,
        color: "#6ee7b7",
        x: 30,
        y: 75,
        dur: 9,
        delay: 0.8,
    },
    {
        id: 5,
        type: "star",
        size: 20,
        color: "#fcd34d",
        x: 38,
        y: 15,
        dur: 11,
        delay: 3.5,
    },
    {
        id: 6,
        type: "circle",
        size: 8,
        color: "#c4b5fd",
        x: 46,
        y: 55,
        dur: 7.5,
        delay: 1.8,
    },
    {
        id: 7,
        type: "star",
        size: 14,
        color: "#fdba74",
        x: 54,
        y: 30,
        dur: 10,
        delay: 4.2,
    },
    {
        id: 8,
        type: "circle",
        size: 12,
        color: "#93c5fd",
        x: 62,
        y: 70,
        dur: 8.5,
        delay: 0.5,
    },
    {
        id: 9,
        type: "star",
        size: 10,
        color: "#f9a8d4",
        x: 70,
        y: 20,
        dur: 6.5,
        delay: 5,
    },
    {
        id: 10,
        type: "circle",
        size: 16,
        color: "#6ee7b7",
        x: 78,
        y: 60,
        dur: 12,
        delay: 2,
    },
    {
        id: 11,
        type: "star",
        size: 22,
        color: "#fcd34d",
        x: 86,
        y: 35,
        dur: 9.5,
        delay: 3,
    },
    {
        id: 12,
        type: "circle",
        size: 9,
        color: "#c4b5fd",
        x: 92,
        y: 75,
        dur: 7,
        delay: 6,
    },
    // extra on desktop
    {
        id: 13,
        type: "star",
        size: 11,
        color: "#fdba74",
        x: 10,
        y: 50,
        dur: 8,
        delay: 4.5,
    },
    {
        id: 14,
        type: "circle",
        size: 13,
        color: "#fcd34d",
        x: 50,
        y: 80,
        dur: 10,
        delay: 1.5,
    },
    {
        id: 15,
        type: "star",
        size: 16,
        color: "#93c5fd",
        x: 72,
        y: 45,
        dur: 7.5,
        delay: 3.8,
    },
];

// Touch ripple effect
const ripples = ref([]);
let rippleId = 0;

const onHeroTouch = (e) => {
    const rect = e.currentTarget.getBoundingClientRect();
    const t = e.touches ? e.touches[0] : e;
    const x = ((t.clientX - rect.left) / rect.width) * 100;
    const y = ((t.clientY - rect.top) / rect.height) * 100;
    const id = ++rippleId;
    ripples.value.push({ id, x, y });
    setTimeout(() => {
        ripples.value = ripples.value.filter((r) => r.id !== id);
    }, 700);
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => initAutoMusic(props.backsound), 100);
});
onUnmounted(() => {
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});

const goBack = () => router.visit(route("playground.index"));
const startMission = (m) => {
    if (m.status !== "completed")
        router.visit(route("playground.missions.show", m.id));
};
const goToPosttest = () =>
    router.visit(route("playground.posttest.show", props.module.id));

const getMissionStatus = (m) =>
    m.status === "completed"
        ? "Selesai"
        : m.status === "in_progress"
          ? "Lanjutkan"
          : "Mulai";
const getMissionStatusColor = (m) =>
    m.status === "completed"
        ? "#10b981"
        : m.status === "in_progress"
          ? "#f59e0b"
          : "#3b82f6";
const getMissionStatusBg = (m) =>
    m.status === "completed"
        ? "rgba(16,185,129,.1)"
        : m.status === "in_progress"
          ? "rgba(245,158,11,.1)"
          : "rgba(59,130,246,.1)";
const getStatusIcon = (m) =>
    m.status === "completed"
        ? CheckCircle2
        : m.status === "in_progress"
          ? Clock
          : Play;

const totalMissions = computed(() => props.missions?.length || 0);
const completedMissions = computed(
    () => props.missions?.filter((m) => m.status === "completed").length || 0,
);
const notStartedMissions = computed(
    () => props.missions?.filter((m) => m.status === "not_started").length || 0,
);
const progressPct = computed(() =>
    totalMissions.value
        ? Math.round((completedMissions.value / totalMissions.value) * 100)
        : 0,
);

const ACCENTS = [
    "#2563EB",
    "#8B5CF6",
    "#0891B2",
    "#16A34A",
    "#E09B2D",
    "#DC2626",
    "#0D9488",
    "#BE185D",
    "#CA8A04",
    "#7C3AED",
];
const accent = (i) => ACCENTS[i % ACCENTS.length];
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

    <div
        class="root"
        :style="background ? { '--bg-img': `url('${background}')` } : {}"
    >
        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <button class="back-btn" @click="goBack">
                <ArrowLeft :size="15" :stroke-width="2.5" />
                <span class="back-lbl">Kembali</span>
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
                    @click="toggleMusic(props.backsound)"
                >
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2" />
                    <VolumeX v-else :size="15" :stroke-width="2" />
                </button>
            </div>
        </header>

        <!-- ══ PAGE BODY ══ -->
        <div class="page-body" :class="{ 'page-body--on': ready }">
            <div class="wrap">
                <!-- ── DONE BANNER ── -->
                <Transition name="banner">
                    <div v-if="all_missions_done" class="done-banner">
                        <Trophy
                            :size="16"
                            :stroke-width="2.5"
                            class="banner-trophy-icon"
                        />
                        <span class="banner-text">Semua misi selesai! 🎉</span>
                        <button class="banner-cta" @click="goToPosttest">
                            Mulai Posttest
                            <ChevronRight :size="14" :stroke-width="3" />
                        </button>
                    </div>
                </Transition>
                <!-- ══ HERO CARD ══ -->
                <section class="hero-section">
                    <div
                        class="hero-card"
                        @touchstart.passive="onHeroTouch"
                        @click="onHeroTouch"
                    >
                        <!-- Animated gradient shimmer background -->
                        <div class="hero-shimmer"></div>

                        <!-- Floating decorations (CSS animated stars & dots) -->
                        <div class="floaters" aria-hidden="true">
                            <span
                                v-for="f in floaters"
                                :key="f.id"
                                class="floater"
                                :class="f.type"
                                :style="{
                                    left: f.x + '%',
                                    top: f.y + '%',
                                    width: f.size + 'px',
                                    height: f.size + 'px',
                                    background:
                                        f.type === 'circle'
                                            ? f.color
                                            : 'transparent',
                                    color: f.color,
                                    animationDuration: f.dur + 's',
                                    animationDelay: f.delay + 's',
                                }"
                            >
                                <!-- star shape via inline svg clip trick -->
                                <svg
                                    v-if="f.type === 'star'"
                                    viewBox="0 0 24 24"
                                    :style="{ fill: f.color }"
                                >
                                    <polygon
                                        points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"
                                    />
                                </svg>
                            </span>
                        </div>

                        <!-- Touch ripples -->
                        <span
                            v-for="r in ripples"
                            :key="r.id"
                            class="ripple"
                            :style="{ left: r.x + '%', top: r.y + '%' }"
                        ></span>

                        <!-- LEFT: module info + progress -->
                        <div class="hero-left">
                            <div class="hero-pill">
                                <Sparkles :size="10" :stroke-width="2.3" />
                                Misi Pembelajaran
                            </div>
                            <h1 class="hero-name">{{ module.name }}</h1>
                            <p class="hero-desc">{{ module.description }}</p>

                            <div class="hp-row">
                                <div class="hp-track">
                                    <div
                                        class="hp-fill"
                                        :style="{ width: progressPct + '%' }"
                                    >
                                        <span
                                            v-if="progressPct > 0"
                                            class="hp-dot"
                                        ></span>
                                    </div>
                                </div>
                                <span class="hp-pct">{{ progressPct }}%</span>
                            </div>
                        </div>

                        <!-- RIGHT: stat blocks -->
                        <div class="hero-right">
                            <div class="hsc">
                                <div class="hsc-icon hsc-blue">
                                    <Target :size="16" :stroke-width="2" />
                                </div>
                                <div class="hsc-body">
                                    <span class="hsc-val">{{
                                        totalMissions
                                    }}</span>
                                    <span class="hsc-lbl">Total Misi</span>
                                </div>
                            </div>
                            <div class="hsc-divider"></div>
                            <div class="hsc">
                                <div class="hsc-icon hsc-green">
                                    <Award :size="16" :stroke-width="2" />
                                </div>
                                <div class="hsc-body">
                                    <span class="hsc-val">{{
                                        completedMissions
                                    }}</span>
                                    <span class="hsc-lbl">Selesai</span>
                                </div>
                            </div>
                            <div class="hsc-divider"></div>
                            <div class="hsc">
                                <div class="hsc-icon hsc-amber">
                                    <BookOpen :size="16" :stroke-width="2" />
                                </div>
                                <div class="hsc-body">
                                    <span class="hsc-val">{{
                                        notStartedMissions
                                    }}</span>
                                    <span class="hsc-lbl">Belum</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── MISSIONS GRID ── -->
                <section class="grid-section">
                    <p class="grid-label">{{ totalMissions }} misi tersedia</p>

                    <div v-if="totalMissions > 0" class="mod-grid">
                        <article
                            v-for="(mission, i) in missions"
                            :key="mission.id"
                            class="mod-card"
                            :class="{
                                'card-show': ready,
                                done: mission.status === 'completed',
                                'card-in-progress':
                                    mission.status === 'in_progress',
                            }"
                            :style="{
                                '--ac': accent(i),
                                '--delay': i * 45 + 'ms',
                            }"
                        >
                            <div
                                class="card-bar"
                                :style="{ background: accent(i) }"
                            ></div>
                            <div
                                class="card-thumb"
                                :style="{ background: accent(i) + '12' }"
                            >
                                <div
                                    class="thumb-icon"
                                    :style="{
                                        background: accent(i) + '1e',
                                        borderColor: accent(i) + '55',
                                    }"
                                >
                                    <component
                                        :is="getStatusIcon(mission)"
                                        :size="24"
                                        :color="accent(i)"
                                        :stroke-width="1.8"
                                    />
                                </div>
                                <div
                                    v-if="mission.status === 'completed'"
                                    class="fin-stamp"
                                    style="
                                        color: #22c55e;
                                        border-color: #22c55e55;
                                        background: #22c55e15;
                                    "
                                >
                                    <CheckCircle2
                                        :size="11"
                                        :stroke-width="2.5"
                                    />
                                    Selesai
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="mod-title">{{ mission.name }}</h3>
                                <p class="mod-desc">
                                    {{ mission.description }}
                                </p>
                                <div
                                    v-if="
                                        mission.best_score != null &&
                                        mission.status !== 'not_started'
                                    "
                                    class="score-row"
                                    :style="{
                                        color: accent(i),
                                        background: accent(i) + '14',
                                    }"
                                >
                                    ⭐ Skor terbaik:
                                    <strong>{{ mission.best_score }}</strong>
                                </div>
                                <div class="divider"></div>
                                <div class="chips">
                                    <span
                                        v-if="mission.total_questions"
                                        class="chip"
                                        :style="{
                                            color: accent(i),
                                            background: accent(i) + '14',
                                        }"
                                    >
                                        <Zap :size="10" :stroke-width="2.5" />
                                        {{ mission.total_questions }} soal
                                    </span>
                                    <span
                                        class="chip"
                                        :style="{
                                            color: getMissionStatusColor(
                                                mission,
                                            ),
                                            background:
                                                getMissionStatusBg(mission),
                                        }"
                                    >
                                        <component
                                            :is="getStatusIcon(mission)"
                                            :size="10"
                                            :stroke-width="2.5"
                                        />
                                        {{ getMissionStatus(mission) }}
                                    </span>
                                </div>
                                <button
                                    class="cta"
                                    :class="{
                                        'cta-new':
                                            mission.status === 'not_started',
                                        'cta-cont':
                                            mission.status === 'in_progress',
                                        'cta-done':
                                            mission.status === 'completed',
                                    }"
                                    :style="
                                        mission.status === 'not_started'
                                            ? `--btnbg:${accent(i)}`
                                            : ''
                                    "
                                    :disabled="mission.status === 'completed'"
                                    @click="startMission(mission)"
                                >
                                    <component
                                        :is="getStatusIcon(mission)"
                                        :size="12"
                                        :stroke-width="2.5"
                                        :fill="
                                            mission.status === 'not_started'
                                                ? 'currentColor'
                                                : 'none'
                                        "
                                    />
                                    {{ getMissionStatus(mission) }}
                                </button>
                            </div>
                        </article>
                    </div>

                    <div v-else class="empty">
                        <BookOpen
                            :size="40"
                            color="rgba(255,255,255,.7)"
                            :stroke-width="1.4"
                        />
                        <p class="empty-t">Belum ada misi</p>
                        <p class="empty-s">
                            Misi akan segera tersedia untuk modul ini.
                        </p>
                    </div>
                </section>
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

.root {
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    font-family: "Nunito", sans-serif;
    position: relative;
    overflow-x: hidden;
}
/* Background image from database (v-bind set as CSS var on root element) */
.root::after {
    content: "";
    position: fixed;
    inset: 0;
    background: var(--bg-img, url("/images/templates/background.png")) center /
        cover no-repeat;
    z-index: -2;
}
/* Lighter, more cheerful overlay — just enough contrast for text */
.root::before {
    content: "";
    position: fixed;
    inset: 0;
    background:
        radial-gradient(
            ellipse 80% 60% at 0% 0%,
            rgba(96, 165, 250, 0.38) 0%,
            transparent 55%
        ),
        radial-gradient(
            ellipse 70% 55% at 100% 100%,
            rgba(139, 92, 246, 0.28) 0%,
            transparent 55%
        ),
        rgba(30, 58, 138, 0.3);
    z-index: -1;
}

/* ══ TOPBAR ══ */
.topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    height: 60px;
    flex-shrink: 0;
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: 10px;
    padding: 0 24px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    box-shadow: 0 2px 24px rgba(0, 0, 0, 0.12);
}
.back-btn {
    justify-self: start;
    display: inline-flex;
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
    transition:
        background 0.18s,
        transform 0.15s;
    white-space: nowrap;
}
.back-btn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}
.brand {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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
    flex-shrink: 0;
}
.brand-name {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #fff;
    white-space: nowrap;
}
.topbar-r {
    justify-self: end;
    display: flex;
    align-items: center;
    gap: 8px;
}
.tbtn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 7px 13px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 10px;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #fff;
    cursor: pointer;
    transition:
        background 0.18s,
        transform 0.15s;
}
.tbtn:hover {
    background: rgba(255, 255, 255, 0.2);
    transform: translateY(-1px);
}
.tbtn-sq {
    padding: 7px 10px;
}
.tbtn--on {
    background: #2563eb !important;
    border-color: #bfdbfe !important;
}
.posttest-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-family: "Righteous", cursive;
    font-size: 12.5px;
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.posttest-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.48);
}

/* ══ PAGE BODY ══ */
.page-body {
    flex: 1;
    padding-bottom: 48px;
    opacity: 0;
    transition: opacity 0.45s;
}
.page-body--on {
    opacity: 1;
}
/* GANTI .wrap yang lama */
.wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 16px 20px 0; /* ← tambah padding-top 16px */
}

/* ── DONE BANNER ── */
.banner-enter-active {
    animation: bannerIn 0.45s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.banner-leave-active {
    animation: bannerIn 0.2s ease-in reverse;
}
@keyframes bannerIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.done-banner {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    margin-bottom: 12px;
    background: linear-gradient(135deg, #1d4ed8, #2563eb);
    border: 1.5px solid #3b82f6;
    border-radius: 14px;
    box-shadow:
        0 3px 0 #1e3a8a,
        0 6px 20px rgba(37, 99, 235, 0.35);
}

.banner-trophy-icon {
    color: #fcd34d;
    flex-shrink: 0;
    filter: drop-shadow(0 1px 3px rgba(0, 0, 0, 0.2));
}

.banner-text {
    flex: 1;
    font-family: "Righteous", cursive;
    font-size: 13px;
    color: #fff;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.banner-cta {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 7px 14px;
    background: #fff;
    color: #1d4ed8;
    font-family: "Righteous", cursive;
    font-size: 12px;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    white-space: nowrap;
    flex-shrink: 0;
    box-shadow: 0 2px 0 #bfdbfe;
    transition: all 0.15s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.banner-cta:hover {
    transform: translateY(-2px);
    box-shadow:
        0 4px 0 #bfdbfe,
        0 6px 14px rgba(37, 99, 235, 0.2);
}
.banner-cta:active {
    transform: translateY(1px);
    box-shadow: 0 1px 0 #bfdbfe;
}

/* ══ HERO CARD ══ */
.hero-section {
    padding: 12px 0 0;
}
.hero-card {
    position: relative;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255, 255, 255, 0.22);
    border-radius: 20px;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    box-shadow:
        0 6px 28px rgba(0, 0, 0, 0.12),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
}

/* Animated shimmer across the card */
.hero-shimmer {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    background: linear-gradient(
        105deg,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.04) 40%,
        rgba(255, 255, 255, 0.09) 50%,
        rgba(255, 255, 255, 0.04) 60%,
        rgba(255, 255, 255, 0) 100%
    );
    background-size: 200% 100%;
    animation: shimmerMove 4s ease-in-out infinite;
}
@keyframes shimmerMove {
    0% {
        background-position: -100% 0;
    }
    100% {
        background-position: 200% 0;
    }
}

/* ── FLOATERS ── */
.floaters {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 1;
    overflow: hidden;
}
.floater {
    position: absolute;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    animation: floatUp ease-in-out infinite both;
    transform-origin: center;
}
.floater.circle {
    border-radius: 50%;
    box-shadow: 0 0 6px 2px currentColor;
}
.floater.star svg {
    width: 100%;
    height: 100%;
    filter: drop-shadow(0 0 4px currentColor);
}

/* Varied float animations — some drift left, some right, some twirl */
@keyframes floatUp {
    0% {
        opacity: 0;
        transform: translateY(6px) scale(0.7) rotate(0deg);
    }
    15% {
        opacity: 0.75;
    }
    50% {
        opacity: 0.55;
        transform: translateY(-14px) scale(1.1) rotate(18deg);
    }
    85% {
        opacity: 0.35;
        transform: translateY(-28px) scale(0.9) rotate(-8deg);
    }
    100% {
        opacity: 0;
        transform: translateY(-42px) scale(0.6) rotate(5deg);
    }
}

/* Every 3rd floater drifts horizontally */
.floater:nth-child(3n) {
    animation-name: floatUpLeft;
}
.floater:nth-child(3n + 2) {
    animation-name: floatUpRight;
}
@keyframes floatUpLeft {
    0% {
        opacity: 0;
        transform: translateY(4px) translateX(0) scale(0.7) rotate(0deg);
    }
    15% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.5;
        transform: translateY(-16px) translateX(-10px) scale(1.1) rotate(-20deg);
    }
    85% {
        opacity: 0.25;
        transform: translateY(-30px) translateX(-18px) scale(0.85) rotate(5deg);
    }
    100% {
        opacity: 0;
        transform: translateY(-44px) translateX(-24px) scale(0.6) rotate(-5deg);
    }
}
@keyframes floatUpRight {
    0% {
        opacity: 0;
        transform: translateY(4px) translateX(0) scale(0.7) rotate(0deg);
    }
    15% {
        opacity: 0.8;
    }
    50% {
        opacity: 0.5;
        transform: translateY(-14px) translateX(12px) scale(1.1) rotate(15deg);
    }
    85% {
        opacity: 0.25;
        transform: translateY(-28px) translateX(20px) scale(0.85) rotate(-6deg);
    }
    100% {
        opacity: 0;
        transform: translateY(-42px) translateX(26px) scale(0.6) rotate(8deg);
    }
}

/* Touch / click ripple */
.ripple {
    position: absolute;
    z-index: 2;
    pointer-events: none;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    transform: translate(-50%, -50%) scale(0);
    background: rgba(255, 255, 255, 0.55);
    animation: rippleOut 0.65s ease-out forwards;
}
@keyframes rippleOut {
    0% {
        transform: translate(-50%, -50%) scale(0);
        opacity: 0.8;
    }
    100% {
        transform: translate(-50%, -50%) scale(18);
        opacity: 0;
    }
}

/* ── LEFT CONTENT ── */
.hero-left {
    position: relative;
    z-index: 3;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 16px 20px 16px 22px;
    gap: 7px;
}
.hero-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 9.5px;
    font-weight: 900;
    letter-spacing: 0.5px;
    color: rgba(255, 255, 255, 0.9);
    background: rgba(255, 255, 255, 0.15);
    border: 1.5px solid rgba(255, 255, 255, 0.25);
    border-radius: 50px;
    padding: 2px 10px;
    width: fit-content;
}
.hero-name {
    font-family: "Righteous", cursive;
    font-size: clamp(16px, 2.2vw, 22px);
    color: #fff;
    line-height: 1.2;
    text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
}
.hero-desc {
    font-size: 11px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.6);
    line-height: 1.4;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 320px;
}
.hp-row {
    display: flex;
    align-items: center;
    gap: 8px;
}
.hp-track {
    flex: 1;
    max-width: 220px;
    height: 6px;
    background: rgba(255, 255, 255, 0.13);
    border-radius: 50px;
    overflow: hidden;
}
.hp-fill {
    position: relative;
    height: 100%;
    background: linear-gradient(90deg, #34d399, #10b981);
    border-radius: 50px;
    transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 0 8px rgba(52, 211, 153, 0.55);
}
.hp-dot {
    position: absolute;
    right: -3px;
    top: 50%;
    transform: translateY(-50%);
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #6ee7b7;
    box-shadow: 0 0 6px 3px rgba(110, 231, 183, 0.7);
    animation: glowPulse 1.5s ease-in-out infinite;
}
@keyframes glowPulse {
    0%,
    100% {
        opacity: 1;
        transform: translateY(-50%) scale(1);
    }
    50% {
        opacity: 0.5;
        transform: translateY(-50%) scale(0.7);
    }
}
.hp-pct {
    font-family: "Righteous", cursive;
    font-size: 12px;
    color: #fff;
    flex-shrink: 0;
}

/* ── RIGHT STAT BLOCKS ── */
.hero-right {
    position: relative;
    z-index: 3;
    display: flex;
    align-items: center;
    border-left: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0 4px;
}
.hsc {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
}
.hsc-icon {
    width: 30px;
    height: 30px;
    border-radius: 9px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hsc-blue {
    background: rgba(96, 165, 250, 0.25);
    color: #93c5fd;
}
.hsc-green {
    background: rgba(52, 211, 153, 0.25);
    color: #6ee7b7;
}
.hsc-amber {
    background: rgba(251, 191, 36, 0.25);
    color: #fcd34d;
}
.hsc-body {
    display: flex;
    flex-direction: column;
}
.hsc-val {
    font-family: "Righteous", cursive;
    font-size: 20px;
    color: #fff;
    line-height: 1;
}
.hsc-lbl {
    font-size: 8.5px;
    font-weight: 800;
    opacity: 0.65;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #fff;
    margin-top: 2px;
}
.hsc-divider {
    width: 1px;
    height: 36px;
    background: rgba(255, 255, 255, 0.12);
    flex-shrink: 0;
}

/* ══ GRID ══ */
.grid-section {
    padding: 12px 0 0;
}
.grid-label {
    font-size: 12px;
    font-weight: 800;
    color: #fff;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.45);
    margin-bottom: 10px;
    letter-spacing: 0.2px;
}
.mod-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 14px;
}

.mod-card {
    background: rgba(255, 255, 255, 0.96);
    border: 1.5px solid rgba(29, 78, 216, 0.12);
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    opacity: 0;
    transform: translateY(24px) scale(0.97);
    transition:
        opacity 0.42s var(--delay, 0ms) ease,
        transform 0.42s var(--delay, 0ms) cubic-bezier(0.34, 1.56, 0.64, 1),
        box-shadow 0.2s ease,
        border-color 0.2s ease;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
}
.mod-card.card-show {
    opacity: 1;
    transform: none;
}
.mod-card:hover {
    transform: translateY(-6px) scale(1.013);
    box-shadow:
        0 16px 40px rgba(0, 0, 0, 0.13),
        0 0 0 1.5px var(--ac);
    border-color: var(--ac);
}
.mod-card.done {
    border-color: rgba(34, 197, 94, 0.35);
}
.mod-card.card-in-progress {
    border-color: rgba(245, 158, 11, 0.28);
}

.card-bar {
    height: 4px;
}
.card-thumb {
    position: relative;
    height: 86px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.thumb-icon {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    border: 2px solid;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fin-stamp {
    position: absolute;
    bottom: 7px;
    right: 7px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 9.5px;
    font-weight: 900;
    border-radius: 50px;
    padding: 2px 8px;
    border: 1.5px solid;
}
.card-body {
    padding: 12px 13px 13px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    flex: 1;
}
.mod-title {
    font-family: "Righteous", cursive;
    font-size: 14.5px;
    color: #1e3a8a;
    line-height: 1.35;
}
.mod-desc {
    font-size: 11.5px;
    font-weight: 700;
    color: #4b6a9b;
    line-height: 1.55;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.score-row {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10.5px;
    font-weight: 800;
    border-radius: 50px;
    padding: 3px 10px;
    width: fit-content;
}
.score-row strong {
    font-weight: 900;
}
.divider {
    height: 1px;
    background: rgba(29, 78, 216, 0.08);
}
.chips {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}
.chip {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 800;
    border-radius: 8px;
    padding: 3px 8px;
}
.cta {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    height: 37px;
    border: none;
    border-radius: 10px;
    font-family: "Righteous", cursive;
    font-size: 13.5px;
    cursor: pointer;
    margin-top: auto;
    transition: all 0.17s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.cta:hover {
    transform: translateY(-2px);
    filter: brightness(1.07);
}
.cta:active {
    transform: translateY(1px);
}
.cta-new {
    background: var(--btnbg, #1d4ed8);
    color: #fff;
    box-shadow: 0 3px 10px rgba(29, 78, 216, 0.3);
}
.cta-cont {
    background: linear-gradient(135deg, #fbbf24, #d97706);
    color: #fff;
    box-shadow: 0 3px 8px rgba(217, 119, 6, 0.28);
}
.cta-done {
    background: linear-gradient(135deg, #34d399, #059669);
    color: #fff;
    box-shadow: 0 3px 8px rgba(5, 150, 105, 0.25);
    cursor: not-allowed;
    opacity: 0.85;
    position: relative;
    overflow: hidden;
}
.cta-done:hover {
    transform: none !important;
    filter: none !important;
}
.cta-done::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.15),
        transparent
    );
    animation: cta-shimmer 2.5s ease-in-out infinite;
}
@keyframes cta-shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

.empty {
    text-align: center;
    padding: 72px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}
.empty-t {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #fff;
    text-shadow: 0 1px 6px rgba(0, 0, 0, 0.2);
}
.empty-s {
    font-size: 13px;
    font-weight: 700;
    color: rgba(255, 255, 255, 0.7);
}

/* ══ RESPONSIVE ══ */
@media (max-width: 1100px) {
    .mod-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (max-width: 820px) {
    .mod-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 700px) {
    .hero-card {
        height: auto;
        flex-direction: column;
    }
    .hero-left {
        padding: 16px 18px 10px;
    }
    .hero-right {
        border-left: none;
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        justify-content: space-around;
        padding: 10px 6px;
    }
    .hsc {
        padding: 8px 10px;
    }
    .hsc-val {
        font-size: 18px;
    }
    .hp-track {
        max-width: none;
        flex: 1;
    }
    .hero-desc {
        max-width: none;
    }
}

@media (max-width: 600px) {
    .wrap {
        padding: 0 14px;
    }
    .topbar {
        padding: 0 14px;
    }
    .posttest-lbl {
        display: none;
    }
    .brand-name {
        font-size: 16px;
    }
    .brand-dot {
        width: 24px;
        height: 24px;
    }
}

@media (max-width: 480px) {
    .topbar {
        height: 52px;
        padding: 0 12px;
    }
    .back-lbl {
        display: none;
    }
    .back-btn {
        padding: 7px 10px;
    }
    .mod-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    .card-thumb {
        height: 72px;
    }
    .hero-name {
        font-size: 17px;
    }
    .hsc-val {
        font-size: 16px;
    }
    .hsc {
        padding: 7px 10px;
    }
}

@media (max-width: 340px) {
    .mod-grid {
        grid-template-columns: 1fr;
    }
}
</style>
