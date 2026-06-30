<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft,
    ArrowRight,
    Home,
    Zap,
    Clock,
    Target,
    Trophy,
    Rocket,
    Flag,
    Loader2,
    Sparkles,
    Star,
    PartyPopper,
    BookOpen,
    Award,
    ListChecks,
    Eye,
    CircleCheck,
    Timer,
    ChevronRight,
    CheckCircle2,
    MousePointerClick,
    Music2,
    VolumeX,
} from "lucide-vue-next";
import { useSfx } from "@/Composable/useSfx";

import Multiple_choice from "@/Components/Quiz/Multiple_choice.vue";
import True_false from "@/Components/Quiz/True_false.vue";
import Case_study from "@/Components/Quiz/Case_study.vue";
import Drag_drop from "@/Components/Quiz/Drag_drop.vue";
import Short_answer from "@/Components/Quiz/Short_answer.vue";
import Materials from "@/Components/Quiz/Materials.vue";
import PretestLayout from "@/Layouts/PretestLayout.vue";

const { playPop, playSuccess, playFail, playClick } = useSfx();
const layoutRef = ref(null);
const confettiCanvas = ref(null);


const props = defineProps({
    quiz: { type: Object, required: true },
    module: { type: Object, default: () => ({ id: null, name: "Modul" }) },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    backsound: { type: String, default: null },
    background: { type: String, default: null },
});

const COMPONENT_MAP = {
    multiple_choices: Multiple_choice,
    true_false: True_false,
    case_study: Case_study,
    drag_drop: Drag_drop,
    short_answer: Short_answer,
    material: Materials,
};

const phase = ref("intro");
const ready = ref(false);
const brandMoved = ref(false);

const timeLimit = computed(() => (props.quiz?.time_limit ?? 10) * 60);

const SS_KEY = `geniuss_pretest_timer_${props.quiz?.id}`;
function ssGet(key, fallback) {
    try {
        const v = sessionStorage.getItem(key);
        return v !== null ? JSON.parse(v) : fallback;
    } catch {
        return fallback;
    }
}
function ssSet(key, val) {
    try {
        sessionStorage.setItem(key, JSON.stringify(val));
    } catch {}
}
function ssDel(key) {
    try {
        sessionStorage.removeItem(key);
    } catch {}
}

const remaining = ref(ssGet(SS_KEY, timeLimit.value));
let timerInt = null;

const timerDisplay = computed(() => {
    const m = String(Math.floor(remaining.value / 60)).padStart(2, "0");
    const s = String(remaining.value % 60).padStart(2, "0");
    return `${m}:${s}`;
});
const timerPct = computed(() => (remaining.value / timeLimit.value) * 100);
const timerWarning = computed(() => remaining.value <= 60);

function startTimer() {
    timerInt = setInterval(() => {
        if (remaining.value <= 0) {
            clearInterval(timerInt);
            ssDel(SS_KEY);
            submitQuiz();
            return;
        }
        remaining.value--;
        ssSet(SS_KEY, remaining.value);
    }, 1000);
}

const questions = computed(() => props.quiz?.questions ?? []);
const totalQ = computed(() => questions.value.length);
const currentIdx = ref(0);
const currentQ = computed(() => questions.value[currentIdx.value] ?? null);
const answers = ref({});
const shakeActive = ref(false);
const submitting = ref(false);
const celebScore = ref(0);

const isFirst = computed(() => currentIdx.value === 0);
const isLast = computed(() => currentIdx.value === totalQ.value - 1);
const quizType = computed(() => props.quiz?.type ?? "multiple_choices");

function isQuestionAnswered(q) {
    const val = answers.value[q?.id];
    if (quizType.value === "drag_drop")
        return (
            val &&
            typeof val === "object" &&
            !Array.isArray(val) &&
            Object.keys(val).length > 0
        );
    if (Array.isArray(val)) return val.length > 0;
    return val !== null && val !== undefined && val !== "";
}

const canGoNext = computed(() => {
    if (!currentQ.value) return true;
    if (quizType.value === "material") return true;
    return isQuestionAnswered(currentQ.value);
});

const answeredCnt = computed(
    () => questions.value.filter((q) => isQuestionAnswered(q)).length,
);
const progressPct = computed(() =>
    totalQ.value === 0
        ? 100
        : Math.round((answeredCnt.value / totalQ.value) * 100),
);

const isAnswerChecked = ref(false);
const isCurrentCorrect = ref(false);


function checkAnswerLocal() {
    const q = currentQ.value;
    if (!q) return false;
    const ans = answers.value[q.id];
    if (ans === undefined || ans === null) return false;

    if (quizType.value === "drag_drop") {
        const items = q.drag_drop_items || [];
        if (items.length === 0) return false;
        return items.every(item => ans[item.id] !== undefined && String(ans[item.id]) === String(item.correct_group_id));
    }

    if (quizType.value === "short_answer") {
        return true; // Essay/short_answer always accepted and treated as correct/complete
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
    const q = currentQ.value;
    if (!q) return "";

    if (quizType.value === "drag_drop") {
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

function updateAnswer({ questionId, value }) {
    if (isAnswerChecked.value) return;
    answers.value = { ...answers.value, [questionId]: value };
}

function goPrev() {
    if (!isFirst.value) {
        currentIdx.value--;
        isAnswerChecked.value = false;
        isCurrentCorrect.value = false;
    }
}
function goNext() {
    if (!isLast.value) {
        currentIdx.value++;
        isAnswerChecked.value = false;
        isCurrentCorrect.value = false;
    } else {
        submitQuiz();
    }
}

function startQuiz() {
    brandMoved.value = true;
    setTimeout(() => {
        phase.value = "quiz";
        startTimer();
    }, 420);
}

function submitQuiz() {
    if (submitting.value) return;
    submitting.value = true;
    clearInterval(timerInt);
    ssDel(SS_KEY);
    phase.value = "celebration"; // tampilkan celebration dulu

    // Tunggu 2.5 detik baru kirim ke server
    setTimeout(() => {
        const payload = {
            quiz_id: props.quiz?.id,
            module_id: props.module?.id,
            time_taken: timeLimit.value - remaining.value,
            answers: Object.entries(answers.value).map(([question_id, value]) => ({
                question_id,
                value,
            })),
        };
        router.post(route("playground.pretest.submit"), payload, {
            preserveState: false,
            onError: () => {
                submitting.value = false;
                phase.value = "quiz";
            },
        });
    }, 2500);
}

function goBack() {
    router.visit(route("playground.index"));
}

const INSTR_ITEMS = [
    { color: "red", icon: Eye, text: "Baca setiap soal dengan teliti." },
    {
        color: "yellow",
        icon: CircleCheck,
        text: "Pilih atau kerjakan jawaban paling tepat.",
    },
    {
        color: "blue",
        icon: Timer,
        text: "Kerjakan sesuai waktu yang tersedia.",
    },
    {
        color: "green",
        icon: ChevronRight,
        text: "Klik Berikutnya setelah menjawab.",
    },
];

const mascotUrl = computed(() => {
    const customMascot = questions.value.find((q) => q?.mascot?.image)?.mascot?.image;
    if (customMascot) {
        if (customMascot.startsWith("http") || customMascot.startsWith("/")) {
            return customMascot;
        }
        return `${window.location.origin}/storage/${customMascot}`;
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

    if (phase.value === "intro") return getMascotImage('nunjuk', "/images/templates/pose_nunjuk.png");
    if (phase.value === "done" || phase.value === "celebration")
        return getMascotImage('jempol', "/images/templates/pose_jempol.png");
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

const BUBBLES_INTRO = [
    "Halo! Udah siap tes skill kamu?",
    "Kuy, kepoin petunjuknya dulu!",
    "Dibawa santai aja, chill!",
];
const BUBBLES_QUIZ_UNANSWERED = [
    "Gas baca soalnya dulu nih!",
    "Slow aja bacanya, dipikirin mateng-mateng!",
    "Fokus dong, kamu pasti bisa!",
    "Cek ombak dulu, baca soalnya baik-baik!",
];
const BUBBLES_QUIZ_ANSWERED = [
    "Cakep! Langsung gas klik Selanjutnya.",
    "Udah yakin sama jawaban ini?",
    "Kece badai! Lanjut ke soal berikutnya yuk.",
    "Mantul! Gas terus pantang mundur!",
];
const BUBBLES_DONE = [
    "Gokil abis! Kamu berhasil nyelesain pretest!",
    "Kece parah! Petualangan seru udah nungguin nih!",
    "Mantap djiwa! Kuy kita cek hasilnya!",
];

const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;

const isStepCorrect = (q) => {
    if (!q) return true;
    const ans = answers[q.id];
    if (ans === undefined || ans === null) return false;
    
    if (props.quiz?.type === "multiple_choices" || props.quiz?.type === "true_false" || props.quiz?.type === "case_study") {
        if (q.options) {
            const correctOpt = q.options.find(o => o.is_correct);
            return correctOpt && String(correctOpt.id) === String(ans);
        }
    }
    return true; // Fallback
};

const activeSpeechText = computed(() => {
    if (phase.value === "intro") {
        return BUBBLES_INTRO[bubbleIdx.value % BUBBLES_INTRO.length];
    }
    if (phase.value === "done" || phase.value === "celebration") {
        return BUBBLES_DONE[bubbleIdx.value % BUBBLES_DONE.length];
    }

    if (currentQ.value) {
        const answered = isQuestionAnswered(currentQ.value);
        if (answered) {
            const correct = isStepCorrect(currentQ.value);
            if (correct) {
                if (currentQ.value.feedback_correct) return currentQ.value.feedback_correct;
                return BUBBLES_QUIZ_ANSWERED[
                    bubbleIdx.value % BUBBLES_QUIZ_ANSWERED.length
                ];
            } else {
                if (currentQ.value.feedback_incorrect) return currentQ.value.feedback_incorrect;
                return "Ayo coba lagi, periksa kembali jawabanmu!";
            }
        } else {
            return BUBBLES_QUIZ_UNANSWERED[
                bubbleIdx.value % BUBBLES_QUIZ_UNANSWERED.length
            ];
        }
    }
    return "Semangat ya! Kamu pasti bisa!";
});

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = bubbleIdx.value + 1;
        bubbleVisible.value = true;
    }, 300);
};

watch(currentIdx, () => {
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleVisible.value = true;
    }, 200);
});

watch(
    () => currentQ.value && answers.value[currentQ.value.id],
    (newVal) => {
        if (newVal !== undefined && newVal !== null && newVal !== "") {
            bubbleIdx.value = Math.floor(
                Math.random() * BUBBLES_QUIZ_ANSWERED.length,
            );
            bubbleVisible.value = false;
            setTimeout(() => {
                bubbleVisible.value = true;
            }, 200);
        }
    },
);

const mascotClicked = ref(false);
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
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleVisible.value = true;
    }, 200);

    if (newPhase === "celebration") {
        playSuccess();
        setTimeout(() => {
            if (confettiCanvas.value) {
                startConfetti(confettiCanvas.value);
            }
        }, 150);
    }
});

const handleGlobalClick = (e) => {
    const target = e.target;
    const button = target.closest("button, .btn, .btn-duo, [role='button'], .clickable-opt, .option-btn, .draggable-item, .nav-left, .music-fab");
    if (button) {
        playClick();
    }
};

onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
    window.addEventListener("click", handleGlobalClick);
});
onUnmounted(() => {
    clearInterval(timerInt);
    clearInterval(bubbleTimer);
    window.removeEventListener("click", handleGlobalClick);
});
</script>

<template>
    <PretestLayout
        ref="layoutRef"
        :timerDisplay="timerDisplay"
        :isWarning="timerWarning"
        :progressPercent="progressPct"
        :showProgress="phase === 'quiz'"
        :backsound="props.backsound"
        :background="backgroundUrl"
    >
        <div class="main-wrapper" :class="{ 'main--on': ready }">
            <!-- Confetti Overlay Canvas -->
            <canvas ref="confettiCanvas" class="confetti-canvas"></canvas>

            <div class="pretest-layout-cols">
                <!-- LEFT COLUMN: MASCOT (DESKTOP ONLY) -->
                <div class="mascot-column" v-if="phase !== 'celebration'" @click="handleMascotClick">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="mascot-bubble-wrap">
                            <div class="mascot-speech-bubble">
                                <span>{{ activeSpeechText }}</span>
                            </div>
                            <div class="bubble-arrow"></div>
                        </div>
                    </Transition>

                    <div class="mascot-image-container mascot-interactive" :class="{ 'mascot-wiggle': mascotClicked }">
                        <img
                            :src="mascotUrl"
                            alt="Maskot"
                            class="mascot-avatar-img"
                        />
                        <div class="mascot-avatar-shadow"></div>
                    </div>
                </div>

                <!-- RIGHT COLUMN: QUIZ CONTENT -->
                <div class="quiz-content-column">
                    <div class="mission-container">
                        <!-- ══ PHASE: INTRO ══ -->
                        <template v-if="phase === 'intro'">
                            <div class="title-pill">
                                PRETEST: {{ module.name }}
                            </div>
                            
                            <div v-if="quiz.image" style="margin-bottom: 16px;">
                                <img :src="quiz.image.startsWith('http') ? quiz.image : `/storage/${quiz.image}`" alt="Quiz Image" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 16px; border: 2px solid #e2e8f0;" />
                            </div>

                            <div class="question-bubble">
                                <span>{{
                                    quiz.description ??
                                    "Jawab semua soal dengan sebaik-baiknya untuk mengukur pemahamanmu sebelum memulai misi!"
                                }}</span>
                            </div>
                            <div
                                class="component-box"
                                style="
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    gap: 20px;
                                "
                            >
                                <div class="icard-stats">
                                    <div class="istat istat--red">
                                        <div class="istat-icon">
                                            <BookOpen
                                                :size="19"
                                                :stroke-width="1.8"
                                            />
                                        </div>
                                        <span class="istat-val">{{
                                            totalQ
                                        }}</span>
                                        <span class="istat-lbl">Soal</span>
                                    </div>
                                    <div class="istat istat--yellow">
                                        <div class="istat-icon">
                                            <Clock
                                                :size="19"
                                                :stroke-width="1.8"
                                            />
                                        </div>
                                        <span class="istat-val">{{
                                            quiz.time_limit ?? 10
                                        }}</span>
                                        <span class="istat-lbl">Menit</span>
                                    </div>
                                    <div class="istat istat--blue">
                                        <div class="istat-icon">
                                            <Award
                                                :size="19"
                                                :stroke-width="1.8"
                                            />
                                        </div>
                                        <span class="istat-val">XP</span>
                                        <span class="istat-lbl">Hadiah</span>
                                    </div>
                                </div>

                                <div class="icard-instr-grid">
                                    <div
                                        v-for="(item, i) in INSTR_ITEMS"
                                        :key="i"
                                        class="instr-row"
                                        :class="`instr-row--${item.color}`"
                                    >
                                        <span class="instr-num">{{
                                            String(i + 1).padStart(2, "0")
                                        }}</span>
                                        <component
                                            :is="item.icon"
                                            :size="13"
                                            :stroke-width="2.5"
                                            class="instr-ico"
                                        />
                                        <span class="instr-txt">{{
                                            item.text
                                        }}</span>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- ══ PHASE: QUIZ ══ -->
                        <template v-else-if="phase === 'quiz'">
                            <div class="title-pill">
                                SOAL {{ currentIdx + 1 }} DARI {{ totalQ }}
                            </div>
                            
                            <div v-if="quiz.image" style="margin-bottom: 16px;">
                                <img :src="quiz.image.startsWith('http') ? quiz.image : `/storage/${quiz.image}`" alt="Quiz Image" style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 16px; border: 2px solid #e2e8f0;" />
                            </div>

                            <div
                                class="question-bubble"
                                v-html="currentQ?.question_text"
                            ></div>
                            <div
                                class="component-box"
                                :class="{ 'opts--shake': shakeActive }"
                                :style="{ pointerEvents: isAnswerChecked ? 'none' : 'auto', opacity: isAnswerChecked ? 0.8 : 1 }"
                            >
                                <component
                                    v-if="currentQ"
                                    :is="COMPONENT_MAP[quizType]"
                                    :question="currentQ"
                                    :modelValue="answers[currentQ.id]"
                                    @update-answer="updateAnswer"
                                />
                            </div>
                        </template>

                        <!-- ══ PHASE: DONE ══ -->
                        <template v-else-if="phase === 'done'">
                            <div class="title-pill" style="color: #58cc02">
                                PRETEST SELESAI
                            </div>
                            <div class="question-bubble">
                                <span
                                    >Kamu telah menyelesaikan pretest dengan
                                    luar biasa. Sekarang saatnya memulai
                                    petualangan misi belajar!</span
                                >
                            </div>
                            <div
                                class="component-box"
                                style="
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: center;
                                    padding: 40px;
                                "
                            >
                                <div class="trophy-wrap">
                                    <div class="trophy-ring">
                                        <Trophy
                                            :size="64"
                                            color="#58cc02"
                                            :stroke-width="1.5"
                                        />
                                    </div>
                                    <span class="sp sp1"
                                        ><Sparkles :size="18" color="#1cb0f6"
                                    /></span>
                                    <span class="sp sp2"
                                        ><Star
                                            :size="16"
                                            color="#ffc800"
                                            fill="#ffc800"
                                    /></span>
                                    <span class="sp sp3"
                                        ><Sparkles :size="14" color="#bfdbfe"
                                    /></span>
                                </div>
                            </div>
                        </template>

                        <!-- ══ PHASE: CELEBRATION (loading while submitting) ══ -->
                        <template v-else-if="phase === 'celebration'">
                            <div class="celeb-overlay">
                                <div class="celeb-mascot-container">
                                    <Transition name="bbl">
                                        <div class="mascot-bubble-wrap">
                                            <div class="mascot-speech-bubble">
                                                <span>Luar biasa! Kamu telah menyelesaikan pretest dengan sangat baik!</span>
                                            </div>
                                            <div class="bubble-arrow"></div>
                                        </div>
                                    </Transition>

                                    <div class="mascot-image-container">
                                        <img
                                            :src="mascotUrl"
                                            alt="Maskot"
                                            class="mascot-avatar-img"
                                            style="animation: none"
                                        />
                                    </div>
                                </div>

                                <div class="celeb-label" style="margin-top: 20px">
                                    Pretest Selesai!
                                </div>
                                <div class="celeb-sub">Menuju halaman misi...</div>

                                <div class="celeb-loader">
                                    <div class="celeb-loader-bar"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ FIXED FOOTER ACTION BAR (DUOLINGO STYLE) ══ -->
        <div class="footer-bar" v-if="phase !== 'celebration'">
            <div class="footer-inner">
                <!-- Left actions (Music + Kembali / Sebelumnya) -->
                <div class="footer-left">
                    <button
                        class="music-footer-btn"
                        @click="layoutRef?.toggleMusic(props.backsound ?? null)"
                        :class="{ 'music-on': layoutRef?.musicOn }"
                        title="Musik Latar"
                    >
                        <Music2 v-if="layoutRef?.musicOn" :size="18" :stroke-width="2.5" />
                        <VolumeX v-else :size="18" :stroke-width="2.5" />
                    </button>

                    <template v-if="phase === 'intro'">
                        <button
                            class="btn-duo btn-duo-secondary btn-back"
                            @click="goBack"
                        >
                            <ArrowLeft :size="18" :stroke-width="3" />
                            <span class="desktop-only">Kembali</span>
                        </button>
                    </template>

                    <template v-else-if="phase === 'quiz'">
                        <button
                            v-if="!isFirst"
                            class="btn-duo btn-duo-secondary"
                            @click="goPrev"
                            :disabled="submitting"
                        >
                            <ArrowLeft :size="18" :stroke-width="3" />
                            <span class="desktop-only">Sebelumnya</span>
                        </button>
                    </template>
                </div>

                <!-- Right actions (Mulai / Selanjutnya / Selesaikan) -->
                <div class="footer-right">
                    <template v-if="phase === 'intro'">
                        <button
                            class="btn-duo btn-duo-success"
                            @click="startQuiz"
                        >
                            <span class="desktop-only">Mulai Pretest</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                    </template>

                    <template v-else-if="phase === 'quiz'">
                        <button
                            v-if="!isAnswerChecked"
                            class="btn-duo btn-duo-success"
                            @click="onCheckAnswer"
                            :disabled="!canGoNext || submitting"
                        >
                            <span>Periksa Jawaban</span>
                            <CheckCircle2 :size="18" :stroke-width="3" />
                        </button>
                    </template>

                    <template v-else-if="phase === 'done'">
                        <button
                            class="btn-duo btn-duo-success"
                            @click="goToMissions"
                            :disabled="submitting"
                        >
                            <span>Mulai Misi</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <!-- Slide-up Instant Feedback Sheet -->
        <Transition name="slide-up">
            <div 
                v-if="isAnswerChecked" 
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
                                {{ quizType === 'short_answer' ? 'Jawabanmu Berhasil Dikirim!' : (isCurrentCorrect ? 'Luar Biasa! Jawabanmu Benar' : 'Kurang Tepat!') }}
                            </h4>
                            <div v-if="!isCurrentCorrect && quizType !== 'short_answer'" class="feedback-correct-answer">
                                <span class="font-bold">Jawaban Benar:</span> {{ correctText }}
                            </div>
                            <div v-if="quizType === 'short_answer' && correctText" class="feedback-correct-answer">
                                <span class="font-bold">Referensi Jawaban:</span> {{ correctText }}
                            </div>
                            <div v-if="currentQ?.explanation" class="feedback-explanation">
                                <div class="explanation-title">Pembahasan:</div>
                                <div class="explanation-body" v-html="currentQ.explanation"></div>
                            </div>
                        </div>
                    </div>
                    
                    <button
                        v-if="isLast"
                        class="btn-duo btn-duo-success btn-feedback-next"
                        @click="goNext"
                        :disabled="submitting"
                    >
                        <span v-if="!submitting">Selesaikan Pretest</span>
                        <Loader2 v-else :size="18" class="spin" />
                        <CheckCircle2 v-if="!submitting" :size="18" />
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
    </PretestLayout>
</template>

<style scoped>
/* ─── MAIN CENTRIC LAYOUT ─── */
.main-wrapper {
    position: relative;
    z-index: 10;
    flex: 1;
    display: flex;
    justify-content: center;
    padding: 20px 24px 130px;
    opacity: 0;
    transition: opacity 0.45s;
    overflow-y: visible;
}
.main--on {
    opacity: 1;
}

.pretest-layout-cols {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 40px;
    width: 100%;
    max-width: 1000px;
    margin: 30px auto 0;
    align-items: flex-start;
}

/* ─── LEFT COLUMN: MASCOT ─── */
.mascot-column {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: sticky;
    top: 100px;
    z-index: 20;
}

.mascot-bubble-wrap {
    position: relative;
    margin-bottom: 22px;
    filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.05));
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

@keyframes floatBubble {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

.mascot-image-container {
    width: 260px;
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: default; /* Kursor tidak lagi berupa pointer */
    user-select: none;
    transform: translateX(-40px);
}

.mascot-avatar-img {
    height: 340px;
    width: auto;
    object-fit: contain;
    animation: mascotIdle 4s ease-in-out infinite;
    filter: drop-shadow(0 8px 20px rgba(28, 176, 246, 0.15));
    transform-origin: bottom center;
}

.mascot-avatar-shadow {
    width: 140px;
    height: 10px;
    background: radial-gradient(
        ellipse,
        rgba(0, 0, 0, 0.12) 0%,
        transparent 80%
    );
    border-radius: 50%;
    margin-top: 6px;
    animation: shadowScale 4s ease-in-out infinite;
}

@keyframes mascotIdle {
    0%,
    100% {
        transform: translateY(0) scale(1) rotate(0deg);
    }
    30% {
        transform: translateY(-10px) scaleX(1.04) scaleY(0.96) rotate(-2deg);
    }
    65% {
        transform: translateY(-4px) scaleX(0.97) scaleY(1.03) rotate(2deg);
    }
}

@keyframes shadowScale {
    0%,
    100% {
        transform: scale(1);
        opacity: 0.9;
    }
    30% {
        transform: scale(0.8);
        opacity: 0.4;
    }
    65% {
        transform: scale(0.95);
        opacity: 0.7;
    }
}

.bbl-enter-active {
    transition:
        opacity 0.3s,
        transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.bbl-leave-active {
    transition: opacity 0.2s;
}
.bbl-enter-from {
    opacity: 0;
    transform: translateY(10px) scale(0.9);
}
.bbl-leave-to {
    opacity: 0;
}

/* ─── RIGHT COLUMN: QUIZ CONTENT ─── */
.quiz-content-column {
    min-width: 0;
    flex: 1;
}

.mission-container {
    width: 100%;
    display: flex;
    flex-direction: column;
}

.title-pill {
    font-family: "Nunito", sans-serif;
    font-weight: 800;
    font-size: 14px;
    color: #1cb0f6;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 12px;
    text-align: left;
}

.question-bubble {
    font-family: "Baloo 2", cursive;
    font-weight: 800;
    font-size: 26px;
    color: #3c3c3c;
    padding: 0;
    text-align: left;
    width: 100%;
    margin-bottom: 28px;
    line-height: 1.35;
}

.component-box {
    width: 100%;
    background: transparent;
    padding: 0;
    box-shadow: none;
    position: relative;
}

/* ─── DEEP OVERRIDES FOR NESTED QUIZ OPTIONS ─── */

/* Hanya normalisasi TF option, biarkan .opt dari Multiple_choice bebas */
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
:deep(.tf-option:hover:not(.selected)) {
    background-color: #f7f7f7 !important;
}
:deep(.tf-option.selected) {
    background-color: #ddf4ff !important;
    border-color: #1cb0f6 !important;
    border-bottom-color: #1899d6 !important;
}
:deep(.tf-option.selected .tf-label) {
    color: #1cb0f6 !important;
}
:deep(.tf-options) {
    gap: 16px !important;
}
:deep(.tf-inner) {
    padding: 14px 20px !important;
    width: 100% !important;
    justify-content: space-between !important;
}
:deep(.tf-label) {
    text-align: left !important;
    font-family: "Nunito", sans-serif !important;
    font-weight: 800 !important;
    color: #3c3c3c !important;
    font-size: 16px !important;
}

/* Drag drop & short answer tetap dinormalisasi */
:deep(.drag-item),
:deep(.dd-item) {
    border: 2px solid #e5e5e5 !important;
    border-bottom: 4px solid #cbd5e1 !important;
    border-radius: 12px !important;
    background-color: #ffffff !important;
    font-family: "Nunito", sans-serif !important;
    font-weight: 800 !important;
    color: #3c3c3c !important;
    padding: 10px 16px !important;
}
:deep(.sa-input),
:deep(.short-answer-input),
:deep(textarea) {
    border: 2px solid #e5e5e5 !important;
    border-radius: 16px !important;
    padding: 14px 18px !important;
    font-family: "Nunito", sans-serif !important;
    font-weight: 700 !important;
    font-size: 16px !important;
    color: #3c3c3c !important;
    outline: none !important;
    transition: border-color 0.2s !important;
    box-shadow: none !important;
    width: 100% !important;
}
:deep(.sa-input:focus),
:deep(.short-answer-input:focus),
:deep(textarea:focus) {
    border-color: #1cb0f6 !important;
}

/* ─── INTRO DETAILS Redesain ─── */
.icard-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    width: 100%;
    max-width: 600px;
    border: none;
    background: transparent;
    margin-bottom: 24px;
}

.istat {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-bottom: 5px solid #cbd5e1;
    border-radius: 20px;
    padding: 16px 12px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}

.istat-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}

.istat--red .istat-icon {
    background-color: #ff4b4b;
}
.istat--yellow .istat-icon {
    background-color: #ffc800;
}
.istat--blue .istat-icon {
    background-color: #1cb0f6;
}

.istat-val {
    font-family: "Baloo 2", cursive;
    font-size: 22px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1;
}

.istat-lbl {
    font-family: "Nunito", sans-serif;
    font-size: 12px;
    font-weight: 800;
    color: #777777;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.icard-instr-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 12px;
    width: 100%;
    max-width: 600px;
}

.instr-row {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 16px;
    padding: 14px 18px;
    font-family: "Nunito", sans-serif;
    font-weight: 800;
    font-size: 14px;
    color: #3c3c3c;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom-width: 2px;
}

.instr-num {
    background-color: #e5e5e5;
    color: #777777;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Baloo 2", cursive;
    font-size: 12px;
    font-weight: 800;
    flex-shrink: 0;
}

.instr-ico {
    color: #1cb0f6;
    flex-shrink: 0;
}

.instr-txt {
    flex: 1;
    line-height: 1.4;
    text-align: left;
}

/* ─── TROPHY DONE Redesain ─── */
.trophy-wrap {
    position: relative;
    width: 140px;
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 20px auto;
}

.trophy-ring {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: #e2fce6;
    border: 3px solid #58cc02;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(88, 204, 2, 0.15);
    animation: tPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

.sp {
    position: absolute;
    animation: spFloat 2.2s ease-in-out infinite;
}
.sp1 {
    top: 0px;
    right: 4px;
}
.sp2 {
    bottom: 4px;
    left: 0px;
    animation-delay: 0.5s;
}
.sp3 {
    top: 10px;
    left: -2px;
    animation-delay: 1.1s;
}

/* ─── FIXED FOOTER ACTION BAR (DUOLINGO STYLE) ─── */
.footer-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 94px;
    background: rgba(255, 255, 255, 0.35);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 1.5px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 -4px 30px rgba(0, 0, 0, 0.03);
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
}

.footer-left,
.footer-right {
    display: flex;
    align-items: center;
}

/* ─── DUOLINGO FLAT 3D BUTTONS ─── */
.btn-duo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 13px 26px;
    border-radius: 16px;
    font-family: "Nunito", "Baloo 2", sans-serif;
    font-size: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    cursor: pointer;
    transition:
        filter 0.15s,
        transform 0.1s,
        border-bottom-width 0.1s;
    user-select: none;
    outline: none;
}

/* Primary Blue Action */
.btn-duo-primary {
    background-color: #1cb0f6;
    border: 2px solid #1cb0f6;
    border-bottom: 5px solid #1899d6;
    color: #ffffff;
}
.btn-duo-primary:hover:not(:disabled) {
    filter: brightness(1.04);
}
.btn-duo-primary:active:not(:disabled) {
    transform: translateY(3px);
    border-bottom-width: 2px;
}

/* Success Green Action */
.btn-duo-success {
    background-color: #58cc02;
    border: 2px solid #58cc02;
    border-bottom: 5px solid #58a700;
    color: #ffffff;
}
.btn-duo-success:hover:not(:disabled) {
    filter: brightness(1.04);
}
.btn-duo-success:active:not(:disabled) {
    transform: translateY(3px);
    border-bottom-width: 2px;
}

/* Secondary White/Gray */
.btn-duo-secondary {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-bottom: 5px solid #cbd5e1;
    color: #afafaf;
}
.btn-duo-secondary:hover:not(:disabled) {
    background-color: #f7f7f7;
    color: #777777;
}
.btn-duo-secondary:active:not(:disabled) {
    transform: translateY(3px);
    border-bottom-width: 2px;
}

.btn-duo:disabled {
    background-color: #e5e5e5 !important;
    border-color: #e5e5e5 !important;
    border-bottom: 2px solid #cbd5e1 !important;
    color: #afafaf !important;
    cursor: not-allowed;
    transform: none !important;
}

.opts--shake {
    animation: optShake 0.5s ease;
}
@keyframes optShake {
    0%,
    100% {
        transform: translateX(0);
    }
    20%,
    60% {
        transform: translateX(-6px);
    }
    40%,
    80% {
        transform: translateX(6px);
    }
}
.spin {
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* ─── CELEBRATION ─── */
.celeb-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    background-color: #ffffff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    animation: celebFadeIn 0.5s ease both;
}
.celeb-mascot-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 80px;
    animation: celebPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}
.mascot-bubble-wrap {
    margin-bottom: 20px;
    width: 280px;
}
.mascot-image-container img {
    max-height: 250px;
}
.celeb-label {
    font-family: "Baloo 2", cursive;
    font-size: 36px;
    font-weight: 800;
    color: #3c3c3c;
    margin-bottom: 8px;
    animation: slideUp 0.5s ease 0.4s both;
}
.celeb-sub {
    font-size: 16px;
    color: #777777;
    font-weight: 700;
    margin-bottom: 28px;
    animation: slideUp 0.5s ease 0.5s both;
}
.celeb-loader {
    width: 220px;
    height: 8px;
    background: #e5e5e5;
    border-radius: 10px;
    overflow: hidden;
    animation: slideUp 0.5s ease 0.6s both;
}
.celeb-loader-bar {
    height: 100%;
    background-color: #1cb0f6;
    width: 0%;
    animation: celebLoad 3s linear 2.5s forwards;
}
@keyframes celebLoad {
    from { width: 0%; }
    to   { width: 100%; }
}
@keyframes celebFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes celebPop {
    from { opacity: 0; transform: scale(0.8) translateY(16px); }
    to   { opacity: 1; transform: scale(1) translateY(0); }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── MUSIC BUTTON (Mobile Footer) ─── */
.music-footer-btn {
    display: none;
    align-items: center;
    justify-content: center;
    width: 44px; height: 44px;
    border-radius: 12px;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    background: #ffffff;
    color: #94a3b8;
    margin-right: 12px;
    cursor: pointer;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.music-footer-btn:active { transform: translateY(2px); border-bottom-width: 2px; }
.music-footer-btn.music-on { background: #1cb0f6; border-color: #1cb0f6; border-bottom-color: #1899d6; color: white; }

/* ─── RESPONSIVE ─── */
@media (max-width: 768px) {
    .main-wrapper {
        padding: 10px 16px 110px;
    }

    .pretest-layout-cols {
        grid-template-columns: 1fr;
        gap: 0;
        margin: 15px auto 0;
    }

    .mascot-column {
        display: none !important;
    }

    .question-bubble {
        font-size: 20px;
        margin-bottom: 20px;
    }

    .title-pill {
        font-size: 12px;
        margin-bottom: 6px;
    }

    .footer-bar {
        height: 80px;
    }

    .footer-inner {
        padding: 0 16px;
    }

    /* Show music btn on mobile */
    .music-footer-btn {
        display: flex;
    }

    .btn-duo {
        padding: 10px 20px;
        font-size: 13px;
        border-radius: 12px;
        border-bottom-width: 4px;
    }

    .btn-duo-primary:active:not(:disabled),
    .btn-duo-success:active:not(:disabled),
    .btn-duo-secondary:active:not(:disabled) {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }

    .icard-stats {
        gap: 10px;
    }

    .istat {
        padding: 12px 6px;
    }

    .istat-val {
        font-size: 18px;
    }

    .istat-lbl {
        font-size: 10px;
    }
    .quiz-content-column {
        display: flex;
        flex-direction: column;
    }

    .component-box {
        flex: 1;
    }

    /* Pastikan mc-opts juga single column via deep override mobile */
    :deep(.mc-opts) {
        grid-template-columns: 1fr !important;
        gap: 10px !important;
    }

    .question-bubble {
        font-size: 18px;
        margin-bottom: 20px;
    }

    /* Celebration mobile */
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

/* ── Mobile ── */
@media (max-width: 600px) {
    .mc-opts {
        grid-template-columns: 1fr; /* ubah dari 1fr 1fr → 1fr */
        gap: 10px;
    }
    .opt-key {
        width: 40px;
        min-width: 40px;
        font-size: 14px;
    }
    .opt-txt {
        font-size: 12.5px;
    }
    .opt-body {
        padding: 9px 9px 9px 10px;
    }
}

/* ── Icon-only minimalist footer buttons on small mobile ── */
@media (max-width: 480px) {
    .desktop-only { display: none !important; }
    .btn-duo {
        padding: 10px;
        border-radius: 14px;
        min-width: 44px;
        height: 44px;
    }
    .footer-bar { height: 76px; }
    .footer-inner { padding: 0 14px; }
    .btn-back {
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 8px !important;
        min-width: auto;
        height: auto;
        color: #94a3b8 !important;
    }
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