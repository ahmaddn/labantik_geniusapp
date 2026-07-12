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
  padding: 8px 0;
}

.sa-card {
  width: 100%;
  max-width: 650px;
  background: #ffffff;
  border: 3px solid #e2e8f0;
  border-bottom: 6px solid #cbd5e1;
  border-radius: 28px;
  padding: 28px 32px;
  display: flex;
  flex-direction: column;
  gap: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.02);
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
  border: 2px solid #e9d5ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 3px 0 0 #e9d5ff;
}

.sa-title {
  font-family: 'Nunito', sans-serif;
  font-size: 14px;
  font-weight: 900;
  color: #a855f7; /* Purple to match icon */
  letter-spacing: 1px;
}

.sa-image-wrap {
  width: 100%;
  border-radius: 20px;
  overflow: hidden;
  border: 3px solid #e2e8f0;
  background: #f8fafc;
}

.sa-image {
  width: 100%;
  max-height: 260px;
  object-fit: cover;
  display: block;
}

.sa-question {
  font-family: 'Nunito', sans-serif;
  font-size: 20px;
  font-weight: 800;
  color: #334155;
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
  padding: 18px 20px;
  font-family: 'Nunito', sans-serif;
  font-size: 16px;
  font-weight: 700;
  color: #334155;
  background-color: #f8fafc;
  border: 3px solid #cbd5e1;
  border-radius: 18px;
  outline: none;
  resize: vertical;
  min-height: 120px;
  transition: all 0.25s cubic-bezier(0.25, 0.8, 0.25, 1);
  line-height: 1.5;
}

.sa-input::placeholder {
  color: #94a3b8;
  font-weight: 600;
}

.sa-input:focus {
  background-color: #ffffff;
  border-color: #a855f7;
  box-shadow: 0 0 0 5px rgba(168, 85, 247, 0.15);
}

/* Mobile Responsiveness */
@media (max-width: 600px) {
  .sa-card {
    padding: 20px 24px;
    border-radius: 24px;
    gap: 20px;
  }
  
  .sa-title {
    font-size: 12px;
  }
  
  .sa-icon-wrap {
    width: 32px;
    height: 32px;
  }
  
  .sa-question {
    font-size: 17px;
  }
  
  .sa-input {
    font-size: 15px;
    padding: 14px 16px;
    min-height: 100px;
  }
}
</style>
