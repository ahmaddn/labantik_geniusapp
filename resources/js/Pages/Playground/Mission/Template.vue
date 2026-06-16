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
import Reflection from "@/Components/Simulation/Reflection.vue";
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
    reflection: Reflection,
};

const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } =
    useMusic();

const TYPE_META = {
    multiple_choices: { label: "PILIHAN GANDA", color: "#3b82f6", bg: "#dbeafe" },
    true_false: { label: "Pilih Gambar Yang Benar", color: "#8b5cf6", bg: "#ede9fe" },
    case_study: { label: "Studi Kasus", color: "#0891b2", bg: "#cffafe" },
    drag_drop: { label: "Seret & Letakkan", color: "#f59e0b", bg: "#fef3c7" },
    short_answer: { label: "Isian Singkat", color: "#6366f1", bg: "#e0e7ff" },
    materials: { label: "Materi", color: "#10b981", bg: "#dcfce7" },
    simulation_clickable: { label: "Simulasi", color: "#14b8a6", bg: "#ccfbf1" },
    simulation_slider: { label: "Simulasi Slider", color: "#f43f5e", bg: "#ffe4e6" },
    simulation_comparison: { label: "Simulasi Perbandingan", color: "#f97316", bg: "#ffedd5" },
    simulation_decision: { label: "Simulasi Keputusan", color: "#8b5cf6", bg: "#ede9fe" },
    reflection: { label: "Refleksi Ilmiah", color: "#3b82f6", bg: "#dbeafe" },
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
const steps = computed(() => {
    const result = [];
    props.mission.quizzes.forEach((quiz, quizIdx) => {
        const isMaterial = quiz.type === "materials";
        const isSimulation =
            quiz.type === "simulation_clickable" ||
            quiz.type === "simulation_slider" ||
            quiz.type === "simulation_comparison" ||
            quiz.type === "simulation_decision";
        const isReflection = quiz.type === "reflection";
        const isDragDrop = quiz.type === "drag_drop";
        const questions = quiz.questions || [];

        if (isMaterial || isSimulation) {
            result.push({
                quizIndex: quizIdx,
                quiz,
                question: questions[0] ?? null,
                questionIndex: 0,
                totalInQuiz: Math.max(questions.length, 1),
                isMaterial: true,
                isReflection: false,
                isDragDrop: false,
            });
        } else if (questions.length === 0) {
            result.push({
                quizIndex: quizIdx,
                quiz,
                question: null,
                questionIndex: 0,
                totalInQuiz: 0,
                isMaterial: false,
                isReflection: isReflection,
                isDragDrop,
            });
        } else {
            questions.forEach((question, qIdx) => {
                result.push({
                    quizIndex: quizIdx,
                    quiz,
                    question,
                    questionIndex: qIdx,
                    totalInQuiz: questions.length,
                    isMaterial: false,
                    isReflection: isReflection,
                    isDragDrop,
                });
            });
        }
    });

    if (props.mission.conclusion_speech || props.mission.conclusion_body) {
        result.push({
            isConclusion: true,
            quizIndex: props.mission.quizzes.length,
            quiz: {
                type: "conclusion",
                title: `KESIMPULAN ${props.mission.name.toUpperCase()}`,
            },
            question: null,
            questionIndex: 0,
            totalInQuiz: 1,
            isMaterial: true,
            isReflection: false,
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
const phase = ref('quiz'); // 'quiz' | 'celebration'

// ── Step helpers (harus dideklarasikan sebelum timer & computed lain) ──
const step = computed(() => steps.value[currentStep.value]);
const isFirst = computed(() => currentStep.value === 0);
const isLast = computed(() => currentStep.value === steps.value.length - 1);

// ── Timer ─────────────────────────────────────────────────────
const SS_TIME_KEY = `geniuss_timer_time_${props.mission.id}`;
const SS_TIMEOUT_KEY = `geniuss_timer_out_${props.mission.id}`;

function ssGetMap(key) {
    try { return JSON.parse(sessionStorage.getItem(key) || "{}"); } catch { return {}; }
}
function ssSetMap(key, val) {
    try { sessionStorage.setItem(key, JSON.stringify(val)); } catch {}
}

const _savedTime = ssGetMap(SS_TIME_KEY);
const _savedTimeout = ssGetMap(SS_TIMEOUT_KEY);

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
const timerWarning = computed(() => timeRemaining.value > 0 && timeRemaining.value <= 60);
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
    if (activeQuizId !== null) saveTimerState(activeQuizId, timeRemaining.value);
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
    if (timedOutQuizzes.value.has(quiz.id)) {
        timeRemaining.value = 0;
        activeQuizId = quiz.id;
        return;
    }
    const saved = ssGetMap(SS_TIME_KEY);
    timeRemaining.value = saved[quiz.id] !== undefined ? saved[quiz.id] : quiz.time_limit;
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
        saveTimerState(quiz.id, timeRemaining.value);
    }, 1000);
}

watch(
    () => step.value?.quiz?.id,
    (newId, oldId) => {
        if (newId !== oldId) startQuizTimer(step.value?.quiz ?? null);
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
    if (timedOutQuizzes.value.has(s.quiz?.id)) return true;
    return isQuestionAnswered(s.question, s.quiz.type);
};

const canGoNext = computed(() => isStepAnswered(step.value));

// ── Progress ───────────────────────────────────────────────────
const totalQuizSteps = computed(() => steps.value.filter((s) => !s.isMaterial).length);
const answeredQuizSteps = computed(() =>
    steps.value.filter((s) => !s.isMaterial && isStepAnswered(s)).length,
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

// ── Speech bubble ──────────────────────────────────────────────
const BUBBLES_UNANSWERED = [
    "Gas baca soalnya dulu nih!",
    "Slow aja bacanya, dipikirin mateng-mateng!",
    "Fokus dong, kamu pasti bisa!",
    "Cek ombak dulu, baca soalnya baik-baik!",
];
const BUBBLES_ANSWERED = [
    "Cakep! Langsung gas klik Selanjutnya.",
    "Udah yakin sama jawaban ini?",
    "Kece badai! Lanjut ke soal berikutnya yuk.",
    "Mantul! Gas terus pantang mundur!",
];
const BUBBLES_MATERIAL = [
    "Kuy kepoin materinya dulu!",
    "Pahami pelan-pelan aja, chill!",
    "Catet di otak ya, ini bekal buat soal nanti!",
];

const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;

const activeSpeechText = computed(() => {
    const s = step.value;
    if (!s) return "Semangat ya!";
    if (s.isMaterial) return BUBBLES_MATERIAL[bubbleIdx.value % BUBBLES_MATERIAL.length];
    if (s.question && isQuestionAnswered(s.question, s.quiz?.type)) {
        if (s.question.options) {
            const ansId = answers[s.question.id];
            const selectedOpt = s.question.options.find(o => o.id === ansId);
            if (selectedOpt && selectedOpt.feedback) {
                return selectedOpt.feedback;
            }
        }
        return BUBBLES_ANSWERED[bubbleIdx.value % BUBBLES_ANSWERED.length];
    }
    return BUBBLES_UNANSWERED[bubbleIdx.value % BUBBLES_UNANSWERED.length];
});

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = bubbleIdx.value + 1;
        bubbleVisible.value = true;
    }, 300);
};

watch(currentStep, () => {
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => { bubbleVisible.value = true; }, 200);
});

// ── Navigation ─────────────────────────────────────────────────
const updateAnswer = (payload) => {
    if (payload?.questionId !== undefined) answers[payload.questionId] = payload.value;
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
const goPrev = () => { if (!isFirst.value) currentStep.value--; };

const goBack = () => {
    router.visit(route("playground.missions.index", props.module.id), {
        replace: true,
        preserveState: false,
        preserveScroll: false,
    });
};
const goHome = () => router.visit(route("playground.index"));

// ── Confirm modal ──────────────────────────────────────────────
const showConfirm = ref(false);
const openConfirm = () => { showConfirm.value = true; };
const closeConfirm = () => { showConfirm.value = false; };

// ── Leave confirm ─────────────────────────────────────────────
const showLeaveConfirm = ref(false);
const hasAnswers = computed(() => Object.keys(answers).length > 0);

const tryGoBack = () => {
    if (hasAnswers.value) showLeaveConfirm.value = true;
    else goBack();
};
const confirmLeave = () => { showLeaveConfirm.value = false; goBack(); };
const cancelLeave = () => { showLeaveConfirm.value = false; };

// ── Submit ─────────────────────────────────────────────────────
const submit = async () => {
    closeConfirm();
    isSubmitting.value = true;
    try {
        const res = await axios.post(
            route("playground.missions.submit", props.mission.id),
            {
                answers: Object.keys(answers).length > 0 ? answers : null,
                quiz_ids: props.mission.quizzes
                    .filter((q) =>
                        !["materials", "simulation_clickable", "simulation_slider", "simulation_comparison", "simulation_decision"].includes(q.type)
                    )
                    .map((q) => q.id),
            }
        );
        const data = res.data;
        if (data.success) {
            try {
                sessionStorage.removeItem(SS_TIME_KEY);
                sessionStorage.removeItem(SS_TIMEOUT_KEY);
            } catch {}
            // Tampilkan phase celebration, bukan redirect langsung
            phase.value = 'celebration';
        } else {
            alert("Gagal menyimpan jawaban: " + (data.error || "Unknown error"));
        }
    } catch (e) {
        console.error(e);
        const msg = e.response?.data?.message || e.message;
        alert("Terjadi kesalahan saat menyimpan jawaban: " + msg);
    } finally {
        isSubmitting.value = false;
    }
};

// ── Celebration navigation ─────────────────────────────────────
const goToMissionsIndex = () => {
    router.visit(route("playground.missions.index", props.module.id));
};
const goToPosttest = () => {
    router.visit(route("playground.posttest.show", props.module.id));
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => initAutoMusic(props.backsound), 100);
    if (step.value?.quiz) startQuizTimer(step.value.quiz);
});
onUnmounted(() => {
    clearInterval(bubbleTimer);
    clearInterval(timerInt);
    document.removeEventListener("visibilitychange", handleVisibility);
    destroyAudio();
});
</script>

<template>
    <div class="app-layout">
        <!-- ░░ FONT LOAD ░░ -->
        <div style="display: none">
            <link rel="preconnect" href="https://fonts.googleapis.com" />
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
            <link
                href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@400;500;600;700;800&family=Righteous&display=swap"
                rel="stylesheet"
            />
        </div>

        <!-- ░░ BACKGROUND ░░ -->
        <div class="bg-scene">
            <div class="sky-gradient"></div>
            <div class="bg-particles">
                <div class="particle p-1"></div>
                <div class="particle p-2"></div>
                <div class="particle p-3"></div>
                <div class="particle p-4"></div>
                <div class="particle p-5"></div>
            </div>
            <!-- Educational floating icons -->
            <div class="edu-particles">
                <svg class="edu-p ep-1" style="top:10%;left:8%;color:#1cb0f6" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <svg class="edu-p ep-2" style="top:18%;right:10%;color:#ffc800" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                <svg class="edu-p ep-3" style="top:35%;left:12%;color:#78c257" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/><path d="M6 6h10M6 10h10"/></svg>
                <svg class="edu-p ep-4" style="top:40%;right:12%;color:#ff847c" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A5 5 0 0 0 8 8c0 1 .3 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6M10 22h4"/></svg>
                <svg class="edu-p ep-5" style="top:65%;left:8%;color:#845ef7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <svg class="edu-p ep-6" style="top:72%;right:14%;color:#00bcd4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round"><line x1="6" y1="6" x2="18" y2="18"/><line x1="6" y1="18" x2="18" y2="6"/></svg>
                <svg class="edu-p ep-7" style="top:85%;left:22%;color:#e91e63" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"/><path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"/></svg>
                <svg class="edu-p ep-8" style="top:25%;left:26%;color:#1cb0f6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v4M12 17v4M3 12h4M17 12h4"/></svg>
            </div>
        </div>

        <!-- ░░ TOP NAV ░░ -->
        <header class="top-nav">
            <div class="nav-inner">
                <!-- Left: back button -->
                <div class="nav-left">
                    <button class="btn-back-nav" @click="tryGoBack" :disabled="isSubmitting" title="Kembali">
                        <ArrowLeft :size="18" :stroke-width="2.5" />
                        <span class="btn-back-label">Kembali</span>
                    </button>
                </div>

                <!-- Center: Progress Bar OR Mission name (on celebration/material) -->
                <div class="nav-center">
                    <Transition name="nav-swap" mode="out-in">
                        <div v-if="phase === 'quiz' && !step?.isMaterial" class="prog-wrapper" key="progress">
                            <div class="prog-track">
                                <div class="prog-fill" :style="{ width: progressPct + '%' }">
                                    <div class="prog-shine"></div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="nav-mission-name" key="mission-name">
                            {{ mission.name }}
                        </div>
                    </Transition>
                </div>

                <!-- Right: Timer -->
                <div class="nav-right">
                    <!-- Timer (only on quiz phase) -->
                    <Transition name="nav-swap">
                        <div
                            v-if="showTimer && phase === 'quiz'"
                            class="timer-badge"
                            :class="{
                                'timer-warning': timerWarning,
                                'timer-out': timedOutQuizzes.has(step?.quiz?.id),
                            }"
                        >
                            <div class="timer-icon-wrap">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/>
                                    <polyline points="12 6 12 12 16 14"/>
                                </svg>
                            </div>
                            <span class="timer-val">{{
                                timedOutQuizzes.has(step?.quiz?.id) ? "Waktu Habis" : timerDisplay
                            }}</span>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <!-- ░░ MAIN SCROLL CONTENT ░░ -->
        <main class="main-scroll">
            <!-- ══ PHASE: CELEBRATION ══ -->
            <Transition name="celeb-fade">
                <div v-if="phase === 'celebration'" class="celeb-overlay">
                    <!-- Mascot + bubble -->
                    <div class="celeb-mascot-container">
                        <Transition name="bbl">
                            <div v-if="bubbleVisible" class="celeb-bubble-wrap">
                                <div class="mascot-speech-bubble">
                                    <span>{{ (typeof $page.props.global_settings?.platform_mascot_dialog === 'string' && $page.props.global_settings.platform_mascot_dialog !== 'null' && $page.props.global_settings.platform_mascot_dialog !== '[null]' && $page.props.global_settings.platform_mascot_dialog.trim() !== '') ? $page.props.global_settings.platform_mascot_dialog : 'Luar biasa! Kamu telah menyelesaikan misi ini dengan sangat baik!' }}</span>
                                </div>
                                <div class="bubble-arrow"></div>
                            </div>
                        </Transition>
                        <img
                            :src="(typeof $page.props.global_settings?.platform_mascot === 'string' && $page.props.global_settings.platform_mascot !== 'null') ? $page.props.global_settings.platform_mascot : '/images/templates/pose_jempol.png'"
                            alt="Maskot"
                            class="celeb-mascot-img"
                        />
                    </div>

                    <!-- Label & sub -->
                    <div class="celeb-label">Misi Selesai!</div>
                    <div class="celeb-sub">Apa yang ingin kamu lakukan selanjutnya?</div>

                    <!-- Actions -->
                    <div class="celeb-actions">
                        <button class="btn-duo btn-duo-secondary" @click="goToMissionsIndex">
                            <Home :size="18" :stroke-width="3" />
                            <span>Ke Beranda Misi</span>
                        </button>
                        <button class="btn-duo btn-duo-success" @click="goToPosttest">
                            <span>Lanjut ke Posttest</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                    </div>
                </div>
            </Transition>

            <div class="main-wrapper" :class="{ 'main--on': ready }" v-show="phase === 'quiz'">
                <div class="pretest-layout-cols">

                    <!-- LEFT COLUMN: MASCOT (DESKTOP ONLY) -->
                    <div class="mascot-column">
                        <Transition name="bbl">
                            <div v-if="bubbleVisible" class="mascot-bubble-wrap">
                                <div class="mascot-speech-bubble">
                                    <span>{{ activeSpeechText }}</span>
                                </div>
                                <div class="bubble-arrow"></div>
                            </div>
                        </Transition>
                        <div class="mascot-image-container">
                            <img :src="mascotUrl" alt="Maskot" class="mascot-avatar-img" />
                            <div class="mascot-avatar-shadow"></div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: QUIZ CONTENT -->
                    <div class="quiz-content-column">
                        <div class="mission-container">

                            <!-- ══ CONCLUSION ══ -->
                            <template v-if="step?.isConclusion">
                                <div class="title-pill">{{ step.quiz.title }}</div>
                                <div class="question-bubble">
                                    {{ mission.conclusion_speech || 'Selamat, kamu telah menyelesaikan misi ini!' }}
                                </div>
                                <div class="component-box">
                                    <p style="font-size:16px;font-weight:700;color:#3c3c3c;line-height:1.7;text-align:center;padding:20px 0;">
                                        {{ mission.conclusion_body || 'Tidak ada penjelasan kesimpulan.' }}
                                    </p>
                                </div>
                            </template>

                            <!-- ══ NORMAL STEP ══ -->
                            <template v-else>
                                <!-- Title pill -->
                                <div class="title-pill">
                                    <span v-if="step?.isMaterial">{{ typeMeta(step?.quiz?.type).label }}</span>
                                    <span v-else>
                                        SOAL {{ currentStep + 1 }} DARI {{ steps.length }}
                                    </span>
                                </div>

                                <!-- Question bubble -->
                                <div
                                    v-if="step?.question && !step.isMaterial && step.quiz.type !== 'short_answer' && step.quiz.type !== 'reflection'"
                                    class="question-bubble"
                                    v-html="step.question.question_text"
                                ></div>
                                <div
                                    v-else-if="step?.isMaterial"
                                    class="question-bubble"
                                    v-html="step.quiz.title"
                                ></div>
                                <div v-else-if="step?.quiz?.type !== 'short_answer' && step?.quiz?.type !== 'reflection'" class="question-bubble" style="color:#94a3b8;">
                                    Tidak ada soal
                                </div>

                                <!-- Component box -->
                                <div
                                    v-if="step"
                                    class="component-box"
                                    :class="{
                                        'opts--shake': shakeActive,
                                        'box-locked': timedOutQuizzes.has(step.quiz?.id),
                                    }"
                                >
                                    <!-- Timeout overlay -->
                                    <div v-if="timedOutQuizzes.has(step.quiz?.id)" class="timeout-overlay">
                                        <Timer :size="28" :stroke-width="2" />
                                        <span>Waktu Habis</span>
                                    </div>

                                    <Transition name="slide-fade" mode="out-in">
                                        <div :key="'step-' + currentStep" style="width: 100%;">
                                            <component
                                                v-if="step.question || step.isMaterial || step.isReflection"
                                                :is="COMPONENT_MAP[step.quiz.type]"
                                                :question="step.question"
                                                :quiz="step.quiz"
                                                :modelValue="answers[step.question?.id]"
                                                @update-answer="updateAnswer"
                                            />
                                        </div>
                                    </Transition>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </div>
        </main>


        <!-- ░░ FIXED FOOTER ACTION BAR ░░ -->
        <div class="footer-bar" v-show="phase === 'quiz'">
            <div class="footer-inner">
                <!-- Left: Music + Sebelumnya -->
                <div class="footer-left">
                    <!-- Music btn (mobile only) -->
                    <button
                        class="music-footer-btn"
                        @click="toggleMusic(props.backsound ?? null)"
                        :class="{ 'music-on': musicOn }"
                        title="Musik Latar"
                    >
                        <Music2 v-if="musicOn" :size="24" :stroke-width="2.5" />
                        <VolumeX v-else :size="24" :stroke-width="2.5" />
                    </button>

                    <button
                        v-if="!isFirst"
                        class="btn-duo btn-duo-secondary"
                        @click="goPrev"
                        :disabled="isSubmitting"
                    >
                        <ArrowLeft :size="18" :stroke-width="3" />
                        <span class="btn-label">Sebelumnya</span>
                    </button>
                </div>

                <!-- Right: Selanjutnya / Selesaikan -->
                <div class="footer-right">
                    <button
                        v-if="isLast"
                        class="btn-duo btn-duo-success"
                        @click="openConfirm"
                        :disabled="isSubmitting || (!canGoNext && !step?.isMaterial)"
                    >
                        <span v-if="!isSubmitting" class="btn-label">Selesaikan Misi</span>
                        <Loader2 v-else :size="18" class="spin" :stroke-width="3" />
                        <CheckCircle2 v-if="!isSubmitting" :size="18" :stroke-width="3" />
                    </button>
                    <button
                        v-else
                        class="btn-duo btn-duo-primary"
                        @click="goNext"
                        :disabled="(!canGoNext && !step?.isMaterial) || isSubmitting"
                    >
                        <span class="btn-label">Selanjutnya</span>
                        <ArrowRight :size="18" :stroke-width="3" />
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ CONFIRM SUBMIT MODAL ══ -->
        <Transition name="overlay-fade">
            <div v-if="showConfirm" class="modal-overlay" @click.self="closeConfirm">
                <Transition name="modal-pop" appear>
                    <div v-if="showConfirm" class="modal">
                        <div class="modal-icon">
                            <Trophy :size="32" color="#58cc02" :stroke-width="1.5" />
                        </div>
                        <h2 class="modal-title">Apakah kamu yakin?</h2>
                        <p class="modal-desc">
                            Jawaban <strong>tidak bisa diubah</strong> setelah dikirim.
                        </p>
                        <div class="modal-actions">
                            <button class="btn-duo btn-duo-secondary" @click="closeConfirm" :disabled="isSubmitting">
                                Batal
                            </button>
                            <button class="btn-duo btn-duo-success" @click="submit" :disabled="isSubmitting">
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
            <div v-if="showLeaveConfirm" class="modal-overlay" @click.self="cancelLeave">
                <Transition name="modal-pop" appear>
                    <div v-if="showLeaveConfirm" class="modal">
                        <div class="modal-icon modal-icon--warn">
                            <Flag :size="28" color="#F59E0B" :stroke-width="1.8" />
                        </div>
                        <h2 class="modal-title">Keluar dari misi?</h2>
                        <p class="modal-desc">
                            Jawaban yang sudah kamu isi
                            <strong>tidak akan disimpan</strong> jika kamu keluar sekarang.
                        </p>
                        <div class="modal-actions">
                            <button class="btn-duo btn-duo-secondary" @click="cancelLeave">
                                Lanjut Ngerjain
                            </button>
                            <button class="btn-duo btn-duo-leave" @click="confirmLeave">
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
@import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@400;500;600;700;800&family=Righteous&display=swap");

/* ─── BASE ─── */
.app-layout {
    position: relative;
    width: 100vw;
    min-height: 100vh;
    font-family: "Nunito", "Baloo 2", sans-serif;
    overflow-x: hidden;
}

/* ─── BG SCENE ─── */
.bg-scene {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
}
.sky-gradient {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #dbeafe 0%, #e0f2fe 40%, #f0fdf4 100%);
}
.bg-particles {
    position: absolute;
    inset: 0;
    overflow: hidden;
}
.bg-particles .particle {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.35;
}
.bg-particles .p-1 { width: 500px; height: 500px; background: radial-gradient(circle, #bfdbfe, #93c5fd); top: -100px; left: -150px; animation: blobDrift1 18s ease-in-out infinite; }
.bg-particles .p-2 { width: 400px; height: 400px; background: radial-gradient(circle, #d1fae5, #6ee7b7); top: 30%; right: -100px; animation: blobDrift2 22s ease-in-out infinite; }
.bg-particles .p-3 { width: 300px; height: 300px; background: radial-gradient(circle, #fce7f3, #f9a8d4); bottom: 10%; left: 20%; animation: blobDrift3 16s ease-in-out infinite; }
.bg-particles .p-4 { width: 350px; height: 350px; background: radial-gradient(circle, #ede9fe, #c4b5fd); top: 50%; left: 40%; animation: blobDrift1 20s ease-in-out infinite reverse; }
.bg-particles .p-5 { width: 250px; height: 250px; background: radial-gradient(circle, #fef3c7, #fde68a); bottom: 20%; right: 20%; animation: blobDrift2 14s ease-in-out infinite reverse; }

@keyframes blobDrift1 { 0%, 100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-40px) scale(1.05); } 66% { transform: translate(-20px,20px) scale(0.95); } }
@keyframes blobDrift2 { 0%, 100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-35px,25px) scale(1.08); } }
@keyframes blobDrift3 { 0%, 100% { transform: translate(0,0) scale(1); } 40% { transform: translate(20px,-30px) scale(0.92); } 80% { transform: translate(-10px,15px) scale(1.04); } }

.edu-particles { position: absolute; inset: 0; overflow: hidden; }
.edu-p { position: absolute; width: 28px; height: 28px; opacity: 0.18; transform-origin: center; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.06)); }
.ep-1, .ep-4, .ep-7 { animation: eduFloat1 15s ease-in-out infinite; }
.ep-2, .ep-5, .ep-8 { animation: eduFloat2 19s ease-in-out infinite; }
.ep-3, .ep-6 { animation: eduFloat3 23s ease-in-out infinite; }
.ep-1 { animation-delay: 0s; } .ep-2 { animation-delay: -3s; } .ep-3 { animation-delay: -6s; }
.ep-4 { animation-delay: -9s; } .ep-5 { animation-delay: -12s; } .ep-6 { animation-delay: -2s; }
.ep-7 { animation-delay: -5s; } .ep-8 { animation-delay: -8s; }

@keyframes eduFloat1 { 0% { transform: translate(0,0) rotate(0deg) scale(1); } 25% { transform: translate(12px,-20px) rotate(45deg) scale(1.1); } 50% { transform: translate(18px,-8px) rotate(90deg) scale(0.95); } 75% { transform: translate(5px,-25px) rotate(135deg) scale(1.05); } 100% { transform: translate(0,0) rotate(180deg) scale(1); } }
@keyframes eduFloat2 { 0% { transform: translate(0,0) rotate(0deg); } 33% { transform: translate(-16px,-18px) rotate(-60deg); } 66% { transform: translate(10px,-30px) rotate(30deg); } 100% { transform: translate(0,0) rotate(0deg); } }
@keyframes eduFloat3 { 0% { transform: translate(0,0) scale(1) rotate(0deg); } 50% { transform: translate(22px,-12px) scale(1.12) rotate(90deg); } 100% { transform: translate(0,0) scale(1) rotate(0deg); } }

/* ─── TOP NAV ─── */
.top-nav {
    position: fixed;
    top: 0; left: 0; right: 0;
    z-index: 50;
    height: 70px;
    background: rgba(255,255,255,0.55);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border-bottom: 1.5px solid rgba(203,213,225,0.35);
    box-shadow: 0 2px 16px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
}
.nav-inner {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    height: 100%;
}
.nav-left { display: flex; align-items: center; width: 140px; flex-shrink: 0; }

/* ─── BACK BUTTON (nav) ─── */
.btn-back-nav {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border-radius: 12px;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 800;
    color: #ffffff;
    background: #ff4b4b;
    border: 2px solid #ff4b4b;
    border-bottom: 3px solid #ea2b2b;
    cursor: pointer;
    transition: all 0.15s ease;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    white-space: nowrap;
}
.btn-back-nav:hover:not(:disabled) { filter: brightness(1.04); border-color: #ea2b2b; }
.btn-back-nav:active:not(:disabled) { transform: translateY(2px); border-bottom-width: 1px; }
.btn-back-nav:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-back-label { display: inline; }

.nav-center {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    max-width: 560px;
    margin: 0 auto;
}

.nav-right {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 140px;
    flex-shrink: 0;
    justify-content: flex-end;
}

/* ─── MISSION NAME (shown on celebration) ─── */
.nav-mission-name {
    font-family: "Nunito", sans-serif;
    font-size: 22px;
    font-weight: 900;
    color: #3c3c3c;
    text-align: center;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

/* ─── NAV SWAP TRANSITION ─── */
.nav-swap-enter-active {
    transition: opacity 0.35s ease, transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.nav-swap-leave-active {
    transition: opacity 0.2s ease, transform 0.2s ease;
}
.nav-swap-enter-from {
    opacity: 0;
    transform: translateY(8px) scale(0.96);
}
.nav-swap-leave-to {
    opacity: 0;
    transform: translateY(-6px) scale(0.96);
}

/* ─── PROGRESS BAR ─── */
.prog-wrapper { width: 100%; max-width: 560px; padding: 0 16px; }
.prog-track {
    width: 100%;
    height: 16px;
    background: #e5e5e5;
    border-radius: 12px;
    position: relative;
    overflow: hidden;
}
.prog-fill {
    height: 100%;
    background: #58cc02;
    border-radius: 12px;
    transition: width 0.6s cubic-bezier(0.34,1.56,0.64,1);
    position: relative;
    min-width: 16px;
}
.prog-shine {
    position: absolute;
    top: 3px; left: 8px; right: 8px;
    height: 4px;
    background: rgba(255,255,255,0.3);
    border-radius: 12px;
}

/* ─── CONTENT SLIDE FADE ─── */
.slide-fade-enter-active { transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1); }
.slide-fade-leave-active { transition: all 0.3s ease; }
.slide-fade-enter-from { transform: translateX(30px); opacity: 0; }
.slide-fade-leave-to { transform: translateX(-30px); opacity: 0; }


/* ─── MUSIC BUTTON (footer — always visible) ─── */
.music-footer-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 52px; height: 52px;
    border-radius: 14px;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    background: #ffffff;
    color: #94a3b8;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.music-footer-btn:hover { background: #f8fafc; border-color: #d1d5db; }
.music-footer-btn:active { transform: translateY(2px); border-bottom-width: 2px; }
.music-footer-btn.music-on { background: #1cb0f6; border-color: #1cb0f6; border-bottom-color: #1899d6; color: white; }
.timer-badge {
    display: flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.85);
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    color: #ff9600;
    padding: 6px 12px;
    border-radius: 12px;
    font-family: "Righteous", cursive;
    font-size: 15px;
    transition: all 0.3s cubic-bezier(0.34,1.56,0.64,1);
    flex-shrink: 0;
}
.timer-icon-wrap { display: flex; align-items: center; animation: timerTick 1s ease-in-out infinite; }
@keyframes timerTick { 0%, 100% { transform: rotate(0deg); } 25% { transform: rotate(-8deg); } 75% { transform: rotate(8deg); } }
.timer-warning {
    border-color: #ff4b4b !important;
    border-bottom-color: #ea2b2b !important;
    color: #ff4b4b !important;
    background: rgba(255,75,75,0.08) !important;
    animation: pulseWarn 0.8s ease infinite !important;
}
.timer-out { border-color: #94a3b8 !important; color: #94a3b8 !important; }
@keyframes pulseWarn { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.06); } }

/* ─── MAIN SCROLL ─── */
.main-scroll {
    position: relative;
    z-index: 10;
    padding-top: 70px;
    min-height: 100vh;
}

/* ─── MAIN WRAPPER ─── */
.main-wrapper {
    position: relative;
    z-index: 10;
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 16px 20px 100px;
    opacity: 0;
    transition: opacity 0.45s;
    overflow-y: auto;
}
.main--on { opacity: 1; }

/* ─── TWO COLUMN GRID ─── */
.pretest-layout-cols {
    display: grid;
    grid-template-columns: 240px 1fr;
    gap: 32px;
    width: 100%;
    max-width: 900px;
    margin: 24px auto 0;
    align-items: start;
}

/* ─── MASCOT COLUMN ─── */
.mascot-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: sticky;
    top: 120px;
    z-index: 20;
    padding-top: 0;
}
.mascot-bubble-wrap {
    position: relative;
    margin-bottom: 20px;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.05));
    width: 100%;
    animation: floatBubble 4s ease-in-out infinite;
}
.mascot-speech-bubble {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 20px;
    padding: 16px 20px;
    text-align: center;
    font-size: 15px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1.45;
    word-break: break-word;
}
.bubble-arrow {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 16px; height: 16px;
    background-color: #ffffff;
    border-right: 2px solid #e5e5e5;
    border-bottom: 2px solid #e5e5e5;
    z-index: 1;
}
@keyframes floatBubble { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-4px); } }

.mascot-image-container {
    width: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    user-select: none;
}
.mascot-avatar-img {
    height: 250px;
    width: auto;
    object-fit: contain;
    animation: mascotIdle 4s ease-in-out infinite;
    filter: drop-shadow(0 8px 20px rgba(28,176,246,0.15));
    transform-origin: bottom center;
}
.mascot-avatar-shadow {
    width: 110px; height: 8px;
    background: radial-gradient(ellipse, rgba(0,0,0,0.12) 0%, transparent 80%);
    border-radius: 50%;
    margin-top: 6px;
    animation: shadowScale 4s ease-in-out infinite;
}
@keyframes mascotIdle { 0%, 100% { transform: translateY(0) scale(1) rotate(0deg); } 30% { transform: translateY(-8px) scaleX(1.03) scaleY(0.97) rotate(-1.5deg); } 65% { transform: translateY(-3px) scaleX(0.98) scaleY(1.02) rotate(1.5deg); } }
@keyframes shadowScale { 0%, 100% { transform: scale(1); opacity: 0.9; } 30% { transform: scale(0.8); opacity: 0.4; } 65% { transform: scale(0.95); opacity: 0.7; } }

/* ─── BUBBLE TRANSITIONS ─── */
.bbl-enter-active { transition: opacity 0.3s, transform 0.4s cubic-bezier(0.34,1.56,0.64,1); }
.bbl-leave-active { transition: opacity 0.2s; }
.bbl-enter-from { opacity: 0; transform: translateY(10px) scale(0.9); }
.bbl-leave-to { opacity: 0; }

/* ─── QUIZ CONTENT COLUMN ─── */
.quiz-content-column { min-width: 0; flex: 1; }
.mission-container { width: 100%; display: flex; flex-direction: column; }

.title-pill {
    font-family: "Nunito", sans-serif;
    font-weight: 800;
    font-size: 12px;
    color: #1cb0f6;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 8px;
    text-align: left;
}

.question-bubble {
    font-family: "Baloo 2", cursive;
    font-weight: 800;
    font-size: 20px;
    color: #3c3c3c;
    padding: 0;
    text-align: left;
    width: 100%;
    margin-bottom: 16px;
    line-height: 1.35;
    word-break: break-word;
    overflow-wrap: break-word;
}

.component-box {
    width: 100%;
    background: transparent;
    padding: 0;
    box-shadow: none;
    position: relative;
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
    background: rgba(255,255,255,0.85);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #ef4444;
    font-family: "Righteous", cursive;
    font-size: 20px;
    gap: 8px;
    border-radius: 16px;
}

/* ─── MUSIC FAB (desktop only) ─── */
.music-fab {
    position: fixed;
    bottom: 24px; left: 24px;
    z-index: 100;
    width: 48px; height: 48px;
    border-radius: 14px;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    cursor: pointer;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #afafaf;
    transition: all 0.15s ease;
}
.music-fab:hover { background: #f7f7f7; transform: translateY(-2px); border-bottom-width: 5px; }
.music-fab:active { transform: translateY(2px); border-bottom-width: 2px; }
.music-fab.music-on { background: #1cb0f6; border-color: #1cb0f6; border-bottom-color: #1899d6; color: white; }

/* ─── FIXED FOOTER BAR ─── */
.footer-bar {
    position: fixed;
    bottom: 0; left: 0; right: 0;
    height: 84px;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border-top: 2px solid rgba(229,233,240,0.8);
    box-shadow: 0 -2px 20px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    z-index: 60;
}
.footer-inner {
    width: 100%;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}
.footer-left {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    min-width: 160px;  /* reserve space so right btn doesn't shift */
}
.footer-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    justify-content: flex-end;
    min-width: 160px;  /* mirror left side width */
}

/* ─── DUOLINGO FLAT 3D BUTTONS ─── */
.btn-duo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 14px;
    font-family: "Nunito", "Baloo 2", sans-serif;
    font-size: 14px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    cursor: pointer;
    transition: filter 0.15s, transform 0.1s, border-bottom-width 0.1s;
    user-select: none;
    outline: none;
    white-space: nowrap;
}
.btn-duo-primary {
    background-color: #1cb0f6;
    border: 2px solid #1cb0f6;
    border-bottom: 5px solid #1899d6;
    color: #ffffff;
}
.btn-duo-primary:hover:not(:disabled) { filter: brightness(1.04); }
.btn-duo-primary:active:not(:disabled) { transform: translateY(3px); border-bottom-width: 2px; }

.btn-duo-success {
    background-color: #58cc02;
    border: 2px solid #58cc02;
    border-bottom: 5px solid #58a700;
    color: #ffffff;
}
.btn-duo-success:hover:not(:disabled) { filter: brightness(1.04); }
.btn-duo-success:active:not(:disabled) { transform: translateY(3px); border-bottom-width: 2px; }

.btn-duo-secondary {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-bottom: 5px solid #cbd5e1;
    color: #afafaf;
}
.btn-duo-secondary:hover:not(:disabled) { background-color: #f7f7f7; color: #777777; }
.btn-duo-secondary:active:not(:disabled) { transform: translateY(3px); border-bottom-width: 2px; }

.btn-duo-leave {
    background-color: #ff4b4b;
    border: 2px solid #ff4b4b;
    border-bottom: 5px solid #ea2b2b;
    color: #ffffff;
}
.btn-duo-leave:hover:not(:disabled) { filter: brightness(1.04); }
.btn-duo-leave:active:not(:disabled) { transform: translateY(3px); border-bottom-width: 2px; }

.btn-duo:disabled {
    background-color: #e5e5e5 !important;
    border-color: #e5e5e5 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    color: #afafaf !important;
    cursor: not-allowed;
    transform: none !important;
}

.btn-label { display: inline; }

/* ─── MODALS ─── */
.modal-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background: rgba(0,0,0,0.45);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal {
    background: #fff;
    border-radius: 24px;
    padding: 32px 28px 24px;
    width: 100%;
    max-width: 380px;
    box-shadow: 0 24px 60px rgba(0,0,0,0.18);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    text-align: center;
}
.modal-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: #d1fae5;
    border: 3px solid #6ee7b7;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 18px rgba(52,211,153,0.22);
}
.modal-icon--warn { background: #fef3c7; border-color: #fcd34d; }
.modal-title {
    font-family: "Baloo 2", cursive;
    font-size: 22px;
    font-weight: 800;
    color: #3c3c3c;
    margin: 0;
}
.modal-desc {
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 700;
    color: #777777;
    line-height: 1.65;
    margin: 0;
}
.modal-desc strong { color: #ff4b4b; }
.modal-actions {
    display: flex;
    gap: 10px;
    width: 100%;
    margin-top: 6px;
    justify-content: center;
}
.modal-actions .btn-duo { flex: 1; padding: 12px 16px; font-size: 13px; }

/* ─── SHAKE ─── */
.opts--shake { animation: optShake 0.5s ease; }
@keyframes optShake { 0%, 100% { transform: translateX(0); } 20%, 60% { transform: translateX(-6px); } 40%, 80% { transform: translateX(6px); } }
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── TRANSITIONS ─── */
.nav-swap-enter-active, .nav-swap-leave-active { transition: all 0.35s ease; }
.nav-swap-enter-from { opacity: 0; transform: scale(0.95); }
.nav-swap-leave-to { opacity: 0; transform: scale(0.95); }

.slide-fade-enter-active { transition: all 0.45s cubic-bezier(0.34,1.56,0.64,1); }
.slide-fade-leave-active { transition: all 0.28s cubic-bezier(0.4,0,1,1); }
.slide-fade-enter-from { opacity: 0; transform: translateY(12px) scale(0.94); }
.slide-fade-leave-to { opacity: 0; transform: translateY(-10px) scale(0.94); }

.overlay-fade-enter-active { transition: opacity 0.25s ease; }
.overlay-fade-leave-active { transition: opacity 0.2s ease; }
.overlay-fade-enter-from, .overlay-fade-leave-to { opacity: 0; }
.modal-pop-enter-active { transition: opacity 0.3s ease, transform 0.35s cubic-bezier(0.34,1.56,0.64,1); }
.modal-pop-leave-active { transition: opacity 0.18s ease, transform 0.18s ease; }
.modal-pop-enter-from { opacity: 0; transform: scale(0.82) translateY(24px); }
.modal-pop-leave-to { opacity: 0; transform: scale(0.94); }

/* ─── DEEP OVERRIDES ─── */
:deep(.tf-option) {
    border: 2px solid #e5e5e5 !important;
    border-bottom: 5px solid #cbd5e1 !important;
    border-radius: 16px !important;
    background-color: #ffffff !important;
    box-shadow: none !important;
    display: flex !important;
    align-items: center !important;
    min-height: 56px !important;
}
:deep(.tf-option:hover:not(.selected)) { background-color: #f7f7f7 !important; }
:deep(.tf-option.selected) { background-color: #ddf4ff !important; border-color: #1cb0f6 !important; border-bottom-color: #1899d6 !important; }
:deep(.tf-option.selected .tf-label) { color: #1cb0f6 !important; }
:deep(.tf-options) { gap: 16px !important; }
:deep(.tf-inner) { padding: 14px 20px !important; width: 100% !important; justify-content: space-between !important; }
:deep(.tf-label) { text-align: left !important; font-family: "Nunito", sans-serif !important; font-weight: 800 !important; color: #3c3c3c !important; font-size: 16px !important; }
:deep(.drag-item), :deep(.dd-item) { border: 2px solid #e5e5e5 !important; border-bottom: 4px solid #cbd5e1 !important; border-radius: 12px !important; background-color: #ffffff !important; font-family: "Nunito", sans-serif !important; font-weight: 800 !important; color: #3c3c3c !important; padding: 10px 16px !important; }
:deep(.sa-input), :deep(.short-answer-input), :deep(textarea) { border: 2px solid #e5e5e5 !important; border-radius: 16px !important; padding: 14px 18px !important; font-family: "Nunito", sans-serif !important; font-weight: 700 !important; font-size: 16px !important; color: #3c3c3c !important; outline: none !important; transition: border-color 0.2s !important; box-shadow: none !important; width: 100% !important; }
:deep(.sa-input:focus), :deep(.short-answer-input:focus), :deep(textarea:focus) { border-color: #1cb0f6 !important; }

/* ─── MOBILE RESPONSIVE ─── */
@media (max-width: 768px) {
    .nav-inner { padding: 0 12px; gap: 8px; }
    .timer-badge { font-size: 12px; padding: 5px 8px; gap: 4px; }
    .timer-badge .timer-icon-wrap svg { width: 12px; height: 12px; }
    .music-fab { display: none; }
    .music-footer-btn { width: 42px; height: 42px; position: static; }
    .music-footer-btn svg { width: 18px; height: 18px; }
    .prog-track { height: 14px; }
    .main-wrapper { padding: 10px 16px 100px; }
    .pretest-layout-cols { grid-template-columns: 1fr; gap: 0; margin: 15px auto 0; }
    .mascot-column { display: none !important; }
    .question-bubble { font-size: 20px; margin-bottom: 16px; }
    .title-pill { font-size: 12px; margin-bottom: 6px; }
    .footer-bar { height: 76px; }
    .footer-inner { padding: 0 14px; }
    .footer-left { min-width: 0; }
    .footer-right { min-width: 0; }
    .btn-duo { padding: 10px 18px; font-size: 13px; }
    .btn-back-label { display: none; }
    .btn-back-nav { padding: 8px 10px; gap: 0; }
}

@media (max-width: 480px) {
    .nav-inner { gap: 6px; padding: 0 10px; }
    .timer-badge { font-size: 11px; padding: 4px 7px; }
    .btn-label { display: none; }
    .btn-duo {
        padding: 10px;
        min-width: 42px;
        height: 42px;
        gap: 0;
        border-radius: 12px;
    }
    .footer-bar { height: 70px; }
    .footer-inner { padding: 0 12px; }
    .music-footer-btn { width: 38px; height: 38px; }
    .music-footer-btn svg { width: 16px; height: 16px; }
    .footer-left { min-width: 0; }
    .footer-right { min-width: 0; }
}

@media (min-width: 769px) {
    .music-footer-btn { position: absolute; left: 24px; }
    .footer-left { padding-left: 64px; }
}

/* ─── CELEBRATION (same style as Pretest) ─── */
.celeb-overlay {
    position: fixed;
    inset: 0;
    z-index: 200;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 24px 20px;
    overflow-y: auto;
    gap: 0;
    animation: celebFadeIn 0.5s ease both;
}

@keyframes celebFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.celeb-mascot-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: celebPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.1s both;
}

@keyframes celebPop {
    from { opacity: 0; transform: scale(0.8) translateY(16px); }
    to { opacity: 1; transform: scale(1) translateY(0); }
}

.celeb-bubble-wrap {
    position: relative;
    width: 260px;
    margin-bottom: 16px;
    filter: drop-shadow(0 4px 12px rgba(0,0,0,0.05));
    animation: floatBubble 4s ease-in-out infinite;
}

@keyframes floatBubble {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-5px); }
}

/* Speech bubble appearance */
.celeb-bubble-wrap .mascot-speech-bubble {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 20px;
    padding: 16px 20px;
    text-align: center;
    font-size: 15px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1.45;
    word-break: break-word;
}

.celeb-bubble-wrap .bubble-arrow {
    position: absolute;
    bottom: -9px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 16px;
    height: 16px;
    background-color: #ffffff;
    border-right: 2px solid #e5e5e5;
    border-bottom: 2px solid #e5e5e5;
    z-index: 1;
}

.celeb-mascot-img {
    height: 180px;
    width: auto;
    object-fit: contain;
    filter: drop-shadow(0 6px 16px rgba(28,176,246,0.15));
}

.celeb-label {
    font-family: "Baloo 2", cursive;
    font-size: 32px;
    font-weight: 800;
    color: #3c3c3c;
    margin-top: 16px;
    margin-bottom: 6px;
    animation: slideUp 0.5s ease 0.3s both;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to { opacity: 1; transform: translateY(0); }
}

.celeb-sub {
    font-size: 15px;
    color: #777777;
    font-weight: 700;
    margin-bottom: 20px;
    animation: slideUp 0.5s ease 0.4s both;
}

.celeb-actions {
    display: flex;
    gap: 12px;
    justify-content: center;
    flex-wrap: wrap;
    animation: slideUp 0.5s ease 0.5s both;
}

.celeb-actions .btn-duo {
    min-width: 160px;
}

.celeb-fade-enter-active {
    transition: opacity 0.4s ease, transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.celeb-fade-enter-from {
    opacity: 0;
    transform: scale(0.95);
}

/* ─── CELEBRATION MOBILE ─── */
@media (max-width: 768px) {
    .celeb-overlay {
        padding: 16px 16px;
        justify-content: center;
        gap: 0;
    }

    .celeb-mascot-img {
        height: 130px;
    }

    .celeb-bubble-wrap {
        width: 220px;
        margin-bottom: 10px;
    }

    .mascot-speech-bubble {
        font-size: 13px;
        padding: 12px 14px;
    }

    .celeb-label {
        font-size: 24px;
        margin-top: 10px;
        margin-bottom: 4px;
    }

    .celeb-sub {
        font-size: 13px;
        margin-bottom: 16px;
    }

    .celeb-actions {
        gap: 10px;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .celeb-actions .btn-duo {
        min-width: 0;
        width: 100%;
        max-width: 280px;
    }
}
</style>
