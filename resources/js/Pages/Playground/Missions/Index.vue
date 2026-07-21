<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import {
    ChevronLeft,
    Music2,
    VolumeX,
    LogOut,
    Target,
    Award,
    FlameKindling,
    GraduationCap,
    BookOpen,
    Lock,
    Star,
    Zap,
    Home,
    Trophy,
    Check,
    RotateCcw,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";
import { useMusic } from "@/Composable/useMusic";

const { musicOn, initAutoMusic, toggleMusic, destroyAudio } = useMusic();

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
    module: {
        type: Object,
        default: () => ({
            id: null,
            name: "Misi Pembelajaran",
            description: "Selesaikan setiap misi untuk memperkuat kemampuanmu!",
        }),
    },
    missions: { type: Array, default: () => [] },
    all_missions_done: { type: Boolean, default: false },
    backsound: { type: String, default: null },
    has_pretest: { type: Boolean, default: false },
    pretest_done: { type: Boolean, default: false },
    has_posttest: { type: Boolean, default: false },
    posttest_done: { type: Boolean, default: false },
});

const ready = ref(false);
const dropdownOpen = ref(false);
const showModal = ref(false);
const modalVisible = ref(false);
const selectedMission = ref(null);

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    setTimeout(() => initAutoMusic(null), 100);
});
onUnmounted(() => destroyAudio());

// Di Index.vue
const goBack = () => {
    // Gunakan playground.index jika itu adalah halaman daftar modul utama siswa
    router.get(
        route("playground.index"),
        {},
        {
            replace: true,
        },
    );
};

const logout = () => router.post(route("playground.logout"));

const isMissionLocked = (i) => {
    if (props.has_pretest && !props.pretest_done) return true;
    if (i === 0) return false;
    return props.missions[i - 1].status !== "completed";
};

const selectedType = ref('mission'); // 'mission' | 'pretest' | 'posttest'

const openModal = (mission, i) => {
    if (isMissionLocked(i)) return;
    selectedType.value = 'mission';
    selectedMission.value = mission;
    showModal.value = true;
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            modalVisible.value = true;
        });
    });
};

const openPretestModal = () => {
    selectedType.value = 'pretest';
    selectedMission.value = {
        name: 'Pre-test',
        description: 'Uji kemampuan awalmu sebelum memulai misi belajar!',
        status: props.pretest_done ? 'completed' : 'not_started',
    };
    showModal.value = true;
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            modalVisible.value = true;
        });
    });
};

const openPosttestModal = () => {
    if (!props.all_missions_done && !props.posttest_done) return;
    selectedType.value = 'posttest';
    selectedMission.value = {
        name: 'Post-test',
        description: 'Uji kemampuan akhirmu setelah menyelesaikan seluruh misi belajar!',
        status: props.posttest_done ? 'completed' : 'not_started',
    };
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
        selectedMission.value = null;
        selectedType.value = 'mission';
    }, 320);
};

const startMission = () => {
    if (!selectedMission.value) return;
    handlePrimaryAction();
};

const handlePrimaryAction = () => {
    if (selectedType.value === 'pretest') {
        closeModal();
        setTimeout(() => router.visit(route("playground.pretest.show", props.module.id)), 150);
    } else if (selectedType.value === 'posttest') {
        closeModal();
        setTimeout(() => router.visit(route("playground.posttest.show", props.module.id)), 150);
    } else {
        const id = selectedMission.value.id;
        closeModal();
        setTimeout(() => router.visit(route("playground.missions.show", id)), 150);
    }
};

const handleRestart = () => {
    if (selectedType.value === 'pretest') {
        closeModal();
        setTimeout(() => router.visit(route("playground.pretest.show", { module: props.module.id, restart: 'true' })), 150);
    } else if (selectedType.value === 'posttest') {
        closeModal();
        setTimeout(() => router.visit(route("playground.posttest.show", { module: props.module.id, restart: 'true' })), 150);
    } else {
        handlePrimaryAction();
    }
};


const goToMissionResult = () => {
    if (!selectedMission.value) return;
    router.visit(route("playground.missions.result", selectedMission.value.id));
};

const showLockedAlert = () => {
    setTimeout(() => {
        const stageBtns = document.querySelectorAll(".st-locked");
        stageBtns.forEach((btn) => {
            btn.style.transform = "translateX(5px)";
            setTimeout(() => (btn.style.transform = "translateX(-5px)"), 100);
            setTimeout(() => (btn.style.transform = "translateX(5px)"), 200);
            setTimeout(() => (btn.style.transform = "translateX(0)"), 300);
        });
    }, 320);
};

const goToPosttest = () =>
    router.visit(route("playground.posttest.show", props.module.id));

const totalMissions = computed(() => props.missions?.length || 0);
const completedMissions = computed(
    () => props.missions?.filter((m) => m.status === "completed").length || 0,
);
const inProgressMissions = computed(
    () => props.missions?.filter((m) => m.status === "in_progress").length || 0,
);
const progressPct = computed(() =>
    totalMissions.value
        ? Math.round((completedMissions.value / totalMissions.value) * 100)
        : 0,
);

const ZIGZAG = [0, 60, 90, 60, 0, -60, -90, -60];
const getOffset = (i) => ZIGZAG[i % ZIGZAG.length];

const COLORS = [
    { bg: "#1cb0f6", sh: "#1899d6" },
    { bg: "#58cc02", sh: "#3f9402" },
    { bg: "#ff9600", sh: "#cc7800" },
    { bg: "#a435f0", sh: "#7c28b0" },
    { bg: "#ff4b4b", sh: "#cc3838" },
    { bg: "#00c9b1", sh: "#009e8a" },
];
const getColor = (i) => COLORS[i % COLORS.length];

const modalAccent = computed(() => {
    if (!selectedMission.value) return "#1cb0f6";
    return selectedMission.value.status === "completed"
        ? "#58cc02"
        : selectedMission.value.status === "in_progress"
          ? "#ff9600"
          : "#1cb0f6";
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

    <div class="app" :class="{ ready }">
        <div class="bg-particles">
            <div class="particle p-1"></div>
            <div class="particle p-2"></div>
            <div class="particle p-3"></div>
            <div class="particle p-4"></div>
            <div class="particle p-5"></div>
        </div>

        <aside class="dsk-sidebar-left">
            <div class="dsk-sidebar-inner">
                <div class="ds-brand">
                    <div
                        class="ds-brand-icon"
                        :class="{
                            'has-logo':
                                $page.props.global_settings?.platform_logo,
                        }"
                    >
                        <img
                            v-if="$page.props.global_settings?.platform_logo"
                            :src="$page.props.global_settings?.platform_logo"
                            alt="Logo"
                            style="
                                width: 100%;
                                height: 100%;
                                object-fit: contain;
                                border-radius: 10px;
                            "
                        />
                        <Zap
                            v-else
                            :size="18"
                            color="#fff"
                            fill="white"
                            :stroke-width="2"
                        />
                    </div>
                    <span class="ds-brand-name">{{
                        $page.props.global_settings?.platform_name || "Geniuss"
                    }}</span>
                </div>

                <div class="ds-sep"></div>
                <p class="ds-label">NAVIGASI</p>

                <button class="ds-nav-btn" @click="goBack">
                    <ChevronLeft :size="17" :stroke-width="3" />
                    <span>Kembali</span>
                </button>

                <div
                    class="ds-nav-btn ds-nav-active"
                    style="pointer-events: none"
                >
                    <Target :size="17" :stroke-width="3" />
                    <span>MISI</span>
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
                        <ChevronLeft
                            :size="14"
                            :stroke-width="3.5"
                            :style="{
                                marginLeft: 'auto',
                                transform: dropdownOpen
                                    ? 'rotate(90deg)'
                                    : 'rotate(-90deg)',
                                transition: 'transform .2s',
                            }"
                        />
                    </button>
                    <Transition name="t-dropdown">
                        <div
                            v-if="dropdownOpen"
                            class="ds-dropdown"
                            @click.stop
                        >
                            <div class="ds-dd-profile">
                                <div class="ds-avatar ds-avatar-lg">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="ds-dd-name">
                                        {{ user.name }}
                                    </div>
                                    <div class="ds-dd-class">
                                        Kelas {{ user.class }}
                                    </div>
                                </div>
                            </div>
                            <div class="ds-dd-sep"></div>
                            <button class="ds-dd-logout" @click="logout">
                                <LogOut :size="15" :stroke-width="3" /> KELUAR
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </aside>

        <main class="main-scroll">
            <header class="mob-topbar">
                <button class="mob-back" @click="goBack">
                    <ChevronLeft :size="22" :stroke-width="3" />
                </button>
                <div class="mob-topbar-center">
                    <span class="mob-topbar-title">{{ module.name }}</span>
                    <div class="mob-mini-progress">
                        <div class="mob-mini-track">
                            <div
                                class="mob-mini-fill"
                                :style="{ width: progressPct + '%' }"
                            ></div>
                        </div>
                        <span class="mob-mini-pct">{{ progressPct }}%</span>
                    </div>
                </div>
                <div class="mob-topbar-right">
                    <div class="mob-stat-pill pill-flame">
                        <FlameKindling :size="15" :stroke-width="3" />
                        <span>{{ inProgressMissions }}</span>
                    </div>
                </div>
            </header>

            <div class="path-container">
                <div class="unit-banner">
                    <div class="unit-banner-left">
                        <span class="unit-subtitle">MODUL PEMBELAJARAN</span>
                        <h1 class="unit-title">{{ module.name }}</h1>
                        <p class="unit-desc">{{ module.description }}</p>
                    </div>
                    <div class="unit-banner-right">
                        <BookOpen
                            :size="48"
                            color="#ffffff"
                            :stroke-width="1.5"
                        />
                    </div>
                </div>

                <Transition name="t-pop">
                    <div v-if="all_missions_done" class="done-banner">
                        <div class="done-icon-wrap">
                            <Trophy
                                :size="24"
                                color="#16a34a"
                                :stroke-width="2.5"
                            />
                        </div>
                        <div class="done-text">
                            <strong>Semua misi selesai!</strong>
                            <span>Kamu siap untuk posttest.</span>
                        </div>
                        <button class="done-btn" @click="goToPosttest">
                            MULAI POSTTEST
                        </button>
                    </div>
                </Transition>

                <div class="mission-path">
                    <!-- PRE-TEST NODE -->
                    <div v-if="has_pretest" class="stage-wrapper">
                        <div class="stage-row" :style="{ '--offset': '-80px' }">
                            <button
                                class="stage-btn"
                                :class="{
                                    'st-completed': pretest_done,
                                    'st-current': !pretest_done
                                }"
                                :style="pretest_done ? { '--c': '#a855f7', '--s': '#7e22ce' } : { '--c': '#1cb0f6', '--s': '#0284c7' }"
                                @click="openPretestModal"
                            >
                                <RotateCcw v-if="pretest_done" :size="36" color="white" :stroke-width="3" />
                                <span v-else class="stage-num">P</span>
                            </button>
                            <div class="stage-label">Pre-test</div>
                        </div>
                        <div v-if="missions.length > 0" class="connector" :style="{ '--off-a': '-80px', '--off-b': getOffset(0) + 'px' }">
                            <div class="conn-dot" :class="{ 'conn-done': pretest_done }"></div>
                            <div class="conn-dot" :class="{ 'conn-done': pretest_done }"></div>
                            <div class="conn-dot" :class="{ 'conn-done': pretest_done }"></div>
                        </div>
                    </div>

                    <template
                        v-for="(mission, i) in missions"
                        :key="mission.id"
                    >
                        <div class="stage-wrapper">
                            <div
                                class="stage-row"
                                :style="{ '--offset': getOffset(i) + 'px' }"
                            >
                                <div
                                    v-if="
                                        !isMissionLocked(i) &&
                                        mission.status !== 'completed' &&
                                        (i === 0 ||
                                            missions[i - 1].status ===
                                                'completed')
                                    "
                                    class="start-tooltip"
                                    :style="{ color: getColor(i).bg }"
                                >
                                    START
                                    <div class="tip-arrow"></div>
                                </div>

                                <button
                                    class="stage-btn"
                                    :class="{
                                        'st-completed':
                                            mission.status === 'completed',
                                        'st-locked': isMissionLocked(i),
                                        'st-current':
                                            !isMissionLocked(i) &&
                                            mission.status !== 'completed',
                                    }"
                                    :style="
                                        isMissionLocked(i)
                                            ? {}
                                            : mission.status === 'completed'
                                              ? {
                                                    '--c': '#a855f7',
                                                    '--s': '#7e22ce',
                                                 }
                                              : {
                                                    '--c': getColor(i).bg,
                                                    '--s': getColor(i).sh,
                                                 }
                                    "
                                    :disabled="isMissionLocked(i)"
                                    @click="openModal(mission, i)"
                                >
                                    <RotateCcw
                                        v-if="mission.status === 'completed'"
                                        :size="36"
                                        color="white"
                                        :stroke-width="3"
                                    />
                                    <span
                                        v-else-if="!isMissionLocked(i)"
                                        class="stage-num"
                                        >{{ i + 1 }}</span
                                    >
                                    <Lock
                                        v-else
                                        :size="28"
                                        color="#cbd5e1"
                                        :stroke-width="3"
                                    />
                                </button>

                                <div
                                    class="stage-label"
                                    :class="{
                                        'stage-label-locked':
                                            isMissionLocked(i),
                                    }"
                                >
                                    {{ mission.name }}
                                </div>
                            </div>

                            <div
                                v-if="i < missions.length - 1 || (i === missions.length - 1 && has_posttest)"
                                class="connector"
                                :style="{
                                    '--off-a': getOffset(i) + 'px',
                                    '--off-b': (i < missions.length - 1 ? getOffset(i + 1) : 80) + 'px',
                                }"
                            >
                                <div
                                    class="conn-dot"
                                    :class="{
                                        'conn-done':
                                            mission.status === 'completed',
                                    }"
                                ></div>
                                <div
                                    class="conn-dot"
                                    :class="{
                                        'conn-done':
                                            mission.status === 'completed',
                                    }"
                                ></div>
                                <div
                                    class="conn-dot"
                                    :class="{
                                        'conn-done':
                                            mission.status === 'completed',
                                    }"
                                ></div>
                            </div>
                        </div>
                    </template>

                    <!-- POST-TEST NODE -->
                    <div v-if="has_posttest" class="stage-wrapper">
                        <div class="stage-row" :style="{ '--offset': '80px' }">
                            <button
                                class="stage-btn"
                                :class="{
                                    'st-completed': posttest_done,
                                    'st-locked': !all_missions_done && !posttest_done,
                                    'st-current': all_missions_done && !posttest_done
                                }"
                                :style="posttest_done ? { '--c': '#a855f7', '--s': '#7e22ce' } : (all_missions_done ? { '--c': '#ff9600', '--s': '#cc7800' } : {})"
                                :disabled="!all_missions_done && !posttest_done"
                                @click="openPosttestModal"
                            >
                                <RotateCcw v-if="posttest_done" :size="36" color="white" :stroke-width="3" />
                                <span v-else-if="all_missions_done" class="stage-num">P</span>
                                <Lock v-else :size="28" color="#cbd5e1" :stroke-width="3" />
                            </button>
                            <div class="stage-label" :class="{ 'stage-label-locked': !all_missions_done && !posttest_done }">Post-test</div>
                        </div>
                    </div>

                    <div v-if="missions.length === 0" class="empty-state">
                        <BookOpen
                            :size="48"
                            color="#cbd5e1"
                            :stroke-width="2"
                        />
                        <h3>Belum ada misi</h3>
                        <p>Misi akan segera ditambahkan oleh gurumu.</p>
                    </div>
                </div>

                <div style="height: 120px"></div>
            </div>

            <nav class="mob-bottomnav">
                <button class="mob-nav-item" @click="goBack">
                    <Home :size="22" :stroke-width="2.5" />
                    <span>Beranda</span>
                </button>
                <button class="mob-nav-item mob-nav-active">
                    <Target :size="22" :stroke-width="2.5" />
                    <span>Misi</span>
                </button>
                <button
                    class="mob-nav-item"
                    :class="{ 'mob-nav-music': musicOn }"
                    @click="toggleMusic(null)"
                >
                    <Music2 v-if="musicOn" :size="22" :stroke-width="2.5" />
                    <VolumeX v-else :size="22" :stroke-width="2.5" />
                    <span>Musik</span>
                </button>
                <button
                    class="mob-nav-item"
                    @click="dropdownOpen = !dropdownOpen"
                >
                    <div class="mob-nav-avatar">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <span>Profil</span>
                </button>

                <Transition name="t-sheet">
                    <div
                        v-if="dropdownOpen"
                        class="mob-sheet-overlay"
                        @click.self="dropdownOpen = false"
                    >
                        <div class="mob-sheet">
                            <div class="mob-sheet-handle"></div>
                            <div
                                class="ds-dd-profile"
                                style="padding: 16px 20px"
                            >
                                <div class="ds-avatar ds-avatar-lg">
                                    {{ user.name.charAt(0).toUpperCase() }}
                                </div>
                                <div>
                                    <div class="ds-dd-name">
                                        {{ user.name }}
                                    </div>
                                    <div class="ds-dd-class">
                                        Kelas {{ user.class?.name || "-" }}
                                    </div>
                                </div>
                            </div>
                            <div class="ds-dd-sep"></div>
                            <button class="ds-dd-logout" @click="logout">
                                <LogOut :size="16" :stroke-width="3" /> KELUAR
                            </button>
                        </div>
                    </div>
                </Transition>
            </nav>
        </main>

        <aside class="dsk-sidebar-right">
            <div class="dsk-sidebar-inner">
                <div class="rs-card">
                    <p class="rs-card-title">
                        <Target :size="14" :stroke-width="3" /> PROGRESS MISI
                    </p>
                    <div class="rs-prog-row">
                        <div class="prog-track">
                            <div
                                class="prog-fill"
                                :style="{ width: progressPct + '%' }"
                            >
                                <div class="prog-shine"></div>
                            </div>
                        </div>
                        <span class="prog-label">{{ progressPct }}%</span>
                    </div>
                </div>

                <div class="rs-stat-stack">
                    <div class="rs-stat chip-blue">
                        <Zap :size="18" fill="currentColor" :stroke-width="0" />
                        <div>
                            <span class="rs-val">{{ totalMissions }}</span
                            ><span class="rs-lbl">Total Misi</span>
                        </div>
                    </div>
                    <div class="rs-stat chip-orange">
                        <FlameKindling :size="18" :stroke-width="3" />
                        <div>
                            <span class="rs-val">{{ inProgressMissions }}</span
                            ><span class="rs-lbl">Dikerjakan</span>
                        </div>
                    </div>
                    <div class="rs-stat chip-green">
                        <Award :size="18" :stroke-width="3" />
                        <div>
                            <span class="rs-val">{{ completedMissions }}</span
                            ><span class="rs-lbl">Selesai</span>
                        </div>
                    </div>
                </div>

                <div class="rs-card rs-class-card" style="margin-top: auto">
                    <div class="rs-class-icon">
                        <GraduationCap :size="18" :stroke-width="2.5" />
                    </div>
                    <div>
                        <p class="rs-class-lbl">KELAS SAYA</p>
                        <p class="rs-class-val">{{ user.class || "-" }}</p>
                    </div>
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
                <div
                    class="modal-icon-wrap"
                    :class="{
                        'mi-restart': selectedMission?.status === 'completed',
                        'mi-prog': selectedMission?.status === 'in_progress',
                        'mi-start':
                            selectedMission?.status === 'not_started' ||
                            !selectedMission?.status,
                    }"
                >
                    <RotateCcw
                        v-if="selectedMission?.status === 'completed'"
                        :size="48"
                        color="#ffffff"
                        :stroke-width="2.5"
                    />
                    <Target
                        v-else-if="selectedMission?.status === 'in_progress'"
                        :size="48"
                        color="#ffffff"
                        :stroke-width="2.5"
                    />
                    <Target
                        v-else
                        :size="48"
                        color="#ffffff"
                        :stroke-width="2.5"
                    />
                </div>

                <h2 class="modal-title">{{ selectedMission?.name }}</h2>
                <p class="modal-desc">
                    {{
                        selectedMission?.description ||
                        "Selesaikan misi ini untuk melanjutkan perjalanan belajarmu!"
                    }}
                </p>

                <div v-if="selectedType === 'mission'" class="modal-stats">
                    <div class="mstat">
                        <span class="mstat-val">{{
                            selectedMission?.total_questions || "-"
                        }}</span>
                        <span class="mstat-lbl">Soal</span>
                    </div>
                    <div class="mstat-sep"></div>
                    <div class="mstat">
                        <span class="mstat-val">{{
                            selectedMission?.best_score ?? "0"
                        }}</span>
                        <span class="mstat-lbl">Skor Terbaik</span>
                    </div>
                    <div class="mstat-sep"></div>
                    <div class="mstat">
                        <span class="mstat-val"
                            ><Star
                                :size="18"
                                fill="#fbbf24"
                                color="#fbbf24"
                                :stroke-width="0"
                        /></span>
                        <span class="mstat-lbl">Reward</span>
                    </div>
                </div>

                <div class="modal-stack-btn">
                    <template v-if="selectedMission?.status === 'completed'">
                        <button class="mcta-primary mcta-restart" @click="handleRestart">
                            MULAI ULANG (RESTART)
                        </button>
                    </template>
                    <template v-else>
                        <button
                            class="mcta-primary"
                            :class="{
                                'mcta-prog': selectedMission?.status === 'in_progress',
                                'mcta-start': selectedMission?.status === 'not_started' || !selectedMission?.status,
                            }"
                            @click="handlePrimaryAction"
                        >
                            {{
                                selectedMission?.status === "in_progress"
                                    ? "LANJUTKAN"
                                    : "MULAI"
                            }}
                        </button>
                    </template>
                    <button class="mcta-secondary" @click="closeModal">
                        NANTI SAJA
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* ════════════════════════════════
   BASE & TYPOGRAPHY
════════════════════════════════ */
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

.app {
    font-family: "Nunito", sans-serif;
    color: #334155;
    min-height: 100dvh;
    background: #f1f5f9;
    display: flex;
    opacity: 0;
    transition: opacity 0.25s ease;
    position: relative;
}
.app.ready {
    opacity: 1;
}

/* ── BACKGROUND PARTICLES ── */
.bg-particles {
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
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

/* ════════════════════════════════
   LEFT SIDEBAR
════════════════════════════════ */
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
    gap: 12px;
    padding: 12px 14px;
    width: 100%;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 14px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    cursor: pointer;
    text-transform: uppercase;
    box-shadow: 0 3px 0 0 #e2e8f0;
    transition: all 0.1s;
}
.ds-music-btn:active {
    transform: translateY(3px);
    box-shadow: 0 0 0 0 #e2e8f0;
}
.ds-music-btn.on {
    background: #fff7ed;
    border-color: #fdba74;
    color: #ea580c;
    box-shadow: 0 3px 0 0 #fdba74;
}

.ds-user {
    position: relative;
}
.ds-user-pill {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 0 0 #e2e8f0;
    cursor: pointer;
    font-family: inherit;
    transition: all 0.1s;
}
.ds-user-pill:hover,
.ds-user-pill.open {
    background: #f8fafc;
}
.ds-user-pill:active {
    transform: translateY(4px);
    box-shadow: 0 0 0 0 #e2e8f0;
}
.ds-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #1cb0f6;
    color: #fff;
    font-weight: 900;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ds-avatar-lg {
    width: 44px;
    height: 44px;
    font-size: 18px;
}
.ds-uname {
    font-size: 14px;
    font-weight: 800;
    color: #334155;
    flex: 1;
    text-align: left;
}
.ds-dropdown {
    position: absolute;
    bottom: calc(100% + 12px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    z-index: 100;
}
.ds-dd-profile {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
}
.ds-dd-name {
    font-size: 15px;
    font-weight: 900;
    color: #1e293b;
}
.ds-dd-class {
    font-size: 13px;
    font-weight: 700;
    color: #94a3b8;
}
.ds-dd-sep {
    height: 2px;
    background: #e2e8f0;
}
.ds-dd-logout {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px;
    background: transparent;
    border: none;
    font-family: inherit;
    font-size: 13px;
    font-weight: 900;
    color: #ef4444;
    cursor: pointer;
    text-transform: uppercase;
}
.ds-dd-logout:hover {
    background: #fef2f2;
}

/* ════════════════════════════════
   MAIN SCROLL AREA
════════════════════════════════ */
.main-scroll {
    flex: 1;
    margin-left: 260px;
    margin-right: 312px;
    height: 100dvh;
    overflow-y: auto;
    overflow-x: hidden;
    display: flex;
    flex-direction: column;
    position: relative;
    scrollbar-width: none;
    z-index: 10;
}

.path-container {
    max-width: 580px;
    margin: 0 auto;
    padding: 24px 24px 0;
    flex: 1;
    width: 100%;
}

/* ── UNIT BANNER ── */
.unit-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #1cb0f6;
    border-radius: 20px;
    padding: 24px 28px;
    color: #ffffff;
    margin-bottom: 40px;
    box-shadow: 0 6px 0 0 #1899d6;
}
.unit-banner-left {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.unit-subtitle {
    font-size: 12px;
    font-weight: 900;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.unit-title {
    font-size: 24px;
    font-weight: 900;
    line-height: 1.2;
}
.unit-desc {
    font-size: 14px;
    font-weight: 700;
    opacity: 0.9;
    margin-top: 4px;
    line-height: 1.4;
}

/* ── DONE BANNER ── */
.done-banner {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #ffffff;
    border: 2px solid #86efac;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 32px;
    box-shadow: 0 4px 0 0 #86efac;
}
.done-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #dcfce7;
    display: flex;
    align-items: center;
    justify-content: center;
}
.done-text {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.done-text strong {
    font-size: 15px;
    font-weight: 900;
    color: #166534;
}
.done-text span {
    font-size: 13px;
    font-weight: 700;
    color: #16a34a;
}
.done-btn {
    background: #16a34a;
    color: #fff;
    border: none;
    border-radius: 14px;
    padding: 12px 18px;
    font-family: inherit;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    white-space: nowrap;
    box-shadow: 0 4px 0 0 #15803d;
    transition: all 0.1s;
}
.done-btn:active {
    transform: translateY(4px);
    box-shadow: 0 0 0 0 #15803d;
}

/* ════════════════════════════════
   MISSION PATH & STAGE BUTTONS
════════════════════════════════ */
.mission-path {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
    padding-bottom: 40px;
}

.stage-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
}

.stage-row {
    display: flex;
    flex-direction: column;
    align-items: center;
    transform: translateX(var(--offset, 0px));
    position: relative;
    z-index: 2;
    padding: 10px 0;
}

/* START TOOLTIP BUBBLE */
.start-tooltip {
    position: absolute;
    top: -45px;
    background: #ffffff;
    padding: 8px 16px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 4px 0 0 #e2e8f0;
    animation: float-tip 2s ease-in-out infinite;
    z-index: 20;
}
.tip-arrow {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    width: 0;
    height: 0;
    border-left: 8px solid transparent;
    border-right: 8px solid transparent;
    border-top: 8px solid #e2e8f0;
}
.tip-arrow::after {
    content: "";
    position: absolute;
    bottom: 3px;
    left: -6px;
    width: 0;
    height: 0;
    border-left: 6px solid transparent;
    border-right: 6px solid transparent;
    border-top: 6px solid #ffffff;
}
@keyframes float-tip {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-6px);
    }
}

/* STAGE BUTTON: Diubah dari Lingkaran (50%) menjadi Kotak Membulat (Rounded Box) */
.stage-btn {
    position: relative;
    width: 90px;
    height: 90px;
    border-radius: 100px; /* <--- Tidak lagi lingkaran penuh, melainkan kotak membulat */
    border: none;
    cursor: pointer;
    background: var(--c, #e2e8f0);
    box-shadow: 0 8px 0 0 var(--s, #cbd5e1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition:
        transform 0.1s,
        box-shadow 0.1s;
    outline: none;
}
.stage-btn:not(:disabled):active {
    transform: translateY(8px);
    box-shadow: 0 0 0 0 var(--s, #cbd5e1);
}
.st-locked {
    background: #f1f5f9;
    box-shadow: 0 8px 0 0 #e2e8f0;
    cursor: not-allowed !important;
}

/* Efek Current */
.st-current {
    animation: bounce-stage 2.5s ease-in-out infinite;
}
.st-current:active {
    animation: none;
}
@keyframes bounce-stage {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.stage-num {
    font-family: "Righteous", cursive;
    font-size: 42px;
    color: #ffffff;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
    line-height: 1;
}

.stage-label {
    margin-top: 24px;
    font-size: 12px;
    font-weight: 800;
    color: #475569;
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 20px;
    padding: 4px 14px;
    max-width: 180px;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    box-shadow: 0 2px 0 0 #e2e8f0;
}
.stage-label-locked {
    color: #94a3b8;
    background: #f8fafc;
}

/* CONNECTOR DOTS */
.connector {
    height: 70px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-evenly;
    transform: translateX(calc((var(--off-a, 0px) + var(--off-b, 0px)) / 2));
    z-index: 1;
    margin: -10px 0;
}
.conn-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e2e8f0;
}
.conn-done {
    background: #86efac;
}

/* Empty State */
.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 64px 32px;
    background: #ffffff;
    border: 2px dashed #cbd5e1;
    border-radius: 24px;
    margin-top: 32px;
}
.empty-state h3 {
    font-size: 18px;
    font-weight: 900;
    color: #64748b;
}
.empty-state p {
    font-size: 14px;
    font-weight: 700;
    color: #94a3b8;
}

.mob-topbar,
.mob-bottomnav {
    display: none;
}

/* ════════════════════════════════
   RIGHT SIDEBAR
════════════════════════════════ */
.dsk-sidebar-right {
    position: fixed;
    top: 16px;
    right: 16px;
    bottom: 16px;
    width: 272px;
    z-index: 50;
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
    gap: 16px;
}
.rs-card {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px;
}
.rs-card-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 900;
    color: #94a3b8;
    letter-spacing: 1px;
    margin-bottom: 12px;
}
.rs-prog-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.prog-track {
    flex: 1;
    height: 14px;
    background: #f1f5f9;
    border-radius: 10px;
    overflow: hidden;
}
.prog-fill {
    height: 100%;
    background: #58cc02;
    border-radius: 10px;
    position: relative;
    transition: width 0.5s ease;
}
.prog-shine {
    position: absolute;
    top: 2px;
    left: 4px;
    right: 4px;
    height: 4px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}
.prog-label {
    font-size: 14px;
    font-weight: 900;
    color: #58cc02;
    min-width: 36px;
    text-align: right;
}

.rs-stat-stack {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.rs-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 14px;
    border: 2px solid;
    border-radius: 16px;
}
.rs-stat div {
    display: flex;
    flex-direction: column;
}
.rs-val {
    font-size: 16px;
    font-weight: 900;
    color: #1e293b;
    line-height: 1.2;
}
.rs-lbl {
    font-size: 12px;
    font-weight: 800;
    color: #94a3b8;
}
.chip-blue {
    color: #1cb0f6;
    border-color: #ddf4ff;
    background: #f0f9ff;
}
.chip-orange {
    color: #ff9600;
    border-color: #ffebc2;
    background: #fff7ed;
}
.chip-green {
    color: #58cc02;
    border-color: #d7ffb8;
    background: #f0fdf4;
}

.rs-class-card {
    display: flex;
    align-items: center;
    gap: 14px;
    background: #fbf5ff !important;
    border-color: #f3e8ff !important;
}
.rs-class-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #a855f7;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 3px 0 0 #9333ea;
    flex-shrink: 0;
}
.rs-class-lbl {
    font-size: 11px;
    font-weight: 900;
    color: #c084fc;
    letter-spacing: 0.5px;
}
.rs-class-val {
    font-size: 16px;
    font-weight: 900;
    color: #7e22ce;
}

/* ════════════════════════════════
   RESPONSIVE MOBILE
════════════════════════════════ */
@media (max-width: 1100px) {
    .dsk-sidebar-right {
        display: none;
    }
    .main-scroll {
        margin-right: 0;
    }
}
@media (max-width: 760px) {
    .dsk-sidebar-left {
        display: none;
    }
    .main-scroll {
        margin-left: 0;
        margin-right: 0;
        height: 100dvh;
        display: block;
    }
    .path-container {
        max-width: 100%;
        padding: 0 16px;
    }

    /* TOP BAR MOBILE */
    .mob-topbar {
        display: flex;
        align-items: center;
        gap: 12px;
        position: sticky;
        top: 0;
        height: 64px;
        padding: 0 16px;
        background: #ffffff;
        border-bottom: 2px solid #e2e8f0;
        z-index: 80;
    }
    .mob-back {
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 4px;
    }
    .mob-topbar-center {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .mob-topbar-title {
        font-size: 15px;
        font-weight: 900;
        color: #cbd5e1;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .mob-mini-progress {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .mob-mini-track {
        flex: 1;
        height: 10px;
        background: #e2e8f0;
        border-radius: 6px;
        overflow: hidden;
    }
    .mob-mini-fill {
        height: 100%;
        background: #58cc02;
        border-radius: 6px;
    }
    .mob-mini-pct {
        display: none;
    }
    .mob-stat-pill {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 14px;
        font-weight: 900;
        color: #ff9600;
    }

    /* UNIT BANNER MOBILE */
    .unit-banner {
        padding: 20px 24px;
        margin: 24px 0 32px;
    }
    .unit-subtitle {
        font-size: 11px;
    }
    .unit-title {
        font-size: 20px;
    }
    .unit-desc {
        display: none;
    }
    .unit-banner-right svg {
        width: 38px;
        height: 38px;
    }

    /* BOTTOM NAV */
    .mob-bottomnav {
        display: flex;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #ffffff;
        border-top: 2px solid #e2e8f0;
        height: 70px;
        z-index: 80;
        padding-bottom: env(safe-area-inset-bottom, 0);
    }
    .mob-nav-item {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 4px;
        color: #94a3b8;
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
        background: none;
        border: none;
        font-family: inherit;
    }
    .mob-nav-active {
        color: #1cb0f6;
        border-top: 3px solid #1cb0f6;
        margin-top: -2px;
        border-radius: 2px;
    }
    .mob-nav-music {
        color: #ea580c;
    }
    .mob-nav-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #1cb0f6;
        color: #fff;
        font-weight: 900;
        font-size: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .mob-sheet-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.4);
        z-index: 200;
        display: flex;
        align-items: flex-end;
    }
    .mob-sheet {
        background: #fff;
        width: 100%;
        border-radius: 24px 24px 0 0;
        border-top: 2px solid #e2e8f0;
        padding-bottom: calc(env(safe-area-inset-bottom, 16px) + 16px);
    }
    .mob-sheet-handle {
        width: 48px;
        height: 5px;
        background: #e2e8f0;
        border-radius: 3px;
        margin: 16px auto;
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
    box-shadow: 0 8px 0 0 rgba(0, 0, 0, 0.08);
}
.mi-restart {
    background: #a855f7;
    box-shadow: 0 8px 0 0 #7e22ce;
}
.mi-prog {
    background: #ff9600;
    box-shadow: 0 8px 0 0 #cc7800;
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
    margin-bottom: 24px;
}

.modal-stats {
    display: flex;
    align-items: center;
    margin: 0 0 32px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    width: 100%;
}
.mstat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 16px 10px;
}
.mstat-val {
    font-size: 18px;
    font-weight: 900;
    color: #1e293b;
    line-height: 1;
}
.mstat-lbl {
    font-size: 11px;
    font-weight: 800;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mstat-sep {
    width: 2px;
    height: 40px;
    background: #e2e8f0;
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
    transition:
        top 0.1s,
        box-shadow 0.1s;
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
.mcta-prog {
    background: #ff9600;
    box-shadow: 0 5px 0 0 #cc7800;
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

/* Transitions */
.t-dropdown-enter-active {
    animation: t-slide-up 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.t-dropdown-leave-active {
    animation: t-slide-up 0.15s ease-in reverse;
}
@keyframes t-slide-up {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.t-pop-enter-active {
    animation: t-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.t-pop-leave-active {
    animation: t-pop 0.15s ease-in reverse;
}
@keyframes t-pop {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
