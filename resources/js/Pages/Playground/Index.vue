<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import {
    BookOpen, Star, CheckCircle2, FileQuestion,
    ChevronDown, Play, Sparkles, LogOut,
    GraduationCap, Trophy, Zap, Music2, VolumeX,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
    learningModules: { type: Array, default: () => [] },
});

const ready       = ref(false);
const dropdownOpen = ref(false);
const menuRef     = ref(null);
const musicOn     = ref(false);
const audioRef    = ref(null);

// ── Music ──────────────────────────────────────────────────────────
const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
        audioRef.value.loop   = true;
        audioRef.value.volume = 0.4;
        audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    }
    if (musicOn.value) { audioRef.value.pause(); musicOn.value = false; }
    else {
        try { await audioRef.value.play(); musicOn.value = true; }
        catch { musicOn.value = false; }
    }
};

const initAutoMusic = async () => {
    audioRef.value = new Audio("/backsound/backsound.mp3");
    audioRef.value.loop   = true;
    audioRef.value.volume = 0.4;
    audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    try { await audioRef.value.play(); musicOn.value = true; }
    catch { musicOn.value = false; }
};

// ── Dropdown ───────────────────────────────────────────────────────
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
    setTimeout(() => { initAutoMusic(); }, 500);
});
onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) { audioRef.value.pause(); audioRef.value = null; }
});

// ── Computed ───────────────────────────────────────────────────────
const totalFinished = computed(
    () => props.learningModules.filter((m) => m.fully_completed).length,
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

        <!-- ══ BG — identik login ══ -->
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

        <!-- ══ TOPBAR — identik login ══ -->
        <header class="topbar">
            <div class="brand">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <div class="topbar-r">
                <!-- Music toggle -->
                <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2"/>
                    <VolumeX v-else        :size="15" :stroke-width="2"/>
                </button>

                <!-- User pill dropdown -->
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

                <!-- ── HERO CARD ── -->
                <section class="hero-section">
                    <div class="hero-card">
                        <!-- Floating bubbles -->
                        <div v-for="n in 22" :key="n" class="hbubble" :class="`hb-${n}`"></div>

                        <div class="hero-body">
                            <div class="hero-greet">
                                <Sparkles :size="12" :stroke-width="2.3"/>
                                Selamat datang kembali!
                            </div>
                            <h1 class="hero-name">Halo, {{ user.name.split(" ")[0] }}! 👋</h1>
                            <p class="hero-sub">
                                Ada <strong>{{ learningModules.length }}</strong> modul seru yang siap dijelajahi hari ini.
                            </p>

                            <div class="hero-stats">
                                <div class="hstat">
                                    <div class="hstat-icon">
                                        <GraduationCap :size="16" :stroke-width="2.3"/>
                                    </div>
                                    <div class="hstat-text">
                                        <span class="hstat-val">{{ user.class?.name }}</span>
                                        <span class="hstat-lbl">Kelas Saya</span>
                                    </div>
                                </div>
                                <div class="hstat-sep"></div>
                                <div class="hstat">
                                    <div class="hstat-icon">
                                        <Trophy :size="16" :stroke-width="2.3"/>
                                    </div>
                                    <div class="hstat-text">
                                        <span class="hstat-val">
                                            {{ totalFinished }}<small>/{{ learningModules.length }}</small>
                                        </span>
                                        <span class="hstat-lbl">Modul Selesai</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Deco rings -->
                        <div class="hero-deco" aria-hidden="true">
                            <div class="dring dr-lg"></div>
                            <div class="dring dr-md"></div>
                            <div class="dring dr-sm"></div>
                            <div class="dring-center">
                                <BookOpen :size="30" color="rgba(255,255,255,.92)" :stroke-width="1.6"/>
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

/* ════════════════════════════════
   BG — identik login
════════════════════════════════ */
.bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
.bg-img  { position: absolute; inset: 0; background: url('/images/templates/background.png') center/cover no-repeat; }
.bg-tint { position: absolute; inset: 0; background: #2563EB; opacity: .52; }

.blob { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); }
.b1 { width:480px;height:480px;top:-140px;left:-100px;background:#1d4ed8;opacity:.35;animation:bDrift 20s ease-in-out infinite alternate; }
.b2 { width:380px;height:380px;bottom:-100px;right:-80px;background:#34D399;opacity:.22;animation:bDrift2 24s ease-in-out infinite alternate; }
.b3 { width:260px;height:260px;top:38%;left:52%;background:#BFDBFE;opacity:.18;animation:bDrift 28s ease-in-out 6s infinite alternate; }
@keyframes bDrift  { 0%{transform:translate(0,0)} 50%{transform:translate(30px,20px) scale(1.05)} 100%{transform:translate(-15px,35px)} }
@keyframes bDrift2 { 0%{transform:translate(0,0)} 50%{transform:translate(-28px,-18px) scale(1.06)} 100%{transform:translate(22px,-40px)} }

.sh { position: absolute; pointer-events: none; }
.sh-circle { border-radius:50%; background:rgba(255,255,255,.06); border:1.5px solid rgba(255,255,255,.1); animation:sDrift ease-in-out infinite alternate; }
.c1{width:150px;height:150px;top:-30px;left:-25px;animation-duration:22s;}
.c2{width:90px;height:90px;bottom:70px;right:50px;animation-duration:28s;animation-delay:4s;}
.sh-ring { border-radius:50%; background:transparent; border:1.5px solid rgba(191,219,254,.2); animation:rPulse ease-out infinite; }
.r1{width:300px;height:300px;top:-60px;left:-60px;animation-duration:9s;}
.r2{width:240px;height:240px;bottom:-50px;right:-50px;animation-duration:12s;animation-delay:2s;}
.r3{width:180px;height:180px;top:38%;left:58%;animation-duration:10s;animation-delay:5s;}
.sh-dot { border-radius:50%; background:rgba(255,255,255,.45); animation:dFloat linear infinite; }
.d1{width:5px;height:5px;top:12%;left:9%;animation-duration:14s;}
.d2{width:3px;height:3px;top:32%;left:22%;animation-duration:18s;animation-delay:2s;}
.d3{width:6px;height:6px;top:58%;left:7%;animation-duration:12s;animation-delay:5s;}
.d4{width:4px;height:4px;top:18%;right:11%;animation-duration:16s;animation-delay:1s;}
.d5{width:5px;height:5px;top:72%;right:16%;animation-duration:20s;animation-delay:3.5s;}
@keyframes sDrift { 0%{transform:translate(0,0) rotate(0)} 50%{transform:translate(14px,-10px) rotate(6deg)} 100%{transform:translate(-10px,18px) rotate(-4deg)} }
@keyframes rPulse { 0%{transform:scale(1);opacity:.38} 70%{transform:scale(1.38);opacity:.06} 100%{transform:scale(1.65);opacity:0} }
@keyframes dFloat { 0%{transform:translateY(0);opacity:0} 10%{opacity:.55} 90%{opacity:.25} 100%{transform:translateY(-150px);opacity:0} }
.bg-dots { position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(circle,rgba(255,255,255,.09) 1px,transparent 1px);background-size:34px 34px; }

/* ════════════════════════════════
   TOPBAR — identik login
════════════════════════════════ */
.topbar {
    position: sticky; top: 0; z-index: 50; height: 56px; flex-shrink: 0;
    display: flex; align-items: center; padding: 0 18px;
    background: rgba(255,255,255,.10); backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(255,255,255,.16);
}

.brand {
    display: flex; align-items: center; gap: 8px;
}
.brand-dot  { width:28px;height:28px;border-radius:8px;background:#2563EB;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,.5); }
.brand-name { font-family:'Righteous',cursive;font-size:18px;color:#fff;white-space:nowrap; }

.topbar-r { display:flex;align-items:center;gap:8px;margin-left:auto; }

.tbtn {
    display: flex; align-items: center; gap: 6px; padding: 7px 13px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
    border-radius: 10px; font-family: 'Nunito', sans-serif;
    font-size: 13px; font-weight: 800; color: #fff; cursor: pointer;
    transition: background .18s, transform .15s;
}
.tbtn:hover { background: rgba(255,255,255,.22); transform: translateY(-1px); }
.tbtn-sq  { padding: 7px 10px; }
.tbtn--on { background: #2563EB; border-color: #BFDBFE; }

/* User pill */
.user-menu { position: relative; }
.user-pill {
    display: flex; align-items: center; gap: 7px;
    padding: 5px 11px 5px 5px;
    background: rgba(255,255,255,.15); border: 1.5px solid rgba(255,255,255,.30);
    border-radius: 50px; cursor: pointer; outline: none;
    transition: all .18s; color: #fff;
}
.user-pill:hover,.user-pill.open { background: rgba(255,255,255,.25); border-color: rgba(255,255,255,.5); }
.avatar-sm {
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(135deg,#60a5fa,#1d4ed8);
    color: #fff; font-weight: 900; font-size: 13px;
    display: flex; align-items: center; justify-content: center;
}
.pill-name { font-size: 12.5px; font-weight: 800; color: #fff; }
.pill-chev { color: rgba(255,255,255,.8); flex-shrink: 0; transition: transform .2s cubic-bezier(.34,1.56,.64,1); }
.user-pill.open .pill-chev { transform: rotate(180deg); }

/* Dropdown */
.dropdown {
    position: absolute; top: calc(100% + 8px); right: 0; width: 210px;
    background: rgba(255,255,255,.98); border: 1.5px solid rgba(29,78,216,.16);
    border-radius: 16px; box-shadow: 0 16px 48px rgba(29,78,216,.14);
    overflow: hidden; z-index: 200;
}
.dd-top { display:flex;align-items:center;gap:10px;padding:13px 13px 11px; }
.dd-ava {
    width:36px;height:36px;border-radius:50%;
    background:linear-gradient(135deg,#60a5fa,#1d4ed8);
    color:#fff;font-weight:900;font-size:16px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.dd-nm  { font-size:13px;font-weight:900;color:#1e3a8a;line-height:1.3; }
.dd-kls { font-size:11px;font-weight:700;color:#6b9fd4; }
.dd-line { height:1px;background:rgba(29,78,216,.1);margin:0 10px; }
.dd-row {
    display:flex;align-items:center;gap:9px;width:100%;padding:10px 13px;
    font-family:'Nunito',sans-serif;font-size:13px;font-weight:800;
    background:none;border:none;cursor:pointer;text-align:left;transition:background .13s;
}
.dd-out { color:#dc2626; }
.dd-out:hover { background:rgba(220,38,38,.06); }
.dd-enter-active { transition: all .2s cubic-bezier(.34,1.56,.64,1); }
.dd-leave-active { transition: all .14s ease; }
.dd-enter-from,.dd-leave-to { opacity:0;transform:translateY(-6px) scale(.97); }

/* ════════════════════════════════
   PAGE BODY
════════════════════════════════ */
.page-body {
    position: relative; z-index: 10; flex: 1;
    padding-bottom: 40px;
    opacity: 0; transition: opacity .45s;
}
.page-body--on { opacity: 1; }

.wrap { max-width: 1180px; margin: 0 auto; padding: 0 20px; }

/* ════════════════════════════════
   HERO
════════════════════════════════ */
.hero-section { padding: 22px 0 0; }

.hero-card {
    position: relative; overflow: hidden;
    background: rgba(255,255,255,.12);
    backdrop-filter: blur(18px);
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 24px; padding: 28px 32px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    box-shadow: 0 8px 32px rgba(0,0,0,.12), inset 0 1px 0 rgba(255,255,255,.22);
}

/* hero bubbles */
.hbubble {
    position: absolute; border-radius: 50%;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.2);
    pointer-events: none;
    bottom: -30px;
    left: var(--left, 50%);
    width: var(--size, 20px);
    height: var(--size, 20px);
    animation: hb-rise var(--dur, 7s) var(--delay, 0s) linear infinite;
}
@keyframes hb-rise {
    0%   { transform: translateY(0) translateX(0); opacity:.55; }
    90%  { opacity:.2; }
    100% { transform: translateY(-300px) translateX(var(--sway, 10px)); opacity:0; }
}
.hb-1  { --size:10px;--left:5%;  --dur:8s; --delay:-1s;  --sway:10px;  }
.hb-2  { --size:22px;--left:12%; --dur:12s;--delay:-4s;  --sway:18px;  }
.hb-3  { --size:8px; --left:20%; --dur:7s; --delay:-2s;  --sway:-8px;  }
.hb-4  { --size:16px;--left:28%; --dur:10s;--delay:-7s;  --sway:-14px; }
.hb-5  { --size:30px;--left:36%; --dur:14s;--delay:-3s;  --sway:-22px; }
.hb-6  { --size:9px; --left:44%; --dur:6s; --delay:-5s;  --sway:-7px;  }
.hb-7  { --size:18px;--left:52%; --dur:11s;--delay:-9s;  --sway:15px;  }
.hb-8  { --size:7px; --left:60%; --dur:7s; --delay:-1s;  --sway:6px;   }
.hb-9  { --size:24px;--left:68%; --dur:13s;--delay:-6s;  --sway:-20px; }
.hb-10 { --size:11px;--left:75%; --dur:8s; --delay:-3.5s;--sway:9px;   }
.hb-11 { --size:14px;--left:82%; --dur:9s; --delay:-8s;  --sway:-12px; }
.hb-12 { --size:20px;--left:90%; --dur:11s;--delay:-2s;  --sway:16px;  }
.hb-13 { --size:6px; --left:17%; --dur:6s; --delay:-4.5s;--sway:5px;   }
.hb-14 { --size:13px;--left:33%; --dur:9s; --delay:-7.5s;--sway:-11px; }
.hb-15 { --size:28px;--left:48%; --dur:15s;--delay:-1.5s;--sway:24px;  }
.hb-16 { --size:9px; --left:58%; --dur:7s; --delay:-5.5s;--sway:-7px;  }
.hb-17 { --size:19px;--left:72%; --dur:10s;--delay:-3s;  --sway:16px;  }
.hb-18 { --size:8px; --left:86%; --dur:6.5s;--delay:-9s; --sway:-6px;  }
.hb-19 { --size:15px;--left:94%; --dur:8s; --delay:-2.5s;--sway:13px;  }
.hb-20 { --size:12px;--left:40%; --dur:9s; --delay:-6.5s;--sway:10px;  }
.hb-21 { --size:25px;--left:7%;  --dur:13s;--delay:-4s;  --sway:20px;  }
.hb-22 { --size:17px;--left:25%; --dur:10s;--delay:-8.5s;--sway:-14px; }

.hero-body { position:relative;z-index:2;flex:1;min-width:0; }
.hero-greet {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 900; letter-spacing: .4px;
    color: rgba(255,255,255,.9);
    background: rgba(255,255,255,.18); border: 1.5px solid rgba(255,255,255,.3);
    border-radius: 50px; padding: 3px 11px; margin-bottom: 10px;
}
.hero-name {
    font-family: 'Righteous', cursive; font-size: clamp(22px,3.2vw,32px);
    color: #fff; line-height: 1.2; margin-bottom: 6px;
    text-shadow: 0 2px 12px rgba(0,0,0,.2);
}
.hero-sub { font-size: clamp(12.5px,1.6vw,14px);font-weight:700;color:rgba(255,255,255,.82);margin-bottom:20px;line-height:1.5; }
.hero-sub strong { color:#fff;font-weight:900; }

.hero-stats {
    display: inline-flex; align-items: stretch;
    background: rgba(255,255,255,.14); border: 1.5px solid rgba(255,255,255,.28);
    border-radius: 14px; overflow: hidden;
}
.hstat { display:flex;align-items:center;gap:10px;padding:10px 18px;color:#fff; }
.hstat-icon { width:32px;height:32px;border-radius:9px;flex-shrink:0;background:rgba(255,255,255,.18);display:flex;align-items:center;justify-content:center; }
.hstat-text { display:flex;flex-direction:column; }
.hstat-val  { font-family:'Righteous',cursive;font-size:16px;line-height:1.2;color:#fff; }
.hstat-val small { font-family:'Nunito',sans-serif;font-size:11px;font-weight:700;opacity:.8; }
.hstat-lbl  { font-size:10px;font-weight:800;opacity:.72;text-transform:uppercase;letter-spacing:.6px; }
.hstat-sep  { width:1px;background:rgba(255,255,255,.22);align-self:stretch; }

.hero-deco {
    position:relative;z-index:2;flex-shrink:0;
    width:124px;height:124px;display:flex;align-items:center;justify-content:center;
}
.dring { position:absolute;border-radius:50%;border:1.5px solid rgba(255,255,255,.2); }
.dr-lg  { width:124px;height:124px;animation:spin-r 18s linear infinite; }
.dr-md  { width:88px; height:88px; animation:spin-r 11s linear infinite reverse; }
.dr-sm  { width:56px; height:56px; animation:spin-r 6s linear infinite; }
.dring-center {
    width:68px;height:68px;border-radius:20px;
    background:rgba(255,255,255,.18);border:2px solid rgba(255,255,255,.3);
    display:flex;align-items:center;justify-content:center;
    backdrop-filter:blur(8px);box-shadow:0 4px 16px rgba(0,0,0,.1);
}
@keyframes spin-r { to { transform: rotate(360deg); } }

/* ════════════════════════════════
   GRID
════════════════════════════════ */
.grid-section { padding: 18px 0 0; }
.grid-label { font-size:12px;font-weight:800;color:rgba(255,255,255,.9);text-shadow:0 1px 4px rgba(0,0,0,.2);margin-bottom:12px;letter-spacing:.2px; }

.mod-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:14px; }

/* ── CARD ── */
.mod-card {
    background: rgba(255,255,255,.96); border: 1.5px solid rgba(29,78,216,.12);
    border-radius: 18px; overflow: hidden;
    display: flex; flex-direction: column;
    opacity: 0; transform: translateY(24px) scale(.97);
    transition: opacity .42s var(--delay,0ms) ease, transform .42s var(--delay,0ms) cubic-bezier(.34,1.56,.64,1),
                box-shadow .2s ease, border-color .2s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,.08);
}
.mod-card.card-show { opacity:1;transform:none; }
.mod-card:hover { transform:translateY(-6px) scale(1.013);box-shadow:0 16px 40px rgba(0,0,0,.13),0 0 0 1.5px var(--ac);border-color:var(--ac); }
.mod-card.done { border-color:rgba(29,78,216,.28); }

.card-bar { height:4px; }
.card-thumb { position:relative;height:86px;display:flex;align-items:center;justify-content:center; }
.thumb-img  { width:100%;height:100%;object-fit:cover; }
.thumb-icon { width:54px;height:54px;border-radius:14px;border:2px solid;display:flex;align-items:center;justify-content:center; }
.fin-stamp {
    position:absolute;bottom:7px;right:7px;
    display:inline-flex;align-items:center;gap:4px;
    font-size:9.5px;font-weight:900;border-radius:50px;padding:2px 8px;border:1.5px solid;
}
.card-body { padding:12px 13px 13px;display:flex;flex-direction:column;gap:7px;flex:1; }
.mod-title { font-family:'Righteous',cursive;font-size:14.5px;color:#1e3a8a;line-height:1.35; }
.mod-desc  { font-size:11.5px;font-weight:700;color:#4b6a9b;line-height:1.55;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden; }
.score-row { display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:800;border-radius:50px;padding:3px 10px;width:fit-content; }
.score-row strong { font-weight:900; }
.divider { height:1px;background:rgba(29,78,216,.08); }
.chips { display:flex;gap:5px;flex-wrap:wrap; }
.chip { display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:800;border-radius:8px;padding:3px 8px; }

/* ── CTA ── */
.cta {
    display:flex;align-items:center;justify-content:center;gap:6px;
    width:100%;height:37px;border:none;border-radius:10px;
    font-family:'Righteous',cursive;font-size:13.5px;
    cursor:pointer;margin-top:auto;
    transition:all .17s cubic-bezier(.34,1.56,.64,1);
}
.cta:hover { transform:translateY(-2px);filter:brightness(1.07); }
.cta:active { transform:translateY(1px); }
.cta-new  { background:var(--btnbg,#1d4ed8);color:#fff;box-shadow:0 3px 10px rgba(29,78,216,.3); }
.cta-cont { background:linear-gradient(135deg,#3b82f6,#1d4ed8);color:#fff;box-shadow:0 3px 8px rgba(29,78,216,.28); }
.cta-done {
    background:linear-gradient(135deg,#34d399,#059669);color:#fff;
    box-shadow:0 3px 8px rgba(5,150,105,.25);cursor:not-allowed;opacity:.85;
    position:relative;overflow:hidden;
}
.cta-done:hover { transform:none !important;filter:none !important; }
.cta-done::after {
    content:'';position:absolute;inset:0;
    background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,.15) 50%,transparent 100%);
    animation:cta-shimmer 2.5s ease-in-out infinite;
}
@keyframes cta-shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }

/* ── EMPTY ── */
.empty { text-align:center;padding:72px 20px;display:flex;flex-direction:column;align-items:center;gap:10px; }
.empty-t { font-family:'Righteous',cursive;font-size:18px;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,.2); }
.empty-s { font-size:13px;font-weight:700;color:rgba(255,255,255,.7); }

/* ════════════════════════════════
   RESPONSIVE
════════════════════════════════ */
@media (max-width: 1100px) { .mod-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 820px) {
    .b1{width:300px;height:300px;} .b2{width:240px;height:240px;} .b3{width:180px;height:180px;}
    .c1{width:100px;height:100px;} .c2{display:none;} .r1{width:200px;height:200px;}
    .topbar { height:52px;padding:0 13px; }
    .brand-name{font-size:16px;} .brand-dot{width:25px;height:25px;}
    .mod-grid { grid-template-columns: repeat(2,1fr); }
    .hero-card { flex-direction:column;align-items:flex-start;padding:22px 20px 24px;gap:18px; }
    .hero-deco { align-self:flex-end;margin-top:-80px;width:100px;height:100px; }
    .dr-lg{width:100px;height:100px;} .dr-md{width:70px;height:70px;} .dr-sm{width:44px;height:44px;}
    .dring-center{width:54px;height:54px;border-radius:15px;}
}
@media (max-width: 600px) {
    .wrap { padding: 0 14px; }
    .hero-card { padding:18px 16px 20px;border-radius:20px; }
    .hero-deco { display:none; }
    .hero-name{font-size:22px;} .hero-sub{font-size:12.5px;margin-bottom:14px;}
    .hero-stats{width:100%;} .hstat{flex:1;}
    .brand-name{font-size:16px;}
}
@media (max-width: 480px) {
    .topbar{height:48px;padding:0 11px;}
    .pill-name{display:none;}
    .mod-grid{grid-template-columns:repeat(2,1fr);gap:10px;}
    .hero-name{font-size:20px;} .hstat{padding:8px 12px;gap:7px;}
    .hstat-val{font-size:14px;} .hstat-icon{width:28px;height:28px;}
    .card-thumb{height:72px;}
}
@media (max-width: 340px) { .mod-grid { grid-template-columns: 1fr; } }
</style>
