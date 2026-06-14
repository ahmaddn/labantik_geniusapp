<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    CheckCircle2,
    XCircle,
    ArrowLeft,
    Trophy,
    ClipboardList,
    ChevronRight,
    Rocket,
    Zap,
    Target,
    BarChart3,
    BookOpen,
    Clock,
    Medal,
    Flame,
    Star,
    Sparkles,
    TrendingUp,
    AlertCircle,
    ChevronDown,
    ChevronUp,
    LayoutGrid,
    MousePointerClick,
    MoveHorizontal,
    ToggleLeft,
    Check,
    X,
} from "lucide-vue-next";

const props = defineProps({
    mission: { type: Object, required: true },
    results: { type: Object, required: true },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    module: { type: Object, default: () => ({ id: null, name: "Modul" }) },
    all_missions_done: { type: Boolean, default: false },
    next_mission: { type: Object, default: null },
    is_overall: { type: Boolean, default: false },
});

const TYPE_META = {
    multiple_choices: {
        label: "Pilihan Ganda",
        color: "#1cb0f6",
        bg: "#ddf4ff",
        icon: LayoutGrid,
    },
    true_false: {
        label: "Benar / Salah",
        color: "#a855f7",
        bg: "#f3e8ff",
        icon: ToggleLeft,
    },
    case_study: {
        label: "Studi Kasus",
        color: "#00c9b1",
        bg: "#ccf4f0",
        icon: BookOpen,
    },
    drag_drop: {
        label: "Seret & Lepas",
        color: "#ff9600",
        bg: "#ffebd6",
        icon: MoveHorizontal,
    },
    short_answer: {
        label: "Isian Singkat",
        color: "#ff4b4b",
        bg: "#ffe5e5",
        icon: MousePointerClick,
    },
};

const showDetails = ref(false);

const formatTime = (sec) => {
    if (!sec || sec < 0) return "0s";
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    if (m > 0) return `${m}m ${s}s`;
    return `${s}s`;
};

const score = computed(() => {
    if (props.is_overall) {
        return Math.round(props.results.overall_score || 0);
    }
    return Math.round(props.results.score || 0);
});

const accuracy = computed(() => {
    if (props.is_overall) return props.results.overall_accuracy || 0;
    const total = props.results.total_questions || 0;
    const correct = props.results.correct_answers || 0;
    if (total === 0) return 0;
    return Math.round((correct / total) * 100);
});

const correctCount = computed(() => {
    if (props.is_overall) {
        return props.results.overall_correct || 0;
    }
    return props.results.correct_answers || 0;
});

const incorrectCount = computed(() => {
    if (props.is_overall) {
        const total = props.results.overall_total || 0;
        return total - (props.results.overall_correct || 0);
    }
    const total = props.results.total_questions || 0;
    return total - (props.results.correct_answers || 0);
});

const gradeData = computed(() => {
    const s = score.value;
    if (s >= 90) return { title: "Luar Biasa!", color: "#58cc02", icon: Star };
    if (s >= 75) return { title: "Kerja Bagus!", color: "#1cb0f6", icon: Zap };
    if (s >= 60)
        return { title: "Cukup Baik!", color: "#ff9600", icon: TrendingUp };
    return { title: "Terus Belajar!", color: "#ff4b4b", icon: Flame };
});

const goBack = () => {
    if (props.is_overall) {
        router.visit(route("playground.index"));
    } else {
        router.visit(route("playground.missions.index", props.module?.id));
    }
};

const startNextMission = () => {
    if (props.next_mission) {
        router.visit(route("playground.missions.show", props.next_mission.id));
    }
};

const goToPosttest = () => {
    if (props.module?.id) {
        router.visit(route("playground.posttest.show", props.module.id));
    }
};

const isAnswerCorrect = (detail, ans) => {
    if (detail.question.type === "drag_drop") return ans.is_correct;
    if (detail.question.type === "short_answer") return ans.is_correct;
    return detail.is_correct;
};

const getQuizExplanation = (detail) => {
    return detail.question?.explanation || null;
};
</script>

<template>
    <div class="app-layout">
        <main class="main-scroll">
            <div class="result-container">
                <div class="header-section">
                    <h1 class="main-title" :style="{ color: gradeData.color }">
                        {{ gradeData.title }}
                    </h1>
                    <p class="subtitle">
                        {{
                            is_overall
                                ? `Evaluasi ${module.name} Selesai`
                                : `Misi ${mission.title} Selesai`
                        }}
                    </p>
                </div>

                <div class="score-mascot-section">
                    <div class="mascot-wrap">
                        <img
                            src="/images/templates/pose_jempol.png"
                            alt="Mascot"
                            class="mascot-img"
                        />
                    </div>

                    <div class="celeb-ring-wrap">
                        <svg class="celeb-ring-svg" viewBox="0 0 140 140">
                            <circle
                                cx="70"
                                cy="70"
                                r="58"
                                class="celeb-track"
                            />
                            <circle
                                cx="70"
                                cy="70"
                                r="58"
                                class="celeb-prog"
                                :style="{
                                    strokeDashoffset: 364 - (364 * score) / 100,
                                    stroke: gradeData.color,
                                }"
                            />
                        </svg>
                        <div class="celeb-ring-inner">
                            <span
                                class="celeb-score"
                                :style="{ color: gradeData.color }"
                                >{{ score }}</span
                            >
                        </div>
                    </div>
                </div>

                <div class="icard-stats">
                    <div class="istat istat--green">
                        <div class="istat-icon">
                            <CheckCircle2 :size="20" :stroke-width="2.5" />
                        </div>
                        <span class="istat-val">{{ correctCount }}</span>
                        <span class="istat-lbl">Benar</span>
                    </div>

                    <div class="istat istat--red">
                        <div class="istat-icon">
                            <XCircle :size="20" :stroke-width="2.5" />
                        </div>
                        <span class="istat-val">{{ incorrectCount }}</span>
                        <span class="istat-lbl">Salah</span>
                    </div>

                    <div class="istat istat--blue">
                        <div class="istat-icon">
                            <BarChart3 :size="20" :stroke-width="2.5" />
                        </div>
                        <span class="istat-val">{{ score }}</span>
                        <span class="istat-lbl">Nilai</span>
                    </div>
                </div>

                <div class="review-section" v-if="!is_overall">
                    <button
                        class="btn-toggle-details"
                        @click="showDetails = !showDetails"
                    >
                        <span>Lihat Detail Jawaban</span>
                        <ChevronUp v-if="showDetails" :size="20" />
                        <ChevronDown v-else :size="20" />
                    </button>

                    <Transition name="slide-fade">
                        <div v-if="showDetails" class="details-list">
                            <div
                                v-for="(detail, index) in results.details"
                                :key="index"
                                class="detail-card"
                                :class="{
                                    'card-correct': detail.is_correct,
                                    'card-wrong': !detail.is_correct,
                                }"
                            >
                                <div class="dc-header">
                                    <div class="dc-badge">
                                        Soal {{ index + 1 }}
                                    </div>
                                    <div class="dc-type">
                                        <component
                                            :is="
                                                TYPE_META[detail.question.type]
                                                    ?.icon || BookOpen
                                            "
                                            :size="14"
                                            class="mr-1"
                                        />
                                        {{
                                            TYPE_META[detail.question.type]
                                                ?.label || "Soal"
                                        }}
                                    </div>
                                    <div
                                        class="dc-status"
                                        :class="
                                            detail.is_correct
                                                ? 'text-green'
                                                : 'text-red'
                                        "
                                    >
                                        <CheckCircle2
                                            v-if="detail.is_correct"
                                            :size="20"
                                        />
                                        <XCircle v-else :size="20" />
                                    </div>
                                </div>

                                <div
                                    class="dc-question"
                                    v-html="detail.question.question_text"
                                ></div>

                                <div class="dc-answers">
                                    <div
                                        v-for="(
                                            ans, aIdx
                                        ) in detail.user_answers"
                                        :key="aIdx"
                                        class="answer-item"
                                        :class="
                                            isAnswerCorrect(detail, ans)
                                                ? 'ans-correct'
                                                : 'ans-wrong'
                                        "
                                    >
                                        <div class="ans-icon">
                                            <Check
                                                v-if="
                                                    isAnswerCorrect(detail, ans)
                                                "
                                                :size="16"
                                            />
                                            <X v-else :size="16" />
                                        </div>
                                        <div class="ans-text">
                                            <span class="font-bold">Kamu:</span>
                                            {{
                                                ans.user_answer_text ||
                                                ans.answer_text ||
                                                "-"
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    v-if="getQuizExplanation(detail)"
                                    class="dc-explanation"
                                >
                                    <div class="expl-title">
                                        <Sparkles :size="14" /> Penjelasan:
                                    </div>
                                    <div
                                        class="expl-text"
                                        v-html="getQuizExplanation(detail)"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>
        </main>

        <footer class="footer-bar">
            <div class="footer-inner">
                <div class="footer-left">
                    <button class="btn-duo btn-duo-secondary" @click="goBack">
                        <ArrowLeft :size="18" :stroke-width="3" />
                        <span>{{
                            is_overall ? "Tutup Evaluasi" : "Kembali"
                        }}</span>
                    </button>
                </div>

                <div class="footer-right">
                    <template v-if="!is_overall">
                        <button
                            v-if="next_mission"
                            class="btn-duo btn-duo-primary"
                            @click="startNextMission"
                        >
                            <span>Misi Selanjutnya</span>
                            <ChevronRight :size="18" :stroke-width="3" />
                        </button>
                        <button
                            v-else-if="all_missions_done"
                            class="btn-duo btn-duo-success"
                            @click="goToPosttest"
                        >
                            <span>Lanjut Posttest</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                        <button
                            v-else
                            class="btn-duo btn-duo-secondary"
                            @click="goBack"
                        >
                            <span>Selesai</span>
                            <CheckCircle2 :size="18" :stroke-width="3" />
                        </button>
                    </template>
                    <template v-else>
                        <button class="btn-duo btn-duo-success" @click="goBack">
                            <span>Selesai</span>
                            <CheckCircle2 :size="18" :stroke-width="3" />
                        </button>
                    </template>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
@import url("https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@400;500;600;700;800&display=swap");

/* ─── BASE ─── */
.app-layout {
    position: relative;
    width: 100vw;
    min-height: 100vh;
    font-family: "Nunito", sans-serif;
    background-color: #ffffff;
    overflow-x: hidden;
}

.main-scroll {
    position: relative;
    z-index: 10;
    padding-top: 40px;
    padding-bottom: 130px;
    min-height: 100vh;
    display: flex;
    justify-content: center;
}

.result-container {
    width: 100%;
    max-width: 600px;
    padding: 0 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    animation: slideUpFade 0.5s ease-out;
}

/* ─── HEADER ─── */
.header-section {
    text-align: center;
    margin-bottom: 30px;
}

.main-title {
    font-family: "Baloo 2", cursive;
    font-size: 42px;
    font-weight: 800;
    line-height: 1.1;
    margin-bottom: 8px;
    letter-spacing: 1px;
}

.subtitle {
    font-size: 18px;
    font-weight: 700;
    color: #afafaf;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* ─── MASCOT & SCORE RING ─── */
.score-mascot-section {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 40px;
    margin-bottom: 40px;
}

.mascot-wrap {
    width: 160px;
}

.mascot-img {
    width: 100%;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 8px 16px rgba(0, 0, 0, 0.1));
    animation: bounceIdle 4s ease-in-out infinite;
}

.celeb-ring-wrap {
    position: relative;
    width: 160px;
    height: 160px;
}

.celeb-ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.celeb-track {
    fill: none;
    stroke: #f1f5f9;
    stroke-width: 12;
}

.celeb-prog {
    fill: none;
    stroke-width: 12;
    stroke-linecap: round;
    stroke-dasharray: 364;
    transition: stroke-dashoffset 1.5s cubic-bezier(0.34, 1.56, 0.64, 1) 0.5s;
}

.celeb-ring-inner {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}

.celeb-score {
    font-family: "Baloo 2", cursive;
    font-size: 52px;
    font-weight: 800;
    line-height: 1;
}

/* ─── STATS GRID ─── */
.icard-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    width: 100%;
    margin-bottom: 30px;
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
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
}

.istat--yellow .istat-icon {
    background-color: #ffc800;
}
.istat--green .istat-icon {
    background-color: #58cc02;
}
.istat--blue .istat-icon {
    background-color: #1cb0f6;
}

.istat-val {
    font-family: "Baloo 2", cursive;
    font-size: 24px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1;
}

.istat-lbl {
    font-size: 13px;
    font-weight: 800;
    color: #afafaf;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* ─── DETAILS ACCORDION ─── */
.review-section {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.btn-toggle-details {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-bottom: 4px solid #cbd5e1;
    border-radius: 16px;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    font-weight: 800;
    color: #777777;
    cursor: pointer;
    transition: all 0.15s ease;
}

.btn-toggle-details:active {
    transform: translateY(2px);
    border-bottom-width: 2px;
}

.details-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.detail-card {
    background-color: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 16px;
    padding: 16px;
    overflow: hidden;
}

.card-correct {
    border-left: 6px solid #58cc02;
}

.card-wrong {
    border-left: 6px solid #ff4b4b;
}

.dc-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

.dc-badge {
    background-color: #f1f5f9;
    color: #64748b;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
}

.dc-type {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 800;
    color: #94a3b8;
    flex: 1;
}

.text-green {
    color: #58cc02;
}
.text-red {
    color: #ff4b4b;
}

.dc-question {
    font-size: 15px;
    font-weight: 700;
    color: #3c3c3c;
    margin-bottom: 16px;
    line-height: 1.5;
}

:deep(.dc-question p) {
    margin: 0;
}

.dc-answers {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.answer-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 12px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 700;
}

.ans-correct {
    background-color: #eefdf0;
    color: #58cc02;
    border: 1px solid #c2f5cc;
}

.ans-wrong {
    background-color: #ffe5e5;
    color: #ff4b4b;
    border: 1px solid #fecaca;
}

.ans-icon {
    flex-shrink: 0;
    margin-top: 2px;
}

.ans-text {
    flex: 1;
}

.dc-explanation {
    background-color: #f8fafc;
    border-radius: 12px;
    padding: 12px;
    border: 1px solid #e2e8f0;
}

.expl-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 800;
    color: #64748b;
    margin-bottom: 4px;
}

.expl-text {
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}

/* ─── FIXED FOOTER ACTION BAR (DUOLINGO STYLE) ─── */
.footer-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 94px;
    background: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border-top: 2px solid #e5e5e5;
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
    font-family: "Nunito", sans-serif;
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

/* ─── ANIMATIONS ─── */
@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounceIdle {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-8px);
    }
}

.slide-fade-enter-active {
    transition: all 0.3s ease-out;
}
.slide-fade-leave-active {
    transition: all 0.2s cubic-bezier(1, 0.5, 0.8, 1);
}
.slide-fade-enter-from,
.slide-fade-leave-to {
    transform: translateY(-10px);
    opacity: 0;
}

/* ─── MOBILE RESPONSIVE ─── */
@media (max-width: 768px) {
    .main-title {
        font-size: 32px;
    }
    .subtitle {
        font-size: 14px;
    }

    .score-mascot-section {
        gap: 20px;
        margin-bottom: 30px;
    }
    .mascot-wrap {
        width: 120px;
    }
    .celeb-ring-wrap {
        width: 130px;
        height: 130px;
    }
    .celeb-score {
        font-size: 40px;
    }

    .icard-stats {
        gap: 10px;
    }
    .istat {
        padding: 12px 8px;
    }
    .istat-val {
        font-size: 18px;
    }
    .istat-lbl {
        font-size: 10px;
    }
    .istat-icon {
        width: 32px;
        height: 32px;
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
    .btn-duo:active:not(:disabled) {
        transform: translateY(2px);
        border-bottom-width: 2px;
    }
}

@media (max-width: 480px) {
    .mascot-wrap {
        display: none; /* Hide mascot on very small screens to focus on score */
    }
    .score-mascot-section {
        justify-content: center;
    }

    .footer-inner {
        gap: 8px;
    }
    .btn-duo span {
        display: none; /* Hide text, only show icons on very small screens */
    }
    .btn-duo {
        padding: 12px;
        border-radius: 50%;
    }
}
</style>
