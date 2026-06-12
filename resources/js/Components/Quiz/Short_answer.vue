<script setup>
import { ref, watch } from 'vue'

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
  <div class="sa">
    <div class="sa-container">
      <img v-if="question?.image" :src="`/storage/${question.image}`" class="sa-bg" alt="Pertanyaan" />
      <div v-else class="sa-bg-placeholder"></div>

      <div class="sa-overlay">
        <div class="sa-bubble">
          <div class="sa-title">Uji Pemahaman Singkat</div>
          <div class="sa-question-text" v-html="question?.question_text"></div>
          <div class="sa-input-wrapper">
            <input 
              v-model="answerText" 
              @input="handleInput" 
              type="text" 
              placeholder="Ketik jawaban kamu di sini..." 
              class="sa-input" 
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.sa {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.sa-container {
  position: relative;
  width: 100%;
  max-width: 800px;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  background: #f1f5f9;
  border: 4px solid #fff;
  min-height: 350px;
  display: flex;
  flex-direction: column;
}

.sa-bg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  z-index: 1;
}

.sa-bg-placeholder {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  overflow: hidden;
  z-index: 1;
}

.sa-bg-placeholder::before {
  content: "";
  position: absolute;
  top: -100px;
  left: -100px;
  right: -100px;
  bottom: -100px;
  background: repeating-linear-gradient(
    45deg,
    #fef08a,
    #fef08a 20px,
    #fef9c3 20px,
    #fef9c3 40px
  );
  animation: moveStripesShortAnswer 2s linear infinite;
}

@keyframes moveStripesShortAnswer {
  0% { transform: translateX(0); }
  100% { transform: translateX(-56.56px); }
}

.sa-overlay {
  position: relative;
  z-index: 2;
  flex: 1;
  display: flex;
  align-items: flex-end;
  justify-content: flex-start;
  padding: 24px;
}

.sa-bubble {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  padding: 20px;
  border-radius: 16px;
  border: 3px solid #e2e8f0;
  box-shadow: 0 8px 25px rgba(0,0,0,0.1);
  max-width: 90%;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.sa-title {
  font-size: 14px;
  font-weight: 900;
  color: #f59e0b;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: -10px;
}

.sa-question-text {
  font-size: 16px;
  font-weight: 800;
  color: #1e293b;
  line-height: 1.5;
}

.sa-input-wrapper {
  width: 100%;
}

.sa-input {
  width: 100%;
  padding: 14px 20px;
  font-size: 15px;
  font-family: 'Nunito', sans-serif;
  font-weight: 800;
  color: #1e293b;
  background-color: #f8fafc;
  border: 3px solid #cbd5e1;
  border-radius: 9999px;
  outline: none;
  transition: all 0.2s ease;
}

.sa-input::placeholder {
  color: #94a3b8;
  font-weight: 700;
}

.sa-input:focus {
  background-color: #fff;
  border-color: #3b82f6;
  box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
}

@media (max-width: 600px) {
  .sa-bubble {
    padding: 16px;
    max-width: 100%;
  }
  .sa-input {
    padding: 12px 16px;
    font-size: 14px;
  }
  .sa-question-text {
    font-size: 14px;
  }
}
</style>
