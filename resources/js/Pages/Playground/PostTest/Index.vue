<script>
// ── Dummy data sesuai DB schema ────────────────────────────────────
const DUMMY_PROPS = {
    quiz: {
        id:          "quiz-post-001",
        title:       "Posttest Penguasaan Materi",
        description: "Uji pemahamanmu secara menyeluruh setelah menyelesaikan semua misi. Tunjukkan kemampuan terbaikmu!",
        time_limit:  15,
        type:        "posttest",
        category:    null,
        module_id:   "mod-001",
        mission_id:  null,
    },
    mission: {
        id:           "mission-001",
        name:         "Misi 1: Dasar Komputer",
        order_number: 1,
        module_id:    "mod-001",
    },
    mascot: {
        id:          "mascot-001",
        name_pose:   "pose_semangat",
        image:       null,
        template_id: "tmpl-001",
    },
    background: {
        id:          "bg-001",
        name:        "Background Posttest",
        image:       null,
        template_id: "tmpl-001",
    },
    attempt: null,
    // Data dummy pretest (untuk perbandingan di result modal)
    pretest_result: {
        score:         60,
        correct_count: 3,
        wrong_count:   2,
        time_taken:    420,
    },
    questions: [
        {
            id:           "q-post-01",
            quiz_id:      "quiz-post-001",
            question_text:"Apa kepanjangan dari CPU dalam ilmu komputer?",
            image:        null,
            order_number: 1,
            mascot_id:    null,
            options: [
                { id: "opt-01a", question_id: "q-post-01", option_text: "Central Processing Unit",   is_correct: true,  feedback: "Benar! CPU adalah otak dari komputer." },
                { id: "opt-01b", question_id: "q-post-01", option_text: "Computer Personal Unit",    is_correct: false, feedback: "Kurang tepat. Coba ingat kembali!" },
                { id: "opt-01c", question_id: "q-post-01", option_text: "Central Program Utility",   is_correct: false, feedback: "Kurang tepat. Perhatikan kata 'Processing'." },
                { id: "opt-01d", question_id: "q-post-01", option_text: "Core Processing Utility",   is_correct: false, feedback: "Kurang tepat. CPU = Central Processing Unit." },
            ],
        },
        {
            id:           "q-post-02",
            quiz_id:      "quiz-post-001",
            question_text:"Komponen berikut yang termasuk perangkat OUTPUT komputer adalah…",
            image:        null,
            order_number: 2,
            mascot_id:    null,
            options: [
                { id: "opt-02a", question_id: "q-post-02", option_text: "Keyboard",   is_correct: false, feedback: "Keyboard adalah perangkat INPUT." },
                { id: "opt-02b", question_id: "q-post-02", option_text: "Mouse",      is_correct: false, feedback: "Mouse adalah perangkat INPUT." },
                { id: "opt-02c", question_id: "q-post-02", option_text: "Monitor",    is_correct: true,  feedback: "Benar! Monitor menampilkan hasil pemrosesan (output)." },
                { id: "opt-02d", question_id: "q-post-02", option_text: "Mikrofon",   is_correct: false, feedback: "Mikrofon adalah perangkat INPUT." },
            ],
        },
        {
            id:           "q-post-03",
            quiz_id:      "quiz-post-001",
            question_text:"RAM dalam komputer berfungsi untuk…",
            image:        null,
            order_number: 3,
            mascot_id:    null,
            options: [
                { id: "opt-03a", question_id: "q-post-03", option_text: "Menyimpan data secara permanen",                 is_correct: false, feedback: "Itu adalah fungsi Harddisk / SSD." },
                { id: "opt-03b", question_id: "q-post-03", option_text: "Menyimpan data sementara saat komputer menyala", is_correct: true,  feedback: "Benar! RAM = Random Access Memory, bersifat sementara (volatile)." },
                { id: "opt-03c", question_id: "q-post-03", option_text: "Memproses instruksi dari pengguna",              is_correct: false, feedback: "Itu adalah fungsi CPU." },
                { id: "opt-03d", question_id: "q-post-03", option_text: "Menampilkan gambar ke layar",                    is_correct: false, feedback: "Itu adalah fungsi GPU / kartu grafis." },
            ],
        },
        {
            id:           "q-post-04",
            quiz_id:      "quiz-post-001",
            question_text:"Sistem operasi yang dikembangkan oleh Microsoft adalah…",
            image:        null,
            order_number: 4,
            mascot_id:    null,
            options: [
                { id: "opt-04a", question_id: "q-post-04", option_text: "macOS",   is_correct: false, feedback: "macOS dikembangkan oleh Apple." },
                { id: "opt-04b", question_id: "q-post-04", option_text: "Linux",   is_correct: false, feedback: "Linux dikembangkan oleh komunitas open-source." },
                { id: "opt-04c", question_id: "q-post-04", option_text: "Windows", is_correct: true,  feedback: "Benar! Windows adalah produk Microsoft." },
                { id: "opt-04d", question_id: "q-post-04", option_text: "Android", is_correct: false, feedback: "Android dikembangkan oleh Google." },
            ],
        },
        {
            id:           "q-post-05",
            quiz_id:      "quiz-post-001",
            question_text:"Berapa nilai biner dari angka desimal 10?",
            image:        null,
            order_number: 5,
            mascot_id:    null,
            options: [
                { id: "opt-05a", question_id: "q-post-05", option_text: "0101", is_correct: false, feedback: "0101 dalam biner = 5 dalam desimal." },
                { id: "opt-05b", question_id: "q-post-05", option_text: "1010", is_correct: true,  feedback: "Benar! 1010 = 8+2 = 10." },
                { id: "opt-05c", question_id: "q-post-05", option_text: "1100", is_correct: false, feedback: "1100 dalam biner = 12 dalam desimal." },
                { id: "opt-05d", question_id: "q-post-05", option_text: "1001", is_correct: false, feedback: "1001 dalam biner = 9 dalam desimal." },
            ],
        },
    ],
};
</script>

<script setup>
/**
 * PosttestQuiz.vue
 *
 * Props sesuai schema DB:
 *   quiz           → quizzes table
 *   questions      → questions table (with .options)
 *   mission        → missions table
 *   mascot         → mascots table
 *   background     → backgrounds table
 *   attempt        → quiz_attempts table
 *   pretest_result → { score, correct_count, wrong_count, time_taken }
 */
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft, ArrowRight, Home,
    Zap, Clock, Music2, VolumeX,
    CheckCircle2, BookOpen, ListChecks,
    Award, Rocket, Target, GraduationCap, Shield,
    AlertTriangle, Flag, Loader2, Trophy,
    TrendingUp, TrendingDown, BarChart3,
    XCircle, CircleDot, Sparkles,
} from "lucide-vue-next";

// ── Props ─────────────────────────────────────────────────────────
const props = defineProps({
    quiz:           { type: Object, default: () => DUMMY_PROPS.quiz           },
    questions:      { type: Array,  default: () => DUMMY_PROPS.questions      },
    mission:        { type: Object, default: () => DUMMY_PROPS.mission        },
    mascot:         { type: Object, default: () => DUMMY_PROPS.mascot         },
    background:     { type: Object, default: () => DUMMY_PROPS.background     },
    attempt:        { type: Object, default: () => DUMMY_PROPS.attempt        },
    pretest_result: { type: Object, default: () => DUMMY_PROPS.pretest_result },
});

const mascotSrc = computed(() =>
    props.mascot?.image
        ? `/storage/${props.mascot.image}`
        : "/images/templates/pose_pikir.png"
);

// ── Phase ─────────────────────────────────────────────────────────
const phase      = ref("intro");
const ready      = ref(false);
const brandMoved = ref(false);

// ── Modal ─────────────────────────────────────────────────────────
const showResult = ref(false);
const activeTab  = ref("summary"); // summary | comparison | review

// ── Timer ─────────────────────────────────────────────────────────
const timeLimit  = computed(() => (props.quiz?.time_limit ?? 15) * 60);
const remaining  = ref(timeLimit.value);
let   timerInt   = null;

const timerDisplay = computed(() => {
    const m = String(Math.floor(remaining.value / 60)).padStart(2, "0");
    const s = String(remaining.value % 60).padStart(2, "0");
    return `${m}:${s}`;
});
const timerPct     = computed(() => (remaining.value / timeLimit.value) * 100);
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

const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : (musicOn.value && audioRef.value.play().catch(() => {}));
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
        audioRef.value.loop    = true;
        audioRef.value.volume  = 0.4;
        audioRef.value.preload = "auto";
        audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    }
    if (musicOn.value) { audioRef.value.pause(); musicOn.value = false; }
    else {
        try { await audioRef.value.play(); musicOn.value = true; }
        catch { musicOn.value = false; }
    }
};

// ── Mascot bubble ─────────────────────────────────────────────────
const BUBBLES = [
    "Ini posttest, fokus ya!", "Terapkan semua yang sudah dipelajari",
    "Hampir selesai, tetap semangat!", "Baca soal dengan cermat",
    "Kamu sudah belajar keras!", "Ini penilaian akhirmu!",
];
const bubbleIdx     = ref(0);
const bubbleVisible = ref(true);
let   bubbleTimer   = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.length;
        bubbleVisible.value = true;
    }, 300);
};

// ── Quiz state ────────────────────────────────────────────────────
const currentIdx   = ref(0);
const answers      = ref({});
const shakeActive  = ref(false);
const submitting   = ref(false);
const selectedAnim = ref(null);
const timeTaken    = ref(0);

const score        = ref(0);
const correctCount = ref(0);
const wrongCount   = ref(0);

const currentQ    = computed(() => props.questions?.[currentIdx.value] ?? null);
const totalQ      = computed(() => props.questions?.length ?? 0);
const answeredCnt = computed(() => Object.keys(answers.value).length);
const progressPct = computed(() =>
    totalQ.value === 0 ? 100 : Math.round((answeredCnt.value / totalQ.value) * 100)
);
const isFirst   = computed(() => currentIdx.value === 0);
const isLast    = computed(() => currentIdx.value === totalQ.value - 1);
const canGoNext = computed(() => !!answers.value[currentQ.value?.id]);

const scoreGrade = computed(() => {
    if (score.value >= 90) return { label: "Luar Biasa!",  color: "#10b981", bg: "#dcfce7" };
    if (score.value >= 75) return { label: "Bagus!",       color: "#3b82f6", bg: "#dbeafe" };
    if (score.value >= 60) return { label: "Cukup Baik",   color: "#f59e0b", bg: "#fef3c7" };
    return                        { label: "Perlu Belajar", color: "#ef4444", bg: "#fee2e2" };
});

// Perbandingan pretest
const pretestScore  = computed(() => props.pretest_result?.score ?? 0);
const scoreDelta    = computed(() => score.value - pretestScore.value);
const scoreDeltaAbs = computed(() => Math.abs(scoreDelta.value));
const improved      = computed(() => scoreDelta.value > 0);
const same          = computed(() => scoreDelta.value === 0);

const fmt = (sec) => {
    const m = Math.floor(sec / 60), s = sec % 60;
    return m === 0 ? `${s} dtk` : `${m} mnt ${s} dtk`;
};
const timeTakenDisplay    = computed(() => fmt(timeTaken.value));
const pretestTimeDisplay  = computed(() => fmt(props.pretest_result?.time_taken ?? 0));

// ── Functions ─────────────────────────────────────────────────────
function selectOption(optId) {
    answers.value[currentQ.value.id] = optId;
    selectedAnim.value = optId;
    setTimeout(() => { selectedAnim.value = null; }, 500);
}
function isSelected(optId)          { return answers.value[currentQ.value?.id] === optId; }
function isCorrectOption(q, optId)  { return q.options?.find(o => o.id === optId)?.is_correct ?? false; }
function getUserAnswer(qId)         { return answers.value[qId]; }
function getFeedback(q, optId)      { return q.options?.find(o => o.id === optId)?.feedback ?? ""; }

function goPrev() { if (!isFirst.value) currentIdx.value--; }
function goNext() {
    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => (shakeActive.value = false), 600);
        return;
    }
    if (!isLast.value) currentIdx.value++;
    else submitQuiz();
}

function startQuiz() {
    brandMoved.value = true;
    setTimeout(() => { phase.value = "quiz"; startTimer(); }, 420);
}

function submitQuiz() {
    if (submitting.value) return;
    submitting.value = true;
    clearInterval(timerInt);
    timeTaken.value = timeLimit.value - remaining.value;

    let correct = 0;
    for (const q of (props.questions || [])) {
        const chosen = answers.value[q.id];
        if (chosen && q.options?.find(o => o.id === chosen)?.is_correct) correct++;
    }
    correctCount.value = correct;
    wrongCount.value   = totalQ.value - correct;
    score.value        = totalQ.value > 0 ? Math.round((correct / totalQ.value) * 100) : 0;

    showResult.value = true;

    router.post(route("playground.posttest.submit"), {
        quiz_id:    props.quiz?.id,
        time_taken: timeTaken.value,
        score:      score.value,
        answers:    Object.entries(answers.value).map(([question_id, selected_option_id]) => ({ question_id, selected_option_id })),
    }, {
        preserveState: true,
        onError: () => { submitting.value = false; },
    });
}

function goToAdventure() { router.visit(route("playground.index")); }
function goBack()        { router.back(); }
function goHome()        { router.visit(route("playground.index")); }

const dotStatus = (i) => {
    if (i === currentIdx.value) return "active";
    if (answers.value[props.questions?.[i]?.id]) return "done";
    return "pending";
};

onMounted(() => {
    setTimeout(() => { ready.value = true; }, 80);
    bubbleTimer = setInterval(rotateBubble, 3500);
    document.addEventListener("visibilitychange", handleVisibility);
});
onUnmounted(() => {
    clearInterval(timerInt);
    clearInterval(bubbleTimer);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) { audioRef.value.pause(); audioRef.value = null; }
});

const OPT_LABELS = ["A", "B", "C", "D", "E"];
</script>

<template>
    <div style="display:none">
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
    </div>

    <div class="root">
        <div class="bg"><div class="bg-dim"></div></div>

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <button class="t-btn t-btn--back" @click="goBack" :disabled="submitting">
                <ArrowLeft :size="15" :stroke-width="2.6"/><span>Kembali</span>
            </button>

            <!-- ✅ FIXED: pakai brand--moved + brand--hidden sama seperti pretest -->
            <div class="brand" :class="{ 'brand--moved': brandMoved, 'brand--hidden': brandMoved }">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2.5"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <Transition name="timer-in">
                <div v-if="phase === 'quiz'" class="topbar-timer" :class="{ 'topbar-timer--warn': timerWarning }">
                    <div class="topbar-timer-inner">
                        <Clock :size="13" :stroke-width="2.5"/>
                        <span class="topbar-timer-val">{{ timerDisplay }}</span>
                    </div>
                    <div class="topbar-timer-track">
                        <div class="topbar-timer-fill" :class="{ 'topbar-timer-fill--warn': timerWarning }" :style="{ width: timerPct + '%' }"></div>
                    </div>
                </div>
            </Transition>
            <div class="topbar-right">
                <button class="t-btn t-btn--sq music-btn" :class="{ 'music-btn--on': musicOn }" @click="toggleMusic">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2.2"/>
                    <VolumeX v-else :size="15" :stroke-width="2.2"/>
                </button>
                <button class="t-btn t-btn--sq" @click="goHome"><Home :size="15" :stroke-width="2.6"/></button>
            </div>
        </header>

        <!-- ══ BODY ══ -->
        <div class="body" :class="{ 'body--on': ready }">

            <!-- SIDEBAR -->
            <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
                <div class="sb-info">
                    <span class="activity-tag">
                        <Target v-if="phase === 'intro'" :size="11" :stroke-width="2.5"/>
                        <Clock  v-else                   :size="11" :stroke-width="2.5"/>
                        {{ phase === 'intro' ? 'Posttest' : 'Mengerjakan' }}
                    </span>
                    <h1 class="sb-title">{{ mission?.name ?? 'Misi Belajar' }}</h1>
                    <p class="sb-sub">{{ quiz?.title ?? 'Posttest Penguasaan Materi' }}</p>
                    <div class="prog" v-if="phase === 'quiz'">
                        <div class="prog-meta">
                            <span class="prog-lbl">Progress</span>
                            <span class="prog-val"><b>{{ answeredCnt }}</b><span class="prog-of"> / {{ totalQ }}</span></span>
                        </div>
                        <div class="prog-bar">
                            <div class="prog-fill" :style="{ width: progressPct + '%' }"><span class="prog-glow"></span></div>
                        </div>
                    </div>
                    <div v-if="phase === 'intro'" class="sb-meta-cards">
                        <div class="sb-meta-item"><BookOpen :size="14" :stroke-width="2"/><span>{{ totalQ }} Soal</span></div>
                        <div class="sb-meta-item"><Clock :size="14" :stroke-width="2"/><span>{{ quiz?.time_limit ?? 15 }} Menit</span></div>
                    </div>
                    <div class="step-dots" v-if="phase === 'quiz'">
                        <button v-for="(q, i) in questions" :key="q.id" class="step-dot" :class="dotStatus(i)" @click.stop="currentIdx = i" :title="`Soal ${i + 1}`">
                            <span v-if="dotStatus(i) === 'done'">✓</span>
                            <span v-else>{{ i + 1 }}</span>
                        </button>
                    </div>
                </div>
                <div class="mascot-wrap">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble">
                            <span>{{ BUBBLES[bubbleIdx] }}</span>
                            <i class="bbl-tail-out"></i><i class="bbl-tail-in"></i>
                        </div>
                    </Transition>
                    <div class="mascot-shadow"></div>
                    <img :src="mascotSrc" alt="Maskot" class="mascot"/>
                </div>
            </aside>

            <!-- GAME PANEL -->
            <section class="game" :class="{ 'game--on': ready }">

                <!-- INTRO -->
                <Transition name="fx-phase">
                <div v-if="phase === 'intro'" class="card">
                    <div class="card-bar card-bar--blue"></div>
                    <div class="card-body">
                        <div class="card-head">
                            <div class="module-label">
                                <span class="module-tag module-tag--blue"><GraduationCap :size="13" :stroke-width="2.2"/>Posttest</span>
                                <h2 class="module-name">{{ mission?.name ?? 'Modul Belajar' }}</h2>
                            </div>
                            <span class="badge bdg-blue"><Target :size="10"/>{{ quiz?.title ?? 'Posttest Penguasaan Materi' }}</span>
                        </div>
                        <div class="divider"></div>
                        <p class="intro-desc">{{ quiz?.description ?? 'Uji pemahamanmu secara menyeluruh setelah menyelesaikan semua misi.' }}</p>
                        <div class="compare-banner">
                            <div class="compare-side">
                                <div class="compare-ico ci-gray"><Shield :size="16" :stroke-width="2"/></div>
                                <div><div class="compare-lbl">Pretest</div><div class="compare-desc">Sebelum belajar</div></div>
                            </div>
                            <div class="compare-arrow">→</div>
                            <div class="compare-side compare-side--active">
                                <div class="compare-ico ci-blue"><GraduationCap :size="16" :stroke-width="2"/></div>
                                <div><div class="compare-lbl">Posttest</div><div class="compare-desc">Setelah belajar</div></div>
                            </div>
                        </div>
                        <div class="stats-row">
                            <div class="stat"><div class="stat-ico si-blue"><BookOpen :size="16" :stroke-width="2"/></div><div><div class="stat-v">{{ totalQ }}</div><div class="stat-l">Soal</div></div></div>
                            <div class="stat-sep"></div>
                            <div class="stat"><div class="stat-ico si-purple"><Clock :size="16" :stroke-width="2"/></div><div><div class="stat-v">{{ quiz?.time_limit ?? 15 }}</div><div class="stat-l">Menit</div></div></div>
                            <div class="stat-sep"></div>
                            <div class="stat"><div class="stat-ico si-yellow"><Award :size="16" :stroke-width="2"/></div><div><div class="stat-v">XP</div><div class="stat-l">Hadiah</div></div></div>
                        </div>
                        <div class="instr">
                            <div class="instr-hd"><ListChecks :size="14" :stroke-width="2.2"/>Petunjuk Pengerjaan</div>
                            <div class="instr-grid">
                                <div class="instr-row" v-for="(t, i) in ['Baca setiap soal dengan teliti.','Pilih satu jawaban paling tepat.','Kerjakan sesuai waktu yang ada.','Hasil akan ditampilkan setelah selesai.']" :key="i">
                                    <span class="instr-n">0{{ i + 1 }}</span><span>{{ t }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- QUIZ -->
                <Transition name="fx-phase">
                <div v-if="phase === 'quiz' && currentQ" class="card">
                    <div class="card-bar card-bar--blue"></div>
                    <div class="card-body">
                        <div class="card-head">
                            <div class="module-label">
                                <span class="module-tag module-tag--blue"><BookOpen :size="13" :stroke-width="2.2"/>Pilihan Ganda</span>
                                <h2 class="module-name">{{ mission?.name ?? 'Modul Belajar' }}</h2>
                            </div>
                            <span class="card-type-pill">Soal {{ currentIdx + 1 }} / {{ totalQ }}</span>
                        </div>
                        <div class="divider"></div>
                        <div class="questions-area">
                            <div class="question-item" :class="{ 'question-item--done': canGoNext }">
                                <div class="q-label">
                                    <span class="q-num q-num--blue">{{ currentIdx + 1 }}</span>
                                    <span class="q-text">{{ currentQ.question_text }}</span>
                                    <CheckCircle2 v-if="canGoNext" :size="15" color="#10b981" :stroke-width="2.5"/>
                                </div>
                                <img v-if="currentQ.image" :src="`/storage/${currentQ.image}`" alt="Gambar soal" class="q-img"/>
                                <div class="opts" :class="{ 'opts--shake': shakeActive }">
                                    <button v-for="(opt, i) in currentQ.options" :key="opt.id" class="opt"
                                        :class="{ 'opt--sel': isSelected(opt.id), 'opt--pop': selectedAnim === opt.id }"
                                        @click="selectOption(opt.id)">
                                        <span class="opt-lbl opt-lbl--blue">{{ OPT_LABELS[i] }}</span>
                                        <span class="opt-txt">{{ opt.option_text }}</span>
                                        <CheckCircle2 :size="15" :stroke-width="2.5" class="opt-chk"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

            </section>
        </div>

        <!-- ══ FOOTER ══ -->
        <footer class="footer">
            <div class="footer-inner">
                <template v-if="phase === 'intro'">
                    <div class="footer-spacer"></div>
                    <span class="footer-info">Siap menyelesaikan posttest?</span>
                    <!-- ✅ FIXED: ganti f-btn--blue → f-btn--finish (hijau) -->
                    <button class="f-btn f-btn--finish" @click="startQuiz">
                        <Rocket :size="15" :stroke-width="2"/><span>Mulai Posttest</span><ArrowRight :size="15" :stroke-width="2.6"/>
                    </button>
                </template>
                <template v-if="phase === 'quiz' && currentQ">
                    <button class="f-btn f-btn--prev" @click="goPrev" :disabled="isFirst || submitting">
                        <ArrowLeft :size="15" :stroke-width="2.6"/><span>Sebelumnya</span>
                    </button>
                    <span class="footer-pos">{{ currentIdx + 1 }} / {{ totalQ }}</span>
                    <button class="f-btn" :class="isLast ? 'f-btn--finish' : 'f-btn--next'" :disabled="submitting" @click="goNext">
                        <template v-if="!isLast"><span>Berikutnya</span><ArrowRight :size="15" :stroke-width="2.6"/></template>
                        <template v-else>
                            <template v-if="!submitting"><Flag :size="14" :stroke-width="2.2"/><span>Selesaikan Posttest</span></template>
                            <template v-else><Loader2 :size="14" :stroke-width="2.2" class="spin"/><span>Menyimpan…</span></template>
                        </template>
                    </button>
                </template>
            </div>
            <p v-if="phase === 'quiz' && !canGoNext" class="footer-hint">
                <AlertTriangle :size="12" :stroke-width="2.4"/>Pilih jawaban terlebih dahulu untuk melanjutkan
            </p>
        </footer>

        <!-- ══════════════════════════════════════════
             RESULT MODAL
        ══════════════════════════════════════════ -->
        <Transition name="modal-in">
        <div v-if="showResult" class="modal-overlay">
            <div class="modal-wrap">

                <!-- Header -->
                <div class="modal-header">
                    <div class="modal-hd-left">
                        <div class="modal-trophy-ico"><Trophy :size="20" :stroke-width="1.8" color="#fff"/></div>
                        <div>
                            <div class="modal-title">Hasil Posttest</div>
                            <div class="modal-subtitle">{{ mission?.name ?? 'Misi Belajar' }}</div>
                        </div>
                    </div>
                    <div class="modal-hd-score">
                        <span class="mhs-num">{{ score }}</span>
                        <span class="mhs-denom">/100</span>
                        <span class="mhs-grade" :style="{ background: scoreGrade.bg, color: scoreGrade.color }">{{ scoreGrade.label }}</span>
                    </div>
                </div>

                <!-- Tabs -->
                <div class="modal-tabs">
                    <button class="tab-btn" :class="{ 'tab-active': activeTab === 'summary' }"    @click="activeTab = 'summary'">
                        <BarChart3 :size="13" :stroke-width="2.2"/>Ringkasan
                    </button>
                    <button class="tab-btn" :class="{ 'tab-active': activeTab === 'comparison' }" @click="activeTab = 'comparison'">
                        <TrendingUp :size="13" :stroke-width="2.2"/>Perbandingan
                    </button>
                    <button class="tab-btn" :class="{ 'tab-active': activeTab === 'review' }"     @click="activeTab = 'review'">
                        <CircleDot :size="13" :stroke-width="2.2"/>Pembahasan
                    </button>
                </div>

                <!-- Body -->
                <div class="modal-body">

                    <!-- ── SUMMARY ── -->
                    <div v-if="activeTab === 'summary'" class="tab-pane">

                        <div class="sum-hero">
                            <div class="sum-circle" :style="{ '--sc': scoreGrade.color }">
                                <svg viewBox="0 0 120 120" class="sum-svg">
                                    <circle class="sum-track" cx="60" cy="60" r="52"/>
                                    <circle class="sum-arc" cx="60" cy="60" r="52"
                                        :style="{ strokeDashoffset: 327 - (327 * score / 100), stroke: scoreGrade.color }"/>
                                </svg>
                                <div class="sum-inner">
                                    <span class="sum-val">{{ score }}</span>
                                    <span class="sum-unit">/100</span>
                                </div>
                            </div>
                            <div class="sum-stats">
                                <div class="sum-stat sum-stat--green">
                                    <CheckCircle2 :size="15" :stroke-width="2.5"/>
                                    <div><div class="ss-val">{{ correctCount }}</div><div class="ss-lbl">Jawaban Benar</div></div>
                                </div>
                                <div class="sum-stat sum-stat--red">
                                    <XCircle :size="15" :stroke-width="2.5"/>
                                    <div><div class="ss-val">{{ wrongCount }}</div><div class="ss-lbl">Jawaban Salah</div></div>
                                </div>
                                <div class="sum-stat sum-stat--blue">
                                    <Clock :size="15" :stroke-width="2.5"/>
                                    <div><div class="ss-val">{{ timeTakenDisplay }}</div><div class="ss-lbl">Waktu Pengerjaan</div></div>
                                </div>
                            </div>
                        </div>

                        <div class="sec-box">
                            <div class="sec-title"><BarChart3 :size="13" :stroke-width="2.2"/>Jawaban per Soal</div>
                            <div class="ans-bars">
                                <div v-for="(q, i) in questions" :key="q.id" class="ans-bar-row">
                                    <span class="abr-n">{{ i + 1 }}</span>
                                    <div class="abr-track">
                                        <div class="abr-fill" :class="isCorrectOption(q, getUserAnswer(q.id)) ? 'abr-ok' : 'abr-err'"></div>
                                    </div>
                                    <CheckCircle2 v-if="isCorrectOption(q, getUserAnswer(q.id))" :size="13" color="#10b981" :stroke-width="2.5"/>
                                    <XCircle      v-else                                          :size="13" color="#ef4444" :stroke-width="2.5"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── COMPARISON ── -->
                    <div v-if="activeTab === 'comparison'" class="tab-pane">

                        <div class="delta-card"
                            :class="improved ? 'delta-up' : same ? 'delta-same' : 'delta-down'">
                            <div class="delta-ico">
                                <TrendingUp   v-if="improved"  :size="24" :stroke-width="2"/>
                                <TrendingDown v-else-if="!same" :size="24" :stroke-width="2"/>
                                <Sparkles     v-else            :size="24" :stroke-width="2"/>
                            </div>
                            <div class="delta-body">
                                <div class="delta-title">
                                    <template v-if="improved">Meningkat {{ scoreDeltaAbs }} poin!</template>
                                    <template v-else-if="same">Skor tetap sama</template>
                                    <template v-else>Turun {{ scoreDeltaAbs }} poin</template>
                                </div>
                                <div class="delta-sub">dibanding hasil Pretest kamu</div>
                            </div>
                            <div class="delta-badge">
                                <span v-if="improved">+{{ scoreDeltaAbs }}</span>
                                <span v-else-if="same">±0</span>
                                <span v-else>-{{ scoreDeltaAbs }}</span>
                            </div>
                        </div>

                        <div class="sec-box">
                            <div class="sec-title"><BarChart3 :size="13" :stroke-width="2.2"/>Perbandingan Skor</div>
                            <div class="cmp-bars">
                                <div class="cmp-bar-row">
                                    <div class="cmp-lbl"><span class="cmp-dot cmp-dot--gray"></span>Pretest</div>
                                    <div class="cmp-track"><div class="cmp-fill cmp-fill--gray" :style="{ width: pretestScore + '%' }"></div></div>
                                    <span class="cmp-num">{{ pretestScore }}</span>
                                </div>
                                <div class="cmp-bar-row">
                                    <div class="cmp-lbl"><span class="cmp-dot cmp-dot--blue"></span>Posttest</div>
                                    <div class="cmp-track"><div class="cmp-fill cmp-fill--blue" :style="{ width: score + '%' }"></div></div>
                                    <span class="cmp-num">{{ score }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="sec-box">
                            <div class="sec-title"><ListChecks :size="13" :stroke-width="2.2"/>Detail Perbandingan</div>
                            <table class="cmp-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th class="th-pre">Pretest</th>
                                        <th class="th-post">Posttest</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="td-lbl">Skor</td>
                                        <td class="td-pre">{{ pretestScore }}</td>
                                        <td class="td-post" :style="{ color: scoreGrade.color }">{{ score }}</td>
                                    </tr>
                                    <tr>
                                        <td class="td-lbl">Benar</td>
                                        <td class="td-pre">{{ pretest_result?.correct_count ?? '–' }}</td>
                                        <td class="td-post">{{ correctCount }}</td>
                                    </tr>
                                    <tr>
                                        <td class="td-lbl">Salah</td>
                                        <td class="td-pre">{{ pretest_result?.wrong_count ?? '–' }}</td>
                                        <td class="td-post">{{ wrongCount }}</td>
                                    </tr>
                                    <tr>
                                        <td class="td-lbl">Waktu</td>
                                        <td class="td-pre">{{ pretestTimeDisplay }}</td>
                                        <td class="td-post">{{ timeTakenDisplay }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ── REVIEW ── -->
                    <div v-if="activeTab === 'review'" class="tab-pane">
                        <div class="review-list">
                            <div v-for="(q, i) in questions" :key="q.id"
                                class="review-item"
                                :class="isCorrectOption(q, getUserAnswer(q.id)) ? 'rv-ok' : 'rv-err'">
                                <div class="rv-head">
                                    <span class="rv-num">{{ i + 1 }}</span>
                                    <span class="rv-q">{{ q.question_text }}</span>
                                    <CheckCircle2 v-if="isCorrectOption(q, getUserAnswer(q.id))" :size="14" color="#10b981" :stroke-width="2.5"/>
                                    <XCircle      v-else                                          :size="14" color="#ef4444" :stroke-width="2.5"/>
                                </div>
                                <div class="rv-opts">
                                    <div v-for="(opt, oi) in q.options" :key="opt.id" class="rv-opt"
                                        :class="{
                                            'rv-opt--correct': opt.is_correct,
                                            'rv-opt--wrong':   getUserAnswer(q.id) === opt.id && !opt.is_correct,
                                        }">
                                        <span class="rv-lbl">{{ OPT_LABELS[oi] }}</span>
                                        <span class="rv-txt">{{ opt.option_text }}</span>
                                        <CheckCircle2 v-if="opt.is_correct"                       :size="12" color="#10b981" :stroke-width="2.5"/>
                                        <XCircle      v-else-if="getUserAnswer(q.id) === opt.id"  :size="12" color="#ef4444" :stroke-width="2.5"/>
                                    </div>
                                </div>
                                <div v-if="getUserAnswer(q.id)" class="rv-feedback">
                                    <Award :size="11" :stroke-width="2.2"/>{{ getFeedback(q, getUserAnswer(q.id)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button class="f-btn f-btn--finish" @click="goToAdventure">
                        <Rocket :size="15" :stroke-width="2"/><span>Kembali ke Peta</span><ArrowRight :size="15" :stroke-width="2.6"/>
                    </button>
                </div>

            </div>
        </div>
        </Transition>

    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.root { min-height: 100dvh; display: flex; flex-direction: column; font-family: 'Nunito', sans-serif; position: relative; overflow-x: hidden; }

/* BG */
.bg { position: fixed; inset: 0; z-index: 0; }
.bg-dim { position: absolute; inset: 0; background: url('/images/templates/background-pretest.png') top center / cover no-repeat fixed; background-color: #0f172a; }

/* TOPBAR */
.topbar { position: relative; z-index: 50; height: 58px; flex-shrink: 0; display: flex; align-items: center; padding: 0 18px; gap: 0; background: rgba(255,255,255,.16); backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8); border-bottom: 1px solid rgba(255,255,255,.38); box-shadow: 0 1px 0 rgba(0,0,0,.06), 0 4px 16px rgba(0,0,0,.06); overflow: hidden; }
.t-btn--back { flex-shrink: 0; margin-right: 10px; z-index: 3; }

/* ✅ FIXED: Brand — centered by default (intro), slides left on quiz — sama persis seperti pretest */
.brand {
    display: flex; align-items: center; gap: 9px;
    position: absolute; left: 50%; transform: translateX(-50%);
    transition: left .45s cubic-bezier(.34,1.56,.64,1), transform .45s cubic-bezier(.34,1.56,.64,1), opacity .4s ease;
    z-index: 2; pointer-events: none;
}
.brand--moved {
    left: 128px;
    transform: translateX(0);
}
.brand--hidden { opacity: 0; pointer-events: none; }

.brand-dot { width: 30px; height: 30px; border-radius: 9px; background: linear-gradient(140deg,#60a5fa,#1d4ed8); display: flex; align-items: center; justify-content: center; box-shadow: 0 3px 10px rgba(29,78,216,.4); flex-shrink: 0; }
.brand-name { font-family: 'Righteous', cursive; font-size: 19px; color: #fff; letter-spacing: -.1px; text-shadow: 0 1px 8px rgba(0,0,0,.2); white-space: nowrap; }

.topbar-timer { position: absolute; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 4px; min-width: 155px; z-index: 2; pointer-events: none; }
.topbar-timer-inner { display: flex; align-items: center; gap: 6px; color: #fff; text-shadow: 0 1px 5px rgba(0,0,0,.2); }
.topbar-timer-val { font-family: 'Righteous', cursive; font-size: 22px; letter-spacing: 1px; line-height: 1; }
.topbar-timer--warn .topbar-timer-inner { color: #fca5a5; }
.topbar-timer--warn .topbar-timer-val { animation: pulse-warn 1s ease-in-out infinite; }
@keyframes pulse-warn { 0%,100%{opacity:1} 50%{opacity:.6} }
.topbar-timer-track { width: 100%; height: 5px; border-radius: 99px; background: rgba(255,255,255,.22); overflow: hidden; }
.topbar-timer-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg,#60a5fa,#1d4ed8); transition: width .9s linear; box-shadow: 0 0 8px rgba(96,165,250,.6); }
.topbar-timer-fill--warn { background: linear-gradient(90deg,#f87171,#ef4444); box-shadow: 0 0 8px rgba(239,68,68,.6); }
.timer-in-enter-active { transition: opacity .4s ease .3s, transform .44s cubic-bezier(.34,1.56,.64,1) .3s; }
.timer-in-leave-active { transition: opacity .2s ease; }
.timer-in-enter-from   { opacity: 0; transform: translateX(-50%) translateY(8px) scale(.85); }
.timer-in-leave-to     { opacity: 0; }
.topbar-right { display: flex; align-items: center; gap: 8px; margin-left: auto; flex-shrink: 0; z-index: 3; }
.t-btn { display: flex; align-items: center; gap: 6px; padding: 7px 14px; background: rgba(255,255,255,.18); backdrop-filter: blur(8px); border: 1.5px solid rgba(255,255,255,.38); border-radius: 10px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; color: #fff; cursor: pointer; transition: all .2s; text-shadow: 0 1px 4px rgba(0,0,0,.15); white-space: nowrap; }
.t-btn:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.t-btn:disabled { opacity: .45; cursor: not-allowed; }
.t-btn--sq { padding: 7px 11px; }
.music-btn--on { background: linear-gradient(135deg,#60a5fa,#1d4ed8); border-color: rgba(255,255,255,.5); box-shadow: 0 4px 12px rgba(29,78,216,.3); }

/* BODY */
.body { position: relative; z-index: 10; flex: 1; display: grid; grid-template-columns: 272px 1fr; gap: 22px; max-width: 1080px; width: 100%; margin: 0 auto; padding: 24px 20px 20px; align-items: start; opacity: 0; transition: opacity .5s ease; }
.body--on { opacity: 1; }

/* SIDEBAR */
.sidebar { display: flex; flex-direction: column; opacity: 0; transform: translateX(-22px); transition: opacity .6s ease, transform .6s cubic-bezier(.34,1.56,.64,1); cursor: pointer; user-select: none; }
.sidebar--on { opacity: 1; transform: none; }
.sb-info { margin-bottom: 18px; }
.activity-tag { display: inline-flex; align-items: center; gap: 5px; background: rgba(29,78,216,.22); border: 1.5px solid rgba(255,255,255,.26); border-radius: 999px; padding: 4px 13px; font-size: 10.5px; font-weight: 800; color: #fff; letter-spacing: .3px; backdrop-filter: blur(8px); margin-bottom: 10px; }
.sb-title { font-family: 'Righteous', cursive; font-size: clamp(17px,2vw,24px); color: #fff; line-height: 1.22; text-shadow: 0 2px 16px rgba(0,0,0,.22); margin-bottom: 5px; }
.sb-sub { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.75); line-height: 1.6; text-shadow: 0 1px 5px rgba(0,0,0,.14); margin-bottom: 16px; }
.prog { width: 100%; margin-bottom: 4px; }
.prog-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.prog-lbl { font-size: 10px; font-weight: 900; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .6px; }
.prog-val { font-family: 'Righteous', cursive; font-size: 15px; color: #fff; display: flex; align-items: center; gap: 3px; }
.prog-val b { font-size: 17px; }
.prog-of { color: rgba(255,255,255,.5); font-size: 12px; }
.prog-bar { height: 9px; border-radius: 99px; background: rgba(255,255,255,.18); overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,.14); }
.prog-fill { height: 100%; border-radius: 99px; position: relative; overflow: hidden; background: linear-gradient(90deg,#60a5fa,#1d4ed8); transition: width .5s cubic-bezier(.34,1.56,.64,1); box-shadow: 0 0 14px rgba(96,165,250,.55); }
.prog-glow { position: absolute; inset: 0; background: linear-gradient(90deg,transparent 0%,rgba(255,255,255,.38) 50%,transparent 100%); animation: shine 2.4s ease-in-out infinite; }
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }
.sb-meta-cards { display: flex; flex-direction: column; gap: 8px; margin-bottom: 4px; }
.sb-meta-item { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); border-radius: 10px; padding: 8px 12px; font-size: 12px; font-weight: 800; color: rgba(255,255,255,.9); backdrop-filter: blur(6px); }
.step-dots { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 14px; }
.step-dot { min-width: 27px; height: 27px; border-radius: 50%; border: 2px solid rgba(255,255,255,.35); background: rgba(255,255,255,.15); font-size: 10.5px; font-weight: 900; color: rgba(255,255,255,.8); cursor: pointer; display: flex; align-items: center; justify-content: center; flex-shrink: 0; transition: all .2s cubic-bezier(.34,1.56,.64,1); backdrop-filter: blur(4px); }
.step-dot:hover { transform: scale(1.15); }
.step-dot.active { background: #fff; border-color: #fff; color: #1d4ed8; box-shadow: 0 3px 10px rgba(0,0,0,.2); transform: scale(1.15); }
.step-dot.done   { background: rgba(74,222,128,.3); border-color: #4ade80; color: #fff; }
.mascot-wrap { position: relative; margin-left: 6px; }
.bubble { position: relative; background: #fff; border: 2.5px solid #93c5fd; border-radius: 18px; padding: 9px 15px; min-width: 150px; max-width: 210px; box-shadow: 0 8px 28px rgba(29,78,216,.13), inset 0 1px 0 rgba(255,255,255,.9); margin-bottom: 8px; animation: float 3.5s ease-in-out infinite; }
.bubble span { font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block; }
.bbl-tail-out, .bbl-tail-in { position: absolute; width: 0; height: 0; font-style: normal; }
.bbl-tail-out { bottom: -16px; left: 18px; border-left: 11px solid transparent; border-right: 7px solid transparent; border-top: 15px solid #93c5fd; }
.bbl-tail-in  { bottom: -12px; left: 19px; border-left: 9px solid transparent; border-right: 6px solid transparent; border-top: 12px solid #fff; }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
.bbl-enter-active { transition: opacity .3s ease, transform .36s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .2s ease, transform .2s ease; }
.bbl-enter-from { opacity: 0; transform: translateY(10px) scale(.9); }
.bbl-leave-to   { opacity: 0; transform: translateY(-7px) scale(.94); }
.mascot-shadow { position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); width: 145px; height: 28px; background: radial-gradient(ellipse,rgba(0,0,0,.18) 0%,transparent 70%); border-radius: 50%; pointer-events: none; }
.mascot { position: relative; z-index: 2; width: clamp(140px,15vw,200px); height: auto; display: block; filter: drop-shadow(0 10px 26px rgba(0,0,0,.2)); animation: bob 3.5s ease-in-out infinite; transform-origin: bottom center; }
@keyframes bob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-9px) rotate(.6deg)} 70%{transform:translateY(-5px) rotate(-.4deg)} }

/* GAME */
.game { opacity: 0; transform: translateY(22px); transition: opacity .6s .12s ease, transform .6s .12s cubic-bezier(.34,1.56,.64,1); }
.game--on { opacity: 1; transform: none; }

/* CARD */
.card { background: rgba(255,255,255,.93); backdrop-filter: blur(28px) saturate(1.6); -webkit-backdrop-filter: blur(28px) saturate(1.6); border-radius: 22px; overflow: hidden; border: 1.5px solid rgba(255,255,255,.85); box-shadow: 0 20px 56px rgba(0,0,0,.12), 0 4px 0 rgba(29,78,216,.07), inset 0 1px 0 #fff; }
.card-bar { height: 4px; }
.card-bar--blue { background: linear-gradient(90deg,#3b82f6,#8b5cf6,#06b6d4); }
.card-body { padding: 20px 22px 24px; }
.card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 13px; flex-wrap: wrap; gap: 8px; }
.module-label { display: flex; flex-direction: column; gap: 3px; }
.module-tag { display: inline-flex; align-items: center; gap: 5px; border: 1.5px solid; border-radius: 50px; padding: 3px 10px; font-size: 10px; font-weight: 900; letter-spacing: .4px; width: fit-content; }
.module-tag--blue { background: linear-gradient(135deg,#eff6ff,#dbeafe); border-color: #bfdbfe; color: #1d4ed8; }
.module-name { font-family: 'Righteous', cursive; font-size: 18px; color: #1e3a8a; line-height: 1.2; }
.card-type-pill { font-size: 11px; font-weight: 900; border-radius: 99px; padding: 4px 12px; background: #dbeafe; color: #2563eb; white-space: nowrap; }
.divider { height: 1px; background: linear-gradient(90deg,transparent,rgba(29,78,216,.1),transparent); margin-bottom: 16px; }
.badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 50px; padding: 4px 11px; font-size: 10.5px; font-weight: 900; white-space: nowrap; }
.bdg-blue { background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; }
.intro-desc { font-size: 13px; font-weight: 700; color: #6b7280; line-height: 1.65; margin-bottom: 16px; }
.compare-banner { display: flex; align-items: center; gap: 10px; background: linear-gradient(135deg,#f0f9ff,#e0f2fe); border: 1.5px solid #bae6fd; border-radius: 14px; padding: 12px 18px; margin-bottom: 16px; }
.compare-side { display: flex; align-items: center; gap: 10px; flex: 1; }
.compare-side--active .compare-ico { box-shadow: 0 4px 12px rgba(29,78,216,.22); }
.compare-ico { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.ci-gray { background: #f1f5f9; color: #94a3b8; border: 1.5px solid #e2e8f0; }
.ci-blue { background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #1d4ed8; border: 1.5px solid #bfdbfe; }
.compare-lbl  { font-family: 'Righteous', cursive; font-size: 14px; color: #1e3a8a; }
.compare-desc { font-size: 11px; font-weight: 700; color: #9ca3af; margin-top: 1px; }
.compare-arrow { font-size: 20px; color: #60a5fa; font-weight: 900; }
.stats-row { display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 20px; margin-bottom: 16px; }
.stat { flex: 1; display: flex; align-items: center; gap: 10px; justify-content: center; }
.stat-ico { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.si-blue   { background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #2563eb; border: 1.5px solid #bfdbfe; }
.si-purple { background: linear-gradient(135deg,#f5f3ff,#ede9fe); color: #7c3aed; border: 1.5px solid #c4b5fd; }
.si-yellow { background: linear-gradient(135deg,#fffbeb,#fef3c7); color: #d97706; border: 1.5px solid #fde68a; }
.stat-v { font-family: 'Righteous', cursive; font-size: 20px; color: #1e3a8a; line-height: 1; }
.stat-l { font-size: 11px; font-weight: 800; color: #9ca3af; }
.stat-sep { width: 1px; height: 38px; background: #e2e8f0; flex-shrink: 0; }
.instr { background: linear-gradient(135deg,#f0f9ff,#e0f2fe); border: 1.5px solid #bae6fd; border-radius: 14px; padding: 14px 18px; }
.instr-hd { display: flex; align-items: center; gap: 7px; font-family: 'Righteous', cursive; font-size: 13px; color: #0369a1; margin-bottom: 12px; }
.instr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.instr-row { display: flex; align-items: flex-start; gap: 8px; font-size: 12px; font-weight: 700; color: #374151; line-height: 1.5; }
.instr-n { font-family: 'Righteous', cursive; font-size: 11px; color: #2563eb; background: rgba(37,99,235,.12); border-radius: 6px; padding: 2px 6px; flex-shrink: 0; line-height: 1.7; }
.questions-area { display: flex; flex-direction: column; gap: 14px; }
.question-item { border: 1.5px solid rgba(29,78,216,.07); border-radius: 14px; padding: 13px; background: #fafbfc; transition: border-color .2s, background .2s; }
.question-item--done { border-color: rgba(16,185,129,.22); background: rgba(240,253,244,.55); }
.q-label { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 11px; }
.q-num { min-width: 21px; height: 21px; border-radius: 50%; color: #fff; font-weight: 900; font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.q-num--blue { background: #3b82f6; }
.q-text { flex: 1; font-size: 13.5px; font-weight: 700; color: #1e3a8a; line-height: 1.6; }
.q-img { max-width: 280px; height: auto; border-radius: 10px; border: 2px solid #bfdbfe; object-fit: cover; margin-bottom: 10px; }
.opts { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.opts--shake { animation: shake .5s ease; }
@keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-7px)} 40%{transform:translateX(7px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
.opt { display: flex; align-items: center; gap: 10px; padding: 11px 13px; border: 2px solid #e5e7eb; border-radius: 11px; background: #fff; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 700; color: #374151; cursor: pointer; text-align: left; position: relative; overflow: hidden; width: 100%; transition: all .2s cubic-bezier(.34,1.56,.64,1); }
.opt::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg,#eff6ff,#dbeafe); opacity: 0; transition: opacity .2s; }
.opt:hover { border-color: #93c5fd; transform: translateY(-2px); box-shadow: 0 5px 14px rgba(29,78,216,.1); }
.opt:hover::before { opacity: 1; }
.opt--sel { border-color: #2563eb; background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #1e3a8a; box-shadow: 0 0 0 3px rgba(37,99,235,.14), 0 4px 14px rgba(29,78,216,.15); transform: translateY(-2px); }
.opt--pop { animation: opt-pop .35s cubic-bezier(.34,1.56,.64,1); }
@keyframes opt-pop { 0%{transform:scale(1)} 40%{transform:scale(1.05)} 100%{transform:scale(1) translateY(-2px)} }
.opt-lbl { width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-family: 'Righteous', cursive; font-size: 12px; box-shadow: 0 2px 6px rgba(0,0,0,.15); position: relative; z-index: 1; }
.opt-lbl--blue { background: linear-gradient(135deg,#60a5fa,#1d4ed8); color: #fff; }
.opt--sel .opt-lbl { background: linear-gradient(135deg,#1d4ed8,#1e3a8a); }
.opt-txt { flex: 1; position: relative; z-index: 1; }
.opt-chk { color: #2563eb; flex-shrink: 0; opacity: 0; transform: scale(0) rotate(-30deg); position: relative; z-index: 1; transition: all .25s cubic-bezier(.34,1.56,.64,1); }
.opt--sel .opt-chk { opacity: 1; transform: scale(1) rotate(0); }

/* FOOTER */
.footer { position: relative; z-index: 50; background: rgba(255,255,255,.18); backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8); border-top: 1px solid rgba(255,255,255,.38); box-shadow: 0 -1px 0 rgba(0,0,0,.04), 0 -4px 16px rgba(0,0,0,.06); padding: 11px 0 8px; flex-shrink: 0; }
.footer-inner { display: flex; align-items: center; gap: 10px; max-width: 1080px; margin: 0 auto; padding: 0 22px; }
.footer-spacer { flex: 1; }
.footer-pos  { font-family: 'Righteous', cursive; font-size: 14px; color: #fff; flex: 1; text-align: center; text-shadow: 0 1px 4px rgba(0,0,0,.15); }
.footer-info { font-family: 'Righteous', cursive; font-size: 13px; color: rgba(255,255,255,.8); flex: 1; text-align: center; }
.footer-hint { display: flex; align-items: center; justify-content: center; gap: 5px; font-size: 11px; font-weight: 800; color: #fde68a; padding: 5px 0 2px; text-shadow: 0 1px 4px rgba(0,0,0,.15); }
.f-btn { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border: none; border-radius: 11px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; cursor: pointer; flex-shrink: 0; transition: all .18s cubic-bezier(.34,1.56,.64,1); white-space: nowrap; }
.f-btn--prev   { background: rgba(255,255,255,.18); color: #fff; border: 1.5px solid rgba(255,255,255,.38); backdrop-filter: blur(8px); }
.f-btn--prev:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.f-btn--prev:disabled { opacity: .4; cursor: not-allowed; }
.f-btn--next   { background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; box-shadow: 0 4px 14px rgba(29,78,216,.38); }
.f-btn--next:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(29,78,216,.48); }
.f-btn--next:disabled { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.45) !important; box-shadow: none !important; cursor: not-allowed; }
.f-btn--finish { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.38); padding: 0 22px; }
.f-btn--finish:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(16,185,129,.48); }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
.fx-phase-enter-active { transition: opacity .4s ease, transform .44s cubic-bezier(.34,1.56,.64,1); }
.fx-phase-leave-active { transition: opacity .22s ease, transform .22s ease; position: absolute; width: 100%; }
.fx-phase-enter-from   { opacity: 0; transform: translateY(22px) scale(.97); }
.fx-phase-leave-to     { opacity: 0; transform: translateY(-14px) scale(.98); }

/* ══════════════════════════════════════════════
   RESULT MODAL
══════════════════════════════════════════════ */
.modal-overlay { position: fixed; inset: 0; z-index: 200; background: rgba(15,23,42,.75); backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px); display: flex; align-items: flex-end; justify-content: center; }
.modal-wrap { width: 100%; max-width: 680px; background: #fff; border-radius: 28px 28px 0 0; display: flex; flex-direction: column; max-height: 92dvh; box-shadow: 0 -24px 60px rgba(0,0,0,.22); overflow: hidden; }
.modal-header { background: linear-gradient(135deg,#1e3a8a,#1d4ed8,#3b82f6); padding: 20px 22px 18px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; gap: 14px; }
.modal-hd-left { display: flex; align-items: center; gap: 12px; }
.modal-trophy-ico { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-title    { font-family: 'Righteous', cursive; font-size: 18px; color: #fff; line-height: 1.2; }
.modal-subtitle { font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,.68); margin-top: 2px; }
.modal-hd-score { display: flex; align-items: flex-end; gap: 4px; flex-shrink: 0; }
.mhs-num   { font-family: 'Righteous', cursive; font-size: 42px; color: #fff; line-height: 1; }
.mhs-denom { font-size: 14px; font-weight: 900; color: rgba(255,255,255,.55); margin-bottom: 5px; }
.mhs-grade { font-family: 'Righteous', cursive; font-size: 12px; border-radius: 99px; padding: 3px 11px; position: absolute; margin-left: -80px; margin-top: 46px; white-space: nowrap; }
.modal-tabs { display: flex; padding: 10px 16px 0; gap: 4px; border-bottom: 1.5px solid #e2e8f0; flex-shrink: 0; background: #fff; }
.tab-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px 10px 0 0; border: none; font-family: 'Nunito', sans-serif; font-size: 12.5px; font-weight: 800; color: #9ca3af; background: transparent; cursor: pointer; transition: all .18s; border-bottom: 2px solid transparent; margin-bottom: -1.5px; }
.tab-btn:hover { color: #2563eb; background: #eff6ff; }
.tab-active { color: #1d4ed8 !important; border-bottom-color: #2563eb !important; background: #f0f9ff !important; }
.modal-body { flex: 1; overflow-y: auto; padding: 18px 20px 12px; overscroll-behavior: contain; }
.tab-pane { display: flex; flex-direction: column; gap: 16px; }
.sum-hero { display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px; }
.sum-circle { position: relative; width: 100px; height: 100px; flex-shrink: 0; }
.sum-svg { width: 100px; height: 100px; transform: rotate(-90deg); }
.sum-track { fill: none; stroke: #e2e8f0; stroke-width: 8; }
.sum-arc   { fill: none; stroke-width: 8; stroke-linecap: round; stroke-dasharray: 327; transition: stroke-dashoffset 1.2s cubic-bezier(.34,1.56,.64,1) .3s; }
.sum-inner { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.sum-val   { font-family: 'Righteous', cursive; font-size: 26px; color: #1e3a8a; line-height: 1; }
.sum-unit  { font-size: 10px; font-weight: 900; color: #9ca3af; }
.sum-stats { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sum-stat  { display: flex; align-items: center; gap: 10px; border-radius: 10px; padding: 9px 12px; }
.sum-stat--green { background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d; }
.sum-stat--red   { background: #fff1f2; border: 1.5px solid #fecaca; color: #b91c1c; }
.sum-stat--blue  { background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; }
.ss-val { font-family: 'Righteous', cursive; font-size: 16px; line-height: 1; }
.ss-lbl { font-size: 10.5px; font-weight: 800; opacity: .7; }
.sec-box { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; }
.sec-title { display: flex; align-items: center; gap: 7px; font-family: 'Righteous', cursive; font-size: 12.5px; color: #1e3a8a; margin-bottom: 12px; }
.ans-bars { display: flex; flex-direction: column; gap: 7px; }
.ans-bar-row { display: flex; align-items: center; gap: 8px; }
.abr-n { font-family: 'Righteous', cursive; font-size: 11px; color: #6b7280; width: 16px; text-align: center; flex-shrink: 0; }
.abr-track { flex: 1; height: 8px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
.abr-fill { height: 100%; border-radius: 99px; width: 100%; }
.abr-ok  { background: linear-gradient(90deg,#4ade80,#10b981); }
.abr-err { background: linear-gradient(90deg,#f87171,#ef4444); }
.delta-card { display: flex; align-items: center; gap: 14px; border-radius: 14px; padding: 14px 18px; border: 1.5px solid; }
.delta-up   { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.delta-down { background: #fff1f2; border-color: #fecaca; color: #b91c1c; }
.delta-same { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.delta-ico  { width: 48px; height: 48px; border-radius: 12px; background: rgba(0,0,0,.06); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.delta-body { flex: 1; }
.delta-title { font-family: 'Righteous', cursive; font-size: 16px; line-height: 1.25; }
.delta-sub   { font-size: 11.5px; font-weight: 700; opacity: .7; margin-top: 3px; }
.delta-badge { font-family: 'Righteous', cursive; font-size: 24px; flex-shrink: 0; }
.cmp-bars { display: flex; flex-direction: column; gap: 12px; }
.cmp-bar-row { display: flex; align-items: center; gap: 10px; }
.cmp-lbl { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 800; color: #374151; width: 70px; flex-shrink: 0; }
.cmp-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.cmp-dot--gray { background: #94a3b8; }
.cmp-dot--blue { background: #2563eb; }
.cmp-track { flex: 1; height: 10px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
.cmp-fill { height: 100%; border-radius: 99px; transition: width 1s cubic-bezier(.34,1.56,.64,1) .2s; }
.cmp-fill--gray { background: linear-gradient(90deg,#94a3b8,#64748b); }
.cmp-fill--blue { background: linear-gradient(90deg,#60a5fa,#1d4ed8); }
.cmp-num { font-family: 'Righteous', cursive; font-size: 14px; color: #1e3a8a; width: 28px; text-align: right; flex-shrink: 0; }
.cmp-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cmp-table thead tr { background: #f1f5f9; }
.cmp-table th { padding: 7px 12px; font-size: 11px; font-weight: 900; color: #6b7280; text-align: center; letter-spacing: .3px; }
.cmp-table th:first-child { text-align: left; }
.th-post { color: #2563eb; }
.cmp-table td { padding: 9px 12px; border-top: 1px solid #f1f5f9; }
.cmp-table tr:hover td { background: #f8fafc; }
.td-lbl  { font-weight: 800; color: #374151; }
.td-pre  { text-align: center; font-weight: 800; color: #64748b; }
.td-post { text-align: center; font-weight: 800; color: #1d4ed8; }
.review-list { display: flex; flex-direction: column; gap: 12px; }
.review-item { border: 1.5px solid; border-radius: 14px; overflow: hidden; }
.rv-ok  { border-color: #bbf7d0; }
.rv-err { border-color: #fecaca; }
.rv-head { display: flex; align-items: flex-start; gap: 8px; padding: 11px 14px 8px; background: rgba(0,0,0,.02); }
.rv-num  { min-width: 21px; height: 21px; border-radius: 50%; background: #3b82f6; color: #fff; font-weight: 900; font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.rv-q    { flex: 1; font-size: 12.5px; font-weight: 700; color: #1e3a8a; line-height: 1.55; }
.rv-opts { display: flex; flex-direction: column; gap: 5px; padding: 8px 14px; }
.rv-opt  { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 9px; border: 1.5px solid transparent; font-size: 12px; font-weight: 700; color: #374151; }
.rv-opt--correct { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.rv-opt--wrong   { background: #fff1f2; border-color: #fecaca; color: #b91c1c; }
.rv-lbl { width: 22px; height: 22px; border-radius: 6px; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-family: 'Righteous', cursive; font-size: 10px; flex-shrink: 0; }
.rv-opt--correct .rv-lbl { background: #bbf7d0; color: #15803d; }
.rv-opt--wrong   .rv-lbl { background: #fecaca; color: #b91c1c; }
.rv-txt { flex: 1; }
.rv-feedback { display: flex; align-items: center; gap: 6px; padding: 8px 14px 11px; font-size: 11.5px; font-weight: 700; color: #6b7280; background: #f8fafc; font-style: italic; }
.modal-footer { padding: 12px 20px 16px; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: flex-end; flex-shrink: 0; background: #fff; }
.modal-in-enter-active { transition: opacity .3s ease, transform .38s cubic-bezier(.34,1.56,.64,1); }
.modal-in-leave-active { transition: opacity .22s ease, transform .22s ease; }
.modal-in-enter-from   { opacity: 0; transform: translateY(80px); }
.modal-in-leave-to     { opacity: 0; transform: translateY(60px); }

/* ══ MOBILE ≤ 820px ══ */
@media (max-width: 820px) {
    .topbar { height: 54px; padding: 0 14px; }
    .brand-name { font-size: 16px; }
    .brand-dot  { width: 28px; height: 28px; }
    .topbar-timer-val { font-size: 19px; }
    .topbar-timer { min-width: 130px; }

    /* ✅ Mobile: brand fade out (tidak slide), sama seperti pretest */
    .brand {
        left: 50% !important;
        transform: translateX(-50%) !important;
        transition: opacity .4s ease, transform .35s ease !important;
    }
    .brand--hidden {
        opacity: 0 !important;
        pointer-events: none;
        transform: translateX(-50%) scale(.88) !important;
    }

    .body { grid-template-columns: 1fr; gap: 0; padding: 0; max-width: 100%; }
    .sidebar { flex-direction: column; gap: 0; opacity: 1 !important; transform: none !important; padding: 14px 16px 12px; background: rgba(15,23,42,.28); backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px); cursor: default; border-bottom: 1px solid rgba(255,255,255,.12); }
    .sb-info { margin-bottom: 0; display: flex; flex-direction: column; gap: 8px; }
    .activity-tag { margin-bottom: 0; font-size: 10px; padding: 3px 10px; }
    .sb-title { font-size: 15px; margin-bottom: 0; }
    .sb-sub   { font-size: 11.5px; margin-bottom: 0; opacity: .85; }
    .prog     { margin-bottom: 0; }
    .prog-meta { margin-bottom: 5px; }
    .prog-bar  { height: 7px; }
    .sb-meta-cards { flex-direction: row; flex-wrap: wrap; gap: 8px; }
    .sb-meta-item  { padding: 6px 10px; font-size: 11.5px; }
    .step-dots { flex-wrap: wrap; gap: 5px; margin-top: 6px; overflow-x: auto; }
    .step-dot  { min-width: 26px; height: 26px; font-size: 10px; }
    .mascot-wrap, .mascot-shadow { display: none; }
    .game { transform: none; opacity: 1; }
    .card { border-radius: 0; border-left: none; border-right: none; border-top: none; }
    .card-body { padding: 16px 15px 22px; }
    .opts { grid-template-columns: 1fr; gap: 8px; }
    .instr-grid { grid-template-columns: 1fr; gap: 7px; }
    .compare-banner { flex-direction: column; gap: 8px; }
    .compare-arrow { transform: rotate(90deg); }
    .footer-inner { padding: 0 16px; gap: 8px; }
    .f-btn { height: 42px; }
    .f-btn--finish { padding: 0 20px; }
    .modal-wrap { max-height: 96dvh; border-radius: 20px 20px 0 0; }
    .modal-header { padding: 16px 16px 14px; flex-wrap: wrap; gap: 10px; }
    .mhs-num { font-size: 34px; }
    .mhs-grade { position: static; margin: 0; font-size: 11px; }
    .modal-hd-score { align-items: center; gap: 6px; }
    .sum-hero { flex-direction: column; align-items: flex-start; gap: 12px; }
    .sum-stats { flex-direction: row; flex-wrap: wrap; }
    .sum-stat  { flex: 1; min-width: 100px; }
    .modal-tabs { padding: 8px 12px 0; }
    .tab-btn   { padding: 6px 10px; font-size: 11.5px; }
    .modal-body { padding: 14px 14px 8px; }
}

@media (max-width: 480px) {
    .topbar { height: 50px; padding: 0 12px; }
    .brand-name { font-size: 15px; }
    .brand-dot  { width: 25px; height: 25px; border-radius: 7px; }
    .topbar-timer-val { font-size: 17px; }
    .topbar-timer { min-width: 115px; }
    .t-btn span { display: none; }
    .t-btn { padding: 6px 10px; }
    .sidebar { padding: 11px 13px 10px; }
    .sb-title { font-size: 14px; }
    .card-body { padding: 13px 12px 18px; }
    .module-name { font-size: 15px; }
    .stats-row { padding: 11px 12px; }
    .stat-v { font-size: 18px; }
    .footer-inner { padding: 0 12px; gap: 7px; }
    .f-btn { height: 38px; padding: 0 12px; font-size: 12px; }
    .f-btn--finish { padding: 0 13px; }
    .footer-pos { font-size: 12px; }
    .modal-header { padding: 14px; }
    .modal-title { font-size: 15px; }
    .tab-btn { font-size: 11px; padding: 6px 9px; gap: 4px; }
    .modal-body { padding: 12px 12px 8px; }
}
</style>
