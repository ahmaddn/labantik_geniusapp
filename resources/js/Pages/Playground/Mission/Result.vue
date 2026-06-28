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
import { useSfx } from "@/Composable/useSfx";

const props = defineProps({
    mission: { type: Object, required: true },
    results: { type: Object, required: true },
    user: { type: Object, default: () => ({ name: "Siswa" }) },
    module: { type: Object, default: () => ({ id: null, name: "Modul" }) },
    all_missions_done: { type: Boolean, default: false },
    next_mission: { type: Object, default: null },
    is_overall: { type: Boolean, default: false },
    is_pretest: { type: Boolean, default: false },
    is_posttest: { type: Boolean, default: false },
});

const { playPop, playSuccess } = useSfx();

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

const showDetails = ref(true);
const animatedScore = ref(0);
const confettiCanvas = ref(null);
const speechTriggered = ref(false);

const formatTime = (sec) => {
    if (!sec || sec < 0) return "0s";
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    if (m > 0) return `${m}m ${s}s`;
    return `${s}s`;
};

const score = computed(() => {
    if (props.is_overall) {
        return Math.round(props.results.overall_score || props.results.score || 0);
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
        return props.results.overall_correct || props.results.correct_answers || 0;
    }
    return props.results.correct_answers || 0;
});

const incorrectCount = computed(() => {
    if (props.is_overall) {
        const total = props.results.overall_total || props.results.total_questions || 0;
        return total - (props.results.overall_correct || props.results.correct_answers || 0);
    }
    const total = props.results.total_questions || 0;
    return total - (props.results.correct_answers || 0);
});

// Breakdown per bagian (untuk is_overall)
const breakdownSections = computed(() => {
    if (!props.is_overall) return [];
    return [
        {
            key: 'pretest',
            label: 'Pretest',
            icon: BookOpen,
            color: '#1cb0f6',
            bg: '#ddf4ff',
            data: props.results.pretest || { correct: 0, incorrect: 0, total: 0, score: 0 },
        },
        {
            key: 'missions',
            label: 'Misi',
            icon: Target,
            color: '#58cc02',
            bg: '#eefdf0',
            data: props.results.missions || { correct: 0, incorrect: 0, total: 0, score: 0 },
        },
        {
            key: 'posttest',
            label: 'Posttest',
            icon: Zap,
            color: '#ff9600',
            bg: '#fff4e5',
            data: props.results.posttest || { correct: 0, incorrect: 0, total: 0, score: 0 },
        },
    ];
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

const goToPosttestResult = () => {
    if (props.module?.id) {
        router.visit(route("playground.posttest.result", props.module.id));
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

const MASCOT_SPEECHES = [
    "Jangan menyerah! Setiap kesalahan adalah pelajaran baru.",
    "Bakat itu dilatih! Kamu berada di jalan yang benar.",
    "Bumi butuh ilmuwan hebat sepertimu, yuk belajar lagi!",
    "Keren banget usahamu hari ini, super bangga!",
    "Fokus pada prosesnya, bukan cuma nilainya ya!",
    "Ingat, Einstein juga pernah gagal sebelum sukses besar!"
];

const getInitialSpeechText = () => {
    const s = score.value;
    if (s >= 90) return "Luar biasa! Nilaimu sangat sempurna!";
    if (s >= 75) return "Kerja bagus! Terus pertahankan prestasimu ya!";
    if (s >= 60) return "Cukup baik! Kamu pasti bisa lebih hebat lagi!";
    return "Jangan menyerah! Terus belajar dan raih mimpimu ya!";
};

const activeMascotSpeech = ref(getInitialSpeechText());
const mascotClicked = ref(false);

const handleMascotClick = () => {
    playPop();
    mascotClicked.value = true;
    speechTriggered.value = true;
    setTimeout(() => { mascotClicked.value = false; }, 500);

    const randomIndex = Math.floor(Math.random() * MASCOT_SPEECHES.length);
    activeMascotSpeech.value = MASCOT_SPEECHES[randomIndex];
    setTimeout(() => { speechTriggered.value = false; }, 300);
};

// Custom Confetti System using HTML5 Canvas
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

    // Spawn initial burst
    for (let i = 0; i < 150; i++) {
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

onMounted(() => {
    // Play Success Chime
    if (score.value >= 60) {
        setTimeout(() => {
            playSuccess();
            startConfetti(confettiCanvas.value);
        }, 300);
    }

    // Score Count Up Animation
    const duration = 1200; // ms
    const startTime = performance.now();
    const targetScore = score.value;

    function animateCount(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        animatedScore.value = Math.round(progress * targetScore);

        if (progress < 1) {
            requestAnimationFrame(animateCount);
        } else {
            animatedScore.value = targetScore;
        }
    }
    requestAnimationFrame(animateCount);
});
</script>

<template>
    <div class="app-layout">
        <!-- Confetti Overlay Canvas -->
        <canvas ref="confettiCanvas" class="confetti-canvas"></canvas>

        <!-- Particles background (behind everything) -->
        <div class="particles-bg" aria-hidden="true">
            <span class="particle p1"></span>
            <span class="particle p2"></span>
            <span class="particle p3"></span>
            <span class="particle p4"></span>
            <span class="particle p5"></span>
            <span class="particle p6"></span>
            <span class="particle p7"></span>
            <span class="particle p8"></span>
        </div>

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
                                : is_pretest
                                ? `Pretest ${module.name} Selesai`
                                : is_posttest
                                ? `Posttest ${module.name} Selesai`
                                : `Misi ${mission.name || mission.title} Selesai`
                        }}
                    </p>
                </div>


                <div class="score-mascot-section">
                    <!-- Mascot with speech bubble -->
                    <div class="mascot-wrap" @click="handleMascotClick">
                        <div class="mascot-speech-bubble-wrap" :class="{ 'speech-pop': speechTriggered }">
                            <div class="mascot-speech">
                                {{ activeMascotSpeech }}
                            </div>
                            <div class="mascot-speech-arrow"></div>
                        </div>
                        <img
                            src="/images/templates/pose_jempol.png"
                            alt="Mascot"
                            class="mascot-img mascot-interactive"
                            :class="{ 'mascot-wiggle': mascotClicked }"
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
                                    strokeDashoffset: 364 - (364 * animatedScore) / 100,
                                    stroke: gradeData.color,
                                }"
                            />
                        </svg>
                        <div class="celeb-ring-inner">
                            <span
                                class="celeb-score"
                                :style="{ color: gradeData.color }"
                                >{{ animatedScore }}</span
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

                <!-- ══ BREAKDOWN PER BAGIAN (is_overall only) ══ -->
                <div v-if="is_overall" class="breakdown-section">
                    <div class="breakdown-title">Rincian Per Bagian</div>
                    <div class="breakdown-cards">
                        <div
                            v-for="section in breakdownSections"
                            :key="section.key"
                            class="breakdown-card"
                            :style="{ borderColor: section.color, '--bc': section.color, '--bg': section.bg }"
                        >
                            <div class="bk-header" :style="{ backgroundColor: section.bg }">
                                <div class="bk-icon" :style="{ backgroundColor: section.color }">
                                    <component :is="section.icon" :size="16" :stroke-width="2.5" color="white" />
                                </div>
                                <span class="bk-label" :style="{ color: section.color }">{{ section.label }}</span>
                            </div>
                            <div class="bk-stats">
                                <div class="bk-stat">
                                    <span class="bk-val bk-correct">{{ section.data.correct }}</span>
                                    <span class="bk-lbl">Benar</span>
                                </div>
                                <div class="bk-divider"></div>
                                <div class="bk-stat">
                                    <span class="bk-val bk-wrong">{{ section.data.incorrect }}</span>
                                    <span class="bk-lbl">Salah</span>
                                </div>
                                <div class="bk-divider"></div>
                                <div class="bk-stat">
                                    <span class="bk-val" :style="{ color: section.color }">{{ section.data.score }}</span>
                                    <span class="bk-lbl">Nilai</span>
                                </div>
                            </div>
                            <div class="bk-progress">
                                <div
                                    class="bk-progress-fill"
                                    :style="{ width: section.data.score + '%', backgroundColor: section.color }"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <!-- Total Keseluruhan -->
                    <div class="total-row">
                        <span class="total-label">Total Keseluruhan</span>
                        <div class="total-pills">
                            <span class="pill pill-green">{{ correctCount }} Benar</span>
                            <span class="pill pill-red">{{ incorrectCount }} Salah</span>
                            <span class="pill pill-blue">Nilai: {{ score }}</span>
                        </div>
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
                                >
                                </div>
                                <div class="dc-answers">
                                     <!-- Jika drag_drop -->
                                     <template v-if="detail.question.type === 'drag_drop'">
                                         <div
                                             v-for="(correctGroup, itemText) in detail.correct_answer_map"
                                             :key="itemText"
                                             class="answer-item"
                                             :class="
                                                 (detail.user_answer_map || {})[itemText] === correctGroup
                                                     ? 'ans-correct'
                                                     : 'ans-wrong'
                                             "
                                         >
                                             <div class="ans-icon">
                                                 <Check
                                                     v-if="(detail.user_answer_map || {})[itemText] === correctGroup"
                                                     :size="16"
                                                 />
                                                 <X v-else :size="16" />
                                             </div>
                                             <div class="ans-text">
                                                 <span class="font-bold">{{ itemText }}:</span>
                                                 Kamu menaruh di <span class="underline">{{ (detail.user_answer_map || {})[itemText] || '(tidak dijawab)' }}</span>
                                                 (Seharusnya: <span class="font-bold">{{ correctGroup }}</span>)
                                             </div>
                                         </div>
                                     </template>

                                     <!-- Jika bukan drag_drop -->
                                     <template v-else>
                                         <div
                                             class="answer-item"
                                             :class="detail.is_correct ? 'ans-correct' : 'ans-wrong'"
                                         >
                                             <div class="ans-icon">
                                                 <Check v-if="detail.is_correct" :size="16" />
                                                 <X v-else :size="16" />
                                             </div>
                                             <div class="ans-text">
                                                 <div>
                                                     <span class="font-bold">Jawaban Kamu:</span>
                                                     <span> {{ detail.user_answer || '(tidak dijawab)' }}</span>
                                                 </div>
                                                 <div v-if="!detail.is_correct" class="mt-1">
                                                     <span class="font-bold" style="color: #58cc02">Jawaban Benar:</span>
                                                     <span style="color: #58cc02"> {{ detail.correct_answer || '-' }}</span>
                                                 </div>
                                             </div>
                                         </div>
                                     </template>
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
                    <template v-if="is_pretest">
                        <button
                            class="btn-duo btn-duo-primary"
                            @click="goBack"
                        >
                            <span>Lanjut Ke Misi</span>
                            <ChevronRight :size="18" :stroke-width="3" />
                        </button>
                    </template>
                    <template v-else-if="is_posttest">
                        <button
                            class="btn-duo btn-duo-success"
                            @click="goToPosttestResult"
                        >
                            <span>Hasil Akhir Evaluasi</span>
                            <Rocket :size="18" :stroke-width="3" />
                        </button>
                    </template>
                    <template v-else-if="!is_overall">
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

/* ─── PARTICLES ─── */
.particles-bg {
    position: fixed;
    inset: 0;
    pointer-events: none;
    z-index: 0;
    overflow: hidden;
}

.particle {
    position: absolute;
    border-radius: 50%;
    opacity: 0;
    animation: particleFloat 8s ease-in-out infinite;
}

.p1 { width: 14px; height: 14px; background: #1cb0f6; top: 10%; left: 8%;  animation-delay: 0s;   animation-duration: 7s; }
.p2 { width: 10px; height: 10px; background: #58cc02; top: 25%; left: 90%; animation-delay: 1.2s; animation-duration: 9s; }
.p3 { width: 18px; height: 18px; background: #ff9600; top: 60%; left: 5%;  animation-delay: 2.4s; animation-duration: 8s; }
.p4 { width: 8px;  height: 8px;  background: #a855f7; top: 80%; left: 85%; animation-delay: 0.6s; animation-duration: 6s; }
.p5 { width: 12px; height: 12px; background: #ffc800; top: 45%; left: 95%; animation-delay: 3s;   animation-duration: 10s; }
.p6 { width: 16px; height: 16px; background: #1cb0f6; top: 15%; left: 50%; animation-delay: 1.8s; animation-duration: 7.5s; }
.p7 { width: 10px; height: 10px; background: #58cc02; top: 70%; left: 40%; animation-delay: 0.9s; animation-duration: 8.5s; }
.p8 { width: 6px;  height: 6px;  background: #ff4b4b; top: 35%; left: 15%; animation-delay: 2s;   animation-duration: 6.5s; }

@keyframes particleFloat {
    0%   { opacity: 0;    transform: translateY(0) scale(0.8); }
    20%  { opacity: 0.6;  }
    50%  { opacity: 0.4;  transform: translateY(-30px) scale(1.1); }
    80%  { opacity: 0.5;  }
    100% { opacity: 0;    transform: translateY(10px) scale(0.8); }
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
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

/* Speech bubble above mascot */
.mascot-speech-bubble-wrap {
    position: relative;
    margin-bottom: 10px;
    filter: drop-shadow(0 4px 10px rgba(0,0,0,0.07));
    animation: floatBubble 4s ease-in-out infinite;
}

.mascot-speech {
    background: #ffffff;
    border: 2px solid #e5e5e5;
    border-radius: 18px;
    padding: 10px 14px;
    font-size: 13px;
    font-weight: 800;
    color: #3c3c3c;
    line-height: 1.4;
    text-align: center;
    max-width: 180px;
    word-break: break-word;
}

.mascot-speech-arrow {
    position: absolute;
    bottom: -9px;
    left: 50%;
    transform: translateX(-50%) rotate(45deg);
    width: 14px;
    height: 14px;
    background: #ffffff;
    border-right: 2px solid #e5e5e5;
    border-bottom: 2px solid #e5e5e5;
    z-index: 1;
}

@keyframes floatBubble {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-5px); }
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
.istat--red .istat-icon {
    background-color: #ff4b4b;
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
        display: none;
    }
    .score-mascot-section {
        gap: 16px;
        justify-content: center;
    }

    .footer-inner {
        gap: 8px;
        padding: 0 20px;
    }
    .btn-duo span { display: none; }
    .btn-duo {
        padding: 12px;
        border-radius: 50%;
        min-width: 48px;
        width: 48px;
        height: 48px;
        gap: 0;
    }
    .footer-bar { height: 76px; }
}

/* ─── BREAKDOWN PER BAGIAN ─── */
.breakdown-section {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 24px;
}

.breakdown-title {
    font-family: "Baloo 2", cursive;
    font-size: 18px;
    font-weight: 800;
    color: #3c3c3c;
    text-align: center;
    padding-bottom: 4px;
    border-bottom: 2px solid #f1f5f9;
}

.breakdown-cards {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.breakdown-card {
    background: #ffffff;
    border: 2px solid;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.bk-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
}

.bk-icon {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.bk-label {
    font-family: "Baloo 2", cursive;
    font-size: 16px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.bk-stats {
    display: flex;
    align-items: center;
    padding: 12px 16px;
    gap: 0;
    border-top: 1px solid #f1f5f9;
}

.bk-stat {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 2px;
}

.bk-divider {
    width: 1px;
    height: 40px;
    background: #e5e7eb;
}

.bk-val {
    font-family: "Baloo 2", cursive;
    font-size: 24px;
    font-weight: 800;
    line-height: 1;
    color: #3c3c3c;
}

.bk-correct { color: #58cc02 !important; }
.bk-wrong   { color: #ff4b4b !important; }

.bk-lbl {
    font-size: 11px;
    font-weight: 800;
    color: #afafaf;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bk-progress {
    height: 6px;
    background: #f1f5f9;
    width: 100%;
}

.bk-progress-fill {
    height: 100%;
    border-radius: 0 3px 3px 0;
    transition: width 1s cubic-bezier(0.34, 1.56, 0.64, 1) 0.3s;
}

.total-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8fafc;
    border: 2px solid #e5e7eb;
    border-radius: 16px;
    padding: 14px 18px;
    gap: 12px;
    flex-wrap: wrap;
}

.total-label {
    font-family: "Baloo 2", cursive;
    font-size: 16px;
    font-weight: 800;
    color: #3c3c3c;
}

.total-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.pill {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 800;
    white-space: nowrap;
}

.pill-green { background: #eefdf0; color: #58cc02; border: 1px solid #c2f5cc; }
.pill-red   { background: #ffe5e5; color: #ff4b4b; border: 1px solid #fecaca; }
.pill-blue  { background: #ddf4ff; color: #1cb0f6; border: 1px solid #bae6fd; }

@media (max-width: 600px) {
    .bk-val { font-size: 18px; }
    .total-row { flex-direction: column; align-items: flex-start; }
}

/* ── Confetti & Gamified CSS ── */
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
    transform: scale(1.06) translateY(-4px);
}
.mascot-wiggle {
    animation: mascotWiggle 0.5s ease-in-out;
}

@keyframes mascotWiggle {
    0%, 100% { transform: rotate(0) scale(1); }
    25% { transform: rotate(-8deg) scale(1.1); }
    75% { transform: rotate(8deg) scale(1.1); }
}

.speech-pop {
    animation: speechPop 0.32s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

@keyframes speechPop {
    0% { transform: scale(0.9); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.details-list .detail-card {
    animation: cardSlideIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}

@keyframes cardSlideIn {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Cascade delays for cards */
.details-list .detail-card:nth-child(1) { animation-delay: 0.05s; }
.details-list .detail-card:nth-child(2) { animation-delay: 0.10s; }
.details-list .detail-card:nth-child(3) { animation-delay: 0.15s; }
.details-list .detail-card:nth-child(4) { animation-delay: 0.20s; }
.details-list .detail-card:nth-child(5) { animation-delay: 0.25s; }
.details-list .detail-card:nth-child(6) { animation-delay: 0.30s; }
.details-list .detail-card:nth-child(7) { animation-delay: 0.35s; }
.details-list .detail-card:nth-child(8) { animation-delay: 0.40s; }

.istat {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s;
}
.istat:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.06);
}

.breakdown-card {
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s;
}
.breakdown-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}
</style>
