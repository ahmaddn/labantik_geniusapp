<script setup>
import { BookOpen, Music, Video } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // shape: { id, title, subtitle, content, image, material_type: 'text|video|audio' }
  },
})

const imageUrl = (path) => {
  if (!path) return null
  if (path.startsWith('http://') || path.startsWith('https://')) return path
  const base = window.location.origin
  const clean = path.startsWith('/') ? path : `/storage/${path}`
  return `${base}${clean}`
}
</script>

<template>
  <div class="mat-container">

    <!-- Image Banner (if exists) -->
    <div v-if="props.question?.image" class="mat-image-wrap">
      <img :src="imageUrl(props.question.image)" :alt="props.question?.title || 'Materi'" class="mat-image" />
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
        <video
          v-if="props.question?.content"
          :src="props.question.content"
          controls
          class="mat-video"
        ></video>
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
  max-height: 280px;
  overflow: hidden;
  background: #f1f5f9;
}

.mat-image {
  width: 100%;
  height: 100%;
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

.mat-title-section {
  flex: 1;
  text-align: left;
}

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
.mat-divider {
  height: 1px;
  background: #e2e8f0;
}

/* ── Content ── */
.mat-content {
  padding: 1.25rem;
  background: #ffffff;
}

.mat-text {
  line-height: 1.7;
  color: #475569;
}

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
  .mat-image-wrap {
    max-height: 200px;
  }

  .mat-image {
    max-height: 200px;
  }

  .mat-header {
    padding: 0.85rem;
    gap: 0.65rem;
  }

  .mat-icon {
    width: 34px;
    height: 34px;
    flex-shrink: 0;
  }

  .mat-title {
    font-size: 0.875rem;
  }

  .mat-subtitle {
    font-size: 0.75rem;
  }

  .mat-content {
    padding: 0.9rem;
  }

  .mat-text p {
    font-size: 0.875rem;
  }
}

.mat-media {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  align-items: center;
}

.mat-video {
  width: 100%;
  max-width: 100%;
  border-radius: 8px;
  background: #000;
}

.mat-audio {
  width: 100%;
  max-width: 100%;
}

.mat-error {
  text-align: center;
  color: #dc2626;
  font-weight: 600;
  margin: 1rem 0 0;
}
</style>
