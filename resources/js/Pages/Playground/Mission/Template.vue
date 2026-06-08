<!-- ini te-->
<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from "vue";
import {
    ArrowLeft,
    ArrowRight,
    Home,
    Zap,
    Clock,
    Music2,
    VolumeX,
    CheckCircle2,
    BookOpen,
    Star,
    Trophy,
    Sparkles,
    ListChecks,
    Award,
    Rocket,
    Target,
    Flame,
    Flag,
    PartyPopper,
    Loader2,
    Check,
    Hash,
    MousePointerClick,
    Eye,
    CircleCheck,
    Timer,
    ChevronRight,
    ClipboardList,
    LayoutGrid,
    ToggleLeft,
    FileSearch,
    GripHorizontal,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";
import axios from "axios";

import True_false from "@/Components/Quiz/True_false.vue";
import Multiple_choice from "@/Components/Quiz/Multiple_choice.vue";
import Case_study from "@/Components/Quiz/Case_study.vue";
import Materials from "@/Components/Quiz/Materials.vue";
import Drag_drop from "@/Components/Quiz/Drag_drop.vue";
import Short_answer from "@/Components/Quiz/Short_answer.vue";
import Clickable_objects from "@/Components/Simulation/ClickableObjects.vue";
import Double_slider from "@/Components/Simulation/DoubleSlider.vue";
import Comparisons from "@/Components/Simulation/Comparisons.vue";
import Decisions from "@/Components/Simulation/Decisions.vue";
import { useMusic } from "@/Composable/useMusic";
// ── Component / type maps ──────────────────────────────────────
const COMPONENT_MAP = {
    multiple_choices: Multiple_choice,
    true_false: True_false,
    case_study: Case_study,
    materials: Materials,
    drag_drop: Drag_drop,
    short_answer: Short_answer,
    simulation_clickable: Clickable_objects,
    simulation_slider: Double_slider,
    simulation_comparison: Comparisons,
    simulation_decision: Decisions,
};
const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();
const brandMoved = ref(false);
const TYPE_META = {
    multiple_choices: {
        label: "PILIHAN GANDA",
        color: "#3b82f6",
        bg: "#dbeafe",
    },
    true_false: {
        label: "Pilih Gambar Yang Benar",
        color: "#8b5cf6",
        bg: "#ede9fe",
    },
    case_study: { label: "Studi Kasus", color: "#0891b2", bg: "#cffafe" },
    drag_drop: { label: "Seret & Letakkan", color: "#f59e0b", bg: "#fef3c7" },
    short_answer: { label: "Isian Singkat", color: "#6366f1", bg: "#e0e7ff" },
    materials: { label: "Materi", color: "#10b981", bg: "#dcfce7" },
    simulation_clickable: { label: "Simulasi", color: "#14b8a6", bg: "#ccfbf1" },
    simulation_slider: { label: "Simulasi Slider", color: "#f43f5e", bg: "#ffe4e6" },
    simulation_comparison: { label: "Simulasi Perbandingan", color: "#f97316", bg: "#ffedd5" },
    simulation_decision: { label: "Simulasi Keputusan", color: "#8b5cf6", bg: "#ede9fe" },
};
const typeMeta = (t) => TYPE_META[t] || TYPE_META.materials;

// ── Props ──────────────────────────────────────────────────────
const props = defineProps({
    mission: { type: Object, required: true },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    module: {
        type: Object,
        default: () => ({ id: null, name: "Module", description: "" }),
    },
    backsound: { type: String, default: null },
    background: { type: String, default: null },
});

// ── Steps — 1 question per page ───────────────────────────────
// Setiap question dalam setiap quiz menjadi 1 step tersendiri.
// Material tetap 1 step penuh (tidak dipecah per question).
const steps = computed(() => {
    const result = [];
    props.mission.quizzes.forEach((quiz, quizIdx) => {
        const isMaterial = quiz.type === "materials";
        const isSimulation = quiz.type === "simulation_clickable" || quiz.type === "simulation_slider" || quiz.type === "simulation_comparison" || quiz.type === "simulation_decision";
        const isDragDrop = quiz.type === "drag_drop";
        const questions = quiz.questions || [];

        if (isMaterial || isSimulation) {
            // Material/Simulation: 1 step, tampilkan question pertama sebagai konten
            result.push({
                quizIndex: quizIdx,
                quiz,
                question: questions[0] ?? null,
                questionIndex: 0,
                totalInQuiz: Math.max(questions.length, 1),
                isMaterial: true,
                isDragDrop: false,
            });
        } else if (questions.length === 0) {
            // Quiz kosong → 1 step placeholder
            result.push({
                quizIndex: quizIdx,
                quiz,
                question: null,
                questionIndex: 0,
                totalInQuiz: 0,
                isMaterial: false,
                isDragDrop,
            });
        } else {
            // ✅ Tiap question = 1 step
            questions.forEach((question, qIdx) => {
                result.push({
                    quizIndex: quizIdx,
                    quiz,
                    question,
                    questionIndex: qIdx,
                    totalInQuiz: questions.length,
                    isMaterial: false,
                    isDragDrop,
                });
            });
        }
    });

    if (props.mission.conclusion_speech || props.mission.conclusion_body) {
        result.push({
            isConclusion: true,
            quizIndex: props.mission.quizzes.length,
            quiz: { type: 'conclusion', title: `KESIMPULAN ${props.mission.name.toUpperCase()}` },
            question: null,
            questionIndex: 0,
            totalInQuiz: 1,
            isMaterial: true,
            isDragDrop: false,
        });
    }

    return result;
});

// ── State ──────────────────────────────────────────────────────
const currentStep = ref(0);
const answers = reactive({});
const isSubmitting = ref(false);
const ready = ref(false);
const shakeActive = ref(false);

// Music

const audioRef = ref(null);

const step = computed(() => steps.value[currentStep.value]);
const isFirst = computed(() => currentStep.value === 0);
const isLast = computed(() => currentStep.value === steps.value.length - 1);

// ── Timer ─────────────────────────────────────────────────────
// sessionStorage keys — scoped per mission agar tidak tabrakan antar misi
const SS_TIME_KEY = `geniuss_timer_time_${props.mission.id}`;
const SS_TIMEOUT_KEY = `geniuss_timer_out_${props.mission.id}`;

// Helpers baca/tulis sessionStorage
function ssGetMap(key) {
    try {
        return JSON.parse(sessionStorage.getItem(key) || "{}");
    } catch {
        return {};
    }
}
function ssSetMap(key, val) {
    try {
        sessionStorage.setItem(key, JSON.stringify(val));
    } catch {}
}

// Load state dari sessionStorage (bertahan saat reload)
const _savedTime = ssGetMap(SS_TIME_KEY); // { [quizId]: secondsRemaining }
const _savedTimeout = ssGetMap(SS_TIMEOUT_KEY); // { [quizId]: true }

const timeRemaining = ref(0);
const timedOutQuizzes = ref(
    new Set(Object.keys(_savedTimeout).filter((k) => _savedTimeout[k])),
);
let timerInt = null;
let activeQuizId = null;

const timerDisplay = computed(() => {
    const m = String(Math.floor(timeRemaining.value / 60)).padStart(2, "0");
    const s = String(timeRemaining.value % 60).padStart(2, "0");
    return `${m}:${s}`;
});
const timerWarning = computed(
    () => timeRemaining.value > 0 && timeRemaining.value <= 60,
);
const showTimer = computed(() => {
    const s = step.value;
    return s && !s.isMaterial && s.quiz?.time_limit > 0;
});

function saveTimerState(quizId, seconds) {
    const map = ssGetMap(SS_TIME_KEY);
    map[quizId] = seconds;
    ssSetMap(SS_TIME_KEY, map);
}

function markTimeout(quizId) {
    const map = ssGetMap(SS_TIMEOUT_KEY);
    map[quizId] = true;
    ssSetMap(SS_TIMEOUT_KEY, map);
    timedOutQuizzes.value = new Set([...timedOutQuizzes.value, quizId]);
}

function pauseActiveTimer() {
    if (activeQuizId !== null) {
        saveTimerState(activeQuizId, timeRemaining.value);
    }
    clearInterval(timerInt);
    timerInt = null;
}

function startQuizTimer(quiz) {
    pauseActiveTimer();

    if (!quiz || !quiz.time_limit || quiz.time_limit <= 0) {
        timeRemaining.value = 0;
        activeQuizId = null;
        return;
    }
    brandMoved.value = true;

    // Sudah timeout → tampilkan 0, tidak perlu interval
    if (timedOutQuizzes.value.has(quiz.id)) {
        timeRemaining.value = 0;
        activeQuizId = quiz.id;
        return;
    }

    // Ambil sisa waktu dari sessionStorage, atau mulai dari awal
    const saved = ssGetMap(SS_TIME_KEY);
    timeRemaining.value =
        saved[quiz.id] !== undefined ? saved[quiz.id] : quiz.time_limit;
    activeQuizId = quiz.id;

    timerInt = setInterval(() => {
        if (timeRemaining.value <= 0) {
            clearInterval(timerInt);
            timerInt = null;
            markTimeout(quiz.id);
            saveTimerState(quiz.id, 0);
            timeRemaining.value = 0;
            return;
        }
        timeRemaining.value--;
        // Simpan ke sessionStorage tiap detik supaya reload tetap lanjut
        saveTimerState(quiz.id, timeRemaining.value);
    }, 1000);
}

// Ganti timer saat pindah ke quiz yang berbeda
watch(
    () => step.value?.quiz?.id,
    (newId, oldId) => {
        if (newId !== oldId) {
            startQuizTimer(step.value?.quiz ?? null);
        }
    },
);

// ── Answer check ───────────────────────────────────────────────
const isQuestionAnswered = (question, quizType) => {
    const ans = answers[question.id];
    if (quizType === "drag_drop") {
        if (!ans || typeof ans !== "object" || Array.isArray(ans)) return false;
        const total = question.drag_drop_items?.length ?? 0;
        return total === 0 || Object.keys(ans).length >= total;
    }
    if (ans === undefined || ans === null) return false;
    if (Array.isArray(ans)) return ans.length > 0;
    return ans !== "";
};
const isStepAnswered = (s) => {
    if (!s || s.isMaterial) return true;
    if (!s.question) return true;
    // Quiz yang sudah timeout → dianggap selesai (bisa next/submit)
    if (timedOutQuizzes.value.has(s.quiz?.id)) return true;
    return isQuestionAnswered(s.question, s.quiz.type);
};

const canGoNext = computed(() => isStepAnswered(step.value));
const allStepsAnswered = computed(() =>
    steps.value.filter((s) => !s.isMaterial).every((s) => isStepAnswered(s)),
);

// ── Progress ───────────────────────────────────────────────────
// Hitung berdasarkan jumlah question steps yang sudah dijawab
const totalQuizSteps = computed(
    () => steps.value.filter((s) => !s.isMaterial).length,
);
const answeredQuizSteps = computed(
    () => steps.value.filter((s) => !s.isMaterial && isStepAnswered(s)).length,
);
const progressPct = computed(() =>
    totalQuizSteps.value === 0
        ? 100
        : Math.round((answeredQuizSteps.value / totalQuizSteps.value) * 100),
);

// ── Mascot ────────────────────────────────────────────────────
const DEFAULT_MASCOT = "/images/templates/pose_nunjuk.png";

const mascotUrl = computed(() => {
    const questions = step.value?.quiz?.questions ?? [];
    for (const q of questions) {
        if (q.mascot?.image) {
            const img = q.mascot.image;
            if (img.startsWith("http") || img.startsWith("/")) return img;
            return `${window.location.origin}/storage/${img}`;
        }
    }
    for (const quiz of props.mission.quizzes) {
        for (const q of quiz.questions ?? []) {
            if (q.mascot?.image) {
                const img = q.mascot.image;
                if (img.startsWith("http") || img.startsWith("/")) return img;
                return `${window.location.origin}/storage/${img}`;
            }
        }
    }
    return DEFAULT_MASCOT;
});

// ── Sidebar speech bubble ──────────────────────────────────────
const BUBBLES = [
    "Ayo semangat! 💪",
    "Baca dengan teliti 👀",
    "Kamu pasti bisa! 🔥",
    "Pikirkan baik-baik 🤔",
    "Hampir selesai! ✨",
    "Fokus ya! 🎯",
];
const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.length;
        bubbleVisible.value = true;
    }, 300);
};

// ── Music ─────────────────────────────────────────────────────

// ── Navigation ─────────────────────────────────────────────────
const updateAnswer = (payload) => {
    if (payload?.questionId !== undefined)
        answers[payload.questionId] = payload.value;
};

const goNext = () => {
    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => (shakeActive.value = false), 600);
        return;
    }
    if (!isLast.value) currentStep.value++;
    else openConfirm();
};

const goPrev = () => {
    if (!isFirst.value) currentStep.value--;
};
const goToStep = (idx) => {
    currentStep.value = idx;
};

const goBack = () => {
    router.visit(route("playground.missions.index", props.module.id), {
        replace: true,
        preserveState: false,
        preserveScroll: false,
    });
};

const goHome = () => router.visit(route("playground.index"));

const stepStatus = (idx) => {
    const s = steps.value[idx];
    if (s.isMaterial) return "material";
    if (idx === currentStep.value) return "active";
    return isStepAnswered(s) ? "done" : "pending";
};

// ── Confirm modal ──────────────────────────────────────────────
const showConfirm = ref(false);
const openConfirm = () => {
    showConfirm.value = true;
};
const closeConfirm = () => {
    showConfirm.value = false;
};

// ── Leave confirm ─────────────────────────────────────────────
const showLeaveConfirm = ref(false);
const hasAnswers = computed(() => Object.keys(answers).length > 0);

const tryGoBack = () => {
    if (hasAnswers.value) {
        showLeaveConfirm.value = true;
    } else {
        goBack();
    }
};
const confirmLeave = () => {
    showLeaveConfirm.value = false;
    goBack();
};
const cancelLeave = () => {
    showLeaveConfirm.value = false;
};

// ── Submit ─────────────────────────────────────────────────────
const submit = async () => {
    closeConfirm();
    isSubmitting.value = true;
    try {
        const res = await axios.post(
            route("playground.missions.submit", props.mission.id),
            {
                answers,
                quiz_ids: props.mission.quizzes
                    .filter((q) => !["materials", "simulation_clickable", "simulation_slider", "simulation_comparison", "simulation_decision"].includes(q.type))
                    .map((q) => q.id),
            }
        );
        const data = res.data;
        if (data.success) {
            // Bersihkan timer state supaya sesi berikutnya mulai fresh
            try {
                sessionStorage.removeItem(SS_TIME_KEY);
                sessionStorage.removeItem(SS_TIMEOUT_KEY);
            } catch {}
            router.visit(route("playground.missions.result", props.mission.id));
        } else
            alert(
                "Gagal menyimpan jawaban: " + (data.error || "Unknown error"),
            );
    } catch (e) {
        console.error(e);
        alert("Terjadi kesalahan saat menyimpan jawaban");
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => initAutoMusic(props.backsound), 100);

    // Mulai timer untuk quiz pertama
    if (step.value?.quiz) {
        startQuizTimer(step.value.quiz);
    }
});
onUnmounted(() => {
    clearInterval(bubbleTimer);
    clearInterval(timerInt);
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});

const TYPE_ICON_MAP = {
    drag_drop: GripHorizontal,
    materials: BookOpen,
    multiple_choices: ClipboardList,
    true_false: ToggleLeft,
    case_study: FileSearch,
};
const typeIcon = (t) => TYPE_ICON_MAP[t] || LayoutGrid;
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

    <div class="root">
        <!-- ══ BG ══ -->
        <div class="bg">
            <div
                class="bg-img"
                :style="{ backgroundImage: `url(${props.background})` }"
            ></div>
            <div class="bg-tint"></div>
            <!-- Blobs -->
            <div class="blob b1"></div>
            <div class="blob b2"></div>
            <div class="blob b3"></div>
            <div class="sh sh-circle c1"></div>
            <div class="sh sh-circle c2"></div>
            <div class="sh sh-ring r1"></div>
            <div class="sh sh-ring r2"></div>
            <div class="sh sh-ring r3"></div>
            <div class="sh sh-dot d1"></div>
            <div class="sh sh-dot d2"></div>
            <div class="sh sh-dot d3"></div>
            <div class="sh sh-dot d4"></div>
            <div class="sh sh-dot d5"></div>
            <div class="bg-dots"></div>
        </div>

        <!-- ══ TOP NAV (Minimal) ══ -->
        <header class="topbar">
            <!-- Brand -->
            <div class="brand" style="position: absolute; left: 18px; display: flex; align-items: center; gap: 8px; z-index: 2;">
                <div style="width: 28px; height: 28px; border-radius: 8px; background: #2563eb; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.5);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="white" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                </div>
                <span style="font-family: 'Righteous', cursive; font-size: 18px; color: #fff;">{{ $page.props.global_settings?.platform_name || 'Geniuss' }}</span>
            </div>

            <!-- Progress Bar inside Topbar -->
            <div class="prog-wrapper">
                <div class="prog-text">
                    {{ answeredQuizSteps }} / {{ totalQuizSteps }} Terjawab
                </div>
                <div class="prog-track">
                    <div class="prog-fill" :style="{ width: progressPct + '%' }">
                        <span class="prog-shine"></span>
                    </div>
                </div>
            </div>

            <!-- Timer (if any) -->
            <Transition name="t-timer">
                <div
                    v-if="showTimer"
                    class="timer"
                    :class="{
                        'timer--warn': timerWarning,
                        'timer--out': timedOutQuizzes.has(step?.quiz?.id),
                    }"
                >
                    <div class="timer-row">
                        <Clock :size="14" :stroke-width="2.5" />
                        <span class="timer-val">{{
                            timedOutQuizzes.has(step?.quiz?.id)
                                ? "Waktu Habis"
                                : timerDisplay
                        }}</span>
                    </div>
                </div>
            </Transition>
            <div class="topbar-r"></div>
        </header>

        <!-- ══ MAIN CENTRIC CONTENT ══ -->
        <main class="main-wrapper" :class="{ 'main--on': ready }">
            <template v-if="step?.isConclusion">
                <div class="mission-container conclusion-container">
                    <div class="title-pill">{{ step.quiz.title }}</div>
                    <div class="conclusion-box border-4 border-blue-600 rounded-[2rem] bg-white p-6 relative flex flex-col gap-6 w-full max-w-4xl mx-auto mt-6 shadow-xl">
                        <!-- Top: Mascot & Speech -->
                        <div class="flex items-start gap-4">
                            <img :src="mascotUrl" alt="Maskot" class="w-32 h-32 object-contain shrink-0" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1))" />
                            <div class="conclusion-speech-bubble relative bg-white border-2 border-cyan-400 rounded-3xl p-5 w-full text-gray-700 text-lg font-medium leading-relaxed" style="border-radius: 2rem;">
                                {{ mission.conclusion_speech || 'Selamat, kamu telah menyelesaikan misi ini!' }}
                                <!-- Tail pointing left to mascot -->
                                <svg class="absolute -left-4 top-8 w-6 h-6 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z" fill="#fff" /></svg>
                            </div>
                        </div>
                        
                        <!-- Bottom: Body -->
                        <div class="conclusion-body bg-gray-50 rounded-[1.5rem] p-6 text-gray-800 text-base leading-relaxed border border-gray-100 min-h-[150px] shadow-inner font-medium">
                            {{ mission.conclusion_body || 'Tidak ada penjelasan kesimpulan.' }}
                        </div>
                    </div>
                </div>
            </template>

            <template v-else>
                <div class="mission-container">
                    <!-- Title Pill -->
                    <div class="title-pill">
                        {{ module.name.toUpperCase() }}
                    </div>

                    <!-- Question Bubble -->
                    <div class="question-bubble" v-if="step?.question && !step.isMaterial" v-html="step.question.question_text"></div>
                    <div class="question-bubble" v-else-if="step?.isMaterial" v-html="step.quiz.title"></div>
                    <div class="question-bubble empty-qs" v-else>
                        Tidak ada soal
                    </div>

                    <!-- Component Container -->
                    <div class="component-box" v-if="step" :class="{ 'opts--shake': shakeActive, 'box-locked': timedOutQuizzes.has(step.quiz?.id) }">
                        <div v-if="timedOutQuizzes.has(step.quiz?.id)" class="timeout-overlay">
                            <Timer :size="28" :stroke-width="2" />
                            <span>Waktu Habis</span>
                        </div>
                        
                        <component
                            v-if="step.question || step.isMaterial"
                            :is="COMPONENT_MAP[step.quiz.type]"
                            :question="step.question"
                            :quiz="step.quiz"
                            :modelValue="answers[step.question?.id]"
                            @update-answer="updateAnswer"
                        />
                    </div>
                </div>
            </template>
        </main>

        <!-- ══ ABSOLUTE ELEMENTS (Mascot & Buttons) ══ -->
        <div class="mascot-absolute" @click="rotateBubble" v-if="!step?.isConclusion">
            <Transition name="bbl">
                <div v-if="bubbleVisible" class="mascot-speech">
                    <span>{{ BUBBLES[bubbleIdx] }}</span>
                    <i class="bbl-arrow-out"></i>
                    <i class="bbl-arrow-in"></i>
                </div>
            </Transition>
            <img :src="mascotUrl" alt="Maskot" class="mascot-img" />
            <div class="mascot-shadow"></div>
        </div>

        <div class="action-btn-absolute">
            <template v-if="isLast">
                <button
                    class="pill-btn pill-btn-finish"
                    @click="openConfirm"
                    :disabled="isSubmitting || (!canGoNext && !step?.isMaterial)"
                >
                    <span v-if="!isSubmitting">Selesaikan Misi</span>
                    <Loader2 v-else :size="20" class="spin" />
                    <CheckCircle2 v-if="!isSubmitting" :size="20" :stroke-width="2.5" />
                </button>
            </template>
            <template v-else>
                <button
                    class="pill-btn pill-btn-next"
                    @click="goNext"
                    :disabled="(!canGoNext && !step?.isMaterial) || isSubmitting"
                >
                    <span>Selanjutnya</span>
                    <ArrowRight :size="20" :stroke-width="2.5" />
                </button>
            </template>
        </div>

        <div class="utils-absolute">
            <button class="util-btn" @click="toggleMusic(props.backsound)">
                <Music2 v-if="musicOn" :size="16" :stroke-width="2" />
                <VolumeX v-else :size="16" :stroke-width="2" />
            </button>
            <button class="util-btn util-btn-back" @click="tryGoBack" :disabled="isSubmitting">
                <span>Back</span>
            </button>
        </div>

        <!-- ══ CONFIRM SUBMIT MODAL ══ -->
        <Transition name="overlay-fade">
            <div
                v-if="showConfirm"
                class="modal-overlay"
                @click.self="closeConfirm"
            >
                <Transition name="modal-pop" appear>
                    <div v-if="showConfirm" class="modal">
                        <div class="modal-icon">
                            <Trophy
                                :size="32"
                                color="#34D399"
                                :stroke-width="1.5"
                            />
                        </div>
                        <h2 class="modal-title">Apakah kamu yakin?</h2>
                        <p class="modal-desc">
                            Jawaban <strong>tidak bisa diubah</strong> setelah
                            dikirim.
                        </p>
                        <div class="modal-actions">
                            <button
                                class="modal-btn modal-btn--cancel"
                                @click="closeConfirm"
                                :disabled="isSubmitting"
                            >
                                Batal
                            </button>
                            <button
                                class="modal-btn modal-btn--confirm"
                                @click="submit"
                                :disabled="isSubmitting"
                            >
                                <span v-if="!isSubmitting">Ya, Kumpulkan!</span>
                                <span v-else>Menyimpan…</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>

        <!-- ══ CONFIRM LEAVE MODAL ══ -->
        <Transition name="overlay-fade">
            <div
                v-if="showLeaveConfirm"
                class="modal-overlay"
                @click.self="cancelLeave"
            >
                <Transition name="modal-pop" appear>
                    <div v-if="showLeaveConfirm" class="modal">
                        <div class="modal-icon modal-icon--warn">
                            <Flag
                                :size="28"
                                color="#F59E0B"
                                :stroke-width="1.8"
                            />
                        </div>
                        <h2 class="modal-title">Keluar dari misi?</h2>
                        <p class="modal-desc">
                            Jawaban yang sudah kamu isi
                            <strong>tidak akan disimpan</strong> jika kamu
                            keluar sekarang.
                        </p>
                        <div class="modal-actions">
                            <button
                                class="modal-btn modal-btn--cancel"
                                @click="cancelLeave"
                            >
                                Lanjut Ngerjain
                            </button>
                            <button
                                class="modal-btn modal-btn--leave"
                                @click="confirmLeave"
                            >
                                Ya, Keluar
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
:root {
    --blue: #2563eb;
    --blue-mid: #1d4ed8;
    --blue-deep: #1e3a8a;
    --blue-soft: #bfdbfe;
    --blue-pale: #eff6ff;
    --mint: #34d399;
    --mint-deep: #059669;
    --mint-soft: #d1fae5;
    --red: #ef4444;
    --red-soft: #fee2e2;
    --yellow: #f59e0b;
    --yellow-soft: #fef3c7;
    --gray-2: #e2e8f0;
    --gray-3: #94a3b8;
    --text: #1e293b;
    --text-mid: #475569;
}

*, *::before, *::after {
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

/* ─── BG ─── */
.bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
.bg-img { position: absolute; inset: 0; background: url("/images/templates/background_misi.png") center/cover no-repeat; }
.bg-tint { position: absolute; inset: 0; background: #90cdf4; opacity: 0.5; }
.blob { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); }
.b1 { width: 480px; height: 480px; top: -140px; left: -100px; background: #63b3ed; opacity: 0.35; animation: bDrift 20s ease-in-out infinite alternate; }
.b2 { width: 380px; height: 380px; bottom: -100px; right: -80px; background: #90cdf4; opacity: 0.22; animation: bDrift2 24s ease-in-out infinite alternate; }
.b3 { width: 260px; height: 260px; top: 38%; left: 52%; background: #bee3f8; opacity: 0.18; animation: bDrift 28s ease-in-out 6s infinite alternate; }
@keyframes bDrift { 0% { transform: translate(0, 0); } 50% { transform: translate(30px, 20px) scale(1.05); } 100% { transform: translate(-15px, 35px); } }
@keyframes bDrift2 { 0% { transform: translate(0, 0); } 50% { transform: translate(-28px, -18px) scale(1.06); } 100% { transform: translate(22px, -40px); } }
.sh { position: absolute; pointer-events: none; }
.sh-circle { border-radius: 50%; background: rgba(255, 255, 255, 0.06); border: 1.5px solid rgba(255, 255, 255, 0.1); animation: sDrift ease-in-out infinite alternate; }
.c1 { width: 150px; height: 150px; top: -30px; left: -25px; animation-duration: 22s; }
.c2 { width: 90px; height: 90px; bottom: 70px; right: 50px; animation-duration: 28s; animation-delay: 4s; }
.sh-ring { border-radius: 50%; background: transparent; border: 1.5px solid rgba(255,255,255, 0.2); animation: rPulse ease-out infinite; }
.r1 { width: 300px; height: 300px; top: -60px; left: -60px; animation-duration: 9s; }
.r2 { width: 240px; height: 240px; bottom: -50px; right: -50px; animation-duration: 12s; animation-delay: 2s; }
.r3 { width: 180px; height: 180px; top: 38%; left: 58%; animation-duration: 10s; animation-delay: 5s; }
.sh-dot { border-radius: 50%; background: rgba(255, 255, 255, 0.45); animation: dFloat linear infinite; }
.d1 { width: 5px; height: 5px; top: 12%; left: 9%; animation-duration: 14s; }
.d2 { width: 3px; height: 3px; top: 32%; left: 22%; animation-duration: 18s; animation-delay: 2s; }
.d3 { width: 6px; height: 6px; top: 58%; left: 7%; animation-duration: 12s; animation-delay: 5s; }
.d4 { width: 4px; height: 4px; top: 18%; right: 11%; animation-duration: 16s; animation-delay: 1s; }
.d5 { width: 5px; height: 5px; top: 72%; right: 16%; animation-duration: 20s; animation-delay: 3.5s; }
@keyframes sDrift { 0% { transform: translate(0, 0) rotate(0); } 50% { transform: translate(14px, -10px) rotate(6deg); } 100% { transform: translate(-10px, 18px) rotate(-4deg); } }
@keyframes rPulse { 0% { transform: scale(1); opacity: 0.38; } 70% { transform: scale(1.38); opacity: 0.06; } 100% { transform: scale(1.65); opacity: 0; } }
@keyframes dFloat { 0% { transform: translateY(0); opacity: 0; } 10% { opacity: 0.55; } 90% { opacity: 0.25; } 100% { transform: translateY(-150px); opacity: 0; } }
.bg-dots { position: absolute; inset: 0; pointer-events: none; background-image: radial-gradient(circle, rgba(255, 255, 255, 0.09) 1px, transparent 1px); background-size: 34px 34px; }

/* ─── TOP NAV (Minimal) ─── */
.topbar {
    position: relative;
    z-index: 50;
    height: 56px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    padding: 0 18px;
    justify-content: center; /* Center the progress bar */
}
.prog-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    min-width: 150px;
    background: rgba(255, 255, 255, 0.3);
    padding: 6px 12px;
    border-radius: 20px;
    backdrop-filter: blur(8px);
}
.prog-text {
    font-size: 11px;
    font-weight: 800;
    color: #1e3a8a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.prog-track {
    width: 100%;
    height: 6px;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 99px;
    overflow: hidden;
}
.prog-fill {
    height: 100%;
    background: #3b82f6;
    border-radius: 99px;
    position: relative;
    transition: width 0.5s ease;
}
.prog-shine {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shine 2.2s infinite;
}
@keyframes shine { 0%, 100% { transform: translateX(-100%); } 60% { transform: translateX(200%); } }

.timer {
    position: absolute;
    right: 20px; /* Instead of center */
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    background: rgba(255,255,255,0.8);
    padding: 6px 12px;
    border-radius: 20px;
    color: #ef4444;
}
.timer-row {
    display: flex;
    align-items: center;
    gap: 6px;
}
.timer-val {
    font-family: "Righteous", cursive;
    font-size: 16px;
}

/* ─── MAIN CENTRIC CONTENT ─── */
.main-wrapper {
    position: relative;
    z-index: 10;
    flex: 1;
    display: flex;
    align-items: flex-start; /* start instead of center to allow scrolling if tall */
    justify-content: center;
    padding: 10px 20px 140px; /* padding bottom for mascot & buttons */
    opacity: 0;
    transition: opacity 0.45s;
    overflow-y: auto;
    overflow-x: hidden;
}
.main--on { opacity: 1; }

.mission-container {
    width: 100%;
    max-width: 800px;
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 10px;
}

.title-pill {
    background: #1e62d0;
    color: #fff;
    font-family: "Nunito", sans-serif;
    font-weight: 900;
    font-size: 18px;
    padding: 8px 32px;
    border-radius: 30px;
    text-transform: uppercase;
    box-shadow: 0 4px 15px rgba(30, 98, 208, 0.4);
    margin-bottom: -16px; /* overlap with bubble */
    z-index: 2;
    border: 3px solid #6cb2f9;
}

.question-bubble {
    background: #a3d9f9;
    color: #fff;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    font-size: 18px;
    padding: 24px 40px 16px;
    border-radius: 30px;
    text-align: center;
    width: 100%;
    max-width: 700px;
    margin-bottom: 24px;
    box-shadow: 0 4px 15px rgba(163, 217, 249, 0.4);
}

.component-box {
    width: 100%;
    background: #fff;
    border-radius: 24px;
    padding: 24px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.1);
    position: relative;
    overflow: hidden;
}
.box-locked {
    filter: grayscale(0.4);
    pointer-events: none;
    opacity: 0.8;
}
.timeout-overlay {
    position: absolute;
    inset: 0;
    z-index: 20;
    background: rgba(255,255,255,0.8);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    font-family: "Righteous", cursive;
    font-size: 24px;
}

.empty-qs {
    color: #fff;
    font-size: 14px;
}

/* ─── ABSOLUTE ELEMENTS ─── */
.mascot-absolute {
    position: fixed;
    bottom: 20px;
    left: 40px;
    z-index: 60;
    display: flex;
    align-items: flex-end;
    cursor: pointer;
}
.mascot-img {
    height: 200px;
    width: auto;
    filter: drop-shadow(0 8px 16px rgba(0,0,0,0.15));
    animation: mBob 3.5s ease-in-out infinite;
    transform-origin: bottom center;
}
@keyframes mBob { 0%, 100% { transform: translateY(0) rotate(0deg); } 45% { transform: translateY(-8px) rotate(1deg); } }

.mascot-speech {
    position: absolute;
    bottom: 50%;
    left: 85%;
    margin-bottom: -10px;
    background: #fff;
    border: 4px solid #fff;
    border-radius: 24px;
    padding: 12px 20px;
    min-width: 140px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    animation: bblFloat 3.5s ease-in-out infinite;
    z-index: 61;
    display: flex;
    align-items: center;
    justify-content: center;
}
.mascot-speech span {
    font-size: 15px;
    font-weight: 800;
    color: #1e3a8a;
    text-align: center;
}
.bbl-arrow-out, .bbl-arrow-in {
    position: absolute;
    width: 0;
    height: 0;
    top: 50%;
    transform: translateY(-50%);
}
.bbl-arrow-out { border-top: 10px solid transparent; border-bottom: 10px solid transparent; border-right: 14px solid rgba(0,0,0,0.05); left: -14px; }
.bbl-arrow-in { border-top: 8px solid transparent; border-bottom: 8px solid transparent; border-right: 12px solid #fff; left: -12px; }

@keyframes bblFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }
.bbl-enter-active { transition: opacity 0.3s, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.bbl-leave-active { transition: opacity 0.2s; }
.bbl-enter-from { opacity: 0; transform: translateY(10px) scale(0.9); }
.bbl-leave-to { opacity: 0; }

.action-btn-absolute {
    position: fixed;
    bottom: 50px;
    right: 40px;
    z-index: 60;
}
.pill-btn {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: #1e62d0;
    color: #fff;
    font-family: "Nunito", sans-serif;
    font-size: 18px;
    font-weight: 900;
    padding: 12px 32px;
    border-radius: 99px;
    border: 3px solid #6cb2f9;
    cursor: pointer;
    box-shadow: 0 8px 25px rgba(30, 98, 208, 0.4);
    transition: all 0.2s;
}
.pill-btn:hover:not(:disabled) {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 30px rgba(30, 98, 208, 0.5);
}
.pill-btn:disabled {
    background: #94a3b8;
    border-color: #cbd5e1;
    box-shadow: none;
    cursor: not-allowed;
    opacity: 0.8;
}
.pill-btn-finish {
    background: #059669;
    border-color: #6ee7b7;
    box-shadow: 0 8px 25px rgba(5, 150, 105, 0.4);
}
.pill-btn-finish:hover:not(:disabled) {
    box-shadow: 0 12px 30px rgba(5, 150, 105, 0.5);
}
.opts--shake { animation: optShake 0.5s ease; }
@keyframes optShake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-5px); } 40%, 80% { transform: translateX(5px); } }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

.utils-absolute {
    position: fixed;
    bottom: 12px;
    right: 40px;
    z-index: 60;
    display: flex;
    align-items: center;
    gap: 12px;
}
.util-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(255,255,255,0.4);
    color: #1e3a8a;
    border: none;
    cursor: pointer;
    transition: background 0.2s;
}
.util-btn:hover { background: rgba(255,255,255,0.8); }
.util-btn-back {
    width: auto;
    border-radius: 12px;
    padding: 0 12px;
    font-weight: 800;
    font-size: 12px;
}

/* ── MODALS ── */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(0, 0, 0, 0.55);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal {
    background: #fff;
    border-radius: 22px;
    padding: 28px 24px 22px;
    width: 100%;
    max-width: 360px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.22);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    text-align: center;
}
.modal-icon { width: 68px; height: 68px; border-radius: 50%; background: #d1fae5; border: 2.5px solid #6ee7b7; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 18px rgba(52,211,153,0.22); }
.modal-icon--warn { background: #fef3c7; border-color: #fcd34d; }
.modal-title { font-family: "Righteous", cursive; font-size: 20px; color: #1e3a8a; margin: 0; }
.modal-desc { font-size: 13px; font-weight: 600; color: #475569; line-height: 1.65; margin: 0; }
.modal-desc strong { color: #dc2626; }
.modal-actions { display: flex; gap: 9px; width: 100%; margin-top: 4px; }
.modal-btn { flex: 1; height: 42px; border: none; border-radius: 12px; font-family: "Righteous", cursive; font-size: 13.5px; cursor: pointer; transition: all 0.18s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-btn--cancel { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.modal-btn--cancel:hover { background: #e2e8f0; transform: translateY(-1px); }
.modal-btn--confirm { background: linear-gradient(135deg, #10b981, #059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,0.35); }
.modal-btn--confirm:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(16,185,129,0.45); }
.modal-btn--leave { background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff; box-shadow: 0 4px 14px rgba(220,38,38,0.3); }
.modal-btn--leave:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(220,38,38,0.4); }

.overlay-fade-enter-active { transition: opacity 0.25s ease; }
.overlay-fade-leave-active { transition: opacity 0.2s ease; }
.overlay-fade-enter-from, .overlay-fade-leave-to { opacity: 0; }
.modal-pop-enter-active { transition: opacity 0.3s ease, transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1); }
.modal-pop-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.modal-pop-enter-from { opacity: 0; transform: scale(0.82) translateY(24px); }
.modal-pop-leave-to { opacity: 0; transform: scale(0.94); }

/* MOBILE RESPONSIVE */
@media (max-width: 820px) {
    .main-wrapper { padding: 0 12px 100px; }
    .title-pill { font-size: 14px; padding: 6px 20px; }
    .question-bubble { font-size: 14px; padding: 20px 20px 12px; }
    .component-box { padding: 16px; border-radius: 16px; }
    .mascot-img { height: 120px; }
    .mascot-speech { padding: 10px 16px; min-width: 120px; margin-bottom: -5px; }
    .mascot-speech span { font-size: 13px; }
    .mascot-absolute { bottom: 10px; left: 10px; }
    .action-btn-absolute { bottom: 40px; right: 20px; }
    .pill-btn { padding: 8px 20px; font-size: 14px; }
    .utils-absolute { bottom: 10px; right: 20px; }
}
</style>
