<script setup>
import { ref, onUnmounted } from 'vue'
import { BookOpen, Music, Video } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
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
  <div class="mat-container">

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
        <p>{{ props.question?.content || 'Konten tidak tersedia' }}</p>
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
        <p v-else class="mat-error">Audio tidak tersedia</p>
      </div>

    </div>
  </div>
</template>

<style scoped>
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
