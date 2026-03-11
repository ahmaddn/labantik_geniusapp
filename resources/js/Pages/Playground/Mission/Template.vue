<!-- ini te-->
<script setup>
import { ref, reactive, computed, onMounted, onUnmounted, watch } from 'vue'
import {
  ArrowLeft, ArrowRight, Home,
  Zap, Clock, Music2, VolumeX,
  CheckCircle2, BookOpen, Star,
  Trophy, Sparkles, ListChecks,
  Award, Rocket, Target, Flame,
  Flag, PartyPopper, Loader2,
  Check, Hash, MousePointerClick,
  Eye, CircleCheck, Timer, ChevronRight,
  ClipboardList, LayoutGrid, ToggleLeft,
  FileSearch, GripHorizontal,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

import True_false      from '@/Components/Quiz/True_false.vue'
import Multiple_choice from '@/Components/Quiz/Multiple_choice.vue'
import Case_study      from '@/Components/Quiz/Case_study.vue'
import Materials       from '@/Components/Quiz/Materials.vue'
import Drag_drop       from '@/Components/Quiz/Drag_drop.vue'
import { useMusic } from '@/Composable/useMusic'
// ── Component / type maps ──────────────────────────────────────
const COMPONENT_MAP = {
  multiple_choices: Multiple_choice,
  true_false:       True_false,
  case_study:       Case_study,
  materials:        Materials,
  drag_drop:        Drag_drop,

}
const { musicOn, handleVisibility, initAutoMusic, toggleMusic, destroyAudio } = useMusic()
const brandMoved = ref(false)
const TYPE_META = {
  multiple_choices: { label: 'Pilihan Ganda',           color: '#3b82f6', bg: '#dbeafe' },
  true_false:       { label: 'Pilih Gambar Yang Benar',  color: '#8b5cf6', bg: '#ede9fe' },
  case_study:       { label: 'Studi Kasus',              color: '#0891b2', bg: '#cffafe' },
  drag_drop:        { label: 'Seret & Letakkan',         color: '#f59e0b', bg: '#fef3c7' },
  materials:        { label: 'Materi',                   color: '#10b981', bg: '#dcfce7' },
}
const typeMeta = (t) => TYPE_META[t] || TYPE_META.materials

// ── Props ──────────────────────────────────────────────────────
const props = defineProps({
  mission: { type: Object, required: true },
  user:    { type: Object, default: () => ({ name: 'Siswa' }) },
  module:  { type: Object, default: () => ({ id: null, name: 'Module', description: '' }) },
   backsound: { type: String, default: null },
   background: { type: String, default: null },
})

// ── Steps — 1 question per page ───────────────────────────────
// Setiap question dalam setiap quiz menjadi 1 step tersendiri.
// Material tetap 1 step penuh (tidak dipecah per question).
const steps = computed(() => {
  const result = []
  props.mission.quizzes.forEach((quiz, quizIdx) => {
    const isMaterial = quiz.type === 'materials'
    const isDragDrop = quiz.type === 'drag_drop'
    const questions  = quiz.questions || []

    if (isMaterial) {
      // Material: 1 step, tampilkan question pertama sebagai konten
      result.push({
        quizIndex:     quizIdx,
        quiz,
        question:      questions[0] ?? null,
        questionIndex: 0,
        totalInQuiz:   Math.max(questions.length, 1),
        isMaterial:    true,
        isDragDrop:    false,
      })
    } else if (questions.length === 0) {
      // Quiz kosong → 1 step placeholder
      result.push({
        quizIndex:     quizIdx,
        quiz,
        question:      null,
        questionIndex: 0,
        totalInQuiz:   0,
        isMaterial:    false,
        isDragDrop,
      })
    } else {
      // ✅ Tiap question = 1 step
      questions.forEach((question, qIdx) => {
        result.push({
          quizIndex:     quizIdx,
          quiz,
          question,
          questionIndex: qIdx,
          totalInQuiz:   questions.length,
          isMaterial:    false,
          isDragDrop,
        })
      })
    }
  })
  return result
})

// ── State ──────────────────────────────────────────────────────
const currentStep  = ref(0)
const answers      = reactive({})
const isSubmitting = ref(false)
const ready        = ref(false)
const shakeActive  = ref(false)

// Music

const audioRef = ref(null)

const step    = computed(() => steps.value[currentStep.value])
const isFirst = computed(() => currentStep.value === 0)
const isLast  = computed(() => currentStep.value === steps.value.length - 1)

// ── Timer ─────────────────────────────────────────────────────
// sessionStorage keys — scoped per mission agar tidak tabrakan antar misi
const SS_TIME_KEY    = `geniuss_timer_time_${props.mission.id}`
const SS_TIMEOUT_KEY = `geniuss_timer_out_${props.mission.id}`

// Helpers baca/tulis sessionStorage
function ssGetMap(key) {
  try { return JSON.parse(sessionStorage.getItem(key) || '{}') } catch { return {} }
}
function ssSetMap(key, val) {
  try { sessionStorage.setItem(key, JSON.stringify(val)) } catch {}
}

// Load state dari sessionStorage (bertahan saat reload)
const _savedTime    = ssGetMap(SS_TIME_KEY)    // { [quizId]: secondsRemaining }
const _savedTimeout = ssGetMap(SS_TIMEOUT_KEY) // { [quizId]: true }

const timeRemaining   = ref(0)
const timedOutQuizzes = ref(new Set(Object.keys(_savedTimeout).filter(k => _savedTimeout[k])))
let   timerInt        = null
let   activeQuizId    = null

const timerDisplay = computed(() => {
  const m = String(Math.floor(timeRemaining.value / 60)).padStart(2, '0')
  const s = String(timeRemaining.value % 60).padStart(2, '0')
  return `${m}:${s}`
})
const timerWarning = computed(() => timeRemaining.value > 0 && timeRemaining.value <= 60)
const showTimer    = computed(() => {
  const s = step.value
  return s && !s.isMaterial && s.quiz?.time_limit > 0
})

function saveTimerState(quizId, seconds) {
  const map = ssGetMap(SS_TIME_KEY)
  map[quizId] = seconds
  ssSetMap(SS_TIME_KEY, map)
}

function markTimeout(quizId) {
  const map = ssGetMap(SS_TIMEOUT_KEY)
  map[quizId] = true
  ssSetMap(SS_TIMEOUT_KEY, map)
  timedOutQuizzes.value = new Set([...timedOutQuizzes.value, quizId])
}

function pauseActiveTimer() {
  if (activeQuizId !== null) {
    saveTimerState(activeQuizId, timeRemaining.value)
  }
  clearInterval(timerInt)
  timerInt = null
}

function startQuizTimer(quiz) {
  pauseActiveTimer()

  if (!quiz || !quiz.time_limit || quiz.time_limit <= 0) {
    timeRemaining.value = 0
    activeQuizId = null
    return
  }
  brandMoved.value = true

  // Sudah timeout → tampilkan 0, tidak perlu interval
  if (timedOutQuizzes.value.has(quiz.id)) {
    timeRemaining.value = 0
    activeQuizId = quiz.id
    return
  }

  // Ambil sisa waktu dari sessionStorage, atau mulai dari awal
  const saved = ssGetMap(SS_TIME_KEY)
  timeRemaining.value = saved[quiz.id] !== undefined ? saved[quiz.id] : quiz.time_limit
  activeQuizId = quiz.id

  timerInt = setInterval(() => {
    if (timeRemaining.value <= 0) {
      clearInterval(timerInt)
      timerInt = null
      markTimeout(quiz.id)
      saveTimerState(quiz.id, 0)
      timeRemaining.value = 0
      return
    }
    timeRemaining.value--
    // Simpan ke sessionStorage tiap detik supaya reload tetap lanjut
    saveTimerState(quiz.id, timeRemaining.value)
  }, 1000)
}

// Ganti timer saat pindah ke quiz yang berbeda
watch(
  () => step.value?.quiz?.id,
  (newId, oldId) => {
    if (newId !== oldId) {
      startQuizTimer(step.value?.quiz ?? null)
    }
  }
)

// ── Answer check ───────────────────────────────────────────────
const isQuestionAnswered = (question, quizType) => {
  const ans = answers[question.id]
  if (quizType === 'drag_drop') {
    if (!ans || typeof ans !== 'object' || Array.isArray(ans)) return false
    const total = question.drag_drop_items?.length ?? 0
    return total === 0 || Object.keys(ans).length >= total
  }
  if (ans === undefined || ans === null) return false
  if (Array.isArray(ans)) return ans.length > 0
  return ans !== ''
}
const isStepAnswered = (s) => {
  if (!s || s.isMaterial) return true
  if (!s.question) return true
  // Quiz yang sudah timeout → dianggap selesai (bisa next/submit)
  if (timedOutQuizzes.value.has(s.quiz?.id)) return true
  return isQuestionAnswered(s.question, s.quiz.type)
}

const canGoNext = computed(() => isStepAnswered(step.value))
const allStepsAnswered = computed(() =>
  steps.value.filter(s => !s.isMaterial).every(s => isStepAnswered(s))
)

// ── Progress ───────────────────────────────────────────────────
// Hitung berdasarkan jumlah question steps yang sudah dijawab
const totalQuizSteps    = computed(() => steps.value.filter(s => !s.isMaterial).length)
const answeredQuizSteps = computed(() => steps.value.filter(s => !s.isMaterial && isStepAnswered(s)).length)
const progressPct       = computed(() =>
  totalQuizSteps.value === 0 ? 100 : Math.round((answeredQuizSteps.value / totalQuizSteps.value) * 100)
)

// ── Mascot ────────────────────────────────────────────────────
const DEFAULT_MASCOT = '/images/templates/pose_nunjuk.png'

const mascotUrl = computed(() => {
  const questions = step.value?.quiz?.questions ?? []
  for (const q of questions) {
    if (q.mascot?.image) {
      const img = q.mascot.image
      if (img.startsWith('http') || img.startsWith('/')) return img
      return `${window.location.origin}/storage/${img}`
    }
  }
  for (const quiz of props.mission.quizzes) {
    for (const q of quiz.questions ?? []) {
      if (q.mascot?.image) {
        const img = q.mascot.image
        if (img.startsWith('http') || img.startsWith('/')) return img
        return `${window.location.origin}/storage/${img}`
      }
    }
  }
  return DEFAULT_MASCOT
})

// ── Sidebar speech bubble ──────────────────────────────────────
const BUBBLES = [
  'Ayo semangat! 💪', 'Baca dengan teliti 👀',
  'Kamu pasti bisa! 🔥', 'Pikirkan baik-baik 🤔',
  'Hampir selesai! ✨', 'Fokus ya! 🎯',
]
const bubbleIdx     = ref(0)
const bubbleVisible = ref(true)
let   bubbleTimer   = null

const rotateBubble = () => {
  bubbleVisible.value = false
  setTimeout(() => {
    bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.length
    bubbleVisible.value = true
  }, 300)
}

// ── Music ─────────────────────────────────────────────────────

// ── Navigation ─────────────────────────────────────────────────
const updateAnswer = (payload) => {
  if (payload?.questionId !== undefined) answers[payload.questionId] = payload.value
}

const goNext = () => {
  if (!canGoNext.value) {
    shakeActive.value = true
    setTimeout(() => (shakeActive.value = false), 600)
    return
  }
  if (!isLast.value) currentStep.value++
  else openConfirm()
}

const goPrev   = () => { if (!isFirst.value) currentStep.value-- }
const goToStep = (idx) => { currentStep.value = idx }

const goBack = () => {
  router.visit(route('playground.missions.index', props.module.id), {
    replace: true,
    preserveState: false,
    preserveScroll: false,
  })
}

const goHome = () => router.visit(route('playground.index'))

const stepStatus = (idx) => {
  const s = steps.value[idx]
  if (s.isMaterial) return 'material'
  if (idx === currentStep.value) return 'active'
  return isStepAnswered(s) ? 'done' : 'pending'
}

// ── Confirm modal ──────────────────────────────────────────────
const showConfirm  = ref(false)
const openConfirm  = () => { showConfirm.value = true }
const closeConfirm = () => { showConfirm.value = false }

// ── Leave confirm ─────────────────────────────────────────────
const showLeaveConfirm = ref(false)
const hasAnswers = computed(() => Object.keys(answers).length > 0)

const tryGoBack = () => {
  if (hasAnswers.value) {
    showLeaveConfirm.value = true
  } else {
    goBack()
  }
}
const confirmLeave = () => { showLeaveConfirm.value = false; goBack() }
const cancelLeave  = () => { showLeaveConfirm.value = false }

// ── Submit ─────────────────────────────────────────────────────
const submit = async () => {
  closeConfirm()
  isSubmitting.value = true
  try {
    const res = await fetch(route('playground.missions.submit', props.mission.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({
        answers,
        quiz_ids: props.mission.quizzes
          .filter(q => q.type !== 'materials')
          .map(q => q.id),
      }),
    })
    const data = await res.json()
    if (data.success) {
      // Bersihkan timer state supaya sesi berikutnya mulai fresh
      try {
        sessionStorage.removeItem(SS_TIME_KEY)
        sessionStorage.removeItem(SS_TIMEOUT_KEY)
      } catch {}
      router.visit(route('playground.missions.result', props.mission.id))
    }
    else alert('Gagal menyimpan jawaban: ' + (data.error || 'Unknown error'))
  } catch (e) {
    console.error(e); alert('Terjadi kesalahan saat menyimpan jawaban')
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
    setTimeout(() => (ready.value = true), 80)
    bubbleTimer = setInterval(rotateBubble, 3500)
    document.addEventListener('visibilitychange', handleVisibility)
    setTimeout(() => initAutoMusic(props.backsound), 100)

    // Mulai timer untuk quiz pertama
    if (step.value?.quiz) {
      startQuizTimer(step.value.quiz)
    }
})
onUnmounted(() => {
  clearInterval(bubbleTimer)
  clearInterval(timerInt)
  document.removeEventListener('visibilitychange', handleVisibility)
  destroyAudio()
})

const TYPE_ICON_MAP = {
  drag_drop:        GripHorizontal,
  materials:        BookOpen,
  multiple_choices: ClipboardList,
  true_false:       ToggleLeft,
  case_study:       FileSearch,
}
const typeIcon = (t) => TYPE_ICON_MAP[t] || LayoutGrid
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
      <div class="bg-img" :style="{ backgroundImage: `url(${props.background})` }"></div>
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
      <button class="tbtn" @click="tryGoBack" :disabled="isSubmitting">
        <ArrowLeft :size="16" :stroke-width="2.5"/>
        <span class="tbtn-lbl">Kembali</span>
      </button>

      <div class="brand" :class="{ 'brand--hide': brandMoved }">
        <div class="brand-dot"><Zap :size="13" color="#fff" fill="white" :stroke-width="2"/></div>
        <span class="brand-name">Geniuss</span>
      </div>
      <Transition name="t-timer">
  <div v-if="showTimer" class="timer" :class="{ 'timer--warn': timerWarning, 'timer--out': timedOutQuizzes.has(step?.quiz?.id) }">
    <div class="timer-row">
      <Clock :size="13" :stroke-width="2"/>
      <span class="timer-val">{{ timedOutQuizzes.has(step?.quiz?.id) ? 'Waktu Habis' : timerDisplay }}</span>
    </div>
    <div class="timer-track">
      <div class="timer-fill" :class="{ 'fill--warn': timerWarning }"
           :style="{ width: timedOutQuizzes.has(step?.quiz?.id) ? '0%' : (timeRemaining / (step.quiz.time_limit) * 100) + '%' }">
      </div>
    </div>
  </div>
</Transition>

      <div class="topbar-r">
        <button class="tbtn tbtn-sq" :class="{ 'tbtn--on': musicOn }" @click="toggleMusic(props.backsound)">
          <Music2 v-if="musicOn"  :size="15" :stroke-width="2"/>
          <VolumeX v-else         :size="15" :stroke-width="2"/>
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
            <component :is="typeIcon(step?.quiz?.type)" :size="11" :stroke-width="2.5"/>
            {{ typeMeta(step?.quiz?.type).label }}
          </span>
          <h1 class="sb-title">{{ mission.name }}</h1>
          <p class="sb-sub">{{ step?.quiz?.title }}</p>
          <p v-if="!step?.isMaterial && step?.totalInQuiz > 1" class="sb-soal">
            Soal {{ (step?.questionIndex ?? 0) + 1 }} / {{ step?.totalInQuiz }}
          </p>

          <div class="prog">
            <div class="prog-meta">
              <span class="prog-lbl">Progress</span>
              <span class="prog-count">
                <b>{{ answeredQuizSteps }}</b> / {{ totalQuizSteps }}
              </span>
            </div>
            <div class="prog-track">
              <div class="prog-fill" :style="{ width: progressPct + '%' }">
                <span class="prog-shine"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Mascot -->
        <div class="mascot-wrap">
          <Transition name="bbl">
            <div v-if="bubbleVisible" class="bubble">
              <span>{{ BUBBLES[bubbleIdx] }}</span>
              <i class="bbl-o"></i>
              <i class="bbl-i"></i>
            </div>
          </Transition>
          <div class="mascot-frame">
            <img :src="mascotUrl" alt="Maskot" class="mascot"/>
            <div class="mascot-shadow"></div>
          </div>
        </div>
      </aside>

      <!-- ── MAIN PANEL ── -->
      <section class="main" :class="{ 'main--on': ready }">
        <div class="qcard" v-if="step" :class="{ 'opts--shake': shakeActive }">

          <!-- Colored top bar per type -->
          <div class="qcard-bar" :style="{ background: typeMeta(step.quiz.type).color }"></div>

          <!-- Blue header band -->
          <div class="qcard-head">
            <div class="qcard-deco qcard-deco-1"></div>
            <div class="qcard-deco qcard-deco-2"></div>
            <div class="qcard-head-inner">
              <div class="qcard-chip"
                   :style="{ background: typeMeta(step.quiz.type).bg, color: typeMeta(step.quiz.type).color }">
                <component :is="typeIcon(step.quiz.type)" :size="11" :stroke-width="2.5"/>
                <span>{{ typeMeta(step.quiz.type).label }}</span>
              </div>
              <span class="qcard-mission">{{ module.name }}</span>

            </div>

            <!-- Timer bar -->
            
          </div>

          <!-- Card body -->
          <div class="qcard-body">

            <!-- Question header: nomor soal + teks pertanyaan -->
            <div class="qcard-title-row" v-if="step.question && !step.isMaterial">

              <p class="qcard-step-title">{{ step.question.question_text }}</p>
              <div v-if="canGoNext" class="q-answered-badge">
                <CheckCircle2 :size="13" :stroke-width="2.5"/>
                <span>Terjawab</span>
              </div>
            </div>

            <!-- Material title row -->
            <div class="qcard-title-row qcard-title-row--mat" v-else-if="step.isMaterial">
              <div class="q-num-badge q-num-badge--mat">
                <BookOpen :size="13" :stroke-width="2.5"/>
              </div>
              <p class="qcard-step-title">{{ step.quiz.title }}</p>
            </div>

            <!-- Single question component -->
            <div
              v-if="step.question"
              class="question-item"
              :class="{
                'question-item--done': canGoNext && !step.isMaterial,
                'question-item--locked': timedOutQuizzes.has(step.quiz?.id)
              }"
            >
              <!-- Timeout overlay -->
              <div v-if="timedOutQuizzes.has(step.quiz?.id)" class="timeout-overlay">
                <div class="timeout-badge">
                  <Timer :size="16" :stroke-width="2"/>
                  <span>Waktu Habis</span>
                </div>
                <p class="timeout-sub">Soal ini tidak bisa dijawab lagi</p>
              </div>

              <component
                :is="COMPONENT_MAP[step.quiz.type]"
                :question="step.question"
                :modelValue="answers[step.question.id]"
                @update-answer="updateAnswer"
              />
            </div>

            <!-- Empty state -->
            <div v-else-if="!step.isMaterial" class="empty-qs">
              <Zap :size="26" color="#94a3b8" :stroke-width="1.4"/>
              <p>Tidak ada soal</p>
            </div>

            <!-- Hint bawah -->
            <div v-if="!canGoNext && !step.isMaterial" class="qcard-hint">
              <MousePointerClick :size="12" :stroke-width="2"/>
              <span>Pilih jawaban untuk melanjutkan</span>
            </div>
            <div v-else-if="!step.isMaterial" class="qcard-hint qcard-hint--done">
              <Sparkles :size="12" :stroke-width="2"/>
              <span>Terjawab! Klik Selanjutnya</span>
            </div>

          </div>
        </div>
      </section>

    </div>

    <!-- ══ FOOTER ══ -->
    <footer class="footer">
      <div class="footer-inner">
        <button class="fbtn fbtn--ghost" @click="goPrev" :disabled="isFirst || isSubmitting">
          <ArrowLeft :size="14" :stroke-width="2.5"/>
          <span>Sebelumnya</span>
        </button>

        <span class="f-pos">{{ currentStep + 1 }} / {{ steps.length }}</span>

        <template v-if="isLast">
          <button
            class="fbtn fbtn--mint"
            :class="{ 'fbtn--locked': !canGoNext && !step?.isMaterial }"
            @click="openConfirm"
            :disabled="isSubmitting || (!canGoNext && !step?.isMaterial)"
          >
            <template v-if="!isSubmitting">
              <Flag :size="13" :stroke-width="2"/>
              <span>Selesaikan Misi</span>
            </template>
            <template v-else>
              <Loader2 :size="13" class="spin"/>
              <span>Menyimpan…</span>
            </template>
          </button>
        </template>
        <template v-else>
          <button
            class="fbtn fbtn--blue"
            :class="{ 'fbtn--locked': !canGoNext && !step?.isMaterial }"
            @click="goNext"
            :disabled="(!canGoNext && !step?.isMaterial) || isSubmitting"
          >
            <span>Selanjutnya</span>
            <ArrowRight :size="14" :stroke-width="2.5"/>
          </button>
        </template>
      </div>

      <p v-if="!canGoNext && !step?.isMaterial" class="footer-hint">
        ⚠️ Jawab semua soal terlebih dahulu untuk melanjutkan
      </p>
    </footer>

    <!-- ══ CONFIRM SUBMIT MODAL ══ -->
    <Transition name="overlay-fade">
      <div v-if="showConfirm" class="modal-overlay" @click.self="closeConfirm">
        <Transition name="modal-pop" appear>
          <div v-if="showConfirm" class="modal">
            <div class="modal-icon">
              <Trophy :size="32" color="#34D399" :stroke-width="1.5"/>
            </div>
            <h2 class="modal-title">Apakah kamu yakin?</h2>
            <p class="modal-desc">
              Jawaban <strong>tidak bisa diubah</strong> setelah dikirim.
            </p>
            <div class="modal-actions">
              <button class="modal-btn modal-btn--cancel" @click="closeConfirm" :disabled="isSubmitting">Batal</button>
              <button class="modal-btn modal-btn--confirm" @click="submit" :disabled="isSubmitting">
                <span v-if="!isSubmitting">Ya, Kumpulkan!</span>
                <span v-else>Menyimpan…</span>
              </button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

    <!-- ══ CONFIRM LEAVE MODAL ══ -->
    <Transition name="overlay-fade">
      <div v-if="showLeaveConfirm" class="modal-overlay" @click.self="cancelLeave">
        <Transition name="modal-pop" appear>
          <div v-if="showLeaveConfirm" class="modal">
            <div class="modal-icon modal-icon--warn">
              <Flag :size="28" color="#F59E0B" :stroke-width="1.8"/>
            </div>
            <h2 class="modal-title">Keluar dari misi?</h2>
            <p class="modal-desc">
              Jawaban yang sudah kamu isi <strong>tidak akan disimpan</strong> jika kamu keluar sekarang.
            </p>
            <div class="modal-actions">
              <button class="modal-btn modal-btn--cancel" @click="cancelLeave">Lanjut Ngerjain</button>
              <button class="modal-btn modal-btn--leave" @click="confirmLeave">Ya, Keluar</button>
            </div>
          </div>
        </Transition>
      </div>
    </Transition>

  </div>
</template>

<style scoped>
/* ─── PALETTE ─── */
:root {
  --blue:        #2563EB;
  --blue-mid:    #1d4ed8;
  --blue-deep:   #1e3a8a;
  --blue-soft:   #BFDBFE;
  --blue-pale:   #EFF6FF;
  --mint:        #34D399;
  --mint-deep:   #059669;
  --mint-soft:   #D1FAE5;
  --red:         #EF4444;
  --red-soft:    #FEE2E2;
  --yellow:      #F59E0B;
  --yellow-soft: #FEF3C7;
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

/* ─── BG ─── */
.bg { position: fixed; inset: 0; z-index: 0; overflow: hidden; }
.bg-img { position:absolute; inset:0; background:url('/images/templates/background_misi.png') center/cover no-repeat; }
.bg-tint { position:absolute; inset:0; background:#2563EB; opacity:.52; }
.blob { position:absolute; border-radius:50%; pointer-events:none; filter:blur(80px); }
.b1 { width:480px; height:480px; top:-140px; left:-100px; background:#1d4ed8; opacity:.35; animation:bDrift 20s ease-in-out infinite alternate; }
.b2 { width:380px; height:380px; bottom:-100px; right:-80px; background:#34D399; opacity:.22; animation:bDrift2 24s ease-in-out infinite alternate; }
.b3 { width:260px; height:260px; top:38%; left:52%; background:#BFDBFE; opacity:.18; animation:bDrift 28s ease-in-out 6s infinite alternate; }
@keyframes bDrift  { 0%{transform:translate(0,0)} 50%{transform:translate(30px,20px) scale(1.05)} 100%{transform:translate(-15px,35px)} }
@keyframes bDrift2 { 0%{transform:translate(0,0)} 50%{transform:translate(-28px,-18px) scale(1.06)} 100%{transform:translate(22px,-40px)} }
.sh { position:absolute; pointer-events:none; }
.sh-circle { border-radius:50%; background:rgba(255,255,255,.06); border:1.5px solid rgba(255,255,255,.1); animation:sDrift ease-in-out infinite alternate; }
.c1 { width:150px; height:150px; top:-30px; left:-25px; animation-duration:22s; }
.c2 { width:90px; height:90px; bottom:70px; right:50px; animation-duration:28s; animation-delay:4s; }
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
.topbar-r { display:flex; align-items:center; gap:8px; margin-left:auto; z-index:3; }
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

/* ─── BODY GRID ─── */
.body { position:relative; z-index:10; flex:1; display:grid; grid-template-columns:264px 1fr; gap:20px; max-width:1080px; width:100%; margin:0 auto; padding:22px 18px 18px; align-items:start; opacity:0; transition:opacity .45s; }
.body--on { opacity:1; }

/* ─── SIDEBAR ─── */
.sidebar { display:flex; flex-direction:column; opacity:0; transform:translateX(-16px); transition:opacity .5s,transform .5s cubic-bezier(.34,1.56,.64,1); user-select:none; cursor:pointer; min-width:0; }
.sidebar--on { opacity:1; transform:none; }
.sb-info { margin-bottom:18px; min-width:0; overflow:hidden; }
.sb-chip { display:inline-flex; align-items:center; gap:5px; background:rgba(255,255,255,.2); border:1px solid rgba(255,255,255,.38); border-radius:999px; padding:4px 13px; font-size:11px; font-weight:900; color:#fff; backdrop-filter:blur(6px); margin-bottom:10px; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sb-title { font-family:'Righteous',cursive; font-size:clamp(16px,2vw,22px); color:#fff; line-height:1.25; margin-bottom:5px; text-shadow:0 1px 10px rgba(0,0,0,.35); word-break:break-word; overflow-wrap:anywhere; min-width:0; }
.sb-sub { font-size:12.5px; font-weight:800; color:rgba(255,255,255,.9); line-height:1.55; margin-bottom:6px; text-shadow:0 1px 6px rgba(0,0,0,.28); word-break:break-word; overflow-wrap:anywhere; min-width:0; }
.sb-soal { font-size:11px; font-weight:900; color:rgba(255,255,255,.65); letter-spacing:.3px; margin-bottom:16px; }
.prog { margin-bottom:2px; min-width:0; }
.prog-meta { display:flex; justify-content:space-between; align-items:center; margin-bottom:7px; gap:8px; min-width:0; }
.prog-lbl { font-size:10px; font-weight:900; color:rgba(255,255,255,.85); text-transform:uppercase; letter-spacing:.6px; white-space:nowrap; flex-shrink:0; }
.prog-count { font-family:'Righteous',cursive; font-size:14px; color:#fff; white-space:nowrap; flex-shrink:0; }
.prog-count b { font-size:16px; }
.prog-track { height:8px; background:rgba(255,255,255,.2); border-radius:99px; overflow:hidden; min-width:0; }
.prog-fill { height:100%; background:#34D399; border-radius:99px; position:relative; overflow:hidden; transition:width .5s cubic-bezier(.34,1.56,.64,1); }
.prog-shine { position:absolute; inset:0; background:linear-gradient(90deg,transparent,rgba(255,255,255,.38),transparent); animation:shine 2.2s ease-in-out infinite; }
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }

/* Mascot */
.mascot-wrap { position:relative; padding-left:4px; }
.bubble { position:relative; background:#fff; border:2px solid #BFDBFE; border-radius:16px; padding:9px 14px; min-width:146px; max-width:210px; box-shadow:0 5px 18px rgba(37,99,235,.13); margin-bottom:6px; animation:bblFloat 3.5s ease-in-out infinite; }
.bubble span { font-size:12.5px; font-weight:800; color:#1e3a8a; display:block; }
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

/* ─── MAIN PANEL ─── */
.main { opacity:0; transform:translateY(16px); transition:opacity .5s .1s,transform .5s .1s cubic-bezier(.34,1.56,.64,1); }
.main--on { opacity:1; transform:none; }

/* ── QUIZ CARD ── */
.qcard {
  background: #FDFCFB;
  border-radius: 20px;
  border: 1.5px solid var(--gray-2);
  overflow: hidden;
  box-shadow: 0 4px 0 #BFDBFE, 0 10px 32px rgba(37,99,235,.10);
}

.qcard-bar { height: 4px; }

.qcard-head {
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
  position: relative; overflow: hidden;
}
.qcard-deco { position:absolute; border-radius:50%; background:rgba(255,255,255,.08); pointer-events:none; }
.qcard-deco-1 { width:160px; height:160px; top:-55px; right:-35px; }
.qcard-deco-2 { width:80px; height:80px; bottom:-30px; left:18px; }

.qcard-head-inner {
  position:relative; z-index:1;
  display:flex; align-items:center; justify-content:space-between;
  gap:8px; padding:12px 18px;
  flex-wrap:wrap;
}

.qcard-chip {
  display:inline-flex; align-items:center; gap:5px;
  border-radius:999px; padding:4px 10px;
  font-size:10.5px; font-weight:900;
  white-space:nowrap; flex-shrink:0;
  border: 1px solid rgba(255,255,255,.3);
}

.qcard-mission {
  flex:1; text-align:center;
  font-family:'Righteous',cursive; font-size:13px;
  color:rgba(255,255,255,.85);
  overflow:hidden; text-overflow:ellipsis;
  padding:0 8px;
  white-space:nowrap;
}

.qcard-counter { display:flex; align-items:baseline; gap:2px; flex-shrink:0; }
.qcard-counter-num { font-family:'Righteous',cursive; font-size:20px; color:#fff; line-height:1; }
.qcard-counter-sep { font-size:13px; color:rgba(255,255,255,.5); margin:0 1px; }
.qcard-counter-tot { font-family:'Righteous',cursive; font-size:13px; color:rgba(255,255,255,.6); }

.qcard-body { padding:20px 20px 24px; }

.qcard-title-row {
  display:flex; align-items:flex-start; gap:11px;
  background:#EFF6FF; padding:16px 16px 16px;
  border-radius:12px; margin-bottom:16px;
}
.qcard-title-row--mat { background:#F0FDF9; }

.q-num-badge {
  display:inline-flex; align-items:center; gap:2px;
  min-width:34px; height:34px; border-radius:10px;
  color:#fff; font-family:'Righteous',cursive; font-size:13px;
  justify-content:center; flex-shrink:0;
  box-shadow:0 3px 8px rgba(59,130,246,.28); margin-top:1px;
}
.q-num-badge--mat { background:#10b981; box-shadow:0 3px 8px rgba(16,185,129,.28); }

.qcard-step-title {
  flex:1; font-size:15px; font-weight:800;
  color:#1E293B; line-height:1.65;
  word-break:break-word; overflow-wrap:anywhere;
}

.q-answered-badge {
  display:inline-flex; align-items:center; gap:4px;
  background:#ECFDF5; border:1.5px solid #6EE7B7;
  border-radius:999px; padding:3px 9px;
  font-size:10px; font-weight:900; color:#059669;
  flex-shrink:0; margin-top:3px; white-space:nowrap;
}

/* Questions area */
.question-item { border:1.5px solid rgba(29,78,216,.07); border-radius:14px; padding:13px; background:#fafbfc; transition:border-color .2s,background .2s; }
.question-item--done { border-color:rgba(16,185,129,.22); background:rgba(240,253,244,.55); }
.empty-qs { text-align:center; padding:28px; display:flex; flex-direction:column; align-items:center; gap:8px; color:#94a3b8; font-size:12px; font-weight:700; }

.qcard-hint { display:flex; align-items:center; justify-content:center; gap:6px; padding:10px 0 4px; font-size:11.5px; font-weight:800; color:#94a3b8; }
.qcard-hint--done { color:#059669; }

/* Shake */
.opts--shake { animation:optShake .5s ease; }
@keyframes optShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 40%{transform:translateX(5px)} 60%{transform:translateX(-3px)} 80%{transform:translateX(3px)} }

/* ─── FOOTER ─── */
.footer { position:relative; z-index:50; background:rgba(255,255,255,.10); backdrop-filter:blur(18px); border-top:1px solid rgba(255,255,255,.16); padding:11px 0 8px; flex-shrink:0; }
.footer-inner { display:flex; align-items:center; gap:10px; max-width:1080px; margin:0 auto; padding:0 20px; }
.f-pos { font-family:'Righteous',cursive; font-size:13px; color:#fff; flex:1; text-align:center; }
.footer-hint { text-align:center; font-size:11px; font-weight:800; color:#fde68a; padding:5px 16px 2px; text-shadow:0 1px 4px rgba(0,0,0,.15); word-break:break-word; line-height:1.5; }

.fbtn { display:inline-flex; align-items:center; gap:6px; height:40px; padding:0 18px; border:none; border-radius:10px; font-family:'Nunito',sans-serif; font-size:13px; font-weight:800; cursor:pointer; flex-shrink:0; white-space:nowrap; transition:transform .15s cubic-bezier(.34,1.56,.64,1),box-shadow .15s; }
.fbtn--ghost { background:rgba(255,255,255,.14); color:#fff; border:1px solid rgba(255,255,255,.25); }
.fbtn--ghost:hover:not(:disabled) { background:rgba(255,255,255,.22); transform:translateY(-1px); }
.fbtn--ghost:disabled { opacity:.4; cursor:not-allowed; }
.fbtn--blue { background:#2563EB; color:#fff; box-shadow:0 3px 12px rgba(37,99,235,.4); }
.fbtn--blue:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 16px rgba(37,99,235,.5); }
.fbtn--blue:disabled, .fbtn--locked { background:rgba(255,255,255,.15)!important; color:rgba(255,255,255,.4)!important; box-shadow:none!important; cursor:not-allowed; }
.fbtn--mint { background:#00c54cd7; color:#fff; box-shadow:0 3px 12px rgba(52,211,153,.4); }
.fbtn--mint:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 5px 16px rgba(52,211,153,.5); }
.fbtn--mint:disabled { opacity:.5; cursor:not-allowed; }

@keyframes spin { to{transform:rotate(360deg);} }
.spin { animation:spin .8s linear infinite; }

/* ── MODALS ── */
.modal-overlay {
  position:fixed; inset:0; z-index:200;
  background:rgba(0,0,0,.55); backdrop-filter:blur(6px);
  display:flex; align-items:center; justify-content:center; padding:20px;
}
.modal {
  background:#fff; border-radius:22px; padding:28px 24px 22px;
  width:100%; max-width:360px;
  box-shadow:0 24px 60px rgba(0,0,0,.22), 0 4px 0 rgba(29,78,216,.08);
  display:flex; flex-direction:column; align-items:center;
  gap:12px; text-align:center;
}
.modal-icon { width:68px; height:68px; border-radius:50%; background:#D1FAE5; border:2.5px solid #6EE7B7; display:flex; align-items:center; justify-content:center; box-shadow:0 5px 18px rgba(52,211,153,.22); }
.modal-icon--warn { background:#FEF3C7; border-color:#FCD34D; }
.modal-title { font-family:'Righteous',cursive; font-size:20px; color:#1e3a8a; margin:0; }
.modal-desc { font-size:13px; font-weight:600; color:#475569; line-height:1.65; margin:0; }
.modal-desc strong { color:#dc2626; }
.modal-actions { display:flex; gap:9px; width:100%; margin-top:4px; }
.modal-btn { flex:1; height:42px; border:none; border-radius:12px; font-family:'Righteous',cursive; font-size:13.5px; cursor:pointer; transition:all .18s cubic-bezier(.34,1.56,.64,1); }
.modal-btn--cancel { background:#f1f5f9; color:#475569; border:1.5px solid #e2e8f0; }
.modal-btn--cancel:hover:not(:disabled) { background:#e2e8f0; transform:translateY(-1px); }
.modal-btn--confirm { background:linear-gradient(135deg,#10b981,#059669); color:#fff; box-shadow:0 4px 14px rgba(16,185,129,.35); }
.modal-btn--confirm:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 6px 18px rgba(16,185,129,.45); }
.modal-btn--leave { background:linear-gradient(135deg,#ef4444,#dc2626); color:#fff; box-shadow:0 4px 14px rgba(220,38,38,.3); }
.modal-btn--leave:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(220,38,38,.4); }
.modal-btn:disabled { opacity:.55; cursor:not-allowed; }

.overlay-fade-enter-active { transition:opacity .25s ease; }
.overlay-fade-leave-active { transition:opacity .2s ease; }
.overlay-fade-enter-from, .overlay-fade-leave-to { opacity:0; }
.modal-pop-enter-active { transition:opacity .3s ease,transform .35s cubic-bezier(.34,1.56,.64,1); }
.modal-pop-leave-active { transition:opacity .18s ease,transform .18s ease; }
.modal-pop-enter-from   { opacity:0; transform:scale(.82) translateY(24px); }
.modal-pop-leave-to     { opacity:0; transform:scale(.94); }

/* ─── MOBILE ≤ 820px ─── */
@media (max-width: 820px) {
  .b1{width:300px;height:300px;} .b2{width:240px;height:240px;} .b3{width:180px;height:180px;}
  .c1{width:100px;height:100px;} .c2{display:none;} .r1{width:200px;height:200px;}
  .topbar{height:52px;padding:0 13px;}
  .brand-name{font-size:16px;} .brand-dot{width:25px;height:25px;}
  .body{grid-template-columns:1fr;gap:0;padding:0;max-width:100%;}

  /* Sidebar jadi horizontal strip di mobile */
  .sidebar{
    opacity:1!important; transform:none!important;
    flex-direction:column; gap:0; cursor:default;
    padding:13px 15px 12px;
    background:rgba(29,78,216,.76); backdrop-filter:blur(18px);
    border-bottom:1px solid rgba(191,219,254,.22);
    overflow:hidden; /* cegah konten sidebar overflow keluar */
  }
  .sb-info{
    margin-bottom:0;
    min-width:0;
    width:100%; /* pastikan sb-info tidak melebihi sidebar */
    overflow:hidden;
  }
  .sb-chip{margin-bottom:6px;font-size:10px; max-width:100%;}
  .sb-title{font-size:15px;margin-bottom:3px;}
  .sb-sub{font-size:11.5px;margin-bottom:10px; white-space:normal;}
  .prog{margin-bottom:0; width:100%;}
  .prog-meta{margin-bottom:4px;}
  .mascot-wrap{display:none;}
  .main{transform:none;opacity:1;}
  .qcard{border-radius:0;border-left:none;border-right:none;}
  .qcard-head-inner{padding:10px 14px; flex-wrap:wrap; gap:6px;}
  .qcard-mission{flex:1; min-width:0; font-size:11.5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;}
  .qcard-counter-num{font-size:17px;}
  .qcard-body{padding:14px 14px 18px;}
  .qcard-title-row{padding:14px 14px;}
  .qcard-step-title{font-size:14px;}
  .footer-inner{padding:0 15px;} .fbtn{height:40px;}
  .fbtn span { white-space:normal; text-align:center; line-height:1.3; }
}

/* ─── TIMER BAR ─── */
.timer-bar {
  display: flex; align-items: center; gap: 8px;
  padding: 7px 18px 10px;
  background: rgba(0,0,0,.15);
  border-top: 1px solid rgba(255,255,255,.12);
  transition: background .3s;
}
.timer-bar--warn { background: rgba(239,68,68,.28); }
.timer-bar--out  { background: rgba(100,116,139,.35); }

.timer-icon {
  display: flex; align-items: center; color: #fff; opacity: .85; flex-shrink: 0;
}
.timer-bar--warn .timer-icon { color: #fca5a5; animation: timerPulse .7s ease-in-out infinite alternate; }

.timer-text {
  font-family: 'Righteous', cursive; font-size: 13px; color: #fff;
  min-width: 52px; flex-shrink: 0; letter-spacing: .5px;
}
.timer-bar--warn .timer-text { color: #fca5a5; }
.timer-bar--out  .timer-text { color: rgba(255,255,255,.55); font-size: 12px; }

.timer-track {
  flex: 1; height: 6px; background: rgba(255,255,255,.2);
  border-radius: 99px; overflow: hidden;
}
.timer-fill {
  height: 100%; background: #34D399; border-radius: 99px;
  transition: width 1s linear, background .4s;
}
.timer-bar--warn .timer-fill { background: #ef4444; }
.timer-bar--out  .timer-fill { background: #94a3b8; }

@keyframes timerPulse { from { opacity: .6 } to { opacity: 1 } }

/* ─── TIMEOUT OVERLAY ─── */
.question-item--locked {
  position: relative; pointer-events: none;
  filter: grayscale(.4); opacity: .7;
}
.timeout-overlay {
  position: absolute; inset: 0; z-index: 10;
  background: rgba(248,250,252,.82); backdrop-filter: blur(3px);
  border-radius: 13px;
  display: flex; flex-direction: column;
  align-items: center; justify-content: center; gap: 6px;
  pointer-events: none;
}
.timeout-badge {
  display: inline-flex; align-items: center; gap: 6px;
  background: #fee2e2; border: 1.5px solid #fca5a5;
  border-radius: 999px; padding: 6px 16px;
  font-size: 13px; font-weight: 900; color: #dc2626;
}
.timeout-sub {
  font-size: 11.5px; font-weight: 700; color: #94a3b8;
}
</style>
