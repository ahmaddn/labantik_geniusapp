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
import { useSfx } from "@/Composable/useSfx";


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
const { playPop, playSuccess, playFail } = useSfx();
const confettiCanvas = ref(null);
const mascotClicked = ref(false);


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
const resetKey = ref(0);

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
    timeRemaining.value = saved[quiz.id] !== undefined ? saved[quiz.id] : (quiz.time_limit * 60);
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

function retryQuiz(quizId) {
    if (!quizId) return;
    
    const mapTimeout = ssGetMap(SS_TIMEOUT_KEY);
    delete mapTimeout[quizId];
    ssSetMap(SS_TIMEOUT_KEY, mapTimeout);

    const mapTime = ssGetMap(SS_TIME_KEY);
    delete mapTime[quizId];
    ssSetMap(SS_TIME_KEY, mapTime);

    const newTimeouts = new Set(timedOutQuizzes.value);
    newTimeouts.delete(quizId);
    timedOutQuizzes.value = newTimeouts;

    if (step.value && step.value.quiz && step.value.quiz.id === quizId) {
        // Hentikan timer lama secara paksa tanpa menyimpan state sisa waktu (karena sudah diset ke 0 sebelumnya)
        clearInterval(timerInt);
        timerInt = null;
        activeQuizId = null; // Mencegah pauseActiveTimer() menyimpan waktu 0 ke sessionStorage
        
        timeRemaining.value = step.value.quiz.time_limit ? (step.value.quiz.time_limit * 60) : 60;
        startQuizTimer(step.value.quiz);
    }
}

const handleGlobalRetry = () => {
    if (step.value?.question?.id) {
        delete answers[step.value.question.id];
    }
    isAnswerChecked.value = false;
    isCurrentCorrect.value = false;
    resetKey.value++;
    if (timedOutQuizzes.value.has(step.value?.quiz?.id)) {
        retryQuiz(step.value?.quiz?.id);
    }
};

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
    const templateMascots = props.module?.template?.mascots || [];
    const getMascotImage = (keyword, fallback) => {
        const m = templateMascots.find(m => m.name_pose?.toLowerCase()?.includes(keyword));
        if (m && m.image) {
            return m.image.startsWith('http') || m.image.startsWith('/') 
                ? m.image 
                : `${window.location.origin}/storage/${m.image}`;
        }
        if (templateMascots.length > 0 && templateMascots[0].image) {
             const m0 = templateMascots[0].image;
             return m0.startsWith('http') || m0.startsWith('/') ? m0 : `${window.location.origin}/storage/${m0}`;
        }
        return fallback;
    };
    
    const s = step.value;
    if (!s) return getMascotImage('pikir', "/images/templates/pose_pikir.png");
    if (s.isConclusion) return getMascotImage('jempol', "/images/templates/pose_jempol.png");
    if (s.isMaterial) return getMascotImage('nunjuk', "/images/templates/pose_nunjuk.png");
    
    if (s.question && isStepAnswered(s)) {
        return getMascotImage('jempol', "/images/templates/pose_jempol.png");
    }
    return getMascotImage('pikir', "/images/templates/pose_pikir.png");
});

const backgroundUrl = computed(() => {
    if (props.background) return props.background;
    const bgs = props.module?.template?.backgrounds || [];
    if (bgs.length > 0 && bgs[0].image) {
        const bg = bgs[0].image;
        return bg.startsWith('http') || bg.startsWith('/') ? bg : `/storage/${bg}`;
    }
    return null;
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

const isStepCorrect = (s) => {
    if (!s || !s.question) return true;
    const ans = answers[s.question.id];
    if (ans === undefined || ans === null) return false;
    
    if (s.quiz?.type === "multiple_choices" || s.quiz?.type === "true_false" || s.quiz?.type === "case_study") {
        if (s.question.options) {
            const correctOpt = s.question.options.find(o => o.is_correct);
            return correctOpt && String(correctOpt.id) === String(ans);
        }
    }
    return true; // Fallback
};

const activeSpeechText = computed(() => {
    const s = step.value;
    if (!s) return "Semangat ya!";
    if (s.isConclusion) return props.mission.conclusion_speech || "Selesai! Jangan lupa catat poin pentingnya.";
    if (s.isMaterial) {
        if (s.question && s.question.speech_bubble) return s.question.speech_bubble;
        return BUBBLES_MATERIAL[bubbleIdx.value % BUBBLES_MATERIAL.length];
    }
    if (s.question && isQuestionAnswered(s.question, s.quiz?.type)) {
        const correct = isStepCorrect(s);
        if (correct) {
            if (s.question.feedback_correct) return s.question.feedback_correct;
            if (s.question.options) {
                const ansId = answers[s.question.id];
                const selectedOpt = s.question.options.find(o => o.id === ansId);
                if (selectedOpt && selectedOpt.feedback) {
                    return selectedOpt.feedback;
                }
            }
            return BUBBLES_ANSWERED[bubbleIdx.value % BUBBLES_ANSWERED.length];
        } else {
            if (s.question.feedback_incorrect) return s.question.feedback_incorrect;
            return "Ayo coba lagi, periksa kembali jawabanmu!";
        }
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

const handleMascotClick = () => {
    playPop();
    mascotClicked.value = true;
    setTimeout(() => { mascotClicked.value = false; }, 500);
    rotateBubble();
};

function startConfetti(canvas) {
    if (!canvas) return;
    const ctx = canvas.getContext("2d");
    let width = canvas.width = window.innerWidth;
    let height = canvas.height = window.innerHeight;

    window.addEventListener("resize", () => {
        if (!canvas) return;
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    });

    const particles = [];
    const colors = ["#1cb0f6", "#58cc02", "#ff9600", "#a855f7", "#ff4b4b", "#ffc800"];

    for (let i = 0; i < 100; i++) {
        particles.push({
            x: Math.random() * width,
            y: Math.random() * height - height,
            r: Math.random() * 6 + 4,
            d: Math.random() * height,
            color: colors[Math.floor(Math.random() * colors.length)],
            tilt: Math.random() * 10 - 5,
            tiltAngleIncremental: Math.random() * 0.07 + 0.02,
            tiltAngle: 0
        });
    }

    function draw() {
        if (!canvas) return;
        ctx.clearRect(0, 0, width, height);

        let active = false;
        particles.forEach((p, idx) => {
            p.tiltAngle += p.tiltAngleIncremental;
            p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
            p.x += Math.sin(p.tiltAngle);
            p.tilt = Math.sin(p.tiltAngle - idx / 3) * 15;

            if (p.y < height) {
                active = true;
            }

            ctx.beginPath();
            ctx.lineWidth = p.r;
            ctx.strokeStyle = p.color;
            ctx.moveTo(p.x + p.tilt + p.r / 2, p.y);
            ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 2);
            ctx.stroke();
        });

        if (active) {
            requestAnimationFrame(draw);
        } else {
            ctx.clearRect(0, 0, width, height);
        }
    }

    draw();
}

watch(phase, (newPhase) => {
    if (newPhase === "celebration") {
        playSuccess();
        setTimeout(() => {
            if (confettiCanvas.value) {
                startConfetti(confettiCanvas.value);
            }
        }, 150);
    }
});


const isAnswerChecked = ref(false);
const isCurrentCorrect = ref(false);

const isCheckable = computed(() => {
    const s = step.value;
    if (!s) return false;
    if (s.isMaterial || s.isReflection || !s.question) return false;
    const t = s.quiz?.type;
    return !["materials", "simulation_clickable", "simulation_slider", "simulation_comparison", "simulation_decision"].includes(t);
});

function checkAnswerLocal() {
    const s = step.value;
    if (!s || !s.question) return false;
    const q = s.question;
    const ans = answers[q.id];
    if (ans === undefined || ans === null) return false;

    if (s.quiz?.type === "drag_drop") {
        const items = q.drag_drop_items || [];
        if (items.length === 0) return false;
        return items.every(item => ans[item.id] !== undefined && String(ans[item.id]) === String(item.correct_group_id));
    }

    if (s.quiz?.type === "short_answer") {
        const userText = String(ans).trim().toLowerCase();
        if (q.options && q.options.length > 0) {
            return q.options.some(opt => opt.is_correct && String(opt.option_text).trim().toLowerCase() === userText);
        }
        return false;
    }

    if (q.options && q.options.length > 0) {
        if (Array.isArray(ans)) {
            const correctIds = q.options.filter(o => o.is_correct).map(o => String(o.id)).sort();
            const userIds = ans.map(id => String(id)).sort();
            return JSON.stringify(userIds) === JSON.stringify(correctIds);
        }
        const selectedOpt = q.options.find(o => String(o.id) === String(ans));
        return selectedOpt ? !!selectedOpt.is_correct : false;
    }
    return true;
}

const correctText = computed(() => {
    const s = step.value;
    if (!s || !s.question) return "";
    const q = s.question;

    if (s.quiz?.type === "drag_drop") {
        const groups = q.drag_drop_groups || [];
        const items = q.drag_drop_items || [];
        return items.map(item => {
            const group = groups.find(g => String(g.id) === String(item.correct_group_id));
            return `"${item.item_text}" masuk ke "${group ? group.group_name : '?'}"`;
        }).join(", ");
    }

    if (q.options && q.options.length > 0) {
        const correctOpts = q.options.filter(o => o.is_correct);
        return correctOpts.map(o => o.option_text || o.text).join(", ");
    }

    return "";
});

function onCheckAnswer() {
    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => {
            shakeActive.value = false;
        }, 600);
        return;
    }
    isCurrentCorrect.value = checkAnswerLocal();
    isAnswerChecked.value = true;
    if (isCurrentCorrect.value) {
        playSuccess();
    } else {
        playFail();
    }
}

// ── Navigation ─────────────────────────────────────────────────
const updateAnswer = (payload) => {
    if (isAnswerChecked.value) return;
    if (payload?.questionId !== undefined) answers[payload.questionId] = payload.value;
};

const goNext = () => {
    if (isCheckable.value && !isAnswerChecked.value) {
        onCheckAnswer();
        return;
    }

    isAnswerChecked.value = false;
    isCurrentCorrect.value = false;

    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => (shakeActive.value = false), 600);
        return;
    }
    if (!isLast.value) {
        currentStep.value++;
    } else {
        openConfirm();
    }
};
const goPrev = () => {
    if (!isFirst.value) {
        currentStep.value--;
        isAnswerChecked.value = false;
        isCurrentCorrect.value = false;
    }
};

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
            // Redirect langsung ke halaman feedback kuis hasil misi
            router.visit(route("playground.missions.result", props.mission.id));
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

const goToNextMission = () => {
    if (props.mission.next_mission_id) {
        router.visit(route("playground.missions.show", props.mission.next_mission_id));
    } else {
        goToMissionsIndex();
    }
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
            <template v-if="backgroundUrl">
                <div class="custom-bg" :style="{ backgroundImage: `url(${backgroundUrl})` }"></div>
            </template>
            <template v-else>
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
            </template>
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
                        <button v-if="!mission.has_next_mission" class="btn-duo btn-duo-success" @click="goToPosttest">
                            <span>Kerjakan Postest</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                        <button v-else class="btn-duo btn-duo-primary" @click="goToNextMission">
                            <span>Lanjutkan Misi Selanjutnya</span>
                            <ArrowRight :size="18" :stroke-width="3" />
                        </button>
                    </div>
                </div>
            </Transition>

            <div class="main-wrapper" :class="{ 'main--on': ready }" v-show="phase === 'quiz'">
                <!-- Confetti Overlay Canvas -->
                <canvas ref="confettiCanvas" class="confetti-canvas"></canvas>

                <div class="pretest-layout-cols">

                    <!-- LEFT COLUMN: MASCOT (DESKTOP ONLY) -->
                    <div class="mascot-column" @click="handleMascotClick">
                        <Transition name="bbl">
                            <div v-if="bubbleVisible" class="mascot-bubble-wrap">
                                <div class="mascot-speech-bubble">
                                    <span>{{ activeSpeechText }}</span>
                                </div>
                                <div class="bubble-arrow"></div>
                            </div>
                        </Transition>
                        <div class="mascot-image-container mascot-interactive" :class="{ 'mascot-wiggle': mascotClicked }">
                            <img :src="mascotUrl" alt="Maskot" class="mascot-avatar-img" />
                            <div class="mascot-avatar-shadow"></div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: QUIZ CONTENT -->
                    <div class="quiz-content-column">
                        <div class="mission-container">

                            <!-- ══ CONCLUSION ══ -->
                            <template v-if="step?.isConclusion">
                                <div class="conclusion-container">
                                    <div class="conclusion-title-badge">
                                        <Star class="conclusion-icon" :size="20" />
                                        <span>{{ step.quiz.title }}</span>
                                        <Star class="conclusion-icon" :size="20" />
                                    </div>
                                    <h2 class="conclusion-subtitle">Kerja bagus! Kamu telah menyelesaikan misi ini <PartyPopper class="inline-icon" :size="24" /></h2>

                                    <div class="conclusion-scroll">
                                        <div class="scroll-top"></div>
                                        <div class="scroll-middle">
                                            <div class="scroll-content">
                                                <div class="scroll-pin">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="#ef4444" stroke="#991b1b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pin"><line x1="12" x2="12" y1="17" y2="22"/><path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a2 2 0 0 0 0-4H8a2 2 0 0 0 0 4h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"/></svg>
                                                </div>
                                                <h3 class="scroll-heading">Ringkasan Pembelajaran</h3>
                                                <div class="scroll-text-body conclusion-body-content" v-html="mission.conclusion_body || 'Tidak ada penjelasan kesimpulan.'"></div>
                                            </div>
                                        </div>
                                        <div class="scroll-bottom"></div>
                                    </div>
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

                                <div v-if="step?.quiz?.image && !step?.isConclusion && !(step?.isMaterial && step?.question?.layout_type === 'cover_page')" style="margin-bottom: 16px;">
                                    <img :src="step.quiz.image.startsWith('http') ? step.quiz.image : `/storage/${step.quiz.image}`" alt="Quiz Image" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 16px; border: 2px solid #e2e8f0;" />
                                </div>

                                <!-- Question bubble -->
                                <div
                                    v-if="step?.question && !step.isMaterial && step.quiz.type !== 'short_answer' && step.quiz.type !== 'reflection'"
                                    class="question-bubble"
                                    v-html="step.question.question_text"
                                ></div>
                                <div
                                    v-else-if="step?.isMaterial && step?.question?.layout_type !== 'cover_page'"
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
                                    :style="{ pointerEvents: (isAnswerChecked && isCheckable) ? 'none' : 'auto', opacity: (isAnswerChecked && isCheckable) ? 0.8 : 1 }"
                                >
                                    <!-- Timeout overlay -->
                                    <div v-if="timedOutQuizzes.has(step.quiz?.id)" class="timeout-overlay">
                                        <Timer :size="28" :stroke-width="2" />
                                        <span>Waktu Habis</span>
                                    </div>

                                    <Transition name="slide-fade" mode="out-in">
                                        <div :key="'step-' + currentStep + '-' + resetKey" style="width: 100%;">
                                            <component
                                                v-if="step.question || step.isMaterial || step.isReflection"
                                                :is="COMPONENT_MAP[step.quiz.type]"
                                                :question="step.question"
                                                :quiz="step.quiz"
                                                :modelValue="answers[step.question?.id]"
                                                :disabled="timedOutQuizzes.has(step.quiz?.id)"
                                                @update-answer="updateAnswer"
                                            />
                                            
                                            <!-- Global Retry Button -->
                                            <div v-if="(step.question || step.isReflection) && step.quiz.type !== 'materials'" class="global-actions">
                                                <button class="global-reset-btn" @click="handleGlobalRetry">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                                    Ulangi Soal Ini
                                                </button>
                                            </div>
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

                <div class="footer-right">
                    <button
                        v-if="isCheckable && !isAnswerChecked"
                        class="btn-duo btn-duo-success"
                        @click="onCheckAnswer"
                        :disabled="!canGoNext || isSubmitting"
                    >
                        <span>Periksa Jawaban</span>
                        <CheckCircle2 :size="18" :stroke-width="3" />
                    </button>
                    <template v-else-if="!isCheckable">
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
                    </template>
                </div>
            </div>
        </div>

        <!-- Slide-up Instant Feedback Sheet -->
        <Transition name="slide-up">
            <div 
                v-if="isAnswerChecked && isCheckable" 
                class="feedback-sheet" 
                :class="isCurrentCorrect ? 'feedback-sheet--correct' : 'feedback-sheet--incorrect'"
            >
                <div class="feedback-sheet-inner">
                    <div class="feedback-status-wrap">
                        <div class="feedback-icon-circle">
                            <svg v-if="isCurrentCorrect" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                            </svg>
                        </div>
                        <div class="feedback-texts">
                            <h4 class="feedback-title">
                                {{ isCurrentCorrect ? 'Luar Biasa! Jawabanmu Benar' : 'Kurang Tepat!' }}
                            </h4>
                            <div v-if="!isCurrentCorrect" class="feedback-correct-answer">
                                <span class="font-bold">Jawaban Benar:</span> {{ correctText }}
                            </div>
                            <div v-if="step?.question?.explanation" class="feedback-explanation">
                                <div class="explanation-title">Pembahasan:</div>
                                <div class="explanation-body" v-html="step.question.explanation"></div>
                            </div>
                        </div>
                    </div>
                    
                    <button
                        v-if="isLast"
                        class="btn-duo btn-duo-success btn-feedback-next"
                        @click="goNext"
                        :disabled="isSubmitting"
                    >
                        <span v-if="!isSubmitting">Selesaikan Misi</span>
                        <Loader2 v-else :size="18" class="spin" />
                        <CheckCircle2 v-if="!isSubmitting" :size="18" />
                    </button>
                    <button
                        v-else
                        class="btn-duo btn-duo-primary btn-feedback-next"
                        @click="goNext"
                    >
                        <span>Lanjutkan</span>
                        <ArrowRight :size="18" />
                    </button>
                </div>
            </div>
        </Transition>

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
.custom-bg {
    position: absolute;
    inset: 0;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    opacity: 1;
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
.global-actions {
    display: flex; 
    justify-content: flex-end; 
    margin-top: 15px; 
    position: relative; 
    z-index: 30; 
    pointer-events: auto;
}
.global-reset-btn {
    display: inline-flex; 
    align-items: center; 
    gap: 5px;
    padding: 6px 13px;
    background: rgba(255,255,255,.7); 
    border: 1.5px solid rgba(29,78,216,.2);
    border-radius: 8px;
    font-size: 11.5px; 
    font-weight: 800; 
    color: #1d4ed8;
    cursor: pointer; 
    transition: all .18s;
    backdrop-filter: blur(4px);
}
.global-reset-btn:hover { 
    background: rgba(255,255,255,.9); 
    border-color: rgba(29,78,216,.35); 
}

/* Conclusion Styles */
.conclusion-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    padding: 1rem 0 3rem;
    animation: fadeIn 0.8s ease-out;
}

.conclusion-title-badge {
    background: linear-gradient(180deg, #16a34a, #15803d);
    color: white;
    padding: 12px 32px;
    border-radius: 999px;
    font-size: 1.25rem;
    font-weight: 900;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 4px 0 #14532d, 0 8px 16px rgba(0,0,0,0.15);
    border: 3px solid #fef08a;
    letter-spacing: 1px;
}

.conclusion-icon {
    color: #fef08a;
    fill: #facc15;
}

.conclusion-subtitle {
    font-size: 1.25rem;
    font-weight: 800;
    color: #1e293b;
    text-align: center;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    background: white;
    padding: 10px 24px;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border: 2px solid #e2e8f0;
}

.conclusion-scroll {
    position: relative;
    width: 100%;
    max-width: 600px;
    margin-top: 1rem;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.15));
}

.scroll-top {
    height: 40px;
    background: #fde08b;
    border-radius: 20px 20px 0 0;
    border: 2px solid #b45309;
    border-bottom: none;
    box-shadow: inset 0 -10px 10px rgba(0,0,0,0.05), inset 0 10px 10px rgba(255,255,255,0.4);
    position: relative;
    z-index: 2;
}
.scroll-top::after {
    content: '';
    position: absolute;
    left: -10px; right: -10px; top: 10px; height: 20px;
    background: #fcd34d;
    border: 2px solid #b45309;
    border-radius: 10px;
    box-shadow: 0 4px 0 rgba(0,0,0,0.1);
}

.scroll-middle {
    background: #fef3c7;
    border-left: 2px solid #b45309;
    border-right: 2px solid #b45309;
    padding: 10px 30px;
    position: relative;
    z-index: 1;
    background-image: 
        linear-gradient(90deg, rgba(180,83,9,0.05) 0%, transparent 5%, transparent 95%, rgba(180,83,9,0.05) 100%),
        repeating-linear-gradient(0deg, transparent, transparent 29px, rgba(180,83,9,0.05) 30px);
}

.scroll-bottom {
    height: 40px;
    background: #fde08b;
    border-radius: 0 0 20px 20px;
    border: 2px solid #b45309;
    border-top: none;
    box-shadow: inset 0 10px 10px rgba(0,0,0,0.05), inset 0 -10px 10px rgba(255,255,255,0.4);
    position: relative;
    z-index: 2;
}
.scroll-bottom::after {
    content: '';
    position: absolute;
    left: -10px; right: -10px; bottom: 10px; height: 20px;
    background: #fcd34d;
    border: 2px solid #b45309;
    border-radius: 10px;
    box-shadow: 0 4px 0 rgba(0,0,0,0.1);
}

.scroll-content {
    background: rgba(255,255,255,0.6);
    border-radius: 16px;
    padding: 30px;
    position: relative;
    border: 2px dashed #d97706;
}

.scroll-pin {
    position: absolute;
    top: -20px;
    left: 50%;
    transform: translateX(-50%);
    filter: drop-shadow(0 4px 6px rgba(0,0,0,0.2));
    z-index: 10;
}

.scroll-heading {
    text-align: center;
    font-size: 1.4rem;
    font-weight: 900;
    color: #92400e;
    margin-bottom: 20px;
    margin-top: 10px;
    text-shadow: 1px 1px 0 rgba(255,255,255,0.8);
}

.scroll-text-body {
    font-size: 1rem;
    line-height: 1.6;
    color: #451a03;
    font-weight: 600;
}

.scroll-text-body :deep(p) {
    margin-bottom: 12px;
}
.scroll-text-body :deep(ul) {
    list-style: none;
    padding-left: 0;
}
.scroll-text-body :deep(li) {
    position: relative;
    padding-left: 32px;
    margin-bottom: 12px;
}
.scroll-text-body :deep(li::before) {
    content: '✅';
    position: absolute;
    left: 0;
    top: -2px;
    font-size: 1.1rem;
}

@media (max-width: 640px) {
    .conclusion-title-badge { font-size: 1rem; padding: 10px 24px; }
    .conclusion-subtitle { font-size: 1rem; padding: 8px 16px; }
    .scroll-content { padding: 20px; }
    .scroll-heading { font-size: 1.2rem; }
}

/* ── Gamification CSS ── */
.confetti-canvas {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    pointer-events: none;
    z-index: 999;
}

.mascot-interactive {
    cursor: pointer;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.mascot-interactive:hover {
    transform: scale(1.05) translateY(-5px);
}
.mascot-wiggle {
    animation: mascotWiggle 0.5s ease-in-out;
}

@keyframes mascotWiggle {
    0%, 100% { transform: rotate(0) scale(1); }
    25% { transform: rotate(-6deg) scale(1.06); }
    75% { transform: rotate(6deg) scale(1.06); }
}

/* Pulse animation for active check/next button */
.btn-duo-success:not(:disabled), .btn-duo-primary:not(:disabled) {
    animation: footerPulse 2s infinite;
}

@keyframes footerPulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.02); box-shadow: 0 0 12px rgba(88, 204, 2, 0.3); }
    100% { transform: scale(1); }
}

/* ── Bottom drawer feedback sheet ── */
.feedback-sheet {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 24px;
    z-index: 100;
    border-top: 4px solid;
    box-shadow: 0 -10px 30px rgba(0,0,0,0.08);
}
.feedback-sheet--correct {
    background-color: #d7ffb8;
    border-color: #58cc02;
    color: #276c00;
}
.feedback-sheet--incorrect {
    background-color: #ffdfe0;
    border-color: #ff4b4b;
    color: #b91c1c;
}
.feedback-sheet-inner {
    max-width: 1000px;
    margin: 0 auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 24px;
}
.feedback-status-wrap {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    flex: 1;
}
.feedback-icon-circle {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    flex-shrink: 0;
}
.feedback-sheet--correct .feedback-icon-circle {
    color: #58cc02;
}
.feedback-sheet--incorrect .feedback-icon-circle {
    color: #ff4b4b;
}
.feedback-texts {
    display: flex;
    flex-direction: column;
    gap: 4px;
    text-align: left;
}
.feedback-title {
    font-family: "Baloo 2", cursive;
    font-size: 22px;
    font-weight: 800;
    margin: 0;
}
.feedback-correct-answer {
    font-family: "Nunito", sans-serif;
    font-size: 15px;
    font-weight: 800;
}
.feedback-explanation {
    margin-top: 8px;
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    color: #475569;
    background: rgba(255, 255, 255, 0.7);
    padding: 10px 14px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,0.05);
    line-height: 1.5;
}
.explanation-title {
    font-weight: 800;
    margin-bottom: 2px;
    color: #1e293b;
}
.btn-feedback-next {
    align-self: center;
    height: 50px;
    min-width: 140px;
}

/* Slide-up transition */
.slide-up-enter-active, .slide-up-leave-active {
    transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s;
}
.slide-up-enter-from, .slide-up-leave-to {
    transform: translateY(100%);
    opacity: 0;
}
</style>
