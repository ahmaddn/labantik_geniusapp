<script setup>
import { computed, onMounted, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  CheckCircle2, XCircle, ArrowLeft, Trophy,
  LayoutGrid, ToggleLeft, FileSearch, ClipboardList,
  ChevronRight, Star, Rocket, Zap, Award, Target,
  BookOpen, Sparkles
} from 'lucide-vue-next'

const props = defineProps({
  mission:           { type: Object,  required: true },
  results:           { type: Object,  required: true },
  user:              { type: Object,  default: () => ({ name: 'Siswa' }) },
  module:            { type: Object,  default: () => ({ id: null, name: 'Modul' }) },
  all_missions_done: { type: Boolean, default: false },
  next_mission:      { type: Object,  default: null },
})

const TYPE_META = {
  multiple_choices: { label: 'Pilihan Ganda',         color: '#3b82f6', bg: 'rgba(59,130,246,0.15)',  icon: '📝' },
  true_false:       { label: 'Pilih Gambar Yang Benar', color: '#8b5cf6', bg: 'rgba(139,92,246,0.15)', icon: '🖼️' },
  case_study:       { label: 'Studi Kasus',            color: '#06b6d4', bg: 'rgba(6,182,212,0.15)',   icon: '🔍' },
  drag_drop:        { label: 'Seret & Letakkan',       color: '#f59e0b', bg: 'rgba(245,158,11,0.15)',  icon: '✋' },
}
const tm = (t) => TYPE_META[t] || { label: t, color: '#64748b', bg: 'rgba(100,116,139,0.15)', icon: '❓' }

const scoreColor = (s) => s >= 80 ? '#10b981' : s >= 60 ? '#f59e0b' : '#ef4444'
const scoreLabel = (s) => s >= 80 ? '🎉 Luar Biasa!' : s >= 60 ? '👍 Cukup Baik!' : '💪 Ayo Semangat!'
const scoreGrade = (s) => s >= 80 ? 'A' : s >= 60 ? 'B' : s >= 40 ? 'C' : 'D'

const animatedScore = ref(0)
const showContent   = ref(false)
const particles     = ref([])

onMounted(() => {
  // generate floating particles
  particles.value = Array.from({ length: 28 }, (_, i) => ({
    id: i,
    x: Math.random() * 100,
    y: Math.random() * 100,
    size: 4 + Math.random() * 14,
    delay: Math.random() * 6,
    dur: 5 + Math.random() * 8,
    emoji: ['⭐','✨','💫','🌟','🔷','🔹','💎','🎯'][Math.floor(Math.random() * 8)],
  }))

  setTimeout(() => { showContent.value = true }, 100)

  // animate score counter
  const target = props.results.score
  let current  = 0
  const step   = target / 60
  const timer  = setInterval(() => {
    current = Math.min(current + step, target)
    animatedScore.value = Math.round(current)
    if (current >= target) clearInterval(timer)
  }, 16)
})

const incorrectByType = computed(() => {
  const groups = {}
  for (const q of props.results.questions_result || []) {
    if (!q.is_correct) {
      if (!groups[q.quiz_type]) groups[q.quiz_type] = []
      groups[q.quiz_type].push(q)
    }
  }
  return groups
})

const hasIncorrect = computed(() =>
  (props.results.questions_result || []).some(q => !q.is_correct)
)

const goToMissions    = () => router.visit(route('playground.missions.index', props.module?.id))
const goToPosttest    = () => router.visit(route('playground.posttest.show',   props.module?.id))
const goToNextMission = () => router.visit(route('playground.missions.show',   props.next_mission.id))

const dashOffset = computed(() => {
  const r = 54; const c = 2 * Math.PI * r
  return c - (c * animatedScore.value / 100)
})
</script>

<template>
  <div class="result-page">

    <!-- ── ANIMATED BACKGROUND ── -->
    <div class="bg-layer">
      <div class="bg-gradient"></div>
      <div class="bg-grid"></div>
      <div class="bg-orb orb-1"></div>
      <div class="bg-orb orb-2"></div>
      <div class="bg-orb orb-3"></div>
    </div>

    <!-- ── FLOATING PARTICLES ── -->
    <div class="particles-layer" aria-hidden="true">
      <span
        v-for="p in particles" :key="p.id"
        class="particle"
        :style="{
          left: p.x + '%', top: p.y + '%',
          fontSize: p.size + 'px',
          animationDelay: p.delay + 's',
          animationDuration: p.dur + 's',
        }"
      >{{ p.emoji }}</span>
    </div>

    <!-- ── TOPBAR ── -->
    <header class="topbar">
      <button class="back-btn" @click="goToMissions">
        <ArrowLeft :size="15" :stroke-width="2.5" />
        <span>Daftar Misi</span>
      </button>
      <div class="topbar-brand">
        <Trophy :size="16" color="#fbbf24" :stroke-width="2.5"/>
        <span>Hasil Misi</span>
      </div>
      <div class="topbar-right">
        <div class="user-badge">
          <span class="user-avatar">{{ (user?.name || 'S')[0].toUpperCase() }}</span>
          <span class="user-name">{{ user?.name || 'Siswa' }}</span>
        </div>
      </div>
    </header>

    <!-- ── MAIN CONTENT ── -->
    <main class="main" :class="{ visible: showContent }">

      <!-- ══ SCORE HERO ══ -->
      <section class="hero-card glass-card">
        <div class="hero-inner">

          <!-- Score Ring -->
          <div class="score-ring-wrap">
            <div class="ring-glow" :style="{ boxShadow: `0 0 40px ${scoreColor(results.score)}55` }"></div>
            <svg class="ring-svg" viewBox="0 0 128 128">
              <defs>
                <linearGradient id="ring-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                  <stop offset="0%" :stop-color="scoreColor(results.score)" stop-opacity="1"/>
                  <stop offset="100%" :stop-color="scoreColor(results.score)" stop-opacity="0.5"/>
                </linearGradient>
              </defs>
              <circle cx="64" cy="64" r="54" class="ring-track"/>
              <circle cx="64" cy="64" r="54" class="ring-progress"
                :style="{
                  strokeDashoffset: dashOffset,
                  stroke: `url(#ring-grad)`
                }"
              />
            </svg>
            <div class="score-center">
              <span class="grade-badge" :style="{ color: scoreColor(results.score) }">{{ scoreGrade(results.score) }}</span>
              <div class="score-num-wrap">
                <span class="score-num" :style="{ color: scoreColor(results.score) }">{{ animatedScore }}</span>
                <span class="score-pct">%</span>
              </div>
            </div>
          </div>

          <!-- Mission Info -->
          <div class="hero-info">
            <p class="hero-module">{{ module?.name }}</p>
            <h1 class="hero-mission">{{ mission.name }}</h1>
            <div class="verdict-pill" :style="{ background: scoreColor(results.score) + '22', color: scoreColor(results.score), borderColor: scoreColor(results.score) + '44' }">
              <Sparkles :size="14" :stroke-width="2.3" />
              {{ scoreLabel(results.score) }}
            </div>

            <!-- Stat Chips -->
            <div class="stat-chips">
              <div class="stat-chip chip-correct">
                <div class="chip-icon"><CheckCircle2 :size="16" :stroke-width="2.3"/></div>
                <div class="chip-body">
                  <span class="chip-val">{{ results.correct }}</span>
                  <span class="chip-lbl">Benar</span>
                </div>
              </div>
              <div class="stat-chip chip-wrong">
                <div class="chip-icon"><XCircle :size="16" :stroke-width="2.3"/></div>
                <div class="chip-body">
                  <span class="chip-val">{{ results.incorrect }}</span>
                  <span class="chip-lbl">Salah</span>
                </div>
              </div>
              <div class="stat-chip chip-total">
                <div class="chip-icon"><ClipboardList :size="16" :stroke-width="2.3"/></div>
                <div class="chip-body">
                  <span class="chip-val">{{ results.total }}</span>
                  <span class="chip-lbl">Total</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stars decoration for high score -->
        <div class="hero-stars" v-if="results.score >= 80">
          <Star v-for="i in 5" :key="i" :size="18" color="#fbbf24" fill="#fbbf24"
            :style="{ animationDelay: (i * 0.12) + 's' }" class="star-pop"/>
        </div>
      </section>

      <!-- ══ PROGRESS BAR OVERVIEW ══ -->
      <section class="overview-card glass-card" v-if="results.breakdown?.length">
        <div class="section-header">
          <div class="section-icon"><Target :size="16" :stroke-width="2.5"/></div>
          <h2 class="section-title">Rincian per Tipe Soal</h2>
        </div>

        <div class="breakdown-list">
          <div
            v-for="item in results.breakdown" :key="item.type"
            class="bk-row"
          >
            <div class="bk-meta">
              <span class="bk-emoji">{{ tm(item.type).icon }}</span>
              <div class="bk-texts">
                <span class="bk-name">{{ tm(item.type).label }}</span>
                <div class="bk-sub">
                  <span class="bk-c">✓ {{ item.correct }}</span>
                  <span class="bk-sep">/</span>
                  <span class="bk-t">{{ item.total }} soal</span>
                </div>
              </div>
              <span class="bk-pct" :style="{ color: scoreColor(item.score) }">{{ item.score }}%</span>
            </div>
            <div class="bk-track">
              <div class="bk-fill"
                :style="{ width: item.score + '%', background: `linear-gradient(90deg, ${tm(item.type).color}, ${scoreColor(item.score)})` }"
              ></div>
            </div>
          </div>
        </div>
      </section>

      <!-- ══ INCORRECT REVIEW ══ -->
      <section class="review-section" v-if="hasIncorrect">
        <div class="section-header">
          <div class="section-icon err"><XCircle :size="16" :stroke-width="2.5"/></div>
          <h2 class="section-title">Soal yang Perlu Diperbaiki</h2>
        </div>

        <div v-for="(questions, type) in incorrectByType" :key="type" class="review-group glass-card">
          <div class="rg-header" :style="{ background: tm(type).bg, borderColor: tm(type).color + '33' }">
            <span class="rg-emoji">{{ tm(type).icon }}</span>
            <span class="rg-label" :style="{ color: tm(type).color }">{{ tm(type).label }}</span>
            <span class="rg-count">{{ questions.length }} salah</span>
          </div>

          <div class="rg-questions">
            <div v-for="(q, idx) in questions" :key="q.question_id" class="rq-card">
              <div class="rq-num-badge">{{ idx + 1 }}</div>
              <div class="rq-content">
                <p class="rq-text">{{ q.question_text }}</p>

                <!-- Drag-drop type -->
                <template v-if="type === 'drag_drop'">
                  <div class="answer-block wrong-block">
                    <div class="ab-header">
                      <XCircle :size="13" color="#ef4444" :stroke-width="2.3"/>
                      <span>Jawaban Kamu</span>
                    </div>
                    <div class="dd-chips">
                      <span v-for="(groupName, itemLabel) in q.user_answer_map" :key="itemLabel" class="dd-chip wrong-chip">
                        {{ itemLabel }} → {{ groupName }}
                      </span>
                    </div>
                  </div>
                  <div class="answer-block correct-block">
                    <div class="ab-header">
                      <CheckCircle2 :size="13" color="#10b981" :stroke-width="2.3"/>
                      <span>Jawaban Benar</span>
                    </div>
                    <div class="dd-chips">
                      <span v-for="(groupName, itemLabel) in q.correct_answer_map" :key="itemLabel" class="dd-chip correct-chip">
                        {{ itemLabel }} → {{ groupName }}
                      </span>
                    </div>
                  </div>
                </template>

                <!-- Other types -->
                <template v-else>
                  <div class="answer-block wrong-block" v-if="q.user_answer_text">
                    <div class="ab-header">
                      <XCircle :size="13" color="#ef4444" :stroke-width="2.3"/>
                      <span>Jawaban Kamu</span>
                    </div>
                    <p class="ab-val wrong-val">{{ q.user_answer_text }}</p>
                  </div>
                  <div class="answer-block correct-block">
                    <div class="ab-header">
                      <CheckCircle2 :size="13" color="#10b981" :stroke-width="2.3"/>
                      <span>Jawaban Benar</span>
                    </div>
                    <p class="ab-val correct-val">{{ q.correct_answer_text }}</p>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- ══ ALL CORRECT ══ -->
      <section class="all-correct glass-card" v-else>
        <div class="ac-sparkles">✨ ⭐ 🏆 ⭐ ✨</div>
        <Trophy :size="56" color="#fbbf24" fill="#fef3c7" :stroke-width="1.5"/>
        <h2 class="ac-title">Sempurna!</h2>
        <p class="ac-sub">Semua jawaban benar! Kamu luar biasa! 🎊</p>
        <div class="ac-confetti">🎉 🎊 🥳 🎈 🎉</div>
      </section>

      <!-- ══ BOTTOM ACTIONS ══ -->
      <div class="actions-bar">
        <button class="btn btn-secondary" @click="goToMissions">
          <ArrowLeft :size="16" :stroke-width="2.5"/>
          <span>Daftar Misi</span>
        </button>

        <button v-if="all_missions_done" class="btn btn-primary btn-green" @click="goToPosttest">
          <Rocket :size="16" :stroke-width="2.5"/>
          <span>Lanjut Posttest</span>
          <div class="btn-shine"></div>
        </button>

        <button v-else-if="next_mission" class="btn btn-primary btn-amber" @click="goToNextMission">
          <span>Misi Selanjutnya</span>
          <ChevronRight :size="16" :stroke-width="2.5"/>
          <div class="btn-shine"></div>
        </button>
      </div>

    </main>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800;900&display=swap');

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

/* ══════════════════════════════════
   BASE
══════════════════════════════════ */
.result-page {
  min-height: 100vh;
  font-family: 'Nunito', sans-serif;
  background: #050d1f;
  position: relative;
  overflow-x: hidden;
  padding-bottom: 80px;
}

/* ══════════════════════════════════
   BACKGROUND
══════════════════════════════════ */
.bg-layer { position: fixed; inset: 0; z-index: 0; pointer-events: none; }

.bg-gradient {
  position: absolute; inset: 0;
  background: linear-gradient(135deg, #050d1f 0%, #0a1628 40%, #0d1f42 70%, #071020 100%);
}

.bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(59,130,246,0.07) 1px, transparent 1px),
    linear-gradient(90deg, rgba(59,130,246,0.07) 1px, transparent 1px);
  background-size: 40px 40px;
}

.bg-orb {
  position: absolute; border-radius: 50%;
  filter: blur(90px); pointer-events: none;
}
.orb-1 { width: 600px; height: 600px; top: -200px; right: -150px; background: radial-gradient(circle, rgba(59,130,246,0.35), transparent 70%); animation: orb-drift 12s ease-in-out infinite alternate; }
.orb-2 { width: 500px; height: 500px; bottom: -100px; left: -100px; background: radial-gradient(circle, rgba(99,102,241,0.25), transparent 70%); animation: orb-drift 15s ease-in-out infinite alternate-reverse; }
.orb-3 { width: 300px; height: 300px; top: 40%; left: 40%; background: radial-gradient(circle, rgba(6,182,212,0.2), transparent 70%); animation: orb-drift 9s ease-in-out infinite alternate; }

@keyframes orb-drift { from { transform: translate(0, 0) scale(1); } to { transform: translate(40px, 40px) scale(1.12); } }

/* ══════════════════════════════════
   PARTICLES
══════════════════════════════════ */
.particles-layer { position: fixed; inset: 0; z-index: 1; pointer-events: none; overflow: hidden; }

.particle {
  position: absolute;
  line-height: 1;
  animation: float-particle linear infinite;
  opacity: 0.6;
  filter: drop-shadow(0 0 6px rgba(59,130,246,0.6));
  user-select: none;
}

@keyframes float-particle {
  0%   { transform: translateY(0px) rotate(0deg) scale(1);   opacity: 0.5; }
  25%  { transform: translateY(-25px) rotate(90deg) scale(1.1); opacity: 0.8; }
  50%  { transform: translateY(-10px) rotate(180deg) scale(0.9); opacity: 0.6; }
  75%  { transform: translateY(-35px) rotate(270deg) scale(1.15); opacity: 0.9; }
  100% { transform: translateY(0px) rotate(360deg) scale(1); opacity: 0.5; }
}

/* ══════════════════════════════════
   GLASSMORPHISM CARD
══════════════════════════════════ */
.glass-card {
  background: rgba(255,255,255,0.06);
  backdrop-filter: blur(24px) saturate(1.4);
  -webkit-backdrop-filter: blur(24px) saturate(1.4);
  border: 1.5px solid rgba(255,255,255,0.12);
  border-radius: 24px;
  box-shadow:
    0 8px 40px rgba(0,0,0,0.4),
    0 1px 0 rgba(255,255,255,0.12) inset,
    0 -1px 0 rgba(0,0,0,0.2) inset;
  position: relative;
  overflow: hidden;
}
.glass-card::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 50%, rgba(255,255,255,0.02) 100%);
  pointer-events: none;
}

/* ══════════════════════════════════
   TOPBAR
══════════════════════════════════ */
.topbar {
  position: sticky; top: 0; z-index: 100;
  display: flex; align-items: center; justify-content: space-between;
  padding: 12px 20px;
  background: rgba(5,13,31,0.8);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(59,130,246,0.2);
  box-shadow: 0 2px 20px rgba(0,0,0,0.5);
}

.back-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 8px 16px;
  background: rgba(59,130,246,0.15);
  border: 1.5px solid rgba(59,130,246,0.35);
  border-radius: 12px;
  color: #93c5fd; font-family: 'Nunito', sans-serif;
  font-size: 12.5px; font-weight: 800;
  cursor: pointer; transition: all 0.2s;
}
.back-btn:hover { background: rgba(59,130,246,0.28); color: #bfdbfe; transform: translateX(-2px); }

.topbar-brand {
  display: flex; align-items: center; gap: 7px;
  font-family: 'Fredoka One', cursive; font-size: 17px; color: #e2e8f0;
  letter-spacing: 0.3px;
}

.user-badge {
  display: flex; align-items: center; gap: 8px;
  background: rgba(59,130,246,0.15); border: 1.5px solid rgba(59,130,246,0.3);
  padding: 6px 12px 6px 6px; border-radius: 50px;
}
.user-avatar {
  width: 26px; height: 26px;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border-radius: 50%; font-size: 11px; font-weight: 900; color: #fff;
  display: flex; align-items: center; justify-content: center;
}
.user-name { font-size: 12px; font-weight: 800; color: #93c5fd; max-width: 90px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* ══════════════════════════════════
   MAIN
══════════════════════════════════ */
.main {
  position: relative; z-index: 5;
  max-width: 740px; margin: 0 auto;
  padding: 28px 16px 0;
  display: flex; flex-direction: column; gap: 20px;
  opacity: 0; transform: translateY(20px);
  transition: opacity 0.5s ease, transform 0.5s ease;
}
.main.visible { opacity: 1; transform: translateY(0); }

/* ══════════════════════════════════
   HERO CARD
══════════════════════════════════ */
.hero-card { padding: 28px 24px 24px; }

.hero-inner {
  display: flex; align-items: center; gap: 28px; flex-wrap: wrap;
}

/* Score Ring */
.score-ring-wrap {
  position: relative; width: 150px; height: 150px;
  flex-shrink: 0;
}
.ring-glow {
  position: absolute; inset: 10px; border-radius: 50%;
  transition: box-shadow 0.5s;
}
.ring-svg {
  width: 100%; height: 100%;
  transform: rotate(-90deg);
  filter: drop-shadow(0 0 12px rgba(59,130,246,0.4));
}
.ring-track { fill: none; stroke: rgba(255,255,255,0.08); stroke-width: 9; }
.ring-progress {
  fill: none; stroke-width: 9; stroke-linecap: round;
  stroke-dasharray: 339.3;
  transition: stroke-dashoffset 0.05s linear;
}
.score-center {
  position: absolute; inset: 0;
  display: flex; flex-direction: column; align-items: center; justify-content: center;
}
.grade-badge { font-family: 'Fredoka One', cursive; font-size: 13px; letter-spacing: 0.5px; opacity: 0.8; }
.score-num-wrap { display: flex; align-items: flex-end; gap: 2px; }
.score-num { font-family: 'Fredoka One', cursive; font-size: 40px; line-height: 1; }
.score-pct { font-size: 16px; font-weight: 900; color: rgba(255,255,255,0.5); margin-bottom: 4px; }

/* Hero Info */
.hero-info { flex: 1; min-width: 200px; }
.hero-module { font-size: 11px; font-weight: 800; color: #60a5fa; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
.hero-mission { font-family: 'Fredoka One', cursive; font-size: 22px; color: #f1f5f9; line-height: 1.25; margin-bottom: 10px; }

.verdict-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 14px; border-radius: 50px; border: 1.5px solid;
  font-size: 13px; font-weight: 800; margin-bottom: 16px;
}

/* Stat Chips */
.stat-chips { display: flex; gap: 8px; flex-wrap: wrap; }
.stat-chip {
  display: flex; align-items: center; gap: 9px;
  padding: 8px 14px; border-radius: 14px;
  border: 1.5px solid;
  min-width: 80px;
}
.chip-icon { display: flex; }
.chip-body { display: flex; flex-direction: column; }
.chip-val { font-family: 'Fredoka One', cursive; font-size: 18px; line-height: 1; }
.chip-lbl { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }

.chip-correct { background: rgba(16,185,129,0.12); border-color: rgba(16,185,129,0.3); color: #6ee7b7; }
.chip-correct .chip-val { color: #10b981; }
.chip-wrong   { background: rgba(239,68,68,0.12);  border-color: rgba(239,68,68,0.3);  color: #fca5a5; }
.chip-wrong .chip-val   { color: #ef4444; }
.chip-total   { background: rgba(148,163,184,0.1); border-color: rgba(148,163,184,0.25); color: #94a3b8; }
.chip-total .chip-val   { color: #cbd5e1; }

/* Stars */
.hero-stars { display: flex; justify-content: center; gap: 6px; margin-top: 20px; }
.star-pop { animation: star-bounce 0.5s ease-out both; }
@keyframes star-bounce { 0% { transform: scale(0) rotate(-20deg); opacity: 0; } 60% { transform: scale(1.3) rotate(5deg); } 100% { transform: scale(1) rotate(0); opacity: 1; } }

/* ══════════════════════════════════
   SECTION HEADERS
══════════════════════════════════ */
.section-header { display: flex; align-items: center; gap: 9px; margin-bottom: 16px; }
.section-icon {
  width: 30px; height: 30px;
  background: linear-gradient(135deg, #3b82f6, #6366f1);
  border-radius: 9px; display: flex; align-items: center; justify-content: center;
  color: #fff; flex-shrink: 0;
}
.section-icon.err { background: linear-gradient(135deg, #ef4444, #f97316); }
.section-title { font-family: 'Fredoka One', cursive; font-size: 17px; color: #e2e8f0; }

/* ══════════════════════════════════
   OVERVIEW / BREAKDOWN
══════════════════════════════════ */
.overview-card { padding: 22px 20px; }

.breakdown-list { display: flex; flex-direction: column; gap: 14px; }

.bk-row { display: flex; flex-direction: column; gap: 7px; }

.bk-meta { display: flex; align-items: center; gap: 10px; }
.bk-emoji { font-size: 20px; flex-shrink: 0; }
.bk-texts { flex: 1; }
.bk-name { font-size: 13px; font-weight: 800; color: #e2e8f0; display: block; }
.bk-sub { display: flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; font-weight: 700; margin-top: 1px; }
.bk-c { color: #10b981; } .bk-sep { color: #334155; }
.bk-pct { font-family: 'Fredoka One', cursive; font-size: 20px; flex-shrink: 0; }

.bk-track { height: 8px; background: rgba(255,255,255,0.07); border-radius: 50px; overflow: hidden; }
.bk-fill { height: 100%; border-radius: 50px; transition: width 1s cubic-bezier(0.34,1.56,0.64,1); min-width: 4px; }

/* ══════════════════════════════════
   REVIEW SECTION
══════════════════════════════════ */
.review-section { display: flex; flex-direction: column; gap: 16px; }

.review-group { overflow: hidden; }

.rg-header {
  display: flex; align-items: center; gap: 10px;
  padding: 10px 16px;
  border-bottom: 1.5px solid;
}
.rg-emoji { font-size: 18px; }
.rg-label { font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.5px; flex: 1; }
.rg-count { font-size: 11px; font-weight: 800; background: rgba(0,0,0,0.2); padding: 2px 10px; border-radius: 50px; color: rgba(255,255,255,0.7); }

.rg-questions { padding: 14px; display: flex; flex-direction: column; gap: 12px; }

.rq-card {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 14px; padding: 14px;
  display: flex; gap: 12px;
  transition: background 0.2s;
}
.rq-card:hover { background: rgba(255,255,255,0.07); }

.rq-num-badge {
  width: 24px; height: 24px; flex-shrink: 0;
  background: linear-gradient(135deg, #ef4444, #f97316);
  border-radius: 50%; font-size: 11px; font-weight: 900; color: #fff;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 2px 8px rgba(239,68,68,0.4);
}

.rq-content { flex: 1; display: flex; flex-direction: column; gap: 9px; }
.rq-text { font-size: 13px; font-weight: 700; color: #cbd5e1; line-height: 1.5; }

.answer-block { border-radius: 10px; padding: 10px 12px; display: flex; flex-direction: column; gap: 6px; }
.wrong-block  { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.25); }
.correct-block { background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.25); }

.ab-header { display: flex; align-items: center; gap: 6px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.4px; }
.wrong-block .ab-header { color: #f87171; }
.correct-block .ab-header { color: #34d399; }

.ab-val { font-size: 13px; font-weight: 700; }
.wrong-val { color: #fca5a5; } .correct-val { color: #6ee7b7; }

.dd-chips { display: flex; flex-wrap: wrap; gap: 5px; }
.dd-chip { font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 6px; }
.wrong-chip   { background: rgba(239,68,68,0.2);  color: #fca5a5; }
.correct-chip { background: rgba(16,185,129,0.2); color: #6ee7b7; }

/* ══════════════════════════════════
   ALL CORRECT
══════════════════════════════════ */
.all-correct {
  padding: 48px 24px; text-align: center;
  display: flex; flex-direction: column; align-items: center; gap: 12px;
  border-color: rgba(251,191,36,0.3) !important;
}
.all-correct::before { background: linear-gradient(135deg, rgba(251,191,36,0.08) 0%, transparent 60%) !important; }
.ac-sparkles, .ac-confetti { font-size: 22px; letter-spacing: 4px; animation: sparkle-bounce 2s ease-in-out infinite; }
@keyframes sparkle-bounce { 0%,100% { transform: scale(1); } 50% { transform: scale(1.08); } }
.ac-title { font-family: 'Fredoka One', cursive; font-size: 30px; color: #fbbf24; }
.ac-sub { font-size: 14px; font-weight: 700; color: #94a3b8; }

/* ══════════════════════════════════
   ACTIONS
══════════════════════════════════ */
.actions-bar {
  display: flex; justify-content: center; gap: 12px;
  flex-wrap: wrap; padding-top: 4px;
}

.btn {
  display: inline-flex; align-items: center; gap: 8px;
  padding: 14px 28px; border-radius: 16px; border: none;
  font-family: 'Fredoka One', cursive; font-size: 15px;
  cursor: pointer; position: relative; overflow: hidden;
  transition: all 0.2s cubic-bezier(0.34,1.56,0.64,1);
  letter-spacing: 0.3px;
}
.btn:hover { transform: translateY(-3px); }
.btn:active { transform: translateY(0) scale(0.97); }

.btn-secondary {
  background: rgba(59,130,246,0.15);
  border: 1.5px solid rgba(59,130,246,0.35);
  color: #93c5fd;
  box-shadow: 0 4px 16px rgba(0,0,0,0.3);
}
.btn-secondary:hover { background: rgba(59,130,246,0.25); color: #bfdbfe; }

.btn-primary { color: #fff; box-shadow: 0 6px 24px rgba(0,0,0,0.4); }
.btn-green {
  background: linear-gradient(135deg, #10b981, #059669);
  box-shadow: 0 6px 24px rgba(16,185,129,0.4);
}
.btn-amber {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  box-shadow: 0 6px 24px rgba(245,158,11,0.4);
}

.btn-shine {
  position: absolute; inset: 0;
  background: linear-gradient(105deg, transparent 30%, rgba(255,255,255,0.25) 50%, transparent 70%);
  transform: translateX(-100%);
  animation: btn-shine 2.5s ease-in-out infinite 1s;
}
@keyframes btn-shine { 0% { transform: translateX(-100%); } 30%,100% { transform: translateX(150%); } }

/* ══════════════════════════════════
   RESPONSIVE
══════════════════════════════════ */
@media (max-width: 560px) {
  .hero-inner { flex-direction: column; align-items: center; text-align: center; }
  .hero-info { display: flex; flex-direction: column; align-items: center; }
  .stat-chips { justify-content: center; }
  .score-ring-wrap { width: 130px; height: 130px; }
  .score-num { font-size: 34px; }
  .topbar-brand span { display: none; }
  .user-name { display: none; }
  .actions-bar { flex-direction: column; align-items: stretch; }
  .btn { justify-content: center; }
  .main { padding: 20px 12px 0; }
}

@media (min-width: 640px) {
  .hero-mission { font-size: 26px; }
  .score-ring-wrap { width: 160px; height: 160px; }
  .score-num { font-size: 44px; }
}
</style>
