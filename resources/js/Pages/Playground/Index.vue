<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import {
    BookOpen, Star, CheckCircle2, FileQuestion,
    ChevronDown, Play, Sparkles, LogOut,
    GraduationCap, Trophy, Zap, Music2, VolumeX,
    FlameKindling, Target, Rocket, Lightbulb,
    TrendingUp, Clock, Award, Brain,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
    learningModules: { type: Array, default: () => [] },
    backsound: { type: String, default: null },
});

const ready        = ref(false);
const dropdownOpen = ref(false);
const menuRef      = ref(null);
const musicOn      = ref(false);
const audioRef     = ref(null);

const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        const src = props.backsound ?? "/backsound/backsound.mp3";
        audioRef.value = new Audio(src);
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
        audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    }
    if (musicOn.value) { audioRef.value.pause(); musicOn.value = false; }
    else {
        try { await audioRef.value.play(); musicOn.value = true; }
        catch { musicOn.value = false; }
    }
};

const handleClickOutside = (e) => {
    if (menuRef.value && !menuRef.value.contains(e.target))
        dropdownOpen.value = false;
};

const logout = () => {
    dropdownOpen.value = false;
    router.post(route("playground.logout"));
};

onMounted(() => {
    setTimeout(() => (ready.value = true), 80);
    document.addEventListener("mousedown", handleClickOutside);
    document.addEventListener("visibilitychange", handleVisibility);

    if (props.backsound) {
        audioRef.value = new Audio(props.backsound);
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
        audioRef.value.preload = "auto";
        audioRef.value.addEventListener("error", () => {
            audioRef.value = null;
            musicOn.value = false;
        });
        audioRef.value.play()
            .then(() => { musicOn.value = true; })
            .catch(() => {
                document.addEventListener("click", () => {
                    audioRef.value?.play()
                        .then(() => { musicOn.value = true; })
                        .catch(() => {});
                }, { once: true });
            });
    }
});

onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) { audioRef.value.pause(); audioRef.value = null; }
});

const totalFinished = computed(
    () => props.learningModules.filter((m) => m.fully_completed).length,
);
const inProgress = computed(
    () => props.learningModules.filter((m) => m.has_attempt && !m.fully_completed).length,
);
const progressPct = computed(() =>
    props.learningModules.length
        ? Math.round((totalFinished.value / props.learningModules.length) * 100)
        : 0,
);

const statusLabel = (m) =>
    m.fully_completed ? "Selesai" : m.has_attempt ? "Lanjutkan" : "Mulai";
const scoreColor = (s) =>
    s >= 80 ? "#16a34a" : s >= 60 ? "#ca8a04" : "#dc2626";
const scoreBg = (s) =>
    s >= 80 ? "rgba(22,163,74,.1)" : s >= 60 ? "rgba(202,138,4,.1)" : "rgba(220,38,38,.1)";

const goToModule = (mod) => {
    router.visit(route("playground.pretest.show", mod.id));
};

const ACCENTS = [
    "#2563EB","#8B5CF6","#0891B2","#16A34A","#E09B2D",
    "#DC2626","#0D9488","#BE185D","#CA8A04","#7C3AED",
];
const accent = (i) => ACCENTS[i % ACCENTS.length];
</script>

<template>
    <div style="display:none">
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
    </div>

    <div class="root">

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div class="brand">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <div class="topbar-r">
                <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2"/>
                    <VolumeX v-else        :size="15" :stroke-width="2"/>
                </button>

                <div class="user-menu" ref="menuRef">
                    <button class="user-pill" :class="{ open: dropdownOpen }" @click="dropdownOpen = !dropdownOpen">
                        <div class="avatar-sm">{{ user.name.charAt(0) }}</div>
                        <span class="pill-name">{{ user.name.split(" ")[0] }}</span>
                        <ChevronDown :size="13" :stroke-width="2.8" class="pill-chev"/>
                    </button>

                    <Transition name="dd">
                        <div v-if="dropdownOpen" class="dropdown">
                            <div class="dd-top">
                                <div class="dd-ava">{{ user.name.charAt(0) }}</div>
                                <div>
                                    <div class="dd-nm">{{ user.name }}</div>
                                    <div class="dd-kls">Kelas {{ user.class?.name }}</div>
                                </div>
                            </div>
                            <div class="dd-line"></div>
                            <button class="dd-row dd-out" @click="logout">
                                <LogOut :size="14" :stroke-width="2.3"/>
                                Keluar
                            </button>
                        </div>
                    </Transition>
                </div>
            </div>
        </header>

        <!-- ══ SCROLLABLE BODY ══ -->
        <div class="page-body" :class="{ 'page-body--on': ready }">
            <div class="wrap">

                <!-- ── HERO COMPACT ── -->
                <section class="hero-section">
                    <div class="hero-card">
                        <!-- Bubbles dekorasi -->
                        <div v-for="n in 10" :key="n" class="hbubble" :class="`hb-${n}`"></div>

                        <!-- Kiri: greeting + stats row -->
                        <div class="hero-left">
                            <div class="hero-greet">
                                <Sparkles :size="11" :stroke-width="2.3"/>
                                Selamat datang kembali!
                            </div>
                            <h1 class="hero-name">Halo, {{ user.name.split(" ")[0] }}! 👋</h1>

                            <div class="hero-stats">
                                <div class="hstat">
                                    <div class="hstat-icon hstat-blue">
                                        <GraduationCap :size="14" :stroke-width="2.3"/>
                                    </div>
                                    <div class="hstat-text">
                                        <span class="hstat-val">{{ user.class?.name }}</span>
                                        <span class="hstat-lbl">Kelas Saya</span>
                                    </div>
                                </div>
                                <div class="hstat-sep"></div>
                                <div class="hstat">
                                    <div class="hstat-icon hstat-gold">
                                        <Trophy :size="14" :stroke-width="2.3"/>
                                    </div>
                                    <div class="hstat-text">
                                        <span class="hstat-val">{{ totalFinished }}<small>/{{ learningModules.length }}</small></span>
                                        <span class="hstat-lbl">Selesai</span>
                                    </div>
                                </div>
                                <div class="hstat-sep"></div>
                                <div class="hstat">
                                    <div class="hstat-icon hstat-orange">
                                        <FlameKindling :size="14" :stroke-width="2.3"/>
                                    </div>
                                    <div class="hstat-text">
                                        <span class="hstat-val">{{ inProgress }}</span>
                                        <span class="hstat-lbl">Berlangsung</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kanan: info cards grid -->
                        <div class="hero-right">
                            <!-- Progress bar card -->
                            <div class="info-card ic-progress">
                                <div class="ic-head">
                                    <TrendingUp :size="14" :stroke-width="2.3" class="ic-icon"/>
                                    <span>Progress Belajar</span>
                                </div>
                                <div class="progress-bar-wrap">
                                    <div class="progress-bar-track">
                                        <div class="progress-bar-fill" :style="{ width: progressPct + '%' }"></div>
                                    </div>
                                    <span class="progress-pct">{{ progressPct }}%</span>
                                </div>
                            </div>

                            <!-- Stat cards -->
                            <div class="ic-row">
                                <div class="info-card ic-mini ic-blue">
                                    <Target :size="18" :stroke-width="2" class="ic-mini-icon"/>
                                    <div class="ic-mini-body">
                                        <span class="ic-mini-val">{{ learningModules.length }}</span>
                                        <span class="ic-mini-lbl">Total Modul</span>
                                    </div>
                                </div>
                                <div class="info-card ic-mini ic-green">
                                    <Award :size="18" :stroke-width="2" class="ic-mini-icon"/>
                                    <div class="ic-mini-body">
                                        <span class="ic-mini-val">{{ totalFinished }}</span>
                                        <span class="ic-mini-lbl">Diselesaikan</span>
                                    </div>
                                </div>
                                <div class="info-card ic-mini ic-purple">
                                    <Brain :size="18" :stroke-width="2" class="ic-mini-icon"/>
                                    <div class="ic-mini-body">
                                        <span class="ic-mini-val">{{ inProgress }}</span>
                                        <span class="ic-mini-lbl">Dikerjakan</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- ── MODULE GRID ── -->
                <section class="grid-section">
                    <p class="grid-label">{{ learningModules.length }} modul tersedia</p>

                    <div class="mod-grid">
                        <article
                            v-for="(mod, i) in learningModules"
                            :key="mod.id"
                            class="mod-card"
                            :class="{ 'card-show': ready, done: mod.fully_completed }"
                            :style="{ '--ac': accent(i), '--delay': i * 40 + 'ms' }"
                        >
                            <div class="card-bar" :style="{ background: accent(i) }"></div>

                            <div class="card-thumb" :style="{ background: accent(i) + '12' }">
                                <template v-if="mod.thumbnail">
                                    <img :src="mod.thumbnail" :alt="mod.name" class="thumb-img"/>
                                </template>
                                <template v-else>
                                    <div class="thumb-icon"
                                        :style="{ background: accent(i) + '1e', borderColor: accent(i) + '55' }">
                                        <BookOpen :size="24" :color="accent(i)" :stroke-width="1.8"/>
                                    </div>
                                </template>

                                <div v-if="mod.fully_completed" class="fin-stamp"
                                    :style="{ color: accent(i), borderColor: accent(i) + '55', background: accent(i) + '15' }">
                                    <CheckCircle2 :size="11" :stroke-width="2.5"/>
                                    Selesai
                                </div>
                            </div>

                            <div class="card-body">
                                <h3 class="mod-title">{{ mod.name }}</h3>
                                <p class="mod-desc">{{ mod.description }}</p>

                                <div v-if="mod.has_attempt" class="score-row"
                                    :style="{ color: scoreColor(mod.best_score), background: scoreBg(mod.best_score) }">
                                    <Star :size="10" fill="currentColor" :stroke-width="0"/>
                                    Skor terbaik: <strong>{{ mod.best_score }}</strong>
                                </div>

                                <div class="divider"></div>

                                <div class="chips">
                                    <span class="chip" :style="{ color: accent(i), background: accent(i) + '14' }">
                                        <FileQuestion :size="10" :stroke-width="2.5"/>
                                        Quiz &amp; Materi
                                    </span>
                                </div>

                                <button
                                    class="cta"
                                    :class="{
                                        'cta-new':  !mod.has_attempt && !mod.fully_completed,
                                        'cta-cont':  mod.has_attempt && !mod.fully_completed,
                                        'cta-done':  mod.fully_completed,
                                    }"
                                    :style="!mod.has_attempt && !mod.fully_completed ? `--btnbg:${accent(i)}` : ''"
                                    :disabled="mod.fully_completed"
                                    @click="!mod.fully_completed && goToModule(mod)"
                                >
                                    <component
                                        :is="mod.fully_completed ? CheckCircle2 : Play"
                                        :size="12"
                                        :stroke-width="2.5"
                                        :fill="!mod.fully_completed ? 'currentColor' : 'none'"
                                    />
                                    {{ statusLabel(mod) }}
                                </button>
                            </div>
                        </article>
                    </div>

                    <div v-if="learningModules.length === 0" class="empty">
                        <BookOpen :size="40" color="rgba(255,255,255,.7)" :stroke-width="1.4"/>
                        <p class="empty-t">Belum ada modul</p>
                        <p class="empty-s">Modul akan segera tersedia.</p>
                    </div>
                </section>

            </div>
        </div>

    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.root {
    min-height: 100dvh;
    display: flex;
    flex-direction: column;
    font-family: 'Nunito', sans-serif;
    position: relative;
    overflow-x: hidden;
}
.root::after {
    content: "";
    position: fixed;
    inset: 0;
    background: url('/images/templates/background.png') center / cover no-repeat;
    z-index: -2;
}
.root::before {
    content: "";
    position: fixed;
    inset: 0;
    background:
        radial-gradient(ellipse 90% 70% at 10% 5%, rgba(30, 64, 175, 0.78) 0%, transparent 55%),
        radial-gradient(ellipse 75% 60% at 90% 95%, rgba(29, 78, 216, 0.72) 0%, transparent 55%),
        rgba(30, 58, 138, 0.65);
    backdrop-filter: blur(0.3px);
    -webkit-backdrop-filter: blur(0.3px);
    z-index: -1;
}

/* ═══ TOPBAR ═══ */
.topbar {
    position: sticky; top: 0; z-index: 50;
    height: 60px; flex-shrink: 0;
    display: flex; align-items: center; padding: 0 24px;
    background: rgba(255, 255, 255, 0.06);
    backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.10);
    box-shadow: 0 2px 24px rgba(0, 0, 0, 0.12);
}
.brand { display: flex; align-items: center; gap: 8px; }
.brand-dot  { width: 28px; height: 28px; border-radius: 8px; background: #2563eb; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37,99,235,.5); }
.brand-name { font-family: 'Righteous', cursive; font-size: 18px; color: #fff; white-space: nowrap; }
.topbar-r { display: flex; align-items: center; gap: 8px; margin-left: auto; }
.tbtn {
    display: flex; align-items: center; gap: 6px; padding: 7px 13px;
    background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.22);
    border-radius: 10px; font-family: 'Nunito', sans-serif;
    font-size: 13px; font-weight: 800; color: #fff; cursor: pointer;
    transition: background .18s, transform .15s;
}
.tbtn:hover { background: rgba(255,255,255,.20); transform: translateY(-1px); }
.tbtn-sq  { padding: 7px 10px; }
.tbtn--on { background: #2563eb !important; border-color: #bfdbfe !important; }

.user-menu { position: relative; }
.user-pill {
    display: flex; align-items: center; gap: 7px;
    padding: 5px 11px 5px 5px;
    background: rgba(255,255,255,.10); border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 50px; cursor: pointer; outline: none;
    transition: all .18s; color: #fff;
}
.user-pill:hover, .user-pill.open { background: rgba(255,255,255,.20); border-color: rgba(255,255,255,.45); }
.avatar-sm {
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(135deg, #60a5fa, #1d4ed8);
    color: #fff; font-weight: 900; font-size: 13px;
    display: flex; align-items: center; justify-content: center;
}
.pill-name { font-size: 12.5px; font-weight: 800; color: #fff; }
.pill-chev { color: rgba(255,255,255,.8); flex-shrink: 0; transition: transform .2s cubic-bezier(.34,1.56,.64,1); }
.user-pill.open .pill-chev { transform: rotate(180deg); }
.dropdown {
    position: absolute; top: calc(100% + 8px); right: 0; width: 210px;
    background: rgba(255,255,255,.98); border: 1.5px solid rgba(29,78,216,.16);
    border-radius: 16px; box-shadow: 0 16px 48px rgba(29,78,216,.14);
    overflow: hidden; z-index: 200;
}
.dd-top { display: flex; align-items: center; gap: 10px; padding: 13px 13px 11px; }
.dd-ava { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg,#60a5fa,#1d4ed8); color: #fff; font-weight: 900; font-size: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.dd-nm  { font-size: 13px; font-weight: 900; color: #1e3a8a; line-height: 1.3; }
.dd-kls { font-size: 11px; font-weight: 700; color: #6b9fd4; }
.dd-line { height: 1px; background: rgba(29,78,216,.1); margin: 0 10px; }
.dd-row { display: flex; align-items: center; gap: 9px; width: 100%; padding: 10px 13px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; background: none; border: none; cursor: pointer; text-align: left; transition: background .13s; }
.dd-out { color: #dc2626; }
.dd-out:hover { background: rgba(220,38,38,.06); }
.dd-enter-active { transition: all .2s cubic-bezier(.34,1.56,.64,1); }
.dd-leave-active { transition: all .14s ease; }
.dd-enter-from, .dd-leave-to { opacity: 0; transform: translateY(-6px) scale(.97); }

/* ═══ PAGE BODY ═══ */
.page-body { position: relative; z-index: 10; flex: 1; padding-bottom: 40px; opacity: 0; transition: opacity .45s; }
.page-body--on { opacity: 1; }
.wrap { max-width: 1180px; margin: 0 auto; padding: 0 20px; }

/* ═══════════════════════════════
   HERO COMPACT
═══════════════════════════════ */
.hero-section { padding: 16px 0 0; }

.hero-card {
    position: relative; overflow: hidden;
    background: rgba(255,255,255,.10);
    backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 20px;
    padding: 18px 22px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    box-shadow: 0 6px 28px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.18);
}

/* Bubbles kecil */
.hbubble {
    position: absolute; border-radius: 50%;
    background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.12);
    pointer-events: none; bottom: -20px;
    left: var(--left, 50%); width: var(--size, 16px); height: var(--size, 16px);
    animation: hb-rise var(--dur, 7s) var(--delay, 0s) linear infinite;
}
@keyframes hb-rise {
    0%   { transform: translateY(0); opacity: .4; }
    90%  { opacity: .1; }
    100% { transform: translateY(-200px) translateX(var(--sway, 8px)); opacity: 0; }
}
.hb-1  { --size:8px; --left:5%;  --dur:8s;  --delay:-1s;  --sway:8px;  }
.hb-2  { --size:16px;--left:15%; --dur:12s; --delay:-4s;  --sway:14px; }
.hb-3  { --size:6px; --left:30%; --dur:7s;  --delay:-2s;  --sway:-6px; }
.hb-4  { --size:12px;--left:45%; --dur:10s; --delay:-7s;  --sway:-10px;}
.hb-5  { --size:20px;--left:60%; --dur:14s; --delay:-3s;  --sway:-16px;}
.hb-6  { --size:7px; --left:72%; --dur:6s;  --delay:-5s;  --sway:-5px; }
.hb-7  { --size:14px;--left:82%; --dur:11s; --delay:-9s;  --sway:12px; }
.hb-8  { --size:5px; --left:90%; --dur:7s;  --delay:-1s;  --sway:5px;  }
.hb-9  { --size:18px;--left:25%; --dur:13s; --delay:-6s;  --sway:-14px;}
.hb-10 { --size:9px; --left:55%; --dur:8s;  --delay:-3.5s;--sway:7px;  }

/* Left side */
.hero-left { position: relative; z-index: 2; flex-shrink: 0; }

.hero-greet {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; font-weight: 900; letter-spacing: .4px;
    color: rgba(255,255,255,.9);
    background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.25);
    border-radius: 50px; padding: 2px 10px; margin-bottom: 6px;
}
.hero-name {
    font-family: 'Righteous', cursive;
    font-size: clamp(18px, 2.4vw, 24px);
    color: #fff; line-height: 1.2; margin-bottom: 12px;
    text-shadow: 0 2px 10px rgba(0,0,0,.2);
}

.hero-stats {
    display: inline-flex; align-items: stretch;
    background: rgba(255,255,255,.12);
    border: 1.5px solid rgba(255,255,255,.2);
    border-radius: 12px; overflow: hidden;
}
.hstat { display: flex; align-items: center; gap: 8px; padding: 8px 14px; color: #fff; }
.hstat-icon {
    width: 28px; height: 28px; border-radius: 8px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.hstat-blue   { background: rgba(96,165,250,.25); }
.hstat-gold   { background: rgba(251,191,36,.25);  }
.hstat-orange { background: rgba(249,115,22,.25);  }
.hstat-text   { display: flex; flex-direction: column; }
.hstat-val    { font-family: 'Righteous', cursive; font-size: 14px; line-height: 1.2; color: #fff; }
.hstat-val small { font-family: 'Nunito', sans-serif; font-size: 10px; font-weight: 700; opacity: .75; }
.hstat-lbl    { font-size: 9.5px; font-weight: 800; opacity: .7; text-transform: uppercase; letter-spacing: .5px; }
.hstat-sep    { width: 1px; background: rgba(255,255,255,.18); align-self: stretch; }

/* Right side */
.hero-right {
    position: relative; z-index: 2;
    flex: 1; max-width: 480px;
    display: flex; flex-direction: column; gap: 8px;
}

/* Info card base */
.info-card {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 12px;
    padding: 10px 14px;
    backdrop-filter: blur(6px);
}

/* Progress card */
.ic-progress {}
.ic-head {
    display: flex; align-items: center; gap: 6px;
    font-size: 11px; font-weight: 800;
    color: rgba(255,255,255,.9);
    margin-bottom: 8px;
}
.ic-icon { flex-shrink: 0; color: rgba(255,255,255,.85); }
.progress-bar-wrap { display: flex; align-items: center; gap: 8px; }
.progress-bar-track {
    flex: 1; height: 8px;
    background: rgba(255,255,255,.15);
    border-radius: 50px; overflow: hidden;
}
.progress-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #34d399, #10b981);
    border-radius: 50px;
    transition: width .8s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 0 8px rgba(52,211,153,.5);
}
.progress-pct {
    font-family: 'Righteous', cursive;
    font-size: 13px; color: #fff;
    flex-shrink: 0; min-width: 34px; text-align: right;
}

/* Mini cards row */
.ic-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 8px; }

.ic-mini {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 12px;
}
.ic-mini-icon { flex-shrink: 0; }
.ic-mini-body { display: flex; flex-direction: column; }
.ic-mini-val  { font-family: 'Righteous', cursive; font-size: 15px; color: #fff; line-height: 1.2; }
.ic-mini-lbl  { font-size: 9px; font-weight: 800; opacity: .75; color: #fff; text-transform: uppercase; letter-spacing: .4px; }

.ic-blue   { border-color: rgba(96,165,250,.35);  }
.ic-blue   .ic-mini-icon { color: #93c5fd; }
.ic-green  { border-color: rgba(52,211,153,.35);  }
.ic-green  .ic-mini-icon { color: #6ee7b7; }
.ic-purple { border-color: rgba(167,139,250,.35); }
.ic-purple .ic-mini-icon { color: #c4b5fd; }

/* ═══════════════════════════════
   GRID
═══════════════════════════════ */
.grid-section { padding: 14px 0 0; }
.grid-label { font-size: 12px; font-weight: 800; color: rgba(255,255,255,.9); text-shadow: 0 1px 4px rgba(0,0,0,.2); margin-bottom: 10px; letter-spacing: .2px; }

.mod-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }

.mod-card {
    background: rgba(255,255,255,.96); border: 1.5px solid rgba(29,78,216,.12);
    border-radius: 18px; overflow: hidden;
    display: flex; flex-direction: column;
    opacity: 0; transform: translateY(24px) scale(.97);
    transition:
        opacity .42s var(--delay,0ms) ease,
        transform .42s var(--delay,0ms) cubic-bezier(.34,1.56,.64,1),
        box-shadow .2s ease, border-color .2s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
}
.mod-card.card-show { opacity: 1; transform: none; }
.mod-card:hover     { transform: translateY(-6px) scale(1.013); box-shadow: 0 16px 40px rgba(0,0,0,.13), 0 0 0 1.5px var(--ac); border-color: var(--ac); }
.mod-card.done      { border-color: rgba(29,78,216,.28); }

.card-bar   { height: 4px; }
.card-thumb { position: relative; height: 86px; display: flex; align-items: center; justify-content: center; }
.thumb-img  { width: 100%; height: 100%; object-fit: cover; }
.thumb-icon { width: 54px; height: 54px; border-radius: 14px; border: 2px solid; display: flex; align-items: center; justify-content: center; }
.fin-stamp  { position: absolute; bottom: 7px; right: 7px; display: inline-flex; align-items: center; gap: 4px; font-size: 9.5px; font-weight: 900; border-radius: 50px; padding: 2px 8px; border: 1.5px solid; }
.card-body  { padding: 12px 13px 13px; display: flex; flex-direction: column; gap: 7px; flex: 1; }
.mod-title  { font-family: 'Righteous', cursive; font-size: 14.5px; color: #1e3a8a; line-height: 1.35; }
.mod-desc   { font-size: 11.5px; font-weight: 700; color: #4b6a9b; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.score-row  { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 800; border-radius: 50px; padding: 3px 10px; width: fit-content; }
.score-row strong { font-weight: 900; }
.divider    { height: 1px; background: rgba(29,78,216,.08); }
.chips      { display: flex; gap: 5px; flex-wrap: wrap; }
.chip       { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; border-radius: 8px; padding: 3px 8px; }

.cta {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; height: 37px; border: none; border-radius: 10px;
    font-family: 'Righteous', cursive; font-size: 13.5px;
    cursor: pointer; margin-top: auto;
    transition: all .17s cubic-bezier(.34,1.56,.64,1);
}
.cta:hover  { transform: translateY(-2px); filter: brightness(1.07); }
.cta:active { transform: translateY(1px); }
.cta-new    { background: var(--btnbg,#1d4ed8); color: #fff; box-shadow: 0 3px 10px rgba(29,78,216,.3); }
.cta-cont   { background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; box-shadow: 0 3px 8px rgba(29,78,216,.28); }
.cta-done   { background: linear-gradient(135deg,#34d399,#059669); color: #fff; box-shadow: 0 3px 8px rgba(5,150,105,.25); cursor: not-allowed; opacity: .85; position: relative; overflow: hidden; }
.cta-done:hover  { transform: none !important; filter: none !important; }
.cta-done::after { content: ''; position: absolute; inset: 0; background: linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%); animation: cta-shimmer 2.5s ease-in-out infinite; }
@keyframes cta-shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }

.empty   { text-align: center; padding: 72px 20px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-t { font-family: 'Righteous', cursive; font-size: 18px; color: #fff; text-shadow: 0 1px 6px rgba(0,0,0,.2); }
.empty-s { font-size: 13px; font-weight: 700; color: rgba(255,255,255,.7); }

/* ═══ RESPONSIVE ═══ */
@media (max-width: 1100px) { .mod-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 900px) {
    .hero-right { display: none; }
    .hero-left  { flex: 1; }
}
@media (max-width: 820px) {
    .topbar { height: 52px; padding: 0 13px; }
    .brand-name { font-size: 16px; }
    .brand-dot  { width: 25px; height: 25px; }
    .mod-grid   { grid-template-columns: repeat(2,1fr); }
    .hero-card  { padding: 16px 18px; }
}
@media (max-width: 600px) {
    .wrap      { padding: 0 14px; }
    .hero-card { padding: 14px 14px; border-radius: 16px; }
    .hero-name { font-size: 20px; }
    .hero-stats { width: 100%; }
    .hstat     { flex: 1; }
}
@media (max-width: 480px) {
    .topbar    { height: 48px; padding: 0 11px; }
    .pill-name { display: none; }
    .mod-grid  { grid-template-columns: repeat(2,1fr); gap: 10px; }
    .hero-name { font-size: 18px; }
    .hstat     { padding: 7px 10px; gap: 6px; }
    .hstat-val { font-size: 13px; }
    .hstat-icon { width: 24px; height: 24px; }
    .card-thumb { height: 72px; }
}
@media (max-width: 340px) { .mod-grid { grid-template-columns: 1fr; } }
</style>
