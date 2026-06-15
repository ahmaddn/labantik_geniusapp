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

      <div class="sa-overlay" :class="{ 'has-bg': question?.image }">
        <div class="sa-bubble">
          <div v-if="question?.question_text" class="sa-question-text" v-html="question.question_text"></div>
          <div class="sa-input-wrapper">
            <input 
              v-model="answerText" 
              @input="handleInput" 
              type="text" 
              placeholder="Ketik jawaban di sini" 
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
  display: flex;
  flex-direction: column;
}

.sa-container {
  position: relative;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.sa-bg {
  width: 100%;
  max-height: 200px;
  object-fit: contain;
  border-radius: 16px;
  background: #f7f7f7;
}

.sa-overlay {
  width: 100%;
  display: flex;
  flex-direction: column;
}

.sa-bubble {
  background: transparent;
  width: 100%;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.sa-question-text {
  font-size: 19px;
  font-weight: 700;
  color: #3c3c3c;
  line-height: 1.4;
  margin-bottom: 8px;
}

.sa-input-wrapper {
  width: 100%;
}

.sa-input {
  width: 100%;
  padding: 16px;
  font-size: 16px;
  font-family: 'Nunito', sans-serif;
  font-weight: 700;
  color: #3c3c3c;
  background-color: #f7f7f7;
  border: 2px solid #e5e5e5;
  border-radius: 16px;
  outline: none;
  transition: all 0.2s ease;
}

.sa-input::placeholder {
  color: #afafaf;
  font-weight: 700;
}

.sa-input:focus {
  background-color: #fff;
  border-color: #1cb0f6;
}

@media (max-width: 600px) {
  .sa-input {
    padding: 14px 16px;
    font-size: 15px;
  }
  .sa-question-text {
    font-size: 17px;
  }
}
</style>
