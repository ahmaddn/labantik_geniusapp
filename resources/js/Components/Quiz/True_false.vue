<script setup>
import { ref, watch } from 'vue'
import { CheckCircle2, XCircle, Circle } from 'lucide-vue-next'
import { useSfx } from '@/Composable/useSfx'

const props = defineProps({
  question: {
    type: Object,
    required: true,
  },
  modelValue: {
    type: [String, Boolean, Number],
    default: null,
  },
})

const emit = defineEmits(['update-answer'])

const { playPop } = useSfx()
const selectedAnswer = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  selectedAnswer.value = newVal
})

const isTrue = (opt) => {
  const text = (opt.option_text || opt.text || '').toLowerCase()
  return text.includes('benar') || text === 'true'
}

const handleSelect = (optionId) => {
  playPop()
  selectedAnswer.value = optionId
  emit('update-answer', {
    questionId: props.question.id,
    value: optionId,
  })
}
</script>

<template>
  <div class="tf-container">
    <div class="tf-options">
      <button
        v-for="option in props.question?.options || []"
        :key="option.id"
        class="tf-option"
        :class="{
          selected: selectedAnswer === option.id,
          'tf-true': isTrue(option),
          'tf-false': !isTrue(option)
        }"
        @click="handleSelect(option.id)"
      >
        <!-- Image if exists -->
        <div v-if="option.option_image" class="tf-img-wrap">
          <img
            :src="`/storage/${option.option_image}`"
            :alt="option.option_text || option.text"
            class="tf-img"
          />
        </div>

        <div class="tf-inner">
          <div class="tf-content-left">
            <span class="tf-label">{{ option.option_text || option.text }}</span>
          </div>
          
          <div class="tf-indicator">
            <template v-if="selectedAnswer === option.id">
              <CheckCircle2 v-if="isTrue(option)" :size="22" color="#22c55e" :stroke-width="3" />
              <XCircle v-else :size="22" color="#ef4444" :stroke-width="3" />
            </template>
            <Circle v-else :size="22" color="#cbd5e1" :stroke-width="2.5" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@700;800;900&display=swap');

.tf-container {
  display: flex;
  flex-direction: column;
  gap: 16px;
  font-family: 'Nunito', sans-serif;
}

.tf-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}

.tf-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: #ffffff;
  border: 3px solid #e2e8f0;
  border-bottom-width: 6px;
  border-radius: 24px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.25, 0.8, 0.25, 1);
  overflow: hidden;
  padding: 0;
  width: 100%;
  position: relative;
  outline: none;
}

.tf-option:hover {
  transform: translateY(-2px);
  border-color: #cbd5e1;
}

/* True Selected Styling (Green) */
.tf-true.selected {
  background: #f0fdf4;
  border-color: #22c55e;
  box-shadow: 0 4px 12px rgba(34, 197, 94, 0.08);
}
.tf-true.selected:hover {
  border-color: #22c55e;
}

/* False Selected Styling (Red) */
.tf-false.selected {
  background: #fef2f2;
  border-color: #ef4444;
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.08);
}
.tf-false.selected:hover {
  border-color: #ef4444;
}

/* Image area */
.tf-img-wrap {
  width: 100%;
  background: #ffffff;
  border-bottom: 2px solid #f1f5f9;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 12px;
  height: 140px;
  overflow: hidden;
}

.tf-img {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  border-radius: 12px;
}

/* Inner row */
.tf-inner {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 18px 20px;
  width: 100%;
}

.tf-content-left {
  flex: 1;
  display: flex;
  align-items: center;
}

.tf-label {
  font-size: 16px;
  font-weight: 800;
  color: #334155;
  text-align: left;
}

.tf-true.selected .tf-label {
  color: #15803d;
}

.tf-false.selected .tf-label {
  color: #b91c1c;
}

.tf-indicator {
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}

@media (max-width: 640px) {
  .tf-options {
    grid-template-columns: 1fr;
    gap: 12px;
  }
  .tf-inner {
    padding: 14px 16px;
  }
  .tf-label {
    font-size: 15px;
  }
}
</style>
