<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    Zap, Clock, Music2, MusicOff, ChevronRight, CheckCircle2,
    AlertCircle, ArrowRight, Target, BookOpen, Star
} from "lucide-vue-next";

// ── Props from Controller ──────────────────────────────────────────
const props = defineProps({
    quiz:       Object,   // { id, title, description, time_limit, type }
    questions:  Array,    // [{ id, question_text, image, order_number, mascot_id, options:[{id,option_text,is_correct,feedback}] }]
    mission:    Object,   // { id, name, order_number }
    mascot:     Object,   // { id, name_pose, image } — nullable
    background: Object,   // { id, image } — nullable
    attempt:    Object,   // existing attempt if any
});

// ── Image helpers ──────────────────────────────────────────────────
const bgStyle = computed(() => {
    const img = props.background?.image
        ? `/storage/${props.background.image}`
        : "/images/templates/background.png";
    return { backgroundImage: `url('${img}')` };
});
const mascotSrc = computed(() =>
    props.mascot?.image
        ? `/storage/${props.mascot.image}`
        : "/images/templates/pose_keren.png"
);

// ── Phase: intro | quiz | done ─────────────────────────────────────
const phase = ref("intro");

// ── Timer ─────────────────────────────────────────────────────────
const timeLimit  = computed(() => (props.quiz?.time_limit ?? 10) * 60); // seconds
const remaining  = ref(timeLimit.value);
let   timerInt   = null;

const timerDisplay = computed(() => {
    const m = String(Math.floor(remaining.value / 60)).padStart(2, "0");
    const s = String(remaining.value % 60).padStart(2, "0");
    return `${m}:${s}`;
});
const timerWarning = computed(() => remaining.value <= 60);

function startTimer() {
    timerInt = setInterval(() => {
        if (remaining.value <= 0) { clearInterval(timerInt); submitQuiz(); return; }
        remaining.value--;
    }, 1000);
}

// ── Music ─────────────────────────────────────────────────────────
const musicOn  = ref(false);
const audioRef = ref(null);

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
        audioRef.value.loop = true; audioRef.value.volume = 0.4;
        audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    }
    if (musicOn.value) { audioRef.value.pause(); musicOn.value = false; }
    else { try { await audioRef.value.play(); musicOn.value = true; } catch { musicOn.value = false; } }
};

const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden ? audioRef.value.pause()
                    : (musicOn.value && audioRef.value.play().catch(() => {}));
};

// ── Mascot Speech Bubble ───────────────────────────────────────────
const INTRO_LINES = [
    "Hei, ayo kita mulai! 🎯",
    "Kamu pasti bisa! 💪",
    "Semangat terus ya! ⭐",
    "Jangan menyerah! 🌟",
    "Bacalah soal baik-baik! 📖",
];
const QUIZ_LINES = [
    "Pikirkan baik-baik! 🤔",
    "Yakin sama jawabanmu? 💡",
    "Kamu hebat! Terusin! ⚡",
    "Hampir selesai! 🏁",
    "Fokus ya, bisa kok! 🎯",
];
const DONE_LINES = [
    "Keren banget kamu! 🏆",
    "Selamat ya! 🎉",
    "Mantap, luar biasa! ⭐",
    "Kamu memang juara! 🥇",
];

const bubbleLines   = computed(() =>
    phase.value === "done" ? DONE_LINES : phase.value === "quiz" ? QUIZ_LINES : INTRO_LINES
);
const bubbleText    = ref(INTRO_LINES[0]);
const bubbleVisible = ref(true);
const bubbleIndex   = ref(0);
let   bubbleTimer   = null;

function startBubble(lines) {
    if (bubbleTimer) clearInterval(bubbleTimer);
    bubbleIndex.value = 0;
    bubbleText.value  = lines[0];
    bubbleVisible.value = true;
    bubbleTimer = setInterval(() => {
        bubbleVisible.value = false;
        setTimeout(() => {
            bubbleIndex.value   = (bubbleIndex.value + 1) % lines.length;
            bubbleText.value    = lines[bubbleIndex.value];
            bubbleVisible.value = true;
        }, 380);
    }, 2800);
}

// ── Quiz state ─────────────────────────────────────────────────────
const currentIdx  = ref(0);
const answers     = ref({}); // { question_id: option_id }
const shakeOption = ref(null);
const submitting  = ref(false);

const currentQuestion = computed(() => props.questions?.[currentIdx.value] ?? null);
const totalQuestions  = computed(() => props.questions?.length ?? 0);
const progress        = computed(() => ((currentIdx.value) / totalQuestions.value) * 100);

function selectOption(optionId) {
    answers.value[currentQuestion.value.id] = optionId;
}

function isSelected(optionId) {
    return answers.value[currentQuestion.value?.id] === optionId;
}

function nextQuestion() {
    if (!answers.value[currentQuestion.value?.id]) {
        shakeOption.value = currentQuestion.value?.id;
        setTimeout(() => (shakeOption.value = null), 600);
        return;
    }
    if (currentIdx.value < totalQuestions.value - 1) {
        currentIdx.value++;
    } else {
        submitQuiz();
    }
}

function startQuiz() {
    phase.value = "quiz";
    startTimer();
    startBubble(QUIZ_LINES);
}

function submitQuiz() {
    if (submitting.value) return;
    submitting.value = true;
    clearInterval(timerInt);
    phase.value = "done";
    startBubble(DONE_LINES);

    // Build payload
    const payload = {
        quiz_id:      props.quiz?.id,
        time_taken:   timeLimit.value - remaining.value,
        answers:      Object.entries(answers.value).map(([question_id, selected_option_id]) => ({
            question_id, selected_option_id
        })),
    };

    router.post(route("playground.pretest.submit"), payload, {
        preserveState: true,
        onError: () => { submitting.value = false; },
    });
}

function goToAdventure() {
    router.visit(route("playground.index"));
}

// ── Lifecycle ──────────────────────────────────────────────────────
onMounted(() => {
    setTimeout(() => startBubble(INTRO_LINES), 800);
    document.addEventListener("visibilitychange", handleVisibility);
});

onUnmounted(() => {
    clearInterval(timerInt);
    clearInterval(bubbleTimer);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) { audioRef.value.pause(); audioRef.value = null; }
});

// option labels
const OPTION_LABELS = ["A", "B", "C", "D", "E"];
</script>

<template>
    <div class="pg" :style="bgStyle">

        <!-- ══ FONTS ══ -->
        <div style="display:none">
            <link rel="preconnect" href="https://fonts.googleapis.com"/>
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
            <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
        </div>

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div class="topbar-inner">
                <!-- Brand -->
                <div class="brand">
                    <div class="brand-ico"><Zap :size="13" color="#fff" fill="white"/></div>
                    <span class="brand-nm">Geniuss</span>
                </div>

                <!-- Centre: progress bar (quiz phase only) -->
                <div v-if="phase === 'quiz'" class="top-progress">
                    <div class="tp-bar">
                        <div class="tp-fill" :style="{ width: progress + '%' }"></div>
                    </div>
                    <span class="tp-label">Soal {{ currentIdx + 1 }} dari {{ totalQuestions }}</span>
                </div>
                <div v-else class="top-title">
                    <Target :size="15" color="#1d4ed8"/>
                    <span>{{ quiz?.title ?? 'Pretest' }}</span>
                </div>

                <!-- Timer -->
                <div class="timer" :class="{ warn: timerWarning }">
                    <Clock :size="15" :stroke-width="2.3"/>
                    <span>{{ timerDisplay }}</span>
                </div>
            </div>
        </header>

        <!-- ══ CONTENT ══ -->
        <main class="main">

            <!-- ────────── INTRO ────────── -->
            <Transition name="slide-up">
            <div v-if="phase === 'intro'" class="panel intro-panel">

                <div class="intro-left">
                    <!-- Badge -->
                    <div class="badge">
                        <Target :size="13" color="#fff"/>
                        <span>Pretest</span>
                    </div>

                    <!-- Title + desc -->
                    <h1 class="intro-title">{{ quiz?.title ?? 'Tantangan Awal' }}</h1>
                    <p class="intro-desc">{{ quiz?.description ?? 'Jawab semua soal berikut dengan sebaik-baiknya!' }}</p>

                    <!-- Meta chips -->
                    <div class="meta-row">
                        <div class="meta-chip">
                            <BookOpen :size="13" color="#1d4ed8"/>
                            <span>{{ totalQuestions }} Soal</span>
                        </div>
                        <div class="meta-chip">
                            <Clock :size="13" color="#1d4ed8"/>
                            <span>{{ quiz?.time_limit ?? 10 }} Menit</span>
                        </div>
                        <div class="meta-chip">
                            <Star :size="13" color="#1d4ed8"/>
                            <span>{{ mission?.name ?? 'Misi' }}</span>
                        </div>
                    </div>

                    <!-- Instruction box -->
                    <div class="instr-box">
                        <h3 class="instr-title">Petunjuk Pengerjaan :</h3>
                        <ul class="instr-list">
                            <li>Baca setiap soal dengan teliti sebelum menjawab.</li>
                            <li>Pilih satu jawaban yang paling tepat untuk setiap soal.</li>
                            <li>Kerjakan dalam waktu yang telah ditentukan.</li>
                            <li>Klik <strong>Berikutnya</strong> setelah memilih jawaban.</li>
                        </ul>
                    </div>

                    <button class="btn-start" @click="startQuiz">
                        <span>Mulai Sekarang</span>
                        <ArrowRight :size="17" :stroke-width="2.5"/>
                    </button>
                </div>

                <!-- Mascot -->
                <div class="mascot-col">
                    <Transition name="bubble">
                        <div v-if="bubbleVisible" class="speech-bubble bubble-right">
                            <span class="bubble-text">{{ bubbleText }}</span>
                        </div>
                    </Transition>
                    <div class="mascot-glow"></div>
                    <img :src="mascotSrc" alt="Maskot" class="mascot-img"/>
                </div>

            </div>
            </Transition>

            <!-- ────────── QUIZ ────────── -->
            <Transition name="slide-up">
            <div v-if="phase === 'quiz' && currentQuestion" class="panel quiz-panel">

                <!-- Question number -->
                <div class="q-header">
                    <span class="q-num">{{ currentIdx + 1 }}.</span>
                </div>

                <!-- Body: text + image | mascot -->
                <div class="q-body">
                    <div class="q-left">
                        <p v-if="currentQuestion.question_text" class="q-text">
                            {{ currentQuestion.question_text }}
                        </p>
                        <img
                            v-if="currentQuestion.image"
                            :src="`/storage/${currentQuestion.image}`"
                            alt="Gambar soal"
                            class="q-img"
                        />
                    </div>
                    <!-- Mascot small -->
                    <div class="q-mascot-wrap">
                        <Transition name="bubble">
                            <div v-if="bubbleVisible" class="speech-bubble bubble-left">
                                <span class="bubble-text">{{ bubbleText }}</span>
                            </div>
                        </Transition>
                        <img :src="mascotSrc" alt="Maskot" class="q-mascot-img"/>
                    </div>
                </div>

                <!-- Options grid -->
                <div
                    class="options-grid"
                    :class="{ shake: shakeOption === currentQuestion.id }"
                >
                    <button
                        v-for="(opt, idx) in currentQuestion.options"
                        :key="opt.id"
                        class="opt-btn"
                        :class="{ selected: isSelected(opt.id) }"
                        @click="selectOption(opt.id)"
                    >
                        <span class="opt-label">{{ OPTION_LABELS[idx] }}</span>
                        <span class="opt-text">{{ opt.option_text }}</span>
                    </button>
                </div>

                <!-- Next button -->
                <div class="q-footer">
                    <button class="btn-next" @click="nextQuestion">
                        <template v-if="currentIdx < totalQuestions - 1">
                            Berikutnya <ChevronRight :size="16" :stroke-width="2.5"/>
                        </template>
                        <template v-else>
                            Selesai <CheckCircle2 :size="16" :stroke-width="2.5"/>
                        </template>
                    </button>
                </div>

            </div>
            </Transition>

            <!-- ────────── DONE ────────── -->
            <Transition name="slide-up">
            <div v-if="phase === 'done'" class="panel done-panel">
                <div class="done-inner">
                    <h2 class="done-title">Kamu telah menyelesaikan<br/>tantangan awal!</h2>
                    <p class="done-sub">Sekarang mari kita lanjutkan petualangan<br/>untuk lebih memahami materinya!</p>
                    <button class="btn-adventure" @click="goToAdventure">
                        <span>Lanjut ke peta petualangan</span>
                        <ArrowRight :size="16" :stroke-width="2.5"/>
                    </button>
                </div>
                <div class="done-mascot-wrap">
                    <Transition name="bubble">
                        <div v-if="bubbleVisible" class="speech-bubble bubble-left">
                            <span class="bubble-text">{{ bubbleText }}</span>
                        </div>
                    </Transition>
                    <img :src="mascotSrc" alt="Maskot" class="done-mascot-img"/>
                </div>
            </div>
            </Transition>

        </main>

        <!-- ══ MUSIC FAB ══ -->
        <button class="music-fab" :class="{ on: musicOn }" @click="toggleMusic"
            :title="musicOn ? 'Matikan musik' : 'Nyalakan musik'">
            <Music2 v-if="musicOn" :size="18" :stroke-width="2.2"/>
            <MusicOff v-else :size="18" :stroke-width="2.2"/>
            <span v-if="musicOn" class="fab-pulse"></span>
        </button>

    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ════════ PAGE ════════ */
.pg {
    min-height: 100vh; height: 100vh; overflow: hidden;
    font-family: 'Nunito', sans-serif;
    background-size: cover; background-position: top center; background-repeat: no-repeat;
    display: flex; flex-direction: column;
}

/* ════════ TOPBAR ════════ */
.topbar {
    flex-shrink: 0; height: 52px;
    background: rgba(255,255,255,.2);
    backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255,255,255,.35);
    padding: 0 28px; z-index: 20; display: flex; align-items: center;
}
.topbar-inner {
    width: 100%; display: flex; align-items: center;
    justify-content: space-between; gap: 16px;
}
.brand { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
.brand-ico {
    width: 26px; height: 26px; border-radius: 7px; flex-shrink: 0;
    background: linear-gradient(140deg,#60a5fa,#1d4ed8);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 2px 8px rgba(29,78,216,.4);
}
.brand-nm { font-family:'Righteous',cursive; font-size:17px; color:#fff; }

.top-progress { flex:1; display:flex; flex-direction:column; align-items:center; gap:4px; }
.tp-bar { width:100%; max-width:320px; height:7px; background:rgba(255,255,255,.35); border-radius:99px; overflow:hidden; }
.tp-fill { height:100%; background:linear-gradient(90deg,#60a5fa,#1d4ed8); border-radius:99px; transition:width .4s ease; }
.tp-label { font-size:11px; font-weight:800; color:rgba(255,255,255,.9); letter-spacing:.3px; }

.top-title { flex:1; display:flex; align-items:center; justify-content:center; gap:6px;
    font-family:'Righteous',cursive; font-size:15px; color:rgba(255,255,255,.95); }

.timer {
    display:flex; align-items:center; gap:5px; flex-shrink:0;
    background:rgba(255,255,255,.25); border:1.5px solid rgba(255,255,255,.4);
    border-radius:50px; padding:5px 13px;
    font-family:'Righteous',cursive; font-size:14px; color:#fff;
    transition:all .3s;
}
.timer.warn { background:rgba(239,68,68,.25); border-color:rgba(239,68,68,.6); color:#fca5a5; animation:pulse-red 1s ease-in-out infinite; }
@keyframes pulse-red { 0%,100%{box-shadow:0 0 0 0 rgba(239,68,68,.3);} 50%{box-shadow:0 0 0 6px rgba(239,68,68,0);} }

/* ════════ MAIN ════════ */
.main {
    flex:1; display:flex; align-items:center; justify-content:center;
    padding:20px 32px; overflow:hidden; position:relative;
}

/* ════════ PANEL ════════ */
.panel {
    background:rgba(255,255,255,.92);
    backdrop-filter:blur(24px) saturate(1.5);
    -webkit-backdrop-filter:blur(24px) saturate(1.5);
    border-radius:24px; overflow:hidden;
    border:1.5px solid rgba(255,255,255,.8);
    box-shadow:0 24px 64px rgba(0,0,0,.15), 0 4px 0 rgba(29,78,216,.1), inset 0 1px 0 #fff;
    width:100%; max-width:860px;
}

/* ════════ INTRO ════════ */
.intro-panel {
    display:grid; grid-template-columns:1fr 220px;
    align-items:center; gap:0; padding:36px 0 36px 40px;
}
.intro-left { display:flex; flex-direction:column; gap:14px; padding-right:20px; }

.badge {
    display:inline-flex; align-items:center; gap:5px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    border-radius:50px; padding:4px 12px;
    font-size:11px; font-weight:800; color:#fff; letter-spacing:.4px;
    box-shadow:0 2px 8px rgba(29,78,216,.3);
    align-self:flex-start;
}
.intro-title { font-family:'Righteous',cursive; font-size:clamp(22px,3vw,32px); color:#1e3a8a; line-height:1.2; }
.intro-desc  { font-size:13px; font-weight:700; color:#6b7280; line-height:1.65; }

.meta-row { display:flex; gap:8px; flex-wrap:wrap; }
.meta-chip {
    display:flex; align-items:center; gap:5px;
    background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:50px;
    padding:4px 12px; font-size:11.5px; font-weight:800; color:#1d4ed8;
}

.instr-box {
    background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:14px; padding:16px 20px;
}
.instr-title { font-family:'Righteous',cursive; font-size:14px; color:#0369a1; margin-bottom:8px; }
.instr-list  { list-style:disc; padding-left:16px; display:flex; flex-direction:column; gap:4px; }
.instr-list li { font-size:12.5px; font-weight:700; color:#374151; line-height:1.6; }

.btn-start {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8);
    color:#fff; border:none; border-radius:12px; padding:12px 28px;
    font-family:'Righteous',cursive; font-size:15px; cursor:pointer;
    box-shadow:0 4px 16px rgba(29,78,216,.4);
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
    align-self:flex-start;
}
.btn-start:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 24px rgba(29,78,216,.5); }
.btn-start:active { transform:scale(.97); }

/* ════════ QUIZ ════════ */
.quiz-panel { padding:28px 36px 24px; display:flex; flex-direction:column; gap:0; }

.q-header { margin-bottom:8px; }
.q-num { font-family:'Righteous',cursive; font-size:28px; color:#1e3a8a; }

.q-body { display:flex; gap:20px; align-items:flex-start; margin-bottom:20px; }
.q-left { flex:1; display:flex; flex-direction:column; gap:12px; }
.q-text { font-size:15px; font-weight:700; color:#1f2937; line-height:1.7; }
.q-img  { width:100%; max-width:280px; height:auto; border-radius:12px; border:2px solid #bfdbfe; object-fit:cover; }

.q-mascot-wrap { position:relative; flex-shrink:0; width:110px; }
.q-mascot-img { width:110px; height:auto; filter:drop-shadow(0 6px 18px rgba(0,0,0,.15)); animation:bob 3.5s ease-in-out infinite; }

.options-grid {
    display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:18px;
}
.options-grid.shake { animation:shake .5s ease; }

.opt-btn {
    display:flex; align-items:center; gap:10px;
    padding:12px 16px; border-radius:14px; cursor:pointer;
    border:2.5px solid #e5e7eb; background:#fff;
    font-family:'Nunito',sans-serif; font-size:13.5px; font-weight:700; color:#374151;
    transition:all .18s cubic-bezier(.34,1.56,.64,1);
    text-align:left;
}
.opt-btn:hover  { border-color:#93c5fd; background:#eff6ff; transform:translateY(-2px); }
.opt-btn.selected { border-color:#1d4ed8; background:linear-gradient(135deg,#eff6ff,#dbeafe); color:#1e3a8a; box-shadow:0 0 0 3px rgba(29,78,216,.12); }

.opt-label {
    width:28px; height:28px; border-radius:8px; flex-shrink:0;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff;
    display:flex; align-items:center; justify-content:center;
    font-family:'Righteous',cursive; font-size:13px;
    box-shadow:0 2px 6px rgba(29,78,216,.3);
}
.opt-btn.selected .opt-label { background:linear-gradient(135deg,#1d4ed8,#1e3a8a); }
.opt-text { flex:1; }

.q-footer { display:flex; justify-content:flex-end; }
.btn-next {
    display:inline-flex; align-items:center; gap:7px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff;
    border:none; border-radius:12px; padding:11px 24px;
    font-family:'Righteous',cursive; font-size:14px; cursor:pointer;
    box-shadow:0 4px 14px rgba(29,78,216,.4);
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
}
.btn-next:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 22px rgba(29,78,216,.5); }
.btn-next:active { transform:scale(.97); }

/* ════════ DONE ════════ */
.done-panel { display:flex; align-items:center; justify-content:space-between; padding:48px 52px; gap:20px; }
.done-inner { display:flex; flex-direction:column; gap:18px; }
.done-title { font-family:'Righteous',cursive; font-size:clamp(22px,3vw,30px); color:#1e3a8a; line-height:1.25; }
.done-sub   { font-size:13.5px; font-weight:700; color:#6b7280; line-height:1.7; }
.btn-adventure {
    display:inline-flex; align-items:center; gap:8px;
    background:linear-gradient(135deg,#3b82f6,#1d4ed8); color:#fff;
    border:none; border-radius:12px; padding:13px 28px;
    font-family:'Righteous',cursive; font-size:15px; cursor:pointer;
    box-shadow:0 4px 16px rgba(29,78,216,.4);
    transition:all .22s cubic-bezier(.34,1.56,.64,1); align-self:flex-start;
}
.btn-adventure:hover { transform:translateY(-2px) scale(1.03); box-shadow:0 8px 24px rgba(29,78,216,.5); }

.done-mascot-wrap { position:relative; width:160px; flex-shrink:0; }
.done-mascot-img { width:160px; height:auto; filter:drop-shadow(0 10px 28px rgba(0,0,0,.18)); animation:bob 3.5s ease-in-out infinite; }

/* ════════ MASCOT / BUBBLE ════════ */
.mascot-col  { position:relative; display:flex; flex-direction:column; align-items:center; }
.mascot-glow {
    position:absolute; bottom:-8px; left:50%; transform:translateX(-50%);
    width:140px; height:30px;
    background:radial-gradient(ellipse,rgba(0,0,0,.16) 0%,transparent 70%);
    border-radius:50%; pointer-events:none;
}
.mascot-img  { width:clamp(120px,18vw,200px); height:auto; position:relative; z-index:2; filter:drop-shadow(0 10px 26px rgba(0,0,0,.2)); animation:bob 3.5s ease-in-out infinite; }

.speech-bubble {
    position:absolute; background:#fff; border:2.5px solid #93c5fd;
    border-radius:18px; padding:9px 14px; min-width:130px; max-width:200px;
    white-space:nowrap; box-shadow:0 8px 24px rgba(29,78,216,.14), inset 0 1px 0 rgba(255,255,255,.9);
    z-index:10; animation:bubble-float 3.5s ease-in-out infinite;
}
.bubble-right { bottom:calc(100% + 8px); left:55%; }
.bubble-left  { bottom:calc(100% + 4px); right:55%; }

.speech-bubble::before, .speech-bubble::after { content:""; position:absolute; width:0; height:0; }

.bubble-right::before { border-left:10px solid transparent; border-right:7px solid transparent; border-top:13px solid #93c5fd; bottom:-15px; left:16px; }
.bubble-right::after  { border-left:8px solid transparent;  border-right:6px solid transparent; border-top:12px solid #fff; bottom:-12px; left:17px; }

.bubble-left::before  { border-left:10px solid transparent; border-right:7px solid transparent; border-top:13px solid #93c5fd; bottom:-15px; right:16px; }
.bubble-left::after   { border-left:8px solid transparent;  border-right:6px solid transparent; border-top:12px solid #fff; bottom:-12px; right:17px; }

.bubble-text { font-size:12.5px; font-weight:800; color:#1e3a8a; display:block; }

@keyframes bubble-float { 0%,100%{transform:translateY(0);} 50%{transform:translateY(-5px);} }
@keyframes bob { 0%,100%{transform:translateY(0) rotate(0);} 45%{transform:translateY(-8px) rotate(.5deg);} 70%{transform:translateY(-4px) rotate(-.4deg);} }

/* ── Bubble transition ── */
.bubble-enter-active { transition:opacity .35s ease, transform .38s cubic-bezier(.34,1.56,.64,1); }
.bubble-leave-active { transition:opacity .26s ease, transform .22s ease; }
.bubble-enter-from   { opacity:0; transform:translateY(8px) scale(.88); }
.bubble-leave-to     { opacity:0; transform:translateY(-6px) scale(.94); }

/* ── Slide transition ── */
.slide-up-enter-active { transition:opacity .45s ease, transform .45s cubic-bezier(.34,1.56,.64,1); }
.slide-up-leave-active { transition:opacity .25s ease, transform .25s ease; position:absolute; }
.slide-up-enter-from   { opacity:0; transform:translateY(24px); }
.slide-up-leave-to     { opacity:0; transform:translateY(-16px); }

/* ════════ MUSIC FAB ════════ */
.music-fab {
    position:fixed; bottom:22px; left:22px; z-index:300;
    width:44px; height:44px; border-radius:50%; border:none; cursor:pointer;
    background:rgba(255,255,255,.88); backdrop-filter:blur(10px);
    color:#1d4ed8; box-shadow:0 3px 14px rgba(0,0,0,.12);
    display:flex; align-items:center; justify-content:center;
    transition:all .22s cubic-bezier(.34,1.56,.64,1);
}
.music-fab:hover { transform:scale(1.1); background:#fff; }
.music-fab.on { background:linear-gradient(135deg,#60a5fa,#1d4ed8); color:#fff; box-shadow:0 5px 18px rgba(29,78,216,.45); }
.fab-pulse { position:absolute; inset:-5px; border-radius:50%; border:2px solid rgba(29,78,216,.4); animation:fab-ring 2s ease-out infinite; pointer-events:none; }
@keyframes fab-ring { 0%{transform:scale(1);opacity:.8;} 100%{transform:scale(1.55);opacity:0;} }

/* ════════ SHAKE ════════ */
@keyframes shake { 0%,100%{transform:translateX(0);} 20%{transform:translateX(-7px);} 40%{transform:translateX(7px);} 60%{transform:translateX(-4px);} 80%{transform:translateX(4px);} }

/* ════════ RESPONSIVE ════════ */
@media (max-width:768px) {
    .pg { height:auto; min-height:100vh; overflow:auto; }
    .intro-panel { grid-template-columns:1fr; padding:28px 24px 24px; }
    .mascot-col { display:none; }
    .quiz-panel { padding:22px 20px 18px; }
    .q-body { flex-direction:column; }
    .q-mascot-wrap { display:none; }
    .options-grid { grid-template-columns:1fr; }
    .done-panel { flex-direction:column; padding:36px 24px; }
    .done-mascot-wrap { display:none; }
}
@media (max-width:480px) {
    .main { padding:16px; }
    .intro-title { font-size:20px; }
    .done-title  { font-size:20px; }
}
</style>
