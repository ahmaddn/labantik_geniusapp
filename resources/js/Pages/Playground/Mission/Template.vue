<script setup>
import { ref, reactive, computed } from 'vue'
import {
  ArrowLeft,
  ArrowRight,
  CheckCircle2,
  XCircle,
  Zap,
  BookOpen,
  ClipboardList,
  LayoutGrid,
  ToggleLeft,
  FileSearch,
  ChevronRight,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'
import Multiple_choice from './Multiple_choice.vue'
import True_false from './True_false.vue'
import Case_study from './Case_study.vue'
import Materials from './Materials.vue'
import Drag_drop from './Drag_drop.vue'

// ── Component Map ─────────────────────────────────────────────
const COMPONENT_MAP = {
  multiple_choices: Multiple_choice,
  true_false: True_false,
  case_study: Case_study,
  materials: Materials,
  drag_drop: Drag_drop,
}

// ── Props ─────────────────────────────────────────────────────
const props = defineProps({
  mission: { type: Object, required: true },
  user: { type: Object, default: () => ({ name: 'Siswa' }) },
})

// ── Build flat "steps" array: each step = one quiz ────────────
// step: { type: 'material' | 'quiz', quizIndex, quiz, isMaterial }
const steps = computed(() => {
  return props.mission.quizzes.map((quiz, idx) => ({
    quizIndex: idx,
    quiz,
    isMaterial: quiz.type === 'materials',
  }))
})

// ── State ─────────────────────────────────────────────────────
const currentStep = ref(0)
const answers = reactive({})
const isSubmitting = ref(false)

// ── Current step helpers ──────────────────────────────────────
const step = computed(() => steps.value[currentStep.value])
const isFirst = computed(() => currentStep.value === 0)
const isLast = computed(() => currentStep.value === steps.value.length - 1)

// A quiz step is "answered" if all its non-material questions have answers
const isStepAnswered = computed(() => {
  if (!step.value) return false
  if (step.value.isMaterial) return true

  const quiz = step.value.quiz
  if (!quiz.questions?.length) return true

  return quiz.questions.every(q => {
    const ans = answers[q.id]
    if (ans === undefined || ans === null) return false
    if (Array.isArray(ans)) return ans.length > 0
    return ans !== ''
  })
})

const canGoNext = computed(() => {
  if (step.value?.isMaterial) return true
  return isStepAnswered.value
})

// ── Progress ──────────────────────────────────────────────────
const totalQuizSteps = computed(() => steps.value.filter(s => !s.isMaterial).length)
const answeredQuizSteps = computed(() => {
  return steps.value.filter(s => {
    if (s.isMaterial) return false
    return s.quiz.questions?.every(q => {
      const ans = answers[q.id]
      if (ans === undefined || ans === null) return false
      if (Array.isArray(ans)) return ans.length > 0
      return ans !== ''
    }) ?? true
  }).length
})

const progressPct = computed(() => {
  if (totalQuizSteps.value === 0) return 100
  return Math.round((answeredQuizSteps.value / totalQuizSteps.value) * 100)
})

// ── Answer handler ────────────────────────────────────────────
const updateAnswer = (payload) => {
  if (typeof payload === 'object' && payload.questionId !== undefined) {
    answers[payload.questionId] = payload.value
  }
}

// ── Navigation ────────────────────────────────────────────────
const goNext = () => {
  if (!isLast.value) currentStep.value++
}
const goPrev = () => {
  if (!isFirst.value) currentStep.value--
}
const goToStep = (idx) => {
  currentStep.value = idx
}

// ── Submit ────────────────────────────────────────────────────
const submit = async () => {
  if (answeredQuizSteps.value === 0) {
    alert('Silakan jawab minimal satu pertanyaan')
    return
  }
  isSubmitting.value = true

  try {
    const quizIds = props.mission.quizzes.map(q => q.id)
    const response = await fetch(route('playground.missions.submit', props.mission.id), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      },
      body: JSON.stringify({ answers, quiz_ids: quizIds }),
    })

    const data = await response.json()

    if (data.success) {
      // Redirect to the dedicated result page
      router.visit(route('playground.missions.result', props.mission.id))
    } else {
      alert('Gagal menyimpan jawaban: ' + (data.error || 'Unknown error'))
    }
  } catch (error) {
    console.error('Submission error:', error)
    alert('Terjadi kesalahan saat menyimpan jawaban')
  } finally {
    isSubmitting.value = false
  }
}

const goBack = () => router.back()

// ── Type labels & icons ───────────────────────────────────────
const TYPE_META = {
  multiple_choices: { label: 'Pilihan Ganda', color: '#3b82f6', bg: '#dbeafe', icon: 'ClipboardList' },
  true_false: { label: 'Benar / Salah', color: '#8b5cf6', bg: '#ede9fe', icon: 'ToggleLeft' },
  case_study: { label: 'Studi Kasus', color: '#0891b2', bg: '#cffafe', icon: 'FileSearch' },
  drag_drop: { label: 'Seret & Letakkan', color: '#f59e0b', bg: '#fef3c7', icon: 'LayoutGrid' },
  materials: { label: 'Materi', color: '#10b981', bg: '#dcfce7', icon: 'BookOpen' },
}

const typeMeta = (type) => TYPE_META[type] || TYPE_META.materials

// Step indicator status
const stepStatus = (idx) => {
  const s = steps.value[idx]
  if (s.isMaterial) return 'material'
  if (idx === currentStep.value) return 'active'
  const allAnswered = s.quiz.questions?.every(q => {
    const ans = answers[q.id]
    if (ans === undefined || ans === null) return false
    if (Array.isArray(ans)) return ans.length > 0
    return ans !== ''
  }) ?? true
  return allAnswered ? 'done' : 'pending'
}

const scoreColor = (s) => s >= 80 ? '#16a34a' : s >= 60 ? '#ca8a04' : '#dc2626'
</script>

<template>
  <div class="template-page">
    <!-- ══ HEADER ══ -->
    <header class="template-header">
      <div class="header-wrap">
        <div class="header-top">
          <button class="back-btn" @click="goBack" :disabled="isSubmitting">
            <ArrowLeft :size="18" :stroke-width="2.5" />
          </button>
          <div class="header-center">
            <h1 class="mission-title">{{ mission.name }}</h1>
            <p class="mission-step-label">
              <span v-if="step?.isMaterial">📖 Materi</span>
              <span v-else>Kuis {{ step?.quizIndex + 1 }} · {{ step?.quiz?.title }}</span>
            </p>
          </div>
          <div class="prog-badge">{{ progressPct }}%</div>
        </div>

        <!-- Progress bar -->
        <div class="prog-bar-wrap">
          <div class="prog-bar">
            <div class="prog-fill" :style="{ width: progressPct + '%' }"></div>
          </div>
          <span class="prog-detail">{{ answeredQuizSteps }}/{{ totalQuizSteps }} selesai</span>
        </div>
      </div>
    </header>

    <!-- ══ STEP NAVIGATOR ══ -->
    <div class="step-nav-wrap">
      <div class="step-nav">
        <button
          v-for="(s, idx) in steps"
          :key="idx"
          class="step-dot"
          :class="stepStatus(idx)"
          @click="goToStep(idx)"
          :title="s.isMaterial ? 'Materi' : s.quiz.title"
        >
          <span v-if="stepStatus(idx) === 'done'" class="dot-check">✓</span>
          <span v-else-if="s.isMaterial" class="dot-icon">📖</span>
          <span v-else>{{ idx + 1 }}</span>
        </button>
      </div>
    </div>

    <!-- ══ CONTENT ══ -->
    <main class="template-content">
      <div class="content-wrap" v-if="step">
        <!-- Quiz card -->
        <div class="quiz-card">
          <!-- Card header -->
          <div class="card-header" :style="{ borderColor: typeMeta(step.quiz.type).color + '44' }">
            <div class="card-type-badge" :style="{ background: typeMeta(step.quiz.type).bg, color: typeMeta(step.quiz.type).color }">
              <BookOpen v-if="step.isMaterial" :size="13" :stroke-width="2.2" />
              <ClipboardList v-else-if="step.quiz.type === 'multiple_choices'" :size="13" :stroke-width="2.2" />
              <ToggleLeft v-else-if="step.quiz.type === 'true_false'" :size="13" :stroke-width="2.2" />
              <FileSearch v-else-if="step.quiz.type === 'case_study'" :size="13" :stroke-width="2.2" />
              <LayoutGrid v-else :size="13" :stroke-width="2.2" />
              {{ typeMeta(step.quiz.type).label }}
            </div>
            <span class="card-title">{{ step.quiz.title }}</span>
          </div>

          <!-- Questions -->
          <div class="questions-area">
            <div
              v-for="(question, qIdx) in step.quiz.questions || []"
              :key="question.id"
              class="question-item"
              :class="{ answered: answers[question.id] !== undefined && answers[question.id] !== null && (Array.isArray(answers[question.id]) ? answers[question.id].length > 0 : true) }"
            >
              <div class="question-header" v-if="!step.isMaterial">
                <span class="q-num" :style="{ background: typeMeta(step.quiz.type).color }">{{ qIdx + 1 }}</span>
                <span class="q-text">{{ question.question_text }}</span>
                <CheckCircle2
                  v-if="answers[question.id] !== undefined && answers[question.id] !== null && (Array.isArray(answers[question.id]) ? answers[question.id].length > 0 : true)"
                  :size="16"
                  color="#10b981"
                  :stroke-width="2.5"
                  class="q-check"
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
              <Zap :size="28" color="#94a3b8" :stroke-width="1.4" />
              <p>Tidak ada soal</p>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- ══ FOOTER NAV ══ -->
    <footer class="template-footer">
      <div class="footer-wrap">
        <button class="btn-nav btn-prev" @click="goPrev" :disabled="isFirst || isSubmitting">
          <ArrowLeft :size="16" :stroke-width="2.5" />
          Sebelumnya
        </button>

        <div class="footer-center">
          <span class="footer-step-info">{{ currentStep + 1 }} / {{ steps.length }}</span>
        </div>

        <!-- Last step: submit button -->
        <template v-if="isLast">
          <button
            class="btn-nav btn-submit-final"
            @click="submit"
            :disabled="isSubmitting || answeredQuizSteps === 0"
          >
            <span v-if="!isSubmitting">Selesaikan Misi 🎯</span>
            <span v-else>Menyimpan...</span>
          </button>
        </template>
        <template v-else>
          <button
            class="btn-nav btn-next"
            @click="goNext"
            :disabled="!canGoNext || isSubmitting"
            :class="{ 'btn-next-locked': !canGoNext }"
          >
            Selanjutnya
            <ArrowRight :size="16" :stroke-width="2.5" />
          </button>
        </template>
      </div>

      <!-- hint if locked -->
      <div v-if="!canGoNext && !step?.isMaterial" class="footer-hint">
        ⚠️ Jawab semua soal terlebih dahulu untuk melanjutkan
      </div>
    </footer>
  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.template-page {
  min-height: 100vh;
  font-family: 'Nunito', sans-serif;
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  display: flex;
  flex-direction: column;
}

/* ── HEADER ── */
.template-header {
  position: sticky;
  top: 0;
  z-index: 50;
  background: rgba(255,255,255,0.94);
  backdrop-filter: blur(14px);
  border-bottom: 1px solid rgba(29,78,216,0.1);
  box-shadow: 0 2px 14px rgba(0,0,0,0.07);
  padding: 12px 0;
}
.header-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.header-top {
  display: flex;
  align-items: center;
  gap: 12px;
}
.back-btn {
  width: 36px; height: 36px;
  border-radius: 10px;
  border: 1.5px solid rgba(29,78,216,0.18);
  background: rgba(29,78,216,0.04);
  color: #1d4ed8;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
  transition: all 0.17s;
}
.back-btn:hover:not(:disabled) { background: rgba(29,78,216,0.12); }
.back-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.header-center {
  flex: 1;
  min-width: 0;
}
.mission-title {
  font-family: 'Righteous', cursive;
  font-size: clamp(14px, 2.2vw, 18px);
  color: #1e3a8a;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.mission-step-label {
  font-size: 11px;
  font-weight: 700;
  color: #6b7280;
  margin-top: 2px;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.prog-badge {
  font-family: 'Righteous', cursive;
  font-size: 15px;
  color: #1d4ed8;
  min-width: 44px;
  text-align: right;
  flex-shrink: 0;
}
.prog-bar-wrap {
  display: flex;
  align-items: center;
  gap: 10px;
}
.prog-bar {
  flex: 1;
  height: 5px;
  border-radius: 10px;
  background: rgba(29,78,216,0.1);
  overflow: hidden;
}
.prog-fill {
  height: 100%;
  background: linear-gradient(90deg, #3b82f6, #1d4ed8);
  border-radius: 10px;
  transition: width 0.4s ease;
  box-shadow: 0 0 6px rgba(29,78,216,0.35);
}
.prog-detail {
  font-size: 10.5px;
  font-weight: 800;
  color: #6b7280;
  white-space: nowrap;
  flex-shrink: 0;
}

/* ── STEP NAVIGATOR ── */
.step-nav-wrap {
  background: rgba(255,255,255,0.6);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid rgba(29,78,216,0.07);
  padding: 10px 0;
  overflow-x: auto;
}
.step-nav {
  display: flex;
  align-items: center;
  gap: 6px;
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px;
}
.step-dot {
  min-width: 32px; height: 32px;
  border-radius: 50%;
  border: 2px solid #e2e8f0;
  background: #fff;
  font-size: 12px;
  font-weight: 900;
  color: #94a3b8;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
  flex-shrink: 0;
}
.step-dot:hover { transform: scale(1.15); }
.step-dot.active {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  border-color: #1d4ed8;
  color: #fff;
  box-shadow: 0 3px 10px rgba(29,78,216,0.35);
  transform: scale(1.15);
}
.step-dot.done {
  background: #dcfce7;
  border-color: #4ade80;
  color: #16a34a;
}
.step-dot.material {
  background: #f0fdf4;
  border-color: #86efac;
  color: #16a34a;
  font-size: 13px;
}
.dot-check { font-size: 13px; }
.dot-icon { font-size: 14px; }

/* ── CONTENT ── */
.template-content {
  flex: 1;
  padding: 20px 0 0;
  overflow-y: auto;
}
.content-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px 20px;
}

.quiz-card {
  background: #fff;
  border: 1.5px solid rgba(29,78,216,0.1);
  border-radius: 18px;
  overflow: hidden;
  box-shadow: 0 4px 20px rgba(0,0,0,0.07);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 14px 16px;
  border-bottom: 1.5px solid;
  background: linear-gradient(135deg, rgba(59,130,246,0.02), rgba(29,78,216,0.01));
}
.card-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  font-size: 10.5px;
  font-weight: 900;
  border-radius: 6px;
  padding: 4px 9px;
  flex-shrink: 0;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}
.card-title {
  font-family: 'Righteous', cursive;
  font-size: 14px;
  color: #1e3a8a;
  flex: 1;
  min-width: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.questions-area {
  padding: 16px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.question-item {
  border-radius: 12px;
  border: 1.5px solid rgba(29,78,216,0.07);
  padding: 14px;
  background: #fafbfc;
  transition: border-color 0.2s;
}
.question-item.answered {
  border-color: rgba(16,185,129,0.25);
  background: rgba(240,253,244,0.4);
}

.question-header {
  display: flex;
  align-items: flex-start;
  gap: 9px;
  margin-bottom: 12px;
}
.q-num {
  min-width: 22px; height: 22px;
  border-radius: 50%;
  color: #fff;
  font-weight: 900;
  font-size: 10.5px;
  display: flex; align-items: center; justify-content: center;
  flex-shrink: 0;
}
.q-text {
  flex: 1;
  font-size: 13.5px;
  font-weight: 700;
  color: #1e3a8a;
  line-height: 1.5;
}
.q-check { flex-shrink: 0; margin-top: 2px; }

.empty-qs {
  text-align: center;
  padding: 30px;
  display: flex; flex-direction: column; align-items: center; gap: 8px;
  color: #94a3b8;
  font-size: 13px; font-weight: 700;
}

/* ── FOOTER ── */
.template-footer {
  background: rgba(255,255,255,0.94);
  backdrop-filter: blur(12px);
  border-top: 1px solid rgba(29,78,216,0.1);
  padding: 12px 0 8px;
  box-shadow: 0 -2px 12px rgba(0,0,0,0.07);
  flex-shrink: 0;
}
.footer-wrap {
  max-width: 860px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.footer-center {
  flex: 1;
  text-align: center;
}
.footer-step-info {
  font-family: 'Righteous', cursive;
  font-size: 14px;
  color: #6b7280;
}
.btn-nav {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  height: 40px;
  padding: 0 18px;
  border: none;
  border-radius: 10px;
  font-family: 'Righteous', cursive;
  font-size: 13px;
  cursor: pointer;
  transition: all 0.17s cubic-bezier(0.34,1.56,0.64,1);
  flex-shrink: 0;
}
.btn-prev {
  background: rgba(29,78,216,0.07);
  color: #1d4ed8;
  border: 1.5px solid rgba(29,78,216,0.18);
}
.btn-prev:hover:not(:disabled) {
  background: rgba(29,78,216,0.14);
}
.btn-prev:disabled { opacity: 0.45; cursor: not-allowed; }
.btn-next {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: #fff;
  box-shadow: 0 3px 10px rgba(29,78,216,0.3);
}
.btn-next:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 14px rgba(29,78,216,0.4);
}
.btn-next:disabled, .btn-next-locked {
  background: #e2e8f0 !important;
  color: #94a3b8 !important;
  box-shadow: none !important;
  cursor: not-allowed;
}
.btn-submit-final {
  background: linear-gradient(135deg, #10b981, #059669);
  color: #fff;
  box-shadow: 0 3px 10px rgba(16,185,129,0.3);
  padding: 0 20px;
}
.btn-submit-final:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 5px 14px rgba(16,185,129,0.4);
}
.btn-submit-final:disabled { opacity: 0.5; cursor: not-allowed; }

.footer-hint {
  text-align: center;
  font-size: 11px;
  font-weight: 700;
  color: #f59e0b;
  padding: 5px 0 2px;
}

/* ── RESULTS MODAL ── */
.results-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.55);
  backdrop-filter: blur(5px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 200;
  padding: 16px;
}
.results-modal {
  background: #fff;
  border-radius: 22px;
  padding: 28px 22px;
  max-width: 440px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 24px 64px rgba(0,0,0,0.22);
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.results-header {
  text-align: center;
}
.results-icon-wrap { margin-bottom: 10px; display: flex; justify-content: center; }
.results-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
}
.results-score-big {
  font-family: 'Righteous', cursive;
  font-size: 28px;
  font-weight: 900;
}
.results-score-pct { font-size: 14px; }
.results-title {
  font-family: 'Righteous', cursive;
  font-size: 22px;
  color: #1e293b;
  margin-bottom: 4px;
}
.results-subtitle { font-size: 13px; color: #64748b; }

.results-summary-row {
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  border-radius: 14px;
  border: 1.5px solid #e2e8f0;
  overflow: hidden;
}
.rs-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
  padding: 14px 8px;
}
.rs-val {
  font-family: 'Righteous', cursive;
  font-size: 22px;
  color: #1e293b;
}
.rs-lbl { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.4px; }
.rs-sep { width: 1px; background: #e2e8f0; align-self: stretch; }

/* Breakdown */
.breakdown-section { display: flex; flex-direction: column; gap: 8px; }
.breakdown-title {
  font-size: 11.5px; font-weight: 900; color: #64748b;
  text-transform: uppercase; letter-spacing: 0.5px;
}
.breakdown-list { display: flex; flex-direction: column; gap: 7px; }
.breakdown-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 10px 12px;
  border-radius: 10px;
  border: 1.5px solid;
}
.bk-left { display: flex; flex-direction: column; gap: 5px; flex: 1; min-width: 0; }
.bk-badge {
  display: inline-flex; align-items: center; gap: 4px;
  font-size: 10px; font-weight: 900; border-radius: 6px;
  padding: 2px 8px; width: fit-content;
  text-transform: uppercase; letter-spacing: 0.3px;
}
.bk-counts { display: flex; align-items: center; gap: 4px; }
.bk-c { font-size: 11px; font-weight: 800; }
.bk-sep-sm { color: #cbd5e1; font-size: 11px; }
.bk-right { display: flex; flex-direction: column; align-items: flex-end; gap: 5px; flex-shrink: 0; }
.bk-score { font-family: 'Righteous', cursive; font-size: 16px; font-weight: 900; }
.bk-mini-bar { width: 72px; height: 5px; background: rgba(0,0,0,0.06); border-radius: 10px; overflow: hidden; }
.bk-mini-fill { height: 100%; border-radius: 10px; transition: width 0.5s ease; }

.btn-finish {
  width: 100%;
  height: 44px;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: #fff;
  font-family: 'Righteous', cursive;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.17s cubic-bezier(0.34,1.56,0.64,1);
  box-shadow: 0 4px 12px rgba(29,78,216,0.3);
}
.btn-finish:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(29,78,216,0.4); }

/* modal transition */
.modal-enter-active { transition: all 0.28s cubic-bezier(0.34,1.56,0.64,1); }
.modal-leave-active { transition: all 0.18s ease; }
.modal-enter-from, .modal-leave-to { opacity: 0; }
.modal-enter-from .results-modal { transform: translateY(40px) scale(0.95); }

@media (max-width: 600px) {
  .content-wrap { padding: 0 12px 16px; }
  .btn-nav { padding: 0 13px; font-size: 12px; }
  .footer-step-info { font-size: 12px; }
  .results-modal { padding: 22px 16px; }
}
@media (max-width: 420px) {
  .step-dot { min-width: 28px; height: 28px; font-size: 11px; }
  .btn-nav { height: 36px; }
}
</style>
