<script setup>
import { ref, watch } from 'vue'
import { PencilLine } from 'lucide-vue-next'

const props = defineProps({
  question:   { type: Object, required: true },
  quiz:       { type: Object, required: false },
  modelValue: { type: String, default: '' },
})

const emit = defineEmits(['update-answer'])

const answerText = ref(props.modelValue || '')

watch(() => props.modelValue, (v) => { answerText.value = v || '' })

const handleInput = () => {
  emit('update-answer', { questionId: props.question.id, value: answerText.value })
}
</script>

<template>
  <div class="sa-wrapper">
    <div class="sa-card">
      
      <!-- Header dengan Icon -->
      <div class="sa-header">
        <div class="sa-icon-wrap">
          <PencilLine :size="20" stroke-width="3" color="#a855f7" />
        </div>
        <h2 class="sa-title">UJI PEMAHAMAN SINGKAT</h2>
      </div>

      <!-- Gambar Opsional -->
      <div v-if="question?.image" class="sa-image-wrap">
        <img :src="`/storage/${question.image}`" class="sa-image" alt="Pertanyaan" />
      </div>

      <!-- Teks Pertanyaan -->
      <div class="sa-question" v-html="question?.question_text"></div>

      <!-- Input Jawaban -->
      <div class="sa-input-group">
        <textarea 
          v-model="answerText" 
          @input="handleInput" 
          placeholder="Ketik jawabanmu di sini..." 
          class="sa-input"
          rows="2"
        ></textarea>
      </div>

    </div>
  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.sa-wrapper {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  padding: 10px;
}

.sa-card {
  width: 100%;
  max-width: 650px;
  background: #ffffff;
  border: 2px solid #e5e7eb;
  border-bottom: 4px solid #e5e7eb;
  border-radius: 24px;
  padding: 28px 32px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  box-shadow: 0 4px 0 rgba(0,0,0,0.02);
}

.sa-header {
  display: flex;
  align-items: center;
  gap: 12px;
}

.sa-icon-wrap {
  width: 36px;
  height: 36px;
  background: #f3e8ff; /* Light purple background */
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sa-title {
  font-family: 'Nunito', sans-serif;
  font-size: 15px;
  font-weight: 900;
  color: #a855f7; /* Purple to match icon */
  letter-spacing: 1.5px;
}

.sa-image-wrap {
  width: 100%;
  border-radius: 16px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
  background: #f9fafb;
}

.sa-image {
  width: 100%;
  max-height: 250px;
  object-fit: cover;
  display: block;
}

.sa-question {
  font-family: 'Nunito', sans-serif;
  font-size: 22px;
  font-weight: 800;
  color: #374151; /* Gray-700 */
  line-height: 1.5;
}

/* Memastikan tag P di dalam pertanyaan juga tebal dan ukurannya pas */
.sa-question :deep(p) {
  margin: 0;
}

.sa-input-group {
  width: 100%;
}

.sa-input {
  width: 100%;
  padding: 16px 20px;
  font-family: 'Nunito', sans-serif;
  font-size: 18px;
  font-weight: 700;
  color: #1f2937;
  background-color: #f3f4f6; /* Gray-100 */
  border: 2px solid #e5e7eb; /* Light border */
  border-bottom: 4px solid #e5e7eb; /* Thick bottom border for 3D effect */
  border-radius: 16px;
  outline: none;
  resize: vertical;
  min-height: 120px;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}

.sa-input::placeholder {
  color: #9ca3af;
  font-weight: 700;
}

.sa-input:focus {
  background-color: #ffffff;
  border-color: #3b82f6; /* Blue border on focus */
  border-bottom-color: #3b82f6;
  box-shadow: 0 4px 14px rgba(59, 130, 246, 0.15);
}

/* Mobile Responsiveness */
@media (max-width: 600px) {
  .sa-card {
    padding: 20px 24px;
    border-radius: 20px;
    gap: 20px;
  }
  
  .sa-title {
    font-size: 14px;
  }
  
  .sa-icon-wrap {
    width: 32px;
    height: 32px;
  }
  
  .sa-question {
    font-size: 18px;
  }
  
  .sa-input {
    font-size: 16px;
    padding: 14px 16px;
    min-height: 100px;
  }
}
</style>
