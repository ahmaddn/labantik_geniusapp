<script setup>
import { ref } from 'vue'
import { ChevronDown, BookOpen, Music, Video } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // shape: { id, title, content, material_type: 'text|video|audio' }
  },
})

const expanded = ref(false)
</script>

<template>
  <div class="mat-container">
    <button class="mat-header" @click="expanded = !expanded">
      <div class="mat-icon">
        <BookOpen v-if="!props.question?.material_type || props.question?.material_type === 'text'" :size="18" />
        <Video v-else-if="props.question?.material_type === 'video'" :size="18" />
        <Music v-else-if="props.question?.material_type === 'audio'" :size="18" />
      </div>
      <div class="mat-title-section">
        <h3 class="mat-title">{{ props.question?.title || 'Materi Pembelajaran' }}</h3>
        <p v-if="props.question?.subtitle" class="mat-subtitle">{{ props.question.subtitle }}</p>
      </div>
      <div class="mat-toggle" :class="{ open: expanded }">
        <ChevronDown :size="18" />
      </div>
    </button>

    <Transition name="mat-slide">
      <div v-show="expanded" class="mat-content">
        <!-- Text Content -->
        <div v-if="!props.question?.material_type || props.question?.material_type === 'text'" class="mat-text">
          <p>{{ props.question?.content || 'Konten tidak tersedia' }}</p>
        </div>

        <!-- Video Content -->
        <div v-else-if="props.question?.material_type === 'video'" class="mat-media">
          <video
            v-if="props.question?.content"
            :src="props.question.content"
            controls
            class="mat-video"
          ></video>
          <p v-else class="mat-error">Video tidak tersedia</p>
        </div>

        <!-- Audio Content -->
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
    </Transition>
  </div>
</template>

<style scoped>
.mat-container {
  background: #fff;
  border-radius: 12px;
  border: 1px solid #e2e8f0;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.2s;
}

.mat-container:hover {
  border-color: #cbd5e1;
}

.mat-header {
  display: flex;
  align-items: center;
  gap: 0.9rem;
  width: 100%;
  padding: 1.1rem;
  background: linear-gradient(90deg, #f8fafc, #f1f5f9);
  border: none;
  cursor: pointer;
  transition: all 0.2s;
  font-family: inherit;
}

.mat-header:hover {
  background: linear-gradient(90deg, #f1f5f9, #e2e8f0);
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

.mat-toggle {
  display: flex;
  align-items: center;
  color: #64748b;
  transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mat-toggle.open {
  transform: rotate(180deg);
}

.mat-content {
  padding: 1.25rem;
  border-top: 1px solid #e2e8f0;
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
  font-size: 0.95rem;
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

.mat-slide-enter-active,
.mat-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.mat-slide-enter-from {
  opacity: 0;
  max-height: 0;
}

.mat-slide-leave-to {
  opacity: 0;
  max-height: 0;
}
</style>
