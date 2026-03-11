<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  ArrowLeft, ArrowRight, Home, Zap, Clock, Music2, VolumeX,
  Target, Trophy, Rocket, Flag, Loader2, Sparkles, Star,
  PartyPopper, BookOpen, Award, ListChecks, Eye, CircleCheck,
  Timer, ChevronRight, CheckCircle2, MousePointerClick,
} from 'lucide-vue-next'

import Multiple_choice from '@/Components/Quiz/Multiple_choice.vue'
import True_false      from '@/Components/Quiz/True_false.vue'
import Case_study      from '@/Components/Quiz/Case_study.vue'
import Materials       from '@/Components/Quiz/Materials.vue'
import Drag_drop       from '@/Components/Quiz/Drag_drop.vue'
import { useMusic } from '@/Composable/useMusic'

// ─── Props ──────────────────────────────────────────────────────────────────
const props = defineProps({
  quiz:      { type: Object, required: true },
  module:    { type: Object, default: () => ({ id: null, name: 'Modul' }) },
  user:      { type: Object, default: () => ({ name: 'Siswa' }) },
  backsound: { type: String, default: null },
  background:{ type: String, default: null },
})

const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } = useMusic()

// ─── Component map ───────────────────────────────────────────────────────────
const COMPONENT_MAP = {
  multiple_choices: Multiple_choice,
  true_false:       True_false,
  case_study:       Case_study,
  drag_drop:        Drag_drop,
  material:         Materials,
}

const TYPE_META = {
  multiple_choices: { label: 'Pilihan Ganda',   color: '#3b82f6', bg: '#dbeafe' },
  true_false:       { label: 'Benar / Salah',    color: '#8b5cf6', bg: '#ede9fe' },
  case_study:       { label: 'Studi Kasus',      color: '#0891b2', bg: '#cffafe' },
  drag_drop:        { label: 'Seret & Letakkan', color: '#f59e0b', bg: '#fef3c7' },
  material:         { label: 'Materi',           color: '#10b981', bg: '#d1fae5' },
}
const typeMeta = (t) => TYPE_META[t] || { label: t, color: '#64748b', bg: '#f1f5f9' }

// ─── Phase ──────────────────────────────────────────────────────────────────
const phase = ref('intro')   // 'intro' | 'quiz' | 'done'

// ─── Mascot (per soal dari DB, fallback ke asset statis) ─────────────────────
const MASCOT_FALLBACK = {
  intro: '/images/templates/pose_nunjuk.png',
  quiz:  '/images/templates/pose_pikir.png',
  done:  '/images/templates/pose_jempol.png',
}

const mascotSrc = computed(() => {
  // Phase done / intro → selalu pakai asset statis
  if (phase.value === 'intro') return MASCOT_FALLBACK.intro
  if (phase.value === 'done')  return MASCOT_FALLBACK.done

  // Phase quiz → cek apakah soal aktif punya mascot dari DB
  const dbMascot = currentQ.value?.mascot?.image
  if (dbMascot) {
    // Jika sudah berupa URL penuh (http/https), pakai langsung
    // Jika path relatif (storage/...), tambahkan /
    return dbMascot.startsWith('http') ? dbMascot : `/${dbMascot}`
  }

  // Fallback ke asset statis
  return MASCOT_FALLBACK.quiz
})

// ─── Bubbles ────────────────────────────────────────────────────────────────
const BUBBLES_INTRO = ['Posttest menanti, semangat!', 'Siap memulai tantangan?', 'Tunjukkan kemampuanmu!']
const BUBBLES_QUIZ  = ['Semangat ya!', 'Baca dengan teliti!', 'Pikirkan baik-baik!', 'Hampir selesai!', 'Fokus dan tenang!']
const BUBBLES_DONE  = ['Luar biasa! Kamu keren!', 'Posttest selesai! Hebat!', 'Jempol buat kamu!']
const BUBBLES = computed(() => {
  if (phase.value === 'intro') return BUBBLES_INTRO
  if (phase.value === 'done')  return BUBBLES_DONE
  return BUBBLES_QUIZ
})
const bubbleIdx     = ref(0)
const bubbleVisible = ref(true)
let   bubbleTimer   = null
const rotateBubble  = () => {
  bubbleVisible.value = false
  setTimeout(() => { bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.value.length; bubbleVisible.value = true }, 300)
}
watch(phase, () => { bubbleIdx.value = 0; bubbleVisible.value = false; setTimeout(() => { bubbleVisible.value = true }, 200) })

// ─── Timer + sessionStorage ──────────────────────────────────────────────────
const ready      = ref(false)
const brandMoved = ref(false)
const timeLimit  = computed(() => (props.quiz?.time_limit ?? 10) * 60)

const SS_KEY = `geniuss_posttest_timer_${props.quiz?.id}`
function ssGet(key, fallback) {
  try { const v = sessionStorage.getItem(key); return v !== null ? JSON.parse(v) : fallback } catch { return fallback }
}
function ssSet(key, val) { try { sessionStorage.setItem(key, JSON.stringify(val)) } catch {} }
function ssDel(key)      { try { sessionStorage.removeItem(key) } catch {} }

const remaining = ref(ssGet(SS_KEY, timeLimit.value))
let   timerInt  = null

const timerDisplay = computed(() => {
  const m = String(Math.floor(remaining.value / 60)).padStart(2, '0')
  const s = String(remaining.value % 60).padStart(2, '0')
  return `${m}:${s}`
})
const timerPct     = computed(() => (remaining.value / timeLimit.value) * 100)
const timerWarning = computed(() => remaining.value <= 60)

function startTimer() {
  timerInt = setInterval(() => {
    if (remaining.value <= 0) {
      clearInterval(timerInt)
      ssDel(SS_KEY)
      submitQuiz()
      return
    }
    remaining.value--
    ssSet(SS_KEY, remaining.value)
  }, 1000)
}

// ─── Music ───────────────────────────────────────────────────────────────────
const audioRef = ref(null)

// ─── Questions & answers ─────────────────────────────────────────────────────
const questions   = computed(() => props.quiz?.questions ?? [])
const totalQ      = computed(() => questions.value.length)
const currentIdx  = ref(0)
const currentQ    = computed(() => questions.value[currentIdx.value] ?? null)
const answers     = ref({})
const shakeActive = ref(false)
const submitting  = ref(false)

const isFirst = computed(() => currentIdx.value === 0)
const isLast  = computed(() => currentIdx.value === totalQ.value - 1)

const quizType = computed(() => props.quiz?.type ?? 'multiple_choices')

function isQuestionAnswered(q) {
  const val = answers.value[q?.id]
  if (quizType.value === 'drag_drop') {
    return val && typeof val === 'object' && !Array.isArray(val) && Object.keys(val).length > 0
  }
  if (Array.isArray(val)) return val.length > 0
  return val !== null && val !== undefined && val !== ''
}

const canGoNext = computed(() => {
  if (!currentQ.value) return true
  if (quizType.value === 'material') return true
  return isQuestionAnswered(currentQ.value)
})

const answeredCnt = computed(() => questions.value.filter(q => isQuestionAnswered(q)).length)
const progressPct = computed(() => totalQ.value === 0 ? 100 : Math.round((answeredCnt.value / totalQ.value) * 100))

function updateAnswer({ questionId, value }) {
  answers.value = { ...answers.value, [questionId]: value }
}

function goPrev() { if (!isFirst.value) currentIdx.value-- }
function goNext() {
  if (!canGoNext.value) {
    shakeActive.value = true
    setTimeout(() => { shakeActive.value = false }, 600)
    return
  }
  if (!isLast.value) currentIdx.value++
  else submitQuiz()
}

// ─── Start / Submit ──────────────────────────────────────────────────────────
function startQuiz() {
  brandMoved.value = true
  setTimeout(() => { phase.value = 'quiz'; startTimer() }, 420)
}

function submitQuiz() {
  if (submitting.value) return
  submitting.value = true
  clearInterval(timerInt)
  ssDel(SS_KEY)
  phase.value = 'done'

  const payload = {
    quiz_id:    props.quiz?.id,
    module_id:  props.module?.id,
    time_taken: timeLimit.value - remaining.value,
    answers:    Object.entries(answers.value).map(([question_id, value]) => ({
      question_id,
      value: Array.isArray(value) ? value : (typeof value === 'object' && value !== null ? value : value),
    })),
  }

  router.post(route('playground.posttest.submit'), payload, {
    preserveState: true,
    onError: () => { submitting.value = false },
  })
}

function goHome() { router.visit(route('playground.index')) }
function goBack() { router.visit(route('playground.index')) }

// ─── Petunjuk items ──────────────────────────────────────────────────────────
const INSTR_ITEMS = [
  { color: 'red',    icon: Eye,          text: 'Baca setiap soal dengan teliti.' },
  { color: 'yellow', icon: CircleCheck,  text: 'Pilih atau kerjakan jawaban paling tepat.' },
  { color: 'blue',   icon: Timer,        text: 'Kerjakan sesuai waktu yang tersedia.' },
  { color: 'green',  icon: ChevronRight, text: 'Klik Berikutnya setelah menjawab.' },
]

// ─── Lifecycle ───────────────────────────────────────────────────────────────
onMounted(() => {
  setTimeout(() => { ready.value = true }, 80)
  bubbleTimer = setInterval(rotateBubble, 3500)
  document.addEventListener('visibilitychange', handleVisibility)
  setTimeout(() => initAutoMusic(props.backsound), 100)
})
onUnmounted(() => {
  clearInterval(timerInt)
  clearInterval(bubbleTimer)
  document.removeEventListener('visibilitychange', handleVisibility)
  destroyAudio()
})
</script>

<template>
  <div class="root">
    <div style="display:none">
      <link rel="preconnect" href="https://fonts.googleapis.com"/>
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
      <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet"/>
    </div>

    <!-- ══ BG ══ -->
    <div class="bg">
      <div class="bg-img" :style="props.background ? { backgroundImage: `url(${props.background})` } : {}"></div>
      <div class="bg-tint"></div>
      <div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
      <div class="sh sh-circle c1"></div><div class="sh sh-circle c2"></div>
      <div class="sh sh-ring r1"></div><div class="sh sh-ring r2"></div><div class="sh sh-ring r3"></div>
      <div class="sh sh-dot d1"></div><div class="sh sh-dot d2"></div><div class="sh sh-dot d3"></div>
      <div class="sh sh-dot d4"></div><div class="sh sh-dot d5"></div>
      <div class="bg-dots"></div>
    </div>

    <!-- ══ TOPBAR ══ -->
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
        <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic(props.backsound)">
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

      <!-- ── SIDEBAR ── -->
      <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
        <div class="sb-info">
          <span class="sb-chip">
            <Target v-if="phase === 'intro'"     :size="11" :stroke-width="2.5"/>
            <Clock  v-else-if="phase === 'quiz'" :size="11" :stroke-width="2.5"/>
            <Trophy v-else                       :size="11" :stroke-width="2.5"/>
            {{ phase === 'intro' ? 'Posttest' : phase === 'quiz' ? 'Mengerjakan' : 'Selesai' }}
          </span>
          <h1 class="sb-title">{{ module.name }}</h1>
          <p class="sb-sub">{{ quiz.title }}</p>
          <p v-if="phase === 'quiz'" class="sb-soal">
            Soal {{ currentIdx + 1 }} / {{ totalQ }}
          </p>

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

          <div v-if="phase !== 'quiz'" class="sb-pills">
            <div class="sb-pill">
              <BookOpen :size="13" :stroke-width="2"/>
              <span>{{ totalQ }} Soal</span>
            </div>
            <div class="sb-pill">
              <Clock :size="13" :stroke-width="2"/>
              <span>{{ quiz.time_limit ?? 10 }} Menit</span>
            </div>
          </div>
        </div>

        <!-- Mascot -->
        <div class="mascot-wrap">
          <Transition name="bbl">
            <div v-if="bubbleVisible" class="bubble"
                 :class="{ 'bubble--intro': phase === 'intro', 'bubble--done': phase === 'done' }">
              <span>{{ BUBBLES[bubbleIdx] }}</span>
              <i class="bbl-o"></i><i class="bbl-i"></i>
            </div>
          </Transition>
          <Transition name="pose-swap">
            <div class="mascot-frame" :key="phase">
              <img :src="mascotSrc" alt="Maskot" class="mascot"/>
              <div class="mascot-shadow"></div>
            </div>
          </Transition>
        </div>
      </aside>

      <!-- ── MAIN ── -->
      <section class="main" :class="{ 'main--on': ready }">

        <!-- ══ INTRO CARD ══ -->
        <Transition name="pg">
        <div v-if="phase === 'intro'" class="icard">
          <div class="icard-head">
            <div class="icard-hdeco icard-hdeco-1"></div>
            <div class="icard-hdeco icard-hdeco-2"></div>
            <div class="icard-head-inner">
              <div class="icard-eyebrow">
                <Zap :size="11" :stroke-width="2.5" fill="currentColor"/>
                <span>Posttest</span>
              </div>
              <h2 class="icard-title">{{ module.name }}</h2>
              <p class="icard-sub">{{ quiz.description ?? 'Jawab semua soal untuk mengukur kemampuanmu setelah menyelesaikan semua misi!' }}</p>
            </div>
          </div>

          <div class="icard-stats">
            <div class="istat istat--red">
              <div class="istat-icon"><BookOpen :size="19" :stroke-width="1.8"/></div>
              <span class="istat-val">{{ totalQ }}</span>
              <span class="istat-lbl">Soal</span>
            </div>
            <div class="istat istat--yellow">
              <div class="istat-icon"><Clock :size="19" :stroke-width="1.8"/></div>
              <span class="istat-val">{{ quiz.time_limit ?? 10 }}</span>
              <span class="istat-lbl">Menit</span>
            </div>
            <div class="istat istat--blue">
              <div class="istat-icon"><Award :size="19" :stroke-width="1.8"/></div>
              <span class="istat-val">XP</span>
              <span class="istat-lbl">Hadiah</span>
            </div>
          </div>

          <div class="icard-body">
            <div class="icard-instr-hd">
              <ListChecks :size="14" :stroke-width="2.5"/>
              <span>Petunjuk Pengerjaan</span>
            </div>
            <div class="icard-instr-grid">
              <div v-for="(item, i) in INSTR_ITEMS" :key="i"
                   class="instr-row" :class="`instr-row--${item.color}`">
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
        <div v-if="phase === 'quiz' && currentQ" class="qcard" :class="{ 'qcard--shake': shakeActive }">
          <div class="qcard-bar" :style="{ background: typeMeta(quizType).color }"></div>

          <div class="qcard-head">
            <div class="qcard-deco qcard-deco-1"></div>
            <div class="qcard-deco qcard-deco-2"></div>
            <div class="qcard-head-inner">
              <div class="qcard-chip" :style="{ background: 'rgba(255,255,255,.18)', border: '1px solid rgba(255,255,255,.28)' }">
                <BookOpen :size="11" :stroke-width="2.5"/>
                <span>{{ typeMeta(quizType).label }}</span>
              </div>
              <span class="qcard-mission">{{ module.name }}</span>
              <div class="qcard-counter">
                <span class="qcard-counter-num">{{ currentIdx + 1 }}</span>
                <span class="qcard-counter-sep">/</span>
                <span class="qcard-counter-tot">{{ totalQ }}</span>
              </div>
            </div>
          </div>

          <div class="qcard-title-row">
            <div class="q-num-badge">#{{ currentIdx + 1 }}</div>
            <p class="qcard-step-title">{{ currentQ.question_text }}</p>
            <div v-if="isQuestionAnswered(currentQ)" class="q-answered-badge">
              <CheckCircle2 :size="12" :stroke-width="2.5"/>
              <span>Terjawab</span>
            </div>
          </div>

          <div class="question-item">
            <component
              :is="COMPONENT_MAP[quizType]"
              :question="currentQ"
              :model-value="answers[currentQ.id]"
              @update-answer="updateAnswer"
            />
          </div>

          <div class="qcard-hint" :class="{ 'qcard-hint--done': isQuestionAnswered(currentQ) }">
            <template v-if="isQuestionAnswered(currentQ)">
              <Sparkles :size="12" :stroke-width="2"/>
              <span>Jawaban dipilih! Klik Berikutnya</span>
            </template>
            <template v-else-if="quizType !== 'material'">
              <MousePointerClick :size="12" :stroke-width="2"/>
              <span>Pilih jawaban untuk melanjutkan</span>
            </template>
          </div>
        </div>
        </Transition>

        <!-- ══ DONE CARD ══ -->
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
              Selamat, kamu sudah menyelesaikan semua misi!
            </p>
          </div>
        </div>
        </Transition>

      </section>
    </div>

    <!-- ══ FOOTER ══ -->
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
              <span>Selesaikan Posttest</span>
            </template>
            <template v-else>
              <Loader2 :size="13" class="spin"/>
              <span>Menyimpan…</span>
            </template>
          </button>
        </template>

        <template v-if="phase === 'done'">
          <div class="f-space"></div>
          <button class="fbtn fbtn--mint" @click="goHome" :disabled="submitting">
            <template v-if="submitting">
              <Loader2 :size="13" class="spin"/>
              <span>Menyimpan…</span>
            </template>
            <template v-else>
              <Rocket :size="14" :stroke-width="2"/>
              <span>Kembali ke Beranda</span>
              <ArrowRight :size="14" :stroke-width="2.5"/>
            </template>
          </button>
        </template>

      </div>
    </footer>

  </div>
</template>

<style scoped>
/* ─── PALETTE ─── */
:root {
  --blue: #2563EB; --blue-mid: #1d4ed8; --blue-deep: #1e3a8a;
  --blue-soft: #BFDBFE; --blue-pale: #EFF6FF;
  --mint: #34D399; --mint-deep: #059669; --mint-soft: #D1FAE5;
  --yellow: #F59E0B; --yellow-deep: #92400E; --yellow-soft: #FEF3C7;
  --gray-2: #e2e8f0; --gray-3: #94a3b8; --text: #1e293b; --text-mid: #475569;
}
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
.root { min-height: 100dvh; display: flex; flex-direction: column; font-family: 'Nunito', sans-serif; position: relative; overflow-x: hidden; }

/* ─── BG ─── */
.bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
.bg-img { position:absolute; inset:0; background:url('/images/templates/background-posttest.png') center/cover no-repeat; }
.bg-tint { position:absolute; inset:0; background:#2563EB; opacity:.52; }
.blob { position:absolute; border-radius:50%; pointer-events:none; filter:blur(80px); }
.b1 { width:480px; height:480px; top:-140px; left:-100px; background:#1d4ed8; opacity:.35; animation:bDrift 20s ease-in-out infinite alternate; }
.b2 { width:380px; height:380px; bottom:-100px; right:-80px; background:#34D399; opacity:.22; animation:bDrift2 24s ease-in-out infinite alternate; }
.b3 { width:260px; height:260px; top:38%; left:52%; background:#BFDBFE; opacity:.18; animation:bDrift 28s ease-in-out 6s infinite alternate; }
@keyframes bDrift  { 0%{transform:translate(0,0)} 50%{transform:translate(30px,20px) scale(1.05)} 100%{transform:translate(-15px,35px)} }
@keyframes bDrift2 { 0%{transform:translate(0,0)} 50%{transform:translate(-28px,-18px) scale(1.06)} 100%{transform:translate(22px,-40px)} }
.sh { position:absolute; pointer-events:none; }
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
.bg-dots { position:absolute; inset:0; pointer-events:none; background-image:radial-gradient(circle,rgba(255,255,255,.09) 1px,transparent 1px); background-size:34px 34px; }

/* ─── TOPBAR ─── */
.topbar { position:relative; z-index:50; height:56px; flex-shrink:0; display:flex; align-items:center; padding:0 18px; background:rgba(255,255,255,.10); backdrop-filter:blur(18px); border-bottom:1px solid rgba(255,255,255,.16); }
.tbtn { display:flex; align-items:center; gap:6px; padding:7px 13px; background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.22); border-radius:10px; font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; color:#fff; cursor:pointer; transition:background .18s,transform .15s; flex-shrink:0; }
.tbtn:hover:not(:disabled) { background:rgba(255,255,255,.22); transform:translateY(-1px); }
.tbtn:disabled { opacity:.4; cursor:not-allowed; }
.tbtn-sq { padding:7px 10px; }
.tbtn--on { background:#2563EB; border-color:#BFDBFE; }
.brand { position:absolute; left:50%; transform:translateX(-50%); display:flex; align-items:center; gap:8px; pointer-events:none; z-index:2; transition:opacity .34s,transform .34s; }
.brand--hide { opacity:0; transform:translateX(-50%) scale(.88); }
.brand-dot { width:28px; height:28px; border-radius:8px; background:#2563EB; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(37,99,235,.5); }
.brand-name { font-family:'Righteous',cursive; font-size:18px; color:#fff; white-space:nowrap; }
.timer { position:absolute; left:50%; transform:translateX(-50%); display:flex; flex-direction:column; align-items:center; gap:4px; pointer-events:none; z-index:2; min-width:138px; }
.timer-row { display:flex; align-items:center; gap:6px; color:#fff; }
.timer-val { font-family:'Righteous',cursive; font-size:21px; letter-spacing:.5px; }
.timer--warn .timer-val { color:#fca5a5; animation:tWarn 1s ease-in-out infinite; }
@keyframes tWarn { 0%,100%{opacity:1} 50%{opacity:.5} }
.timer-track { width:100%; height:4px; background:rgba(255,255,255,.2); border-radius:99px; overflow:hidden; }
.timer-fill { height:100%; background:#BFDBFE; border-radius:99px; transition:width .9s linear; }
.fill--warn { background:#f87171; }
.t-timer-enter-active { transition:opacity .4s ease .25s,transform .42s cubic-bezier(.34,1.56,.64,1) .25s; }
.t-timer-leave-active { transition:opacity .18s ease; }
.t-timer-enter-from   { opacity:0; transform:translateX(-50%) translateY(6px) scale(.88); }
.t-timer-leave-to     { opacity:0; }
.topbar-r { display:flex; align-items:center; gap:8px; margin-left:auto; z-index:3; }

/* ─── BODY GRID ─── */
.body { position:relative; z-index:10; flex:1; display:grid; grid-template-columns:264px 1fr; gap:20px; max-width:1080px; width:100%; margin:0 auto; padding:22px 18px 18px; align-items:start; opacity:0; transition:opacity .45s; }
.body--on { opacity:1; }

/* ─── SIDEBAR ─── */
.sidebar { display:flex; flex-direction:column; opacity:0; transform:translateX(-16px); transition:opacity .5s,transform .5s cubic-bezier(.34,1.56,.64,1); user-select:none; cursor:pointer; min-width:0; }
.sidebar--on { opacity:1; transform:none; }
.sb-info { margin-bottom:18px; min-width:0; overflow:hidden; width:100%; }
.sb-chip { display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.38); border-radius:999px; padding:4px 13px; font-size:11px; font-weight:900; color:#fff; backdrop-filter:blur(6px); margin-bottom:10px; max-width:100%; }
.sb-title { font-family:'Righteous',cursive; font-size:clamp(16px,2vw,22px); color:#fff; line-height:1.25; margin-bottom:5px; text-shadow:0 1px 10px rgba(0,0,0,.35); word-break:break-word; overflow-wrap:anywhere; }
.sb-sub { font-size:12.5px; font-weight:800; color:rgba(255,255,255,.9); line-height:1.55; margin-bottom:6px; text-shadow:0 1px 6px rgba(0,0,0,.28); word-break:break-word; overflow-wrap:anywhere; }
.sb-soal { font-size:11px; font-weight:900; color:rgba(255,255,255,.65); letter-spacing:.3px; margin-bottom:16px; }
.prog { margin-bottom:2px; min-width:0; }
.prog-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:7px; gap:8px; }
.prog-lbl { font-size:10px; font-weight:900; color:rgba(255,255,255,.85); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap; flex-shrink:0; }
.prog-count { font-family:'Righteous',cursive; font-size:14px; color:#fff; white-space:nowrap; flex-shrink:0; }
.prog-count b { font-size:16px; }
.prog-track { height:8px; background:rgba(255,255,255,.2); border-radius:99px; overflow:hidden; min-width:0; }
.prog-fill { height:100%; background:#34D399; border-radius:99px; position:relative; overflow:hidden; transition:width .5s cubic-bezier(.34,1.56,.64,1); }
.prog-shine { position:absolute; inset:0; background:linear-gradient(90deg,transparent,rgba(255,255,255,.38),transparent); animation:shine 2.2s ease-in-out infinite; }
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }
.sb-pills { display:flex; flex-direction:column; gap:8px; }
.sb-pill { display:flex; align-items:center; gap:8px; background:rgba(255,255,255,.16); border:1px solid rgba(255,255,255,.28); border-radius:10px; padding:9px 13px; font-size:12.5px; font-weight:800; color:#fff; backdrop-filter:blur(6px); }

/* Mascot */
.mascot-wrap { position:relative; padding-left:4px; }
.bubble { position:relative; background:#fff; border:2px solid #BFDBFE; border-radius:16px; padding:9px 14px; min-width:146px; max-width:210px; box-shadow:0 5px 18px rgba(37,99,235,.13); margin-bottom:6px; animation:bblFloat 3.5s ease-in-out infinite; }
.bubble span { font-size:12.5px; font-weight:800; color:#1e3a8a; display:block; }
.bubble--intro { border-color:#FCD34D; box-shadow:0 5px 18px rgba(245,158,11,.18); }
.bubble--intro span { color:#92400e; }
.bubble--intro .bbl-o { border-top-color:#FCD34D; }
.bubble--done { border-color:#6EE7B7; box-shadow:0 5px 18px rgba(52,211,153,.22); }
.bubble--done span { color:#065f46; }
.bubble--done .bbl-o { border-top-color:#6EE7B7; }
.bbl-o,.bbl-i { position:absolute; width:0; height:0; font-style:normal; }
.bbl-o { bottom:-14px; left:15px; border-left:10px solid transparent; border-right:6px solid transparent; border-top:13px solid #BFDBFE; }
.bbl-i { bottom:-10px; left:16px; border-left:8px solid transparent; border-right:5px solid transparent; border-top:11px solid #fff; }
@keyframes bblFloat { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-5px)} }
.bbl-enter-active { transition:opacity .26s,transform .3s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition:opacity .16s; }
.bbl-enter-from   { opacity:0; transform:translateY(8px) scale(.92); }
.bbl-leave-to     { opacity:0; }
.mascot-frame { position:relative; display:inline-block; }
.mascot { position:relative; z-index:2; width:clamp(138px,14vw,192px); height:auto; display:block; filter:drop-shadow(0 10px 22px rgba(0,0,0,.22)); animation:mBob 3.5s ease-in-out infinite; transform-origin:bottom center; }
.mascot-shadow { position:absolute; bottom:0; left:50%; transform:translateX(-50%); width:65%; height:13px; background:radial-gradient(ellipse at center,rgba(0,0,0,.28)0%,transparent 70%); border-radius:50%; z-index:1; }
@keyframes mBob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-8px) rotate(.5deg)} 70%{transform:translateY(-4px) rotate(-.3deg)} }
.pose-swap-enter-active { transition:opacity .4s ease,transform .45s cubic-bezier(.34,1.56,.64,1); }
.pose-swap-leave-active { transition:opacity .2s ease; position:absolute; bottom:0; }
.pose-swap-enter-from   { opacity:0; transform:translateY(18px) scale(.88); }
.pose-swap-leave-to     { opacity:0; }

/* ─── MAIN ─── */
.main { opacity:0; transform:translateY(16px); transition:opacity .5s .1s,transform .5s .1s cubic-bezier(.34,1.56,.64,1); }
.main--on { opacity:1; transform:none; }
.pg-enter-active { transition:opacity .36s,transform .4s cubic-bezier(.34,1.56,.64,1); }
.pg-leave-active { transition:opacity .18s,transform .18s; position:absolute; width:100%; }
.pg-enter-from   { opacity:0; transform:translateY(16px) scale(.98); }
.pg-leave-to     { opacity:0; transform:translateY(-10px) scale(.98); }

/* ─── INTRO CARD ─── */
.icard { background:#FDFCFB; border-radius:20px; overflow:hidden; border:1.5px solid var(--gray-2); box-shadow:0 4px 0 var(--blue-soft),0 10px 32px rgba(59,130,246,.10); }
.icard-head { background:linear-gradient(135deg,#3B82F6 0%,#2563EB 100%); position:relative; overflow:hidden; }
.icard-hdeco { position:absolute; border-radius:50%; background:rgba(255,255,255,.08); pointer-events:none; }
.icard-hdeco-1 { width:200px; height:200px; top:-70px; right:-40px; }
.icard-hdeco-2 { width:100px; height:100px; bottom:-42px; left:18px; }
.icard-head-inner { position:relative; z-index:1; padding:22px 22px 24px; }
.icard-eyebrow { display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,.20); border:1px solid rgba(255,255,255,.30); border-radius:999px; padding:4px 12px; font-size:11px; font-weight:900; color:#fff; margin-bottom:11px; }
.icard-title { font-family:'Righteous',cursive; font-size:clamp(18px,2.5vw,25px); color:#fff; line-height:1.2; margin-bottom:8px; text-shadow:0 2px 10px rgba(0,0,0,.15); word-break:break-word; }
.icard-sub { font-size:13px; font-weight:700; color:rgba(255,255,255,.88); line-height:1.6; max-width:460px; word-break:break-word; }
.icard-stats { display:grid; grid-template-columns:repeat(3,1fr); border-bottom:1.5px solid var(--gray-2); }
.istat { display:flex; flex-direction:column; align-items:center; gap:5px; padding:20px 12px 18px; border-right:1.5px solid var(--gray-2); }
.istat:last-child { border-right:none; }
.istat--red    { background:#FFF7F7; }
.istat--red .istat-icon    { background:#F87171; box-shadow:0 4px 14px rgba(248,113,113,.30); }
.istat--red .istat-val     { color:#C53030; }
.istat--red .istat-lbl     { color:#E53E3E; }
.istat--yellow { background:#FFFDF0; }
.istat--yellow .istat-icon { background:#FBBF24; box-shadow:0 4px 14px rgba(251,191,36,.30); }
.istat--yellow .istat-val  { color:#92400E; }
.istat--yellow .istat-lbl  { color:#D97706; }
.istat--blue   { background:#F0FDF9; }
.istat--blue .istat-icon   { background:#34D399; box-shadow:0 4px 14px rgba(52,211,153,.30); }
.istat--blue .istat-val    { color:#065F46; }
.istat--blue .istat-lbl    { color:#059669; }
.istat-icon { width:46px; height:46px; border-radius:13px; display:flex; align-items:center; justify-content:center; color:#fff; margin-bottom:2px; }
.istat-val { font-family:'Righteous',cursive; font-size:24px; line-height:1; }
.istat-lbl { font-size:11px; font-weight:900; text-transform:uppercase; letter-spacing:.5px; }
.icard-body { padding:18px 20px 22px; }
.icard-instr-hd { display:flex; align-items:center; gap:7px; font-family:'Righteous',cursive; font-size:13.5px; color:var(--blue-mid); margin-bottom:13px; }
.icard-instr-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.instr-row { display:flex; align-items:center; gap:9px; border-radius:12px; padding:11px 13px; font-size:12.5px; font-weight:800; color:var(--text); border:1.5px solid transparent; }
.instr-row--red    { background:#FFF7F7; border-color:#FECACA; }
.instr-row--red .instr-num { background:#F87171; color:#fff; }
.instr-row--red .instr-ico { color:#F87171; }
.instr-row--yellow { background:#FFFDF0; border-color:#FDE68A; }
.instr-row--yellow .instr-num { background:#FBBF24; color:#fff; }
.instr-row--yellow .instr-ico { color:#D97706; }
.instr-row--green  { background:#F0FDF9; border-color:#A7F3D0; }
.instr-row--green .instr-num { background:#34D399; color:#fff; }
.instr-row--green .instr-ico { color:#059669; }
.instr-row--blue   { background:#EFF6FF; border-color:#BFDBFE; }
.instr-row--blue .instr-num { background:#3B82F6; color:#fff; }
.instr-row--blue .instr-ico { color:#3B82F6; }
.instr-num { font-family:'Righteous',cursive; font-size:11px; border-radius:6px; padding:3px 7px; flex-shrink:0; line-height:1.4; }
.instr-ico { flex-shrink:0; }
.instr-txt { flex:1; line-height:1.45; word-break:break-word; }

/* ─── QUIZ CARD ─── */
.qcard { background:#FDFCFB; border-radius:20px; border:1.5px solid var(--gray-2); overflow:hidden; box-shadow:0 4px 0 #BFDBFE,0 10px 32px rgba(37,99,235,.10); }
.qcard--shake { animation:qShake .5s ease; }
@keyframes qShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 40%{transform:translateX(5px)} 60%{transform:translateX(-3px)} 80%{transform:translateX(3px)} }
.qcard-bar { height:4px; }
.qcard-head { background:linear-gradient(135deg,#3B82F6 0%,#2563EB 100%); position:relative; overflow:hidden; }
.qcard-deco { position:absolute; border-radius:50%; background:rgba(255,255,255,.08); pointer-events:none; }
.qcard-deco-1 { width:160px; height:160px; top:-55px; right:-35px; }
.qcard-deco-2 { width:80px; height:80px; bottom:-30px; left:18px; }
.qcard-head-inner { position:relative; z-index:1; display:flex; align-items:center; justify-content:space-between; gap:8px; padding:12px 18px; flex-wrap:wrap; }
.qcard-chip { display:inline-flex; align-items:center; gap:5px; border-radius:999px; padding:4px 10px; font-size:10.5px; font-weight:900; color:#fff; white-space:nowrap; flex-shrink:0; }
.qcard-mission { flex:1; min-width:0; text-align:center; font-family:'Righteous',cursive; font-size:13px; color:rgba(255,255,255,.85); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; padding:0 8px; }
.qcard-counter { display:flex; align-items:baseline; gap:2px; flex-shrink:0; }
.qcard-counter-num { font-family:'Righteous',cursive; font-size:20px; color:#fff; line-height:1; }
.qcard-counter-sep { font-size:13px; color:rgba(255,255,255,.5); margin:0 1px; }
.qcard-counter-tot { font-family:'Righteous',cursive; font-size:13px; color:rgba(255,255,255,.6); }
.qcard-title-row { padding:18px 20px; display:flex; align-items:flex-start; gap:11px; background:#EFF6FF; }
.q-num-badge { display:inline-flex; align-items:center; justify-content:center; min-width:34px; height:34px; border-radius:10px; background:#3B82F6; color:#fff; font-family:'Righteous',cursive; font-size:13px; flex-shrink:0; box-shadow:0 3px 8px rgba(59,130,246,.28); margin-top:1px; }
.qcard-step-title { flex:1; font-size:15px; font-weight:800; color:#1E293B; line-height:1.65; word-break:break-word; overflow-wrap:anywhere; min-width:0; }
.q-answered-badge { display:inline-flex; align-items:center; gap:4px; background:#ECFDF5; border:1.5px solid #6EE7B7; border-radius:999px; padding:3px 9px; font-size:10px; font-weight:900; color:#059669; flex-shrink:0; margin-top:3px; white-space:nowrap; }
.question-item { padding:16px 20px; }
.qcard-hint { display:flex; align-items:center; justify-content:center; gap:6px; padding:10px 20px 14px; font-size:11.5px; font-weight:800; color:#94a3b8; }
.qcard-hint--done { color:#059669; }

/* ─── DONE CARD ─── */
.dcard { background:#fff; border-radius:20px; overflow:hidden; border:1.5px solid var(--gray-2); position:relative; box-shadow:0 4px 0 #6EE7B7,0 10px 32px rgba(52,211,153,.15); }
.dcard-confetti { position:absolute; inset:0; pointer-events:none; overflow:hidden; }
.conf { position:absolute; border-radius:3px; animation:confFall linear infinite; }
.conf-1{width:8px;height:8px;background:#F59E0B;top:-10px;left:8%;animation-duration:3.5s;animation-delay:.1s;transform:rotate(20deg);}
.conf-2{width:6px;height:6px;background:#2563EB;top:-10px;left:22%;animation-duration:4s;animation-delay:.6s;transform:rotate(-15deg);}
.conf-3{width:7px;height:7px;background:#EF4444;top:-10px;left:38%;animation-duration:3.2s;animation-delay:.3s;transform:rotate(35deg);}
.conf-4{width:5px;height:5px;background:#34D399;top:-10px;left:55%;animation-duration:4.5s;animation-delay:.9s;transform:rotate(-30deg);}
.conf-5{width:8px;height:8px;background:#A78BFA;top:-10px;left:70%;animation-duration:3.8s;animation-delay:.5s;transform:rotate(10deg);}
.conf-6{width:5px;height:5px;background:#F59E0B;top:-10px;left:82%;animation-duration:4.2s;animation-delay:1.1s;transform:rotate(-25deg);}
.conf-7{width:6px;height:6px;background:#EF4444;top:-10px;left:91%;animation-duration:3.6s;animation-delay:.7s;transform:rotate(45deg);}
.conf-8{width:7px;height:7px;background:#2563EB;top:-10px;left:48%;animation-duration:3.9s;animation-delay:1.3s;transform:rotate(-10deg);}
@keyframes confFall { 0%{transform:translateY(0) rotate(0);opacity:1} 100%{transform:translateY(320px) rotate(360deg);opacity:0} }
.dcard-inner { display:flex; flex-direction:column; align-items:center; text-align:center; padding:42px 28px 38px; gap:12px; }
.trophy-wrap { position:relative; width:100px; height:100px; display:flex; align-items:center; justify-content:center; }
.trophy-ring { width:88px; height:88px; border-radius:50%; background:var(--mint-soft); border:2.5px solid var(--mint); display:flex; align-items:center; justify-content:center; box-shadow:0 5px 22px rgba(52,211,153,.26); animation:tPop .5s cubic-bezier(.34,1.56,.64,1) both .08s; }
@keyframes tPop { from{transform:scale(0) rotate(-15deg);opacity:0} to{transform:scale(1) rotate(0);opacity:1} }
.sp { position:absolute; animation:spFloat 2.2s ease-in-out infinite; }
.sp1{top:-2px;right:2px;} .sp2{bottom:2px;left:-2px;animation-delay:.5s} .sp3{top:8px;left:-4px;animation-delay:1.1s}
@keyframes spFloat { 0%,100%{transform:translateY(0) rotate(0)} 50%{transform:translateY(-8px) rotate(14deg)} }
.done-ttl { font-family:'Righteous',cursive; font-size:clamp(20px,3vw,26px); color:var(--blue-deep); }
.done-party { animation:spFloat 1.8s ease-in-out infinite; }
.done-sub { font-size:13.5px; font-weight:700; color:var(--text-mid); line-height:1.7; }

/* ─── FOOTER ─── */
.footer { position:relative; z-index:50; background:rgba(255,255,255,.10); backdrop-filter:blur(18px); border-top:1px solid rgba(255,255,255,.16); padding:11px 0 8px; flex-shrink:0; }
.footer-inner { display:flex; align-items:center; gap:10px; max-width:1080px; margin:0 auto; padding:0 20px; }
.f-space { flex:1; }
.f-pos { font-family:'Righteous',cursive; font-size:13px; color:#fff; flex:1; text-align:center; }
.fbtn { display:inline-flex; align-items:center; gap:6px; height:40px; padding:0 18px; border:none; border-radius:10px; font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; cursor:pointer; flex-shrink:0; transition:transform .15s cubic-bezier(.34,1.56,.64,1),box-shadow .15s; }
.fbtn span { white-space:normal; }
.fbtn--ghost { background:rgba(255,255,255,.14); color:#fff; border:1px solid rgba(255,255,255,.25); }
.fbtn--ghost:hover:not(:disabled) { background:rgba(255,255,255,.22); transform:translateY(-1px); }
.fbtn--ghost:disabled { opacity:.4; cursor:not-allowed; }
.fbtn--blue { background:#2563EB; color:#fff; box-shadow:0 3px 12px rgba(37,99,235,.4); }
.fbtn--blue:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 16px rgba(37,99,235,.5); }
.fbtn--blue:disabled { background:rgba(255,255,255,.15)!important; color:rgba(255,255,255,.4)!important; box-shadow:none!important; cursor:not-allowed; }
.fbtn--mint { background:#00c54cd7; color:#fff; box-shadow:0 3px 12px rgba(52,211,153,.4); }
.fbtn--mint:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 16px rgba(52,211,153,.5); }
.fbtn--mint:disabled { opacity:.5; cursor:not-allowed; }
.fbtn--yellow { background:#2563EB; color:#fff; box-shadow:0 3px 12px rgba(37,99,235,.4); }
.fbtn--yellow:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 16px rgba(37,99,235,.5); }
@keyframes spin { to{transform:rotate(360deg);} }
.spin { animation:spin .8s linear infinite; }

/* ─── MOBILE ─── */
@media (max-width: 820px) {
  .b1{width:300px;height:300px;} .b2{width:240px;height:240px;} .b3{width:180px;height:180px;}
  .c1{width:100px;height:100px;} .c2{display:none;} .r1{width:200px;height:200px;}
  .topbar{height:52px;padding:0 13px;} .brand-name{font-size:16px;} .brand-dot{width:25px;height:25px;} .timer-val{font-size:18px;}
  .body{grid-template-columns:1fr;gap:0;padding:0;max-width:100%;}
  .sidebar{opacity:1!important;transform:none!important;flex-direction:column;gap:0;cursor:default;padding:13px 15px 12px;background:rgba(29,78,216,.76);backdrop-filter:blur(18px);border-bottom:1px solid rgba(191,219,254,.22);overflow:hidden;}
  .sb-info{margin-bottom:0;width:100%;}
  .sb-chip{margin-bottom:6px;font-size:10px;} .sb-title{font-size:15px;margin-bottom:3px;} .sb-sub{font-size:11.5px;margin-bottom:6px;white-space:normal;}
  .prog{margin-bottom:0;width:100%;} .prog-meta{margin-bottom:4px;}
  .sb-pills{flex-direction:row;flex-wrap:wrap;gap:7px;} .sb-pill{padding:6px 10px;font-size:11.5px;border-radius:8px;}
  .mascot-wrap{display:none;}
  .main{transform:none;opacity:1;}
  .icard,.qcard,.dcard{border-radius:0;border-left:none;border-right:none;}
  .icard-head-inner{padding:16px 15px 18px;} .icard-title{font-size:18px;}
  .icard-stats{grid-template-columns:repeat(3,1fr);} .istat{padding:15px 8px 13px;} .istat-icon{width:38px;height:38px;border-radius:10px;} .istat-val{font-size:20px;}
  .icard-body{padding:14px 15px 18px;} .icard-instr-grid{grid-template-columns:1fr;}
  .qcard-head-inner{padding:10px 14px;flex-wrap:wrap;gap:6px;}
  .qcard-title-row{padding:14px 14px;}
  .qcard-step-title{font-size:14px;}
  .question-item{padding:12px 14px;}
  .footer-inner{padding:0 15px;} .fbtn{height:40px;}
}
@media (max-width: 480px) {
  .topbar{height:48px;padding:0 11px;} .tbtn-lbl{display:none;} .tbtn{padding:7px 9px;}
  .timer-val{font-size:16px;} .timer{min-width:105px;}
  .sidebar{padding:11px 12px 10px;} .sb-title{font-size:13px;} .sb-sub{font-size:11px;}
  .icard-head-inner{padding:14px 13px 16px;} .istat{padding:12px 6px 10px;}
  .istat-icon{width:34px;height:34px;border-radius:9px;} .istat-val{font-size:18px;}
  .icard-body{padding:12px 13px 16px;}
  .qcard-step-title{font-size:13.5px;} .q-answered-badge{display:none;}
  .dcard-inner{padding:32px 16px 28px;}
  .footer-inner{padding:0 12px;gap:7px;} .fbtn{height:38px;padding:0 13px;font-size:12px;} .f-pos{font-size:12px;}
}
</style>
