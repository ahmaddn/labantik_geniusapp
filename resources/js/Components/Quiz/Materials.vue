<script setup>
import { ref, onUnmounted, computed } from 'vue'
import { BookOpen, Music, Video, CloudRain, Droplets } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
})

const sliderValue = ref(50)

const conceptualData = computed(() => {
  if (props.question?.layout_type !== 'conceptual_systematic') return null
  try {
    return JSON.parse(props.question.content)
  } catch (e) {
    return null
  }
})

const isVideo = (path) => {
  if (!path) return false
  const ext = path.split('?')[0].split('.').pop().toLowerCase()
  return ['mp4', 'webm', 'ogg', 'mov', 'avi'].includes(ext)
}

const imageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const base = window.location.origin
  const clean = path.startsWith('/') ? path : `/storage/${path}`
  return `${base}${clean}`
}

// ── Canvas draw helper ──
const makeDrawLoop = (videoRef, canvasRef) => {
  let rafId = null
  const draw = () => {
    const video = videoRef.value
    const canvas = canvasRef.value
    if (!video || !canvas) return
    const ctx = canvas.getContext('2d')
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
    if (!video.paused && !video.ended) {
      rafId = requestAnimationFrame(draw)
    }
  }
  const start = () => {
    cancelAnimationFrame(rafId)
    draw()
  }
  const stop = () => {
    cancelAnimationFrame(rafId)
    // draw one last frame so it doesn't freeze on black
    const video = videoRef.value
    const canvas = canvasRef.value
    if (!video || !canvas) return
    const ctx = canvas.getContext('2d')
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  }
  const cleanup = () => cancelAnimationFrame(rafId)
  return { start, stop, cleanup }
}

// ── Banner player ──
const bannerVideoEl = ref(null)
const bannerCanvasEl = ref(null)
const bannerPlaying = ref(false)
const bannerLoop = ref(null)

const onBannerLoaded = () => {
  bannerLoop.value = makeDrawLoop(bannerVideoEl, bannerCanvasEl)
}

const toggleBannerPlay = () => {
  if (!bannerVideoEl.value) return
  if (bannerPlaying.value) {
    bannerVideoEl.value.pause()
  } else {
    bannerVideoEl.value.play()
  }
  bannerPlaying.value = !bannerPlaying.value
}

const onBannerPlay = () => {
  bannerPlaying.value = true
  bannerLoop.value?.start()
}
const onBannerPause = () => {
  bannerPlaying.value = false
  bannerLoop.value?.stop()
}
const onBannerSeeked = () => {
  // redraw immediately on seek so canvas doesn't lag
  const video = bannerVideoEl.value
  const canvas = bannerCanvasEl.value
  if (!video || !canvas) return
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  if (!video.paused) bannerLoop.value?.start()
}

// ── Content player ──
const videoEl = ref(null)
const canvasEl = ref(null)
const playing = ref(false)
const loop = ref(null)

const onLoaded = () => {
  loop.value = makeDrawLoop(videoEl, canvasEl)
}

const togglePlay = () => {
  if (!videoEl.value) return
  if (playing.value) {
    videoEl.value.pause()
  } else {
    videoEl.value.play()
  }
  playing.value = !playing.value
}

const onPlay = () => {
  playing.value = true
  loop.value?.start()
}
const onPause = () => {
  playing.value = false
  loop.value?.stop()
}
const onSeeked = () => {
  const video = videoEl.value
  const canvas = canvasEl.value
  if (!video || !canvas) return
  const ctx = canvas.getContext('2d')
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height)
  if (!video.paused) loop.value?.start()
}

onUnmounted(() => {
  loop.value?.cleanup()
  bannerLoop.value?.cleanup()
})
</script>

<template>
  <div v-if="props.question?.layout_type === 'conceptual_systematic'" class="cs-container">
    <h2 class="cs-title uppercase">
        {{ props.question.title }}
    </h2>

    <div class="cs-grid">
      <!-- Left Texts -->
      <div class="cs-texts-left">
        <div class="cs-text-box">
          <p>{{ conceptualData?.topLeft }}</p>
          <div class="cs-arrow right"></div>
        </div>
        <div class="cs-text-box">
          <p>{{ conceptualData?.bottomLeft }}</p>
          <div class="cs-arrow right"></div>
        </div>
      </div>

      <!-- Center Image -->
      <div class="cs-image-wrap">
        <img :src="imageUrl(props.question.image)" alt="Concept" class="cs-center-img" />
      </div>

      <!-- Right Texts -->
      <div class="cs-texts-right">
        <div class="cs-text-box">
          <div class="cs-arrow left"></div>
          <p>{{ conceptualData?.topRight }}</p>
        </div>
        <div class="cs-text-box">
          <div class="cs-arrow left"></div>
          <p>{{ conceptualData?.bottomRight }}</p>
        </div>
      </div>
    </div>

    <!-- Slider Area -->
    <div class="cs-slider-area">
      <div class="cs-slider-wrap">
        <div class="cs-slider-icon bg-blue-500"><CloudRain class="w-5 h-5 text-white" /></div>
        <span class="cs-slider-label">{{ conceptualData?.sliderMin || 'Ringan' }}</span>
        <input type="range" min="0" max="100" v-model="sliderValue" class="cs-slider" />
        <span class="cs-slider-label">{{ conceptualData?.sliderMax || 'Deras' }}</span>
        <div class="cs-slider-icon bg-blue-700"><Droplets class="w-5 h-5 text-white" /></div>
      </div>
    </div>

    <!-- Metrics Area -->
    <div class="cs-metrics-area">
      <p class="cs-instruction">Geser intensitas dan amati perubahan pada indikator</p>
      
      <div class="cs-metrics-grid">
        <!-- Metric 1 -->
        <div class="cs-metric-box metric-green" :style="{ transform: `scale(${1 + sliderValue/500})` }">
          <h4 class="cs-metric-title">{{ conceptualData?.metric1Title }}</h4>
          <p class="cs-metric-desc">{{ conceptualData?.metric1Desc }}</p>
          <div class="cs-metric-value">{{ Math.round(sliderValue * 0.8) + 20 }} L/s</div>
        </div>
        
        <!-- Metric 2 -->
        <div class="cs-metric-box metric-blue" :style="{ transform: `scale(${1 + sliderValue/300})` }">
          <h4 class="cs-metric-title">{{ conceptualData?.metric2Title }}</h4>
          <p class="cs-metric-desc">{{ conceptualData?.metric2Desc }}</p>
          <div class="cs-metric-value">{{ Math.round(sliderValue * 1.5) }} m³</div>
        </div>
      </div>
    </div>
  </div>

  <div v-else class="mat-container">

    <!-- Image/Video Banner -->
    <div v-if="props.question?.image" class="mat-image-wrap">

      <!-- Banner: Video -->
      <div v-if="isVideo(props.question.image)" class="video-wrap">
        <div class="vid-inner">
          <canvas ref="bannerCanvasEl" class="mat-canvas-bg" width="640" height="360"></canvas>
          <video
            ref="bannerVideoEl"
            :src="imageUrl(props.question.image)"
            class="mat-video"
            controls
            @loadeddata="onBannerLoaded"
            @play="onBannerPlay"
            @pause="onBannerPause"
            @seeked="onBannerSeeked"
          ></video>
        </div>

        <div class="vid-controls">
          <button class="vid-play-btn" @click="toggleBannerPlay">
            <svg v-if="!bannerPlaying" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M8 5v14l11-7z"/></svg>
            <svg v-else viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
            {{ bannerPlaying ? 'Pause' : 'Play' }}
          </button>
        </div>
      </div>

      <!-- Banner: Image -->
      <img
        v-else
        :src="imageUrl(props.question.image)"
        :alt="props.question?.title || 'Materi'"
        class="mat-image"
      />
    </div>

    <!-- Header -->
    <div class="mat-header">
      <div class="mat-icon">
        <BookOpen v-if="!props.question?.material_type || props.question?.material_type === 'text'" :size="18" />
        <Video    v-else-if="props.question?.material_type === 'video'" :size="18" />
        <Music    v-else-if="props.question?.material_type === 'audio'" :size="18" />
      </div>
      <div class="mat-title-section">
        <h3 class="mat-title">{{ props.question?.title || 'Materi Pembelajaran' }}</h3>
        <p v-if="props.question?.subtitle" class="mat-subtitle">{{ props.question.subtitle }}</p>
      </div>
    </div>

    <div class="mat-divider"></div>

    <!-- Content -->
    <div class="mat-content">

      <!-- Text -->
      <div v-if="!props.question?.material_type || props.question?.material_type === 'text'" class="mat-text">
        <div v-html="props.question?.content || 'Konten tidak tersedia'"></div>
      </div>

      <!-- Video -->
      <div v-else-if="props.question?.material_type === 'video'" class="mat-media">
        <div v-if="props.question?.content" class="video-wrap">
          <div class="vid-inner">
            <canvas ref="canvasEl" class="mat-canvas-bg" width="640" height="360"></canvas>
            <video
              ref="videoEl"
              :src="props.question.content"
              class="mat-video"
              controls
              @loadeddata="onLoaded"
              @play="onPlay"
              @pause="onPause"
              @seeked="onSeeked"
            ></video>
          </div>

          <div class="vid-controls">
            <button class="vid-play-btn" @click="togglePlay">
              <svg v-if="!playing" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M8 5v14l11-7z"/></svg>
              <svg v-else viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
              {{ playing ? 'Pause' : 'Amati Perbuatan' }}
            </button>
          </div>
        </div>
        <p v-else class="mat-error">Video tidak tersedia</p>
      </div>

      <!-- Audio -->
      <div v-else-if="props.question?.material_type === 'audio'" class="mat-media">
        <audio
          v-if="props.question?.content"
          :src="props.question.content"
          controls
          class="mat-audio"
        ></audio>
        <p v-else class="mat-error">Audio tidak ti9ersedia</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* ── Conceptual Systematic Styles ── */
.cs-container {
  background: linear-gradient(180deg, #d3e9ff 0%, #b5dbff 100%);
  border-radius: 24px;
  padding: 2.5rem 2rem;
  box-shadow: 0 10px 30px rgba(0, 100, 200, 0.15);
  font-family: 'Nunito', 'Inter', sans-serif;
  overflow: hidden;
  position: relative;
  width: 100%;
}

.cs-title {
  text-align: center;
  font-size: 1.8rem;
  font-weight: 900;
  color: #1e3a5f;
  margin-bottom: 2.5rem;
  text-shadow: 0 2px 4px rgba(255, 255, 255, 0.5);
  letter-spacing: 0.5px;
}

.cs-grid {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2rem;
  margin-bottom: 3rem;
}

@media (min-width: 768px) {
  .cs-grid {
    flex-direction: row;
    justify-content: center;
    align-items: stretch;
    gap: 1.5rem;
  }
}

.cs-texts-left, .cs-texts-right {
  display: flex;
  flex-direction: column;
  justify-content: space-around;
  gap: 1.5rem;
  flex: 1;
  max-width: 280px;
}

.cs-text-box {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  border-radius: 16px;
  padding: 1.25rem;
  font-size: 0.95rem;
  font-weight: 600;
  color: #334155;
  line-height: 1.5;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  position: relative;
  transition: transform 0.3s ease;
}

.cs-text-box:hover {
  transform: translateY(-2px);
  background: rgba(255, 255, 255, 0.8);
}

.cs-arrow {
  display: none;
  position: absolute;
  top: 50%;
  width: 30px;
  height: 2px;
  background: #64748b;
}

@media (min-width: 768px) {
  .cs-arrow { display: block; }
  .cs-arrow.right { right: -30px; }
  .cs-arrow.left { left: -30px; }
  .cs-arrow::after {
    content: '';
    position: absolute;
    top: -4px;
    border-top: 5px solid transparent;
    border-bottom: 5px solid transparent;
  }
  .cs-arrow.right::after { right: -5px; border-left: 6px solid #64748b; }
  .cs-arrow.left::after { left: -5px; border-right: 6px solid #64748b; }
}

.cs-image-wrap {
  flex: 0 0 auto;
  display: flex;
  justify-content: center;
  align-items: center;
}

.cs-center-img {
  width: 100%;
  max-width: 280px;
  height: auto;
  object-fit: contain;
  border-radius: 20px;
  box-shadow: 0 8px 24px rgba(0,0,0,0.1);
  background: #fff;
  padding: 10px;
}

/* ── Slider Area ── */
.cs-slider-area {
  display: flex;
  justify-content: center;
  margin-bottom: 2rem;
}

.cs-slider-wrap {
  display: flex;
  align-items: center;
  gap: 1rem;
  width: 100%;
  max-width: 600px;
  background: rgba(255, 255, 255, 0.4);
  padding: 1rem 1.5rem;
  border-radius: 100px;
}

.cs-slider-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.cs-slider-label {
  font-weight: 800;
  color: #1e3a5f;
  font-size: 0.95rem;
}

.cs-slider {
  flex: 1;
  -webkit-appearance: none;
  appearance: none;
  height: 12px;
  background: #e2e8f0;
  border-radius: 10px;
  outline: none;
  box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.cs-slider::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #3b82f6;
  cursor: pointer;
  box-shadow: 0 0 0 4px #fff, 0 4px 8px rgba(0,0,0,0.2);
  transition: transform 0.1s;
}
.cs-slider::-webkit-slider-thumb:active { transform: scale(1.1); }

/* ── Metrics Area ── */
.cs-metrics-area {
  background: rgba(126, 191, 252, 0.4);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 2rem;
  text-align: center;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.cs-instruction {
  font-weight: 700;
  color: #1e40af;
  margin-bottom: 1.5rem;
  font-size: 1.05rem;
}

.cs-metrics-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  justify-content: center;
}

@media (min-width: 640px) {
  .cs-metrics-grid { flex-direction: row; }
}

.cs-metric-box {
  padding: 1.25rem 2rem;
  border-radius: 20px;
  color: white;
  transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow: 0 8px 20px rgba(0,0,0,0.15);
  flex: 1;
  max-width: 280px;
  margin: 0 auto;
}

.metric-green {
  background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
  border: 3px solid #bef264;
}

.metric-blue {
  background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
  border: 3px solid #93c5fd;
}

.cs-metric-title {
  font-size: 1.2rem;
  font-weight: 800;
  margin: 0;
  text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.cs-metric-desc {
  font-size: 0.85rem;
  opacity: 0.9;
  margin: 0.25rem 0 0.75rem 0;
}

.cs-metric-value {
  background: rgba(255, 255, 255, 0.25);
  padding: 0.5rem;
  border-radius: 12px;
  font-weight: 900;
  font-size: 1.2rem;
  backdrop-filter: blur(4px);
}

/* ── Standard Material Styles ── */
.mat-container {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  width: 100%;
  max-width: 100%;
  box-sizing: border-box;
}

/* ── Image Banner ── */
.mat-image-wrap {
  width: 100%;
  overflow: hidden;
  background: #f1f5f9;
}

.mat-image {
  width: 100%;
  max-height: 280px;
  object-fit: cover;
  display: block;
}

/* ── Header ── */
.mat-header {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  padding: 1.1rem;
  background: linear-gradient(90deg, #f8fafc, #f1f5f9);
}

.mat-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #dbeafe;
  border-radius: 10px;
  color: #0369a1;
  flex-shrink: 0;
}

.mat-title-section { flex: 1; text-align: left; }

.mat-title {
  margin: 0;
  font-size: 0.95rem;
  font-weight: 700;
  color: #1e293b;
}

.mat-subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 500;
}

/* ── Divider ── */
.mat-divider { height: 1px; background: #e2e8f0; }

/* ── Content ── */
.mat-content { padding: 1.25rem; background: #ffffff; }

.mat-text { line-height: 1.7; color: #475569; }

.mat-text p {
  margin: 0;
  white-space: pre-wrap;
  word-wrap: break-word;
  word-break: break-word;
  overflow-wrap: break-word;
  font-size: 0.95rem;
  max-width: 100%;
}

/* ── Mobile ── */
@media (max-width: 640px) {
  .mat-image { max-height: 200px; }
  .mat-header { padding: 0.85rem; gap: 0.65rem; }
  .mat-icon { width: 34px; height: 34px; }
  .mat-title { font-size: 0.875rem; }
  .mat-subtitle { font-size: 0.75rem; }
  .mat-content { padding: 0.9rem; }
  .mat-text p { font-size: 0.875rem; }
}

.mat-media {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  align-items: center;
}

/* ── Video Player ── */
.video-wrap { width: 100%; display: flex; flex-direction: column; }

.vid-inner {
  position: relative;
  width: 100%;
  max-height: 400px;
  overflow: hidden;
  background: #000;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Canvas blur background */
.mat-canvas-bg {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(18px) brightness(0.45) saturate(1.4);
  transform: scale(1.12);
  pointer-events: none;
  z-index: 0;
}

.mat-video {
  position: relative;
  z-index: 1;
  width: 100%;
  max-height: 400px;
  object-fit: contain;
  display: block;
  background: transparent;
}

/* ── Controls ── */
.vid-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px 16px;
  background: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.vid-play-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 9px 28px;
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: #fff;
  font-size: 13.5px;
  font-weight: 700;
  border: none;
  border-radius: 50px;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(29,78,216,0.35);
  transition: all 0.18s cubic-bezier(0.34,1.56,0.64,1);
}
.vid-play-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(29,78,216,0.45); }
.vid-play-btn:active { transform: translateY(1px); }

/* ── Audio ── */
.mat-audio { width: 100%; max-width: 100%; }

.mat-error {
  text-align: center;
  color: #dc2626;
  font-weight: 600;
  margin: 1rem 0 0;
}
</style>
