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
    <div v-if="question?.image" class="sa-img-container">
        <img :src="`/storage/${question.image}`" class="sa-img" alt="Pertanyaan" />
    </div>

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
</template>

<style scoped>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.sa {
  display: flex;
  flex-direction: column;
  gap: 16px;
  width: 100%;
}

.sa-img-container {
    width: 100%;
    display: flex;
    justify-content: center;
    margin-bottom: 12px;
}
.sa-img {
    max-height: 250px;
    max-width: 100%;
    object-fit: contain;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border: 3px solid white;
}

.sa-input-wrapper {
  width: 100%;
}

.sa-input {
  width: 100%;
  padding: 16px 20px;
  font-size: 16px;
  font-family: 'Nunito', sans-serif;
  font-weight: 800;
  color: #1e293b;
  background-color: #f8fafc;
  border: 3px solid #cbd5e1;
  border-radius: 24px;
  outline: none;
  transition: all 0.2s ease;
  text-align: center;
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
  .sa-input {
    padding: 12px 16px;
    font-size: 14px;
    border-radius: 16px;
  }
}
</style>
