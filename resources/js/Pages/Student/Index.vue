<template>
  <PlaygroundLayout :siswa="siswa">
    <div class="index-page">

      <!-- Hero -->
      <section class="hero">
        <div class="mascot-side">
          <div class="mascot-emoji">💧</div>
          <div class="speech-bubble">
            Hai, <strong>{{ firstName }}</strong>! 👋<br>
            Mau petualangan apa hari ini?
          </div>
        </div>
        <div class="hero-info">
          <h1 class="page-title">Pilih Modul Petualangan</h1>
          <p class="page-sub">Setiap modul adalah petualangan baru. Selesaikan untuk kumpulkan bintang! ⭐</p>
          <div class="stats-row">
            <div class="stat-chip">🏆 {{ completedCount }} Selesai</div>
            <div class="stat-chip">⭐ {{ totalStars }} Bintang</div>
            <div class="stat-chip">🔥 {{ streak }} Hari Streak</div>
          </div>
        </div>
      </section>

      <!-- Filter -->
      <div class="filter-bar">
        <button
          v-for="tab in tabs"
          :key="tab.value"
          class="filter-btn"
          :class="{ active: activeTab === tab.value }"
          @click="activeTab = tab.value"
        >
          {{ tab.icon }} {{ tab.label }}
          <span class="tab-count">{{ countByStatus(tab.value) }}</span>
        </button>
      </div>

      <!-- Grid -->
      <div class="module-grid">
        <template v-if="filteredModules.length > 0">
          <div
            v-for="mod in filteredModules"
            :key="mod.id"
            class="mod-card"
            :class="[`s-${mod.status}`, { clickable: mod.status === 'aktif' }]"
            @click="handleCardClick(mod)"
          >
            <div v-if="mod.status !== 'aktif'" class="ribbon" :class="`ribbon-${mod.status}`">
              {{ mod.status === 'segera' ? '🔒 Segera' : '✅ Selesai' }}
            </div>
            <div class="card-top" :style="{ background: mod.color }">
              <div class="mod-emoji">{{ mod.icon }}</div>
              <div v-if="mod.status === 'aktif' && mod.progress > 0" class="prog-pill">{{ mod.progress }}%</div>
              <div v-if="mod.status === 'selesai'" class="stars-wrap">
                <span v-for="n in 3" :key="n" :class="n <= mod.stars ? '' : 'star-off'">⭐</span>
              </div>
            </div>
            <div class="card-body">
              <span class="subject-tag">{{ mod.subject }}</span>
              <h3 class="mod-title">{{ mod.title }}</h3>
              <p class="mod-desc">{{ mod.description }}</p>
              <div class="meta-row">
                <span>🕐 {{ mod.duration }}</span>
                <span>🎯 Kelas {{ mod.grade }}</span>
              </div>
              <div v-if="mod.status === 'aktif'" class="prog-bar-wrap">
                <div class="prog-bar"><div class="prog-fill" :style="{ width: mod.progress + '%' }"></div></div>
                <span class="prog-label">{{ mod.progress > 0 ? mod.progress + '% selesai' : 'Belum dimulai' }}</span>
              </div>
              <button class="cta-btn" :class="`cta-${mod.status}`" :disabled="mod.status === 'segera'">
                <span v-if="mod.status === 'aktif'">{{ mod.progress > 0 ? '▶ Lanjutkan' : '🚀 Mulai' }}</span>
                <span v-else-if="mod.status === 'segera'">🔒 Segera Hadir</span>
                <span v-else>🔄 Main Lagi</span>
              </button>
            </div>
          </div>
        </template>
        <div v-else class="empty-state">
          <span>🔍</span>
          <p>Tidak ada modul di kategori ini.</p>
        </div>
      </div>

    </div>
  </PlaygroundLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import PlaygroundLayout from '@/Layouts/PlaygroundLayout.vue'

const props = defineProps({
  modules:        { type: Array,  default: () => [] },
  completedCount: { type: Number, default: 0 },
  totalStars:     { type: Number, default: 0 },
  streak:         { type: Number, default: 0 },
  siswa:          { type: Object, default: () => ({ name: 'Siswa', kelas: '', xp: 0 }) },
})

const firstName = computed(() => (props.siswa?.name ?? 'Siswa').split(' ')[0])
const activeTab = ref('semua')

const tabs = [
  { value: 'semua',   label: 'Semua',        icon: '📚' },
  { value: 'aktif',   label: 'Aktif',        icon: '✅' },
  { value: 'segera',  label: 'Segera Hadir', icon: '🔒' },
  { value: 'selesai', label: 'Selesai',      icon: '🏆' },
]

const filteredModules = computed(() =>
  activeTab.value === 'semua' ? props.modules : props.modules.filter(m => m.status === activeTab.value)
)

function countByStatus(val) {
  if (val === 'semua') return props.modules.length
  return props.modules.filter(m => m.status === val).length
}

function handleCardClick(mod) {
  if (mod.status !== 'aktif') return
  router.visit(route('student.module.show', mod.slug))
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Baloo+2:wght@700;800&display=swap');

.index-page { max-width: 1200px; margin: 0 auto; padding: 2rem 1.5rem 5rem; font-family: 'Nunito', sans-serif; }

.hero { display: flex; align-items: center; gap: 2rem; background: rgba(255,255,255,0.76); border: 2.5px solid rgba(168,216,240,0.7); border-radius: 24px; padding: 1.8rem 2.5rem; margin-bottom: 2rem; box-shadow: 0 8px 32px rgba(100,180,240,0.16); backdrop-filter: blur(8px); }
.mascot-side { display: flex; align-items: flex-start; gap: 0.8rem; flex-shrink: 0; }
.mascot-emoji { font-size: 4.5rem; animation: mascotBob 2.8s ease-in-out infinite; filter: drop-shadow(0 4px 12px rgba(30,140,220,0.3)); }
@keyframes mascotBob { 0%,100% { transform: translateY(0) rotate(-3deg); } 50% { transform: translateY(-10px) rotate(3deg); } }
.speech-bubble { background: #fff; border: 2.5px solid #a8d8f0; border-radius: 16px 16px 16px 0; padding: 0.8rem 1.1rem; font-size: 0.92rem; color: #1a5a8a; font-weight: 600; line-height: 1.55; box-shadow: 0 4px 12px rgba(100,180,240,0.15); max-width: 210px; }
.hero-info { flex: 1; }
.page-title { font-family: 'Baloo 2', cursive; font-size: 1.95rem; font-weight: 800; color: #1a4f78; margin-bottom: 0.35rem; line-height: 1.2; }
.page-sub { color: #4a7c9e; font-size: 0.95rem; font-weight: 600; margin-bottom: 1rem; }
.stats-row { display: flex; gap: 0.7rem; flex-wrap: wrap; }
.stat-chip { background: linear-gradient(135deg, #e0f4ff, #cceeff); border: 2px solid #a8d8f0; color: #1a6fa3; font-weight: 800; font-size: 0.82rem; padding: 0.3rem 0.9rem; border-radius: 99px; }

.filter-bar { display: flex; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 1.6rem; }
.filter-btn { display: flex; align-items: center; gap: 0.4rem; padding: 0.48rem 1.1rem; border-radius: 99px; border: 2.5px solid #a8d8f0; background: rgba(255,255,255,0.72); color: #4a7c9e; font-family: 'Nunito', sans-serif; font-weight: 700; font-size: 0.88rem; cursor: pointer; transition: all 0.18s; }
.filter-btn:hover { border-color: #4ec9ff; color: #1a6fa3; }
.filter-btn.active { background: #1a6fa3; border-color: #1a6fa3; color: #fff; box-shadow: 0 4px 12px rgba(26,111,163,0.28); }
.tab-count { background: #d4ecf7; color: #1a6fa3; border-radius: 99px; padding: 0 0.45rem; font-size: 0.76rem; font-weight: 800; min-width: 20px; text-align: center; }
.filter-btn.active .tab-count { background: rgba(255,255,255,0.3); color: #fff; }

.module-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(275px, 1fr)); gap: 1.5rem; }

.mod-card { border-radius: 20px; background: #fff; border: 2.5px solid #d4ecf7; box-shadow: 0 6px 22px rgba(100,180,240,0.12); overflow: hidden; position: relative; transition: transform 0.24s, box-shadow 0.24s, border-color 0.24s; display: flex; flex-direction: column; }
.mod-card.clickable { cursor: pointer; }
.mod-card.clickable:hover { transform: translateY(-6px) scale(1.02); box-shadow: 0 18px 42px rgba(30,120,200,0.22); border-color: #4ec9ff; }
.mod-card.s-segera { opacity: 0.72; filter: grayscale(18%); }

.ribbon { position: absolute; top: 13px; right: -8px; font-size: 0.7rem; font-weight: 800; color: #fff; padding: 0.25rem 1.2rem 0.25rem 0.8rem; border-radius: 4px 0 0 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.18); z-index: 3; }
.ribbon-segera { background: #f0ac00; }
.ribbon-selesai { background: #2ecc71; }

.card-top { height: 125px; display: flex; align-items: center; justify-content: center; position: relative; flex-shrink: 0; }
.mod-emoji { font-size: 3.8rem; filter: drop-shadow(0 4px 10px rgba(0,0,0,0.18)); animation: iconBob 3s ease-in-out infinite; }
@keyframes iconBob { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
.prog-pill { position: absolute; bottom: 8px; right: 10px; background: rgba(255,255,255,0.92); color: #1a6fa3; font-weight: 800; font-size: 0.74rem; padding: 0.18rem 0.6rem; border-radius: 99px; border: 2px solid #4ec9ff; }
.stars-wrap { position: absolute; bottom: 8px; right: 10px; display: flex; gap: 1px; font-size: 1rem; }
.star-off { filter: grayscale(1) brightness(1.7); }

.card-body { padding: 1rem 1.15rem 1.15rem; display: flex; flex-direction: column; flex: 1; }
.subject-tag { display: inline-block; background: #e8f4ff; color: #1a6fa3; font-weight: 800; font-size: 0.66rem; text-transform: uppercase; letter-spacing: 0.06em; padding: 0.18rem 0.65rem; border-radius: 99px; border: 1.5px solid #bee6ff; margin-bottom: 0.45rem; align-self: flex-start; }
.mod-title { font-family: 'Baloo 2', cursive; font-weight: 800; font-size: 1rem; color: #1a3a5c; line-height: 1.3; margin-bottom: 0.3rem; }
.mod-desc { font-size: 0.8rem; color: #6a90a8; font-weight: 600; line-height: 1.45; margin-bottom: 0.65rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.meta-row { display: flex; gap: 0.7rem; font-size: 0.76rem; color: #7aaccf; font-weight: 700; margin-bottom: 0.7rem; }

.prog-bar-wrap { margin-bottom: 0.85rem; }
.prog-bar { height: 8px; background: #e0f0ff; border-radius: 99px; overflow: hidden; margin-bottom: 0.28rem; }
.prog-fill { height: 100%; background: linear-gradient(90deg, #4ec9ff, #1a9aff); border-radius: 99px; transition: width 0.6s ease; min-width: 4px; }
.prog-label { font-size: 0.73rem; color: #4a9ac4; font-weight: 700; }

.cta-btn { margin-top: auto; width: 100%; padding: 0.58rem; border-radius: 12px; border: none; font-family: 'Nunito', sans-serif; font-weight: 800; font-size: 0.86rem; cursor: pointer; transition: all 0.2s; }
.cta-btn:disabled { cursor: not-allowed; }
.cta-aktif { background: linear-gradient(135deg, #4ec9ff, #1a7fd4); color: #fff; box-shadow: 0 4px 14px rgba(26,127,212,0.32); }
.cta-aktif:hover { filter: brightness(1.08); transform: translateY(-1px); }
.cta-segera { background: #f0f4f8; color: #9ab8cc; }
.cta-selesai { background: linear-gradient(135deg, #7ae97a, #2ecc71); color: #fff; box-shadow: 0 4px 12px rgba(46,204,113,0.28); }

.empty-state { grid-column: 1 / -1; display: flex; flex-direction: column; align-items: center; padding: 4rem; gap: 0.6rem; color: #7aaccf; font-weight: 700; font-size: 1rem; }
.empty-state span { font-size: 2.8rem; }

@media (max-width: 640px) {
  .hero { flex-direction: column; text-align: center; }
  .mascot-side { flex-direction: column; align-items: center; }
  .speech-bubble { border-radius: 16px; }
  .stats-row { justify-content: center; }
  .page-title { font-size: 1.5rem; }
  .module-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 400px) { .module-grid { grid-template-columns: 1fr; } }
</style>
