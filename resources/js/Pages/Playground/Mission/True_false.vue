<script setup>
import { ref, watch } from 'vue'
import { CheckCircle2, Circle } from 'lucide-vue-next'

const props = defineProps({
  question: {
    type: Object,
    required: true,
    // shape: { id, question_text, options: [{ id, option_text, option_image, is_correct }] }
  },
  modelValue: {
    type: [String, Boolean, Number],
    default: null,
  },
})

const emit = defineEmits(['update-answer'])

const selectedAnswer = ref(props.modelValue)

watch(() => props.modelValue, (newVal) => {
  selectedAnswer.value = newVal
})

const isTrue = (opt) => {
  const text = (opt.option_text || opt.text || '').toLowerCase()
  return text.includes('benar') || text === 'true'
}

const handleSelect = (optionId) => {
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
          <div class="tf-icon-wrap">
            <span class="tf-emoji">{{ isTrue(option) ? '✓' : '✗' }}</span>
          </div>
          <span class="tf-label">{{ option.option_text || option.text }}</span>
          <div class="tf-radio">
            <CheckCircle2 v-if="selectedAnswer === option.id" :size="20" :stroke-width="2" />
            <Circle v-else :size="20" :stroke-width="1.8" />
          </div>
        </div>
      </button>
    </div>
  </div>
</template>

<style scoped>
.tf-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.tf-options {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.9rem;
}

.tf-option {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0;
  background: #f8fafc;
  border: 2.5px solid #e2e8f0;
  border-radius: 14px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
  font-family: inherit;
  overflow: hidden;
  padding: 0;
}

.tf-option:hover {
  background: #f1f5f9;
  border-color: #cbd5e1;
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
}

.tf-true.selected {
  background: #dcfce7;
  border-color: #4ade80;
  box-shadow: 0 0 0 4px rgba(74, 222, 128, 0.12);
}

.tf-false.selected {
  background: #fee2e2;
  border-color: #ef4444;
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.12);
}

/* Image area */
.tf-img-wrap {
  width: 100%;
  background: #fff;
  border-bottom: 1px solid rgba(0,0,0,0.06);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 10px;
  max-height: 120px;
  overflow: hidden;
}

.tf-img {
  max-width: 100%;
  max-height: 100px;
  object-fit: contain;
  border-radius: 8px;
}

/* Inner row */
.tf-inner {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.6rem;
  padding: 1rem;
  width: 100%;
}

.tf-icon-wrap {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  flex-shrink: 0;
  font-size: 16px;
  font-weight: 900;
}

.tf-true .tf-icon-wrap {
  background: rgba(74, 222, 128, 0.15);
  color: #16a34a;
}

.tf-false .tf-icon-wrap {
  background: rgba(239, 68, 68, 0.1);
  color: #dc2626;
}

.tf-label {
  font-size: 0.95rem;
  font-weight: 700;
  color: #334155;
  flex: 1;
  text-align: center;
}

.tf-true.selected .tf-label {
  color: #166534;
}

.tf-false.selected .tf-label {
  color: #7f1d1d;
}

.tf-radio {
  flex-shrink: 0;
  color: #94a3b8;
}

.tf-true.selected .tf-radio {
  color: #16a34a;
}

.tf-false.selected .tf-radio {
  color: #dc2626;
}

@media (max-width: 480px) {
  .tf-options {
    grid-template-columns: 1fr;
  }
}
</style>
