<script setup>
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import {
  ArrowLeft, ArrowRight, CheckCircle2, Home,
  Zap, BookOpen, ClipboardList, LayoutGrid, ToggleLeft, FileSearch,
  GripHorizontal,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import Multiple_choice from './Multiple_choice.vue'
import True_false      from './True_false.vue'
import Case_study      from './Case_study.vue'
import Materials       from './Materials.vue'
import Drag_drop       from './Drag_drop.vue'

// ── Component / type maps ──────────────────────────────────────
const COMPONENT_MAP = {
  multiple_choices: Multiple_choice,
  true_false:       True_false,
  case_study:       Case_study,
  materials:        Materials,
  drag_drop:        Drag_drop,
}
const TYPE_META = {
  multiple_choices: { label: 'Pilihan Ganda',    color: '#3b82f6', bg: '#dbeafe' },
  true_false:       { label: 'Pilih Gambar Yang Benar',    color: '#8b5cf6', bg: '#ede9fe' },
  case_study:       { label: 'Studi Kasus',      color: '#0891b2', bg: '#cffafe' },
  drag_drop:        { label: 'Seret & Letakkan', color: '#f59e0b', bg: '#fef3c7' },
  materials:        { label: 'Materi',           color: '#10b981', bg: '#dcfce7' },
}
const typeMeta = (t) => TYPE_META[t] || TYPE_META.materials

// ── Props ──────────────────────────────────────────────────────
const props = defineProps({
  mission: { type: Object, required: true },
  user:    { type: Object, default: () => ({ name: 'Siswa' }) },
   module: {
    type: Object,
    default: () => ({ id: null, name: 'Module', description: '' }),
  }
})

// ── Steps ──────────────────────────────────────────────────────
const steps = computed(() =>
  props.mission.quizzes.map((quiz, idx) => ({
    quizIndex:  idx,
    quiz,
    isMaterial: quiz.type === 'materials',
    isDragDrop: quiz.type === 'drag_drop',
  }))
)


// ── State ──────────────────────────────────────────────────────
const currentStep  = ref(0)
const answers      = reactive({})
const isSubmitting = ref(false)
const ready        = ref(false)

// Music
const musicOn  = ref(false)
const audioRef = ref(null)

const step    = computed(() => steps.value[currentStep.value])
const isFirst = computed(() => currentStep.value === 0)
const isLast  = computed(() => currentStep.value === steps.value.length - 1)

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
  const qs = s.quiz.questions || []
  return qs.length === 0 || qs.every(q => isQuestionAnswered(q, s.quiz.type))
}

const canGoNext       = computed(() => isStepAnswered(step.value))
const allStepsAnswered = computed(() =>
  steps.value.filter(s => !s.isMaterial).every(s => isStepAnswered(s))
)

// ── Progress ───────────────────────────────────────────────────
const totalQuizSteps    = computed(() => steps.value.filter(s => !s.isMaterial).length)
const answeredQuizSteps = computed(() => steps.value.filter(s => !s.isMaterial && isStepAnswered(s)).length)
const progressPct       = computed(() =>
  totalQuizSteps.value === 0 ? 100 : Math.round((answeredQuizSteps.value / totalQuizSteps.value) * 100)
)

// ── Sidebar speech bubble ──────────────────────────────────────
const BUBBLES = [
  'Ayo semangat! 💪', 'Baca dengan teliti 👀',
  'Kamu pasti bisa! 🔥', 'Pikirkan baik-baik 🤔',
  'Hampir selesai! ✨', 'Fokus ya! 🎯',
]
const bubbleIdx     = ref(0)
const bubbleVisible = ref(true)
let bubbleTimer     = null

const rotateBubble = () => {
  bubbleVisible.value = false
  setTimeout(() => {
    bubbleIdx.value = (bubbleIdx.value + 1) % BUBBLES.length
    bubbleVisible.value = true
  }, 300)
}

// ── Music ─────────────────────────────────────────────────────
const handleVisibility = () => {
  if (!audioRef.value) return
  document.hidden
    ? audioRef.value.pause()
    : musicOn.value && audioRef.value.play().catch(() => {})
}

const toggleMusic = async () => {
  if (!audioRef.value) {
    audioRef.value = new Audio('/backsound/backsound.mp3')
    audioRef.value.loop    = true
    audioRef.value.volume  = 0.4
    audioRef.value.preload = 'auto'
    audioRef.value.addEventListener('error', () => {
      audioRef.value = null
      musicOn.value  = false
    })
  }
  if (musicOn.value) {
    audioRef.value.pause()
    musicOn.value = false
  } else {
    try {
      await audioRef.value.play()
      musicOn.value = true
    } catch {
      musicOn.value = false
    }
  }
}

// ── Navigation ─────────────────────────────────────────────────
const updateAnswer = (payload) => {
  if (payload?.questionId !== undefined) answers[payload.questionId] = payload.value
}
const goNext   = () => { if (!isLast.value) currentStep.value++ }
const goPrev   = () => { if (!isFirst.value) currentStep.value-- }
const goToStep = (idx) => { currentStep.value = idx }
const goBack   = () => router.back()
const goHome   = () => router.visit(route('playground.index'))

const stepStatus = (idx) => {
  const s = steps.value[idx]
  if (s.isMaterial) return 'material'
  if (idx === currentStep.value) return 'active'
  return isStepAnswered(s) ? 'done' : 'pending'
}

// ── Confirm modal ──────────────────────────────────────────────
const showConfirm = ref(false)
const openConfirm  = () => { showConfirm.value = true }
const closeConfirm = () => { showConfirm.value = false }

// ── Submit ─────────────────────────────────────────────────────
const submit = async () => {
  closeConfirm()
  isSubmitting.value = true
  try {
    const res  = await fetch(route('playground.missions.submit', props.mission.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({ answers, quiz_ids: props.mission.quizzes.map(q => q.id) }),
    })
    const data = await res.json()
    if (data.success) router.visit(route('playground.missions.result', props.mission.id))
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
})
onUnmounted(() => {
  clearInterval(bubbleTimer)
  document.removeEventListener('visibilitychange', handleVisibility)
  if (audioRef.value) { audioRef.value.pause(); audioRef.value = null }
})
</script>

<template>
  <div style="display:none">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Righteous&display=swap" rel="stylesheet" />
  </div>

  <div class="root">

    <!-- ══ BACKGROUND ══ -->
    <div class="bg">
      <div class="bg-dim"></div>
    </div>

    <!-- ══ TOPBAR ══ -->
    <header class="topbar">
      <button class="t-btn" @click="goBack" :disabled="isSubmitting">
        <ArrowLeft :size="15" :stroke-width="2.6" />
        <span>Kembali</span>
      </button>
      <div class="brand">
        <div class="brand-dot">
          <Zap :size="13" color="#fff" fill="white" :stroke-width="2.5" />
        </div>
        <span class="brand-name">Geniuss</span>
      </div>

      <button class="t-btn t-btn--sq music-btn" :class="{ 'music-btn--on': musicOn }" @click="toggleMusic" :title="musicOn ? 'Matikan musik' : 'Nyalakan musik'">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" width="15" height="15">
          <path d="M9 18V5l12-2v13" />
          <circle cx="6" cy="18" r="3" />
          <circle cx="18" cy="16" r="3" />
        </svg>
      </button>

      <button class="t-btn t-btn--sq" @click="goHome">
        <Home :size="15" :stroke-width="2.6" />
      </button>
    </header>

    <!-- ══ BODY ══ -->
    <div class="body" :class="{ 'body--on': ready }">

      <!-- ─── SIDEBAR ─── -->
      <aside class="sidebar" :class="{ 'sidebar--on': ready }" @click="rotateBubble">
        <div class="sb-info">
          <span class="activity-tag">
            <GripHorizontal v-if="step?.isDragDrop"                            :size="11" :stroke-width="2.5" />
            <BookOpen       v-else-if="step?.isMaterial"                       :size="11" :stroke-width="2.5" />
            <ClipboardList  v-else-if="step?.quiz?.type === 'multiple_choices'" :size="11" :stroke-width="2.5" />
            <ToggleLeft     v-else-if="step?.quiz?.type === 'true_false'"       :size="11" :stroke-width="2.5" />
            <FileSearch     v-else-if="step?.quiz?.type === 'case_study'"       :size="11" :stroke-width="2.5" />
            <LayoutGrid     v-else                                              :size="11" :stroke-width="2.5" />
            {{ typeMeta(step?.quiz?.type).label }}
          </span>

          <h1 class="sb-title">{{ mission.name }}</h1>
          <p class="sb-sub">{{ step?.quiz?.title }}</p>

          <div class="prog">
            <div class="prog-meta">
              <span class="prog-lbl">Progress</span>
              <span class="prog-val">
                <b>{{ answeredQuizSteps }}</b>
                <span> / {{ totalQuizSteps }}</span>
              </span>
            </div>
            <div class="prog-bar">
              <div class="prog-fill" :style="`width:${progressPct}%`">
                <span class="prog-glow"></span>
              </div>
            </div>
          </div>


        </div>

        <div class="mascot-wrap">
          <Transition name="bbl">
            <div v-if="bubbleVisible" class="bubble">
              <span>{{ BUBBLES[bubbleIdx] }}</span>
              <i class="bbl-tail-out"></i>
              <i class="bbl-tail-in"></i>
            </div>
          </Transition>
          <div class="mascot-shadow"></div>
          <img src="/images/templates/pose_nunjuk.png" class="mascot" alt="Maskot" />
        </div>
      </aside>

      <!-- ─── GAME PANEL ─── -->
      <section class="game" :class="{ 'game--on': ready }">
        <div class="card" v-if="step">
          <div class="card-bar" :style="{ background: `linear-gradient(90deg, ${typeMeta(step.quiz.type).color}, ${typeMeta(step.quiz.type).color}88)` }"></div>

          <div class="card-body">
            <div class="card-head">
              <div class="card-brand">
                <div class="card-brand-ico">
                  <Zap :size="14" color="#fff" fill="white" :stroke-width="2.4" />
                </div>
                <div>
                  <div class="card-brand-nm"><span>{{ module.name }}</span></div>
                  <div class="card-brand-sub">{{ typeMeta(step.quiz.type).label }}</div>
                </div>
              </div>
              <span
                class="card-type-pill"
                :style="{ background: typeMeta(step.quiz.type).bg, color: typeMeta(step.quiz.type).color }"
              >
                Kuis {{ (step.quizIndex ?? 0) + 1 }} / {{ steps.length }}
              </span>
            </div>

            <div class="divider"></div>

            <div class="questions-area">
              <div
                v-for="(question, qIdx) in (step.quiz.questions || [])"
                :key="question.id"
                class="question-item"
                :class="{ 'question-item--done': isQuestionAnswered(question, step.quiz.type) }"
              >
                <div class="q-label" v-if="!step.isMaterial">
                  <span class="q-num" :style="{ background: typeMeta(step.quiz.type).color }">{{ qIdx + 1 }}</span>
                  <span class="q-text">{{ question.question_text }}</span>
                  <CheckCircle2
                    v-if="isQuestionAnswered(question, step.quiz.type)"
                    :size="15" color="#10b981" :stroke-width="2.5"
                  />
                </div>
                <component
                  :is="COMPONENT_MAP[step.quiz.type]"
                  :question="question"
                  :modelValue="answers[question.id]"
                  @update-answer="updateAnswer"
                />
              </div>

              <div v-if="!step.quiz.questions?.length" class="empty-qs">
                <Zap :size="26" color="#94a3b8" :stroke-width="1.4" />
                <p>Tidak ada soal</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- ══ FOOTER NAV ══ -->
    <footer class="footer">
      <div class="footer-inner">
        <button class="f-btn f-btn--prev" @click="goPrev" :disabled="isFirst || isSubmitting">
          <ArrowLeft :size="15" :stroke-width="2.6" />
          <span>Sebelumnya</span>
        </button>

        <span class="footer-pos">{{ currentStep + 1 }} / {{ steps.length }}</span>

        <template v-if="isLast">
          <button
            class="f-btn f-btn--finish"
            :class="{ 'f-btn--locked': !canGoNext }"
            @click="openConfirm"
            :disabled="isSubmitting || !canGoNext"
          >
            <span v-if="!isSubmitting">Selesaikan Misi</span>
            <span v-else>Menyimpan…</span>
          </button>
        </template>
        <template v-else>
          <button
            class="f-btn f-btn--next"
            :class="{ 'f-btn--locked': !canGoNext }"
            @click="goNext"
            :disabled="!canGoNext || isSubmitting"
          >
            Selanjutnya
            <ArrowRight :size="15" :stroke-width="2.6" />
          </button>
        </template>
      </div>

      <p v-if="!canGoNext && !step?.isMaterial" class="footer-hint">
        ⚠️ Jawab semua soal terlebih dahulu untuk melanjutkan
      </p>
    </footer>

    <!-- ══ CONFIRM MODAL ══ -->
<Transition name="overlay-fade">
  <div v-if="showConfirm" class="modal-overlay" @click.self="closeConfirm">
    <Transition name="modal-pop" appear>
      <div v-if="showConfirm" class="modal">
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
    <!-- ══ MUSIC FAB ══ -->


  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ══ ROOT ══ */
.root { min-height: 100vh; display: flex; flex-direction: column; font-family: 'Nunito', sans-serif; position: relative; }

/* ══ BG ══ */
.bg { position: fixed; inset: 0; z-index: 0; }
.bg-dim {
  position: absolute; inset: 0;
  background: url('/images/templates/background_misi.png') top center / cover no-repeat fixed;
}

/* ══ TOPBAR ══ */
.topbar {
  position: relative; z-index: 50;
  height: 54px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: space-between; gap: 10px;
  padding: 0 22px;
  background: rgba(255,255,255,0.16);
  backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8);
  border-bottom: 1px solid rgba(255,255,255,0.38);
  box-shadow: 0 1px 0 rgba(0,0,0,.04), 0 4px 16px rgba(0,0,0,.04);
}
.brand { display: flex; align-items: center; gap: 9px; }
.brand-dot {
  width: 30px; height: 30px; border-radius: 9px;
  background: linear-gradient(140deg,#60a5fa,#1d4ed8);
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 3px 10px rgba(29,78,216,.4);
}
.brand-name { font-family: 'Righteous', cursive; font-size: 19px; color: #fff; letter-spacing: -.1px; text-shadow: 0 1px 8px rgba(0,0,0,.2); }
.t-btn {
  display: flex; align-items: center; gap: 6px;
  padding: 7px 15px;
  background: rgba(255,255,255,.18); backdrop-filter: blur(8px);
  border: 1.5px solid rgba(255,255,255,.38); border-radius: 10px;
  font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; color: #fff;
  cursor: pointer; transition: all .2s;
  text-shadow: 0 1px 4px rgba(0,0,0,.15);
}
.t-btn:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.t-btn:disabled { opacity: .45; cursor: not-allowed; }
.t-btn--sq { padding: 7px 12px; }

/* Music button */
.music-btn { margin-left: auto; transition: all .2s; }
.music-btn--on { background: linear-gradient(135deg, #60a5fa, #1d4ed8); border-color: rgba(255,255,255,.5); box-shadow: 0 4px 12px rgba(29,78,216,.3); }
.music-btn--on:hover { background: linear-gradient(135deg, #3b82f6, #1e40af); }

/* ══ BODY ══ */
.body {
  position: relative; z-index: 10; flex: 1;
  display: grid; grid-template-columns: 272px 1fr;
  gap: 22px; max-width: 1080px; width: 100%; margin: 0 auto;
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
.sb-title { font-family: 'Righteous', cursive; font-size: clamp(17px,2vw,24px); color: #fff; line-height: 1.22; text-shadow: 0 2px 16px rgba(0,0,0,.22); margin-bottom: 5px; }
.sb-sub { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,.75); line-height: 1.6; text-shadow: 0 1px 5px rgba(0,0,0,.14); margin-bottom: 16px; }

.prog { width: 100%; margin-bottom: 14px; }
.prog-meta { display: flex; justify-content: space-between; align-items: center; margin-bottom: 7px; }
.prog-lbl { font-size: 10px; font-weight: 900; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .6px; }
.prog-val { font-family: 'Righteous', cursive; font-size: 15px; color: #fff; display: flex; align-items: baseline; gap: 1px; }
.prog-val b { font-size: 17px; }
.prog-val span { color: rgba(255,255,255,.5); font-size: 12px; }
.prog-bar { height: 9px; border-radius: 99px; background: rgba(255,255,255,.18); overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,.14); }
.prog-fill { height: 100%; border-radius: 99px; position: relative; overflow: hidden; background: linear-gradient(90deg,#60a5fa,#1d4ed8); transition: width .5s cubic-bezier(.34,1.56,.64,1); box-shadow: 0 0 14px rgba(96,165,250,.55); }
.prog-glow { position: absolute; inset: 0; background: linear-gradient(90deg,transparent 0%,rgba(255,255,255,.38) 50%,transparent 100%); animation: shine 2.4s ease-in-out infinite; }
@keyframes shine { 0%,100%{transform:translateX(-100%)} 60%{transform:translateX(200%)} }

.step-dots { display: flex; flex-wrap: wrap; gap: 5px; }
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
.step-dot.material { background: rgba(16,185,129,.25); border-color: #34d399; font-size: 12px; }

/* Mascot */
.mascot-wrap { position: relative; margin-left: 6px; }
.bubble {
  position: relative; background: #fff; border: 2.5px solid #93c5fd;
  border-radius: 18px; padding: 9px 15px; min-width: 150px; max-width: 210px;
  box-shadow: 0 8px 28px rgba(29,78,216,.13), inset 0 1px 0 rgba(255,255,255,.9);
  margin-bottom: 8px; animation: float 3.5s ease-in-out infinite;
}
.bubble span { font-family: 'Nunito', sans-serif; font-size: 12.5px; font-weight: 800; color: #1e3a8a; display: block; }
.bbl-tail-out, .bbl-tail-in { position: absolute; width: 0; height: 0; font-style: normal; }
.bbl-tail-out { bottom: -16px; left: 18px; border-left: 11px solid transparent; border-right: 7px solid transparent; border-top: 15px solid #93c5fd; }
.bbl-tail-in  { bottom: -12px; left: 19px; border-left: 9px solid transparent;  border-right: 6px solid transparent;  border-top: 12px solid #fff; }
@keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-6px)} }
.bbl-enter-active { transition: opacity .3s ease, transform .36s cubic-bezier(.34,1.56,.64,1); }
.bbl-leave-active { transition: opacity .2s ease, transform .2s ease; }
.bbl-enter-from   { opacity: 0; transform: translateY(10px) scale(.9); }
.bbl-leave-to     { opacity: 0; transform: translateY(-7px) scale(.94); }
.mascot-shadow { position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%); width: 145px; height: 28px; background: radial-gradient(ellipse,rgba(0,0,0,.18) 0%,transparent 70%); border-radius: 50%; pointer-events: none; }
.mascot { position: relative; z-index: 2; width: clamp(140px,15vw,200px); height: auto; display: block; filter: drop-shadow(0 10px 26px rgba(0,0,0,.2)); animation: bob 3.5s ease-in-out infinite; transform-origin: bottom center; }
@keyframes bob { 0%,100%{transform:translateY(0) rotate(0deg)} 45%{transform:translateY(-9px) rotate(.6deg)} 70%{transform:translateY(-5px) rotate(-.4deg)} }

/* ══ GAME PANEL ══ */
.game { opacity: 0; transform: translateY(22px); transition: opacity .6s .12s ease, transform .6s .12s cubic-bezier(.34,1.56,.64,1); }
.game--on { opacity: 1; transform: none; }
.card { background: rgba(255,255,255,.93); backdrop-filter: blur(28px) saturate(1.6); -webkit-backdrop-filter: blur(28px) saturate(1.6); border-radius: 22px; overflow: hidden; border: 1.5px solid rgba(255,255,255,.85); box-shadow: 0 20px 56px rgba(0,0,0,.12), 0 4px 0 rgba(29,78,216,.07), inset 0 1px 0 #fff; }
.card-bar { height: 4px; background-size: 200%; animation: bar 3.5s linear infinite; }
@keyframes bar { 0%{background-position:0%} 100%{background-position:200%} }
.card-body { padding: 20px 22px 24px; }
.card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 13px; }
.card-brand { display: flex; align-items: center; gap: 10px; }
.card-brand-ico { width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0; background: linear-gradient(140deg,#60a5fa,#1d4ed8); display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 12px rgba(29,78,216,.28); }
.card-brand-nm { font-family: 'Righteous', cursive; font-size: 17px; color: #1e3a8a; line-height: 1.1; }
.ac { color: #1d4ed8; }
.card-brand-sub { font-size: 9.5px; font-weight: 800; color: #9ca3af; letter-spacing: .5px; text-transform: uppercase; margin-top: 2px; }
.card-type-pill { font-size: 11px; font-weight: 900; border-radius: 99px; padding: 4px 12px; letter-spacing: .2px; }
.divider { height: 1px; background: linear-gradient(90deg,transparent,rgba(29,78,216,.1),transparent); margin-bottom: 16px; }
.questions-area { display: flex; flex-direction: column; gap: 14px; }
.question-item { border: 1.5px solid rgba(29,78,216,.07); border-radius: 14px; padding: 13px; background: #fafbfc; transition: border-color .2s, background .2s; }
.question-item--done { border-color: rgba(16,185,129,.22); background: rgba(240,253,244,.55); }
.q-label { display: flex; align-items: flex-start; gap: 8px; margin-bottom: 11px; }
.q-num { min-width: 21px; height: 21px; border-radius: 50%; color: #fff; font-weight: 900; font-size: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px; }
.q-text { flex: 1; font-size: 13px; font-weight: 700; color: #1e3a8a; line-height: 1.5; }
.empty-qs { text-align: center; padding: 28px; display: flex; flex-direction: column; align-items: center; gap: 8px; color: #94a3b8; font-size: 12px; font-weight: 700; }

/* ══ FOOTER ══ */
.footer {
  position: relative; z-index: 50;
  background: rgba(255,255,255,.18); backdrop-filter: blur(22px) saturate(1.8); -webkit-backdrop-filter: blur(22px) saturate(1.8);
  border-top: 1px solid rgba(255,255,255,.38);
  box-shadow: 0 -1px 0 rgba(0,0,0,.04), 0 -4px 16px rgba(0,0,0,.04);
  padding: 11px 0 8px; flex-shrink: 0;
}
.footer-inner { display: flex; align-items: center; gap: 10px; max-width: 1080px; margin: 0 auto; padding: 0 22px; }
.footer-pos { font-family: 'Righteous', cursive; font-size: 14px; color: #fff; flex: 1; text-align: center; text-shadow: 0 1px 4px rgba(0,0,0,.15); }
.f-btn { display: inline-flex; align-items: center; gap: 6px; height: 40px; padding: 0 18px; border: none; border-radius: 11px; font-family: 'Nunito', sans-serif; font-size: 13px; font-weight: 800; cursor: pointer; flex-shrink: 0; transition: all .18s cubic-bezier(.34,1.56,.64,1); }
.f-btn--prev { background: rgba(255,255,255,.18); color: #fff; border: 1.5px solid rgba(255,255,255,.38); backdrop-filter: blur(8px); }
.f-btn--prev:hover:not(:disabled) { background: rgba(255,255,255,.3); transform: translateY(-1px); }
.f-btn--prev:disabled { opacity: .4; cursor: not-allowed; }
.f-btn--next { background: linear-gradient(135deg,#3b82f6,#1d4ed8); color: #fff; box-shadow: 0 4px 14px rgba(29,78,216,.38); }
.f-btn--next:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(29,78,216,.48); }
.f-btn--next:disabled, .f-btn--locked { background: rgba(255,255,255,.15) !important; color: rgba(255,255,255,.45) !important; box-shadow: none !important; cursor: not-allowed; }
.f-btn--finish { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.38); padding: 0 22px; }
.f-btn--finish:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(16,185,129,.48); }
.f-btn--finish:disabled { opacity: .5; cursor: not-allowed; }
.footer-hint { text-align: center; font-size: 11px; font-weight: 800; color: #fde68a; padding: 5px 0 2px; text-shadow: 0 1px 4px rgba(0,0,0,.15); }

/* ══ CONFIRM MODAL ══ */
.modal-overlay {
  position: fixed; inset: 0; z-index: 200;
  background: rgba(0,0,0,.55);
  backdrop-filter: blur(6px);
  display: flex; align-items: center; justify-content: center;
  padding: 20px;
}
.modal {
  background: #fff; border-radius: 22px; padding: 28px 24px 22px;
  width: 100%; max-width: 360px;
  box-shadow: 0 24px 60px rgba(0,0,0,.22), 0 4px 0 rgba(29,78,216,.08);
  display: flex; flex-direction: column; align-items: center;
  gap: 12px; text-align: center;
}
.modal-title { font-family: 'Righteous', cursive; font-size: 20px; color: #1e3a8a; margin: 0; }
.modal-desc { font-size: 13px; font-weight: 600; color: #475569; line-height: 1.65; margin: 0; }
.modal-desc strong { color: #dc2626; }
.modal-actions { display: flex; gap: 9px; width: 100%; margin-top: 4px; }
.modal-btn {
  flex: 1; height: 42px; border: none; border-radius: 12px;
  font-family: 'Righteous', cursive; font-size: 13.5px;
  cursor: pointer; transition: all .18s cubic-bezier(.34,1.56,.64,1);
}
.modal-btn--cancel { background: #f1f5f9; color: #475569; border: 1.5px solid #e2e8f0; }
.modal-btn--cancel:hover:not(:disabled) { background: #e2e8f0; transform: translateY(-1px); }
.modal-btn--confirm { background: linear-gradient(135deg,#10b981,#059669); color: #fff; box-shadow: 0 4px 14px rgba(16,185,129,.35); }
.modal-btn--confirm:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(16,185,129,.45); }
.modal-btn:disabled { opacity: .55; cursor: not-allowed; }

/* Overlay: fade in/out */
.overlay-fade-enter-active { transition: opacity .25s ease; }
.overlay-fade-leave-active { transition: opacity .2s ease; }
.overlay-fade-enter-from,
.overlay-fade-leave-to { opacity: 0; }

/* Modal card: pop in, shrink out */
.modal-pop-enter-active {
  transition: opacity .3s ease, transform .35s cubic-bezier(.34,1.56,.64,1);
}
.modal-pop-leave-active {
  transition: opacity .18s ease, transform .18s ease;
}
.modal-pop-enter-from {
  opacity: 0;
  transform: scale(.82) translateY(24px);
}
.modal-pop-leave-to {
  opacity: 0;
  transform: scale(.94);
}
/* ══ MUSIC FAB ══ */
.music-fab {
   bottom: 26px; left: 26px; z-index: 301;
  width: 50px; height: 50px; border-radius: 50%; border: none;
  cursor: pointer; outline: none;
  background: rgba(255,255,255,0.18); backdrop-filter: blur(10px);
  color: #fff; box-shadow: 0 4px 20px rgba(29,78,216,0.22);
  display: flex; align-items: center; justify-content: center;
  transition: all 0.25s cubic-bezier(0.34,1.56,0.64,1);
}
.music-fab:hover { transform: scale(1.1); background: rgba(255,255,255,0.28); }
.music-fab.on {
  background: linear-gradient(135deg,#60a5fa,#1d4ed8); color: #fff;
  box-shadow: 0 6px 24px rgba(29,78,216,0.44);
}
.music-fab svg { width: 21px; height: 21px; }
.fab-pulse {
  position: absolute; inset: -5px; border-radius: 50%;
  border: 2px solid rgba(255,255,255,0.5);
  animation: fab-ring 2s ease-out infinite; pointer-events: none;
}
@keyframes fab-ring {
  0%   { transform: scale(1);    opacity: 0.8; }
  100% { transform: scale(1.55); opacity: 0; }
}

/* ── RESPONSIVE ── */
@media (max-width: 820px) {
  .body { grid-template-columns: 1fr; gap: 0; padding: 0; max-width: 100%; }
  .sidebar { flex-direction: row; flex-wrap: nowrap; align-items: center; gap: 10px; opacity: 1 !important; transform: none !important; padding: 10px 16px; background: rgba(0,0,0,.18); backdrop-filter: blur(12px); cursor: default; }
  .sb-info { flex: 1; min-width: 0; margin-bottom: 0; display: flex; flex-direction: column; gap: 4px; }
  .activity-tag { margin-bottom: 0; }
  .sb-title { font-size: 14px; margin-bottom: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .sb-sub { display: none; }
  .prog { margin-bottom: 0; }
  .prog-meta { margin-bottom: 4px; }
  .prog-bar { height: 6px; }
  .step-dots { flex-wrap: nowrap; overflow-x: auto; gap: 4px; }
  .step-dot { min-width: 24px; height: 24px; font-size: 10px; flex-shrink: 0; }
  .mascot-wrap, .bubble, .mascot-shadow { display: none; }
  .game { transform: none; opacity: 1; }
  .card { border-radius: 0; border-left: none; border-right: none; border-top: none; }
  .card-body { padding: 14px 13px 18px; }
}
@media (max-width: 480px) {
  .topbar { height: 48px; padding: 0 14px; }
  .brand-name { font-size: 16px; }
  .brand-dot { width: 26px; height: 26px; border-radius: 7px; }
  .t-btn span { display: none; }
  .t-btn { padding: 6px 9px; }
  .sidebar { padding: 8px 12px; gap: 8px; }
  .sb-title { font-size: 13px; }
  .activity-tag { font-size: 9.5px; padding: 3px 9px; }
  .card-body { padding: 12px 11px 16px; }
  .card-brand-ico { width: 30px; height: 30px; }
  .card-brand-nm { font-size: 15px; }
  .footer-inner { padding: 0 12px; gap: 7px; }
  .f-btn { height: 37px; padding: 0 12px; font-size: 12px; }
  .f-btn--finish { padding: 0 14px; }
  .footer-pos { font-size: 12px; }
}
</style>
