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
} from "lucide-vue-next";

import Multiple_choice from "@/Components/Quiz/Multiple_choice.vue";
import True_false from "@/Components/Quiz/True_false.vue";
import Case_study from "@/Components/Quiz/Case_study.vue";
import Drag_drop from "@/Components/Quiz/Drag_drop.vue";
import Short_answer from "@/Components/Quiz/Short_answer.vue";
import Materials from "@/Components/Quiz/Materials.vue";
import PretestLayout from "@/Layouts/PretestLayout.vue";

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

const mascotUrl = computed(() => {
    if (phase.value === "intro") return "/images/templates/pose_nunjuk.png";
    if (phase.value === "done" || phase.value === "celebration") return "/images/templates/pose_jempol.png";
    return "/images/templates/pose_pikir.png";
});

const BUBBLES_INTRO = ["Yuk, baca petunjuknya dulu!", "Siap mengukur akhir belajarmu?", "Tunjukkan kemampuan terbaikmu!"];
const BUBBLES_QUIZ = ["Semangat ya!", "Baca dengan teliti!", "Pikirkan baik-baik!", "Hampir selesai!", "Fokus dan tenang!"];
const BUBBLES_DONE = ["Luar biasa! Kamu keren!", "Posttest selesai! Hebat!", "Jempol buat kamu!"];
const BUBBLES_CELEBRATION = ["Woohooo! Skor kamu keren!", "Kamu luar biasa!", "Tunggu sebentar..."];
const BUBBLES = computed(() => {
    if (phase.value === "intro") return BUBBLES_INTRO;
    if (phase.value === "done" || phase.value === "celebration") return BUBBLES_CELEBRATION;
    return BUBBLES_QUIZ;
});
const bubbleIdx = ref(0);
const bubbleVisible = ref(true);
let bubbleTimer = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.value.length;
        bubbleVisible.value = true;
    }, 300);
};

watch(phase, () => {
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => { bubbleVisible.value = true; }, 200);
});

const timeLimit = computed(() => (props.quiz?.time_limit ?? 10) * 60);

const SS_KEY = `geniuss_posttest_timer_${props.quiz?.id}`;
function ssGet(key, fallback) {
    try { const v = sessionStorage.getItem(key); return v !== null ? JSON.parse(v) : fallback; } catch { return fallback; }
}
function ssSet(key, val) { try { sessionStorage.setItem(key, JSON.stringify(val)); } catch {} }
function ssDel(key) { try { sessionStorage.removeItem(key); } catch {} }

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
        if (remaining.value <= 0) { clearInterval(timerInt); ssDel(SS_KEY); submitQuiz(); return; }
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
    if (quizType.value === "drag_drop") return val && typeof val === "object" && !Array.isArray(val) && Object.keys(val).length > 0;
    if (Array.isArray(val)) return val.length > 0;
    return val !== null && val !== undefined && val !== "";
}

const canGoNext = computed(() => {
    if (!currentQ.value) return true;
    if (quizType.value === "material") return true;
    return isQuestionAnswered(currentQ.value);
});

const answeredCnt = computed(() => questions.value.filter((q) => isQuestionAnswered(q)).length);
const progressPct = computed(() => totalQ.value === 0 ? 100 : Math.round((answeredCnt.value / totalQ.value) * 100));

function updateAnswer({ questionId, value }) { answers.value = { ...answers.value, [questionId]: value }; }

function goPrev() { if (!isFirst.value) currentIdx.value--; }
function goNext() {
    if (!canGoNext.value) { shakeActive.value = true; setTimeout(() => { shakeActive.value = false; }, 600); return; }
    if (!isLast.value) currentIdx.value++;
    else submitQuiz();
}

function startQuiz() {
    brandMoved.value = true;
    setTimeout(() => { phase.value = "quiz"; startTimer(); }, 420);
}

function submitQuiz() {
    if (submitting.value) return;
    clearInterval(timerInt);
    ssDel(SS_KEY);
    // Hitung estimasi skor lokal untuk celebration (sekedar estimasi, skor final dari server)
    const total = questions.value.filter(q => q.quiz_type !== 'material').length || questions.value.length;
    const correct = questions.value.filter(q => isQuestionAnswered(q)).length;
    celebScore.value = total > 0 ? Math.round((correct / total) * 100) : 0;
    phase.value = "celebration";

    // Setelah 3 detik baru submit ke backend
    setTimeout(() => {
        submitting.value = true;
        const payload = {
            quiz_id: props.quiz?.id,
            module_id: props.module?.id,
            time_taken: timeLimit.value - remaining.value,
            answers: Object.entries(answers.value).map(([question_id, value]) => ({ question_id, value })),
        };
        router.post(route("playground.posttest.submit"), payload, {
            preserveState: false,
            onError: () => { submitting.value = false; phase.value = "quiz"; },
        });
    }, 3200);
}

function goHome() { router.visit(route("playground.index")); }
function goBack() { router.visit(route("playground.index")); }

const INSTR_ITEMS = [
    { color: "red", icon: Eye, text: "Baca setiap soal dengan teliti." },
    { color: "yellow", icon: CircleCheck, text: "Pilih atau kerjakan jawaban paling tepat." },
    { color: "blue", icon: Timer, text: "Kerjakan sesuai waktu yang tersedia." },
    { color: "green", icon: ChevronRight, text: "Klik Berikutnya setelah menjawab." },
];

onMounted(() => {
    setTimeout(() => { ready.value = true; }, 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
});
onUnmounted(() => {
    clearInterval(timerInt);
    clearInterval(bubbleTimer);
});
</script>

<template>
    <PretestLayout
        :timerDisplay="timerDisplay"
        :isWarning="timerWarning"
        :progressPercent="progressPct"
        :showProgress="phase === 'quiz'"
        :backsound="props.backsound"
    >
        <div class="main-wrapper" :class="{ 'main--on': ready }">
            <div class="mission-container">
                <!-- ══ PHASE: INTRO ══ -->
                <template v-if="phase === 'intro'">
                    <div class="title-pill" style="background: #ef4444; border-color: #fca5a5; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);">
                        POSTTEST: {{ module.name.toUpperCase() }}
                    </div>
                    <div class="question-bubble" style="background: #fecaca;">
                        <span style="color: #991b1b;">{{ quiz.description ?? "Jawab semua soal untuk mengukur kemampuanmu setelah menyelesaikan semua misi!" }}</span>
                    </div>
                    <div class="component-box" style="display: flex; flex-direction: column; align-items: center; gap: 20px;">
                        <div class="icard-stats">
                            <div class="istat istat--red">
                                <div class="istat-icon"><BookOpen :size="19" :stroke-width="1.8" /></div>
                                <span class="istat-val">{{ totalQ }}</span>
                                <span class="istat-lbl">Soal</span>
                            </div>
                            <div class="istat istat--yellow">
                                <div class="istat-icon"><Clock :size="19" :stroke-width="1.8" /></div>
                                <span class="istat-val">{{ quiz.time_limit ?? 10 }}</span>
                                <span class="istat-lbl">Menit</span>
                            </div>
                            <div class="istat istat--blue">
                                <div class="istat-icon"><Award :size="19" :stroke-width="1.8" /></div>
                                <span class="istat-val">XP</span>
                                <span class="istat-lbl">Hadiah</span>
                            </div>
                        </div>

                        <div class="icard-instr-grid">
                            <div v-for="(item, i) in INSTR_ITEMS" :key="i" class="instr-row" :class="`instr-row--${item.color}`">
                                <span class="instr-num">{{ String(i + 1).padStart(2, "0") }}</span>
                                <component :is="item.icon" :size="13" :stroke-width="2.5" class="instr-ico" />
                                <span class="instr-txt">{{ item.text }}</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ══ PHASE: QUIZ ══ -->
                <template v-else-if="phase === 'quiz'">
                    <div class="title-pill">
                        SOAL {{ currentIdx + 1 }}
                    </div>
                    <div class="question-bubble" v-html="currentQ?.question_text"></div>
                    <div class="component-box" :class="{ 'opts--shake': shakeActive }">
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
                    <div class="title-pill" style="background: #10b981; border-color: #34d399; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);">
                        SELESAI!
                    </div>
                    <div class="question-bubble" style="background: #a7f3d0;">
                        <span style="color: #065f46;">Selamat! Posttest telah selesai. Mari kita lihat hasilnya.</span>
                    </div>
                    <div class="component-box" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px;">
                        <div class="trophy-wrap">
                            <div class="trophy-ring">
                                <Trophy :size="64" color="#34D399" :stroke-width="1.5" />
                            </div>
                            <span class="sp sp1"><Sparkles :size="18" color="#2563EB" /></span>
                            <span class="sp sp2"><Star :size="16" color="#F59E0B" fill="#F59E0B" /></span>
                            <span class="sp sp3"><Sparkles :size="14" color="#BFDBFE" /></span>
                        </div>
                    </div>
                </template>

                <!-- ══ PHASE: CELEBRATION ══ -->
                <template v-else-if="phase === 'celebration'">
                    <div class="celeb-overlay">
                        <div class="celeb-confetti" aria-hidden="true">
                            <span v-for="n in 18" :key="n" :class="`conf conf-${n}`"></span>
                        </div>
                        <div class="celeb-ring-wrap">
                            <svg class="celeb-ring-svg" viewBox="0 0 140 140">
                                <circle cx="70" cy="70" r="58" class="celeb-track"/>
                                <circle cx="70" cy="70" r="58" class="celeb-prog"
                                    :style="{ strokeDashoffset: 364 - (364 * celebScore / 100) }"/>
                            </svg>
                            <div class="celeb-ring-inner">
                                <span class="celeb-score">{{ celebScore }}</span>
                                <span class="celeb-pct">%</span>
                            </div>
                        </div>
                        <div class="celeb-label">🎉 Posttest Selesai!</div>
                        <div class="celeb-sub">Menyimpan nilai...</div>
                        <div class="celeb-loader">
                            <div class="celeb-loader-bar"></div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- ══ ABSOLUTE ELEMENTS (Mascot & Buttons) ══ -->
        <div class="mascot-absolute" @click="rotateBubble">
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
            <template v-if="phase === 'intro'">
                <button
                    class="pill-btn"
                    style="background: #f59e0b; border-color: #fbbf24; box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4);"
                    @click="startQuiz"
                >
                    <span>Mulai Posttest</span>
                    <Rocket :size="20" :stroke-width="2.5" />
                </button>
                <button
                    class="pill-btn"
                    style="background: #ef4444; border-color: #f87171; box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4); margin-left: 10px;"
                    @click="goBack"
                >
                    <ArrowLeft :size="20" :stroke-width="2.5" />
                    <span>Kembali</span>
                </button>
            </template>

            <template v-else-if="phase === 'quiz'">
                <div style="display: flex; gap: 10px;">
                    <button
                        v-if="!isFirst"
                        class="pill-btn"
                        style="background: #64748b; border-color: #94a3b8; box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4);"
                        @click="goPrev"
                        :disabled="submitting"
                    >
                        <ArrowLeft :size="20" :stroke-width="2.5" />
                    </button>

                    <button
                        v-if="isLast"
                        class="pill-btn pill-btn-finish"
                        @click="submitQuiz"
                        :disabled="submitting || !canGoNext"
                    >
                        <span v-if="!submitting">Selesaikan Posttest</span>
                        <Loader2 v-else :size="20" class="spin" />
                        <CheckCircle2 v-if="!submitting" :size="20" :stroke-width="2.5" />
                    </button>
                    <button
                        v-else
                        class="pill-btn pill-btn-next"
                        @click="goNext"
                        :disabled="!canGoNext || submitting"
                    >
                        <span>Selanjutnya</span>
                        <ArrowRight :size="20" :stroke-width="2.5" />
                    </button>
                </div>
            </template>

            <template v-else-if="phase === 'done'">
                <button
                    class="pill-btn pill-btn-finish"
                    @click="goHome"
                    :disabled="submitting"
                >
                    <span>Kembali ke Beranda</span>
                    <Rocket :size="20" :stroke-width="2.5" />
                </button>
            </template>
        </div>
    </PretestLayout>
</template>

<style scoped>
/* ─── MAIN CENTRIC CONTENT ─── */
.main-wrapper {
    position: relative;
    z-index: 10;
    flex: 1;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 10px 20px 140px;
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
    margin-bottom: -16px;
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

/* ── INTRO STATS ── */
.icard-stats { display: grid; grid-template-columns: repeat(3, 1fr); width: 100%; max-width: 500px; border-radius: 16px; overflow: hidden; border: 1.5px solid #e2e8f0; margin-bottom: 10px; }
.istat { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 20px 12px 18px; border-right: 1.5px solid #e2e8f0; }
.istat:last-child { border-right: none; }
.istat--red { background: #fff7f7; }
.istat--red .istat-icon { background: #f87171; box-shadow: 0 4px 14px rgba(248,113,113,0.3); }
.istat--red .istat-val { color: #c53030; }
.istat--red .istat-lbl { color: #e53e3e; }
.istat--yellow { background: #fffdf0; }
.istat--yellow .istat-icon { background: #fbbf24; box-shadow: 0 4px 14px rgba(251,191,36,0.3); }
.istat--yellow .istat-val { color: #92400e; }
.istat--yellow .istat-lbl { color: #d97706; }
.istat--blue { background: #f0fdf9; }
.istat--blue .istat-icon { background: #34d399; box-shadow: 0 4px 14px rgba(52,211,153,0.3); }
.istat--blue .istat-val { color: #065f46; }
.istat--blue .istat-lbl { color: #059669; }
.istat-icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; margin-bottom: 2px; }
.istat-val { font-family: "Righteous", cursive; font-size: 24px; line-height: 1; }
.istat-lbl { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; }

.icard-instr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; width: 100%; max-width: 600px; }
.instr-row { display: flex; align-items: center; gap: 9px; border-radius: 12px; padding: 11px 13px; font-size: 12.5px; font-weight: 800; color: #1e293b; border: 1.5px solid transparent; }
.instr-row--red { background: #fff7f7; border-color: #fecaca; }
.instr-row--red .instr-num { background: #f87171; color: #fff; }
.instr-row--red .instr-ico { color: #f87171; }
.instr-row--yellow { background: #fffdf0; border-color: #fde68a; }
.instr-row--yellow .instr-num { background: #fbbf24; color: #fff; }
.instr-row--yellow .instr-ico { color: #d97706; }
.instr-row--green { background: #f0fdf9; border-color: #a7f3d0; }
.instr-row--green .instr-num { background: #34d399; color: #fff; }
.instr-row--green .instr-ico { color: #059669; }
.instr-row--blue { background: #eff6ff; border-color: #bfdbfe; }
.instr-row--blue .instr-num { background: #3b82f6; color: #fff; }
.instr-row--blue .instr-ico { color: #3b82f6; }
.instr-num { font-family: "Righteous", cursive; font-size: 11px; border-radius: 6px; padding: 3px 7px; flex-shrink: 0; line-height: 1.4; }
.instr-ico { flex-shrink: 0; }
.instr-txt { flex: 1; line-height: 1.45; word-break: break-word; }

/* ── TROPHY ── */
.trophy-wrap { position: relative; width: 120px; height: 120px; display: flex; align-items: center; justify-content: center; margin: 20px; }
.trophy-ring { width: 110px; height: 110px; border-radius: 50%; background: #d1fae5; border: 2.5px solid #34d399; display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 22px rgba(52,211,153,0.26); animation: tPop 0.5s cubic-bezier(0.34,1.56,0.64,1) both 0.08s; }
@keyframes tPop { from { transform: scale(0) rotate(-15deg); opacity: 0; } to { transform: scale(1) rotate(0); opacity: 1; } }
.sp { position: absolute; animation: spFloat 2.2s ease-in-out infinite; }
.sp1 { top: -2px; right: 2px; }
.sp2 { bottom: 2px; left: -2px; animation-delay: 0.5s; }
.sp3 { top: 8px; left: -4px; animation-delay: 1.1s; }
@keyframes spFloat { 0%, 100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-8px) rotate(14deg); } }

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
    .action-btn-absolute { bottom: 20px; right: 20px; }
    .pill-btn { padding: 8px 20px; font-size: 14px; }
    .icard-stats { grid-template-columns: 1fr; }
    .icard-instr-grid { grid-template-columns: 1fr; }
}

/* ══ CELEBRATION ══ */
.celeb-overlay {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    animation: celebFadeIn 0.5s ease both;
}
@keyframes celebFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.celeb-confetti {
    position: absolute; inset: 0; pointer-events: none; overflow: hidden;
}
.conf { position: absolute; border-radius: 2px; animation: confFall linear infinite; }
.conf-1  { width:7px;height:7px;background:#f59e0b;top:-8px;left:5%;animation-duration:2.5s;animation-delay:.1s; }
.conf-2  { width:5px;height:5px;background:#3b82f6;top:-8px;left:15%;animation-duration:3.1s;animation-delay:.5s; }
.conf-3  { width:6px;height:6px;background:#ef4444;top:-8px;left:27%;animation-duration:2.7s;animation-delay:.2s; }
.conf-4  { width:5px;height:5px;background:#10b981;top:-8px;left:40%;animation-duration:3.3s;animation-delay:.8s; }
.conf-5  { width:8px;height:8px;background:#a78bfa;top:-8px;left:55%;animation-duration:2.9s;animation-delay:.4s; }
.conf-6  { width:5px;height:5px;background:#f59e0b;top:-8px;left:68%;animation-duration:3.2s;animation-delay:1s; }
.conf-7  { width:6px;height:6px;background:#ef4444;top:-8px;left:79%;animation-duration:2.6s;animation-delay:.6s; }
.conf-8  { width:4px;height:4px;background:#3b82f6;top:-8px;left:88%;animation-duration:3s;animation-delay:1.2s; }
.conf-9  { width:7px;height:7px;background:#10b981;top:-8px;left:22%;animation-duration:3.5s;animation-delay:.3s; }
.conf-10 { width:5px;height:5px;background:#a78bfa;top:-8px;left:48%;animation-duration:2.8s;animation-delay:.9s; }
.conf-11 { width:6px;height:6px;background:#f59e0b;top:-8px;left:62%;animation-duration:3.2s;animation-delay:1.4s; }
.conf-12 { width:4px;height:4px;background:#ef4444;top:-8px;left:92%;animation-duration:2.5s;animation-delay:.7s; }
.conf-13 { width:8px;height:8px;background:#3b82f6;top:-8px;left:10%;animation-duration:3.1s;animation-delay:0.2s; }
.conf-14 { width:5px;height:5px;background:#10b981;top:-8px;left:35%;animation-duration:2.7s;animation-delay:1.1s; }
.conf-15 { width:6px;height:6px;background:#f59e0b;top:-8px;left:75%;animation-duration:3.4s;animation-delay:0.5s; }
.conf-16 { width:7px;height:7px;background:#a78bfa;top:-8px;left:85%;animation-duration:2.9s;animation-delay:0.8s; }
.conf-17 { width:5px;height:5px;background:#ef4444;top:-8px;left:50%;animation-duration:3.2s;animation-delay:0.4s; }
.conf-18 { width:6px;height:6px;background:#3b82f6;top:-8px;left:95%;animation-duration:2.6s;animation-delay:1.3s; }
@keyframes confFall { 0%{transform:translateY(0) rotate(0);opacity:1} 100%{transform:translateY(100vh) rotate(360deg);opacity:0} }

.celeb-ring-wrap {
    position: relative;
    width: 160px;
    height: 160px;
    margin-bottom: 20px;
    animation: celebPop 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) 0.2s both;
}
@keyframes celebPop {
    0% { transform: scale(0.5); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.celeb-ring-svg {
    width: 100%; height: 100%; transform: rotate(-90deg);
}
.celeb-track {
    fill: none; stroke: rgba(0,0,0,0.05); stroke-width: 12;
}
.celeb-prog {
    fill: none; stroke-width: 12; stroke-linecap: round;
    stroke-dasharray: 364;
    transition: stroke-dashoffset 1.5s cubic-bezier(0.34,1.56,0.64,1) 0.5s;
    stroke: #10b981;
}

.celeb-ring-inner {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center; gap: 2px;
}
.celeb-score {
    font-family: "Righteous", cursive; font-size: 48px; color: #10b981; line-height: 1;
}
.celeb-pct {
    font-size: 20px; font-weight: 900; color: #94a3b8; align-self: flex-end; margin-bottom: 8px;
}

.celeb-label {
    font-family: "Righteous", cursive;
    font-size: 32px;
    color: #1e3a8a;
    margin-bottom: 8px;
    animation: slideUp 0.5s ease 0.4s both;
}
.celeb-sub {
    font-size: 16px;
    color: #64748b;
    font-weight: 700;
    margin-bottom: 24px;
    animation: slideUp 0.5s ease 0.5s both;
}

.celeb-loader {
    width: 200px;
    height: 6px;
    background: rgba(0,0,0,0.05);
    border-radius: 10px;
    overflow: hidden;
    animation: slideUp 0.5s ease 0.6s both;
}
.celeb-loader-bar {
    height: 100%;
    background: #10b981;
    width: 0%;
    animation: celebLoad 3s linear forwards;
}
@keyframes celebLoad {
    to { width: 100%; }
}
@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: none; }
}
</style>
