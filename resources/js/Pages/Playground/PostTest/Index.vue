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
 * Design system sepenuhnya diselaraskan dengan PretestQuiz.vue:
 *  - BG layer identik (blobs, rings, dots, tint)
 *  - Topbar identik (brand hide/fade, timer, music, home)
 *  - Sidebar identik (sb-chip, sb-title, sb-sub, progress, pills, step-dots)
 *  - Maskot: pose berubah per phase (pose_nunjuk → pose_pikir → pose_jempol)
 *  - Bubble: warna berubah per phase (yellow intro, default quiz, green done)
 *  - icard: header biru, stat tiles merah·kuning·hijau, petunjuk 4 warna
 *  - qcard: identik dengan pretest
 *  - dcard: identik dengan pretest (confetti, trophy, sparkles)
 *  - Footer buttons: fbtn--yellow intro, fbtn--blue/mint quiz, fbtn--mint done
 *  - Result modal: slide-up dari bawah dengan 3 tab
 *  - Semua transisi & animasi identik
 */
import { ref, computed, onMounted, onUnmounted, watch } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft, ArrowRight, Home,
    Zap, Clock, Music2, VolumeX,
    CheckCircle2, BookOpen, Star,
    Trophy, Sparkles, ListChecks,
    Award, Rocket, Target, Flame,
    Flag, PartyPopper, Loader2,
    Hash, MousePointerClick,
    Eye, CircleCheck, Timer, ChevronRight,
    BarChart3, TrendingUp, TrendingDown,
    XCircle, CircleDot, GraduationCap, Shield,
} from "lucide-vue-next";

// ── Props ──────────────────────────────────────────────────────────
const props = defineProps({
    quiz:           { type: Object, default: () => DUMMY_PROPS.quiz           },
    questions:      { type: Array,  default: () => DUMMY_PROPS.questions      },
    mission:        { type: Object, default: () => DUMMY_PROPS.mission        },
    mascot:         { type: Object, default: () => DUMMY_PROPS.mascot         },
    background:     { type: Object, default: () => DUMMY_PROPS.background     },
    attempt:        { type: Object, default: () => DUMMY_PROPS.attempt        },
    pretest_result: { type: Object, default: () => DUMMY_PROPS.pretest_result },
});

// ── Phase ──────────────────────────────────────────────────────────
const phase      = ref("intro");
const ready      = ref(false);
const brandMoved = ref(false);

// ── Modal (result) ─────────────────────────────────────────────────
const showResult = ref(false);
const activeTab  = ref("summary"); // summary | comparison | review

// ── Maskot pose per phase (sama persis seperti pretest) ────────────
const mascotSrc = computed(() => {
    if (phase.value === "intro") return "/images/templates/pose_nunjuk.png";
    if (phase.value === "done")  return "/images/templates/pose_jempol.png";
    return "/images/templates/pose_pikir.png";
});

// ── Bubble messages per phase ──────────────────────────────────────
const BUBBLES_INTRO = [
    "Ini posttest terakhirmu, semangat!",
    "Siap menunjukkan kemampuan terbaikmu?",
    "Terapkan semua yang sudah dipelajari!",
    "Yuk, kita buktikan hasilnya!",
];
const BUBBLES_QUIZ = [
    "Ini posttest, fokus ya!",
    "Terapkan semua yang sudah dipelajari",
    "Hampir selesai, tetap semangat!",
    "Baca soal dengan cermat",
    "Kamu sudah belajar keras!",
    "Ini penilaian akhirmu!",
];
const BUBBLES_DONE = [
    "Luar biasa! Kamu keren!",
    "Posttest selesai! Hebat sekali!",
    "Jempol buat kamu!",
    "Kerja bagus! Mantap!",
];

const BUBBLES = computed(() => {
    if (phase.value === "intro") return BUBBLES_INTRO;
    if (phase.value === "done")  return BUBBLES_DONE;
    return BUBBLES_QUIZ;
});

const bubbleIdx     = ref(0);
const bubbleVisible = ref(true);
let   bubbleTimer   = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.value.length;
        bubbleVisible.value = true;
    }, 300);
};

// Reset bubble saat phase berganti (sama seperti pretest)
watch(phase, () => {
    bubbleIdx.value = 0;
    bubbleVisible.value = false;
    setTimeout(() => { bubbleVisible.value = true; }, 200);
});

// ── Timer ──────────────────────────────────────────────────────────
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

// ── Music ──────────────────────────────────────────────────────────
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

// ── Quiz state ─────────────────────────────────────────────────────
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

// ── Result / comparison ────────────────────────────────────────────
const scoreGrade = computed(() => {
    if (score.value >= 90) return { label: "Luar Biasa!",  color: "#059669", bg: "#dcfce7" };
    if (score.value >= 75) return { label: "Bagus!",       color: "#2563eb", bg: "#dbeafe" };
    if (score.value >= 60) return { label: "Cukup Baik",   color: "#d97706", bg: "#fef3c7" };
    return                        { label: "Perlu Belajar", color: "#dc2626", bg: "#fee2e2" };
});

const pretestScore  = computed(() => props.pretest_result?.score ?? 0);
const scoreDelta    = computed(() => score.value - pretestScore.value);
const scoreDeltaAbs = computed(() => Math.abs(scoreDelta.value));
const improved      = computed(() => scoreDelta.value > 0);
const same          = computed(() => scoreDelta.value === 0);

const fmt = (sec) => {
    const m = Math.floor(sec / 60), s = sec % 60;
    return m === 0 ? `${s} dtk` : `${m} mnt ${s} dtk`;
};
const timeTakenDisplay   = computed(() => fmt(timeTaken.value));
const pretestTimeDisplay = computed(() => fmt(props.pretest_result?.time_taken ?? 0));

// ── Functions ──────────────────────────────────────────────────────
function selectOption(optId) {
    answers.value[currentQ.value.id] = optId;
    selectedAnim.value = optId;
    setTimeout(() => { selectedAnim.value = null; }, 400);
}
function isSelected(optId)         { return answers.value[currentQ.value?.id] === optId; }
function isCorrectOption(q, optId) { return q.options?.find(o => o.id === optId)?.is_correct ?? false; }
function getUserAnswer(qId)        { return answers.value[qId]; }
function getFeedback(q, optId)     { return q.options?.find(o => o.id === optId)?.feedback ?? ""; }

function goPrev() { if (!isFirst.value) currentIdx.value--; }
function goNext() {
    if (!canGoNext.value) {
        shakeActive.value = true;
        setTimeout(() => (shakeActive.value = false), 600);
        return;
    }
    if (!isLast.value) { currentIdx.value++; }
    else { submitQuiz(); }
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
    timeTaken.value = timeLimit.value - remaining.value;

    let correct = 0;
    for (const q of (props.questions || [])) {
        const chosen = answers.value[q.id];
        if (chosen && q.options?.find(o => o.id === chosen)?.is_correct) correct++;
    }
    correctCount.value = correct;
    wrongCount.value   = totalQ.value - correct;
    score.value        = totalQ.value > 0 ? Math.round((correct / totalQ.value) * 100) : 0;

    // Tampilkan done card dulu, lalu buka modal
    phase.value = "done";
    setTimeout(() => { showResult.value = true; }, 600);

    router.post(route("playground.posttest.submit"), {
        quiz_id:    props.quiz?.id,
        time_taken: timeTaken.value,
        score:      score.value,
        answers:    Object.entries(answers.value).map(([question_id, selected_option_id]) => ({
            question_id, selected_option_id,
        })),
    }, {
        preserveState: true,
        onError: () => { submitting.value = false; },
    });
}

function goToAdventure() { router.visit(route("playground.index")); }
function goBack()        { router.back(); }
function goHome()        { router.visit(route("playground.index")); }


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

// Petunjuk pengerjaan — sama struktur dengan pretest
const INSTR_ITEMS = [
    { color: "red",    icon: Eye,          text: "Baca setiap soal dengan teliti." },
    { color: "yellow", icon: CircleCheck,  text: "Pilih satu jawaban paling tepat." },
    { color: "green",  icon: Timer,        text: "Kerjakan sesuai waktu yang ada." },
    { color: "blue",   icon: ChevronRight, text: "Hasil ditampilkan setelah selesai." },
];
</script>

<template>
    <div style="display:none">
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
    </div>

    <div class="root">

        <!-- ══ BG (identik dengan pretest) ══ -->
        <div class="bg">
            <div class="bg-img"></div>
            <div class="bg-tint"></div>
            <div class="blob b1"></div>
            <div class="blob b2"></div>
            <div class="blob b3"></div>
            <div class="sh sh-circle c1"></div>
            <div class="sh sh-circle c2"></div>
            <div class="sh sh-ring r1"></div>
            <div class="sh sh-ring r2"></div>
            <div class="sh sh-ring r3"></div>
            <div class="sh sh-dot d1"></div>
            <div class="sh sh-dot d2"></div>
            <div class="sh sh-dot d3"></div>
            <div class="sh sh-dot d4"></div>
            <div class="sh sh-dot d5"></div>
            <div class="bg-dots"></div>
        </div>

        <!-- ══ TOPBAR (identik dengan pretest) ══ -->
        <header class="topbar">
            <button class="tbtn" @click="goBack" :disabled="submitting">
                <ArrowLeft :size="16" :stroke-width="2.5"/>
                <span class="tbtn-lbl">Kembali</span>
            </button>

            <div class="brand" :class="{ 'brand--hide': brandMoved }">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <Transition name="t-timer">
                <div v-if="phase === 'quiz'" class="timer" :class="{ 'timer--warn': timerWarning }">
                    <div class="timer-row">
                        <Clock :size="13" :stroke-width="2"/>
                        <span class="timer-val">{{ timerDisplay }}</span>
                    </div>
                    <div class="timer-track">
                        <div class="timer-fill" :class="{ 'fill--warn': timerWarning }" :style="{ width: timerPct + '%' }"></div>
                    </div>
                </div>
            </Transition>

            <div class="topbar-r">
                <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2"/>
                    <VolumeX v-else        :size="15" :stroke-width="2"/>
                </button>
                <button class="tbtn tbtn-sq" @click="goHome">
                    <Home :size="15" :stroke-width="2"/>
                </button>
            </div>
        </header>

        <!-- ══ BODY ══ -->
        <div class="body" :class="{ 'body--on': ready }">

            <!-- ── SIDEBAR (identik dengan pretest) ── -->
            <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
                <div class="sb-info">
                    <span class="sb-chip">
                        <Target v-if="phase === 'intro'"     :size="11" :stroke-width="2.5"/>
                        <Clock  v-else-if="phase === 'quiz'" :size="11" :stroke-width="2.5"/>
                        <Trophy v-else                       :size="11" :stroke-width="2.5"/>
                        {{ phase === 'intro' ? 'Posttest' : phase === 'quiz' ? 'Mengerjakan' : 'Selesai' }}
                    </span>
                    <h1 class="sb-title">{{ mission?.name ?? 'Misi Belajar' }}</h1>
                    <p class="sb-sub">{{ quiz?.title ?? 'Posttest Penguasaan Materi' }}</p>

                    <!-- Progress bar saat quiz -->
                    <div class="prog" v-if="phase === 'quiz'">
                        <div class="prog-meta">
                            <span class="prog-lbl">Progress</span>
                            <span class="prog-count"><b>{{ answeredCnt }}</b> / {{ totalQ }}</span>
                        </div>
                        <div class="prog-track">
                            <div class="prog-fill" :style="{ width: progressPct + '%' }">
                                <span class="prog-shine"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Info pills saat intro / done -->
                    <div v-if="phase !== 'quiz'" class="sb-pills">
                        <div class="sb-pill">
                            <BookOpen :size="13" :stroke-width="2"/>
                            <span>{{ totalQ }} Soal</span>
                        </div>
                        <div class="sb-pill">
                            <Clock :size="13" :stroke-width="2"/>
                            <span>{{ quiz?.time_limit ?? 15 }} Menit</span>
                        </div>
                    </div>

                </div>

                <!-- Mascot — pose & bubble warna berubah per phase -->
                <div class="mascot-wrap">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble"
                             :class="{
                                 'bubble--intro': phase === 'intro',
                                 'bubble--done':  phase === 'done',
                             }">
                            <span>{{ BUBBLES[bubbleIdx] }}</span>
                            <i class="bbl-o"></i>
                            <i class="bbl-i"></i>
                        </div>
                    </Transition>
                    <!-- key=phase → Vue re-mount img saat pose ganti, trigger pose-swap transition -->
                    <Transition name="pose-swap">
                        <div class="mascot-frame" :key="phase">
                            <img :src="mascotSrc" alt="Maskot" class="mascot"/>
                            <div class="mascot-shadow"></div>
                        </div>
                    </Transition>
                </div>
            </aside>

            <!-- ── MAIN PANEL ── -->
            <section class="main" :class="{ 'main--on': ready }">

                <!-- ══ INTRO CARD ══ -->
                <Transition name="pg">
                <div v-if="phase === 'intro'" class="icard">

                    <!-- Blue header band -->
                    <div class="icard-head">
                        <div class="icard-hdeco icard-hdeco-1"></div>
                        <div class="icard-hdeco icard-hdeco-2"></div>
                        <div class="icard-head-inner">
                            <div class="icard-eyebrow">
                                <GraduationCap :size="11" :stroke-width="2.5"/>
                                <span>Posttest</span>
                            </div>
                            <h2 class="icard-title">{{ mission?.name ?? 'Modul Belajar' }}</h2>
                            <p class="icard-sub">
                                {{ quiz?.description ?? 'Uji pemahamanmu secara menyeluruh setelah menyelesaikan semua misi.' }}
                            </p>
                        </div>
                    </div>

                    <!-- 3 stat tiles: Merah · Kuning · Hijau -->
                    <div class="icard-stats">
                        <div class="istat istat--red">
                            <div class="istat-icon"><BookOpen :size="19" :stroke-width="1.8"/></div>
                            <span class="istat-val">{{ totalQ }}</span>
                            <span class="istat-lbl">Soal</span>
                        </div>
                        <div class="istat istat--yellow">
                            <div class="istat-icon"><Clock :size="19" :stroke-width="1.8"/></div>
                            <span class="istat-val">{{ quiz?.time_limit ?? 15 }}</span>
                            <span class="istat-lbl">Menit</span>
                        </div>
                        <div class="istat istat--green">
                            <div class="istat-icon"><Award :size="19" :stroke-width="1.8"/></div>
                            <span class="istat-val">XP</span>
                            <span class="istat-lbl">Hadiah</span>
                        </div>
                    </div>

                    <!-- Petunjuk pengerjaan -->
                    <div class="icard-body">
                        <div class="icard-instr-hd">
                            <ListChecks :size="14" :stroke-width="2.5"/>
                            <span>Petunjuk Pengerjaan</span>
                        </div>
                        <div class="icard-instr-grid">
                            <div
                                v-for="(item, i) in INSTR_ITEMS" :key="i"
                                class="instr-row"
                                :class="`instr-row--${item.color}`">
                                <span class="instr-num">{{ String(i+1).padStart(2,'0') }}</span>
                                <component :is="item.icon" :size="13" :stroke-width="2.5" class="instr-ico"/>
                                <span class="instr-txt">{{ item.text }}</span>
                            </div>
                        </div>
                    </div>

                </div>
                </Transition>

                <!-- ══ QUIZ CARD ══ -->
                <Transition name="pg">
                <div v-if="phase === 'quiz' && currentQ" class="qcard">

                    <!-- Compact blue header -->
                    <div class="qcard-head">
                        <div class="qcard-deco qcard-deco-1"></div>
                        <div class="qcard-deco qcard-deco-2"></div>
                        <div class="qcard-head-inner">
                            <div class="qcard-chip">
                                <BookOpen :size="11" :stroke-width="2.5"/>
                                <span>Pilihan Ganda</span>
                            </div>
                            <span class="qcard-mission">{{ mission?.name ?? 'Modul Belajar' }}</span>
                            <div class="qcard-counter">
                                <span class="qcard-counter-num">{{ currentIdx + 1 }}</span>
                                <span class="qcard-counter-sep">/</span>
                                <span class="qcard-counter-tot">{{ totalQ }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Question text -->
                    <div class="qcard-question">
                        <div class="q-num-badge">
                            <Hash :size="12" :stroke-width="3"/>{{ currentIdx + 1 }}
                        </div>
                        <p class="q-text">{{ currentQ.question_text }}</p>
                        <div v-if="canGoNext" class="q-answered-badge">
                            <CheckCircle2 :size="13" :stroke-width="2.5"/>
                            <span>Terjawab</span>
                        </div>
                    </div>

                    <img v-if="currentQ.image" :src="`/storage/${currentQ.image}`" class="q-img" alt=""/>

                    <!-- Options (A–E, identik pretest) -->
                    <div class="qcard-opts" :class="{ 'opts--shake': shakeActive }">
                        <button
                            v-for="(opt, i) in currentQ.options" :key="opt.id"
                            class="opt"
                            :class="[
                                `opt--${['a','b','c','d','e'][i]}`,
                                { 'opt--sel': isSelected(opt.id), 'opt--pop': selectedAnim === opt.id }
                            ]"
                            @click="selectOption(opt.id)">
                            <span class="opt-key">{{ OPT_LABELS[i] }}</span>
                            <span class="opt-txt">{{ opt.option_text }}</span>
                            <CheckCircle2 :size="14" :stroke-width="2.5" class="opt-chk"/>
                        </button>
                    </div>

                    <!-- Bottom hint -->
                    <div v-if="!canGoNext" class="qcard-hint">
                        <MousePointerClick :size="12" :stroke-width="2"/>
                        <span>Pilih satu jawaban untuk melanjutkan</span>
                    </div>
                    <div v-else class="qcard-hint qcard-hint--done">
                        <Sparkles :size="12" :stroke-width="2"/>
                        <span>Jawaban dipilih! Klik Berikutnya</span>
                    </div>

                </div>
                </Transition>

                <!-- ══ DONE CARD (identik pretest) ══ -->
                <Transition name="pg">
                <div v-if="phase === 'done'" class="dcard">
                    <div class="dcard-confetti" aria-hidden="true">
                        <span v-for="n in 8" :key="n" :class="`conf conf-${n}`"></span>
                    </div>
                    <div class="dcard-inner">
                        <div class="trophy-wrap">
                            <div class="trophy-ring">
                                <Trophy :size="48" color="#34D399" :stroke-width="1.5"/>
                            </div>
                            <span class="sp sp1"><Sparkles :size="13" color="#2563EB"/></span>
                            <span class="sp sp2"><Star :size="11" color="#F59E0B" fill="#F59E0B"/></span>
                            <span class="sp sp3"><Sparkles :size="10" color="#BFDBFE"/></span>
                        </div>
                        <h2 class="done-ttl">Posttest Selesai!</h2>
                        <div class="done-party">
                            <PartyPopper :size="22" color="#F59E0B" :stroke-width="1.8"/>
                        </div>
                        <p class="done-sub">
                            Kamu telah menyelesaikan posttest dengan luar biasa.<br/>
                            Lihat hasilnya dan lanjutkan petualangan belajarmu!
                        </p>
                    </div>
                </div>
                </Transition>

            </section>
        </div>

        <!-- ══ FOOTER (identik dengan pretest) ══ -->
        <footer class="footer">
            <div class="footer-inner">

                <template v-if="phase === 'intro'">
                    <div class="f-space"></div>
                    <button class="fbtn fbtn--yellow" @click="startQuiz">
                        <Rocket :size="14" :stroke-width="2"/>
                        <span>Mulai Posttest</span>
                        <ArrowRight :size="14" :stroke-width="2.5"/>
                    </button>
                </template>

                <template v-if="phase === 'quiz' && currentQ">
                    <button class="fbtn fbtn--ghost" @click="goPrev" :disabled="isFirst || submitting">
                        <ArrowLeft :size="14" :stroke-width="2.5"/>
                        <span>Sebelumnya</span>
                    </button>
                    <span class="f-pos">{{ currentIdx + 1 }} / {{ totalQ }}</span>
                    <button class="fbtn"
                        :class="isLast ? 'fbtn--mint' : 'fbtn--blue'"
                        :disabled="submitting"
                        @click="goNext">
                        <template v-if="!isLast">
                            <span>Berikutnya</span>
                            <ArrowRight :size="14" :stroke-width="2.5"/>
                        </template>
                        <template v-else-if="!submitting">
                            <Flag :size="13" :stroke-width="2"/>
                            <span>Selesaikan</span>
                        </template>
                        <template v-else>
                            <Loader2 :size="13" class="spin"/>
                            <span>Menyimpan…</span>
                        </template>
                    </button>
                </template>

                <template v-if="phase === 'done'">
                    <div class="f-space"></div>
                    <button class="fbtn fbtn--mint" @click="showResult = true">
                        <BarChart3 :size="14" :stroke-width="2"/>
                        <span>Lihat Hasil</span>
                        <ArrowRight :size="14" :stroke-width="2.5"/>
                    </button>
                </template>

            </div>
        </footer>

        <!-- ══════════════════════════════════════════
             RESULT MODAL — slide up dari bawah
        ══════════════════════════════════════════ -->
        <Transition name="modal-in">
        <div v-if="showResult" class="modal-overlay" @click.self="showResult = false">
            <div class="modal-wrap">

                <!-- Header -->
                <div class="modal-header">
                    <div class="modal-hd-left">
                        <div class="modal-trophy-ico">
                            <Trophy :size="20" :stroke-width="1.8" color="#fff"/>
                        </div>
                        <div>
                            <div class="modal-title">Hasil Posttest</div>
                            <div class="modal-subtitle">{{ mission?.name ?? 'Misi Belajar' }}</div>
                        </div>
                    </div>
                    <div class="modal-hd-score">
                        <span class="mhs-num">{{ score }}</span>
                        <span class="mhs-denom">/100</span>
                        <span class="mhs-grade" :style="{ background: scoreGrade.bg, color: scoreGrade.color }">
                            {{ scoreGrade.label }}
                        </span>
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

                <!-- Modal body -->
                <div class="modal-body">

                    <!-- ── SUMMARY ── -->
                    <div v-if="activeTab === 'summary'" class="tab-pane">
                        <div class="sum-hero">
                            <div class="sum-circle">
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
                                    <CheckCircle2 v-if="isCorrectOption(q, getUserAnswer(q.id))" :size="13" color="#059669" :stroke-width="2.5"/>
                                    <XCircle      v-else                                          :size="13" color="#dc2626" :stroke-width="2.5"/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ── COMPARISON ── -->
                    <div v-if="activeTab === 'comparison'" class="tab-pane">
                        <div class="delta-card" :class="improved ? 'delta-up' : same ? 'delta-same' : 'delta-down'">
                            <div class="delta-ico">
                                <TrendingUp   v-if="improved"   :size="24" :stroke-width="2"/>
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
                                    <tr><td class="td-lbl">Skor</td><td class="td-pre">{{ pretestScore }}</td><td class="td-post" :style="{ color: scoreGrade.color }">{{ score }}</td></tr>
                                    <tr><td class="td-lbl">Benar</td><td class="td-pre">{{ pretest_result?.correct_count ?? '–' }}</td><td class="td-post">{{ correctCount }}</td></tr>
                                    <tr><td class="td-lbl">Salah</td><td class="td-pre">{{ pretest_result?.wrong_count ?? '–' }}</td><td class="td-post">{{ wrongCount }}</td></tr>
                                    <tr><td class="td-lbl">Waktu</td><td class="td-pre">{{ pretestTimeDisplay }}</td><td class="td-post">{{ timeTakenDisplay }}</td></tr>
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
                                    <CheckCircle2 v-if="isCorrectOption(q, getUserAnswer(q.id))" :size="14" color="#059669" :stroke-width="2.5"/>
                                    <XCircle      v-else                                          :size="14" color="#dc2626" :stroke-width="2.5"/>
                                </div>
                                <div class="rv-opts">
                                    <div v-for="(opt, oi) in q.options" :key="opt.id" class="rv-opt"
                                        :class="{
                                            'rv-opt--correct': opt.is_correct,
                                            'rv-opt--wrong':   getUserAnswer(q.id) === opt.id && !opt.is_correct,
                                        }">
                                        <span class="rv-lbl">{{ OPT_LABELS[oi] }}</span>
                                        <span class="rv-txt">{{ opt.option_text }}</span>
                                        <CheckCircle2 v-if="opt.is_correct"                      :size="12" color="#059669" :stroke-width="2.5"/>
                                        <XCircle      v-else-if="getUserAnswer(q.id) === opt.id" :size="12" color="#dc2626" :stroke-width="2.5"/>
                                    </div>
                                </div>
                                <div v-if="getUserAnswer(q.id)" class="rv-feedback">
                                    <Award :size="11" :stroke-width="2.2"/>{{ getFeedback(q, getUserAnswer(q.id)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal footer -->
                <div class="modal-footer">
                    <button class="fbtn fbtn--mint" @click="goToAdventure">
                        <Rocket :size="14" :stroke-width="2"/>
                        <span>Kembali ke Peta</span>
                        <ArrowRight :size="14" :stroke-width="2.5"/>
                    </button>
                </div>

            </div>
        </div>
        </Transition>

    </div>
</template>

<style scoped>
/* ═══════════════════════════════════════════
   PALETTE — identik dengan PretestQuiz
═══════════════════════════════════════════ */
:root {
    --blue:        #2563EB;
    --blue-mid:    #1d4ed8;
    --blue-deep:   #1e3a8a;
    --blue-soft:   #BFDBFE;
    --blue-pale:   #EFF6FF;
    --mint:        #34D399;
    --mint-deep:   #059669;
    --mint-soft:   #D1FAE5;
    --red:         #F87171;
    --red-deep:    #C53030;
    --red-soft:    #FFF7F7;
    --yellow:      #FBBF24;
    --yellow-deep: #92400E;
    --yellow-soft: #FFFDF0;
    --green:       #34D399;
    --green-deep:  #059669;
    --green-soft:  #F0FDF9;
    --gray-2:      #e2e8f0;
    --gray-3:      #94a3b8;
    --text:        #1e293b;
    --text-mid:    #475569;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.root {
    min-height: 100dvh; display: flex; flex-direction: column;
    font-family: 'Nunito', sans-serif; position: relative; overflow-x: hidden;
}

/* ═══════════════════════════════════════════
   BG — identik dengan pretest
═══════════════════════════════════════════ */
.bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
.bg-img  { position: absolute; inset: 0; background: url('/images/templates/background-pretest.png') center/cover no-repeat; }
.bg-tint { position: absolute; inset: 0; background: #2563EB; opacity: .52; }

.blob { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); }
.b1 { width:480px; height:480px; top:-140px; left:-100px; background:#1d4ed8; opacity:.35; animation:bDrift 20s ease-in-out infinite alternate; }
.b2 { width:380px; height:380px; bottom:-100px; right:-80px; background:#34D399; opacity:.22; animation:bDrift2 24s ease-in-out infinite alternate; }
.b3 { width:260px; height:260px; top:38%; left:52%; background:#BFDBFE; opacity:.18; animation:bDrift 28s ease-in-out 6s infinite alternate; }
@keyframes bDrift  { 0%{transform:translate(0,0)} 50%{transform:translate(30px,20px) scale(1.05)} 100%{transform:translate(-15px,35px)} }
@keyframes bDrift2 { 0%{transform:translate(0,0)} 50%{transform:translate(-28px,-18px) scale(1.06)} 100%{transform:translate(22px,-40px)} }

.sh { position: absolute; pointer-events: none; }
.sh-circle { border-radius: 50%; background: rgba(255,255,255,.06); border: 1.5px solid rgba(255,255,255,.1); animation: sDrift ease-in-out infinite alternate; }
.c1 { width:150px; height:150px; top:-30px; left:-25px; animation-duration:22s; }
.c2 { width:90px; height:90px; bottom:70px; right:50px; animation-duration:28s; animation-delay:4s; }
.sh-ring { border-radius: 50%; background: transparent; border: 1.5px solid rgba(191,219,254,.2); animation: rPulse ease-out infinite; }
.r1{width:300px;height:300px;top:-60px;left:-60px;animation-duration:9s;}
.r2{width:240px;height:240px;bottom:-50px;right:-50px;animation-duration:12s;animation-delay:2s;}
.r3{width:180px;height:180px;top:38%;left:58%;animation-duration:10s;animation-delay:5s;}
.sh-dot { border-radius: 50%; background: rgba(255,255,255,.45); animation: dFloat linear infinite; }
.d1{width:5px;height:5px;top:12%;left:9%;animation-duration:14s;}
.d2{width:3px;height:3px;top:32%;left:22%;animation-duration:18s;animation-delay:2s;}
.d3{width:6px;height:6px;top:58%;left:7%;animation-duration:12s;animation-delay:5s;}
.d4{width:4px;height:4px;top:18%;right:11%;animation-duration:16s;animation-delay:1s;}
.d5{width:5px;height:5px;top:72%;right:16%;animation-duration:20s;animation-delay:3.5s;}
@keyframes sDrift { 0%{transform:translate(0,0) rotate(0)} 50%{transform:translate(14px,-10px) rotate(6deg)} 100%{transform:translate(-10px,18px) rotate(-4deg)} }
@keyframes rPulse { 0%{transform:scale(1);opacity:.38} 70%{transform:scale(1.38);opacity:.06} 100%{transform:scale(1.65);opacity:0} }
@keyframes dFloat { 0%{transform:translateY(0);opacity:0} 10%{opacity:.55} 90%{opacity:.25} 100%{transform:translateY(-150px);opacity:0} }
.bg-dots { position: absolute; inset: 0; pointer-events: none; background-image: radial-gradient(circle,rgba(255,255,255,.09) 1px,transparent 1px); background-size: 34px 34px; }

/* ═══════════════════════════════════════════
   TOPBAR — identik pretest
═══════════════════════════════════════════ */
.topbar { position: relative; z-index: 50; height: 56px; flex-shrink: 0; display: flex; align-items: center; padding: 0 18px; background: rgba(255,255,255,.10); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(255,255,255,.16); }
.tbtn { display: flex; align-items: center; gap: 6px; padding: 7px 13px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22); border-radius: 10px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; color: #fff; cursor: pointer; transition: background .18s, transform .15s; flex-shrink: 0; }
.tbtn:hover:not(:disabled) { background: rgba(255,255,255,.22); transform: translateY(-1px); }
.tbtn:disabled { opacity: .4; cursor: not-allowed; }
.tbtn-sq  { padding: 7px 10px; }
.tbtn--on { background: #2563EB; border-color: #BFDBFE; }

.brand { position: absolute; left: 50%; transform: translateX(-50%); display: flex; align-items: center; gap: 8px; pointer-events: none; z-index: 2; transition: opacity .34s, transform .34s; }
.brand--hide { opacity: 0; transform: translateX(-50%) scale(.88); }
.brand-dot  { width: 28px; height: 28px; border-radius: 8px; background: #2563EB; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37,99,235,.5); }
.brand-name { font-family: 'Righteous', cursive; font-size: 18px; color: #fff; white-space: nowrap; }

.timer { position: absolute; left: 50%; transform: translateX(-50%); display: flex; flex-direction: column; align-items: center; gap: 4px; pointer-events: none; z-index: 2; min-width: 138px; }
.timer-row  { display: flex; align-items: center; gap: 6px; color: #fff; }
.timer-val  { font-family: 'Righteous', cursive; font-size: 21px; letter-spacing: .5px; }
.timer--warn .timer-val { color: #fca5a5; animation: tWarn 1s ease-in-out infinite; }
@keyframes tWarn { 0%,100%{opacity:1} 50%{opacity:.5} }
.timer-track { width: 100%; height: 4px; background: rgba(255,255,255,.2); border-radius: 99px; overflow: hidden; }
.timer-fill  { height: 100%; background: #BFDBFE; border-radius: 99px; transition: width .9s linear; }
.fill--warn  { background: #f87171; }
.t-timer-enter-active { transition: opacity .4s ease .25s, transform .42s cubic-bezier(.34,1.56,.64,1) .25s; }
.t-timer-leave-active { transition: opacity .18s ease; }
.t-timer-enter-from   { opacity: 0; transform: translateX(-50%) translateY(6px) scale(.88); }
.t-timer-leave-to     { opacity: 0; }
.topbar-r { display: flex; align-items: center; gap: 8px; margin-left: auto; z-index: 3; }

/* ═══════════════════════════════════════════
   BODY GRID
═══════════════════════════════════════════ */
.body { position: relative; z-index: 10; flex: 1; display: grid; grid-template-columns: 264px 1fr; gap: 20px; max-width: 1080px; width: 100%; margin: 0 auto; padding: 22px 18px 18px; align-items: start; opacity: 0; transition: opacity .45s; }
.body--on { opacity: 1; }

/* ═══════════════════════════════════════════
   SIDEBAR — identik pretest
═══════════════════════════════════════════ */
.sidebar { display: flex; flex-direction: column; opacity: 0; transform: translateX(-16px); transition: opacity .5s, transform .5s cubic-bezier(.34,1.56,.64,1); user-select: none; cursor: pointer; }
.sidebar--on { opacity: 1; transform: none; }
.sb-info { margin-bottom: 18px; }

.sb-chip { display: inline-flex; align-items: center; gap: 5px; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.38); border-radius: 999px; padding: 4px 13px; font-size: 11px; font-weight: 900; color: #fff; backdrop-filter: blur(6px); margin-bottom: 10px; }
.sb-title { font-family: 'Righteous', cursive; font-size: clamp(16px,2vw,22px); color: #fff; line-height: 1.25; margin-bottom: 5px; text-shadow: 0 1px 10px rgba(0,0,0,.35); }
.sb-sub   { font-size: 12.5px; font-weight: 800; color: rgba(255,255,255,.9); line-height: 1.55; margin-bottom: 16px; text-shadow: 0 1px 6px rgba(0,0,0,.28); }

/* Progress */
.prog { margin-bottom: 2px; }
.prog-meta  { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.prog-lbl   { font-size: 10px; font-weight: 900; color: rgba(255,255,255,.85); text-transform: uppercase; letter-spacing: .6px; }
.prog-count { font-family: 'Righteous', cursive; font-size: 14px; color: #fff; }
.prog-count b { font-size: 16px; }
.prog-track { height: 8px; background: rgba(255,255,255,.2); border-radius: 99px; overflow: hidden; }
.prog-fill  { height: 100%; background: #34D399; border-radius: 99px; position: relative; overflow: hidden; transition: width .5s cubic-bezier(.34,1.56,.64,1); }
.prog-shine { position: absolute; inset: 0; background: linear-gradient(90deg,transparent,rgba(255,255,255,.38),transparent); animation: shine 2.2s ease-in-out infinite; }
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }

/* Sidebar pills */
.sb-pills { display: flex; flex-direction: column; gap: 8px; }
.sb-pill  { display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28); border-radius: 10px; padding: 9px 13px; font-size: 12.5px; font-weight: 800; color: #fff; backdrop-filter: blur(6px); }

/* Step dots */


/* ── Mascot area — identik pretest ── */
.mascot-wrap { position: relative; padding-left: 4px; }

/* Speech bubble — warna berubah per phase */
.bubble {
    position: relative; background: #fff;
    border: 2px solid #BFDBFE; border-radius: 16px;
    padding: 9px 14px; min-width: 146px; max-width: 210px;
    box-shadow: 0 5px 18px rgba(37,99,235,.13); margin-bottom: 6px;
    animation: bblFloat 3.5s ease-in-out infinite;
}
.bubble span { font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block; }

/* Intro → yellow border */
.bubble--intro { border-color: #FCD34D; box-shadow: 0 5px 18px rgba(245,158,11,.18); }
.bubble--intro span { color: #92400e; }
.bubble--intro .bbl-o { border-top-color: #FCD34D; }

/* Done → green border */
.bubble--done { border-color: #6EE7B7; box-shadow: 0 5px 18px rgba(52,211,153,.22); }
.bubble--done span { color: #065f46; }
.bubble--done .bbl-o { border-top-color: #6EE7B7; }

.bbl-o, .bbl-i { position: absolute; width: 0; height: 0; font-style: normal; }
.bbl-o { bottom: -14px; left: 15px; border-left: 10px solid transparent; border-right: 6px solid transparent; border-top: 13px solid #BFDBFE; }
.bbl-i { bottom: -10px; left: 16px; border-left: 8px solid transparent; border-right: 5px solid transparent; border-top: 11px solid #fff; }
@keyframes bblFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

.bbl-enter-active { transition: opacity .26s, transform .3s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .16s; }
.bbl-enter-from   { opacity: 0; transform: translateY(8px) scale(.92); }
.bbl-leave-to     { opacity: 0; }

/* Mascot image */
.mascot-frame { position: relative; display: inline-block; }
.mascot { position: relative; z-index: 2; width: clamp(138px,14vw,192px); height: auto; display: block; filter: drop-shadow(0 10px 22px rgba(0,0,0,.22)); animation: mBob 3.5s ease-in-out infinite; transform-origin: bottom center; }
.mascot-shadow { position: absolute; bottom: 0; left: 50%; transform: translateX(-50%); width: 65%; height: 13px; background: radial-gradient(ellipse at center,rgba(0,0,0,.28) 0%,transparent 70%); border-radius: 50%; z-index: 1; }
@keyframes mBob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-8px) rotate(.5deg)} 70%{transform:translateY(-4px) rotate(-.3deg)} }

/* Pose swap transition */
.pose-swap-enter-active { transition: opacity .4s ease, transform .45s cubic-bezier(.34,1.56,.64,1); }
.pose-swap-leave-active { transition: opacity .2s ease; position: absolute; bottom: 0; }
.pose-swap-enter-from   { opacity: 0; transform: translateY(18px) scale(.88); }
.pose-swap-leave-to     { opacity: 0; }

/* ═══════════════════════════════════════════
   MAIN PANEL
═══════════════════════════════════════════ */
.main { opacity: 0; transform: translateY(16px); transition: opacity .5s .1s, transform .5s .1s cubic-bezier(.34,1.56,.64,1); }
.main--on { opacity: 1; transform: none; }

/* Phase card transitions */
.pg-enter-active { transition: opacity .36s, transform .4s cubic-bezier(.34,1.56,.64,1); }
.pg-leave-active { transition: opacity .18s, transform .18s; position: absolute; width: 100%; }
.pg-enter-from   { opacity: 0; transform: translateY(16px) scale(.98); }
.pg-leave-to     { opacity: 0; transform: translateY(-10px) scale(.98); }

/* ═══════════════════════════════════════════
   INTRO CARD — identik pretest palette
═══════════════════════════════════════════ */
.icard {
    background: #FDFCFB; border-radius: 20px; overflow: hidden;
    border: 1.5px solid var(--gray-2);
    box-shadow: 0 4px 0 var(--blue-soft), 0 10px 32px rgba(59,130,246,.10);
}

/* Blue header */
.icard-head { background: linear-gradient(135deg,#3B82F6 0%,#2563EB 100%); position: relative; overflow: hidden; }
.icard-hdeco { position: absolute; border-radius: 50%; background: rgba(255,255,255,.08); pointer-events: none; }
.icard-hdeco-1 { width:200px; height:200px; top:-70px; right:-40px; }
.icard-hdeco-2 { width:100px; height:100px; bottom:-42px; left:18px; }
.icard-head-inner { position: relative; z-index: 1; padding: 22px 22px 24px; }
.icard-eyebrow {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.20); border: 1px solid rgba(255,255,255,.30);
    border-radius: 999px; padding: 4px 12px;
    font-size: 11px; font-weight: 900; color: #fff; margin-bottom: 11px;
}
.icard-title { font-family: 'Righteous', cursive; font-size: clamp(18px,2.5vw,25px); color: #fff; line-height: 1.2; margin-bottom: 8px; text-shadow: 0 2px 10px rgba(0,0,0,.15); }
.icard-sub   { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.88); line-height: 1.6; max-width: 460px; }

/* Stat tiles: Merah · Kuning · Hijau */
.icard-stats { display: grid; grid-template-columns: repeat(3,1fr); border-bottom: 1.5px solid var(--gray-2); }
.istat { display: flex; flex-direction: column; align-items: center; gap: 5px; padding: 20px 12px 18px; border-right: 1.5px solid var(--gray-2); }
.istat:last-child { border-right: none; }

.istat--red    { background: #FFF7F7; }
.istat--red    .istat-icon { background: #F87171; box-shadow: 0 4px 14px rgba(248,113,113,.30); }
.istat--red    .istat-val  { color: #C53030; }
.istat--red    .istat-lbl  { color: #E53E3E; }

.istat--yellow { background: #FFFDF0; }
.istat--yellow .istat-icon { background: #FBBF24; box-shadow: 0 4px 14px rgba(251,191,36,.30); }
.istat--yellow .istat-val  { color: #92400E; }
.istat--yellow .istat-lbl  { color: #D97706; }

.istat--green  { background: #F0FDF9; }
.istat--green  .istat-icon { background: #34D399; box-shadow: 0 4px 14px rgba(52,211,153,.30); }
.istat--green  .istat-val  { color: #065F46; }
.istat--green  .istat-lbl  { color: #059669; }

.istat-icon { width: 46px; height: 46px; border-radius: 13px; display: flex; align-items: center; justify-content: center; color: #fff; margin-bottom: 2px; }
.istat-val  { font-family: 'Righteous', cursive; font-size: 24px; line-height: 1; }
.istat-lbl  { font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: .5px; }

/* Petunjuk — 4 warna: Merah · Kuning · Hijau · Biru */
.icard-body     { padding: 18px 20px 22px; }
.icard-instr-hd { display: flex; align-items: center; gap: 7px; font-family: 'Righteous', cursive; font-size: 13.5px; color: var(--blue-mid); margin-bottom: 13px; }
.icard-instr-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }

.instr-row  { display: flex; align-items: center; gap: 9px; border-radius: 12px; padding: 11px 13px; font-size: 12.5px; font-weight: 800; color: var(--text); border: 1.5px solid transparent; }
.instr-row--red    { background: #FFF7F7; border-color: #FECACA; }
.instr-row--red    .instr-num { background: #F87171; color: #fff; }
.instr-row--red    .instr-ico { color: #F87171; }
.instr-row--yellow { background: #FFFDF0; border-color: #FDE68A; }
.instr-row--yellow .instr-num { background: #FBBF24; color: #fff; }
.instr-row--yellow .instr-ico { color: #D97706; }
.instr-row--green  { background: #F0FDF9; border-color: #A7F3D0; }
.instr-row--green  .instr-num { background: #34D399; color: #fff; }
.instr-row--green  .instr-ico { color: #059669; }
.instr-row--blue   { background: #EFF6FF; border-color: #BFDBFE; }
.instr-row--blue   .instr-num { background: #3B82F6; color: #fff; }
.instr-row--blue   .instr-ico { color: #3B82F6; }

.instr-num { font-family: 'Righteous', cursive; font-size: 11px; border-radius: 6px; padding: 3px 7px; flex-shrink: 0; line-height: 1.4; }
.instr-ico { flex-shrink: 0; }
.instr-txt { flex: 1; line-height: 1.45; }

/* ═══════════════════════════════════════════
   QUIZ CARD — identik pretest
═══════════════════════════════════════════ */

.qcard {
    background: #FDFCFB;                          /* putih hangat, bukan abu */
    border-radius: 20px;
    border: 1.5px solid #E2E8F0;
    overflow: hidden;
    box-shadow: 0 4px 0 #BFDBFE, 0 10px 32px rgba(37,99,235,.10);
}

/* Header biru gradient lembut */
.qcard-head {
    background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
    position: relative;
    overflow: hidden;
}

/* Dekorasi bulat — putih transparan, bukan merah */
.qcard-deco {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.08);        /* ← fix: hapus merah, ganti putih soft */
    pointer-events: none;
}
.qcard-deco-1 { width: 160px; height: 160px; top: -55px; right: -35px; }
.qcard-deco-2 { width: 80px;  height: 80px;  bottom: -30px; left: 18px; }

.qcard-head-inner {
    position: relative; z-index: 1;
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; padding: 12px 18px;
}

/* Chip label */
.qcard-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.28);
    border-radius: 999px; padding: 4px 10px;
    font-size: 10.5px; font-weight: 900; color: #fff;
    white-space: nowrap; flex-shrink: 0;
}

.qcard-mission {
    flex: 1; text-align: center;
    font-family: 'Righteous', cursive; font-size: 13px;
    color: rgba(255,255,255,.85);
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    padding: 0 8px;
}

/* Counter soal */
.qcard-counter     { display: flex; align-items: baseline; gap: 2px; flex-shrink: 0; }
.qcard-counter-num { font-family: 'Righteous', cursive; font-size: 20px; color: #fff; line-height: 1; }
.qcard-counter-sep { font-size: 13px; color: rgba(255,255,255,.5); margin: 0 1px; }
.qcard-counter-tot { font-family: 'Righteous', cursive; font-size: 13px; color: rgba(255,255,255,.6); }

/* Area pertanyaan */
.qcard-question {
    padding: 20px 20px 0;
    display: flex; align-items: flex-start; gap: 11px;
    /* Background lembut biru pucat agar ada aksen warna */
    background: #EFF6FF;
    margin: 0;
}

/* Badge nomor soal — biru */
.q-num-badge {
    display: inline-flex; align-items: center; gap: 2px;
    min-width: 34px; height: 34px; border-radius: 10px;
    background: #3B82F6;                           /* biru lembut */
    color: #fff;
    font-family: 'Righteous', cursive; font-size: 13px;
    justify-content: center; flex-shrink: 0;
    box-shadow: 0 3px 8px rgba(59,130,246,.28);
    margin-top: 1px;
}

.q-text {
    flex: 1; font-size: 15px; font-weight: 800;
    color: #1E293B; line-height: 1.65;
    padding-bottom: 20px;                          /* padding dalam area biru */
}

/* Badge "Terjawab" — hijau mint soft */
.q-answered-badge {
    display: inline-flex; align-items: center; gap: 4px;
    background: #ECFDF5;
    border: 1.5px solid #6EE7B7;
    border-radius: 999px; padding: 3px 9px;
    font-size: 10px; font-weight: 900; color: #059669;
    flex-shrink: 0; margin-top: 3px; white-space: nowrap;
}

/* Gambar soal */
.q-img {
    max-width: 100%; border-radius: 12px;
    border: 2px solid #BFDBFE;
    margin: 14px 20px 0; display: block;
}

/* ── Area pilihan jawaban ── */
.qcard-opts {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 10px;
    padding: 16px 20px 4px;
    background: #FDFCFB;
}

/* Options */
.qcard-opts { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding: 16px 20px 4px; }
.opts--shake { animation: optShake .5s ease; }
@keyframes optShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 40%{transform:translateX(5px)} 60%{transform:translateX(-3px)} 80%{transform:translateX(3px)} }

.opt { display: flex; align-items: stretch; border-radius: 13px; overflow: hidden; border: 2px solid transparent; background: #fff; cursor: pointer; text-align: left; width: 100%; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; color: #1e293b; min-height: 52px; box-shadow: 0 2px 0 rgba(0,0,0,.06), 0 3px 10px rgba(0,0,0,.05); transition: transform .18s cubic-bezier(.34,1.56,.64,1), box-shadow .18s, border-color .18s; }
.opt:hover { transform: translateY(-2px); box-shadow: 0 5px 0 rgba(0,0,0,.07), 0 8px 18px rgba(0,0,0,.08); }
.opt--pop  { animation: oPop .3s cubic-bezier(.34,1.56,.64,1) forwards; }
@keyframes oPop { 0%{transform:scale(1)} 45%{transform:scale(1.04) translateY(-2px)} 100%{transform:translateY(-2px)} }

.opt--a { border-color:#BFDBFE; } .opt--a .opt-key { background:#DBEAFE; color:#1d4ed8; border-right-color:#BFDBFE; }
.opt--a.opt--sel { border-color:#2563EB; box-shadow:0 5px 0 #1d4ed8,0 8px 18px rgba(37,99,235,.2); transform:translateY(-2px); }
.opt--a.opt--sel .opt-key { background:#2563EB; color:#fff; border-right-color:#2563EB; }

.opt--b { border-color:#FCD34D; } .opt--b .opt-key { background:#FEF3C7; color:#B45309; border-right-color:#FCD34D; }
.opt--b.opt--sel { border-color:#D97706; box-shadow:0 5px 0 #B45309,0 8px 18px rgba(217,119,6,.2); transform:translateY(-2px); }
.opt--b.opt--sel .opt-key { background:#D97706; color:#fff; border-right-color:#D97706; }

.opt--c { border-color:#6EE7B7; } .opt--c .opt-key { background:#D1FAE5; color:#047857; border-right-color:#6EE7B7; }
.opt--c.opt--sel { border-color:#059669; box-shadow:0 5px 0 #047857,0 8px 18px rgba(5,150,105,.2); transform:translateY(-2px); }
.opt--c.opt--sel .opt-key { background:#059669; color:#fff; border-right-color:#059669; }

.opt--d { border-color:#FDA4AF; } .opt--d .opt-key { background:#FFE4E6; color:#BE123C; border-right-color:#FDA4AF; }
.opt--d.opt--sel { border-color:#E11D48; box-shadow:0 5px 0 #BE123C,0 8px 18px rgba(225,29,72,.2); transform:translateY(-2px); }
.opt--d.opt--sel .opt-key { background:#E11D48; color:#fff; border-right-color:#E11D48; }

.opt--e { border-color:#C4B5FD; } .opt--e .opt-key { background:#EDE9FE; color:#6D28D9; border-right-color:#C4B5FD; }
.opt--e.opt--sel { border-color:#7C3AED; box-shadow:0 5px 0 #6D28D9,0 8px 18px rgba(124,58,237,.2); transform:translateY(-2px); }
.opt--e.opt--sel .opt-key { background:#7C3AED; color:#fff; border-right-color:#7C3AED; }

.opt-key { display: flex; align-items: center; justify-content: center; width: 46px; min-width: 46px; align-self: stretch; font-family: 'Righteous', cursive; font-size: 16px; border-right: 2px solid transparent; flex-shrink: 0; transition: background .18s, color .18s, border-color .18s; }
.opt-txt  { flex: 1; padding: 12px 10px 12px 11px; line-height: 1.4; color: #1e293b; font-size: 13px; }
.opt-chk  { color: var(--mint); flex-shrink: 0; margin-right: 10px; align-self: center; opacity: 0; transform: scale(0) rotate(-20deg); transition: all .22s cubic-bezier(.34,1.56,.64,1); }
.opt--sel .opt-chk { opacity: 1; transform: scale(1) rotate(0); }

.qcard-hint      { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 20px 14px; font-size: 11.5px; font-weight: 800; color: #94a3b8; }
.qcard-hint--done { color: #059669; }

/* ═══════════════════════════════════════════
   DONE CARD — identik pretest
═══════════════════════════════════════════ */
.dcard {
    background: #fff; border-radius: 20px; overflow: hidden;
    border: 1.5px solid var(--gray-2); position: relative;
    box-shadow: 0 4px 0 #6EE7B7, 0 10px 32px rgba(52,211,153,.15);
}
.dcard-confetti { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.conf { position: absolute; border-radius: 3px; animation: confFall linear infinite; }
.conf-1{width:8px;height:8px;background:#F59E0B;top:-10px;left:8%;animation-duration:3.5s;animation-delay:.1s;transform:rotate(20deg);}
.conf-2{width:6px;height:6px;background:#2563EB;top:-10px;left:22%;animation-duration:4s;animation-delay:.6s;transform:rotate(-15deg);}
.conf-3{width:7px;height:7px;background:#F87171;top:-10px;left:38%;animation-duration:3.2s;animation-delay:.3s;transform:rotate(35deg);}
.conf-4{width:5px;height:5px;background:#34D399;top:-10px;left:55%;animation-duration:4.5s;animation-delay:.9s;transform:rotate(-30deg);}
.conf-5{width:8px;height:8px;background:#A78BFA;top:-10px;left:70%;animation-duration:3.8s;animation-delay:.5s;transform:rotate(10deg);}
.conf-6{width:5px;height:5px;background:#F59E0B;top:-10px;left:82%;animation-duration:4.2s;animation-delay:1.1s;transform:rotate(-25deg);}
.conf-7{width:6px;height:6px;background:#F87171;top:-10px;left:91%;animation-duration:3.6s;animation-delay:.7s;transform:rotate(45deg);}
.conf-8{width:7px;height:7px;background:#2563EB;top:-10px;left:48%;animation-duration:3.9s;animation-delay:1.3s;transform:rotate(-10deg);}
@keyframes confFall { 0%{transform:translateY(0) rotate(0);opacity:1} 100%{transform:translateY(320px) rotate(360deg);opacity:0} }

.dcard-inner { display: flex; flex-direction: column; align-items: center; text-align: center; padding: 42px 28px 38px; gap: 12px; }
.trophy-wrap { position: relative; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; }
.trophy-ring { width: 88px; height: 88px; border-radius: 50%; background: var(--mint-soft); border: 2.5px solid var(--mint); display: flex; align-items: center; justify-content: center; box-shadow: 0 5px 22px rgba(52,211,153,.26); animation: tPop .5s cubic-bezier(.34,1.56,.64,1) both .08s; }
@keyframes tPop { from{transform:scale(0) rotate(-15deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1} }
.sp { position: absolute; animation: spFloat 2.2s ease-in-out infinite; }
.sp1{top:-2px;right:2px;} .sp2{bottom:2px;left:-2px;animation-delay:.5s} .sp3{top:8px;left:-4px;animation-delay:1.1s}
@keyframes spFloat { 0%,100%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-8px) rotate(14deg)} }
.done-ttl   { font-family: 'Righteous', cursive; font-size: clamp(20px,3vw,26px); color: var(--blue-deep); }
.done-party { animation: spFloat 1.8s ease-in-out infinite; }
.done-sub   { font-size: 13.5px; font-weight: 700; color: var(--text-mid); line-height: 1.7; }

/* ═══════════════════════════════════════════
   FOOTER — identik pretest
═══════════════════════════════════════════ */
.footer { position: relative; z-index: 50; background: rgba(255,255,255,.10); backdrop-filter: blur(18px); border-top: 1px solid rgba(255,255,255,.16); padding: 11px 0 8px; flex-shrink: 0; }
.footer-inner { display: flex; align-items: center; gap: 10px; max-width: 1080px; margin: 0 auto; padding: 0 20px; }
.f-space { flex: 1; }
.f-pos   { font-family: 'Righteous', cursive; font-size: 13px; color: #fff; flex: 1; text-align: center; }

.fbtn { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border: none; border-radius: 10px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; cursor: pointer; flex-shrink: 0; white-space: nowrap; transition: transform .15s cubic-bezier(.34,1.56,.64,1), box-shadow .15s; }
.fbtn--ghost  { background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.25); }
.fbtn--ghost:hover:not(:disabled) { background: rgba(255,255,255,.22); transform: translateY(-1px); }
.fbtn--ghost:disabled  { opacity: .4; cursor: not-allowed; }
.fbtn--blue   { background: #2563EB; color: #fff; box-shadow: 0 3px 12px rgba(37,99,235,.4); }
.fbtn--blue:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 5px 16px rgba(37,99,235,.5); }
.fbtn--blue:disabled   { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.4) !important; box-shadow: none !important; cursor: not-allowed; }
.fbtn--mint   { background: #34D399; color: #fff; box-shadow: 0 3px 12px rgba(52,211,153,.4); }
.fbtn--mint:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 5px 16px rgba(52,211,153,.5); }
.fbtn--mint:disabled   { opacity: .5; cursor: not-allowed; }
.fbtn--yellow { background: #2563EB; color: #fff; box-shadow: 0 3px 12px rgba(37,99,235,.4); } /* sama warna biru, konsisten pretest */
.fbtn--yellow:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 5px 16px rgba(37,99,235,.5); }

@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }

/* ═══════════════════════════════════════════
   RESULT MODAL
═══════════════════════════════════════════ */
.modal-overlay { position: fixed; inset: 0; z-index: 200; background: rgba(15,23,42,.72); backdrop-filter: blur(14px); display: flex; align-items: flex-end; justify-content: center; }
.modal-wrap    { width: 100%; max-width: 680px; background: #fff; border-radius: 28px 28px 0 0; display: flex; flex-direction: column; max-height: 92dvh; box-shadow: 0 -24px 60px rgba(0,0,0,.22); overflow: hidden; }

.modal-header  { background: linear-gradient(135deg,#1e3a8a,#1d4ed8,#3B82F6); padding: 20px 22px 18px; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; gap: 14px; }
.modal-hd-left { display: flex; align-items: center; gap: 12px; }
.modal-trophy-ico { width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.modal-title    { font-family: 'Righteous', cursive; font-size: 18px; color: #fff; line-height: 1.2; }
.modal-subtitle { font-size: 11.5px; font-weight: 700; color: rgba(255,255,255,.68); margin-top: 2px; }
.modal-hd-score { display: flex; align-items: flex-end; gap: 4px; flex-shrink: 0; flex-direction: column; align-items: flex-end; }
.mhs-num   { font-family: 'Righteous', cursive; font-size: 42px; color: #fff; line-height: 1; }
.mhs-denom { font-size: 14px; font-weight: 900; color: rgba(255,255,255,.55); }
.mhs-grade { font-family: 'Righteous', cursive; font-size: 11px; border-radius: 99px; padding: 3px 11px; white-space: nowrap; }

.modal-tabs { display: flex; padding: 10px 16px 0; gap: 4px; border-bottom: 1.5px solid #e2e8f0; flex-shrink: 0; background: #fff; }
.tab-btn    { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 10px 10px 0 0; border: none; font-family: 'Nunito', sans-serif; font-size: 12.5px; font-weight: 800; color: #9ca3af; background: transparent; cursor: pointer; transition: all .18s; border-bottom: 2px solid transparent; margin-bottom: -1.5px; }
.tab-btn:hover { color: #2563eb; background: #eff6ff; }
.tab-active    { color: #1d4ed8 !important; border-bottom-color: #2563eb !important; background: #f0f9ff !important; }

.modal-body { flex: 1; overflow-y: auto; padding: 18px 20px 12px; overscroll-behavior: contain; }
.tab-pane   { display: flex; flex-direction: column; gap: 16px; }

/* Summary */
.sum-hero  { display: flex; align-items: center; gap: 16px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 16px; }
.sum-circle{ position: relative; width: 100px; height: 100px; flex-shrink: 0; }
.sum-svg   { width: 100px; height: 100px; transform: rotate(-90deg); }
.sum-track { fill: none; stroke: #e2e8f0; stroke-width: 8; }
.sum-arc   { fill: none; stroke-width: 8; stroke-linecap: round; stroke-dasharray: 327; transition: stroke-dashoffset 1.2s cubic-bezier(.34,1.56,.64,1) .3s; }
.sum-inner { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
.sum-val   { font-family: 'Righteous', cursive; font-size: 26px; color: #1e3a8a; line-height: 1; }
.sum-unit  { font-size: 10px; font-weight: 900; color: #9ca3af; }
.sum-stats { display: flex; flex-direction: column; gap: 8px; flex: 1; }
.sum-stat        { display: flex; align-items: center; gap: 10px; border-radius: 10px; padding: 9px 12px; }
.sum-stat--green { background: #f0fdf4; border: 1.5px solid #bbf7d0; color: #15803d; }
.sum-stat--red   { background: #fff1f2; border: 1.5px solid #fecaca; color: #b91c1c; }
.sum-stat--blue  { background: #eff6ff; border: 1.5px solid #bfdbfe; color: #1d4ed8; }
.ss-val { font-family: 'Righteous', cursive; font-size: 16px; line-height: 1; }
.ss-lbl { font-size: 10.5px; font-weight: 800; opacity: .7; }

.sec-box   { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 16px; }
.sec-title { display: flex; align-items: center; gap: 7px; font-family: 'Righteous', cursive; font-size: 12.5px; color: #1e3a8a; margin-bottom: 12px; }
.ans-bars  { display: flex; flex-direction: column; gap: 7px; }
.ans-bar-row { display: flex; align-items: center; gap: 8px; }
.abr-n     { font-family: 'Righteous', cursive; font-size: 11px; color: #6b7280; width: 16px; text-align: center; flex-shrink: 0; }
.abr-track { flex: 1; height: 8px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
.abr-fill  { height: 100%; border-radius: 99px; width: 100%; }
.abr-ok    { background: linear-gradient(90deg,#4ade80,#10b981); }
.abr-err   { background: linear-gradient(90deg,#f87171,#ef4444); }

/* Comparison */
.delta-card { display: flex; align-items: center; gap: 14px; border-radius: 14px; padding: 14px 18px; border: 1.5px solid; }
.delta-up   { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.delta-down { background: #fff1f2; border-color: #fecaca; color: #b91c1c; }
.delta-same { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; }
.delta-ico  { width: 48px; height: 48px; border-radius: 12px; background: rgba(0,0,0,.06); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.delta-body { flex: 1; }
.delta-title{ font-family: 'Righteous', cursive; font-size: 16px; line-height: 1.25; }
.delta-sub  { font-size: 11.5px; font-weight: 700; opacity: .7; margin-top: 3px; }
.delta-badge{ font-family: 'Righteous', cursive; font-size: 24px; flex-shrink: 0; }
.cmp-bars   { display: flex; flex-direction: column; gap: 12px; }
.cmp-bar-row{ display: flex; align-items: center; gap: 10px; }
.cmp-lbl    { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 800; color: #374151; width: 70px; flex-shrink: 0; }
.cmp-dot    { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.cmp-dot--gray { background: #94a3b8; }
.cmp-dot--blue { background: #2563eb; }
.cmp-track  { flex: 1; height: 10px; border-radius: 99px; background: #e2e8f0; overflow: hidden; }
.cmp-fill   { height: 100%; border-radius: 99px; transition: width 1s cubic-bezier(.34,1.56,.64,1) .2s; }
.cmp-fill--gray { background: linear-gradient(90deg,#94a3b8,#64748b); }
.cmp-fill--blue { background: linear-gradient(90deg,#60a5fa,#1d4ed8); }
.cmp-num    { font-family: 'Righteous', cursive; font-size: 14px; color: #1e3a8a; width: 28px; text-align: right; flex-shrink: 0; }
.cmp-table  { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.cmp-table thead tr { background: #f1f5f9; }
.cmp-table th { padding: 7px 12px; font-size: 11px; font-weight: 900; color: #6b7280; text-align: center; letter-spacing: .3px; }
.cmp-table th:first-child { text-align: left; }
.th-post    { color: #2563eb; }
.cmp-table td { padding: 9px 12px; border-top: 1px solid #f1f5f9; }
.cmp-table tr:hover td { background: #f8fafc; }
.td-lbl  { font-weight: 800; color: #374151; }
.td-pre  { text-align: center; font-weight: 800; color: #64748b; }
.td-post { text-align: center; font-weight: 800; color: #1d4ed8; }

/* Review */
.review-list{ display: flex; flex-direction: column; gap: 12px; }
.review-item{ border: 1.5px solid; border-radius: 14px; overflow: hidden; }
.rv-ok  { border-color: #bbf7d0; }
.rv-err { border-color: #fecaca; }
.rv-head{ display: flex; align-items: flex-start; gap: 8px; padding: 11px 14px 8px; background: rgba(0,0,0,.02); }
.rv-num { min-width: 21px; height: 21px; border-radius: 50%; background: #3b82f6; color: #fff; font-weight: 900; font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.rv-q   { flex: 1; font-size: 12.5px; font-weight: 700; color: #1e3a8a; line-height: 1.55; }
.rv-opts{ display: flex; flex-direction: column; gap: 5px; padding: 8px 14px; }
.rv-opt { display: flex; align-items: center; gap: 8px; padding: 7px 10px; border-radius: 9px; border: 1.5px solid transparent; font-size: 12px; font-weight: 700; color: #374151; }
.rv-opt--correct{ background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.rv-opt--wrong  { background: #fff1f2; border-color: #fecaca; color: #b91c1c; }
.rv-lbl { width: 22px; height: 22px; border-radius: 6px; background: #e2e8f0; color: #64748b; display: flex; align-items: center; justify-content: center; font-family: 'Righteous', cursive; font-size: 10px; flex-shrink: 0; }
.rv-opt--correct .rv-lbl { background: #bbf7d0; color: #15803d; }
.rv-opt--wrong   .rv-lbl { background: #fecaca; color: #b91c1c; }
.rv-txt { flex: 1; }
.rv-feedback { display: flex; align-items: center; gap: 6px; padding: 8px 14px 11px; font-size: 11.5px; font-weight: 700; color: #6b7280; background: #f8fafc; font-style: italic; }

.modal-footer { padding: 12px 20px 16px; border-top: 1.5px solid #e2e8f0; display: flex; justify-content: flex-end; flex-shrink: 0; background: #fff; }

/* Modal transition */
.modal-in-enter-active { transition: opacity .3s ease, transform .38s cubic-bezier(.34,1.56,.64,1); }
.modal-in-leave-active { transition: opacity .22s ease, transform .22s ease; }
.modal-in-enter-from   { opacity: 0; transform: translateY(80px); }
.modal-in-leave-to     { opacity: 0; transform: translateY(60px); }

/* ═══════════════════════════════════════════
   MOBILE ≤ 820px
═══════════════════════════════════════════ */
@media (max-width: 820px) {
    .b1{width:300px;height:300px;} .b2{width:240px;height:240px;} .b3{width:180px;height:180px;}
    .c1{width:100px;height:100px;} .c2{display:none;} .r1{width:200px;height:200px;}
    .topbar { height: 52px; padding: 0 13px; }
    .brand-name { font-size: 16px; } .brand-dot { width: 25px; height: 25px; } .timer-val { font-size: 18px; }
    .body { grid-template-columns: 1fr; gap: 0; padding: 0; max-width: 100%; }
    .sidebar { opacity: 1 !important; transform: none !important; flex-direction: column; gap: 0; cursor: default; padding: 13px 15px 12px; background: rgba(29,78,216,.76); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(191,219,254,.22); }
    .sb-info { margin-bottom: 0; }
    .sb-chip { margin-bottom: 6px; font-size: 10px; } .sb-title { font-size: 15px; margin-bottom: 3px; } .sb-sub { font-size: 11.5px; margin-bottom: 10px; }
    .prog { margin-bottom: 0; } .sb-pills { flex-direction: row; flex-wrap: wrap; gap: 7px; } .sb-pill { padding: 6px 10px; font-size: 11.5px; border-radius: 8px; }
    .mascot-wrap { display: none; }
    .main { transform: none; opacity: 1; }
    .icard { border-radius: 0; border-left: none; border-right: none; box-shadow: 0 4px 0 #BFDBFE, 0 8px 20px rgba(37,99,235,.1); }
    .icard-head-inner { padding: 16px 15px 18px; } .icard-title { font-size: 18px; }
    .icard-stats { grid-template-columns: repeat(3,1fr); }
    .istat { padding: 15px 8px 13px; } .istat-icon { width: 38px; height: 38px; border-radius: 10px; } .istat-val { font-size: 20px; }
    .icard-body { padding: 14px 15px 18px; } .icard-instr-grid { grid-template-columns: 1fr; }
    .qcard { border-radius: 0; border-left: none; border-right: none; box-shadow: 0 4px 0 #BFDBFE, 0 8px 20px rgba(37,99,235,.1); }
    .qcard-head-inner { padding: 10px 14px; } .qcard-mission { font-size: 11.5px; } .qcard-counter-num { font-size: 17px; }
    .qcard-question { padding: 16px 14px 0; gap: 9px; } .q-text { font-size: 14px; }
    .qcard-opts { grid-template-columns: 1fr; gap: 8px; padding: 14px 14px 4px; } .qcard-hint { padding: 8px 14px 12px; }
    .dcard { border-radius: 0; border-left: none; border-right: none; }
    .footer-inner { padding: 0 15px; } .fbtn { height: 40px; }
    .modal-wrap { max-height: 96dvh; border-radius: 20px 20px 0 0; }
    .modal-header { padding: 16px 16px 14px; flex-wrap: wrap; gap: 10px; }
    .mhs-num { font-size: 34px; }
    .sum-hero { flex-direction: column; align-items: flex-start; gap: 12px; }
    .sum-stats { flex-direction: row; flex-wrap: wrap; } .sum-stat { flex: 1; min-width: 100px; }
    .modal-tabs { padding: 8px 12px 0; } .tab-btn { padding: 6px 10px; font-size: 11.5px; }
    .modal-body { padding: 14px 14px 8px; }
}

@media (max-width: 480px) {
    .topbar { height: 48px; padding: 0 11px; } .tbtn-lbl { display: none; } .tbtn { padding: 7px 9px; }
    .timer-val { font-size: 16px; } .timer { min-width: 105px; }
    .sidebar { padding: 11px 12px 10px; } .sb-title { font-size: 14px; }
    .icard-head-inner { padding: 14px 13px 16px; }
    .istat { padding: 12px 6px 10px; } .istat-icon { width: 34px; height: 34px; border-radius: 9px; } .istat-val { font-size: 18px; }
    .icard-body { padding: 12px 13px 16px; }
    .dcard-inner { padding: 32px 16px 28px; }
    .footer-inner { padding: 0 12px; gap: 7px; } .fbtn { height: 38px; padding: 0 13px; font-size: 12px; } .f-pos { font-size: 12px; }
    .modal-header { padding: 14px; } .modal-title { font-size: 15px; }
    .tab-btn { font-size: 11px; padding: 6px 9px; gap: 4px; } .modal-body { padding: 12px 12px 8px; }
}
</style>
