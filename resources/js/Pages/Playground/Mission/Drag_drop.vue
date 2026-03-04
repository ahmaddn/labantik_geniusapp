<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
    ArrowLeft, Home, RotateCcw, CheckCircle, ChevronRight,
    Award, Target, XCircle, GripHorizontal, Layers, Zap,
    Sparkles, Trophy, Star,
    Fish, TreePine, Bird, Mountain, Leaf, Droplets, Sun, CloudRain
} from 'lucide-vue-next'

// ── Props ──────────────────────────────────────────────────────
const props = defineProps({
    mission:    { type: Object, default: null },
    module:     { type: Object, default: null },
    groups:     { type: Array,  default: () => [] },
    items:      { type: Array,  default: () => [] },
    mascot:     { type: Object, default: null },
    background: { type: Object, default: null },
    auth:       { type: Object, default: null },
})

// ── Default assets (fallback ke file lokal) ────────────────────
const mascotSrc = computed(() => props.mascot?.image
    ? '/images/templates/pose_nunjuk.png'
    : '/images/templates/pose_keren.png')

// ── Speech bubble ──────────────────────────────────────────────
const BUBBLES = [
    'Seret gambar nya! 🎯',
    'Pikirkan baik-baik! 🤔',
    'Kamu pasti bisa 💪',
    'Perhatikan ciri-cirinya 👀',
    'Hampir selesai 🔥',
    'Hidup Jokowi ✨',
    'Santaiii 🏆',
]
const bubbleText    = ref(BUBBLES[0])
const bubbleVisible = ref(true)
const bubbleIndex   = ref(0)
let   bubbleTimer   = null

const showNextBubble = () => {
    bubbleVisible.value = false
    setTimeout(() => {
        bubbleIndex.value   = (bubbleIndex.value + 1) % BUBBLES.length
        bubbleText.value    = BUBBLES[bubbleIndex.value]
        bubbleVisible.value = true
    }, 350)
}
const startBubble = () => { bubbleTimer = setInterval(showNextBubble, 3000) }

// ── Icon map ───────────────────────────────────────────────────
const ICON_MAP = { Fish, TreePine, Bird, Mountain, Leaf, Droplets, Sun, CloudRain }
const iconComp = (name) => ICON_MAP[name] ?? Target

// ── Build data ─────────────────────────────────────────────────
const buildItems = () => {
    if (props.items?.length) return props.items.map(it => ({
        id: it.id, label: it.item_text, image: it.item_image ?? null,
        groupId: it.correct_group_id, placed: false, correct: null, iconName: null,
    }))
    return [
        { id:1, label:'Ikan',     image:null, iconName:'Fish',      groupId:1, placed:false, correct:null },
        { id:2, label:'Pohon',    image:null, iconName:'TreePine',   groupId:1, placed:false, correct:null },
        { id:3, label:'Burung',   image:null, iconName:'Bird',       groupId:1, placed:false, correct:null },
        { id:4, label:'Batu',     image:null, iconName:'Mountain',   groupId:2, placed:false, correct:null },
        { id:5, label:'Rumput',   image:null, iconName:'Leaf',       groupId:1, placed:false, correct:null },
        { id:6, label:'Air',      image:null, iconName:'Droplets',   groupId:2, placed:false, correct:null },
        { id:7, label:'Matahari', image:null, iconName:'Sun',        groupId:2, placed:false, correct:null },
        { id:8, label:'Hujan',    image:null, iconName:'CloudRain',  groupId:2, placed:false, correct:null },
    ]
}
const buildZones = () => {
    if (props.groups?.length) return props.groups.map(g => ({ id: g.id, label: g.group_name, sublabel: '', items: [] }))
    return [
        { id:1, label:'BIOTIK',  sublabel:'Makhluk Hidup',   items:[] },
        { id:2, label:'ABIOTIK', sublabel:'Benda Tak Hidup', items:[] },
    ]
}

const dragItems    = ref(buildItems())
const dropZones    = ref(buildZones())
const dragged      = ref(null)
const phase        = ref('play')
const score        = ref(0)
const showWrong    = ref(false)
const ready        = ref(false)
const dragOverZone = ref(null)

const unplaced    = computed(() => dragItems.value.filter(i => !i.placed))
const placedCount = computed(() => dragItems.value.filter(i => i.placed).length)
const totalCount  = computed(() => dragItems.value.length)
const progressPct = computed(() => Math.round((placedCount.value / Math.max(totalCount.value, 1)) * 100))
const wrongItems  = computed(() => dragItems.value.filter(i => i.placed && !i.correct))
const starCount   = computed(() => {
    const p = score.value / Math.max(totalCount.value, 1)
    return p === 1 ? 3 : p >= .6 ? 2 : p >= .3 ? 1 : 0
})
const resultTitle = computed(() => ['Coba Lagi!', 'Terus Semangat!', 'Bagus Sekali!', 'Sempurna! 🎉'][starCount.value])

// ── Mouse drag ─────────────────────────────────────────────────
const onDragStart  = item  => { dragged.value = item }
const onDragOver   = (e, zoneId) => { e.preventDefault(); dragOverZone.value = zoneId }
const onDragLeave  = ()    => { dragOverZone.value = null }
const onDrop = zone => {
    dragOverZone.value = null
    if (!dragged.value) return
    const item = dragged.value
    item.placed = true; item.correct = item.groupId === zone.id
    zone.items.push(item); dragged.value = null
    if (unplaced.value.length === 0) {
        score.value = dragItems.value.filter(i => i.correct).length
        setTimeout(() => phase.value = 'result', 700)
    }
}

// ── Touch drag ─────────────────────────────────────────────────
let tItem = null, tClone = null
const onTouchStart = (item, e) => {
    tItem = item
    const r = e.currentTarget.getBoundingClientRect()
    tClone = e.currentTarget.cloneNode(true)
    Object.assign(tClone.style, {
        position:'fixed', zIndex:'9999', opacity:'.9', pointerEvents:'none',
        width:r.width+'px', height:r.height+'px', left:r.left+'px', top:r.top+'px',
        transform:'scale(1.1) rotate(2deg)',
        borderRadius:'14px', boxShadow:'0 12px 32px rgba(29,78,216,.28)'
    })
    document.body.appendChild(tClone)
}
const onTouchMove = e => {
    e.preventDefault()
    if (!tClone) return
    const t = e.touches[0]
    tClone.style.left = (t.clientX - 44)+'px'
    tClone.style.top  = (t.clientY - 44)+'px'
}
const onTouchEnd = e => {
    tClone?.remove(); tClone = null
    if (!tItem) return
    const t  = e.changedTouches[0]
    const el = document.elementsFromPoint(t.clientX, t.clientY).find(el => el.dataset.zoneid)
    if (el) { const z = dropZones.value.find(z => z.id == el.dataset.zoneid); if (z) { dragged.value = tItem; onDrop(z) } }
    tItem = null
}

const reset = () => {
    dropZones.value.forEach(z => z.items = [])
    dragItems.value.forEach(i => { i.placed = false; i.correct = null })
    score.value = 0; phase.value = 'play'; showWrong.value = false
}

// ── Zone palettes ──────────────────────────────────────────────
const PALETTES = [
    { bg:'rgba(236,253,245,.96)', dash:'rgba(52,211,153,.45)',  solid:'#34d399', head:'#065f46', badge:'#10b981', light:'#d1fae5' },
    { bg:'rgba(239,246,255,.96)', dash:'rgba(96,165,250,.45)',  solid:'#60a5fa', head:'#1e40af', badge:'#3b82f6', light:'#dbeafe' },
    { bg:'rgba(253,244,255,.96)', dash:'rgba(192,132,252,.45)', solid:'#c084fc', head:'#6b21a8', badge:'#a855f7', light:'#f3e8ff' },
    { bg:'rgba(255,251,235,.96)', dash:'rgba(251,191,36,.45)',  solid:'#fbbf24', head:'#92400e', badge:'#f59e0b', light:'#fef3c7' },
]
const zp = i => PALETTES[i % PALETTES.length]

const goBack = () => router.visit(route('student.missions.show', props.module?.id ?? 1))
const goHome = () => router.visit(route('student.missions.index'))

onMounted(() => {
    setTimeout(() => (ready.value = true), 100)
    setTimeout(() => startBubble(), 1400)
})
onUnmounted(() => { clearInterval(bubbleTimer); bubbleTimer = null })
</script>

<template>
    <Head :title="mission?.title ?? 'Seret dan Letakkan'" />

    <div style="display:none">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet" />
    </div>

    <div class="root">

        <!-- BG -->
        <div class="bg">
            <img :src="bgSrc" class="bg-img" alt="" />
            <div class="bg-dim"></div>
        </div>

        <!-- TOPBAR -->
        <header class="topbar">
            <button class="t-btn" @click="goBack">
                <ArrowLeft :size="15" :stroke-width="2.6" />
                <span>Kembali</span>
            </button>
            <div class="brand">
                <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2.5" /></div>
                <span class="brand-name">Geniuss</span>
            </div>
            <button class="t-btn t-btn--sq" @click="goHome">
                <Home :size="15" :stroke-width="2.6" />
            </button>
        </header>

        <!-- BODY -->
        <div class="body" :class="{ 'body--on': ready }">

            <!-- ═══ SIDEBAR ═══ -->
            <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="showNextBubble">

                <div class="sb-info">
                    <span class="activity-tag">
                        <GripHorizontal :size="11" :stroke-width="2.5" />
                        Seret &amp; Letakkan
                    </span>
                    <h1 class="sb-title">{{ mission?.title ?? 'Klasifikasi Gambar' }}</h1>
                    <p class="sb-sub">{{ module?.title ?? 'Seret gambar ke kelompok yang tepat!' }}</p>

                    <!-- progress -->
                    <div class="prog">
                        <div class="prog-meta">
                            <span class="prog-lbl">Progress</span>
                            <span class="prog-val">
                                <b>{{ placedCount }}</b>
                                <span>/</span>
                                <span>{{ totalCount }}</span>
                            </span>
                        </div>
                        <div class="prog-bar">
                            <div class="prog-fill" :style="`width:${progressPct}%`">
                                <span class="prog-glow"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- mascot -->
                <div class="mascot-wrap">
                    <Transition name="bbl">
                        <div v-if="bubbleVisible" class="bubble">
                            <span>{{ bubbleText }}</span>
                            <i class="bbl-tail-out"></i>
                            <i class="bbl-tail-in"></i>
                        </div>
                    </Transition>
                    <div class="mascot-shadow"></div>
                    <img :src="mascotSrc" class="mascot" alt="Maskot" />
                </div>
            </aside>

            <!-- ═══ GAME PANEL ═══ -->
            <section class="game" :class="{ 'game--on': ready }">

                <!-- ─── PLAY ─── -->
                <Transition name="phase">
                <div v-if="phase === 'play'" class="card" key="play"
                    @touchmove.prevent="onTouchMove"
                    @touchend="onTouchEnd">

                    <div class="card-bar"></div>
                    <div class="card-body">

                        <!-- Card header -->
                        <div class="card-head">
                            <div class="card-brand">
                                <div class="card-brand-ico">
                                    <Zap :size="14" color="#fff" fill="white" :stroke-width="2.4" />
                                </div>
                                <div>
                                    <div class="card-brand-nm">Geni<span class="ac">uss</span></div>
                                    <div class="card-brand-sub">Drag &amp; Drop Activity</div>
                                </div>
                            </div>
                            <button class="btn-reset" @click="reset">
                                <RotateCcw :size="13" :stroke-width="2.5" />
                                Ulangi
                            </button>
                        </div>
                        <div class="divider"></div>

                        <!-- Bank -->
                        <div class="bank">
                            <div class="row-head">
                                <div class="row-ico"><Layers :size="12" :stroke-width="2.5" /></div>
                                <span class="row-lbl">Gambar Tersedia</span>
                                <span class="row-badge">{{ unplaced.length }}</span>
                            </div>
                            <div class="bank-grid">
                                <TransitionGroup name="pop">
                                    <div
                                        v-for="item in unplaced"
                                        :key="item.id"
                                        class="dcard"
                                        draggable="true"
                                        @dragstart="onDragStart(item)"
                                        @touchstart.prevent="onTouchStart(item, $event)"
                                    >
                                        <div class="dcard-img">
                                            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
                                            <div v-else class="dcard-ico">
                                                <component :is="iconComp(item.iconName)" v-if="item.iconName" :size="27" :stroke-width="1.5" color="#2563eb" />
                                                <Target v-else :size="24" :stroke-width="1.5" color="#3b82f6" />
                                            </div>
                                        </div>
                                        <span class="dcard-lbl">{{ item.label }}</span>
                                        <GripHorizontal :size="11" :stroke-width="2" color="#cbd5e1" class="dcard-grip" />
                                    </div>
                                </TransitionGroup>
                                <div v-if="unplaced.length === 0" class="bank-done">
                                    <CheckCircle :size="22" :stroke-width="2" color="#22c55e" />
                                    <span>Semua sudah ditempatkan!</span>
                                </div>
                            </div>
                        </div>

                        <!-- Drop zones -->
                        <div class="zones">
                            <div
                                v-for="(zone, zi) in dropZones"
                                :key="zone.id"
                                class="zone"
                                :class="{ 'zone--over': dragOverZone === zone.id }"
                                :data-zoneid="zone.id"
                                :style="`--zbg:${zp(zi).bg};--zdash:${zp(zi).dash};--zsolid:${zp(zi).solid};--zhead:${zp(zi).head};--zbadge:${zp(zi).badge};--zlight:${zp(zi).light}`"
                                @dragover="onDragOver($event, zone.id)"
                                @dragleave="onDragLeave"
                                @drop="onDrop(zone)"
                            >
                                <div class="zone-header">
                                    <div class="zone-ico-wrap">
                                        <Layers :size="15" :stroke-width="2.2" />
                                    </div>
                                    <div class="zone-info">
                                        <div class="zone-name">{{ zone.label }}</div>
                                        <div v-if="zone.sublabel" class="zone-sub">{{ zone.sublabel }}</div>
                                    </div>
                                    <div class="zone-count">{{ zone.items.length }}</div>
                                </div>
                                <div class="zone-body" :data-zoneid="zone.id">
                                    <template v-if="zone.items.length > 0">
                                        <div
                                            v-for="item in zone.items" :key="item.id"
                                            class="placed"
                                            :class="item.correct ? 'placed--ok' : 'placed--err'"
                                        >
                                            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
                                            <div v-else class="placed-ico">
                                                <component :is="iconComp(item.iconName)" v-if="item.iconName" :size="17" :stroke-width="1.8" />
                                                <Target v-else :size="17" :stroke-width="1.6" />
                                            </div>
                                            <span>{{ item.label }}</span>
                                            <div class="placed-flag">
                                                <CheckCircle v-if="item.correct" :size="12" :stroke-width="2.5" />
                                                <XCircle v-else :size="12" :stroke-width="2.5" />
                                            </div>
                                        </div>
                                    </template>
                                    <div v-else class="zone-hint" :data-zoneid="zone.id">
                                        <GripHorizontal :size="18" :stroke-width="1.5" />
                                        <span>Letakkan di sini</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                </Transition>

                <!-- ─── RESULT ─── -->
                <Transition name="phase">
                <div v-if="phase === 'result'" class="card result-card" key="result">
                    <div class="card-bar"></div>
                    <div class="card-body result-body">

                        <div class="confetti-wrap" aria-hidden="true">
                            <div v-for="n in 14" :key="n" class="c-dot" :style="`--ci:${n}`"></div>
                        </div>

                        <div class="r-trophy" :class="starCount >= 2 ? 'trophy--gold' : 'trophy--dim'">
                            <Trophy :size="38" :stroke-width="1.6" />
                        </div>

                        <h2 class="r-title">{{ resultTitle }}</h2>

                        <div class="r-stars">
                            <div v-for="n in 3" :key="n"
                                class="r-star"
                                :class="n <= starCount ? 'star--on' : 'star--off'"
                                :style="`animation-delay:${n*.15}s`">
                                <Star :size="n === 2 ? 38 : 28" :stroke-width="1.5" :fill="n <= starCount ? '#f59e0b' : 'none'" />
                            </div>
                        </div>

                        <div class="r-score">
                            <span class="r-num">{{ score }}</span>
                            <span class="r-div">/</span>
                            <span class="r-tot">{{ totalCount }}</span>
                            <span class="r-lbl">jawaban benar</span>
                        </div>

                        <div v-if="wrongItems.length" class="r-wrong">
                            <button class="rw-toggle" @click="showWrong = !showWrong">
                                <XCircle :size="14" color="#dc2626" :stroke-width="2.2" />
                                <span>{{ wrongItems.length }} jawaban perlu diperbaiki</span>
                                <ChevronRight :size="13" :class="{ 'chev-open': showWrong }" style="margin-left:auto" />
                            </button>
                            <Transition name="drop">
                                <div v-if="showWrong" class="rw-list">
                                    <div v-for="w in wrongItems" :key="w.id" class="rw-row">
                                        <div class="rw-thumb">
                                            <img v-if="w.image" :src="`/storage/${w.image}`" :alt="w.label" />
                                            <component :is="iconComp(w.iconName)" v-else-if="w.iconName" :size="14" :stroke-width="1.8" />
                                            <Target v-else :size="14" :stroke-width="1.6" />
                                        </div>
                                        <span class="rw-nm">{{ w.label }}</span>
                                        <ChevronRight :size="11" color="#cbd5e1" />
                                        <span class="rw-ans">{{ dropZones.find(z => z.id === w.groupId)?.label ?? '?' }}</span>
                                    </div>
                                </div>
                            </Transition>
                        </div>

                        <div class="r-actions">
                            <button class="act act--ghost" @click="reset">
                                <RotateCcw :size="15" :stroke-width="2.5" />
                                Coba Lagi
                            </button>
                            <button class="act act--primary" @click="goHome">
                                <Home :size="15" :stroke-width="2.5" />
                                Kembali ke Misi
                            </button>
                        </div>

                    </div>
                </div>
                </Transition>

            </section>

        </div>
    </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ══ ROOT ══════════════════════════════════════════════════ */
.root {
    min-height: 100vh;
    display: flex; flex-direction: column;
    font-family: 'Nunito', sans-serif;
}

/* ══ BACKGROUND ════════════════════════════════════════════ */
.bg { position: fixed; inset: 0; z-index: 0; }
.bg-img { width: 100%; height: 100%; object-fit: cover; object-position: center top; display: block; }
.bg-dim {
    position: absolute; inset: 0;
    background: url("/images/templates/background_misi.png") top center / cover
        no-repeat fixed;
}

/* ══ TOPBAR ════════════════════════════════════════════════ */
.topbar {
    position: relative; z-index: 50;
    height: 54px; flex-shrink: 0;
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 22px;
    background: rgba(255,255,255,.16);
    backdrop-filter: blur(22px) saturate(1.8);
    -webkit-backdrop-filter: blur(22px) saturate(1.8);
    border-bottom: 1px solid rgba(255,255,255,.38);
    box-shadow: 0 1px 0 rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
}
.brand { display: flex; align-items: center; gap: 9px; }
.brand-dot {
    width: 30px; height: 30px; border-radius: 9px;
    background: linear-gradient(140deg, #60a5fa, #1d4ed8);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 3px 10px rgba(29,78,216,.4);
}
.brand-name {
    font-family: 'Righteous', cursive;
    font-size: 19px; color: #fff; letter-spacing: -.1px;
    text-shadow: 0 1px 8px rgba(0,0,0,.2);
}
.t-btn {
    display: flex; align-items: center; gap: 6px;
    padding: 7px 15px;
    background: rgba(255,255,255,.18);
    backdrop-filter: blur(8px);
    border: 1.5px solid rgba(255,255,255,.38);
    border-radius: 10px;
    font-family: 'Nunito', sans-serif;
    font-size: 13px; font-weight: 800; color: #fff;
    cursor: pointer; transition: all .2s;
    text-shadow: 0 1px 4px rgba(0,0,0,.15);
}
.t-btn:hover { background: rgba(255,255,255,.3); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.t-btn--sq   { padding: 7px 12px; }

/* ══ BODY GRID ═════════════════════════════════════════════ */
.body {
    position: relative; z-index: 10;
    flex: 1;
    display: grid;
    grid-template-columns: 272px 1fr;
    gap: 22px;
    max-width: 1080px; width: 100%;
    margin: 0 auto;
    padding: 24px 20px 36px;
    align-items: start;
}

/* ══ SIDEBAR ═══════════════════════════════════════════════ */
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
    background: rgba(29,78,216,.22);
    border: 1.5px solid rgba(255,255,255,.26);
    border-radius: 999px; padding: 4px 13px;
    font-size: 10.5px; font-weight: 800; color: #fff;
    letter-spacing: .3px; backdrop-filter: blur(8px);
    margin-bottom: 11px;
    text-shadow: 0 1px 3px rgba(0,0,0,.12);
}

.sb-title {
    font-family: 'Righteous', cursive;
    font-size: clamp(17px, 2vw, 25px);
    color: #fff; line-height: 1.22;
    text-shadow: 0 2px 16px rgba(0,0,0,.22);
    margin-bottom: 6px;
}
.sb-sub {
    font-size: 12.5px; font-weight: 700;
    color: rgba(255,255,255,.78); line-height: 1.65;
    text-shadow: 0 1px 5px rgba(0,0,0,.14);
    margin-bottom: 17px;
}

/* Progress */
.prog { width: 100%; }
.prog-meta {
    display: flex; justify-content: space-between; align-items: center;
    margin-bottom: 7px;
}
.prog-lbl {
    font-size: 10px; font-weight: 900;
    color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .6px;
}
.prog-val {
    font-family: 'Righteous', cursive;
    font-size: 15px; color: #fff; display: flex; align-items: baseline; gap: 1px;
}
.prog-val b { font-size: 17px; }
.prog-val span { color: rgba(255,255,255,.5); font-size: 12px; }
.prog-bar {
    height: 9px; border-radius: 99px;
    background: rgba(255,255,255,.18);
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0,0,0,.14);
}
.prog-fill {
    height: 100%; border-radius: 99px; position: relative; overflow: hidden;
    background: linear-gradient(90deg, #60a5fa, #1d4ed8);
    transition: width .5s cubic-bezier(.34,1.56,.64,1);
    box-shadow: 0 0 14px rgba(96,165,250,.55);
}
.prog-glow {
    position: absolute; inset: 0;
    background: linear-gradient(90deg, transparent 0%, rgba(255,255,255,.38) 50%, transparent 100%);
    animation: shine 2.4s ease-in-out infinite;
}
@keyframes shine { 0%,100% { transform: translateX(-100%); } 60% { transform: translateX(200%); } }

/* Mascot */
.mascot-wrap { position: relative; margin-left: 6px; }
.bubble {
    position: center;
    bottom: calc(100% - 16px);
    left: 52%;
    background: #fff;
    border: 2.5px solid #93c5fd;
    border-radius: 18px;
    padding: 9px 15px;
    min-width: 158px; max-width: 218px;
    white-space: nowrap;
    box-shadow: 0 8px 28px rgba(29,78,216,.13), inset 0 1px 0 rgba(255,255,255,.9);
    z-index: 20;
    animation: float 3.5s ease-in-out infinite;
}
.bubble span {
    font-family: 'Nunito', sans-serif;
    font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block;
}
.bbl-tail-out, .bbl-tail-in {
    position: absolute; width: 0; height: 0; font-style: normal;
}
.bbl-tail-out { bottom:-16px; left:18px; border-left:11px solid transparent; border-right:7px solid transparent; border-top:15px solid #93c5fd; }
.bbl-tail-in  { bottom:-12px; left:19px; border-left: 9px solid transparent; border-right:6px solid transparent; border-top:12px solid #fff; }
@keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-6px); } }

.bbl-enter-active { transition: opacity .3s ease, transform .36s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .2s ease, transform .2s ease; }
.bbl-enter-from   { opacity: 0; transform: translateY(10px) scale(.9); }
.bbl-leave-to     { opacity: 0; transform: translateY(-7px) scale(.94); }

.mascot-shadow {
    position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%);
    width: 145px; height: 28px;
    background: radial-gradient(ellipse, rgba(0,0,0,.18) 0%, transparent 70%);
    border-radius: 50%; pointer-events: none;
}
.mascot {
    position: relative; z-index: 2;
    width: clamp(145px, 15vw, 205px); height: auto; display: block;
    filter: drop-shadow(0 10px 26px rgba(0,0,0,.2));
    animation: bob 3.5s ease-in-out infinite;
    transform-origin: bottom center;
}
@keyframes bob {
    0%,100% { transform: translateY(0) rotate(0deg); }
    45%      { transform: translateY(-9px) rotate(.6deg); }
    70%      { transform: translateY(-5px) rotate(-.4deg); }
}
.sb-hint {
    display: flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 700;
    color: rgba(255,255,255,.48); margin-top: 9px;
    text-shadow: 0 1px 4px rgba(0,0,0,.12);
}

/* ══ GAME PANEL ════════════════════════════════════════════ */
.game {
    opacity: 0; transform: translateY(22px);
    transition: opacity .6s .12s ease, transform .6s .12s cubic-bezier(.34,1.56,.64,1);
}
.game--on { opacity: 1; transform: none; }

.card {
    background: rgba(255,255,255,.93);
    backdrop-filter: blur(28px) saturate(1.6);
    -webkit-backdrop-filter: blur(28px) saturate(1.6);
    border-radius: 22px; overflow: hidden;
    border: 1.5px solid rgba(255,255,255,.85);
    box-shadow:
        0 20px 56px rgba(0,0,0,.12),
        0 4px 0 rgba(29,78,216,.07),
        inset 0 1px 0 #fff;
}
.card-bar {
    height: 4px;
    background: linear-gradient(90deg, #1d4ed8, #60a5fa, #1d4ed8);
    background-size: 200%;
    animation: bar 3.5s linear infinite;
}
@keyframes bar { 0% { background-position: 0%; } 100% { background-position: 200%; } }

.card-body { padding: 22px 24px 26px; }

/* Card header */
.card-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px;
}
.card-brand { display: flex; align-items: center; gap: 10px; }
.card-brand-ico {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    background: linear-gradient(140deg, #60a5fa, #1d4ed8);
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(29,78,216,.28);
}
.card-brand-nm {
    font-family: 'Righteous', cursive;
    font-size: 17px; color: #1e3a8a; line-height: 1.1;
}
.ac  { color: #1d4ed8; }
.card-brand-sub {
    font-size: 9.5px; font-weight: 800; color: #9ca3af;
    letter-spacing: .5px; text-transform: uppercase; margin-top: 2px;
}
.btn-reset {
    display: flex; align-items: center; gap: 5px;
    padding: 7px 13px;
    background: #f8fafc; border: 1.5px solid #e2e8f0;
    border-radius: 9px;
    font-family: 'Nunito', sans-serif;
    font-size: 12px; font-weight: 800; color: #64748b;
    cursor: pointer; transition: all .2s;
}
.btn-reset:hover {
    background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8;
    transform: translateY(-1px);
}
.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(29,78,216,.1), transparent);
    margin-bottom: 18px;
}

/* Bank */
.bank { margin-bottom: 18px; }
.row-head {
    display: flex; align-items: center; gap: 6px; margin-bottom: 12px;
}
.row-ico {
    width: 22px; height: 22px; border-radius: 6px;
    background: rgba(29,78,216,.08);
    color: #1d4ed8;
    display: flex; align-items: center; justify-content: center;
}
.row-lbl {
    font-size: 10.5px; font-weight: 900; color: #64748b;
    text-transform: uppercase; letter-spacing: .5px; flex: 1;
}
.row-badge {
    background: #1d4ed8; color: #fff;
    font-size: 10px; font-weight: 900;
    padding: 2px 9px; border-radius: 99px;
}
.bank-grid {
    display: flex; flex-wrap: wrap; gap: 8px;
    min-height: 88px; align-content: flex-start;
}
.bank-done {
    display: flex; align-items: center; gap: 7px;
    color: #16a34a; font-size: 12.5px; font-weight: 800; margin: auto;
}

/* Drag cards */
.dcard {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    background: #fff; border-radius: 14px; padding: 9px 10px 7px;
    min-width: 74px; max-width: 86px;
    border: 2px solid rgba(29,78,216,.08);
    box-shadow: 0 2px 8px rgba(0,0,0,.06), 0 1px 0 rgba(0,0,0,.02);
    cursor: grab; user-select: none; position: relative;
    transition: transform .22s cubic-bezier(.34,1.56,.64,1), box-shadow .22s, border-color .18s;
}
.dcard:hover {
    transform: scale(1.1) translateY(-5px);
    border-color: #bfdbfe;
    box-shadow: 0 10px 24px rgba(29,78,216,.16), 0 2px 0 rgba(29,78,216,.05);
}
.dcard:active { cursor: grabbing; transform: scale(.97); }
.dcard-img { width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; }
.dcard-img img { width: 100%; height: 100%; object-fit: contain; border-radius: 8px; }
.dcard-ico {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1.5px solid rgba(29,78,216,.09);
}
.dcard-lbl { font-size: 11px; font-weight: 900; color: #1e293b; text-align: center; line-height: 1.2; }
.dcard-grip { opacity: 0; transition: opacity .18s; }
.dcard:hover .dcard-grip { opacity: 1; }

/* Zones */
.zones {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(192px, 1fr));
    gap: 12px;
}
.zone {
    background: var(--zbg);
    border: 2px dashed var(--zdash);
    border-radius: 18px; padding: 14px;
    min-height: 172px;
    display: flex; flex-direction: column; gap: 10px;
    transition: transform .18s, box-shadow .18s, border-color .18s;
}
.zone:hover { transform: scale(1.015); box-shadow: 0 6px 20px rgba(0,0,0,.07); }
.zone--over {
    border-style: solid;
    border-color: var(--zsolid);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--zsolid) 20%, transparent), 0 8px 22px rgba(0,0,0,.1);
    transform: scale(1.02);
}

.zone-header { display: flex; align-items: center; gap: 8px; }
.zone-ico-wrap {
    width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
    background: var(--zlight);
    color: var(--zhead);
    display: flex; align-items: center; justify-content: center;
    border: 1.5px solid var(--zdash);
}
.zone-info { flex: 1; }
.zone-name {
    font-family: 'Righteous', cursive;
    font-size: 13.5px; color: var(--zhead); line-height: 1;
}
.zone-sub  { font-size: 10px; font-weight: 700; color: var(--zhead); opacity: .62; margin-top: 2px; }
.zone-count {
    min-width: 24px; height: 24px; border-radius: 50%;
    background: var(--zbadge); color: #fff;
    display: flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 900; flex-shrink: 0;
}
.zone-body {
    display: flex; flex-wrap: wrap; gap: 6px;
    flex: 1; min-height: 82px;
    border-radius: 12px; padding: 7px;
    background: rgba(255,255,255,.5);
    border: 1.5px dashed rgba(0,0,0,.07);
    align-content: flex-start;
}
.zone-hint {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    margin: auto; color: rgba(0,0,0,.18);
    font-size: 10.5px; font-weight: 800; pointer-events: none;
}

/* Placed */
.placed {
    display: flex; flex-direction: column; align-items: center; gap: 3px;
    padding: 6px 8px; border-radius: 11px; border: 2px solid;
    min-width: 58px; position: relative;
    animation: popin .28s cubic-bezier(.34,1.56,.64,1);
}
.placed img { width: 36px; height: 36px; object-fit: contain; border-radius: 6px; }
.placed-ico { width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; }
.placed span { font-size: 10px; font-weight: 900; color: #1e293b; text-align: center; line-height: 1.2; }
.placed--ok  { background: #f0fdf4; border-color: #4ade80; }
.placed--err { background: #fef2f2; border-color: #fca5a5; }
.placed-flag {
    position: absolute; top: -6px; right: -6px;
    width: 18px; height: 18px; border-radius: 50%;
    background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,.1);
    display: flex; align-items: center; justify-content: center;
}
.placed--ok  .placed-flag { color: #22c55e; }
.placed--err .placed-flag { color: #ef4444; }
@keyframes popin { from { transform: scale(.3) rotate(-10deg); opacity: 0; } to { transform: scale(1) rotate(0); opacity: 1; } }

/* ── Transitions ──────────────────────────────────────────── */
.pop-enter-active { transition: all .26s cubic-bezier(.34,1.56,.64,1); }
.pop-leave-active { transition: all .16s ease; position: absolute; }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(.3); }

.phase-enter-active { transition: all .4s cubic-bezier(.34,1.56,.64,1); }
.phase-leave-active { transition: all .24s ease; position: absolute; width: 100%; }
.phase-enter-from   { opacity: 0; transform: translateY(16px) scale(.98); }
.phase-leave-to     { opacity: 0; transform: translateY(-10px) scale(.98); }

.drop-enter-active { transition: all .28s ease; }
.drop-leave-active { transition: all .18s ease; }
.drop-enter-from, .drop-leave-to { opacity: 0; transform: translateY(-5px); }

/* ── Result ───────────────────────────────────────────────── */
.result-body { text-align: center; position: relative; overflow: hidden; }

.confetti-wrap { position: absolute; inset: 0; pointer-events: none; overflow: hidden; }
.c-dot {
    position: absolute; top: -14px;
    left: calc(var(--ci) * 7.1%);
    width: 8px; height: 8px; border-radius: 2px;
    background: hsl(calc(var(--ci) * 25), 85%, 60%);
    animation: cfall calc(.9s + var(--ci) * .07s) ease-in calc(var(--ci) * .06s) both;
}
@keyframes cfall { from { transform: translateY(0) rotate(0); opacity: 1; } to { transform: translateY(230px) rotate(400deg); opacity: 0; } }

.r-trophy {
    width: 80px; height: 80px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
}
.trophy--gold {
    background: linear-gradient(135deg, #fef9c3, #fbbf24);
    color: #78350f;
    box-shadow: 0 8px 28px rgba(251,191,36,.38), 0 0 0 6px rgba(251,191,36,.1);
}
.trophy--dim { background: rgba(100,116,139,.1); color: #94a3b8; }

.r-title { font-family: 'Righteous', cursive; font-size: 22px; color: #1e3a8a; margin-bottom: 14px; }

.r-stars {
    display: flex; justify-content: center; align-items: flex-end; gap: 6px; margin-bottom: 16px;
}
.r-star   { animation: spop .45s cubic-bezier(.34,1.56,.64,1) both; }
.star--on { color: #f59e0b; }
.star--off{ color: #e2e8f0; }
@keyframes spop { from { transform: scale(0) rotate(-25deg); opacity: 0; } to { transform: scale(1) rotate(0); opacity: 1; } }

.r-score {
    display: flex; align-items: baseline; justify-content: center; gap: 3px; margin-bottom: 20px;
}
.r-num { font-family: 'Righteous', cursive; font-size: 3.2rem; color: #1d4ed8; line-height: 1; }
.r-div { font-size: 1.8rem; color: #cbd5e1; margin: 0 2px; }
.r-tot { font-size: 1.8rem; font-weight: 800; color: #94a3b8; }
.r-lbl { font-size: 12px; font-weight: 700; color: #94a3b8; margin-left: 6px; }

.r-wrong { margin-bottom: 20px; text-align: left; }
.rw-toggle {
    width: 100%; display: flex; align-items: center; gap: 6px;
    background: #fef2f2; border: none; border-radius: 10px; padding: 9px 12px;
    font-family: 'Nunito', sans-serif; font-size: 12.5px; font-weight: 800; color: #dc2626;
    cursor: pointer; transition: background .15s;
}
.rw-toggle:hover { background: #fee2e2; }
.chev-open { transform: rotate(90deg); transition: transform .2s; }
.rw-list {
    margin-top: 6px; padding: 10px;
    background: #fff7f7; border-radius: 12px;
    border: 1px solid #fecaca;
    display: flex; flex-direction: column; gap: 6px;
}
.rw-row { display: flex; align-items: center; gap: 7px; font-size: 12.5px; }
.rw-thumb {
    width: 30px; height: 30px; border-radius: 7px;
    background: #fee2e2; overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    color: #64748b; flex-shrink: 0;
}
.rw-thumb img { width: 100%; height: 100%; object-fit: contain; }
.rw-nm  { font-weight: 800; color: #1e293b; flex: 1; }
.rw-ans { font-weight: 900; color: #16a34a; font-size: 11.5px; }

.r-actions { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; }
.act {
    display: flex; align-items: center; gap: 7px;
    height: 46px; padding: 0 24px; border-radius: 12px; border: none;
    font-family: 'Righteous', cursive; font-size: 14px;
    cursor: pointer; transition: all .22s cubic-bezier(.34,1.56,.64,1);
}
.act--ghost   { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.act--ghost:hover { background: #eff6ff; border-color: #bfdbfe; color: #1d4ed8; transform: translateY(-2px); }
.act--primary { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; box-shadow: 0 4px 16px rgba(29,78,216,.32); }
.act--primary:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 8px 24px rgba(29,78,216,.42); }

/* ── Responsive ───────────────────────────────────────────── */
@media (max-width: 820px) {
    .body { grid-template-columns: 1fr; gap: 14px; padding: 14px 14px 28px; }
    .sidebar { flex-direction: row; flex-wrap: wrap; align-items: flex-start; gap: 12px; }
    .sb-info { flex: 1; min-width: 160px; margin-bottom: 0; }
    .mascot-wrap { width: 105px; }
    .mascot { width: 105px !important; }
    .sb-hint { display: none; }
    .sb-title { font-size: 17px; }
}
@media (max-width: 560px) {
    .card-body { padding: 15px 14px 20px; }
    .zones { grid-template-columns: 1fr; }
    .r-actions { flex-direction: column; }
    .r-num { font-size: 2.6rem; }
    .t-btn span { display: none; }
    .t-btn { padding: 7px 10px; }
}
@media (max-width: 400px) {
    .dcard { min-width: 66px; padding: 7px 8px; }
    .dcard-img, .dcard-ico { width: 44px; height: 44px; }
}
</style>
