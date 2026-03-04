<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
    ArrowLeft, Home, RotateCcw, CheckCircle,
    ChevronRight, Award, Target, XCircle,
    GripHorizontal, Layers,
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

// ── Mascot speech ──────────────────────────────────────────────
const MSGS = [
    'Seret gambar ke kelompok yang tepat!',
    'Pikirkan baik-baik sebelum menjawab ya!',
    'Kamu pasti bisa, semangat terus!',
    'Perhatikan ciri-ciri setiap gambarnya!',
    'Hampir selesai, terus semangat!',
]
const msgIdx    = ref(0)
const displayed = ref('')
const typing    = ref(false)
let typeTimer   = null
let cycleTimer  = null

const typeText = (text) => {
    displayed.value = ''; typing.value = true; let i = 0
    clearInterval(typeTimer)
    typeTimer = setInterval(() => {
        if (i < text.length) displayed.value += text[i++]
        else { typing.value = false; clearInterval(typeTimer) }
    }, 48)
}
const nextMsg = () => { msgIdx.value = (msgIdx.value + 1) % MSGS.length; typeText(MSGS[msgIdx.value]) }
const startCycle = () => { typeText(MSGS[0]); clearInterval(cycleTimer); cycleTimer = setInterval(nextMsg, 5000) }

// ── Game data ──────────────────────────────────────────────────
// Icon map untuk dummy data
const ICON_MAP = { Fish, TreePine, Bird, Mountain, Leaf, Droplets, Sun, CloudRain }
const iconComponent = (name) => ICON_MAP[name] ?? Target

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

const dragItems   = ref(buildItems())
const dropZones   = ref(buildZones())
const dragged     = ref(null)
const phase       = ref('play')
const score       = ref(0)
const showWrong   = ref(false)

const unplaced    = computed(() => dragItems.value.filter(i => !i.placed))
const placedCount = computed(() => dragItems.value.filter(i => i.placed).length)
const totalCount  = computed(() => dragItems.value.length)
const progressPct = computed(() => Math.round((placedCount.value / Math.max(totalCount.value, 1)) * 100))
const wrongItems  = computed(() => dragItems.value.filter(i => i.placed && !i.correct))
const starCount   = computed(() => { const p = score.value / Math.max(totalCount.value, 1); return p === 1 ? 3 : p >= .6 ? 2 : p >= .3 ? 1 : 0 })
const resultTitle = computed(() => ['Coba Lagi!', 'Terus Semangat!', 'Bagus Sekali!', 'Sempurna!'][starCount.value])

// ── Mouse drag ─────────────────────────────────────────────────
const onDragStart = item => { dragged.value = item }
const onDragOver  = e => e.preventDefault()
const onDrop = zone => {
    if (!dragged.value) return
    const item = dragged.value
    item.placed = true; item.correct = item.groupId === zone.id
    zone.items.push(item); dragged.value = null
    if (unplaced.value.length === 0) { score.value = dragItems.value.filter(i => i.correct).length; setTimeout(() => phase.value = 'result', 600) }
}

// ── Touch drag ─────────────────────────────────────────────────
let tItem = null, tClone = null
const onTouchStart = (item, e) => {
    tItem = item
    const r = e.currentTarget.getBoundingClientRect()
    tClone = e.currentTarget.cloneNode(true)
    Object.assign(tClone.style, { position:'fixed', zIndex:'9999', opacity:'.88', pointerEvents:'none', width:r.width+'px', height:r.height+'px', left:r.left+'px', top:r.top+'px', transform:'scale(1.12)', borderRadius:'14px' })
    document.body.appendChild(tClone)
}
const onTouchMove = e => {
    e.preventDefault()
    if (!tClone) return
    const t = e.touches[0]
    tClone.style.left = (t.clientX - 44)+'px'; tClone.style.top = (t.clientY - 44)+'px'
}
const onTouchEnd = e => {
    tClone?.remove(); tClone = null
    if (!tItem) return
    const t = e.changedTouches[0]
    const el = document.elementsFromPoint(t.clientX, t.clientY).find(el => el.dataset.zoneid)
    if (el) { const z = dropZones.value.find(z => z.id == el.dataset.zoneid); if (z) { dragged.value = tItem; onDrop(z) } }
    tItem = null
}

const reset = () => {
    dropZones.value.forEach(z => z.items = [])
    dragItems.value.forEach(i => { i.placed = false; i.correct = null })
    score.value = 0; phase.value = 'play'; showWrong.value = false
}

// ── Colors per zone ────────────────────────────────────────────
const ZONE_COLORS = [
    { bg:'#f0fdf4', border:'rgba(74,222,128,.45)',  head:'#15803d' },
    { bg:'#f0f9ff', border:'rgba(56,189,248,.45)',  head:'#0369a1' },
    { bg:'#fdf4ff', border:'rgba(192,132,252,.45)', head:'#7e22ce' },
    { bg:'#fff7ed', border:'rgba(251,191,36,.45)',  head:'#92400e' },
]
const zc = i => ZONE_COLORS[i % ZONE_COLORS.length]

const goBack = () => router.visit(route('student.missions.show', props.module?.id ?? 1))
const goHome = () => router.visit(route('student.missions.index'))

onMounted(startCycle)
onUnmounted(() => { clearInterval(cycleTimer); clearInterval(typeTimer) })
</script>

<template>
    <Head :title="mission?.title ?? 'Seret dan Letakkan'" />

    <div class="pg-page">
        <!-- BG -->
        <div class="pg-bg">
            <img v-if="background?.image" :src="`/storage/${background.image}`" class="pg-bg-img" alt="" />
            <div class="pg-bg-overlay"></div>
        </div>

        <!-- TOPBAR -->
        <header class="pg-topbar">
            <button class="pg-btn pg-btn-icon" @click="goBack"><ArrowLeft :size="17" /> Kembali</button>
            <nav class="pg-breadcrumb">
                <span>{{ module?.title ?? 'Misi' }}</span>
                <ChevronRight :size="13" />
                <span class="pg-breadcrumb-active">{{ mission?.title ?? 'Aktivitas' }}</span>
            </nav>
            <button class="pg-btn pg-btn-icon" @click="goHome"><Home :size="17" /></button>
        </header>

        <!-- MASCOT -->
        <div class="pg-mascot" @click="nextMsg">
            <div class="pg-speech">
                <p class="pg-speech-text">{{ displayed }}<span v-if="typing" class="pg-cursor">|</span></p>
                <span class="pg-speech-hint">Klik untuk pesan lain ✦</span>
            </div>
            <img v-if="mascot?.image" :src="`/storage/${mascot.image}`" class="pg-mascot-img" alt="Maskot" />
            <div v-else class="mf-wrap">
                <div class="mf-body">
                    <div class="mf-eyes"><div class="mf-eye"></div><div class="mf-eye"></div></div>
                    <div class="mf-smile"></div>
                </div>
            </div>
        </div>

        <!-- ════ PLAY ════ -->
        <Transition name="pg-slide">
        <main v-if="phase === 'play'" class="pg-content dd-layout" key="play">

            <!-- Header -->
            <div class="dd-header pg-fade-in" style="--d:0ms">
                <div>
                    <div class="pg-badge" style="margin-bottom:.5rem"><GripHorizontal :size="13" /> Seret &amp; Letakkan</div>
                    <h1 class="pg-title">Seret gambar ke kelompok yang tepat.</h1>
                    <p class="pg-subtitle" style="margin-top:.35rem">{{ mission?.title ?? 'Klasifikasikan setiap gambar!' }}</p>
                </div>
                <div class="dd-meta">
                    <div class="pg-progress" style="min-width:180px">
                        <span class="pg-progress-label">{{ placedCount }}/{{ totalCount }}</span>
                        <div class="pg-progress-track"><div class="pg-progress-fill" :style="`width:${progressPct}%`"></div></div>
                    </div>
                    <button class="pg-btn pg-btn-ghost" @click="reset"><RotateCcw :size="14" /> Ulangi</button>
                </div>
            </div>

            <!-- Item bank -->
            <div class="dd-bank pg-card pg-fade-in" style="--d:80ms" @touchmove.prevent="onTouchMove" @touchend="onTouchEnd">
                <div class="dd-bank-label">
                    <Layers :size="14" /><span>Gambar tersedia</span>
                    <span class="dd-bank-count">{{ unplaced.length }}</span>
                </div>
                <div class="dd-bank-grid">
                    <TransitionGroup name="pg-pop">
                        <div v-for="item in unplaced" :key="item.id" class="dd-item"
                            draggable="true"
                            @dragstart="onDragStart(item)"
                            @touchstart.prevent="onTouchStart(item, $event)">
                            <div class="dd-item-img">
                                <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
                                <div v-else class="dd-item-ph" :class="item.iconName ? 'dd-item-ph-icon' : ''">
                                    <component :is="iconComponent(item.iconName)" v-if="item.iconName" :size="32" stroke-width="1.5" color="var(--pg-blue-dk)" />
                                    <Target v-else :size="28" stroke-width="1.4" color="var(--pg-blue)" />
                                </div>
                            </div>
                            <span class="dd-item-label">{{ item.label }}</span>
                        </div>
                    </TransitionGroup>
                    <div v-if="unplaced.length === 0" class="dd-bank-done">
                        <CheckCircle :size="26" color="var(--pg-green)" />
                        <span>Semua gambar sudah ditempatkan!</span>
                    </div>
                </div>
            </div>

            <!-- Drop zones -->
            <div class="dd-zones pg-fade-in" style="--d:160ms">
                <div v-for="(zone, zi) in dropZones" :key="zone.id" class="dd-zone"
                    :data-zoneid="zone.id"
                    :style="`background:${zc(zi).bg}; border-color:${zc(zi).border}`"
                    @dragover="onDragOver" @drop="onDrop(zone)">

                    <div class="dd-zone-header" :style="`color:${zc(zi).head}`">
                        <div class="dd-zone-icon" :style="`background:${zc(zi).border}; color:${zc(zi).head}`">
                            <Layers :size="18" stroke-width="2" />
                        </div>
                        <div>
                            <div class="dd-zone-name">{{ zone.label }}</div>
                            <div v-if="zone.sublabel" class="dd-zone-sub">{{ zone.sublabel }}</div>
                        </div>
                        <div class="dd-zone-cnt" :style="`background:${zc(zi).head}`">{{ zone.items.length }}</div>
                    </div>

                    <div class="dd-zone-body" :data-zoneid="zone.id">
                        <div v-for="item in zone.items" :key="item.id" class="dd-placed"
                            :class="item.correct ? 'dd-ok' : 'dd-err'">
                            <img v-if="item.image" :src="`/storage/${item.image}`" :alt="item.label" />
                            <div v-else class="dd-placed-ph">
                                <component :is="iconComponent(item.iconName)" v-if="item.iconName" :size="22" stroke-width="1.5" />
                                <Target v-else :size="22" stroke-width="1.5" />
                            </div>
                            <span>{{ item.label }}</span>
                            <div class="dd-placed-badge">
                                <CheckCircle v-if="item.correct"  :size="14" />
                                <XCircle     v-else               :size="14" />
                            </div>
                        </div>
                        <div v-if="zone.items.length === 0" class="dd-zone-hint" :data-zoneid="zone.id">
                            <GripHorizontal :size="20" stroke-width="1.5" /><span>Letakkan di sini</span>
                        </div>
                    </div>
                </div>
            </div>

        </main>
        </Transition>

        <!-- ════ RESULT ════ -->
        <Transition name="pg-slide">
        <main v-if="phase === 'result'" class="pg-content res-wrap" key="result">
            <div class="res-card pg-card-solid pg-fade-in" style="--d:0ms">
                <div class="pg-confetti-wrap">
                    <div v-for="n in 11" :key="n" class="pg-confetti" :style="`--i:${n}`">
                        <Award :size="16" color="#f59e0b" />
                    </div>
                </div>

                <div class="res-icon" :class="starCount >= 2 ? 'res-good' : 'res-low'">
                    <Award :size="40" stroke-width="1.6" />
                </div>

                <h2 class="pg-title res-title">{{ resultTitle }}</h2>

                <div class="res-stars">
                    <div v-for="n in 3" :key="n" class="res-star"
                        :class="n <= starCount ? 'star-lit' : 'star-dim'"
                        :style="`animation-delay:${n*.18}s`">
                        <Award :size="n === 2 ? 38 : 30" stroke-width="1.5" />
                    </div>
                </div>

                <div class="res-score">
                    <span class="sc-num">{{ score }}</span>
                    <span class="sc-sep">/</span>
                    <span class="sc-den">{{ totalCount }}</span>
                    <span class="sc-lbl">jawaban benar</span>
                </div>

                <div v-if="wrongItems.length" class="res-wrong">
                    <button class="wrong-toggle" @click="showWrong = !showWrong">
                        <XCircle :size="15" color="var(--pg-red)" />
                        {{ wrongItems.length }} jawaban perlu diperbaiki
                        <ChevronRight :size="14" :class="showWrong ? 'chev-r' : ''" style="margin-left:auto" />
                    </button>
                    <Transition name="pg-slide">
                        <div v-if="showWrong" class="wrong-list">
                            <div v-for="w in wrongItems" :key="w.id" class="wrong-row">
                                <img v-if="w.image" :src="`/storage/${w.image}`" :alt="w.label" />
                                <div v-else class="wrong-ph">
                                    <component :is="iconComponent(w.iconName)" v-if="w.iconName" :size="18" stroke-width="1.5" />
                                    <Target v-else :size="18" stroke-width="1.5" />
                                </div>
                                <span class="wrong-name">{{ w.label }}</span>
                                <ChevronRight :size="13" color="#94a3b8" />
                                <span class="wrong-ans">{{ dropZones.find(z => z.id === w.groupId)?.label ?? '?' }}</span>
                            </div>
                        </div>
                    </Transition>
                </div>

                <div class="res-actions">
                    <button class="pg-btn pg-btn-ghost" @click="reset"><RotateCcw :size="16" /> Coba Lagi</button>
                    <button class="pg-btn pg-btn-primary" @click="goHome"><Home :size="16" /> Kembali ke Misi</button>
                </div>
            </div>
        </main>
        </Transition>
    </div>
</template>

<style scoped>
/* playground.css harus di-import di app.js / app.css:
   import '@/css/playground.css'
   ─────────────────────────────────────────────────── */

.dd-layout { display:flex; flex-direction:column; gap:1.1rem; max-width:860px; }

.dd-header { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
.dd-meta   { display:flex; flex-direction:column; align-items:flex-end; gap:.6rem; flex-shrink:0; }

/* Bank */
.dd-bank { padding:1rem 1.1rem; }
.dd-bank-label { display:flex; align-items:center; gap:.4rem; font-size:.78rem; font-weight:800; color:var(--pg-gray); margin-bottom:.75rem; text-transform:uppercase; letter-spacing:.4px; }
.dd-bank-count { background:var(--pg-blue); color:#fff; font-size:.7rem; font-weight:900; padding:.1rem .5rem; border-radius:20px; margin-left:auto; }
.dd-bank-grid  { display:flex; flex-wrap:wrap; gap:.65rem; min-height:80px; align-items:flex-start; }
.dd-bank-done  { display:flex; align-items:center; gap:.5rem; color:var(--pg-green); font-weight:800; font-size:.88rem; margin:auto; }

/* Item */
.dd-item { display:flex; flex-direction:column; align-items:center; gap:.3rem; background:#fff; border-radius:14px; padding:.6rem .7rem; min-width:78px; max-width:90px; border:2px solid rgba(2,132,199,.1); box-shadow:0 3px 12px rgba(0,0,0,.08); cursor:grab; user-select:none; transition:all .2s cubic-bezier(.34,1.56,.64,1); }
.dd-item:hover { transform:scale(1.09) translateY(-4px); border-color:var(--pg-blue-lt); box-shadow:0 8px 22px rgba(56,189,248,.3); }
.dd-item:active { cursor:grabbing; transform:scale(.95); }
.dd-item-img   { width:58px; height:58px; display:flex; align-items:center; justify-content:center; }
.dd-item-img img { width:100%; height:100%; object-fit:contain; border-radius:8px; }
.dd-item-ph    { width:58px; height:58px; background:rgba(2,132,199,.07); border-radius:10px; display:flex; align-items:center; justify-content:center; }
.dd-item-ph-icon { background:rgba(12,74,110,.06); border:1.5px solid rgba(2,132,199,.15); }
.dd-item-label { font-size:.75rem; font-weight:800; color:#334155; text-align:center; line-height:1.2; }

/* Zones */
.dd-zones { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px,1fr)); gap:1rem; }
.dd-zone  { border-radius:20px; border:2.5px dashed; padding:1rem; min-height:180px; display:flex; flex-direction:column; gap:.75rem; transition:transform .15s,box-shadow .15s; }
.dd-zone:hover { transform:scale(1.01); box-shadow:0 8px 28px rgba(0,0,0,.1); }

.dd-zone-header { display:flex; align-items:center; gap:.6rem; }
.dd-zone-icon   { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.dd-zone-name   { font-family:var(--pg-font-display); font-size:1rem; font-weight:900; line-height:1; }
.dd-zone-sub    { font-size:.74rem; font-weight:700; opacity:.7; margin-top:.15rem; }
.dd-zone-cnt    { margin-left:auto; min-width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.78rem; font-weight:900; color:#fff; flex-shrink:0; }

.dd-zone-body   { display:flex; flex-wrap:wrap; gap:.5rem; flex:1; min-height:80px; border-radius:12px; padding:.4rem; border:1.5px dashed rgba(0,0,0,.08); background:rgba(255,255,255,.4); align-content:flex-start; }
.dd-zone-hint   { display:flex; flex-direction:column; align-items:center; gap:.3rem; color:rgba(0,0,0,.22); font-size:.76rem; font-weight:800; margin:auto; pointer-events:none; }

/* Placed */
.dd-placed { display:flex; flex-direction:column; align-items:center; gap:.2rem; padding:.5rem .55rem; border-radius:12px; border:2px solid transparent; min-width:64px; font-size:.72rem; font-weight:800; color:#334155; position:relative; animation:placeIn .3s cubic-bezier(.34,1.56,.64,1); }
.dd-placed img { width:44px; height:44px; object-fit:contain; border-radius:7px; }
.dd-placed-ph  { width:44px; height:44px; display:flex; align-items:center; justify-content:center; }
.dd-ok  { background:#dcfce7; border-color:#4ade80; }
.dd-err { background:#fee2e2; border-color:#fca5a5; }
.dd-placed-badge { position:absolute; top:-6px; right:-6px; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#fff; box-shadow:0 2px 6px rgba(0,0,0,.12); }
.dd-ok  .dd-placed-badge { color:var(--pg-green); }
.dd-err .dd-placed-badge { color:var(--pg-red); }
@keyframes placeIn { from { transform:scale(.4); opacity:0; } to { transform:scale(1); opacity:1; } }

/* Result */
.res-wrap { display:flex; align-items:center; justify-content:center; min-height:calc(100vh - 5rem); }
.res-card { max-width:460px; width:100%; padding:2.2rem 2rem; text-align:center; position:relative; overflow:hidden; }
.res-icon { width:80px; height:80px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; }
.res-good { background:linear-gradient(135deg,#fef3c7,#fbbf24); color:#92400e; box-shadow:0 8px 28px rgba(245,158,11,.35); }
.res-low  { background:rgba(100,116,139,.1); color:var(--pg-gray); }
.res-title { margin-bottom:.85rem; }
.res-stars { display:flex; justify-content:center; align-items:flex-end; gap:.5rem; margin-bottom:1.1rem; }
.res-star  { animation:starPop .45s cubic-bezier(.34,1.56,.64,1) both; }
.star-lit  { color:#f59e0b; } .star-dim { color:#e2e8f0; }
@keyframes starPop { from { transform:scale(0) rotate(-30deg); } to { transform:scale(1) rotate(0); } }
.res-score { display:flex; align-items:baseline; justify-content:center; gap:.3rem; margin-bottom:1.25rem; }
.sc-num { font-family:var(--pg-font-display); font-size:3.2rem; font-weight:900; color:var(--pg-blue); line-height:1; }
.sc-sep { font-size:1.8rem; color:#cbd5e1; } .sc-den { font-size:1.8rem; font-weight:800; color:#94a3b8; }
.sc-lbl { font-size:.82rem; font-weight:700; color:#94a3b8; margin-left:.4rem; }

.res-wrong { margin-bottom:1.25rem; text-align:left; }
.wrong-toggle { display:flex; align-items:center; gap:.4rem; font-family:var(--pg-font-body); font-size:.82rem; font-weight:800; color:#dc2626; background:#fee2e2; border:none; border-radius:10px; padding:.5rem .9rem; cursor:pointer; width:100%; transition:background .15s; }
.wrong-toggle:hover { background:#fecaca; }
.chev-r { transform:rotate(90deg); }
.wrong-list { margin-top:.5rem; display:flex; flex-direction:column; gap:.4rem; background:#fff7f7; border-radius:12px; padding:.75rem; border:1px solid #fecaca; }
.wrong-row  { display:flex; align-items:center; gap:.5rem; font-size:.82rem; }
.wrong-row img { width:30px; height:30px; object-fit:contain; border-radius:6px; }
.wrong-ph   { width:30px; height:30px; display:flex; align-items:center; justify-content:center; color:var(--pg-gray); }
.wrong-name { font-weight:800; color:#334155; flex:1; }
.wrong-ans  { font-weight:900; color:var(--pg-green); }

.res-actions { display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap; }

/* Mascot fallback */
.mf-wrap { width:80px; }
.mf-body { width:72px; height:96px; background:linear-gradient(180deg,#7dd3fc,#0284c7); border-radius:50% 50% 40% 40%; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px; box-shadow:0 8px 20px rgba(2,132,199,.4); }
.mf-eyes { display:flex; gap:14px; }
.mf-eye  { width:13px; height:13px; background:#fff; border-radius:50%; position:relative; }
.mf-eye::after { content:''; position:absolute; width:7px; height:7px; background:#1e293b; border-radius:50%; top:3px; left:3px; }
.mf-smile { width:26px; height:13px; border:3px solid #fff; border-top:none; border-radius:0 0 13px 13px; }

/* Responsive */
@media (max-width:640px) {
    .dd-header { flex-direction:column; gap:.75rem; }
    .dd-meta   { flex-direction:row; align-items:center; width:100%; justify-content:space-between; }
    .dd-zones  { grid-template-columns:1fr; }
    .res-card  { padding:1.75rem 1.25rem; }
    .res-actions { flex-direction:column; }
    .sc-num    { font-size:2.5rem; }
}
@media (max-width:400px) {
    .dd-item    { min-width:68px; padding:.5rem; }
    .dd-item-img, .dd-item-ph { width:48px; height:48px; }
}
</style>
