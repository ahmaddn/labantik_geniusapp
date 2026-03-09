<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import {
    Zap, LogIn, User,
    Rocket, Music2, VolumeX, ArrowLeft,
    GraduationCap, Sparkles, Star, BookOpen,
    Shield,
} from "lucide-vue-next";

const ready      = ref(false);
const brandMoved = ref(false);
const musicOn    = ref(false);
const audioRef   = ref(null);

// ── Speech bubble ──────────────────────────────────────────────────
const BUBBLE_LINES = [
    "Hai! Aku Geni 👋",
    "Udah siap belajar? 😎",
    "Kamu pasti bisa! 💪",
    "Yuk, masuk dulu~",
    "Siap jadi jagoan Geniuss? 🚀",
    "Ayo kita berkompetisi 🏆",
    "Halo, sobat Geniuss! 😊",
];
const bubbleIdx     = ref(0);
const bubbleVisible = ref(true);
let   bubbleTimer   = null;

const rotateBubble = () => {
    bubbleVisible.value = false;
    setTimeout(() => {
        bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLE_LINES.length;
        bubbleVisible.value = true;
    }, 300);
};

// ── Form ───────────────────────────────────────────────────────────
const form        = useForm({ nama: "" });
const loginUrl    = route("login.admin");
const localErrors = ref({ nama: "" });
const shakeBtn    = ref(false);

const errors = {
    get nama() { return localErrors.value.nama || form.errors.nama || ""; },
};

// ── Music ──────────────────────────────────────────────────────────
const handleVisibility = () => {
    if (!audioRef.value) return;
    document.hidden
        ? audioRef.value.pause()
        : (musicOn.value && audioRef.value.play().catch(() => {}));
};

const toggleMusic = async () => {
    if (!audioRef.value) {
        audioRef.value = new Audio("/backsound/intro-song.mp3");
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
    audioRef.value = new Audio("/backsound/intro-song.mp3");
    audioRef.value.loop   = true;
    audioRef.value.volume = 0.4;
    audioRef.value.addEventListener("error", () => { audioRef.value = null; musicOn.value = false; });
    try { await audioRef.value.play(); musicOn.value = true; }
    catch { musicOn.value = false; }
};

// ── Validation ─────────────────────────────────────────────────────
function validateNama() {
    if (!form.nama.trim()) { localErrors.value.nama = "Username wajib diisi!"; return false; }
    localErrors.value.nama = ""; return true;
}

function handleLogin() {
    if (!validateNama()) {
        shakeBtn.value = true;
        setTimeout(() => (shakeBtn.value = false), 600);
        return;
    }
    form.post(route("playground.authenticate"), {
        onSuccess: () => router.visit(route("playground.index")),
        onError: () => {
            shakeBtn.value = true;
            setTimeout(() => (shakeBtn.value = false), 600);
        },
    });
}

function goBack() { router.visit(loginUrl); }

onMounted(() => {
    setTimeout(() => { ready.value = true; brandMoved.value = true; }, 80);
    setTimeout(() => { bubbleTimer = setInterval(rotateBubble, 2800); }, 1200);
    document.addEventListener("visibilitychange", handleVisibility);
    setTimeout(() => { initAutoMusic(); }, 500);
});
onUnmounted(() => {
    clearInterval(bubbleTimer);
    document.removeEventListener("visibilitychange", handleVisibility);
    if (audioRef.value) { audioRef.value.pause(); audioRef.value = null; }
});
</script>

<template>
    <div style="display:none">
        <link rel="preconnect" href="https://fonts.googleapis.com"/>
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
    </div>

    <div class="root">

        <!-- ══ BG ══ -->
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

        <!-- ══ TOPBAR ══ -->
        <header class="topbar">
            <button class="tbtn" @click="goBack">
                <ArrowLeft :size="16" :stroke-width="2.5"/>
                <span class="tbtn-lbl">Login Ke Admin</span>
            </button>

            <div class="brand" :class="{ 'brand--hide': brandMoved }">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <div class="brand brand--center">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
                <span class="brand-name">Geniuss</span>
            </div>

            <div class="topbar-r">
                <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic">
                    <Music2 v-if="musicOn" :size="15" :stroke-width="2"/>
                    <VolumeX v-else        :size="15" :stroke-width="2"/>
                </button>
            </div>
        </header>

        <!-- ══ BODY ══ -->
        <div class="body" :class="{ 'body--on': ready }">

            <!-- ── SIDEBAR ── -->
            <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
                <div class="sb-info">
                    <span class="sb-chip">
                        <GraduationCap :size="11" :stroke-width="2.5"/>
                        Playground
                    </span>
                    <h1 class="sb-title">Selamat Datang!</h1>
                    <p class="sb-sub">Masuk dan mulai petualangan belajarmu bersama Geniuss hari ini.</p>

                    <div class="sb-pills">
                        <div class="sb-pill">
                            <BookOpen :size="13" :stroke-width="2"/>
                            <span>Mode Belajar</span>
                        </div>
                        <div class="sb-pill">
                            <Sparkles :size="13" :stroke-width="2"/>
                            <span>Interaktif</span>
                        </div>
                    </div>
                </div>

                <!-- Mascot + Bubble -->
                <div class="mascot-wrap">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble bubble--intro">
                            <span>{{ BUBBLE_LINES[bubbleIdx] }}</span>
                            <i class="bbl-o"></i>
                            <i class="bbl-i"></i>
                        </div>
                    </Transition>
                    <div class="mascot-frame">
                        <img src="/images/templates/pose_keren.png" alt="Maskot Geniuss" class="mascot"/>
                        <div class="mascot-shadow"></div>
                    </div>
                </div>
            </aside>

            <!-- ── MAIN PANEL ── -->
            <section class="main" :class="{ 'main--on': ready }">

                <!-- ══ LOGIN CARD ══ -->
                <div class="icard">

                    <!-- Blue header band -->
                    <div class="icard-head">
                        <div class="icard-hdeco icard-hdeco-1"></div>
                        <div class="icard-hdeco icard-hdeco-2"></div>
                        <div class="icard-head-inner">
                            <div class="icard-eyebrow">
                                <GraduationCap :size="11" :stroke-width="2.5"/>
                                <span>Playground Geniuss</span>
                            </div>
                            <h2 class="icard-title">Masuk ke Playground</h2>
                            <p class="icard-sub">
                                Masukkan username dari gurumu untuk memulai.
                            </p>
                        </div>
                    </div>

                    <!-- 3 stat tiles -->
                    <div class="icard-stats">
                        <div class="istat istat--red">
                            <div class="istat-icon"><Rocket :size="19" :stroke-width="1.8"/></div>
                            <span class="istat-val">Fun</span>
                            <span class="istat-lbl">Belajar</span>
                        </div>
                        <div class="istat istat--yellow">
                            <div class="istat-icon"><Star :size="19" :stroke-width="1.8" fill="currentColor"/></div>
                            <span class="istat-val">XP</span>
                            <span class="istat-lbl">Reward</span>
                        </div>
                        <div class="istat istat--green">
                            <div class="istat-icon"><Shield :size="19" :stroke-width="1.8"/></div>
                            <span class="istat-val">Aman</span>
                            <span class="istat-lbl">Terjaga</span>
                        </div>
                    </div>

                    <!-- Form body -->
                    <div class="icard-body">

                        <!-- Field: Username saja -->
                        <div class="fw">
                            <label class="flabel">Username <span class="req">*</span></label>
                            <div class="if-wrap">
                                <User :size="16" :stroke-width="2.2" class="if-icon"/>
                                <input
                                    v-model="form.nama"
                                    type="text"
                                    placeholder="Tulis username"
                                    maxlength="60"
                                    autocomplete="off"
                                    :class="['if-input', errors.nama ? 'if-err' : 'if-blue']"
                                    @focus="localErrors.nama = ''; form.clearErrors('nama')"
                                    @blur="validateNama()"
                                    @input="localErrors.nama = ''; form.clearErrors('nama')"
                                    @keyup.enter="handleLogin"
                                />
                            </div>
                            <p v-if="errors.nama" class="errmsg">{{ errors.nama }}</p>
                        </div>

                    </div>
                </div>

            </section>
        </div>

        <!-- ══ FOOTER ══ -->
        <footer class="footer">
            <div class="footer-inner">
                <div class="f-space"></div>
                <button
                    class="fbtn fbtn--yellow"
                    :class="{ shake: shakeBtn, loading: form.processing }"
                    :disabled="form.processing"
                    @click="handleLogin">
                    <template v-if="!form.processing">
                        <LogIn :size="14" :stroke-width="2.5"/>
                        <span>Masuk Sekarang</span>
                    </template>
                    <template v-else>
                        <span class="spinner"></span>
                        <span>Memproses…</span>
                    </template>
                </button>
            </div>
        </footer>

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
   BG
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
   TOPBAR
════════════════════════════════ */
.topbar {
    position: relative; z-index: 50; height: 56px; flex-shrink: 0;
    display: flex; align-items: center; padding: 0 18px;
    background: rgba(255,255,255,.10); backdrop-filter: blur(18px);
    border-bottom: 1px solid rgba(255,255,255,.16);
}
.tbtn {
    display: flex; align-items: center; gap: 6px; padding: 7px 13px;
    background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.22);
    border-radius: 10px; font-family: 'Nunito', sans-serif;
    font-size: 13px; font-weight: 800; color: #fff; cursor: pointer;
    transition: background .18s, transform .15s; flex-shrink: 0;
    text-decoration: none;
}
.tbtn:hover { background: rgba(255,255,255,.22); transform: translateY(-1px); }
.tbtn-sq  { padding: 7px 10px; }
.tbtn--on { background: #2563EB; border-color: #BFDBFE; }

.brand {
    position: absolute; left: 50%; transform: translateX(-50%);
    display: flex; align-items: center; gap: 8px;
    pointer-events: none; z-index: 2;
    transition: opacity .34s, transform .34s;
    opacity: 0;
}
.brand--hide { opacity: 0; transform: translateX(-50%) scale(.88); }

.brand--center {
    opacity: 1 !important;
    transform: translateX(-50%) !important;
}

.brand-dot  { width:28px;height:28px;border-radius:8px;background:#2563EB;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,.5); }
.brand-name { font-family:'Righteous',cursive;font-size:18px;color:#fff;white-space:nowrap; }
.topbar-r   { display:flex;align-items:center;gap:8px;margin-left:auto;z-index:3; }

/* ════════════════════════════════
   BODY GRID
════════════════════════════════ */
.body {
    position: relative; z-index: 10; flex: 1;
    display: grid; grid-template-columns: 264px 1fr; gap: 20px;
    max-width: 1080px; width: 100%; margin: 0 auto;
    padding: 22px 18px 18px; align-items: start;
    opacity: 0; transition: opacity .45s;
}
.body--on { opacity: 1; }

/* ════════════════════════════════
   SIDEBAR
════════════════════════════════ */
.sidebar {
    display: flex; flex-direction: column;
    opacity: 0; transform: translateX(-16px);
    transition: opacity .5s, transform .5s cubic-bezier(.34,1.56,.64,1);
    user-select: none; cursor: pointer;
}
.sidebar--on { opacity: 1; transform: none; }
.sb-info { margin-bottom: 18px; }

.sb-chip {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.38);
    border-radius: 999px; padding: 4px 13px;
    font-size: 11px; font-weight: 900; color: #fff;
    backdrop-filter: blur(6px); margin-bottom: 10px;
}
.sb-title { font-family:'Righteous',cursive;font-size:clamp(16px,2vw,22px);color:#fff;line-height:1.25;margin-bottom:5px;text-shadow:0 1px 10px rgba(0,0,0,.35); }
.sb-sub   { font-size:12.5px;font-weight:800;color:rgba(255,255,255,.9);line-height:1.55;margin-bottom:16px;text-shadow:0 1px 6px rgba(0,0,0,.28); }

.sb-pills { display: flex; flex-direction: column; gap: 8px; }
.sb-pill  { display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.28);border-radius:10px;padding:9px 13px;font-size:12.5px;font-weight:800;color:#fff;backdrop-filter:blur(6px); }

.mascot-wrap { position:relative; padding-left:4px; }

.bubble {
    position: relative; background: #fff;
    border: 2px solid #BFDBFE; border-radius: 16px;
    padding: 9px 14px; min-width: 146px; max-width: 210px;
    box-shadow: 0 5px 18px rgba(37,99,235,.13); margin-bottom: 6px;
    animation: bblFloat 3.5s ease-in-out infinite;
}
.bubble span { font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block; }
.bubble--intro { border-color: #FCD34D; box-shadow: 0 5px 18px rgba(245,158,11,.18); }
.bubble--intro span { color: #92400e; }

.bbl-o,.bbl-i { position:absolute;width:0;height:0;font-style:normal; }
.bbl-o { bottom:-14px;left:15px;border-left:10px solid transparent;border-right:6px solid transparent;border-top:13px solid #FCD34D; }
.bbl-i { bottom:-10px;left:16px;border-left:8px solid transparent;border-right:5px solid transparent;border-top:11px solid #fff; }
@keyframes bblFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }

.bbl-enter-active { transition: opacity .26s, transform .3s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .16s; }
.bbl-enter-from   { opacity: 0; transform: translateY(8px) scale(.92); }
.bbl-leave-to     { opacity: 0; }

.mascot-frame { position:relative; display:inline-block; }
.mascot {
    position: relative; z-index: 2;
    width: clamp(138px,14vw,192px); height: auto; display: block;
    filter: drop-shadow(0 10px 22px rgba(0,0,0,.22));
    animation: mBob 3.5s ease-in-out infinite;
    transform-origin: bottom center;
}
.mascot-shadow {
    position: absolute; bottom: 0; left: 50%; transform: translateX(-50%);
    width: 65%; height: 13px;
    background: radial-gradient(ellipse at center,rgba(0,0,0,.28) 0%,transparent 70%);
    border-radius: 50%; z-index: 1;
}
@keyframes mBob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-8px) rotate(.5deg)} 70%{transform:translateY(-4px) rotate(-.3deg)} }

/* ════════════════════════════════
   MAIN PANEL
════════════════════════════════ */
.main {
    opacity: 0; transform: translateY(16px);
    transition: opacity .5s .1s, transform .5s .1s cubic-bezier(.34,1.56,.64,1);
}
.main--on { opacity: 1; transform: none; }

/* ════════════════════════════════
   LOGIN CARD
════════════════════════════════ */
.icard {
    background: #FDFCFB; border-radius: 20px; overflow: hidden;
    border: 1.5px solid #e2e8f0;
    box-shadow: 0 4px 0 #BFDBFE, 0 10px 32px rgba(59,130,246,.10);
}

.icard-head {
    background: linear-gradient(135deg,#3B82F6 0%,#2563EB 100%);
    position: relative; overflow: hidden;
}
.icard-hdeco { position:absolute;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none; }
.icard-hdeco-1 { width:200px;height:200px;top:-70px;right:-40px; }
.icard-hdeco-2 { width:100px;height:100px;bottom:-42px;left:18px; }
.icard-head-inner { position:relative;z-index:1;padding:22px 22px 24px; }
.icard-eyebrow {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.20); border: 1px solid rgba(255,255,255,.30);
    border-radius: 999px; padding: 4px 12px;
    font-size: 11px; font-weight: 900; color: #fff; margin-bottom: 11px;
}
.icard-title { font-family:'Righteous',cursive;font-size:clamp(18px,2.5vw,25px);color:#fff;line-height:1.2;margin-bottom:8px;text-shadow:0 2px 10px rgba(0,0,0,.15); }
.icard-sub   { font-size:13px;font-weight:700;color:rgba(255,255,255,.88);line-height:1.6;max-width:460px; }

.icard-stats { display:grid;grid-template-columns:repeat(3,1fr);border-bottom:1.5px solid #e2e8f0; }
.istat { display:flex;flex-direction:column;align-items:center;gap:5px;padding:20px 12px 18px;border-right:1.5px solid #e2e8f0; }
.istat:last-child { border-right:none; }

.istat--red    { background:#FFF7F7; }
.istat--red    .istat-icon { background:#F87171;box-shadow:0 4px 14px rgba(248,113,113,.30); }
.istat--red    .istat-val  { color:#C53030; }
.istat--red    .istat-lbl  { color:#E53E3E; }

.istat--yellow { background:#FFFDF0; }
.istat--yellow .istat-icon { background:#FBBF24;box-shadow:0 4px 14px rgba(251,191,36,.30); }
.istat--yellow .istat-val  { color:#92400E; }
.istat--yellow .istat-lbl  { color:#D97706; }

.istat--green  { background:#F0FDF9; }
.istat--green  .istat-icon { background:#34D399;box-shadow:0 4px 14px rgba(52,211,153,.30); }
.istat--green  .istat-val  { color:#065F46; }
.istat--green  .istat-lbl  { color:#059669; }

.istat-icon { width:46px;height:46px;border-radius:13px;display:flex;align-items:center;justify-content:center;color:#fff;margin-bottom:2px; }
.istat-val  { font-family:'Righteous',cursive;font-size:24px;line-height:1; }
.istat-lbl  { font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.5px; }

.icard-body { padding: 20px 22px 26px; }

.fw { display:flex;flex-direction:column;gap:5px;margin-bottom:14px; }
.fw:last-child { margin-bottom: 0; }
.flabel { font-size:11.5px;font-weight:800;color:#374151;letter-spacing:.2px;padding-left:2px; }
.req    { color: #ef4444; }

.if-wrap  { position:relative;display:flex;align-items:center; }
.if-icon  { position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9ca3af;pointer-events:none;flex-shrink:0;transition:color .15s; }
.if-input {
    width:100%; padding:12px 16px 12px 42px;
    border-width:2px; border-style:solid; border-radius:13px;
    outline:none; font-family:'Nunito',sans-serif; font-size:14px;
    font-weight:700; color:#111827; background:#fff;
    transition:border-color .2s, background .2s, box-shadow .2s;
}
.if-input::placeholder { color:#9ca3af;font-weight:600; }
.if-blue { border-color:#BFDBFE; }
.if-blue:hover { border-color:#93c5fd; }
.if-blue:focus { border-color:#3b82f6;background:#eff6ff;box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.if-wrap:focus-within .if-icon { color:#3b82f6; }
.if-err { border-color:#fca5a5;background:#fef2f2; }
.if-err:focus { border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1); }

.errmsg { font-size:11.5px;font-weight:700;color:#ef4444;padding-left:2px;animation:errin .18s ease; }
@keyframes errin { from{opacity:0;transform:translateY(-3px)} to{opacity:1;transform:none} }

/* ════════════════════════════════
   FOOTER
════════════════════════════════ */
.footer {
    position:relative;z-index:50;
    background:rgba(255,255,255,.10);backdrop-filter:blur(18px);
    border-top:1px solid rgba(255,255,255,.16);
    padding:11px 0 8px;flex-shrink:0;
}
.footer-inner { display:flex;align-items:center;gap:10px;max-width:1080px;margin:0 auto;padding:0 20px; }
.f-space { flex:1; }

.fbtn {
    display:inline-flex;align-items:center;gap:6px;height:40px;padding:0 18px;
    border:none;border-radius:10px;font-family:'Nunito',sans-serif;
    font-size:13px;font-weight:800;cursor:pointer;flex-shrink:0;white-space:nowrap;
    transition:transform .15s cubic-bezier(.34,1.56,.64,1),box-shadow .15s;
}
.fbtn--yellow { background:#2563EB;color:#fff;box-shadow:0 3px 12px rgba(37,99,235,.4); }
.fbtn--yellow:hover:not(:disabled) { transform:translateY(-2px);box-shadow:0 5px 16px rgba(37,99,235,.5); }
.fbtn--yellow:disabled { opacity:.5;cursor:not-allowed; }
.fbtn.shake { animation: shake .5s ease; }
.fbtn.loading { opacity:.8; cursor:wait; }

@keyframes shake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-8px)} 40%{transform:translateX(8px)} 60%{transform:translateX(-5px)} 80%{transform:translateX(5px)} }

.spinner { display:inline-block;width:16px;height:16px;border:2.5px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .75s linear infinite; }
@keyframes spin { to{transform:rotate(360deg)} }

/* ════════════════════════════════
   RESPONSIVE ≤ 820px
════════════════════════════════ */
@media (max-width: 820px) {
    .b1{width:300px;height:300px;} .b2{width:240px;height:240px;} .b3{width:180px;height:180px;}
    .c1{width:100px;height:100px;} .c2{display:none;} .r1{width:200px;height:200px;}
    .topbar { height:52px;padding:0 13px; }
    .brand-name{font-size:16px;} .brand-dot{width:25px;height:25px;}
    .body { grid-template-columns:1fr;gap:0;padding:0;max-width:100%; }
    .sidebar {
        opacity:1 !important;transform:none !important;
        flex-direction:column;gap:0;cursor:default;
        padding:13px 15px 12px;
        background:rgba(29,78,216,.76);backdrop-filter:blur(18px);
        border-bottom:1px solid rgba(191,219,254,.22);
    }
    .sb-info { margin-bottom:0; }
    .sb-chip{margin-bottom:6px;font-size:10px;} .sb-title{font-size:15px;margin-bottom:3px;} .sb-sub{font-size:11.5px;margin-bottom:10px;}
    .sb-pills{flex-direction:row;flex-wrap:wrap;gap:7px;} .sb-pill{padding:6px 10px;font-size:11.5px;border-radius:8px;}
    .mascot-wrap{display:none;}
    .main{transform:none;opacity:1;}
    .icard{border-radius:0;border-left:none;border-right:none;}
    .icard-head-inner{padding:16px 15px 18px;} .icard-title{font-size:18px;}
    .icard-stats{grid-template-columns:repeat(3,1fr);}
    .istat{padding:15px 8px 13px;} .istat-icon{width:38px;height:38px;border-radius:10px;} .istat-val{font-size:20px;}
    .icard-body{padding:16px 15px 20px;}
    .footer-inner{padding:0 15px;} .fbtn{height:40px;}
}
@media (max-width: 480px) {
    .topbar{height:48px;padding:0 11px;} .tbtn-lbl{display:none;} .tbtn{padding:7px 9px;}
    .icard-head-inner{padding:14px 13px 16px;}
    .istat{padding:12px 6px 10px;} .istat-icon{width:34px;height:34px;border-radius:9px;} .istat-val{font-size:18px;}
    .icard-body{padding:14px 13px 18px;}
    .footer-inner{padding:0 12px;gap:7px;} .fbtn{height:38px;padding:0 13px;font-size:12px;}
}
</style>
