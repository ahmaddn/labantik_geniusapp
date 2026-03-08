//ini page daftar modul
<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import {
    BookOpen,
    Star,
    CheckCircle2,
    FileQuestion,
    ChevronDown,
    RefreshCw,
    Play,
    Sparkles,
    LogOut,
    GraduationCap,
    Trophy,
    Zap,
} from "lucide-vue-next";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    user: {
        type: Object,
        default: () => ({ name: "Siswa", class: { name: "-" } }),
    },
    learningModules: { type: Array, default: () => [] },
});

const ready = ref(false);
const dropdownOpen = ref(false);
const menuRef = ref(null);
const musicOn = ref(false);
const audioRef = ref(null);

const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : musicOn.value && audioRef.value.play().catch(() => {});
};
const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/backsound.mp3");
        audioRef.value.loop = true;
        audioRef.value.volume = 0.4;
        audioRef.value.preload = "auto";
        audioRef.value.addEventListener("error", () => {
            audioRef.value = null;
            musicOn.value = false;
        });
    }
    if (musicOn.value) {
        audioRef.value.pause();
        musicOn.value = false;
    } else {
        try {
            await audioRef.value.play();
            musicOn.value = true;
        } catch (e) {
            musicOn.value = false;
        }
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
});
onUnmounted(() => {
    document.removeEventListener("mousedown", handleClickOutside);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) {
        audioRef.value.pause();
        audioRef.value = null;
    }
});

const totalFinished = computed(
    () => props.learningModules.filter((m) => m.fully_completed).length,
);
const statusLabel = (m) =>
    m.fully_completed ? "Selesai" : m.has_attempt ? "Lanjutkan" : "Mulai";
const scoreColor = (s) =>
    s >= 80 ? "#16a34a" : s >= 60 ? "#ca8a04" : "#dc2626";
const scoreBg = (s) =>
    s >= 80
        ? "rgba(22,163,74,.1)"
        : s >= 60
          ? "rgba(202,138,4,.1)"
          : "rgba(220,38,38,.1)";

// ── DIUBAH: klik modul → pretest dulu ──────────────────────────────
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
    <div style="display: none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap"
            rel="stylesheet"
        />
    </div>

    <div class="pg">
        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <div class="wrap">
                <div class="brand">
                    <div class="brand-icon">
                        <Zap :size="16" color="#fff" :stroke-width="2.5" fill="white" />
                    </div>
                    <span class="brand-name">Geniuss</span>
                </div>
            </div>

            <div class="user-menu" ref="menuRef">
                <button
                    class="user-pill"
                    @click="dropdownOpen = !dropdownOpen"
                    :class="{ open: dropdownOpen }"
                >
                    <div class="avatar-sm">{{ user.name.charAt(0) }}</div>
                    <span class="pill-name">{{ user.name.split(" ")[0] }}</span>
                    <ChevronDown :size="13" :stroke-width="2.8" class="pill-chev" />
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
                            <LogOut :size="14" :stroke-width="2.3" />
                            Keluar
                        </button>
                    </div>
                </Transition>
            </div>
        </header>

        <!-- ══ HERO ══ -->
        <section class="hero-section" :class="{ show: ready }">
            <div class="wrap">
                <div class="hero-card">
<div v-for="n in 30" :key="n" class="bubble" :class="`bubble-${n}`"></div>
                    <div class="hero-body">
                        <div class="hero-greet">
                            <Sparkles :size="12" :stroke-width="2.3" />
                            Selamat datang kembali!
                        </div>
                        <h1 class="hero-name">Halo, {{ user.name.split(" ")[0] }}! 👋</h1>
                        <p class="hero-sub">
                            Ada <strong>{{ learningModules.length }}</strong> modul
                            seru yang siap dijelajahi hari ini.
                        </p>

                        <div class="hero-stats">
                            <div class="hstat">
                                <div class="hstat-icon">
                                    <GraduationCap :size="16" :stroke-width="2.3" />
                                </div>
                                <div class="hstat-text">
                                    <span class="hstat-val">{{ user.class?.name }}</span>
                                    <span class="hstat-lbl">Kelas Saya</span>
                                </div>
                            </div>
                            <div class="hstat-sep"></div>
                            <div class="hstat">
                                <div class="hstat-icon">
                                    <Trophy :size="16" :stroke-width="2.3" />
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

                    <div class="hero-deco" aria-hidden="true">
                        <div class="dring dr-lg"></div>
                        <div class="dring dr-md"></div>
                        <div class="dring dr-sm"></div>
                        <div class="dring-center">
                            <BookOpen :size="32" color="rgba(255,255,255,.92)" :stroke-width="1.6" />
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ══ GRID ══ -->
        <main class="grid-section" :class="{ show: ready }">
            <div class="wrap">
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
                                <img :src="mod.thumbnail" :alt="mod.name" class="thumb-img" />
                            </template>
                            <template v-else>
                                <div
                                    class="thumb-icon"
                                    :style="{ background: accent(i) + '1e', borderColor: accent(i) + '55' }"
                                >
                                    <BookOpen :size="24" :color="accent(i)" :stroke-width="1.8" />
                                </div>
                            </template>

                            <div
                                v-if="mod.fully_completed"
                                class="fin-stamp"
                                :style="{ color: accent(i), borderColor: accent(i) + '55', background: accent(i) + '15' }"
                            >
                                <CheckCircle2 :size="11" :stroke-width="2.5" />
                                Selesai
                            </div>
                        </div>

                        <div class="card-body">
                            <h3 class="mod-title">{{ mod.name }}</h3>
                            <p class="mod-desc">{{ mod.description }}</p>

                            <div
                                v-if="mod.has_attempt"
                                class="score-row"
                                :style="{ color: scoreColor(mod.best_score), background: scoreBg(mod.best_score) }"
                            >
                                <Star :size="10" fill="currentColor" :stroke-width="0" />
                                Skor terbaik: <strong>{{ mod.best_score }}</strong>
                            </div>

                            <div class="divider"></div>

                            <div class="chips">
                                <span
                                    class="chip"
                                    :style="{ color: accent(i), background: accent(i) + '14' }"
                                >
                                    <FileQuestion :size="10" :stroke-width="2.5" />
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
                    <BookOpen :size="40" color="#3b82f6" :stroke-width="1.4" />
                    <p class="empty-t">Belum ada modul</p>
                    <p class="empty-s">Modul akan segera tersedia.</p>
                </div>
            </div>
        </main>

        <!-- ══ MUSIC FAB ══ -->
        <button
            class="music-fab"
            :class="{ on: musicOn }"
            @click="toggleMusic"
            :title="musicOn ? 'Matikan musik' : 'Nyalakan musik'"
        >
            <svg v-if="musicOn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M9 18V5l12-2v13" />
                <circle cx="6" cy="18" r="3" />
                <circle cx="18" cy="16" r="3" />
            </svg>
            <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M9 18V5l12-2v13" />
                <circle cx="6" cy="18" r="3" />
                <circle cx="18" cy="16" r="3" />
                <line x1="1" y1="1" x2="23" y2="23" />
            </svg>
            <span v-if="musicOn" class="fab-pulse"></span>
        </button>
    </div>
</template>

<style scoped>
*,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }

.pg {
    min-height: 100vh;
    font-family: "Nunito", sans-serif;
    padding-bottom: 96px;
    background: url("/images/templates/background.png") top center / cover no-repeat fixed;
}
.wrap { max-width: 1180px; margin: 0 auto; padding: 0 20px; }
.row-between { display: flex; align-items: center; justify-content: space-between; }

/* ── TOPBAR ── */
.topbar {
    position: sticky; top: 0; z-index: 100;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
    border-bottom: 1px solid rgba(255,255,255,0.35);
    padding: 0 32px; z-index: 20;
    box-shadow: 0 1px 18px rgba(0,0,0,0.08);
    display: flex; align-items: center;
}
.topbar .wrap { padding-top: 10px; padding-bottom: 10px; flex: 1; display: flex; align-items: center; }
.brand { display: flex; align-items: center; gap: 9px; }
.brand-icon {
    width: 32px; height: 32px; border-radius: 10px;
    background: linear-gradient(140deg,#60a5fa,#1d4ed8);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(29,78,216,0.38);
}
.brand-name { font-family: "Righteous",cursive; font-size: 20px; color: #1e3a8a; letter-spacing: -0.2px; }

/* ── USER PILL ── */
.user-menu { position: relative; flex-shrink: 0; padding: 10px 0; }
.user-pill {
    display: flex; align-items: center; gap: 7px;
    padding: 5px 11px 5px 5px;
    background: rgba(255,255,255,0.9);
    border: 1.5px solid rgba(29,78,216,0.22);
    border-radius: 50px; cursor: pointer; outline: none; transition: all 0.18s;
}
.user-pill:hover,.user-pill.open { background: #fff; border-color: rgba(29,78,216,0.5); box-shadow: 0 3px 14px rgba(29,78,216,0.14); }
.avatar-sm {
    width: 28px; height: 28px; border-radius: 50%;
    background: linear-gradient(135deg,#60a5fa,#1d4ed8);
    color: #fff; font-weight: 900; font-size: 13px;
    display: flex; align-items: center; justify-content: center;
}
.pill-name { font-size: 12.5px; font-weight: 800; color: #1e3a8a; }
.pill-chev { color: #1d4ed8; flex-shrink: 0; transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1); }
.user-pill.open .pill-chev { transform: rotate(180deg); }

/* ── DROPDOWN ── */
.dropdown {
    position: absolute; top: calc(100% + 8px); right: 0; width: 210px;
    background: rgba(255,255,255,0.98);
    border: 1.5px solid rgba(29,78,216,0.16);
    border-radius: 16px;
    box-shadow: 0 16px 48px rgba(29,78,216,0.14), 0 2px 8px rgba(0,0,0,0.05);
    overflow: hidden; z-index: 200;
}
.dd-top { display: flex; align-items: center; gap: 10px; padding: 13px 13px 11px; }
.dd-ava {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg,#60a5fa,#1d4ed8);
    color: #fff; font-weight: 900; font-size: 16px;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}
.dd-nm { font-size: 13px; font-weight: 900; color: #1e3a8a; line-height: 1.3; }
.dd-kls { font-size: 11px; font-weight: 700; color: #6b9fd4; }
.dd-line { height: 1px; background: rgba(29,78,216,0.1); margin: 0 10px; }
.dd-row {
    display: flex; align-items: center; gap: 9px; width: 100%;
    padding: 10px 13px; font-family: "Nunito",sans-serif;
    font-size: 13px; font-weight: 800; background: none; border: none;
    cursor: pointer; text-align: left; transition: background 0.13s;
}
.dd-out { color: #dc2626; }
.dd-out:hover { background: rgba(220,38,38,0.06); }
.dd-enter-active { transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1); }
.dd-leave-active { transition: all 0.14s ease; }
.dd-enter-from,.dd-leave-to { opacity: 0; transform: translateY(-6px) scale(0.97); }

/* ── HERO ── */
.hero-section {
    padding: 20px 0 0;
    opacity: 0; transform: translateY(22px);
    transition: opacity 0.55s ease, transform 0.55s cubic-bezier(0.34,1.56,0.64,1);
}
.hero-section.show { opacity: 1; transform: none; }
.hero-card {
    position: relative; overflow: hidden;
    background: linear-gradient(130deg,#3b82f6 0%,#1d4ed8 50%,#1e40af 100%);
    border-radius: 24px; padding: 28px 32px;
    display: flex; align-items: center; justify-content: space-between; gap: 20px;
    box-shadow: 0 12px 44px rgba(29,78,216,0.38), 0 4px 0 rgba(14,57,165,0.22), inset 0 1px 0 rgba(255,255,255,0.18);
}
/* ── BUBBLES ── */
.bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    backdrop-filter: blur(2px);
    pointer-events: none;
    animation: bubble-rise var(--dur, 6s) var(--delay, 0s) ease-in infinite;
    bottom: -40px;
    left: var(--left, 50%);
    width: var(--size, 20px);
    height: var(--size, 20px);
}

@keyframes bubble-rise {
    0%   { transform: translateY(0)      translateX(0);                 opacity: 0.6; }
    90%  { opacity: 0.4; }
    100% { transform: translateY(-420px) translateX(var(--sway, 12px)); opacity: 0;   }
}

.bubble {
    position: absolute;
    border-radius: 50%;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.22);
    backdrop-filter: blur(2px);
    pointer-events: none;
    bottom: -40px;
    left: var(--left, 50%);
    width: var(--size, 20px);
    height: var(--size, 20px);
    animation: bubble-rise var(--dur, 6s) var(--delay, 0s) linear infinite;
}

.bubble-1  { --size:12px; --left:8%;   --dur:7s;   --delay:-3.5s;  --sway:14px;  }
.bubble-2  { --size:55px; --left:15%;  --dur:13s;  --delay:-2s;    --sway:24px;  }
.bubble-3  { --size:9px;  --left:22%;  --dur:6s;   --delay:-5s;    --sway:8px;   }
.bubble-4  { --size:20px; --left:30%;  --dur:9s;   --delay:-7s;    --sway:-10px; }
.bubble-5  { --size:62px; --left:37%;  --dur:15s;  --delay:-1s;    --sway:-28px; }
.bubble-6  { --size:10px; --left:44%;  --dur:6.5s; --delay:-4s;    --sway:-7px;  }
.bubble-7  { --size:26px; --left:51%;  --dur:11s;  --delay:-9s;    --sway:-18px; }
.bubble-8  { --size:8px;  --left:57%;  --dur:7.5s; --delay:-3s;    --sway:-5px;  }
.bubble-9  { --size:48px; --left:63%;  --dur:12s;  --delay:-6s;    --sway:20px;  }
.bubble-10 { --size:11px; --left:70%;  --dur:6s;   --delay:-2.5s;  --sway:-9px;  }
.bubble-11 { --size:22px; --left:76%;  --dur:10s;  --delay:-8s;    --sway:16px;  }
.bubble-12 { --size:70px; --left:38%;  --dur:16s;  --delay:-12s;   --sway:-32px; }
.bubble-13 { --size:13px; --left:82%;  --dur:8s;   --delay:-1.5s;  --sway:-13px; }
.bubble-14 { --size:17px; --left:93%;  --dur:9.5s; --delay:-5.5s;  --sway:12px;  }
.bubble-15 { --size:58px; --left:11%;  --dur:14s;  --delay:-4s;    --sway:26px;  }
.bubble-16 { --size:10px; --left:48%;  --dur:7s;   --delay:-7.5s;  --sway:10px;  }
.bubble-17 { --size:21px; --left:26%;  --dur:11s;  --delay:-3s;    --sway:17px;  }
.bubble-18 { --size:9px;  --left:67%;  --dur:6.5s; --delay:-9.5s;  --sway:-8px;  }
.bubble-19 { --size:16px; --left:5%;   --dur:8s;   --delay:-2s;    --sway:12px;  }
.bubble-20 { --size:24px; --left:19%;  --dur:12s;  --delay:-6s;    --sway:20px;  }
.bubble-21 { --size:7px;  --left:34%;  --dur:5.5s; --delay:-3.5s;  --sway:6px;   }
.bubble-22 { --size:15px; --left:55%;  --dur:9s;   --delay:-1s;    --sway:-11px; }
.bubble-23 { --size:19px; --left:72%;  --dur:9s;   --delay:-8s;    --sway:15px;  }
.bubble-24 { --size:14px; --left:85%;  --dur:8s;   --delay:-4.5s;  --sway:10px;  }
.bubble-25 { --size:23px; --left:96%;  --dur:10s;  --delay:-7s;    --sway:19px;  }
.bubble-26 { --size:18px; --left:12%;  --dur:10s;  --delay:-5s;    --sway:-15px; }
.bubble-27 { --size:8px;  --left:43%;  --dur:6s;   --delay:-10s;   --sway:-6px;  }
.bubble-28 { --size:13px; --left:59%;  --dur:7.5s; --delay:-2.5s;  --sway:-11px; }
.bubble-29 { --size:11px; --left:78%;  --dur:7s;   --delay:-6.5s;  --sway:-9px;  }
.bubble-30 { --size:17px; --left:91%;  --dur:11s;  --delay:-3s;    --sway:22px;  }
.hero-body { position: relative; z-index: 2; flex: 1; min-width: 0; }
.hero-greet {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 900; letter-spacing: 0.4px;
    color: rgba(255,255,255,0.9);
    background: rgba(255,255,255,0.18); border: 1.5px solid rgba(255,255,255,0.3);
    border-radius: 50px; padding: 3px 11px; margin-bottom: 10px;
}
.hero-name {
    font-family: "Righteous",cursive; font-size: clamp(22px,3.2vw,32px);
    color: #fff; line-height: 1.2; margin-bottom: 6px;
    text-shadow: 0 2px 10px rgba(14,57,165,0.3);
}
.hero-sub { font-size: clamp(12.5px,1.6vw,14px); font-weight: 700; color: rgba(255,255,255,0.82); margin-bottom: 20px; line-height: 1.5; }
.hero-sub strong { color: #fff; font-weight: 900; }
.hero-stats {
    display: inline-flex; align-items: stretch;
    background: rgba(255,255,255,0.14); border: 1.5px solid rgba(255,255,255,0.28);
    border-radius: 14px; overflow: hidden;
}
.hstat { display: flex; align-items: center; gap: 10px; padding: 10px 18px; color: #fff; }
.hstat-icon { width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0; background: rgba(255,255,255,0.18); display: flex; align-items: center; justify-content: center; }
.hstat-text { display: flex; flex-direction: column; }
.hstat-val { font-family: "Righteous",cursive; font-size: 16px; line-height: 1.2; color: #fff; }
.hstat-val small { font-family: "Nunito",sans-serif; font-size: 11px; font-weight: 700; opacity: 0.8; }
.hstat-lbl { font-size: 10px; font-weight: 800; opacity: 0.72; text-transform: uppercase; letter-spacing: 0.6px; }
.hstat-sep { width: 1px; background: rgba(255,255,255,0.22); align-self: stretch; }
.hero-deco { position: relative; z-index: 2; flex-shrink: 0; width: 124px; height: 124px; display: flex; align-items: center; justify-content: center; }
.dring { position: absolute; border-radius: 50%; border: 1.5px solid rgba(255,255,255,0.2); }
.dr-lg  { width: 124px; height: 124px; animation: spin-r 18s linear infinite; }
.dr-md  { width: 88px;  height: 88px;  animation: spin-r 11s linear infinite reverse; }
.dr-sm  { width: 56px;  height: 56px;  animation: spin-r 6s linear infinite; }
.dring-center {
    width: 68px; height: 68px; border-radius: 20px;
    background: rgba(255,255,255,0.18); border: 2px solid rgba(255,255,255,0.3);
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(8px); box-shadow: 0 4px 16px rgba(0,0,0,0.1);
}
@keyframes spin-r { to { transform: rotate(360deg); } }

/* ── GRID ── */
.grid-section {
    padding: 20px 0 0;
    opacity: 0; transform: translateY(8px);
    transition: opacity 0.4s 0.16s ease, transform 0.4s 0.16s cubic-bezier(0.34,1.56,0.64,1);
}
.grid-section.show { opacity: 1; transform: none; }
.grid-label { font-size: 12px; font-weight: 800; color: rgba(255,255,255,0.9); text-shadow: 0 1px 4px rgba(0,0,0,0.2); margin-bottom: 12px; letter-spacing: 0.2px; }
.mod-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 14px; }

/* ── CARD ── */
.mod-card {
    background: #fff; border: 1.5px solid rgba(29,78,216,0.12);
    border-radius: 18px; overflow: hidden;
    display: flex; flex-direction: column;
    opacity: 0; transform: translateY(24px) scale(0.97);
    transition: opacity 0.42s var(--delay,0ms) ease, transform 0.42s var(--delay,0ms) cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,0.07);
}
.mod-card.card-show { opacity: 1; transform: none; }
.mod-card:hover { transform: translateY(-6px) scale(1.013); box-shadow: 0 16px 40px rgba(0,0,0,0.13), 0 0 0 1.5px var(--ac); border-color: var(--ac); }
.mod-card.done { border-color: rgba(29,78,216,0.28); }
.card-bar { height: 4px; }
.card-thumb { position: relative; height: 86px; display: flex; align-items: center; justify-content: center; }
.thumb-img { width: 100%; height: 100%; object-fit: cover; }
.thumb-icon { width: 54px; height: 54px; border-radius: 14px; border: 2px solid; display: flex; align-items: center; justify-content: center; }
.fin-stamp {
    position: absolute; bottom: 7px; right: 7px;
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 9.5px; font-weight: 900; border-radius: 50px; padding: 2px 8px; border: 1.5px solid;
}
.card-body { padding: 12px 13px 13px; display: flex; flex-direction: column; gap: 7px; flex: 1; }
.mod-title { font-family: "Righteous",cursive; font-size: 14.5px; color: #1e3a8a; line-height: 1.35; }
.mod-desc { font-size: 11.5px; font-weight: 700; color: #4b6a9b; line-height: 1.55; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.score-row { display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; font-weight: 800; border-radius: 50px; padding: 3px 10px; width: fit-content; }
.score-row strong { font-weight: 900; }
.divider { height: 1px; background: rgba(29,78,216,0.08); }
.chips { display: flex; gap: 5px; flex-wrap: wrap; }
.chip { display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; border-radius: 8px; padding: 3px 8px; }

/* ── CTA ── */
.cta {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; height: 37px; border: none; border-radius: 10px;
    font-family: "Righteous",cursive; font-size: 13.5px;
    cursor: pointer; margin-top: auto;
    transition: all 0.17s cubic-bezier(0.34,1.56,0.64,1);
}
.cta:hover { transform: translateY(-2px); filter: brightness(1.07); }
.cta:active { transform: translateY(1px); }
.cta-new  { background: var(--btnbg,#1d4ed8); color: #fff; box-shadow: 0 3px 10px rgba(29,78,216,0.3); }
.cta-cont { background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; box-shadow: 0 3px 8px rgba(29,78,216,0.28); }
.cta-done {
    background: linear-gradient(135deg, #34d399, #059669);
    color: #fff;
    box-shadow: 0 3px 8px rgba(5,150,105,0.25);
    cursor: not-allowed;
    opacity: 0.85;
    position: relative;
    overflow: hidden;
}
.cta-done:hover { transform: none !important; filter: none !important; }
.cta-done::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
    animation: cta-shimmer 2.5s ease-in-out infinite;
}
@keyframes cta-shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

/* ── EMPTY ── */
.empty { text-align: center; padding: 72px 20px; display: flex; flex-direction: column; align-items: center; gap: 10px; }
.empty-t { font-family: "Righteous",cursive; font-size: 18px; color: #1e3a8a; }
.empty-s { font-size: 13px; font-weight: 700; color: #6b9fd4; }

/* ── MUSIC FAB ── */
.music-fab {
    position: fixed; bottom: 26px; left: 26px; z-index: 301;
    width: 50px; height: 50px; border-radius: 50%; border: none;
    cursor: pointer; outline: none;
    background: rgba(255,255,255,0.92); backdrop-filter: blur(10px);
    color: #1d4ed8; box-shadow: 0 4px 20px rgba(29,78,216,0.22);
    display: flex; align-items: center; justify-content: center;
    transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
.music-fab:hover { transform: scale(1.1); background: #fff; }
.music-fab.on { background: linear-gradient(135deg,#60a5fa,#1d4ed8); color: #fff; box-shadow: 0 6px 24px rgba(29,78,216,0.44); }
.music-fab svg { width: 21px; height: 21px; }
.fab-pulse { position: absolute; inset: -5px; border-radius: 50%; border: 2px solid rgba(29,78,216,0.4); animation: fab-ring 2s ease-out infinite; pointer-events: none; }
@keyframes fab-ring { 0% { transform: scale(1); opacity: 0.8; } 100% { transform: scale(1.55); opacity: 0; } }

/* ── RESPONSIVE ── */
@media (max-width: 1100px) { .mod-grid { grid-template-columns: repeat(3,1fr); } }
@media (max-width: 768px) {
    .mod-grid { grid-template-columns: repeat(2,1fr); }
    .hero-card { flex-direction: column; align-items: flex-start; padding: 22px 20px 24px; gap: 18px; }
    .hero-deco { align-self: flex-end; margin-top: -80px; width: 100px; height: 100px; }
    .dr-lg { width: 100px; height: 100px; } .dr-md { width: 70px; height: 70px; } .dr-sm { width: 44px; height: 44px; }
    .dring-center { width: 54px; height: 54px; border-radius: 15px; }
}
@media (max-width: 600px) {
    .wrap { padding: 0 14px; }
    .hero-card { padding: 18px 16px 20px; border-radius: 20px; }
    .hero-deco { display: none; }
    .hero-name { font-size: 22px; } .hero-sub { font-size: 12.5px; margin-bottom: 14px; }
    .hero-stats { width: 100%; } .hstat { flex: 1; }
    .brand-name { font-size: 18px; }
}
@media (max-width: 420px) {
    .mod-grid { grid-template-columns: repeat(2,1fr); gap: 10px; }
    .hero-name { font-size: 20px; } .hstat { padding: 8px 12px; gap: 7px; }
    .hstat-val { font-size: 14px; } .hstat-icon { width: 28px; height: 28px; }
    .card-thumb { height: 72px; }
    .music-fab { bottom: 18px; left: 26px; width: 44px; height: 44px; }
}
@media (max-width: 340px) { .mod-grid { grid-template-columns: 1fr; } }
</style>
