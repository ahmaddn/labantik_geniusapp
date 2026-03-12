<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    CheckCircle2, XCircle, ArrowLeft, Trophy, ClipboardList,
    ChevronRight, Rocket, Zap, Target, BarChart3, BookOpen,
    Clock, Medal, Flame, Star, Sparkles, TrendingUp,
    AlertCircle, ChevronDown, ChevronUp, LayoutGrid,
    MousePointerClick, MoveHorizontal, ToggleLeft,
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
    multiple_choices: { label: "Pilihan Ganda", color: "#3b82f6", bg: "#dbeafe", icon: LayoutGrid },
    true_false: { label: "Benar / Salah", color: "#8b5cf6", bg: "#ede9fe", icon: ToggleLeft },
    case_study: { label: "Studi Kasus", color: "#0891b2", bg: "#cffafe", icon: BookOpen },
    drag_drop: { label: "Seret & Letakkan", color: "#f59e0b", bg: "#fef3c7", icon: MoveHorizontal },
};
const tm = (t) => TYPE_META[t] || { label: t, color: "#64748b", bg: "#f1f5f9", icon: ClipboardList };

const scoreColor = (s) => s >= 80 ? "#10b981" : s >= 60 ? "#f59e0b" : "#ef4444";
const scoreGrad = (s) => s >= 80
    ? "linear-gradient(135deg, #10b981, #059669)"
    : s >= 60
    ? "linear-gradient(135deg, #f59e0b, #d97706)"
    : "linear-gradient(135deg, #ef4444, #dc2626)";
const scoreBg = (s) => s >= 80 ? "#ecfdf5" : s >= 60 ? "#fffbeb" : "#fef2f2";
const scoreLabel = (s) => s >= 80 ? "Luar Biasa!" : s >= 60 ? "Cukup Baik" : "Tetap Semangat";
const scoreEmoji = (s) => s >= 80 ? "🏆" : s >= 60 ? "👍" : "💪";
const scoreTier = (s) => s >= 80 ? "gold" : s >= 60 ? "silver" : "bronze";

// Animasi score counter
const displayScore = ref(0);
const animStarted = ref(false);
const expanded = ref({});

const toggleExpand = (key) => {
    expanded.value[key] = !expanded.value[key];
};

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
    (props.results.questions_result || []).some((q) => !q.is_correct)
);

const correctPct = computed(() =>
    props.results.total > 0 ? Math.round((props.results.correct / props.results.total) * 100) : 0
);

onMounted(() => {
    setTimeout(() => {
        animStarted.value = true;
        const target = props.results.score;
        const duration = 1200;
        const step = 16;
        const increment = target / (duration / step);
        let current = 0;
        const timer = setInterval(() => {
            current = Math.min(current + increment, target);
            displayScore.value = Math.round(current);
            if (current >= target) clearInterval(timer);
        }, step);
    }, 300);
});

const goToMissions = () => router.visit(route("playground.missions.index", props.module?.id));
const goToPosttest = () => router.visit(route("playground.posttest.show", props.module?.id));
const goToNextMission = () => router.visit(route("playground.missions.show", props.next_mission.id));
</script>

<template>
    <div class="rp">
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet" />

        <!-- BG -->
        <div class="rp-bg">
            <div class="rp-bg-dot"></div>
            <div class="blob bl1"></div>
            <div class="blob bl2"></div>
            <div class="blob bl3"></div>
        </div>

        <!-- TOPBAR -->
        <header class="topbar">
            <button class="back-btn" @click="goToMissions">
                <ArrowLeft :size="15" :stroke-width="2.5" />
                <span>Daftar Misi</span>
            </button>
            <div class="topbar-center">
                <Target :size="15" color="#2563eb" :stroke-width="2.5" />
                <span>Hasil Misi</span>
            </div>
            <div style="width: 90px"></div>
        </header>

        <main class="main" :class="{ 'main--in': animStarted }">

            <!-- ══ HERO ══ -->
            <section class="hero" :class="`hero--${scoreTier(results.score)}`">

                <!-- confetti dots saat skor tinggi -->
                <div class="hero-confetti" v-if="results.score >= 80" aria-hidden="true">
                    <span v-for="n in 12" :key="n" :class="`conf conf-${n}`"></span>
                </div>

                <div class="hero-inner">
                    <!-- Left: ring -->
                    <div class="hero-ring-wrap">
                        <div class="hero-ring" :class="`ring--${scoreTier(results.score)}`">
                            <svg class="ring-svg" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="50" class="ring-track" />
                                <circle cx="60" cy="60" r="50" class="ring-prog"
                                    :style="{
                                        strokeDashoffset: animStarted ? (314 - (314 * results.score) / 100) : 314,
                                        stroke: scoreColor(results.score)
                                    }" />
                            </svg>
                            <div class="ring-inner">
                                <span class="ring-num" :style="{ color: scoreColor(results.score) }">{{ displayScore }}</span>
                                <span class="ring-pct">%</span>
                            </div>
                        </div>
                        <div class="ring-tier-badge" :class="`tier--${scoreTier(results.score)}`">
                            <Medal :size="12" :stroke-width="2.5" />
                            <span>{{ scoreTier(results.score) === 'gold' ? 'Emas' : scoreTier(results.score) === 'silver' ? 'Perak' : 'Perunggu' }}</span>
                        </div>
                    </div>

                    <!-- Right: info -->
                    <div class="hero-info">
                        <div class="hero-module-chip">
                            <Zap :size="11" fill="currentColor" :stroke-width="2" />
                            <span>{{ module.name }}</span>
                        </div>
                        <h1 class="hero-mission">{{ mission.name }}</h1>
                        <div class="hero-verdict" :class="`verdict--${scoreTier(results.score)}`">
                            <span class="verdict-emoji">{{ scoreEmoji(results.score) }}</span>
                            <span class="verdict-text">{{ scoreLabel(results.score) }}</span>
                        </div>

                        <!-- stat pills -->
                        <div class="hero-stats">
                            <div class="hstat hstat--green">
                                <CheckCircle2 :size="14" :stroke-width="2.3" />
                                <span class="hstat-val">{{ results.correct }}</span>
                                <span class="hstat-lbl">Benar</span>
                            </div>
                            <div class="hstat hstat--red">
                                <XCircle :size="14" :stroke-width="2.3" />
                                <span class="hstat-val">{{ results.incorrect }}</span>
                                <span class="hstat-lbl">Salah</span>
                            </div>
                            <div class="hstat hstat--blue">
                                <ClipboardList :size="14" :stroke-width="2.3" />
                                <span class="hstat-val">{{ results.total }}</span>
                                <span class="hstat-lbl">Total</span>
                            </div>
                        </div>

                        <!-- accuracy bar -->
                        <div class="acc-bar-wrap">
                            <div class="acc-bar-label">
                                <span>Akurasi</span>
                                <span class="acc-pct" :style="{ color: scoreColor(results.score) }">{{ correctPct }}%</span>
                            </div>
                            <div class="acc-track">
                                <div class="acc-fill" :class="`acc--${scoreTier(results.score)}`"
                                    :style="{ width: animStarted ? correctPct + '%' : '0%' }">
                                    <span class="acc-shine"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══ BREAKDOWN ══ -->
            <section class="card section-card" v-if="results.breakdown?.length">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-icon card-icon--blue">
                            <BarChart3 :size="15" :stroke-width="2.3" />
                        </div>
                        <h2 class="card-title">Rincian per Tipe</h2>
                    </div>
                    <span class="card-badge">{{ results.breakdown.length }} tipe</span>
                </div>
                <div class="bk-list">
                    <div v-for="(item, i) in results.breakdown" :key="item.type"
                        class="bk-row" :style="{ '--bc': tm(item.type).color, '--bb': tm(item.type).bg, animationDelay: (i * 0.08) + 's' }">
                        <div class="bk-row-icon" :style="{ background: tm(item.type).bg, color: tm(item.type).color }">
                            <component :is="tm(item.type).icon" :size="14" :stroke-width="2.2" />
                        </div>
                        <div class="bk-row-body">
                            <div class="bk-row-top">
                                <span class="bk-row-label">{{ tm(item.type).label }}</span>
                                <span class="bk-row-score" :style="{ color: scoreColor(item.score) }">{{ item.score }}%</span>
                            </div>
                            <div class="bk-bar">
                                <div class="bk-fill" :style="{ width: animStarted ? item.score + '%' : '0%', background: tm(item.type).color }"></div>
                            </div>
                            <div class="bk-row-meta">
                                <span class="bk-c"><CheckCircle2 :size="11" :stroke-width="2.5" /> {{ item.correct }} benar</span>
                                <span class="bk-sep">·</span>
                                <span class="bk-w"><XCircle :size="11" :stroke-width="2.5" /> {{ item.incorrect }} salah</span>
                                <span class="bk-sep">·</span>
                                <span class="bk-t">{{ item.total }} soal</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ══ REVIEW SOAL SALAH ══ -->
            <section class="card section-card" v-if="hasIncorrect">
                <div class="card-head">
                    <div class="card-head-left">
                        <div class="card-icon card-icon--red">
                            <AlertCircle :size="15" :stroke-width="2.3" />
                        </div>
                        <h2 class="card-title">Soal yang Salah</h2>
                    </div>
                    <span class="card-badge card-badge--red">
                        {{ (results.questions_result || []).filter(q => !q.is_correct).length }} soal
                    </span>
                </div>

                <div class="review-groups">
                    <div v-for="(questions, type) in incorrectByType" :key="type" class="rg">
                        <!-- Type header — clickable to collapse -->
                        <button class="rg-head" :style="{ background: tm(type).bg, color: tm(type).color }"
                            @click="toggleExpand(type)">
                            <div class="rg-head-left">
                                <div class="rg-icon" :style="{ background: tm(type).color + '22' }">
                                    <component :is="tm(type).icon" :size="13" :stroke-width="2.3" />
                                </div>
                                <span class="rg-type-label">{{ tm(type).label }}</span>
                                <span class="rg-count-badge">{{ questions.length }}</span>
                            </div>
                            <ChevronDown v-if="!expanded[type]" :size="15" :stroke-width="2.3" class="rg-chev" />
                            <ChevronUp v-else :size="15" :stroke-width="2.3" class="rg-chev" />
                        </button>

                        <!-- Questions list -->
                        <Transition name="slide-down">
                            <div class="rg-questions" v-if="expanded[type]">
                                <div v-for="(q, idx) in questions" :key="q.question_id" class="rq">
                                    <div class="rq-head">
                                        <div class="rq-num">{{ idx + 1 }}</div>
                                        <p class="rq-text">{{ q.question_text }}</p>
                                    </div>

                                    <!-- drag_drop -->
                                    <template v-if="type === 'drag_drop'">
                                        <div class="rq-pair">
                                            <div class="rq-answer wrong-answer">
                                                <div class="rq-ans-head">
                                                    <XCircle :size="13" color="#ef4444" :stroke-width="2.3" />
                                                    <span>Jawaban Kamu</span>
                                                </div>
                                                <div class="rq-chips">
                                                    <span v-for="(gn, il) in q.user_answer_map" :key="il" class="chip chip--wrong">{{ il }} → {{ gn }}</span>
                                                </div>
                                            </div>
                                            <div class="rq-arrow">→</div>
                                            <div class="rq-answer correct-answer">
                                                <div class="rq-ans-head">
                                                    <CheckCircle2 :size="13" color="#10b981" :stroke-width="2.3" />
                                                    <span>Jawaban Benar</span>
                                                </div>
                                                <div class="rq-chips">
                                                    <span v-for="(gn, il) in q.correct_answer_map" :key="il" class="chip chip--correct">{{ il }} → {{ gn }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- others -->
                                    <template v-else>
                                        <div class="rq-pair">
                                            <div class="rq-answer wrong-answer" v-if="q.user_answer_text">
                                                <div class="rq-ans-head">
                                                    <XCircle :size="13" color="#ef4444" :stroke-width="2.3" />
                                                    <span>Jawaban Kamu</span>
                                                </div>
                                                <p class="rq-ans-val rq-ans-wrong">{{ q.user_answer_text }}</p>
                                            </div>
                                            <div class="rq-arrow" v-if="q.user_answer_text">→</div>
                                            <div class="rq-answer correct-answer">
                                                <div class="rq-ans-head">
                                                    <CheckCircle2 :size="13" color="#10b981" :stroke-width="2.3" />
                                                    <span>Jawaban Benar</span>
                                                </div>
                                                <p class="rq-ans-val rq-ans-correct">{{ q.correct_answer_text }}</p>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </section>

            <!-- ══ ALL CORRECT ══ -->
            <section class="all-correct" v-else>
                <div class="ac-glow"></div>
                <div class="ac-icon-wrap">
                    <Trophy :size="42" color="#f59e0b" :stroke-width="1.5" />
                    <span class="ac-spark sp1"><Sparkles :size="13" color="#2563eb" /></span>
                    <span class="ac-spark sp2"><Star :size="11" color="#f59e0b" fill="#f59e0b" /></span>
                    <span class="ac-spark sp3"><Sparkles :size="10" color="#10b981" /></span>
                </div>
                <h3 class="ac-title">Sempurna!</h3>
                <p class="ac-sub">Semua jawaban benar. Kamu luar biasa!</p>
            </section>

            <!-- ══ FOOTER ACTIONS ══ -->
            <div class="actions">
                <button class="act-btn act-btn--ghost" @click="goToMissions">
                    <ArrowLeft :size="15" :stroke-width="2.5" />
                    <span>Daftar Misi</span>
                </button>
                <button v-if="all_missions_done" class="act-btn act-btn--mint" @click="goToPosttest">
                    <Rocket :size="15" :stroke-width="2.3" />
                    <span>Lanjut Posttest</span>
                    <ChevronRight :size="14" :stroke-width="2.5" />
                </button>
                <button v-else-if="next_mission" class="act-btn act-btn--yellow" @click="goToNextMission">
                    <Flame :size="15" :stroke-width="2.3" />
                    <span>Misi Selanjutnya</span>
                    <ChevronRight :size="14" :stroke-width="2.5" />
                </button>
            </div>

        </main>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.rp {
    min-height: 100vh;
    font-family: "Nunito", sans-serif;
    background: #eef2ff;
    position: relative;
    overflow-x: hidden;
    padding-bottom: 80px;
}

/* ── BG ── */
.rp-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
.rp-bg-dot {
    position: absolute; inset: 0;
    background-image: radial-gradient(circle, rgba(99,102,241,0.07) 1px, transparent 1px);
    background-size: 28px 28px;
}
.blob { position: absolute; border-radius: 50%; filter: blur(90px); pointer-events: none; }
.bl1 { width: 560px; height: 560px; top: -160px; right: -120px; background: #a5b4fc; opacity: 0.3; animation: bDrift 22s ease-in-out infinite alternate; }
.bl2 { width: 420px; height: 420px; bottom: -100px; left: -80px; background: #6ee7b7; opacity: 0.25; animation: bDrift 26s ease-in-out 5s infinite alternate-reverse; }
.bl3 { width: 280px; height: 280px; top: 40%; left: 40%; background: #fde68a; opacity: 0.18; animation: bDrift 18s ease-in-out 2s infinite alternate; }
@keyframes bDrift { 0% { transform: translate(0,0) scale(1); } 100% { transform: translate(30px,40px) scale(1.06); } }

/* ── TOPBAR ── */
.topbar {
    position: sticky; top: 0; z-index: 50;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 20px; height: 56px;
    background: rgba(255,255,255,0.85); backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(99,102,241,0.12);
    box-shadow: 0 2px 16px rgba(0,0,0,0.05);
}
.back-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; background: rgba(37,99,235,0.07);
    border: 1.5px solid rgba(37,99,235,0.18); border-radius: 10px;
    color: #1d4ed8; font-family: "Nunito", sans-serif;
    font-size: 12.5px; font-weight: 800; cursor: pointer;
    transition: all 0.16s;
}
.back-btn:hover { background: rgba(37,99,235,0.13); transform: translateY(-1px); }
.topbar-center {
    display: flex; align-items: center; gap: 6px;
    font-family: "Righteous", cursive; font-size: 16px; color: #1e3a8a;
}

/* ── MAIN ── */
.main {
    position: relative; z-index: 1;
    max-width: 720px; margin: 0 auto;
    padding: 24px 16px 0;
    display: flex; flex-direction: column; gap: 20px;
    opacity: 0; transform: translateY(18px);
    transition: opacity 0.5s, transform 0.5s cubic-bezier(0.34,1.56,0.64,1);
}
.main--in { opacity: 1; transform: none; }

/* ══ HERO ══ */
.hero {
    border-radius: 24px; overflow: hidden; position: relative;
    border: 1.5px solid rgba(255,255,255,0.6);
    box-shadow: 0 8px 40px rgba(37,99,235,0.12);
    animation: heroIn 0.6s cubic-bezier(0.34,1.56,0.64,1) 0.1s both;
}
@keyframes heroIn { from { opacity: 0; transform: translateY(24px) scale(0.97); } to { opacity: 1; transform: none; } }

.hero--gold { background: linear-gradient(145deg, #fffbeb 0%, #fef3c7 40%, #ecfdf5 100%); }
.hero--silver { background: linear-gradient(145deg, #fffbeb 0%, #fef9c3 40%, #fff 100%); }
.hero--bronze { background: linear-gradient(145deg, #fff1f2 0%, #ffe4e6 40%, #fff 100%); }

.hero-confetti { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.conf { position: absolute; border-radius: 2px; animation: confFall linear infinite; }
.conf-1  { width:7px;height:7px;background:#f59e0b;top:-8px;left:5%;animation-duration:3.2s;animation-delay:.1s; }
.conf-2  { width:5px;height:5px;background:#3b82f6;top:-8px;left:15%;animation-duration:4s;animation-delay:.5s; }
.conf-3  { width:6px;height:6px;background:#ef4444;top:-8px;left:27%;animation-duration:3.5s;animation-delay:.2s; }
.conf-4  { width:5px;height:5px;background:#10b981;top:-8px;left:40%;animation-duration:4.3s;animation-delay:.8s; }
.conf-5  { width:8px;height:8px;background:#a78bfa;top:-8px;left:55%;animation-duration:3.8s;animation-delay:.4s; }
.conf-6  { width:5px;height:5px;background:#f59e0b;top:-8px;left:68%;animation-duration:4.1s;animation-delay:1s; }
.conf-7  { width:6px;height:6px;background:#ef4444;top:-8px;left:79%;animation-duration:3.4s;animation-delay:.6s; }
.conf-8  { width:4px;height:4px;background:#3b82f6;top:-8px;left:88%;animation-duration:3.9s;animation-delay:1.2s; }
.conf-9  { width:7px;height:7px;background:#10b981;top:-8px;left:22%;animation-duration:4.5s;animation-delay:.3s; }
.conf-10 { width:5px;height:5px;background:#a78bfa;top:-8px;left:48%;animation-duration:3.7s;animation-delay:.9s; }
.conf-11 { width:6px;height:6px;background:#f59e0b;top:-8px;left:62%;animation-duration:4.2s;animation-delay:1.4s; }
.conf-12 { width:4px;height:4px;background:#ef4444;top:-8px;left:92%;animation-duration:3.3s;animation-delay:.7s; }
@keyframes confFall { 0%{transform:translateY(0) rotate(0);opacity:1} 100%{transform:translateY(350px) rotate(360deg);opacity:0} }

.hero-inner {
    position: relative; z-index: 1;
    display: flex; align-items: center; gap: 24px;
    padding: 28px 24px;
    flex-wrap: wrap;
}

/* Ring */
.hero-ring-wrap { display: flex; flex-direction: column; align-items: center; gap: 8px; flex-shrink: 0; }
.hero-ring { position: relative; width: 128px; height: 128px; }
.ring-svg { width: 100%; height: 100%; transform: rotate(-90deg); }
.ring-track { fill: none; stroke: rgba(0,0,0,0.08); stroke-width: 8; }
.ring-prog {
    fill: none; stroke-width: 8; stroke-linecap: round;
    stroke-dasharray: 314;
    transition: stroke-dashoffset 1.4s cubic-bezier(0.34,1.56,0.64,1) 0.3s;
}
.ring-inner {
    position: absolute; inset: 0;
    display: flex; align-items: center; justify-content: center; gap: 1px;
}
.ring-num { font-family: "Righteous", cursive; font-size: 34px; line-height: 1; }
.ring-pct { font-size: 14px; font-weight: 900; color: #94a3b8; align-self: flex-end; margin-bottom: 5px; }

.ring-tier-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 12px; border-radius: 99px;
    font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px;
}
.tier--gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #fff; box-shadow: 0 2px 8px rgba(245,158,11,0.4); }
.tier--silver { background: linear-gradient(135deg, #94a3b8, #64748b); color: #fff; box-shadow: 0 2px 8px rgba(100,116,139,0.3); }
.tier--bronze { background: linear-gradient(135deg, #fb923c, #ea580c); color: #fff; box-shadow: 0 2px 8px rgba(234,88,12,0.35); }

/* Hero info */
.hero-info { flex: 1; min-width: 220px; display: flex; flex-direction: column; gap: 10px; }
.hero-module-chip {
    display: inline-flex; align-items: center; gap: 5px; width: fit-content;
    padding: 4px 12px; border-radius: 99px;
    background: rgba(37,99,235,0.1); border: 1px solid rgba(37,99,235,0.2);
    font-size: 11px; font-weight: 900; color: #1d4ed8;
}
.hero-mission {
    font-family: "Righteous", cursive;
    font-size: clamp(17px, 2.5vw, 22px);
    color: #1e3a8a; line-height: 1.25;
}
.hero-verdict {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 6px 14px; border-radius: 12px; width: fit-content;
    font-size: 13px; font-weight: 900;
}
.verdict--gold   { background: #fef3c7; color: #92400e; border: 1.5px solid #fde68a; }
.verdict--silver { background: #fef9c3; color: #854d0e; border: 1.5px solid #fef08a; }
.verdict--bronze { background: #fee2e2; color: #991b1b; border: 1.5px solid #fecaca; }
.verdict-emoji { font-size: 16px; }

/* Hstats */
.hero-stats { display: flex; gap: 8px; flex-wrap: wrap; }
.hstat {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 11px; border-radius: 10px;
    font-size: 12px; font-weight: 800;
}
.hstat-val { font-family: "Righteous", cursive; font-size: 15px; }
.hstat-lbl { opacity: 0.75; font-size: 11px; }
.hstat--green { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.hstat--red   { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
.hstat--blue  { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }

/* Accuracy bar */
.acc-bar-wrap { display: flex; flex-direction: column; gap: 5px; }
.acc-bar-label { display: flex; justify-content: space-between; font-size: 11px; font-weight: 800; color: #64748b; }
.acc-pct { font-family: "Righteous", cursive; font-size: 13px; }
.acc-track { height: 7px; background: rgba(0,0,0,0.08); border-radius: 99px; overflow: hidden; }
.acc-fill {
    height: 100%; border-radius: 99px;
    position: relative; overflow: hidden;
    transition: width 1.4s cubic-bezier(0.34,1.56,0.64,1) 0.4s;
}
.acc--gold   { background: linear-gradient(90deg, #10b981, #059669); }
.acc--silver { background: linear-gradient(90deg, #f59e0b, #d97706); }
.acc--bronze { background: linear-gradient(90deg, #ef4444, #dc2626); }
.acc-shine {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shine 2.2s ease-in-out infinite;
}
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }

/* ══ CARD (breakdown + review) ══ */
.card {
    background: #fff; border-radius: 20px;
    border: 1.5px solid rgba(99,102,241,0.1);
    box-shadow: 0 4px 24px rgba(37,99,235,0.07);
    overflow: hidden;
    animation: cardIn 0.5s ease both;
}
@keyframes cardIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
.section-card:nth-child(2) { animation-delay: 0.12s; }
.section-card:nth-child(3) { animation-delay: 0.22s; }

.card-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 16px 18px;
    border-bottom: 1.5px solid rgba(0,0,0,0.05);
    background: #fafbff;
}
.card-head-left { display: flex; align-items: center; gap: 10px; }
.card-icon {
    width: 32px; height: 32px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.card-icon--blue { background: #dbeafe; color: #2563eb; }
.card-icon--red  { background: #fee2e2; color: #dc2626; }
.card-title { font-family: "Righteous", cursive; font-size: 15px; color: #1e3a8a; }
.card-badge {
    display: inline-flex; align-items: center;
    padding: 3px 10px; border-radius: 99px;
    background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;
    font-size: 11px; font-weight: 900;
}
.card-badge--red { background: #fee2e2; color: #dc2626; border-color: #fecaca; }

/* Breakdown list */
.bk-list { padding: 12px 16px; display: flex; flex-direction: column; gap: 10px; }
.bk-row {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 12px; border-radius: 12px;
    background: var(--bb); border: 1.5px solid color-mix(in srgb, var(--bc) 20%, transparent);
    animation: rowIn 0.4s ease both;
}
@keyframes rowIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: none; } }
.bk-row-icon {
    width: 34px; height: 34px; border-radius: 9px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.bk-row-body { flex: 1; display: flex; flex-direction: column; gap: 6px; min-width: 0; }
.bk-row-top { display: flex; align-items: center; justify-content: space-between; }
.bk-row-label { font-size: 12px; font-weight: 900; color: var(--bc); text-transform: uppercase; letter-spacing: 0.3px; }
.bk-row-score { font-family: "Righteous", cursive; font-size: 17px; }
.bk-bar { height: 5px; background: rgba(0,0,0,0.09); border-radius: 99px; overflow: hidden; }
.bk-fill { height: 100%; border-radius: 99px; transition: width 1.2s cubic-bezier(0.34,1.56,0.64,1) 0.5s; }
.bk-row-meta { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 700; flex-wrap: wrap; }
.bk-c { display: flex; align-items: center; gap: 3px; color: #15803d; }
.bk-w { display: flex; align-items: center; gap: 3px; color: #dc2626; }
.bk-t { color: #64748b; }
.bk-sep { color: #cbd5e1; }

/* Review groups */
.review-groups { display: flex; flex-direction: column; gap: 0; }
.rg { border-bottom: 1px solid rgba(0,0,0,0.05); }
.rg:last-child { border-bottom: none; }

.rg-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 18px; width: 100%; border: none; cursor: pointer;
    transition: opacity 0.15s;
}
.rg-head:hover { opacity: 0.85; }
.rg-head-left { display: flex; align-items: center; gap: 9px; }
.rg-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; }
.rg-type-label { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.3px; }
.rg-count-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 20px; height: 20px; border-radius: 50%;
    background: currentColor; opacity: 1; font-size: 10px; font-weight: 900;
    color: #fff; filter: brightness(0.8) saturate(1.2);
}
.rg-chev { flex-shrink: 0; }

.rg-questions { padding: 10px 14px 14px; display: flex; flex-direction: column; gap: 10px; background: #fafbff; }

.rq {
    background: #fff; border: 1.5px solid #e2e8f0;
    border-radius: 14px; padding: 14px;
    display: flex; flex-direction: column; gap: 11px;
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
}
.rq-head { display: flex; align-items: flex-start; gap: 10px; }
.rq-num {
    min-width: 24px; height: 24px; background: #ef4444; color: #fff;
    border-radius: 50%; font-size: 11px; font-weight: 900;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.rq-text { font-size: 13px; font-weight: 700; color: #1e293b; line-height: 1.55; flex: 1; }

.rq-pair {
    display: flex; align-items: flex-start; gap: 8px; flex-wrap: wrap;
}
.rq-answer { flex: 1; min-width: 120px; border-radius: 10px; padding: 10px 12px; display: flex; flex-direction: column; gap: 6px; }
.wrong-answer { background: #fef2f2; border: 1.5px solid #fecaca; }
.correct-answer { background: #f0fdf4; border: 1.5px solid #bbf7d0; }
.rq-ans-head { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 900; color: #475569; }
.rq-ans-val { font-size: 12.5px; font-weight: 700; line-height: 1.45; }
.rq-ans-wrong { color: #dc2626; }
.rq-ans-correct { color: #15803d; }
.rq-arrow { align-self: center; font-size: 16px; color: #94a3b8; flex-shrink: 0; margin-top: 18px; }
.rq-chips { display: flex; flex-wrap: wrap; gap: 4px; }
.chip { font-size: 11px; font-weight: 700; padding: 3px 9px; border-radius: 6px; }
.chip--wrong { background: #fee2e2; color: #dc2626; }
.chip--correct { background: #dcfce7; color: #15803d; }

/* Transition */
.slide-down-enter-active { transition: all 0.28s cubic-bezier(0.34,1.56,0.64,1); }
.slide-down-leave-active { transition: all 0.18s ease; }
.slide-down-enter-from { opacity: 0; transform: translateY(-8px); }
.slide-down-leave-to { opacity: 0; transform: translateY(-4px); }

/* All correct */
.all-correct {
    background: #fff; border-radius: 20px; padding: 48px 24px;
    text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px;
    border: 1.5px solid rgba(16,185,129,0.2); position: relative; overflow: hidden;
    box-shadow: 0 4px 24px rgba(16,185,129,0.1);
    animation: cardIn 0.5s ease 0.15s both;
}
.ac-glow {
    position: absolute; width: 200px; height: 200px;
    background: radial-gradient(circle, rgba(52,211,153,0.2) 0%, transparent 70%);
    top: 50%; left: 50%; transform: translate(-50%,-50%);
    pointer-events: none;
}
.ac-icon-wrap { position: relative; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; }
.ac-spark { position: absolute; animation: spFloat 2.2s ease-in-out infinite; }
.sp1 { top: -4px; right: -2px; }
.sp2 { bottom: 0; left: -4px; animation-delay: 0.6s; }
.sp3 { top: 6px; left: -6px; animation-delay: 1.2s; }
@keyframes spFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px) rotate(10deg)} }
.ac-title { font-family: "Righteous", cursive; font-size: 24px; color: #065f46; }
.ac-sub { font-size: 13.5px; font-weight: 700; color: #6b7280; }

/* Actions */
.actions {
    display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;
    padding-bottom: 8px;
    animation: cardIn 0.5s ease 0.3s both;
}
.act-btn {
    display: inline-flex; align-items: center; gap: 7px;
    height: 46px; padding: 0 22px; border: none; border-radius: 13px;
    font-family: "Nunito", sans-serif; font-size: 13.5px; font-weight: 800;
    cursor: pointer; transition: transform 0.15s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.15s;
    white-space: nowrap;
}
.act-btn:hover { transform: translateY(-2px); }
.act-btn--ghost {
    background: rgba(37,99,235,0.08);
    border: 1.5px solid rgba(37,99,235,0.2);
    color: #1d4ed8;
}
.act-btn--ghost:hover { background: rgba(37,99,235,0.14); box-shadow: 0 4px 14px rgba(37,99,235,0.2); }
.act-btn--mint {
    background: linear-gradient(135deg, #10b981, #059669); color: #fff;
    box-shadow: 0 4px 14px rgba(16,185,129,0.35);
}
.act-btn--mint:hover { box-shadow: 0 6px 20px rgba(16,185,129,0.5); }
.act-btn--yellow {
    background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff;
    box-shadow: 0 4px 14px rgba(245,158,11,0.35);
}
.act-btn--yellow:hover { box-shadow: 0 6px 20px rgba(245,158,11,0.5); }

/* ── MOBILE ── */
@media (max-width: 640px) {
    .topbar { padding: 0 13px; height: 50px; }
    .back-btn span { display: none; }
    .back-btn { padding: 7px 10px; }
    .hero-inner { padding: 20px 16px; gap: 16px; }
    .hero-ring { width: 108px; height: 108px; }
    .ring-num { font-size: 28px; }
    .hero-info { min-width: 160px; }
    .hstat-lbl { display: none; }
    .main { padding: 16px 12px 0; gap: 14px; }
    .card-head { padding: 12px 14px; }
    .bk-list { padding: 10px 12px; }
    .rg-questions { padding: 8px 10px 12px; }
    .rq { padding: 12px; }
    .rq-pair { flex-direction: column; gap: 6px; }
    .rq-arrow { display: none; }
    .actions { flex-direction: column; align-items: stretch; }
    .act-btn { justify-content: center; height: 44px; }
}

@media (max-width: 380px) {
    .hero-inner { flex-direction: column; align-items: center; text-align: center; }
    .hero-module-chip, .hero-verdict, .hero-stats { margin: 0 auto; }
    .acc-bar-wrap { width: 100%; }
}
</style>
