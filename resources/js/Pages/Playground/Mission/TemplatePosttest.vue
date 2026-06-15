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
    VolumeX,
    Music2,
} from "lucide-vue-next";

import Multiple_choice from "@/Components/Quiz/Multiple_choice.vue";
import True_false from "@/Components/Quiz/True_false.vue";
import Case_study from "@/Components/Quiz/Case_study.vue";
import Drag_drop from "@/Components/Quiz/Drag_drop.vue";
import Short_answer from "@/Components/Quiz/Short_answer.vue";
import Materials from "@/Components/Quiz/Materials.vue";
import PretestLayout from "@/Layouts/PosttestLayout.vue";

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

const SS_KEY = `geniuss_posttest_timer_${props.quiz?.id}`;
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

function updateAnswer({ questionId, value }) {
    answers.value = { ...answers.value, [questionId]: value };
}

function goPrev() {
    if (!isFirst.value) currentIdx.value--;
}
function goNext() {
    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => {
            shakeActive.value = false;
        }, 600);
        return;
    }
    if (!isLast.value) currentIdx.value++;
    else submitQuiz();
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
    clearInterval(timerInt);
    ssDel(SS_KEY);

    const total =
        questions.value.filter((q) => q.quiz_type !== "material").length ||
        questions.value.length;
    const correct = questions.value.filter((q) => isQuestionAnswered(q)).length;
    celebScore.value = total > 0 ? Math.round((correct / total) * 100) : 0;
    phase.value = "celebration";

    setTimeout(() => {
        submitting.value = true;
        const payload = {
            quiz_id: props.quiz?.id,
            module_id: props.module?.id,
            time_taken: timeLimit.value - remaining.value,
            answers: Object.entries(answers.value).map(
                ([question_id, value]) => ({ question_id, value }),
            ),
        };
        // Menggunakan rute khusus posttest
        router.post(route("playground.posttest.submit"), payload, {
            preserveState: false,
            onError: () => {
                submitting.value = false;
                phase.value = "quiz";
            },
        });
    }, 3200);
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
    const customMascot = questions.value.find((q) => q?.mascot?.image)?.mascot
        ?.image;
    if (customMascot) {
        if (customMascot.startsWith("http") || customMascot.startsWith("/")) {
            return customMascot;
        }
        return `${window.location.origin}/storage/${customMascot}`;
    }

    if (phase.value === "intro") return "/images/templates/pose_nunjuk.png";
    if (phase.value === "done" || phase.value === "celebration")
        return "/images/templates/pose_jempol.png";
    return "/images/templates/pose_pikir.png";
});

const BUBBLES_INTRO = [
    "Yuk, baca petunjuknya dulu!",
    "Siap mengukur akhir belajarmu?",
    "Tunjukkan kemampuan terbaikmu!",
];
const BUBBLES_QUIZ_UNANSWERED = [
    "Ayo dibaca dulu soalnya dengan teliti!",
    "Pikirkan baik-baik ya!",
    "Fokus dan tenang, kamu pasti bisa!",
    "Membaca soal membantu menemukan jawaban!",
];
const BUBBLES_QUIZ_ANSWERED = [
    "Pilihan yang bagus! Ayo klik Selanjutnya.",
    "Jawaban terpilih! Yakin dengan pilihanmu?",
    "Luar biasa! Mari lanjut ke pertanyaan berikut.",
    "Bagus sekali! Mari lanjutkan!",
];
const BUBBLES_DONE = [
    "Luar biasa! Kamu menyelesaikan posttest!",
    "Hebat sekali! Saatnya melihat hasil!",
    "Kerja bagus! Ayo kita lihat hasilnya!",
];

const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;
const layoutRef = ref(null);

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
            return BUBBLES_QUIZ_ANSWERED[
                bubbleIdx.value % BUBBLES_QUIZ_ANSWERED.length
            ];
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

watch(phase, () => {
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleVisible.value = true;
    }, 200);
});

onMounted(() => {
    setTimeout(() => {
        ready.value = true;
    }, 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
});
onUnmounted(() => {
    clearInterval(timerInt);
    clearInterval(bubbleTimer);
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
    >
        <div class="main-wrapper" :class="{ 'main--on': ready }">
            <div class="pretest-layout-cols">
                <div class="mascot-column" v-if="phase !== 'celebration'">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="mascot-bubble-wrap">
                            <div class="mascot-speech-bubble">
                                <span>{{ activeSpeechText }}</span>
                            </div>
                            <div class="bubble-arrow"></div>
                        </div>
                    </Transition>

                    <div class="mascot-image-container">
                        <img
                            :src="mascotUrl"
                            alt="Maskot"
                            class="mascot-avatar-img"
                        />
                        <div class="mascot-avatar-shadow"></div>
                    </div>
                </div>

                <div class="quiz-content-column">
                    <div class="mission-container">
                        <template v-if="phase === 'intro'">
                            <div class="title-pill">
                                POSTTEST: {{ module.name }}
                            </div>
                            <div class="question-bubble">
                                <span>{{
                                    quiz.description ??
                                    "Jawab semua soal dengan sebaik-baiknya untuk mengukur pemahaman akhirmu setelah menyelesaikan misi!"
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

                        <template v-else-if="phase === 'quiz'">
                            <div class="title-pill">
                                SOAL {{ currentIdx + 1 }} DARI {{ totalQ }}
                            </div>
                            <div
                                class="question-bubble"
                                v-html="currentQ?.question_text"
                            ></div>
                            <div
                                class="component-box"
                                :class="{ 'opts--shake': shakeActive }"
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

                        <template v-else-if="phase === 'done'">
                            <div class="title-pill" style="color: #58cc02">
                                POSTTEST SELESAI
                            </div>
                            <div class="question-bubble">
                                <span
                                    >Kamu telah menyelesaikan posttest dengan
                                    luar biasa. Sekarang saatnya melihat hasil
                                    akhirmu!</span
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

                        <template v-else-if="phase === 'celebration'">
                            <div class="celeb-overlay">
                                <div class="celeb-mascot-container">
                                    <Transition name="bbl">
                                        <div class="mascot-bubble-wrap">
                                            <div class="mascot-speech-bubble">
                                                <span
                                                    >Luar biasa! Kamu telah
                                                    menyelesaikan posttest
                                                    dengan sangat baik!</span
                                                >
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

                                <div
                                    class="celeb-label"
                                    style="margin-top: 20px"
                                >
                                    Posttest Selesai!
                                </div>
                                <div class="celeb-sub">Menuju hasil akhir…</div>

                                <div class="celeb-loader">
                                    <div class="celeb-loader-bar"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bar" v-if="phase !== 'celebration'">
            <div class="footer-inner">
                <div class="footer-left">
                    <button
                        class="music-footer-btn"
                        @click="layoutRef?.toggleMusic(props.backsound ?? null)"
                        :class="{ 'music-on': layoutRef?.musicOn }"
                    >
                        <Music2 v-if="layoutRef?.musicOn" :size="18" :stroke-width="2.5" />
                        <VolumeX v-else :size="18" :stroke-width="2.5" />
                    </button>

                    <template v-if="phase === 'intro'">
                        <button
                            class="btn-duo btn-duo-secondary btn-icon-mobile"
                            @click="goBack"
                        >
                            <ArrowLeft :size="18" :stroke-width="3" />
                            <span class="hide-mobile">Kembali</span>
                        </button>
                    </template>

                    <template v-else-if="phase === 'quiz'">
                        <button
                            v-if="!isFirst"
                            class="btn-duo btn-duo-secondary btn-icon-mobile"
                            @click="goPrev"
                            :disabled="submitting"
                        >
                            <ArrowLeft :size="18" :stroke-width="3" />
                            <span class="hide-mobile">Sebelumnya</span>
                        </button>
                    </template>
                </div>

                <div class="footer-right">
                    <template v-if="phase === 'intro'">
                        <button
                            class="btn-duo btn-duo-success btn-icon-mobile"
                            @click="startQuiz"
                        >
                            <span class="hide-mobile">Mulai Posttest</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                    </template>

                    <template v-else-if="phase === 'quiz'">
                        <button
                            v-if="isLast"
                            class="btn-duo btn-duo-success btn-icon-mobile"
                            @click="submitQuiz"
                            :disabled="submitting || !canGoNext"
                        >
                            <span v-if="!submitting" class="hide-mobile">Selesaikan Posttest</span>
                            <Loader2 v-else :size="18" class="spin" />
                            <CheckCircle2
                                v-if="!submitting"
                                :size="18"
                                :stroke-width="3"
                            />
                        </button>
                        <button
                            v-else
                            class="btn-duo btn-duo-primary btn-icon-mobile"
                            @click="goNext"
                            :disabled="!canGoNext || submitting"
                        >
                            <span class="hide-mobile">Selanjutnya</span>
                            <ArrowRight :size="18" :stroke-width="3" />
                        </button>
                    </template>
                </div>
            </div>
        </div>
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
    overflow-y: auto;
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
    cursor: default;
    user-select: none;
}

.mascot-avatar-img {
    height: 290px;
    width: auto;
    object-fit: contain;
    animation: mascotIdle 4s ease-in-out infinite;
    filter: drop-shadow(0 8px 20px rgba(28, 176, 246, 0.15));
    transform-origin: bottom center;
}

.mascot-avatar-shadow {
    width: 130px;
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

/* ─── CELEBRATION Redesain ─── */
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
    animation: celebLoad 3s linear forwards;
}

/* ─── MOBILE RESPONSIVE ─── */
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
    .btn-duo {
        padding: 10px 20px;
        font-size: 13px;
        border-radius: 12px;
        border-bottom-width: 4px;
    }
    .hide-mobile { display: none; }
    .btn-icon-mobile { padding: 10px !important; width: 48px; min-width: 48px; height: 48px; display: inline-flex; justify-content: center; align-items: center; border-radius: 12px; }
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
}
/* ─── CELEBRATION ─── */
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
@keyframes celebFadeIn { from { opacity: 0; } to { opacity: 1; } }
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
.mascot-bubble-wrap {
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
.mascot-avatar-img {
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
.celeb-loader {
    width: 180px;
    height: 8px;
    background-color: #f1f5f9;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
    margin-top: 20px;
    animation: slideUp 0.5s ease 0.5s both;
}
.celeb-loader-bar {
    position: absolute;
    top: 0; left: 0; bottom: 0;
    width: 40%;
    background-color: #1cb0f6;
    border-radius: 8px;
    animation: loading 1.5s infinite ease-in-out;
}
@keyframes loading {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(250%); }
}

@media (max-width: 768px) {
    .celeb-overlay { padding: 16px 16px; justify-content: center; gap: 0; }
    .mascot-avatar-img { height: 130px; }
    .mascot-bubble-wrap { width: 220px; margin-bottom: 10px; }
    .mascot-speech-bubble { font-size: 13px; padding: 12px 14px; }
    .celeb-label { font-size: 24px; margin-top: 10px; margin-bottom: 4px; }
    .celeb-sub { font-size: 13px; margin-bottom: 16px; }
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

@media (max-width: 768px) {
    .music-footer-btn { display: flex; }
}

/* ── Minimalist footer adjustment on small mobile ── */
@media (max-width: 480px) {
    .footer-bar { height: 76px; }
    .footer-inner { padding: 0 16px; }
}
</style>
