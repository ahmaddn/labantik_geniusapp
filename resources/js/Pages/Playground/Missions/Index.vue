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
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

// ── Props ─────────────────────────────────────────────────────────
const props = defineProps({
    module: {
        type: Object,
        default: () => ({ id: null, name: "Module", description: "" }),
    },
    missions: {
        type: Array,
        default: () => [],
    },
    user: {
        type: Object,
        default: () => ({ name: "Siswa" }),
    },
});

// ── State ─────────────────────────────────────────────────────────
const ready = ref(false);
const musicOn = ref(false);
const audioRef = ref(null);

// ── Music ─────────────────────────────────────────────────────────
const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
        audioRef.value.preload = "auto";
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

// ── Lifecycle ─────────────────────────────────────────────────────
onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    document.addEventListener("visibilitychange", handleVisibility);
});
onUnmounted(() => {
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
});

// ── Helpers ───────────────────────────────────────────────────────
const goBack = () => router.visit(route("playground.index"));

const startMission = (mission) => {
  if (mission.status === 'completed') return
  router.visit(route('playground.missions.show', mission.id))
}
// ── Status helpers (pakai field 'status' dari controller) ─────────
const getMissionStatus = (mission) => {
    if (mission.status === "completed") return "Selesai";
    if (mission.status === "in_progress") return "Lanjutkan";
    return "Mulai";
};
const getMissionStatusColor = (mission) => {
    if (mission.status === "completed") return "#10b981";
    if (mission.status === "in_progress") return "#f59e0b";
    return "#3b82f6";
};
const getMissionStatusBg = (mission) => {
    if (mission.status === "completed") return "rgba(16,185,129,.1)";
    if (mission.status === "in_progress") return "rgba(245,158,11,.1)";
    return "rgba(59,130,246,.1)";
};
const getStatusIcon = (mission) => {
    if (mission.status === "completed") return CheckCircle2;
    if (mission.status === "in_progress") return Clock;
    return Play;
};

// ── Stats ─────────────────────────────────────────────────────────
const totalMissions = computed(() => props.missions?.length || 0);
const completedMissions = computed(
    () => props.missions?.filter((m) => m.status === "completed").length || 0,
);
const inProgressMissions = computed(
    () => props.missions?.filter((m) => m.status === "in_progress").length || 0,
);
const notStartedMissions = computed(
    () => props.missions?.filter((m) => m.status === "not_started").length || 0,
);
</script>

<template>
    <div class="pg-missions">
        <!-- HEADER -->
        <header class="missions-header">
            <button class="back-btn" @click="goBack">
                <ArrowLeft :size="18" />
                Kembali
            </button>
            <div class="header-info">
                <h1 class="missions-title">{{ module.name }}</h1>
                <p class="missions-subtitle">{{ module.description }}</p>
            </div>
            <div class="header-spacer"></div>
        </header>

        <!-- STATS CARD -->
        <section class="stats-section" :class="{ show: ready }">
            <div class="wrap">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div
                            class="stat-icon"
                            style="
                                background: rgba(59, 130, 246, 0.15);
                                color: #3b82f6;
                            "
                        >
                            <BookOpen :size="20" :stroke-width="2" />
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ totalMissions }}</div>
                            <div class="stat-label">Total Misi</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div
                            class="stat-icon"
                            style="
                                background: rgba(16, 185, 129, 0.15);
                                color: #10b981;
                            "
                        >
                            <CheckCircle2 :size="20" :stroke-width="2" />
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">
                                {{ completedMissions }}
                            </div>
                            <div class="stat-label">Selesai</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div
                            class="stat-icon"
                            style="
                                background: rgba(245, 158, 11, 0.15);
                                color: #f59e0b;
                            "
                        >
                            <Clock :size="20" :stroke-width="2" />
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">
                                {{ inProgressMissions }}
                            </div>
                            <div class="stat-label">Sedang Berjalan</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div
                            class="stat-icon"
                            style="
                                background: rgba(107, 114, 155, 0.15);
                                color: #6b727b;
                            "
                        >
                            <Zap :size="20" :stroke-width="2" />
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">
                                {{ notStartedMissions }}
                            </div>
                            <div class="stat-label">Belum Dimulai</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- MISSIONS GRID -->
        <main class="missions-section" :class="{ show: ready }">
            <div class="wrap">
                <div v-if="totalMissions > 0" class="missions-grid">
                    <article
                        v-for="(mission, i) in missions"
                        :key="mission.id"
                        class="mission-card"
                        :class="{
                            'card-show': ready,
                            'card-completed': mission.status === 'completed',
                            'card-in-progress':
                                mission.status === 'in_progress',
                        }"
                        :style="{ '--delay': i * 50 + 'ms' }"
                    >
                        <!-- Status badge -->
                        <div
                            class="mission-badge"
                            :style="{
                                color: getMissionStatusColor(mission),
                                background: getMissionStatusBg(mission),
                            }"
                        >
                            <component
                                :is="getStatusIcon(mission)"
                                :size="16"
                                :stroke-width="2"
                            />
                            {{ getMissionStatus(mission) }}
                        </div>

                        <!-- Body -->
                        <div class="mission-body">
                            <h3 class="mission-title">{{ mission.name }}</h3>
                            <p class="mission-desc">
                                {{ mission.description }}
                            </p>
                            <div class="mission-meta">
                                <div
                                    v-if="mission.total_questions"
                                    class="meta-item"
                                >
                                    <span class="meta-icon">❓</span>
                                    <span
                                        >{{
                                            mission.total_questions
                                        }}
                                        soal</span
                                    >
                                </div>
                                <div
                                    v-if="
                                        mission.best_score != null &&
                                        mission.status !== 'not_started'
                                    "
                                    class="meta-item"
                                >
                                    <span class="meta-icon">⭐</span>
                                    <span>Skor: {{ mission.best_score }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <button
                            class="mission-btn"
                            :class="`btn--${mission.status}`"
                            :disabled="mission.status === 'completed'"
                            @click="startMission(mission)"
                        >
                            <component
                                :is="getStatusIcon(mission)"
                                :size="14"
                                :stroke-width="2.5"
                            />
                            {{ getMissionStatus(mission) }}
                        </button>
                    </article>
                </div>

                <div v-else class="empty-state">
                    <BookOpen :size="48" color="#cbd5e1" :stroke-width="1.4" />
                    <p class="empty-title">Belum ada misi</p>
                    <p class="empty-desc">
                        Misi akan segera tersedia untuk modul ini.
                    </p>
                </div>
            </div>
        </main>

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
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.pg-missions {
    min-height: 100vh;
    font-family: "Nunito", sans-serif;
    padding-bottom: 60px;
    background: url("/images/templates/background.png") top center / cover
        no-repeat fixed;
}
.wrap {
    max-width: 1180px;
    margin: 0 auto;
    padding: 0 20px;
}

/* ── HEADER ── */
.missions-header {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.35);
    padding: 14px 32px;
    box-shadow: 0 1px 18px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 16px;
}
.back-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: rgba(255, 255, 255, 0.9);
    border: 1.5px solid rgba(29, 78, 216, 0.22);
    border-radius: 10px;
    font-weight: 700;
    font-size: 13px;
    color: #1e3a8a;
    cursor: pointer;
    transition: all 0.2s;
    font-family: "Nunito", sans-serif;
}
.back-btn:hover {
    background: #fff;
    border-color: rgba(29, 78, 216, 0.5);
    box-shadow: 0 3px 14px rgba(29, 78, 216, 0.14);
}
.header-info {
    flex: 1;
}
.missions-title {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #1e3a8a;
    margin-bottom: 2px;
}
.missions-subtitle {
    font-size: 12px;
    color: rgba(30, 58, 138, 0.72);
    font-weight: 600;
}
.header-spacer {
    flex-shrink: 0;
}

/* ── STATS ── */
.stats-section {
    padding: 14px 0;
    opacity: 0;
    transform: translateY(16px);
    transition:
        opacity 0.4s ease,
        transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.stats-section.show {
    opacity: 1;
    transform: none;
}
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.stat-card {
    background: rgba(255, 255, 255, 0.92);
    border: 1.5px solid rgba(29, 78, 216, 0.12);
    border-radius: 14px;
    padding: 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.2s;
}
.stat-card:hover {
    border-color: rgba(29, 78, 216, 0.28);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.stat-content {
    flex: 1;
}
.stat-value {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #1e3a8a;
    line-height: 1;
}
.stat-label {
    font-size: 10px;
    font-weight: 800;
    color: #6b7181;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-top: 3px;
}

/* ── MISSIONS GRID ── */
.missions-section {
    padding: 16px 0;
    opacity: 0;
    transform: translateY(8px);
    transition:
        opacity 0.4s 0.12s ease,
        transform 0.4s 0.12s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.missions-section.show {
    opacity: 1;
    transform: none;
}
.missions-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}

/* ── MISSION CARD ── */
.mission-card {
  background: #fff; border: 1.5px solid rgba(29,78,216,0.12);
  border-radius: 16px; padding: 16px;
  display: flex; flex-direction: column; gap: 12px;
  opacity: 0; transform: translateY(20px) scale(0.97);
  transition:
    opacity 0.4s var(--delay,0ms) ease,
    transform 0.4s var(--delay,0ms) cubic-bezier(0.34,1.56,0.64,1),
    box-shadow 0.2s ease, border-color 0.2s ease;
  box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}
.mission-card.card-show { opacity: 1; transform: none; }
.mission-card:hover { transform: translateY(-4px) scale(1.01); box-shadow: 0 12px 32px rgba(0,0,0,0.12); border-color: rgba(29,78,216,0.28); }

/* Completed card: subtle green tint */
.mission-card.card-completed {
    border-color: rgba(16, 185, 129, 0.35);
    background: linear-gradient(145deg, #fff 0%, #f0fdf4 100%);
}
.mission-card.card-completed:hover {
    border-color: rgba(16, 185, 129, 0.6);
    box-shadow: 0 12px 32px rgba(16, 185, 129, 0.12);
}

/* In-progress card: subtle amber tint */
.mission-card.card-in-progress {
    border-color: rgba(245, 158, 11, 0.28);
}
.mission-card.card-in-progress:hover {
    border-color: rgba(245, 158, 11, 0.5);
    box-shadow: 0 12px 32px rgba(245, 158, 11, 0.12);
}

.mission-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 800;
    padding: 4px 10px;
    border-radius: 50px;
    width: fit-content;
}
.mission-body {
    flex: 1;
}
.mission-title {
    font-family: "Righteous", cursive;
    font-size: 15px;
    color: #1e3a8a;
    margin-bottom: 4px;
    line-height: 1.3;
}
.mission-desc {
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    line-height: 1.5;
    margin-bottom: 10px;
}
.mission-meta {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
    color: #6b7181;
}
.meta-icon {
    font-size: 13px;
}

/* ── CTA BUTTON ── */
.mission-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    border: none;
    border-radius: 10px;
    font-family: "Righteous", cursive;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    color: #fff;
}
.mission-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.1);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
}
.mission-btn:active {
    transform: translateY(0);
}

/* Mulai → biru */
.btn--not_started {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    box-shadow: 0 3px 12px rgba(29, 78, 216, 0.28);
}

/* Lanjutkan → amber */
.btn--in_progress {
    background: linear-gradient(135deg, #fbbf24, #d97706);
    box-shadow: 0 3px 12px rgba(217, 119, 6, 0.28);
}

/* Selesai → hijau, tidak bisa diklik */
.btn--completed {
    background: linear-gradient(135deg, #34d399, #059669);
    box-shadow: 0 3px 12px rgba(5, 150, 105, 0.28);
    position: relative;
    overflow: hidden;
    cursor: not-allowed;
    opacity: 0.85;
}
.btn--completed:hover {
    transform: none;
    filter: none;
    box-shadow: 0 3px 12px rgba(5, 150, 105, 0.28);
}
.btn--completed::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(255, 255, 255, 0.15) 50%,
        transparent 100%
    );
    animation: shimmer 2.5s ease-in-out infinite;
}
@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}
.mission-btn:hover {
    transform: translateY(-2px);
    filter: brightness(1.12);
    box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
}
.mission-btn:active {
    transform: translateY(0);
}

/* ── EMPTY STATE ── */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}
.empty-title {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #1e3a8a;
}
.empty-desc {
    font-size: 13px;
    font-weight: 600;
    color: #6b7181;
}

/* ══ MUSIC FAB ══ */
.music-fab {
    position: fixed;
    bottom: 26px;
    left: 26px;
    z-index: 301;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    outline: none;
    background: rgba(255, 255, 255, 0.92);
    backdrop-filter: blur(10px);
    color: #1d4ed8;
    box-shadow: 0 4px 20px rgba(29, 78, 216, 0.22);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.music-fab:hover {
    transform: scale(1.1);
    background: #fff;
}
.music-fab.on {
    background: linear-gradient(135deg, #60a5fa, #1d4ed8);
    color: #fff;
    box-shadow: 0 6px 24px rgba(29, 78, 216, 0.44);
}
.music-fab svg {
    width: 21px;
    height: 21px;
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
        transform: scale(1.55);
        opacity: 0;
    }
}

/* ── RESPONSIVE ── */
@media (max-width: 1100px) {
    .missions-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (max-width: 768px) {
    .missions-header {
        flex-wrap: wrap;
        padding: 12px 16px;
    }
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    .missions-grid {
        grid-template-columns: 1fr;
    }
}
@media (max-width: 480px) {
    .missions-title {
        font-size: 16px;
    }
    .missions-subtitle {
        font-size: 11px;
    }
    .stat-card {
        padding: 12px;
    }
    .stat-value {
        font-size: 16px;
    }
    .mission-title {
        font-size: 14px;
    }
    .mission-desc {
        font-size: 11px;
    }
    .music-fab {
        bottom: 18px;
        left: 18px;
        width: 44px;
        height: 44px;
    }
}
</style>
