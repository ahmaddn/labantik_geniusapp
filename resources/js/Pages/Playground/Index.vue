<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import {
    BookOpen,
    Star,
    CheckCircle2,
    ChevronDown,
    Play,
    LogOut,
    GraduationCap,
    Zap,
    Music2,
    VolumeX,
    FlameKindling,
    Target,
    Award,
    Image as ImageIcon,
    RotateCcw,
    Eye,
    Menu,
    X,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";
import { useMusic } from "@/Composable/useMusic";

const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
    learningModules: { type: Array, default: () => [] },
    backsound: { type: String, default: null },
});

const ready = ref(false);
const dropdownOpen = ref(false);
const menuRef = ref(null);
const showModal = ref(false);
const modalVisible = ref(false);
const selectedModule = ref(null);
const mobileSidebarOpen = ref(false);

const openModal = (mod) => {
    selectedModule.value = mod;
    showModal.value = true;
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            modalVisible.value = true;
        });
    });
};

const closeModal = () => {
    modalVisible.value = false;
    setTimeout(() => {
        showModal.value = false;
        selectedModule.value = null;
    }, 320);
};

const goToModuleResult = () => {
    if (!selectedModule.value) return;
    router.visit(route("playground.posttest.result", selectedModule.value.id));
};

const restartModule = () => {
    if (!selectedModule.value) return;
    router.visit(route("playground.pretest.show", { module: selectedModule.value.id, restart: 'true' }));
};


const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target))
        dropdownOpen.value = false;
};

const logout = () => {
    dropdownOpen.value = false;
    router.post(route("playground.logout"));
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => initAutoMusic(null), 100);
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});

const totalFinished = computed(
    () => props.learningModules.filter((m) => m.fully_completed).length,
);
const inProgress = computed(
    () =>
        props.learningModules.filter((m) => m.has_attempt && !m.fully_completed)
            .length,
);
const progressPct = computed(() =>
    props.learningModules.length
        ? Math.round((totalFinished.value / props.learningModules.length) * 100)
        : 0,
);

const statusLabel = (m) =>
    m.fully_completed ? "Selesai" : m.has_attempt ? "Lanjutkan" : "Mulai";

const goToModule = (mod) => {
    router.visit(route("playground.pretest.show", mod.id));
};

const ACCENTS = ["#58cc02", "#1cb0f6", "#ff9600", "#a435f0"];
const accent = (i) => ACCENTS[i % ACCENTS.length];

// ── Tab switcher & Filter ──
const activeFilter = ref("all"); // "all", "class", "general"

const filteredModules = computed(() => {
    if (activeFilter.value === "class") {
        return props.learningModules.filter((m) => !m.is_general);
    } else if (activeFilter.value === "general") {
        return props.learningModules.filter((m) => m.is_general);
    }
    return props.learningModules;
});
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

    <div class="duo-layout" :class="{ 'layout--ready': ready }" ref="menuRef">
        <div class="bg-particles">
            <div class="particle p-1"></div>
            <div class="particle p-2"></div>
            <div class="particle p-3"></div>
            <div class="particle p-4"></div>
            <div class="particle p-5"></div>
        </div>

        <div v-if="mobileSidebarOpen" class="mobile-sidebar-backdrop" @click="mobileSidebarOpen = false"></div>

        <aside class="dsk-sidebar-left" :class="{ 'mobile-open': mobileSidebarOpen }">
            <div class="dsk-sidebar-inner">
                <div class="ds-brand">
                <div class="ds-brand-icon" :class="{ 'has-logo': $page.props.global_settings?.platform_logo }">
                    <img v-if="$page.props.global_settings?.platform_logo" :src="$page.props.global_settings?.platform_logo" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;" />
                    <Zap
                        v-else
                        :size="22"
                        color="#fff"
                        fill="white"
                        :stroke-width="2"
                    />
                </div>
                    <span class="ds-brand-name">{{
                        $page.props.global_settings?.platform_name || "Geniuss"
                    }}</span>
                    <button class="mobile-sidebar-close" @click="mobileSidebarOpen = false">
                        <X :size="20" color="#94a3b8" :stroke-width="2.5" />
                    </button>
                </div>

                <div class="ds-sep"></div>
                <p class="ds-label">NAVIGASI</p>

                <div
                    class="ds-nav-btn ds-nav-active"
                    style="pointer-events: none"
                >
                    <BookOpen :size="17" :stroke-width="3" />
                    <span>MODUL</span>
                </div>

                <div class="ds-spacer"></div>

                <button
                    class="ds-music-btn"
                    :class="{ on: musicOn }"
                    @click="toggleMusic(null)"
                >
                    <Music2 v-if="musicOn" :size="17" :stroke-width="3" />
                    <VolumeX v-else :size="17" :stroke-width="3" />
                    <span>MUSIK: {{ musicOn ? "ON" : "OFF" }}</span>
                </button>

                <div class="ds-sep"></div>

                <div class="ds-user">
                    <button
                        class="ds-user-pill"
                        :class="{ open: dropdownOpen }"
                        @click="dropdownOpen = !dropdownOpen"
                    >
                        <div class="ds-avatar">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                        <span class="ds-uname">{{
                            user.name.split(" ")[0]
                        }}</span>
                        <ChevronDown
                            :size="14"
                            :stroke-width="3.5"
                            :style="{
                                marginLeft: 'auto',
                                transform: dropdownOpen
                                    ? 'rotate(180deg)'
                                    : 'rotate(0deg)',
                                transition: 'transform .2s',
                            }"
                        />
                    </button>

                    <Transition name="t-dropdown">
                        <div v-if="dropdownOpen" class="ds-dropdown">
                            <div class="ds-dd-profile">
                                <div class="ds-avatar ds-avatar-lg">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="ds-dd-text">
                                    <div class="ds-dd-name">{{ user.name }}</div>
                                    <div class="ds-dd-class">
                                        Kelas {{ user.class?.name || '-' }}
                                    </div>
                                </div>
                            </div>
                            <div class="ds-dd-sep"></div>
                            <button class="ds-dd-logout" @click="logout">
                                <LogOut :size="16" :stroke-width="2.5" /> KELUAR
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <aside class="mobile-bottom-nav">
            <nav class="mobile-menu-grid">
                <div class="mobile-menu-item active">
                    <BookOpen :size="18" :stroke-width="2.5" />
                    <span>MODUL</span>
                </div>
                <div
                    class="mobile-menu-item music-toggle-item"
                    :class="{ 'music-on': musicOn }"
                    @click="toggleMusic(null)"
                >
                    <Music2 v-if="musicOn" :size="18" :stroke-width="2.5" />
                    <VolumeX v-else :size="18" :stroke-width="2.5" />
                    <span>MUSIK: {{ musicOn ? "ON" : "OFF" }}</span>
                </div>
            </nav>
        </aside>

        <main class="duo-main-content">
            <div class="learning-path-container">
                <header class="mobile-top-bar">
                    <div class="mobile-brand">
                        <button class="mobile-sidebar-toggle" @click="mobileSidebarOpen = true">
                            <Menu :size="22" color="#334155" :stroke-width="2.5" />
                        </button>
                        <div class="ds-brand-icon" :class="{ 'has-logo': $page.props.global_settings?.platform_logo }" style="width: 28px; height: 28px; border-radius: 6px;">
                            <img v-if="$page.props.global_settings?.platform_logo" :src="$page.props.global_settings?.platform_logo" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 6px;" />
                            <Zap
                                v-else
                                :size="16"
                                color="#fff"
                                fill="white"
                                :stroke-width="2"
                            />
                        </div>
                        <span class="mobile-brand-text">{{
                            $page.props.global_settings?.platform_name ||
                            "Geniuss"
                        }}</span>
                    </div>
                    <div
                        class="mobile-user-trigger"
                        @click.stop="dropdownOpen = !dropdownOpen"
                    >
                        <div class="avatar-chunky mobile-avatar">
                            {{ user.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>

                    <Transition name="dd">
                        <div
                            v-if="dropdownOpen"
                            class="duo-dropdown mobile-dd"
                            @click.stop
                        >
                            <div class="dd-profile-info">
                                <div class="dd-avatar-big">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="dd-text">
                                    <div class="dd-name">{{ user.name }}</div>
                                    <div class="dd-class">
                                        Kelas {{ user.class?.name }}
                                    </div>
                                </div>
                            </div>
                            <div class="dd-divider"></div>
                            <button class="dd-logout-btn" @click="logout">
                                <LogOut :size="16" :stroke-width="2.5" />
                                KELUAR
                            </button>
                        </div>
                    </Transition>
                </header>

                <header class="path-header">
                    <h1 class="path-title">Materi Pembelajaran</h1>
                    <p class="path-subtitle">
                        Selesaikan setiap modul untuk memperkuat kemampuanmu!
                    </p>
                </header>

                <div class="mobile-stats-card">
                    <div class="mobile-progress-section">
                        <div class="duo-progress-wrapper">
                            <div class="duo-progress-track">
                                <div
                                    class="duo-progress-bar"
                                    :style="{ width: progressPct + '%' }"
                                >
                                    <div class="progress-shine"></div>
                                </div>
                            </div>
                            <span class="duo-progress-text"
                                >{{ progressPct }}%</span
                            >
                        </div>
                    </div>
                    <div class="mobile-stats-grid">
                        <div class="m-stat-box box-xp">
                            <Zap :size="14" fill="currentColor" />
                            <span>{{ learningModules.length }} Modul</span>
                        </div>
                        <div class="m-stat-box box-streak">
                            <FlameKindling :size="14" />
                            <span>{{ inProgress }} Progres</span>
                        </div>
                        <div class="m-stat-box box-crown">
                            <Award :size="14" />
                            <span>{{ totalFinished }} Selesai</span>
                        </div>
                    </div>
                </div>

                <!-- ░░ TAB FILTER SWITCHER (Chunky Duolingo Style) ░░ -->
                <div class="duo-tabs-container">
                    <button
                        class="duo-tab-pill"
                        :class="{ active: activeFilter === 'all' }"
                        @click="activeFilter = 'all'"
                    >
                        <BookOpen :size="16" :stroke-width="3" />
                        <span>Semua Modul</span>
                    </button>
                    <button
                        class="duo-tab-pill"
                        :class="{ active: activeFilter === 'class' }"
                        @click="activeFilter = 'class'"
                    >
                        <GraduationCap :size="16" :stroke-width="3" />
                        <span>Modul Kelas {{ user.class?.name || '-' }}</span>
                    </button>
                    <button
                        class="duo-tab-pill"
                        :class="{ active: activeFilter === 'general' }"
                        @click="activeFilter = 'general'"
                    >
                        <Star :size="16" :stroke-width="3" />
                        <span>Modul Umum</span>
                    </button>
                </div>

                <div class="missions-scroll-viewport">
                    <div class="missions-grid-layout">
                        <div
                            v-for="(mod, i) in filteredModules"
                            :key="mod.id"
                            class="image-chunky-card"
                        >
                            <div class="mission-photo-area">
                                <img
                                    v-if="mod.thumbnail"
                                    :src="
                                        mod.thumbnail.startsWith('http')
                                            ? mod.thumbnail
                                            : `/storage/${mod.thumbnail}`
                                    "
                                    :alt="mod.name"
                                    class="mission-uploaded-img"
                                />
                                <div
                                    v-else
                                    class="mission-img-fallback"
                                    :style="{
                                        backgroundColor: accent(i) + '20',
                                        color: accent(i),
                                    }"
                                >
                                    <ImageIcon :size="32" :stroke-width="1.5" />
                                </div>
                            </div>

                            <div class="mission-card-center-info">
                                <h3 class="mission-box-title">
                                    {{ mod.name }}
                                </h3>
                                <p class="mission-box-desc">
                                    {{ mod.description }}
                                </p>

                                <div
                                    v-if="mod.has_attempt"
                                    class="mission-box-score"
                                >
                                    <Star
                                        :size="14"
                                        fill="currentColor"
                                        :stroke-width="0"
                                    />
                                    <span
                                        >Skor terbaik:
                                        <strong>{{
                                            mod.best_score
                                        }}</strong></span
                                    >
                                </div>
                            </div>

                            <div class="mission-card-bottom-action">
                                <button
                                    class="chunky-full-btn"
                                    :class="{
                                        'btn-start':
                                            !mod.has_attempt &&
                                            !mod.fully_completed,
                                        'btn-continue':
                                            mod.has_attempt &&
                                            !mod.fully_completed,
                                        'btn-restart': mod.fully_completed,
                                    }"
                                    @click="goToModule(mod)"
                                >
                                    <Eye v-if="mod.fully_completed" :size="20" :stroke-width="3" />
                                    <span v-else class="btn-circle-indicator"></span>
                                    <span>{{
                                        mod.fully_completed ? 'LIHAT' : statusLabel(mod).toUpperCase()
                                    }}</span>
                                </button>
                            </div>
                        </div>

                        <div
                            v-if="learningModules.length === 0"
                            class="empty-path-card"
                        >
                            <BookOpen
                                :size="40"
                                color="#94a3b8"
                                :stroke-width="2"
                            />
                            <h3>Belum ada modul tersedia</h3>
                            <p>
                                Modul belajar akan segera ditambahkan oleh guru
                                kamu.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <aside class="duo-sidebar-right">
            <div class="chunky-right-card">
                <div class="stats-header-title">
                    <Target :size="16" :stroke-width="2.5" />
                    <span>PROGRESS BELAJAR</span>
                </div>
                <div class="duo-progress-wrapper">
                    <div class="duo-progress-track">
                        <div
                            class="duo-progress-bar"
                            :style="{ width: progressPct + '%' }"
                        >
                            <div class="progress-shine"></div>
                        </div>
                    </div>
                    <span class="duo-progress-text">{{ progressPct }}%</span>
                </div>
            </div>

            <div class="stats-vertical-stack">
                <div class="chunky-stat-box box-xp">
                    <Zap :size="18" fill="currentColor" :stroke-width="0" />
                    <div class="stat-box-info">
                        <span class="stat-count">{{
                            learningModules.length
                        }}</span>
                        <span class="stat-label">Total Modul</span>
                    </div>
                </div>

                <div class="chunky-stat-box box-streak">
                    <FlameKindling :size="18" :stroke-width="2.5" />
                    <div class="stat-box-info">
                        <span class="stat-count">{{ inProgress }}</span>
                        <span class="stat-label">Dikerjakan</span>
                    </div>
                </div>

                <div class="chunky-stat-box box-crown">
                    <Award :size="18" :stroke-width="2.5" />
                    <div class="stat-box-info">
                        <span class="stat-count">{{ totalFinished }}</span>
                        <span class="stat-label">Selesai</span>
                    </div>
                </div>
            </div>

            <div class="chunky-right-card class-info-card">
                <div class="class-badge-icon">
                    <GraduationCap :size="20" :stroke-width="2.5" />
                </div>
                <div class="class-badge-details">
                    <span class="class-label-top">KELAS SAYA</span>
                    <span class="class-name-text">{{
                        user.class?.name || "-"
                    }}</span>
                </div>
            </div>
        </aside>
    </div>

    <Teleport to="body">
        <div
            v-if="showModal"
            class="modal-overlay"
            :class="{ 'modal-overlay-visible': modalVisible }"
            @click.self="closeModal"
        >
            <div
                class="modal-card"
                :class="{ 'modal-card-visible': modalVisible }"
            >
                <div class="modal-icon-wrap mi-restart">
                    <RotateCcw :size="48" color="#ffffff" :stroke-width="2.5" />
                </div>
                
                <h2 class="modal-title">{{ selectedModule?.name }}</h2>
                <p class="modal-desc">
                    Modul ini sudah kamu selesaikan. Apakah kamu ingin berlatih lagi?
                </p>

                <div class="modal-stack-btn">
                    <button class="mcta-primary mcta-restart" @click="restartModule">
                        MULAI ULANG
                    </button>
                    <button class="mcta-secondary" @click="closeModal">
                        NANTI SAJA
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.duo-layout {
    min-height: 100dvh;
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #bae6fd 100%);
    display: flex;
    font-family: "Nunito", sans-serif;
    color: #334155;
    opacity: 0;
    transition: opacity 0.2s ease;
    position: relative;
    overflow: hidden;
}
.layout--ready {
    opacity: 1;
}

.bg-particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 1;
}
.particle {
    position: absolute;
    background: rgba(56, 189, 248, 0.15);
    border-radius: 35% 65% 70% 30% / 50% 30% 70% 50%;
    animation: floatParticle 8s infinite ease-in-out;
}
.p-1 {
    width: 80px;
    height: 80px;
    top: 15%;
    left: 20%;
    animation-delay: 0s;
}
.p-2 {
    width: 120px;
    height: 120px;
    bottom: 10%;
    left: 40%;
    border-radius: 60% 40% 30% 70%;
    animation-delay: 2s;
}
.p-3 {
    width: 60px;
    height: 60px;
    top: 40%;
    right: 25%;
    animation-delay: 4s;
}
.p-4 {
    width: 90px;
    height: 90px;
    top: 8%;
    right: 45%;
    border-radius: 40% 60% 50% 50%;
    animation-delay: 1s;
}
.p-5 {
    width: 70px;
    height: 70px;
    bottom: 30%;
    left: 15%;
    animation-delay: 5s;
}

@keyframes floatParticle {
    0%,
    100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-15px) rotate(15deg);
    }
}

.dsk-sidebar-left {
    position: fixed;
    top: 16px;
    left: 16px;
    bottom: 16px;
    width: 228px;
    z-index: 50;
    display: flex;
    flex-direction: column;
}
.dsk-sidebar-inner {
    height: 100%;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    box-shadow: 0 4px 0 0 #e2e8f0;
    padding: 20px 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.ds-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0 4px 12px;
    border-bottom: 2px solid #f1f5f9;
}
.ds-brand-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #1cb0f6;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 0 0 #1899d6;
    flex-shrink: 0;
}
.ds-brand-icon.has-logo {
    background: transparent;
    box-shadow: none;
}
.ds-brand-name {
    font-size: 20px;
    font-weight: 900;
    color: #1cb0f6;
    text-transform: uppercase;
    letter-spacing: -0.5px;
}
.ds-sep {
    height: 2px;
    background: #f1f5f9;
    border-radius: 2px;
    margin: 4px 0;
}
.ds-label {
    font-size: 11px;
    font-weight: 900;
    color: #94a3b8;
    letter-spacing: 1px;
    padding: 0 6px;
}
.ds-nav-btn {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    width: 100%;
    text-align: left;
    background: transparent;
    border: 2px solid transparent;
    border-radius: 14px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    cursor: pointer;
    text-transform: uppercase;
    transition: background 0.15s;
}
.ds-nav-btn:hover {
    background: #f8fafc;
    border-color: #f1f5f9;
}
.ds-nav-active {
    background: #ddf4ff !important;
    border-color: #84d8ff !important;
    color: #1cb0f6 !important;
}
.ds-spacer {
    flex: 1;
}

.ds-music-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 14px;
    border-radius: 14px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 900;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.15s;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-bottom: 4px solid #cbd5e1;
    color: #64748b;
}
.ds-music-btn:hover {
    filter: brightness(0.95);
}
.ds-music-btn:active {
    transform: translateY(2px);
    border-bottom-width: 2px;
}
.ds-music-btn.on {
    background: #fff7ed;
    border-color: #fdba74;
    border-bottom-color: #fb923c;
    color: #ea580c;
}

.ds-user {
    position: relative;
}
.ds-user-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px;
    width: 100%;
    border-radius: 16px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-bottom: 4px solid #cbd5e1;
    cursor: pointer;
    text-align: left;
    transition: all 0.15s;
}
.ds-user-pill:hover {
    background: #f8fafc;
}
.ds-user-pill.open {
    border-bottom-width: 2px;
    transform: translateY(2px);
    background: #f1f5f9;
}
.ds-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #38bdf8;
    color: white;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
}
.ds-avatar-lg {
    width: 48px;
    height: 48px;
    font-size: 20px;
}
.ds-uname {
    font-size: 14px;
    font-weight: 800;
    color: #334155;
    flex: 1;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.ds-dropdown {
    position: absolute;
    bottom: calc(100% + 10px);
    left: 0;
    width: 240px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
    box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
    z-index: 60;
}
.ds-dd-profile {
    display: flex;
    align-items: center;
    gap: 12px;
}
.ds-dd-text {
    flex: 1;
    min-width: 0;
}
.ds-dd-name {
    font-weight: 800;
    color: #1e293b;
    font-size: 15px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.ds-dd-class {
    font-size: 13px;
    color: #64748b;
    font-weight: 700;
}
.ds-dd-sep {
    height: 2px;
    background: #f1f5f9;
    margin: 14px 0;
}
.ds-dd-logout {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    background: transparent;
    color: #ef4444;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.15s;
}
.ds-dd-logout:hover {
    background: #fef2f2;
    border-color: #fca5a5;
}

.t-dropdown-enter-active,
.t-dropdown-leave-active {
    transition: opacity 0.2s, transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.t-dropdown-enter-from,
.t-dropdown-leave-to {
    opacity: 0;
    transform: translateY(10px) scale(0.95);
}

.duo-main-content {
    flex: 1;
    margin-left: 272px;
    margin-right: 336px;
    padding: 32px 16px;
    display: flex;
    justify-content: center;
    height: 100dvh;
    z-index: 5;
}
.learning-path-container {
    width: 100%;
    max-width: 640px;
    display: flex;
    flex-direction: column;
}
.path-header {
    text-align: center;
    margin-bottom: 24px;
    background: rgba(255, 255, 255, 0.8);
    border: 2px solid #cbd5e1;
    padding: 16px;
    border-radius: 16px;
    box-shadow: 0 4px 0 0 #cbd5e1;
}
.path-title {
    font-size: 24px;
    font-weight: 900;
    color: #1e293b;
}
.path-subtitle {
    font-size: 14px;
    font-weight: 700;
    color: #64748b;
}

.missions-scroll-viewport {
    flex: 1;
    overflow-y: auto;
    padding-right: 6px;
    padding-bottom: 40px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.missions-scroll-viewport::-webkit-scrollbar {
    width: 6px;
}
.missions-scroll-viewport::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.missions-grid-layout {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.image-chunky-card {
    display: flex;
    flex-direction: column;
    background: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 0px 0px transparent;
    transition:
        transform 0.15s ease,
        box-shadow 0.15s ease;
    text-align: center;
    align-items: center;
}
.image-chunky-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
}

.mission-photo-area {
    width: 100%;
    height: 120px;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 16px;
    flex-shrink: 0;
}
.mission-uploaded-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.mission-img-fallback {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
}

.mission-card-center-info {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    flex: 1;
    margin-bottom: 20px;
}
.mission-box-title {
    font-size: 20px;
    font-weight: 800;
    color: #2e2e2e;
    line-height: 1.2;
}
.mission-box-desc {
    font-size: 14px;
    font-weight: 600;
    color: #777777;
    line-height: 1.4;
}
.mission-box-score {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 800;
    color: #e67e00;
    background: #ffebd0;
    padding: 4px 14px;
    border-radius: 50px;
    margin-top: 4px;
}

.mission-card-bottom-action {
    width: 100%;
}
.chunky-full-btn {
    width: 100%;
    height: 48px;
    border: none;
    border-radius: 16px;
    font-family: inherit;
    font-size: 15px;
    font-weight: 900;
    letter-spacing: 0.8px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.05s ease;
    position: relative;
    top: 0;
}
.chunky-full-btn:active {
    top: 4px;
    box-shadow: 0 0 0 0 transparent !important;
}

.btn-circle-indicator {
    width: 14px;
    height: 14px;
    background-color: #ffffff;
    border-radius: 50%;
    display: inline-block;
}

.btn-start {
    background: #1cb0f6;
    box-shadow: 0 4px 0 0 #0284c7;
    color: #ffffff;
}
.btn-start:hover {
    background: #0ea5e9;
}

.btn-continue {
    background: #ff9600;
    box-shadow: 0 4px 0 0 #ea580c;
    color: #ffffff;
}
.btn-continue:hover {
    background: #ea580c;
}

.btn-restart {
    background: #a855f7;
    box-shadow: 0 4px 0 0 #7e22ce;
    color: #ffffff;
}
.btn-restart:hover {
    background: #9333ea;
}
.btn-restart:active {
    top: 4px;
    box-shadow: 0 0 0 0 #7e22ce !important;
}

.empty-path-card {
    grid-column: span 2;
    text-align: center;
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 18px;
    padding: 40px;
    color: #64748b;
}

.duo-sidebar-right {
    position: fixed;
    top: 16px;
    right: 16px;
    bottom: 16px;
    width: 300px;
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid #cbd5e1;
    border-radius: 20px;
    padding: 20px 14px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 10;
    box-shadow: 0 6px 0 0 #cbd5e1;
}

.chunky-right-card {
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    padding: 12px;
    background: #ffffff;
}
.stats-header-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 900;
    color: #475569;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.duo-progress-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
}
.duo-progress-track {
    flex: 1;
    height: 12px;
    background-color: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    position: relative;
}
.duo-progress-bar {
    height: 100%;
    background: #22c55e;
    border-radius: 10px;
    position: relative;
    transition: width 0.4s ease;
}
.progress-shine {
    position: absolute;
    top: 2px;
    left: 3px;
    right: 3px;
    height: 3px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 10px;
}
.duo-progress-text {
    font-size: 13px;
    font-weight: 900;
    color: #22c55e;
}

.stats-vertical-stack {
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.chunky-stat-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    background: #ffffff;
}
.stat-box-info {
    display: flex;
    flex-direction: column;
}
.stat-count {
    font-size: 15px;
    font-weight: 900;
    color: #1e293b;
    line-height: 1.2;
}
.stat-label {
    font-size: 11px;
    font-weight: 700;
    color: #94a3b8;
}

.box-xp {
    color: #f97316;
    border-color: #ffedd5;
    background: #fffaf5;
}
.box-streak {
    color: #ef4444;
    border-color: #fee2e2;
    background: #fffafb;
}
.box-crown {
    color: #eab308;
    border-color: #fef9c3;
    background: #fffdf5;
}

.class-info-card {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    border-color: #e2e8f0;
}
.class-badge-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    background: #a855f7;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 0 0 #7e22ce;
    flex-shrink: 0;
}
.class-badge-details {
    display: flex;
    flex-direction: column;
}
.class-label-top {
    font-size: 10px;
    font-weight: 800;
    color: #94a3b8;
}
.class-name-text {
    font-size: 15px;
    font-weight: 900;
    color: #334155;
}

/* 📱 ELEMENT KHUSUS MOBILE (Default Tersembunyi di Desktop) */
.mobile-top-bar,
.mobile-stats-card,
.mobile-bottom-nav,
.mobile-sidebar-close {
    display: none;
}

@media (max-width: 1140px) {
    .duo-sidebar-right {
        display: none !important;
    }
    .duo-main-content {
        margin-right: 0px;
    }
}

@media (max-width: 900px) {
    .missions-grid-layout {
        grid-template-columns: 1fr;
    }
    .empty-path-card {
        grid-column: span 1;
    }
}

/* ════════════════════════════════════════════════
   📱 VERSI MOBILE SECARA MENYELURUH (Clean & Minimalis)
   ════════════════════════════════════════════════ */
@media (max-width: 768px) {
    .duo-layout {
        flex-direction: column;
    }

    .dsk-sidebar-left {
        transform: translateX(-150%);
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex !important;
        z-index: 200;
        top: 0;
        bottom: 0;
        left: 0;
        height: 100dvh;
        border-radius: 0;
    }
    
    .dsk-sidebar-left.mobile-open {
        transform: translateX(0);
        box-shadow: 4px 0 24px rgba(0,0,0,0.15);
    }

    .dsk-sidebar-inner {
        border-radius: 0;
        border-left: none;
        border-top: none;
        border-bottom: none;
    }

    .mobile-sidebar-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.5);
        z-index: 190;
        backdrop-filter: blur(2px);
    }

    .duo-main-content {
        margin-left: 0px;
        margin-right: 0px;
        padding: 72px 16px 80px;
        height: auto;
        min-height: 100dvh;
        overflow-y: visible;
    }
    .missions-scroll-viewport {
        overflow-y: visible;
        padding-bottom: 0px;
    }

    /* Top Bar Mobile & Pembenahan Dropdown Profil */
    .mobile-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        height: 56px;
        background: rgba(255, 255, 255, 0.96);
        border-bottom: 1px solid #e2e8f0;
        padding: 0 16px;
        z-index: 150;
    }
    .mobile-brand {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mobile-brand-text {
        font-size: 18px;
        font-weight: 900;
        color: #0ea5e9;
        text-transform: uppercase;
    }
    .mobile-user-trigger {
        cursor: pointer;
        padding: 4px;
    }
    .mobile-avatar {
        width: 32px;
        height: 32px;
        font-size: 13px;
        border-radius: 50%;
        background: #38bdf8;
        color: white;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mobile-sidebar-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        margin-right: 4px;
    }
    
    .mobile-sidebar-close {
        display: flex;
        align-items: center;
        justify-content: center;
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
        margin-left: auto;
    }

    /* Set Kontainer Dropdown Mobile agar Tepat di Bawah Avatar */
    .mobile-dd {
        position: absolute;
        top: 60px;
        bottom: auto;
        left: auto;
        right: 16px;
        width: 220px;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        border-radius: 16px;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        padding: 16px;
        z-index: 200;
    }
    
    .dd-profile-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .dd-avatar-big {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: #38bdf8;
        color: white;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    
    .dd-text {
        flex: 1;
        min-width: 0;
    }
    
    .dd-name {
        font-weight: 800;
        color: #1e293b;
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .dd-class {
        font-size: 12px;
        color: #64748b;
        font-weight: 700;
    }
    
    .dd-divider {
        height: 2px;
        background: #f1f5f9;
        margin: 12px 0;
    }
    
    .dd-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        padding: 10px;
        border-radius: 12px;
        border: 2px solid #e2e8f0;
        background: transparent;
        color: #ef4444;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s;
    }
    
    .dd-logout-btn:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    /* Perampingan Path Header */
    .path-header {
        padding: 12px;
        margin-bottom: 16px;
        box-shadow: 0 3px 0 0 #cbd5e1;
    }
    .path-title {
        font-size: 20px;
    }
    .path-subtitle {
        font-size: 13px;
    }

    /* Banner Statistik Ringkas di Mobile */
    .mobile-stats-card {
        display: flex;
        flex-direction: column;
        gap: 10px;
        background: #ffffff;
        border: 2px solid #cbd5e1;
        border-radius: 16px;
        padding: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 0 0 #cbd5e1;
    }
    .mobile-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }
    .m-stat-box {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 6px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 800;
        border: 1px solid transparent;
    }

    /* Navigasi Bawah Lebih Tipis, Bersih, dan Elegan (Minimalis) */
    .mobile-bottom-nav {
        display: block;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        height: 52px;
        background: #ffffff;
        border-top: 1px solid #e2e8f0;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.04);
        z-index: 150;
    }
    .mobile-menu-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        height: 100%;
        max-width: 480px;
        margin: 0 auto;
    }
    .mobile-menu-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 2px;
        color: #64748b;
        font-size: 10px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .mobile-menu-item.active {
        color: #0ea5e9;
    }

    /* Style Khusus Tombol Musik Terintegrasi */
    .music-toggle-item {
        color: #64748b;
    }
    .music-toggle-item.music-on {
        color: #f57c00;
        font-weight: 900;
    }

    /* Optimalisasi Grid Card Modul agar Lebih Bersih */
    .missions-grid-layout {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    .image-chunky-card {
        padding: 16px;
        border-radius: 20px;
    }
    .mission-photo-area {
        height: 100px;
        margin-bottom: 12px;
    }
    .mission-box-title {
        font-size: 18px;
    }
    .mission-box-desc {
        font-size: 13px;
    }
}
/* ════════════════════════════════
   MODAL
════════════════════════════════ */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 500;
    background: rgba(15, 23, 42, 0.5);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    padding: 0;
    opacity: 0;
    transition: opacity 0.3s ease;
}
.modal-overlay-visible {
    opacity: 1;
}

@media (min-width: 768px) {
    .modal-overlay {
        align-items: center;
        padding: 24px;
    }
}

.modal-card {
    background: #ffffff;
    border-radius: 32px 32px 0 0;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.12);
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 24px 24px;
    position: relative;
    transform: translateY(100%);
    transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@media (min-width: 768px) {
    .modal-card {
        border-radius: 32px;
        box-shadow: 0 24px 64px rgba(0, 0, 0, 0.15);
        transform: scale(0.9) translateY(20px);
    }
}

.modal-card-visible {
    transform: translateY(0);
}
@media (min-width: 768px) {
    .modal-card-visible {
        transform: scale(1) translateY(0);
    }
}

.modal-icon-wrap {
    width: 96px;
    height: 96px;
    border-radius: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 24px;
    box-shadow: 0 8px 0 0 rgba(0,0,0,0.08);
}
.mi-restart {
    background: #a855f7;
    box-shadow: 0 8px 0 0 #7e22ce;
}
.mi-start {
    background: #1cb0f6;
    box-shadow: 0 8px 0 0 #0284c7;
}

.modal-title {
    font-family: "Righteous", cursive;
    font-size: 26px;
    font-weight: 900;
    color: #334155;
    text-align: center;
    margin-bottom: 12px;
}
.modal-desc {
    font-size: 15px;
    font-weight: 700;
    color: #64748b;
    text-align: center;
    line-height: 1.5;
    margin-bottom: 32px;
}

.modal-stack-btn {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
}
.mcta-primary {
    height: 52px;
    border: none;
    border-radius: 16px;
    font-family: "Nunito", sans-serif;
    font-size: 15px;
    font-weight: 900;
    letter-spacing: 0.8px;
    cursor: pointer;
    position: relative;
    top: 0;
    color: #fff;
    transition: top 0.1s, box-shadow 0.1s;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.mcta-primary:active {
    top: 5px;
    box-shadow: 0 0 0 0 transparent !important;
}

.mcta-restart {
    background: #a855f7;
    box-shadow: 0 5px 0 0 #7e22ce;
}
.mcta-danger {
    background: #ef4444;
    box-shadow: 0 5px 0 0 #b91c1c;
}
.mcta-start {
    background: #1cb0f6;
    box-shadow: 0 5px 0 0 #0284c7;
}

.mcta-secondary {
    height: 52px;
    background: transparent;
    border: none;
    color: #a855f7;
    font-family: "Nunito", sans-serif;
    font-size: 15px;
    font-weight: 900;
    letter-spacing: 0.5px;
    cursor: pointer;
    width: 100%;
}
.mcta-secondary:active {
    opacity: 0.7;
}

/* ════════════════════════════════
   TAB FILTER SWITCHER (Chunky Duolingo Style)
   ════════════════════════════════ */
.duo-tabs-container {
    display: flex;
    gap: 12px;
    margin-bottom: 24px;
    flex-wrap: wrap;
    padding: 0 4px;
}
.duo-tab-pill {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 10px 18px;
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 850;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    box-shadow: 0 4px 0 0 #cbd5e1;
    position: relative;
    top: 0;
    transition: all 0.1s ease;
}
.duo-tab-pill:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.duo-tab-pill:active {
    top: 3px;
    box-shadow: 0 1px 0 0 #cbd5e1;
}
.duo-tab-pill.active {
    background: #e0f2fe;
    border-color: #0ea5e9;
    color: #0369a1;
    box-shadow: 0 4px 0 0 #0284c7;
}
.duo-tab-pill.active:active {
    top: 3px;
    box-shadow: 0 1px 0 0 #0284c7;
}
</style>
