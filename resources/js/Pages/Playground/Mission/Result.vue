<script setup>
import { computed } from "vue";
import { router } from "@inertiajs/vue3";
import {
    CheckCircle2,
    XCircle,
    ArrowLeft,
    Trophy,
    LayoutGrid,
    ToggleLeft,
    FileSearch,
    ClipboardList,
    BookOpen,
    ChevronRight,
    Star,
    Rocket,
    MapPin,
} from "lucide-vue-next";

const props = defineProps({
    mission: { type: Object, required: true },
    results: { type: Object, required: true },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    module: { type: Object, default: () => ({ id: null, name: "Modul" }) },
    all_missions_done: { type: Boolean, default: false },
    next_mission: { type: Object, default: null },
});

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
};
const tm = (t) => TYPE_META[t] || { label: t, color: "#64748b", bg: "#f1f5f9" };

const scoreColor = (s) =>
    s >= 80 ? "#16a34a" : s >= 60 ? "#ca8a04" : "#dc2626";
const scoreBg = (s) => (s >= 80 ? "#dcfce7" : s >= 60 ? "#fef9c3" : "#fee2e2");
const scoreLabel = (s) =>
    s >= 80 ? "Hebat! 🎉" : s >= 60 ? "Cukup Baik 👍" : "Perlu Belajar Lagi 💪";

const incorrectByType = computed(() => {
    const groups = {};
    for (const q of props.results.questions_result || []) {
        if (!q.is_correct) {
            if (!groups[q.quiz_type]) groups[q.quiz_type] = [];
            groups[q.quiz_type].push(q);
        }
    }
    return groups;
});

const hasIncorrect = computed(() =>
    (props.results.questions_result || []).some((q) => !q.is_correct),
);

const goToMissions = () =>
    router.visit(route("playground.missions.index", props.module?.id));
const goToPosttest = () =>
    router.visit(route("playground.posttest.show", props.module?.id));
const goToNextMission = () =>
    router.visit(route("playground.missions.show", props.next_mission.id));
</script>

<template>
    <div class="result-page">
        <div class="bg-blob blob-1"></div>
        <div class="bg-blob blob-2"></div>

        <!-- ── TOPBAR ── -->
        <header class="topbar">
            <button class="back-btn" @click="goToMissions">
                <ArrowLeft :size="16" :stroke-width="2.5" />
                Daftar Misi
            </button>
            <span class="topbar-title">Hasil Misi</span>
            <div class="topbar-spacer"></div>
        </header>

        <main class="main">
            <!-- ── SCORE HERO ── -->
            <section class="score-hero">
                <div
                    class="score-ring"
                    :style="{
                        '--sc': scoreColor(results.score),
                        '--sb': scoreBg(results.score),
                    }"
                >
                    <svg class="ring-svg" viewBox="0 0 120 120">
                        <circle cx="60" cy="60" r="52" class="ring-track" />
                        <circle
                            cx="60"
                            cy="60"
                            r="52"
                            class="ring-progress"
                            :style="{
                                strokeDashoffset:
                                    327 - (327 * results.score) / 100,
                                stroke: scoreColor(results.score),
                            }"
                        />
                    </svg>
                    <div class="score-center">
                        <span
                            class="score-num"
                            :style="{ color: scoreColor(results.score) }"
                            >{{ results.score }}</span
                        >
                        <span class="score-pct">%</span>
                    </div>
                </div>

                <h1 class="score-mission">{{ mission.name }}</h1>
                <p
                    class="score-verdict"
                    :style="{ color: scoreColor(results.score) }"
                >
                    {{ scoreLabel(results.score) }}
                </p>

                <div class="score-chips">
                    <div class="sc-chip sc-correct">
                        <CheckCircle2 :size="15" :stroke-width="2.3" />
                        <strong>{{ results.correct }}</strong> Benar
                    </div>
                    <div class="sc-chip sc-incorrect">
                        <XCircle :size="15" :stroke-width="2.3" />
                        <strong>{{ results.incorrect }}</strong> Salah
                    </div>
                    <div class="sc-chip sc-total">
                        <ClipboardList :size="15" :stroke-width="2.3" />
                        <strong>{{ results.total }}</strong> Total
                    </div>
                </div>
            </section>

            <!-- ── BREAKDOWN ── -->
            <section class="breakdown-section" v-if="results.breakdown?.length">
                <h2 class="section-title">Rincian per Tipe Soal</h2>
                <div class="breakdown-grid">
                    <div
                        v-for="item in results.breakdown"
                        :key="item.type"
                        class="bk-card"
                        :style="{
                            '--bc': tm(item.type).color,
                            '--bb': tm(item.type).bg,
                        }"
                    >
                        <div class="bk-top">
                            <span class="bk-badge">{{
                                tm(item.type).label
                            }}</span>
                            <span
                                class="bk-score"
                                :style="{ color: scoreColor(item.score) }"
                                >{{ item.score }}%</span
                            >
                        </div>
                        <div class="bk-bar">
                            <div
                                class="bk-fill"
                                :style="{
                                    width: item.score + '%',
                                    background: tm(item.type).color,
                                }"
                            ></div>
                        </div>
                        <div class="bk-stats">
                            <span class="bk-c">✓ {{ item.correct }} benar</span>
                            <span class="bk-dot">·</span>
                            <span class="bk-w"
                                >✗ {{ item.incorrect }} salah</span
                            >
                            <span class="bk-dot">·</span>
                            <span class="bk-t">{{ item.total }} soal</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ── INCORRECT QUESTIONS REVIEW ── -->
            <section class="review-section" v-if="hasIncorrect">
                <h2 class="section-title">
                    <XCircle :size="18" color="#ef4444" :stroke-width="2.3" />
                    Soal yang Salah
                </h2>

                <div
                    v-for="(questions, type) in incorrectByType"
                    :key="type"
                    class="review-type-group"
                >
                    <div
                        class="review-type-header"
                        :style="{
                            background: tm(type).bg,
                            color: tm(type).color,
                        }"
                    >
                        <span class="rtb-badge">{{ tm(type).label }}</span>
                        <span class="rtb-count"
                            >{{ questions.length }} soal salah</span
                        >
                    </div>

                    <div class="review-questions">
                        <div
                            v-for="(q, idx) in questions"
                            :key="q.question_id"
                            class="review-q"
                        >
                            <div class="rq-header">
                                <div class="rq-num">{{ idx + 1 }}</div>
                                <p class="rq-text">{{ q.question_text }}</p>
                            </div>

                            <template v-if="type === 'drag_drop'">
                                <div class="rq-dd-review">
                                    <div class="rq-answer-row wrong">
                                        <XCircle
                                            :size="14"
                                            color="#ef4444"
                                            :stroke-width="2.3"
                                        />
                                        <div class="rq-answer-label">
                                            Jawaban Kamu:
                                        </div>
                                        <div class="rq-dd-items">
                                            <span
                                                v-for="(
                                                    groupName, itemLabel
                                                ) in q.user_answer_map"
                                                :key="itemLabel"
                                                class="rq-dd-chip wrong-chip"
                                            >
                                                {{ itemLabel }} →
                                                {{ groupName }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="rq-answer-row correct-row">
                                        <CheckCircle2
                                            :size="14"
                                            color="#10b981"
                                            :stroke-width="2.3"
                                        />
                                        <div class="rq-answer-label">
                                            Jawaban Benar:
                                        </div>
                                        <div class="rq-dd-items">
                                            <span
                                                v-for="(
                                                    groupName, itemLabel
                                                ) in q.correct_answer_map"
                                                :key="itemLabel"
                                                class="rq-dd-chip correct-chip"
                                            >
                                                {{ itemLabel }} →
                                                {{ groupName }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-else>
                                <div class="rq-answers">
                                    <div
                                        class="rq-answer-row wrong"
                                        v-if="q.user_answer_text"
                                    >
                                        <XCircle
                                            :size="14"
                                            color="#ef4444"
                                            :stroke-width="2.3"
                                        />
                                        <span class="rq-answer-label"
                                            >Jawaban Kamu:</span
                                        >
                                        <span class="rq-answer-val wrong-val">{{
                                            q.user_answer_text
                                        }}</span>
                                    </div>
                                    <div class="rq-answer-row correct-row">
                                        <CheckCircle2
                                            :size="14"
                                            color="#10b981"
                                            :stroke-width="2.3"
                                        />
                                        <span class="rq-answer-label"
                                            >Jawaban Benar:</span
                                        >
                                        <span
                                            class="rq-answer-val correct-val"
                                            >{{ q.correct_answer_text }}</span
                                        >
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </section>

            <!-- All correct state -->
            <section class="all-correct" v-else>
                <div class="ac-icon">🏆</div>
                <p class="ac-text">Semua jawaban benar! Luar biasa!</p>
            </section>

            <!-- ── BOTTOM ACTION ── -->
            <div class="bottom-action">
                <button class="btn-home" @click="goToMissions">
                    <ArrowLeft :size="15" :stroke-width="2.5" />
                    Kembali ke Daftar Misi
                </button>
                <!-- Semua misi selesai → posttest -->
                <button
                    v-if="all_missions_done"
                    class="btn-posttest"
                    @click="goToPosttest"
                >
                    <Rocket :size="15" :stroke-width="2.5" />
                    Lanjut ke Posttest
                </button>

                <!-- Ada misi berikutnya → lanjut misi -->
                <button
                    v-else-if="next_mission"
                    class="btn-next"
                    @click="goToNextMission"
                >
                    <ChevronRight :size="15" :stroke-width="2.5" />
                    Misi Selanjutnya
                </button>

                <!-- Selalu tampil tombol kembali -->
            </div>
        </main>
    </div>
</template>

<style scoped>
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}

/* ══════════════════════════════════
   BASE
══════════════════════════════════ */
.result-page {
    min-height: 100vh;
    font-family: "Nunito", sans-serif;
    background: #f0f4ff;
    position: relative;
    overflow-x: hidden;
    padding-bottom: 60px;
}

.bg-blob {
    position: fixed;
    border-radius: 50%;
    pointer-events: none;
    z-index: 0;
    filter: blur(80px);
    opacity: 0.25;
}
.blob-1 {
    width: 500px;
    height: 500px;
    top: -150px;
    right: -150px;
    background: #a5b4fc;
}
.blob-2 {
    width: 400px;
    height: 400px;
    bottom: -100px;
    left: -100px;
    background: #6ee7b7;
}

/* ══════════════════════════════════
   TOPBAR
══════════════════════════════════ */
.topbar {
    position: sticky;
    top: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.88);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid rgba(29, 78, 216, 0.1);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 14px;
    background: rgba(29, 78, 216, 0.07);
    border: 1.5px solid rgba(29, 78, 216, 0.18);
    border-radius: 10px;
    color: #1d4ed8;
    font-family: "Nunito", sans-serif;
    font-size: 12.5px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.17s;
}
.back-btn:hover {
    background: rgba(29, 78, 216, 0.14);
}
.topbar-title {
    font-family: "Righteous", cursive;
    font-size: 16px;
    color: #1e3a8a;
}
.topbar-spacer {
    width: 80px;
}

/* ══════════════════════════════════
   MAIN
══════════════════════════════════ */
.main {
    position: relative;
    z-index: 1;
    max-width: 700px;
    margin: 0 auto;
    padding: 28px 16px 0;
    display: flex;
    flex-direction: column;
    gap: 28px;
}

/* ── SCORE HERO ── */
.score-hero {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    background: #fff;
    border-radius: 24px;
    padding: 32px 24px;
    box-shadow: 0 6px 30px rgba(29, 78, 216, 0.1);
    border: 1.5px solid rgba(29, 78, 216, 0.1);
}
.score-ring {
    position: relative;
    width: 130px;
    height: 130px;
}
.ring-svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}
.ring-track {
    fill: none;
    stroke: #e2e8f0;
    stroke-width: 8;
}
.ring-progress {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 327;
    transition: stroke-dashoffset 1s ease;
}
.score-center {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1px;
}
.score-num {
    font-family: "Righteous", cursive;
    font-size: 32px;
    line-height: 1;
}
.score-pct {
    font-size: 14px;
    font-weight: 900;
    color: #94a3b8;
    align-self: flex-end;
    margin-bottom: 4px;
}
.score-mission {
    font-family: "Righteous", cursive;
    font-size: 20px;
    color: #1e3a8a;
    text-align: center;
}
.score-verdict {
    font-size: 14px;
    font-weight: 800;
}
.score-chips {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: center;
}
.sc-chip {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 12.5px;
    font-weight: 800;
}
.sc-correct {
    background: #dcfce7;
    color: #15803d;
}
.sc-incorrect {
    background: #fee2e2;
    color: #dc2626;
}
.sc-total {
    background: #f1f5f9;
    color: #475569;
}

/* ── SECTION TITLE ── */
.section-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-family: "Righteous", cursive;
    font-size: 16px;
    color: #1e3a8a;
    margin-bottom: 12px;
}

/* ── BREAKDOWN ── */
.breakdown-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 10px;
}
.bk-card {
    background: var(--bb);
    border: 1.5px solid color-mix(in srgb, var(--bc) 25%, transparent);
    border-radius: 14px;
    padding: 13px 14px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}
.bk-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.bk-badge {
    font-size: 10.5px;
    font-weight: 900;
    color: var(--bc);
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.bk-score {
    font-family: "Righteous", cursive;
    font-size: 18px;
}
.bk-bar {
    height: 5px;
    background: rgba(0, 0, 0, 0.08);
    border-radius: 10px;
    overflow: hidden;
}
.bk-fill {
    height: 100%;
    border-radius: 10px;
    transition: width 0.6s ease;
}
.bk-stats {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 11px;
    font-weight: 700;
}
.bk-c {
    color: #16a34a;
}
.bk-w {
    color: #dc2626;
}
.bk-t {
    color: #64748b;
}
.bk-dot {
    color: #cbd5e1;
}

/* ── REVIEW ── */
.review-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.review-type-group {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    border: 1.5px solid rgba(0, 0, 0, 0.07);
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
}
.review-type-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
}
.rtb-badge {
    font-size: 11px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.rtb-count {
    font-size: 11px;
    font-weight: 800;
    opacity: 0.8;
}
.review-questions {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.review-q {
    background: #fafbfc;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    padding: 13px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.rq-header {
    display: flex;
    align-items: flex-start;
    gap: 9px;
}
.rq-num {
    min-width: 22px;
    height: 22px;
    background: #ef4444;
    color: #fff;
    border-radius: 50%;
    font-size: 10.5px;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.rq-text {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.5;
    flex: 1;
}
.rq-answers {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.rq-answer-row {
    display: flex;
    align-items: flex-start;
    gap: 7px;
    padding: 8px 11px;
    border-radius: 8px;
    font-size: 12.5px;
}
.wrong {
    background: #fef2f2;
    border: 1px solid #fecaca;
}
.correct-row {
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
}
.rq-answer-label {
    font-weight: 800;
    color: #475569;
    white-space: nowrap;
    flex-shrink: 0;
    font-size: 12px;
}
.rq-answer-val {
    font-weight: 700;
    flex: 1;
}
.wrong-val {
    color: #dc2626;
}
.correct-val {
    color: #16a34a;
}
.rq-dd-review {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.rq-dd-items {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    flex: 1;
}
.rq-dd-chip {
    font-size: 11px;
    font-weight: 700;
    border-radius: 6px;
    padding: 3px 9px;
}
.wrong-chip {
    background: #fee2e2;
    color: #dc2626;
}
.correct-chip {
    background: #dcfce7;
    color: #15803d;
}

/* ── ALL CORRECT ── */
.all-correct {
    background: #fff;
    border-radius: 18px;
    padding: 40px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    border: 1.5px solid rgba(16, 185, 129, 0.2);
    box-shadow: 0 4px 20px rgba(16, 185, 129, 0.08);
}
.ac-icon {
    font-size: 48px;
}
.ac-text {
    font-family: "Righteous", cursive;
    font-size: 18px;
    color: #065f46;
}

/* ── BOTTOM ACTION ── */
.bottom-action {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-home {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 32px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-family: "Righteous", cursive;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(29, 78, 216, 0.3);
    transition: all 0.17s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn-home:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(29, 78, 216, 0.4);
}

.btn-next {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 32px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-family: "Righteous", cursive;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    transition: all 0.17s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn-next:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(245, 158, 11, 0.48);
}

.btn-posttest {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 13px 32px;
    background: linear-gradient(135deg, #10b981, #059669);
    color: #fff;
    border: none;
    border-radius: 14px;
    font-family: "Righteous", cursive;
    font-size: 14px;
    cursor: pointer;
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    transition: all 0.17s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.btn-posttest:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(16, 185, 129, 0.48);
}

@media (max-width: 480px) {
    .breakdown-grid {
        grid-template-columns: 1fr;
    }
    .score-hero {
        padding: 24px 16px;
    }
    .topbar-spacer {
        width: 60px;
    }
    .bottom-action {
        flex-direction: column;
        align-items: stretch;
    }
    .btn-home,
    .btn-next,
    .btn-posttest {
        justify-content: center;
    }
}
</style>
