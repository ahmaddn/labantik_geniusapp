<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { router } from "@inertiajs/vue3";
import {
    ArrowLeft, ArrowRight, Home,
    Zap, Clock, Music2, VolumeX,
    CheckCircle2, BookOpen, Star,
    Trophy, Sparkles, ListChecks,
    Shield, Award, Rocket, Target, Flame,
    AlertTriangle, Flag, PartyPopper, Loader2,
} from "lucide-vue-next";

const props = defineProps({
    quiz:       Object,
    questions:  Array,
    mission:    Object,
    mascot:     Object,
    background: Object,
    attempt:    Object,
});

const mascotSrc = computed(() =>
    props.mascot?.image
        ? `/storage/${props.mascot.image}`
        : "/images/templates/pose_pikir.png"
);

// ── Phase ─────────────────────────────────────────────────────────
const phase      = ref("intro");
const ready      = ref(false);
const brandMoved = ref(false); // false=center(intro), true=left(quiz/done)

// ── Timer ─────────────────────────────────────────────────────────
const timeLimit  = computed(() => (props.quiz?.time_limit ?? 10) * 60);
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
        audioRef.value.addEventListener("error", () => {
            audioRef.value = null;
            musicOn.value  = false;
        });
    }
    if (musicOn.value) {
        audioRef.value.pause();
        musicOn.value = false;
    } else {
        try { await audioRef.value.play(); musicOn.value = true; }
        catch   { musicOn.value = false; }
    }
};

// ── Mascot bubble ─────────────────────────────────────────────────
const BUBBLES = [
    "Semangat Yah! 💪", "Baca dengan teliti 👀",
    "Awas, Ada jebakan!", "Pikirkan baik-baik ",
    "Hampir selesai?", "Fokus ya! 🎯",
    "Hati-hati! Jangan salah pilih",
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

const currentQ    = computed(() => props.questions?.[currentIdx.value] ?? null);
const totalQ      = computed(() => props.questions?.length ?? 0);
const answeredCnt = computed(() => Object.keys(answers.value).length);
const progressPct = computed(() =>
    totalQ.value === 0 ? 100 : Math.round((answeredCnt.value / totalQ.value) * 100)
);

const isFirst   = computed(() => currentIdx.value === 0);
const isLast    = computed(() => currentIdx.value === totalQ.value - 1);
const canGoNext = computed(() => !!answers.value[currentQ.value?.id]);

function selectOption(optId) {
    answers.value[currentQ.value.id] = optId;
    selectedAnim.value = optId;
    setTimeout(() => { selectedAnim.value = null; }, 500);
}
function isSelected(optId) { return answers.value[currentQ.value?.id] === optId; }

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
    phase.value = "done";

    const payload = {
        quiz_id:    props.quiz?.id,
        time_taken: timeLimit.value - remaining.value,
        answers:    Object.entries(answers.value).map(([question_id, selected_option_id]) => ({
            question_id, selected_option_id,
        })),
    };
    router.post(route("playground.pretest.submit"), payload, {
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

        <!-- BG -->
        <div class="bg"><div class="bg-dim"></div></div>

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">

            <!-- Tombol Kembali — fixed kiri -->
            <button class="t-btn t-btn--back" @click="goBack" :disabled="submitting">
                <ArrowLeft :size="15" :stroke-width="2.6"/>
                <span>Kembali</span>
            </button>

            <!-- Brand: di tengah saat intro, slide ke kiri saat quiz/done -->
            <div class="brand" :class="{ 'brand--moved': brandMoved, 'brand--hidden': brandMoved }">
                <div class="brand-dot">
                    <Zap :size="13" color="#fff" fill="white" :stroke-width="2.5"/>
                </div>
                <span class="brand-name">Geniuss</span>
            </div>

            <!-- Timer: muncul di tengah saat phase === quiz -->
            <Transition name="timer-in">
                <div v-if="phase === 'quiz'"
                    class="topbar-timer"
                    :class="{ 'topbar-timer--warn': timerWarning }">
                    <div class="topbar-timer-inner">
                        <Clock :size="13" :stroke-width="2.5"/>
                        <span class="topbar-timer-val">{{ timerDisplay }}</span>
                    </div>
                    <div class="topbar-timer-track">
                        <div class="topbar-timer-fill"
                            :class="{ 'topbar-timer-fill--warn': timerWarning }"
                            :style="{ width: timerPct + '%' }">
                        </div>
                    </div>
                </div>
            </Transition>

            <!-- RIGHT: musik + home -->
            <div class="topbar-right">
                <button class="t-btn t-btn--sq music-btn"
                    :class="{ 'music-btn--on': musicOn }"
                    @click="toggleMusic"
                    :title="musicOn ? 'Matikan musik' : 'Nyalakan musik'">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2.2"/>
                    <VolumeX v-else        :size="15" :stroke-width="2.2"/>
                </button>
                <button class="t-btn t-btn--sq" @click="goHome">
                    <Home :size="15" :stroke-width="2.6"/>
                </button>
            </div>

        </header>

        <!-- ══ BODY ══ -->
        <div class="body" :class="{ 'body--on': ready }">

            <!-- ─── SIDEBAR ─── -->
            <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
                <div class="sb-info">

                    <span class="activity-tag">
                        <Target v-if="phase === 'intro'" :size="11" :stroke-width="2.5"/>
                        <Clock  v-else-if="phase === 'quiz'" :size="11" :stroke-width="2.5"/>
                        <Trophy v-else :size="11" :stroke-width="2.5"/>
                        {{ phase === 'intro' ? 'Pretest' : phase === 'quiz' ? 'Mengerjakan' : 'Selesai' }}
                    </span>

                    <h1 class="sb-title">{{ mission?.name ?? 'Misi Belajar' }}</h1>
                    <p class="sb-sub">{{ quiz?.title ?? 'Tantangan Awal' }}</p>

                    <!-- Progress (quiz only) -->
                    <div class="prog" v-if="phase === 'quiz'">
                        <div class="prog-meta">
                            <span class="prog-lbl">Progress</span>
                            <span class="prog-val">
                                <b>{{ answeredCnt }}</b>
                                <span class="prog-of"> / {{ totalQ }}</span>
                            </span>
                        </div>
                        <div class="prog-bar">
                            <div class="prog-fill" :style="{ width: progressPct + '%' }">
                                <span class="prog-glow"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta cards (intro/done) -->
                    <div v-if="phase !== 'quiz'" class="sb-meta-cards">
                        <div class="sb-meta-item">
                            <BookOpen :size="14" :stroke-width="2"/>
                            <span>{{ totalQ }} Soal</span>
                        </div>
                        <div class="sb-meta-item">
                            <Clock :size="14" :stroke-width="2"/>
                            <span>{{ quiz?.time_limit ?? 10 }} Menit</span>
                        </div>
                    </div>



                </div>

                <!-- Mascot + bubble -->
                <div class="mascot-wrap">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble">
                            <span>{{ BUBBLES[bubbleIdx] }}</span>
                            <i class="bbl-tail-out"></i>
                            <i class="bbl-tail-in"></i>
                        </div>
                    </Transition>
                    <div class="mascot-shadow"></div>
                    <img :src="mascotSrc" alt="Maskot" class="mascot"/>
                </div>
            </aside>

            <!-- ─── GAME PANEL ─── -->
            <section class="game" :class="{ 'game--on': ready }">

                <!-- INTRO -->
                <Transition name="fx-phase">
                <div v-if="phase === 'intro'" class="card">
                    <div class="card-bar card-bar--blue"></div>
                    <div class="card-body">
                        <div class="card-head">
                            <div class="module-label">
                                <span class="module-tag"><Shield :size="13" :stroke-width="2.2"/>Pretest</span>
                                <h2 class="module-name">{{ mission?.name ?? 'Modul Belajar' }}</h2>
                            </div>
                            <span class="badge bdg-orange">
                                <Flame :size="10"/>{{ quiz?.title ?? 'Tantangan Awal' }}
                            </span>
                        </div>
                        <div class="divider"></div>
                        <p class="intro-desc">{{ quiz?.description ?? 'Jawab semua soal berikut dengan sebaik-baiknya untuk mengukur pemahamanmu!' }}</p>
                        <div class="stats-row">
                            <div class="stat">
                                <div class="stat-ico si-blue"><BookOpen :size="16" :stroke-width="2"/></div>
                                <div><div class="stat-v">{{ totalQ }}</div><div class="stat-l">Soal</div></div>
                            </div>
                            <div class="stat-sep"></div>
                            <div class="stat">
                                <div class="stat-ico si-purple"><Clock :size="16" :stroke-width="2"/></div>
                                <div><div class="stat-v">{{ quiz?.time_limit ?? 10 }}</div><div class="stat-l">Menit</div></div>
                            </div>
                            <div class="stat-sep"></div>
                            <div class="stat">
                                <div class="stat-ico si-yellow"><Award :size="16" :stroke-width="2"/></div>
                                <div><div class="stat-v">XP</div><div class="stat-l">Hadiah</div></div>
                            </div>
                        </div>
                        <div class="instr">
                            <div class="instr-hd"><ListChecks :size="14" :stroke-width="2.2"/>Petunjuk Pengerjaan</div>
                            <div class="instr-grid">
                                <div class="instr-row" v-for="(t, i) in [
                                    'Baca setiap soal dengan teliti.',
                                    'Pilih satu jawaban paling tepat.',
                                    'Kerjakan sesuai waktu yang ada.',
                                    'Klik Berikutnya setelah memilih.',
                                ]" :key="i">
                                    <span class="instr-n">0{{ i + 1 }}</span>
                                    <span>{{ t }}</span>
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
                                <span class="module-tag"><BookOpen :size="13" :stroke-width="2.2"/>Pilihan Ganda</span>
                                <h2 class="module-name">{{ mission?.name ?? 'Modul Belajar' }}</h2>
                            </div>
                            <span class="card-type-pill">Soal {{ currentIdx + 1 }} / {{ totalQ }}</span>
                        </div>
                        <div class="divider"></div>
                        <div class="questions-area">
                            <div class="question-item" :class="{ 'question-item--done': canGoNext }">
                                <div class="q-label">
                                    <span class="q-num">{{ currentIdx + 1 }}</span>
                                    <span class="q-text">{{ currentQ.question_text }}</span>
                                    <CheckCircle2 v-if="canGoNext" :size="15" color="#10b981" :stroke-width="2.5"/>
                                </div>
                                <img v-if="currentQ.image"
                                    :src="`/storage/${currentQ.image}`"
                                    alt="Gambar soal" class="q-img"/>
                                <div class="opts" :class="{ 'opts--shake': shakeActive }">
                                    <button
                                        v-for="(opt, i) in currentQ.options" :key="opt.id"
                                        class="opt"
                                        :class="{ 'opt--sel': isSelected(opt.id), 'opt--pop': selectedAnim === opt.id }"
                                        @click="selectOption(opt.id)">
                                        <span class="opt-lbl">{{ OPT_LABELS[i] }}</span>
                                        <span class="opt-txt">{{ opt.option_text }}</span>
                                        <CheckCircle2 :size="15" :stroke-width="2.5" class="opt-chk"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </Transition>

                <!-- DONE -->
                <Transition name="fx-phase">
                <div v-if="phase === 'done'" class="card">
                    <div class="card-bar card-bar--gold"></div>
                    <div class="card-body done-body">
                        <div class="trophy-wrap">
                            <div class="trophy-ring">
                                <Trophy :size="52" color="#f59e0b" :stroke-width="1.6"/>
                            </div>
                            <span class="sp sp1"><Sparkles :size="14" color="#60a5fa"/></span>
                            <span class="sp sp2"><Star :size="12" color="#f59e0b" fill="#f59e0b"/></span>
                            <span class="sp sp3"><Sparkles :size="11" color="#a78bfa"/></span>
                        </div>
                        <h2 class="done-ttl">
                            Tantangan Selesai!
                            <PartyPopper class="done-ttl-icon" :size="26" color="#f59e0b" :stroke-width="1.8"/>
                        </h2>
                        <p class="done-sub">Kamu telah menyelesaikan pretest dengan luar biasa.<br/>Lanjutkan petualangan belajarmu sekarang!</p>
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
                    <button class="f-btn f-btn--finish" @click="startQuiz">
                        <Rocket :size="15" :stroke-width="2"/>
                        <span>Mulai Sekarang</span>
                        <ArrowRight :size="15" :stroke-width="2.6"/>
                    </button>
                </template>

                <template v-if="phase === 'quiz' && currentQ">
                    <button class="f-btn f-btn--prev" @click="goPrev" :disabled="isFirst || submitting">
                        <ArrowLeft :size="15" :stroke-width="2.6"/>
                        <span>Sebelumnya</span>
                    </button>
                    <span class="footer-pos">{{ currentIdx + 1 }} / {{ totalQ }}</span>
                    <button class="f-btn"
                        :class="isLast ? 'f-btn--finish' : 'f-btn--next'"
                        :disabled="submitting" @click="goNext">
                        <template v-if="!isLast">
                            <span>Berikutnya</span>
                            <ArrowRight :size="15" :stroke-width="2.6"/>
                        </template>
                        <template v-else>
                            <template v-if="!submitting">
                                <Flag :size="14" :stroke-width="2.2"/>
                                <span>Selesaikan Pretest</span>
                            </template>
                            <template v-else>
                                <Loader2 :size="14" :stroke-width="2.2" class="spin"/>
                                <span>Menyimpan…</span>
                            </template>
                        </template>
                    </button>
                </template>

                <template v-if="phase === 'done'">
                    <div class="footer-spacer"></div>
                    <button class="f-btn f-btn--finish" @click="goToAdventure">
                        <Rocket :size="15" :stroke-width="2"/>
                        <span>Lanjut ke Peta</span>
                        <ArrowRight :size="15" :stroke-width="2.6"/>
                    </button>
                </template>

            </div>
            <p v-if="phase === 'quiz' && !canGoNext" class="footer-hint">
                <AlertTriangle :size="12" :stroke-width="2.4"/>
                Pilih jawaban terlebih dahulu untuk melanjutkan
            </p>
        </footer>

    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ══ ROOT ══ */
.root {
    min-height: 100dvh; display: flex; flex-direction: column;
    font-family: 'Nunito', sans-serif; position: relative; overflow-x: hidden;
}

/* ══ BG ══ */
.bg { position: fixed; inset: 0; z-index: 0; }
.bg-dim {
    position: absolute; inset: 0;
    background: url('/images/templates/background-pretest.png') top center / cover no-repeat fixed;
}

/* ══ TOPBAR ══ */
.topbar {
    position: relative; z-index: 50;
    height: 58px; flex-shrink: 0;
    display: flex; align-items: center;
    padding: 0 18px; gap: 0;
    background: rgba(255,255,255,.16);
    backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8);
    border-bottom: 1px solid rgba(255,255,255,.38);
    box-shadow: 0 1px 0 rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
    overflow: hidden;
}

/* Kembali button */
.t-btn--back { flex-shrink: 0; margin-right: 10px; z-index: 3; }

/* Brand: centered by default (intro), slides left on quiz */
.brand {
    display: flex; align-items: center; gap: 9px;
    position: absolute; left: 50%; transform: translateX(-50%);
    transition: left .45s cubic-bezier(.34,1.56,.64,1), transform .45s cubic-bezier(.34,1.56,.64,1);
    z-index: 2; pointer-events: none;
}
.brand--moved {
    left: 128px;
    transform: translateX(0);
}
.brand-dot {
    width: 30px; height: 30px; border-radius: 9px;
    background: linear-gradient(140deg,#60a5fa,#1d4ed8);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(29,78,216,.4); flex-shrink: 0;
}
.brand-name {
    font-family: 'Righteous', cursive; font-size: 19px;
    color: #fff; letter-spacing: -.1px;
    text-shadow: 0 1px 8px rgba(0,0,0,.2); white-space: nowrap;
}

/* Timer — centered absolute, appears on quiz */
.topbar-timer {
    position: absolute; left: 50%; transform: translateX(-50%);
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    min-width: 155px; z-index: 2; pointer-events: none;
}
.topbar-timer-inner {
    display: flex; align-items: center; gap: 6px;
    color: #fff; text-shadow: 0 1px 5px rgba(0,0,0,.2);
}
.topbar-timer-val {
    font-family: 'Righteous', cursive; font-size: 22px;
    letter-spacing: 1px; line-height: 1;
}
.topbar-timer--warn .topbar-timer-inner { color: #fca5a5; }
.topbar-timer--warn .topbar-timer-val   { animation: pulse-warn 1s ease-in-out infinite; }
@keyframes pulse-warn { 0%,100%{opacity:1} 50%{opacity:.6} }
.topbar-timer-track {
    width: 100%; height: 5px; border-radius: 99px;
    background: rgba(255,255,255,.22); overflow: hidden;
}
.topbar-timer-fill {
    height: 100%; border-radius: 99px;
    background: linear-gradient(90deg,#60a5fa,#1d4ed8);
    transition: width .9s linear; box-shadow: 0 0 8px rgba(96,165,250,.6);
}
.topbar-timer-fill--warn {
    background: linear-gradient(90deg,#f87171,#ef4444);
    box-shadow: 0 0 8px rgba(239,68,68,.6);
}

/* Timer transition */
.timer-in-enter-active { transition: opacity .4s ease .3s, transform .44s cubic-bezier(.34,1.56,.64,1) .3s; }
.timer-in-leave-active { transition: opacity .2s ease; }
.timer-in-enter-from   { opacity: 0; transform: translateX(-50%) translateY(8px) scale(.85); }
.timer-in-leave-to     { opacity: 0; }

/* RIGHT buttons */
.topbar-right {
    display: flex; align-items: center; gap: 8px;
    margin-left: auto; flex-shrink: 0; z-index: 3;
}

/* ── Common buttons ── */
.t-btn {
    display: flex; align-items: center; gap: 6px; padding: 7px 14px;
    background: rgba(255,255,255,.18); backdrop-filter: blur(8px);
    border: 1.5px solid rgba(255,255,255,.38); border-radius: 10px;
    font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; color: #fff;
    cursor: pointer; transition: all .2s;
    text-shadow: 0 1px 4px rgba(0,0,0,.15); white-space: nowrap;
}
.t-btn:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.t-btn:disabled { opacity: .45; cursor: not-allowed; }
.t-btn--sq { padding: 7px 11px; }
.music-btn--on {
    background: linear-gradient(135deg,#60a5fa,#1d4ed8);
    border-color: rgba(255,255,255,.5); box-shadow: 0 4px 12px rgba(29,78,216,.3);
}

/* ══ BODY ══ */
.body {
    position: relative; z-index: 10; flex: 1;
    display: grid; grid-template-columns: 272px 1fr; gap: 22px;
    max-width: 1080px; width: 100%; margin: 0 auto;
    padding: 24px 20px 20px; align-items: start;
    opacity: 0; transition: opacity .5s ease;
}
.body--on { opacity: 1; }

/* ══ SIDEBAR ══ */
.sidebar {
    display: flex; flex-direction: column;
    opacity: 0; transform: translateX(-22px);
    transition: opacity .6s ease, transform .6s cubic-bezier(.34,1.56,.64,1);
    cursor: pointer; user-select: none;
}
.sidebar--on { opacity: 1; transform: none; }
.sb-info { margin-bottom: 18px; }

.activity-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(29,78,216,.22); border: 1.5px solid rgba(255,255,255,.26);
    border-radius: 999px; padding: 4px 13px;
    font-size: 10.5px; font-weight: 800; color: #fff; letter-spacing: .3px;
    backdrop-filter: blur(8px); margin-bottom: 10px;
}
.sb-title {
    font-family: 'Righteous', cursive; font-size: clamp(17px,2vw,24px);
    color: #fff; line-height: 1.22; text-shadow: 0 2px 16px rgba(0,0,0,.22); margin-bottom: 5px;
}
.sb-sub {
    font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.75);
    line-height: 1.6; text-shadow: 0 1px 5px rgba(0,0,0,.14); margin-bottom: 16px;
}

.prog { width: 100%; margin-bottom: 4px; }
.prog-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.prog-lbl { font-size: 10px; font-weight: 900; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .6px; }
.prog-val { font-family: 'Righteous', cursive; font-size: 15px; color: #fff; display: flex; align-items: center; gap: 3px; }
.prog-val b { font-size: 17px; }
.prog-of { color: rgba(255,255,255,.5); font-size: 12px; }
.prog-bar { height: 9px; border-radius: 99px; background: rgba(255,255,255,.18); overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,.14); }
.prog-fill {
    height: 100%; border-radius: 99px; position: relative; overflow: hidden;
    background: linear-gradient(90deg,#60a5fa,#1d4ed8);
    transition: width .5s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 0 14px rgba(96,165,250,.55);
}
.prog-glow {
    position: absolute; inset: 0;
    background: linear-gradient(90deg,transparent 0%,rgba(255,255,255,.38) 50%,transparent 100%);
    animation: shine 2.4s ease-in-out infinite;
}
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }

.sb-meta-cards { display: flex; flex-direction: column; gap: 8px; margin-bottom: 4px; }
.sb-meta-item {
    display: flex; align-items: center; gap: 8px;
    background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22);
    border-radius: 10px; padding: 8px 12px;
    font-size: 12px; font-weight: 800; color: rgba(255,255,255,.9);
    backdrop-filter: blur(6px);
}

.step-dots { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 14px; }
.step-dot {
    min-width: 27px; height: 27px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.35); background: rgba(255,255,255,.15);
    font-size: 10.5px; font-weight: 900; color: rgba(255,255,255,.8);
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; transition: all .2s cubic-bezier(.34,1.56,.64,1); backdrop-filter: blur(4px);
}
.step-dot:hover { transform: scale(1.15); }
.step-dot.active { background: #fff; border-color: #fff; color: #1d4ed8; box-shadow: 0 3px 10px rgba(0,0,0,.2); transform: scale(1.15); }
.step-dot.done   { background: rgba(74,222,128,.3); border-color: #4ade80; color: #fff; }

/* Mascot */
.mascot-wrap { position: relative; margin-left: 6px; }
.bubble {
    position: relative; background: #fff; border: 2.5px solid #93c5fd;
    border-radius: 18px; padding: 9px 15px; min-width: 150px; max-width: 210px;
    box-shadow: 0 8px 28px rgba(29,78,216,.13), inset 0 1px 0 rgba(255,255,255,.9);
    margin-bottom: 8px; animation: float 3.5s ease-in-out infinite;
}
.bubble span { font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block; }
.bbl-tail-out, .bbl-tail-in { position: absolute; width: 0; height: 0; font-style: normal; }
.bbl-tail-out { bottom: -16px; left: 18px; border-left: 11px solid transparent; border-right: 7px solid transparent; border-top: 15px solid #93c5fd; }
.bbl-tail-in  { bottom: -12px; left: 19px; border-left: 9px solid transparent; border-right: 6px solid transparent; border-top: 12px solid #fff; }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
.bbl-enter-active { transition: opacity .3s ease, transform .36s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .2s ease, transform .2s ease; }
.bbl-enter-from   { opacity: 0; transform: translateY(10px) scale(.9); }
.bbl-leave-to     { opacity: 0; transform: translateY(-7px) scale(.94); }
.mascot-shadow {
    position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
    width: 145px; height: 28px;
    background: radial-gradient(ellipse,rgba(0,0,0,.18) 0%,transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.mascot {
    position: relative; z-index: 2; width: clamp(140px,15vw,200px); height: auto; display: block;
    filter: drop-shadow(0 10px 26px rgba(0,0,0,.2));
    animation: bob 3.5s ease-in-out infinite; transform-origin: bottom center;
}
@keyframes bob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-9px) rotate(.6deg)} 70%{transform:translateY(-5px) rotate(-.4deg)} }

/* ══ GAME PANEL ══ */
.game { opacity: 0; transform: translateY(22px); transition: opacity .6s .12s ease, transform .6s .12s cubic-bezier(.34,1.56,.64,1); }
.game--on { opacity: 1; transform: none; }

/* ══ CARD ══ */
.card {
    background: rgba(255,255,255,.93);
    backdrop-filter: blur(28px) saturate(1.6); -webkit-backdrop-filter: blur(28px) saturate(1.6);
    border-radius: 22px; overflow: hidden;
    border: 1.5px solid rgba(255,255,255,.85);
    box-shadow: 0 20px 56px rgba(0,0,0,.12), 0 4px 0 rgba(29,78,216,.07), inset 0 1px 0 #fff;
}
.card-bar { height: 4px; }
.card-bar--blue { background: linear-gradient(90deg,#3b82f6,#8b5cf6,#06b6d4); }
.card-bar--gold { background: linear-gradient(90deg,#f59e0b,#f97316,#ef4444); }
.card-body { padding: 20px 22px 24px; }

.card-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 13px; flex-wrap: wrap; gap: 8px;
}
.module-label { display: flex; flex-direction: column; gap: 3px; }
.module-tag {
    display: inline-flex; align-items: center; gap: 5px;
    background: linear-gradient(135deg,#eff6ff,#dbeafe);
    border: 1.5px solid #bfdbfe; border-radius: 50px;
    padding: 3px 10px; font-size: 10px; font-weight: 900;
    color: #1d4ed8; letter-spacing: .4px; width: fit-content;
}
.module-name { font-family: 'Righteous', cursive; font-size: 18px; color: #1e3a8a; line-height: 1.2; }
.card-type-pill {
    font-size: 11px; font-weight: 900; border-radius: 99px;
    padding: 4px 12px; background: #dbeafe; color: #2563eb; white-space: nowrap;
}
.divider { height: 1px; background: linear-gradient(90deg,transparent,rgba(29,78,216,.1),transparent); margin-bottom: 16px; }
.badge { display: inline-flex; align-items: center; gap: 5px; border-radius: 50px; padding: 4px 11px; font-size: 10.5px; font-weight: 900; white-space: nowrap; }
.bdg-orange { background: #fff7ed; border: 1.5px solid #fed7aa; color: #c2410c; }

.intro-desc { font-size: 13px; font-weight: 700; color: #6b7280; line-height: 1.65; margin-bottom: 16px; }
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
.q-num { min-width: 21px; height: 21px; border-radius: 50%; background: #3b82f6; color: #fff; font-weight: 900; font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.q-text { flex: 1; font-size: 13.5px; font-weight: 700; color: #1e3a8a; line-height: 1.6; }
.q-img { max-width: 280px; height: auto; border-radius: 10px; border: 2px solid #bfdbfe; object-fit: cover; margin-bottom: 10px; }

.opts { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
.opts--shake { animation: shake .5s ease; }
@keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-7px)} 40%{transform:translateX(7px)} 60%{transform:translateX(-4px)} 80%{transform:translateX(4px)} }
.opt {
    display: flex; align-items: center; gap: 10px; padding: 11px 13px;
    border: 2px solid #e5e7eb; border-radius: 11px; background: #fff;
    font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 700; color: #374151;
    cursor: pointer; text-align: left; position: relative; overflow: hidden; width: 100%;
    transition: all .2s cubic-bezier(.34,1.56,.64,1);
}
.opt::before { content: ''; position: absolute; inset: 0; background: linear-gradient(135deg,#eff6ff,#dbeafe); opacity: 0; transition: opacity .2s; }
.opt:hover { border-color: #93c5fd; transform: translateY(-2px); box-shadow: 0 5px 14px rgba(29,78,216,.1); }
.opt:hover::before { opacity: 1; }
.opt--sel { border-color: #2563eb; background: linear-gradient(135deg,#eff6ff,#dbeafe); color: #1e3a8a; box-shadow: 0 0 0 3px rgba(37,99,235,.14), 0 4px 14px rgba(29,78,216,.15); transform: translateY(-2px); }
.opt--pop { animation: opt-pop .35s cubic-bezier(.34,1.56,.64,1); }
@keyframes opt-pop { 0%{transform:scale(1)} 40%{transform:scale(1.05)} 100%{transform:scale(1) translateY(-2px)} }
.opt-lbl { width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0; background: linear-gradient(135deg,#60a5fa,#1d4ed8); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Righteous', cursive; font-size: 12px; box-shadow: 0 2px 6px rgba(29,78,216,.28); position: relative; z-index: 1; }
.opt--sel .opt-lbl { background: linear-gradient(135deg,#1d4ed8,#1e3a8a); }
.opt-txt { flex: 1; position: relative; z-index: 1; }
.opt-chk { color: #2563eb; flex-shrink: 0; opacity: 0; transform: scale(0) rotate(-30deg); position: relative; z-index: 1; transition: all .25s cubic-bezier(.34,1.56,.64,1); }
.opt--sel .opt-chk { opacity: 1; transform: scale(1) rotate(0); }

.done-body { display: flex; flex-direction: column; align-items: center; gap: 16px; text-align: center; padding: 44px 32px 40px; }
.trophy-wrap { position: relative; width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; }
.trophy-ring { width: 90px; height: 90px; border-radius: 50%; background: linear-gradient(135deg,#fffbeb,#fef3c7); border: 3px solid #fde68a; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 30px rgba(245,158,11,.28); animation: trophy-pop .6s cubic-bezier(.34,1.56,.64,1) both .1s; }
@keyframes trophy-pop { from{transform:scale(0) rotate(-20deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1} }
.sp { position: absolute; animation: sp-float 2.2s ease-in-out infinite; }
.sp1{top:2px;right:6px} .sp2{bottom:6px;left:2px;animation-delay:.55s} .sp3{top:10px;left:0;animation-delay:1.1s}
@keyframes sp-float { 0%,100%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-9px) rotate(18deg)} }
.done-ttl { font-family: 'Righteous', cursive; font-size: clamp(20px,3vw,26px); color: #1e3a8a; line-height: 1.2; display: flex; align-items: center; gap: 8px; }
.done-ttl-icon { flex-shrink: 0; }
.done-sub { font-size: 13.5px; font-weight: 700; color: #6b7280; line-height: 1.7; }

.fx-phase-enter-active { transition: opacity .4s ease, transform .44s cubic-bezier(.34,1.56,.64,1); }
.fx-phase-leave-active { transition: opacity .22s ease, transform .22s ease; position: absolute; width: 100%; }
.fx-phase-enter-from   { opacity: 0; transform: translateY(22px) scale(.97); }
.fx-phase-leave-to     { opacity: 0; transform: translateY(-14px) scale(.98); }

/* ══ FOOTER ══ */
.footer {
    position: relative; z-index: 50;
    background: rgba(255,255,255,.18);
    backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8);
    border-top: 1px solid rgba(255,255,255,.38);
    box-shadow: 0 -1px 0 rgba(0,0,0,.04), 0 -4px 16px rgba(0,0,0,.04);
    padding: 11px 0 8px; flex-shrink: 0;
}
.footer-inner { display: flex; align-items: center; gap: 10px; max-width: 1080px; margin: 0 auto; padding: 0 22px; }
.footer-spacer { flex: 1; }
.footer-pos { font-family: 'Righteous', cursive; font-size: 14px; color: #fff; flex: 1; text-align: center; text-shadow: 0 1px 4px rgba(0,0,0,.15); }
.footer-hint { display: flex; align-items: center; justify-content: center; gap: 5px; text-align: center; font-size: 11px; font-weight: 800; color: #fde68a; padding: 5px 0 2px; text-shadow: 0 1px 4px rgba(0,0,0,.15); }
@keyframes spin { to { transform: rotate(360deg); } }
.spin { animation: spin .8s linear infinite; }
.f-btn { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border: none; border-radius: 11px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; cursor: pointer; flex-shrink: 0; transition: all .18s cubic-bezier(.34,1.56,.64,1); white-space: nowrap; }
.f-btn--prev { background: rgba(255,255,255,.18); color: #fff; border: 1.5px solid rgba(255,255,255,.38); backdrop-filter: blur(8px); }
.f-btn--prev:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.f-btn--prev:disabled { opacity: .4; cursor: not-allowed; }
.f-btn--next { background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; box-shadow: 0 4px 14px rgba(29,78,216,.38); }
.f-btn--next:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(29,78,216,.48); }
.f-btn--next:disabled { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.45) !important; box-shadow: none !important; cursor: not-allowed; }
.f-btn--finish { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.38); padding: 0 22px; }
.f-btn--finish:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(16,185,129,.48); }
.f-btn--finish:disabled { opacity: .5; cursor: not-allowed; }

/* ══════════════════════════════════════════════
   MOBILE ≤ 820px
══════════════════════════════════════════════ */
@media (max-width: 820px) {
    /* Topbar */
    .topbar { height: 54px; padding: 0 14px; }
    .brand-name { font-size: 16px; }
    .brand-dot  { width: 28px; height: 28px; }
    .topbar-timer-val { font-size: 19px; }
    .topbar-timer { min-width: 130px; }

    /* Mobile: brand fades out when moved, does NOT slide to left */
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

    /* Body: single column */
    .body { grid-template-columns: 1fr; gap: 0; padding: 0; max-width: 100%; }

    /* Sidebar: clean vertical strip */
    .sidebar {
        flex-direction: column; gap: 0;
        opacity: 1 !important; transform: none !important;
        padding: 14px 16px 12px;
        background: rgba(15,23,42,.28);
        backdrop-filter: blur(18px); -webkit-backdrop-filter: blur(18px);
        cursor: default;
        border-bottom: 1px solid rgba(255,255,255,.12);
    }
    .sb-info { margin-bottom: 0; display: flex; flex-direction: column; gap: 8px; }

    /* Title row */
    .activity-tag { margin-bottom: 0; font-size: 10px; padding: 3px 10px; }
    .sb-title { font-size: 15px; margin-bottom: 0; }
    .sb-sub   { font-size: 11.5px; margin-bottom: 0; opacity: .85; }

    /* Progress bar */
    .prog        { margin-bottom: 0; }
    .prog-meta   { margin-bottom: 5px; }
    .prog-lbl    { font-size: 9.5px; }
    .prog-val    { font-size: 13px; }
    .prog-val b  { font-size: 15px; }
    .prog-bar    { height: 7px; }

    /* Step dots */
    .step-dots {
        flex-wrap: wrap; gap: 5px; margin-top: 6px; overflow-x: auto;
        padding-bottom: 2px;
    }
    .step-dot { min-width: 26px; height: 26px; font-size: 10px; }

    /* Meta cards: horizontal */
    .sb-meta-cards { flex-direction: row; flex-wrap: wrap; gap: 8px; margin-bottom: 0; }
    .sb-meta-item  { padding: 6px 10px; font-size: 11.5px; border-radius: 8px; }

    /* Hide mascot */
    .mascot-wrap, .mascot-shadow { display: none; }

    /* Game card */
    .game { transform: none; opacity: 1; }
    .card { border-radius: 0; border-left: none; border-right: none; border-top: none; }
    .card-body { padding: 16px 15px 22px; }

    /* Options: single column */
    .opts { grid-template-columns: 1fr; gap: 8px; }

    /* Instructions: single column */
    .instr-grid { grid-template-columns: 1fr; gap: 7px; }

    /* Done */
    .done-body { padding: 34px 20px 30px; }

    /* Footer */
    .footer-inner { padding: 0 16px; gap: 8px; }
    .f-btn        { height: 42px; }
    .f-btn--finish { padding: 0 20px; }
    .footer-hint { font-size: 11px; }
}

/* ══ EXTRA SMALL ≤ 480px ══ */
@media (max-width: 480px) {
    .topbar { height: 50px; padding: 0 12px; }
    .brand-name { font-size: 15px; }
    .brand-dot  { width: 25px; height: 25px; border-radius: 7px; }
    .brand--moved { left: 106px; }
    .topbar-timer-val { font-size: 17px; }
    .topbar-timer { min-width: 115px; }
    .t-btn span { display: none; }
    .t-btn { padding: 6px 10px; }

    .sidebar { padding: 11px 13px 10px; }
    .sb-title { font-size: 14px; }

    .card-body  { padding: 13px 12px 18px; }
    .module-name { font-size: 15px; }
    .stats-row  { padding: 11px 12px; }
    .stat-v     { font-size: 18px; }

    .footer-inner { padding: 0 12px; gap: 7px; }
    .f-btn        { height: 38px; padding: 0 12px; font-size: 12px; }
    .f-btn--finish { padding: 0 13px; }
    .footer-pos   { font-size: 12px; }
}
</style>
